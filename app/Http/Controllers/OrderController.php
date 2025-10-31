<?php

namespace App\Http\Controllers;

use App\Models\Checkout;
use App\Models\Invoice;
use App\Models\OrderedBuild;
use App\Models\ShoppingCart;
use App\Models\UserBuild;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = OrderedBuild::with([
            'userBuild.case',
            'userBuild.cpu',
            'userBuild.motherboard',
            'userBuild.gpu',
            'userBuild.ram',
            'userBuild.storage',
            'userBuild.psu',
            'userBuild.cooler', 
            'userBuild.user',
            ])
            ->where(function ($query) {
                $query->where('user_id', Auth::id())
                    ->orWhere(function ($q) {
                        $q->whereNull('user_id')
                            ->where('status', 'Pending');
                    });
            })
            ->orderByRaw("
                CASE 
                    WHEN status = 'Approved' AND pickup_date IS NULL THEN 1
                    WHEN status = 'Pending' AND pickup_date IS NULL THEN 2
                    WHEN status = 'Picked Up' AND pickup_date IS NULL THEN 3
                    WHEN status = 'Picked Up' AND pickup_date IS NOT NULL THEN 4
                    WHEN status = 'Approved' AND pickup_date IS NOT NULL THEN 5
                    WHEN status = 'Declined' THEN 6
                    ELSE 7
                END
            ")
            ->orderBy('created_at', 'desc')  // FIFO within groups (oldest first)
            ->paginate(7);

        $allCheckouts = Checkout::with([
            'cartItem' => fn ($q) => $q->with([
                'case', 'cpu', 'gpu', 'motherboard', 'ram', 'storage', 'psu', 'cooler',
                'shoppingCart.user',
            ]),
        ])
        ->orderByRaw("
            CASE 
                WHEN pickup_status = 'Pending' AND pickup_date IS NULL THEN 1
                WHEN pickup_status IS NULL AND pickup_date IS NULL THEN 2
                WHEN pickup_status = 'Picked up' AND pickup_date IS NOT NULL THEN 3
                ELSE 4
            END
        ")
        ->orderBy('checkout_date', 'desc')
        ->get();

        // Step 1: Group by ShoppingCart ID + Checkout Timestamp
        $grouped = $allCheckouts->groupBy(function ($checkout) {
            return $checkout->cartItem->shopping_cart_id . '|' . $checkout->checkout_date->format('Y-m-d H:i:s');
        });

        // Step 2: Transform each group into a custom object
        $groupedOrders = $grouped->map(function ($checkouts) {
            $first = $checkouts->first();
            $cartItems = $checkouts->map->cartItem;

            return [
                'shopping_cart_id' => $first->cartItem->shopping_cart_id,
                'checkout_date' => $first->checkout_date,
                'total_cost' => $checkouts->sum('total_cost'),
                'payment_method' => $checkouts->pluck('payment_method')->unique()->implode(', '),
                'payment_status' => $checkouts->pluck('payment_status')->unique()->implode(', '),
                'pickup_status' => $checkouts->pluck('pickup_status')->unique()->implode(', '),
                'pickup_date' => $checkouts->min('pickup_date'),
                'user' => $first->cartItem->shoppingCart->user,
                'cart_items' => $cartItems->values()
            ];
        })->values();

        // Step 3: Manual Pagination
        $page = request()->get('page', 1);
        $perPage = 5;
        $paginatedGroupedOrders = new LengthAwarePaginator(
            $groupedOrders->forPage($page, $perPage),
            $groupedOrders->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );


        return view('staff.order', [
            'orders' => $orders,
            'groupedCheckouts' => $paginatedGroupedOrders
        ]);
    }


    public function approve($id) {
        $order = OrderedBuild::findOrFail($id);
        $staffUser = Auth::user();

        $oldStatus = $order->status;

        $order->update([
            'status' => 'Approved',
            'user_id' => $staffUser->id,
        ]);

        // Log the order approval
        ActivityLogService::orderApproved($order, $staffUser, $oldStatus);

        return redirect()->route('staff.order')->with([
            'message' => 'Order approved',
            'type' => 'success',
        ]);
    }

    public function decline($id) {
        $order = OrderedBuild::findOrFail($id);
        $staffUser = Auth::user();

        $oldStatus = $order->status;

        $order->update([
            'status' => 'Declined',
            'user_id' => $staffUser->id,
        ]);

        // Log the order decline
        ActivityLogService::orderDeclined($order, $staffUser, $oldStatus);

        return redirect()->route('staff.order')->with([
            'message' => 'Order declined',
            'type' => 'success',
        ]);
    }

    public function ready($id) {
        $order = OrderedBuild::findOrFail($id);
        $staffUser = Auth::user();

        $order->update([
            'pickup_status' => 'Pending',
        ]);

        // Log the order ready for pickup
        ActivityLogService::orderReadyForPickup($order, $staffUser);

        return redirect()->route('staff.order')->with([
            'message' => 'Order ready for pickup',
            'type' => 'success',
        ]);
    }

    public function readyComponents($cartId, $date)
    {
        $staffUser = Auth::user();
        
        // Convert the 'date' to a Carbon instance (including time)
        $checkoutDate = Carbon::parse($date);
        
        // Find the specific checkout records grouped by cartId and checkoutDate
        $checkouts = Checkout::with('cartItem')
            ->whereHas('cartItem', function($query) use ($cartId) {
                $query->where('shopping_cart_id', $cartId);
            })
            ->whereDate('checkout_date', $checkoutDate->toDateString()) // Filter by the date part
            ->whereTime('checkout_date', $checkoutDate->toTimeString()) // Filter by the time part
            ->get();

        // Update the pickup_status to 'Pending' for each of the checkouts in this group
        foreach ($checkouts as $checkout) {
            $oldStatus = $checkout->pickup_status;
            
            $checkout->update([
                'pickup_status' => 'Pending',
            ]);

            // Log each component ready for pickup
            ActivityLogService::componentReadyForPickup($checkout, $staffUser, $oldStatus);
        }

        return back()->with([
            'message' => 'The selected items are now ready for pickup.',
            'type' => 'success',
        ]);
    }

    public function pickup($id) {
        $userId = Auth::id();
        $staffUser = Auth::user();

        $order = OrderedBuild::findOrFail($id);

        $oldData = [
            'pickup_status' => $order->pickup_status,
            'payment_status' => $order->payment_status,
        ];

        $order->update([
            'pickup_status' => 'Picked up',
            'pickup_date' => now(),
            'payment_status' => 'Paid'
        ]);

        $invoice = Invoice::create([
            'build_id' => $id,
            'staff_id' => $userId,
        ]);

        // Log the order pickup
        ActivityLogService::orderPickedUp($order, $staffUser, $oldData);
        
        // Log the invoice creation
        ActivityLogService::invoiceCreated($invoice, $staffUser);

        return redirect()->route('staff.order')->with([
            'message' => 'Order marked as picked up',
            'type' => 'success',
        ]);
    }
    
    public function pickupComponents($cartId, $date)
    {
        $userId = Auth::id();
        $staffUser = Auth::user();

        // Convert the 'date' to a Carbon instance (including time)
        $checkoutDate = Carbon::parse($date);

        // Find the specific checkout records grouped by cartId and checkoutDate
        $checkouts = Checkout::with('cartItem')
            ->whereHas('cartItem', function($query) use ($cartId) {
                $query->where('shopping_cart_id', $cartId);
            })
            ->whereDate('checkout_date', $checkoutDate->toDateString()) // Filter by the date part
            ->whereTime('checkout_date', $checkoutDate->toTimeString()) // Filter by the time part
            ->get();

        // Loop through each matching checkout and update its related pickup status
        foreach ($checkouts as $checkout) {
            $oldData = [
                'pickup_status' => $checkout->pickup_status,
                'payment_status' => $checkout->payment_status,
            ];

            $checkout->update([
                'pickup_status' => 'Picked up',
                'pickup_date' => now(),
                'payment_status' => 'Paid'
            ]);

            // Log each component pickup
            ActivityLogService::componentPickedUp($checkout, $staffUser, $oldData);
        }

        $invoice = Invoice::create([
            'order_id' => $checkouts->first()->id,
            'staff_id' => $userId,
        ]);

        // Log the invoice creation for components
        ActivityLogService::invoiceCreated($invoice, $staffUser);

        return back()->with([
            'message' => 'The selected items have been marked as picked up.',
            'type' => 'success',
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

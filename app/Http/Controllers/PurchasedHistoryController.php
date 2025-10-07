<?php

namespace App\Http\Controllers;

use App\Models\Checkout;
use App\Models\Order;
use App\Models\OrderedBuild;
use Illuminate\Support\Facades\Auth;

class PurchasedHistoryController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $user = Auth::user();

        // Get individual component checkouts
        $allCheckouts = Checkout::with([
            'cartItem.shoppingCart.user',
            'cartItem.case',
            'cartItem.cpu', 
            'cartItem.gpu',
            'cartItem.motherboard',
            'cartItem.ram',
            'cartItem.storage',
            'cartItem.psu',
            'cartItem.cooler',
            'invoice'
        ])
        ->whereHas('cartItem.shoppingCart', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->where('pickup_status', 'Pending')
        ->orWhereNotNull('pickup_date')
        ->orderBy('checkout_date', 'desc')
        ->get();

        // Group by ShoppingCart ID + Checkout Timestamp
        $grouped = $allCheckouts->groupBy(function ($checkout) {
            return $checkout->cartItem->shopping_cart_id . '|' . $checkout->checkout_date->format('Y-m-d H:i:s');
        });

        // Transform individual component orders
        $componentOrders = $grouped->map(function ($checkouts) {
            $first = $checkouts->first();
            $cartItems = $checkouts->map->cartItem;

            return [
                'type' => 'component',
                'id' => 'comp_' . $first->cartItem->shopping_cart_id,
                'order_id' => $first->cartItem->shopping_cart_id,
                'checkout_date' => $first->checkout_date,
                'pickup_date' => $first->pickup_date,
                'updated_at' => $first->updated_at,
                'pickup_status' => $first->pickup_status,
                'total_cost' => $checkouts->sum('total_cost'),
                'cart_items' => $cartItems->values(),
                'payment_method' => $first->payment_method,
                'payment_status' => $first->payment_status,
                'user' => $first->cartItem->shoppingCart->user,
                'build_name' => 'Custom Components Order',
            ];
        })->values();

        // Get complete build orders
        $buildOrders = OrderedBuild::with([
            'user', 
            'userBuild.case',
            'userBuild.cpu',
            'userBuild.motherboard',
            'userBuild.gpu',
            'userBuild.ram',
            'userBuild.storage',
            'userBuild.psu',
            'userBuild.cooler',  
            'userBuild.user'
        ])
        ->whereHas('userBuild.user', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->where('pickup_status', 'Pending')
        ->orWhereNotNull('pickup_date')
        ->get()
        ->map(function ($order) {
            return [
                'type' => 'build',
                'id' => 'build_' . $order->id,
                'order_id' => $order->id,
                'checkout_date' => $order->created_at,
                'pickup_date' => $order->pickup_date,
                'updated_at' => $order->updated_at,
                'pickup_status' => $order->pickup_status,
                'total_cost' => $order->userBuild->total_price,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'user' => $order->userBuild->user,
                'build_name' => $order->userBuild->build_name,
                'user_build' => $order->userBuild,
            ];
        });

        // Merge both collections and sort by checkout date
        $allOrders = $componentOrders->merge($buildOrders)
            ->sortByDesc('checkout_date')
            ->values();

        // Manual pagination for the combined collection
        $page = request()->get('page', 1);
        $perPage = 4;
        $paginatedOrders = new \Illuminate\Pagination\LengthAwarePaginator(
            $allOrders->forPage($page, $perPage),
            $allOrders->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('customer.purchasedhistory', compact('paginatedOrders'));
    }

    // optional invoice view if you want it
    public function invoice(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load('items');
        $hardwareMap = config('hardware', []);
        return view('customer.invoice', compact('order', 'hardwareMap'));
    }
}
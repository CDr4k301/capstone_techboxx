<?php

namespace App\Http\Controllers;

use App\Models\Checkout;
use App\Models\ShoppingCart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CheckoutDetailsController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $allCheckouts = Checkout::with([
            'cartItem.shoppingCart.user',
            'cartItem.case',
            'cartItem.cpu', 
            'cartItem.gpu',
            'cartItem.motherboard',
            'cartItem.ram',
            'cartItem.storage',
            'cartItem.psu',
            'cartItem.cooler'
        ])
        ->whereHas('cartItem.shoppingCart', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->whereNull('pickup_date')
        ->orderBy('checkout_date', 'desc')
        ->get();

        // Group by ShoppingCart ID + Checkout Timestamp
        $grouped = $allCheckouts->groupBy(function ($checkout) {
            return $checkout->cartItem->shopping_cart_id . '|' . $checkout->checkout_date->format('Y-m-d H:i:s');
        });

        // Transform each group
        $groupedCheckouts = $grouped->map(function ($checkouts) {
            $first = $checkouts->first();
            $cartItems = $checkouts->map->cartItem;

            return [
                'shopping_cart_id' => $first->cartItem->shopping_cart_id,
                'checkout_date' => $first->checkout_date,
                'updated_at' => $first->updated_at,
                'pickup_status' => $first->pickup_status,
                'total_cost' => $checkouts->sum('total_cost'),
                'cart_items' => $cartItems->values(),
                'payment_method' => $first->payment_method,
                'user' => $first->cartItem->shoppingCart->user,
                'checkout_ids' => $checkouts->pluck('id')->toArray(),
            ];
        })->values();

        // Manual pagination
        $page = request()->get('page', 1);
        $perPage = 1;
        $paginatedGroups = new \Illuminate\Pagination\LengthAwarePaginator(
            $groupedCheckouts->forPage($page, $perPage),
            $groupedCheckouts->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('customer.checkoutdetails', compact('paginatedGroups'));
    }

    public function cancelMultiple(Request $request)
    {
        try {
            DB::beginTransaction();

            $checkoutIds = $request->input('checkout_ids', []);
            
            if (empty($checkoutIds)) {
                return response()->json([
                    'message' => 'No checkout items found to cancel',
                    'type' => 'error',
                ], 400);
            }

            $checkouts = Checkout::whereIn('id', $checkoutIds)->get();
            
            foreach ($checkouts as $checkout) {
                // Update payment status to 'Cancelled'
                $checkout->update([
                    'payment_status' => 'Cancelled',
                ]);

                $checkout->delete();
                
                // Restore stock
                $this->restoreStock($checkout);
            }

            DB::commit();

            return response()->json([
                'message' => 'Order cancelled successfully',
                'type' => 'success',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to cancel order: ' . $e->getMessage(),
                'type' => 'error',
            ], 500);
        }
    }

    private function restoreStock(Checkout $checkout)
    {
        // Restore stock for the cancelled item
        $cartItem = $checkout->cartItem;
        if ($cartItem && $cartItem->product) {
            $modelMap = config('components', []);
            $model = $modelMap[$cartItem->product_type] ?? null;
            
            if ($model) {
                $product = $model::find($cartItem->product_id);
                if ($product) {
                    $product->increment('stock', $cartItem->quantity);
                }
            }
        }
    }

}
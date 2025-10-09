<?php

namespace App\Http\Controllers;

use App\Models\Checkout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function process(Request $request)
    {
        $user = Auth::user();
        $selectedItemsRaw = $request->input('selected_items', '[]');
        $selectedIds = json_decode($selectedItemsRaw, true) ?: [];
        $paymentMethod = $request->input('payment_method', 'Cash on Pickup');

        if (strtolower($paymentMethod) === 'cash on pickup') {
            $paymentMethod = 'Cash';
        }

        if ($user) {
            $shoppingCart = $user->shoppingCart;
            if (!$shoppingCart) {
                return redirect()->back()->with('error', 'Your cart is empty!');
            }

            $cartItems = $shoppingCart->cartItem()->whereIn('id', $selectedIds)->get();
            if ($cartItems->isEmpty()) {
                return redirect()->route('cart.index')->with('error', 'No items selected.');
            }

            $modelMap = config('components', []);
            
            // Validate stock availability first
            $stockErrors = [];
            foreach ($cartItems as $ci) {
                $model = $modelMap[$ci->product_type] ?? null;
                $product = $model ? $model::find($ci->product_id) : null;
                
                if ($product) {
                    $ci->setRelation('product', $product);
                    
                    // Check if sufficient stock is available
                    if ($product->stock < $ci->quantity) {
                        $stockErrors[] = "Insufficient stock for {$product->brand} {$product->model}. Available: {$product->stock}, Requested: {$ci->quantity}";
                    }
                }
            }
            
            // If any stock errors, return with errors
            if (!empty($stockErrors)) {
                return redirect()->route('cart.index')->with('error', implode('<br>', $stockErrors));
            }

            $grandTotal = $cartItems->sum(fn($i) => ($i->product->price ?? 0) * ($i->quantity ?? 0));

            // Use database transaction to ensure data consistency
            DB::beginTransaction();
            
            try {
                // Iterate through selected cart items and create Checkout records
                foreach ($cartItems as $ci) {
                    $product = $ci->product;
                    $name = $product->brand ?? ($product->name ?? ($product->model ?? 'Product'));

                    // Decrement the stock
                    $model = $modelMap[$ci->product_type] ?? null;
                    if ($model) {
                        $affected = $model::where('id', $ci->product_id)
                                         ->where('stock', '>=', $ci->quantity)
                                         ->decrement('stock', $ci->quantity);
                        
                        if (!$affected) {
                            throw new \Exception("Failed to update stock for {$name}. Possibly insufficient stock.");
                        }
                    }

                    // Prepare the data before creating the Checkout record
                    $data = [
                        'cart_item_id' => $ci->id,
                        'checkout_date' => now()->toDateTimeString(),
                        'total_cost' => $ci->total_price,
                        'payment_method' => $paymentMethod,
                        'payment_status' => $paymentMethod === 'Cash' ? 'Pending' : 'Paid',
                    ];

                    // Create the Checkout record for each cart item
                    $checkout = Checkout::create($data);

                    $ci->update(['processed' => true]);
                }

                DB::commit();

                // Redirect to PayPal payment page if PayPal is selected
                if ($paymentMethod === 'PayPal') {
                    return redirect()->route('paypal.create', [
                        'checkout_id' => $checkout->id,
                        'amount' => $grandTotal,
                    ]);
                }

                // If payment is Cash on Pickup, no need to delete the cart items
                // Just show the success message
                return redirect()->route('cart.index')->with('success', 'Order placed successfully! Stock has been updated.');

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Checkout process failed: ' . $e->getMessage());
                return redirect()->route('cart.index')->with('error', 'Checkout failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('cart.index')->with('success', 'Order placed successfully!');
    }
}
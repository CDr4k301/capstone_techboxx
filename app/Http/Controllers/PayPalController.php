<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Checkout;
use App\Models\OrderedBuild;
use App\Models\UserBuild;
use Illuminate\Support\Facades\DB;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;

class PayPalController extends Controller
{
    private $client;

    public function __construct()
    {
        $clientId = env('PAYPAL_CLIENT_ID') ?: env('CLIENT_ID');
        $clientSecret = env('PAYPAL_SECRET') ?: env('CLIENT_SECRET');

        $environment = new SandboxEnvironment($clientId, $clientSecret);
        $this->client = new PayPalHttpClient($environment);
    }

    public function create(Request $request)
    {
        $amount = $request->input('amount');
        $checkoutIdsRaw = $request->input('checkout_ids'); // For cart items
        $orderedBuildId = $request->input('ordered_build_id'); // For ordered builds
        $selected = $request->input('selected'); // Comma-separated cart item IDs

        $paypalRequest = new OrdersCreateRequest();
        $paypalRequest->prefer('return=representation');
        $paypalRequest->body = [
            "intent" => "CAPTURE",
            "purchase_units" => [[
                "amount" => [
                    "currency_code" => "PHP",
                    "value" => (string) $amount
                ]
            ]],
            "application_context" => [
                "return_url" => route('paypal.success', [
                    'checkout_ids' => $checkoutIdsRaw,
                    'ordered_build_id' => $orderedBuildId,
                    'selected' => $selected
                ]),
                "cancel_url" => route('paypal.cancel', [
                    'checkout_ids' => $checkoutIdsRaw,
                    'ordered_build_id' => $orderedBuildId,
                    'selected' => $selected
                ]),
            ]
        ];

        try {
            $response = $this->client->execute($paypalRequest);
        } catch (\Exception $e) {
            Log::error('PayPal create error: ' . $e->getMessage());
            return redirect()->route('cart.index')->with('error', 'Unable to start PayPal payment.');
        }

        foreach ($response->result->links as $link) {
            if ($link->rel === 'approve') {
                return redirect($link->href);
            }
        }

        return redirect()->route('cart.index')->with('error', 'Unable to start PayPal payment.');
    }

    public function success(Request $request)
    {
        $paypalOrderId = $request->query('token');
        $checkoutIdsRaw = $request->query('checkout_ids', '');
        $orderedBuildId = $request->query('ordered_build_id');
        $selectedRaw = $request->query('selected', '');

        $checkoutIds = array_filter(array_map('intval', explode(',', $checkoutIdsRaw)));
        $selectedIds = array_filter(array_map('intval', explode(',', $selectedRaw)));

        try {
            $captureRequest = new OrdersCaptureRequest($paypalOrderId);
            $captureResponse = $this->client->execute($captureRequest);
        } catch (\Exception $e) {
            Log::error('PayPal capture error: ' . $e->getMessage());
            return redirect()->route('cart.index')->with('error', 'Payment capture failed.');
        }

        if (isset($captureResponse->result->status) && $captureResponse->result->status === "COMPLETED") {
            // Update checkout payment statuses (for cart items)
            if (!empty($checkoutIds)) {
                Checkout::whereIn('id', $checkoutIds)->update(['payment_status' => 'Paid']);
            }

            // Update ordered build payment status
            if ($orderedBuildId) {
                OrderedBuild::where('id', $orderedBuildId)->update(['payment_status' => 'Paid']);
            }

            return redirect()->route('cart.index')->with('success', 'Payment successful! Order placed.');
        }

        return redirect()->route('cart.index')->with('error', 'Payment not completed.');
    }

    public function cancel(Request $request)
    {
        $checkoutIdsRaw = $request->query('checkout_ids', '');
        $orderedBuildId = $request->query('ordered_build_id');
        $checkoutIds = array_filter(array_map('intval', explode(',', $checkoutIdsRaw)));
        
        // Handle cart item cancellations
        if (!empty($checkoutIds)) {
            $checkouts = Checkout::whereIn('id', $checkoutIds)->get();
            
            foreach ($checkouts as $checkout) {
                $cartItem = $checkout->cartItem;
                
                if ($cartItem) {
                    $modelMap = config('components', []);
                    $productType = $cartItem->product_type;
                    $model = $modelMap[$productType] ?? null;
                    
                    if ($model) {
                        $model::where('id', $cartItem->product_id)
                            ->increment('stock', $cartItem->quantity);
                        
                        Log::info("Stock restored for {$productType} ID {$cartItem->product_id}: +{$cartItem->quantity} units");
                    }
                }
            }
            
            Checkout::whereIn('id', $checkoutIds)->update(['payment_status' => 'Cancelled']);
            Checkout::whereIn('id', $checkoutIds)->delete();
        }
        
        // Handle ordered build cancellation
        if ($orderedBuildId) {
            $orderedBuild = OrderedBuild::find($orderedBuildId);
            
            if ($orderedBuild) {
                // Restore stock for all components in the build
                $this->restoreBuildStock($orderedBuild);
                
                // Update and soft delete the ordered build
                $orderedBuild->update([
                    'payment_status' => 'Cancelled',
                    'cancelled_at' => now()
                ]);
                $orderedBuild->delete();
                
                Log::info("Ordered build ID {$orderedBuildId} cancelled and stock restored");
            }
        }
        
        return redirect()->route('cart.index')->with('success', 'Payment was cancelled.');
    }

    private function restoreBuildStock(OrderedBuild $orderedBuild)
    {
        $userBuild = $orderedBuild->userBuild;
        
        if (!$userBuild) return;
        
        $components = [
            'pc_case_id' => 'pc_cases',
            'cooler_id' => 'coolers',
            'cpu_id' => 'cpus',
            'gpu_id' => 'gpus',
            'motherboard_id' => 'motherboards',
            'psu_id' => 'psus',
            'ram_id' => 'rams',
            'storage_id' => 'storages',
        ];
        
        foreach ($components as $componentField => $tableName) {
            if ($userBuild->$componentField) {
                DB::table($tableName)
                    ->where('id', $userBuild->$componentField)
                    ->increment('stock', 1); // Each build uses 1 of each component
                
                Log::info("Stock restored for {$tableName} ID {$userBuild->$componentField}: +1 unit");
            }
        }
    }
}
<?php

namespace App\Providers;

use App\Http\Controllers\ComponentDetailsController;
use App\Models\Checkout;
use App\Models\OrderedBuild;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // forcefully registering sidebar icons components to render
        Blade::component('components.icons.dashboard', 'x-icons.dashboard');
        Blade::component('components.icons.user', 'x-icons.user');
        Blade::component('components.icons.order', 'x-icons.order');
        Blade::component('components.icons.component', 'x-icons.component');
        Blade::component('components.icons.bargraph', 'x-icons.bargraph');
        Blade::component('components.icons.inventory', 'x-icons.inventory');
        Blade::component('components.icons.software', 'x-icons.software');
        Blade::component('components.icons.logs', 'x-icons.logs');
        Blade::component('components.icons.build', 'x-icons.build');
        Blade::component('components.icons.checkout', 'x-icons.checkout');
        Blade::component('components.icons.purchase', 'x-icons.purchase');
        Blade::component('components.icons.supplier', 'x-icons.supplier');
        Blade::component('components.icons.manage', 'x-icons.manage');

        View::composer('*', function ($view) {
            $cartCount = 0;
            
            if(Auth::check()) {
                // If user is logged in, get from database - only unprocessed items
                $user = Auth::user();
                if ($user->shoppingCart) {
                    $cartCount = $user->shoppingCart->cartItem()
                        ->where('processed', false)
                        ->sum('quantity');
                }
            } elseif(session('cart')) {
                // If guest, get from session
                $cartCount = array_sum(array_column(session('cart'), 'quantity'));
            }
            
            // Share low stock count
            $lowStockCount = 0;
            if(Auth::check()) {
                try {
                    $lowStockThreshold = 10;
                    $components = app(ComponentDetailsController::class)->getAllFormattedComponents();
                    $lowStockCount = $components->filter(function ($component) use ($lowStockThreshold) {
                        return $component->stock <= $lowStockThreshold;
                    })->count();
                } catch (\Exception $e) {
                    // Log error or handle gracefully
                    logger()->error('Low stock count error: ' . $e->getMessage());
                }
            }

            // Order Builds count
            $pendingPickupCount = OrderedBuild::whereNull('pickup_date')
            ->where(function ($query) {
                $query->where('user_id', Auth::id())
                    ->orWhere(function ($q) {
                        $q->whereNull('user_id')
                            ->where('status', 'Pending');
                    });
            })
            ->whereIn('status', ['Pending', 'Approved'])
            ->count();
                
            // Check-out Product count
            $pendingCheckoutCount = Checkout::where(function ($query) {
                $query->whereNull('pickup_status')
                      ->orWhere(function ($q) {
                          $q->where('pickup_status', 'Pending')
                            ->whereNull('pickup_date');
                      });
            })
            ->join('cart_items', 'checkouts.cart_item_id', '=', 'cart_items.id')
            ->distinct('cart_items.shopping_cart_id')
            ->count('cart_items.shopping_cart_id');

            // Combine both counts
            $totalPendingOrders = $pendingPickupCount + $pendingCheckoutCount;
                
            $view->with('cartCount', $cartCount)
                 ->with('lowStockCount', $lowStockCount)
                 ->with('totalPendingOrders', $totalPendingOrders)
                 ->with('pendingPickupCount', $pendingPickupCount)
                 ->with('pendingCheckoutCount', $pendingCheckoutCount);
        });
    }
}

<x-dashboardlayout>
    <div x-data="orderModal()" class="p-6 h-[90%]">
    <div x-data="orderModal()" class="p-6 h-[90%]">
        <h2 class="text-2xl font-semibold mb-6">Checkout Details</h2>

        <div class="bg-white rounded-lg shadow mb-3 h-[80%] flex flex-col">
            <!-- Table Header -->
            <div class="overflow-x-auto border-b border-gray-200">
                <table class="min-w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-700 border-b">Component</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-700 border-b">Category</th>
                            <th class="px-6 py-3 text-center text-sm font-medium text-gray-700 border-b">Qty</th>
                            <th class="px-6 py-3 text-right text-sm font-medium text-gray-700 border-b">Price</th>
                        </tr>
                    </thead>
                </table>
            </div>

            <!-- Scrollable Body -->
            <div class="flex-1 overflow-y-auto overflow-x-auto">
                <table class="min-w-full h-full">
                    <tbody class="align-top">
                        @forelse ($paginatedGroups as $index => $group)
                            @foreach ($group['cart_items'] as $cartItem)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 border-b">
                                        @php
                                            $model = 'Unknown';
                                            switch($cartItem->product_type) {
                                                case 'case': $model = $cartItem->case?->brand . ' ' . $cartItem->case?->model ?? 'N/A'; break;
                                                case 'cpu': $model = $cartItem->cpu?->brand . ' ' . $cartItem->cpu?->model ?? 'N/A'; break;
                                                case 'gpu': $model = $cartItem->gpu?->brand . ' ' . $cartItem->gpu?->model ?? 'N/A'; break;
                                                case 'motherboard': $model = $cartItem->motherboard?->brand . ' ' . $cartItem->motherboard?->model ?? 'N/A'; break;
                                                case 'ram': $model = $cartItem->ram?->brand . ' ' . $cartItem->ram?->model ?? 'N/A'; break;
                                                case 'storage': $model = $cartItem->storage?->brand . ' ' . $cartItem->storage?->model ?? 'N/A'; break;
                                                case 'psu': $model = $cartItem->psu?->brand . ' ' . $cartItem->psu?->model ?? 'N/A'; break;
                                                case 'cooler': $model = $cartItem->cooler?->brand . ' ' . $cartItem->cooler?->model ?? 'N/A'; break;
                                            }
                                        @endphp
                                        {{ $model }}
                                    </td>
                                    <td class="px-6 py-4 border-b">{{ ucfirst($cartItem->product_type) }}</td>
                                    <td class="px-6 py-4 border-b text-center">{{ $cartItem->quantity }}</td>
                                    <td class="px-6 py-4 border-b text-right">₱{{ number_format($cartItem->total_price, 2) }}</td>
                                </tr>
                            @endforeach

{{-- TOTAL EACH ORDER --}}
<tr class="bg-gray-50 h-16">
    <td colspan="3" class="px-6 border-b text-right font-semibold align-middle">
        Total:
    </td>
    <td class="px-6 border-b text-right font-bold text-gray-700 align-middle">
        ₱{{ number_format($group['total_cost'], 2) }}
    </td>
</tr>
        <div class="bg-white rounded-lg shadow mb-3 h-[80%] flex flex-col">
            <!-- Table Header -->
            <div class="overflow-x-auto border-b border-gray-200">
                <table class="min-w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-700 border-b">Component</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-700 border-b">Category</th>
                            <th class="px-6 py-3 text-center text-sm font-medium text-gray-700 border-b">Qty</th>
                            <th class="px-6 py-3 text-right text-sm font-medium text-gray-700 border-b">Price</th>
                        </tr>
                    </thead>
                </table>
            </div>

            <!-- Scrollable Body -->
            <div class="flex-1 overflow-y-auto overflow-x-auto">
                <table class="min-w-full h-full">
                    <tbody class="align-top">
                        @forelse ($paginatedGroups as $index => $group)
                            @foreach ($group['cart_items'] as $cartItem)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 border-b">
                                        @php
                                            $model = 'Unknown';
                                            switch($cartItem->product_type) {
                                                case 'case': $model = $cartItem->case?->brand . ' ' . $cartItem->case?->model ?? 'N/A'; break;
                                                case 'cpu': $model = $cartItem->cpu?->brand . ' ' . $cartItem->cpu?->model ?? 'N/A'; break;
                                                case 'gpu': $model = $cartItem->gpu?->brand . ' ' . $cartItem->gpu?->model ?? 'N/A'; break;
                                                case 'motherboard': $model = $cartItem->motherboard?->brand . ' ' . $cartItem->motherboard?->model ?? 'N/A'; break;
                                                case 'ram': $model = $cartItem->ram?->brand . ' ' . $cartItem->ram?->model ?? 'N/A'; break;
                                                case 'storage': $model = $cartItem->storage?->brand . ' ' . $cartItem->storage?->model ?? 'N/A'; break;
                                                case 'psu': $model = $cartItem->psu?->brand . ' ' . $cartItem->psu?->model ?? 'N/A'; break;
                                                case 'cooler': $model = $cartItem->cooler?->brand . ' ' . $cartItem->cooler?->model ?? 'N/A'; break;
                                            }
                                        @endphp
                                        {{ $model }}
                                    </td>
                                    <td class="px-6 py-4 border-b">{{ ucfirst($cartItem->product_type) }}</td>
                                    <td class="px-6 py-4 border-b text-center">{{ $cartItem->quantity }}</td>
                                    <td class="px-6 py-4 border-b text-right">₱{{ number_format($cartItem->total_price, 2) }}</td>
                                </tr>
                            @endforeach

                            {{-- TOTAL EACH ORDER --}}
                            <tr class="bg-gray-50 h-16">
                                <td colspan="3" class="px-6 border-b text-right font-semibold align-middle">
                                    Total:
                                </td>
                                <td class="px-6 border-b text-right font-bold text-gray-700 align-middle">
                                    ₱{{ number_format($group['total_cost'], 2) }}
                                </td>
                            </tr>

                            {{-- ORDER STATUS BUTTON --}}
                            <tr class="bg-gray-50 h-16">
                                <td colspan="4" class="px-6 border-b text-right align-middle">
                                    <button @click="setSelectedOrder({{ $index }})"
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                                        Order Status
                                    </button>
                                </td>
                            </tr>


                            <tr>
                                <td colspan="4" class="px-6 py-2 bg-gray-100"></td>
                            </tr>
                        @empty
                            <tr class="h-full">
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500 align-middle">
                                    No checkout items found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>


        <!-- Order Status Modal -->
        <div x-show="showModal" x-cloak x-transition
                class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-[99999]">
            <div @click.away="showModal = false" 
                class="bg-white w-full max-w-2xl m-4 p-8 rounded-lg shadow-xl relative overflow-y-scroll">
                <button @click="showModal = false" 
                        class="absolute top-4 right-4 text-gray-500 hover:text-black text-xl">✖</button>

                <h3 class="text-2xl font-bold mb-6 text-center">Order Status</h3>

                <!-- Order Information -->
                <div class="mb-6 space-y-2" x-show="selectedOrder">
                    <div class="flex justify-between">
                        <span class="font-semibold">Order ID:</span>
                        <span x-text="'#' + selectedOrder.shopping_cart_id" class="text-gray-700">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-semibold">Checkout Date:</span>
                        <span x-text="formatDate(selectedOrder.checkout_date)" class="text-gray-700">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-semibold">Customer Name:</span>
                        <span x-text="selectedOrder.user ? selectedOrder.user.first_name + ' ' + selectedOrder.user.last_name : 'Unknown'" class="text-gray-700">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-semibold">Payment Method:</span>
                        <span x-text="selectedOrder.payment_method || 'N/A'" class="text-gray-700">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-semibold">Contact Number:</span>
                        <span x-text="selectedOrder.user?.phone_number || '0917-XXX-XXXX'" class="text-gray-700">-</span>
                    </div>
                </div>

                <!-- Status Timeline -->
                <h4 class="font-bold text-lg mb-4 border-t border-gray-200 pt-3">Status Timeline</h4>
                <div class="space-y-4">
                    <!-- Submitted Status (Always shown) -->
                    <div class="flex items-start space-x-3">
                        <div class="flex flex-col items-center">
                            <div class="w-3 h-3 bg-green-500 rounded-full mt-1"></div>
                            <div class="w-0.5 h-8 bg-gray-300" x-show="selectedOrder.pickup_status === 'Pending'"></div>
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between items-start">
                                <span class="font-semibold text-sm" x-text="formatDate(selectedOrder.checkout_date) + ' - Submitted'"></span>
                            </div>
                            <p class="text-sm text-gray-600 mt-1">Your order has been placed successfully.</p>
                        </div>
                    </div>
                    
                    <!-- To Pick Up Status (Only shown when pickup_status is 'Pending') -->
                    <div class="flex items-start space-x-3" x-show="selectedOrder.pickup_status === 'Pending'">
                        <div class="flex flex-col items-center">
                            <div class="w-3 h-3 bg-green-500 rounded-full mt-1"></div>
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between items-start">
                                <span class="font-semibold text-sm" x-text="formatDate(selectedOrder.updated_at) + ' - To Pick Up'"></span>
                            </div>
                            <p class="text-sm text-gray-600 mt-1">Your order is ready to be picked up from the shop.</p>
                        </div>
                    </div>
                </div>

                <!-- Current Status -->
                <div class="text-center">
                    <p class="font-bold text-lg mt-3">
                        Current Status: 
                        <template x-if="selectedOrder.pickup_status === null">
                            <span class="text-blue-600">Submitted</span>
                        </template>
                        <template x-if="selectedOrder.pickup_status === 'Pending'">
                            <span class="text-green-600">Ready for Pickup</span>
                        </template>
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{ $paginatedGroups->links() }}


    <script>
        function orderModal() {
            return {
                showModal: false,
                selectedOrder: null,
                paginatedGroups: @json($paginatedGroups->items()),
                
                setSelectedOrder(index) {
                    console.log('Setting selected order:', index);
                    console.log('Available orders:', this.paginatedGroups);
                    
                    if (this.paginatedGroups[index]) {
                        this.selectedOrder = this.paginatedGroups[index];
                        this.showModal = true;
                        console.log('Selected order data:', this.selectedOrder);
                    } else {
                        console.error('Order not found at index:', index);
                    }
                },
                
                formatDate(dateString) {
                    if (!dateString) return '-';
                    const date = new Date(dateString);
                    return date.toLocaleDateString('en-US', { 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                },
                
                getComponentModel(item) {
                    if (!item) return 'Unknown';
                    
                    const productType = item.product_type;
                    const productData = item[productType];
                    
                    if (productData && productData.brand && productData.model) {
                        return productData.brand + ' ' + productData.model;
                    }
                    
                    return 'Unknown Component';
                }
            }
        }
    </script>
</x-dashboardlayout>
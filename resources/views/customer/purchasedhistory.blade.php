<x-dashboardlayout>
    <div x-data="orderModal()" class="p-6 h-[85%]">
        <h2 class="text-2xl font-semibold mb-6">Purchase History</h2>

        <div class="overflow-x-auto bg-white rounded-lg shadow mb-3">
            <table class="min-w-full border border-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 border-b text-left text-sm font-medium text-gray-700">Order Type</th>
                        <th class="px-6 py-3 border-b text-left text-sm font-medium text-gray-700">Order Name</th>
                        <th class="px-6 py-3 border-b text-left text-sm font-medium text-gray-700">Order Date</th>
                        <th class="px-6 py-3 border-b text-right text-sm font-medium text-gray-700">Total</th>
                        <th class="px-6 py-3 border-b text-right text-sm font-medium text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($paginatedOrders as $index => $order)
                        <tr class="border-b">
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $order->type === 'component' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $order->type === 'component' ? 'Components' : 'Complete Build' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-medium">{{ $order->build_name }}</td>
                            <td class="px-6 py-4">{{ $order->checkout_date->format('M d, Y') }}</td>
                            <td class="px-6 py-4 text-right">₱{{ number_format($order->total_cost, 2) }}</td>
                            <td class="px-6 py-4 text-right">
                                <button @click="setSelectedOrder({{ $index }})"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                                    View Invoice
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                No purchase history found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- UNIFIED INVOICE MODAL -->
        <div x-show="showModal" x-cloak x-transition
                class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-[99999] p-4">
            <div @click.away="showModal = false" 
                class="bg-white w-full max-w-4xl h-[90vh] rounded-xl shadow-2xl relative flex flex-col">
                
                <!-- Header -->
                <div class="flex-shrink-0 px-6 py-4 border-b border-gray-200 bg-white rounded-t-xl">
                    <div class="flex items-center justify-between">
                        <h3 class="text-2xl font-bold text-gray-800">Order Invoice</h3>
                        <button @click="showModal = false" 
                                class="text-gray-400 hover:text-gray-600 transition-colors duration-200 p-2 rounded-lg hover:bg-gray-100">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Scrollable Content -->
                <div class="flex-1 overflow-y-auto p-6">
                    <!-- Order Information -->
                    <div class="mb-6 space-y-3 bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <div class="flex justify-between items-center">
                            <span class="font-semibold text-gray-700">Order ID:</span>
                            <span x-text="'#' + selectedOrder.order_id" class="text-gray-900 font-mono bg-white px-3 py-1 rounded border text-sm">-</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="font-semibold text-gray-700">Order Type:</span>
                            <span x-text="selectedOrder.type === 'component' ? 'Components Order' : 'Complete Build'" 
                                  class="text-gray-900 capitalize">-</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="font-semibold text-gray-700">Checkout Date:</span>
                            <span x-text="formatDate(selectedOrder.checkout_date)" class="text-gray-900">-</span>
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <h4 class="font-bold text-lg mb-4 border-t border-gray-200 pt-4 text-gray-800">Customer Information</h4>
                    <div class="mb-6 space-y-3 bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <div class="flex justify-between items-center">
                            <span class="font-semibold text-gray-700">Customer Name:</span>
                            <span x-text="selectedOrder.user ? selectedOrder.user.first_name + ' ' + selectedOrder.user.last_name : 'Unknown'" class="text-gray-900">-</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="font-semibold text-gray-700">Contact Number:</span>
                            <span x-text="selectedOrder.user?.phone_number || '0917-XXX-XXXX'" class="text-gray-900">-</span>
                        </div>
                    </div>
                    
                    <!-- Fulfillment Method -->
                    <h4 class="font-bold text-lg mb-4 border-t border-gray-200 pt-4 text-gray-800">Fulfillment Method: In-Store Pickup</h4>
                    <div class="mb-6 space-y-3 bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <div class="flex justify-between items-center">
                            <span class="font-semibold text-gray-700">Pickup Location:</span>
                            <span class="text-gray-900">Madoxx.QWE</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="font-semibold text-gray-700">Address:</span>
                            <span class="text-gray-900">Pardo, Cebu City</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="font-semibold text-gray-700">Picked Up Date:</span>
                            <span x-text="formatDate(selectedOrder.pickup_date)" class="text-gray-900">-</span>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <h4 class="font-bold text-lg mb-4 border-t border-gray-200 pt-4 text-gray-800">Order Summary</h4>
                    <div class="mb-6 bg-white rounded-lg border border-gray-200 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                            Component
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                            Category
                                        </th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                            Unit Price
                                        </th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                            Qty
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                            Subtotal
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <!-- Component Orders -->
                                    <template x-if="selectedOrder.type === 'component'">
                                        <template x-for="(item, index) in selectedOrder.cart_items" :key="index">
                                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900" x-text="getComponentModel(item)"></div>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 capitalize" 
                                                        x-text="item.product_type"></span>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-600">
                                                    ₱<span x-text="Number(item.total_price / item.quantity).toLocaleString('en-PH', {minimumFractionDigits: 2})">0.00</span>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-600">
                                                    <span x-text="item.quantity" class="font-semibold"></span>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-900 font-semibold">
                                                    ₱<span x-text="Number(item.total_price).toLocaleString('en-PH', {minimumFractionDigits: 2})">0.00</span>
                                                </td>
                                            </tr>
                                        </template>
                                    </template>

                                    <!-- Build Orders -->
                                    <template x-if="selectedOrder.type === 'build'">
                                        <template x-for="component in buildComponents" :key="component.key">
                                            <tr class="hover:bg-gray-50 transition-colors duration-150" 
                                                x-show="selectedOrder.user_build && selectedOrder.user_build[component.key]">
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900" 
                                                        x-text="getBuildComponentModel(component.key)"></div>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 capitalize">
                                                        <span x-text="component.name"></span>
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-600">
                                                    ₱<span x-text="Number(getComponentPrice(component.key)).toLocaleString('en-PH', {minimumFractionDigits: 2})">0.00</span>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-600">
                                                    <span class="font-semibold">1</span>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-900 font-semibold">
                                                    ₱<span x-text="Number(getComponentPrice(component.key)).toLocaleString('en-PH', {minimumFractionDigits: 2})">0.00</span>
                                                </td>
                                            </tr>
                                        </template>
                                    </template>
                                </tbody>
                                <tfoot class="bg-gray-50 border-t border-gray-200">
                                    <tr>
                                        <td colspan="4" class="px-4 py-4 text-sm font-semibold text-gray-900 text-right">
                                            Total Amount:
                                        </td>
                                        <td class="px-4 py-4 text-right">
                                            <span class="text-lg font-bold text-gray-900">
                                                ₱<span x-text="Number(selectedOrder.total_cost).toLocaleString('en-PH', {minimumFractionDigits: 2})">0.00</span>
                                            </span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Payment Information -->
                    <h4 class="font-bold text-lg mb-4 border-t border-gray-200 pt-4 text-gray-800">Payment</h4>
                    <div class="mb-6 space-y-3 bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <div class="flex justify-between items-center">
                            <span class="font-semibold text-gray-700">Payment Method:</span>
                            <span x-text="selectedOrder.payment_method || 'N/A'" class="text-gray-900 capitalize">-</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="font-semibold text-gray-700">Payment Status:</span>
                            <span x-text="selectedOrder.payment_status || 'N/A'" class="text-gray-900 capitalize">-</span>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex-shrink-0 px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-xl">
                    <div class="flex justify-end">
                        <button @click="showModal = false" 
                                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{ $paginatedOrders->links() }}

    <script>
        function orderModal() {
            return {
                showModal: false,
                selectedOrder: null,
                paginatedOrders: @json($paginatedOrders->items()),
                
                buildComponents: [
                    { key: 'case', name: 'Case' },
                    { key: 'cpu', name: 'CPU' },
                    { key: 'motherboard', name: 'Motherboard' },
                    { key: 'ram', name: 'RAM' },
                    { key: 'storage', name: 'Storage' },
                    { key: 'gpu', name: 'GPU' },
                    { key: 'psu', name: 'PSU' },
                    { key: 'cooler', name: 'Cooler' }
                ],
                
                setSelectedOrder(index) {
                    if (this.paginatedOrders[index]) {
                        this.selectedOrder = this.paginatedOrders[index];
                        this.showModal = true;
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
                },
                
                getComponentPrice(componentKey) {
                    return this.selectedOrder.user_build?.[componentKey]?.price || 0;
                },
                
                getBuildComponentModel(componentKey) {
                    const component = this.selectedOrder.user_build?.[componentKey];
                    if (!component) return 'N/A';
                    return `${component.brand} ${component.model}`;
                }
            }
        }
    </script>
</x-dashboardlayout>
<x-dashboardlayout>
    <div x-data = "{ showModal:false, selectedOrder:{} }" 
        class="p-6 h-[90%]">
        <h2 class="text-2xl font-semibold mb-6">Order Details</h2>

        <div class="overflow-x-auto bg-white rounded-lg shadow mb-3 ">
            <table class="min-w-full border border-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 border-b text-left text-sm font-medium text-gray-700">Build Name</th>
                        <th class="px-6 py-3 border-b text-right text-sm font-medium text-gray-700">Price</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td class="px-6 py-4 border-b">{{ $order->userBuild->build_name }}</td>
                            <td class="px-6 py-4 border-b text-right">₱{{ number_format($order->userBuild->total_price, 2) }}</td>
                        </tr>
                        
                        <tr class="bg-gray-50">
                            <td colspan="2" class="px-6 py-3 border-b text-right">
                                <div class="font-semibold text-gray-700">
                                    <button @click="showModal = true; selectedOrder = {{ $order->toJson() }}"
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                                        Order Status
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-gray-500">No orders found.</td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

        <!-- Order Status Modal -->
        <div x-show="showModal" x-cloak x-transition
                class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-[99999]">
            <div @click.away="showModal = false" 
                class="bg-white w-full max-w-2xl m-4 p-8 rounded-lg shadow-xl relative overflow-y-scroll h-[80%]">
                <button @click="showModal = false" 
                        class="absolute top-4 right-4 text-gray-500 hover:text-black text-xl">✖</button>

                <h3 class="text-2xl font-bold mb-6 text-center">Order Status</h3>

                    {{-- <pre x-text="JSON.stringify(selectedOrder, null, 2)"></pre> --}}

                <!-- Order Information -->
                <div class="mb-6 space-y-2">
                    <div class="flex justify-between">
                        <span class="font-semibold">Checkout ID:</span>
                        <span x-text="'#' + selectedOrder.user_build.user_id" class="text-gray-700">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-semibold">Checkout ID:</span>
                        <span x-text="'#' + selectedOrder.user_build.user_id" class="text-gray-700">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-semibold">Checkout Date:</span>
                        <span x-text="new Date(selectedOrder.created_at).toISOString().split('T')[0]" class="text-gray-700">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-semibold">Customer Name:</span>
                        <span x-text="selectedOrder.user_build.user ? selectedOrder.user_build.user.first_name + ' ' + selectedOrder.user_build.user.last_name : 'Unknown'" class="text-gray-700">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-semibold">Payment Method:</span>
                        <span x-text="selectedOrder.payment_method || 'N/A'" class="text-gray-700">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-semibold">Contact Number:</span>
                        <span x-text="selectedOrder.user_build.user.phone_number || '0917-XXX-XXXX'" class="text-gray-700">-</span>
                    </div>
                </div>

                {{-- COMPONENTS --}}
                <h4 class="font-bold text-lg mb-4 border-t border-gray-200 pt-3">Components</h4>
                <div class="mb-6 space-y-2">
                    <div class="flex justify-between">
                        <span class="font-semibold">Case:</span>
                        <span x-text="selectedOrder.user_build.case.brand + ' ' + selectedOrder.user_build.case.model" class="text-gray-700">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-semibold">CPU:</span>
                        <span x-text="selectedOrder.user_build.cpu.brand + ' ' + selectedOrder.user_build.cpu.model" class="text-gray-700">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-semibold">RAM:</span>
                        <span x-text="selectedOrder.user_build.ram.brand + ' ' + selectedOrder.user_build.ram.model" class="text-gray-700">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-semibold">Storage:</span>
                        <span x-text="selectedOrder.user_build.storage.brand + ' ' + selectedOrder.user_build.storage.model" class="text-gray-700">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-semibold">Motherboard:</span>
                        <span x-text="selectedOrder.user_build.motherboard.brand + ' ' + selectedOrder.user_build.motherboard.model" class="text-gray-700">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-semibold">GPU:</span>
                        <span x-text="selectedOrder.user_build.gpu.brand + ' ' + selectedOrder.user_build.gpu.model" class="text-gray-700">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-semibold">PSU:</span>
                        <span x-text="selectedOrder.user_build.psu.brand + ' ' + selectedOrder.user_build.psu.model" class="text-gray-700">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-semibold">Cooler:</span>
                        <span x-text="selectedOrder.user_build.cooler.brand + ' ' + selectedOrder.user_build.case.model" class="text-gray-700">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-semibold">Price:</span>
                        <span x-text="'₱' + Number(selectedOrder.user_build.total_price).toLocaleString('en-PH', {minimumFractionDigits: 2})" class="text-gray-700">-</span>
                    </div>
                    
                </div>

                <!-- Status Timeline -->
                <h4 class="font-bold text-lg mb-4 border-t border-gray-200 pt-3">Status Timeline</h4>
                <div class="space-y-4">
                    <!-- Submitted Status (Always shown) -->
                    <div class="flex items-start space-x-3">
                        <div class="flex flex-col items-center">
                            <div class="w-3 h-3 bg-green-500 rounded-full mt-1"></div>
                            <div class="w-0.5 h-8 bg-gray-300" 
                                x-show="selectedOrder.status === 'Approved' || (selectedOrder.status === 'Approved' && selectedOrder.pickup_status === 'Pending')"></div>
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between items-start">
                                <span class="font-semibold text-sm" x-text="formatDate(selectedOrder.created_at) + ' - Submitted'"></span>
                            </div>
                            <p class="text-sm text-gray-600 mt-1">Your order has been placed successfully.</p>
                        </div>
                    </div>

                    <!-- Approved Status -->
                    <div class="flex items-start space-x-3" x-show="selectedOrder.status === 'Approved'">
                        <div class="flex flex-col items-center">
                            <div class="w-3 h-3 bg-green-500 rounded-full mt-1"></div>
                            <div class="w-0.5 h-8 bg-gray-300" 
                                x-show="selectedOrder.pickup_status === 'Pending'"></div>
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between items-start">
                                <span class="font-semibold text-sm" x-text="formatDate(selectedOrder.updated_at) + ' - Approved'"></span>
                            </div>
                            <p class="text-sm text-gray-600 mt-1">Order approved and queued for assembling.</p>
                        </div>
                    </div>
                    
                    <!-- Ready for Pickup Status -->
                    <div class="flex items-start space-x-3" x-show="selectedOrder.status === 'Approved' && selectedOrder.pickup_status === 'Pending'">
                        <div class="flex flex-col items-center">
                            <div class="w-3 h-3 bg-green-500 rounded-full mt-1"></div>
                            <!-- No connecting line for the last item -->
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between items-start">
                                <span class="font-semibold text-sm" x-text="formatDate(selectedOrder.updated_at) + ' - Ready for Pickup'"></span>
                            </div>
                            <p class="text-sm text-gray-600 mt-1">Your order is ready to be picked up from the shop.</p>
                        </div>
                    </div>
                </div>

                <!-- Current Status -->
                <div class="text-center">
                    <p class="font-bold text-lg mt-3">
                        Current Status: 
                        <template x-if="selectedOrder.status === 'Pending'">
                            <span class="text-blue-600">Submitted</span>
                        </template>
                        <template x-if="selectedOrder.status === 'Approved' && selectedOrder.pickup_status !== 'Pending'">
                            <span class="text-yellow-600">Approved</span>
                        </template>
                        <template x-if="selectedOrder.status === 'Approved' && selectedOrder.pickup_status === 'Pending'">
                            <span class="text-green-600">Ready for Pickup</span>
                        </template>
                    </p>
                </div>
            </div>
        </div>
    </div>
    {{ $orders->links() }}

    <script>
        formatDate(dateString, format = 'default') {
            if (!dateString) return 'Date unavailable';
            
            try {
                const date = new Date(dateString);
                if (isNaN(date.getTime())) return 'Invalid date';
                
                if (format === 'Y-m-d') {
                    return date.toISOString().split('T')[0]; // Returns "2024-01-15"
                } else {
                    return date.toLocaleDateString('en-US', { 
                        month: '2-digit', 
                        day: '2-digit', 
                        year: '2-digit',
                        hour: '2-digit',
                        minute: '2-digit'
                    }).replace(',', '');
                }
            } catch (error) {
                return 'Date error';
            }
        }
    </script>
</x-dashboardlayout>
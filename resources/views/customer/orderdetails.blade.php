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
                class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm flex items-center justify-center z-[99999] p-4">
            <div @click.away="showModal = false" 
                class="bg-white w-full max-w-4xl rounded-2xl shadow-2xl relative max-h-[90vh] overflow-hidden flex flex-col">
                
                <!-- Header -->
                <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-2xl font-bold text-white">Order Status</h3>
                        <button @click="showModal = false" 
                                class="text-white hover:bg-white hover:bg-opacity-20 p-2 rounded-full transition-all duration-200 transform hover:scale-110">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Content -->
                <div class="flex-1 overflow-y-auto p-6">
                    <!-- Order Information -->
                    <div class="bg-gray-50 rounded-xl p-6 mb-6 border border-gray-200">
                        <h4 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b border-gray-300">Order Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="font-semibold text-gray-700">Checkout ID:</span>
                                <span x-text="'#' + selectedOrder.user_build.user_id" class="text-blue-600 font-mono font-bold">-</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="font-semibold text-gray-700">Checkout Date:</span>
                                <span x-text="new Date(selectedOrder.created_at).toISOString().split('T')[0]" class="text-gray-800 font-medium">-</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="font-semibold text-gray-700">Customer Name:</span>
                                <span x-text="selectedOrder.user_build.user ? selectedOrder.user_build.user.first_name + ' ' + selectedOrder.user_build.user.last_name : 'Unknown'" class="text-gray-800 font-medium">-</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="font-semibold text-gray-700">Payment Method:</span>
                                <span x-text="selectedOrder.payment_method || 'N/A'" class="text-gray-800 font-medium">-</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="font-semibold text-gray-700">Contact Number:</span>
                                <span x-text="selectedOrder.user_build.user.phone_number || '0917-XXX-XXXX'" class="text-gray-800 font-medium">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- COMPONENTS -->
                    <div class="bg-white rounded-xl p-6 mb-6 border border-gray-200 shadow-sm">
                        <h4 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b border-gray-300">Components</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex justify-between items-center py-3 px-4 bg-blue-50 rounded-lg">
                                <span class="font-semibold text-gray-700">Case:</span>
                                <span x-text="selectedOrder.user_build.case.brand + ' ' + selectedOrder.user_build.case.model" class="text-gray-800 text-sm font-medium text-right">-</span>
                            </div>
                            <div class="flex justify-between items-center py-3 px-4 bg-blue-50 rounded-lg">
                                <span class="font-semibold text-gray-700">CPU:</span>
                                <span x-text="selectedOrder.user_build.cpu.brand + ' ' + selectedOrder.user_build.cpu.model" class="text-gray-800 text-sm font-medium text-right">-</span>
                            </div>
                            <div class="flex justify-between items-center py-3 px-4 bg-blue-50 rounded-lg">
                                <span class="font-semibold text-gray-700">RAM:</span>
                                <span x-text="selectedOrder.user_build.ram.brand + ' ' + selectedOrder.user_build.ram.model" class="text-gray-800 text-sm font-medium text-right">-</span>
                            </div>
                            <div class="flex justify-between items-center py-3 px-4 bg-blue-50 rounded-lg">
                                <span class="font-semibold text-gray-700">Storage:</span>
                                <span x-text="selectedOrder.user_build.storage.brand + ' ' + selectedOrder.user_build.storage.model" class="text-gray-800 text-sm font-medium text-right">-</span>
                            </div>
                            <div class="flex justify-between items-center py-3 px-4 bg-blue-50 rounded-lg">
                                <span class="font-semibold text-gray-700">Motherboard:</span>
                                <span x-text="selectedOrder.user_build.motherboard.brand + ' ' + selectedOrder.user_build.motherboard.model" class="text-gray-800 text-sm font-medium text-right">-</span>
                            </div>
                            <div class="flex justify-between items-center py-3 px-4 bg-blue-50 rounded-lg">
                                <span class="font-semibold text-gray-700">GPU:</span>
                                <span x-text="selectedOrder.user_build.gpu.brand + ' ' + selectedOrder.user_build.gpu.model" class="text-gray-800 text-sm font-medium text-right">-</span>
                            </div>
                            <div class="flex justify-between items-center py-3 px-4 bg-blue-50 rounded-lg">
                                <span class="font-semibold text-gray-700">PSU:</span>
                                <span x-text="selectedOrder.user_build.psu.brand + ' ' + selectedOrder.user_build.psu.model" class="text-gray-800 text-sm font-medium text-right">-</span>
                            </div>
                            <div class="flex justify-between items-center py-3 px-4 bg-blue-50 rounded-lg">
                                <span class="font-semibold text-gray-700">Cooler:</span>
                                <span x-text="selectedOrder.user_build.cooler.brand + ' ' + selectedOrder.user_build.case.model" class="text-gray-800 text-sm font-medium text-right">-</span>
                            </div>
                        </div>
                        
                        <!-- Total Price -->
                        <div class="mt-6 pt-4 border-t border-gray-200">
                            <div class="flex justify-between items-center py-3 px-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg border border-green-200">
                                <span class="font-bold text-gray-800 text-lg">Total Price:</span>
                                <span x-text="'₱' + Number(selectedOrder.user_build.total_price).toLocaleString('en-PH', {minimumFractionDigits: 2})" class="text-green-600 font-bold text-xl">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- Status Timeline -->
                    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                        <h4 class="text-lg font-bold text-gray-800 mb-6 pb-2 border-b border-gray-300">Order Progress</h4>
                        <div class="space-y-6">
                            <!-- Submitted Status (Always shown) -->
                            <div class="flex items-start space-x-4">
                                <div class="flex flex-col items-center">
                                    <div class="w-4 h-4 bg-green-500 rounded-full mt-1 ring-4 ring-green-100"></div>
                                    <div class="w-0.5 h-12 bg-green-300" 
                                        x-show="selectedOrder.status === 'Approved' || (selectedOrder.status === 'Approved' && selectedOrder.pickup_status === 'Pending')"></div>
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between items-start">
                                        <span class="font-bold text-gray-800" x-text="formatDate(selectedOrder.created_at) + ' - Submitted'"></span>
                                        <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded-full">Completed</span>
                                    </div>
                                    <p class="text-gray-600 mt-2">Your order has been placed successfully and is awaiting approval.</p>
                                </div>
                            </div>

                            <!-- Approved Status -->
                            <div class="flex items-start space-x-4" x-show="selectedOrder.status === 'Approved'">
                                <div class="flex flex-col items-center">
                                    <div class="w-4 h-4 bg-green-500 rounded-full mt-1 ring-4 ring-green-100"></div>
                                    <div class="w-0.5 h-12 bg-green-300" 
                                        x-show="selectedOrder.pickup_status === 'Pending'"></div>
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between items-start">
                                        <span class="font-bold text-gray-800" x-text="formatDate(selectedOrder.updated_at) + ' - Approved'"></span>
                                        <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded-full">Completed</span>
                                    </div>
                                    <p class="text-gray-600 mt-2">Order has been approved and is currently being assembled by our technicians.</p>
                                </div>
                            </div>
                            
                            <!-- Ready for Pickup Status -->
                            <div class="flex items-start space-x-4" x-show="selectedOrder.status === 'Approved' && selectedOrder.pickup_status === 'Pending'">
                                <div class="flex flex-col items-center">
                                    <div class="w-4 h-4 bg-green-500 rounded-full mt-1 ring-4 ring-green-100"></div>
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between items-start">
                                        <span class="font-bold text-gray-800" x-text="formatDate(selectedOrder.updated_at) + ' - Ready for Pickup'"></span>
                                        <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded-full">Completed</span>
                                    </div>
                                    <p class="text-gray-600 mt-2">Your PC build is complete and ready to be picked up from our shop location.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Current Status Badge -->
                        <div class="mt-8 pt-6 border-t border-gray-200 text-center">
                            <p class="text-sm font-semibold text-gray-600 mb-2">CURRENT STATUS</p>
                            <template x-if="selectedOrder.status === 'Pending'">
                                <span class="inline-flex items-center px-6 py-3 bg-blue-100 text-blue-800 rounded-full text-lg font-bold">
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                    </svg>
                                    Submitted
                                </span>
                            </template>
                            <template x-if="selectedOrder.status === 'Approved' && selectedOrder.pickup_status !== 'Pending'">
                                <span class="inline-flex items-center px-6 py-3 bg-yellow-100 text-yellow-800 rounded-full text-lg font-bold">
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Approved
                                </span>
                            </template>
                            <template x-if="selectedOrder.status === 'Approved' && selectedOrder.pickup_status === 'Pending'">
                                <span class="inline-flex items-center px-6 py-3 bg-green-100 text-green-800 rounded-full text-lg font-bold">
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Ready for Pickup
                                </span>
                            </template>
                        </div>
                    </div>
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
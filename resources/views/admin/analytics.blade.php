        <x-dashboardlayout>
            <div class=" p-6">
                <!-- Page Header -->
            <h2 class="text-2xl font-semibold">Analytics</h2>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-8 mt-8">
                <!-- Left Container: Order Reports + Product Orders -->
                <div class="bg-white p-4 rounded-lg shadow lg:col-span-2 flex flex-col">
                    <h3 class="font-semibold mb-4">Order Reports</h3>
                    <div class="flex flex-col lg:flex-row gap-4 flex-1">
                        <!-- Pie Chart -->
                        <div class="flex-1 flex justify-center items-center">
                            <canvas id="orderPieChart" height="180"></canvas>
                        </div>

                        <!-- Product Orders Table -->
                        <div class="flex-1">
                            <table id="productOrdersTable" class="w-full text-sm text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-gray-300">
                                        <th class="py-2 text-center">Color</th>
                                        <th class="py-2">Product</th>
                                        <th class="py-2 text-center">Orders</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($ordersByType as $row)
                                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                                            <td class="py-2 text-center">
                                                <span class="inline-block w-4 h-4 rounded-full" style="background-color: #ccc;"></span>
                                            </td>
                                            <td class="py-2">{{ $row['product_type'] }}</td>
                                            <td class="py-2 text-center">{{ $row['total_orders'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Right Container: Cart Analysis -->
                <div class="bg-white p-4 rounded-lg shadow lg:col-span-1 flex flex-col">
                    <h3 class="font-semibold mb-3">Cart Analysis</h3>
                    <div class="flex-1 overflow-x-auto">
                        <table class="w-full text-sm text-left border-collapse table-fixed">
                            <!-- Define column widths -->
                            <colgroup>
                                <col class="w-[50%]">   <!-- Product Name -->
                                <col class="w-[25%]">   <!-- Add to Cart -->
                                <col class="w-[25%]">   <!-- Orders -->
                            </colgroup>

                            <thead>
                                <tr class="border-b border-gray-300">
                                    <th class="py-2 text-xs">Product Name</th>
                                    <th class="py-2 text-xs text-center">Add to Cart</th>
                                    <th class="py-2 text-xs text-center">Orders</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cartAnalysis as $cart)
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="py-2 text-xs truncate" title="{{ $cart['name'] }}">{{ $cart['name'] }}</td>
                                        <td class="py-2 text-xs text-center">{{ $cart['add_to_cart'] }}</td>
                                        <td class="py-2 text-xs text-center">{{ $cart['ordered_quantity'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-2 flex justify-between items-center">
                        @php
                            $start = ($cartAnalysis->currentPage() - 1) * $cartAnalysis->perPage() + 1;
                            $end = min($cartAnalysis->currentPage() * $cartAnalysis->perPage(), $cartAnalysis->total());
                        @endphp

                        <span class="text-gray-600 text-sm">
                            Showing {{ $start }} to {{ $end }} of {{ $cartAnalysis->total() }} results
                        </span>

                        <div class="flex items-center space-x-2">
                            {{-- Previous Page --}}
                            @if ($cartAnalysis->onFirstPage())
                                <span class="px-3 py-1 border rounded text-gray-400 cursor-not-allowed">&lt;</span>
                            @else
                                <a href="{{ $cartAnalysis->previousPageUrl() }}" class="px-3 py-1 border rounded hover:bg-gray-100">&lt;</a>
                            @endif

                            {{-- Current Page --}}
                            <span class="px-3 py-1 border rounded bg-gray-200">{{ $cartAnalysis->currentPage() }}</span>

                            {{-- Next Page --}}
                            @if ($cartAnalysis->hasMorePages())
                                <a href="{{ $cartAnalysis->nextPageUrl() }}" class="px-3 py-1 border rounded hover:bg-gray-100">&gt;</a>
                            @else
                                <span class="px-3 py-1 border rounded text-gray-400 cursor-not-allowed">&gt;</span>
                            @endif
                        </div>
                    </div>
                </div>

            </div>



                <!-- Frequent Product Bought Together -->
                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-white p-4 rounded-lg shadow col-span-2">
                        <h3 class="font-semibold mb-3">Frequent Product Bought Together</h3>
                        <table class="w-full text-sm text-left border-collapse">
                            <thead>
                                <tr class="border-b border-gray-300">
                                    <th class="py-2">Product A</th>
                                    <th class="py-2">Product B</th>
                                    <th class="py-2 text-center">Total Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($frequentPairs as $pair)
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="py-2">{{ $pair->product_a }}</td>
                                        <td class="py-2">{{ $pair->product_b }}</td>
                                        <td class="py-2 text-center">₱{{ number_format($pair->total_price, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

            <script>
                
                const pieCtx = document.getElementById('orderPieChart').getContext('2d');
                const pieLabels = {!! json_encode($ordersByType->pluck('product_type')) !!};
                const pieData = {!! json_encode($ordersByType->pluck('total_orders')) !!};
                const pieColors = ['#60A5FA','#34D399','#FBBF24','#F87171','#A78BFA','#E879F9','#CBD5E1', '#FCA5A5'];
                const total = pieData.reduce((sum, val) => sum + val, 0);

                new Chart(pieCtx, {
                    type: 'doughnut',
                    data: {
                        labels: pieLabels,
                        datasets: [{
                            data: pieData,
                            backgroundColor: pieColors
                        }]
                    },
                    options: {
                        plugins: {
                            legend: { display: false }, // 🔥 Hide the bottom color legend
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const value = context.raw;
                                        const percentage = ((value / total) * 100).toFixed(1);
                                        return `${context.label}: ${value} total_orders (${percentage}%)`;
                                    }
                                }
                            },
                            datalabels: {
                                color: '#fff',
                                font: { weight: 'bold', size: 13 },
                                formatter: (value) => ((value / total) * 100).toFixed(1) + '%'
                            }
                        }
                    },
                    plugins: [ChartDataLabels]
                });

                // ----------------------------
                // COLOR INDICATORS IN PRODUCT ORDERS TABLE
                // ----------------------------
                const colorDots = document.querySelectorAll('#productOrdersTable tbody tr span');
                colorDots.forEach((dot, index) => {
                    if (pieColors[index]) {
                        dot.style.backgroundColor = pieColors[index];
                    }
                });
            </script>
        </x-dashboardlayout>
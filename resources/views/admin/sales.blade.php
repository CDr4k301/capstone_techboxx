<x-dashboardlayout>
    <div class="p-4">
        <!-- Page Header -->
        <div class="flex items-center gap-3 mb-6">
            <h2 class="text-2xl font-semibold">Sales Report ({{ ucfirst($period) }})</h2>

            <form method="GET" action="{{ route('admin.sales') }}">
                <select name="period"
                        class="border rounded px-3 pr-8 py-1 text-sm appearance-none bg-white
                               bg-[url('data:image/svg+xml;utf8,<svg fill=\'%23000\' height=\'20\' viewBox=\'0 0 20 20\' width=\'20\' xmlns=\'http://www.w3.org/2000/svg\'><path d=\'M5.516 7.548l4.484 4.482 4.484-4.482L16 9.064l-6 6-6-6z\'/></svg>')] bg-no-repeat bg-[right_0.5rem_center] cursor-pointer"
                        onchange="this.form.submit()">
                    <option value="daily" {{ $period == 'daily' ? 'selected' : '' }}>Daily</option>
                    <option value="weekly" {{ $period == 'weekly' ? 'selected' : '' }}>Weekly</option>
                    <option value="monthly" {{ $period == 'monthly' ? 'selected' : '' }}>Monthly</option>
                    <option value="annually" {{ $period == 'annually' ? 'selected' : '' }}>Annually</option>
                </select>
            </form>

            <a href="{{ route('admin.sales.download', request()->query()) }}" 
            class="bg-green-600 hover:bg-green-700 text-white px-4 py-1 rounded text-sm flex items-center gap-2 transition-colors no-print">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Download PDF Report
            </a>
        </div>

        <!-- KPI Summary -->
        <div class="grid grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-5 rounded-lg shadow-md">
                <h3 class="text-gray-500 text-sm mb-1">Total Components Sold</h3>
                <p class="text-2xl font-semibold">{{ number_format($summary['total_sold']) }}</p>
            </div>

            <div class="bg-white p-5 rounded-lg shadow-md">
                <h3 class="text-gray-500 text-sm mb-1">Cost of Goods Sold</h3>
                <p class="text-2xl font-semibold text-blue-600">₱{{ number_format($summary['cost_of_goods'], 2) }}</p>
            </div>

            <div class="bg-white p-5 rounded-lg shadow-md">
                <h3 class="text-gray-500 text-sm mb-1">Revenue</h3>
                <p class="text-2xl font-semibold text-green-600">₱{{ number_format($summary['revenue'], 2) }}</p>
            </div>

            <div class="bg-white p-5 rounded-lg shadow-md">
                <h3 class="text-gray-500 text-sm mb-1">Profit</h3>
                <p class="text-2xl font-semibold text-emerald-600">₱{{ number_format($summary['profit'], 2) }}</p>
            </div>
        </div>

        <!-- Top Selling Products + Sales Overview -->
        <div class="grid grid-cols-12 gap-4">
            <!-- Top Selling Products -->
            <div class="col-span-5 flex flex-col">
                <div class="bg-white p-4 rounded-lg shadow h-full flex flex-col">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="font-semibold text-sm">Top Selling Products</h3>
                        <form method="GET" action="{{ route('admin.sales') }}" class="flex items-center gap-2">
                            <!-- Preserve current period -->
                            <input type="hidden" name="period" value="{{ request('period', 'monthly') }}">

                            <select name="filter_type"
                                class="border rounded px-2 py-1 text-sm bg-white cursor-pointer transition-all duration-150
                                    w-auto min-w-[110px] max-w-[190px]
                                    pr-6 appearance-none
                                    bg-[url('data:image/svg+xml;utf8,<svg fill=\'%23333\' height=\'16\' viewBox=\'0 0 20 20\' width=\'16\' xmlns=\'http://www.w3.org/2000/svg\'><path d=\'M5.5 7.5l4.5 4.5 4.5-4.5z\'/></svg>')]]
                                    bg-no-repeat bg-[right_0.5rem_center]"
                                onchange="this.form.submit()">
                                <option value="">View All</option>
                                <option value="cpu" {{ request('filter_type') == 'cpu' ? 'selected' : '' }}>CPU</option>
                                <option value="gpu" {{ request('filter_type') == 'gpu' ? 'selected' : '' }}>GPU</option>
                                <option value="ram" {{ request('filter_type') == 'ram' ? 'selected' : '' }}>RAM</option>
                                <option value="storage" {{ request('filter_type') == 'storage' ? 'selected' : '' }}>Storage</option>
                                <option value="motherboard" {{ request('filter_type') == 'motherboard' ? 'selected' : '' }}>Motherboard</option>
                                <option value="psu" {{ request('filter_type') == 'psu' ? 'selected' : '' }}>PSU</option>
                                <option value="case" {{ request('filter_type') == 'case' ? 'selected' : '' }}>Case</option>
                                <option value="cooler" {{ request('filter_type') == 'cooler' ? 'selected' : '' }}>Cooler</option>
                            </select>
                        </form>
                    </div>

                    <!-- Table Container -->
                    <div class="flex-1">
                        <table class="w-full text-xs text-left border-collapse table-fixed">
                            <colgroup>
                                <col class="w-[60%]">   <!-- Product column -->
                                <col class="w-[20%]">   <!-- Sold column -->
                                <col class="w-[20%]">   <!-- Earnings column -->
                            </colgroup>

                            <thead class="bg-gray-100 sticky top-0 z-10">
                                <tr class="border-b border-gray-300 text-[11px]">
                                    <th class="py-1 px-2">Product</th>
                                    <th class="py-1 px-2 text-center">Sold</th>
                                    <th class="py-1 px-2 text-center">Earnings</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($groupedSalesWithDetails as $product)
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="py-1 px-2 truncate">{{ $product['product_name'] }}</td>
                                        <td class="py-1 px-2 text-center">{{ $product['total_sold'] }}</td>
                                        <td class="py-1 px-2 text-center whitespace-nowrap">
                                            ₱{{ number_format(($product['selling_price'] * $product['total_sold']) - ($product['base_price'] * $product['total_sold']), 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-6 text-center text-gray-500 text-xs">
                                            No products found for this filter.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-3">
                        @php
                            $perPage = $groupedSalesWithDetails->perPage();
                            $currentPage = $groupedSalesWithDetails->currentPage();
                            $total = $groupedSalesWithDetails->total();
                            $from = ($currentPage - 1) * $perPage + 1;
                            $to = min($currentPage * $perPage, $total);
                        @endphp

                        <div class="mt-3 flex items-center justify-between text-xs text-gray-600">
                            <!-- Showing X to Y of Z -->
                            <div>
                                Showing {{ $from }} to {{ $to }} of {{ $total }} results
                            </div>

                            <!-- Pagination Buttons -->
                            <div class="flex items-center gap-1">
                                <!-- Previous Page -->
                                @if($currentPage > 1)
                                    <a href="{{ $groupedSalesWithDetails->appends(request()->only(['period', 'filter_type']))->previousPageUrl() }}"
                                    class="px-2 py-1 border rounded hover:bg-gray-100">&lt;</a>
                                @else
                                    <span class="px-2 py-1 border rounded text-gray-400 cursor-not-allowed">&lt;</span>
                                @endif

                                <!-- Current Page -->
                                <span class="px-2 py-1 border rounded bg-gray-200">{{ $currentPage }}</span>

                                <!-- Next Page -->
                                @if($currentPage < $groupedSalesWithDetails->lastPage())
                                    <a href="{{ $groupedSalesWithDetails->appends(request()->only(['period', 'filter_type']))->nextPageUrl() }}"
                                    class="px-2 py-1 border rounded hover:bg-gray-100">&gt;</a>
                                @else
                                    <span class="px-2 py-1 border rounded text-gray-400 cursor-not-allowed">&gt;</span>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Sales Overview Chart -->
            <div class="col-span-7 flex flex-col">
                <div class="bg-white p-4 rounded-lg shadow flex-1 flex flex-col">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="font-semibold">Sales Overview</h3>
                        <div class="text-xs text-gray-400">Showing {{ ucfirst($period) }} Data</div>
                    </div>
                    <div class="flex-1">
                        <canvas id="salesOverviewChart" class="w-full h-full"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

    <script>
        // ----------------------------
        // SALES OVERVIEW (LINE CHART)
        // ----------------------------
        const salesCtx = document.getElementById('salesOverviewChart').getContext('2d');
        const labels = {!! json_encode($salesLabels) !!};
        const data = {!! json_encode($salesTotals) !!};
        const xAxisLabel = {!! json_encode($xAxisLabel) !!};

        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total Sales',
                    data: data,
                    borderColor: '#34D399',
                    backgroundColor: 'rgba(52, 211, 153, 0.2)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: '#34D399',
                    pointBorderColor: '#fff',
                    pointHoverRadius: 6,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { 
                        display: false // Hide the legend completely
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '₱' + context.raw.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Total Sales (₱)',
                            color: '#374151',
                            font: {
                                size: 12,
                                weight: 'bold'
                            }
                        },
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    x: {
                        title: {
                            display: true,
                            text: xAxisLabel,
                            color: '#374151',
                            font: {
                                size: 12,
                                weight: 'bold'
                            }
                        },
                        grid: { display: false }
                    }
                }
            }
        });
    </script>
</x-dashboardlayout>

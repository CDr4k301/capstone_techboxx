<x-dashboardlayout>
    <div class="p-6 space-y-6">

        {{-- Top Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-pink-100 p-6 rounded-2xl shadow">
                <h2 class="text-3xl font-bold">{{ $totalOrders }}</h2>
                <p class="text-gray-700">Total Orders</p>
            </div>
            <div class="bg-yellow-100 p-6 rounded-2xl shadow">
                <h2 class="text-3xl font-bold">{{ $pendingOrders }}</h2>
                <p class="text-gray-700">Pending Orders</p>
            </div>
            <div class="bg-green-100 p-6 rounded-2xl shadow">
                <h2 class="text-3xl font-bold">₱{{ number_format($revenue, 2) }}</h2>
                <p class="text-gray-700">Daily Revenue</p>
            </div>
            <div class="bg-blue-500 text-white p-6 rounded-2xl shadow flex items-center gap-3">
                <span class="text-4xl">⚠️</span>
                <div>
                    <h2 class="text-3xl font-bold">{{ $lowStockItems }}</h2>
                    <p>Low Stock Items</p>
                </div>
            </div>
        </div>

        {{-- Chart + Recent Orders --}}
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
            {{-- 📊 Order Volume --}}
            <div class="lg:col-span-3 bg-white rounded-2xl shadow p-6">
                <div class="flex items-start justify-between mb-4">
                    <h2 class="font-semibold text-gray-700">Order Volume</h2>
                </div>

                <div class="relative h-56">
                    <canvas id="orderVolumeChart"></canvas>
                </div>
            </div>

            {{-- Recent Orders --}}
            <div class="lg:col-span-2 bg-white rounded-2xl shadow p-6">
                <h2 class="font mb-4">Recent Orders</h2>
                <div>
                    <table class="w-full text-sm text-gray-600 border-separate border-spacing-y-2">
                        <thead>
                            <tr class="text-gray-500 text-xs uppercase">
                                <th class="py-3 px-4 text-left">Order ID</th>
                                <th class="py-3 px-4 text-left">Customer</th>
                                <th class="py-3 px-4 text-left">Date</th>
                                <th class="py-3 px-4 text-left">Amount</th>
                                <th class="py-3 px-4 text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentOrders->sortBy('id') as $order)
                                <tr>
                                    <td class="py-3 px-4">{{ $order->id }}</td>
                                    <td class="py-3 px-4">{{ $order->customer->name ?? 'N/A' }}</td>
                                    <td class="py-3 px-4">{{ $order->created_at->format('Y-m-d') }}</td>
                                    <td class="py-3 px-4">₱{{ number_format($order->total, 2) }}</td>
                                    <td class="py-3 px-4">
                                        @if($order->status === 'completed')
                                            <span class="text-green-600 font-semibold">Completed</span>
                                        @elseif($order->status === 'pending')
                                            <span class="text-yellow-600 font-semibold">Pending</span>
                                        @elseif($order->status === 'paid')
                                            <span class="text-blue-600 font-semibold">Paid</span>
                                        @else
                                            <span class="text-gray-600 font-semibold">{{ ucfirst($order->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('orderVolumeChart');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: [
                    "{{ $prevMonth->format('F') }}", 
                    "{{ $thisMonth->format('F') }}"
                ],
                datasets: [
                    {
                        label: 'Order Volume',
                        data: [{{ $previousMonthOrders }}, {{ $thisMonthOrders }}],
                        borderColor: '#2563eb',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: false,
                        pointRadius: 6,
                        pointBackgroundColor: '#2563eb'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    }
                }
            }
        });
    </script>
</x-dashboardlayout>
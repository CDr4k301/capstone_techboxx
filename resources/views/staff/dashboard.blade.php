<x-dashboardlayout>
    <div class="p-4">
        <!-- Welcome -->
        <h2 class="text-xl font-semibold mb-4">
            Welcome, {{ Auth::user()->first_name ?? 'Staff' }}
        </h2>

        <!-- Top stats as separate square boxes -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="border rounded-lg p-4 text-center">
                <p class="text-sm text-gray-500">Orders in Progress</p>
                <h3 class="text-2xl font-bold text-indigo-600">{{ $ordersInProgress }}</h3>
            </div>
            <div class="border rounded-lg p-4 text-center">
                <p class="text-sm text-gray-500">Inventory Warnings</p>
                <h3 class="text-2xl font-bold text-red-500">{{ $inventoryWarnings }}</h3>
            </div>
        </div>

        <!-- Tasks + Notifications side by side -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Tasks -->
            <div class="border rounded-lg p-4 flex flex-col">
                <h4 class="font-semibold text-sm mb-2">Tasks:</h4>
                <ul class="space-y-1 text-sm overflow-y-auto max-h-64"> <!-- Added max height and scroll -->
                    @forelse($tasks as $task)
                        <li class="py-1"> <!-- Added padding for better spacing -->
                            Pending Order Approval 
                            <span class="font-mono text-gray-500">#{{ $task->id }}</span>
                        </li>
                    @empty
                        <li class="text-gray-500 py-1">No pending tasks</li>
                    @endforelse
                </ul>
            </div>

            <!-- Notifications -->
            <div class="border rounded-lg p-4">
                <h4 class="font-semibold text-sm mb-2">Notifications:</h4>
                <ul class="space-y-1 text-sm overflow-y-auto max-h-64">
                    @foreach($notifications['stockIns'] as $stockIn)
                        <li class="text-green-600">
                            Stock-in: {{ $stockIn->quantity_changed }} units ({{ $stockIn->component_id }})
                        </li>
                    @endforeach

                    @foreach($notifications['stockOuts'] as $stockOut)
                        <li class="text-red-600">
                            Stock-out: {{ $stockOut->quantity_changed }} units ({{ $stockOut->component_id }})
                        </li>
                    @endforeach

                    @foreach($notifications['reorders'] as $component)
                        <li class="text-red-600 font-semibold">
                            Reorder alert: {{ $component->brand }} {{ $component->model }} (Stock: {{ $component->stock }})
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</x-dashboardlayout>
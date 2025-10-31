<x-dashboardlayout>
    <div class="p-4">
        <h2 class="text-xl font-semibold mb-4">
            Welcome, {{ Auth::user()->first_name ?? 'Staff' }}
        </h2>

        <!-- Top Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="border rounded-lg p-4 text-center">
                <p class="text-sm text-gray-500">Orders in Progress</p>
                <h3 class="text-2xl font-bold text-indigo-600">{{ $totalPendingOrders }}</h3>
            </div>
            <div class="border rounded-lg p-4 text-center">
                <p class="text-sm text-gray-500">Inventory Warnings</p>
                <h3 class="text-2xl font-bold text-red-500">{{ $inventoryWarnings }}</h3>
            </div>
        </div>

        <!-- Tasks + Notifications -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Tasks -->
            <div class="col-span-1 border rounded-lg p-4 flex flex-col bg-white h-[420px]">
                <h4 class="font-semibold text-sm mb-2">Tasks:</h4>

                <!-- Task List (scrollable if needed) -->
                <ul class="space-y-1 text-sm overflow-y-auto flex-1 pr-2">
                    @forelse($tasksPaginated as $task)
                        <li class="py-1">
                            Pending Order Approval 
                            <span class="font-mono text-gray-500">#{{ $task->id }}</span>
                        </li>
                    @empty
                        <li class="text-gray-500 py-1">No pending tasks</li>
                    @endforelse
                </ul>

                <div class="mt-2 border-t bg-gray-50 p-2 text-xs text-gray-600 flex items-center justify-between">
                    @if ($tasksPaginated->count() > 0)
                        <span>
                            Showing {{ $tasksPaginated->firstItem() }}–{{ $tasksPaginated->lastItem() }}
                            of {{ $tasksPaginated->total() }}
                        </span>
                    @else
                        <span>No results</span>
                    @endif

                    <div class="space-x-2">
                        @if ($tasksPaginated->onFirstPage())
                            <span class="text-gray-400">&lt;</span>
                        @else
                            <a href="{{ $tasksPaginated->previousPageUrl() }}" class="text-indigo-600 hover:underline">&lt;</a>
                        @endif

                        <span class="px-2 py-1 border rounded text-gray-700">
                            {{ $tasksPaginated->currentPage() }}
                        </span>

                        @if ($tasksPaginated->hasMorePages())
                            <a href="{{ $tasksPaginated->nextPageUrl() }}" class="text-indigo-600 hover:underline">&gt;</a>
                        @else
                            <span class="text-gray-400">&gt;</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Notifications -->
            <div class="col-span-2 border rounded-lg p-4 flex flex-col bg-white h-[420px]">
                <h4 class="font-semibold text-sm mb-3">Notifications:</h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 flex-1 overflow-hidden">
                    <!-- Stock-in -->
                    <div class="flex flex-col border rounded-lg overflow-hidden bg-white">
                        <h5 class="font-semibold text-green-600 px-4 py-2 border-b">Stock-in</h5>
                        <div class="overflow-y-auto flex-1">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-100 sticky top-0">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-semibold text-gray-600 border-b">Product</th>
                                        <th class="px-4 py-2 text-center font-semibold text-gray-600 border-b">Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($notifications['stockIns'] as $stockIn)
                                        <tr class="border-b hover:bg-gray-50">
                                            <td class="px-4 py-2 text-gray-700 max-w-[180px] truncate" title="{{ $stockIn->component_id }}">
                                                {{ $stockIn->component_id }}
                                            </td>
                                            <td class="px-4 py-2 text-center text-green-600">
                                                +{{ $stockIn->quantity_changed }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="px-4 py-3 text-center text-gray-500">
                                                No stock-ins available
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="border-t bg-gray-50 p-2 text-xs text-gray-600 flex items-center justify-between">
                            @if ($notifications['stockIns']->count() > 0)
                                <span>
                                    Showing {{ $notifications['stockIns']->firstItem() }}–{{ $notifications['stockIns']->lastItem() }}
                                    of {{ $notifications['stockIns']->total() }}
                                </span>
                            @else
                                <span>No results</span>
                            @endif
                            <div class="space-x-2">
                                @if ($notifications['stockIns']->onFirstPage())
                                    <span class="text-gray-400">&lt;</span>
                                @else
                                    <a href="{{ $notifications['stockIns']->previousPageUrl() }}" class="text-indigo-600 hover:underline">&lt;</a>
                                @endif

                                <span class="px-2 py-1 border rounded text-gray-700">
                                    {{ $notifications['stockIns']->currentPage() }}
                                </span>

                                @if ($notifications['stockIns']->hasMorePages())
                                    <a href="{{ $notifications['stockIns']->nextPageUrl() }}" class="text-indigo-600 hover:underline">&gt;</a>
                                @else
                                    <span class="text-gray-400">&gt;</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Stock-out -->
                    <div class="flex flex-col border rounded-lg overflow-hidden bg-white">
                        <h5 class="font-semibold text-red-600 px-4 py-2 border-b">Stock-out</h5>
                        <div class="overflow-y-auto flex-1">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-100 sticky top-0">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-semibold text-gray-600 border-b">Product</th>
                                        <th class="px-4 py-2 text-center font-semibold text-gray-600 border-b">Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($notifications['stockOuts'] as $stockOut)
                                        <tr class="border-b hover:bg-gray-50">
                                            <td class="px-4 py-2 text-gray-700 max-w-[180px] truncate" title="{{ $stockOut->component_id }}">
                                                {{ $stockOut->component_id }}
                                            </td>
                                            <td class="px-4 py-2 text-center text-red-600">
                                                -{{ $stockOut->quantity_changed }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="px-4 py-3 text-center text-gray-500">
                                                No stock-outs available
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="border-t bg-gray-50 p-2 text-xs text-gray-600 flex items-center justify-between">
                            @if ($notifications['stockOuts']->count() > 0)
                                <span>
                                    Showing {{ $notifications['stockOuts']->firstItem() }}–{{ $notifications['stockOuts']->lastItem() }}
                                    of {{ $notifications['stockOuts']->total() }}
                                </span>
                            @else
                                <span>No results</span>
                            @endif
                            <div class="space-x-2">
                                @if ($notifications['stockOuts']->onFirstPage())
                                    <span class="text-gray-400">&lt;</span>
                                @else
                                    <a href="{{ $notifications['stockOuts']->previousPageUrl() }}" class="text-indigo-600 hover:underline">&lt;</a>
                                @endif

                                <span class="px-2 py-1 border rounded text-gray-700">
                                    {{ $notifications['stockOuts']->currentPage() }}
                                </span>

                                @if ($notifications['stockOuts']->hasMorePages())
                                    <a href="{{ $notifications['stockOuts']->nextPageUrl() }}" class="text-indigo-600 hover:underline">&gt;</a>
                                @else
                                    <span class="text-gray-400">&gt;</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-dashboardlayout>

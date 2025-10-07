@php
    $activeTab = request()->query('tab', 'order'); // default to 'order' tab
@endphp

<x-dashboardlayout>
    <h2>Order Processing</h2>

    <div class="header-container">
        <div class="order-tab">
            <button class="{{ $activeTab === 'order' ? 'active' : '' }}" id="orderBuilds">Order Builds</button>
            <button class="{{ $activeTab === 'checkout' ? 'active' : '' }}" id="checkOutComponents">Check-out Product</button>
        </div>
    </div>

    {{-- ORDER BUILDS --}}
    <section class="section-style !pl-0 !h-[65vh] {{ $activeTab !== 'order' ? 'hide' : '' }}" 
        id="orderBuildsSection">
        <div x-data="{ showModal: false, selectedBuild:{} }" 
            class="h-[55vh]">
            <table class="table mb-3">
                <thead>
                    <tr class="text-sm">
                        <th>Order ID</th>
                        <th>Build Details</th>
                        <th>Order Date</th>
                        <th>Order Status</th>
                        <th>Pickup Status</th>
                        <th>Pickup Date</th>
                        <th>Payment Status</th>
                        <th>Payment Method</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                    <tr 
                        @class([
                            'bg-gray-200 text-gray-500 pointer-events-none' => $order->status === 'Declined',
                            'hover:opacity-50',
                        ])
                        @click="showModal = true; selectedBuild = {{ $order->toJson() }};"
                    >
                        <td>{{ $order->id}}</td>
                        <td @click="showModal = true; selectedBuild = {{ $order->toJson() }};"
                            class="build-details">{{ $order->userBuild->build_name}}</td>
                        <td class="text-center !pr-[1.5%]">{{ $order->created_at ? $order->created_at->format('Y-m-d') : 'N/A' }}</td>
                        <td>{{ $order->status }}</td>
                        <td>{{ $order->pickup_status ? $order->pickup_status : '-' }}</td>
                        <td>{{ $order->pickup_date ? $order->pickup_date->format('Y-m-d') : '-' }}</td>
                        <td>{{ $order->payment_status }}</td>
                        <td>{{ $order->payment_method }}</td>
                        <td class="align-middle text-center">
                            <div class="flex justify-center gap-2">
                                @if ($order->status === 'Pending')
                                    <form action={{ route('staff.order.approve', $order->id) }} method="POST">
                                        @csrf
                                        <button type="submit" @click.stop>
                                            <x-icons.check/>
                                        </button>
                                    </form>
                                    <form action={{ route('staff.order.decline', $order->id) }} method="POST">
                                        @csrf
                                        <button type="submit" @click.stop>
                                            <x-icons.close/>      
                                        </button>
                                    </form>
                                @elseif ($order->status === 'Approved' && $order->pickup_status === null)
                                    <form action={{ route('staff.order.ready', $order->id) }} method="POST">
                                        @csrf
                                        <button @click.stop type="submit" class="action-button">
                                            Build Completed 
                                        </button>
                                    </form>
                                @elseif ($order->pickup_status === 'Pending' && $order->status === 'Approved')
                                    <form action={{ route('staff.order.pickup', $order->id) }} method="POST">
                                        @csrf
                                        <button @click.stop type="submit" class="action-button">
                                            Picked up    
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>    
                    @endforeach
                </tbody>
            </table>

            {{-- VIEW MODAL --}}
            <div x-show="showModal" x-cloak x-transition class="modal overflow-y-scroll p-5">
                <div class="add-component" @click.away="showModal = false">
                    <div class="relative !m-0">
                        <h2 class="text-center w-[100%]">
                            Build Details
                            <x-icons.close class="close" @click="showModal = false"/>    
                        </h2>
                    </div>
                    {{-- <pre x-text="JSON.stringify(selectedBuild, null, 2)"></pre> --}}
                    <div class="build-details-modal">
                        <div class="build-details-header">
                            <h4>Customer Information</h4>
                        </div>
                        <div>
                            <p>Name</p>
                            <p x-text="selectedBuild.user_build.user.first_name + ' ' + selectedBuild.user_build.user.last_name"></p>
                        </div>
                        <div>
                            <p>Contact No</p>
                            <p x-text="selectedBuild.user_build.user.phone_number"></p>
                        </div>
                        <div>
                            <p>Email</p>
                            <p x-text="selectedBuild.user_build.user.email"></p>
                        </div>
                        <div>
                            <p>Build Name</p>
                            <p x-text="selectedBuild.user_build.build_name"></p>
                        </div>
                    </div>
                    <div class="build-details-modal">
                        <div class="build-details-header">
                            <h4>Component</h4>
                        </div>
                        <div>
                            <p>Case</p>
                            <p x-text="selectedBuild.user_build.case.brand + '' + selectedBuild.user_build.case.model "></p>
                        </div>
                        <div>
                            <p>CPU</p>
                            <p x-text="selectedBuild.user_build.cpu.brand + '' + selectedBuild.user_build.cpu.model "></p>
                        </div>
                        <div>
                            <p>RAM</p>
                            <p x-text="selectedBuild.user_build.ram.brand + '' + selectedBuild.user_build.ram.model "></p>
                        </div>
                        <div>
                            <p>Storage</p>
                            <p x-text="selectedBuild.user_build.storage.brand + '' + selectedBuild.user_build.storage.model "></p>
                        </div>
                        <div>
                            <p>Motherboard</p>
                            <p x-text="selectedBuild.user_build.motherboard.brand + '' + selectedBuild.user_build.motherboard.model "></p>
                        </div>
                        <div>
                            <p>GPU</p>
                            <p x-text="selectedBuild.user_build.gpu.brand + '' + selectedBuild.user_build.gpu.model "></p>
                        </div>
                        <div>
                            <p>PSU</p>
                            <p x-text="selectedBuild.user_build.psu.brand + '' + selectedBuild.user_build.psu.model "></p>
                        </div>
                        <div>
                            <p>Cooler</p>
                            <p x-text="selectedBuild.user_build.cooler.brand + '' + selectedBuild.user_build.cooler.model "></p>
                        </div>
                    </div>
                    <div class="build-details-modal">
                        <div class="build-details-price">
                            <h4>Build Price:</h4>
                            <h4 x-text="'₱' + (parseFloat(selectedBuild.user_build.total_price)).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')"></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{ $orders->appends(['tab' => 'order'])->links() }}
    </section>

    {{-- CHECK OUT COMPONENTS --}}
    <section class="section-style !pl-0 !h-[65vh] {{ $activeTab !== 'checkout' ? 'hide' : '' }}" 
    id="checkOutComponentsSection">
        <div x-data="orderModal()" 
            class="h-[55vh]">
            <table class="table mb-3">
                <thead>
                    <tr>
                        <th>Check-Out ID</th>
                        <th>Check-Out Date</th>
                        <th>Total Cost</th>
                        <th>Pickup Status</th>
                        <th>Payment Method</th>
                        <th>Payment Status</th>
                        <th>Pickup Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($groupedCheckouts as $group)
                        <tr
                            @class([
                                'bg-gray-200 text-gray-500 pointer-events-none' => $group['pickup_status'] === 'Declined',
                                'hover:opacity-50'
                            ])
                            @click="setSelectedOrder({{ json_encode($group) }})"
                        >
                            <td>{{ $group['shopping_cart_id'] }}</td>
                            <td>{{ $group['checkout_date'] ? ($group['checkout_date'])->format('Y-m-d') : '-' }}</td>
                            <td class="text-center">{{ number_format($group['total_cost'], 2) }}</td>
                            <td>{{ $group['pickup_status'] ?? '-' }}</td>
                            <td>{{ $group['payment_method'] ?? '-' }}</td>
                            <td>{{ $group['payment_status'] ?? '-' }}</td>
                            <td>{{ $group['pickup_date'] ? ($group['pickup_date'])->format('Y-m-d') : '-' }}</td>
                            <td class="align-middle text-center">
                                <div class="flex justify-center gap-2">
                                    @if (empty($group['pickup_status']))
                                        <form action="{{ route('staff.order.ready-components', ['id' => $group['shopping_cart_id'], 'date' => \Carbon\Carbon::parse($group['checkout_date'])->format('Y-m-d H:i:s')]) }}" method="POST">
                                            @csrf
                                            <button @click.stop type="submit" class="action-button">
                                                Ready for pickup 
                                            </button>
                                        </form>
                                    @elseif (str_contains($group['pickup_status'], 'Pending'))
                                        <form action="{{ route('staff.order.pickup-components',  ['id' => $group['shopping_cart_id'], 'date' => \Carbon\Carbon::parse($group['checkout_date'])->format('Y-m-d H:i:s')]) }}" method="POST">
                                            @csrf
                                            <button @click.stop type="submit" class="action-button">
                                                Picked up    
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>

            </table>

            <div x-show="showModal" x-cloak x-transition class="modal overflow-y-scroll p-5">
                <div class="add-component" @click.away="showModal = false">
                    <div class="relative !m-0">
                        <h2 class="text-center w-[100%]">
                            Order Details
                            <x-icons.close class="close" @click="showModal = false"/>    
                        </h2>
                    </div>
                    {{-- <pre x-text="JSON.stringify(selectedOrder, null, 2)"></pre> --}}
                    <div class="build-details-modal">
                        <div class="build-details-header">
                            <h4>Customer Information</h4>
                        </div>
                        <div>
                            <p>Name</p>
                            <p x-text="selectedOrder.user.first_name + ' ' + selectedOrder.user.last_name"></p>
                        </div>
                        <div>
                            <p>Contact No</p>
                            <p x-text="selectedOrder.user.phone_number"></p>
                        </div>
                        <div>
                            <p>Email</p>
                            <p x-text="selectedOrder.user.email"></p>
                        </div>
                        <div>
                            <p>Checkout Date</p>
                            <p x-text="new Date(selectedOrder.checkout_date).toLocaleDateString()"></p>
                        </div>
                    </div>
                    <div class="build-details-modal">
                        <table class="table mt-3">
                            <thead>
                                <tr>
                                    <th>Component</th>
                                    <th>Type</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in selectedOrder.cart_items" :key="`${item.id}-${index}`">
                                    <tr>
                                        <td x-text="getComponentModel(item)"></td>
                                        <td x-text="item.product_type"></td>
                                        <td x-text="item.quantity"></td>
                                        <td x-text="item.total_price"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                        </div>
                    <div class="build-details-modal">
                        <div class="build-details-price !border-none">
                            <h4>Total Cost:</h4>
                            <h4 x-text="'₱' + (selectedOrder.total_cost.toFixed(2)).replace(/\B(?=(\d{3})+(?!\d))/g, ',')"></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{ $groupedCheckouts->appends(['tab' => 'checkout'])->links() }}

    </section>

</x-dashboardlayout>

<script>
    function orderModal() {
        return {
            showModal: false,
            selectedOrder: {},
            setSelectedOrder(order) {
                // Force a full reactivity reset
                this.selectedOrder = {};
                this.showModal = false;

                setTimeout(() => {
                    this.selectedOrder = JSON.parse(JSON.stringify(order));
                    this.showModal = true;
                }, 50);
            },
            getComponentModel(item) {
                console.log(item);
                if (item.product_type === 'case' && item.case) return item.case.model;
                if (item.product_type === 'cpu' && item.cpu) return item.cpu.model;
                if (item.product_type === 'gpu' && item.gpu) return item.gpu.model;
                if (item.product_type === 'motherboard' && item.motherboard) return item.motherboard.model;
                if (item.product_type === 'ram' && item.ram) return item.ram.model;
                if (item.product_type === 'storage' && item.storage) return item.storage.model;
                if (item.product_type === 'psu' && item.psu) return item.psu.model;
                if (item.product_type === 'cooler' && item.cooler) return item.cooler.model;
                return 'Unknown';
            }
        }
    }
</script>


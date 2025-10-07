<x-dashboardlayout>
<h1>Dashboard</h1>

{{-- Customer Profile --}}
<section class="customer-profile" x-data="{ user: @js(Auth::user()), showEditModal: false }">
    <div class="profile">
        <div>
            <x-icons.profile />
        </div>
        <div class="profile-details">
            <div>
                <span>Name</span>
                <span>:</span>
                <span>{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</span>
            </div>
            <div >
                <span>Email</span>
                <span>:</span>
                <span>{{ Auth::user()->email }}</span>
            </div>
            <div>
                <span>Contact</span>
                <span>:</span>
                <span>{{ Auth::user()->phone_number ? Auth::user()->phone_number : '-'}}</span>
                <span class="email-row">
                    @if (Auth::user()->phone_number === null)
                        <u @click="showEditModal = true ">(Add Contact Number)</u>
                    @endif    
                </span>
            </div>
            <div>
                {{-- change status to Verified --}}
                <span>Status</span>
                <span>:</span>
                <span>{{ Auth::user()->status }}</span> 
            </div>
        </div>
    </div>

    <div>
        <button @click="showEditModal = true">
            <x-icons.edit />
        </button>
    </div>
    
    {{-- <hr> --}}

    {{-- Edit Modal --}}
    <div x-show="showEditModal" x-cloak x-transition class="modal">
        <div class="modal-form" @click.away="showEditModal = false">
            <div class="relative !m-0">
                <h2 class="text-center w-[100%]">
                    Edit Information
                    <x-icons.close class="close" @click="showEditModal = false"/>    
                </h2>
            </div>

            <form action="{{ route('customer.profile.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div>
                    <label for="first_name">First Name</label>
                    <input type="text" name="first_name"  x-model="user.first_name">
                </div>
                <div>
                    <label for="last_name">Last Name</label>
                    <input type="text" name="last_name" x-model="user.last_name">
                </div>
                <div>
                    <label for="email">Email</label>
                    <input type="email" name="email" x-model="user.email" readonly>
                </div>
                <div>
                    <label for="phone_number">Phone Number</label>
                    <input type="text" name="phone_number" x-model="user.phone_number">
                </div>

                <button type="submit">Save</button>
            </form>
        </div>
    </div>
</section>

{{-- Builds Table --}}
<section class="section-style !pl-0 !h-[50vh]">
    <div class="builds-table">
        <x-icons.build />
        <p>Builds</p>
    </div>

    <div>
        <table class="table">
            <thead>
                <tr class="border-t border-black">
                    <th class="pt-2">Build Name/Name</th>
                    <th class="pt-2">Details</th>
                    <th class="pt-2">Date Created</th>
                    <th class="pt-2">Total Cost</th>
                    <th class="pt-2">Status</th>
                    <th class="pt-2">Actions</th>
                </tr>
            </thead>
        </table>
    </div>
    <div x-data="{ showModal: false, orderModal: false, selectedBuild:{} }"
        class="table-body">
        <table class="table">
            <tbody>
                @foreach ($userBuilds as $userBuild)
                    <tr @click="showModal = true; selectedBuild = {{ $userBuild->toJson() }};"
                        class="hover:opacity-50">
                        <td>{{ $userBuild->build_name }}</td>
                        <td class="text-center !pr-[2.5%]">View</td>
                        <td class="text-center !pr-[1.5%]">{{ $userBuild->created_at ? $userBuild->created_at->format('Y-m-d') : 'N/A' }}</td>
                        <td class="text-center">₱ {{ $userBuild->total_price }}</td>
                        <td class="text-center !pr-[.6%]">{{ $userBuild->status }}</td>
                        <td class="text-center">
                            @if ($userBuild->status !== 'Ordered')
                                <button type="submit" 
                                    @click.stop 
                                    @click="orderModal = true; selectedBuild = {{ $userBuild->toJson() }};"
                                    class="cursor-pointer">
                                    <u>Order</u>
                                </button>
                            @else
                                <button disable 
                                    class="cursor-not-allowed text-gray-400 opacity-50">
                                    <u>Order</u>
                                </button>
                            @endif
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
                    <div>
                        <p>Build Name</p>
                        <p x-text="selectedBuild.build_name"></p>
                    </div>
                </div>
                <div class="build-details-modal">
                    <div class="build-details-header">
                        <h4>Component</h4>
                    </div>
                    <div>
                        <p>Case</p>
                        <p x-text="selectedBuild.case.brand + '' + selectedBuild.case.model "></p>
                    </div>
                    <div>
                        <p>CPU</p>
                        <p x-text="selectedBuild.cpu.brand + '' + selectedBuild.cpu.model "></p>
                    </div>
                    <div>
                        <p>RAM</p>
                        <p x-text="selectedBuild.ram.brand + '' + selectedBuild.ram.model "></p>
                    </div>
                    <div>
                        <p>SSD</p>
                        <p x-text="selectedBuild.storage.brand + '' + selectedBuild.storage.model "></p>
                    </div>
                    <div>
                        <p>Motherboard</p>
                        <p x-text="selectedBuild.motherboard.brand + '' + selectedBuild.motherboard.model "></p>
                    </div>
                    <div>
                        <p>GPU</p>
                        <p x-text="selectedBuild.gpu.brand + '' + selectedBuild.gpu.model "></p>
                    </div>
                    <div>
                        <p>HDD</p>
                        <p x-text="selectedBuild.storage.brand + '' + selectedBuild.storage.model "></p>
                    </div>
                    <div>
                        <p>PSU</p>
                        <p x-text="selectedBuild.psu.brand + '' + selectedBuild.psu.model "></p>
                    </div>
                    <div>
                        <p>Cooler</p>
                        <p x-text="selectedBuild.cooler.brand + '' + selectedBuild.cooler.model "></p>
                    </div>
                </div>
                <div class="build-details-modal">
                    <div class="build-details-price">
                        <h4>Build Price:</h4>
                        <h4 x-text="'₱' + (parseFloat(selectedBuild.total_price)).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')"></h4>
                    </div>
                </div>
            </div>
        </div>

        {{-- ORDER MODAL --}}
        <div x-show="orderModal" x-cloak x-transition class="modal overflow-y-scroll p-5">
            <div class="add-component" @click.away="orderModal = false">
                <div class="relative !m-0">
                    <h2 class="text-center w-[100%]">
                        Build Details
                        <x-icons.close class="close" @click="orderModal = false"/>    
                    </h2>
                </div>
                <form action="{{ route('build.save.order')}}" method="POST">
                    @csrf
                    <input type="hidden" name="user_build_id" x-bind:value="selectedBuild.id">
                    <input type="hidden" name="total_price" x-bind:value="selectedBuild.total_price">
                    <div class="build-details-modal">
                        <div class="build-details-header">
                            <h4>Customer Information</h4>
                        </div>
                        <div>
                            <p>Name</p>
                            <p x-text="selectedBuild.user.first_name + ' ' + selectedBuild.user.last_name"></p>
                        </div>
                        <div>
                            <p>Contact No</p>
                            <p x-text="selectedBuild.user.phone_number"></p>
                        </div>
                        <div>
                            <p>Email</p>
                            <p x-text="selectedBuild.user.email"></p>
                        </div>
                        <div>
                            <p>Build Name</p>
                            <p x-text="selectedBuild.build_name"></p>
                        </div>
                    </div>
                    <div class="build-details-modal">
                        <div class="build-details-header">
                            <h4>Component</h4>
                        </div>
                        <div>
                            <p>Case</p>
                            <p x-text="selectedBuild.case.brand + '' + selectedBuild.case.model "></p>
                        </div>
                        <div>
                            <p>CPU</p>
                            <p x-text="selectedBuild.cpu.brand + '' + selectedBuild.cpu.model "></p>
                        </div>
                        <div>
                            <p>RAM</p>
                            <p x-text="selectedBuild.ram.brand + '' + selectedBuild.ram.model "></p>
                        </div>
                        <div>
                            <p>SSD</p>
                            <p x-text="selectedBuild.storage.brand + '' + selectedBuild.storage.model "></p>
                        </div>
                        <div>
                            <p>Motherboard</p>
                            <p x-text="selectedBuild.motherboard.brand + '' + selectedBuild.motherboard.model "></p>
                        </div>
                        <div>
                            <p>GPU</p>
                            <p x-text="selectedBuild.gpu.brand + '' + selectedBuild.gpu.model "></p>
                        </div>
                        <div>
                            <p>HDD</p>
                            <p x-text="selectedBuild.storage.brand + '' + selectedBuild.storage.model "></p>
                        </div>
                        <div>
                            <p>PSU</p>
                            <p x-text="selectedBuild.psu.brand + '' + selectedBuild.psu.model "></p>
                        </div>
                        <div>
                            <p>Cooler</p>
                            <p x-text="selectedBuild.cooler.brand + '' + selectedBuild.cooler.model "></p>
                        </div>
                    </div>
                    <div class="build-details-modal">
                        <div class="build-details-price">
                            <h4>Build Price:</h4>
                            <h4 x-text="'₱' + (parseFloat(selectedBuild.total_price)).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')"></h4>
                        </div>
                    </div>

                    <div>
                        <h4>Payment Method</h4>
                        <div class="flex gap-2">
                            <input type="hidden" name="payment_method" id="payment_method" required>
                            <button
                                type="button"
                                onclick="selectPayment('PayPal', this)"
                                class="payment-btn px-4 py-2 rounded-lg font-semibold hover:!bg-yellow-400">
                                PayPal
                            </button>
                            <button
                                type="button"
                                onclick="selectPayment('Cash on Pickup', this)"
                                class="payment-btn px-4 py-2 rounded-lg font-semibold hover:!bg-yellow-400">
                                Cash On Pickup
                            </button>
                        </div>
                    </div>
                    
                    <div class="flex justify-end mt-4">
                        <button type="submit" 
                            class="bg-blue-500 hover:!bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Order Build
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{ $userBuilds->links() }}
</section>

</x-dashboardlayout>
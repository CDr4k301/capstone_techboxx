<x-dashboardlayout>
    <h2>Inventory</h2>

    <div class="header-container" x-data="{ showStockInModal: false, showStockOutModal: false, componentModal: null }">
        <div>
            <button class="modal-button" @click="showStockInModal = true">
                Stock-In
            </button>
            <button class="modal-button" @click="showStockOutModal = true">
                Stock-Out
            </button>        
        </div>
        

        <div>
            <form action=" {{ route('staff.inventory.search') }}" method="GET">
                <input 
                    type="text"
                    name="search"
                    placeholder="Search components"
                    value="{{ request('search') }}"
                    class="search-bar"
                >
                <button type='submit'>
                    <x-icons.search class="search-icon"/>
                </button>
            </form>
        </div>
    
        {{-- STOCK-IN MODAL --}}
        <div x-show="showStockInModal" x-cloak x-transition class="modal"
             x-data="{ components: @js($components),
                       selectedComponenentId: '',
                       selectedComponent: {}}">
            <div class="add-component" @click.away="showStockInModal = false">
                <div class="relative !m-0">
                    <h2 class="text-center w-[100%]">
                        STOCK-IN FORM
                        <x-icons.close class="close" @click="showStockInModal = false"/>    
                    </h2>
                </div>
                <form class="inventory-form" method="POST" action="{{ route('staff.inventory.stock-in') }}">
                    @csrf
                    <input type="hidden" name="type" :value="selectedComponent.component_type">
                    <div>
                        <label for="">Component</label>
                        <select required name="stockInId" id="stockInId"
                                x-model="selectedComponentId"
                                x-init="selectedComponentId = ''"
                                @change="selectedComponent = components.find(c => c.id == selectedComponentId)">
                            <option disabled selected hidden value="">Select a component</option>
                            @foreach ($components as $component)
                                <option value="{{ $component->id }}">
                                    {{ $component->label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="">Current Stock</label>
                        <input type="text" placeholder="00" :value="selectedComponent.stock" readonly>
                    </div>
                    <div>
                        <label for="">Quantities to Add</label>
                        <input required name="stock" id="stock" type="number" placeholder="00" onkeydown="return !['e','E','+','-'].includes(event.key)">
                    </div>

                    <div>
                        <button>Confirm Stock-in</button>
                        <button @click="showStockInModal = false">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- STOCK-OUT MODAL --}}
        <div x-show="showStockOutModal" x-cloak x-transition class="modal"
             x-data="{ components: @js($components),
                       selectedComponenentId: '',
                       selectedComponent: {}}">
            <div class="add-component" @click.away="showStockOutModal = false">
                <div class="relative !m-0">
                    <h2 class="text-center w-[100%]">
                        STOCK-OUT FORM
                        <x-icons.close class="close" @click="showStockOutModal = false"/>    
                    </h2>
                </div>
                <form class="inventory-form" method="POST" action="{{ route('staff.inventory.stock-out') }}">
                    @csrf
                    <input type="hidden" name="type" :value="selectedComponent.component_type">
                    <div>
                        <label for="">Component</label>
                        <select required name="stockOutId" id="stockOutId"
                                x-model="selectedComponentId"
                                x-init="selectedComponentId = ''"
                                @change="selectedComponent = components.find(c => c.id == selectedComponentId)">
                            <option disabled selected hidden value="">Select a component</option>
                            @foreach ($components as $component)
                                <option value="{{ $component->id }}">
                                    {{ $component->label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="">Current Stock</label>
                        <input type="text" placeholder="00" :value="selectedComponent.stock" readonly>
                    </div>
                    <div>
                        <label for="">Quantities to Remove</label>
                        <input required name="stock" id="stock" type="number" placeholder="00" onkeydown="return !['e','E','+','-'].includes(event.key)">
                    </div>

                    <div>
                        <button>Confirm Stock-out</button>
                        <button @click="showStockOutModal = false">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- TABLE --}}
    <section class="section-style !pl-0 !h-[65vh]">
        <div x-data="{ showStockInCompModal: false, showStockOutCompModal: false, selectedComponent:{} }" 
             class="h-[55vh]">
            <table class="table mb-3">
                <thead>
                    <tr class="text-sm">
                        <th>Component</th>
                        <th>Category</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($components as $component)
                    <tr>
                        <td class="text-center">{{ $component->brand}} {{ $component->model }}</td>
                        <td class="text-center">{{ ucfirst($component->component_type) }}</td>
                        <td class="text-center">{{ $component->stock }}</td>
                        <td class="text-center"><span class="{{ $component->status === 'Low' ? 'text-red-500' : 'text-green-600' }}">
                            {{ $component->status}}        
                        </span></td>
                        <td class="align-middle text-center">
                            <div class="flex justify-center gap-2">
                                <button @click="showStockInCompModal = true; selectedComponent = {{ $component->toJson() }};">
                                    <x-icons.stockin/>    
                                </button>
                                <button @click="showStockOutCompModal = true; selectedComponent = {{ $component->toJson() }};">
                                    <x-icons.stockout/>    
                                </button>
                            </div>
                        </td>
                    </tr>    
                    @endforeach
                </tbody>
            </table>

            {{-- STOCK-IN MODAL --}}
            <div x-show="showStockInCompModal" x-cloak x-transition class="modal">
                <div class="add-component" @click.away="showStockInCompModal = false">
                <div class="relative !m-0">
                    <h2 class="text-center w-[100%]">
                        STOCK-IN FORM
                        <x-icons.close class="close" @click="showStockInCompModal = false"/>    
                    </h2>
                </div>
                    <form class="inventory-form" method="POST" action="{{ route('staff.inventory.stock-in') }}">
                        @csrf
                        <input type="hidden" name="stockInId" :value="selectedComponent.id">
                        <input type="hidden" name="type" :value="selectedComponent.component_type">
                        <div>
                            <label for="">Component</label>
                            <input type="text" x-model="selectedComponent.label" readonly>
                        </div>
                        <div>
                            <label for="">Current Stock</label>
                            <input type="text" placeholder="00" :value="selectedComponent.stock" readonly>
                        </div>
                        <div>
                            <label for="">Quantities to Add</label>
                            <input required name="stock" id="stock" type="number" placeholder="00" onkeydown="return !['e','E','+','-'].includes(event.key)">
                        </div>
                        <div>
                            <button>Confirm Stock-in</button>
                            <button @click="showStockInCompModal = false">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- STOCK-OUT MODAL --}}
            <div x-show="showStockOutCompModal" x-cloak x-transition class="modal">
                <div class="add-component" @click.away="showStockOutCompModal = false">
                    <h2 class="text-center w-[100%]">
                        STOCK-OUT FORM
                        <x-icons.close class="close" @click="showStockOutCompModal = false"/>    
                    </h2>
                    <form class="inventory-form" method="POST" action="{{ route('staff.inventory.stock-out') }}">
                        @csrf
                        <input type="hidden" name="stockOutId" :value="selectedComponent.id">
                        <input type="hidden" name="type" :value="selectedComponent.component_type">
                        <div>
                            <label for="">Component</label>
                            <input type="text" x-model="selectedComponent.label" readonly>
                        </div>
                        <div>
                            <label for="">Current Stock</label>
                            <input type="text" placeholder="00" :value="selectedComponent.stock" readonly>
                        </div>
                        <div>
                            <label for="">Quantities to Remove</label>
                            <input required name="stock" id="stock" type="number" placeholder="00" onkeydown="return !['e','E','+','-'].includes(event.key)">
                        </div>
                        <div>
                            <button>Confirm Stock-out</button>
                            <button @click="showStockOutCompModal = false">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        {{ $components->links() }}
    </section>
    

</x-dashboardlayout>
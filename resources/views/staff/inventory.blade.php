<x-dashboardlayout>
    <h2>Inventory</h2>

    <div class="header-container" x-data="{ showStockInModal: false, showStockOutModal: false, componentModal: null }">
        <div>
            {{-- <button class="modal-button" @click="showStockInModal = true">
                Stock-In
            </button>
            <button class="modal-button" @click="showStockOutModal = true">
                Stock-Out
            </button>         --}}
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
    
        
    </div>

    {{-- TABLE --}}
    <section class="section-style !pl-0 !h-[65vh]">
        <div x-data="{ showStockInCompModal: false, showStockOutCompModal: false, selectedComponent:{} }" 
             class="h-[55vh]">
            <table class="table mb-3">
                <colgroup>
                    <col class="w-[30%]">  
                    <col class="w-[15%]">  
                    <col class="w-[15%]"> 
                    <col class="w-[15%]"> 
                    <col class="w-[15%]"> 
                </colgroup>
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
                            <input name="label" type="text" x-model="selectedComponent.label" readonly>
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
                            <input name="label" type="text" x-model="selectedComponent.label" readonly>
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
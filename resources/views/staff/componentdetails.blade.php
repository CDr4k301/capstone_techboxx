<x-dashboardlayout>
    <h2>Component Details</h2>

    <div class="header-container" x-data="{ showAddModal: false, componentModal: null }">
        <button class="modal-button" @click="showAddModal = true">
            Add New Component
        </button>

        <div>
            <form action=" {{ route('staff.componentdetails.search') }}" method="GET">
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
    
        {{-- ADD COMPONENT MODAL --}}
        <div x-show="showAddModal" x-cloak x-transition class="modal">
            <div class="add-component" @click.away="showAddModal = false">
                @include('staff.componentdetails.add.addnewcomponent')
            </div>
        </div>

        @foreach (['cpu', 'gpu', 'ram', 'motherboard', 'storage', 'psu', 'case', 'cooler'] as $type)
            <div x-show="componentModal === '{{ $type }}'" x-cloak x-transition class="modal p-10 overflow-y-scroll">
                <div class="new-component" @click.away="componentModal = null; showAddModal = true;">
                    

                    @include('staff.componentdetails.add.' . $type, [
                        'moboSpecs' => $moboSpecs,
                        'gpuSpecs' => $gpuSpecs,
                        'caseSpecs' => $caseSpecs,
                        'psuSpecs' => $psuSpecs,
                        'ramSpecs' => $ramSpecs,
                        'storageSpecs' => $storageSpecs,
                        'cpuSpecs' => $cpuSpecs,
                        'coolerSpecs' => $coolerSpecs,
                    ])
                </div>
            </div>
        @endforeach
    </div>

    {{-- TABLE --}}
    <section class="section-style !pl-0 !h-[65vh]">
        <div x-data="{ showViewModal: false, showEditModal: false, selectedComponent:{} }" class=" h-[55vh]">
            <table class="table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Supplier</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    @foreach ($components as $component)
                    <tr @click="showViewModal = true; selectedComponent = {{ $component->toJson() }};"
                    class="{{ $component->deleted_at ? 'bg-gray-300 opacity-50 cursor-not-allowed' : '' }} hover:opacity-50" >
                        <td>{{ $component->buildCategory->name}}</td>
                        <td>{{ $component->supplier->name}}</td>
                        <td>{{ $component->brand}} {{ $component->model }}</td>
                        <td>₱{{ number_format($component->price, 2) }}</td>
                        <td>{{ $component->stock }}</td>
                        <td class="align-middle text-center">
                            <div class="flex justify-center gap-2">
                                @if($component->deleted_at)
                                    {{-- Restore Button for Soft Deleted Components --}}
                                    <form action="{{ route('staff.componentdetails.restore', ['type' => $component->component_type, 'id' => $component->id]) }}" method="POST">
                                        @csrf
                                        @method('PATCH') <!-- or any method you use for restoring -->
                                        <button @click.stop type="submit">
                                            <x-icons.restore />
                                        </button>
                                    </form>
                                @else
                                    {{-- Edit Button for Active Components --}}
                                    <button @click.stop @click="showEditModal = true; selectedComponent = {{ $component->toJson() }};">
                                        <x-icons.edit />
                                    </button>

                                    {{-- Delete Button for Active Components --}}
                                    <form action="{{ route('staff.componentdetails.delete', ['type' => $component->component_type, 'id' => $component->id]) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button @click.stop type="submit">
                                            <x-icons.delete />
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
            <div x-show="showViewModal" x-cloak x-transition class="modal modal-scroll">
                <div class="view-component" @click.away="showViewModal = false">
                    <div x-show="selectedComponent.component_type === 'motherboard'">
                        @include('staff.componentdetails.view.motherboard')
                    </div>

                    <div x-show="selectedComponent.component_type === 'gpu'">
                        @include('staff.componentdetails.view.gpu')
                    </div>

                    <div x-show="selectedComponent.component_type === 'case'">
                        @include('staff.componentdetails.view.case')
                    </div>

                    <div x-show="selectedComponent.component_type === 'psu'">
                        @include('staff.componentdetails.view.psu')
                    </div>

                    <div x-show="selectedComponent.component_type === 'ram'">
                        @include('staff.componentdetails.view.ram')
                    </div>

                    <div x-show="selectedComponent.component_type === 'storage'">
                        @include('staff.componentdetails.view.storage')
                    </div>

                    <div x-show="selectedComponent.component_type === 'cpu'">
                        @include('staff.componentdetails.view.cpu')
                    </div>

                    <div x-show="selectedComponent.component_type === 'cooler'">
                        @include('staff.componentdetails.view.cooler')
                    </div>
                </div>
            </div>

            {{-- EDIT MODAL --}}
            <div x-show="showEditModal" x-cloak x-transition class="modal modal-scroll">
                <div class="new-component" @click.away="showEditModal = false">
                    <div x-show="selectedComponent.component_type === 'motherboard'">
                        @include('staff.componentdetails.edit.motherboard')
                    </div>

                    <div x-show="selectedComponent.component_type === 'gpu'">
                        @include('staff.componentdetails.edit.gpu')
                    </div>

                    <div x-show="selectedComponent.component_type === 'case'">
                        @include('staff.componentdetails.edit.case')
                    </div>

                    <div x-show="selectedComponent.component_type === 'psu'">
                        @include('staff.componentdetails.edit.psu')
                    </div>

                    <div x-show="selectedComponent.component_type === 'ram'">
                        @include('staff.componentdetails.edit.ram')
                    </div>

                    <div x-show="selectedComponent.component_type === 'storage'">
                        @include('staff.componentdetails.edit.storage')
                    </div>

                    <div x-show="selectedComponent.component_type === 'cpu'">
                        @include('staff.componentdetails.edit.cpu')
                    </div>

                    <div x-show="selectedComponent.component_type === 'cooler'">
                        @include('staff.componentdetails.edit.cooler')
                    </div>
                </div>
            </div>
        </div>

    {{ $components->links() }}
        
    </section>

    <h2>Supplier</h2>

    <div class="header-container" x-data="{ showAddModal: false, showAddBrandModal: false }">
        <div>
            <button class="modal-button" @click="showAddModal = true">
                Add Supplier
            </button>
        </div>

        <div>
            <form action=" {{ route('staff.supplier.search') }}" method="GET">
                <input 
                    type="text"
                    name="search_supplier"
                    placeholder="Search supplier"
                    value="{{ request('search_supplier') }}"
                    class="search-bar"
                >
                <button type='submit'>
                    <x-icons.search class="search-icon"/>
                </button>
            </form>
        </div>
        
        {{-- STOCK-IN MODAL --}}
        <div x-show="showAddModal" x-cloak x-transition class="modal">
            <div class="add-component" @click.away="showAddModal = false">
                <div class="relative !m-0">
                    <h2 class="text-center w-[100%]">
                        SUPPLIER FORM
                        <x-icons.close class="close" @click="showAddModal = false"/>    
                    </h2>
                </div>
                <form class="inventory-form" method="POST" action="{{ route('staff.supplier.store') }}">
                    @csrf
                    <div>
                        <label for="">Supplier Name</label>
                        <input required type="text" name="name">
                    </div>
                    <div>
                        <label for="">Contact Person</label>
                        <input required type="text" name="contact_person">
                    </div>
                    <div>
                        <label for="">Email</label>
                        <input required type="email" name="email">
                    </div>
                    <div>
                        <label for="">Contact number</label>
                        <input required name="phone" id="phone" type="number" onkeydown="return !['e','E','+','-'].includes(event.key)">
                    </div>    
                    <div>
                        <button>Add Supplier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <section class="section-style !pl-0 !pb-3 !h-[65vh]">
        <div x-data="{ showViewModal: false, currentSupplier: null, showEditModal: false }" class="h-[75vh]">
            <table class="table">
                <thead>
                    <tr class="text-sm">
                        <th class="text-left p-2">Supplier Name</th>
                        <th class="text-left p-2">Contact Person</th>
                        <th class="text-left p-2">Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($suppliers as $supplier)
                        <tr @click="showViewModal = true; currentSupplier = {{ $supplier->toJson() }};"
                            class="{{ $supplier->is_active ? '' : 'bg-gray-300 opacity-50 cursor-not-allowed' }} hover:opacity-50" >
                            <td>{{$supplier->name}}</td>
                            <td>{{$supplier->contact_person}}</td>
                            <td>{{$supplier->email}}</td>
                            <td class="text-center">{{$supplier->phone}}</td>
                            <td class="text-center">{{$supplier->is_active ? 'Active' : 'Inactive' }}</td>
                            <td>
                                <div class="flex justify-center gap-2">
                                    @if (!$supplier->is_active)
                                        <form action="{{ route('staff.supplier.restore', $supplier->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button @click.stop type="submit" title="Activate">
                                                <x-icons.restore />
                                            </button>
                                        </form>
                                    @else
                                        <button @click.stop @click="
                                            currentSupplier = {{ $supplier->toJson() }};
                                            showEditModal = true
                                        ">
                                            <x-icons.edit/>    
                                        </button>
                                        <form action="{{ route('staff.supplier.delete', $supplier->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button @click.stop type="submit">
                                                <x-icons.delete/>    
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
            <div x-show="showViewModal" x-cloak x-transition class="modal">
                <div class="modal-form" @click.away="showViewModal = false">
                    <div class="relative !m-0">
                        <h2 class="text-center w-[100%]">
                            Supplier Details
                            <x-icons.close class="close" @click="showViewModal = false"/>    
                        </h2>
                    </div>

                    <div class="supplier-container mt-3">
                        <div>
                            <p>Supplier Name</p>
                            <p x-text="currentSupplier ? currentSupplier.name : ''"></p>    
                        </div>
                        <div>
                            <p>Contact Person</p>
                            <p x-text="currentSupplier ? currentSupplier.contact_person : ''"></p>    
                        </div>
                        <div>
                            <p>Email</p>
                            <p x-text="currentSupplier ? currentSupplier.email : ''"></p>    
                        </div>
                        <div>
                            <p>Phone</p>
                            <p x-text="currentSupplier ? currentSupplier.phone : ''"></p>    
                        </div>
                    </div>
                </div>
            </div>

            {{-- EDIT MODAL --}}
            <div x-show="showEditModal" x-cloak x-transition class="modal">
                <div class="add-component" @click.away="showEditModal = false">
                    <div class="relative !m-0">
                        <h2 class="text-center w-[100%]">
                            Edit Details
                            <x-icons.close class="close" @click="showEditModal = false"/>    
                        </h2>
                    </div>

                    <form class="inventory-form" method="POST" :action="'{{ url('staff/supplier/update') }}/' + currentSupplier.id">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id" x-model="currentSupplier.id">
                        <div>
                            <label for="">Supplier Name</label>
                            <input required type="text" name="name" x-model="currentSupplier.name">
                        </div>
                        <div>
                            <label for="">Contact Person</label>
                            <input required type="text" name="contact_person" x-model="currentSupplier.contact_person">
                        </div>
                        <div>
                            <label for="">Email</label>
                            <input required type="email" name="email" x-model="currentSupplier.email">
                        </div>
                        <div>
                            <label for="">Contact number</label>
                            <input required name="phone" id="phone" type="number" x-model="currentSupplier.phone" onkeydown="return !['e','E','+','-'].includes(event.key)">
                        </div>    
                        <div>
                            <button>Update Details</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        {{ $suppliers->links() }}
    </section>    
</x-dashboardlayout>
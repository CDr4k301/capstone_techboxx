@props(['coolerSpecs'])

<div class="relative !m-0">
    <h2 class="text-center w-[100%]">
        EDIT
        <x-icons.close class="close" @click="showEditModal = false"/>    
    </h2>
</div>

<form x-bind:action="'/staff/component-details/cooler/' + selectedComponent.id" method="POST" class="new-component-form" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="component_type" value="cooler">
    <input type="hidden" name="_method" value="PUT">

    <div class="form-container">
        {{-- SPECS --}}
        <div class="form-divider">
            <div>
                <label for="">Supplier</label>
                <select required name="supplier_id"  x-model="selectedComponent.supplier_id" class="supplier-select">
                    <option disabled selected hidden value="">Select a supplier</option>
                    @foreach ($coolerSpecs['suppliers'] as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="">Build Category</label>
                <select required name="build_category_id" id="build_category_id" x-model="selectedComponent.build_category_id">
                    <option disabled selected hidden value="">Select build category</option>   
                    @foreach ($caseSpecs['buildCategories'] as $buildCategory)
                        <option value="{{ $buildCategory->id }}">{{ $buildCategory->name }}</option>
                    @endforeach 
                </select>  
            </div>
            <div>
                <label for="">Brand</label>
                <input name="brand" required type="text" x-model="selectedComponent.brand" placeholder="Enter Brand">
            </div>

            <div>
                <label for="">Model</label>
                <input name="model" required type="text" x-model="selectedComponent.model" placeholder="Enter Model">
            </div>

            <div>
                <label for="">Cooler Type</label>
                <select required name="cooler_type" id="cooler_type" x-model="selectedComponent.cooler_type">
                    <option disabled selected hidden value="">Select cooler type</option>
                    @foreach ($coolerSpecs['cooler_types'] as $cooler_type)
                        <option value="{{ $cooler_type }}">{{ $cooler_type }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="">Max Tdp</label>
                <input required name="max_tdp" id="max_tdp" type="number" placeholder="00 W" x-model="selectedComponent.max_tdp" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>
            <div class="flex flex-col"
                x-data="{ slots:[{}] }">
                <template x-for="(slot, index) in selectedComponent.socket_compatibility" 
                          :key="index">
                    <div>
                        <label for="">Socket Compatibility <span x-text="index + 1"></span></label>
                        <select required :name="'socket_compatibility[]'" id="socket_compatibility" x-model="selectedComponent.socket_compatibility[index]">
                            <option disabled selected hidden value="">Select socket compatibility</option>
                            @foreach ($coolerSpecs['socket_compatibilities'] as $socket_compatibility)
                                <option value="{{ $socket_compatibility }}">{{ $socket_compatibility }}</option>
                            @endforeach
                        </select>
                        
                        <template x-if="index > 0">
                            <button type="button"
                                class="remove-add"
                                @click="selectedComponent.socket_compatibility.splice(index, 1)">
                                x
                            </button>    
                        </template>
                    </div>
                </template>
                
                {{-- ADD SOCKET BUTTON --}}
                <button type="button"
                        @click="selectedComponent.socket_compatibility.push([])"
                        class="add-pcie">
                    + Add socket
                </button>
            </div>

            

            

        </div>

        {{-- INVENTORY --}}
        <div class="form-divider">
            <div>
                <label for="">Radiator Size</label>
                <input name="radiator_size_mm" id="radiator_size_mm" type="number" x-model="selectedComponent.radiator_size_mm" placeholder="00 mm (if liquid cooler)" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>

            <div>
                <label for="">Fan Count</label>
                <input required name="fan_count" id="fan_count" type="number" placeholder="Enter number of fan" x-model="selectedComponent.fan_count" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>

            <div>
                <label for="">Height</label>
                <input required name="height_mm" id="height_mm" type="number" placeholder="00 mm" x-model="selectedComponent.height_mm" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>
            <div>
                <label for="">Base Price</label>
                <input required name="base_price" id="base_price" type="number" step="0.01" placeholder="Enter price" x-model="selectedComponent.base_price" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>
            <div>
                <label for="">Selling Price</label>
                <input required name="price" id="price" type="number" step="0.01" placeholder="Enter price" x-model="selectedComponent.price" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>
            
            

            <div>
                <label for="">Stock</label>
                <input required name="stock" id="stock" type="number" placeholder="Enter stock" x-model="selectedComponent.stock" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>
            <div>
                <label for="">Upload image</label>
                <input type="file" name="image" accept="image/*">
            </div>

            <div>
                <label for="">Upload 3d model</label>
                <input type="file" name="model_3d" accept=".glb">
            </div>
        </div>      
    </div>
    
    <button>Update Component</button>
</form>
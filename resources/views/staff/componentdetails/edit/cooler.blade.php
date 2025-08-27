@props(['coolerSpecs'])

<div class="new-component-header">
    <h2 class="text-center">Cooler</h2>
</div>

<form x-bind:action="'/staff/component-details/cooler/' + selectedComponent.id" method="POST" class="new-component-form" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="component_type" value="cooler">

    <div class="form-container">
        {{-- SPECS --}}
        <div class="form-divider">
            <div>
                <label for="">Brand</label>
                <select required name="brand" id="brand" x-model="selectedComponent.brand">
                    <option disabled selected hidden value="">Select a brand</option>
                    @foreach ($coolerSpecs['brands'] as $brand)
                        <option value="{{ $brand }}">{{ $brand }}</option>
                    @endforeach
                </select>
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

            <div class="flex flex-col"
                x-data="{ slots:[{}] }">
                <template x-for="(slot, index) in slots" 
                          :key="index">
                    <div>
                        <label for="">Socket Compatibility <span x-text="index + 1"></span></label>
                        <select required :name="'socket_compatibility[]'" id="socket_compatibility" x-model="selectedComponent.socket_compatibility">
                            <option disabled selected hidden value="">Select socket compatibility</option>
                            @foreach ($coolerSpecs['socket_compatibilities'] as $socket_compatibility)
                                <option value="{{ $socket_compatibility }}">{{ $socket_compatibility }}</option>
                            @endforeach
                        </select>
                        
                        <template x-if="index > 0">
                            <button type="button"
                                class="remove-add"
                                @click="slots.splice(index, 1)">
                                x
                            </button>    
                        </template>
                    </div>
                </template>
                
                {{-- ADD SOCKET BUTTON --}}
                <button type="button"
                        @click="slots.push({})"
                        class="add-pcie">
                    + Add socket
                </button>
            </div>

            <div>
                <label for="">Max Tdp</label>
                <input required name="max_tdp" id="max_tdp" type="number" placeholder="00 W" x-model="selectedComponent.max_tdp" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>

            <div>
                <label for="">Radiator Size</label>
                <input required name="radiator_size_mm" id="radiator_size_mm" type="number" x-model="selectedComponent.radiator_size_mm" placeholder="00 mm (if liquid cooler)" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>

            <div>
                <label for="">Fan Count</label>
                <input required name="fan_count" id="fan_count" type="number" placeholder="Enter number of fan" x-model="selectedComponent.fan_count" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>

            <div>
                <label for="">Height</label>
                <input required name="height_mm" id="height_mm" type="number" placeholder="00 mm" x-model="selectedComponent.height_mm" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>

        </div>

        {{-- INVENTORY --}}
        <div class="form-divider">
            <div>
                <label for="">Price</label>
                <input required name="price" id="price" type="number" step="0.01" placeholder="Enter price" x-model="selectedComponent.price" onkeydown="return !['e','E','+','-'].includes(event.key)">
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
                <label for="">Stock</label>
                <input required name="stock" id="stock" type="number" placeholder="Enter stock" x-model="selectedComponent.stock" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>

            <div>
                <label for="">Upload product image</label>
                <input type="file" name="image" multiple accept="image/*">
            </div>
        </div>      
    </div>
    
    <button>Update Component</button>
</form>
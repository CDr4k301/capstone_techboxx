@props(['storageSpecs'])

<div class="flex flex-row justify-between">
    <button @click="componentModal = null; showAddModal = true;">
        <x-icons.arrow class="rotate-90 hover:opacity-50 w-[24px] h-[24px]"/>
    </button>
    <h2 class="text-center">Storage</h2>
    <button @click="componentModal = null; showAddModal = true;">
        <x-icons.close/>
    </button>
</div>

<form action="{{ route('staff.componentdetails.storage.store') }}" method="POST" class="new-component-form" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="component_type" value="storage">
    <div class="form-container">
        {{-- SPECS --}}
        <div class="form-divider">
            <div>
                <label for="">Supplier</label>
                <select required name="supplier_id" class="supplier-select">
                    <option disabled selected hidden value="">Select a supplier</option>
                    @foreach ($storageSpecs['suppliers'] as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="">Build Category</label>
                <select required name="build_category_id" id="build_category_id">
                    <option disabled selected hidden value="">Select build category</option>   
                    @foreach ($storageSpecs['buildCategories'] as $buildCategory)
                        <option value="{{ $buildCategory->id }}">{{ $buildCategory->name }}</option>
                    @endforeach 
                </select>  
            </div>
            <div>
                <label for="">Brand</label>
                <input name="brand" required type="text" placeholder="Enter Brand">
            </div>

            <div>
                <label for="">Models</label>
                <input name="model" required type="text" placeholder="Enter Model">
            </div>

            <div>
                <label for="">Storage Type</label>
                <select required name="storage_type" id="storage_type">
                    <option disabled selected hidden value="">Select storage type</option>
                    @foreach ($storageSpecs['storage_types'] as $storage_type)
                        <option value="{{ $storage_type }}">{{ $storage_type }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="">Interface</label>
                <select required name="interface" id="interface">
                    <option disabled selected hidden value="">Select interface</option>
                    @foreach ($storageSpecs['interfaces'] as $interface)
                        <option value="{{ $interface }}">{{ $interface }}</option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label for="">Capacity GB</label>
                <input required name="capacity_gb" id="capacity_gb" type="number" placeholder="000 GB" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>

            <div>
                <label for="">Form Factor</label>
                <select required name="form_factor" id="form_factor">
                    <option disabled selected hidden value="">Select form factor</option>
                    @foreach ($storageSpecs['form_factors'] as $form_factor)
                        <option value="{{ $form_factor }}">{{ $form_factor }}</option>
                    @endforeach
                </select>
            </div>

            
        </div>

        {{-- INVENTORY --}}
        <div class="form-divider">
            <div>
                <label for="">Read Speed Mbps</label>
                <input required name="read_speed_mbps" id="read_speed_mbps" type="number" placeholder="000 MB/s" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>

            <div>
                <label for="">Write Speed Mbps</label>
                <input required name="write_speed_mbps" id="write_speed_mbps" type="number" placeholder="000 MB/s" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>
            <div>
                <label for="">Base Price</label>
                <input required name="base_price" id="base_price" type="number" step="0.01" placeholder="Enter Price" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>
            <div>
                <label for="">Selling Price</label>
                <input required name="price" id="price" type="number" step="0.01" placeholder="Enter Price" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>
            <div>
                <label for="">Stock</label>
                <input required name="stock" id="stock" type="number" placeholder="Enter stock" onkeydown="return !['e','E','+','-'].includes(event.key)">
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
    
    <button>Add Component</button>
</form>

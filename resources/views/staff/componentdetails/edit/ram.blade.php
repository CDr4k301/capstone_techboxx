@props(['ramSpecs'])
<div class="relative !m-0">
    <h2 class="text-center w-[100%]">
        EDIT
        <x-icons.close class="close" @click="showEditModal = false"/>    
    </h2>
</div>

<form x-bind:action="'/staff/component-details/ram/' + selectedComponent.id" method="POST" class="new-component-form" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="_method" value="PUT">
    <div class="form-container">
        {{-- SPECS --}}
        <div class="form-divider">
            <div>
                <label for="">Supplier</label>
                <select required name="supplier_id"  x-model="selectedComponent.supplier_id" class="supplier-select">
                    <option disabled selected hidden value="">Select a supplier</option>
                    @foreach ($ramSpecs['suppliers'] as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="">Build Category</label>
                <select required name="build_category_id" id="build_category_id" x-model="selectedComponent.build_category_id">
                    <option disabled selected hidden value="">Select build category</option>   
                    @foreach ($ramSpecs['buildCategories'] as $buildCategory)
                        <option value="{{ $buildCategory->id }}">{{ $buildCategory->name }}</option>
                    @endforeach 
                </select>  
            </div>
            <div>
                <label for="">Brand</label>
                <input name="brand" required type="text" x-model="selectedComponent.brand" placeholder="Enter Brand">
            </div>

            <div>
                <label for="">Models</label>
                <input name="model" required type="text" placeholder="Enter Model" x-model="selectedComponent.model">
            </div>

            <div>
                <label for="">RAM Type</label>
                <select required name="ram_type" id="ram_type" x-model="selectedComponent.ram_type">
                    <option disabled selected hidden value="">Select a ram</option>
                    @foreach ($ramSpecs['rams'] as $ram)
                        <option value="{{ $ram }}">{{ $ram }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="">Speed Mhz</label>
                <input required name="speed_mhz" id="speed_mhz" type="number" placeholder="0000 MHz" x-model="selectedComponent.speed_mhz" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>
            
            <div>
                <label for="">Size Per Module GB</label>
                <input required name="size_per_module_gb" id="size_per_module_gb" type="number" placeholder="00 GB" x-model="selectedComponent.size_per_module_gb" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>

            <div>
                <label for="">Total Capacity GB</label>
                <input required name="total_capacity_gb" id="total_capacity_gb" type="number" placeholder="00 GB" x-model="selectedComponent.total_capacity_gb" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>

            

        </div>

        {{-- INVENTORY --}}
        <div class="form-divider">
            <div>
                <label for="">Module Count</label>
                <input required name="module_count" id="module_count" type="number" placeholder="00" x-model="selectedComponent.module_count" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>

            <div>
                <label for="">ECC Support</label>
                <select required name="is_ecc" id="is_ecc" x-model="selectedComponent.is_ecc">
                    <option disabled selected hidden value="">Select option</option>
                    <option value="Yes">Yes</option>
                    <option value="No">No (Non-ECC)</option>
                </select>
            </div>
            
            <div>
                <label for="">RGB Support</label>
                    <select required name="is_rgb" id="is_rgb" x-model="selectedComponent.is_rgb">
                        <option disabled selected hidden value="">Select option</option>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
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
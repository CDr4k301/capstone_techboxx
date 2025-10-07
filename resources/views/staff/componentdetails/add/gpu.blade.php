@props(['gpuSpecs'])

<div class="flex flex-row justify-between">
    <button @click="componentModal = null; showAddModal = true;">
        <x-icons.arrow class="rotate-90 hover:opacity-50 w-[24px] h-[24px]"/>
    </button>
    <h2 class="text-center">GPU</h2>
    <button @click="componentModal = null; showAddModal = true;">
        <x-icons.close/>
    </button>
</div>

<form action="{{ route('staff.componentdetails.gpu.store') }}" method="POST" class="new-component-form" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="component_type" value="gpu">

    <div class="form-container">
        {{-- SPECS --}}
        <div class="form-divider gpu">
            <div>
                <label for="">Supplier</label>
                <select required name="supplier_id" class="supplier-select">
                    <option disabled selected hidden value="">Select a supplier</option>
                    @foreach ($gpuSpecs['suppliers'] as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="">Build Category</label>
                <select required name="build_category_id" id="build_category_id">
                    <option disabled selected hidden value="">Select build category</option>   
                    @foreach ($gpuSpecs['buildCategories'] as $buildCategory)
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
                <label for="">Video RAM GB</label>
                <input required type="number" name="vram_gb" placeholder="00 GB" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>

            <div>
                <label for="">Power Draw Watts</label>
                <input required type="number" name="power_draw_watts" placeholder="00 W TDP" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>
            
            <div>
                <label  for="">Recommended PSU Watt</label>
                <input required type="number" name="recommended_psu_watt" placeholder="00 W" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>

            <div>
                <label for="">Length</label>
                <input required type="number" name="length_mm" placeholder="00 mm" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>

            
        </div>

        {{-- INVENTORY --}}
        <div class="form-divider">
            <div>
                <label for="">PCIe Interface</label>
                <select required name="pcie_interface" id="pcie_interface">
                    <option disabled selected hidden value="">Select a PCIe interface</option>
                    @foreach ($gpuSpecs['pcie_interfaces'] as $pcie_interface)
                        <option value="{{ $pcie_interface }}">{{ $pcie_interface }}</option>
                    @endforeach
                </select> 
            </div>

            <div>
                <label for="">Connectors Required</label>
                <select required name="connectors_required" id="connectors_required">
                    <option disabled selected hidden value="">Select connectors</option>
                    @foreach ($gpuSpecs['connectors_requireds'] as $connectors_required)
                        <option value="{{ $connectors_required }}">{{ $connectors_required }}</option>
                    @endforeach
                </select> 
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


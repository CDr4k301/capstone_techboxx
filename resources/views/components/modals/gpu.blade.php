@props(['gpuSpecs'])

<div class="new-component-header">
    {{ $slot }}

    <h2 class="text-center">GPU</h2>
</div>

<form action="{{ route('staff.componentdetails.gpu.store') }}" method="POST" class="new-component-form" enctype="multipart/form-data">
    @csrf
    <div class="form-container">
        {{-- SPECS --}}
        <div class="form-divider gpu">
            <div>
                <label for="">Brand</label>
                <select required name="brand" id="brand">
                    <option disabled selected hidden value="">Select a brand</option>
                    @foreach ($gpuSpecs['brands'] as $brand)
                        <option value="{{ $brand->brand }}">{{ $brand->brand }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="">Models</label>
                <input name="model" required type="text" placeholder="Enter Model">
            </div>

            <div>
                <label for="">Video RAM GB</label>
                <div class="w-[80%]">
                    <input required type="number" name="memory_capacity" placeholder="00 GB" onkeydown="return !['e','E','+','-'].includes(event.key)">
                    <select required name="vram_gb" id="vram_gb">
                        <option disabled selected hidden value="">Select a memory type</option>
                        @foreach ($gpuSpecs['vram_gbs'] as $vram_gb)
                            <option value="{{ $vram_gb }}">{{ $vram_gb }}</option>
                        @endforeach
                    </select>    
                </div>
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

            <div>
                <label for="">PCIe Interface</label>
                <select required name="pcie_interface" id="pcie_interface">
                    <option disabled selected hidden value="">Select a PCIe interface</option>
                    @foreach ($gpuSpecs['pcie_interfaces'] as $pcie_interface)
                        <option value="{{ $pcie_interface->pcie_interface }}">{{ $pcie_interface->pcie_interface }}</option>
                    @endforeach
                </select> 
            </div>

            <div>
                <label for="">Connectors Required</label>
                <select required name="connectors_required" id="connectors_required">
                    <option disabled selected hidden value="">Select connectors</option>
                    @foreach ($gpuSpecs['connectors_requireds'] as $connectors_required)
                        <option value="{{ $connectors_required->connectors_required }}">{{ $connectors_required->connectors_required }}</option>
                    @endforeach
                </select> 
            </div>
        </div>

        {{-- INVENTORY --}}
        <div class="form-divider">
            <div>
                <label for="">Price</label>
                <input required name="price" id="price" type="number" step="0.01" placeholder="Enter price" onkeydown="return !['e','E','+','-'].includes(event.key)">
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
                <label for="">Stock</label>
                <input required name="stock" id="stock" type="number" placeholder="Enter stock" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>

            <div>
                <label for="product-img">Upload product image</label>    
                
                <div class="product-img">
                    <input required type="file" id="image" name="image" accept="image/*" class="custom-file" onchange="updateFileName(this)">

                    {{-- upload icon --}}
                    <label for="image">
                        <x-icons.upload class="upload-product"/>    
                    </label>

                    {{-- show the file name --}}
                    <p id="filename" class="filename text-gray-500">Upload product image</p>
                </div>
            </div>

            <div>
                <label for="product_img">Upload product 3d model</label>    
                
                <div class="product-img">
                    <input type="file" id="model_3d" name="model_3d" accept=".obj,.fbx,.glb,.gltf,.stl,.dae,.3ds" class="custom-file" onchange="updateFileName(this)">

                    {{-- upload icon --}}
                    <label for="model_3d">
                        <x-icons.upload class="upload-product"/>    
                    </label>

                    {{-- show the file name --}}
                    <p id="filename" class="filename text-gray-500">Upload product image</p>
                </div>
            </div>
        </div> 
    </div>
    
    <button>Add Component</button>
</form>
@props(['cpuSpecs'])

<div class="relative !m-0">
    <h2 class="text-center w-[100%]">
        EDIT
        <x-icons.close class="close" @click="showEditModal = false"/>    
    </h2>
</div>

<form x-bind:action="'/staff/component-details/cpu/' + selectedComponent.id" method="POST" class="new-component-form" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="_method" value="PUT">
    <div class="form-container">
        {{-- SPECS --}}
        <div class="form-divider">
            <div>
                <label for="">Supplier</label>
                <select required name="supplier_id"  x-model="selectedComponent.supplier_id" class="supplier-select">
                    <option disabled selected hidden value="">Select a supplier</option>
                    @foreach ($cpuSpecs['suppliers'] as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="">Build Category</label>
                <select required name="build_category_id" id="build_category_id" x-model="selectedComponent.build_category_id">
                    <option disabled selected hidden value="">Select build category</option>   
                    @foreach ($cpuSpecs['buildCategories'] as $buildCategory)
                        <option value="{{ $buildCategory->id }}">{{ $buildCategory->name }}</option>
                    @endforeach 
                </select>  
            </div>
            <div>
                <label for="">Brand</label>
                <select required name="brand" x-model="selectedComponent.brand">
                    <option disabled selected hidden value="">Select a supplier first</option>
                    @foreach ($cpuSpecs['brands'] as $brand)
                        <option value="{{ $brand }}">{{ $brand }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="">Models</label>
                <input name="model" required type="text" x-model="selectedComponent.model" placeholder="Enter Model">
            </div>

            <div>
                <label for="">Socket Type</label>
                <select required name="socket_type" id="socket_type" x-model="selectedComponent.socket_type">
                    <option disabled selected hidden value="">Select a socket type</option>
                    @foreach ($cpuSpecs['socket_types'] as $socket_type)
                        <option value="{{ $socket_type }}">{{ $socket_type }}</option>
                    @endforeach
                </select>  
            </div>

            <div>
                <label for="">Cores</label>
                <input required name="cores" id="cores" type="number" placeholder="00" x-model="selectedComponent.cores" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>
            
            <div>
                <label for="">Threads</label>
                <input required name="threads" id="threads" type="number" placeholder="00" x-model="selectedComponent.threads" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>

            <div>
                <label for="">Base Clocks</label>
                <input required name="base_clock" id="base_clock" type="number" step="0.01" x-model="selectedComponent.base_clock" placeholder="0.00 GHz" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>

            <div>
                <label for="">Boost Clocks</label>
                <input required name="boost_clock" id="boost_clock" type="number" step="0.01" x-model="selectedComponent.boost_clock" placeholder="0.00 GHz" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>

            
        </div>

        {{-- INVENTORY --}}
        <div class="form-divider">
            <div>
                <label for="">TDP</label>
                <input required name="tdp" id="tdp" type="number" placeholder="00 W" x-model="selectedComponent.tdp" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>
            
            <div>
                <label for="">Integrated Graphics</label>
                <select required name="integrated_graphics" id="integrated_graphics" x-model="selectedComponent.integrated_graphics">
                    <option disabled selected hidden value="">Has integrated graphics</option>
                    @foreach ($cpuSpecs['integrated_displays'] as $integrated_displays)
                        <option value="{{ $integrated_displays }}">{{ $integrated_displays }}</option>
                    @endforeach
                </select>  
            </div>

            <div>
                <label for="">Generation</label>
                <select required name="generation" id="generation" x-model="selectedComponent.generation">
                    <option disabled selected hidden value="">Select generation</option>
                    @foreach ($cpuSpecs['generations'] as $generation)
                        <option value="{{ $generation }}">{{ $generation }}</option>
                    @endforeach
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
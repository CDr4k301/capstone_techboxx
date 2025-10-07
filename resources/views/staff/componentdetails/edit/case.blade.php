@props(['caseSpecs'])

<div class="relative !m-0">
    <h2 class="text-center w-[100%]">
        EDIT
        <x-icons.close class="close" @click="showEditModal = false"/>    
    </h2>
</div>

<form x-bind:action="'/staff/component-details/case/' + selectedComponent.id" method="POST" class="new-component-form" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="_method" value="PUT">
    {{-- <pre x-text="JSON.stringify(selectedComponent, null, 2)"></pre> --}}
    <div class="form-container">
        {{-- SPECS --}}
        <div class="form-divider">
            <div>
                <label for="">Supplier</label>
                <select required name="supplier_id"  x-model="selectedComponent.supplier_id" class="supplier-select">
                    <option disabled selected hidden value="">Select a supplier</option>
                    @foreach ($caseSpecs['suppliers'] as $supplier)
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
                <label for="">Form Factor Support</label>
                <select required name="form_factor_support" id="form_factor_support" x-model="selectedComponent.form_factor_support">
                    <option disabled selected hidden value="">Select a form factor support</option>
                    @foreach ($caseSpecs['form_factor_supports'] as $form_factor_support)
                        <option value="{{ $form_factor_support }}">{{ $form_factor_support }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="">Max GPU Lenght mm</label>
                <input required name="max_gpu_length_mm" id="max_gpu_length_mm" type="number" x-model="selectedComponent.max_gpu_length_mm" placeholder="Enter Max GPU Length" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>
            
            <div>
                <label for="">Max Cooler Height mm</label>
                <input required name="max_cooler_height_mm" id="max_cooler_height_mm" type="number" x-model="selectedComponent.max_cooler_height_mm" placeholder="Enter Max Cooler Height" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>

            
            

            <div class="flex flex-col"
                 x-data="{ selectedComponent: { radiator_supports; [] } }">
                <template x-for="(support, index) in selectedComponent.radiator_supports"
                          :key="index">
                    <div >
                        <label for="">Radiator Support <span x-text="index + 1"></span></label>
                        <div class="w-[80%]">
                            <select required :name="'radiator_support[' + index + '][location]'" id="radiatorlocation" x-model="support.location">
                                <option disabled selected hidden value="">Location</option>
                                @foreach ($caseSpecs['locations'] as $location)
                                    <option value="{{ $location }}">{{ $location }}</option>
                                @endforeach
                            </select>
                            <input required :name="'radiator_support[' + index + '][size_mm]'"  id="size_mm" type="number" x-model="support.size_mm"  placeholder="Size 00 mm" onkeydown="return !['e','E','+','-'].includes(event.key)">
                        </div>
                        
                        <template x-if="index > 0">
                            <button type="button"
                                class="remove-add"
                                @click="selectedComponent.radiator_supports.splice(index, 1)">
                                x
                            </button>    
                        </template>
                    </div>
                </template>
                
                {{-- ADD RADIATOR SUPPORT BUTTON --}}
                <button type="button"
                        @click="selectedComponent.radiator_supports.push({ location: '', size_mm: '' })"
                        class="add-pcie">
                    + Add Radiator Support
                </button>
            </div>
        </div>

        {{-- INVENTORY --}}
        <div class="form-divider">
            <div>
                <label for="">Fan Mount</label>
                <input required name="fan_mounts" id="fan_mounts" type="number" placeholder="00 GB" x-model="selectedComponent.fan_mounts" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>
            
            <div>
                <label for="">Drive Bay</label>
                <div class="w-[80%]">
                    <input required name='3_5_bays' id='3_5_bays' type="number" placeholder='No. of 3.5" bays' x-model="selectedComponent['3_5_bays']" onkeydown="return !['e','E','+','-'].includes(event.key)">
                    <input required name='2_5_bays' id='2_5_bays' type="number" placeholder='No. of 2.5" bays' x-model="selectedComponent['2_5_bays']" onkeydown="return !['e','E','+','-'].includes(event.key)">
                </div>
            </div>

            <div>
                <label for="">Front USB Port</label>
                <div class="w-[80%]">
                    <input required name='usb_3_0_type_A' id='usb_3_0_type_A' type="number" placeholder='USB 3.0' x-model="selectedComponent['usb_3_0_type_A']" onkeydown="return !['e','E','+','-'].includes(event.key)">
                    <input required name='usb_2_0' id='usb_2_0' type="number" placeholder='USB 2.0' x-model="selectedComponent['usb_2_0']" onkeydown="return !['e','E','+','-'].includes(event.key)">
                    <input required name='usb_c' id='usb_c' type="number" placeholder='USB-C' x-model="selectedComponent['usb_c']" onkeydown="return !['e','E','+','-'].includes(event.key)">
                    <input required name='audio_jacks' id='audio_jacks' type="number" placeholder='Audio jacks' x-model="selectedComponent['audio_jacks']" onkeydown="return !['e','E','+','-'].includes(event.key)">
                </div>
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
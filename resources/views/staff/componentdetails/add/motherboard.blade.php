@props(['moboSpecs'])
{{-- <pre>{{ json_encode($ram_type) }}</pre> --}}
<div class="flex flex-row justify-between">
    <button @click="componentModal = null; showAddModal = true;">
        <x-icons.arrow class="rotate-90 hover:opacity-50 w-[24px] h-[24px]"/>
    </button>
    <h2 class="text-center">Motherboard</h2>
    <button @click="componentModal = null; showAddModal = true;">
        <x-icons.close/>
    </button>
</div>

<form action="{{ route('staff.componentdetails.motherboard.store') }}" method="POST" class="new-component-form" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="component_type" value="motherboard">
    <div class="form-container">
        {{-- SPECS --}}
        <div class="form-divider">
            <div>
                <label for="">Supplier</label>
                <select required name="supplier_id" class="supplier-select">
                    <option disabled selected hidden value="">Select a supplier</option>
                    @foreach ($moboSpecs['suppliers'] as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="">Build Category</label>
                <select required name="build_category_id" id="build_category_id">
                    <option disabled selected hidden value="">Select build category</option>   
                    @foreach ($moboSpecs['buildCategories'] as $buildCategory)
                        <option value="{{ $buildCategory->id }}">{{ $buildCategory->name }}</option>
                    @endforeach 
                </select>  
            </div>
            <div>
                <label for="">Brand</label>
                <input name="brand" required type="text" placeholder="Enter Brand">
            </div>
            <div>
                <label for="">Model</label>
                <input name="model" type="text" placeholder="Enter model" required>
            </div>
            <div>
                <label for="">Socket Types</label>
                <select name="socket_type" id="socket_type">
                    <option disabled selected hidden value="">Select a socket type</option>
                    @foreach ($moboSpecs['socket_types'] as $socket_type)
                        <option value="{{ $socket_type }}">{{ $socket_type }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="">Chipset</label>
                <select name="chipset" id="chipset">
                    <option disabled selected hidden value="">Select a chipset</option>
                    @foreach ($moboSpecs['chipsets'] as $chipset)
                        <option value="{{ $chipset }}">{{ $chipset }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="">Form Factor</label>
                <select name="form_factor" id="form_factor">
                    <option disabled selected hidden value="">Select a form factor</option>
                    @foreach ($moboSpecs['form_factors'] as $form_factor)
                        <option value="{{ $form_factor }}">{{ $form_factor }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="">RAM Type</label>
                <select name="ram_type" id="ram_type">
                    <option disabled selected hidden value="">Select a ram type</option>
                    @foreach ($moboSpecs['ram_types'] as $ram_type)
                        <option value="{{ $ram_type }}">{{ $ram_type }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="">Max RAM</label>
                <input required name="max_ram" id="max_ram" type="number" placeholder="00 GB" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>
            <div>
                <label for="">RAM Slots</label>
                <input required name="ram_slots" id="ram_slots" type="number" placeholder="No. of ram slots" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>
            <div>
                <label for="">Max RAM Speed</label>
                <input required name="max_ram_speed" id="max_ram_speed" type="number" placeholder="000 MHz" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>            
            <div class="flex flex-col"
                x-data="{ slots:[{}] }">
                    <template x-for="(slot, index) in slots" 
                            :key="index">
                        <div>
                            <label for="">Supported CPU <span x-text="index + 1"></span></label>
                            <select required :name="'supported_cpu[]'" id="supported_cpu">
                                <option disabled selected hidden value="">Select Compatible CPU</option>
                                @foreach ($moboSpecs['supported_CPUs'] as $supported_cpu)
                                    <option value="{{ $supported_cpu }}">{{ $supported_cpu }}</option>
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
                        + Add CPU
                    </button>
                </div>



        </div>

        {{-- INVENTORY --}}
        <div class="form-divider">
            <div>
                <label for="">PCIe Slots</label>
                <input required name="pcie_slots" id="pcie_slots" type="number" placeholder="No. of pcie slots" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>
            <div>
                <label for="">M2 Slots</label>
                <input required name="m2_slots" id="m2_slots" type="number" placeholder="No. of m2 slots" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>
            <div>
                <label for="">Sata Ports</label>
                <input required name="sata_ports" id="sata_ports" type="number" placeholder="No. of sata ports" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>
            <div>
                <label for="">USB Ports</label>
                <input required name="usb_ports" id="usb_ports" type="number" placeholder="No. of usb ports" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>
            <div>
                <label for="">Wi-Fi onboard</label>
                <select name="wifi_onboard" id="wifi_onboard">
                    <option disabled selected hidden value="">Has Wi-Fi onboard</option>
                    @foreach ($moboSpecs['wifi_onboards'] as $wifi_onboard)
                        <option value="{{ $wifi_onboard }}">{{ $wifi_onboard }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="">Price</label>
                <input required name="price" id="price" type="number" step="0.01" placeholder="Enter price" onkeydown="return !['e','E','+','-'].includes(event.key)">
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

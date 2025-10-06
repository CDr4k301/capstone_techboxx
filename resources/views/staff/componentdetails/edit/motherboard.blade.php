@props(['moboSpecs'])
<div class="relative !m-0">
    <h2 class="text-center w-[100%]">
        EDIT
        <x-icons.close class="close" @click="showEditModal = false"/>    
    </h2>
</div>

<form x-bind:action="'/staff/component-details/motherboard/' + selectedComponent.id" method="POST" class="new-component-form" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="_method" value="PUT">
    <div class="form-container">
        {{-- SPECS --}}
        <div class="form-divider">
            <div>
                <label for="">Supplier</label>
                <select required name="supplier_id"  x-model="selectedComponent.supplier_id" class="supplier-select">
                    <option disabled selected hidden value="">Select a supplier</option>
                    @foreach ($moboSpecs['suppliers'] as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="">Build Category</label>
                <select required name="build_category_id" id="build_category_id" x-model="selectedComponent.build_category_id">
                    <option disabled selected hidden value="">Select build category</option>   
                    @foreach ($moboSpecs['buildCategories'] as $buildCategory)
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
                <input name="model" type="text" placeholder="Enter model" x-model="selectedComponent.model" required>
            </div>
            <div>
                <label for="">Socket Types</label>
                <select name="socket_type" id="socket_type" x-model="selectedComponent.socket_type">
                    <option disabled selected hidden value="">Select a socket type</option>
                    @foreach ($moboSpecs['socket_types'] as $socket_type)
                        <option value="{{ $socket_type }}">{{ $socket_type }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="">Chipset</label>
                <select name="chipset" id="chipset" x-model="selectedComponent.chipset"> 
                    <option disabled selected hidden value="">Select a chipset</option>
                    @foreach ($moboSpecs['chipsets'] as $chipset)
                        <option value="{{ $chipset }}">{{ $chipset }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="">Form Factor</label>
                <select name="form_factor" id="form_factor" x-model="selectedComponent.form_factor">
                    <option disabled selected hidden value="">Select a form factor</option>
                    @foreach ($moboSpecs['form_factors'] as $form_factor)
                        <option value="{{ $form_factor }}">{{ $form_factor }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="">RAM Type</label>
                <select name="ram_type" id="ram_type" x-model="selectedComponent.ram_type">
                    <option disabled selected hidden value="">Select a ram type</option>
                    @foreach ($moboSpecs['ram_types'] as $ram_type)
                        <option value="{{ $ram_type }}">{{ $ram_type }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="">Max RAM</label>
                <input required name="max_ram" id="max_ram" type="number" placeholder="00 GB" x-model="selectedComponent.max_ram" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>
            <div>
                <label for="">RAM Slots</label>
                <input required name="ram_slots" id="ram_slots" type="number" placeholder="No. of ram slots" x-model="selectedComponent.ram_slots" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>
            <div>
                <label for="">Max RAM Speed</label>
                <input required name="max_ram_speed" id="max_ram_speed" type="number" placeholder="000 MHz" x-model="selectedComponent.max_ram_speed" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>
            
            <div class="flex flex-col"
                x-data="{ slots:[{}] }">
                    <template x-for="(slot, index) in selectedComponent.supported_cpu" 
                            :key="index">
                        <div>
                            <label for="">Supported CPU <span x-text="index + 1"></span></label>
                            <select required :name="'supported_cpu[]'" id="supported_cpu" x-model="selectedComponent.supported_cpu[index]">
                                <option disabled selected hidden value="">Select Compatible CPU</option>
                                @foreach ($moboSpecs['supported_CPUs'] as $supported_cpu)
                                    <option value="{{ $supported_cpu }}">{{ $supported_cpu }}</option>
                                @endforeach
                            </select>
                            
                            <template x-if="index > 0">
                                <button type="button"
                                    class="remove-add"
                                    @click="selectedComponent.supported_cpu.splice(index, 1)">
                                    x
                                </button>    
                            </template>
                        </div>
                    </template>
                    
                    {{-- ADD SOCKET BUTTON --}}
                    <button type="button"
                            @click="selectedComponent.supported_cpu.push([])"
                            class="add-pcie">
                        + Add CPU
                    </button>
                    
            </div>
        </div>

        {{-- INVENTORY --}}
        <div class="form-divider">
            <div>
                <label for="">PCIe Slots</label>
                <input required name="pcie_slots" id="pcie_slots" type="number" placeholder="No. of pcie slots" x-model="selectedComponent.pcie_slots" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>
            <div>
                <label for="">M2 Slots</label>
                <input required name="m2_slots" id="m2_slots" type="number" placeholder="No. of m2 slots" x-model="selectedComponent.m2_slots" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>
            <div>
                <label for="">Sata Ports</label>
                <input required name="sata_ports" id="sata_ports" type="number" placeholder="No. of sata ports" x-model="selectedComponent.sata_ports" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>
            <div>
                <label for="">USB Ports</label>
                <input required name="usb_ports" id="usb_ports" type="number" placeholder="No. of usb ports" x-model="selectedComponent.usb_ports" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>
            <div>
                <label for="">Wi-Fi onboard</label>
                <select name="wifi_onboard" id="wifi_onboard" x-model="selectedComponent.wifi_onboard">
                    <option disabled selected hidden value="">Has Wi-Fi onboard</option>
                    @foreach ($moboSpecs['wifi_onboards'] as $wifi_onboard)
                        <option value="{{ $wifi_onboard }}">{{ $wifi_onboard }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="">Price</label>
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


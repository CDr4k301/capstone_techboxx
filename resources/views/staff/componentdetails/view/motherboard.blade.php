{{-- <pre x-text="JSON.stringify(selectedComponent, null, 2)"></pre> --}}
<div class="relative !m-0">
    <h2 class="text-center w-[100%]">
        VIEW
        <x-icons.close class="close" @click="showViewModal = false"/>    
    </h2>
</div>
<div class="view-container">
    {{-- IMAGE --}}
    <div class="image-container">
        <img :src="`/${selectedComponent.image}`" alt="Product Image" >
    </div>

    <div x-show="!selectedComponent.image || selectedComponent.image.length === 0">
        <p>No image uploaded.</p>
    </div>
    {{-- SPECS --}}
    <div class="specs-container">
        <div>
            <p>Brand</p>
            <p x-text="selectedComponent.brand"></p>
        </div>
        <div>
            <p>Model</p>
            <p x-text="selectedComponent.model"></p>
        </div>
        <div>
            <p>Socket Type</p>
            <p x-text="selectedComponent.socket_type"></p>
        </div>
        <div>
            <p>Chipset</p>
            <p x-text="selectedComponent.chipset"></p>
        </div>
        <div>
            <p>Form Factor </p>
            <p x-text="selectedComponent.form_factor"></p>
        </div>
        <div>
            <p>Form Factor </p>
            <p x-html="selectedComponent.cpu_display"></p>
        </div>
        <div>
            <p>RAM Type</p>
            <p x-text="selectedComponent.ram_type"></p>
        </div>
        <div>
            <p>Max RAM</p>
            <p x-text="selectedComponent.max_ram + ' GB'"></p>
        </div>
        <div>
            <p>No. of RAM Slots</p>
            <p x-text="selectedComponent.ram_slots"></p>
        </div>
        <div>
            <p>Max RAM Speed</p>
            <p x-text="selectedComponent.max_ram_speed + ' MHz'"></p>
        </div>
        <div>
            <p>No. of PCIe Slots </p>
            <p x-html="selectedComponent.pcie_slots"></p>
        </div>
        <div>
            <p>No. of M.2 Slots </p>
            <p x-html="selectedComponent.m2_slots"></p>
        </div>
        <div>
            <p>No. of SATA Ports </p>
            <p x-html="selectedComponent.sata_ports"></p>
        </div>
        <div>
            <p>No. of USB Ports </p>
            <p x-html="selectedComponent.usb_ports"></p>
        </div>
        <div>
            <p>Wi-Fi Onboard </p>
            <p x-text="selectedComponent.wifi_display"></p>
        </div>
        <div>
            <p>Price </p>
            <p x-text="selectedComponent.price_display"></p>
        </div>
        <div>
            <p>Stock </p>
            <p x-text="selectedComponent.stock"></p>
        </div>
    </div>
</div>
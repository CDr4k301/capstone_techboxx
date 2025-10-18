<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Techboxx</title>

    @vite([
        'resources\css\app.css',
        'resources\css\build.css',
        'resources\css\buildextsoft.css',
        'resources\css\buildextsoft.css',
        'resources\js\app.js',
        'resources\js\buildext.js',
        'resources\css\admin-staff\modal.css',
        ])
    
</head>
<script>
    window.selectedComponents = @json(session('selected_components', []));
</script>

<body class="flex flex-col" x-data="softwareModal()">

<script>
    window.selectedComponents = @json(session('selected_components', []));
</script>

<body class="flex flex-col" x-data="softwareModal()">
    @if (session('message'))
        <x-message :type="session('type')">
            {{ session('message') }}
        </x-message>
    @endif

    <main class="main-content header !m-0">
        <div class="ext-icons">
            @if (auth()->user() && auth()->user()->role === 'Customer')
                <button type="button" onclick="window.location='{{ route('techboxx.build.extend') }}'">
                    <x-icons.arrow class="ext-arrow"/>
                </button>
                <button @click="openModal('save')">
                    <x-icons.save class="ext-save"/>
                </button>
                <button @click="openModal('order')">
                    <x-icons.cart class="ext-cart"/>
                </button>
                <button id="reloadButton">
                    <x-icons.reload class="ext-reload" />
                </button>
            @else
                <button type="button" onclick="window.location='{{ route('techboxx.build.extend') }}'">
                    <x-icons.arrow class="build-arrow"/>
                </button>
                <button id="reloadButton">
                    <x-icons.reload class="ext-reload" />
                </button>
            @endif
        </div>
        
        <form action="" class="enter-build-name">
            <input type="text" value="YOUR PC">
        </form>

        <section class="model-section">
            <div id="canvas-container"></div>
        </section>

        <div class="layout-container">
            <div x-data="{ viewModal: false, selectedSoftware: {} }">
                <section class="software-section">
                    <label class="soft">Software Compatibility</label>
                    @foreach ($buildCategories as $category)
                        <h3>{{ $category->name }}</h3>
                        <div class="software-icons">
                            @foreach ($softwares->where('build_category_id', $category->id) as $software)
                                <div 
                                    @click="viewModal = true; selectedSoftware = {{ $software->toJson() }}"
                                    class="cursor-pointer"
                                >
                                    <img 
                                        src="{{ asset('storage/' . $software->icon) }}" 
                                        alt="{{ $software->name }}"
                                        class="hover:scale-105 transition bg-white"
                                    >
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </section>

                {{-- VIEW SOFTWARE DETAILS MODAL --}}
                <div 
                    x-show="viewModal" 
                    x-cloak 
                    x-transition 
                    class="fixed inset-0 bg-opacity-50 flex justify-center items-center overflow-y-auto z-50 p-5"
                >
                    <div 
                        class=" max-w-2xl rounded-lg shadow-lg p-6 relative"
                        @click.away="viewModal = false"
                    >
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-xl font-semibold text-white">Software Details</h2>
                            <button 
                                @click="viewModal = false"
                                class=" text-white hover:tetext-gray-600 text-2xl leading-none"
                            >&times;</button>
                        </div>

                        <div class="flex items-center gap-4 mb-6">
                            <img 
                                :src="'/storage/' + selectedSoftware.icon" 
                                alt="Software Icon" 
                                class="w-12 h-12 rounded-md object-contain shadow bg-white"
                            >
                            <h3 class="text-lg font-medium text-white" x-text="selectedSoftware.name"></h3>
                        </div>

                        <div class="mb-6">
                            <h4 class="font-semibold text-white mb-2">Minimum System Requirements</h4>
                            <div class="grid grid-cols-2 gap-y-2 text-sm text-white">
                                <p>Operating System:</p> <p x-text="selectedSoftware.os_min || '-'"></p>
                                <p>CPU:</p> <p x-text="selectedSoftware.cpu_min || '-'"></p>
                                <p>GPU:</p> <p x-text="selectedSoftware.gpu_min || '-'"></p>
                                <p>RAM:</p> <p x-text="selectedSoftware.ram_min || '-'"></p>
                                <p>Storage:</p> <p x-text="selectedSoftware.storage_min || '-'"></p>
                            </div>
                        </div>

                        <div>
                            <h4 class="font-semibold text-white mb-2">Recommended System Requirements</h4>
                            <div class="grid grid-cols-2 gap-y-2 text-sm text-white">
                                <p>CPU:</p> <p x-text="selectedSoftware.cpu_reco || '-'"></p>
                                <p>GPU:</p> <p x-text="selectedSoftware.gpu_reco || '-'"></p>
                                <p>RAM:</p> <p x-text="selectedSoftware.ram_reco || '-'"></p>
                                <p>Storage:</p> <p x-text="selectedSoftware.storage_reco || '-'"></p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button 
                                @click="checkCompatibility()"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200"
                            >
                                Check Compatibility
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            </div>

            

            {{-- COMPONENTS --}}
            {{-- <div class="component-section">
                <div class="component-section-left">
                    <x-component data-type="motherboard">Motherboard</x-component>
                    <x-component data-type="cpu">CPU</x-component>
                    <x-component data-type="gpu">GPU</x-component>
                    <x-component data-type="ram">RAM</x-component>
                </div>
                <div class="component-section-right">
                    <x-component data-type="case">Case</x-component>
                    <x-component data-type="ssd">SSD</x-component>
                    <x-component data-type="hdd">HDD</x-component>
                    <x-component data-type="cooler">Cooler</x-component>
                    <x-component data-type="psu">PSU</x-component>
                </div>
            </div> --}}
            
<div class="component-section">
    <div class="component-section-left">
        @foreach (['motherboard','cpu','gpu','ram'] as $type)
            @if(isset($selectedComponents[$type]))
                @php $component = $selectedComponents[$type]; @endphp
                <div class="component-button">
                    @if(!empty($component->image))
                        <img src="{{ asset('storage/' . $component->image) }}" alt="{{ $component->brand }} {{ $component->model }}">
                    @endif
                    <p class="component-name">{{ $component->brand }} {{ $component->model }}</p>
                </div>
            @endif
        @endforeach
    </div>

    <div class="component-section-right">
        @foreach (['case','ssd','hdd','cooler','psu'] as $type)
            @if(isset($selectedComponents[$type]))
                @php $component = $selectedComponents[$type]; @endphp
                <div class="component-button">
                    @if(!empty($component->image))
                        <img src="{{ asset('storage/' . $component->image) }}" alt="{{ $component->brand }} {{ $component->model }}">
                    @endif
                    <p class="component-name">{{ $component->brand }} {{ $component->model }}</p>
                </div>
            @endif
        @endforeach
    </div>
</div>



        </div>
    </main>
    </div>


    <script>

    const fullComponents = @json($fullComponents);

    function softwareModal() {
        return {
            viewModal: false,
            selectedSoftware: {},
            // Use fullComponents passed from the controller
            fullComponents: @json($fullComponents),

            checkCompatibility() {
                const ramSize = this.fullComponents.ram?.total_capacity_gb || 0;
                const ssdSize = this.fullComponents.ssd?.capacity_gb || 0;
                const hddSize = this.fullComponents.hdd?.capacity_gb || 0;
                const totalStorage = ssdSize + hddSize;

                const minRam = parseInt(this.selectedSoftware.ram_min || 0);
                const minStorage = parseInt(this.selectedSoftware.storage_min || 0);

                let messages = [];

                if (ramSize < minRam) {
                    messages.push(`Insufficient RAM: You have ${ramSize}GB, minimum required is ${minRam}GB.`);
                }
                if (totalStorage < minStorage) {
                    messages.push(`Insufficient Storage: You have ${totalStorage}GB, minimum required is ${minStorage}GB.`);
                }

                if (messages.length === 0) {
                    alert("✅ Your build meets the minimum requirements for this software!");
                } else {
                    alert("⚠ Compatibility Issues:\n" + messages.join("\n"));
                }
            }

        }
    }
    </script>


</body>

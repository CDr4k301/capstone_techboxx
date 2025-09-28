<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Techboxx</title>

    @vite([
        'resources\css\app.css',
        'resources\css\landingpage\header.css',
        'resources\css\build.css',
        'resources\css\admin-staff\modal.css',
        'resources\js\app.js',
        'resources\js\component-viewer.js',
        'resources\js\build.js',
        ])
    
</head>
<body x-data="{ 
    showModal: false,
    currentUser: {
        first_name: '{{ Auth::user()->first_name ?? '' }}',
        last_name: '{{ Auth::user()->last_name ?? '' }}',
        phone: '{{ Auth::user()->phone_number ?? '' }}'
    },
    selectedComponents: {},
    totalPrice: 0,
    populateModal() {
        // Copy global selectedComponents to Alpine.js reactive data
        this.selectedComponents = { ...window.selectedComponents || {} };
        
        // Calculate and set total price
        let totalPrice = 0;
        for (const [type, component] of Object.entries(this.selectedComponents)) {
            if (component && component.price) {
                totalPrice += component.price;
            }
        }
        this.totalPrice = totalPrice;
        
        // Update hidden inputs
        this.updateModalHiddenInputs();
        
        // Show the modal
        this.showModal = true;
    },
    updateModalHiddenInputs() {
        const componentTypes = ['gpu', 'motherboard', 'cpu', 'hdd', 'ssd', 'psu', 'ram', 'cooler', 'case'];
        
        componentTypes.forEach(type => {
            const component = this.selectedComponents[type];
            const hiddenInput = document.getElementById(`hidden_${type}`);
            
            if (hiddenInput && component && component.componentId) {
                hiddenInput.value = component.componentId;
            }
        });

        // Handle storage component specifically
        const storageInput = document.getElementById('hidden_storage');
        if (storageInput) {
            if (this.selectedComponents.hdd && this.selectedComponents.hdd.componentId) {
                storageInput.value = this.selectedComponents.hdd.componentId;
            } else if (this.selectedComponents.ssd && this.selectedComponents.ssd.componentId) {
                storageInput.value = this.selectedComponents.ssd.componentId;
            }
        }

        // Update total price hidden input
        const totalPriceInput = document.getElementById('hidden_total_price');
        if (totalPriceInput) {
            let totalPrice = 0;
            for (const [type, component] of Object.entries(this.selectedComponents)) {
                if (component && component.price) {
                    totalPrice += component.price;
                }
            }
            totalPriceInput.value = totalPrice.toFixed(2);
        }
    }
}"  
class="flex">

    @if (session('message'))
        <x-message :type="session('type')">
            {{ session('message') }}
        </x-message>
    @endif

    <x-buildheader :name="Auth::user()?->first_name" />
    
    <div id="loadingSpinner" class="hidden">
        <div class="spinner-message">
            <pre id="loadingText">Loading...</pre>
        </div>
    </div>

    {{-- VIEW MODAL --}}
    <div x-show="showModal" x-cloak x-transition class="modal overflow-y-scroll p-5">
        <div class="add-component" @click.away="showModal = false">
            <div class="relative !m-0">
                <h2 class="text-center w-[100%]">
                    Build Details
                    <x-icons.close class="close hover:opacity-50" @click="showModal = false"/>    
                </h2>
            </div>

            {{-- TRANSFERRED FORM LOGIC --}}
            <form action="{{ route('build.order') }}" method="POST" id="cartForm">
                @csrf
                
                {{-- Hidden inputs for component IDs --}}
                @php
                    $componentTypes = ['gpu', 'motherboard', 'cpu', 'hdd', 'ssd', 'psu', 'ram', 'cooler', 'case'];
                @endphp

                @foreach ($componentTypes as $componentType)
                    @php
                        $inputName = ($componentType === 'hdd' || $componentType === 'ssd') ? 'storage' : $componentType;
                    @endphp
                    <input type="hidden" name="component_ids[{{ $inputName }}]" id="hidden_{{ $componentType }}" value="">
                @endforeach

                {{-- Hidden input for total price --}}
                <input type="hidden" name="total_price" id="hidden_total_price" value="">

                <div class="build-details-modal">
                    <div class="build-details-header">
                        <h4>Customer Information</h4>
                    </div>
                    <div>
                        <p>Name</p>
                        <p x-text="currentUser.first_name + ' ' + currentUser.last_name"></p>
                    </div>
                    <div>
                        <p>Contact No</p>
                        <p x-text="currentUser.phone"></p>
                    </div>
                    <div>
                        <p>Build Name</p>
                        <input required type="text" name="build_name" placeholder="Enter build name" class="build-name">
                    </div>
                </div>

                <div class="build-details-modal">
                    <div class="build-details-header">
                        <h4>Selected Components</h4>
                    </div>
                    
                    {{-- Dynamic component display --}}
                    <template x-for="(component, type) in selectedComponents" :key="type">
                        <div class="component-input-group">
                            <p x-text="type.toUpperCase()" class="component-label"></p>
                            <input type="text" 
                                   :name="'components[' + type + ']'" 
                                   :value="component.name" 
                                   readonly 
                                   class="build-name-component readonly">
                            <input type="hidden" 
                                   :name="'component_ids[' + type + ']'" 
                                   :value="component.componentId">
                        </div>
                    </template>

                    {{-- Fallback if no components selected --}}
                    <div x-show="Object.keys(selectedComponents).length === 0">
                        <p class="text-gray-500">No components selected yet</p>
                    </div>
                </div>

                <div class="build-details-modal">
                    <div class="build-details-price">
                        <h4>Build Price:</h4>
                        <h4 x-text="'₱' + totalPrice.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')"></h4>
                    </div>
                </div>

                <div>
                    <h4>Payment Method</h4>
                    <div class="flex gap-2">
                        <input type="hidden" name="payment_method" id="payment_method" required>
                        <button
                            type="button"
                            onclick="selectPayment('PayPal', this)"
                            class="payment-btn px-4 py-2 rounded-lg font-semibold hover:!bg-yellow-400">
                            PayPal
                        </button>
                        <button
                            type="button"
                            onclick="selectPayment('Cash on Pickup', this)"
                            class="payment-btn px-4 py-2 rounded-lg font-semibold hover:!bg-yellow-400">
                            Cash On Pickup
                        </button>
                    </div>
                </div>

                <div class="flex justify-end mt-4">
                    <button type="submit" class="bg-blue-500 hover:!bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Order
                    </button>
                </div>
            </form>
        </div>
    </div>

    <main class="main-content flex justify-evenly h-[91vh] gap-1">
        <section class="preview-section header">
            <div class="build-icons">
                <form action="{{ route('home') }}">
                    @csrf
                    <button>
                        <x-icons.arrow class="build-arrow"/>
                    </button>
                </form>
                <form action="{{ route('home') }}">
                    @csrf
                    <button>
                        <x-icons.save class="build-save"/>
                    </button>
                </form>
                <button @click="populateModal()">
                    <x-icons.cart class="build-cart"/>
                </button>
            </div>
            <div id="sidebar">
                <h3 class="mb-3 text-center">BUILD COMPONENTS</h3>
                <div id="components">
                    <div id="gpu" class="draggable"><p>GPU</p></div>
                    <div id="motherboard" class="draggable"><p>Motherboard</p></div>
                    <div id="cpu" class="draggable"><p>CPU</p></div>
                    <div id="hdd" class="draggable"><p>HDD</p></div>
                    <div id="ssd" class="draggable"><p>SDD</p></div>
                    <div id="psu" class="draggable"><p>PSU</p></div>    
                    <div id="ram" class="draggable"><p>RAM</p></div>    
                    <div id="cooler" class="draggable"><p>Cooler</p></div>
                </div>
            </div>
            <div id="canvas-container"></div>
            <form action="{{ route('techboxx.build.extend') }}">
                <button>
                    <x-icons.expand />
                </button>
            </form>
            <button id="reloadButton">
                <x-icons.reload />
            </button>

        </section>
        <section class="buttons-section">
            <div data-group="buildType">
                <button id="customBuildBtn"><p>Custom Build</p></button>
                <button id="generateBuildBtn"><p>Generate Build</p></button>
            </div>
            <div data-group="cpuBrand">
                <button id="amdBtn"><p>AMD</p></button>
                <button id="intelBtn"><p>Intel</p></button>
            </div>
            <div data-group="useCase">
                <button id="generalUseBtn"><p>General Use</p></button>
                <button id="gamingBtn"><p>Gaming</p></button>
                <button id="graphicsIntensiveBtn"><p>Graphics Intensive</p></button>
            </div>
            <div class="budget-section">
                <button disabled><p>Budget</p></button>
                <input name="budget" id="budget" type="number" step="0.01" placeholder="Enter budget" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>
            <div class="generate-button">
                <button id="generateBtn"><p>Generate Build</p></button>
            </div>
            

            {{-- THIS SECTION WILL SHOW WHEN GENERATE BUILD IS CLICKED --}}
            <div class="generate-build hidden" id="buildSection">
                <button data-type=""><p>Chipset <span class="selected-name">None</span></p></button>
                <button data-type="case"><p>Case <span class="selected-name">None</span></p></button>
                <button data-type="gpu"><p>GPU <span class="selected-name">None</span></p></button>
                <button data-type="motherboard"><p>Motherboard <span class="selected-name">None</span></p></button>
                <button data-type="cpu"><p>CPU <span class="selected-name">None</span></p></button>
                <button data-type="hdd"><p>HDD <span class="selected-name">None</span></p></button>
                <button data-type="ssd"><p>SSD <span class="selected-name">None</span></p></button>
                <button data-type="psu"><p>PSU <span class="selected-name">None</span></p></button>
                <button data-type="ram"><p>RAM <span class="selected-name">None</span></p></button>
                <button data-type="cooler"><p>Cooler <span class="selected-name">None</span></p></button>
            </div>
        </section>   
        <section class="catalog-section">
            <div class="catalog-button">
                <button id="componentsTab">Components</button>
                <button id="summaryTab">Summary</button>
            </div>

            {{-- COMPONENTS --}}
            <div id="componentsSection">
                <div class="catalog-header">
                    <div class="catalog-title">
                        <p id="catalogTitle">All Components</p>
                        <x-icons.info title="This is information about the processor"/>
                    </div> 
                    <div class="catalog-filter">
                        <button><p>filter</p></button>
                        <button><p>filter</p></button>    
                    </div>
                </div>
                <div class="catalog-list"> 
                    @foreach ($components as $component)
                        <div class="catalog-item" 
                             data-type="{{ strtolower($component->component_type) }}"
                             data-name="{{ ucfirst($component->brand )}} {{ ucfirst($component->model )}}"
                             data-category="{{ $component->buildCategory->name}}"
                             data-price="{{ $component->price }}"
                             data-image="{{ asset('storage/' . $component->image) }}"
                             data-model="{{ isset($component->model_3d) ? asset('storage/' . $component->model_3d) : '' }}"
                             data-id="{{ $component->id }}">
                            <div class="catalog-image">
                                @if (!empty($component->image))
                                    <img src="{{ asset('storage/' . $component->image )}}" alt="Product image">
                                @else
                                    <p>No image uploaded.</p>
                                @endif
                            </div>
                            <div class="catalog-specs">
                                <p>{{ ucfirst($component->component_type) }}</p>
                                <p><strong>{{ $component->brand}} {{ $component->model }}</strong></p>
                                <p>₱{{ number_format($component->price, 2) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>    
            </div>
            
            {{-- SUMMARY --}}
            <div class="summary-section hidden" id="summarySection">
                <div class="summary-date">
                    <p>Build Date: <span id="buildDate">01/01/2025 </span></p>
                </div>
                <div class="summary-table">
                    <table>
                        <thead>
                            <tr>
                                {{-- <th><p>ID</p></th> --}}
                                <th><p>Components</p></th>
                                <th><p>Quantity</p></th>
                                <th><p>Price</p></th>
                            </tr>
                        </thead>

                        <tbody id="summaryTableBody">
                        </tbody>
                    </table>
                </div>
                <div class="build-details">
                </div>
            </div>
        </section>    
    </main>

    <script>
        function selectPayment(method, button) {
            console.log('Selecting payment:', method);
            
            // Reset all buttons to gray
            document.querySelectorAll('.payment-btn').forEach(btn => {
                btn.style.backgroundColor = '#e5e7eb'; // gray-200
                btn.style.color = '#374151'; // gray-700
                btn.style.border = '2px solid transparent';
            });
            
            // Style clicked button as active
            button.style.backgroundColor = '#fbbf24'; // yellow-400
            button.style.color = '#1f2937'; // gray-900
            button.style.border = '2px solid #f59e0b'; // yellow-500 border
            
            // Set hidden input
            document.getElementById('payment_method').value = method;
            console.log('Payment method set to:', document.getElementById('payment_method').value);
        }

        // Initialize payment buttons on load
        document.addEventListener('DOMContentLoaded', function() {
            // Ensure all payment buttons have initial gray style
            document.querySelectorAll('.payment-btn').forEach(btn => {
                if (!btn.style.backgroundColor) {
                    btn.style.backgroundColor = '#e5e7eb';
                    btn.style.color = '#374151';
                }
            });
        });
    </script>
</body>
</html>
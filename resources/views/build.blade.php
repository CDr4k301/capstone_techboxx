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
    modalType: 'order', // 'order' or 'save'
    currentUser: {
        first_name: '{{ Auth::user()->first_name ?? '' }}',
        last_name: '{{ Auth::user()->last_name ?? '' }}',
        phone: '{{ Auth::user()->phone_number ?? '' }}'
    },
    selectedPayment: '',
    selectedComponents: {},
    totalPrice: 0,
    compatibilityResults: null,
    
    // Open modal for specific type
    async openModal(type) {
        this.modalType = type;
        this.selectedPayment = ''; // Reset payment selection
        
        // Check compatibility before showing modal
        const isCompatible = await this.checkCompatibility();
        if (isCompatible) {
            this.populateModal();
            this.showModal = true;
        } else {
            this.showCompatibilityAlert();
        }
    },
    
    // Check compatibility with selected components
    async checkCompatibility() {
        const componentSelections = {};
        
        for (const [type, component] of Object.entries(window.selectedComponents || {})) {
            if (component && component.componentId) {
                componentSelections[type + '_id'] = component.componentId;
            }
        }
        
        // Check if we have enough components to validate
        if (Object.keys(componentSelections).length === 0) {
            alert('⚠️ No components selected.\nPlease choose at least one component before ordering.');
            return false;
        }
        
        try {
            const response = await fetch('/techboxx/build/validate', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-CSRF-TOKEN': document.querySelector(`meta[name='csrf-token']`).content
                },
                body: JSON.stringify(componentSelections)
            });
            
            const data = await response.json();
            this.compatibilityResults = data;
            return !data.errors || data.errors.length === 0;
            
        } catch (error) {
            console.error('Compatibility check failed:', error);
            return true;
        }
    },
    
    showCompatibilityAlert() {
        let message = '❌ Compatibility Issues Found:\n\n';
        
        if (this.compatibilityResults.errors && this.compatibilityResults.errors.length > 0) {
            message += 'Errors:\n' + this.compatibilityResults.errors.join('\n') + '\n\n';
        }
        
        if (this.compatibilityResults.warnings && this.compatibilityResults.warnings.length > 0) {
            message += 'Warnings:\n' + this.compatibilityResults.warnings.join('\n') + '\n\n';
        }
        
        message += 'Please fix these issues before proceeding with your order.';
        alert(message);
    },
    
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

        // Handle storage components specifically - both HDD and SSD become 'storage'
        const storageInput = document.getElementById('hidden_storage');
        if (storageInput) {
            // Clear storage input first
            storageInput.value = '';
            
            // Prioritize SSD over HDD if both are selected
            if (this.selectedComponents.ssd && this.selectedComponents.ssd.componentId) {
                storageInput.value = this.selectedComponents.ssd.componentId;
                console.log('Storage set to SSD:', this.selectedComponents.ssd.componentId);
            } else if (this.selectedComponents.hdd && this.selectedComponents.hdd.componentId) {
                storageInput.value = this.selectedComponents.hdd.componentId;
                console.log('Storage set to HDD:', this.selectedComponents.hdd.componentId);
            }
            
            // Clear the individual hdd/ssd hidden inputs since we're using storage now
            const hddInput = document.getElementById('hidden_hdd');
            const ssdInput = document.getElementById('hidden_ssd');
            if (hddInput) hddInput.value = '';
            if (ssdInput) ssdInput.value = '';
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
    },
    
    // NEW: Form submission handler
    submitForm(event) {
        console.log('=== FORM SUBMISSION STARTED ===');
        
        // Prevent default immediately
        event.preventDefault();
        
        // Validate build name
        const buildNameInput = document.querySelector(`input[name='build_name']`);
        const buildName = buildNameInput ? buildNameInput.value : '';
        if (!buildName.trim()) {
            alert('Please enter a build name.');
            return false;
        }
        
        // Validate components
        if (Object.keys(this.selectedComponents).length === 0) {
            alert('Please select at least one component.');
            return false;
        }
        
        // Validate all required components are selected
        const requiredComponents = ['gpu', 'motherboard', 'cpu', 'psu', 'ram', 'cooler', 'case'];
        const missingComponents = [];
        
        requiredComponents.forEach(type => {
            if (!this.selectedComponents[type] || !this.selectedComponents[type].componentId) {
                const componentNames = {
                    'gpu': 'GPU',
                    'motherboard': 'Motherboard',
                    'cpu': 'CPU',
                    'psu': 'Power Supply',
                    'ram': 'RAM',
                    'cooler': 'Cooler',
                    'case': 'Case'
                };
                missingComponents.push(componentNames[type]);
            }
        });
        
        // Check storage
        if (!this.selectedComponents.ssd?.componentId && !this.selectedComponents.hdd?.componentId) {
            missingComponents.push('Storage (HDD or SSD)');
        }
        
        if (missingComponents.length > 0) {
            alert(`Please select the following components:\n\n${missingComponents.join('\n')}`);
            return false;
        }
        
        // Check compatibility errors
        if (this.compatibilityResults && this.compatibilityResults.errors && this.compatibilityResults.errors.length > 0) {
            alert('Please fix compatibility issues before proceeding.');
            return false;
        }
        
        console.log('=== ALL VALIDATIONS PASSED - SUBMITTING FORM ===');
        
        // Update hidden inputs one final time
        this.updateModalHiddenInputs();
        
        // Submit the form programmatically
        const form = event.target;
        console.log('Submitting form to:', form.action);
        form.submit();
    },
    
    // Computed properties for dynamic content
    get modalTitle() {
        return this.modalType === 'order' ? 'Order Build' : 'Save Build';
    },
    
    get submitButtonText() {
        return this.modalType === 'order' ? 'Order' : 'Save Build';
    },
    
    get formAction() {
        return this.modalType === 'order' ? '{{ route('build.order') }}' : '{{ route('build.save') }}';
    }
}"  
class="flex">

    @if (session('message'))
        <x-message :type="session('type')">
            {{ session('message') }}
        </x-message>
    @endif

    <x-buildheader :name="Auth::user()?->first_name" />
    
    <div id="loadingSpinner" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-2xl p-8 max-w-md mx-4">
            <div class="flex flex-col items-center">
                <!-- Spinner -->
                <div class="w-16 h-16 border-4 border-blue-100 border-t-blue-600 rounded-full animate-spin mb-6"></div>
                
                <!-- Loading message -->
                <div class="text-center">
                    <pre id="loadingText" class="text-gray-800 font-semibold whitespace-pre-wrap text-base bg-blue-50 p-4 rounded-lg border border-blue-100 mb-4"></pre>
                </div>
                
                <p class="text-gray-600 text-sm">Generating your perfect build...</p>
            </div>
        </div>
    </div>

    {{-- SINGLE ADAPTIVE MODAL --}}
    <div x-show="showModal" x-cloak x-transition class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black bg-opacity-50 backdrop-blur-sm">
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto" @click.away="showModal = false">
            {{-- Header --}}
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 rounded-t-2xl z-10">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-bold text-gray-800" x-text="modalTitle"></h2>
                    <button @click="showModal = false" class="p-2 hover:bg-gray-100 rounded-full transition-colors duration-200">
                        <x-icons.close class="w-6 h-6 text-gray-500 hover:text-gray-700" />
                    </button>
                </div>
                
                {{-- Compatibility Status Display --}}
                <div x-show="compatibilityResults" class="mt-3">
                    <template x-if="compatibilityResults.errors && compatibilityResults.errors.length > 0">
                        <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                            <div class="flex items-center">
                                <span class="text-red-800 font-semibold">❌ Compatibility Issues Found</span>
                            </div>
                            <ul class="text-red-700 text-sm mt-1 list-disc list-inside">
                                <template x-for="error in compatibilityResults.errors" :key="error">
                                    <li x-text="error"></li>
                                </template>
                            </ul>
                        </div>
                    </template>
                    
                    <template x-if="(!compatibilityResults.errors || compatibilityResults.errors.length === 0) && compatibilityResults.warnings && compatibilityResults.warnings.length > 0">
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                            <div class="flex items-center">
                                <span class="text-yellow-800 font-semibold">⚠️ Compatibility Warnings</span>
                            </div>
                            <ul class="text-yellow-700 text-sm mt-1 list-disc list-inside">
                                <template x-for="warning in compatibilityResults.warnings" :key="warning">
                                    <li x-text="warning"></li>
                                </template>
                            </ul>
                        </div>
                    </template>
                    
                    <template x-if="(!compatibilityResults.errors || compatibilityResults.errors.length === 0) && (!compatibilityResults.warnings || compatibilityResults.warnings.length === 0)">
                        <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                            <div class="flex items-center">
                                <span class="text-green-800 font-semibold">✅ All Components Compatible</span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- DYNAMIC FORM --}}
            <form :action="formAction" method="POST" id="cartForm" class="p-6 space-y-6" @submit.prevent="submitForm($event)">
                @csrf
                
                {{-- Hidden inputs for component IDs --}}
                @php
                    $componentTypes = ['gpu', 'motherboard', 'cpu', 'storage', 'psu', 'ram', 'cooler', 'case'];
                @endphp

                @foreach ($componentTypes as $componentType)
                    <input type="hidden" name="component_ids[{{ $componentType }}]" id="hidden_{{ $componentType }}" value="">
                @endforeach

                {{-- Hidden input for total price --}}
                <input type="hidden" name="total_price" id="hidden_total_price" value="">

                {{-- Customer Information --}}
                <div class="bg-gray-50 rounded-xl p-6 space-y-4">
                    <div class="border-b border-gray-200 pb-3">
                        <h4 class="text-lg font-semibold text-gray-800">Customer Information</h4>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Name</p>
                            <p class="text-gray-800 font-semibold" x-text="currentUser.first_name + ' ' + currentUser.last_name"></p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600">Contact No</p>
                            <p class="text-gray-800 font-semibold" x-text="currentUser.phone"></p>
                        </div>
                    </div>
                    <div>
                        <label for="build_name" class="block text-sm font-medium text-gray-700 mb-2">Build Name</label>
                        <input 
                            required 
                            type="text" 
                            name="build_name" 
                            id="build_name"
                            placeholder="Enter build name" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                        >
                    </div>
                </div>

                {{-- Selected Components --}}
                <div class="bg-gray-50 rounded-xl p-6 space-y-4">
                    <div class="border-b border-gray-200 pb-3">
                        <h4 class="text-lg font-semibold text-gray-800">Selected Components</h4>
                    </div>
                    
                    {{-- Dynamic component display --}}
                    <template x-for="(component, type) in selectedComponents" :key="type">
                        <div class="flex items-center justify-between bg-white rounded-lg border border-gray-200 p-4">
                            <div class="flex-1">
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide" x-text="type"></p>
                                <p class="text-gray-800 font-semibold" x-text="component.name"></p>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-green-600" x-text="'₱' + component.price.toFixed(2)"></p>
                            </div>
                            <input type="hidden" 
                                :name="'component_ids[' + type + ']'" 
                                :value="component.componentId">
                        </div>
                    </template>

                    {{-- Fallback if no components selected --}}
                    <div x-show="Object.keys(selectedComponents).length === 0" class="text-center py-8">
                        <p class="text-gray-500 text-lg">No components selected yet</p>
                        <p class="text-gray-400 text-sm mt-2">Please select components from the catalog</p>
                    </div>
                </div>

                {{-- Total Price --}}
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-200">
                    <div class="flex justify-between items-center">
                        <h4 class="text-xl font-bold text-gray-800">Total Build Price:</h4>
                        <h4 class="text-2xl font-bold text-green-600" x-text="'₱' + totalPrice.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')"></h4>
                    </div>
                </div>

                {{-- PAYMENT METHOD - ONLY FOR ORDER --}}
                <div x-show="modalType === 'order'" class="bg-gray-50 rounded-xl p-6 space-y-4">
                    <h4 class="text-lg font-semibold text-gray-800">Payment Method</h4>
                    <div class="flex gap-3">
                        <input type="hidden" name="payment_method" id="payment_method" required>
                        <button
                            type="button"
                            onclick="selectPayment('PayPal', this)"
                            class="payment-btn flex-1 bg-gray-200 text-gray-800 font-semibold py-3 px-4 rounded-lg border-2 border-transparent hover:bg-yellow-400 hover:border-yellow-500 transition-all duration-200 transform hover:scale-105">
                            PayPal
                        </button>
                        <button
                            type="button"
                            onclick="selectPayment('Cash on Pickup', this)"
                            class="payment-btn flex-1 bg-gray-200 text-gray-800 font-semibold py-3 px-4 rounded-lg border-2 border-transparent hover:bg-yellow-400 hover:border-yellow-500 transition-all duration-200 transform hover:scale-105">
                            Cash On Pickup
                        </button>
                    </div>
                </div>

                {{-- UPDATED: Submit Button with Compatibility Check --}}
                <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                    <div x-show="compatibilityResults && compatibilityResults.errors && compatibilityResults.errors.length > 0" 
                         class="text-red-600 text-sm font-medium">
                        ❌ Fix compatibility issues to proceed
                    </div>
                    <div class="flex gap-3 ml-auto">
                        <button 
                            type="button" 
                            @click="showModal = false"
                            class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-3 px-6 rounded-lg transition-colors duration-200">
                            Cancel
                        </button>
                        <button 
                            type="submit" 
                            :class="{
                                'bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700': !(compatibilityResults && compatibilityResults.errors && compatibilityResults.errors.length > 0),
                                'bg-gray-400 cursor-not-allowed': compatibilityResults && compatibilityResults.errors && compatibilityResults.errors.length > 0
                            }"
                            class="text-white font-bold py-3 px-8 rounded-lg shadow-lg transition-all duration-200 transform hover:scale-105 focus:ring-4 focus:ring-blue-300 focus:ring-opacity-50"
                            x-text="submitButtonText">
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <main class="main-content flex justify-evenly h-[91vh] ">
        <section class="preview-section header">
           <div class="build-icons">
                @if (auth()->user() && auth()->user()->role === 'Customer')
                    <form action="{{ route('home') }}">
                        {{-- @csrf --}}
                        <button>
                            <x-icons.arrow class="build-arrow"/>
                        </button>
                    </form>
                    <button @click="openModal('save')">
                        <x-icons.save class="build-save"/>
                    </button>
                    <button @click="openModal('order')">
                        <x-icons.cart class="build-cart"/>
                    </button>
                @elseif (!auth()->check())
                    <form action="{{ route('home') }}">
                        <button>
                            <x-icons.arrow class="build-arrow"/>
                        </button>
                    </form>
                @else
                @endif
            </div>
            <div id="sidebar">
                <h3 class="mb-3 text-center">BUILD COMPONENTS</h3>
                <div id="components">
                    <div id="motherboard" class="draggable"><p>Motherboard</p></div>
                    <div id="cpu" class="draggable"><p>CPU</p></div>
                    <div id="ram" class="draggable"><p>RAM</p></div>    
                    <div id="ssd" class="draggable"><p>SDD</p></div>
                    <div id="hdd" class="draggable"><p>HDD</p></div>
                    <div id="cooler" class="draggable"><p>Cooler</p></div>
                    <div id="gpu" class="draggable"><p>GPU</p></div>
                    <div id="psu" class="draggable"><p>PSU</p></div>   
                </div>
            </div>
            <div id="canvas-container"></div>
            <form action="{{ route('techboxx.build.extend') }}">
                <button>
                    <x-icons.expand />
                </button>
            </form>
            <button id="reloadButton">
                <x-icons.reload class="reload"/>
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
                    <button data-type="case"><p>Case <span class="selected-name">None</span></p></button>
                    <button data-type="motherboard"><p>Motherboard <span class="selected-name">None</span></p></button>
                    <button data-type="cpu"><p>CPU <span class="selected-name">None</span></p></button>
                    <button data-type="ram"><p>RAM <span class="selected-name">None</span></p></button>
                    <button data-type="ssd"><p>SSD <span class="selected-name">None</span></p></button>
                    <button data-type="hdd"><p>HDD <span class="selected-name">None</span></p></button>
                    <button data-type="cooler"><p>Cooler <span class="selected-name">None</span></p></button>
                    <button data-type="gpu"><p>GPU <span class="selected-name">None</span></p></button>
                    <button data-type="psu"><p>PSU <span class="selected-name">None</span></p></button>
                </div>

            
        </section>   
        <section class="catalog-section">
            <div class="catalog-button">
                <button id="componentsTab"  class="active">Components</button>
                <button id="summaryTab">Summary</button>
                <button id="remarksTab">Remarks</button>
            </div>

            {{-- COMPONENTS --}}
            <div id="componentsSection">
                <div class="catalog-header">
                    <div class="catalog-title">
                        <p id="catalogTitle">All Components</p>
                        <x-icons.info title="This is information about the processor"/>
                    </div> 
                    {{-- FILTER --}}
                    <div class="search-area">
                        <form action=" {{ route('techboxx.search') }}" method="POST"
                            class="component-search">
                            @csrf
                            <input 
                                type="text"
                                name="search"
                                placeholder="Search components"
                                value="{{ request('search') }}"
                                
                            >
                            <button type='submit'>
                                <x-icons.search class="component-search-icon"/>
                            </button>
                        </form>
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

            {{-- REMARKS --}}
            <div class="summary-section hidden" id="remarksSection">
                <div class="remarks-content bg-white rounded-lg border border-gray-200 h-full flex flex-col" id="buildRemarks">
                    <!-- Default state when no remarks are available -->
                    <div class="flex flex-col items-center justify-center py-12 text-center flex-1">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-600 mb-2">No Analysis Available</h3>
                        <p class="text-gray-500 text-sm max-w-sm">
                            Generate a build with a budget to see detailed analysis, performance insights, and smart recommendations.
                        </p>
                    </div>
                </div>
            </div>
        </section>    
    </main>
</body>
</html>
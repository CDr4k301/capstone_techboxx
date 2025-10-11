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
        'resources\css\buildext.css',
        'resources\js\app.js',
        'resources\js\buildext.js',
        'resources\css\admin-staff\modal.css',
        ])
    
</head>
<body class="flex flex-col"
      x-data="{ 
          showViewModal: false, 
          selectedComponent: {},
          showModal: false,
          modalType: 'order', // 'order' or 'save'
          buildName: 'YOUR PC',
          currentUser: {
              first_name: '{{ Auth::user()->first_name ?? '' }}',
              last_name: '{{ Auth::user()->last_name ?? '' }}',
              phone: '{{ Auth::user()->phone_number ?? '' }}'
          },
          selectedComponents: {},
          totalPrice: 0,
          
          // Open modal for specific type
          openModal(type) {
              this.modalType = type;
              this.populateModal();
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
      }">
    @if (session('message'))
        <x-message :type="session('type')">
            {{ session('message') }}
        </x-message>
    @endif

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
            </div>

            {{-- DYNAMIC FORM --}}
            <form :action="formAction" method="POST" id="cartForm" class="p-6 space-y-6">
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
                            x-model="buildName"
                            class="text-black w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
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

                {{-- Submit Button --}}
                <div class="flex justify-end pt-4 border-t border-gray-200">
                    <button 
                        type="submit" 
                        class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold py-3 px-8 rounded-lg shadow-lg transition-all duration-200 transform hover:scale-105 focus:ring-4 focus:ring-blue-300 focus:ring-opacity-50"
                        x-text="submitButtonText">
                    </button>
                </div>
            </form>
        </div>
    </div>

    <main class="main-content header !m-0">
        <div class="ext-icons">
            @if (auth()->user() && auth()->user()->role === 'Customer')
                <form action="{{ route('home') }}">
                    <button>
                        <x-icons.arrow class="ext-arrow"/>
                    </button>
                </form>
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
                <form action="{{ route('techboxx.build') }}">
                    @csrf
                    <button>
                        <x-icons.arrow class="build-arrow"/>
                    </button>
                </form>
                <button id="reloadButton">
                    <x-icons.reload class="ext-reload" />
                </button>
            @endif
        </div>
        
        <form class="enter-build-name">
            <input type="text" value="YOUR PC" x-model="buildName">
        </form>

        <section class="model-section">
            <div id="canvas-container"></div>
        </section>

        <div class="layout-container">
            {{-- STEPS --}}
            <section class="steps-section">
                <div>
                    <h3>VIRTUAL PC BUILD GUIDE</h3>
                    <p>1. Install the Motherboard</p>
                    <p>2. Install the CPU</p>
                    <p>3. Install the RAM</p>
                    <p>4. Install the SSD</p>
                    <p>5. Install the HDD</p>
                    <p>6. Install the CPU Cooler</p>
                    <p>7. Install the GPU</p>
                    <p>8. Install the PSU</p>
                    <p>9. Power On</p>
                </div>
            </section>

            {{-- COMPATIBILITY --}}
            <section class="compatibility-section">
                <div>
                    <h4>COMPATIBILITY CHECK</h4>
                    <button id="validateBuild">Validate Build</button>
                </div>
            </section>

            {{-- COMPONENTS --}}
            
        </div>
    </main>
    <section class="catalog-wrapper">
        <div class="slide-container">
            <div class="component-section">
                <x-icons.arrow class="component-arrow" />
                <x-component data-type="case">Case</x-component>
                <x-component data-type="motherboard">Motherboard</x-component>
                <x-component data-type="cpu">CPU</x-component>
                <x-component data-type="ram">RAM</x-component>
                <x-component data-type="ssd">SSD</x-component>
                <x-component data-type="hdd">HDD</x-component>
                <x-component data-type="cooler">Cooler</x-component>
                <x-component data-type="gpu">GPU</x-component>
                <x-component data-type="psu">PSU</x-component>
            </div>

            <div class="catalog-section" id="catalogSection">
                @foreach ($components as $component)
                    <x-buildcatalog :component="$component"/>
                @endforeach
            </div>
        </div>
    </section>
    <div x-show="showViewModal" x-cloak x-transition class="modal view-specs modal-scroll">
        <div class="view-component" @click.away="showViewModal = false">
            <div x-show="selectedComponent.component_type === 'motherboard'">
                @include('staff.componentdetails.view.motherboard')
            </div>

            <div x-show="selectedComponent.component_type === 'gpu'">
                @include('staff.componentdetails.view.gpu')
            </div>

            <div x-show="selectedComponent.component_type === 'case'">
                @include('staff.componentdetails.view.case')
            </div>

            <div x-show="selectedComponent.component_type === 'psu'">
                @include('staff.componentdetails.view.psu')
            </div>

            <div x-show="selectedComponent.component_type === 'ram'">
                @include('staff.componentdetails.view.ram')
            </div>

            <div x-show="selectedComponent.component_type === 'ssd' || selectedComponent.component_type === 'hdd'">
                @include('staff.componentdetails.view.storage')
            </div>

            <div x-show="selectedComponent.component_type === 'cpu'">
                @include('staff.componentdetails.view.cpu')
            </div>

            <div x-show="selectedComponent.component_type === 'cooler'">
                @include('staff.componentdetails.view.cooler')
            </div>
        </div>
    </div>
</body>

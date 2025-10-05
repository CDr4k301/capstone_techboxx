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
      x-data="{ showViewModal: false, selectedComponent:{} }">
    @if (session('message'))
        <x-message :type="session('type')">
            {{ session('message') }}
        </x-message>
    @endif

    {{-- <header>
        <div class="header-logo">
            <img src="{{ asset('images\Logo.png') }}" alt="Logo" class="logo">
            <a href="{{ route('home') }}"><h2>Madoxx.qwe</h2></a>  
        </div>
    </header> --}}

    <main class="main-content header !m-0">
        <div class="ext-icons">
            <form action="{{ route('home') }}">
                @csrf
                <button>
                    <x-icons.arrow class="build-arrow"/>
                </button>
            </form>
            <form>
                @csrf
                <button>
                    <x-icons.save class="ext-save"/>
                </button>
            </form>
            <button>
                <x-icons.cart class="ext-cart"/>
            </button>
            <button id="reloadButton">
                <x-icons.reload />
            </button>
        </div>
        
        <form action="" class="enter-build-name">
            <input type="text" value="YOUR PC">
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
        </div>
    </main>
    {{-- COMPONENTS --}}
    <section class="catalog-wrapper">
        <div class="slide-container">
            <div class="component-section">
                    <x-component data-type="case">Case</x-component>
                    <x-component data-type="motherboard">Motherboard</x-component>
                    <x-component data-type="cpu">CPU</x-component>
                    <x-component data-type="ram">RAM</x-component>
                    <x-component data-type="ssd">SSD</x-component>
                    <x-icons.arrow class="component-arrow" />
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
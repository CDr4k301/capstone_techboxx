{{-- <html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
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

    <main class="main-content header !m-0">
        <div class="ext-icons">
            @if (auth()->user() && auth()->user()->role === 'Customer')
                <form action="{{ route('home') }}">
                    @csrf
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
        
        <form action="" class="enter-build-name">
            <input type="text" value="YOUR PC">
        </form>

        <section class="model-section">
            <div id="canvas-container"></div>
        </section>

        <div class="layout-container">
            <section class="software-section">
                <label for="" class="soft">Software Compatibility</label>
                <div class="category">
                    <label for="">General Use</label>
                    <label for="">Gaming</label>
                    <label for="">Graphic Use</label>
                </div>
            </section> --}}
            

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
            </div>
                
        </div>
    </main>
    </div>
</body> --}}

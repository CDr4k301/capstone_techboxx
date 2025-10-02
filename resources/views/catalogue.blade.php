<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalogue - Techboxx</title>

    @vite([
        'resources/css/app.css',
        'resources/css/landingpage/header.css',
        'resources/js/app.js',
    ])
</head>
<body class="flex">

    @if (session('message'))
        <x-message :type="session('type')">
            {{ session('message') }}
        </x-message>
    @endif

    <!-- Fixed landing header -->
    <x-landingheader :name="Auth::user()?->first_name" />

    <main class="main-content">
        <!-- Top Nav Tabs (optional filter shortcuts) -->
        <div class="w-full border-b bg-white shadow-sm">
            <div class="flex justify-center items-center gap-8 py-4 text-sm font-semibold">

                <!-- ALL -->
                <a href="{{ route('catalogue') }}"
                class="{{ !request('sort') ? 'text-blue-500 font-bold underline' : 'text-blue-500' }} hover:underline">
                ALL
                </a>

                <!-- NEW IN -->
                <a href="{{ route('catalogue', ['sort' => 'newest']) }}"
                class="{{ request('sort') === 'newest' ? 'text-[#F17720] font-bold underline' : 'text-[#F17720]' }} hover:underline flex items-center gap-1">
                NEW IN <x-icons.sparkle/>
                </a>

                <!-- HOT -->
                <a href="{{ route('catalogue', ['sort' => 'price_desc']) }}"
                class="{{ request('sort') === 'price_desc' ? 'text-[#FF6B6B] font-bold underline' : 'text-[#FF6B6B]' }} hover:underline flex items-center gap-1">
                HOT <x-icons.fire/>
                </a>

                <!-- RECENT -->
                <a href="{{ route('catalogue', ['sort' => 'recent']) }}"
                class="{{ request('sort') === 'recent' ? 'text-[#50C878] font-bold underline' : 'text-[#50C878]' }} hover:underline flex items-center gap-1">
                RECENT <x-icons.recent/>
                </a>

                <!-- POPULAR -->
                <a href="{{ route('catalogue', ['sort' => 'name_asc']) }}"
                class="{{ request('sort') === 'name_asc' ? 'text-[#FFD700] font-bold underline' : 'text-[#FFD700]' }} hover:underline flex items-center gap-1">
                POPULAR <x-icons.star/>
                </a>

            </div>
        </div>


        <!-- Search + Sort -->
        <div class="w-full flex justify-end items-center px-8 py-4 border-b bg-white">
            <form method="GET" action="{{ route('catalogue') }}" class="flex items-center gap-2 max-w-lg w-full">
                <div class="relative flex-1">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">🔍</span>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search for items or categories"
                        class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-full bg-transparent focus:outline-none focus:ring-2 focus:ring-blue-200 shadow-sm transition placeholder-gray-400 text-sm"
                    >
                </div>

                @if(request('sort'))
                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                @endif

                <select name="sort" onchange="this.form.submit()"
                    class="ml-2 pl-4 py-2 border border-gray-200 rounded-full bg-white text-gray-700 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-200">
                    <option value="">Sort: Default</option>
                    <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Sort: New</option>
                    <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                    <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                    <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Name: A-Z</option>
                    <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Name: Z-A</option>
                </select>
            </form>
        </div>

        <div class="min-h-screen bg-gray-100 flex">

            <!-- Sidebar -->
            <aside class="w-full sm:w-1/4 p-6 border-r bg-white shadow overflow-y-auto flex flex-col">
                <!-- CATEGORY -->
                <h2 class="font-bold mb-3">CATEGORY</h2>
                <form id="sidebar-filter-form" method="GET" action="{{ route('catalogue') }}" class="flex-1">
                    <!-- Preserve all query params except category, brands, min/max price, page -->
                    @foreach(request()->except(['category','brands','min_price','max_price','page']) as $key => $val)
                        <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                    @endforeach

                    <ul class="text-sm space-y-1">
                        @forelse($categories as $cat)
                            <li>
                                <label class="cursor-pointer hover:underline flex items-center">
                                    <input
                                        type="radio"
                                        name="category"
                                        value="{{ $cat }}"
                                        class="mr-2 category-radio"
                                        {{ request('category') === $cat ? 'checked' : '' }}
                                    >
                                    {{ strtoupper($cat) }}
                                </label>
                            </li>
                        @empty
                            <li class="text-gray-400">No categories</li>
                        @endforelse
                    </ul>

                    <!-- PRICE -->
                    <h2 class="font-bold mt-6 mb-2">PRICE</h2>
                    <div class="flex gap-2 mb-2">
                    <!-- PRICE -->
                    <h2 class="font-bold mt-6 mb-2">PRICE</h2>
                    <div class="flex gap-2 mb-2">
                        <input type="number" name="min_price" placeholder="Min ₱"
                            value="{{ request('min_price') }}" min="0" step="1"
                            value="{{ request('min_price') }}" min="0" step="1"
                            class="border p-1 w-24 rounded">
                        <input type="number" name="max_price" placeholder="Max ₱"
                            value="{{ request('max_price') }}" min="0" step="1"
                            value="{{ request('max_price') }}" min="0" step="1"
                            class="border p-1 w-24 rounded">
                    </div>

                    <!-- BRANDS -->
                    <h2 class="font-bold mt-6 mb-2">BRAND</h2>
                    <ul class="text-sm space-y-1">
                        @forelse($brands as $brand)
                            <li class="flex items-center">
                                <input
                                    type="checkbox"
                                    name="brands[]"
                                    value="{{ $brand }}"
                                    id="brand-{{ $loop->index }}"
                                    class="mr-2 brand-checkbox"
                                    {{ in_array($brand, request('brands', [])) ? 'checked' : '' }}
                                >
                                <label for="brand-{{ $loop->index }}"
                                    class="hover:underline cursor-pointer {{ in_array($brand, request('brands', [])) ? 'text-blue-600 font-semibold' : '' }}">
                                    {{ $brand }}
                                </label>
                            </li>
                        @empty
                            <li class="text-gray-400">No brands</li>
                        @endforelse
                    </ul>
                    <!-- BRANDS -->
                    <h2 class="font-bold mt-6 mb-2">BRAND</h2>
                    <ul class="text-sm space-y-1">
                        @forelse($brands as $brand)
                            <li class="flex items-center">
                                <input
                                    type="checkbox"
                                    name="brands[]"
                                    value="{{ $brand }}"
                                    id="brand-{{ $loop->index }}"
                                    class="mr-2 brand-checkbox"
                                    {{ in_array($brand, request('brands', [])) ? 'checked' : '' }}
                                >
                                <label for="brand-{{ $loop->index }}"
                                    class="hover:underline cursor-pointer {{ in_array($brand, request('brands', [])) ? 'text-blue-600 font-semibold' : '' }}">
                                    {{ $brand }}
                                </label>
                            </li>
                        @empty
                            <li class="text-gray-400">No brands</li>
                        @endforelse
                    </ul>

                    <!-- Submit button (optional, form auto-submits via JS) -->
                    <button type="submit" class="hidden"></button>
                </form>

                <!-- Clear All Filters -->
                <div class="mt-auto pt-6">
                    <a href="{{ route('catalogue') }}"
                    class="block text-center px-3 py-2 bg-blue-500 text-white text-sm rounded hover:bg-blue-600 ">
                        Clear All Filters
                    </a>
                </div>

            </aside>

            <!-- JS to auto-submit filters -->
            <script>
                // Auto-submit when a brand checkbox changes
                document.querySelectorAll('.brand-checkbox').forEach(function(checkbox){
                    checkbox.addEventListener('change', function(){
                        document.getElementById('sidebar-filter-form').submit();
                    });
                });

                // Auto-submit when a category radio changes
                document.querySelectorAll('.category-radio').forEach(function(radio){
                    radio.addEventListener('change', function(){
                        document.getElementById('sidebar-filter-form').submit();
                    });
                });

                // Optional: auto-submit on price input change (on blur)
                document.querySelectorAll('input[name="min_price"], input[name="max_price"]').forEach(function(input){
                    input.addEventListener('blur', function(){
                        document.getElementById('sidebar-filter-form').submit();
                    });
                });
            </script>

            <!-- Product Grid -->
            <main class="w-full sm:w-3/4 p-6 grid  grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
                x-data="{ openModal: false, specs: {}, name: '', image: '' }"
                x-on:open-specs.window="openModal = true; specs = $event.detail.specs; name = $event.detail.name; image = $event.detail.image;">

                @forelse($products as $product)
                    <div class="relative border rounded-lg p-4 text-center bg-blue-50 shadow hover:shadow-lg transition flex flex-col justify-between h-[360px] group">
                        
                        <!-- Menu -->
                        {{-- <button @click="$dispatch('open-specs', { 
                            specs: {{ json_encode($product['specs']) }}, 
                            name: '{{ $product['name'] }}', 
                            image: {{ json_encode($product['image']) }} })"
                                class="absolute top-2 right-2 p-2 rounded-full bg-white shadow hover:bg-gray-100">
                            <svg xmlns="http://www.w3.org/2000/svg" 
                                fill="currentColor" 
                                viewBox="0 0 16 16" 
                                class="w-5 h-5 text-gray-700">
                                <path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z"/>
                            </svg>
                        </button> --}}

                        <!-- Image + Name as Link -->
                        <a href="{{ route('catalogue.show', ['table' => $product['table'], 'id' => $product['id']]) }}">
                            <img src="{{ asset('storage/' . $product['image']) }}"
                                alt="{{ $product['name'] }}"
                                class="mx-auto mb-3 h-32 object-contain">
                        </a>

                        <h3 class="font-bold text-sm truncate hover:underline">
                            <a href="{{ route('catalogue.show', ['table' => $product['table'], 'id' => $product['id']]) }}">
                                {{ $product['name'] }}
                            </a>
                        </h3>

                        <p class="text-xs text-gray-600">{{ $product['brand'] }}</p>
                        <p class="text-[11px] text-gray-500 mt-0.5">{{ strtoupper($product['category']) }}</p>

                        <!-- ⭐ Rating -->
                        <p class="text-yellow-500 text-sm mb-1">
                            @if(!empty($product['rating']))
                                ⭐ {{ $product['rating'] }} ({{ $product['reviews_count'] ?? 0 }})
                            @else
                                ⭐ No reviews yet
                            @endif
                        </p>

                        <!-- Price -->
                        <p class="text-gray-800 font-semibold mt-1">₱{{ number_format($product['price'], 0) }}</p>

                        <!-- Add to Cart -->
                        <form action="{{ route('cart.add') }}" method="POST" class="mt-auto">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product['id'] }}">
                            <input type="hidden" name="name" value="{{ $product['name'] }}">
                            <input type="hidden" name="price" value="{{ $product['price'] }}">
                            <input type="hidden" name="component_type" value="{{ $product['category'] }}">
                            <button type="submit" class="w-full py-2 bg-white border rounded-md font-semibold text-gray-700 shadow hover:bg-gray-100">
                                Add to Cart
                            </button>
                        </form>
                    </div>
                @empty
                    <p class="col-span-4 text-center text-gray-500">No products available.</p>
                @endforelse


                {{-- <!-- 🔥 Global Modal -->
                <template x-if="openModal">
                    <div class="fixed inset-0 flex items-center justify-center !z-[99999]">
                        <!-- Overlay -->
                        <div class="absolute inset-0 bg-black bg-opacity-50" @click="openModal = false"></div>

                        <!-- Modal box -->
                        <div class="relative bg-white text-black rounded-lg shadow-xl w-[700px] max-h-[85vh] overflow-y-auto p-6 z-60">

                            <!-- Close button -->
                            <button @click="openModal = false"
                                    class="absolute top-3 right-3 text-gray-500 hover:text-gray-800 text-xl">
                                ✖
                            </button>

                            <h2 class="text-xl font-semibold text-center mb-6" x-text="name"></h2>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Left: Product Image -->
                                <div class="flex items-center justify-center border rounded-md p-3 bg-gray-50">
                                    <template x-if="image">
                                        <img :src="`/storage/${image}`"
                                            :alt="name"
                                            class="max-h-60 object-contain">
                                    </template>
                                    <template x-if="!image">
                                        <p class="text-gray-500">No image uploaded.</p>
                                    </template>
                                </div>

                                <!-- Right: Specs Table -->
                                <div>
                                    <table class="w-full text-sm border-collapse">
                                        <tbody>
                                            <template x-for="(value, key) in specs" :key="key">
                                                <tr class="border-b">
                                                    <td class="font-semibold py-1 pr-3" x-text="key.replace('_',' ').toUpperCase()"></td>
                                                    <td class="py-1" x-text="value"></td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </template> --}}
            </main>
        </div>

        <div class="px-6 py-4">
            {{ $products->withQueryString()->links() }}
        </div>

    </main>
    
    <script src="//unpkg.com/alpinejs" defer></script>
    <script>
        // Auto-submit when a brand checkbox changes
        document.querySelectorAll('.brand-checkbox').forEach(function(checkbox){
            checkbox.addEventListener('change', function(){
                document.getElementById('sidebar-filter-form').submit();
            });
        });

        // Auto-submit when a category radio changes
        document.querySelectorAll('.category-radio').forEach(function(radio){
            radio.addEventListener('change', function(){
                document.getElementById('sidebar-filter-form').submit();
            });
        });

        // Optional: auto-submit on price input change (on blur)
        document.querySelectorAll('input[name="min_price"], input[name="max_price"]').forEach(function(input){
            input.addEventListener('blur', function(){
                document.getElementById('sidebar-filter-form').submit();
            });
        });
    </script>
</body>
</html>

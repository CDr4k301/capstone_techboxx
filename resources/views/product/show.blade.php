<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $product['name'] ?? 'Product' }} - Techboxx</title>

    {{-- Include same Vite assets as catalogue so header styles & scripts load --}}
    @vite([
        'resources/css/app.css',
        'resources/css/landingpage/header.css',
        'resources/js/app.js',
    ])
</head>
<body class="bg-gray-100">

    @if (session('message'))
        <x-message :type="session('type')">
            {{ session('message') }}
        </x-message>
    @endif

    <!-- Fixed site header -->
    <x-landingheader :name="Auth::user()?->first_name" />

    @php
        $mainImage = asset('storage/' . str_replace('\\', '/', $product['image'] ?? 'images/placeholder.png'));
        $thumbs = [$mainImage];
    @endphp

    <main class="max-w-7xl mx-auto px-6 pt-24 pb-12">
        <!-- Breadcrumb -->
        <nav class="text-xs text-gray-500 mb-6">
            <a href="{{ route('home') }}" class="hover:underline">Home</a>
            <span class="mx-2">/</span>
            <a href="{{ route('catalogue') }}" class="hover:underline">Products</a>
            <span class="mx-2">/</span>
            <span class="text-gray-700">{{ $product['name'] ?? 'Product' }}</span>
        </nav>

        <!-- TWO COLUMN LAYOUT -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            
            <!-- LEFT: Image box -->
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex flex-col items-center">
                    <div class="flex-1 flex items-center justify-center">
                        <div x-data='{ main: @json($mainImage), thumbs: @json($thumbs) }' class="w-full">
                            <div class="flex justify-center items-center border rounded-md bg-gray-50 p-6">
                                <img :src="main" alt="{{ $product['name'] ?? 'Product' }}" class="max-h-[420px] object-contain">
                            </div>

                            <!-- Mobile thumbs -->
                            <div class="flex gap-3 mt-4 md:hidden justify-center">
                                <template x-for="(t,i) in thumbs" :key="i">
                                    <button type="button" @click="main = t" class="w-20 h-20 border rounded-md p-1 bg-white">
                                        <img :src="t" class="w-full h-full object-contain">
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Product info box -->
            <div class="bg-white shadow rounded-lg p-6">
                <h1 class="text-3xl font-bold text-gray-900">{{ $product['name'] ?? 'Product' }}</h1>
                <div class="flex items-center gap-3 mt-2">
                    <p class="text-xs uppercase text-gray-500">{{ $product['brand'] ?? 'Unknown Brand' }}</p>
                    <span class="inline-block h-1 w-1 bg-gray-300 rounded-full"></span>
                    <p class="text-xs text-gray-400">{{ ucfirst($product['category'] ?? '') }}</p>
                </div>

                <!-- Ratings -->
                <div class="flex items-center gap-3 mt-4 text-yellow-400">
                    ★★★★☆ <span class="text-sm text-gray-500 ml-2">(No reviews)</span>
                </div>

                <!-- Short description -->
                <p class="text-gray-700 mt-4 leading-relaxed">
                    {{ Str::limit($product['description'] ?? 'This is a placeholder description.', 120) }}
                </p>
                <a href="#full-description" class="text-blue-600 hover:underline text-sm">
                    See full description
                </a>

                <!-- Price & Stock -->
                <div class="mt-6 border-t pt-6">
                    <p class="text-sm text-gray-500">Price</p>
                    <div class="text-2xl font-bold text-blue-600">₱{{ number_format($product['price'] ?? 0, 0) }}</div>

                    <!-- Stock directly below price -->
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">Stock</p>
                        @if(($product['stock'] ?? 0) > 0)
                            <span class="px-3 py-1 bg-green-50 text-green-700 rounded-full text-sm font-semibold">
                                In stock
                            </span>
                        @else
                            <span class="px-3 py-1 bg-red-50 text-red-700 rounded-full text-sm font-semibold">
                                Out of stock
                            </span>
                        @endif
                    </div>

                    <!-- Add to cart -->
                    <form action="{{ route('cart.add') }}" method="POST" class="mt-6">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product['id'] }}">
                        <input type="hidden" name="name" value="{{ $product['name'] }}">
                        <input type="hidden" name="price" value="{{ $product['price'] }}">
                        <input type="hidden" name="component_type" value="{{ $product['category'] }}">


                        <!-- Quantity dropdown -->
                        <div class="flex items-center gap-3 mt-4">
                            <label class="text-sm text-gray-600">Quantity</label>
                            <div class="relative">
                                <select name="quantity"
                                    class="appearance-none border rounded px-3 pr-8 py-2 focus:ring-blue-500 focus:border-blue-500">
                                    @for($i = 1; $i <= min(10, $product['stock'] ?? 0); $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>

                            <button type="submit"
                                class="ml-auto px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition"
                                @if(($product['stock'] ?? 0) <= 0) disabled @endif>
                                Add to Cart
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Full Description -->
        <div id="full-description" class="mt-12 bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-bold mb-3">Product Specifications</h2>
            
            <!-- Combined Specifications -->
            <div class="grid grid-cols-1 gap-4 mb-6">
                <!-- Common Specifications from Main Table -->
                @foreach($commonColumns as $column)
                    @if(in_array($column, $columns) && !empty($row->$column))
                        <div class="flex justify-between border-b pb-2">
                            <span class="font-medium capitalize">{{ str_replace('_', ' ', $column) }}:</span>
                            <span class="text-gray-700">
                                @if($column === 'price')
                                    ${{ number_format($row->$column, 2) }}
                                @else
                                    {{ $row->$column }}
                                @endif
                            </span>
                        </div>
                    @endif
                @endforeach

                <!-- Drive Bays from Related Table -->
                @if(isset($relatedData['drive_bays']) && $relatedData['drive_bays'])
                    @if(isset($relatedData['drive_bays']->{'3_5_bays'}) && $relatedData['drive_bays']->{'3_5_bays'})
                        <div class="flex justify-between border-b pb-2">
                            <span class="font-medium capitalize">3.5" Drive Bays:</span>
                            <span class="text-gray-700">{{ $relatedData['drive_bays']->{'3_5_bays'} }}</span>
                        </div>
                    @endif
                    
                    @if(isset($relatedData['drive_bays']->{'2_5_bays'}) && $relatedData['drive_bays']->{'2_5_bays'} !== null)
                        <div class="flex justify-between border-b pb-2">
                            <span class="font-medium capitalize">2.5" Drive Bays:</span>
                            <span class="text-gray-700">{{ $relatedData['drive_bays']->{'2_5_bays'} }}</span>
                        </div>
                    @endif

                    @if(isset($relatedData['front_ports']->{'usb_3_0_type_A'}) && $relatedData['front_ports']->{'usb_3_0_type_A'} !== null)
                        <div class="flex justify-between border-b pb-2">
                            <span class="font-medium capitalize">USB 3.0 Type-A:</span>
                            <span class="text-gray-700">{{ $relatedData['front_ports']->{'usb_3_0_type_A'} }}</span>
                        </div>
                    @endif

                    @if(isset($relatedData['front_ports']->{'usb_2_0'}) && $relatedData['front_ports']->{'usb_2_0'} !== null)
                        <div class="flex justify-between border-b pb-2">
                            <span class="font-medium capitalize">USB 2.0:</span>
                            <span class="text-gray-700">{{ $relatedData['front_ports']->{'usb_2_0'} }}</span>
                        </div>
                    @endif

                    @if(isset($relatedData['front_ports']->{'audio_jacks'}) && $relatedData['front_ports']->{'audio_jacks'} !== null)
                        <div class="flex justify-between border-b pb-2">
                            <span class="font-medium capitalize">Audio Jacks:</span>
                            <span class="text-gray-700">{{ $relatedData['front_ports']->{'audio_jacks'} }}</span>
                        </div>
                    @endif

                    @if(isset($relatedData['radiator_support']) && $relatedData['radiator_support']->isNotEmpty())
                        @php
                            // Group by location and collect all sizes
                            $radiatorGroups = [];
                            foreach($relatedData['radiator_support'] as $radiator) {
                                $location = $radiator->location ?? 'Unknown';
                                $size = $radiator->size_mm ?? '';
                                if ($size) {
                                    if (!isset($radiatorGroups[$location])) {
                                        $radiatorGroups[$location] = [];
                                    }
                                    $radiatorGroups[$location][] = $size;
                                }
                            }
                            
                            // Build the display array with concatenated sizes
                            $radiatorSupport = [];
                            foreach($radiatorGroups as $location => $sizes) {
                                // Remove duplicates and sort sizes
                                $uniqueSizes = array_unique($sizes);
                                sort($uniqueSizes);
                                
                                // Concatenate sizes with slashes
                                $sizeString = implode(' / ', $uniqueSizes);
                                $radiatorSupport[] = $location . ': ' . $sizeString . 'mm';
                            }
                        @endphp
                        
                        <div class="flex flex-col sm:flex-row sm:justify-between border-b pb-2">
                            <span class="font-medium capitalize sm:mb-0 mb-1">Radiator Support:</span>
                            <span class="text-gray-700 text-right">
                                @foreach($radiatorSupport as $support)
                                    {{ $support }}@if(!$loop->last)<br>@endif
                                @endforeach
                            </span>
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <!-- Customer Comments -->
        <div class="mt-12 bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-bold mb-3">Customer Comments</h2>
            @if(!empty($product['comments']))
                @foreach($product['comments'] as $comment)
                    <div class="border-b py-3">
                        <p class="text-gray-800">{{ $comment['content'] }}</p>
                        <span class="text-sm text-gray-500">– {{ $comment['user_name'] ?? 'Anonymous' }}</span>
                    </div>
                @endforeach
            @else
                <p class="text-gray-500">No comments yet. Be the first to leave one!</p>
            @endif
        </div>
    </main>

    <script src="//unpkg.com/alpinejs" defer></script>
</body>
</html>
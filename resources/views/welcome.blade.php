<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Techboxx</title>

    @vite([
        'resources\css\app.css',
        'resources\css\landingpage\header.css',
        'resources\js\app.js'])
</head>
<body class="flex">

    @if (session('message'))
        <x-message :type="session('type')">
            {{ session('message') }}
        </x-message>
    @endif

    <x-landingheader :name="Auth::user()?->first_name" />

    <main class="main-content !p-0">
        <!-- Hero Section -->
        <section class="text-left bg-gradient-to-b from-blue-800 to-blue-500 py-20 text-white relative overflow-hidden flex items-center justify-between px-10">
            
            <!-- LEFT: Your existing text (unchanged) -->
            <div class="max-w-3xl pl-20">
            <h1 class="text-4xl md:text-6xl font-extrabold leading-tight">TechBoxx PC Builder Simulator</h1>
            <p class="mt-4 text-xl text-gray-200">Don’t just buy it. <span class="font-semibold">BUILD</span> it.</p>
            <a href="{{ route('techboxx.build.extend')}}" class="mt-8 inline-block px-8 py-3 bg-gray-600 text-white font-bold rounded-xl shadow-lg hover:bg-yellow-300 transition">
                Design yours →
            </a>
            </div>

            <!-- RIGHT: Only adjusted image -->
            <div class="px-40 flex items-center">
            <img src="{{ asset('image 5.png') }}" 
                alt="PC Simulator Demo" 
                class="w-80 md:w-[430px] object-contain">
            </div>

        </section>


        <!-- PC Builder Simulator Section -->
        <section id="builder" class="grid md:grid-cols-2 items-center gap-12 px-8 py-20 max-w-6xl mx-auto">
            <div>
            <h2 class="text-3xl md:text-4xl font-bold">PC Builder Simulator</h2>
            <p class="mt-4 text-lg text-gray-600 leading-relaxed">
                Build and visualize custom PC setups in real-time. Add parts, check compatibility, and explore your build in full 3D.
            </p>
            <a href="{{ route('techboxx.build') }}" class="mt-8 inline-block px-6 py-3 bg-blue-700 text-white font-semibold rounded-lg shadow hover:bg-blue-600 transition">Get Started →</a>
            </div>
            <div class="flex justify-center">
            <img src="{{ asset('121.png') }}" alt="PC Simulator Demo" class="">
            </div>
        </section>

        <!-- See Components Section -->
        <section class="grid md:grid-cols-2 items-center gap-12 px-8 py-20 bg-gray-50 max-w-6xl mx-auto">
            <div class="flex justify-center">
            <img src="{{ asset('2W1.png') }}" alt="Components" class="">
            </div>
            <div>
            <h2 class="text-3xl md:text-4xl font-bold">See Components</h2>
            <p class="mt-4 text-lg text-gray-600 leading-relaxed">
                Browse and assemble PC parts in a full 3D builder. Instantly check compatibility as you shop from our interactive catalog.
            </p>
            <a href="{{ route('catalogue') }}" class="mt-8 inline-block px-6 py-3 bg-blue-700 text-white font-semibold rounded-lg shadow hover:bg-blue-600 transition">Browse Now →</a>
            </div>
        </section>


        <!-- Footer -->
        <footer class="text-center py-6 bg-gray-900 text-gray-400 text-sm">
            © 2025 TechBoxx. All rights reserved.
        </footer>
        <script>
        function addToCart(productId, price) {
            const qty = document.querySelector(`#qty-${productId}`).value;

            fetch("{{ route('cart.add') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: qty,
                    price: price
                })
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
            });
        }</script>
    </main>
</body>
</html>
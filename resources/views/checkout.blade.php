<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

    <!-- Page Header -->
    <div class="w-full shadow-lg">
        <div class="bg-gradient-to-r from-blue-900 to-blue-700 text-white px-6 py-5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <h1 class="text-2xl md:text-3xl font-extrabold">Maddox.qwe | Checkout</h1>
            </div>
        </div>
    </div>

    <div class="w-full">
        <div class="rounded-xl p-6 w-full">

            <!-- Back to cart -->
            <div class="mb-4">
                <a href="{{ route('cart.index') }}" class="text-gray-700 hover:text-blue-900 font-medium">
                    ← Back to Cart
                </a>
            </div>

            <!-- Checkout Table -->
            <div class="overflow-x-auto border border-gray-200 rounded-xl mb-2">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="text-gray-700 text-xs md:text-sm uppercase tracking-wide border-b border-100">
                            <th class="p-4 text-left w-[30%]">Products Ordered</th>
                            <th class="p-4 text-left">Category</th>
                            <th class="p-4 text-center">Price</th>
                            <th class="p-4 text-center">Quantity</th>
                            <th class="p-4 text-center">Item Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @php
                            $rows = $selectedItems ?? null;
                        @endphp

                        @if($rows)
                            @foreach($rows as $item)
                                @php $itemTotal = $item->product ? $item->product->price * $item->quantity : 0; @endphp
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="p-4">
                                        <div class="flex items-center gap-4">
                                            <img src="{{ $item->product->image ?? 'https://via.placeholder.com/72' }}"
                                                alt="{{ $item->product->brand }} {{ $item->product->model }}"
                                                class="w-16 h-16 md:w-20 md:h-20 object-cover rounded-lg border border-gray-200 shadow-sm" />
                                            <div>
                                                <div class="font-semibold text-gray-800">{{ $item->product->brand }} {{ $item->product->model }}</div>
                                                @if($item->product->sku)
                                                    <div class="text-xs text-gray-500">SKU: {{ $item->product->sku }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4 text-gray-600">{{ ucfirst($item->product_type) ?? '—' }}</td>
                                    <td class="p-4 text-center text-gray-800">₱{{ number_format($item->product->price, 2) }}</td>
                                    <td class="p-4 text-center">
                                        <span class="px-4 font-semibold text-gray-900 select-none">{{ $item->quantity }}</span>
                                    </td>
                                    <td class="p-4 text-center font-semibold text-gray-900">₱{{ number_format($itemTotal, 2) }}</td>
                                </tr>
                            @endforeach
                        @else
                            @php $cart = session('cart', []); @endphp
                            @foreach($cart as $id => $item)
                                @php $itemTotal = $item['price'] * $item['quantity']; @endphp
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="p-4">
                                        <div class="flex items-center gap-4">
                                            <img src="{{ $item['image'] ?? 'https://via.placeholder.com/72' }}"
                                                alt="{{ $item['name'] }}"
                                                class="w-16 h-16 md:w-20 md:h-20 object-cover rounded-lg border border-gray-200 shadow-sm" />
                                            <div>
                                                <div class="font-semibold text-gray-800">{{ $item['name'] }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4 text-gray-600">{{ $item['category'] ?? '—' }}</td>
                                    <td class="p-4 text-center text-gray-800">₱{{ number_format($item['price'], 2) }}</td>
                                    <td class="p-4 text-center">
                                        <span class="px-4 font-semibold text-gray-900 select-none">{{ $item['quantity'] }}</span>
                                    </td>
                                    <td class="p-4 text-center font-semibold text-gray-900">₱{{ number_format($itemTotal, 2) }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Order Total -->
            <div class="flex justify-end items-center text-sm font-semibold text-gray-900 mb-6">
                @php
                    if(isset($rows)) {
                        $grandTotal = collect($rows)->sum(fn($i) => $i->product->price * $i->quantity);
                        $qty = collect($rows)->sum('quantity');
                    } else {
                        $cart = session('cart', []);
                        $grandTotal = collect($cart)->sum(fn($i) => $i['price'] * $i['quantity']);
                        $qty = collect($cart)->sum('quantity');
                    }
                @endphp
                <span class="mr-2">Order Total ({{ $qty }} items):</span>
                <span class="text-lg font-bold">₱{{ number_format($grandTotal, 2) }}</span>
            </div>

            <!-- Checkout Form -->
            <div class="border border-gray-200 rounded-xl p-5">
                <form id="checkout-form" action="{{ route('checkout.process') }}" method="POST">
                    @csrf

                    <!-- Hidden inputs for backend -->
                    @php
                        if (isset($rows) && $rows) {
                            $selectedIdsJson = collect($rows)->pluck('id')->toJson();
                        } else {
                            $cart = session('cart', []);
                            $selectedIdsJson = json_encode(array_keys($cart));
                        }
                    @endphp

                    <input type="hidden" name="selected_items" value='{{ $selectedIdsJson }}'>
                    <input type="hidden" name="payment_method" id="payment_method" required>

                    <!-- Name -->
                    <div class="flex items-center mb-3">
                        <span class="w-32 text-gray-700 font-medium">Name:</span>
                        <span class="flex-1 text-gray-900 font-semibold">
                            {{ Auth::user()->first_name ?? '' }} {{ Auth::user()->last_name ?? '' }}
                        </span>
                    </div>

                    <!-- Contact Info -->
                    <div class="flex items-center mb-3">
                        <span class="w-32 text-gray-700 font-medium">Contact Info:</span>
                        <span class="flex-1 text-gray-900 font-semibold">
                            {{ Auth::user()->phone_number ?? '' }}
                        </span>
                    </div>

                    <!-- Payment Methods -->
                    <div class="flex items-center mb-6">
                        <p class="w-40 text-gray-700 font-medium">Payment Method:</p>
                        <div class="flex gap-4">
                            <button
                                type="button"
                                onclick="selectPayment('PayPal', this)"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold">
                                PayPal
                            </button>
                            <button
                                type="button"
                                onclick="selectPayment('Cash on Pickup', this)"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold">
                                Cash On Pickup
                            </button>
                        </div>
                    </div>

                    <!-- Total Payment -->
                    <div class="flex justify-end mb-4">
                        <span class="text-gray-700 mr-2">Total Payment:</span>
                        <span class="text-pink-600 font-bold text-lg">
                            ₱{{ number_format($grandTotal, 2) }}
                        </span>
                    </div>

                    <!-- Checkout Button -->
                    <div class="flex justify-end mb-6">
                        <button id="checkout-btn" type="submit"
                            class="bg-pink-400 hover:bg-pink-600 text-white px-6 py-3 rounded font-semibold transition">
                            Check Out
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <!-- Scripts -->
    <script>
        let selectedPayment = null;

        function selectPayment(method, btn) {
            selectedPayment = method;
            document.getElementById('payment_method').value = method;

            // reset styles
            document.querySelectorAll('[onclick^="selectPayment"]').forEach(b => {
                b.classList.remove('bg-yellow-400', 'text-gray-900');
                b.classList.add('bg-gray-200', 'text-gray-700');
            });

            // highlight
            btn.classList.remove('bg-gray-200', 'text-gray-700');
            btn.classList.add('bg-yellow-400', 'text-gray-900');
        }

        document.getElementById('checkout-form').addEventListener('submit', function(e) {
            if (!selectedPayment) {
                e.preventDefault();
                alert('Please select a payment method before checking out.');
                return false;
            }
        });
    </script>
    <!-- Add this script to prevent form resubmission -->
    <script>
        // Prevent form resubmission when page is refreshed or back button is pressed
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }

        // Disable form submission after it's been submitted once
        let formSubmitted = false;
        
        document.getElementById('checkout-form').addEventListener('submit', function(e) {
            if (!selectedPayment) {
                e.preventDefault();
                alert('Please select a payment method before checking out.');
                return false;
            }
            
            // Prevent multiple submissions
            if (formSubmitted) {
                e.preventDefault();
                alert('Your order is already being processed. Please wait.');
                return false;
            }
            
            formSubmitted = true;
            
            // Disable the submit button
            const submitBtn = document.getElementById('checkout-btn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Processing...';
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        });

        // Also disable the button if user tries to go back and forward again
        window.addEventListener('pageshow', function(event) {
            if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
                const submitBtn = document.getElementById('checkout-btn');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Order Processed';
                    submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                }
            }
        });
    </script>
</body>
</html>

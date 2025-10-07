<nav class="nav-bar">

    {{-- fetching the user role in the database --}}
    {{-- ucfirst -> capitalizes the first character string --}}
    @if ($role === 'Customer')
        <h1>Techboxx</h1>
    @else
        <h1>{{ ucfirst($role) }}</h1>
    @endif

    {{-- grouping links in an array based on roles --}}
    @php
        $links = match($role) {
            'Admin' => [
                ['route' => route('admin.dashboard'), 'label' => 'Dashboard', 'icon' => 'dashboard'],
                [
                    'label' => 'Manage',
                    'icon' => 'manage',
                    'children' => [
                        ['route' => route('admin.useraccount'), 'label' => 'Accounts', 'icon' => 'user'],
                        ['route' => route('staff.order'), 'label' => 'Order', 'icon' => 'order'],
                        ['route' => route('staff.componentdetails'), 'label' => 'Component', 'icon' => 'component'],
                        ['route' => route('staff.inventory'), 'label' => 'Inventory', 'icon' => 'inventory'],
                        ['route' => route('staff.software-details'), 'label' => 'Software', 'icon' => 'software'],
                    ]
                ],
                ['route' => route('admin.sales'), 'label' => 'Report', 'icon' => 'bargraph'],
                ['route' => route('admin.activitylogs'), 'label' => 'Activity Logs', 'icon' => 'logs'],
                ['route' => route('techboxx.build'), 'label' => 'Build', 'icon' => 'build', 'style' => 'last-nav'],
            ],
            'Staff' => [
                ['route' => route('staff.dashboard') , 'label' => 'Dashboard', 'icon' => 'dashboard'],
                [
                    'label' => 'Manage',
                    'icon' => 'manage',
                    'children' => [
                        ['route' => route('staff.order'), 'label' => 'Order', 'icon' => 'order'],
                        ['route' => route('staff.componentdetails'), 'label' => 'Component', 'icon' => 'component'],
                        ['route' => route('staff.inventory'), 'label' => 'Inventory', 'icon' => 'inventory'],
                        ['route' => route('staff.software-details'), 'label' => 'Software', 'icon' => 'software'],
                    ]
                ],
                ['route' => route('techboxx.build') , 'label' => 'Build', 'icon' => 'build', 'style' => 'last-nav'],
            ],
            'Customer' => [
                ['route' => route('customer.dashboard'), 'label' => 'Profile', 'icon' => 'dashboard'],
                ['route' => route('customer.checkoutdetails'), 'label' => 'Checkout Details', 'icon' => 'checkout'],
                ['route' => route('customer.orderdetails'), 'label' => 'Order Details', 'icon' => 'order'],
                ['route' => route('customer.purchasedhistory'), 'label' => 'Purchased History', 'icon' => 'purchase'],
            ],
            default => []
        };
    @endphp

    {{-- rendering links base on roles --}}
    <ul>
        @foreach ($links as $link)
            @php
                $isActive = isset($link['route']) && $link['route'] !== '' && request()->is(ltrim(parse_url($link['route'], PHP_URL_PATH), '/') . '*');
                // Check if any child link is active
                $isDropdownActive = isset($link['children']) && collect($link['children'])->contains(fn($child) => request()->is(ltrim(parse_url($child['route'], PHP_URL_PATH), '/') . '*'));
            @endphp

            @if (isset($link['children']))
                <li class="dropdown-nav {{ $isDropdownActive ? 'active' : '' }}">
                    <a href="#" onclick="event.preventDefault(); this.nextElementSibling.classList.toggle('show')">
                        <x-dynamic-component :component="'x-icons.' . $link['icon']" />
                        {{ $link['label'] }}
                    </a>
                    <ul class="submenu {{ $isDropdownActive ? 'show' : '' }}">
                        @foreach ($link['children'] as $sublink)
                            @php
                                $isSubActive = request()->is(ltrim(parse_url($sublink['route'], PHP_URL_PATH), '/') . '*');
                            @endphp
                            <li class="{{ $isSubActive ? 'active' : '' }}">
                                <a href="{{ $sublink['route'] }}">
                                    <x-dynamic-component :component="'x-icons.' . $sublink['icon']" />
                                    {{ $sublink['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </li>
            @else
                <li class="{{ (($link['style'] ?? '' ) . ($isActive ? ' active' : '')) }}">
                    <a href="{{ $link['route'] }}">
                        <x-dynamic-component :component="'x-icons.' . $link['icon']" />
                        {{ $link['label'] }}
                    </a>
                </li>
            @endif
        @endforeach
    </ul>
</nav>
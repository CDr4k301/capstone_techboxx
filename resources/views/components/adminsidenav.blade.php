<nav class="nav-bar  shadow-[inset_-2px_0_1px_rgba(0,0,0,0.3)]">
    {{-- Display the site or role heading --}}
    @if($role === 'Customer')
        <h1>Techboxx</h1>
    @else
        <h1>{{ ucfirst($role) }}</h1>
    @endif
    <div class="separate ">
        <ul class="nav_container">
            {{-- ADMIN NAVBAR --}}
            @if($role === 'Admin')
                <li class="tabs">
                    <a href="{{ route('admin.dashboard') }}" 
                       class="parent {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <x-dynamic-component :component="'x-icons.dashboard'" />
                        Dashboard
                    </a>
                </li>

                <li class="tabs">
                    <a href="{{ route('admin.useraccount') }}" class="parent">
                        <x-dynamic-component :component="'x-icons.manage'" />
                        Manage
                    </a>
                    <ul>
                        <li>
                            <a href="{{ route('admin.useraccount') }}" 
                               class="child {{ request()->routeIs('admin.useraccount') ? 'active' : '' }}">
                                <x-dynamic-component :component="'x-icons.user'" />
                                Accounts
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('staff.order') }}" 
                            class="child relative pr-6 {{ request()->routeIs('staff.order') ? 'active' : '' }} flex items-center gap-2">
                                <x-dynamic-component :component="'x-icons.order'" />
                                <span>Orders</span>
                                @if($totalPendingOrders > 0)
                                    <span class="group relative">
                                        <span class="bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                            {{ $totalPendingOrders }}
                                        </span>
                                        <span class="absolute hidden group-hover:block bg-gray-800 text-white text-xs rounded py-1 px-2 -bottom-8 left-1/2 transform -translate-x-1/2 whitespace-nowrap">
                                            {{ $totalPendingOrders }} pending orders
                                        </span>
                                    </span>
                                @endif
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('staff.componentdetails') }}" 
                               class="child {{ request()->routeIs('staff.componentdetails') ? 'active' : '' }}">
                                <x-dynamic-component :component="'x-icons.component'" />
                                Components
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('staff.inventory') }}" 
                            class="child relative {{ request()->routeIs('staff.inventory') ? 'active' : '' }}">
                                <x-dynamic-component :component="'x-icons.inventory'" />
                                Inventory
                                @if($lowStockCount > 0)
                                    <span class="group relative">
                                        <span class="bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                            {{ $lowStockCount }}
                                        </span>
                                        <span class="absolute hidden group-hover:block bg-gray-800 text-white text-xs rounded py-1 px-2 -bottom-8 left-1/2 transform -translate-x-1/2 whitespace-nowrap">
                                            {{ $lowStockCount }} low stock items
                                        </span>
                                    </span>
                                @endif
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('staff.software-details') }}" 
                               class="child {{ request()->routeIs('staff.software-details') ? 'active' : '' }}">
                                <x-dynamic-component :component="'x-icons.software'" />
                                Software
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="tabs">
                    <a href="#" class="parent">
                        <x-dynamic-component :component="'x-icons.bargraph'" />
                        Reports
                    </a>
                    <ul>
                        <li>
                            <a href="{{ route('admin.sales') }}" 
                               class="child {{ request()->routeIs('admin.sales') ? 'active' : '' }}">
                                <x-dynamic-component :component="'x-icons.bargraph'" />
                                Sales
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.analytics') }}" 
                               class="child {{ request()->routeIs('admin.analytics') ? 'active' : '' }}">
                                <x-dynamic-component :component="'x-icons.bargraph'" />
                                Analytics
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.activitylogs') }}" 
                               class="child {{ request()->routeIs('admin.activitylogs') ? 'active' : '' }}">
                                <x-dynamic-component :component="'x-icons.logs'" />
                                Activity Logs
                            </a>
                        </li>
                    </ul>
                </li>

            {{-- STAFF NAVBAR --}}
            @elseif($role === 'Staff')
                <li class="tabs">
                    <a href="{{ route('staff.dashboard') }}" 
                       class="parent {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}">
                        <x-dynamic-component :component="'x-icons.dashboard'" />
                        Dashboard
                    </a>
                </li>

                <li class="tabs">
                    <a href="#" class="parent">
                        <x-dynamic-component :component="'x-icons.manage'" />
                        Manage
                    </a>
                    <ul>
                        <li>
                            <a href="{{ route('staff.order') }}" 
                            class="child relative pr-6 {{ request()->routeIs('staff.order') ? 'active' : '' }} flex items-center gap-2">
                                <x-dynamic-component :component="'x-icons.order'" />
                                <span>Orders</span>
                                @if($totalPendingOrders > 0)
                                    <span class="group relative">
                                        <span class="bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                            {{ $totalPendingOrders }}
                                        </span>
                                        <span class="absolute hidden group-hover:block bg-gray-800 text-white text-xs rounded py-1 px-2 -bottom-8 left-1/2 transform -translate-x-1/2 whitespace-nowrap">
                                            {{ $totalPendingOrders }} pending orders
                                        </span>
                                    </span>
                                @endif
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('staff.componentdetails') }}" 
                               class="child {{ request()->routeIs('staff.componentdetails') ? 'active' : '' }}">
                                <x-dynamic-component :component="'x-icons.component'" />
                                Components
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('staff.inventory') }}" 
                            class="child relative {{ request()->routeIs('staff.inventory') ? 'active' : '' }}">
                                <x-dynamic-component :component="'x-icons.inventory'" />
                                Inventory
                                @if($lowStockCount > 0)
                                    <span class="group relative">
                                        <span class="bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                            {{ $lowStockCount }}
                                        </span>
                                        <span class="absolute hidden group-hover:block bg-gray-800 text-white text-xs rounded py-1 px-2 -bottom-8 left-1/2 transform -translate-x-1/2 whitespace-nowrap">
                                            {{ $lowStockCount }} low stock items
                                        </span>
                                    </span>
                                @endif
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('staff.software-details') }}" 
                               class="child {{ request()->routeIs('staff.software-details') ? 'active' : '' }}">
                                <x-dynamic-component :component="'x-icons.software'" />
                                Software
                            </a>
                        </li>
                    </ul>
                </li>

            {{-- CUSTOMER NAVBAR --}}
            @elseif($role === 'Customer')
                <li>
                    <a href="{{ route('customer.dashboard') }}" 
                       class="parent {{ request()->routeIs('customer.dashboard') ? 'active' : '' }}">
                        <x-dynamic-component :component="'x-icons.dashboard'" />
                        Profile
                    </a>
                </li>
                <li>
                    <a href="{{ route('customer.checkoutdetails') }}" 
                       class="parent {{ request()->routeIs('customer.checkoutdetails') ? 'active' : '' }}">
                        <x-dynamic-component :component="'x-icons.checkout'" />
                        Checkout Details
                    </a>
                </li>
                <li>
                    <a href="{{ route('customer.orderdetails') }}" 
                       class="parent {{ request()->routeIs('customer.orderdetails') ? 'active' : '' }}">
                        <x-dynamic-component :component="'x-icons.order'" />
                        Order Details
                    </a>
                </li>
                <li>
                    <a href="{{ route('customer.purchasedhistory') }}" 
                       class="parent {{ request()->routeIs('customer.purchasedhistory') ? 'active' : '' }}">
                        <x-dynamic-component :component="'x-icons.purchase'" />
                        Purchased History
                    </a>
                </li>
            @endif
        </ul>

        <a href="{{ route('techboxx.build') }}" 
           class="build {{ request()->routeIs('techboxx.build') ? 'active' : '' }}">
            <x-dynamic-component :component="'x-icons.build'" />
            Build
        </a>
    </div>
</nav>

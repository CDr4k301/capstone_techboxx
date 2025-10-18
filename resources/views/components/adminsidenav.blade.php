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
                               class="child {{ request()->routeIs('staff.order') ? 'active' : '' }}">
                                <x-dynamic-component :component="'x-icons.order'" />
                                Orders
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
                               class="child {{ request()->routeIs('staff.inventory') ? 'active' : '' }}">
                                <x-dynamic-component :component="'x-icons.inventory'" />
                                Inventory
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
                               class="child {{ request()->routeIs('staff.order') ? 'active' : '' }}">
                                <x-dynamic-component :component="'x-icons.order'" />
                                Orders
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
                               class="child {{ request()->routeIs('staff.inventory') ? 'active' : '' }}">
                                <x-dynamic-component :component="'x-icons.inventory'" />
                                Inventory
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

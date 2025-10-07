<nav class="nav-bar">
    {{-- Display the site or role heading --}}
    @if($role === 'Customer')
        <h1>Techboxx</h1>
    @else
        <h1>{{ ucfirst($role) }}</h1>
    @endif
    <div class="separate">
    <ul class="nav_container">
        {{-- ADMIN NAVBAR --}}
        @if($role === 'Admin')
            <li class = "tabs">
                <a href="{{ route('admin.dashboard') }}" class = "parent" id="d">
                    <x-dynamic-component :component="'x-icons.dashboard'" />
                    Dashboard
                </a>
            </li>

            <li class = "tabs">
                <a href="#" class = "parent">
                    <x-dynamic-component :component="'x-icons.manage'" />
                    Manage
                </a>
                <ul>
                     <li>
                        <a href="{{ route('admin.useraccount') }}" class = "child">
                            <x-dynamic-component :component="'x-icons.user'" />
                            Accounts
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('staff.order') }}"class = "child">
                            <x-dynamic-component :component="'x-icons.order'" />
                            Orders
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('staff.componentdetails') }}"class = "child">
                            <x-dynamic-component :component="'x-icons.component'" />
                            Components
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('staff.inventory') }}"class = "child">
                            <x-dynamic-component :component="'x-icons.inventory'" />
                            Inventory
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('staff.software-details') }}"class = "child">
                            <x-dynamic-component :component="'x-icons.software'" />
                            Software
                        </a>
                    </li>
                </ul>
            </li>



            <li class = "tabs">
                <a href="{{ route('admin.sales') }}" class = "parent">
                    <x-dynamic-component :component="'x-icons.bargraph'" />
                    Report
                </a>
                <ul>
                    <li>
                        <a href="{{ route('admin.activitylogs') }}"class = "child">
                            <x-dynamic-component :component="'x-icons.logs'" />
                            Activity Logs
                        </a>
                    </li>

                </ul>
            </li>

            


        {{-- STAFF NAVBAR --}}
        @elseif($role === 'Staff')
            <li class = "tabs">
                <a href="{{ route('staff.dashboard') }}" class = "parent" id="d">
                    <x-dynamic-component :component="'x-icons.dashboard'" />
                    Dashboard
                </a>
            </li>

            <li class = "tabs">
                <a href="#" class = "parent">
                    <x-dynamic-component :component="'x-icons.manage'" />
                    Manage
                </a>
                <ul>
                    <li>
                        <a href="{{ route('staff.order') }}"class = "child">
                            <x-dynamic-component :component="'x-icons.order'" />
                            Orders
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('staff.componentdetails') }}"class = "child">
                            <x-dynamic-component :component="'x-icons.component'" />
                            Components
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('staff.inventory') }}"class = "child">
                            <x-dynamic-component :component="'x-icons.inventory'" />
                            Inventory
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('staff.software-details') }}"class = "child">
                            <x-dynamic-component :component="'x-icons.software'" />
                            Software
                        </a>
                    </li>
                </ul>
            </li>



        {{-- CUSTOMER NAVBAR --}}
        @elseif($role === 'Customer')

            <li>
                <a href="{{ route('customer.dashboard') }}"  class = "parent">
                    <x-dynamic-component :component="'x-icons.dashboard'" />
                    Profile
                </a>
            </li>

            <li>
                <a href="{{ route('customer.checkoutdetails') }}" class = "parent">
                    <x-dynamic-component :component="'x-icons.checkout'" />
                    Checkout Details
                </a>
            </li>

            <li>
                <a href="{{ route('customer.orderdetails') }}" class = "parent">
                    <x-dynamic-component :component="'x-icons.order'" />
                    Order Details
                </a>
            </li>

            <li>
                <a href="{{ route('customer.purchasedhistory') }}" class = "parent">
                    <x-dynamic-component :component="'x-icons.purchase'" />
                    Purchased History
                </a>
            </li>

        @else
            {{-- No nav items if role is not recognized --}}
        @endif
    </ul>
            <a href="{{ route('techboxx.build') }}" class="build">
                <x-dynamic-component :component="'x-icons.build'" />
                Build
            </a>
    </div>
</nav>

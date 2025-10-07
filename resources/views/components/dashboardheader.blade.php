<header class="header">
    <div>
        <img src="{{ asset('images\Logo.png') }}" alt="Logo" class="logo">
        <h2>Madoxx.qwe</h2>
    </div>
    
    <div class="gap-3">
        <x-icons.bell/>
        <div x-data="{ open: false }" class="gap-1">
            <x-icons.profile/>
            <p>{{ $slot }}</p>
            <p class="role">{{ $role }}</p>
            
            {{-- Clickable arrow --}}
            <button @click="open = !open">
                <x-icons.arrow class="hover:text-gray-500"/>
            </button>

            {{-- Dropdown menu --}}
            <div
                x-show="open"
                x-cloak
                x-transition
                @click.outside="open = false"
                class="dropdown"
            >
                <div class="dropdown-profile">
                    <x-icons.profile/>
                    <div class="flex flex-col w-full">
                        <div class="gap-1 w-full">
                            <p>{{ $slot }}</p>
                            <p class="role">{{ $role }}</p>    
                        </div>
                        <div class="w-full">
                            <p class="email">{{ $email }}</p>
                        </div>
                    </div>
                </div>
                <div class="w-full ">
                    <a  href="{{ route('logout') }}"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                        class="sign-out hover:text-gray-50"
                    >
                        <x-icons.signout/>
                        Sign Out
                    </a>

                    <form action="{{ route('logout') }}" method="POST" id="logout-form">
                        @csrf
                    </form>    
                </div>
            </div>
        </div>    
    </div>
</header>
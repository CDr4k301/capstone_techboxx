<x-dashboardlayout>
    {{-- add new staff/admin --}}
    <section class="section-style">
        <h2 class="section-header">Add New Admin / Staff</h2>

        <form action="{{ route('admin.users.add')}}" method="POST" class="form !flex !flex-col">
            @csrf
            <div class="flex flex-row gap-2">
                <div class="flex flex-col gap-2">
                    <div class="input-label">
                        <label for="first_name">First name</label>
                        <input required type="text" name="first_name">
                    </div>
                    
                    <div class="input-label">
                        <label for="last_name">Last Name</label>
                        <input required type="text" name="last_name">
                    </div>

                    <div class="input-label">
                        <label for="email">Email</label>
                        <input required type="email" name="email">
                    </div>

                    <div class="input-label">
                        <label for="password">Password</label>
                        <input required type="text" name="password" value="password" readonly class="text-[var(--gray)]">
                    </div>

                    <div class="input-label">
                        <label for="role">Role</label>
                        <select name="role" id="role" class="pt-0 pb-0 pl-1">
                            <option value="Staff">Staff</option>
                            <option value="Admin">Admin</option>
                        </select>               
                    </div>
                </div>
                <div class="flex flex-col gap-2">
                    <div class="input-label">
                        <label for="phone_number">Contact Number</label>
                        <input required name="phone_number" id="phone_number" type="number" onkeydown="return !['e','E','+','-'].includes(event.key)">
                    </div>
                    <div class="input-label">
                        <label for="address">Address</label>
                        <input required type="text" name="address">
                    </div>
                </div>
            </div>
            <button class="form-button">Add</button>    
        </form>   
        
    </section>

    {{-- unverified users --}}
    z        <h2 class="section-header">Unverified Customers</h2>
        
        <div class="table-body">
            <table class="table mb-[3%]">
                <thead>
                    <tr class="text-base">
                        <th class="text-left pl-2">Name</th>
                        <th class="text-left pl-2">Email</th>
                        <th>ID Uploaded</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($unverifiedUsers as $unverifieduser)
                        <tr>
                            <td>{{ $unverifieduser->first_name}} {{ $unverifieduser->last_name}}</td>
                            <td>{{ $unverifieduser->email}}</td>
                            <td class="text-center">
                                <a href="{{ asset('storage/' . $unverifieduser->id_uploaded) }}" target="_blank" class="hover:underline">
                                    {{ basename($unverifieduser->id_uploaded)}}
                                </a>
                            </td>
                            <td class="align-middle text-center">
                                <div class="flex justify-center gap-2">
                                    <form action="{{ route('admin.users.approved', $unverifieduser->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" ><x-icons.check /></button>
                                    </form>
                                    <form action="{{ route('admin.users.decline', $unverifieduser->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" ><x-icons.close /></button>
                                    </form>
                                </div>
                            </td>    
                        </tr>    
                    @endforeach
                </tbody>
            </table>   
        </div>
        {{ $unverifiedUsers->appends(request()->except('page'))->links() }}
    </section>    
    
    {{-- user acccounts --}}
    <section class="section-style mb-[10%] !h-[100vh] pb-[1%]">
        <div class="header-container">
            <h2 class="section-header">User accounts</h2>
            {{-- search bar --}}
            <form action="{{ route('admin.useraccount') }}" method="GET">
                <input 
                    type="text"
                    name="search"
                    placeholder="Seach Users"
                    value="{{ request('search') }}" 
                    class="search-bar"
                >
                <button type="submit">
                    <x-icons.search class="search-icon"/>
                </button>
            </form>
        </div>

        <div class="table-body" x-data="{ showEditModal: false, selectedUser: {}, showViewModal: false, selectedUser: {} }">
            <table class="table mb-[3%]">
                <thead>
                    <tr class="text-base">
                        <th class="text-left pl-2">Name</th>
                        <th class="text-left pl-2">Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($userAccounts as $userAccount)
                        <tr @if($userAccount->status === 'Inactive') class="bg-gray-200 opacity-60" @endif>
                            <td>{{ $userAccount->first_name }} {{ $userAccount->last_name }}</td>
                            <td>{{ $userAccount->email }}</td>
                            <td class="text-center">{{ ucfirst($userAccount->role) }}</td>
                            <td class="text-center">{{ ucfirst($userAccount->status) }}</td>
                            <td class="align-middle text-center">
                                <div class="flex justify-center gap-2">
                                    @if ($userAccount->status === 'Inactive')
                                        {{-- ✅ Restore Button --}}
                                        <form action="{{ route('admin.users.restore', $userAccount->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" title="Activate">
                                                <x-icons.restore />
                                            </button>
                                        </form>
                                    @else
                                        {{-- View / Edit / Delete Buttons --}}
                                        @if ($userAccount->role === 'Customer') 
                                            <button @click="showViewModal = true; selectedUser = {{ $userAccount->toJson() }}">
                                                <x-icons.view />    
                                            </button>
                                        @else
                                            <button @click="showEditModal = true; selectedUser = {{ $userAccount->toJson() }}">
                                                <x-icons.edit />
                                            </button>
                                        @endif

                                        <form action="{{ route('admin.users.delete', $userAccount->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit">
                                                <x-icons.delete />
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>    
                        </tr>    
                        @endforeach
                </tbody>
            </table>    


            {{-- Edit Modal --}}
            <div x-show="showEditModal" x-cloak x-transition class="modal">
                <div class="modal-form" @click.away="showEditModal = false">
                    <div class="flex">
                        <h2 class="text-center">Edit User</h2>
                        <x-icons.close class="close" @click="showEditModal = false"/>    
                    </div>
                    <form :action="'/users/' + selectedUser.id + '/update'" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id" x-model="selectedUser.id">

                        <div>
                            <label for="first_name">First Name</label>
                            <input readonly x-model="selectedUser.first_name">
                        </div>
                        <div>
                            <label for="last_name">Last Name</label>
                            <input readonly x-model="selectedUser.last_name">
                        </div>
                        <div>
                            <label>Contact Number</label>
                            <input readonly x-model="selectedUser.phone_number">
                        </div>
                        <div>
                            <label>Address</label>
                            <input readonly x-model="selectedUser.address">
                        </div>
                        <div>
                            <label for="email">Email</label>
                            <input type="email" name="email" x-model="selectedUser.email">
                        </div>
                        <div>
                            <label for="role">Role</label>
                            <select name="role" id="role" class="rounded" x-model="selectedUser.role">
                                <option value="Staff">Staff</option>
                                <option value="Admin">Admin</option>
                            </select>               
                        </div>

                        <button type="submit">Save</button>
                    </form>
                </div>
            </div>

            {{-- View Modal --}}
            <div x-show="showViewModal" x-cloak x-transition class="modal">
                <div class="modal-form" @click.away="showViewModal = false">
                    <div>
                        <h2 class="text-center">Customer Details</h2>
                        <x-icons.close class="close" @click="showViewModal = false"/>        
                    </div>

                    <form>
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id" x-model="selectedUser.id">

                        <div>
                            <label for="first_name">First Name</label>
                            <input type="text" name="first_name" x-model="selectedUser.first_name" readonly>
                        </div>
                        <div>
                            <label for="last_name">Last Name</label>
                            <input type="text" name="last_name" x-model="selectedUser.last_name" readonly>
                        </div>
                        <div>
                            <label>Contact Number</label>
                            <input readonly x-model="selectedUser.phone_number">
                        </div>
                        <div>
                            <label>Address</label>
                            <input readonly x-model="selectedUser.address">
                        </div>
                        <div>
                            <label for="email">Email</label>
                            <input type="text" name="email" x-model="selectedUser.email" readonly>
                        </div>
                        <div>
                            <label for="role">Role</label>
                            <input type="text" name="role" x-model="selectedUser.role" readonly>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        {{ $userAccounts->appends(request()->except('page_unverified'))->links() }}
    </section>
</x-dashboardlayout>
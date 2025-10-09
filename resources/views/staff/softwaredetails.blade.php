<x-dashboardlayout>
    <h2>Software Dashboard</h2>

    <div class="header-container" x-data="{ showAddModal: false }">
        <button class="modal-button" @click="showAddModal = true">
            Add Software
        </button>

        <div>
            <form action=" {{ route('staff.software-details.search') }}" method="GET">
                <input 
                    type="text"
                    name="search"
                    placeholder="Search software"
                    value="{{ request('search') }}"
                    class="search-bar"
                >
                <button type='submit'>
                    <x-icons.search class="search-icon"/>
                </button>
            </form>
        </div>
    
        {{-- ADD MODAL --}}
        <div x-show="showAddModal" x-cloak x-transition class="modal">
            <div class="add-software" @click.away="showAddModal = false">
                <div class="relative !m-0">
                    <h2 class="text-center w-[100%]">
                        ADD SOFTWARE
                        <x-icons.close class="close" @click="showAddModal = false"/>    
                    </h2>
                </div>
                <form class="software-form" 
                method="POST" action="{{ route('staff.software-details.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="software-details">
                        <div class="software-info">
                            <div>
                                <div class="software-input">
                                    <label for="">Software Name</label>
                                    <input placeholder="Enter software name" required type="text" name="name">
                                </div>

                                <div class="software-input">
                                    <label for="">Software Icon</label>
                                    <input type="file" name="icon" accept="image/*">
                                </div>
                            </div>
                            <div>
                                <div class="software-input">
                                    <label for="build_category_id">Category</label>
                                    <select required name="build_category_id" id="build_category_id" class="pt-0 pb-0 pl-1">
                                        <option disabled selected hidden value="">Select build category</option>   
                                        @foreach ($buildCategories as $category)
                                            <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>    
                            </div>
                        </div>
                        <div>
                            <p>Minimum System Requirements</p>

                            <div class="software-input">
                                <label for="">Operating System</label>
                                <input placeholder="Enter software name" type="text" name="os_min">
                            </div>   
                            
                            <div class="software-input">
                                <label for="">CPU</label>
                                <input placeholder="Enter software name" type="text" name="cpu_min">
                            </div>   

                            <div class="software-input">
                                <label for="">GPU</label>
                                <input placeholder="Enter software name" type="text" name="gpu_min">
                            </div>   

                            <div class="software-input">
                                <label for="">RAM</label>
                                <input placeholder="00 GB" name="ram_min" id="ram_min" type="number" step="2" onkeydown="return !['e','E','+','-'].includes(event.key)">
                            </div>   

                            <div class="software-input">
                                <label for="">Storage</label>
                                <input placeholder="00 GB" name="storage_min" id="storage_min" type="number" onkeydown="return !['e','E','+','-'].includes(event.key)">
                            </div>

                            <p>Recommended System Requirements</p>
                            
                            <div class="software-input">
                                <label for="">CPU</label>
                                <input placeholder="Enter software name" type="text" name="cpu_reco">
                            </div>   

                            <div class="software-input">
                                <label for="">GPU</label>
                                <input placeholder="Enter software name" type="text" name="gpu_reco">
                            </div>   

                            <div class="software-input">
                                <label for="">RAM</label>
                                <input placeholder="00 GB" name="ram_reco" id="ram_reco" type="number" step="2" onkeydown="return !['e','E','+','-'].includes(event.key)">
                            </div>   

                            <div class="software-input">
                                <label for="">Storage</label>
                                <input placeholder="00 GB" name="storage_reco" id="storage_reco" type="number" onkeydown="return !['e','E','+','-'].includes(event.key)">
                            </div>
                        </div>
                    </div>
                    <div>
                        <button>Add Software</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- TABLE --}}
    <section class="section-style !pl-0 !h-[65vh]">
        <div class="h-[55vh]"
        x-data="{ viewModal: false, editModal: false, selectedSoftware:{} }"> 
            <table class="table mb-3">
                <thead>
                    <tr class="text-sm">
                        <th class="text-center">Name</th>
                        <th class="text-center">Category</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($softwares as $software)
                        <tr class="hover:opacity-50 {{ $software->deleted_at ? 'bg-gray-300 opacity-50 cursor-not-allowed' : '' }}" 
                        @click="{{ $software->deleted_at ? '' : "viewModal = true; selectedSoftware = " . $software->toJson() }}">
                            <td class="text-center">{{ $software->name }}</td>
                            <td class="text-center">{{ $software->buildCategory->name }}</td>
                            <td>
                                @if($software->deleted_at)
                                <form action="{{ route('staff.software-details.restore', ['id' => $software->id]) }}" method="POST">
                                        @csrf
                                        @method('PATCH') <!-- or any method you use for restoring -->
                                        <button @click.stop type="submit">
                                            <x-icons.restore />
                                        </button>
                                    </form>
                                @else
                                <div class="flex justify-center gap-2">
                                    <button @click.stop @click="editModal = true; selectedSoftware = {{ $software->toJson() }}">
                                        <x-icons.edit />
                                    </button>
                                    <form action="{{ route('staff.software-details.delete', ['id' => $software->id] )}}" method="POST">
                                        @csrf
                                        <button @click.stop>
                                            <x-icons.delete />
                                        </button>
                                    </form>
                                </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>   
            
            {{-- VIEW MODAL --}}
            <div x-show="viewModal" x-cloak x-transition class="modal overflow-y-scroll p-5">
                <div class="add-software !w-1/3 flex flex-col justify-center" 
                @click.away="viewModal = false">
                    <div class="relative !m-0">
                        <h2 class="text-center w-[100%] font-medium">
                            Software Details
                            <x-icons.close class="close" 
                            @click="viewModal = false"/>    
                        </h2>
                    </div>
                    {{-- <pre x-text="JSON.stringify(selectedSoftware, null, 2)"></pre> --}}

                    <div class="software-name">
                        <div>
                            <img :src="'/storage/' + selectedSoftware.icon" alt="Software Icon" alt="Software Icon">
                        </div>
                        <div class="flex gap-3">
                            <p x-text="selectedSoftware.name"></p>
                        </div>
                    </div>
                    <div class="software-specs">
                        <h4 class="font-medium">Minimum System Requirements</h4>

                        <div>
                            <p>Operating System</p>
                            <p x-text="selectedSoftware.os_min ? selectedSoftware.os_min : '-'"></p>
                        </div>
                        <div>
                            <p>CPU</p>
                            <p x-text="selectedSoftware.cpu_min ? selectedSoftware.cpu_min : '-'"></p>
                        </div>
                        <div>
                            <p>GPU</p>
                            <p x-text="selectedSoftware.gpu_min ? selectedSoftware.gpu_min : '-'"></p>
                        </div>
                        <div>
                            <p>RAM</p>
                            <p x-text="selectedSoftware.ram_min ? selectedSoftware.ram_min : '-'"></p>
                        </div>
                        <div>
                            <p>Storage</p>
                            <p x-text="selectedSoftware.storage_min ? selectedSoftware.storage_min : '-'"></p>
                        </div>

                        <h4 class="font-medium">Recommended System Requirements</h4>

                        <div>
                            <p>CPU</p>
                            <p x-text="selectedSoftware.cpu_reco ? selectedSoftware.cpu_reco : '-'"></p>
                        </div>
                        <div>
                            <p>GPU</p>
                            <p x-text="selectedSoftware.gpu_reco ?selectedSoftware.gpu_reco : '-'"></p>
                        </div>
                        <div>
                            <p>RAM</p>
                            <p x-text="selectedSoftware.ram_reco ? selectedSoftware.ram_reco : '-'"></p>
                        </div>
                        <div>
                            <p>Storage</p>
                            <p x-text="selectedSoftware.storage_reco ? selectedSoftware.storage_reco : '-'"></p>
                        </div>
                        
                    </div>
                </div>
            </div>

            {{-- EDIT MODAL --}}
            <div x-show="editModal" x-cloak x-transition class="modal">
                <div class="add-software" 
                @click.away="editModal = false">
                    <div class="relative !m-0">
                        <h2 class="text-center w-[100%]">
                            Edit Software Details
                            <x-icons.close class="close" 
                            @click="editModal = false"/>    
                        </h2>
                    </div>

                    <form class="software-form" 
                    method="POST" :action="`/staff/software-details/update/${selectedSoftware.id}`" enctype="multipart/form-data">
                        @csrf
                        <div class="software-details">
                            <div class="software-info">
                                <div>
                                    <div class="software-input">
                                        <label for="">Software Name</label>
                                        <input x-model="selectedSoftware.name" required type="text" name="name">
                                    </div>

                                    <div class="software-input">
                                        <label for="">Software Icon</label>
                                        <input type="file" name="icon" accept="image/*">
                                    </div>
                                </div>
                                <div>
                                    <div class="software-input">
                                        <label for="build_category_id">Category</label>
                                        <select x-model="selectedSoftware.build_category_id" required name="build_category_id" id="build_category_id" class="pt-0 pb-0 pl-1">
                                            <option disabled selected hidden value="">Select build category</option>   
                                            @foreach ($buildCategories as $category)
                                                <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>    
                                </div>
                            </div>
                            <div>
                                <p>Minimum System Requirements</p>

                                <div class="software-input">
                                    <label for="">Operating System</label>
                                    <input x-model="selectedSoftware.os_min" type="text" name="os_min">
                                </div>   
                                
                                <div class="software-input">
                                    <label for="">CPU</label>
                                    <input x-model="selectedSoftware.cpu_min" type="text" name="cpu_min">
                                </div>   

                                <div class="software-input">
                                    <label for="">GPU</label>
                                    <input x-model="selectedSoftware.gpu_min" type="text" name="gpu_min">
                                </div>   

                                <div class="software-input">
                                    <label for="">RAM</label>
                                    <input x-model="selectedSoftware.ram_min" name="ram_min" id="ram_min" type="number" step="2" onkeydown="return !['e','E','+','-'].includes(event.key)">
                                </div>   

                                <div class="software-input">
                                    <label for="">Storage</label>
                                    <input x-model="selectedSoftware.storage_min" name="storage_min" id="storage_min" type="number" onkeydown="return !['e','E','+','-'].includes(event.key)">
                                </div>

                                <p>Recommended System Requirements</p>
                                
                                <div class="software-input">
                                    <label for="">CPU</label>
                                    <input x-model="selectedSoftware.cpu_reco" type="text" name="cpu_reco">
                                </div>   

                                <div class="software-input">
                                    <label for="">GPU</label>
                                    <input x-model="selectedSoftware.gpu_reco" type="text" name="gpu_reco">
                                </div>   

                                <div class="software-input">
                                    <label for="">RAM</label>
                                    <input x-model="selectedSoftware.ram_reco" name="ram_reco" id="ram_reco" type="number" step="2" onkeydown="return !['e','E','+','-'].includes(event.key)">
                                </div>   

                                <div class="software-input">
                                    <label for="">Storage</label>
                                    <input x-model="selectedSoftware.storage_reco" name="storage_reco" id="storage_reco" type="number" onkeydown="return !['e','E','+','-'].includes(event.key)">
                                </div>
                            </div>
                        </div>
                        <div>
                            <button>Update Software</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        {{ $softwares->links() }}
    </section>

</x-dashboardlayout>

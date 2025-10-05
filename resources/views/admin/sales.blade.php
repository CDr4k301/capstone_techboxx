<x-dashboardlayout>
    <h2>Sales Report</h2>
    <div class="header-container" x-data="{ showStockInModal: false, showStockOutModal: false, componentModal: null }">
        <div>
        </div>
        <div>
            <form action=" {{ route('staff.inventory.search') }}" method="GET">
                <input 
                    type="text"
                    name="search"
                    placeholder="Search components"
                    value="{{ request('search') }}"
                    class="search-bar"
                >
                <button type='submit'>
                    <x-icons.search class="search-icon"/>
                </button>
            </form>
        </div>
    </div>

    {{-- TABLE --}}
    <section class="section-style !pl-0 !h-[65vh]">
        <div>
            <table class="table">
                <thead>
                    <tr class="text-sm">
                        <th>Date</th>
                        <th>Component</th>
                        <th>Category</th>
                        <th>Customer</th>
                        <th>Qty</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
            </table> 
        </div>

        <div x-data="{ showStockInCompModal: false, showStockOutCompModal: false, selectedComponent:{} }" class="overflow-y-scroll">
            <table class="table">
                <tbody>
                    
                </tbody>
            </table>

            
    </section>
</x-dashboardlayout>

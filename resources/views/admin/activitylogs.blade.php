<x-dashboardlayout>
    <h2>Activity Logs</h2>

    {{-- TABLE --}}
    <section class="section-style !pl-0 !h-[65vh]">
        <div>
            <table class="table">
                <thead>
                    <tr class="text-sm">
                        <th>User</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>Date</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $log->user->name }}</div>
                            <div class="text-sm text-gray-500">{{ $log->user->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ $log->description }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $log->created_at->format('M d, Y h:i A') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $log->ip_address }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table> 
        </div>
    </section>
    {{ $logs->links() }}

</x-dashboardlayout>

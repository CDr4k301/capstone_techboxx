<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Welcome</title>

    @vite([
        'resources\css\app.css', 
        'resources\css\admin-staff\sidebar.css',
        'resources\css\admin-staff\header.css',
        'resources\css\customer\profile.css', 
        'resources\css\admin-staff\table.css', 
        'resources\css\admin-staff\modal.css',
        'resources\css\admin-staff\form.css',
        'resources\css\customer\header.css', 
        'resources\js\app.js',
        'resources\js\addComponent.js',
        'resources\js\order.js',
        'resources\js\customerdashboard.js',
        ])

</head>
<body class="body">

    @if (session('message'))
    <x-message :type="session('type')">
        {{ session('message') }}
    </x-message>
    
    @endif
    {{-- Display a dynamic header base on user information --}}
    @if (Auth::user()->role === 'Customer')
        <x-customerheader :email="Auth::user()->email" >
            {{ Auth::user()->first_name }}
        </x-customerheader >
    @else
        <x-dashboardheader :email="Auth::user()->email" :role="Auth::user()->role">
            {{-- retrieves the current authenticated user --}}
            {{ Auth::user()->first_name }}
        </x-dashboardheader>
    @endif

    {{-- Display a dynamic sidebar heading base on user --}}
    <x-adminsidenav :role="Auth::user()?->role" />

    <main class="main-content shadow-[-2px_0_4px_rgba(0,0,0,0.3)]">
        {{ $slot }}
    </main>

    
</body>
</html>
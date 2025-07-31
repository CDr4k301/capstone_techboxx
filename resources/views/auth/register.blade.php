<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Register</title>

    @vite(['resources\css\app.css', 'resources\css\verificationform.css'])

</head>
<body>
    <form action="{{ route('register') }}" method="POST" enctype="multipart/form-data" class="form">
        @csrf
        <x-logoheader name="header">
            <x-slot name="header">
                <h1>Sign up</h1>      
                <p>Already have an account? <a href="{{ route('login') }}">Login</a></p> 
            </x-slot>
        
            <div class="flex gap-2">
                <div>
                    <label for="first_name">First Name</label>
                    <input type="text" name="first_name">
                </div>

                <div>
                    <label for="last_name">Last Name</label>
                    <input type="text" name="last_name">
                </div>    
            </div>

            <div>
                <label for="email">Email</label>
                <input type="email" name="email">
            </div>

            <div>
                <label for="password">Password</label>
                <input type="password" name="password">
            </div>

            <div>
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" name="password_confirmation">
            </div>

            <div>
                <label for="id_uploaded" class="upload-label">
                    Upload a valid ID
                    <x-icons.info/>
                </label>    
                
                <div class="id-upload">
                    <input type="file" id="id_uploaded" name="id_uploaded" accept="image/*" class="custom-file" onchange="updateFileName(this)">

                    {{-- upload icon --}}
                    <label for="id_uploaded">
                        <x-icons.upload />    
                    </label>

                    {{-- show the file name --}}
                    <p id="filename" class="filename"></p>
                </div>
            </div>
        </x-logoheader>

        {{-- validation errors --}}
        @if ($errors->any())
            <ul class=" text-left text-xs text-red-500">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <x-usersbutton label="Register"/>
        
    </form>

    {{-- showing the name of the uploaded file --}}
    <script>
        function updateFileName(input) {
            const fileNameDisplay = document.getElementById('filename');
            const file = input.files[0];

            if (file) {
                fileNameDisplay.textContent = file.name;
            } else {
                fileNameDisplay.textContent = '';
            }
        }
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Two Factor Challenge - {{ config('app.name') }}</title>

    {{ mix('resources/css/app.css') }}

</head>

<body class="p-0 m-0">
<div class="vh-100 d-flex justify-content-center align-items-center">
    <!-- container -->
    <div style="width: 500px; max-width: 100%;">
        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif
        <div class="card">
            <div class="card-body">

                @if(session()->get('success'))
                    <div class="alert alert-success">
                        <strong>Success!</strong>
                        {{ session()->get('success') }}
                    </div>
                @endif

                @if(session()->get('error'))
                    <div class="alert alert-danger">
                        <strong>Error!</strong>
                        {{ session()->get('error') }}
                    </div>
                @endif


                @yield('content')
            </div>
        </div>
    </div>
</div>
</body>

</html>

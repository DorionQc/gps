<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ config('app.name', 'Laravel') }}</title>
    <!-- Jquery (cuz everything depends on it nowadays -->
    <script src="{{ asset('js/vendor/jquery.js') }}"></script>
    <!-- Styles -->
    <link href="{{ asset('css/foundation.min.css') }}" rel="stylesheet">
    <!-- JS -->
    <script src="{{ asset('js/vendor/foundation.min.js') }}"></script>
    <script src="{{ asset('js/vendor/what-input.js') }}"></script>
    
    <!-- Actual code -->
    <script src="{{ asset('js/app.js') }}"></script>
    
    <!-- Actual style -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    
	@stack('head')
	
	@stack('head_end')
    
</head>
<body>
    <div id="app">
    	@include('layouts/partials/header')

        <main class="py-4">
            @yield('content')
        </main>
    </div>
    
</body>
</html>

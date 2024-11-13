<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="{{ asset('images/2.png') }}">

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <!-- Scripts -->
    {{-- <script src='https://unpkg.com/webex/umd/calling.min.js'></script> --}}
    <!-- Add inside the <head> tag in your Blade template -->
    <meta name="webex-access-token" content="{{ env('VITE_WEBEX_ACCESS_TOKEN') }}">
    <!-- Add inside the <head> tag in your Blade template, before your Vue app scripts -->
    <script>
        window.WEBEX_ACCESS_TOKEN = "{{ env('VITE_WEBEX_ACCESS_TOKEN') }}";
        // console.log(window.WEBEX_ACCESS_TOKEN);

    </script>



    @routes
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
</head>

<body class="font-sans antialiased h-screen overflow-y-hidden">
    @inertia
</body>

</html>

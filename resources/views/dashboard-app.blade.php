<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="x-ua-compatible" content="ie=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Avantiy 2026 - Development</title>
        {{-- Favicon set (generated from resources/icons/avantiy-icon.svg via npm run favicons) --}}
        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any" />
        <link rel="icon" href="{{ asset('images/favicon.svg') }}" type="image/svg+xml" />
        <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}" />
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32x32.png') }}" />
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon-16x16.png') }}" />
        @vite(['resources/js/app.js'])
        @vite(['resources/css/app.css'])
    </head>
    <body>
        <div id="app"></div>
    </body>
</html>

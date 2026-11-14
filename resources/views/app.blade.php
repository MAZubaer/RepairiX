<!doctype html>
<html lang="en" class="scroll-smooth">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0a9488">
    <title inertia>{{ config('app.name', 'RepairiX') }}</title>
    <link rel="icon" href="{{ asset('images/repairix_short.png') }}" type="image/png" />
    <link rel="apple-touch-icon" href="{{ asset('images/repairix_short.png') }}" />
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800|noto-sans-bengali:500,700&display=swap" rel="stylesheet" />
    @inertiaHead
    @vite(['resources/js/app.js', 'resources/css/app.css'])
  </head>
  <body class="min-h-screen">
    @inertia
  </body>
</html>

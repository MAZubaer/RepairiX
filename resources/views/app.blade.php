<!doctype html>
<html lang="en" class="scroll-smooth">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0a9488">
    <script>
      // Apply night mode before the page paints (no white flash). See resources/js/lib/theme.js.
      (function () {
        var saved = null;
        try { saved = localStorage.getItem('repairix-theme'); } catch (e) {}
        var dark = saved ? saved === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
        if (dark) {
          document.documentElement.classList.add('dark');
          document.querySelector('meta[name="theme-color"]').setAttribute('content', '#020617');
        }
      })();
    </script>
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

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Repairix') }}</title>
    @inertiaHead
    @vite(['resources/js/app.js', 'resources/css/app.css'])
  </head>
  <body>
    @inertia

    <script>
      (function(){
        try{
          var s = document.querySelector('script[type="application/json"]');
          var appEl = document.getElementById('app');
          if(s && appEl){
            var txt = (s.textContent || s.innerText || '').trim();
            if(txt) appEl.dataset.page = txt;
          }
        }catch(e){/* no-op */}
      })();
    </script>
  </body>
</html>

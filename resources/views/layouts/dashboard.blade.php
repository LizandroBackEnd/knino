<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>K-NINO</title>

  <!-- Favicon -->
  <link rel="icon" type="image/png" href="{{ asset('logos/icon.png') }}">
  <link rel="shortcut icon" href="{{ asset('logos/icon.png') }}" />

       <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap"
        rel="stylesheet">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap"
        rel="stylesheet">

  @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dashboard-router.js', 'resources/js/toast.js', 'resources/js/alertDelete.js', 'resources/js/logout.js', 'resources/js/role-ui.js'])
</head>

<body class="min-h-screen bg-neutral">
  <div class="flex">
        @php $isFragment = request()->ajax() || request()->header('X-Requested-With') === 'XMLHttpRequest' || request()->query('ajax') == '1'; @endphp
        @if($isFragment)
          <div id="dashboard-content">
            @yield('content')
          </div>
        @else
          <x-sidebar />

          <div class="flex-1 min-h-screen bg-neutral">
            <x-header />

            <main>
              <div id="dashboard-content">
                @yield('content')
              </div>
            </main>
          </div>
        @endif
  </div>

</body>

</html>

<!doctype html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>Iniciar sesión - K-NINO</title>

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet">

	@vite([
	    'resources/css/app.css',
	    'resources/css/global.css',
	    'resources/js/toast.js',
	    'resources/js/login-forgot.js',
	    'resources/js/login.js'
	])

</head>
<body class="antialiased">
	<div class="min-h-screen flex flex-col lg:flex-row">
		<!-- Left / Form -->
		<div class="lg:w-1/2 flex items-center justify-center p-12" style="background: linear-gradient(120deg,#fbf6ee 0%, #f3fbf9 100%);">
			<div class="w-full max-w-lg">
				<div class="flex items-center gap-4 mb-6">
					<div class="w-18 h-18 rounded-lg bg-white/60 flex items-center justify-center shadow">
						<img src="{{ asset('logos/icon.png') }}" alt="K-NINO" class="w-14">
					</div>
					<div>
						<div class="font-extrabold text-lg text-[var(--green)]">K-NINO</div>
						<div class="text-sm text-gray-500">Veterinaria</div>
					</div>
				</div>

				<h1 class="text-2xl font-bold text-[var(--green)] mb-1">Bienvenido a K-NINO</h1>
				<p class="text-gray-500 mb-6">Sistema de Gestión Veterinaria</p>

				<div class="bg-white rounded-xl shadow p-6">
					<h3 class="text-lg font-semibold mb-2">Iniciar Sesión</h3>
					<p class="text-sm text-gray-500 mb-4">Ingresa tus credenciales para acceder al sistema</p>

					<form id="login-form" action="#" method="POST" class="space-y-4">
						<div>
							<label for="email" class="form-label">Correo</label>
							<input id="email" name="email" type="email" class="form-control w-full" placeholder="Ingresa tu correo">
						</div>

						<div>
							<label for="password" class="form-label">Contraseña</label>
							<input id="password" name="password" type="password" class="form-control w-full" placeholder="Ingresa tu contraseña">
						</div>

						<div class="flex items-center justify-end">
							<a id="forgot-password-link" href="#" class="text-sm font-semibold text-[var(--green)]">¿Olvidaste tu contraseña?</a>
						</div>

						<button type="submit" class="btn-green w-full py-3 rounded-md font-semibold text-white">Iniciar Sesión</button>
					</form>

				</div>
			</div>
		</div>

		<div class="lg:w-1/2 flex items-center justify-center p-12" style="background: linear-gradient(135deg,var(--green) 0%, #45c6d8 60%, #6ec6ff 100%); color:white;">
			<div class="text-center max-w-md">
				<h2 class="text-3xl font-extrabold mt-4">Cuidando a tus mejores amigos</h2>
				<p class="mt-6 text-white/95 text-lg">Gestión integral para tu clínica veterinaria en Ciudad Nezahualcóyotl</p>
			</div>
		</div>
	</div>
</body>
</html>


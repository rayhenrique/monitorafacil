<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Acesso do gestor · {{ trim($settings['municipio_nome'] ?? '') ?: 'Monitora Fácil' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    <main class="mx-auto flex min-h-screen w-full max-w-md flex-col justify-center px-4 py-8 sm:px-6 sm:py-12">
        <div class="mb-8">
            @if (filled($settings['logo_path'] ?? null))
                <img src="{{ asset('storage/' . ltrim($settings['logo_path'], '/')) }}" alt="Logotipo de {{ trim($settings['municipio_nome'] ?? '') ?: 'município' }}" class="mb-5 h-12 w-12 rounded-xl object-contain">
            @else
                <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-teal-800 text-lg font-bold text-white">MF</div>
            @endif
            <p class="text-sm font-semibold uppercase tracking-widest text-teal-800">Monitora Fácil</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight">Acesso do gestor</h1>
            <p class="mt-2 text-sm text-slate-600">Entre para acompanhar os dados consolidados do município.</p>
        </div>

        <form method="post" action="{{ route('login') }}" class="space-y-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" novalidate>
            @csrf
            <div>
                <label for="email" class="mb-2 block text-sm font-medium">E-mail</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" autofocus required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-200">
                @error('email') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="password" class="mb-2 block text-sm font-medium">Senha</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-200">
                @error('password') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="remember" class="rounded border-slate-300 text-teal-800 focus:ring-teal-600">
                Manter conectado
            </label>
            <button type="submit" class="w-full rounded-lg bg-teal-800 px-4 py-3 font-semibold text-white hover:bg-teal-900 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2">Entrar</button>
        </form>
    </main>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard' }} — MeetSC</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-brand-50 font-sans text-slate-900">
    <div class="mx-auto flex min-h-screen max-w-[1600px]">
        <x-app.sidebar />

        <div class="flex-1 px-6 py-6 sm:px-10 sm:py-8">
            <x-app.topbar />

            <main class="mt-8">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>

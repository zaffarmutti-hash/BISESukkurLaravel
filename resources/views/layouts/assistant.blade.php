<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Assistant Controller') - BISE Sukkur</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="/css/superadmin.css">
    <style>
        :root {
            --assistant-ink: #142b3b;
            --assistant-muted: #607383;
            --assistant-border: #dce6e8;
            --assistant-shadow: 0 12px 30px rgba(20, 43, 59, 0.07);
        }

        * { box-sizing: border-box; }

        body {
            min-height: 100vh;
            margin: 0;
            color: var(--assistant-ink);
            background: linear-gradient(140deg, #f3f8fa 0%, #f7f8f3 52%, #f4f7fb 100%);
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .assistant-shell { min-height: 100vh; }
        .assistant-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem max(1rem, calc((100vw - 1200px) / 2));
            border-bottom: 1px solid var(--assistant-border);
            background: rgba(255, 255, 255, 0.86);
        }
        .assistant-brand { color: var(--assistant-ink); font-size: 0.95rem; font-weight: 800; text-decoration: none; }
        .assistant-user { display: flex; align-items: center; gap: 1rem; color: var(--assistant-muted); font-size: 0.85rem; font-weight: 600; }
        .assistant-logout { padding: 0.5rem 0.8rem; border: 1px solid var(--assistant-border); border-radius: 8px; color: var(--assistant-ink); background: #fff; font: inherit; font-size: 0.8rem; font-weight: 700; cursor: pointer; }
        .assistant-main { width: min(100% - 2rem, 1200px); margin: 2rem auto; }

        @media (max-width: 520px) {
            .assistant-topbar { align-items: flex-start; flex-direction: column; }
            .assistant-user { width: 100%; justify-content: space-between; }
            .assistant-main { margin: 1rem auto; }
        }
    </style>
</head>
<body>
    <div class="assistant-shell">
        <header class="assistant-topbar">
            <a class="assistant-brand" href="{{ route('assistant.dashboard') }}">BISE SUKKUR / ASSISTANT CONTROLLER</a>
            <div class="assistant-user">
                <span>{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="assistant-logout" type="submit">Sign out</button>
                </form>
            </div>
        </header>
        <main class="assistant-main">
            @yield('content')
        </main>
    </div>
</body>
</html>

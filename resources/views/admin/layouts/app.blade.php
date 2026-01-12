<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') - VIBE Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Space+Mono:wght@400;700&display=swap"
        rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cream: '#fffef0',
                        brutal: {
                            orange: '#ff5c00',
                            yellow: '#facc15',
                            purple: '#7c3aed',
                            dark: '#1a1a1a',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Space Grotesk', sans-serif;
        }

        .font-mono {
            font-family: 'Space Mono', monospace;
        }

        .brutal-shadow {
            box-shadow: 4px 4px 0px #1a1a1a;
        }

        .brutal-shadow-sm {
            box-shadow: 3px 3px 0px #1a1a1a;
        }

        .brutal-border {
            border: 3px solid #1a1a1a;
        }

        .nav-link {
            transition: all 0.15s ease;
        }

        .nav-link:hover {
            transform: translateX(5px);
        }

        .nav-link.active {
            background: #facc15;
            color: #1a1a1a;
            font-weight: 700;
        }
    </style>
</head>

<body class="bg-cream">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-72 bg-brutal-dark text-white brutal-border border-l-0 border-t-0 border-b-0">
            <div class="p-6 border-b-3 border-brutal-yellow">
                <h1 class="text-2xl font-bold font-mono text-brutal-yellow">⚡ VIBE ADMIN</h1>
            </div>
            <nav class="mt-6 px-4 space-y-2">
                <a href="{{ route('admin.dashboard') }}"
                    class="nav-link flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'active brutal-border bg-brutal-yellow text-brutal-dark' : 'hover:bg-white/10' }}">
                    <span class="text-xl mr-3">📊</span>
                    <span class="font-mono font-bold">Dashboard</span>
                </a>
                <a href="{{ route('admin.products.index') }}"
                    class="nav-link flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('admin.products.*') ? 'active brutal-border bg-brutal-yellow text-brutal-dark' : 'hover:bg-white/10' }}">
                    <span class="text-xl mr-3">📦</span>
                    <span class="font-mono font-bold">Produits</span>
                </a>
                <a href="{{ route('admin.orders.index') }}"
                    class="nav-link flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('admin.orders.*') ? 'active brutal-border bg-brutal-yellow text-brutal-dark' : 'hover:bg-white/10' }}">
                    <span class="text-xl mr-3">🛒</span>
                    <span class="font-mono font-bold">Commandes</span>
                </a>
            </nav>

            <div class="absolute bottom-0 w-72 p-4 border-t-3 border-white/20">
                <div class="text-sm text-gray-400 mb-3 font-mono">{{ Auth::user()->name ?? 'Admin' }}</div>
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="flex items-center text-red-400 hover:text-red-300 w-full font-mono font-bold">
                        <span class="text-xl mr-3">🚪</span>
                        Déconnexion
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            @if(session('success'))
                <div class="bg-green-100 brutal-border brutal-shadow text-green-800 px-4 py-3 mb-6 font-bold">
                    ✅ {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 brutal-border brutal-shadow text-red-800 px-4 py-3 mb-6 font-bold">
                    ❌ {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-100 brutal-border brutal-shadow text-red-800 px-4 py-3 mb-6 font-bold">
                    ⚠️
                    <ul class="ml-6 list-disc">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>

</html>
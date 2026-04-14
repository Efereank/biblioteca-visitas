<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Biblioteca Pública del Zulia "María Calcaño"</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50 antialiased">
    <div class="min-h-screen flex flex-col">
        {{--  Banner --}}
        <header class="relative h-48 md:h-64 bg-cover bg-center" style="background-image: url('{{ asset('images/biblioteca.jpg') }}')">
            <div class="absolute inset-0 bg-black bg-opacity-50"></div>
            <div class="relative container mx-auto px-4 h-full flex flex-col justify-center">
                <h1 class="text-white text-3xl md:text-5xl font-bold drop-shadow-lg">Biblioteca Pública del Zulia</h1>
                <p class="text-white text-lg md:text-xl mt-1 md:mt-2 drop-shadow">"María Calcaño"</p>
            </div>
        </header>

        {{-- Navegación responsive con menú hamburguesa --}}
        <nav x-data="{ mobileOpen: false }" class="bg-white shadow-md sticky top-0 z-40">
            <div class="container mx-auto px-4">
                <div class="flex items-center justify-between h-16">
                    {{-- Logo --}}
                    <div class="flex-shrink-0 md:hidden">
                        <span class="text-gray-800 font-semibold">Menú</span>
                    </div>

                    {{-- Menú desktop --}}
                    <div class="hidden md:flex md:space-x-8">
                        <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('dashboard') ? 'text-blue-600 border-b-2 border-blue-600' : '' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('visitas.create') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('visitas.create') ? 'text-blue-600 border-b-2 border-blue-600' : '' }}">
                            Registro Visita
                        </a>
                        <a href="{{ route('visitas.historial') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('visitas.historial') ? 'text-blue-600 border-b-2 border-blue-600' : '' }}">
                            Historial
                        </a>
                        <a href="{{ route('visitantes.index') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('visitantes.index') ? 'text-blue-600 border-b-2 border-blue-600' : '' }}">
                            Visitantes
                        </a>
                        <a href="{{ route('reportes') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('reportes') ? 'text-blue-600 border-b-2 border-blue-600' : '' }}">
                            Reportes
                        </a>
                    </div>

                    {{-- Botón hamburguesa --}}
                    <div class="md:hidden">
                        <button @click="mobileOpen = !mobileOpen" class="text-gray-700 hover:text-blue-600 focus:outline-none">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path x-show="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                <path x-show="mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Menú móvil desplegable --}}
                <div x-show="mobileOpen" x-cloak class="md:hidden pb-4 space-y-2" @click.away="mobileOpen = false">
                    <a href="{{ route('dashboard') }}" class="block text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('dashboard') ? 'text-blue-600 bg-blue-50' : '' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('visitas.create') }}" class="block text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('visitas.create') ? 'text-blue-600 bg-blue-50' : '' }}">
                        Registro Visita
                    </a>
                    <a href="{{ route('visitas.historial') }}" class="block text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('visitas.historial') ? 'text-blue-600 bg-blue-50' : '' }}">
                        Historial
                    </a>
                    <a href="{{ route('visitantes.index') }}" class="block text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('visitantes.index') ? 'text-blue-600 bg-blue-50' : '' }}">
                        Visitantes
                    </a>
                    <a href="{{ route('reportes') }}" class="block text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('reportes') ? 'text-blue-600 bg-blue-50' : '' }}">
                        Reportes
                    </a>
                </div>
            </div>
        </nav>

        {{-- Contenido principal --}}
        <main class="flex-grow container mx-auto px-4 py-6 md:py-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6 text-sm md:text-base">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6 text-sm md:text-base">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>

        {{-- Footer --}}
        <footer class="bg-white border-t py-4 mt-auto">
            <div class="container mx-auto px-4 text-center text-gray-500 text-sm">
                &copy; {{ date('Y') }} Biblioteca Pública del Zulia "María Calcaño"
            </div>
        </footer>
    </div>

    @stack('scripts')
</body>
</html>

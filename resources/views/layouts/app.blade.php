<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Biblioteca Pública del Zulia "María Calcaño"</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.jpeg') }}">

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

        {{-- Navegación --}}
        <nav x-data="{ mobileOpen: false }" class="bg-white shadow-md sticky top-0 z-40">
            <div class="container mx-auto px-4">
                <div class="flex items-center justify-between h-16">
                    {{-- Logo y menú desktop --}}
                    <div class="flex items-center gap-6">
                        {{-- Logo --}}
                        <a href="/" class="flex-shrink-0">
                            <img src="{{ asset('img/logo2.png') }}" alt="Logo" class="h-10 w-auto">
                        </a>

                        {{-- Menú desktop --}}
                        <div class="hidden md:flex md:space-x-4">
                            {{-- Dashboard y Reportes solo admin --}}
                            @if(Auth::user()->isAdmin())
                            <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('dashboard') ? 'text-blue-600 border-b-2 border-blue-600' : '' }}">
                                Dashboard
                            </a>
                            @endif

                            <a href="{{ route('visitas.create') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('visitas.create') ? 'text-blue-600 border-b-2 border-blue-600' : '' }}">
                                Registro Visita
                            </a>
                            <a href="{{ route('visitas.historial') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('visitas.historial') ? 'text-blue-600 border-b-2 border-blue-600' : '' }}">
                                Historial
                            </a>

                            {{-- Visitantes solo admin y recepcionista --}}
                            @if(!Auth::user()->isBibliotecario())
                            <a href="{{ route('visitantes.index') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('visitantes.index') ? 'text-blue-600 border-b-2 border-blue-600' : '' }}">
                                Visitantes
                            </a>
                            @endif

                            {{-- Reportes solo admin --}}
                            @if(Auth::user()->isAdmin())
                            <a href="{{ route('reportes') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('reportes') ? 'text-blue-600 border-b-2 border-blue-600' : '' }}">
                                Reportes
                            </a>
                            @endif

                            {{-- Menú Administración solo admin --}}
                            @if(Auth::user()->isAdmin())
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium flex items-center gap-1">
                                    Administración
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                    <div x-show="open" x-cloak @click.away="open = false" class="absolute left-0 mt-2 w-56 bg-white rounded-md shadow-lg py-1 z-50 border">
                                    <a href="{{ route('tipos-visitante.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('tipos-visitante.*') ? 'bg-blue-50 text-blue-600' : '' }}"> Tipos de Visitante</a>
                                    <a href="{{ route('propositos-visita.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('propositos-visita.*') ? 'bg-blue-50 text-blue-600' : '' }}"> Propósitos de Visita</a>
                                    <a href="{{ route('actividades.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('actividades.*') ? 'bg-blue-50 text-blue-600' : '' }}"> Actividades</a>
                                    <a href="{{ route('salas.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('salas.*') ? 'bg-blue-50 text-blue-600' : '' }}"> Salas</a>
                                    <a href="{{ route('perfiles-interes.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('perfiles-interes.*') ? 'bg-blue-50 text-blue-600' : '' }}"> Perfiles de Interés</a>
                                    <a href="{{ route('subcategorias-interes.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('subcategorias-interes.*') ? 'bg-blue-50 text-blue-600' : '' }}"> Subcategorías</a>
                                    <a href="{{ route('users.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('users.*') ? 'bg-blue-50 text-blue-600' : '' }}"> Usuarios</a>

                                    <a href="{{ route('municipios.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('municipios.*') ? 'bg-blue-50 text-blue-600' : '' }}">Municipios</a>
                                    <a href="{{ route('parroquias.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('parroquias.*') ? 'bg-blue-50 text-blue-600' : '' }}">Parroquias</a>
                                    <a href="{{ route('ciudades.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('ciudades.*') ? 'bg-blue-50 text-blue-600' : '' }}">Ciudades</a>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Menú de usuario --}}
                    <div class="hidden md:flex items-center">
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                    <div>{{ Auth::user()->name }}</div>
                                    <div class="ml-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('profile.edit')">
                                    {{ __('Mi Perfil') }}
                                </x-dropdown-link>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-dropdown-link :href="route('logout')"
                                            onclick="event.preventDefault();
                                                        this.closest('form').submit();">
                                        {{ __('Cerrar Sesión') }}
                                    </x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>

                    {{-- Botón hamburguesa móvil --}}
                    <div class="md:hidden">
                        <button @click="mobileOpen = !mobileOpen" class="text-gray-700 hover:text-blue-600 focus:outline-none">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path x-show="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                <path x-show="mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Menú móvil --}}
                <div x-show="mobileOpen" x-cloak class="md:hidden pb-4 space-y-2" @click.away="mobileOpen = false">
                    @if(Auth::user()->isAdmin())
                    <a href="{{ route('dashboard') }}" class="block text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-base font-medium">Dashboard</a>
                    @endif

                    <a href="{{ route('visitas.create') }}" class="block text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-base font-medium">Registro Visita</a>
                    <a href="{{ route('visitas.historial') }}" class="block text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-base font-medium">Historial</a>

                    @if(!Auth::user()->isBibliotecario())
                    <a href="{{ route('visitantes.index') }}" class="block text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-base font-medium">Visitantes</a>
                    @endif

                    @if(Auth::user()->isAdmin())
                    <a href="{{ route('reportes') }}" class="block text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-base font-medium">Reportes</a>

                    <hr class="my-2">
                    <p class="px-3 py-1 text-xs text-gray-500 uppercase">Administración</p>
                    <a href="{{ route('tipos-visitante.index') }}" class="block text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-base font-medium"> Tipos de Visitante</a>
                    <a href="{{ route('propositos-visita.index') }}" class="block text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-base font-medium"> Propósitos de Visita</a>
                    <a href="{{ route('actividades.index') }}" class="block text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-base font-medium"> Actividades</a>
                    <a href="{{ route('salas.index') }}" class="block text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-base font-medium"> Salas</a>
                    <a href="{{ route('perfiles-interes.index') }}" class="block text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-base font-medium"> Perfiles de Interés</a>
                    <a href="{{ route('subcategorias-interes.index') }}" class="block text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-base font-medium"> Subcategorías</a>
                    <a href="{{ route('users.index') }}" class="block text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-base font-medium"> Usuarios</a>
                    @endif

                    <hr class="my-2">
                    <div class="px-3 py-2 text-sm text-gray-500">{{ Auth::user()->name }}</div>
                    <a href="{{ route('profile.edit') }}" class="block text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-base font-medium">Perfil</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-base font-medium">
                            Cerrar sesión
                        </button>
                    </form>
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

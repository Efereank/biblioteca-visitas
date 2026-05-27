@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="p-6 flex justify-between items-center border-b">
            <h2 class="text-xl font-bold text-gray-800">Usuarios del sistema</h2>
            <a href="{{ route('users.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm">
                + Nuevo usuario
            </a>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rol</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Salas</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($users as $user)
                <tr>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $user->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $user->email }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        @if($user->isAdmin())
                            <span class="px-2 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">Admin</span>
                        @elseif($user->isBibliotecario())
                            <span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">Bibliotecario</span>
                        @else
                            <span class="px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">Recepcionista</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        @if($user->isBibliotecario())
                            {{ $user->salas->pluck('nombre')->join(', ') ?: 'Sin salas asignadas' }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm space-x-2">
                        <a href="{{ route('users.edit', $user) }}" class="text-blue-600 hover:underline">Editar</a>
                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar usuario?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

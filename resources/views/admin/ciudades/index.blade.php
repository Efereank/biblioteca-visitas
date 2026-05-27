@extends('layouts.app')

@section('content')
<div class="bg-white rounded-xl shadow-lg overflow-hidden">
    <div class="px-6 py-4 border-b flex justify-between items-center">
        <h2 class="text-xl font-bold text-gray-800">Ciudades</h2>
        <a href="{{ route('ciudades.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm font-medium">
            + Nueva Ciudad
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Municipio</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($ciudades as $ciudad)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $ciudad->nombre }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $ciudad->municipio->nombre ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-right">
                        <a href="{{ route('ciudades.edit', $ciudad) }}" class="text-blue-600 hover:text-blue-800 mr-3">Editar</a>
                        <form action="{{ route('ciudades.destroy', $ciudad) }}" method="POST" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800" onclick="return confirm('¿Eliminar esta ciudad?')">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-6 py-8 text-center text-gray-500">No hay ciudades registradas.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

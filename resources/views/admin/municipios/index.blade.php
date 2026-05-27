@extends('layouts.app')

@section('content')
<div class="bg-white rounded-xl shadow-lg overflow-hidden">
    <div class="px-6 py-4 border-b flex justify-between items-center">
        <h2 class="text-xl font-bold text-gray-800">Municipios</h2>
        <a href="{{ route('municipios.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm font-medium">
            + Nuevo Municipio
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Capital</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Parroquias</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Ciudades</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($municipios as $municipio)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $municipio->nombre }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $municipio->capital ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-center text-gray-500">{{ $municipio->parroquias_count }}</td>
                    <td class="px-6 py-4 text-sm text-center text-gray-500">{{ $municipio->ciudades_count }}</td>
                    <td class="px-6 py-4 text-sm text-right">
                        <a href="{{ route('municipios.edit', $municipio) }}" class="text-blue-600 hover:text-blue-800 mr-3">Editar</a>
                        <form action="{{ route('municipios.destroy', $municipio) }}" method="POST" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800" onclick="return confirm('¿Eliminar este municipio?')">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">No hay municipios registrados.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

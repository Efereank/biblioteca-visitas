@extends('layouts.app')

@section('content')
<div x-data="crudManager('salas', 'nombre', 'descripcion')" class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="p-6 flex justify-between items-center border-b">
            <h2 class="text-xl font-bold text-gray-800">Salas</h2>
            <button @click="openModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm">+ Agregar</button>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descripción</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($items as $item)
                <tr>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $item->nombre }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $item->descripcion ?? 'Sin descripción' }}</td>
                    <td class="px-6 py-4 text-sm space-x-2">
                        <button @click="editItem({{ $item->id }}, '{{ $item->nombre }}', '{{ $item->descripcion }}')" class="text-blue-600 hover:underline">Editar</button>
                        <button @click="deleteItem({{ $item->id }})" class="text-red-600 hover:underline">Eliminar</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @include('admin.partials.modal')
</div>
@endsection

@extends('layouts.app')

@section('content')
<div x-data="crudManager('perfiles-interes', 'nombre')" class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="p-6 flex justify-between items-center border-b">
            <h2 class="text-xl font-bold text-gray-800">Perfiles de Interés</h2>
            <button @click="openModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm">+ Agregar Perfil</button>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Perfil</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subcategorías</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($perfiles as $perfil)
                <tr>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $perfil->nombre }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $perfil->subcategorias_count }}</td>
                    <td class="px-6 py-4 text-sm space-x-2">
                        <button @click="editItem({{ $perfil->id }}, '{{ $perfil->nombre }}')" class="text-blue-600 hover:underline">Editar</button>
                        <button @click="deleteItem({{ $perfil->id }})" class="text-red-600 hover:underline">Eliminar</button>
                        <a href="{{ route('subcategorias-interes.index', ['perfil_id' => $perfil->id]) }}" class="text-green-600 hover:underline">Subcategorías</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @include('admin.partials.modal-simple')
</div>
@endsection

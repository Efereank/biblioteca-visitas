@extends('layouts.app')

@section('content')
<div x-data="crudManager('actividades', 'nombre')" class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="p-6 flex justify-between items-center border-b">
            <h2 class="text-xl font-bold text-gray-800">Actividades</h2>
            <button @click="openModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm">+ Agregar</button>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($items as $item)
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $item->nombre }}</td>
                        <td class="px-6 py-4 text-sm space-x-2">
                            <button @click="editItem({{ $item->id }}, '{{ $item->nombre }}')" class="text-blue-600 hover:underline">Editar</button>
                            <button @click="deleteItem({{ $item->id }})" class="text-red-600 hover:underline">Eliminar</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal --}}
    <div x-show="modalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" @click.self="modalOpen = false">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
            <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-bold mb-4" x-text="editing ? 'Editar Actividad' : 'Nueva Actividad'"></h3>
                <form @submit.prevent="save">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium">Nombre</label>
                            <input type="text" x-model="form.nombre" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="button" @click="modalOpen = false" class="px-4 py-2 border rounded-md text-gray-700">Cancelar</button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

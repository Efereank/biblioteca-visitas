@extends('layouts.app')

@section('content')
<div x-data="crudManager('tipos-visitante')" class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="p-6 flex justify-between items-center border-b">
            <h2 class="text-xl font-bold text-gray-800">Tipos de Visitante</h2>
            <button @click="openModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm">
                + Agregar
            </button>
        </div>

        {{-- Tabla --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Color</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($tipos as $tipo)
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $tipo->nombre }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-block w-6 h-6 rounded-full" style="background-color: {{ $tipo->color }}"></span>
                            <span class="ml-2 text-sm text-gray-600">{{ $tipo->color }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm space-x-2">
                            <button @click="editItem({{ $tipo->id }}, '{{ $tipo->nombre }}', '{{ $tipo->color }}')" class="text-blue-600 hover:underline">Editar</button>
                            <button @click="deleteItem({{ $tipo->id }})" class="text-red-600 hover:underline">Eliminar</button>
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
                <h3 class="text-lg font-bold mb-4" x-text="editing ? 'Editar Tipo' : 'Nuevo Tipo'"></h3>
                <form @submit.prevent="save">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium">Nombre</label>
                            <input type="text" x-model="form.nombre" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Color</label>
                            <div class="flex gap-2 mt-1">
                                <input type="color" x-model="form.color" class="h-10 w-16 rounded border-gray-300">
                                <input type="text" x-model="form.color" required class="flex-1 rounded-md border-gray-300 shadow-sm">
                            </div>
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

@push('scripts')
<script>
    function crudManager(baseUrl) {
        return {
            modalOpen: false,
            editing: false,
            itemId: null,
            form: { nombre: '', color: '#3B82F6' },

            openModal() {
                this.editing = false;
                this.itemId = null;
                this.form = { nombre: '', color: '#3B82F6' };
                this.modalOpen = true;
            },

            editItem(id, nombre, color) {
                this.editing = true;
                this.itemId = id;
                this.form = { nombre: nombre, color: color };
                this.modalOpen = true;
            },

            async save() {
                const url = this.editing ? `/${baseUrl}/${this.itemId}` : `/${baseUrl}`;
                const method = this.editing ? 'PUT' : 'POST';
                try {
                    const res = await fetch(url, {
                        method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.form)
                    });
                    if (res.ok) {
                        window.location.reload();
                    } else {
                        const err = await res.json();
                        alert(err.message || 'Error');
                    }
                } catch (e) {
                    console.error(e);
                    alert('Error de conexión');
                }
            },

            async deleteItem(id) {
                if (await showConfirm({ title: '¿Eliminar?', text: 'Esta acción no se puede deshacer' })) {
                    try {
                        const res = await fetch(`/${baseUrl}/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            }
                        });
                        if (res.ok) window.location.reload();
                        else alert('No se pudo eliminar');
                    } catch (e) {
                        alert('Error');
                    }
                }
            }
        }
    }
</script>
@endpush

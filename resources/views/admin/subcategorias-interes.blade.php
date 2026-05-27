@extends('layouts.app')

@section('content')
<div x-data="subcategoriaManager({{ request('perfil_id') ?? 'null' }})" class="max-w-5xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="p-6 flex justify-between items-center border-b">
            <h2 class="text-xl font-bold text-gray-800">Subcategorías de Interés</h2>
            <button @click="openModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm">+ Agregar</button>
        </div>

        <div class="p-4 bg-gray-50">
            <label class="block text-sm font-medium text-gray-700 mb-1">Filtrar por Perfil</label>
            <select x-model="perfilId" @change="cargarSubcategorias" class="w-full md:w-64 rounded-md border-gray-300 shadow-sm text-sm">
                <option value="">Todos los perfiles</option>
                @foreach($perfiles as $perfil)
                    <option value="{{ $perfil->id }}">{{ $perfil->nombre }}</option>
                @endforeach
            </select>
        </div>

        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subcategoría</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Perfil</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <template x-for="sub in subcategorias" :key="sub.id">
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900" x-text="sub.nombre"></td>
                        <td class="px-6 py-4 text-sm text-gray-600" x-text="sub.perfil?.nombre || ''"></td>
                        <td class="px-6 py-4 text-sm space-x-2">
                            <button @click="editItem(sub.id, sub.nombre, sub.perfil_interes_id)" class="text-blue-600 hover:underline">Editar</button>
                            <button @click="deleteItem(sub.id)" class="text-red-600 hover:underline">Eliminar</button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    {{-- Modal --}}
    <div x-show="modalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" @click.self="modalOpen = false">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
            <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-bold mb-4" x-text="editing ? 'Editar' : 'Nueva'"></h3>
                <form @submit.prevent="save">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium">Perfil</label>
                            <select x-model="form.perfil_interes_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Seleccione</option>
                                @foreach($perfiles as $perfil)
                                    <option value="{{ $perfil->id }}">{{ $perfil->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
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

@push('scripts')
<script>
    function subcategoriaManager(perfilIdInicial) {
        return {
            perfilId: perfilIdInicial || '',
            subcategorias: @json($subcategorias), // datos iniciales desde Blade
            modalOpen: false,
            editing: false,
            itemId: null,
            form: { nombre: '', perfil_interes_id: '' },

            async init() {
                // Si hay datos iniciales, no es necesario cargar de nuevo
                if (this.subcategorias.length === 0) {
                    await this.cargarSubcategorias();
                }
            },

            async cargarSubcategorias() {
                let url = '/subcategorias-interes';
                const params = new URLSearchParams();
                if (this.perfilId) params.append('perfil_id', this.perfilId);
                const qs = params.toString();
                if (qs) url += '?' + qs;
                const res = await fetch(url, {
                    headers: { 'Accept': 'application/json' }
                });
                if (res.ok) {
                    this.subcategorias = await res.json();
                }
            },

            openModal() {
                this.editing = false;
                this.itemId = null;
                this.form = { nombre: '', perfil_interes_id: this.perfilId || '' };
                this.modalOpen = true;
            },

            editItem(id, nombre, perfilId) {
                this.editing = true;
                this.itemId = id;
                this.form = { nombre: nombre, perfil_interes_id: perfilId };
                this.modalOpen = true;
            },

            async save() {
                const url = this.editing ? `/subcategorias-interes/${this.itemId}` : '/subcategorias-interes';
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
                        this.modalOpen = false;
                        await this.cargarSubcategorias();
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
                        const res = await fetch(`/subcategorias-interes/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            }
                        });
                        if (res.ok) {
                            await this.cargarSubcategorias();
                        } else {
                            alert('No se pudo eliminar');
                        }
                    } catch (e) {
                        alert('Error');
                    }
                }
            }
        }
    }
</script>
@endpush


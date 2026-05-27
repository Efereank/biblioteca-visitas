<div x-show="modalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" @click.self="modalOpen = false">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-bold mb-4" x-text="editing ? 'Editar' : 'Nuevo'"></h3>
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

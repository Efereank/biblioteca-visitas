@php
    $isEdit = isset($user);
    $route = $isEdit ? route('users.update', $user) : route('users.store');
    $method = $isEdit ? 'PUT' : 'POST';
@endphp

<form action="{{ $route }}" method="POST" class="space-y-6">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div>
        <label class="block text-sm font-medium text-gray-700">Nombre</label>
        <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Email</label>
        <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Contraseña {{ $isEdit ? '(dejar en blanco para no cambiar)' : '' }}</label>
        <input type="password" name="password" {{ $isEdit ? '' : 'required' }}
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Rol</label>
        <select name="role" id="role-select" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            <option value="">Seleccione rol</option>
            <option value="admin" {{ old('role', $user->role ?? '') === 'admin' ? 'selected' : '' }}>Administrador</option>
            <option value="bibliotecario" {{ old('role', $user->role ?? '') === 'bibliotecario' ? 'selected' : '' }}>Bibliotecario de sala</option>
            <option value="recepcionista" {{ old('role', $user->role ?? '') === 'recepcionista' ? 'selected' : '' }}>Recepcionista</option>
        </select>
        @error('role') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div id="salas-section" style="display: none;">
        <label class="block text-sm font-medium text-gray-700">Salas asignadas (solo para bibliotecario)</label>
        <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2">
            @foreach($salas as $sala)
                <label class="inline-flex items-center">
                    <input type="checkbox" name="salas[]" value="{{ $sala->id }}"
                        {{ in_array($sala->id, old('salas', $userSalas ?? [])) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700">{{ $sala->nombre }}</span>
                </label>
            @endforeach
        </div>
        @error('salas') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="flex justify-end gap-2">
        <a href="{{ route('users.index') }}" class="px-4 py-2 border rounded-md text-gray-700 hover:bg-gray-50">Cancelar</a>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Guardar</button>
    </div>
</form>

@push('scripts')
<script>
    const roleSelect = document.getElementById('role-select');
    const salasSection = document.getElementById('salas-section');

    function toggleSalas() {
        salasSection.style.display = roleSelect.value === 'bibliotecario' ? 'block' : 'none';
    }

    roleSelect.addEventListener('change', toggleSalas);

    // Ejecutar al cargar la página para mostrar/ocultar según el rol actual
    toggleSalas();
</script>
@endpush

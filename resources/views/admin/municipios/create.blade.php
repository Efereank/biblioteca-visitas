@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto">
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Nuevo Municipio</h2>
        <form action="{{ route('municipios.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700">Nombre *</label>
                <input type="text" name="nombre" required value="{{ old('nombre') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('nombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Capital</label>
                <input type="text" name="capital" value="{{ old('capital') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div class="flex justify-end gap-2">
                <a href="{{ route('municipios.index') }}" class="px-4 py-2 border rounded-md text-gray-700 hover:bg-gray-50">Cancelar</a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Guardar</button>
            </div>
        </form>
    </div>
</div>
@endsection

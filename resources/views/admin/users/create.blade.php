@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">{{ isset($user) ? 'Editar usuario' : 'Nuevo usuario' }}</h2>
        @include('admin.users.form')
    </div>
</div>
@endsection

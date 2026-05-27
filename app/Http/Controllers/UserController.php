<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Sala;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        $this->authorizeAdmin();
        $users = User::with('salas')->get();
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $this->authorizeAdmin();
        $salas = Sala::all();
        return view('admin.users.create', compact('salas'));
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin();
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:admin,bibliotecario,recepcionista',
            'salas'    => 'nullable|array',
            'salas.*'  => 'exists:salas,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);

        if ($request->role === 'bibliotecario' && $request->has('salas')) {
            $user->salas()->sync($request->salas);
        }

        return redirect()->route('users.index')->with('success', 'Usuario creado exitosamente.');
    }

    public function edit(User $user)
    {
        $this->authorizeAdmin();
        $salas = Sala::all();
        $userSalas = $user->salas->pluck('id')->toArray();
        return view('admin.users.edit', compact('user', 'salas', 'userSalas'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeAdmin();
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'role'     => 'required|in:admin,bibliotecario,recepcionista',
            'salas'    => 'nullable|array',
            'salas.*'  => 'exists:salas,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user->name  = $request->name;
        $user->email = $request->email;
        $user->role  = $request->role;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        if ($request->role === 'bibliotecario') {
            $user->salas()->sync($request->salas ?? []);
        } else {
            $user->salas()->detach();
        }

        return redirect()->route('users.index')->with('success', 'Usuario actualizado.');
    }

    public function destroy(User $user)
    {
        $this->authorizeAdmin();
        if ($user->id === Auth::id()) {
            return redirect()->route('users.index')->with('error', 'No puedes eliminarte a ti mismo.');
        }
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Usuario eliminado.');
    }

    private function authorizeAdmin()
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }
    }
}

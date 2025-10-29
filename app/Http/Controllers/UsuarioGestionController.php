<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsuarioGestionController extends Controller
{
    /** Listado + filtros */
    public function index(Request $request)
    {
        $q      = trim($request->query('q', ''));
        $rol    = $request->query('rol', '');
        $estado = $request->query('estado', '');

        $users = Usuario::query()
            ->when($q !== '', fn($qq) => $qq->where(function($w) use ($q){
                $w->where('nombre', 'ilike', "%{$q}%")
                  ->orWhere('correo', 'ilike', "%{$q}%");
            }))
            ->when($rol !== '', fn($qq) => $qq->where('id_rol', $rol))
            ->when($estado !== '', fn($qq) => $qq->where('activo', $estado === 'Activo'))
            ->orderBy('nombre')
            ->get();

        $roles = $this->rolesAsignables(); // para filtro y formulario
        return view('usuarios.index', compact('users','roles','q','rol','estado'));
    }

    /** Form crear */
    public function create()
    {
        $roles = $this->rolesAsignables();
        return view('usuarios.create', compact('roles'));
    }

    /** Guardar */
    public function store(Request $request)
    {
        $roles = $this->rolesAsignables();
        $request->validate([
            'nombre'  => ['required','string','max:120'],
            'correo'  => ['required','email','max:150','unique:usuarios,correo'],
            'telefono'=> ['nullable','string','max:30'],
            'id_rol'  => ['required', Rule::in($roles->pluck('id')->all())],
            'activo'  => ['required','in:0,1'],
            'contra' => ['required','string','min:8','confirmed'],
        ]);

        $payload = [
            'nombre'     => $request->nombre,
            'correo'     => $request->correo,
            'contra' => Hash::make($request->contra),
            'id_rol'     => (int)$request->id_rol,
            'activo'     => (bool)$request->activo,
        ];
        if ($request->filled('telefono') && \Schema::hasColumn('usuarios','telefono')) {
            $payload['telefono'] = $request->telefono;
        }

        Usuario::create($payload);
        return redirect()->route('usuarios.index')->with('ok','Usuario creado.');
    }

    /** Form editar */
    public function edit(Usuario $usuario)
    {
        $this->authorizeEdicion($usuario);
        $roles = $this->rolesAsignables();
        return view('usuarios.edit', compact('usuario','roles'));
    }

    /** Actualizar */
    public function update(Request $request, Usuario $usuario)
    {
        $this->authorizeEdicion($usuario);

        $roles = $this->rolesAsignables();
        $request->validate([
            'nombre'  => ['required','string','max:120'],
            'correo'  => ['required','email','max:150', Rule::unique('usuarios','correo')->ignore($usuario->id, 'id')],
            'telefono'=> ['nullable','string','max:30'],
            'id_rol'  => ['required', Rule::in($roles->pluck('id')->all())],
            'activo'  => ['required','in:0,1'],
            'contra' => ['nullable','string','min:8','confirmed'],
        ]);

        $payload = [
            'nombre' => $request->nombre,
            'correo' => $request->correo,
            'id_rol' => (int)$request->id_rol,
            'activo' => (bool)$request->activo,
        ];
        if ($request->filled('telefono') && \Schema::hasColumn('usuarios','telefono')) {
            $payload['telefono'] = $request->telefono;
        }
        if ($request->filled('contra')) {
            $payload['contra'] = Hash::make($request->contra);
        }

        $usuario->update($payload);
        return redirect()->route('usuarios.index')->with('ok','Usuario actualizado.');
    }

    /** Eliminar */
    public function destroy(Usuario $usuario)
    {
        $this->authorizeEdicion($usuario, true);

        if (auth()->id() === $usuario->id) {
            return back()->withErrors('No puedes eliminar tu propio usuario.');
        }

        try {
            $usuario->delete();
            return redirect()->route('usuarios.index')->with('ok','Usuario eliminado.');
        } catch (\Throwable $e) {
            return back()->withErrors('No se pudo eliminar (puede estar referenciado).');
        }
    }

    /** Activar / Desactivar */
    public function activar(Usuario $usuario)
    {
        $this->authorizeEdicion($usuario);
        if (auth()->id() === $usuario->id) {
            return back()->withErrors('No puedes cambiar tu propio estado.');
        }
        $usuario->activo = true;
        $usuario->save();
        return back()->with('ok','Usuario activado.');
    }

    public function desactivar(Usuario $usuario)
    {
        $this->authorizeEdicion($usuario);
        if (auth()->id() === $usuario->id) {
            return back()->withErrors('No puedes cambiar tu propio estado.');
        }
        $usuario->activo = false;
        $usuario->save();
        return back()->with('ok','Usuario desactivado.');
    }

    /** ===== Helpers de permisos granulares ===== */

    /** Roles que el usuario actual puede asignar */
    protected function rolesAsignables()
    {
        // Admin: todos los roles. Carpintero: solo "cliente"
        if (auth()->user()?->esAdmin()) {
            return Rol::orderBy('nombre')->get(['id','nombre','slug']);
        }
        return Rol::where('slug','cliente')->get(['id','nombre','slug']);
    }

    /** Reglas para editar/eliminar/activar a otros */
    protected function authorizeEdicion(Usuario $target, bool $forDelete = false): void
    {
        $me = auth()->user();

        // Carpintero NO puede editar/eliminar/activar a Admin/Carpintero
        if ($me->esCarpintero() && !$target->esCliente()) {
            abort(403, 'No autorizado.');
        }

        // Nadie puede eliminar/editar a un Admin salvo otro Admin
        if ($target->esAdmin() && !$me->esAdmin()) {
            abort(403, 'No autorizado.');
        }

        // Opcional: impedir que te quites el rol a ti mismo en edición
        if (!$me->esAdmin() && $me->id === $target->id && $forDelete) {
            abort(403, 'No puedes eliminarte a ti mismo.');
        }
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Usuario;

class AuthController extends Controller
{
    /* LOGIN */
    public function loginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $cred = $request->validate([
            'correo' => ['required','email'],
            'contra' => ['required']
        ]);

        if (Auth::attempt(['correo' => $cred['correo'], 'password' => $cred['contra'], 'activo' => true])) {
            $request->session()->regenerate();
            $u = Auth::user();
            // Redirección según rol
            if ($u->esCliente()) return redirect()->route('catalogo.index');
            if ($u->esAdmin() || $u->esCarpintero()) return redirect()->route('panel.dashboard');
        }

        return back()->withErrors(['correo' => 'Credenciales inválidas'])->onlyInput('correo');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('catalogo.index');
    }

    /* REGISTRO (cliente) */
    public function registerForm()
    {
        return view('auth.register'); 
    }

    public function register(Request $request)
    {
        $messages = [
            'nombre.required'   => 'El nombre es obligatorio.',
            'nombre.max'        => 'El nombre no debe superar :max caracteres.',

            'correo.required'   => 'El correo es obligatorio.',
            'correo.email'      => 'Ingresa un correo válido.',
            'correo.max'        => 'El correo no debe superar :max caracteres.',
            'correo.unique'     => 'Este correo ya está registrado.',

            'telefono.max'      => 'El teléfono no debe superar :max caracteres.',

            'contra.required'   => 'La contraseña es obligatoria.',
            'contra.min'        => 'La contraseña debe tener al menos :min caracteres.',
            'contra.confirmed'  => 'Las contraseñas no coinciden.',

            'acepta.accepted'   => 'Debes aceptar los términos para continuar.',
        ];

        $data = $request->validate([
            'nombre'   => ['required','string','max:120'],
            'correo'   => ['required','email','max:150','unique:usuarios,correo'],
            'telefono' => ['nullable','string','max:30'],
            'contra'   => ['required','string','min:8','confirmed'],
            'acepta'   => ['accepted'],
        ], $messages);

        $rolClienteId = \DB::table('roles')->where('slug','cliente')->value('id');
        if (!$rolClienteId) {
            return back()->withErrors('No se encontró el rol de cliente. Configura la tabla roles.')
                        ->withInput();
        }

        $payload = [
            'nombre'     => $data['nombre'],
            'correo'     => $data['correo'],
            'contra' => \Hash::make($data['contra']),
            'id_rol'     => $rolClienteId,
            'activo'     => true,
        ];
        if (!empty($data['telefono']) && \Schema::hasColumn('usuarios','telefono')) {
            $payload['telefono'] = $data['telefono'];
        }

        $u = Usuario::create($payload);
        \Auth::login($u);

        return redirect()->route('catalogo.index')->with('ok', '¡Cuenta creada! Bienvenido/a.');
    }
}

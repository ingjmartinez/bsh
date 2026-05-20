<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class AuthController extends Controller
{
    /**
     * Mostrar formulario de login.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect('/');
        }

        return view('auth.login');
    }

    /**
     * Procesar inicio de sesión.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, false)) {
            $request->session()->regenerate();
            $request->session()->put('auth_last_activity_at', time());

            $user = Auth::user();
            if ($user && Schema::hasColumn('users', 'current_login_at') && Schema::hasColumn('users', 'last_login_at')) {
                try {
                    $user->last_login_at = $user->current_login_at;
                    $user->current_login_at = now();
                    $user->save();
                } catch (Throwable $error) {
                    report($error);
                }
            }

            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    /**
     * Cerrar sesión.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * Enviar código de reseteo al correo del usuario.
     */
    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'email_reset' => ['required', 'email'],
        ], [
            'email_reset.required' => 'Debe indicar el correo electrónico.',
            'email_reset.email' => 'El correo electrónico no es válido.',
        ]);

        $email = strtolower(trim($data['email_reset']));
        $cacheStore = Cache::store('file');
        $rateKey = 'reset-password:' . sha1($email);

        $nextAllowedAt = (int) $cacheStore->get($rateKey, 0);

        if ($nextAllowedAt > time()) {
            $seconds = $nextAllowedAt - time();
            $minutes = intdiv($seconds, 60);
            $remainingSeconds = $seconds % 60;

            $waitMessage = $minutes > 0
                ? "Espere {$minutes} min {$remainingSeconds} seg para volver a resetear esta contraseña."
                : "Espere {$remainingSeconds} seg para volver a resetear esta contraseña.";

            return back()->withErrors(['email_reset' => $waitMessage])->withInput();
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return back()
                ->withErrors(['email_reset' => 'No existe un usuario registrado con ese correo.'])
                ->withInput();
        }

        $codigo = strtoupper(Str::random(8));

        $cacheStore->put(
            'password-reset-code:' . sha1($email),
            [
                'code_hash' => Hash::make($codigo),
            ],
            now()->addMinutes(15)
        );

        try {
            Mail::raw(
                "Hola {$user->name},\n\n" .
                "Se solicitó un reseteo de contraseña para tu cuenta.\n" .
                "Tu código de verificación es: {$codigo}\n\n" .
                "Este código expira en 15 minutos.\n" .
                "Luego de ingresarlo podrás definir una nueva contraseña.\n\n" .
                "ERP BSH Support",
                function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('Código para resetear contraseña - ERP BSH Support');
                }
            );
        } catch (Throwable $error) {
            return back()->withErrors([
                'email_reset' => 'No se pudo enviar el correo de verificación. Verifique la configuración SMTP.',
            ]);
        }

        $cacheStore->put($rateKey, time() + 180, now()->addSeconds(180));

        return redirect()
            ->route('login.reset-password.form', ['email' => $email])
            ->with('status', 'Se envió un código al correo indicado. Ingrésalo para definir tu nueva contraseña.');
    }

    /**
     * Mostrar formulario para validar código y definir nueva contraseña.
     */
    public function showResetPasswordForm(Request $request)
    {
        return view('auth.reset-password-code', [
            'email' => $request->query('email', old('email')),
        ]);
    }

    /**
     * Confirmar código y cambiar contraseña.
     */
    public function confirmResetPassword(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'codigo' => ['required', 'string', 'min:4', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'codigo.required' => 'Debe ingresar el código enviado por correo.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
            'password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
        ]);

        $email = strtolower(trim($data['email']));
        $cacheKey = 'password-reset-code:' . sha1($email);
        $cacheStore = Cache::store('file');
        $payload = $cacheStore->get($cacheKey);

        if (!$payload) {
            return back()->withErrors([
                'codigo' => 'El código es inválido o ha expirado. Solicite uno nuevo.',
            ])->withInput();
        }

        if (!Hash::check($data['codigo'], $payload['code_hash'])) {
            return back()->withErrors([
                'codigo' => 'El código ingresado no es correcto.',
            ])->withInput();
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'No existe un usuario registrado con ese correo.',
            ])->withInput();
        }

        $user->password = Hash::make($data['password']);
        $user->save();

        $cacheStore->forget($cacheKey);
        $cacheStore->forget('reset-password:' . sha1($email));

        return redirect()
            ->route('login')
            ->with('status', 'Contraseña actualizada correctamente. Ya puedes iniciar sesión.');
    }
}


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
     * Procesar inicio de sesiÃ³n.
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
     * Cerrar sesiÃ³n.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * Enviar cÃ³digo de reseteo al correo del usuario.
     */
    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'email_reset' => ['required', 'email'],
        ], [
            'email_reset.required' => 'Debe indicar el correo electrÃ³nico.',
            'email_reset.email' => 'El correo electrÃ³nico no es vÃ¡lido.',
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
                ? "Espere {$minutes} min {$remainingSeconds} seg para volver a resetear esta contraseÃ±a."
                : "Espere {$remainingSeconds} seg para volver a resetear esta contraseÃ±a.";

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
                "Se solicitÃ³ un reseteo de contraseÃ±a para tu cuenta.\n" .
                "Tu cÃ³digo de verificaciÃ³n es: {$codigo}\n\n" .
                "Este cÃ³digo expira en 15 minutos.\n" .
                "Luego de ingresarlo podrÃ¡s definir una nueva contraseÃ±a.\n\n" .
                "ERP BSH Support",
                function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('CÃ³digo para resetear contraseÃ±a - ERP BSH Support');
                }
            );
        } catch (Throwable $error) {
            return back()->withErrors([
                'email_reset' => 'No se pudo enviar el correo de verificaciÃ³n. Verifique la configuraciÃ³n SMTP.',
            ]);
        }

        $cacheStore->put($rateKey, time() + 180, now()->addSeconds(180));

        return redirect()
            ->route('login.reset-password.form', ['email' => $email])
            ->with('status', 'Se enviÃ³ un cÃ³digo al correo indicado. IngrÃ©salo para definir tu nueva contraseÃ±a.');
    }

    /**
     * Mostrar formulario para validar cÃ³digo y definir nueva contraseÃ±a.
     */
    public function showResetPasswordForm(Request $request)
    {
        return view('auth.reset-password-code', [
            'email' => $request->query('email', old('email')),
        ]);
    }

    /**
     * Confirmar cÃ³digo y cambiar contraseÃ±a.
     */
    public function confirmResetPassword(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'codigo' => ['required', 'string', 'min:4', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'codigo.required' => 'Debe ingresar el cÃ³digo enviado por correo.',
            'password.confirmed' => 'La confirmaciÃ³n de contraseÃ±a no coincide.',
            'password.min' => 'La nueva contraseÃ±a debe tener al menos 8 caracteres.',
        ]);

        $email = strtolower(trim($data['email']));
        $cacheKey = 'password-reset-code:' . sha1($email);
        $cacheStore = Cache::store('file');
        $payload = $cacheStore->get($cacheKey);

        if (!$payload) {
            return back()->withErrors([
                'codigo' => 'El cÃ³digo es invÃ¡lido o ha expirado. Solicite uno nuevo.',
            ])->withInput();
        }

        if (!Hash::check($data['codigo'], $payload['code_hash'])) {
            return back()->withErrors([
                'codigo' => 'El cÃ³digo ingresado no es correcto.',
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
            ->with('status', 'ContraseÃ±a actualizada correctamente. Ya puedes iniciar sesiÃ³n.');
    }
}


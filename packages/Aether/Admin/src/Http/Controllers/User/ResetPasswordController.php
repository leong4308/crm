<?php

namespace Aether\Admin\Http\Controllers\User;

use Aether\Admin\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ResetPasswordController extends Controller
{
    use ResetsPasswords;

    /**
     * Muestra la vista de restablecimiento de contraseña para el token dado.
     *
     * Si no hay ningún token presente, muestre el formulario de solicitud de enlace.
     *
     * @param  string|null  $token
     * @return Factory|View
     */
    public function create($token = null)
    {
        return view('admin::sessions.reset-password')->with([
            'token' => $token,
            'email' => request('email'),
        ]);
    }

    /**
     * Almacene un recurso recién creado en el almacenamiento.
     *
     * @return Response
     */
    public function store()
    {
        try {
            $this->validate(request(), [
                'token' => 'required',
                'email' => 'required|email',
                'password' => 'required|confirmed|min:6',
            ]);

            $response = $this->broker()->reset(
                request(['email', 'password', 'password_confirmation', 'token']), function ($admin, $password) {
                    $this->resetPassword($admin, $password);
                }
            );

            if ($response == Password::PASSWORD_RESET) {
                return redirect()->route('admin.dashboard.index');
            }

            return back()
                ->withInput(request(['email']))
                ->withErrors([
                    'email' => trans($response),
                ]);
        } catch (\Exception $exception) {
            session()->flash('error', trans($exception->getMessage()));

            return redirect()->back();
        }
    }

    /**
     * Restablezca la contraseña del administrador proporcionada.
     *
     * @param  CanResetPassword  $admin
     * @param  string  $password
     * @return void
     */
    protected function resetPassword($admin, $password)
    {
        $admin->password = Hash::make($password);

        $admin->setRememberToken(Str::random(60));

        $admin->save();

        event(new PasswordReset($admin));

        auth()->guard('user')->login($admin);
    }

    /**
     * Obtenga el corredor que se utilizará durante el restablecimiento de la contraseña.
     *
     * @return PasswordBroker
     */
    public function broker()
    {
        return Password::broker('users');
    }
}

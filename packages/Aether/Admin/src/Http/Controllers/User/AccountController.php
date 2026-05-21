<?php

namespace Aether\Admin\Http\Controllers\User;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Aether\Admin\Http\Controllers\Controller;

class AccountController extends Controller
{
    /**
     * Mostrar el formulario para crear un nuevo recurso.
     *
     * @return View
     */
    public function edit()
    {
        $user = auth()->guard('user')->user();

        return view('admin::user.account.edit', compact('user'));
    }

    /**
     * Actualice el recurso especificado en el almacenamiento.
     *
     * @return Response
     */
    public function update()
    {
        $user = auth()->guard('user')->user();

        $this->validate(request(), [
            'name' => 'required',
            'email' => 'email|unique:users,email,'.$user->id,
            'password' => 'nullable|min:6|confirmed',
            'current_password' => 'required|min:6',
            'image.*' => 'nullable|mimes:bmp,jpeg,jpg,png,webp',
        ]);

        $data = request()->only([
            'name',
            'email',
            'password',
            'password_confirmation',
            'current_password',
            'image',
        ]);

        if (! Hash::check($data['current_password'], $user->password)) {
            session()->flash('warning', trans('admin::app.account.edit.invalid-password'));

            return redirect()->back();
        }

        if (isset($data['role_id']) || isset($data['view_permission'])) {
            session()->flash('warning', trans('admin::app.user.account.permission-denied'));

            return redirect()->back();
        }

        $isPasswordChanged = false;

        if (! $data['password']) {
            unset($data['password']);
        } else {
            $isPasswordChanged = true;

            $data['password'] = bcrypt($data['password']);
        }

        if (request()->hasFile('image')) {
            // Eliminar imagen anterior si existe
            if ($user->image) {
                Storage::delete($user->image);
            }

            $data['image'] = current(request()->file('image'))->store('admins/'.$user->id);
        } elseif (isset($data['image']) && is_array($data['image']) && empty(array_filter($data['image']))) {
            // Se marcó para eliminar (array vacío)
            if ($user->image) {
                Storage::delete($user->image);
            }

            $data['image'] = null;
        } else {
            // No se subió imagen nueva ni se marcó para eliminar: conservar la actual
            $data['image'] = $user->image;
        }

        $user->update($data);

        if ($isPasswordChanged) {
            Event::dispatch('user.account.update-password', $user);
        }

        session()->flash('success', trans('admin::app.account.edit.update-success'));

        return back();
    }
}

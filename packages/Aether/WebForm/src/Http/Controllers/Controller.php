<?php

namespace Aether\WebForm\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Mostrar una lista del recurso.
     *
     * @return Response
     */
    public function redirectToLogin()
    {
        return redirect()->route('admin.session.create');
    }
}

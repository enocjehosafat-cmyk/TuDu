<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;

class Home extends BaseController
{
    public function index(): RedirectResponse
    {
        if (! auth()->loggedIn()) {
            return redirect()->to(site_url('login'));
        }

        $usuario = auth()->user();

        if ($usuario->inGroup('superadmin')) {
            return redirect()->to(site_url('admin/dashboard'));
        }

        if ($usuario->inGroup('cliente')) {
            return redirect()->to(site_url('cliente/dashboard'));
        }

        if ($usuario->inGroup('empleado')) {
            return redirect()->to(site_url('empleado/dashboard'));
        }

        return redirect()->to(site_url('pendiente'));
    }

    public function pendiente(): string
    {
        return view('pendiente', ['title' => 'Cuenta pendiente']);
    }
}

<?php

namespace App\Controllers;

use App\Models\EmpleadoModel;
use App\Models\EmpresaModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Shield\Controllers\LoginController as ShieldLoginController;

class LoginController extends ShieldLoginController
{
    public function loginAction(): RedirectResponse
    {
        if (auth()->loggedIn()) {
            return redirect()->to(config('Auth')->loginRedirect());
        }

        $identity = trim((string) $this->request->getPost('identity'));
        $password = (string) $this->request->getPost('password');

        if ($identity === '' || $password === '') {
            return redirect()->back()->withInput()->with('error', 'Escribe tus credenciales.');
        }

        $field = filter_var($identity, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $field     => $identity,
            'password' => $password,
        ];

        try {
            $authenticator = auth('session')->getAuthenticator();
            $result        = $authenticator->attempt($credentials);

            if (! $result->isOK()) {
                return redirect()->back()->withInput()->with('error', 'Credenciales incorrectas.');
            }

            if ($authenticator->hasAction()) {
                return redirect()->route('auth-action-show')->withCookies();
            }

            $usuario = $authenticator->getUser();

            if ($usuario === null) {
                return redirect()->back()->with('error', 'No se pudo establecer la sesion.');
            }

            if ($usuario->inGroup('cliente') || $usuario->inGroup('empleado')) {
                $perfil  = (new EmpleadoModel())->where('usuario_id', $usuario->id)->first();
                $empresa = $perfil === null ? null : (new EmpresaModel())->find((int) $perfil['empresa_id']);

                if ($perfil === null || $empresa === null || (int) $empresa['estatus'] !== 1) {
                    auth()->logout();

                    return redirect()->back()->withInput()->with('error', 'Tu cuenta no tiene una empresa activa. Contacta al administrador.');
                }
            }

            if (! $usuario->isActivated() && $usuario->inGroup('pendiente')) {
                auth()->logout();

                return redirect()->back()->withInput()->with('error', 'Debes verificar tu correo antes de iniciar sesion.');
            }

            if (! $usuario->active) {
                auth()->logout();

                return redirect()->back()->withInput()->with('error', 'Tu cuenta esta inactiva. Contacta al administrador.');
            }

            if ($usuario->inGroup('superadmin')) {
                return redirect()->to(site_url('admin/dashboard'))->withCookies();
            }

            if ($usuario->inGroup('cliente')) {
                return redirect()->to(site_url('cliente/dashboard'))->withCookies();
            }

            if ($usuario->inGroup('empleado')) {
                return redirect()->to(site_url('empleado/dashboard'))->withCookies();
            }

            return redirect()->to(site_url('pendiente'))->withCookies();
        } catch (\Throwable $e) {
            log_message('error', 'Error en login: ' . $e->getMessage());

            return redirect()->back()->withInput()->with('error', 'Error interno del sistema.');
        }
    }
}

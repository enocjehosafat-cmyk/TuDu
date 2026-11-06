<?php

namespace App\Controllers;

use App\Models\RegistroPendienteModel;
use App\Models\UsuariosShieldModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Shield\Controllers\RegisterController as ShieldRegisterController;

class RegisterController extends ShieldRegisterController
{
    public function registerAction(): RedirectResponse
    {
        if (auth()->loggedIn()) {
            return redirect()->to(config('Auth')->registerRedirect());
        }

        $authenticator = auth('session')->getAuthenticator();

        if ($authenticator->hasAction()) {
            return redirect()->route('auth-action-show');
        }

        $reglas = [
            'nombre_empresa'   => 'required|min_length[3]|max_length[150]',
            'nombre'           => 'required|min_length[2]|max_length[100]',
            'apellidos'        => 'required|min_length[2]|max_length[100]',
            'username'         => 'required|min_length[3]|max_length[30]|regex_match[/\A[a-zA-Z0-9\.]+\z/]|is_unique[users.username]',
            'email'            => 'required|valid_email|max_length[254]',
            'telefono'         => 'permit_empty|max_length[20]',
            'password'         => 'required|min_length[8]|max_length[72]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (! $this->validate($reglas)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email    = strtolower(trim((string) $this->request->getPost('email')));
        $username = strtolower(trim((string) $this->request->getPost('username')));

        if (! service('usuario')->emailDisponible($email)) {
            return redirect()->back()->withInput()->with('error', 'Ese correo ya está registrado.');
        }

        if (! service('usuario')->usernameDisponible($username)) {
            return redirect()->back()->withInput()->with('error', 'Ese nombre de usuario ya está registrado.');
        }

        $db = db_connect();
        $db->transBegin();

        try {
            $usuario = service('usuario')->crear([
                'username'  => $username,
                'email'     => $email,
                'password'  => (string) $this->request->getPost('password'),
                'nombre'    => (string) $this->request->getPost('nombre'),
                'apellidos' => (string) $this->request->getPost('apellidos'),
                'telefono'  => $this->request->getPost('telefono'),
                'active'    => true,
            ], ['pendiente']);

            $registro = new RegistroPendienteModel();

            if (! $registro->insert([
                'usuario_id'     => $usuario->id,
                'nombre_empresa' => (string) $this->request->getPost('nombre_empresa'),
                'nombre'         => (string) $this->request->getPost('nombre'),
                'apellidos'      => (string) $this->request->getPost('apellidos'),
                'telefono'       => $this->request->getPost('telefono'),
            ])) {
                throw new \RuntimeException('No se pudieron guardar los datos del registro.');
            }

            $authenticator->startLogin($usuario);
            $authenticator->startUpAction('register', $usuario);

            $db->transCommit();

            return redirect()->route('auth-action-show');
        } catch (\Throwable $e) {
            $db->transRollback();
            auth()->logout();
            log_message('error', 'Error en registro: ' . $e->getMessage());

            return redirect()->back()->withInput()->with('error', 'Ocurrió un error al crear tu cuenta. Intenta de nuevo.');
        }
    }

    public function resendVerification(): RedirectResponse
    {
        $email = strtolower(trim((string) $this->request->getPost('email')));

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with('error', 'Escribe un correo válido.');
        }

        $mensaje = 'Si el correo pertenece a una cuenta sin verificar, enviamos un nuevo código de verificación.';
        $usuario = (new UsuariosShieldModel())->findByCredentials(['email' => $email]);

        if ($usuario === null || ! $usuario->inGroup('pendiente')) {
            return redirect()->back()->with('message', $mensaje);
        }

        try {
            $actionClass = setting('Auth.actions')['register'] ?? null;

            if (! is_string($actionClass) || $actionClass === '' || ! class_exists($actionClass)) {
                throw new \RuntimeException('No hay una acción de registro configurada.');
            }

            $action = new $actionClass();

            if (! method_exists($action, 'enviarCorreoVerificacion')) {
                throw new \RuntimeException('La acción de registro no permite reenviar verificaciones.');
            }

            $action->enviarCorreoVerificacion($usuario);

            log_message('info', 'Correo de verificación reenviado al usuario ID {id}.', [
                'id' => $usuario->id,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Error al reenviar la verificación: {mensaje}', [
                'mensaje' => $e->getMessage(),
            ]);
        }

        return redirect()->back()->with('message', $mensaje);
    }
}

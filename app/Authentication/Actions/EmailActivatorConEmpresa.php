<?php

namespace App\Authentication\Actions;

use App\Models\EmpresaModel;
use App\Models\RegistroPendienteModel;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Authentication\Actions\EmailActivator;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Exceptions\RuntimeException;

class EmailActivatorConEmpresa extends EmailActivator
{
    /**
     * @return \CodeIgniter\HTTP\RedirectResponse|string
     */
    public function verify(IncomingRequest $request)
    {
        $authenticator = auth('session')->getAuthenticator();
        $pendienteUser = $authenticator->getPendingUser();
        $registro      = $pendienteUser === null
            ? session('registro_pendiente')
            : (new RegistroPendienteModel())->where('usuario_id', $pendienteUser->id)->first();

        if (! is_array($registro) || trim((string) ($registro['nombre_empresa'] ?? '')) === '') {
            return redirect()->to(site_url('login'))
                ->with('error', 'No encontramos los datos de tu registro. Contacta al administrador.');
        }

        $db = db_connect();
        $db->transBegin();

        try {
            $resultado = parent::verify($request);
            $usuario   = auth()->user();

            if (! $usuario instanceof User) {
                $db->transCommit();

                return $resultado;
            }

            $usuario = auth()->getProvider()->findById($usuario->id) ?? $usuario;

            if (! $usuario->isActivated()) {
                $db->transCommit();

                return $resultado;
            }

            if (! $usuario->inGroup('pendiente')) {
                (new RegistroPendienteModel())->where('usuario_id', $usuario->id)->delete();
                $db->transCommit();

                return $resultado;
            }

            $empresaModel = new EmpresaModel();
            $empresaId    = (int) $empresaModel->insert([
                'nombre'  => trim((string) $registro['nombre_empresa']),
                'estatus' => 1,
            ]);

            if ($empresaId === 0) {
                throw new RuntimeException('No se pudo crear la empresa del registro.');
            }

            service('usuario')->actualizar($usuario, [
                'username'  => $usuario->username,
                'email'     => $usuario->email,
                'nombre'    => trim((string) ($registro['nombre'] ?? '')),
                'apellidos' => trim((string) ($registro['apellidos'] ?? '')),
                'telefono'  => $registro['telefono'] ?? null,
                'active'    => true,
            ], ['cliente'], $empresaId);

            (new RegistroPendienteModel())->where('usuario_id', $usuario->id)->delete();

            $db->transCommit();
            session()->remove('registro_pendiente');

            return redirect()->to(site_url('cliente/dashboard'))
                ->with('message', 'Tu correo fue verificado y tu empresa fue creada correctamente.');
        } catch (\Throwable $e) {
            $db->transRollback();

            log_message('error', 'Error al verificar el correo y crear la empresa: {mensaje}', [
                'mensaje' => $e->getMessage(),
            ]);

            auth()->logout();

            return redirect()->to(site_url('login'))
                ->with('error', 'No se pudo completar la verificación de tu correo. Intenta nuevamente.');
        }
    }

    public function enviarCorreoVerificacion(User $usuario): string
    {
        $codigo = $this->createIdentity($usuario);

        $this->enviarCorreo($usuario, $codigo);

        return $codigo;
    }

    private function enviarCorreo(User $usuario, string $codigo): void
    {
        $emailUsuario = $usuario->email;

        if ($emailUsuario === null) {
            throw new RuntimeException('El usuario no tiene un correo configurado.');
        }

        /** @var IncomingRequest $request */
        $request   = service('request');
        $ipAddress = $request->getIPAddress();
        $userAgent = (string) $request->getUserAgent();
        $date      = Time::now()->toDateTimeString();

        helper('email');

        $email = emailer(['mailType' => 'html'])
            ->setFrom((string) setting('Email.fromEmail'), (string) (setting('Email.fromName') ?? ''));

        $email->setTo($emailUsuario);
        $email->setSubject(lang('Auth.emailActivateSubject'));
        $email->setMessage($this->view(
            setting('Auth.views')['action_email_activate_email'],
            [
                'code'      => $codigo,
                'user'      => $usuario,
                'ipAddress' => $ipAddress,
                'userAgent' => $userAgent,
                'date'      => $date,
            ],
            ['debug' => false],
        ));

        if ($email->send(false) === false) {
            throw new RuntimeException('No se pudo enviar el correo de verificación.');
        }

        $email->clear();
    }
}

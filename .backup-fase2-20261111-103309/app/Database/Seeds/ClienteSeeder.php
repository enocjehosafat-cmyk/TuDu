<?php

namespace App\Database\Seeds;

use App\Models\EmpresaModel;
use App\Models\UsuariosShieldModel;
use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Entities\User;

class ClienteSeeder extends Seeder
{
    public function run()
    {
        $empresaModel = new EmpresaModel();
        $empresaId    = (int) env('tudu.demoEmpresaId', 0);
        $empresa      = $empresaId > 0 ? $empresaModel->find($empresaId) : null;

        if ($empresa === null) {
            $empresa = $empresaModel->where('estatus', 1)->orderBy('id', 'ASC')->first();
        }

        if ($empresa === null) {
            $empresa = [
                'id'     => (int) $empresaModel->insert([
                    'nombre'  => 'Empresa Demo',
                    'estatus' => 1,
                ]),
                'nombre'  => 'Empresa Demo',
            ];
        }

        $email    = (string) env('tudu.clienteEmail', 'cliente@tudu.com');
        $username = (string) env('tudu.clienteUsername', 'cliente.demo');

        $userModel = new UsuariosShieldModel();
        $usuario   = $userModel->findByCredentials(['email' => $email]);

        if ($usuario === null) {
            $usuario = $userModel->findByCredentials(['username' => $username]);
        }

        $creado = false;

        if ($usuario === null) {
            $password = (string) env('tudu.clientePassword', '');

            if ($password === '') {
                $password = bin2hex(random_bytes(9));
            }

            $usuario = new User([
                'username' => $username,
                'email'    => $email,
                'password' => $password,
                'active'   => true,
            ]);

            $userModel->save($usuario);
            $usuario = $userModel->findById($userModel->getInsertID());

            if ($usuario === null) {
                throw new \RuntimeException('No se pudo crear el usuario cliente.');
            }

            $creado = true;
        }

        if (! $usuario->active) {
            $usuario->active = true;
            $userModel->save($usuario);
        }

        if (! $usuario->inGroup('cliente')) {
            $usuario->addGroup('cliente');
        }

        if ($usuario->inGroup('pendiente')) {
            $usuario->removeGroup('pendiente');
        }

        $perfil = service('usuario')->perfilDe($usuario);

        if ($perfil === null) {
            service('usuario')->guardarPerfil($usuario, [
                'nombre'    => $username,
                'apellidos' => 'Demo',
                'telefono'  => null,
                'active'    => true,
            ], (int) $empresa['id']);
        }

        echo "\nCliente de prueba asegurado.\n";
        echo 'Usuario: ' . $usuario->username . "\n";
        echo 'Correo: ' . ($usuario->getEmail() ?? '—') . "\n";
        echo 'Empresa: ' . $empresa['nombre'] . "\n";

        if ($creado) {
            echo 'Contraseña temporal: ' . $password . "\n";
        } else {
            echo "La contraseña existente no fue modificada.\n";
        }
    }
}

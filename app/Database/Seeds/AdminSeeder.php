<?php

namespace App\Database\Seeds;

use App\Models\UsuariosShieldModel;
use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Entities\User;

class AdminSeeder extends Seeder
{
    public function run()
    {
        $email    = (string) env('tudu.adminEmail', 'admin@tudu.com');
        $username = (string) env('tudu.adminUsername', 'superadmin');

        $userModel = new UsuariosShieldModel();
        $usuario   = $userModel->findByCredentials(['email' => $email]);

        if ($usuario === null) {
            $usuario = $userModel->findByCredentials(['username' => $username]);
        }

        $creado = false;

        if ($usuario === null) {
            $password = (string) env('tudu.adminPassword', '');

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
                throw new \RuntimeException('No se pudo crear el Super Admin.');
            }

            $creado = true;
        }

        if (! $usuario->active) {
            $usuario->active = true;
            $userModel->save($usuario);
        }

        service('usuario')->convertirEnSuperadmin($usuario);
        $usuario = $userModel->findById($usuario->id);

        if ($usuario === null) {
            throw new \RuntimeException('No se pudo recuperar el Super Admin.');
        }

        echo "\nSuper Admin asegurado.\n";
        echo 'Usuario: ' . $usuario->username . "\n";
        echo 'Correo: ' . ($usuario->getEmail() ?? '—') . "\n";

        if ($creado) {
            echo 'Contraseña temporal: ' . $password . "\n";
            echo "Cambia esta contraseña después de iniciar sesión.\n";
        } else {
            echo "La contraseña existente no fue modificada.\n";
        }
    }
}

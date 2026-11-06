<?php

namespace App\Controllers;

use App\Models\EmpleadoModel;
use CodeIgniter\Controller;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Shield\Entities\User;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
    }

    protected function usuario(): ?User
    {
        return auth()->user();
    }

    protected function esSuperadmin(): bool
    {
        return $this->usuario()?->inGroup('superadmin') ?? false;
    }

    protected function esCliente(): bool
    {
        return $this->usuario()?->inGroup('cliente') ?? false;
    }

    protected function esEmpleado(): bool
    {
        return $this->usuario()?->inGroup('empleado') ?? false;
    }

    protected function perfilActual(): ?array
    {
        $usuario = $this->usuario();

        if ($usuario === null) {
            return null;
        }

        return model(EmpleadoModel::class)
            ->where('usuario_id', $usuario->id)
            ->first();
    }

    protected function empresaId(): int
    {
        $perfil = $this->perfilActual();

        if ($perfil === null) {
            throw PageNotFoundException::forPageNotFound('Tu usuario no tiene una empresa asignada.');
        }

        return (int) $perfil['empresa_id'];
    }
}

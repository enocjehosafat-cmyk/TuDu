<?php

namespace App\Filters;

use App\Models\EmpleadoModel;
use App\Models\EmpresaModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class EmpresaActiva implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $usuario = auth()->user();

        if ($usuario === null) {
            return;
        }

        if (! $usuario->active) {
            auth()->logout();

            return redirect()->to(site_url('login'))
                ->with('error', 'Tu cuenta esta inactiva. Contacta al administrador.');
        }

        $perfil = (new EmpleadoModel())
            ->where('usuario_id', $usuario->id)
            ->first();

        if ($perfil === null) {
            return redirect()->to(site_url('pendiente'))
                ->with('error', 'Tu usuario no tiene una empresa asignada.');
        }

        $empresa = (new EmpresaModel())->find((int) $perfil['empresa_id']);

        if ($empresa === null || (int) $empresa['estatus'] !== 1) {
            auth()->logout();

            return redirect()->to(site_url('login'))
                ->with('error', 'La empresa esta inactiva. Contacta al administrador.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
    }
}

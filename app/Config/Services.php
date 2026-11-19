<?php

namespace Config;

use App\Services\RolService;
use App\Services\UsuarioService;
use CodeIgniter\Config\BaseService;

class Services extends BaseService
{
    public static function usuario(bool $getShared = true): UsuarioService
    {
        if ($getShared) {
            return static::getSharedInstance('usuario');
        }

        return new UsuarioService();
    }

    public static function rol(bool $getShared = true): RolService
    {
        if ($getShared) {
            return static::getSharedInstance('rol');
        }

        return new RolService();
    } 
    public static function audit(bool $getShared = true): \App\Services\AuditService
    {
        if ($getShared) {
            return static::getSharedInstance('audit');
        }

        return new \App\Services\AuditService();
    }
}

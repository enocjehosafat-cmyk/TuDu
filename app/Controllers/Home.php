<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        // 0/0;
        echo'hola mundo';
        return view('welcome_message');
    }
}

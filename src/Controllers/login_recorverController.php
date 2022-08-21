<?php

namespace App\Controllers;

# Load model

class Login_recorverController extends Controller
{

    public function index()
    {
        $this->twig->display('/login_recorver/index.html.twig');
    }
}

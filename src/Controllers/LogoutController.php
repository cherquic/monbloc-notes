<?php

namespace App\Controllers;

# Load model

class LogoutController extends Controller
{

    public function index()
    {
        $this->twig->display('/login/index.html.twig');
    }
}

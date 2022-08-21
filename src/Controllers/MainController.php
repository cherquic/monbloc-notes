<?php

namespace App\Controllers;

# Load model

class MainController extends Controller
{

    public function index()
    {
        $this->twig->display('/main/index.html.twig');
    }
}

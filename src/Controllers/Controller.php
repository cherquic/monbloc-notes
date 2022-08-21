<?php

namespace App\Controllers;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

abstract class Controller
{

    private $loader;
    protected $twig;


    public function __construct()
    {
        // le dossier ou on trouve les templates
        //$this->loader = new FilesystemLoader($_SERVER['DOCUMENT_ROOT'] . '\templates');
        $this->loader = new \Twig\Loader\FilesystemLoader('templates');

        // initialiser l'environement Twig
        $this->twig = new \Twig\Environment($this->loader);
    }
}

<?php

session_start();

// On définit une constante contenant le dossier racine
define('ROOT', dirname(__DIR__) . "/MBN");

// On importe les namespaces nécessaires
use App\Autoloader;
use App\Core\Main;


// On importe l'Autoloader
require_once('vendor/autoload.php');
require_once 'src/Autoloader.php';
Autoloader::register();

// On instancie Main
$app = new Main();

// On démarre l'application
$app->start();

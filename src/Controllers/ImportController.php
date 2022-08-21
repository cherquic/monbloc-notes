<?php

namespace App\Controllers;

use App\Models\BalanceModel;
use App\Models\planComptableModel;
use App\Models\ImportModel;

class ImportController extends Controller
{
    private $categorie = null;
    private $pathUpload = null;
    private $pathFileUpload = null;
    private $model = null;

    ////////////////////////////////////////////////////////////////////////////////////////
    //																			INIT      //																					//
    ////////////////////////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////////////////////////
    //																			FONCTIONS //																					//
    ////////////////////////////////////////////////////////////////////////////////////////


    public function index()
    {
        $fileNameToUpload = null;
        $filePathToUpload = null;

        if (isset($_POST) && isset($_FILES)) {
            //var_dump($_POST);
            //var_dump($_FILES);

            $this->categorie = $_POST['categorie'] ?? '';

            $fileNameToUpload  = $_FILES['fileToUpload']['name'] ?? '';
            $filePathToUpload = $_FILES['fileToUpload']['tmp_name'] ?? '';

            if ($fileNameToUpload) {
                $filePathToUpload = $_FILES['fileToUpload']['tmp_name'] ?? '';
                $this->pathFileUpload = $this->pathUpload . $fileNameToUpload;
                move_uploaded_file($filePathToUpload, $this->pathFileUpload);
                $this->upload($this->pathFileUpload);
            }
        }

        // Récupèrer les données
        $this->model = new ImportModel;
        $tables =   $this->model->get_db_allData();

        // Afficher la vue
        $this->twig->display('/import/index.html.twig', [
            'tables' => $tables
        ]);
    }


    public function upload($filePathUpload)
    {

        $model = new ImportModel;

        // choisir la table dans laquelle importer
        switch ($this->categorie) {
            case "balance":
                $model = new balanceModel;
                break;
            case "planComptable":
                $model = new plancomptableModel;
                break;
            default:
                echo "Erreur : categorie du fichier à importer inconnue";
                die();
                break;
        }

        // Récupèrer les données
        echo ($filePathUpload);
        $model->import_csv_to_db($filePathUpload);
    }
}

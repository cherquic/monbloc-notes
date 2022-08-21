<?php

namespace App\Controllers;

use App\Models\PlanComptableModel;

class PlanComptableController extends Controller
{

    ////////////////////////////////////////////////////////////////////////////////////////
    //																			INIT      //																					//
    ////////////////////////////////////////////////////////////////////////////////////////


    ////////////////////////////////////////////////////////////////////////////////////////
    //																			FONCTIONS //																					//
    ////////////////////////////////////////////////////////////////////////////////////////

    public function index()
    {
        $model = new PlanComptableModel;
        // Récupèrer les données
        $comptes =  $model->get_db_allData();

        // Afficher la vue
        $this->twig->display('/planComptable/index.html.twig', [
            'comptes' => $comptes
        ]);
    }
}

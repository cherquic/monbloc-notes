<?php

namespace App\Controllers;

use App\Models\BalanceModel;
use App\Models\PlanComptableModel;

class BalanceController extends Controller
{

    ////////////////////////////////////////////////////////////////////////////////////////
    //																			INIT      //																					//
    ////////////////////////////////////////////////////////////////////////////////////////


    ////////////////////////////////////////////////////////////////////////////////////////
    //																			FONCTIONS //																					//
    ////////////////////////////////////////////////////////////////////////////////////////

    public function index()
    {
        // Rattacher des comptes
        if (isset($_POST)) {
            $this->rattacheCptToDb();
        } else {
            $this->AfficherView();
        }
    }

    public function AfficherView()
    {
        // Récupèrer les données de la balance
        $model = new BalanceModel;
        $comptes =  $model->get_db_allData();

        // Récupèrer les comptes du PlanComptable regional
        $modelPC = new PlanComptableModel;
        $comptesPC =  $modelPC->get_db_allData();

        // Afficher la vue
        $this->twig->display('/balance/index.html.twig', [
            'comptes' => $comptes,
            'comptesPC' => $comptesPC
        ]);
    }

    public function rattacheCptToDb()
    {
        $model = new BalanceModel();

        if (isset($_POST['comptesBal'])) {
            $comptePC = explode("-", $_POST['comptePC'])[0];
            $libellePC = explode("-", $_POST['comptePC'])[1];
            $comptesBal =  explode("\n", $_POST['comptesBal']);
            //var_dump( $comptePC); 
            //var_dump( $comptesBal);

            $nb = count($comptesBal);
            for ($z = 0; $z < $nb; $z++) {
                $compteBal = explode(" - ", $comptesBal[$z])[0];

                //var_dump($compteBal);
                $model->let_db_cptPC($compteBal, $comptePC, $libellePC);
            }
        }

        // Afficher la vue
        $this->AfficherView();
    }
}

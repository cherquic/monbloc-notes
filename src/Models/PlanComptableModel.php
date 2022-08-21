<?php

namespace App\Models;

use App\Core\Db;
use PDOException;

class PlanComptableModel extends Model
{
    protected $db                   = null;
    protected $database             = null;
    #
    protected $table                = 'plancomptable';

    protected $fields = [
        'id'        => [
            'type'           => 'INT',
            'constraint'     => 'NOT NULL AUTO_INCREMENT',
            'primaryKey'     => true
        ],
        'compte'        => [
            'type'           => 'VARCHAR(10)',
            'constraint'     => 'NOT NULL',
            'default'        => "-",
            'UNIQUE KEY'     => true
        ],
        'libelle'      => [
            'type'           =>  'VARCHAR(100)',
            'default'        => '-',
        ],
        'cleAna'      => [
            'type'           =>  'VARCHAR(25)',
            'default'        => '-',
        ],
        'cle2'      => [
            'type'           =>  'VARCHAR(25)',
            'default'        => '-',
        ]
    ];

    public $data = array();

    ////////////////////////////////////////////////////////////////////////////////////////
    //																			INIT																					//
    ////////////////////////////////////////////////////////////////////////////////////////
    public function __construct()
    {
    }


    ////////////////////////////////////////////////////////////////////////////////////////
    //															 LIRE DONNEES EN DATABASE //																				//
    ////////////////////////////////////////////////////////////////////////////////////////
    // Lre les donnees
    public function get_db_allData()
    {

        //Creer la table si n'existe pas
        $this->create_db_table($this->fields, false);

        //Lire les données
        $results = $this->findAll();
        foreach ($results as $compte) {
            $this->data[$compte["compte"]]["id"] = $compte["id"];
            $this->data[$compte["compte"]]["compte"] = $compte["compte"];
            $this->data[$compte["compte"]]["libelle"] = stripcslashes($compte["libelle"]);
            $this->data[$compte["compte"]]["cleAna"] = stripcslashes($compte["cleAna"]);
            $this->data[$compte["compte"]]["cle2"] = stripcslashes($compte["cle2"]);
        }
        return $this->data;
    }

    ////////////////////////////////////////////////////////////////////////////////////////
    //							                     	IMPORTER CSV FILE EN DATABASE 	  //
    ////////////////////////////////////////////////////////////////////////////////////////
    public function import_csv_to_db(string $csvFileToLoad)
    {
        $csvArr = [];
        //Creer la table si n'existe pas
        $this->create_db_table($this->fields, true);

        $this->import_csv_to_array($csvFileToLoad, $csvArr);
        $this->import_array_to_db($csvArr);
    }

    # import en memoire
    public function import_csv_to_array(string $csvFileToLoad, array &$csvArr = [])
    {

        $i = 0;
        $numberOfFields = 4;
        $csvArr = array();

        if (($readCSVFile = fopen($csvFileToLoad, "r")) != false) {

            // Lirele fichier
            while (!feof($readCSVFile) && ($filedata = fgetcsv($readCSVFile, 1000, ";")) !== false) {
                $num = count($filedata);
                if ($i > 0 && $num == $numberOfFields && $filedata[0] != "") {
                    $csvArr[$i]['compte'] = $filedata[0];
                    $csvArr[$i]['libelle'] = $filedata[1];
                    $csvArr[$i]['cleAna'] = $filedata[2];
                    $csvArr[$i]['cle2'] = $filedata[3];
                }
                $i++;
            }
            fclose($readCSVFile);
        } else {
            die("Can't read file");
        }
    }

    #import en bdd
    public function import_array_to_db(array &$csvArr)
    {
        $db_conn = $this->db = Db::getInstance();
        $isSavingTo = true;

        //Importer données en bdd;
        # Preparer la requete

        $query = $db_conn->prepare(
            "INSERT INTO " . $this->table .
                " 
                (compte,libelle,cleAna,cle2)
                VALUES 
                (:compte, :libelle,:cleAna,:cle2)
            "
        );

        # importer une ligne
        $succesfull = 0;
        $err = 0;
        foreach ($csvArr as $data) {
            $values = array(
                "compte" =>  filter_var($data['compte'], FILTER_SANITIZE_SPECIAL_CHARS),
                "libelle" => utf8_encode(filter_var($data['libelle'], FILTER_SANITIZE_SPECIAL_CHARS)),
                "cleAna" => utf8_encode(filter_var($data['cleAna'], FILTER_SANITIZE_SPECIAL_CHARS)),
                "cle2" => utf8_encode(filter_var($data['cle2'], FILTER_SANITIZE_SPECIAL_CHARS))
            );
            try {
                $query->execute($values);
            } catch (PDOException $e) {
                $isSavingTo = false;
                die("Error: " . $e->getMessage());
            }
        }

        //check file imported
        if ($isSavingTo) {
            //echo "&#x1F601; Data Successfully imported into database";
        } else {
            echo "&#x1F914; Something went wrong";
        }
    }
}

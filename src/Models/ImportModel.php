<?php

namespace App\Models;

use App\Core\Db;
use PDOException;

class ImportModel extends Model
{
	protected $db                   = null;
	protected $forge                = null;
	protected $database             = null;
	#
	protected $table                = 'balance';
	protected $primaryKey           = 'comptes';

	protected $fields = [
		'id'		=> [
			'type'           => 'INT',
			'constraint'     => 'NOT NULL AUTO_INCREMENT',
			'primaryKey' 	=> true
		],
		'compte'		=> [
			'type'           => 'VARCHAR(10)',
			'constraint'     => 'NOT NULL',
			'default'        => "-",
			'UNIQUE KEY' 	=> true
		],
		'libelle' 		=> [
			'type'           => 'VARCHAR(100)',
			'default'        => "-"
		],
		'debit' 		=> [
			'type'           => 'DOUBLE',
			'default'        => 0
		],
		'credit' 		=> [
			'type'           => 'DOUBLE',
			'default'        => 0
		],
		'comptePC'      => [
			'type'           => 'VARCHAR(10)',
			'default'        => "-"
		],
		'libellePC'      => [
			'type'           => 'VARCHAR(100)',
			'default'        => "-"
		],
		'attachment'      => [
			'type'           => 'VARCHAR(5)',
			'default'        => "NR"
		]
	];
	public $data = array();
	#
	private $planComptableData 	= null;
	protected $tablePC			= 'planComptable';
	#
	private $CptsRattachesManuData 	= null;
	protected $tableCptsRatt		= 'CptsRattachesManu';


	////////////////////////////////////////////////////////////////////////////////////////
	//																			INIT																					//
	////////////////////////////////////////////////////////////////////////////////////////
	public function __construct()
	{
		//$this->table = 'balance';
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
			$this->data[$compte["compte"]]["debit"] = number_format($compte["debit"], 2, ".", " ");
			$this->data[$compte["compte"]]["credit"] = number_format($compte["credit"], 2, ".", " ");
			$this->data[$compte["compte"]]["comptePC"] = $compte["comptePC"];
			$this->data[$compte["compte"]]["libellePC"] = $compte["libellePC"];
			$this->data[$compte["compte"]]["attachment"] = $compte["attachment"];
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
				if ($i > 0 && $num == $numberOfFields) {
					$csvArr[$i]['compte'] = $filedata[0];
					$csvArr[$i]['libelle'] = $filedata[1];
					$csvArr[$i]['debit'] = $filedata[2];
					$csvArr[$i]['credit'] = $filedata[3];
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
			"INSERT INTO balance 
                (compte,libelle,debit,credit,comptePC, libellePC,  attachment)
                VALUES 
                (:compte, :libelle,:debit,:credit,:comptePC,:libellePC,:attachment)
            "
		);

		# importer une ligne
		$succesfull = 0;
		$err = 0;
		foreach ($csvArr as $data) {
			$values = array(
				"compte" =>  filter_var($data['compte'], FILTER_SANITIZE_SPECIAL_CHARS),
				"libelle" => utf8_encode(filter_var($data['libelle'], FILTER_SANITIZE_SPECIAL_CHARS)),
				"debit" => str_replace(',', '.', $data['debit']),
				"credit" => str_replace(',', '.', $data['credit']),
				"comptePC" =>  "-",
				"libellePC" => "-",
				'attachment' => "NR"
			);
			try {

				$query->execute($values);
			} catch (PDOException $e) {
				var_dump($values);
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

	// write csv
	public function loadNew_db_csv($csvArr, $planComptableData, $CptsRattachesManuData)
	{

		$this->planComptableData = $planComptableData;
		$this->CptsRattachesManuData = $CptsRattachesManuData;

		//(re)Creer la table
		$this->create_db_table($this->fields, true);


		//Importer données;
		$succesfull = 0;
		$err = 0;
		foreach ($csvArr as $data) {
			//values
			$compte = "'" . $data['compte'] . "'";
			$libelle = "'" . addslashes($data['libelle'])  . "'";
			$debit = str_replace(',', '.', $data['debit']);
			$credit = str_replace(',', '.', $data['credit']);
			$comptePC = "'-'";
			$libellePC = "'-'";
			$attachment = "'NR'";

			//Find cle
			$query = $this->db->query("
				SELECT count(*) as s from  $this->table
				WHERE compte =   $compte 
				");
			$findRecord = $query->getResult();

			//Insert
			if ($findRecord[0]->s == 0) {

				//Correspondance avec le plan comptable
				if (isset($this->planComptableData)) {
					$exacte = true;
					$key = $this->searchForId('numCpt', $this->planComptableData, $data['compte'], $exacte);

					if (isset($key)) {
						$comptePC = "'" . $this->planComptableData[$key]["compte"] . "'";
						$libellePC = "'" .  addslashes($this->planComptableData[$key]["libelle"]) . "'";
						$attachment =  ($exacte) ? "'Auto'" : "'Auto(0)'";
					}
				}

				// Insert
				$sql = "
					INSERT into " . $this->table . "
					(compte,libelle,debit,credit,comptePC,libellePC, attachment)
					VALUES ($compte,$libelle,$debit,$credit,$comptePC,$libellePC,$attachment)
				";
				$this->db->query($sql);

				if ($this->db->affectedRows()) {
					$succesfull++;
				} else {
					$err++;
				}
			} else {
				$err++;
			}
		}
		return array('succesfull' => $succesfull, 'err' => $err);
	}

	public function let_db_cptPC($compte, $comptePC, $libellePC)
	{

		$sql = "
					update " . $this->table . " 
					SET comptePC = $comptePC, 
						libellePC= '$libellePC',
						attachment='MANU' 
					WHERE compte=$compte
				";
		//echo $sql;
		$this->db->query($sql);
	}


	////////////////////////////////////////////////////////////////////////////////////////
	//																			UTILS	
	////////////////////////////////////////////////////////////////////////////////////////
	// -- Recherche dans un tableau multidimensionnel
	// les comptes du planCpt sont tous sur le meme nb de cracteres
	// PC (6152000) = Balance (6152xxx) sipas de detaille de ce compte dans le PC
	private function searchForId($id, $array, $cptFind, &$exacte)
	{

		// sortie de la boucle
		if (strLen($cptFind) == 0) {
			return null;
		}

		// Recherche x car
		$LEN_CPTS_PC = 8;
		$cptToFind = substr($cptFind . "0000000000000", 0, $LEN_CPTS_PC);

		foreach ($array as $key => $val) {

			if ($val["compte"] == $cptToFind) {
				return $key;
			}
		}

		// Recherche x-1 car
		$cptFind = substr($cptFind, 0, strLen($cptFind) - 1);
		$exacte = false;
		$this->searchForId($id, $array, $cptFind, $exacte);
	}
}

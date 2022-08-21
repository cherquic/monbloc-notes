<?php

# App\Models\BalanceModel
namespace App\Models;

use CodeIgniter\Model;

class CptsRattachesManuModel extends Model
{
    protected $db                   = null;
    protected $forge                = null;
    protected $database             = null;
    #
    protected $table                = 'CptsRattachesManu';
    protected $primaryKey           = 'comptes';
    protected $fields = [
        'compte'        => [
            'type'           => 'VARCHAR',
            'constraint'     => '10',
            'default'        => '-',
            'unique'         => true,
        ],
        'libelle'         => [
            'type'           => 'VARCHAR',
            'constraint'     => '100',
            'default'        => '-',
        ],
        'comptePC'      => [
            'type'           => 'VARCHAR',
            'constraint'     => '10',
            'default'        => '-',
        ],
        'libellePC'      => [
            'type'           =>  'VARCHAR',
            'constraint'     => '100',
            'default'        => '-',
        ],
    ];
    public $data = array();

    ////////////////////////////////////////////////////////////////////////////////////////
    //																			INIT																					//
    ////////////////////////////////////////////////////////////////////////////////////////
    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->forge = \Config\Database::forge();
    }

    ////////////////////////////////////////////////////////////////////////////////////////
    //																			ACTIONS																					//
    ////////////////////////////////////////////////////////////////////////////////////////
    // Read
    public function get_db_allData()
    {


        //Ceer table if not exist
        $this->create_db_table();

        //Afficher les données
        $query = $this->db->query("
			SELECT * FROM $this->table
		");

        $results = $query->getResultArray();
        foreach ($results as $compte) {
            $this->data[$compte["compte"]]["compte"] = $compte["compte"];
            $this->data[$compte["compte"]]["libelle"] = stripcslashes($compte["libelle"]);
            $this->data[$compte["compte"]]["comptePC"] = $compte["comptePC"];
            $this->data[$compte["compte"]]["libellePC"] = $compte["libellePC"];
        }
        return $this->data;
    }

    // write id
    public function let_db_id($compte, $libelle, $comptePC, $libellePC)
    {

        $succesfull = 0;
        $err = 0;

        //Find cle
        $query = $this->db->query("
			SELECT count(*) as s from  $this->table
			WHERE compte =   $compte 
			");
        $findRecord = $query->getResult();

        //Insert
        if ($findRecord[0]->s == 0) {
            $sql = "
				INSERT into " . $this->table . "
				(compte,libelle,comptePC,libellePC)
				VALUES ($compte,$libelle,$comptePC,$libellePC)
			";
        }
        //Update
        else {
            $sql = "
				UPDATE  $this->table 
				SET comptesPC= $comptePC , libellePC=  $libellePC 
				WHERE compte =  $compte 
			";
        }
        $this->db->query($sql);
        if ($this->db->affectedRows()) {
            $succesfull++;
        } else {
            $err++;
        }

        return array('succesfull' => $succesfull, 'err' => $err);
    }


    ////////////////////////////////////////////////////////////////////////////////////////
    //																		ACTIONS METHODS																//
    ////////////////////////////////////////////////////////////////////////////////////////
    public function add_db_table()
    {
        $this->drop_db_table();
        $this->create_db_table();
    }

    //Suppression
    private function drop_db_table()
    {
        // Produces: DROP TABLE IF EXISTS `table_name`
        $this->forge->dropTable($this->table, true);
    }

    //Creation (apres suppresson)
    private function create_db_table()
    {
        // gives CREATE TABLE IF NOT EXISTS table_name
        $this->forge->addField($this->fields);
        $this->forge->createTable($this->table, true);
    }
}

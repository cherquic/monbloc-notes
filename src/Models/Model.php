<?php

namespace App\Models;

use App\Core\Db;
use PDOException;

class Model extends Db
{
    // Table de la base de données
    protected $table;
    // Instance de Db
    protected $db;


    ////////////////////////////////////////////////////////////////////////////////////////
    //																			INIT																					//
    ////////////////////////////////////////////////////////////////////////////////////////
    public function __construct()
    {
        // On récupère l'instance de Db
        $this->db = Db::getInstance();
    }


    ////////////////////////////////////////////////////////////////////////////////////////
    //																        METHODS	TABLE //
    ////////////////////////////////////////////////////////////////////////////////////////
    //Creation (apres suppresson O/N)
    #   $Fiels = [
    #       ['compte' => ['type' => 'VARCHAR(10)','constraint' => NOT NULL, 'DEFAULT' => '-', 'UNIQUE KEY' => false, 'PRIMARY KEY' > true ]
    #   ...  ]
    #  Requete : CREATE TABLE Nom_table ( `compte` varchar(10) NOT NULL DEFAULT '-', ...,  PRIMARY KEY (`compte`),  UNIQUE KEY `test` (`test`))

    private function isTableExist()
    {
        // Récupèrer l'instance de Db
        $bdName = $this->db = Db::getDbName();

        // Lister les tables
        $query =  $this->requete('SHOW TABLES FROM  ' . $bdName);;
        $runQuery = $query->fetchAll();

        $tables = array();
        foreach ($runQuery as $champ => $valeur) {
            foreach ($valeur as $key => $value) {
                $tables[] = $value;
            }
        }
        if (in_array($this->table, $tables)) {
            return TRUE;
        }
    }

    //Suppression
    public function drop_db_table()
    {
        return $this->requete('DROP TABLE IF EXISTS ' . $this->table);
    }

    public function create_db_table(array  $fields, bool $drop)
    {

        // Supprimer la table et la recree=er
        if ($drop) {
            $this->drop_db_table();
            $this->create_db_table_notExist($fields);
        } else {

            // Creer la table si n'eiste pas
            $tableExist =  $this->isTableExist();
            if (!$tableExist) $this->create_db_table_notExist($fields);
        }
    }

    private  function create_db_table_notExist(array  $fields)
    {
        $champs = [];
        $valeurs = [];

        // 1. Definitions des champs : une ligne par champ
        foreach ($fields as $champ => $valeurs) {

            # Initialiser la ligne
            $ids = [];
            $valeur = [];

            # lire la ligne
            foreach ($valeurs as $id => $valeur) {
                switch ($id) {
                    case 'type':
                        $ids[] = $valeur;
                        break;
                    case 'constraint':
                        if ($valeur != '') $ids[] = $valeur;
                        break;
                    case 'default':
                        if ($valeur != '') $ids[] = 'DEFAULT ' . "'" .  $valeur  . "'";
                        break;
                    default:
                        break;
                }
            }
            // Ajouter au tableau une ligne sous forme d'une chaine de caractères :  `compte` varchar(10) NOT NULL DEFAULT '-'
            $champs[] =  "`$champ` " . implode(' ', $ids);
        }


        // 2. Definitions des cles : ne pas ajouter un elt a chaque ligne

        # Initialise  
        $ids = [];

        # lire chaque ligne
        foreach ($fields as $champ => $valeurs) {
            foreach ($valeurs as $id => $valeur) {
                if ($id ==  'primaryKey' && $valeur) {
                    // !!! if ($valeur == 'true') $ids[] = 'PRIMARY KEY (`' . $champ . '`)'; !!!
                    $ids[] = 'PRIMARY KEY (`' . $champ . '`)';
                } elseif ($id ==  'unique'  && $valeur) {
                    $ids[] = 'UNIQUE KEY `' .  $champ . '`(`' . $champ . '`)';
                }
            }
        }
        // Ajouter au tableau une chaine de caractères :  PRIMARY KEY (`compte`), UNIQUE KEY `test` (`test`),
        $champs[] =  implode(' ', $ids);

        // Transforme le tableau   en une chaine de caractères
        $liste_champs = implode(', ', $champs);

        // 3. Executer la requête
        return $this->requete('CREATE TABLE `' . $this->table . '`(' . $liste_champs . ')');
    }


    ////////////////////////////////////////////////////////////////////////////////////////
    //															        METHODS	REQUETE   //
    ////////////////////////////////////////////////////////////////////////////////////////
    //  SELECT
    public function findAll()
    {
        $query = $this->requete('SELECT * FROM ' . $this->table);
        return $query->fetchAll();
    }

    public function findBy(array $criteres)
    {
        $champs = [];
        $valeurs = [];

        // On boucle pour éclater le tableau
        foreach ($criteres as $champ => $valeur) {
            // SELECT * FROM annonces WHERE actif = ? AND signale = 0
            // bindValue(1, valeur)
            $champs[] = "$champ = ?";
            $valeurs[] = $valeur;
        }

        // On transforme le tableau "champs" en une chaine de caractères
        $liste_champs = implode(' AND ', $champs);

        // On exécute la requête
        return $this->requete('SELECT * FROM ' . $this->table . ' WHERE ' . $liste_champs, $valeurs)->fetchAll();
    }

    public function findId(int $id)
    {
        return $this->requete("SELECT * FROM {$this->table} WHERE id = $id")->fetch();
    }

    // INSERT
    public function create(Model $model)
    {
        $champs = [];
        $inter = [];
        $valeurs = [];

        // On boucle pour éclater le tableau
        foreach ($model as $champ => $valeur) {
            // INSERT INTO annonces (titre, description, actif) VALUES (?, ?, ?)
            if ($valeur != null && $champ != 'db' && $champ != 'table') {
                $champs[] = $champ;
                $inter[] = "?";
                $valeurs[] = $valeur;
            }
        }

        // On transforme le tableau "champs" en une chaine de caractères
        $liste_champs = implode(', ', $champs);
        $liste_inter = implode(', ', $inter);

        // On exécute la requête

        return $this->requete('INSERT INTO ' . $this->table . ' (' . $liste_champs . ')VALUES(' . $liste_inter . ')', $valeurs);
    }


    // UPDATE
    public function update(int $id, Model $model)
    {
        $champs = [];
        $valeurs = [];

        // On boucle pour éclater le tableau
        foreach ($model as $champ => $valeur) {
            // UPDATE annonces SET titre = ?, description = ?, actif = ? WHERE id= ?
            if ($valeur !== null && $champ != 'db' && $champ != 'table') {
                $champs[] = "$champ = ?";
                $valeurs[] = $valeur;
            }
        }
        $valeurs[] = $id;

        // On transforme le tableau "champs" en une chaine de caractères
        $liste_champs = implode(', ', $champs);

        // On exécute la requête
        return $this->requete('UPDATE ' . $this->table . ' SET ' . $liste_champs . ' WHERE id = ?', $valeurs);
    }

    // DELETE
    public function delete(int $id)
    {
        return $this->requete("DELETE FROM {$this->table} WHERE id = ?", [$id]);
    }


    ////////////////////////////////////////////////////////////////////////////////////////
    //															         EXECUTE REQUETE  //
    ////////////////////////////////////////////////////////////////////////////////////////
    public function requete(string $sql, array $attributs = null)
    {

        $this->db = Db::getInstance();

        // On vérifie si on a des attributs
        if ($attributs !== null) {
            // Requête préparée
            $query = $this->db->prepare($sql);
            $query->execute($attributs);
            return $query;
        } else {
            // Requête simple
            return $this->db->query($sql);
        }
    }


    public function hydrate(array $donnees)
    {
        foreach ($donnees as $key => $value) {
            // On récupère le nom du setter correspondant à la clé (key)
            // titre -> setTitre
            $setter = 'set' . ucfirst($key);

            // On vérifie si le setter existe
            if (method_exists($this, $setter)) {
                // On appelle le setter
                $this->$setter($value);
            }
        }
        return $this;
    }


    ////////////////////////////////////////////////////////////////////////////////////////
    //							                                                     FIN  //
    ////////////////////////////////////////////////////////////////////////////////////////
}

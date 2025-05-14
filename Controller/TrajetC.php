<?php

include_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../Model/Trajet.php');

class TrajetC {

    public function AjouterTrajet($trajet) {
        $db = config::getConnexion();
    
        try {
            $req = $db->prepare('
                INSERT INTO trajet (CIN, Adresse_Depart, Adresse_Arrivee, Nombre_Places, Prix, Distance, Duree)
                VALUES (:p, :a, :o, :m, :n, :j, :e)
            ');
            $req->execute([
                'p' => $trajet->getCIN(),
                'a' => $trajet->getAdresseDepart(),
                'o' => $trajet->getAdresseArrivee(),
                'm' => $trajet->getNombrePlaces(),
                'n' => $trajet->getPrix(),
                'j' => $trajet->getDistance(),
                'e' => $trajet->getDuree(),
            ]);
            return $db->lastInsertId(); 
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
    public function ListeTrajet() {
        $sql = "SELECT * FROM trajet";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC); // Retourne toujours un array
        } catch (PDOException $e) {
            error_log("Erreur SQL : " . $e->getMessage());
            return []; // Retourne un tableau vide en cas d'erreur
        }
    }
    
    public function DeleteTrajet($id) {
        $db = config::getConnexion();
        
        try {
            // Valider l'ID
            if (!is_numeric($id)) {
                throw new InvalidArgumentException("ID invalide");
            }
    
            // Configuration PDO
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Commencer une transaction pour garantir l'intégrité des données
            $db->beginTransaction();
            
            // 1. D'abord supprimer toutes les inscriptions associées à ce trajet
            $sqlDeleteInscriptions = "DELETE FROM inscription WHERE id_trajet = :id_trajet";
            $queryDeleteInscriptions = $db->prepare($sqlDeleteInscriptions);
            $queryDeleteInscriptions->bindValue(':id_trajet', $id, PDO::PARAM_INT);
            $queryDeleteInscriptions->execute();
            
            $inscriptionsDeleted = $queryDeleteInscriptions->rowCount();
            error_log("Suppression du trajet ID $id - $inscriptionsDeleted inscriptions associées supprimées");
            
            // 2. Ensuite supprimer le trajet
            $sqlDeleteTrajet = "DELETE FROM trajet WHERE ID_Trajet = :id";
            $queryDeleteTrajet = $db->prepare($sqlDeleteTrajet);
            $queryDeleteTrajet->bindValue(':id', $id, PDO::PARAM_INT);
            $queryDeleteTrajet->execute();
            
            $trajetDeleted = $queryDeleteTrajet->rowCount();
            
            // Valider la transaction
            $db->commit();
            
            // Journalisation
            error_log("Suppression du trajet ID $id terminée - Trajet supprimé: $trajetDeleted");
            
            return $trajetDeleted > 0;
    
        } catch (PDOException $e) {
            // En cas d'erreur, annuler la transaction
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Erreur SQL lors de la suppression du trajet: " . $e->getMessage());
            throw new RuntimeException("Erreur de base de données: " . $e->getMessage());
        }
    }
    

    public function GetTrajet($id) {
        $db = config::getConnexion();

        try {
            $req = $db->prepare('SELECT * FROM trajet WHERE ID_Trajet = :id');
            $req->execute([
                'id' => $id
            ]);

            return $req->fetch();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    
    public function ajouterTrajetAvecRetourID($trajet) {
        $sql = "INSERT INTO trajet (CIN, Adresse_Depart, Adresse_Arrivee, Nombre_Places, Prix, Distance, Duree)
                VALUES (:CIN, :Adresse_Depart, :Adresse_Arrivee, :Nombre_Places, :Prix, :Distance, :Duree)";
        
        $db = config::getConnexion();
    
        try {
            $query = $db->prepare($sql);
    
            $query->execute([
                'CIN'   => $trajet->getCIN(),
                'Adresse_Depart'   => $trajet->getAdresseDepart(),
                'Adresse_Arrivee'  => $trajet->getAdresseArrivee(),
                'Nombre_Places'    => $trajet->getNombrePlaces(),
                'Prix'            => $trajet->getPrix(),
                'Distance'        => $trajet->getDistance(),
                'Duree'           => $trajet->getDuree()
            ]);
    
            // Retourne l'ID du trajet inséré
            return $db->lastInsertId();
    
        } catch (PDOException $e) {
            // Tu peux aussi logger cette erreur dans un fichier si besoin
            echo "Erreur lors de l'ajout du trajet : " . $e->getMessage();
            return false;
        }
    }
    
    // Nouvelle version : accepte un seul objet Trajet
    public function updateTrajet($trajet)
    {
        $sql = "UPDATE trajet SET 
                    CIN = :CIN, 
                    Adresse_Depart = :adresse_depart, 
                    Adresse_Arrivee = :adresse_arrivee, 
                    Nombre_Places = :nombre_places, 
                    Prix = :prix, 
                    Distance = :distance, 
                    Duree = :duree
                WHERE ID_Trajet = :id_trajet";
    
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'CIN' => $trajet->getCIN(),
                'adresse_depart' => $trajet->getAdresseDepart(),
                'adresse_arrivee' => $trajet->getAdresseArrivee(),
                'nombre_places' => $trajet->getNombrePlaces(),
                'prix' => $trajet->getPrix(),
                'distance' => $trajet->getDistance(),
                'duree' => $trajet->getDuree(),
                'id_trajet' => $trajet->getID_Trajet()
            ]);
        } catch (Exception $e) {
            echo 'Erreur : ' . $e->getMessage();
        }
    }

    public function rechercherTrajetParID($id) {
        $sql = "SELECT * FROM trajet WHERE ID_Trajet = :id";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->bindValue(':id', $id);
            $query->execute();
            return $query->fetch();
        } catch (PDOException $e) {
            die('Erreur: '.$e->getMessage());
        }
    }

    public function ListeTrajetsAvecInscriptions() {
        $sql = "SELECT t.*, i.Telephone, i.Categorie, i.DateReservation, i.Paiement 
                FROM trajet t 
                LEFT JOIN inscription i ON t.CIN = i.ID";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur SQL (jointure) : " . $e->getMessage());
            return [];
        }
    }
    
    public function getInscriptionsByTrajetId($trajetId) {
        $sql = "SELECT i.* FROM inscription i 
                INNER JOIN trajet t ON i.ID = t.CIN 
                WHERE t.ID_Trajet = :id";
        
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->bindValue(':id', $trajetId, PDO::PARAM_INT);
            $query->execute();
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur SQL (get inscription): " . $e->getMessage());
            return null;
        }
    }

    public function verifierExistenceInscription($id) {
        $sql = "SELECT COUNT(*) FROM inscription WHERE ID = :id";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->execute();
            return $query->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Erreur vérification inscription: " . $e->getMessage());
            return false;
        }
    }

    public function getTrajetsParInscription($inscriptionId) {
        $sql = "SELECT * FROM trajet WHERE CIN = :id";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->bindValue(':id', $inscriptionId, PDO::PARAM_INT);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur récupération trajets par inscription: " . $e->getMessage());
            return [];
        }
    }

    public function rechercherTrajets($searchTerm) {
        $sql = "SELECT * FROM trajet 
                WHERE Adresse_Depart LIKE :search
                OR Adresse_Arrivee LIKE :search
                OR CIN LIKE :search
                OR CAST(Nombre_Places AS CHAR) LIKE :search
                OR CAST(Prix AS CHAR) LIKE :search";
        
        $db = Config::getConnexion();
        
        try {
            $searchTerm = "%{$searchTerm}%"; // Ajouter les wildcards pour la recherche partielle
            $query = $db->prepare($sql);
            $query->bindValue(':search', $searchTerm, PDO::PARAM_STR);
            $query->execute();
            
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur de recherche: " . $e->getMessage());
            return [];
        }
    }
    
    public function rechercherTrajetsAvances($criteres) {
        $sql = "SELECT * FROM trajet WHERE 1=1";
        $params = [];
        
        // Ajouter les critères conditionnellement
        if (!empty($criteres['adresse_depart'])) {
            $sql .= " AND Adresse_Depart LIKE :adresse_depart";
            $params[':adresse_depart'] = '%' . $criteres['adresse_depart'] . '%';
        }
        
        if (!empty($criteres['adresse_arrivee'])) {
            $sql .= " AND Adresse_Arrivee LIKE :adresse_arrivee";
            $params[':adresse_arrivee'] = '%' . $criteres['adresse_arrivee'] . '%';
        }
        
        if (!empty($criteres['min_places'])) {
            $sql .= " AND Nombre_Places >= :min_places";
            $params[':min_places'] = $criteres['min_places'];
        }
        
        if (!empty($criteres['max_prix'])) {
            $sql .= " AND Prix <= :max_prix";
            $params[':max_prix'] = $criteres['max_prix'];
        }
        
        $db = Config::getConnexion();
        
        try {
            $query = $db->prepare($sql);
            foreach ($params as $key => $value) {
                $query->bindValue($key, $value);
            }
            $query->execute();
            
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur de recherche avancée: " . $e->getMessage());
            return [];
        }
    }

    // Fonction pour obtenir les statistiques des adresses de départ
    public function getStatistiqueAdressesDepart() {
        $db = config::getConnexion();
        
        try {
            $query = $db->query('
                SELECT Adresse_Depart, COUNT(*) as nombre 
                FROM trajet 
                GROUP BY Adresse_Depart
            ');
            
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erreur statistiques adresses départ: " . $e->getMessage());
            return [];
        }
    }
    
    // Fonction pour obtenir les statistiques de nombre de places
    public function getStatistiquePlaces() {
        $db = config::getConnexion();
        
        try {
            $query = $db->query('
                SELECT Nombre_Places, COUNT(*) as nombre 
                FROM trajet 
                GROUP BY Nombre_Places
                ORDER BY Nombre_Places
            ');
            
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erreur statistiques places: " . $e->getMessage());
            return [];
        }
    }


    public function GetTrajetByCIN($cin) {
    try {
            $sql = "SELECT * FROM trajet WHERE CIN = :cin";
            $db = config::getConnexion();
            $query = $db->prepare($sql);
            $query->bindParam(':cin', $cin, PDO::PARAM_INT);
            $query->execute();
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Log the error or handle it as needed
            return null; // Return null if an error occurs or no record is found
        }
    }
    
    // Fonction pour obtenir les statistiques de prix par tranches
    public function getStatistiquePrix() {
        $db = config::getConnexion();
        
        try {
            $query = $db->query('
                SELECT 
                    CASE
                        WHEN Prix < 50 THEN "< 50 DT"
                        WHEN Prix BETWEEN 50 ET 100 THEN "50-100 DT"
                        WHEN Prix BETWEEN 101 ET 150 THEN "101-150 DT"
                        ELSE "> 150 DT"
                    END as tranche_prix,
                    COUNT(*) as nombre
                FROM trajet
                GROUP BY tranche_prix
                ORDER BY MIN(Prix)
            ');
            
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erreur statistiques prix: " . $e->getMessage());
            return [];
        }
    }
}
?>

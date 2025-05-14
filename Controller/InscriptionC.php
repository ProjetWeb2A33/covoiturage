<?php 

include_once(__DIR__ . '/../config.php');

class InscriptionC {

    public function ListeInscription() {
        $db = config::getConnexion();
    
        try {
            // Requête SQL pour récupérer toutes les inscriptions
            $query = $db->query('SELECT * FROM inscription');
            
            // Retourner les résultats sous forme de tableau associatif
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            // Gérer les erreurs de la base de données
            die ('Error: '.$e->getMessage());
        }
    }
    public function DeleteInscription($id) {
        $db = config::getConnexion();
    
        try {
            // Commencer une transaction pour assurer l'intégrité des données
            $db->beginTransaction();
            
            // 1. Vérifier si l'inscription existe
            $checkReq = $db->prepare('SELECT id FROM inscription WHERE id = :id');
            $checkReq->execute(['id' => $id]);
            if (!$checkReq->fetch()) {
                // L'inscription n'existe pas
                return false;
            }
            
            // 2. Supprimer l'inscription
            $delInsReq = $db->prepare('DELETE FROM inscription WHERE id = :id');
            $delInsReq->execute(['id' => $id]);
            
            // Valider la transaction
            $db->commit();
            return true;
    
        } catch (Exception $e) {
            // En cas d'erreur, annuler toutes les modifications
            $db->rollBack();
            error_log('Error in DeleteInscription: ' . $e->getMessage());
            die('Error: ' . $e->getMessage());
            return false;
        }
    }
    
  
  public function AjouterInscription($inscription) {
    $db = config::getConnexion();

    try {
        $req = $db->prepare('
            INSERT INTO inscription (telephone, categorie, dateReservation, paiement, id_trajet)
            VALUES (:p, :a, :o, :m, :id_trajet)
        ');
        $req->execute([
            'p' => $inscription->getTelephone(),
            'a' => $inscription->getCategorie(),
            'o' => $inscription->getDateReservation(),
            'm' => $inscription->getPaiement(),
            'id_trajet' => $inscription->getID_Trajet()
        ]);
        return $db->lastInsertId(); 
    } catch (Exception $e) {
        die('Error: ' . $e->getMessage());
    }
}
    

   public function GetInscription($id) {

    $db=config::getConnexion();

    try {

        $req=$db->prepare ('SELECT * FROM inscription WHERE id =:id');
        $req-> execute ([
            'id' => $id

        ]);

        return $req ->fetch ();

    } catch (Exception $e){
        die ('Error: '.$e->getMessage());
    }
   }



   public function ajouterInscriptionAvecRetourID(Inscription $inscription) {
    try {
        $pdo = config::getConnexion();

        $sql = "INSERT INTO inscription (Telephone, Categorie, DateReservation, Paiement, id_trajet) 
                VALUES (:Telephone, :Categorie, :DateReservation, :Paiement, :id_trajet)";

        $query = $pdo->prepare($sql);

        $query->execute([
            ':Telephone' => $inscription->getTelephone(),
            ':Categorie' => $inscription->getCategorie(),
            ':DateReservation' => $inscription->getDateReservation(),
            ':Paiement' => $inscription->getPaiement(),
            ':id_trajet' => $inscription->getID_Trajet()
        ]);

        return $pdo->lastInsertId(); // Correctement utilisé
    } catch (PDOException $e) {
        echo "Erreur lors de l'ajout : " . $e->getMessage();
        return false;
    }
}

    public function updateInscription($inscription) {
    $sql = "UPDATE inscription SET 
            Telephone = :telephone,
            Categorie = :categorie,
            DateReservation = :dateReservation,
            Paiement = :paiement,
            ID_Trajet = :idTrajet
            WHERE ID = :id";
    
    $db = config::getConnexion();
    $req = $db->prepare($sql);
    
    $req->bindValue(':telephone', $inscription->getTelephone());
    $req->bindValue(':categorie', $inscription->getCategorie());
    $req->bindValue(':dateReservation', $inscription->getDateReservation());
    $req->bindValue(':paiement', $inscription->getPaiement());
    $req->bindValue(':idTrajet', $inscription->getID_Trajet());
    $req->bindValue(':id', $inscription->getId());
    
    try {
        return $req->execute();
    } catch (Exception $e) {
        error_log("Erreur lors de la mise à jour: ".$e->getMessage());
        return false;
    }
}
    
    public function getInscriptionByTelephone($telephone) {
        $db = config::getConnexion();
        
        try {
            $req = $db->prepare('SELECT * FROM inscription WHERE telephone = :telephone');
            $req->execute([
                'telephone' => $telephone
            ]);
            
            return $req->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
    
    // Fonction pour obtenir les statistiques des catégories d'inscription
    public function getStatistiqueCategories() {
        $db = config::getConnexion();
        
        try {
            $query = $db->query('
                SELECT categorie, COUNT(*) as nombre 
                FROM inscription 
                GROUP BY categorie
            ');
            
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
            return [];
        }
    }
    
    // Fonction pour obtenir les statistiques des modes de paiement
    public function getStatistiquePaiements() {
        $db = config::getConnexion();
        
        try {
            $query = $db->query('
                SELECT paiement, COUNT(*) as nombre 
                FROM inscription 
                GROUP BY paiement
            ');
            
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
            return [];
        }
    }
    
    // Fonction pour obtenir les statistiques des inscriptions par mois
    public function getStatistiqueParMois() {
        $db = config::getConnexion();
        
        try {
            $query = $db->query('
                SELECT DATE_FORMAT(dateReservation, "%Y-%m") as mois, COUNT(*) as nombre 
                FROM inscription 
                GROUP BY DATE_FORMAT(dateReservation, "%Y-%m")
                ORDER BY mois
            ');
            
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
            return [];
        }
    }
   
}

?>
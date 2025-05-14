<?php 

include_once __DIR__ . '/../config.php';


class evenementE {

    // Liste de tous les événements
    public function ListeEvenements() {
        $db = config::getConnexion();
        try {
            return $db->query('SELECT * FROM evenement');
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    // Ajouter un événement
    public function AjouterEvenement($evenement) {
        $db = config::getConnexion();
        try {
            $req = $db->prepare('
                INSERT INTO evenement (nomE, date, lieu, nbrPlace_restante, nbrPlace_occupe, tarification)
                VALUES (:nomE, :dateE, :lieu, :rest, :occupe, :tarif)
            ');
            $req->execute([
                'nomE' => $evenement->getNomE(),
                'dateE' => $evenement->getDate(),
                'lieu' => $evenement->getLieu(),
                'rest' => $evenement->getNbrPlaceRestante(),
                'occupe' => $evenement->getNbrPlaceOccupe(),
                'tarif' => $evenement->getTarification()
                
            ]);
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    // Supprimer un événement
    public function SupprimerEvenement($id) {
        $db = config::getConnexion();
        try {
            $req = $db->prepare('DELETE FROM evenement WHERE idE = :id');
            $req->execute(['id' => $id]);
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    // Obtenir un événement par ID
    public function getEvenementById($idE) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('SELECT * FROM evenement WHERE idE = :id');
            $query->execute(['id' => $idE]);
            $result = $query->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function showEvenement($id)
{
    $sql = "SELECT * from evenement where idE = $id";
    $db = config::getConnexion();
    try {
        $query = $db->prepare($sql);
        $query->execute();
        $evenement = $query->fetch();  // Changez ici pour $evenement
        return $evenement;  // Retournez $evenement
    } catch (Exception $e) {
        die('Error: ' . $e->getMessage());
    }
}


    // Modifier un événement
    public function ModifierEvenement($evenement ,$id) {
        $db = config::getConnexion();
        try {
            $req = $db->prepare(
                'UPDATE evenement SET 
                    nomE = :nomE, 
                    date = :dateE, 
                    lieu = :lieu, 
                    nbrPlace_restante = :rest, 
                    nbrPlace_occupe = :occupe, 
                    tarification = :tarif 
                WHERE idE = :id
            ');
            $req->execute([
                'id' => $id,
                'nomE' => $evenement->getNomE(),
                'dateE' => $evenement->getDate(),
                'lieu' => $evenement->getLieu(),
                'rest' => $evenement->getNbrPlaceRestante(),
                'occupe' => $evenement->getNbrPlaceOccupe(),
                'tarif' => $evenement->getTarification()
            ]);
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }
}

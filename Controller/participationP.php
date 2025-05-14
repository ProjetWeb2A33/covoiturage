<?php 

include_once __DIR__ . '/../config.php';

class ParticipationP {

    // Lister toutes les participations
    public function ListeParticipations() {
        $db = config::getConnexion();
        try {
            $liste = $db->query('SELECT * FROM participation');
            return $liste;
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    // Ajouter une participation
    public function AjouterParticipation($p) {
      $db = config::getConnexion();
      try {
          $req = $db->prepare('
            INSERT INTO participation (
                idE, nom_participant, prenom_participant, numTel_participant, mail_participant, type_stationnement
            ) VALUES (:idE, :nom, :prenom, :tel, :mail, :type_stationnement)
           ');
          $req->execute([
            'idE' => $p->getIdE(),
            'nom' => $p->getNomParticipant(),
            'prenom' => $p->getPrenomParticipant(),
            'tel' => $p->getNumTelParticipant(),
            'mail' => $p->getMailParticipant(),
            'type_stationnement' => $p->getTypeStationnement()  // nouveau champ
            ]);
        } catch (Exception $e) {
          die('Erreur : ' . $e->getMessage());
        }
    }

    // Supprimer une participation
    public function SupprimerParticipation($id) {
        $db = config::getConnexion();
        try {
            $req = $db->prepare('DELETE FROM participation WHERE id_participation = :id');
            $req->execute(['id' => $id]);
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    // Récupérer une participation par ID
    public function getParticipationById($id_participation) {
        $db = config::getConnexion();
        try {
            $req = $db->prepare('SELECT * FROM participation WHERE id_participation = :id');
            $req->execute(['id' => $id_participation]);
            $result = $req->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    function showParticipation($id)
    {
        $sql = "SELECT * from participation where id_participation = $id";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute();
            return $query->fetch();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    // Modifier une participation
    public function ModifierParticipation($p, $id) {
        $db = config::getConnexion();
        try {
            $req = $db->prepare(
                'UPDATE participation SET
                    idE = :idE,
                    nom_participant = :nom,
                    prenom_participant = :prenom,
                    numTel_participant = :tel,
                    mail_participant = :mail,
                    type_stationnement = :type_stationnement
                WHERE id_participation = :id'
            );
            $req->execute([
                'idE' => $p->getIdE(),
                'nom' => $p->getNomParticipant(),
                'prenom' => $p->getPrenomParticipant(),
                'tel' => $p->getNumTelParticipant(),
                'mail' => $p->getMailParticipant(),
                'type_stationnement' => $p->getTypeStationnement(), // ajout ici
                'id' => $id
            ]);
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    // Mettre à jour les points de fidélité
    public function mettreAJourPointsFidelite($idParticipation, $montant) {
        try {
            $db = config::getConnexion();
            $points = floor($montant / 10); // 1 point pour chaque 10 DT
            
            $query = $db->prepare("UPDATE participation SET points_fidelite = points_fidelite + :points WHERE id_participation = :id");
            return $query->execute([
                'points' => $points,
                'id' => $idParticipation
            ]);
        } catch (Exception $e) {
            error_log("Erreur lors de la mise à jour des points : " . $e->getMessage());
            return false;
        }
    }

    // Récupérer les points de fidélité
    public function getPointsFidelite($email) {
        try {
            $db = config::getConnexion();
            $query = $db->prepare("SELECT SUM(points_fidelite) as total_points FROM participation WHERE mail_participant = :email");
            $query->execute(['email' => $email]);
            $result = $query->fetch();
            return $result['total_points'] ?? 0;
        } catch (Exception $e) {
            error_log("Erreur lors de la récupération des points : " . $e->getMessage());
            return 0;
        }
    }

    // Récupérer l'historique des participations
    public function getHistoriqueParticipations($email) {
        try {
            $db = config::getConnexion();
            $query = $db->prepare(
                "SELECT p.*, e.nomE, e.tarification 
                 FROM participation p 
                 JOIN evenement e ON p.idE = e.idE 
                 WHERE p.mail_participant = :email 
                 ORDER BY p.id_participation DESC"
            );
            $query->execute(['email' => $email]);
            return $query->fetchAll();
        } catch (Exception $e) {
            error_log("Erreur lors de la récupération de l'historique : " . $e->getMessage());
            return [];
        }
    }
}

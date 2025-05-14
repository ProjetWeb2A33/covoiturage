<?php 

include_once __DIR__ . '/../config.php';

class ReservationR {

    
    public function ListeReservation() {
        $db = config::getConnexion();
        try {
            $query = $db->prepare("
                SELECT 
                    r.ID_Reservation,
                    r.idParking,
                    r.idClient,
                    r.nom_client,
                    r.email,
                    r.horaire_d,
                    r.horaire_f,
                    r.statut,
                    r.prolongation,
                    r.payment,
                    r.disponibilite,
                    p.Nom_Parking AS Nom_Parking
                FROM reservation r
                LEFT JOIN parking p ON r.idParking = p.ID_Parking
            ");
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function DeleteReservation($ID_Reservation) {

        $db = config::getConnexion();

        try {

            $req = $db->prepare('
            DELETE FROM reservation WHERE ID_Reservation = :ID_Reservation
            ');
            $req->execute([
                'ID_Reservation' => $ID_Reservation
            ]);

        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function AjouterReservation($reservation) {
        $db = config::getConnexion();
        try {
            $req = $db->prepare('
                INSERT INTO reservation (idParking, nom_client, idClient, horaire_d, horaire_f, statut, prolongation, payment, disponibilite,email) 
                VALUES (:p, :nc, :c, :hd, :hf, :s, :pr, :py, :d, :e)
            ');
            $req->execute([
                'p'  => $reservation->getIdParking(),
                'nc' => $reservation->getNomClient(),
                'c'  => $reservation->getIdClient(),
                'hd' => $reservation->getHoraireD(),
                'hf' => $reservation->getHoraireF(),
                's'  => $reservation->getStatut(),
                'pr' => $reservation->getProlongation(),
                'py' => $reservation->getPayment(),
                'd'  => $reservation->getDisponibilite(),
                'e' =>$reservation->getEmail() 


            ]);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
    
    
    public function getReservationById($id) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare("
                SELECT 
                    r.*,
                    p.Nom_Parking AS Nom_Parking
                FROM reservation r
                LEFT JOIN parking p ON r.idParking = p.ID_Parking
                WHERE r.ID_Reservation = :id
            ");
            $query->execute(['id' => $id]);
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }
    public function UpdateReservation($ID_Reservation, Reservation $reservation) {
        $db = config::getConnexion();

        try {
             $query = $db->prepare("UPDATE reservation SET 
                
                    idParking = :p,
                    idClient = :c,
                    nom_client = :nc, 
                    horaire_d = :hd,
                    horaire_f = :hf,
                    statut = :s,
                    prolongation = :pr,
                    payment = :py,
                    disponibilite = :d,
                    email =:e
                WHERE ID_Reservation = :id
            ");
            
            return $query->execute([
                'p'  => $reservation->getIdParking(),
                'c'  => $reservation->getIdClient(),
                'nc' => $reservation->getNomClient(),
                'hd' => $reservation->getHoraireD(),
                'hf' => $reservation->getHoraireF(),
                's'  => $reservation->getStatut(),
                'pr' => $reservation->getProlongation(),
                'py' => $reservation->getPayment(),
                'd'  => $reservation->getDisponibilite(),
                'e'  => $reservation->getEmail(),
                'id' => $ID_Reservation
            ]);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function getAvailableParkings() {
        $db = config::getConnexion();
        try {
            $query = $db->prepare("SELECT ID_Parking, Nom_Parking FROM parking WHERE ID_Parking IS NOT NULL");
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

  

    public function getBestParking($horaire_d, $horaire_f) {
    $db = config::getConnexion();

    try {
        // Validation des formats HH:MM
        if (!preg_match('/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/', $horaire_d) || 
            !preg_match('/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/', $horaire_f)) {
            throw new Exception("Format d'horaire invalide");
        }

        // Requête adaptée à votre structure existante
        $sql = "SELECT p.*, 
                       (p.Nombre_Dispo / p.Capacite_Totale) * 100 AS disponibilite_score
                FROM parking p
                WHERE TIME(p.Horaire_Ouv) <= TIME(:horaire_d)
                  AND TIME(p.Horaire_Ferm) >= TIME(:horaire_f)
                  AND p.Nombre_Dispo > 0
                ORDER BY 
                   (p.Nombre_Dispo / p.Capacite_Totale) DESC,
                   CASE 
                      WHEN p.Tarification LIKE '%basique%' THEN 1
                      WHEN p.Tarification LIKE '%services%' THEN 2
                      WHEN p.Tarification LIKE '%événementielle%' THEN 3
                      ELSE 4
                   END,
                   TIME(p.Horaire_Ferm) - TIME(p.Horaire_Ouv) DESC
                LIMIT 1";

        $query = $db->prepare($sql);
        $query->execute([
            'horaire_d' => $horaire_d,
            'horaire_f' => $horaire_f
        ]);

        return $query->fetch(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Erreur dans getBestParking: " . $e->getMessage());
            return null;
        }
    }
    public function calculateParkingScore($parking) {
        // Poids configurables
        $weights = [
            'disponibilite' => 0.4,
            'tarification' => 0.3,
            'plage_horaire' => 0.2,
            'abonnement' => 0.1
        ];

        // 1. Score de disponibilité (40%)
        $dispoScore = ($parking['Nombre_Dispo'] / $parking['Capacite_Totale']) * $weights['disponibilite'] * 100;

        // 2. Score de tarification (30%)
        $tarifScore = 0;
        $tarif = strtolower($parking['Tarification']);
        if (strpos($tarif, 'basique') !== false) {
            $tarifScore = 100;
        } elseif (strpos($tarif, 'services') !== false) {
            $tarifScore = 70;
        } elseif (strpos($tarif, 'événementielle') !== false) {
            $tarifScore = 40;
        }
        $tarifScore *= $weights['tarification'];

        // 3. Score de plage horaire (20%)
        $horaireOuv = $this->timeToDecimal($parking['Horaire_Ouv']);
        $horaireFerm = $this->timeToDecimal($parking['Horaire_Ferm']);
        $plageHoraire = max(0, $horaireFerm - $horaireOuv); // Éviter les valeurs négatives
        $plageScore = ($plageHoraire / 24) * $weights['plage_horaire'] * 100;

        // 4. Score d'abonnement (10%)
        $abonnementScore = 0;
        $abonnement = strtolower($parking['Abonnement']);
        if (strpos($abonnement, 'premium') !== false) {
            $abonnementScore = 100;
        } elseif (strpos($abonnement, 'mensuel') !== false) {
            $abonnementScore = 70;
        } elseif (strpos($abonnement, 'annuel') !== false) {
            $abonnementScore = 50;
        }
        $abonnementScore *= $weights['abonnement'];

        // Score total
        $totalScore = $dispoScore + $tarifScore + $plageScore + $abonnementScore;

        return round($totalScore, 2);
    }

    private function timeToDecimal($time) {
        if (empty($time)) {
            return 0;
        }

        // Nettoyer et formater l'heure
        $time = preg_replace('/[^0-9:]/', '', $time);
        $parts = explode(':', $time);

        $hour = (int)$parts[0];
        $minutes = isset($parts[1]) ? (int)$parts[1] : 0;

        // Validation
        $hour = max(0, min(23, $hour));
        $minutes = max(0, min(59, $minutes));

        return $hour + ($minutes / 60);
    }

    private function validateTime($time) {
        if (preg_match('/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/', $time)) {
            return $time;
        }
        return false;
    }
}

?>

         
        



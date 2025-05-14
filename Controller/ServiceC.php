<?php
include_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Service.php';
require_once __DIR__ . '/../vendor-omar/autoload.php';


USE Mpdf\Mpdf;


class ServiceC {


    //use Mpdf\Mpdf;
    public function getHistoriqueServices(): array {
        $db = config::getConnexion();
        try {
            $sql = "SELECT date_modification, table_affectee, action, ancien_valeur, nouveau_valeur, utilisateur, id_ligne_affectee 
                    FROM historique WHERE table_affectee = 'service' ORDER BY date_modification DESC";
            $stmt = $db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération de l'historique des services : " . $e->getMessage());
        }
    }

    public function exporterHistoriquePDF() {
       
        $historique = $this->getHistoriqueServices();

        $mpdf = new \mpdf\mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 15,
            'margin_bottom' => 15,
        ]);

        $html = '<h1>Historique des Modifications des Services</h1>';
        $html .= '<table border="1" style="border-collapse: collapse; width: 100%;">';
        $html .= '<thead><tr><th>Date</th><th>Table</th><th>Action</th><th>Ancienne Valeur</th><th>Nouvelle Valeur</th><th>Utilisateur</th><th>ID Ligne</th></tr></thead>';
        $html .= '<tbody>';

        foreach ($historique as $entry) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($entry['date_modification']) . '</td>';
            $html .= '<td>' . htmlspecialchars($entry['table_affectee']) . '</td>';
            $html .= '<td>' . htmlspecialchars($entry['action']) . '</td>';
            $html .= '<td>' . htmlspecialchars($entry['ancien_valeur'] ?? 'N/A') . '</td>';
            $html .= '<td>' . htmlspecialchars($entry['nouveau_valeur'] ?? 'N/A') . '</td>';
            $html .= '<td>' . htmlspecialchars($entry['utilisateur']) . '</td>';
            $html .= '<td>' . htmlspecialchars($entry['id_ligne_affectee']) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        $mpdf->WriteHTML($html);
        $mpdf->Output('historique_services_' . date('Y-m-d_H-i-s') . '.pdf', 'D');
        exit();
    }

    public function ListeService() {
        $db = config::getConnexion();
        try {
            $stmt = $db->query('SELECT * FROM service');
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la récupération des services : ' . $e->getMessage());
        }
    }

    public function AjouterService(Service $svc) {
        $db = config::getConnexion();
        try {
            $sql = 'INSERT INTO service (Nom_u, station_id, duree_heures, montant) VALUES (:Nom_u, :station_id, :duree_heures, :montant)';
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'Nom_u' => $svc->getNomU(),
                'station_id' => $svc->getStationId(),
                'duree_heures' => $svc->getDureeHeures(),
                'montant' => $svc->getMontant(),
            ]);
        } catch (Exception $e) {
            throw new Exception('Erreur lors de l\'ajout du service : ' . $e->getMessage());
        }
    }

    public function GetService(int $id_service) {
        $db = config::getConnexion();
        try {
            $stmt = $db->prepare('SELECT * FROM service WHERE id_service = :id');
            $stmt->execute(['id' => $id_service]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la récupération du service : ' . $e->getMessage());
        }
    }

    public function UpdateService(int $id_service, Service $svc) {
        $db = config::getConnexion();
        try {
            $sql = 'UPDATE service SET Nom_u = :Nom_u, station_id = :station_id, duree_heures = :duree_heures, montant = :montant WHERE id_service = :id';
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'Nom_u' => $svc->getNomU(),
                'station_id' => $svc->getStationId(),
                'duree_heures' => $svc->getDureeHeures(),
                'montant' => $svc->getMontant(),
                'id' => $id_service,
            ]);
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la mise à jour du service : ' . $e->getMessage());
        }
    }

    public function SupprimerService(int $id_service) {
        $db = config::getConnexion();
        try {
            // Démarrer une transaction pour garantir la cohérence
            $db->beginTransaction();

            // Mettre à jour les commandes associées
            $updateStmt = $db->prepare("UPDATE orders SET statut = 'annulé', id_service = NULL WHERE id_service = :idService");
            $updateStmt->execute([':idService' => $id_service]);

            // Supprimer le service
            $deleteStmt = $db->prepare('DELETE FROM service WHERE id_service = :id');
            $deleteStmt->execute(['id' => $id_service]);

            // Notifier les utilisateurs des commandes affectées
            $affectedOrders = $updateStmt->rowCount();
            if ($affectedOrders > 0) {
                $orderStmt = $db->prepare("SELECT idOrder, ID_Utilisateur FROM orders WHERE id_service IS NULL AND statut = 'annulé'");
                $orderStmt->execute();
                while ($order = $orderStmt->fetch(PDO::FETCH_ASSOC)) {
                    $this->notifyUser($order['idOrder'], $order['ID_Utilisateur']);
                }
            }

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            error_log("Erreur lors de la suppression du service $id_service : " . $e->getMessage());
            throw new Exception('Erreur lors de la suppression du service : ' . $e->getMessage());
        }
    }

    private function notifyUser($idOrder, $userId) {
        $db = config::getConnexion();
        $stmt = $db->prepare("SELECT email FROM utilisateur WHERE ID_Utilisateur = :userId");
        $stmt->execute([':userId' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && isset($user['email'])) {
            $to = $user['email'];
            $subject = "Annulation de votre commande #{$idOrder}";
            $message = "Bonjour,\n\nVotre commande #{$idOrder} a été annulée car le service associé a été supprimé par l'administrateur. Pour plus d'informations, contactez-nous à contact@easyparki.com.\n\nCordialement,\nL'équipe EasyParki";
            $headers = "From: noreply@easyparki.com";

            mail($to, $subject, $message, $headers);
            error_log("Notification envoyée à {$to} pour la commande #{$idOrder}");
        }
    }

    public function getServiceCount(): int {
        $db = config::getConnexion();
        try {
            $stmt = $db->query('SELECT COUNT(*) AS total FROM service');
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$res['total'];
        } catch (Exception $e) {
            throw new Exception('Erreur lors du comptage des services : ' . $e->getMessage());
        }
    }

    public function SupprimerServicesByStationId(int $stationId): void {
        $db = config::getConnexion();
        try {
            $sql = 'DELETE FROM service WHERE station_id = :station_id';
            $stmt = $db->prepare($sql);
            $stmt->execute([':station_id' => $stationId]);
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la suppression des services associés : ' . $e->getMessage());
        }
    }
}
?>
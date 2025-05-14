<?php
include __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Station.php';
// require_once __DIR__ . '/../Controller/ServiceC.php'; // Corrected path to ServiceC.php (same directory as StationC.php)
// require  'C:\xampp\htdocs\integration\vendor-omar\mpdf\mdpf';

USE Mpdf\Mpdf;

class StationC {



    public function getHistoriqueStations(): array {
        $db = config::getConnexion();
        try {
            $sql = "SELECT date_modification, table_affectee, action, ancien_valeur, nouveau_valeur, utilisateur, id_ligne_affectee 
                    FROM historique WHERE table_affectee = 'station' ORDER BY date_modification DESC";
            $stmt = $db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération de l'historique des stations : " . $e->getMessage());
        }
    }

    public function exporterHistoriquePDF() {
        require_once __DIR__ . '/../../../../vendor/autoload.php';
        $historique = $this->getHistoriqueStations();

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 15,
            'margin_bottom' => 15,
        ]);

        $html = '<h1>Historique des Modifications des Stations</h1>';
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
        $mpdf->Output('historique_stations_' . date('Y-m-d_H-i-s') . '.pdf', 'D');
        exit();
    }
    public function AjouterStation(Station $station): void {
        $db = config::getConnexion();
        try {
            $sql = "INSERT INTO station (Emplacement, TypeS) VALUES (:emplacement, :typeS)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':emplacement' => $station->getEmplacement(),
                ':typeS' => $station->getTypeS()
            ]);
        } catch (Exception $e) {
            throw new Exception("Erreur lors de l'ajout de la station : " . $e->getMessage());
        }
    }

    public function UpdateStation(int $idStation, Station $station): void {
        $db = config::getConnexion();
        try {
            $sql = "UPDATE station SET Emplacement = :emplacement, TypeS = :typeS WHERE idStation = :idStation";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':idStation' => $idStation,
                ':emplacement' => $station->getEmplacement(),
                ':typeS' => $station->getTypeS()
            ]);
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la mise à jour de la station : " . $e->getMessage());
        }
    }

    public function SupprimerStation(int $idStation): void {
        $db = config::getConnexion();
        try {
            $sql = "DELETE FROM station WHERE idStation = :idStation";
            $stmt = $db->prepare($sql);
            $stmt->execute([':idStation' => $idStation]);
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la suppression de la station : " . $e->getMessage());
        }
    }

    public function ListeStation(): array {
        $db = config::getConnexion();
        try {
            $sql = "SELECT * FROM station";
            $stmt = $db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération des stations : " . $e->getMessage());
        }
    }

    public function getStationCount(): int {
        $db = config::getConnexion();
        try {
            $sql = "SELECT COUNT(*) FROM station";
            $stmt = $db->query($sql);
            return (int) $stmt->fetchColumn();
        } catch (Exception $e) {
            throw new Exception("Erreur lors du comptage des stations : " . $e->getMessage());
        }
    }

    public function getServiceTypeStats(): array {
        $db = config::getConnexion();
        try {
            $sql = "SELECT TypeS, COUNT(*) as total FROM station GROUP BY TypeS";
            $stmt = $db->query($sql);

            // Initialize stats for all possible TypeS values
            $stats = [
                "lavage" => 0,
                "reparation" => 0,
                "entretien" => 0,
                "recharge" => 0
            ];

            // Populate stats
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $type = strtolower(trim($row['TypeS']));
                if (array_key_exists($type, $stats)) {
                    $stats[$type] = $row['total'];
                }
            }

            // Calculate percentages
            $total = array_sum($stats);
            $percentages = [
                'pourcentLavage' => $total ? round(($stats['lavage'] / $total) * 100, 2) : 0,
                'pourcentReparation' => $total ? round(($stats['reparation'] / $total) * 100, 2) : 0,
                'pourcentEntretien' => $total ? round(($stats['entretien'] / $total) * 100, 2) : 0,
                'pourcentRecharge' => $total ? round(($stats['recharge'] / $total) * 100, 2) : 0
            ];

            return $percentages;
        } catch (Exception $e) {
            throw new Exception("Erreur lors du calcul des statistiques : " . $e->getMessage());
        }
    }

    public function getStationsByType(string $type): array {
        $db = config::getConnexion();
        try {
            $sql = "SELECT * FROM station WHERE TypeS = :type";
            $stmt = $db->prepare($sql);
            $stmt->execute([':type' => $type]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération des stations par type : " . $e->getMessage());
        }
    }
    public function GetStation(int $idStation): ?array {
        $db = config::getConnexion();
        try {
            $sql = "SELECT * FROM station WHERE idStation = :idStation";
            $stmt = $db->prepare($sql);
            $stmt->execute([':idStation' => $idStation]);
            $station = $stmt->fetch(PDO::FETCH_ASSOC);
            return $station ?: null; // Retourne null si aucune station n'est trouvée
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération de la station : " . $e->getMessage());
        }
    }
}
?>
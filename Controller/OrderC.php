<?php
require_once __DIR__ . '/../config.php'; // Adjust path as needed
require_once __DIR__ . '/../model/Order.php'; // Adjust path as needed

class OrderC {
    public function ListeOrders() {
        try {
            $sql = "SELECT * FROM orders";
            $db = config::getConnexion();
            $req = $db->query($sql);
            return $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error in ListeOrders: " . $e->getMessage());
            throw new Exception("Unable to fetch orders: " . $e->getMessage());
        }
    }

    public function GetOrder($idOrder) {
        try {
            $sql = "SELECT * FROM orders WHERE idOrder = :idOrder";
            $db = config::getConnexion();
            $req = $db->prepare($sql);
            $req->bindValue(':idOrder', $idOrder, PDO::PARAM_INT);
            $req->execute();
            $result = $req->fetch(PDO::FETCH_ASSOC);
            return $result ?: null; // Return null if no order found
        } catch (Exception $e) {
            error_log("Error in GetOrder: " . $e->getMessage());
            throw new Exception("Unable to fetch order: " . $e->getMessage());
        }
    }

    public function SupprimerOrder($idOrder) {
        try {
            $sql = "DELETE FROM orders WHERE idOrder = :idOrder";
            $db = config::getConnexion();
            $req = $db->prepare($sql);
            $req->bindValue(':idOrder', $idOrder, PDO::PARAM_INT);
            $req->execute();
            if ($req->rowCount() === 0) {
                throw new Exception("No order found with idOrder $idOrder to delete.");
            }
        } catch (Exception $e) {
            error_log("Error in SupprimerOrder: " . $e->getMessage());
            throw new Exception("Failed to delete order: " . $e->getMessage());
        }
    }

    public function UpdateOrder($idOrder, $order) {
        try {
            $sql = "UPDATE orders SET ID_Utilisateur = :ID_Utilisateur, id_service = :id_service, date_commande = :date_commande, station = :station, statut = :statut WHERE idOrder = :idOrder";
            $db = config::getConnexion();
            $req = $db->prepare($sql);
            $req->bindValue(':idOrder', $idOrder, PDO::PARAM_INT);
            $req->bindValue(':ID_Utilisateur', $order->getIDUtilisateur(), PDO::PARAM_INT);
            $req->bindValue(':id_service', $order->getIdService(), PDO::PARAM_INT);
            $req->bindValue(':date_commande', $order->getDateCommande());
            $req->bindValue(':station', $order->getStation());
            $req->bindValue(':statut', $order->getStatut());
            $req->execute();
            if ($req->rowCount() === 0) {
                throw new Exception("No order found with idOrder $idOrder to update.");
            }
        } catch (Exception $e) {
            error_log("Error in UpdateOrder: " . $e->getMessage());
            throw new Exception("Failed to update order: " . $e->getMessage());
        }
    }
}
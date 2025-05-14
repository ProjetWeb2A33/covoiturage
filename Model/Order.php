<?php
class Order {
    private $idOrder;
    private $ID_Utilisateur;
    private $id_service;
    private $date_commande;
    private $station;
    private $statut;

    public function __construct($idOrder, $ID_Utilisateur, $id_service, $date_commande, $station, $statut) {
        $this->idOrder = $idOrder;
        $this->ID_Utilisateur = $ID_Utilisateur;
        $this->id_service = $id_service;
        $this->date_commande = $date_commande;
        $this->station = $station;
        $this->statut = $statut;
    }

    public function getIdOrder() {
        return $this->idOrder;
    }

    public function getIDUtilisateur() {
        return $this->ID_Utilisateur;
    }

    public function getIdService() {
        return $this->id_service;
    }

    public function getDateCommande() {
        return $this->date_commande;
    }

    public function getStation() {
        return $this->station;
    }

    public function getStatut() {
        return $this->statut;
    }
}
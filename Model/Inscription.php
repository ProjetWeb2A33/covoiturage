<?php

class Inscription 
{
    private int $id;
    private int $telephone;
    private string $categorie;
    private string $dateReservation;
    private string $paiement;
    private int $id_trajet;

    public function __construct($telephone, $categorie, $dateReservation, $paiement, $id_trajet)
    {
        $this->telephone = $telephone;
        $this->categorie = $categorie;
        $this->dateReservation = $dateReservation;
        $this->paiement = $paiement;
        $this->id_trajet = $id_trajet;
    } 

    /*public function __destruct () {
        echo "je suis le destructor";
    }*/

    public function getID(){
        return $this->id;
    }
    public function getTelephone(){
        return $this->telephone;
    }
    public function getCategorie(){
        return $this->categorie;
    }
    public function getDateReservation(){
        return $this->dateReservation;
    }
    public function getPaiement(){
        return $this->paiement;
    }
    public function getID_Trajet(){
        return $this->id_trajet;
    }

    public function setID(int $id){
        $this->id = $id;
    }
    public function setTelephone(int $telephone){
        $this->telephone = $telephone;
    }
    public function setCategorie(string $categorie){
        $this->categorie = $categorie;
    }
    public function setDateReservation(string $dateReservation){
        $this->dateReservation = $dateReservation;
    }
    public function setPaiement(string $paiement){
        $this->paiement = $paiement;
    }
    public function setID_Trajet(int $id_trajet){
        $this->id_trajet = $id_trajet;
    }
}
?>
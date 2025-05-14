<?php
class Trajet {
    private $ID_Trajet;
    private $CIN;
    private $AdresseDepart;
    private $AdresseArrivee;
    private $NombrePlaces;
    private $Prix;
    private $Distance;
    private $Duree;

    public function __construct($CIN, $AdresseDepart, $AdresseArrivee, $NombrePlaces, $Prix, $Distance, $Duree) {
        $this->CIN = $CIN;
        $this->AdresseDepart = $AdresseDepart;
        $this->AdresseArrivee = $AdresseArrivee;
        $this->NombrePlaces = $NombrePlaces;
        $this->Prix = $Prix;
        $this->Distance = $Distance;
        $this->Duree = $Duree;
    }

    // Getters
    public function getID_Trajet() { 
        return $this->ID_Trajet;
    }
    public function getCIN() {
        return $this->CIN; 
    }
    public function getAdresseDepart() {
        return $this->AdresseDepart;
    }
    public function getAdresseArrivee() { 
        return $this->AdresseArrivee;
    }
    public function getNombrePlaces() {
        return $this->NombrePlaces; 
    }
    public function getPrix() { 
        return $this->Prix; 
    }
    public function getDistance() { 
        return $this->Distance; 
    }
    public function getDuree() {
        return $this->Duree; 
    }

    // Setters
    public function setID_Trajet($id) {
        $this->ID_Trajet = $id;
    }
    public function setCIN($C) { 
        $this->CIN = $C;
    }
    public function setAdresseDepart($a) { 
        $this->AdresseDepart = $a;
    }
    public function setAdresseArrivee($a) { 
        $this->AdresseArrivee = $a; 
    }
    public function setNombrePlaces($n) { 
        $this->NombrePlaces = $n; 
    }
    public function setPrix($p) { 
        $this->Prix = $p;
    }
    public function setDistance($d) { 
        $this->Distance = $d;
    }
    public function setDuree($d) { 
        $this->Duree = $d; 
    }
}
?>
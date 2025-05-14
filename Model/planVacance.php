<?php
class PlanVacance
{
    private ?int $id_plan = null;
    private ?string $identifiant = null;
    private ?string $nom_utilisateur = null;
    private ?string $email = null; // New email field
    private ?string $date_depart = null;
    private ?string $date_retour = null;
    private ?string $type_transport = null;
    private ?string $location_voiture = null;
    private ?string $besoin_parking = null;
    private ?int $id_hotel = null;

    public function __construct($id, $identifiant, $nu, $email, $dd, $dr, $tt, $lv, $bp, $idh)
    {
        $this->id_plan = $id;
        $this->identifiant = $identifiant;
        $this->nom_utilisateur = $nu;
        $this->email = $email; // New email assignment
        $this->date_depart = $dd;
        $this->date_retour = $dr;
        $this->type_transport = $tt;
        $this->location_voiture = $lv;
        $this->besoin_parking = $bp;
        $this->id_hotel = $idh;
    }

    // Getters and setters for identifiant
    public function getIdentifiant() { return $this->identifiant; }
    public function setIdentifiant($identifiant) { $this->identifiant = $identifiant; return $this; }

    // Getters and setters for email
    public function getEmail() { return $this->email; }
    public function setEmail($email) { $this->email = $email; return $this; }

    // Existing getters/setters...
    public function getIdPlan() { return $this->id_plan; }
    public function getNomUtilisateur() { return $this->nom_utilisateur; }
    public function setNomUtilisateur($nu) { $this->nom_utilisateur = $nu; return $this; }
    public function getDateDepart() { return $this->date_depart; }
    public function setDateDepart($dd) { $this->date_depart = $dd; return $this; }
    public function getDateRetour() { return $this->date_retour; }
    public function setDateRetour($dr) { $this->date_retour = $dr; return $this; }
    public function getTypeTransport() { return $this->type_transport; }
    public function setTypeTransport($tt) { $this->type_transport = $tt; return $this; }
    public function getLocationVoiture() { return $this->location_voiture; }
    public function setLocationVoiture($lv) { $this->location_voiture = $lv; return $this; }
    public function getBesoinParking() { return $this->besoin_parking; }
    public function setBesoinParking($bp) { $this->besoin_parking = $bp; return $this; }
    public function getIdHotel() { return $this->id_hotel; }
    public function setIdHotel($idh) { $this->id_hotel = $idh; return $this; }
}

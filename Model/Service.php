<?php


class Service
{
    private $Nom_u;
    private $station_id;
    private $duree_heures;
    private $montant;

    public function __construct($Nom_u = '', $station_id = 0, $duree_heures = 0, $montant = 0)
    {
        $this->Nom_u        = $Nom_u;
        $this->station_id   = $station_id;
        $this->duree_heures = $duree_heures;
        $this->montant      = $montant;
    }

    // camelCase → correspond aux appels dans tables1.php
    public function setNomU($Nom_u)             { $this->Nom_u = $Nom_u; }
    public function getNomU()                   { return $this->Nom_u; }
    public function setStationId($station_id)   { $this->station_id = $station_id; }
    public function getStationId()               { return $this->station_id; }
    public function setDureeHeures($duree_heures) { $this->duree_heures = $duree_heures; }
    public function getDureeHeures()             { return $this->duree_heures; }
    public function setMontant($montant)        { $this->montant = $montant; }
    public function getMontant()                 { return $this->montant; }
}

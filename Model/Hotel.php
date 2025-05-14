<?php
class Hotel
{
    private ?int $id_hotel = null;
    private ?string $nom_hotel = null;
    private ?string $adresse = null;
    private ?string $ville = null;
    private ?int $nombre_places_parking = null;
    private ?int $places_parking_disponibles = null;
    private ?string $categorie = null;
    private ?string $image = null;

    public function __construct($id = null, $n, $a, $v, $npp, $ppd, $c, $img = null)
    {
        $this->id_hotel = $id;
        $this->nom_hotel = $n;
        $this->adresse = $a;
        $this->ville = $v;
        $this->nombre_places_parking = $npp;
        $this->places_parking_disponibles = $ppd;
        $this->categorie = $c;
        $this->image = $img;
    }

    // Getters and Setters
    public function getIdHotel() { return $this->id_hotel; }

    public function getNomHotel() { return $this->nom_hotel; }
    public function setNomHotel($n) { $this->nom_hotel = $n; return $this; }

    public function getAdresse() { return $this->adresse; }
    public function setAdresse($a) { $this->adresse = $a; return $this; }

    public function getVille() { return $this->ville; }
    public function setVille($v) { $this->ville = $v; return $this; }

    public function getNombrePlacesParking() { return $this->nombre_places_parking; }
    public function setNombrePlacesParking($npp) { $this->nombre_places_parking = $npp; return $this; }

    public function getPlacesParkingDisponibles() { return $this->places_parking_disponibles; }
    public function setPlacesParkingDisponibles($ppd) { $this->places_parking_disponibles = $ppd; return $this; }

    public function getCategorie() { return $this->categorie; }
    public function setCategorie($c) { $this->categorie = $c; return $this; }

    public function getImage() { return $this->image; }
    public function setImage($img) { $this->image = $img; return $this; }
}
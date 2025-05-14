<?php

class Evenement
{
    private ?int $idE= null;
    private ?string $nomE= null;
    private ?string $date=null;
    private ?string $lieu=null;
    private ?int $nbrPlace_restante=null;
    private ?int $nbrPlace_occupe=null;
    private ?float $tarification=null;
    
    // Constructeur sans ID (pour les insertions)
public function __construct(
    ?int $id = null,
    string $nomE,
    string $date,
    string $lieu,
    int $nbrPlace_restante,
    int $nbrPlace_occupe,
    float $tarification,
) {
    $this->idE = $id;
    $this->nomE = $nomE;
    $this->date = $date;
    $this->lieu = $lieu;
    $this->nbrPlace_restante = $nbrPlace_restante;
    $this->nbrPlace_occupe = $nbrPlace_occupe;
    $this->tarification = $tarification;
}


    // Getters

    public function getIdE(): int
    {
        return $this->idE;
    }

    public function getNomE(): string
    {
        return $this->nomE;
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function getLieu(): string
    {
        return $this->lieu;
    }

    public function getNbrPlaceRestante(): int
    {
        return $this->nbrPlace_restante;
    }

    public function getNbrPlaceOccupe(): int
    {
        return $this->nbrPlace_occupe;
    }

    public function getTarification(): float
    {
        return $this->tarification;
    }

    // Setters

    public function setIdE(int $idE): void
    {
        $this->idE = $idE;
    }

    public function setNomE(string $nomE): void
    {
        $this->nomE = $nomE;
    }

    public function setDate(string $date): void
    {
        $this->date = $date;
    }

    public function setLieu(string $lieu): void
    {
        $this->lieu = $lieu;
    }

    public function setNbrPlaceRestante(int $nbr): void
    {
        $this->nbrPlace_restante = $nbr;
    }

    public function setNbrPlaceOccupe(int $nbr): void
    {
        $this->nbrPlace_occupe = $nbr;
    }

    public function setTarification(float $tarif): void
    {
        $this->tarification = $tarif;
    }

}

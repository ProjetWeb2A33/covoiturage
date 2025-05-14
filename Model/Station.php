<?php
require_once __DIR__ . '/../config.php';

class Station {
    private int $idStation;
    private string $Emplacement;
    private string $TypeS;

    public function __construct(int $idStation = 0, string $Emplacement = '', string $TypeS = '') {
        $this->idStation = $idStation;
        $this->Emplacement = $Emplacement;
        $this->TypeS = $TypeS;
    }

    // Getters
    public function getIdStation(): int {
        return $this->idStation;
    }

    public function getEmplacement(): string {
        return $this->Emplacement;
    }

    public function getTypeS(): string {
        return $this->TypeS;
    }

    // Setters
    public function setIdStation(int $idStation): void {
        $this->idStation = $idStation;
    }

    public function setEmplacement(string $Emplacement): void {
        $this->Emplacement = $Emplacement;
    }

    public function setTypeS(string $TypeS): void {
        $this->TypeS = $TypeS;
    }
}
?>
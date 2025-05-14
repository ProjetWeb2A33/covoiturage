<?php
// Empêcher toute sortie avant la génération du PDF
ob_start();

// Fichier d'export PDF pour les trajets
require_once 'C:/xampp/htdocs/integration/Controller/TrajetC.php';


// Charger la bibliothèque TCPDF
require 'C:\xampp\htdocs\integration\view\TCPDF\tcpdf.php';

// Définir notre classe personnalisée qui étend TCPDF
class PDF extends TCPDF {
    public function Header() {
        $this->SetFont('helvetica', 'B', 16);
        $this->Cell(0, 15, 'Liste des Trajets - EasyParki', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(15);
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

// Obtenir les trajets
$tc = new TrajetC();
$listeTrajets = $tc->ListeTrajet();

// Créer une instance de PDF
$pdf = new PDF('L', 'mm', 'A4', true, 'UTF-8', false);

// Définir les informations du document
$pdf->SetCreator('EasyParki');
$pdf->SetAuthor('EasyParki');
$pdf->SetTitle('Liste des Trajets');
$pdf->SetSubject('Liste des Trajets');
$pdf->SetKeywords('EasyParki, Trajets, PDF, Liste');

// Activer l'en-tête et le pied de page
$pdf->setPrintHeader(true);
$pdf->setPrintFooter(true);

// Configurer les marges
$pdf->SetMargins(10, 20, 10);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);

// Configurer l'auto page breaks
$pdf->SetAutoPageBreak(TRUE, 15);

// Ajouter une page
$pdf->AddPage();

// En-tête du tableau
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetFillColor(52, 152, 219); // Couleur bleue
$pdf->SetTextColor(255, 255, 255); // Texte blanc

// Définir les largeurs des colonnes
$w = array(15, 20, 45, 45, 20, 25, 25, 40);

// En-tête du tableau
$header = array('ID', 'ID Insc.', 'Adresse Départ', 'Adresse Arrivée', 'Places', 'Prix (DT)', 'Distance', 'Durée');
for($i = 0; $i < count($header); $i++) {
    $pdf->Cell($w[$i], 10, $header[$i], 1, 0, 'C', 1);
}
$pdf->Ln();

// Contenu du tableau
$pdf->SetFont('helvetica', '', 9);
$pdf->SetFillColor(240, 240, 240); // Couleur grise claire alternée
$pdf->SetTextColor(0, 0, 0); // Texte noir
$fill = false;
foreach ($listeTrajets as $trajet) {
    $pdf->Cell($w[0], 8, $trajet['ID_Trajet'], 1, 0, 'C', $fill);
    $pdf->Cell($w[1], 8, $trajet['ID_Inscription'], 1, 0, 'C', $fill);
    $pdf->Cell($w[2], 8, $trajet['Adresse_Depart'], 1, 0, 'L', $fill);
    $pdf->Cell($w[3], 8, $trajet['Adresse_Arrivee'], 1, 0, 'L', $fill);
    $pdf->Cell($w[4], 8, $trajet['Nombre_Places'], 1, 0, 'C', $fill);
    $pdf->Cell($w[5], 8, $trajet['Prix'] . ' DT', 1, 0, 'R', $fill);
    $pdf->Cell($w[6], 8, $trajet['Distance'] . ' km', 1, 0, 'R', $fill);
    $pdf->Cell($w[7], 8, $trajet['Duree'], 1, 0, 'C', $fill);
    $pdf->Ln();
    $fill = !$fill; // Alterner les couleurs de fond
}

// Ajouter des statistiques
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Statistiques des Trajets', 0, 1, 'C');
$pdf->Ln(5);

// Obtenir les statistiques
$statsAdresses = $tc->getStatistiqueAdressesDepart();
$statsPlaces = $tc->getStatistiquePlaces();
$statsPrix = $tc->getStatistiquePrix();

// Tableau des statistiques d'adresses de départ
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Répartition par adresse de départ', 0, 1, 'L');
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetFillColor(52, 152, 219);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(80, 8, 'Adresse de départ', 1, 0, 'C', 1);
$pdf->Cell(30, 8, 'Nombre de trajets', 1, 1, 'C', 1);
$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(0, 0, 0);
$fill = false;
foreach ($statsAdresses as $stat) {
    $pdf->Cell(80, 8, $stat['Adresse_Depart'], 1, 0, 'L', $fill);
    $pdf->Cell(30, 8, $stat['nombre'], 1, 1, 'C', $fill);
    $fill = !$fill;
}
$pdf->Ln(10);

// Tableau des statistiques de places
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Répartition par nombre de places', 0, 1, 'L');
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetFillColor(52, 152, 219);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(80, 8, 'Nombre de places', 1, 0, 'C', 1);
$pdf->Cell(30, 8, 'Nombre de trajets', 1, 1, 'C', 1);
$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(0, 0, 0);
$fill = false;
foreach ($statsPlaces as $stat) {
    $pdf->Cell(80, 8, $stat['Nombre_Places'] . ' places', 1, 0, 'L', $fill);
    $pdf->Cell(30, 8, $stat['nombre'], 1, 1, 'C', $fill);
    $fill = !$fill;
}
$pdf->Ln(10);

// Tableau des statistiques de prix
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Répartition par tranche de prix', 0, 1, 'L');
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetFillColor(52, 152, 219);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(80, 8, 'Tranche de prix', 1, 0, 'C', 1);
$pdf->Cell(30, 8, 'Nombre de trajets', 1, 1, 'C', 1);
$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(0, 0, 0);
$fill = false;
foreach ($statsPrix as $stat) {
    $pdf->Cell(80, 8, $stat['tranche_prix'], 1, 0, 'L', $fill);
    $pdf->Cell(30, 8, $stat['nombre'], 1, 1, 'C', $fill);
    $fill = !$fill;
}

// Avant de générer le PDF, nettoyer tout tampon de sortie
ob_end_clean();

// Générer le PDF et le télécharger
$pdfFileName = 'Liste_Trajets_' . date('Y-m-d') . '.pdf';

// Forcer le téléchargement du PDF
$pdf->Output($pdfFileName, 'D');
exit; // Assurez-vous que rien n'est exécuté après
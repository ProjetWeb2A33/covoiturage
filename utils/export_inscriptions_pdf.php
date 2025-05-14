<?php
// Empêcher toute sortie avant la génération du PDF
ob_start();

// Fichier d'export PDF pour les inscriptions
require_once 'C:/xampp/htdocs/integration/Controller/InscriptionC.php';

// Charger la bibliothèque TCPDF
require 'C:\xampp\htdocs\integration\view\TCPDF\tcpdf.php';
// require_once 'C:/xampp/htdocs/integration/view/TCPDF/tcpdf.php';

// Définir notre classe personnalisée qui étend TCPDF
class PDF extends TCPDF {
    public function Header() {
        $this->SetFont('helvetica', 'B', 16);
        $this->Cell(0, 15, 'Liste des Inscriptions - EasyParki', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(15);
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

// Obtenir les inscriptions
$ic = new InscriptionC();
$listeInscriptions = $ic->ListeInscription();

// Obtenir les statistiques
$statsCategories = $ic->getStatistiqueCategories();
$statsPaiements = $ic->getStatistiquePaiements();
$statsMois = $ic->getStatistiqueParMois();

// Créer une instance de PDF
$pdf = new PDF('P', 'mm', 'A4', true, 'UTF-8', false);

// Définir les informations du document
$pdf->SetCreator('EasyParki');
$pdf->SetAuthor('EasyParki');
$pdf->SetTitle('Liste des Inscriptions');
$pdf->SetSubject('Liste des Inscriptions');
$pdf->SetKeywords('EasyParki, Inscriptions, PDF, Liste');

// Activer l'en-tête et le pied de page
$pdf->setPrintHeader(true);
$pdf->setPrintFooter(true);

// Configurer les marges
$pdf->SetMargins(15, 20, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);

// Configurer l'auto page breaks
$pdf->SetAutoPageBreak(TRUE, 15);

// Ajouter une page
$pdf->AddPage();

// En-tête du tableau
$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetFillColor(52, 152, 219); // Couleur bleue
$pdf->SetTextColor(255, 255, 255); // Texte blanc

// Définir les largeurs des colonnes
$w = array(15, 40, 40, 50, 45);

// En-tête du tableau
$header = array('ID', 'Téléphone', 'Catégorie', 'Date Réservation', 'Mode Paiement');
for($i = 0; $i < count($header); $i++) {
    $pdf->Cell($w[$i], 10, $header[$i], 1, 0, 'C', 1);
}
$pdf->Ln();

// Contenu du tableau
$pdf->SetFont('helvetica', '', 10);
$pdf->SetFillColor(240, 240, 240); // Couleur grise claire alternée
$pdf->SetTextColor(0, 0, 0); // Texte noir
$fill = false;

foreach ($listeInscriptions as $inscription) {
    // Normalisation des clés pour s'assurer que nous avons les bonnes clés
    $id = isset($inscription['id']) ? $inscription['id'] : (isset($inscription['ID']) ? $inscription['ID'] : 'N/A');
    $telephone = isset($inscription['telephone']) ? $inscription['telephone'] : (isset($inscription['Telephone']) ? $inscription['Telephone'] : 'N/A');
    $categorie = isset($inscription['categorie']) ? $inscription['categorie'] : (isset($inscription['Categorie']) ? $inscription['Categorie'] : 'N/A');
    $dateReservation = isset($inscription['dateReservation']) ? $inscription['dateReservation'] : (isset($inscription['DateReservation']) ? $inscription['DateReservation'] : 'N/A');
    $paiement = isset($inscription['paiement']) ? $inscription['paiement'] : (isset($inscription['Paiement']) ? $inscription['Paiement'] : 'N/A');
    
    $pdf->Cell($w[0], 8, $id, 1, 0, 'C', $fill);
    $pdf->Cell($w[1], 8, $telephone, 1, 0, 'L', $fill);
    $pdf->Cell($w[2], 8, $categorie, 1, 0, 'C', $fill);
    $pdf->Cell($w[3], 8, $dateReservation, 1, 0, 'C', $fill);
    $pdf->Cell($w[4], 8, $paiement, 1, 0, 'C', $fill);
    $pdf->Ln();
    $fill = !$fill; // Alterner les couleurs de fond
}

// Ajouter une page pour les statistiques
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Statistiques des Inscriptions', 0, 1, 'C');
$pdf->Ln(5);

// Tableau des statistiques par catégorie
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Répartition par catégorie', 0, 1, 'L');
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetFillColor(52, 152, 219);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(80, 8, 'Catégorie', 1, 0, 'C', 1);
$pdf->Cell(30, 8, 'Nombre', 1, 1, 'C', 1);
$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(0, 0, 0);
$fill = false;
foreach ($statsCategories as $stat) {
    $pdf->Cell(80, 8, $stat['categorie'], 1, 0, 'L', $fill);
    $pdf->Cell(30, 8, $stat['nombre'], 1, 1, 'C', $fill);
    $fill = !$fill;
}
$pdf->Ln(10);

// Tableau des statistiques par mode de paiement
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Répartition par mode de paiement', 0, 1, 'L');
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetFillColor(52, 152, 219);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(80, 8, 'Mode de paiement', 1, 0, 'C', 1);
$pdf->Cell(30, 8, 'Nombre', 1, 1, 'C', 1);
$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(0, 0, 0);
$fill = false;
foreach ($statsPaiements as $stat) {
    $pdf->Cell(80, 8, $stat['paiement'], 1, 0, 'L', $fill);
    $pdf->Cell(30, 8, $stat['nombre'], 1, 1, 'C', $fill);
    $fill = !$fill;
}
$pdf->Ln(10);

// Tableau des statistiques par mois
if (count($statsMois) > 0) {
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Répartition par mois', 0, 1, 'L');
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetFillColor(52, 152, 219);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(80, 8, 'Mois', 1, 0, 'C', 1);
    $pdf->Cell(30, 8, 'Nombre', 1, 1, 'C', 1);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(0, 0, 0);
    $fill = false;
    foreach ($statsMois as $stat) {
        // Formater le mois pour l'affichage (YYYY-MM → Mois YYYY)
        $date = \DateTime::createFromFormat('Y-m', $stat['mois']);
        $moisFormate = $date ? $date->format('F Y') : $stat['mois'];
        
        $pdf->Cell(80, 8, $moisFormate, 1, 0, 'L', $fill);
        $pdf->Cell(30, 8, $stat['nombre'], 1, 1, 'C', $fill);
        $fill = !$fill;
    }
}

// Avant de générer le PDF, nettoyer tout tampon de sortie
ob_end_clean();

// Générer le PDF et le télécharger
$pdfFileName = 'Liste_Inscriptions_' . date('Y-m-d') . '.pdf';

// Forcer le téléchargement du PDF
$pdf->Output($pdfFileName, 'D');
exit; // Assurez-vous que rien n'est exécuté après
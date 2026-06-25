<?php
require_once 'config.php';
require_once 'fpdf.php'; 

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) die("Accès interdit.");

$stmt = db()->prepare("
    SELECT s.*, st.titre as stage_titre, d.nom as dept_nom 
    FROM stagiaires s 
    JOIN stages st ON s.stage_id = st.id 
    LEFT JOIN departements d ON st.departement_id = d.id 
    WHERE s.id = ?
");
$stmt->execute([$id]);
$s = $stmt->fetch();

if (!$s) die("Candidature introuvable.");

class PDF extends FPDF {
    function Header() {
        if (file_exists('logo.png')) {
            $this->Image('logo.png', 10, 6, 30);
        }
        $this->SetFont('Helvetica', 'B', 15);
        $this->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'RÉCÉPISSÉ DE CANDIDATURE'), 0, 0, 'C');
        $this->Ln(20);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// Infos du Ministère / Département
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', "M.  T.  C.  A"), 0, 1, 'C');
$pdf->Ln(5); // Ajout d'un saut de ligne manquant
$pdf->SetFont('Helvetica', 'B', 12); // Correction de la ligne incomplète
$pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', "Département sollicité : " . ($s['dept_nom'] ?? 'Non spécifié')), 0, 1, 'L');
$pdf->SetFont('Helvetica', '', 12);
$pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', "Stage : " . $s['stage_titre']), 0, 1, 'L');
$pdf->Ln(10);
// Tableau récapitulatif
$pdf->SetFont('Helvetica', 'B', 12);
$pdf->Cell(60, 10, 'Champs', 1, 0, 'C', true);
$pdf->Cell(130, 10, 'Renseignements', 1, 1, 'C', true); // Correction faute de frappe
$pdf->SetFont('Helvetica', '', 11); // Correction de la ligne incomplète
$data = [
    'Nom' => $s['nom'],
    'Prénom' => $s['prenom'],
    'Email' => $s['email'], 
    'Téléphone' => $s['telephone'],
    'Date de naissance' => date('d/m/Y', strtotime($s['date_naissance'])),
    'Adresse' => $s['adresse'],
    'Formation' => $s['formation'],
    'Spécialité' => $s['specialite'],
    'Type de stage' => ucfirst($s['type_stage']),
    'Durée' => $s['duree_choisie'],
    'Motif' => ucfirst($s['motif'])
];

if ($s['type_stage'] === 'academique') {
    $data['Modalité'] = ucfirst($s['modalite']);
    $data['Ecole'] = $s['ecole_nom'];
    $data['Directeur Ecole'] = $s['ecole_directeur'];
    $data['Adresse Ecole'] = $s['ecole_adresse'];
    $data['Téléphone Ecole'] = $s['ecole_telephone'];
    $data['Email Ecole'] = $s['ecole_email'];
}

if ($s['modalite'] === 'binome') {
    $data['---'] = '---'; // Séparateur visuel
    $data['Nom Binôme'] = $s['binome_nom'];
    $data['Prénom Binôme'] = $s['binome_prenom'];
    $data['Email Binôme'] = $s['binome_email'];
    $data['Tél Binôme'] = $s['binome_telephone'];
    $data['Né(e) le (Binôme)'] = date('d/m/Y', strtotime($s['binome_date_naissance']));
    $data['Adresse Binôme'] = $s['binome_adresse'];
    $data['Formation Binôme'] = $s['binome_formation'];
    $data['Spécialité Binôme'] = $s['binome_specialite'];
}

foreach ($data as $key => $val) {
    $pdf->Cell(60, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', (string)$key), 1);
    $pdf->Cell(130, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', (string)$val), 1);
    $pdf->Ln();
}

$pdf->Ln(20);
$pdf->SetFont('Helvetica', 'I', 10);
$pdf->MultiCell(0, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', "Ce document atteste que votre candidature a bien été enregistrée le " . date('d/m/Y à H:i') . ".\nConservez ce récépissé pour toute réclamation."));

// Sortie du PDF
header('Content-Disposition: attachment; filename="Recepisse_'.$s['nom'].'.pdf"'); // Correction de la ligne incomplète
$pdf->Output('D', 'Recepisse_'.$s['nom'].'.pdf');
exit;
?>
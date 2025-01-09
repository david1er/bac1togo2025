<?php
include_once 'generales.php';
$lien = lien();

foreach ($_REQUEST as $key => $val) {
    $$key = mysqli_real_escape_string($lien, trim(addslashes($val)));
}

if ($annee == '') {
    $annee = recupAnnee($lien); //$annee = date("Y");
}

//Recuperation du timbre
  
$sql_timbre = "SELECT ministere, secretariat_general, direction, libelle_examen FROM bg_timbre WHERE 1";
$result_timbre = bg_query($lien, $sql_timbre, __FILE__, __LINE__);
$row_timbre = mysqli_fetch_array($result_timbre);

$ministere = $row_timbre['ministere'];
$secretariat_general = $row_timbre['secretariat_general'];
$direction = $row_timbre['direction'];
$libelle_examen = $row_timbre['libelle_examen'];

$pdf_dir = '../ezpdf/';
include $pdf_dir . 'class.ezpdf.php'; // inclusion du code de la bibliothque
$pdf = new Cezpdf('A4', 'portrait'); // 595.28 x 841.89
$pdf->selectFont($pdf_dir . 'fonts/Helvetica.afm');
$options = array('b' => 'Helvetica-Bold.afm');
$family = 'Helvetica';
$pdf->setFontFamily($family, $options);
$pdf->setStrokeColor(0, 0, 0);
$pdf->setLineStyle(1, 'round', 'round');
$width = $pdf->ez['pageWidth'];
$height = $pdf->ez['pageHeight'];
setlocale(LC_TIME, 'fr_FR', 'fr_BE.UTF8', 'fr_FR.UTF8');
$sDate = utf8_decode(strftime("%A %d %B %Y", mktime(0, 0, 0, date('m'), date('d'), date('Y'))));
$DatePied = "IMPRIME LE " . date('d') . '/' . date('m') . '/' . date('Y');
$pdf->ezStartPageNumbers(570, 15, 8, 'left', "$DatePied - Page {PAGENUM} sur {TOTALPAGENUM}");

$options = array('titleFontSize' => 16, 'fontSize' => 10);
$id_inspection = $_GET['id_inspection'];

$sql = "SELECT eta.etablissement,eta.si_centre, eta.id_centre, eta.id
       FROM bg_ref_etablissement eta, bg_ref_inspection insp
	   WHERE eta.id_inspection=insp.id AND insp.id=$id_inspection AND eta.id_centre!=0
       GROUP BY eta.id_centre,eta.etablissement ";
//echo $sql;
$result = bg_query($lien, $sql, __FILE__, __LINE__);
//var_dump($result);

$data = array();
$colonnes = array('etablissement', 'id_centre', 'si_centre', 'id');
while ($row = mysqli_fetch_array($result)) {
    foreach ($colonnes as $col) {
        $$col = utf8_decode($row[$col]);
    }

    if ($si_centre == 'non') {
        $sql11 = "SELECT etablissement FROM bg_ref_etablissement WHERE id=$id_centre";
        $result11 = bg_query($lien, $sql11, __FILE__, __LINE__);
        $sol11 = mysqli_fetch_array($result11);
        $id_centre = ($sol11['etablissement']);
        var_dump($id);
    } else if ($si_centre == 'oui') {
        $id_centre = ($etablissement);
        //var_dump($centre);

    }

    $sql_effectif = "SELECT COUNT(id_candidat) AS effectif FROM bg_candidats WHERE etablissement = $id";
    $result_effectif = bg_query($lien, $sql_effectif, __FILE__, __FILE__);
    $sol_effectif = mysqli_fetch_array($result_effectif);
    $effectif = $sol_effectif['effectif'];
    var_dump($effectif);

    $data[$jury][] = array('Etablissement' => $etablissement, 'Id' => $id_centre, 'Effectif' => $effectif);
}

$cols = array('Id' => 'CENTRE D\'ECRIT', 'Etablissement' => 'ETABLISSEMENTS', 'Effectif' => 'EFFECTIF');
//$titre_tab='Jury '.$j;
$sql20 = "SELECT insp.inspection, reg.region
		  FROM bg_ref_inspection insp, bg_ref_region reg
		  WHERE insp.id_region=reg.id AND insp.id=$id_inspection";
$result20 = bg_query($lien, $sql20, __FILE__, __LINE__);
$sol20 = mysqli_fetch_array($result20);
$inspection = ($sol20['inspection']);
$region = ($sol20['region']);

foreach ($data as $jury => $data2) {
    $pdf->ezSetY($height - 25);
    $pdf->addJpegFromFile("../images/logo.png", 275, 750, 45);
    $pdf->ezColumnsStart(array('num' => 3, 'gap' => 10));
    $pdf->ezText(utf8_decode("<b>$ministere\n**********\n$secretariat_general\n**********\n$direction</b>"), 9, array('justification' => 'center'));
    $pdf->ezNewPage();
    $pdf->ezNewPage();
    $pdf->ezText(utf8_decode("<b>REPUBLIQUE TOGOLAISE\nTravail - Liberté - Patrie</b>"), 9, array('justification' => 'center'));
    $pdf->ezColumnsStop();

    $pdf->ezSetY($height - 100);
    $pdf->ezText(utf8_decode("<b>\n\n $libelle_examen </b>"), 11, array('justification' => 'center'));
    $mois = moisSession2($lien, $annee);
    $pdf->ezText(utf8_decode("<b>Session de $mois $annee\n</b>"), 10, array('justification' => 'center'));

    $pdf->ezText(utf8_decode("\n<b><u>Listes des établissements avec leurs centres d'ecrits</u>\n\nREGION: $region\nINSPECTION: $inspection</b>\n"), 13, array('justification' => 'center'));

    $pdf->ezTable($data2, $cols, $titre_tab, $options);
}
//$pdf->setEncryption("$param","$param",array('copy','print'));
//$pdf->ezInsertMode(1,1,'before');
$pdf->ezInsertMode(0);
$pdfG = "Listes_des_etablissements" . getRewriteString($j);

$title = "Listes des etablissements ";
$infos = array('Title' => $title, 'Author' => 'CNDP-TICE', 'CreationDate' => date("d/m/Y"));
$pdf->addInfo($infos);

// do the output, this is my standard testing output code, adding ?d=1
// to the url puts the pdf code to the screen in raw form, good for
// checking
// for parse errors before you actually try to generate the pdf file.
if (isset($_REQUEST['d'])) {
    $d = $_REQUEST['d'];
}

if (isset($d) && $d) {
    $pdfcode = $pdf->output(1);
    $pdfcode = str_replace("\n", "\n<br>", htmlspecialchars($pdfcode));
    echo '<html><body>';
    echo trim($pdfcode);
    echo '</body></html>';
} else {
    //$pdf->ezStream();

    $pdfcode = $pdf->ezOutput();
    $pdf_fic = "pdf/" . $annee . "-$pdfG.pdf";
    $fp = fopen($pdf_fic, 'wb');
    fwrite($fp, $pdfcode);
    fclose($fp);

    echo "<A HREF='$pdf_fic'>Cliquez ici pour lire le fichier $pdf_fic</a>\n";
    echo "<script>document.location='$pdf_fic'</script>";
}

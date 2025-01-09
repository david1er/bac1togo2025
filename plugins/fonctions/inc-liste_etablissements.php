<?php
include_once 'generales.php';
$lien = lien();

foreach ($_REQUEST as $key => $val) {
    $$key = mysqli_real_escape_string($lien, trim(addslashes($val)));
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
$pdf = new Cezpdf('A4', 'landscape'); // 595.28 x 841.89
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

if (isset($_GET['id_region'])) {
    $sql = "SELECT cen.si_centre, eta.id_centre as id_centre, reg.region, pre.prefecture, eta.etablissement, cen.etablissement as centre, vil.ville, ins.inspection, eta.login_eta, eta.mdp_eta
		FROM bg_ref_inspection ins, bg_ref_ville vil, bg_ref_prefecture pre, bg_ref_region reg, bg_ref_etablissement eta
		LEFT JOIN bg_ref_etablissement cen ON eta.id_centre=cen.id
		WHERE reg.id=pre.id_region
		AND pre.id=vil.id_prefecture
		AND eta.id_ville=vil.id
		AND ins.id=eta.id_inspection
		AND eta.si_centre='non'
        AND reg.id=$id_region
		ORDER BY reg.region, pre.prefecture, cen.etablissement ";
} else {
    $sql = "SELECT cen.si_centre, eta.id_centre as id_centre, reg.region, pre.prefecture, eta.etablissement, cen.etablissement as centre, vil.ville, ins.inspection, eta.login_eta, eta.mdp_eta
    FROM bg_ref_inspection ins, bg_ref_ville vil, bg_ref_prefecture pre, bg_ref_region reg, bg_ref_etablissement eta
    LEFT JOIN bg_ref_etablissement cen ON eta.id_centre=cen.id
    WHERE reg.id=pre.id_region
    AND pre.id=vil.id_prefecture
    AND eta.id_ville=vil.id
    AND ins.id=eta.id_inspection
    AND eta.si_centre='non'
    ORDER BY reg.region, pre.prefecture, cen.etablissement ";
}
//echo $sql;
$result = bg_query($lien, $sql, __FILE__, __LINE__);

$data = array();
$colonnes = array('si_centre', 'id_centre', 'region', 'prefecture', 'etablissement', 'centre', 'ville', 'inspection', 'login_eta', 'mdp_eta');
while ($row = mysqli_fetch_array($result)) {
    foreach ($colonnes as $col) {
        $$col = utf8_decode($row[$col]);
    }

    $data[$id_centre][] = array('eta' => $etablissement, 'pre' => $prefecture, 'vil' => $ville, 'ins' => $inspection, 'log' => $login_eta, 'mdp' => $mdp_eta);
}

$cols = array('pre' => 'PREFECTURE', 'vil' => 'VILLE', 'ins' => 'INSPECTION', 'eta' => 'ETABLISSEMENT', 'log' => 'LOGIN', 'mdp' => 'MOT DE PASSE');
//$titre_tab='Jury '.$j;
foreach ($data as $id_centre => $data2) {
    $centre = selectReferentiel($lien, 'etablissement', $id_centre);
    $pdf->ezSetY($height - 25);
    $pdf->addJpegFromFile("../images/logo.png", 390, 520, 45);
    $pdf->ezColumnsStart(array('num' => 3, 'gap' => 10));
    $pdf->ezText(utf8_decode("<b>$ministere\n**********\n$secretariat_general\n**********\n$direction</b>"), 9, array('justification' => 'center'));
    $pdf->ezNewPage();
    $pdf->ezNewPage();
    $pdf->ezText(utf8_decode("<b>REPUBLIQUE TOGOLAISE\nTravail - Liberté - Patrie</b>"), 9, array('justification' => 'center'));
    $pdf->ezColumnsStop();

    $pdf->ezSetY($height - 100);
    $pdf->ezText(utf8_decode("<b>$libelle_examen</b>"), 11, array('justification' => 'center'));

    $pdf->ezText(utf8_decode("\n<b><u>LISTE DES CENTRES ET DES ETABLISSEMENTS</u>\nCENTRE: $centre</b>\n"), 13, array('justification' => 'center'));

    $pdf->ezTable($data2, $cols, $titre_tab);
    $pdf->ezNewPage();
}
//$pdf->ezInsertMode(1,1,'before');
$pdf->ezInsertMode(0);
$pdfG = "liste_etablissements" . getRewriteString($j);

$title = "Liste des etablissements ";
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

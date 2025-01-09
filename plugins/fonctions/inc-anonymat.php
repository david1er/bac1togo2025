<?php
include_once 'generales.php';
$lien = lien();

//Recuperation du timbre
  
$sql_timbre = "SELECT ministere, secretariat_general, direction, libelle_examen FROM bg_timbre WHERE 1";
$result_timbre = bg_query($lien, $sql_timbre, __FILE__, __LINE__);
$row_timbre = mysqli_fetch_array($result_timbre);

$ministere = $row_timbre['ministere'];
$secretariat_general = $row_timbre['secretariat_general'];
$direction = $row_timbre['direction'];
$libelle_examen = $row_timbre['libelle_examen'];

foreach ($_REQUEST as $key => $val) {
    $$key = mysqli_real_escape_string($lien, trim(addslashes($val)));
}

if ($annee == '') {
    $annee = recupAnnee($lien); //$annee = date("Y");
}

$param = selectCodeAno($lien, $annee, $s);
$centre = selectReferentiel($lien, 'etablissement', $c);

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
$pdf->ezStartPageNumbers(570, 15, 8, 'left', "$centre / $DatePied - Page {PAGENUM} sur {TOTALPAGENUM}");

$sql = "SELECT can.num_table, ser.serie, rep.jury, rep.id_anonyme as num_anonyme
		FROM bg_candidats can, bg_ref_serie ser, bg_repartition rep
		WHERE can.annee=$annee AND rep.annee=$annee AND can.num_table=rep.num_table AND can.serie=ser.id
		AND rep.id_centre=$c AND can.type_session=$s AND rep.id_type_session=$s
		ORDER BY rep.jury, rep.numero ";
$result = bg_query($lien, $sql, __FILE__, __LINE__);

$data = array();
$colonnes = array('num_table', 'serie', 'jury', 'num_anonyme');
while ($row = mysqli_fetch_array($result)) {
    foreach ($colonnes as $col) {
        $$col = utf8_encode($row[$col]);
    }

    $data[$jury][] = array('Jury' => $jury, 'Num' => $num_table, 'Ano' => $num_anonyme, 'Serie' => $serie);
}

$cols = array('Jury' => utf8_decode('JURYS'), 'Num' => 'NUMERO DE TABLE', 'Ano' => 'ANONYMAT', 'Serie' => 'SERIE');
$titre_tab = $centre;
foreach ($data as $jury => $data2) {
    $pdf->ezSetY($height - 25);
//    $pdf->addJpegFromFile("../images/logo.png",300,750,45);
    $pdf->ezColumnsStart(array('num' => 3, 'gap' => 10));
    $pdf->ezText(utf8_decode("<b>$ministere\n**********\n$secretariat_general\n**********\n$direction</b>"), 9, array('justification' => 'center'));
    $pdf->ezNewPage();
    $pdf->ezNewPage();
    $pdf->ezText(utf8_decode("<b>REPUBLIQUE TOGOLAISE\nTravail - Liberté - Patrie</b>"), 9, array('justification' => 'center'));
    $pdf->ezColumnsStop();

    $pdf->ezSetY($height - 100);
    $pdf->ezText(utf8_decode("<b>$libelle_examen</b>"), 11, array('justification' => 'center'));
    $mois = moisSession($lien, $annee, $jury);
    $pdf->ezText(utf8_decode("<b>Session de $mois $annee\n</b>"), 10, array('justification' => 'center'));
    $pdf->ezText(utf8_decode("\n<b><u>Anonymat des candidats</u>\nJury: $jury</b>\n"), 13, array('justification' => 'center'));

    $pdf->ezTable($data2, $cols, $titre_tab);
    $pdf->ezNewPage();
}

$pdf->setEncryption("$param", "$param", array('copy', 'print'));
//$pdf->ezInsertMode(1,1,'before');
$pdf->ezInsertMode(0);
$pdfG = "anonymat_" . getRewriteString($centre);

$title = "Anonymat des candidats ";
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

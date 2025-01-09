<?php
include_once 'generales.php';
$lien = lien();

//$tBddConf=getBddConf();
//mysql_connect($tBddConf['host'], $tBddConf['user'], $tBddConf['pass']) or die('Connection impossible<br/>'.mysql_error());
//mysql_select_db($tBddConf['bdd']) or die('Base inaccessible<br/>'.mysql_error());

foreach ($_REQUEST as $key => $val) {
    $$key = mysqli_real_escape_string($lien, trim(addslashes($val)));
}

if ($annee == '') {
    $annee = recupAnnee($lien); //$annee = date("Y");
}

if (isset($_GET['region'])) {
    $region = $_GET['region'];
}

/////$param = selectCodeAno($lien, $annee, $s);
//$centre = selectReferentiel($lien, 'etablissement', $c);

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
$pdf->ezStartPageNumbers(570, 15, 8, 'left', "$centre / $DatePied ");

$sql = "SELECT reg.region, cent.etablissement as centre,cod.jury, cod.code, count(*) as effectif
FROM bg_repartition rep, bg_ref_region reg, bg_ref_prefecture pref, bg_ref_ville vil,
bg_ref_etablissement eta, bg_ref_etablissement cent,bg_codes cod,
bg_ref_inspection insp, bg_ref_serie ser, bg_ref_eps spo, bg_candidats can
LEFT JOIN bg_ref_langue_vivante lang ON can.lv2=lang.id
LEFT JOIN bg_ref_epreuve_facultative_a ef1 ON can.efa=ef1.id
LEFT JOIN bg_ref_epreuve_facultative_b ef2 ON can.efb=ef2.id
WHERE can.annee=$annee AND rep.annee=$annee AND can.num_table=rep.num_table AND rep.id_centre=cent.id AND rep.jury=cod.jury
AND cent.id_ville=vil.id AND vil.id_prefecture=pref.id AND pref.id_region=reg.id
AND eta.id_inspection=insp.id AND can.serie=ser.id AND can.eps=spo.id
AND eta.id=can.etablissement
AND reg.id=$region
GROUP BY reg.region, cent.id, rep.jury, ser.serie
ORDER BY rep.jury";
$result = bg_query($lien, $sql, __FILE__, __LINE__);

$data = array();
$colonnes = array('region', 'centre', 'jury', 'code', 'effectif');
while ($row = mysqli_fetch_array($result)) {
    foreach ($colonnes as $col) {
        $$col = utf8_encode($row[$col]);
    }

    $data[$jury][] = array('centre' => $centre, 'jury' => $jury, 'code' => $code, 'effectif' => $effectif);
}

$cols = array('centre' => 'CENTRE', 'jury' => 'JURY', 'code' => 'CODE', 'effectif' => 'EFFECTIF');
//$titre_tab = $centre;
foreach ($data as $jury => $data2) {
    $pdf->ezSetY($height - 25);
    $pdf->addJpegFromFile("../images/logo.png", 275, 750, 45);
    $pdf->ezColumnsStart(array('num' => 3, 'gap' => 10));
    $pdf->ezText(utf8_decode("<b>MINISTERE DES ENSEIGNEMENTS PRIMAIRE,\nSECONDAIRE ET TECHNIQUE\n**********\nSECRETARIAT GENERAL\n**********\nDIRECTION DES EXAMENS, CONCOURS ET CERTIFICATIONS</b>"), 9, array('justification' => 'center'));
    $pdf->ezNewPage();
    $pdf->ezNewPage();
    $pdf->ezText(utf8_decode("<b>REPUBLIQUE TOGOLAISE\nTravail - Liberté - Patrie</b>"), 9, array('justification' => 'center'));
    $pdf->ezColumnsStop();

    $pdf->ezSetY($height - 120);
    $pdf->ezText(utf8_decode("<b>BACCALAUREAT PREMIERE PARTIE (BAC 1)</b>"), 11, array('justification' => 'center'));
    $mois = moisSession2($lien, $annee);
    $pdf->ezText(utf8_decode("<b>Session de $mois $annee\n</b>"), 10, array('justification' => 'center'));
    $pdf->ezText(utf8_decode("\n<b><u>Statistique des anonymats </u>\nRegion: $region</b>\n"), 13, array('justification' => 'center'));

    $pdf->ezTable($data2, $cols, $titre_tab);
    $pdf->ezNewPage();
}

//$pdf->setEncryption("$param", "$param", array('copy', 'print'));
//$pdf->ezInsertMode(1,1,'before');
$pdf->ezInsertMode(0);
$pdfG = "anonymats_" . getRewriteString($region);

$title = "Statistique des anonymats";
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

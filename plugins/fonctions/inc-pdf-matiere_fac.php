<?php
include 'generales.php';
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

//Recuperation de l'inspection
$id_inspection = $_GET['id_inspection'];

$sql11 = "SELECT * FROM bg_ref_inspection WHERE id = $id_inspection";
$result11 = bg_query($lien, $sql11, __FILE__, __LINE__);
$sol11 = mysqli_fetch_array($result11);
$inspection = ($sol11['inspection']);
//$id_inspection = 1;

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
$DateConvocPied = "Edition du " . date('d') . '/' . date('m') . '/' . date('Y');
$pdf->ezStartPageNumbers(570, 15, 9, 'left', "$DateConvocPied STATISTIQUES DES EPREUVES FACULTATIVES AU BACCALAUREAT PREMIERE PARTIE (BAC1) -  {PAGENUM} sur {TOTALPAGENUM}");

if ($type == 0) {
    $options = array(
        'spacing' => 1,
        'justification' => 'full',
        'titleFontSize' => 12,
        'fontSize' => 10,
        'maxWidth' => 815,
        'cols' => array(
            'dldn' => array('justification' => 'left', 'width' => 200),
        ),
    );
    $cols = array('centre' => 'CENTRES', 'Effectif' => utf8_decode('Effectif'), 'EPS' => 'EPS', 'Allemand' => 'Allemand', 'Arabe' => 'Arabe', 'Espagnol' => 'Espagnol', 'Ewe' => 'Ewe', 'Kabye' => 'Kabye', 'Russe' => 'Russe', 'Agri' => 'Agri', 'Dessin' => 'Dessin', 'EM' => 'EM', 'Musique' => 'Musique');
    $titre1 = 'STATISTIQUES DES EPREUVES FACULTATIVES AU BAC 1';
} else {
    $options = array('spacing' => 1, 'justification' => 'full', 'titleFontSize' => 12, 'fontSize' => 9, 'maxWidth' => 815);
    $cols = array('N°' => utf8_decode('N°ENR'), 'noms' => 'CANDIDAT', 'dldn' => 'DATE ET LIEU DE NAISSANCE', 'serie' => 'SERIE', 'annee1' => 'ANNEE BAC I ', 'jury1' => 'JURY BAC I ', 'table1' => utf8_decode('N°TABLE BAC I '), 'obs' => 'OBSERVATIONS');
    $order = " ORDER BY can.annee_bac1, can.jury_bac1, can.num_table_bac1";
    $titre1 = 'CONTROLE DU BAC I';
}

//if (isset($_GET['id_region'])) {
$pdf->ezSetY($height - 25);
$pdf->addJpegFromFile("../images/logo.png", 400, 500, 45);
$pdf->ezColumnsStart(array('num' => 3, 'gap' => 10));
$pdf->ezText(utf8_decode("<b>$ministere\n**********\n$secretariat_general\n**********\n$direction</b>"), 9, array('justification' => 'center'));
$pdf->ezNewPage();
$pdf->ezNewPage();
$pdf->ezText(utf8_decode("<b>REPUBLIQUE TOGOLAISE\nTravail - Liberté - Patrie</b>"), 9, array('justification' => 'center'));
$pdf->ezColumnsStop();

$pdf->ezSetY($height - 100);
$pdf->ezText(utf8_decode("<b>$libelle_examen </b>"), 14, array('justification' => 'center'));
$mois = moisSession2($lien, $annee);
$pdf->ezText(utf8_decode("<b>Session de $mois $annee\n</b>"), 10, array('justification' => 'center'));
$pdf->ezText(utf8_decode("<b>$titre1\n</b>"), 13, array('justification' => 'center'));
$pdf->ezText(utf8_decode("<b>INSPECTION: " . $inspection . " </b>"), 12, array('justification' => 'center'));

$sql = "SELECT centre,insp.inspection, etab.etablissement,
    COUNT(id_candidat) AS Effectif,
    COUNT(IF(eps=1,eps,NULL)) AS EPS,
    COUNT(IF(efa=1,efa,NULL)) AS Allemand,
    COUNT(IF(efa=5,efa,NULL)) AS Arabe,
    COUNT(IF(efa=2,efa,NULL)) AS Espagnol,
    COUNT(IF(efa=8,efa,NULL)) AS Ewe,
    COUNT(IF(efa=9,efa,NULL)) AS Kabye,
    COUNT(IF(efa=4,efa,NULL)) AS Russe,
    COUNT(IF(efb=9,efb,NULL)) AS Agri,
    COUNT(IF(efb=6,efb,NULL) OR IF(efa=6,efa,NULL)) AS Dessin,
    COUNT(IF(efb=8,efb,NULL)) AS EM,
    COUNT(IF(efb=7,efb,NULL) OR IF(efa=7,efa,NULL)) AS Musique
    FROM bg_candidats cand, bg_ref_etablissement etab, bg_ref_inspection insp
    WHERE cand.etablissement=etab.id AND etab.id_inspection=insp.id AND etab.id_inspection=$id_inspection
    GROUP BY centre";
$result = bg_query($lien, $sql, __FILE__, __LINE__);
$colonnes = array('centre', 'Effectif', 'EPS', 'Allemand', 'Arabe', 'Espagnol', 'Ewe', 'Kabye', 'Russe', 'Agri', 'Dessin', 'EM', 'Musique');

$i = 0;
while ($row = mysqli_fetch_array($result)) {
    foreach ($colonnes as $col) {
        $$col = $row[$col];
    }

    $i++;
    $data[$i] = array('serie' => $row['serie'],
        'centre' => centreEts($lien, $row['centre']), //.  $row['centre'],
        'Effectif' => $row['Effectif'],
        'EPS' => $row['EPS'],
        'Allemand' => $row['Allemand'],
        'Arabe' => $row['Arabe'],
        'Espagnol' => $row['Espagnol'],
        'Ewe' => $row['Ewe'],
        'Kabye' => $row['Kabye'],
        'Russe' => $row['Russe'],
        'Agri' => $row['Agri'],
        'Dessin' => $row['Dessin'],
        'EM' => $row['EM'],
        'Musique' => $row['Musique']);
}

$titre_tab = html_entity_decode(".");
$pdf->ezTable($data, $cols, $titre_tab, $options);
$pdf->ezText(utf8_decode(" Nombre de lignes: <b>$i</b>"), 10, array('justification' => 'left'));

//}

$pdfG = "STATISTIQUES_DES_EPREUVES_FACULTATIVES_" . $region;
$pdf->ezInsertMode(1, 1, 'before');
$pdf->ezInsertMode(0);

$title = "STATISTIQUES DES EPREUVES FACULTATIVES DU BAC 1 $annee";
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
    $pdf_fic = "pdf/" . $annee . "_$pdfG.pdf";
    $fp = fopen($pdf_fic, 'wb');
    fwrite($fp, $pdfcode);
    fclose($fp);

    echo "<A HREF='$pdf_fic'>Cliquez ici pour lire le fichier $pdf_fic</a>\n";
    echo "<script>document.location='$pdf_fic'</script>";
    session_destroy();
}

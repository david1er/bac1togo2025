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
$pdf->ezStartPageNumbers(15, 15, 8, 'right', "Inspection : $inspection");

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
    $cols = array('etablissement' => 'Centre', 'nbre' => utf8_decode('Effectif'));
    $titre1 = 'STATISTICS DES REJETS PAR INSPECTION';
} else {
    $options = array('spacing' => 1, 'justification' => 'full', 'titleFontSize' => 12, 'fontSize' => 9, 'maxWidth' => 815);
    $cols = array('N°' => utf8_decode('N°ENR'), 'noms' => 'CANDIDAT', 'dldn' => 'DATE ET LIEU DE NAISSANCE', 'serie' => 'SERIE', 'annee1' => 'ANNEE BAC I ', 'jury1' => 'JURY BAC I ', 'table1' => utf8_decode('N°TABLE BAC I '), 'obs' => 'OBSERVATIONS');
    $order = " ORDER BY can.annee_bac1, can.jury_bac1, can.num_table_bac1";
    $titre1 = 'CONTROLE DU BAC I';
}

$pdf->ezSetY($height - 25);
$pdf->addJpegFromFile("../images/logo.png", 275, 750, 45);
$pdf->ezColumnsStart(array('num' => 3, 'gap' => 10));
$pdf->ezText(utf8_decode("<b>$ministere\n**********\n$secretariat_general\n**********\n$direction</b>"), 9, array('justification' => 'center'));
$pdf->ezNewPage();
$pdf->ezNewPage();
$pdf->ezText(utf8_decode("<b>REPUBLIQUE TOGOLAISE\nTravail - Liberté - Patrie</b>"), 9, array('justification' => 'center'));
$pdf->ezColumnsStop();

$pdf->ezSetY($height - 100);
$pdf->ezText(utf8_decode("<b>\n\n$libelle_examen </b>"), 11, array('justification' => 'center'));
$mois = moisSession2($lien, $annee);
$pdf->ezText(utf8_decode("<b>Session de $mois $annee\n</b>"), 10, array('justification' => 'center'));

$pdf->ezText(utf8_decode("<b><u>Listes des centres comportant des Rejets</u>\nINSPECTION: $inspection</b>"), 13, array('justification' => 'center'));

$sql = "SELECT eta.etablissement, eta.id as id_centre, count(can.centre) as nbre
FROM bg_ref_etablissement eta, bg_candidats can
WHERE   eta.id_inspection=$id_inspection AND can.centre=eta.id AND can.statu=0
AND can.annee=$annee $andWhere
GROUP BY eta.id, can.centre ORDER BY eta.etablissement";
$result = bg_query($lien, $sql, __FILE__, __LINE__);
// $colonnes = array('centre', 'Effectif', 'EPS', 'Allemand', 'Arabe', 'Espagnol', 'Ewe', 'Kabye', 'Russe', 'Agri', 'Dessin', 'EM', 'Musique');
$colonnes = array('id_centre', 'etablissement', 'nbre');
$i = 0;
while ($row = mysqli_fetch_array($result)) {
    foreach ($colonnes as $col) {
        $$col = $row[$col];
    }

    $i++;
    $data[$i] = array(
        'etablissement' => $row['etablissement'],
        'nbre' => $row['nbre'],
    );
}

$titre_tab = html_entity_decode(".");
$pdf->ezTable($data, $cols, $titre_tab, $options);
$pdf->ezText(utf8_decode(" Nombre de lignes: <b>$i</b>"), 10, array('justification' => 'left'));

//}

$pdfG = "STATISTIQUES_DES_CENTRES_PAR_INSPECTION_" . $inspection;
$pdf->ezInsertMode(1, 1, 'before');
$pdf->ezInsertMode(0);

$title = "STATISTIQUES DES CENTRES PAR INSPECTION $annee";
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

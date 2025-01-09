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
$DateConvocPied = "IMPRIME LE " . date('d') . '/' . date('m') . '/' . date('Y');
$pdf->ezStartPageNumbers(50, 15, 7, 'right', "$DateConvocPied REPUBLIQUE TOGOLAISE - DIRECTION DES EXAMENS, CONCOURS ET CERTIFICATIONS - Page {PAGENUM} sur {TOTALPAGENUM}");

$options = array(
    'spacing' => 1,
    'justification' => 'full',
    'titleFontSize' => 14,
    'fontSize' => 12,
    'maxWidth' => 815,
);

$sql = "SELECT * FROM bg_ref_region ORDER BY region";
$result = bg_query($lien, $sql, __FILE__, __LINE__);
while ($row = mysqli_fetch_array($result)) {
    $id_region = $row['id'];
    $region = $row['region'];
    $data = array();
    $totSerie = array();
    $totGene = 0;

    $sql1 = "SELECT reg.id as id_region, pre.id as id_prefecture, eta.id as id_centre, eta.etablissement as centre, ser.id as id_serie, count(*) as nbre_cand
			FROM bg_ref_region reg, bg_ref_prefecture pre, bg_ref_ville vil, bg_ref_etablissement eta, bg_candidats can, bg_repartition rep, bg_ref_serie ser
			WHERE can.annee=$annee AND rep.annee=$annee AND can.serie=ser.id AND can.num_table=rep.num_table
			AND eta.id_ville=vil.id AND vil.id_prefecture=pre.id AND pre.id_region=reg.id AND rep.id_centre=eta.id
			AND reg.id=$id_region
			GROUP BY eta.id, ser.id
			ORDER BY reg.region, eta.etablissement, ser.serie ";

    $result1 = bg_query($lien, $sql1, __FILE__, __LINE__);
    $tab = array('id_centre', 'centre', 'id_serie', 'nbre_cand');
    while ($row1 = mysqli_fetch_array($result1)) {
        foreach ($tab as $val) {
            $$val = $row1[$val];
        }

        $data[$id_centre]['centre'] = $centre;
        $data[$id_centre][$id_serie] = $nbre_cand;
        $data[$id_centre]['total'] += $nbre_cand;
        $totSerie[$id_serie] += $nbre_cand;
        $totGene += $nbre_cand;
    }
    $data['fin']['centre'] = 'TOTAL';
    $data['fin']['total'] = $totGene;
    foreach ($totSerie as $id_serie => $nbre) {
        $data['fin'][$id_serie] = $nbre;
    }

    $cols = array('centre' => 'CENTRES');

    $sql2 = "SELECT id as id_serie, serie FROM bg_ref_serie ORDER BY serie";
    $result2 = bg_query($lien, $sql2, __FILE__, __LINE__);
    while ($row2 = mysqli_fetch_array($result2)) {
        $id_serie = $row2['id_serie'];
        $serie = $row2['serie'];
        $cols[$id_serie] = $serie;
    }
    $cols['total'] = 'TOTAL';

    $pdf->ezSetY($height - 25);
    $pdf->addJpegFromFile("../images/logo.png", 390, 520, 45);
    $pdf->ezColumnsStart(array('num' => 3, 'gap' => 10));
    $pdf->ezText(utf8_decode("<b>$ministere\n**********\n$secretariat_general\n**********\n$direction</b>"), 9, array('justification' => 'center'));
    $pdf->ezNewPage();
    $pdf->ezNewPage();
    $pdf->ezText(utf8_decode("<b>REPUBLIQUE TOGOLAISE\nTravail - Liberté - Patrie</b>"), 9, array('justification' => 'center'));
    $pdf->ezColumnsStop();

    $pdf->ezSetY($height - 100);
    $pdf->ezText(utf8_decode("<b>$libelle_examen</b>"), 14, array('justification' => 'center'));
    $mois = moisSession2($lien, $annee);
    $pdf->ezText(utf8_decode("<b>Session de $mois $annee\n</b>"), 10, array('justification' => 'center'));
    $pdf->ezText(utf8_decode("<b>$region</b>"), 14, array('justification' => 'center'));

    $titre_tab = utf8_decode("<b>STATISTIQUES SUR LES CENTRES DE COMPOSITION</b>");
    $pdf->ezTable($data, $cols, $titre_tab, $options);
    $pdf->ezNewPage();

}

$pdfG = "stats_repartition_" . $annee;
$pdf->ezInsertMode(1, 1, 'before');
$pdf->ezInsertMode(0);

$title = "Statistiques par centre pour le bac 1 _ $annee";
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

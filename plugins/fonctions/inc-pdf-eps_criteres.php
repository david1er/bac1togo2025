<?php
session_start();
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
$options = array('b' => 'Helvetica-Bold.afm', 'titleFontSize' => 14, 'fontSize' => 12);
$family = 'Helvetica';
$pdf->setFontFamily($family, $options);
$pdf->setStrokeColor(0, 0, 0);
$pdf->setLineStyle(1, 'round', 'round');
$width = $pdf->ez['pageWidth'];
$height = $pdf->ez['pageHeight'];
setlocale(LC_TIME, 'fr_FR', 'fr_BE.UTF8', 'fr_FR.UTF8');
$DateConvocPied = "IMPRIME LE " . date('d') . '/' . date('m') . '/' . date('Y');
$pdf->ezStartPageNumbers(50, 15, 7, 'right', "$DateConvocPied REPUBLIQUE TOGOLAISE - DIRECTION DES EXAMENS, CONCOURS ET CERTIFICATIONS - Page {PAGENUM} sur {TOTALPAGENUM}");

if ($id_atelier > 0) {

    $sql = "SELECT * FROM bg_performance WHERE id_atelier=" . $id_atelier . " ORDER BY note";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $tab = array('id_atelier', 'note', 'fille', 'garcon');
    while ($row = mysqli_fetch_array($result)) {
        foreach ($tab as $val) {
            $$val = html_entity_decode(utf8_decode($row[$val]));
        }

        $tabPer[$note]['f'] = $fille;
        $tabPer[$note]['g'] = $garcon;
        $tabPer[$note]['n'] = $note;
    }

    $pdf->ezSetY($height - 25);
    $pdf->addJpegFromFile("../images/logo.png", 270, 750, 45);
    $pdf->ezColumnsStart(array('num' => 3, 'gap' => 10));
    $pdf->ezText(utf8_decode("<b>$ministere\n**********\n$secretariat_general\n**********\n$direction</b>"), 9, array('justification' => 'center'));
    $pdf->ezNewPage();
    $pdf->ezNewPage();
    $pdf->ezText(utf8_decode("<b>REPUBLIQUE TOGOLAISE\nTravail - Liberté - Patrie</b>"), 9, array('justification' => 'center'));
    $pdf->ezColumnsStop();

    $pdf->ezSetY($height - 130);
    $pdf->ezText(utf8_decode("<b>$libelle_examen    </b>"), 13, array('justification' => 'center'));
    $mois = moisSession2($lien, $annee, $jury);
    $pdf->ezText(utf8_decode("<b>Session de $mois $annee\n</b>"), 10, array('justification' => 'center'));

    $atelier = selectReferentiel($lien, 'atelier', $id_atelier);
    $unite = getUniteAtelier($lien, $id_atelier);
    $pdf->ezText(utf8_decode("Atelier : <b>$atelier ($unite)</b>\n"), 12, array('justification' => 'center'));

    $cols = array('n' => 'NOTES', 'f' => 'FILLES', 'g' => 'GARCONS');
    $titre_tab = "CRITERES DE NOTATION ";
    $pdf->ezTable($tabPer, $cols, $titre_tab, $options);
    if ($id_atelier == 6) {
        $pdf->ezNewPage();
        $cols_p = array('NOTES', 'BAREME FILLES', 'SAISIE FILLES', 'BAREME GARCONS', 'SAISIE GARCONS');
        $tabConf[0][0] = "1";
        $tabConf[0][1] = "1m";
        $tabConf[0][2] = "21:1";
        $tabConf[0][3] = "2m";
        $tabConf[0][4] = "25:1";
        $tabConf[1][0] = "2";
        $tabConf[1][1] = "2m";
        $tabConf[1][2] = "20:2";
        $tabConf[1][3] = "3m";
        $tabConf[1][4] = "24:2";
        $tabConf[2][0] = "3";
        $tabConf[2][1] = "3m";
        $tabConf[2][2] = "19:3";
        $tabConf[2][3] = "5m";
        $tabConf[2][4] = "23:3";
        $tabConf[3][0] = "4";
        $tabConf[3][1] = "4m";
        $tabConf[3][2] = "18:4";
        $tabConf[3][3] = "8m";
        $tabConf[3][4] = "22:4";
        $tabConf[4][0] = "5";
        $tabConf[4][1] = "5m";
        $tabConf[4][2] = "17:5";
        $tabConf[4][3] = "10m";
        $tabConf[4][4] = "21:5";

        $pdf->ezText(utf8_decode("<b>QUELQUES PRECISIONS</b>"), 12, array('justification' => 'center'));
        $pdf->ezText(utf8_decode("<b>Pour qu'il y ai conformité entre les formats des données saisies les données ont été modifiés</b>"), 12, array('justification' => 'left'));
        $pdf->ezTable($tabConf, $cols_p, "PRECISIONS", $options);
    }

}

//$pdf->ezStartPageNumbers(10,20,8,'right',"REPUBLIQUE TOGOLAISE - OFFICE DU BACCALAUREAT - $DateConvocPied - PAGE {PAGENUM} sur {TOTALPAGENUM}");
$pdfG = "criteres_eps" . $id_atelier;

$pdf->ezInsertMode(1, 1, 'before');
$pdf->ezInsertMode(0);

$title = "criteres eps" . $atelier;
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

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

//Injection de controle du chef lieu des regions en fonction des jurys

$sql_chefLieu = "SELECT region_division, chef_lieu FROM bg_ref_region_division WHERE id_region = $id_region ";
$result_chefLieu = bg_query($lien, $sql_chefLieu, __FILE__, __LINE__);
$row_chefLieu = mysqli_fetch_array($result_chefLieu);

// affiche le chef_lieu
$chefLieu = $row_chefLieu['chef_lieu'];
//Affichage de la region avec l'id comme paramètre

function selectRegion($lien, $id_region)
{
    $sql = "SELECT region FROM bg_ref_region WHERE id='$id_region' ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $row = mysqli_fetch_array($result);
    return $row['region'];
}
$region = selectRegion($lien, $id_region);
if ($id_region == -1) {
    $region = 'NATIONAL';
    $chefLieu = 'Lomé';
}

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
$DateConvocPied = "Edition du " . date('d') . '/' . date('m') . '/' . date('Y').utf8_decode(" à $chefLieu");
$pdf->ezStartPageNumbers(470, 15, 9, 'left', "$DateConvocPied Tendances des " . utf8_decode("résultats") . " BACCALAUREAT PREMIERE PARTIE (BAC1) -  {PAGENUM} sur {TOTALPAGENUM}");

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
    $cols = array('serie' => 'SERIE', 'inscrit' => 'Inscrits', 'present' => utf8_decode('Présents'), 'Admis' => 'Admis', '9,00' => '9,00', '8.75' => '8.75', '8.5' => '8.5', '8.25' => '8.25', '8' => '8', '7.75' => '7.75', '7.5' => '7.5', '7.25' => '7.25', '7' => '7', '6.75' => '6.75', '6.5' => '6.5', '6-' => '6<', 'Abs' => 'Abs', 'Exclu' => 'Exclu');
    $titre1 = 'TENDANCES DES RESULTATS';
} else {
    $options = array('spacing' => 1, 'justification' => 'full', 'titleFontSize' => 12, 'fontSize' => 9, 'maxWidth' => 815);
    $cols = array('N°' => utf8_decode('N°ENR'), 'noms' => 'CANDIDAT', 'dldn' => 'DATE ET LIEU DE NAISSANCE', 'serie' => 'SERIE', 'annee1' => 'ANNEE BAC I ', 'jury1' => 'JURY BAC I ', 'table1' => utf8_decode('N°TABLE BAC I '), 'obs' => 'OBSERVATIONS');
    $order = " ORDER BY can.annee_bac1, can.jury_bac1, can.num_table_bac1";
    $titre1 = 'CONTROLE DU BAC I';
}

if (isset($_GET['id_region'])) {
    $pdf->ezSetY($height - 25);
    $pdf->addJpegFromFile("../images/logo.png", 400, 500, 45);
    $pdf->ezColumnsStart(array('num' => 3, 'gap' => 10));
    $pdf->ezText(utf8_decode("<b>$ministere\n**********\n$secretariat_general\n**********\n$direction</b>"), 9, array('justification' => 'center'));
    $pdf->ezNewPage();
    $pdf->ezNewPage();
    $pdf->ezText(utf8_decode("<b>REPUBLIQUE TOGOLAISE\nTravail - Liberté - Patrie</b>"), 9, array('justification' => 'center'));
    $pdf->ezColumnsStop();

    $pdf->ezSetY($height - 150);
    $pdf->ezText(utf8_decode("<b>$libelle_examen </b>"), 14, array('justification' => 'center'));
    $mois = moisSession2($lien, $annee);
    $pdf->ezText(utf8_decode("<b>Session de $mois $annee\n</b>"), 10, array('justification' => 'center'));
    $pdf->ezText(utf8_decode("<b>$titre1\n</b>"), 13, array('justification' => 'center'));
    $pdf->ezText(utf8_decode("<b>Région ou département: " . strtoupper($region) . " </b>"), 10, array('justification' => 'center'));
    $pdf->ezText(utf8_decode("Etat avec effectif cumulé - Effectif de calcul : Réel"), 10, array('justification' => 'center'));

    if ($id_region != -1) {
        $andWhere = " AND res.id_region=$id_region ";
    } else {
        $andWhere = '';
        $region = 'NATIONAL';
    }

    $sql = "SELECT ser.serie,
    COUNT(IF(moyenne>0,moyenne,NULL)) AS Present,
    COUNT(res.id_anonyme) AS 'Inscrit',
    COUNT(IF(moyenne>=10,moyenne,NULL)) AS Admis,
    COUNT(IF(moyenne>=9,moyenne,NULL)) AS '9,00',
    COUNT(IF(moyenne>=8.75,moyenne,NULL)) AS '8.75',
    COUNT(IF(moyenne>=8.5,moyenne,NULL)) AS '8.5',
    COUNT(IF(moyenne>=8.25,moyenne,NULL)) AS '8.25',
    COUNT(IF(moyenne>=8,moyenne,NULL)) AS '8',
    COUNT(IF(moyenne>=7.75,moyenne,NULL)) AS '7.75',
    COUNT(IF(moyenne>=7.5,moyenne,NULL)) AS '7.5',
    COUNT(IF(moyenne>=7.25,moyenne,NULL)) AS '7.25',
    COUNT(IF(moyenne>=7,moyenne,NULL)) AS '7',
    COUNT(IF(moyenne>=6.75,moyenne,NULL)) AS '6.75',
    COUNT(IF(moyenne>=6.5,moyenne,NULL)) AS '6.5',
    COUNT(IF(moyenne>6,moyenne,NULL)) AS '6-',
    COUNT(IF(moyenne<=0,moyenne,NULL)) AS Abs,
    COUNT(IF(delib1='Fraude',delib1,NULL)) AS Exclu
    FROM bg_resultats AS res
    JOIN bg_ref_region reg ON reg.id = res.id_region
    JOIN bg_ref_serie ser ON ser.id = res.id_serie
    $andWhere
    GROUP BY ser.serie
    UNION ALL
    SELECT 'TOTAL',
    COUNT(IF(moyenne>0,moyenne,NULL)) AS Present,
    COUNT(res.id_anonyme) AS 'Inscrit',
    COUNT(IF(moyenne>=10,moyenne,NULL)) AS Admis,
    COUNT(IF(moyenne>=9,moyenne,NULL)) AS '9,00',
    COUNT(IF(moyenne>=8.75,moyenne,NULL)) AS '8.75',
    COUNT(IF(moyenne>=8.5,moyenne,NULL)) AS '8.5',
    COUNT(IF(moyenne>=8.25,moyenne,NULL)) AS '8.25',
     COUNT(IF(moyenne>=8,moyenne,NULL)) AS '8',
    COUNT(IF(moyenne>=7.75,moyenne,NULL)) AS '7.75',
    COUNT(IF(moyenne>=7.5,moyenne,NULL)) AS '7.5',
    COUNT(IF(moyenne>=7.25,moyenne,NULL)) AS '7.25',
    COUNT(IF(moyenne>=7,moyenne,NULL)) AS '7',
    COUNT(IF(moyenne>=6.75,moyenne,NULL)) AS '6.75',
    COUNT(IF(moyenne>=6.5,moyenne,NULL)) AS '6.5',
    COUNT(IF(moyenne>6,moyenne,NULL)) AS '6-',
    COUNT(IF(moyenne<=0,moyenne,NULL)) AS Abs,
	COUNT(IF(delib1='Fraude',delib1,NULL)) AS Exclu
    FROM bg_resultats AS res
    JOIN bg_ref_region reg ON reg.id = res.id_region
    JOIN bg_ref_serie ser ON ser.id = res.id_serie
    $andWhere";

    // $sql = "SELECT ser.serie,
    // COUNT(IF(moyenne>0,moyenne,NULL)) AS Present,
    // COUNT(can.num_table) AS 'Inscrit',
    // COUNT(IF(moyenne>=10,moyenne,NULL)) AS Admis,
    // COUNT(IF(moyenne>=9,moyenne,NULL)) AS '9,00',
    // COUNT(IF(moyenne>=8.75,moyenne,NULL)) AS '8.75',
    // COUNT(IF(moyenne>=8.5,moyenne,NULL)) AS '8.5',
    // COUNT(IF(moyenne>=8.25,moyenne,NULL)) AS '8.25',
    // COUNT(IF(moyenne>=8,moyenne,NULL)) AS '8',
    // COUNT(IF(moyenne>=7.75,moyenne,NULL)) AS '7.75',
    // COUNT(IF(moyenne>=7.5,moyenne,NULL)) AS '7.5',
    // COUNT(IF(moyenne>=7.25,moyenne,NULL)) AS '7.25',
    // COUNT(IF(moyenne>=7,moyenne,NULL)) AS '7',
    // COUNT(IF(moyenne>=6.75,moyenne,NULL)) AS '6.75',
    // COUNT(IF(moyenne>=6.5,moyenne,NULL)) AS '6.5',
    // COUNT(IF(moyenne>6,moyenne,NULL)) AS '6-',
    // COUNT(IF(moyenne<=0,moyenne,NULL)) AS Abs,
    // COUNT(IF(delib1='Fraude',delib1,NULL)) AS Exclu
    // FROM bg_resultats as res
    // JOIN bg_repartition rep
    // on rep.id_anonyme=res.id_anonyme
    // JOIN bg_candidats can
    // on can.num_table=rep.num_table
	// JOIN bg_ref_etablissement etab
    // on etab.id=can.etablissement
    // JOIN bg_ref_region_division reg
    // on reg.id=etab.id_region_division
    // JOIN bg_ref_serie ser
    // on ser.id=can.serie $andWhere
    // GROUP BY can.serie
    // UNION ALL
    // SELECT 'TOTAL',
    // COUNT(IF(moyenne>0,moyenne,NULL)) AS Present,
    // COUNT(can.num_table) AS 'Inscrit',
    // COUNT(IF(moyenne>=10,moyenne,NULL)) AS Admis,
    // COUNT(IF(moyenne>=9,moyenne,NULL)) AS '9,00',
    // COUNT(IF(moyenne>=8.75,moyenne,NULL)) AS '8.75',
    // COUNT(IF(moyenne>=8.5,moyenne,NULL)) AS '8.5',
    // COUNT(IF(moyenne>=8.25,moyenne,NULL)) AS '8.25',
    //  COUNT(IF(moyenne>=8,moyenne,NULL)) AS '8',
    // COUNT(IF(moyenne>=7.75,moyenne,NULL)) AS '7.75',
    // COUNT(IF(moyenne>=7.5,moyenne,NULL)) AS '7.5',
    // COUNT(IF(moyenne>=7.25,moyenne,NULL)) AS '7.25',
    // COUNT(IF(moyenne>=7,moyenne,NULL)) AS '7',
    // COUNT(IF(moyenne>=6.75,moyenne,NULL)) AS '6.75',
    // COUNT(IF(moyenne>=6.5,moyenne,NULL)) AS '6.5',
    // COUNT(IF(moyenne>6,moyenne,NULL)) AS '6-',
    // COUNT(IF(moyenne<=0,moyenne,NULL)) AS Abs,
	// COUNT(IF(delib1='Fraude',delib1,NULL)) AS Exclu
    // FROM bg_resultats as res
    // JOIN bg_repartition rep
    // on rep.id_anonyme=res.id_anonyme
    // JOIN bg_candidats can
    // on can.num_table=rep.num_table
	// JOIN bg_ref_etablissement etab
    // on etab.id=can.etablissement
    // JOIN bg_ref_region_division reg
    // on reg.id=etab.id_region_division
    // JOIN bg_ref_serie ser
    // on ser.id=can.serie $andWhere";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $colonnes = array('serie',  'inscrit','present', 'Admis', '9,00', '8.75', '8.5', '8.25', '8', '7.75', '7.5', '7.25', '7', '6.75', '6.5', '6<', 'Abs', 'Exclu');

    $i = 0;
    while ($row = mysqli_fetch_array($result)) {
        /* foreach($colonnes as $col) $$col=utf8_decode(html_entity_decode(($row[$col]))); */
        foreach ($colonnes as $col) {
            $$col = $row[$col];
        }

        $i++;
        $data[$i] = array('serie' => $row['serie'],
            'inscrit' => $row['Inscrit'],
            'present' => $row['Present'],
            'Admis' => $row['Admis']. "\n" . "<b>" . number_format((($row['Admis'] * 100) / $row['Present']), 2, ',', '') . '%</b>',
            '9,00' => $row['9,00'] . "\n" . "<b>" . number_format((($row['9,00'] * 100) / $row['Present']), 2, ',', '') . '%</b>',
            '8.75' => $row['8.75'] . "\n" . "<b>" . number_format((($row['8.75'] * 100) / $row['Present']), 2, ',', '') . '%</b>',
            '8.5' => $row['8.5'] . "\n" . "<b>" . number_format((($row['8.5'] * 100) / $row['Present']), 2, ',', '') . '%</b>',
            '8.25' => $row['8.25'] . "\n" . "<b>" . number_format((($row['8.25'] * 100) / $row['Present']), 2, ',', '') . '%</b>',
            '8' => $row['8'] . "\n" . "<b>" . number_format((($row['8'] * 100) / $row['Present']), 2, ',', '') . '%</b>',
            '7.75' => $row['7.75'] . "\n" . "<b>" . number_format((($row['7.75'] * 100) / $row['Present']), 2, ',', '') . '%</b>',
            '7.5' => $row['7.5'] . "\n" . "<b>" . number_format((($row['7.5'] * 100) / $row['Present']), 2, ',', '') . '%</b>',
            '7.25' => $row['7.25'] . "\n" . "<b>" . number_format((($row['7.25'] * 100) / $row['Present']), 2, ',', '') . '%</b>',
            '7' => $row['7'] . "\n" . "<b>" . number_format((($row['7'] * 100) / $row['Present']), 2, ',', '') . '%</b>',
            '6.75' => $row['6.75'] . "\n" . "<b>" . number_format((($row['6.75'] * 100) / $row['Present']), 2, ',', '') . '%</b>',
            '6.5' => $row['6.5'] . "\n" . "<b>" . number_format((($row['6.5'] * 100) / $row['Present']), 2, ',', '') . '%</b>',
            '6-' => $row['6-'] . "\n" . "<b>" . number_format((($row['6-'] * 100) / $row['Present']), 2, ',', '') . '%</b>',
            'Abs' => $row['Abs'],
            'Exclu' => $row['Exclu']);
    }

    $titre_tab = html_entity_decode(".");
    $pdf->ezTable($data, $cols, $titre_tab, $options);
    $pdf->ezText(utf8_decode(" Nombre de lignes: <b>$i</b>"), 10, array('justification' => 'left'));

}

$pdfG = "tendances_des_resultats_" . $region;
$pdf->ezInsertMode(1, 1, 'before');
$pdf->ezInsertMode(0);

$title = utf8_decode("Tendances des résultats pour le bac $annee");
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

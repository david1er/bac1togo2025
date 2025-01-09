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
$pdf->ezStartPageNumbers(570, 15, 8, 'left', "$DateConvocPied Tendances des " . utf8_decode("résultats") . " BACCALAUREAT PREMIERE PARTIE (BAC1) -  {PAGENUM} sur {TOTALPAGENUM}");

if ($type == 0) {
    $options = array(
        'spacing' => 1,
        'justification' => 'full',
        'titleFontSize' => 12,
        'fontSize' => 8.5,
        'maxWidth' => 800, 
        /* 'cols' => array(
            'serie' => array('justification' => 'center', 'width' => 20),
            'present' => array('justification' => 'center', 'width' => 25),
            'present F' => array('justification' => 'center', 'width' => 25),
            'present G' => array('justification' => 'center', 'width' => 25),
            'inscrit' => array('justification' => 'center', 'width' => 25),
            'inscrit F' => array('justification' => 'center', 'width' => 25),
            'inscrit G' => array('justification' => 'center', 'width' => 25),
        ), */
    );
    $cols = array('serie' => 'SERIE', 'present' => utf8_decode('Présents'), 'present F' => utf8_decode('Présents F'), 'present G' => utf8_decode('Présents G'), 'inscrit' => 'Inscrits T','inscrit F' => 'Inscrits F','inscrit G' => 'Inscrits G', 'Admis' => 'Admis','Admis F' => 'Admis F','Admis G' => 'Admis G',
        '9,00' => '9,00','9,00/ F' => '9,00 | F','9,00/ G' => '9,00 | G',
        '8.75' => '8.75','8.75/ F' => '8.75 | F','8.75/ G' => '8.75 | G',
        '8.5' => '8.5','8.5/ F' => '8.5 | F','8.5/ G' => '8.5 | G',
        '8.25' => '8.25','8.25/ F' => '8.25 | F','8.25/ G' => '8.25 | G',
        '8' => '8','8/ F' => '8 | F','8/ G' => '8 | G',
        '7.75' => '7.75','7.75/ F' => '7.75 | F','7.75/ G' => '7.75 | G',
        '7.5' => '7.5','7.5/ F' => '7.5 | F','7.5/ G' => '7.5 | G',
        '7.25' => '7.25','7.25/ F' => '7.25 | F','7.25/ G' => '7.25 | G',
        '7' => '7','7/ F' => '7 | F','7/ G' => '7 | G',
        '6.75' => '6.75','6.75/ F' => '6.75 | F','6.75/ G' => '6.75 | G',
        '6.5' => '6.5','6.5/ F' => '6.5 | F','6.5/ G' => '6.5 | G',
        '6-' => '6<','6-/ F' => '6- | F','6-/ G' => '6- | G',
        'Abs' => 'Abs','Abs F' => 'Abs | F','Abs G' => 'Abs | G', 
        'Exclu' => 'Exclu','Exclu F' => 'Exclu | F','Exclu G' => 'Exclu | G');
    $titre1 = 'TENDANCES DES RESULTATS SELON LE SEXE';
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

    $pdf->ezSetY($height - 100);
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
    COUNT(IF(moyenne>0 AND can.sexe=1,moyenne,NULL)) AS 'Present F',
    COUNT(IF(moyenne>0 AND can.sexe=2,moyenne,NULL)) AS 'Present G',
    COUNT(can.num_table) AS 'Inscrit T',
    COUNT(IF(can.sexe=1,can.sexe,NULL)) AS 'Inscrit F',
    COUNT(IF(can.sexe=2,can.sexe,NULL)) AS 'Inscrit G',
    COUNT(IF(moyenne>=10,moyenne,NULL)) AS Admis,
    COUNT(IF(moyenne>=10 AND can.sexe=1,moyenne,NULL)) AS 'Admis F',
    COUNT(IF(moyenne>=10 AND can.sexe=2,moyenne,NULL)) AS 'Admis G',
    COUNT(IF(moyenne>=9,moyenne,NULL)) AS '9,00',
    COUNT(IF(moyenne>=9 AND can.sexe=1,moyenne,NULL)) AS '9,00/ F',
    COUNT(IF(moyenne>=9 AND can.sexe=2,moyenne,NULL)) AS '9,00/ G',
    COUNT(IF(moyenne>=8.75,moyenne,NULL)) AS '8.75',
    COUNT(IF(moyenne>=8.75 AND can.sexe=1,moyenne,NULL)) AS '8.75/ F',
    COUNT(IF(moyenne>=8.75 AND can.sexe=2,moyenne,NULL)) AS '8.75/ G',
    COUNT(IF(moyenne>=8.5,moyenne,NULL)) AS '8.5',
    COUNT(IF(moyenne>=8.5 AND can.sexe=1,moyenne,NULL)) AS '8.5/ F',
    COUNT(IF(moyenne>=8.5 AND can.sexe=2,moyenne,NULL)) AS '8.5/ G',
    COUNT(IF(moyenne>=8.25,moyenne,NULL)) AS '8.25',
    COUNT(IF(moyenne>=8.25 AND can.sexe=1,moyenne,NULL)) AS '8.25/ F',
    COUNT(IF(moyenne>=8.25 AND can.sexe=2,moyenne,NULL)) AS '8.25/ G',
    COUNT(IF(moyenne>=8,moyenne,NULL)) AS '8',
    COUNT(IF(moyenne>=8 AND can.sexe=1,moyenne,NULL)) AS '8/ F',
    COUNT(IF(moyenne>=8 AND can.sexe=2,moyenne,NULL)) AS '8/ G',
    COUNT(IF(moyenne>=7.75,moyenne,NULL)) AS '7.75',
    COUNT(IF(moyenne>=7.75 AND can.sexe=1,moyenne,NULL)) AS '7.75/ F',
    COUNT(IF(moyenne>=7.75 AND can.sexe=2,moyenne,NULL)) AS '7.75/ G',
    COUNT(IF(moyenne>=7.5,moyenne,NULL)) AS '7.5',
    COUNT(IF(moyenne>=7.5 AND can.sexe=1,moyenne,NULL)) AS '7.5/ F',
    COUNT(IF(moyenne>=7.5 AND can.sexe=2,moyenne,NULL)) AS '7.5/ G',
    COUNT(IF(moyenne>=7.25,moyenne,NULL)) AS '7.25',
    COUNT(IF(moyenne>=7.25 AND can.sexe=1,moyenne,NULL)) AS '7.25/ F',
    COUNT(IF(moyenne>=7.25 AND can.sexe=2,moyenne,NULL)) AS '7.25/ G',
    COUNT(IF(moyenne>=7,moyenne,NULL)) AS '7',
    COUNT(IF(moyenne>=7 AND can.sexe=1,moyenne,NULL)) AS '7/ F',
    COUNT(IF(moyenne>=7 AND can.sexe=2,moyenne,NULL)) AS '7/ G',
    COUNT(IF(moyenne>=6.75,moyenne,NULL)) AS '6.75',
    COUNT(IF(moyenne>=6.75 AND can.sexe=1,moyenne,NULL)) AS '6.75/ F',
    COUNT(IF(moyenne>=6.75 AND can.sexe=2,moyenne,NULL)) AS '6.75/ G',
    COUNT(IF(moyenne>=6.5,moyenne,NULL)) AS '6.5',
    COUNT(IF(moyenne>=6.5 AND can.sexe=1,moyenne,NULL)) AS '6.5/ F',
    COUNT(IF(moyenne>=6.5 AND can.sexe=2,moyenne,NULL)) AS '6.5/ G',
    COUNT(IF(moyenne>6,moyenne,NULL)) AS '6-',
    COUNT(IF(moyenne>=6 AND can.sexe=1,moyenne,NULL)) AS '6-/ F',
    COUNT(IF(moyenne>=6 AND can.sexe=2,moyenne,NULL)) AS '6-/ G',
    COUNT(IF(moyenne<=0,moyenne,NULL)) AS Abs,
    COUNT(IF(moyenne<=0 AND can.sexe=1,moyenne,NULL)) AS 'Abs F',
    COUNT(IF(moyenne<=0 AND can.sexe=2,moyenne,NULL)) AS 'Abs G',
    COUNT(IF(delib1='Fraude',delib1,NULL)) AS Exclu,
    COUNT(IF(delib1='Fraude' AND can.sexe=1,delib1,NULL)) AS 'Exclu F',
    COUNT(IF(delib1='Fraude' AND can.sexe=2,delib1,NULL)) AS 'Exclu G'
    FROM bg_resultats AS res
            JOIN bg_ref_region reg ON reg.id = res.id_region
            JOIN bg_candidats can ON can.num_table = res.nt
            JOIN bg_ref_serie ser ON ser.id = res.id_serie $andWhere
    GROUP BY can.serie
    UNION ALL
    SELECT 'TOTAL',
    COUNT(IF(moyenne>0,moyenne,NULL)) AS Present,
    COUNT(IF(moyenne>0 AND can.sexe=1,moyenne,NULL)) AS 'Present F',
    COUNT(IF(moyenne>0 AND can.sexe=2,moyenne,NULL)) AS 'Present G',
    COUNT(can.num_table) AS 'Inscrit T',
    COUNT(IF(can.sexe=1,can.sexe,NULL)) AS 'Inscrit F',
    COUNT(IF(can.sexe=2,can.sexe,NULL)) AS 'Inscrit G',
    COUNT(IF(moyenne>=10,moyenne,NULL)) AS Admis,
    COUNT(IF(moyenne>=10 AND can.sexe=1,moyenne,NULL)) AS 'Admis F',
    COUNT(IF(moyenne>=10 AND can.sexe=2,moyenne,NULL)) AS 'Admis G',
    COUNT(IF(moyenne>=9,moyenne,NULL)) AS '9,00',
    COUNT(IF(moyenne>=9 AND can.sexe=1,moyenne,NULL)) AS '9,00/ F',
    COUNT(IF(moyenne>=9 AND can.sexe=2,moyenne,NULL)) AS '9,00/ G',
    COUNT(IF(moyenne>=8.75,moyenne,NULL)) AS '8.75',
    COUNT(IF(moyenne>=8.75 AND can.sexe=1,moyenne,NULL)) AS '8.75/ F',
    COUNT(IF(moyenne>=8.75 AND can.sexe=2,moyenne,NULL)) AS '8.75/ G',
    COUNT(IF(moyenne>=8.5,moyenne,NULL)) AS '8.5',
    COUNT(IF(moyenne>=8.5 AND can.sexe=1,moyenne,NULL)) AS '8.5/ F',
    COUNT(IF(moyenne>=8.5 AND can.sexe=2,moyenne,NULL)) AS '8.5/ G',
    COUNT(IF(moyenne>=8.25,moyenne,NULL)) AS '8.25',
    COUNT(IF(moyenne>=8.25 AND can.sexe=1,moyenne,NULL)) AS '8.25/ F',
    COUNT(IF(moyenne>=8.25 AND can.sexe=2,moyenne,NULL)) AS '8.25/ G',
    COUNT(IF(moyenne>=8,moyenne,NULL)) AS '8',
    COUNT(IF(moyenne>=8 AND can.sexe=1,moyenne,NULL)) AS '8/ F',
    COUNT(IF(moyenne>=8 AND can.sexe=2,moyenne,NULL)) AS '8/ G',
    COUNT(IF(moyenne>=7.75,moyenne,NULL)) AS '7.75',
    COUNT(IF(moyenne>=7.75 AND can.sexe=1,moyenne,NULL)) AS '7.75/ F',
    COUNT(IF(moyenne>=7.75 AND can.sexe=2,moyenne,NULL)) AS '7.75/ G',
    COUNT(IF(moyenne>=7.5,moyenne,NULL)) AS '7.5',
    COUNT(IF(moyenne>=7.5 AND can.sexe=1,moyenne,NULL)) AS '7.5/ F',
    COUNT(IF(moyenne>=7.5 AND can.sexe=2,moyenne,NULL)) AS '7.5/ G',
    COUNT(IF(moyenne>=7.25,moyenne,NULL)) AS '7.25',
    COUNT(IF(moyenne>=7.25 AND can.sexe=1,moyenne,NULL)) AS '7.25/ F',
    COUNT(IF(moyenne>=7.25 AND can.sexe=2,moyenne,NULL)) AS '7.25/ G',
    COUNT(IF(moyenne>=7,moyenne,NULL)) AS '7',
    COUNT(IF(moyenne>=7 AND can.sexe=1,moyenne,NULL)) AS '7/ F',
    COUNT(IF(moyenne>=7 AND can.sexe=2,moyenne,NULL)) AS '7/ G',
    COUNT(IF(moyenne>=6.75,moyenne,NULL)) AS '6.75',
    COUNT(IF(moyenne>=6.75 AND can.sexe=1,moyenne,NULL)) AS '6.75/ F',
    COUNT(IF(moyenne>=6.75 AND can.sexe=2,moyenne,NULL)) AS '6.75/ G',
    COUNT(IF(moyenne>=6.5,moyenne,NULL)) AS '6.5',
    COUNT(IF(moyenne>=6.5 AND can.sexe=1,moyenne,NULL)) AS '6.5/ F',
    COUNT(IF(moyenne>=6.5 AND can.sexe=2,moyenne,NULL)) AS '6.5/ G',
    COUNT(IF(moyenne>6,moyenne,NULL)) AS '6-',
    COUNT(IF(moyenne>=6 AND can.sexe=1,moyenne,NULL)) AS '6-/ F',
    COUNT(IF(moyenne>=6 AND can.sexe=2,moyenne,NULL)) AS '6-/ G',
    COUNT(IF(moyenne<=0,moyenne,NULL)) AS Abs,
    COUNT(IF(moyenne<=0 AND can.sexe=1,moyenne,NULL)) AS 'Abs F',
    COUNT(IF(moyenne<=0 AND can.sexe=2,moyenne,NULL)) AS 'Abs G',
    COUNT(IF(delib1='Fraude',delib1,NULL)) AS Exclu,
    COUNT(IF(delib1='Fraude' AND can.sexe=1,delib1,NULL)) AS 'Exclu F',
    COUNT(IF(delib1='Fraude' AND can.sexe=2,delib1,NULL)) AS 'Exclu G'
    FROM bg_resultats AS res
            JOIN bg_ref_region reg ON reg.id = res.id_region
            JOIN bg_candidats can ON can.num_table = res.nt
            JOIN bg_ref_serie ser ON ser.id = res.id_serie $andWhere";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $colonnes = array('serie', 'present','present F','present G', 'inscrit T','inscrit F','inscrit G', 'Admis','Admis F','Admis G',
        '9,00','9,00 | F','9,00 | G', '8.75','8.75 | F','8.75 | G', '8.5','8.5 | F','8.5 | G', '8.25','8.25 | F','8.25 | G', '8','8 | F','8 | G',
        '7.75','7.75 | F','7.75 | G', '7.5','7.5 | F','7.5 | G', '7.25','7.25 | F','7.25 | G', '7','7 | F','7 | G', 
        '6.75','6.75 | F','6.75 | G', '6.5','6.5 | F','6.5 | G', '6<','6< | F','6< | G ',
         'Abs','Abs F','Abs G', 'Exclu','Exclu F','Exclu G');

    $i = 0;
    while ($row = mysqli_fetch_array($result)) {
        //$data[] = $row;
        /* foreach($colonnes as $col) $$col=utf8_decode(html_entity_decode(($row[$col]))); */
        foreach ($colonnes as $col) {
            $$col = $row[$col];
            //var_dump($$col);
        }

        $i++;

         // 1ere methode(horizontal)
        $data1[$i] = array(utf8_decode('Série') => $row['serie'],
            'Inscrit G' => $row['Inscrit G'],
            'Inscrit F' => $row['Inscrit F'],
            'Inscrit T' => $row['Inscrit T'], 
            utf8_decode('Présent G') => $row['Present G'],
            utf8_decode('Présent F') => $row['Present F'],
            utf8_decode('Présent T') => $row['Present'],
            'Admis G' => $row['Admis G'] . "\n" . "<b>" . number_format((($row['Admis G'] * 100) / $row['Present G']), 2, ',', '') . '%</b>',
            'Admis F' => $row['Admis F'] . "\n" . "<b>" . number_format((($row['Admis F'] * 100) / $row['Present F']), 2, ',', '') . '%</b>',
            'Admis T' => $row['Admis'] . "\n" . "<b>" . number_format((($row['Admis'] * 100) / $row['Present']), 2, ',', '') . '%</b>',
            '9,00/ G' => $row['9,00/ G'] . "\n" . "<b>" . number_format((($row['9,00/ G'] * 100) / $row['Present G']), 2, ',', '') . '%</b>',
            '9,00/ F' => $row['9,00/ F'] . "\n" . "<b>" . number_format((($row['9,00/ F'] * 100) / $row['Present F']), 2, ',', '') . '%</b>',
            '9,00 T' => $row['9,00'] . "\n" . "<b>" . number_format((($row['9,00'] * 100) / $row['Present']), 2, ',', '') . '%</b>',
            '8,75/ G' => $row['8.75/ G'] . "\n" . "<b>" . number_format((($row['8.75/ G'] * 100) / $row['Present G']), 2, ',', '') . '%</b>',
            '8,75/ F' => $row['8.75/ F'] . "\n" . "<b>" . number_format((($row['8.75/ F'] * 100) / $row['Present F']), 2, ',', '') . '%</b>',
            '8,75 T' => $row['8.75'] . "\n" . "<b>" . number_format((($row['8.75'] * 100) / $row['Present']), 2, ',', '') . '%</b>',
            '8,5/ G' => $row['8.5/ G'] . "\n" . "<b>" . number_format((($row['8.5/ G'] * 100) / $row['Present G']), 2, ',', '') . '%</b>',
            '8,5/ F' => $row['8.5/ F'] . "\n" . "<b>" . number_format((($row['8.5/ F'] * 100) / $row['Present F']), 2, ',', '') . '%</b>',
            '8,5 T' => $row['8.5'] . "\n" . "<b>" . number_format((($row['8.5'] * 100) / $row['Present']), 2, ',', '') . '%</b>',
             );
    $data2[$i] = array( utf8_decode('Série') => $row['serie'],
            '8,25/ G' => $row['8.25/ G'] . "\n" . "<b>" . number_format((($row['8.25/ G'] * 100) / $row['Present G']), 2, ',', '') . '%</b>',
            '8,25/ F' => $row['8.25/ F'] . "\n" . "<b>" . number_format((($row['8.25/ F'] * 100) / $row['Present F']), 2, ',', '') . '%</b>',
            '8,25 T' => $row['8.25'] . "\n" . "<b>" . number_format((($row['8.25'] * 100) / $row['Present']), 2, ',', '') . '%</b>',
            '8/ G' => $row['8/ G'] . "\n" . "<b>" . number_format((($row['8/ G'] * 100) / $row['Present G']), 2, ',', '') . '%</b>',
            '8/ F' => $row['8/ F'] . "\n" . "<b>" . number_format((($row['8/ F'] * 100) / $row['Present F']), 2, ',', '') . '%</b>',
            '8 T' => $row['8'] . "\n" . "<b>" . number_format((($row['8'] * 100) / $row['Present']), 2, ',', '') . '%</b>',
            '7,75/ G' => $row['7.75/ G'] . "\n" . "<b>" . number_format((($row['7.75/ G'] * 100) / $row['Present G']), 2, ',', '') . '%</b>',
            '7,75/ F' => $row['7.75/ F'] . "\n" . "<b>" . number_format((($row['7.75/ F'] * 100) / $row['Present F']), 2, ',', '') . '%</b>',
            '7,75 T' => $row['7.75'] . "\n" . "<b>" . number_format((($row['7.75'] * 100) / $row['Present']), 2, ',', '') . '%</b>',
            '7,5/ G' => $row['7.5/ G'] . "\n" . "<b>" . number_format((($row['7.5/ G'] * 100) / $row['Present G']), 2, ',', '') . '%</b>',
            '7,5/ F' => $row['7.5/ F'] . "\n" . "<b>" . number_format((($row['7.5/ F'] * 100) / $row['Present F']), 2, ',', '') . '%</b>',
            '7,5 T' => $row['7.5'] . "\n" . "<b>" . number_format((($row['7.5'] * 100) / $row['Present']), 2, ',', '') . '%</b>',
            '7,25/ G' => $row['7.25/ G'] . "\n" . "<b>" . number_format((($row['7.25/ G'] * 100) / $row['Present G']), 2, ',', '') . '%</b>',
            '7,25/ F' => $row['7.25/ F'] . "\n" . "<b>" . number_format((($row['7.25/ F'] * 100) / $row['Present F']), 2, ',', '') . '%</b>',
            '7,25 T' => $row['7.25'] . "\n" . "<b>" . number_format((($row['7.25'] * 100) / $row['Present']), 2, ',', '') . '%</b>',
            '7/ G' => $row['7/ G'] . "\n" . "<b>" . number_format((($row['7/ G'] * 100) / $row['Present G']), 2, ',', '') . '%</b>',
            '7/ F' => $row['7/ F'] . "\n" . "<b>" . number_format((($row['7/ F'] * 100) / $row['Present F']), 2, ',', '') . '%</b>',
            '7 T' => $row['7'] . "\n" . "<b>" . number_format((($row['7'] * 100) / $row['Present']), 2, ',', '') . '%</b>',
             );
        $data3[$i] = array(
            utf8_decode('Série') => $row['serie'],
            '6,75/ G' => $row['6.75/ G'] . "\n" . "<b>" . number_format((($row['6.75/ G'] * 100) / $row['Present G']), 2, ',', '') . '%</b>',
            '6,75/ F' => $row['6.75/ F'] . "\n" . "<b>" . number_format((($row['6.75/ F'] * 100) / $row['Present F']), 2, ',', '') . '%</b>',
            '6,75 T' => $row['6.75'] . "\n" . "<b>" . number_format((($row['6.75'] * 100) / $row['Present']), 2, ',', '') . '%</b>',
            '6,5/ G' => $row['6.5/ G'] . "\n" . "<b>" . number_format((($row['6.5/ G'] * 100) / $row['Present G']), 2, ',', '') . '%</b>',
            '6,5/ F' => $row['6.5/ F'] . "\n" . "<b>" . number_format((($row['6.5/ F'] * 100) / $row['Present F']), 2, ',', '') . '%</b>',
            '6,5 T' => $row['6.5'] . "\n" . "<b>" . number_format((($row['6.5'] * 100) / $row['Present']), 2, ',', '') . '%</b>',
            '>=6/ G' => $row['6-/ G'] . "\n" . "<b>" . number_format((($row['6-/ G'] * 100) / $row['Present G']), 2, ',', '') . '%</b>',
            '>=6/ F' => $row['6-/ F'] . "\n" . "<b>" . number_format((($row['6-/ F'] * 100) / $row['Present F']), 2, ',', '') . '%</b>',
            '>=6 T' => $row['6-'] . "\n" . "<b>" . number_format((($row['6-'] * 100) / $row['Present']), 2, ',', '') . '%</b>',
            'Abs G' => $row['Abs G'],
            'Abs F' => $row['Abs F'],
            'Abs T' => $row['Abs'],
            'Exclu G' => $row['Exclu G'],
            'Exclu F' => $row['Exclu F'],
            'Exclu T' => $row['Exclu'],
            );

        //2ieme methode(verticale) 
        /* $data['serie'][] = $row['serie'];
            $data['present'][] = $row['Present'];
            $data['present F'][] = $row['Present F'];
            $data['present G'][] = $row['Present G'];
            $data['inscrit'][] = $row['Inscrit T'];
            $data['inscrit F'][] = $row['Inscrit F'];
            $data['inscrit G'][] = $row['Inscrit G'];
            $data['Admis'][] = $row['Admis'];
            $data['Admis F'][] = $row['Admis F'];
            $data['Admis G'][] = $row['Admis G'];
            $data['9,00'][] = $row['9,00'] . "\n" . "<b>" . number_format((($row['9,00'] * 100) / $row['Present']), 2, ',', '') . '%</b>';
            $data['9,00/ F'][] = $row['9,00/ F'] . "\n" . "<b>" . number_format((($row['9,00/ F'] * 100) / $row['Present F']), 2, ',', '') . '%</b>';
            $data['9,00/ G'][] = $row['9,00/ G'] . "\n" . "<b>" . number_format((($row['9,00/ G'] * 100) / $row['Present G']), 2, ',', '') . '%</b>';
            $data['8.75'][] = $row['8.75'] . "\n" . "<b>" . number_format((($row['8.75'] * 100) / $row['Present']), 2, ',', '') . '%</b>';
            $data['8.75/ F'][] = $row['8.75/ F'] . "\n" . "<b>" . number_format((($row['8.75/ F'] * 100) / $row['Present F']), 2, ',', '') . '%</b>';
            $data['8.75/ G'][] = $row['8.75/ G'] . "\n" . "<b>" . number_format((($row['8.75/ G'] * 100) / $row['Present G']), 2, ',', '') . '%</b>';
            $data['8.5'][] = $row['8.5'] . "\n" . "<b>" . number_format((($row['8.5'] * 100) / $row['Present']), 2, ',', '') . '%</b>';
            $data['8.5/ F'][] = $row['8.5/ F'] . "\n" . "<b>" . number_format((($row['8.5/ F'] * 100) / $row['Present F']), 2, ',', '') . '%</b>';
            $data['8.5/ G'][] = $row['8.5/ G'] . "\n" . "<b>" . number_format((($row['8.5/ G'] * 100) / $row['Present G']), 2, ',', '') . '%</b>';
            $data['8.25'][] = $row['8.25'] . "\n" . "<b>" . number_format((($row['8.25'] * 100) / $row['Present']), 2, ',', '') . '%</b>';
            $data['8.25/ F'][] = $row['8.25/ F'] . "\n" . "<b>" . number_format((($row['8.25/ F'] * 100) / $row['Present F']), 2, ',', '') . '%</b>';
            $data['8.25/ G'][] = $row['8.25/ G'] . "\n" . "<b>" . number_format((($row['8.25/ G'] * 100) / $row['Present G']), 2, ',', '') . '%</b>';
            $data['8'][] = $row['8'] . "\n" . "<b>" . number_format((($row['8'] * 100) / $row['Present']), 2, ',', '') . '%</b>';
            $data['8/ F'][] = $row['8/ F'] . "\n" . "<b>" . number_format((($row['8/ F'] * 100) / $row['Present F']), 2, ',', '') . '%</b>';
            $data['8/ G'][] = $row['8/ G'] . "\n" . "<b>" . number_format((($row['8/ G'] * 100) / $row['Present G']), 2, ',', '') . '%</b>';
            $data['7.75'][] = $row['7.75'] . "\n" . "<b>" . number_format((($row['7.75'] * 100) / $row['Present']), 2, ',', '') . '%</b>';
            $data['7.75/ F'][] = $row['7.75/ F'] . "\n" . "<b>" . number_format((($row['7.75/ F'] * 100) / $row['Present F']), 2, ',', '') . '%</b>';
            $data['7.75/ G'][] = $row['7.75/ G'] . "\n" . "<b>" . number_format((($row['7.75/ G'] * 100) / $row['Present G']), 2, ',', '') . '%</b>';
            $data['7.5'][] = $row['7.5'] . "\n" . "<b>" . number_format((($row['7.5'] * 100) / $row['Present']), 2, ',', '') . '%</b>';
            $data['7.5/ F'][] = $row['7.5/ F'] . "\n" . "<b>" . number_format((($row['7.5/ F'] * 100) / $row['Present F']), 2, ',', '') . '%</b>';
            $data['7.5/ G'][] = $row['7.5/ G'] . "\n" . "<b>" . number_format((($row['7.5/ G'] * 100) / $row['Present G']), 2, ',', '') . '%</b>';
            $data['7.25'][] = $row['7.25'] . "\n" . "<b>" . number_format((($row['7.25'] * 100) / $row['Present']), 2, ',', '') . '%</b>';
            $data['7.25/ F'][] = $row['7.25/ F'] . "\n" . "<b>" . number_format((($row['7.25/ F'] * 100) / $row['Present F']), 2, ',', '') . '%</b>';
            $data['7.25/ G'][] = $row['7.25/ G'] . "\n" . "<b>" . number_format((($row['7.25/ G'] * 100) / $row['Present G']), 2, ',', '') . '%</b>';
            $data['7'][] = $row['7'] . "\n" . "<b>" . number_format((($row['7'] * 100) / $row['Present']), 2, ',', '') . '%</b>';
            $data['7/ F'][] = $row['7/ F'] . "\n" . "<b>" . number_format((($row['7/ F'] * 100) / $row['Present F']), 2, ',', '') . '%</b>';
            $data['7/ G'][] = $row['7/ G'] . "\n" . "<b>" . number_format((($row['7/ G'] * 100) / $row['Present G']), 2, ',', '') . '%</b>';
            $data['6.75'][] = $row['6.75'] . "\n" . "<b>" . number_format((($row['6.75'] * 100) / $row['Present']), 2, ',', '') . '%</b>';
            $data['6.75/ F'][] = $row['6.75/ F'] . "\n" . "<b>" . number_format((($row['6.75/ F'] * 100) / $row['Present F']), 2, ',', '') . '%</b>';
            $data['6.75/ G'][] = $row['6.75/ G'] . "\n" . "<b>" . number_format((($row['6.75/ G'] * 100) / $row['Present G']), 2, ',', '') . '%</b>';
            $data['6.5'][] = $row['6.5'] . "\n" . "<b>" . number_format((($row['6.5'] * 100) / $row['Present']), 2, ',', '') . '%</b>';
            $data['6.5/ F'][] = $row['6.5/ F'] . "\n" . "<b>" . number_format((($row['6.5/ F'] * 100) / $row['Present F']), 2, ',', '') . '%</b>';
            $data['6.5/ G'][] = $row['6.5/ G'] . "\n" . "<b>" . number_format((($row['6.5/ G'] * 100) / $row['Present G']), 2, ',', '') . '%</b>';
            $data['6-'][] = $row['6-'] . "\n" . "<b>" . number_format((($row['6-'] * 100) / $row['Present']), 2, ',', '') . '%</b>';
            $data['6-/ F'][] = $row['6-/ F'] . "\n" . "<b>" . number_format((($row['6-/ F'] * 100) / $row['Present F']), 2, ',', '') . '%</b>';
            $data['6-/ G'][] = $row['6-/ G'] . "\n" . "<b>" . number_format((($row['6-/ G'] * 100) / $row['Present G']), 2, ',', '') . '%</b>';
            $data['Abs'][] = $row['Abs'];
            $data['Abs F'][] = $row['Abs F'];
            $data['Abs G'][] = $row['Abs G'];
            $data['Exclu'][] = $row['Exclu'];
            $data['Exclu F'][] = $row['Exclu F'];
            $data['Exclu G'][] = $row['Exclu G']; */

    }


    $titre_tab = html_entity_decode(".");
    $pdf->ezSetDy(-20); // Ajuster la position verticale
    $pdf->ezTable($data1,null, utf8_decode('Présent<=Moyenne<=8,5'),$options);
    //  Déplacer vers la droite pour créer de l'espace pour le deuxième tableau
    $pdf->ezSetDy(-20); // Ajuster la position verticale
    $pdf->ezTable($data2, null, utf8_decode('8,25<=Moyenne<=7'), $options);
    //  Déplacer vers la droite pour créer de l'espace pour le deuxième tableau
    $pdf->ezSetDy(-30); // Ajuster la position verticale
    $pdf->ezTable($data3, null, utf8_decode('6,75<=Moyenne<=Exclu'), $options);
    //$pdf->ezTable($data, $cols, $titre_tab, $options);
    //$pdf->ezTable($data);
    $pdf->ezText(utf8_decode(" Nombre de lignes: <b>$i</b>"), 10, array('justification' => 'left'));
    $pdf->ezStream();
}

$pdfG = "tendances_des_resultats_par_sexe_" . $region;
$pdf->ezInsertMode(1, 1, 'before');
$pdf->ezInsertMode(0);

$title = "Tendances des résultats pour le bac $annee";
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


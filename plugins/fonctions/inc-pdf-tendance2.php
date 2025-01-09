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
$sDateConvoc = utf8_decode(strftime("%A %d %B %Y", mktime(0, 0, 0, date('m'), date('d'), date('Y'))));
$DateConvocPied = "IMPRIME LE " . date('d') . '/' . date('m') . '/' . date('Y');
$pdf->ezStartPageNumbers(100, 15, 9, 'right', "REPUBLIQUE TOGOLAISE - DIRECTION DES EXAMENS, CONCOURS ET CERTIFICATIONS - $DateConvocPied - PAGE {PAGENUM} sur {TOTALPAGENUM}");
//$pdf->ezStartPageNumbers(570, 15, 8, 'left', "$DateConvocPied - Page {PAGENUM} sur {TOTALPAGENUM}");

$pdf->ezSetY($height - 25);
$pdf->addJpegFromFile("../images/logo.png", 390, 500, 45);
$pdf->ezColumnsStart(array('num' => 3, 'gap' => 10));
$pdf->ezText(utf8_decode("<b>MINISTERE DES ENSEIGNEMENTS PRIMAIRE,\nSECONDAIRE ET TECHNIQUE\n**********\nSECRETARIAT GENERAL\n**********\nDIRECTION DES EXAMENS, CONCOURS ET CERTIFICATIONS</b>"), 9, array('justification' => 'center'));
$pdf->ezNewPage();
$pdf->ezNewPage();
$pdf->ezText(utf8_decode("<b>REPUBLIQUE TOGOLAISE\nTravail - Liberté - Patrie</b>"), 9, array('justification' => 'center'));
$pdf->ezColumnsStop();

//Injection de controle du chef lieu des regions en fonction des jurys
$region = $_GET['region'];

if ($region == "GRAND-LOME") {
    $jury1 = 1;
    $jury2 = 299;
} else if ($region == "MARITIME") {
    $jury1 = 300;
    $jury2 = 499;
    // $chefLieu = "Tsevié";
} else if ($region == "PLATEAUX-EST") {
    $jury1 = 500;
    $jury2 = 599;
    //$chefLieu = "Atakpamé";
} else if ($region == "PLATEAUX-OUEST") {
    $jury1 = 600;
    $jury2 = 699;
    // $chefLieu = "Kpalimé";
} else if ($region == "CENTRALE") {
    $jury1 = 700;
    $jury2 = 799;
    // $chefLieu = "Sokodé";
} else if ($region == "KARA") {
    $jury1 = 800;
    $jury2 = 899;
    // $chefLieu = "Kara";
} else if ($region == "SAVANE") {
    $jury1 = 900;
    $jury2 = 999;
    // $chefLieu = "Dapaong";
} else if ($region == "NATIONAL") {
    $jury1 = 1;
    $jury2 = 1000;
    // $chefLieu = "Lomé";
}

$sql2 = "SELECT res.num_table, res.id_anonyme, res.total, res.moyenne, res.moyenne2, res.delib1, res.delib2 \n
			FROM bg_resultats res, bg_repartition rep, bg_candidats can \n
			WHERE res.annee=$annee AND res.id_anonyme=rep.id_anonyme AND rep.num_table=can.num_table AND can.serie=1
			AND res.jury BETWEEN $jury1 AND $jury2 AND (delib1='Absent' OR delib2='Abandon')
			/* UNION ALL
			SELECT res.num_table, res.id_anonyme, res.total, res.moyenne, res.moyenne2, res.delib1, res.delib2 \n
			FROM bg_resultats res, bg_repartition rep, bg_candidats can \n
			WHERE res.annee=$annee AND res.id_anonyme=rep.id_anonyme AND rep.num_table=can.num_table AND can.serie=2
			AND res.jury BETWEEN $jury1 AND $jury2 AND (delib1='Absent' OR delib2='Abandon')
			UNION ALL
			SELECT res.num_table, res.id_anonyme, res.total, res.moyenne, res.moyenne2, res.delib1, res.delib2 \n
			FROM bg_resultats res, bg_repartition rep, bg_candidats can \n
			WHERE res.annee=$annee AND res.id_anonyme=rep.id_anonyme AND rep.num_table=can.num_table AND can.serie=3
			AND res.jury BETWEEN $jury1 AND $jury2 AND (delib1='Absent' OR delib2='Abandon') */
			";

/* $sql2="SELECT num_table, id_anonyme, total, moyenne, moyenne2, delib1, delib2 \n
FROM bg_resultats \n
WHERE annee=$annee AND jury BETWEEN $jury1 AND $jury2 AND (delib1='Absent' OR delib2='Abandon')"; */
$result2 = bg_query($lien, $sql2, __FILE__, __LINE__);
$absents = mysqli_num_rows($result2);

$sql3 = "SELECT res.num_table, res.id_anonyme, res.total, res.moyenne, res.moyenne2, res.delib1, res.delib2 \n
			FROM bg_resultats res, bg_repartition rep, bg_candidats can \n
			WHERE res.annee=$annee AND res.id_anonyme=rep.id_anonyme AND rep.num_table=can.num_table AND can.serie=1
			AND res.jury BETWEEN $jury1 AND $jury2
			/* UNION ALL
			SELECT res.num_table, res.id_anonyme, res.total, res.moyenne, res.moyenne2, res.delib1, res.delib2 \n
			FROM bg_resultats res, bg_repartition rep, bg_candidats can \n
			WHERE res.annee=$annee AND res.id_anonyme=rep.id_anonyme AND rep.num_table=can.num_table AND can.serie=2
			AND res.jury BETWEEN $jury1 AND $jury2
			UNION ALL
			SELECT res.num_table, res.id_anonyme, res.total, res.moyenne, res.moyenne2, res.delib1, res.delib2 \n
			FROM bg_resultats res, bg_repartition rep, bg_candidats can \n
			WHERE res.annee=$annee AND res.id_anonyme=rep.id_anonyme AND rep.num_table=can.num_table AND can.serie=3
			AND res.jury BETWEEN $jury1 AND $jury2 */
			 ";
//  requete original tendance
$sqloffreStat = "SELECT ser.serie,
	COUNT(IF(moyenne>0,moyenne,NULL)) AS Present,
	COUNT(IF(moyenne>=10,moyenne,NULL)) AS Admis,
	COUNT(IF(moyenne>=9,moyenne,NULL)) AS '9,00',
	COUNT(IF(moyenne>=8.75,moyenne,NULL)) AS '8.75',
	COUNT(IF(moyenne>=8.5,moyenne,NULL)) AS '8.5',
	COUNT(IF(moyenne>=8.25,moyenne,NULL)) AS '8.25',
	COUNT(IF(moyenne>=8,moyenne,NULL)) AS '8',
	COUNT(IF(moyenne>8,moyenne,NULL)) AS '8-',
	COUNT(IF(moyenne<=0,moyenne,NULL)) AS Abs
	FROM bg_resultats as res
	JOIN bg_candidats can
	on can.num_table=res.num_table
	JOIN bg_ref_serie ser
	on ser.id=can.serie
	GROUP BY can.serie
	UNION ALL
	SELECT 'TOTAL',
	COUNT(IF(moyenne>0,moyenne,NULL)) AS Present,
	COUNT(IF(moyenne>=10,moyenne,NULL)) AS Admis,
	COUNT(IF(moyenne>=9,moyenne,NULL)) AS '9,00',
	COUNT(IF(moyenne>=8.75,moyenne,NULL)) AS '8.75',
	COUNT(IF(moyenne>=8.5,moyenne,NULL)) AS '8.5',
	COUNT(IF(moyenne>=8.25,moyenne,NULL)) AS '8.25',
	COUNT(IF(moyenne>=8,moyenne,NULL)) AS '8',
	COUNT(IF(moyenne>8,moyenne,NULL)) AS '8-',
	COUNT(IF(moyenne<=0,moyenne,NULL)) AS Abs
	FROM bg_resultats as res
	JOIN bg_candidats can
	on can.num_table=res.num_table
	JOIN bg_ref_serie ser
	on ser.id=can.serie";

$result3 = bg_query($lien, $sql3, __FILE__, __LINE__);
$effectif = mysqli_num_rows($result3);
$tab3 = array('num_table', 'id_anonyme', 'total', 'moyenne', 'moyenne2', 'delib1', 'delib2');
while ($row3 = mysqli_fetch_array($result3)) {
    foreach ($tab3 as $val) {
        $$val = html_entity_decode($row3[$val]);
    }

    $tMoyStats[$id_anonyme] = $moyenne;
}

foreach ($tMoyStats as $moyenne) {
    $effectif_total++;
    if ($moyenne < 8) {
        $moy_0_8++;
    } elseif ($moyenne >= 8 && $moyenne < 8.25) {
        $moy_8_825++;
    } elseif ($moyenne >= 8.25 && $moyenne < 8.5) {
        $moy_825_850++;
    } elseif ($moyenne >= 8.5 && $moyenne < 8.75) {
        $moy_85_875++;
    } elseif ($moyenne >= 8.75 && $moyenne < 9) {
        $moy_875_9++;
    } elseif ($moyenne >= 9 && $moyenne < 10) {
        $moy_9_10++;
    } elseif ($moyenne >= 10 && $moyenne < 12) {
        $moy_10_12++;
    } elseif ($moyenne >= 12 && $moyenne < 14) {
        $moy_12_14++;
    } elseif ($moyenne >= 14 && $moyenne < 16) {
        $moy_14_16++;
    } elseif ($moyenne >= 16 && $moyenne < 18) {
        $moy_16_18++;
    } elseif ($moyenne >= 18 && $moyenne <= 20) {
        $moy_18_20++;
    }

    if ($moyenne >= 9) {
        $moy_9++;
    }

}

$moy_18_20_2 = $moy_18_20;
$moy_16_18_2 = $moy_16_18 + $moy_18_20_2;
$moy_14_16_2 = $moy_16_18_2 + $moy_14_16;
$moy_12_14_2 = $moy_14_16_2 + $moy_12_14;
$moy_10_12_2 = $moy_12_14_2 + $moy_10_12;
$moy_9_10_2 = $moy_10_12_2 + $moy_9_10;
$moy_875_9_2 = $moy_9_10_2 + $moy_875_9;
$moy_85_875_2 = $moy_875_9_2 + $moy_85_875;
$moy_825_850_2 = $moy_85_875_2 + $moy_825_850;
$moy_8_825_2 = $moy_825_850_2 + $moy_8_825;
$moy_0_8_2 = $moy_8_825_2 + $moy_0_8;

/* $data2[12]['intervalle']='18 <= Moyenne <= 20';
$data2[12]['effectif']=$moy_18_20_2;
$data2[12]['taux']=round(($moy_18_20_2 * 100)/$effectif_total,2,PHP_ROUND_HALF_EVEN) . '%';

$data2[11]['intervalle']='16 <= Moyenne < 18';
$data2[11]['effectif']=$moy_16_18_2;
$data2[11]['taux']=round(($moy_16_18_2 * 100)/$effectif_total,2,PHP_ROUND_HALF_EVEN) . '%';

$data2[10]['intervalle']='14 <= Moyenne < 16';
$data2[10]['effectif']=$moy_14_16_2;
$data2[10]['taux']=round(($moy_14_16_2 * 100)/$effectif_total,2,PHP_ROUND_HALF_EVEN) . '%';

$data2[9]['intervalle']='12 <= Moyenne < 14';
$data2[9]['effectif']=$moy_12_14_2;
$data2[9]['taux']=round(($moy_12_14 * 100)/$effectif_total,2,PHP_ROUND_HALF_EVEN) . '%';

$data2[8]['intervalle']='10 <= Moyenne < 12';
$data2[8]['effectif']=$moy_10_12_2;
$data2[8]['taux']=round(($moy_10_12_2 * 100)/$effectif_total,2,PHP_ROUND_HALF_EVEN) . '%'; */

echo "<table border='1'>

<tr>
<th>intervalle</th>
<th>effectif</th>
<th>taux</th>
</tr>";

echo "<tr>";
echo "<td>" . $row3['intervalle'] . "</td>";
echo "<td>" . $row3['effectif'] . "</td>";
echo "<td>" . $row3['taux'] . "</td>";
echo "</tr>";

echo "</table>";

$data2[6]['intervalle'] = '>= 9';
$data2[6]['effectif'] = $moy_9_10_2 . "\n" . round(($moy_9_10_2 * 100) / $effectif_total, 2, PHP_ROUND_HALF_EVEN) . '%';
$data2[6]['taux'] = round(($moy_9_10_2 * 100) / $effectif_total, 2, PHP_ROUND_HALF_EVEN) . '%';

$data2[5]['intervalle'] = '>=8,75';
$data2[5]['effectif'] = $moy_875_9_2 . "\n" . round(($moy_875_9_2 * 100) / $effectif_total, 2, PHP_ROUND_HALF_EVEN) . '%';
$data2[5]['taux'] = round(($moy_875_9_2 * 100) / $effectif_total, 2, PHP_ROUND_HALF_EVEN) . '%';

$data2[4]['intervalle'] = '>=8,5 ';
$data2[4]['effectif'] = $moy_85_875_2 . "\n" . round(($moy_85_875_2 * 100) / $effectif_total, 2, PHP_ROUND_HALF_EVEN) . '%';
$data2[4]['taux'] = round(($moy_85_875_2 * 100) / $effectif_total, 2, PHP_ROUND_HALF_EVEN) . '%';

$data2[3]['intervalle'] = '>=8,25';
$data2[3]['effectif'] = $moy_825_850_2 . "\n" . round(($moy_825_850_2 * 100) / $effectif_total, 2, PHP_ROUND_HALF_EVEN) . '%';
$data2[3]['taux'] = round(($moy_825_850_2 * 100) / $effectif_total, 2, PHP_ROUND_HALF_EVEN) . '%';

$data2[2]['intervalle'] = '>=8';
$data2[2]['effectif'] = $moy_8_825_2 . "\n" . round(($moy_8_825_2 * 100) / $effectif_total, 2, PHP_ROUND_HALF_EVEN) . '%';
$data2[2]['taux'] = round(($moy_8_825_2 * 100) / $effectif_total, 2, PHP_ROUND_HALF_EVEN) . '%';

$data2[1]['intervalle'] = '0 <= Moyenne < 8';
$data2[1]['effectif'] = $moy_0_8_2 . "\n" . round(($moy_0_8_2 * 100) / $effectif_total, 2, PHP_ROUND_HALF_EVEN) . '%';
$data2[1]['taux'] = round(($moy_0_8_2 * 100) / $effectif_total, 2, PHP_ROUND_HALF_EVEN) . '%';

$data2[13]['intervalle'] = 'Presents';
$data2[13]['effectif'] = $effectif;
$data2[13]['taux'] = '';

$data2[14]['intervalle'] = 'Absents ou Abandons';
$data2[14]['effectif'] = $absents;
$data2[14]['taux'] = '';

$taux = round(($moy_9 * 100) / $effectif_total, 2, PHP_ROUND_HALF_EVEN);

$pdf->ezSetY($height - 25);
//    $pdf->addJpegFromFile("../images/logo.png",565,750,45);
$pdf->ezColumnsStart(array('num' => 3, 'gap' => 10));
$pdf->ezText(utf8_decode("<b>MINISTERE DES ENSEIGNEMENTS PRIMAIRE,\nSECONDAIRE ET TECHNIQUE\n**********\nSECRETARIAT GENERAL\n**********\nDIRECTION DES EXAMENS, CONCOURS ET CERTIFICATIONS</b>"), 9, array('justification' => 'center'));
$pdf->ezNewPage();
$pdf->ezNewPage();
$pdf->ezText(utf8_decode("<b>REPUBLIQUE TOGOLAISE\nTravail - Liberté - Patrie</b>"), 9, array('justification' => 'center'));
$pdf->ezColumnsStop();

$pdf->ezSetY($height - 100);
$pdf->ezText(utf8_decode("<b>BACCALAUREAT PREMIERE PARTIE (BAC 1)</b>"), 13, array('justification' => 'center'));
$mois = moisSession($lien, $annee, $jury);
$pdf->ezText(utf8_decode("<b>Session de $mois $annee\n</b>"), 10, array('justification' => 'center'));

$pdf->ezText(utf8_decode("\n<b><u>TENDANCE: $region </u></b>\n"), 18, array('justification' => 'center'));
$pdf->ezText(utf8_decode("<b>SERIE A4</b>\n"), 13, array('justification' => 'center'));
$pdf->ezText(utf8_decode("<b>TAUX D'ADMISSIBILITE = $moy_9 / $effectif_total SOIT $taux </b>\n"), 13, array('justification' => 'center'));
$entete2 = array(
    'serie' => utf8_decode("Série"),
    'present' => utf8_decode("Présents"),
    'inscrit' => utf8_decode("Inscrit"),
    'Admis' => "Admis",
    'intervalle' => "INTERVALLES DE MOYENNE",
    'effectif' => "EFFECTIF",
    'taux' => "TAUX",
);
//    $titre_tab2=html_entity_decode("JURY $jury - SERIE $serie - ANNEE $annee \n - TAUX D'ADMISSIBILITE = $moy_9 / $effectif_total SOIT $taux %");
$pdf->ezTable($data2, $entete2, $titre_tab2, $options2);

/*
echo "<pre>";
print_r($entete);
echo "</pre>";
 */

$pdfG = "grands_releves_jury" . $jury . '_serie' . $serie;
$pdf->ezInsertMode(1, 1, 'before');
$pdf->ezInsertMode(0);

$title = "TENDANCES jury" . $jury . '_serie' . $serie . '_annee' . $annee;
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

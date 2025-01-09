<?php
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

//Recuperation des parametres
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
$DateConvocPied = "IMPRIME LE " . date('d') . '/' . date('m') . '/' . date('Y');
$pdf->ezStartPageNumbers(570, 15, 8, 'left', "$DateConvocPied REPUBLIQUE TOGOLAISE - $direction - Page {PAGENUM} sur {TOTALPAGENUM}");
//$pdf->ezStartPageNumbers(50,15,7,'right',"$DateConvocPied REPUBLIQUE TOGOLAISE - OFFICE DU BACCALAUREAT - Page {PAGENUM} sur {TOTALPAGENUM}");

$options = array(
    'spacing' => 1.5,
    'justification' => 'full',
    'titleFontSize' => 16,
    'fontSize' => 11,
);

if (isset($_GET['annee'])) {
    if ($id_type_session != 0) {
        $andWhere .= " AND can.type_session=$id_type_session ";
    }

    if ($id_region != 0) {
        $andWhere .= " AND reg.id=$id_region ";
    }

    if ($id_prefecture != 0) {
        $andWhere .= " AND pref.id=$id_prefecture ";
    }

    if ($id_ville != 0) {
        $andWhere .= " AND vil.id=$id_ville ";
    }

    if ($id_inspection != 0) {
        $andWhere .= " AND insp.id=$id_inspection ";
    }

    if ($id_serie != 0) {
        $andWhere .= " AND ser.id=$id_serie ";
    }

    if ($id_sexe != 0) {
        $andWhere .= " AND can.sexe=$id_sexe ";
    }

    if ($id_eps != 0) {
        $andWhere .= " AND spo.id=$id_eps ";
    }

    if ($id_langue_vivante != 0) {
        $andWhere .= " AND lang.id=$id_langue_vivante ";
    }

    if ($id_centre != 0) {
        $andWhere .= " AND cent.id=$id_centre ";
    }

    if ($id_etablissement != 0) {
        $andWhere .= " AND eta.id=$id_etablissement ";
    }

    if ($jury != 0) {
        $andWhere .= " AND rep.jury=$jury ";
    }

    $pdf->ezSetY($height - 25);
//    $pdf->addJpegFromFile("../images/logo.png",575,750,45);
    //    $pdf->addJpegFromFile("../images/logo.png",390,520,45);
    $pdf->addJpegFromFile("../images/logo.png", 270, 760, 45);
    $pdf->ezColumnsStart(array('num' => 3, 'gap' => 10));
    $pdf->ezText(utf8_decode("<b>$ministere\n**********\n$secretariat_general\n**********\n$direction</b>"), 9, array('justification' => 'center'));
    $pdf->ezNewPage();
    $pdf->ezNewPage();
    $pdf->ezText(utf8_decode("<b>REPUBLIQUE TOGOLAISE\nTravail - Liberté - Patrie</b>"), 9, array('justification' => 'center'));
    $pdf->ezColumnsStop();

    $pdf->ezSetY($height - 140);
    $pdf->ezText(utf8_decode("<b>$libelle_examen</b>"), 14, array('justification' => 'center'));
    $mois = moisSession2($lien, $annee);
    $pdf->ezText(utf8_decode("<b>Session de $mois $annee\n</b>"), 10, array('justification' => 'center'));

    $sql = "SELECT eta.etablissement, delib1, count(*) as nbre
		FROM bg_ref_region reg,
		bg_ref_prefecture pre,
		bg_ref_ville vil,
		bg_ref_serie ser,
		bg_ref_inspection insp,
		bg_ref_etablissement eta,
		bg_ref_etablissement cent,
		bg_ref_eps spo,
		bg_resultats res,
		bg_repartition rep,
		bg_candidats can
		LEFT JOIN bg_ref_langue_vivante lang ON lang.id=can.lv2
		WHERE rep.num_table=can.num_table AND rep.annee=$annee AND can.annee=$annee
		AND can.serie=ser.id AND eta.id_ville=vil.id AND res.annee=$annee AND res.num_table=can.num_table
		AND vil.id_prefecture=pre.id AND pre.id_region=reg.id AND can.etablissement=eta.id AND rep.id_centre=cent.id
		AND insp.id=eta.id_inspection AND spo.id=can.eps
		$andWhere
		GROUP BY eta.id, delib1
		ORDER BY eta.etablissement ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $tab = array('etablissement', 'delib1', 'nbre');

    $cols = array('eta' => 'ETABLISSEMENTS', 'ins' => 'INSCRITS', 'pre' => 'PRESENTS', 'ad' => 'ADMIS', 'tx' => 'TAUX');
    while ($row = mysqli_fetch_array($result)) {
        foreach ($tab as $val) {
            $$val = $row[$val];
        }

        $tNbre[$etablissement][$delib1] = $nbre;
    }

    foreach ($tNbre as $etablissement => $tDelib1) {
        foreach ($tDelib1 as $delib1 => $nb) {
            $inscrits += $nb;
            if ($delib1 != 'Abandon' && $delib1 != 'Absent') {
                $presents += $nb;
            }

            if ($delib1 != 'Abandon' && $delib1 != 'Absent' && $delib1 != 'Ajourne') {
                $admis += $nb;
            }

        }
        $taux = number_format((($admis * 100) / $presents), 2, ',', '') . '%';
        $data[] = array('eta' => utf8_decode($etablissement), 'ins' => $inscrits, 'pre' => intval($presents), 'ad' => intval($admis), 'tx' => $taux);
        $tot_inscrits += $inscrits;
        $tot_presents += $presents;
        $tot_admis += $admis;
        $inscrits = $presents = $admis = 0;
    }
    $tot_taux = number_format((($tot_admis * 100) / $tot_presents), 2, ',', '') . '%';
    $data[] = array('eta' => 'TOTAL', 'ins' => $tot_inscrits, 'pre' => $tot_presents, 'ad' => $tot_admis, 'tx' => $tot_taux);

    $titre_tab = utf8_decode(html_entity_decode("<b>TAUX DE REUSSITE</b>"));
    $pdf->ezTable($data, $cols, $titre_tab, $options);
}

$pdfG = "taux_etablissement" . $annee;
$pdf->ezInsertMode(1, 1, 'before');
$pdf->ezInsertMode(0);

$title = "taux_etablissement" . $annee;
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

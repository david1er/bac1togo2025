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
    'titleFontSize' => 14,
    'fontSize' => 10,
    'maxWidth' => 545,
);

if (isset($_GET['annee'])) {
    if ($id_type_session != 0) {
        $andWhere .= " AND can.type_session=$id_type_session ";
    }

    if ($id_region != 0) {
        $andWhere .= " AND reg.id=$id_region ";
        $andTitre .= ' || ' . selectReferentiel($lien, 'region', $id_region);
    }
    if ($id_prefecture != 0) {
        $andWhere .= " AND pref.id=$id_prefecture ";
        $andTitre .= ' || ' . selectReferentiel($lien, 'prefecture', $id_prefecture);
    }
    if ($id_ville != 0) {
        $andWhere .= " AND vil.id=$id_ville ";
        $andTitre .= ' || ' . selectReferentiel($lien, 'ville', $id_ville);
    }
    if ($id_inspection != 0) {
        $andWhere .= " AND insp.id=$id_inspection ";
        $andTitre .= ' || ' . selectReferentiel($lien, 'inspection', $id_inspection);
    }
    if ($id_serie != 0) {
        $andWhere .= " AND ser.id=$id_serie ";
        $andTitre .= ' || Serie: ' . selectReferentiel($lien, 'serie', $id_serie);
    }
    if ($id_sexe != 0) {
        $andWhere .= " AND can.sexe=$id_sexe ";
        $andTitre .= ' || Sexe: ' . selectReferentiel($lien, 'sexe', $id_sexe);
    }
    if ($id_eps != 0) {
        $andWhere .= " AND spo.id=$id_eps ";
        $andTitre .= ' || ' . selectReferentiel($lien, 'eps', $id_eps);
    }
    if ($id_langue_vivante != 0) {
        $andWhere .= " AND lang.id=$id_langue_vivante ";
        $andTitre .= ' || LV: ' . selectReferentiel($lien, 'langue_vivante', $id_langue_vivante);
    }
    if ($id_fac != 0) {
        $andWhere .= " AND (faca.id=$id_fac OR facb.id=$id_fac) ";
        if ($id_fac != 8) {
            $andTitre .= ' || EF: ' . selectReferentiel($lien, 'epreuve_facultative_a', $id_fac);
        } elseif ($id_fac == 8) {
            $andTitre .= ' || EF: ' . selectReferentiel($lien, 'epreuve_facultative_b', $id_fac);
        }

    }
    if ($id_centre != 0) {
        $andWhere .= " AND cent.id=$id_centre ";
        $andTitre .= ' || Centre: ' . selectReferentiel($lien, 'etablissement', $id_centre);
    }
    if ($id_etablissement != 0) {
        $andWhere .= " AND eta.id=$id_etablissement ";
        $andTitre .= ' || Etablissement: ' . selectReferentiel($lien, 'etablissement', $id_etablissement);
    }
    if ($jury != 0) {
        $andWhere .= " AND rep.jury=$jury ";
        $andTitre .= ' || ' . $jury;
    }

    $pdf->ezSetY($height - 25);
//    $pdf->addJpegFromFile("../images/logo.png",575,750,45);
    $pdf->addJpegFromFile("../images/logo.png", 270, 760, 45);
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

    if ($tache == 'stats_presents') {
        $andWhere2 = " AND res.delib1!='Absent' AND res.delib1!='Abandon' ";
        $titre = " STATISTIQUES DES PRESENTS ";
    } elseif ($tache == 'stats_absents') {
        $andWhere2 = " AND (res.delib1='Absent' OR res.delib1='Abandon') ";
        $titre = " STATISTIQUES DES ABSENTS ";
    } elseif ($tache == 'stats_admis') {
        $andWhere2 = " AND (res.delib1='Passable' OR res.delib1='Abien' OR res.delib1='Bien' OR res.delib1='TBien' OR res.delib1='Oral') ";
        $titre = " STATISTIQUES DES ADMIS ";
    } else {
        $andWhere2 = '';
        $titre = " STATISTIQUES DES INSCRITS ";
    }
    $pdf->ezText(utf8_decode("<b>$andTitre</b>"), 10, array('justification' => 'center'));
    $sql = "SELECT reg.id as id_region, pre.id as id_prefecture, cent.id as id_centre, cent.etablissement as centre, ser.id as id_serie, count(*) as nbre_cand
		FROM bg_ref_region reg,
		bg_ref_prefecture pre,
		bg_ref_ville vil,
		bg_ref_etablissement cent,
		bg_repartition rep,
		bg_ref_serie ser,
		bg_ref_inspection insp,
		bg_ref_etablissement eta,
		bg_ref_eps spo,
		bg_candidats can
		LEFT JOIN bg_ref_langue_vivante lang ON lang.id=can.lv2
		LEFT JOIN bg_ref_epreuve_facultative_a faca ON faca.id=can.efa
		LEFT JOIN bg_ref_epreuve_facultative_b facb ON facb.id=can.efb
		LEFT JOIN bg_resultats res ON (res.num_table=can.num_table AND res.annee=$annee)
		WHERE can.annee=$annee AND rep.annee=$annee AND can.serie=ser.id AND can.num_table=rep.num_table
		AND cent.id_ville=vil.id AND vil.id_prefecture=pre.id AND pre.id_region=reg.id AND rep.id_centre=cent.id
		AND insp.id=eta.id_inspection AND eta.id=can.etablissement AND spo.id=can.eps
		$andWhere $andWhere2
		GROUP BY cent.id, ser.id
		ORDER BY reg.region, cent.etablissement, ser.serie ";

    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $tab = array('id_centre', 'centre', 'id_serie', 'nbre_cand');

    while ($row = mysqli_fetch_array($result)) {
        foreach ($tab as $val) {
            $$val = utf8_decode($row[$val]);
        }

        $tCapacite[$id_centre][$id_serie] = $nbre_cand;
        $tCentres[$id_centre] = $centre;
        $tTotaux[$id_centre] += $nbre_cand;
        $tTotSerie[$id_serie] += $nbre_cand;
        $total_general += $nbre_cand;
    }
    $sql2 = "SELECT id as id_serie, serie FROM bg_ref_serie ORDER BY serie";
    $result2 = bg_query($lien, $sql2, __FILE__, __LINE__);

    $cols['centre'] = 'CENTRES';
    while ($row2 = mysqli_fetch_array($result2)) {
        $id_serie = $row2['id_serie'];
        $serie = $row2['serie'];
        $tSeries[$id_serie] = $row2['serie'];
        $cols[$id_serie] = $serie;
    }
    $cols['total'] = 'TOTAL';

    $i = 0;
    foreach ($tCentres as $id_centre => $centre) {
        $data[$i]['centre'] = $centre;
        foreach ($tSeries as $id_serie => $serie) {
            $data[$i][$id_serie] = $tCapacite[$id_centre][$id_serie];
        }
        $data[$i]['total'] = $tTotaux[$id_centre];
        $i++;
    }

    $data[$i]['centre'] = 'TOTAL';
    foreach ($tSeries as $id_serie => $serie) {
        $data[$i][$id_serie] = $tTotSerie[$id_serie];
    }
    $data[$i]['total'] = $total_general;

    $titre_tab = html_entity_decode("<b>$titre</b>");
    $pdf->ezTable($data, $cols, $titre_tab, $options);
}

$pdfG = "liste_" . $tache . "_" . $id_centre;
$pdf->ezInsertMode(1, 1, 'before');
$pdf->ezInsertMode(0);

$title = "liste_" . $tache . "_" . $centre;
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

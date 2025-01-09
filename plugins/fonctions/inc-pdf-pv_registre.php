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
$pdf = new Cezpdf('A4', 'landscape'); // 595.28 x 841.89
$pdf->selectFont($pdf_dir . 'fonts/Helvetica.afm');
$options = array('b' => 'Helvetica-Bold.afm','fontSize' => 8);
$family = 'Helvetica';
$pdf->setFontFamily($family, $options);
$pdf->setStrokeColor(0, 0, 0);
$pdf->setLineStyle(1, 'round', 'round');
$width = $pdf->ez['pageWidth'];
$height = $pdf->ez['pageHeight'];
setlocale(LC_TIME, 'fr_FR', 'fr_BE.UTF8', 'fr_FR.UTF8');
$sDateConvoc = utf8_decode(strftime("%A %d %B %Y", mktime(0, 0, 0, date('m'), date('d'), date('Y'))));
$DateConvocPied = "" . date('d') . '/' . date('m') . '/' . date('Y');
$pdf->ezStartPageNumbers(300, 14, 7, 'right', (utf8_decode("BACCALAUREAT PREMIERE PARTIE (BAC 1) Session de $mois $annee P.V AVEC NOTES SUR 20")));
$pdf->ezStartPageNumbers(190, 7, 7, 'right', (utf8_decode("LEGENDE : (R) = RACHETE - ** CANDIDAT A LA SESSION REMPLAC. -§ = NOTE ELIMINATOIRE £ = NOTE CONSERVEE - # = NOTE NON PRISE EN COMPTE - *= MAT. FACULTATIVE")));
$pdf->ezStartPageNumbers(130, 8, 6, 'left', "Edition du $DateConvocPied - Page {PAGENUM} / {TOTALPAGENUM}");

$pdf->ezSetY($height - 25);
$pdf->addJpegFromFile("../images/logo.png", 390, 500, 45);
$pdf->ezColumnsStart(array('num' => 3, 'gap' => 10));
$pdf->ezText(utf8_decode("<b>MINISTERE DES ENSEIGNEMENTS PRIMAIRE,\nSECONDAIRE ET TECHNIQUE\n**********\nSECRETARIAT GENERAL\n**********\nDIRECTION DES EXAMENS, CONCOURS ET CERTIFICATIONS</b>"), 9, array('justification' => 'center'));
$pdf->ezNewPage();
$pdf->ezNewPage();
$pdf->ezText(utf8_decode("<b>REPUBLIQUE TOGOLAISE\nTravail - Liberté - Patrie</b>"), 9, array('justification' => 'center'));
$pdf->ezColumnsStop();

$pdf->ezSetY($height - 100);
$mois = moisSession2($lien, $annee);
$pdf->ezText(utf8_decode("<b>BACCALAUREAT PREMIERE PARTIE (BAC 1) Session : $mois $annee</b>"), 12, array('justification' => 'center'));

$pdf->ezText(utf8_decode("<b>P.V AVEC NOTES SUR 20 - EPREUVES ECRITES</b>"), 10, array('justification' => 'center'));
//$pdf->ezText(utf8_decode("<b>Taux d'admissibilité = .... soit ....</b>"), 10, array('justification' => 'center'));

/*********************************** */
if (isset($_GET['id_centre'])) {
    if ($id_centre != 0) {
        $andWhere .= " AND cent.id=$id_centre ";
    }

    $centre = selectReferentiel($lien, 'etablissement', $id_centre);
    //Entete suite
    $pdf->ezText(utf8_decode("<b>Centre d'examen : $centre\n</b>"), 10, array('justification' => 'center'));
    $pdf->ezText(utf8_decode("<b>ECM (GE) : Ed. Civ. et Morale - FR (GE) : Français - LV1 (GE) : L.V. 1 - LV2 (GE) : L.V2 - MATHS (GE) : Mathématiques - SCPHY (GE) : Sc. Physiques - SVT (GE): Sciences de la Vie et la Terre (S.V.T) - HG (GE) : Histoire - Géographie - EPS (GE) : Education Physique et Sportive (E.P.S) - LFA (GX) : Liste A - Langues facultatives - LFB (GX) : Liste B - Des./Ens. M./Init. Agri. - LFC (GX) : Liste C - Musique \n</b>"), 10, array('justification' => 'center'));
    //Fin Entete suite
    /* if ($jury > 0 && $id_serie > 0) {
    $serie = selectReferentiel($lien, 'serie', $id_serie);
    if ($deliberation == 2) {
    $andWhere = " AND res.delib1='Oral'";
    }

    if ($tri == 'oui') {
    $andWhere = '';
    } */

    $sql = "SELECT reg.id as id_region, pre.id as id_prefecture, cent.id as id_centre, cent.etablissement as centre, ser.id as id_serie,res.num_table,res.id_anonyme,nom,prenoms,res.annee,ser.serie,sexe,can.ddn,can.ldn,
    sum(IF(nte.id_matiere=48,note,NULL)) AS LV1,
    sum(IF(nte.id_matiere=51,note,NULL)) AS LV2,
    sum(IF(nte.id_matiere=60,note,NULL)) AS ECM,
    sum(IF(nte.id_matiere=46,note,NULL)) AS Fr,
    sum(IF(nte.id_matiere=47,note,NULL)) AS HG,
    sum(IF(nte.id_matiere=29,note,NULL)) AS Maths,
    sum(IF(nte.id_matiere=49,note,NULL)) AS SVT,
    sum(IF(nte.id_matiere=50,note,NULL)) AS Physique,
    sum(IF(nte.id_matiere=8,note,NULL)) AS Cout,
    sum(IF(nte.id_matiere=61,note,NULL)) AS Agri,
    sum(IF(nte.id_matiere=42,note,NULL)) AS EPS,
    sum(IF(nte.id_matiere=3,note,NULL)) AS Ewe_Kabye_Latin,
    sum(IF(nte.id_matiere=4,note,NULL)) AS Russe,
    sum(IF(nte.id_matiere=5,note,NULL)) AS Arabe,
    sum(IF(nte.id_matiere=6,note,NULL)) AS Dessin,
    sum(IF(nte.id_matiere=7,note,NULL)) AS Musique,
    nte.jury,total,res.coeff,moyenne,delib1,
    count(*) as nbre_cand
    FROM bg_ref_region reg,
    bg_notes as nte,
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
    LEFT JOIN bg_resultats res ON (res.num_table=can.num_table)
    WHERE can.serie=ser.id AND can.num_table=rep.num_table
    AND nte.num_table=res.num_table AND nte.id_type_note<>3
    AND can.num_table=nte.num_table
    AND ser.id=can.serie
    AND cent.id_ville=vil.id AND vil.id_prefecture=pre.id AND pre.id_region=reg.id AND rep.id_centre=cent.id
    AND insp.id=eta.id_inspection AND eta.id=can.etablissement AND spo.id=can.eps
    $andWhere $andWhere2
    GROUP BY cent.id, num_table
    ORDER BY num_table";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $tab = array('num_table',
        'id_anonyme',
        'nom',
        'prenoms',
        'annee',
        'serie',
        'sexe',
        'ddn',
        'ldn',
        'LV1',
        'LV2', 'ECM', 'Fr', 'HG', 'Maths', 'SVT', 'Physique',
        'Cout', 'Agri', 'EPS', 'Ewe_Kabye_Latin', 'Russe', 'Arabe', 'Dessin', 'Musique',
        'jury', 'total', 'coeff', 'moyenne', 'delib1');
    $i = 0;
    while ($row = mysqli_fetch_array($result)) {
        foreach ($tab as $val) {
            $$val = $row[$val];
        }

        /** Conformité du sexe suivants l'abreviation */
        if ($sexe == '2') {
            $sexe = 'M';
            $accord = 'é';
        } else {
            $sexe = 'F';
            $accord = 'ée';
        }

        /** Conformité des Langues facultatives comme LFA */
        if ($row['Ewe_Kabye_Latin']) {
            $LFA = $row['Ewe_Kabye_Latin'];
        } elseif ($row['Russe']) {
            $LFA = $row['Russe'];
        } elseif ($row['Arabe']) {
            $LFA = $row['Arabe'];
        }

        /** Conformité des Décisions et Mentions */
        if ($delib1 == 'Ajourne') {
            $delib1 = '<b>AJ</b>';
            $mention = '';
        } elseif ($delib1 == 'Oral') {
            $delib1 = '<b>Adss</b>';
            $mention = '';
        } elseif ($delib1 == 'Passable') {
            $delib1 = '<b>ADM</b>';
            $mention = 'P';
        } elseif ($delib1 == 'Abien') {
            $delib1 = '<b>ADM</b>';
            $mention = 'AB';
        } elseif ($delib1 == 'Bien') {
            $delib1 = '<b>ADM</b>';
            $mention = 'B';
        } elseif ($delib1 == 'TBien') {
            $delib1 = '<b>ADM</b>';
            $mention = 'TB';
        } elseif ($delib1 == 'Absent') {
            $delib1 = '<b>Absent</b>';
            $mention = '';
        } else {
            $delib1 = '<b>--</b>';
            $mention = '---';}
        $ddn = afficher_date($ddn);
        $dldn = html_entity_decode("N").$accord. html_entity_decode(" le ").$ddn . "\n". html_entity_decode("à ") . $ldn;
        $candidat = '<b>' . $nom . '</b> ' . $prenoms . "\n" . $dldn;
        $num_table = '<b>' . $num_table . '</b>';
        if ($id_eps == 1) {
            $atelier = '(' . substr(selectReferentiel($lien, 'atelier', $atelier1), 0, 1) . substr(selectReferentiel($lien, 'atelier', $atelier2), 0, 1) . substr(selectReferentiel($lien, 'atelier', $atelier3), 0, 1) . ')';
        } else {
            $atelier = '';
        }

        $eps .= ' ' . $atelier;
        $options = array(
            //'spacing' => 1,
            'justification' => 'full',
            //'titleFontSize' => 10,
            'fontSize' => 8,
            //'maxWidth' => 830, 
            'cols' => array(
                'identite du candidat' => array('justification' => 'left', 'width' => 150),
                'Décision' => array('justification' => 'center', 'width' => 20),
                'Mention' => array('justification' => 'center', 'width' => 25),
                'SCPHY' => array('justification' => 'center', 'width' => 20),
                'MATHS' => array('justification' => 'center', 'width' => 20),
            ),
        );
        $i++;
        $data[$i] = array(
            //'num'=>$num_table,'noms'=>utf8_decode($candidat),'dldn'=>utf8_decode($dldn),'eta'=>$etablissement,'serie'=>$serie,'jr'=>$jury,'eps'=>$eps,'lv2'=>$langue_vivante,'efa'=>$epreuve_facultative_a,'efb'=>$epreuve_facultative_b,
            utf8_decode('identite du candidat') => utf8_decode($candidat),
            'sexe' => utf8_decode($sexe),
            utf8_decode('N° table') => $num_table,
            'serie' => $serie,
            utf8_decode('Décision') => $delib1,
            'Mention' => $mention,
            'ECM' => $row['ECM'],
            'Fr' => $row['Fr'],
            'LV1' => $row['LV1'],
            'LV2' => $row['LV2'],
            'HG' => $row['HG'],
            'MATHS' => $row['Maths'],
            'SVT' => $row['SVT'],
            'SCPHY' => $row['Physique'],
            'EPS' => $row['EPS'],
            'LFA*' => $LFA,
            'Dess' => $row['Dessin'],
            'Cout' => $row['Cout'],
            'Agri' => $row['Agri'],
            'LFC*' => $row['Musique'],
            'coeff' => $coeff,
            'Tot.1' => $total,
            'Moy.1' => $moyenne);
    }

    $pdf->ezTable($data, $cols, $titre_tab, $options);
    $pdf->ezText(utf8_decode(" Nombre de lignes: <b>$i</b>"), 10, array('justification' => 'left', 'fontSize' => 7));
/* }
 */

    // debut ****yves
    $pdf->ezNewPage();
    // $sql2 = "SELECT num_table, id_anonyme, total, moyenne, moyenne2, delib1, delib2 \n
    // FROM bg_resultats \n
    // WHERE annee=$annee AND jury=$jury AND (delib1='Absent' OR delib2='Abandon')";
    // $result2 = bg_query($lien, $sql2, __FILE__, __LINE__);
    // $absents = mysqli_num_rows($result2);

    // $sql3 = "SELECT num_table, id_anonyme, total, moyenne, moyenne2, delib1, delib2 \n
    // FROM bg_resultats \n
    // WHERE annee=$annee AND jury=$jury ";

    $sql3 = "SELECT res.num_table, res.id_anonyme, total, moyenne, moyenne2, delib1, delib2 \n
    FROM bg_resultats res,bg_repartition rep\n
    WHERE res.annee=$annee AND  res.num_table=rep.num_table AND rep.id_centre=$id_centre";
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
        if ($moyenne < 6) {
            $moy_0_6++;
        } elseif ($moyenne >= 6 && $moyenne < 7.5) {
            $moy_6_75++;
        } elseif ($moyenne >= 7.5 && $moyenne < 8) {
            $moy_75_8++;
        } elseif ($moyenne >= 8 && $moyenne < 8.5) {
            $moy_8_85++;
        } elseif ($moyenne >= 8.5 && $moyenne < 9) {
            $moy_85_9++;
        } elseif ($moyenne >= 9 && $moyenne < 9.5) {
            $moy_9_95++;
        } elseif ($moyenne >= 9.5 && $moyenne < 10) {
            $moy_95_10++;
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

    $data2[1]['intervalle'] = '0 <= Moyenne < 6';
    $data2[1]['effectif'] = $moy_0_6;
    $data2[1]['taux'] = round(($moy_0_6 * 100) / $effectif_total, 2, PHP_ROUND_HALF_EVEN) . '%';

    $data2[2]['intervalle'] = '6 <= Moyenne < 7,50';
    $data2[2]['effectif'] = $moy_6_75;
    $data2[2]['taux'] = round(($moy_6_75 * 100) / $effectif_total, 2, PHP_ROUND_HALF_EVEN) . '%';

    $data2[3]['intervalle'] = '7,50 <= Moyenne < 8';
    $data2[3]['effectif'] = $moy_75_8;
    $data2[3]['taux'] = round(($moy_75_8 * 100) / $effectif_total, 2, PHP_ROUND_HALF_EVEN) . '%';

    $data2[4]['intervalle'] = '8 <= Moyenne < 8,50';
    $data2[4]['effectif'] = $moy_8_85;
    $data2[4]['taux'] = round(($moy_8_85 * 100) / $effectif_total, 2, PHP_ROUND_HALF_EVEN) . '%';

    $data2[5]['intervalle'] = '8,50 <= Moyenne < 9';
    $data2[5]['effectif'] = $moy_85_9;
    $data2[5]['taux'] = round(($moy_85_9 * 100) / $effectif_total, 2, PHP_ROUND_HALF_EVEN) . '%';

    $data2[6]['intervalle'] = '9 <= Moyenne < 9,50';
    $data2[6]['effectif'] = $moy_9_95;
    $data2[6]['taux'] = round(($moy_9_95 * 100) / $effectif_total, 2, PHP_ROUND_HALF_EVEN) . '%';

    $data2[7]['intervalle'] = '9,50 <= Moyenne < 10';
    $data2[7]['effectif'] = $moy_95_10;
    $data2[7]['taux'] = round(($moy_95_10 * 100) / $effectif_total, 2, PHP_ROUND_HALF_EVEN) . '%';

    $data2[8]['intervalle'] = '10 <= Moyenne < 12';
    $data2[8]['effectif'] = $moy_10_12;
    $data2[8]['taux'] = round(($moy_10_12 * 100) / $effectif_total, 2, PHP_ROUND_HALF_EVEN) . '%';

    $data2[9]['intervalle'] = '12 <= Moyenne < 14';
    $data2[9]['effectif'] = $moy_12_14;
    $data2[9]['taux'] = round(($moy_12_14 * 100) / $effectif_total, 2, PHP_ROUND_HALF_EVEN) . '%';

    $data2[10]['intervalle'] = '14 <= Moyenne < 16';
    $data2[10]['effectif'] = $moy_14_16;
    $data2[10]['taux'] = round(($moy_14_16 * 100) / $effectif_total, 2, PHP_ROUND_HALF_EVEN) . '%';

    $data2[11]['intervalle'] = '16 <= Moyenne < 18';
    $data2[11]['effectif'] = $moy_16_18;
    $data2[11]['taux'] = round(($moy_16_18 * 100) / $effectif_total, 2, PHP_ROUND_HALF_EVEN) . '%';

    $data2[12]['intervalle'] = '18 <= Moyenne <= 20';
    $data2[12]['effectif'] = $moy_18_20;
    $data2[12]['taux'] = round(($moy_18_20 * 100) / $effectif_total, 2, PHP_ROUND_HALF_EVEN) . '%';

    $data2[13]['intervalle'] = 'Presents';
    $data2[13]['effectif'] = $effectif;
    $data2[13]['taux'] = '';

    $data2[14]['intervalle'] = 'Absents ou Abandons';
    $data2[14]['effectif'] = $absents;
    $data2[14]['taux'] = '';

    $taux = round(($moy_9 * 100) / $effectif_total, 2, PHP_ROUND_HALF_EVEN);

    $pdf->ezSetY($height - 25);
//    $pdf->addJpegFromFile("../images/togo.jpeg",565,750,45);
    $pdf->ezColumnsStart(array('num' => 3, 'gap' => 10));
    $pdf->ezText(utf8_decode("<b>$ministere\n**********\n$secretariat_general\n**********\n$direction</b>"), 9, array('justification' => 'center'));
    $pdf->ezNewPage();
    $pdf->ezNewPage();
    $pdf->ezText(utf8_decode("<b>REPUBLIQUE TOGOLAISE\nTravail - Liberté - Patrie</b>"), 9, array('justification' => 'center'));
    $pdf->ezColumnsStop();

    $pdf->ezSetY($height - 100);
    $mois = moisSession2($lien, $annee);
    $pdf->ezText(utf8_decode("<b>$libelle_examen Session : $mois $annee</b>"), 12, array('justification' => 'center'));

    $pdf->ezText(utf8_decode("<b>P.V AVEC NOTES SUR 20 - EPREUVES ECRITES</b>"), 10, array('justification' => 'center'));

    $pdf->ezText(utf8_decode("\n<b><u>STATISTIQUES </u></b>\n"), 18, array('justification' => 'center'));
    $pdf->ezText(utf8_decode("<b>Centre d'examen : $centre - ANNEE $annee \n TAUX D'ADMISSIBILITE = $moy_9 / $effectif_total SOIT $taux </b>\n"), 13, array('justification' => 'center'));
    $entete2 = array('intervalle' => "INTERVALLES DE MOYENNE", 'effectif' => "EFFECTIF", 'taux' => "TAUX");
//    $titre_tab2=html_entity_decode("JURY $jury - SERIE $serie - ANNEE $annee \n - TAUX D'ADMISSIBILITE = $moy_9 / $effectif_total SOIT $taux %");
    $pdf->ezTable($data2, $entete2, $titre_tab2, $options2);

// fin ****yves
}
/************************************************* */

/*
echo "<pre>";
print_r($entete);
echo "</pre>";
 */

$pdfG = "registre_jury" . $jury . '_serie' . $serie;
$pdf->ezInsertMode(1, 1, 'before');
$pdf->ezInsertMode(0);

$title = "Registre jury" . "[" . $jury1 . "-" . $jury2 . "]" . '_serie' . $serie . '_annee' . $annee;
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

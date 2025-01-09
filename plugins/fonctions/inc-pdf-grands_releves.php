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
$pdf->ezStartPageNumbers(100, 15, 9, 'right', "REPUBLIQUE TOGOLAISE - $direction - $DateConvocPied - PAGE {PAGENUM} sur {TOTALPAGENUM}");
//$pdf->ezStartPageNumbers(570, 15, 8, 'left', "$DateConvocPied - Page {PAGENUM} sur {TOTALPAGENUM}");

$pdf->ezSetY($height - 25);
$pdf->addJpegFromFile("../images/logo.png", 390, 500, 45);
$pdf->ezColumnsStart(array('num' => 3, 'gap' => 10));
$pdf->ezText(utf8_decode("<b>$ministere\n**********\n$secretariat_general\n**********\n$direction</b>"), 9, array('justification' => 'center'));
$pdf->ezNewPage();
$pdf->ezNewPage();
$pdf->ezText(utf8_decode("<b>REPUBLIQUE TOGOLAISE\nTravail - Liberté - Patrie</b>"), 9, array('justification' => 'center'));
$pdf->ezColumnsStop();

$pdf->ezSetY($height - 100);
$pdf->ezText(utf8_decode("<b>$libelle_examen</b>"), 12, array('justification' => 'center'));
$mois = moisSession($lien, $annee, $jury);
$pdf->ezText(utf8_decode("<b>Session de $mois $annee\n</b>"), 10, array('justification' => 'center'));

$pdf->ezText(utf8_decode("\n<b><u>GRANDS RELEVES </u></b>\n"), 13, array('justification' => 'center'));

if ($jury > 0 && $id_serie > 0) {
    $serie = selectReferentiel($lien, 'serie', $id_serie);
    if ($deliberation == 2) {
        $andWhere = " AND res.delib1='Oral'";
    }

    if ($tri == 'oui') {
        $andWhere = '';
    }

    $sql = "SELECT notes.num_table, notes.id_anonyme, notes.note, notes.id_matiere, notes.id_type_note, mat.matiere, mat.abreviation, notes.coeff, res.total, res.moyenne, res.moyenne2, res.delib1, res.delib2 \n
			FROM bg_notes notes, bg_ref_matiere mat, bg_resultats res \n
			WHERE notes.annee=$annee AND res.annee=$annee AND notes.id_anonyme=res.id_anonyme AND notes.id_matiere=mat.id AND notes.id_type_note!=5 \n
			AND notes.jury=$jury AND res.jury=$jury AND notes.id_serie=$id_serie $andWhere
			ORDER BY notes.ano, notes.id_type_note, mat.matiere";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $tab = array('num_table', 'id_anonyme', 'note', 'id_matiere', 'id_type_note', 'matiere', 'abreviation', 'coeff', 'total', 'moyenne', 'moyenne2', 'delib1', 'delib2');
    while ($row = mysqli_fetch_array($result)) {
        foreach ($tab as $val) {
            $$val = html_entity_decode($row[$val]);
        }

        if ($note >= 0) {
            $tNotes[$id_anonyme][$id_matiere][$id_type_note]['note'] = $note;
        } else {
            $tNotes[$id_anonyme][$id_matiere][$id_type_note]['note'] = 'N/C';
        }
        if ($moyenne >= 0) {
            if ($delib1 == 'Oral' && $moyenne2 > 0) {
                $tMoyennes[$id_anonyme] = "$moyenne ($moyenne2)";
            } else {
                $tMoyennes[$id_anonyme] = $moyenne;
            }

            $tTotaux[$id_anonyme] = $total;
        } else {
            $tMoyennes[$id_anonyme] = 'N/C';
            $tTotaux[$id_anonyme] = 'N/C';
        }
        if ($deliberation <= 1) {
            $tDecis[$id_anonyme] = $delib1;
        }

        if ($deliberation == 2 || ($tri == 'oui' && $delib1 == 'Oral')) {
            $tDecis[$id_anonyme] = $delib2;
        }

        if ($deliberation == 2 && ($tri == 'oui' && $delib1 != 'Oral')) {
            $tDecis[$id_anonyme] = $delib1;
        }

        if ($id_type_note == 4) {
            $type = 'Fac.';
        } else {
            $type = selectReferentiel($lien, 'type_note', $id_type_note);
        }

        $tCand[$id_anonyme] = $num_table;
        $tMatieres[$id_type_note][$id_matiere] = $abreviation . html_entity_decode("\n $type \n $coeff");
        $tCoeffs[$id_matiere][$id_type_note] = $coeff;
    }
    //    print_r($tNotes);
    $entete['candidat'] = "CANDIDAT";
    $i = 0;
    foreach ($tCand as $id_anonyme => $num_table) {
        if ($deliberation == 0) {
            $candidat = $id_anonyme;
        } else {
            $tNoms = getNoms($lien, $annee, $num_table);
            $candidat = $num_table . ' ' . $tNoms['noms'];
        }

        $coeff_total = 0;
        foreach (array('4', '1', '2', '3') as $id_type_note) {
            foreach ($tMatieres[$id_type_note] as $id_matiere => $matiere) {
                $coeff_total += $tCoeffs[$id_matiere][$id_type_note];

                $intitule = $id_matiere . '_' . $id_type_note;
                $entete[$intitule] = utf8_decode($matiere);
                $data[$i][$intitule] = $tNotes[$id_anonyme][$id_matiere][$id_type_note]['note'];
            }
        }
        $data[$i]['candidat'] = utf8_decode($candidat);
        $data[$i]['total'] = $tTotaux[$id_anonyme];
        $data[$i]['moyenne'] = $tMoyennes[$id_anonyme];
        $data[$i]['delib1'] = $tDecis[$id_anonyme];
        $i++;
    }
    if ($tri != 'oui') {
        $tcoef = html_entity_decode("\n $coeff_total");
    } else {
        $tcoef = '';
    }

    $entete['total'] = 'Total' . $tcoef;
    $entete['moyenne'] = 'MOY.';
    $entete['delib1'] = 'Resultat';

    $sql_code = "SELECT code FROM bg_codes WHERE annee=$annee AND jury=$jury LIMIT 0,1";
    $result_code = bg_query($lien, $sql_code, __FILE__, __LINE__);
    $row_code = mysqli_fetch_array($result_code);
    $code_jury = $row_code['code'];
    $deliberation < 1 ? $intitule_jury = $code_jury : $intitule_jury = $jury;

    if ($id_serie > 8) {
        $fontSize = 8;
    } else {
        $fontSize = 9;
    }

    if ($deliberation < 1) {
        $options = array('titleFontSize' => 9, 'fontSize' => $fontSize, 'maxWidth' => 780);
    } else {
        $options = array('cols' => array('candidat' => array('justification' => 'left', 'width' => 150),
            'total' => array('justification' => 'left', 'width' => 30),
            'delib1' => array('justification' => 'left', 'width' => 45),
            'moyenne' => array('justification' => 'left', 'width' => 35)),
            'titleFontSize' => 8, 'fontSize' => 8, 'maxWidth' => 760);
    }

    $options2 = array('titleFontSize' => 14, 'fontSize' => 13);

    $titre_tab = html_entity_decode("JURY $intitule_jury - SERIE $serie - ANNEE $annee");
    $pdf->ezTable($data, $entete, $titre_tab, $options);
    $date_delib = dateDeliberation($lien, $annee, $jury);
    $president_jury = presidentJurys($lien, $annee, $jury);

//Injection de controle du chef lieu des regions en fonction des jurys
$sql_chefLieu = "SELECT region_division, chef_lieu, borne_jury, borne_jury_fin FROM bg_ref_region_division";
$result_chefLieu = bg_query($lien, $sql_chefLieu, __FILE__, __LINE__);

// Vérifie s'il y a des résultats
if (mysqli_num_rows($result_chefLieu) > 0) {
    // Parcourir chaque ligne du résultat
    while ($row_chefLieu = mysqli_fetch_array($result_chefLieu)) {
        // Vérifie si $jury se situe dans la plage déterminée pour cette ligne
        if ($jury >= $row_chefLieu['borne_jury'] && $jury < $row_chefLieu['borne_jury_fin']) {
            $chefLieu = $row_chefLieu['chef_lieu'];
            echo "Le chef-lieu pour le jury $jury est : $chefLieu";
        }
    }
} else {
    echo "Aucun résultat trouvé.";
}

    $pdf->ezText(utf8_decode("\n $chefLieu, le " . date('d') . '/' . date('m') . '/' . date('Y')), 12, array('justification' => 'right'));

    //$pdf->ezText(utf8_decode("\nLOME, le $date_delib"),12,array('justification'=>'right'));
    $pdf->ezText(utf8_decode("\nSignature du Président du Jury $intitule_jury\n\n\n\n$president_jury"), 12, array('justification' => 'center'));

    $pdf->ezNewPage();

    $sql2 = "SELECT num_table, id_anonyme, total, moyenne, moyenne2, delib1, delib2 \n
			FROM bg_resultats \n
			WHERE annee=$annee AND jury=$jury AND (delib1='Absent' OR delib2='Abandon')";
    $result2 = bg_query($lien, $sql2, __FILE__, __LINE__);
    $absents = mysqli_num_rows($result2);

    $sql3 = "SELECT num_table, id_anonyme, total, moyenne, moyenne2, delib1, delib2 \n
			FROM bg_resultats \n
			WHERE annee=$annee AND jury=$jury ";
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

    $pdf->ezText(utf8_decode("\n<b><u>STATISTIQUES </u></b>\n"), 18, array('justification' => 'center'));
    $pdf->ezText(utf8_decode("<b>JURY $intitule_jury - SERIE $serie - ANNEE $annee \n TAUX D'ADMISSIBILITE = $moy_9 / $effectif_total SOIT $taux </b>\n"), 13, array('justification' => 'center'));
    $entete2 = array('intervalle' => "INTERVALLES DE MOYENNE", 'effectif' => "EFFECTIF", 'taux' => "TAUX");
//    $titre_tab2=html_entity_decode("JURY $jury - SERIE $serie - ANNEE $annee \n - TAUX D'ADMISSIBILITE = $moy_9 / $effectif_total SOIT $taux %");
    $pdf->ezTable($data2, $entete2, $titre_tab2, $options2);

}
/*
echo "<pre>";
print_r($entete);
echo "</pre>";
 */

$pdfG = "grands_releves_jury" . $jury . '_serie' . $serie;
$pdf->ezInsertMode(1, 1, 'before');
$pdf->ezInsertMode(0);

$title = "Grands releves jury" . $jury . '_serie' . $serie . '_annee' . $annee;
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

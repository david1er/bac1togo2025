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
if ($t == 'p' || ($t == 'l' && $a == 4)) {
    $pdf = new Cezpdf('A4', 'landscape');
    $maxWidth = 830;
    $titleFontSize = 6;
    $fontSize = 7;
    if ($e == 0) {
        $options = array('cols' => array(
            'Candidat' => array('justification' => 'right', 'width' => 200),
        ));
    }
} else {
    $pdf = new Cezpdf('A4', 'portrait');
    $maxWidth = 545;
    $titleFontSize = 9;
    $fontSize = 9;
} // 595.28 x 841.89
$pdf->selectFont($pdf_dir . 'fonts/Helvetica.afm');
$options = array('b' => 'Helvetica-Bold.afm');
$family = 'Helvetica';
$pdf->setFontFamily($family, $options);
$pdf->setStrokeColor(0, 0, 0);
$pdf->setLineStyle(1, 'round', 'round');
$width = $pdf->ez['pageWidth'];
$height = $pdf->ez['pageHeight'];
$pdf->ezSetMargins(30, 30, 50, 30);
setlocale(LC_TIME, 'fr_FR', 'fr_BE.UTF8', 'fr_FR.UTF8');
$DatePied = "IMPRIME LE " . date('d') . '/' . date('m') . '/' . date('Y');
$pdf->ezStartPageNumbers(30, 10, 7, 'right', $DatePied . " - $direction -  $annee - Page {PAGENUM}/{TOTALPAGENUM} / EXAMINATEURS: .......................................................................");

//$pdf->line(25,27,570,27);
//$pdf->addText(30,18,7,'EXAMINATEURS: ');
if (isset($_GET['centre_id'])) {
    $sql = "SELECT * FROM bg_ref_etablissement WHERE id_centre=$centre_id";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $colonnes = array('si_centre', 'id', 'id_centre', 'region', 'prefecture', 'etablissement', 'centre', 'ville', 'inspection', 'login_eta', 'mdp_eta');
    while ($row = mysqli_fetch_array($result)) {
        foreach ($colonnes as $col) {
            $$col = $row[$col];
        }
        $options = array(
            'spacing' => 1,
            'justification' => 'full',
            'titleFontSize' => $titleFontSize,
            'fontSize' => $fontSize,
            'maxWidth' => $maxWidth,
        );

        $andWhere = '';
        $titre = '';
        if ($centre_id != 0) {$andWhere .= " AND cen.id=$centre_id ";
            $centre = selectReferentiel($lien, 'etablissement', $centre_id);
            $titre .= "Centre: <b>" . utf8_decode($centre) . " - </b>";}
        // if ($centre_id != 0) {
        //     $sql2 = "SELECT id_centre FROM bg_ref_etablissement WHERE id=" . $id;
        //     $result2 = bg_query($lien, $sql2, __FILE__, __LINE__);
        //     $row2 = mysqli_fetch_array($result2);
        //     $id_centre = $row2['id_centre'];
        //     $centre = selectReferentiel($lien, 'etablissement', $id_centre);
        //     $titre .= "Centre: <b>$centre - </b>";
        // }
        // if ($e == 0) {
        $andWhere .= " AND eta.id=$id ";
        $etablissement = selectReferentiel($lien, 'etablissement', $id);
        $titre .= "Etablissement: <b>" . utf8_decode($etablissement) . "</b> \n";
        // }
        if ($s != 0) {$andWhere .= " AND ser.id=$s ";
            $serie = selectReferentiel($lien, 'serie', $s);
            $titre .= "Serie: <b>$serie</b> \n";}
        if ($a != 0) {$andWhereAtelier = " AND (can.atelier1=$a || can.atelier2=$a || can.atelier3=$a) ";
            $atelier = selectReferentiel($lien, 'atelier', $a);
            $titre .= "Atelier: <b>$atelier</b>";}
        // if ($e == 0) {$andWhere .= " AND eta.id=0 ";
        //     $titre = 'Atelier: ________________    Etablissement: _________________________________';}
        $colonnes = array('nom', 'prenoms', 'serie', 'num_table', 'sexe', 'id_candidat', 'num_table', 'sexe', 'id_sexe', 'id_centre');
        $cols = array('N°' => utf8_decode('N°'), 'Candidat' => 'NOMS  ET  PRENOMS', 'Serie' => 'Ser.', 'sex' => 'Sex');

        if ($t == 'l') {
            $nomTableau = "LISTE DES CANDIDATS APTES A L'EPREUVE D'EPS ";

            $sql = "SELECT nom, prenoms, can.id_candidat, ser.serie, sex.sexe, can.num_table, sex.id as id_sexe, eta.id_centre
		FROM bg_ref_sexe sex, bg_ref_serie ser, bg_ref_etablissement eta, bg_ref_etablissement cen, bg_candidats can
		LEFT JOIN bg_repartition rep ON (rep.annee=$annee AND rep.num_table=can.num_table)
		WHERE can.annee=$annee AND can.etablissement=eta.id AND sex.id=can.sexe
		AND can.centre=cen.id AND can.serie=ser.id $andWhere $andWhereAtelier
		ORDER BY nom, prenoms ";

            // if ($a == 12) {
            //     $sql2 = "SELECT * FROM bg_performance WHERE id_atelier=12";
            //     $result2 = bg_query($lien, $sql2, __FILE__, __LINE__);
            //     while ($row2 = mysqli_fetch_array($result2)) {
            //         $tabBaremes[] = $row2['fille'];
            //         $tabBaremes[] = $row2['garcon'];
            //     }
            //     asort($tabBaremes);
            //     foreach ($tabBaremes as $val) {
            //         $cols[$val] = $val;
            //     }

            //     $cols['perf'] = 'Perf.';
            //     $cols['obs'] = 'Observ.';

            // } else {
            $cols['essai1'] = 'ESSAI 1';
            $cols['essai2'] = 'ESSAI 2';
            $cols['essai3'] = 'ESSAI 3';
            $cols['perf'] = 'PERFOR.';
            $cols['obs'] = 'OBSERT.';
            // }

        }

        $result = bg_query($lien, $sql, __FILE__, __LINE__);

        $pdf->ezSetY($height - 25);
        if ($t == 'p' || ($t == 'l' && $a == 12)) {
            $pdf->addJpegFromFile("../images/logo.png", 405, 500, 45);
        } else {
            $pdf->addJpegFromFile("../images/logo.png", 285, 750, 45);
        }

        $pdf->ezColumnsStart(array('num' => 3, 'gap' => 10));
        $pdf->ezText(utf8_decode("<b>$ministere\n**********\n$secretariat_general\n**********\n$direction</b>"), 9, array('justification' => 'center'));
        $pdf->ezNewPage();
        $pdf->ezNewPage();
        $pdf->ezText(utf8_decode("<b>REPUBLIQUE TOGOLAISE\nTravail - Liberté - Patrie</b>"), 9, array('justification' => 'center'));
        $pdf->ezColumnsStop();

        $pdf->ezSetY($height - 130);
        $pdf->ezText(utf8_decode("<b>$libelle_examen</b>"), 11, array('justification' => 'center'));
        $mois = moisSession2($lien, $annee, $jury);
        $pdf->ezText(utf8_decode("<b>Session de $mois $annee\n</b>"), 10, array('justification' => 'center'));

        $pdf->ezText(utf8_decode("\n<b><u>$nomTableau </u></b>\n"), 13, array('justification' => 'center'));
        $pdf->ezText(html_entity_decode("$titre\n"), 13, array('justification' => 'left'));

        $titre_tab = '';
        $data = array();
        $i = 1;

        while ($row = mysqli_fetch_array($result)) {
            foreach ($colonnes as $col) {
                $$col = $row[$col];
            }

            if ($id_sexe == 2) {
                $civ = 'M.';
                $e = '';
            } else {
                $civ = 'Mlle';
                $e = 'e'; // pour mettre des mots au fminin si c'est une candidate
            }
            $candidat = utf8_decode(html_entity_decode("$civ <b>$nom</b> $prenoms"));
            if ($num_table != '') {
                $numero = $num_table;
            } else {
                $numero = $id_candidat;
            }

            $data[$i] = array('N°' => $i, 'Candidat' => $candidat, 'Serie' => $serie, 'sex' => $sexe);
            if ($t == 'n') {$data[$i]['Note'] = $note;} elseif ($t == 'np') {$data[$i]['Performance'] = $performance;
                $data[$i]['Note'] = $note_perf;} elseif ($t == 'p') {
                $tabNotes = notesEPSParCandidat($lien, $annee, $num_table);
                for ($a = 1; $a <= 7; $a++) {
                    //$atelier=selectReferentiel('atelier',$a);
                    $performance = $tabNotes[$a]['performance'];
                    $note_perf = $tabNotes[$a]['note_perf'];
                    if ($performance != '') {
                        $affiche = $performance . '[' . $note_perf . ']';
                    } else {
                        $affiche = '';
                    }

                    $data[$i][$a] = $affiche;
                }
                $data[$i]['note'] = $tabNotes['note'];
            }
            $i++;
        }

        if ($e == 0 && $t == 'l') {
            $noms = utf8_decode(html_entity_decode("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"));
            for ($i == 0; $i < 13; $i++) {
                $data[$i] = array('N°' => $i, 'Candidat' => $noms, 'Serie' => '', 'sex' => '');
            }
        }

        $pdf->ezTable($data, $cols, $titre_tab, $options);

    }

}
//$pdf->ezInsertMode(1,1,'before');
$pdf->ezInsertMode(0);
$pdfG = "listeEPS_" . getRewriteString($centre) . "_" . getRewriteString($atelier);

$title = "Liste EPS_" . getRewriteString($centre) . "_" . getRewriteString($atelier);
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
    $pdf_fic = "pdf/" . $annee . "-$pdfG.pdf";
    $fp = fopen($pdf_fic, 'wb');
    fwrite($fp, $pdfcode);
    fclose($fp);

    echo "<A HREF='$pdf_fic'>Cliquez ici pour lire le fichier $pdf_fic</a>\n";
    echo "<script>document.location='$pdf_fic'</script>";
}

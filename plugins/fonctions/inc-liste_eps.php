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
$pdf->ezStartPageNumbers(30, 10, 7, 'right', $DatePied . " - DIRECTION DES EXAMENS, CONCOURS ET CERTIFICATIONS - JUIN $annee - Page {PAGENUM}/{TOTALPAGENUM} / EXAMINATEURS: ..............................................................................");

//$pdf->line(25,27,570,27);
//$pdf->addText(30,18,7,'EXAMINATEURS: ');

$options = array(
    'spacing' => 1,
    'justification' => 'full',
    'titleFontSize' => $titleFontSize,
    'fontSize' => $fontSize,
    'maxWidth' => $maxWidth,
);

$andWhere = '';
$titre = '';
if ($c != 0) {$andWhere .= " AND cen.id=$c ";
    $centre = selectReferentiel($lien, 'etablissement', $c);
    $titre .= "Centre: <b>$centre - </b>";}
if ($e != 0 && $c == 0) {
    $sql2 = "SELECT id_centre FROM bg_ref_etablissement WHERE id=" . $e;
    $result2 = bg_query($lien, $sql2, __FILE__, __LINE__);
    $row2 = mysqli_fetch_array($result2);
    $id_centre = $row2['id_centre'];
    $centre = selectReferentiel($lien, 'etablissement', $id_centre);
    $titre .= "Centre: <b>" . utf8_decode($centre) . " - </b>";
}
if ($e != 0) {$andWhere .= " AND eta.id=$e ";
    $etablissement = selectReferentiel($lien, 'etablissement', $e);
    $titre .= "Etablissement: <b>" . utf8_decode($etablissement) . "</b> \n";}
if ($s != 0) {$andWhere .= " AND ser.id=$s ";
    $serie = selectReferentiel($lien, 'serie', $s);
    $titre .= "Serie: <b>$serie</b> \n";}
if ($a != 0) {$andWhereAtelier = " AND (can.atelier1=$a || can.atelier2=$a || can.atelier3=$a) ";
    $atelier = selectReferentiel($lien, 'atelier', $a);
    $titre .= "Atelier: <b>$atelier</b>";}
if ($e == 0) {$andWhere .= " AND eta.id=0 ";
    $titre = 'Atelier: ________________	Etablissement: _________________________________';}
$colonnes = array('nom', 'prenoms', 'serie', 'num_table', 'sexe', 'id_candidat', 'num_table', 'sexe', 'id_sexe', 'id_centre');
// $cols = array('N°' => utf8_decode('N°'), 'Candidat' => 'NOMS  ET  PRENOMS', 'Serie' => 'Ser.', 'sex' => 'Sex');
$cols = array('N°' => utf8_decode('N°'), 'Num' => utf8_decode('N°TABLE'), 'Candidat' => 'NOMS  ET  PRENOMS', 'Serie' => 'Ser.', 'sex' => 'Sex');

if ($t == 'l') {
    $nomTableau = "LISTE DES CANDIDATS APTES A L'EPREUVE D'EPS ";
/*
$sql="SELECT nom, prenoms, can.num_table, ser.serie, can.sexe
FROM bg_candidats can, bg_ref_serie ser, bg_repartition rep, bg_ref_etablissement eta, bg_ref_etablissement cen
WHERE can.annee=$annee AND rep.annee=$annee AND can.num_table=rep.num_table AND can.etablissement=eta.id
AND rep.id_centre=cen.id AND can.serie=ser.id $andWhere $andWhereAtelier
ORDER BY can.num_table ";
 */
    $sql = "SELECT nom, prenoms, can.id_candidat, ser.serie, sex.sexe, can.num_table, sex.id as id_sexe, eta.id_centre
		FROM bg_ref_sexe sex, bg_ref_serie ser, bg_ref_etablissement eta, bg_ref_etablissement cen, bg_candidats can
		LEFT JOIN bg_repartition rep ON (rep.annee=$annee AND rep.num_table=can.num_table)
		WHERE can.annee=$annee AND can.etablissement=eta.id AND sex.id=can.sexe
		AND can.centre=cen.id AND can.serie=ser.id $andWhere $andWhereAtelier
		ORDER BY nom, prenoms ";

    if ($a == 8) {
        $sql2 = "SELECT * FROM bg_performance WHERE id_atelier=8";
        $result2 = bg_query($lien, $sql2, __FILE__, __LINE__);
        while ($row2 = mysqli_fetch_array($result2)) {
            $tabBaremes[] = $row2['fille'];
            $tabBaremes[] = $row2['garcon'];
        }
        asort($tabBaremes);
        foreach ($tabBaremes as $val) {
            $cols[$val] = $val;
        }

        $cols['perf'] = 'Perf.';
        $cols['obs'] = 'Observ.';

    } else {
        $cols['essai1'] = 'ESSAI 1';
        $cols['essai2'] = 'ESSAI 2';
        $cols['essai3'] = 'ESSAI 3';
        $cols['perf'] = 'PERFOR.';
        $cols['obs'] = 'OBSERT.';
    }

} elseif ($t == 'n') {
    $colonnes[] = 'note';
    $cols['Note'] = 'NOTE';
    $nomTableau = "NOTES DES CANDIDATS APTES A L'EPREUVE D'EPS";

    $sql = "SELECT nom, prenoms, can.num_table, ser.serie, sex.sexe, sex.id as id_sexe, note
		FROM bg_candidats can, bg_ref_serie ser, bg_repartition rep, bg_ref_etablissement eta,
		bg_ref_etablissement cen, bg_notes_eps notes, bg_ref_sexe sex
		WHERE can.annee=$annee AND rep.annee=$annee AND notes.annee=$annee
		AND sex.id=can.sexe AND can.num_table=notes.num_table AND can.num_table=rep.num_table AND can.etablissement=eta.id
		AND rep.id_centre=cen.id AND can.serie=ser.id $andWhere
		ORDER BY can.num_table ";

} elseif ($t == 'np') {
    $unite = utf8_decode(getUniteAtelier($lien, $a));
    $colonnes = array_merge($colonnes, array('note_perf', 'performance'));
    $cols['Performance'] = "PERFORMANCE \n($unite) ";
    $cols['Note'] = 'NOTE';
    $nomTableau = "PERFORMANCES ET NOTES DES CANDIDATS PAR ATELIER";

    $sql = "SELECT nom, prenoms, can.num_table, ser.serie, sex.sexe, sex.id as id_sexe, performance, note_perf
		FROM bg_candidats can, bg_ref_serie ser, bg_repartition rep, bg_ref_etablissement eta, bg_ref_etablissement cen, bg_notes_performance perf, bg_ref_sexe sex
		WHERE can.annee=$annee AND rep.annee=$annee AND perf.annee=$annee AND can.num_table=perf.num_table AND can.num_table=rep.num_table AND can.etablissement=eta.id
		AND rep.id_centre=cen.id AND can.serie=ser.id AND sex.id=can.sexe AND perf.id_atelier=$a $andWhere $andWhereAtelier
		ORDER BY can.num_table ";
} elseif ($t == 'p') {
    for ($a = 1; $a <= 7; $a++) {
        $atelier = selectReferentiel($lien, 'atelier', $a);
        $cols[$a] = strtoupper($atelier);
    }
    $cols['note'] = 'NOTE';
    $nomTableau = "PERFORMANCES ET NOTES DES CANDIDATS PAR ATELIER";
    $sql = "SELECT nom, prenoms, can.num_table, ser.serie, sex.sexe, sex.id as id_sexe
		FROM bg_candidats can, bg_ref_serie ser, bg_repartition rep, bg_ref_etablissement eta, bg_ref_etablissement cen, bg_ref_sexe sex
		WHERE can.annee=$annee AND rep.annee=$annee AND can.num_table=rep.num_table AND can.etablissement=eta.id AND can.sexe=sex.id
		AND rep.id_centre=cen.id AND can.serie=ser.id $andWhere
		ORDER BY can.num_table ";
}

$result = bg_query($lien, $sql, __FILE__, __LINE__);

$pdf->ezSetY($height - 25);
if ($t == 'p' || ($t == 'l' && $a == 8)) {
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
        $numero = '';
    }

    $data[$i] = array('N°' => $i, 'Num' => $numero, 'Candidat' => $candidat, 'Serie' => $serie, 'sex' => $sexe);
    // $data[$i] = array('N°' => $num_table, 'Candidat' => $candidat, 'Serie' => $serie, 'sex' => $sexe);
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
    for ($i == 0; $i < 6; $i++) {
        $data[$i] = array('N°' => $i, 'Candidat' => $noms, 'Serie' => '', 'sex' => '');
    }
}

$pdf->ezTable($data, $cols, $titre_tab, $options);

//$pdf->ezInsertMode(1,1,'before');
$pdf->ezInsertMode(0);
$pdfG = "listeEPS_" . getRewriteString($centre) . "_" . getRewriteString($etablissement) . "_" . $serie . "_" . getRewriteString($atelier);

$title = "Liste EPS_" . getRewriteString($centre) . "_" . getRewriteString($etablissement) . "_" . $serie . "_" . getRewriteString($atelier);
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

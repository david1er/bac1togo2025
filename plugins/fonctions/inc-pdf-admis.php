<?php
include_once 'generales.php';
$lien = lien();

//Recuperation du timbre
  
$sql_timbre = "SELECT ministere, secretariat_general, direction, libelle_examen FROM bg_timbre WHERE 1";
$result_timbre = bg_query($lien, $sql_timbre, __FILE__, __LINE__);
$row_timbre = mysqli_fetch_array($result_timbre);

$ministere = $row_timbre['ministere'];
$secretariat_general = $row_timbre['secretariat_general'];
$direction = $row_timbre['direction'];
$libelle_examen = $row_timbre['libelle_examen'];

foreach ($_REQUEST as $key => $val) {
    $$key = mysqli_real_escape_string($lien, trim(addslashes($val)));
}

if ($annee == '') {
    $annee = recupAnnee($lien); //$annee = date("Y");
}

$pdf_dir = '../ezpdf/';
include $pdf_dir . 'class.ezpdf.php'; // inclusion du code de la bibliothque
$pdf = new Cezpdf('A3', 'landscape'); // 595.28 x 841.89
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
$pdf->ezStartPageNumbers(570, 15, 8, 'left', "$DateConvocPied REPUBLIQUE TOGOLAISE - DIRECTION DES EXAMENS, CONCOURS ET CERTIFICATIONS - Page {PAGENUM} sur {TOTALPAGENUM}");
//$pdf->ezStartPageNumbers(50,15,7,'right',"$DateConvocPied REPUBLIQUE TOGOLAISE - OFFICE DU BACCALAUREAT - Page {PAGENUM} sur {TOTALPAGENUM}");

$options = array(
    'spacing' => 1.5,
    'justification' => 'full',
    'titleFontSize' => 14,
    'fontSize' => 14,
);

if (isset($_GET['id_centre'])) {
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
    $pdf->addJpegFromFile("../images/logo.png", 575, 750, 45);
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

    if ($tache == 'classement_candidats') {
        $andWhere .= " AND (delib1='Passable' OR delib1='Abien' OR delib1='Bien' OR delib1='TBien') ";
        $limit = " LIMIT 0, 2000 ";
        $andTitre = " PAR ORDRE DE MERITE";
        $orderBy = 'moyenne DESC';
        // } else if ($tache == 'classement_jury') {
        //     $andWhere .= " AND (delib1='Passable' OR delib1='Abien' OR delib1='Bien' OR delib1='TBien') ";
        //     $limit = " LIMIT 0, 2000 ";
        //     $andTitre = " PAR ORDRE DE MERITE";
        //     $orderBy = 'moyenne DESC';
    } else {
        $andWhere .= " AND ((delib1='Passable' OR delib1='Abien' OR delib1='Bien' OR delib1='TBien') OR (delib1='Oral' AND delib2='Passable'))";
        $andTitre = " DES ADMIS";
        $orderBy = 'can.nom, can.prenoms';
    }

    $sql = "SELECT can.num_table, can.nom, can.prenoms, can.ddn, can.ldn, can.pdn, eta.etablissement, cent.etablissement as centre,
		ser.serie, rep.jury, lang.langue_vivante, ef1.epreuve_facultative_a, ef2.epreuve_facultative_b, spo.eps,
		res.moyenne, res.moyenne2, res.delib1, res.delib2
		FROM bg_repartition rep, bg_ref_region reg, bg_ref_prefecture pref, bg_ref_ville vil,
		bg_ref_etablissement eta, bg_ref_etablissement cent, bg_ref_sexe sex,
		bg_ref_inspection insp, bg_ref_serie ser, bg_ref_eps spo, bg_resultats res, bg_candidats can
		LEFT JOIN bg_ref_langue_vivante lang ON can.lv2=lang.id
		LEFT JOIN bg_ref_epreuve_facultative_a ef1 ON can.efa=ef1.id
		LEFT JOIN bg_ref_epreuve_facultative_b ef2 ON can.efb=ef2.id
		WHERE can.annee=$annee AND rep.annee=$annee AND res.annee=$annee
		AND can.num_table=rep.num_table AND rep.id_centre=cent.id AND res.num_table=can.num_table
		AND can.etablissement=eta.id AND eta.id_ville=vil.id AND vil.id_prefecture=pref.id AND pref.id_region=reg.id
		AND eta.id_inspection=insp.id AND can.serie=ser.id AND can.eps=spo.id AND can.sexe=sex.id
		$andWhere
		ORDER BY $orderBy
		$limit ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $tab = array('num_table', 'nom', 'prenoms', 'ddn', 'ldn', 'pdn', 'etablissement', 'centre', 'serie', 'jury', 'moyenne', 'sexe',
        'moyenne2', 'delib1', 'delib2');

    $cols = array('id' => utf8_decode('N°'), 'num' => utf8_decode('N° DE TABLE'), 'noms' => 'CANDIDAT', 'dldn' => 'DATE ET LIEU DE NAISSANCE', 'eta' => 'ETABLISSEMENT', 'serie' => 'SERIE', 'moyenne' => 'MOYENNE', 'mention' => 'MENTION');
    $i = 1;

    while ($row = mysqli_fetch_array($result)) {
        foreach ($tab as $val) {
            $$val = utf8_decode($row[$val]);
        }

        if ($delib1 == 'Oral') {$delib1 = $delib2;
            $moyenne = $moyenne2;}
        if ($sexe == 'M') {
            $civ = 'M.';
            $e = '';
        } else {
            $civ = 'Mlle';
            $e = 'e'; // pour mettre des mots au fminin si c'est une candidate
        }
        $candidat = "$civ <b>$nom</b> $prenoms";
        $ddn = afficher_date($ddn);
        $dldn = $ddn . utf8_decode(html_entity_decode(" à ")) . $ldn . '(' . selectReferentiel($lien, 'pays', $pdn) . ')';
        $data[] = array('id' => $i, 'num' => $num_table, 'noms' => $candidat, 'dldn' => $dldn, 'eta' => $etablissement, 'serie' => $serie, 'moyenne' => $moyenne, 'mention' => $delib1);
        $i++;
    }

    $titre_tab = html_entity_decode("<b>LISTE $andTitre || CENTRE : " . strtoupper($centre) . " </b>");
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

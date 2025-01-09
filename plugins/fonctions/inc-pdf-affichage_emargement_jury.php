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
$DateConvocPied = "IMPRIME LE " . date('d') . '/' . date('m') . '/' . date('Y');

$options = array(
    'spacing' => 1,
    'justification' => 'full',
    'titleFontSize' => 11,
    'fontSize' => 10,
    'maxWidth' => 790,
    'cols' => array(
        'noms' => array('justification' => 'left', 'width' => 170),
        'dldn' => array('justification' => 'left', 'width' => 150),
        'eta' => array('justification' => 'left', 'width' => 110),
        'sexe' => array('sexe' => 'left', 'width' => 20),
        'serie' => array('sexe' => 'left', 'width' => 30),
    ),
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

    if ($tache == 'affichage_jury') {
        $orderBy = 'ser.serie, rep.numero, can.nom ';
        $andTitre = " D'AFFICHAGE";
        $cols = array('num' => utf8_decode('N° TABLE'), 'noms' => 'CANDIDAT', 'dldn' => 'DATE ET LIEU DE NAISSANCE', 'sexe' => 'SEXE', 'eta' => 'ETABLISSEMENT', 'serie' => 'SERIE', 'jr' => 'JURY', 'eps' => 'EPS', 'lv2' => 'LV2', 'efa' => 'EF. A', 'efb' => 'EF. B');
    } elseif ($tache == 'emargement_jury') {
        $orderBy = 'rep.numero';
        $andTitre = " D'EMARGEMENT";
        $cols = array('num' => utf8_decode('N° TABLE'), 'noms' => 'CANDIDAT', 'dldn' => 'DATE ET LIEU DE NAISSANCE', 'sexe' => 'SEXE', 'eta' => 'ETABLISSEMENT', 'serie' => 'SERIE', 'jr' => 'JURY', 'eps' => 'EPS', 'lv2' => 'LV2', 'efa' => 'EF. A', 'efb' => 'EF. B', 'sig' => '     EMARGEMENT   ');
    } else {
        $orderBy = 'rep.numero';
        $andTitre = " D'EMARGEMENT";
        $cols = array('num' => utf8_decode('N° TABLE'), 'noms' => 'CANDIDAT', 'dldn' => 'DATE ET LIEU DE NAISSANCE', 'sexe' => 'SEXE', 'emarg1' => '1ERE DEMI JOURNEE', 'emarg2' => '2IEME DEMI JOURNEE', 'emarg3' => '3IEME DEMI JOURNEE', 'emarg4' => '4IEME DEMI JOURNEE', 'emarg5' => '5IEME DEMI JOURNEE', 'emarg6' => '6IEME DEMI JOURNEE');
    }

    $sql = "SELECT can.num_table, can.nom, can.prenoms, can.ddn, can.ldn, can.pdn, eta.etablissement, cent.etablissement as centre, spo.id as id_eps,
		can.sexe, ser.serie, rep.jury, lang.langue_vivante, ef1.epreuve_facultative_a, ef2.epreuve_facultative_b, spo.eps, can.atelier1, can.atelier2, can.atelier3
		FROM bg_repartition rep, bg_ref_region reg, bg_ref_prefecture pref, bg_ref_ville vil,
		bg_ref_etablissement eta, bg_ref_etablissement cent,
		bg_ref_inspection insp, bg_ref_serie ser, bg_ref_eps spo, bg_candidats can
		LEFT JOIN bg_ref_langue_vivante lang ON can.lv2=lang.id
		LEFT JOIN bg_ref_epreuve_facultative_a ef1 ON can.efa=ef1.id
		LEFT JOIN bg_ref_epreuve_facultative_b ef2 ON can.efb=ef2.id
		WHERE can.annee=$annee AND rep.annee=$annee AND can.num_table=rep.num_table AND rep.id_centre=cent.id
		AND can.etablissement=eta.id AND eta.id_ville=vil.id AND vil.id_prefecture=pref.id AND pref.id_region=reg.id
		AND eta.id_inspection=insp.id AND can.serie=ser.id AND can.eps=spo.id AND can.statu=1
		$andWhere
		ORDER BY $orderBy ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $tab = array('num_table', 'nom', 'prenoms', 'ddn', 'ldn', 'pdn', 'etablissement', 'centre', 'serie', 'jury', 'langue_vivante', 'sexe',
        'epreuve_facultative_a', 'epreuve_facultative_b', 'eps', 'atelier1', 'atelier2', 'atelier3', 'id_eps');

    while ($row = mysqli_fetch_array($result)) {
        foreach ($tab as $val) {
            $$val = $row[$val];
        }

        if ($sexe == '2') {
            $civ = 'M.';
            $sex = 'M';
            $e = '';
        } else {
            $civ = 'Mlle';
            $sex = 'F';
            $e = 'e'; // pour mettre des mots au fminin si c'est une candidate
        }
        $candidat = "<b>$nom</b> $prenoms";
        $ddn = afficher_date($ddn);
        $dldn = $ddn . html_entity_decode(" à ") . $ldn;
        if ($pdn != 1) {
            $dldn .= '(' . selectReferentiel($lien, 'pays', $pdn) . ')';
        }

        if ($id_eps == 1) {
            $atelier = '(' . substr(selectReferentiel($lien, 'atelier', $atelier1), 0, 1) . substr(selectReferentiel($lien, 'atelier', $atelier2), 0, 1) . substr(selectReferentiel($lien, 'atelier', $atelier3), 0, 1) . ')';
        } else {
            $atelier = '';
        }

        $eps .= ' ' . $atelier;
        $data[$jury][] = array('num' => $num_table, 'noms' => utf8_decode($candidat), 'dldn' => utf8_decode($dldn), 'sexe' => $sex, 'eta' => utf8_decode($etablissement), 'serie' => $serie, 'jr' => $jury, 'eps' => $eps, 'lv2' => $langue_vivante, 'efa' => utf8_decode($epreuve_facultative_a), 'efb' => utf8_decode($epreuve_facultative_b));
    }

    $pdf->ezStartPageNumbers(790, 15, 10, 'left', "$DateConvocPied");
    $pdf->ezStartPageNumbers(470, 15, 10, 'left', "Page {PAGENUM} sur {TOTALPAGENUM}");
    $pdf->ezStartPageNumbers(15, 15, 10, 'right', "Centre d'ecrit : $centre");

    $titre_tab = utf8_decode(html_entity_decode("<b>LISTE $andTitre || CENTRE : " . strtoupper($centre) . " </b>"));
    //$pdf->ezTable($data,$cols,$titre_tab,$options);
    foreach ($data as $jury => $data2) {
        $pdf->ezSetY($height - 25);
        $pdf->addJpegFromFile("../images/logo.png", 390, 510, 45);
        $pdf->ezColumnsStart(array('num' => 3, 'gap' => 10));
        $pdf->ezText(utf8_decode("<b>$ministere\n**********\n$secretariat_general\n**********\n$direction</b>"), 9, array('justification' => 'center'));
        $pdf->ezNewPage();
        $pdf->ezNewPage();
        $pdf->ezText(utf8_decode("<b>REPUBLIQUE TOGOLAISE\nTravail - Liberté - Patrie</b>"), 9, array('justification' => 'center'));
        $pdf->ezColumnsStop();

        $pdf->ezSetY($height - 100);
        $pdf->ezText(utf8_decode("<b>$libelle_examen </b>"), 14, array('justification' => 'center'));
        $mois = moisSession($lien, $annee, $jury);
        $pdf->ezText(utf8_decode("<b>Session de $mois $annee\n</b>"), 10, array('justification' => 'center'));

        $pdf->ezTable($data2, $cols, $titre_tab, $options);
        $pdf->ezNewPage();
    }
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

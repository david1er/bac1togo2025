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
$pdf->ezStartPageNumbers(570, 15, 8, 'left', "$DateConvocPied REPUBLIQUE TOGOLAISE - DIRECTION DES EXAMENS, CONCOURS ET CERTIFICATIONS - Page {PAGENUM} sur {TOTALPAGENUM}");
//$pdf->ezStartPageNumbers(50,15,7,'right',"$DateConvocPied REPUBLIQUE TOGOLAISE - OFFICE DU BACCALAUREAT - Page {PAGENUM} sur {TOTALPAGENUM}");

$options = array(
    'spacing' => 1.5,
    'justification' => 'full',
    'titleFontSize' => 14,
    'fontSize' => 12,
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
        $andTitre .= ' || EF: ' . selectReferentiel($lien, 'epreuve_facultative_a', $id_fac);
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

    // $pdf->ezText(utf8_decode("<b>$andTitre</b>"), 10, array('justification' => 'center'));

    $sql = "SELECT reg.region, cent.id as id_centre, ser.serie, rep.jury, cent.etablissement as centre,
			min(rep.numero) as mini_numero, max(rep.numero) as maxi_numero, count(*) as nbre
			FROM bg_repartition rep, bg_ref_region reg, bg_ref_prefecture pref, bg_ref_ville vil,
			bg_ref_etablissement eta, bg_ref_etablissement cent,
			bg_ref_inspection insp, bg_ref_serie ser, bg_ref_eps spo, bg_candidats can
			LEFT JOIN bg_ref_langue_vivante lang ON can.lv2=lang.id
			LEFT JOIN bg_ref_epreuve_facultative_a ef1 ON can.efa=ef1.id
			LEFT JOIN bg_ref_epreuve_facultative_b ef2 ON can.efb=ef2.id
			WHERE can.annee=$annee AND rep.annee=$annee AND can.num_table=rep.num_table AND rep.id_centre=cent.id
			AND cent.id_ville=vil.id AND vil.id_prefecture=pref.id AND pref.id_region=reg.id
			AND eta.id_inspection=insp.id AND can.serie=ser.id AND can.eps=spo.id
			AND eta.id=can.etablissement
			$andWhere
			GROUP BY reg.region, cent.id, rep.jury, ser.serie
			ORDER BY rep.jury ";

    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $tab = array('region', 'id_centre', 'serie', 'jury', 'nbre', 'mini_numero', 'maxi_numero', 'centre');
    while ($row = mysqli_fetch_array($result)) {
        foreach ($tab as $val) {
            $$val = utf8_decode($row[$val]);
        }

        $tJurys[] = $jury;
        $tabJurys[$id_centre][$jury]['region'] = $region;
        $tabJurys[$id_centre][$jury]['centre'] = $centre;
        $tabJurys[$id_centre][$jury]['serie'] = $serie;
        $tabJurys[$id_centre][$jury]['mini'] = $mini_numero;
        $tabJurys[$id_centre][$jury]['maxi'] = $maxi_numero;
        $tabJurys[$id_centre][$jury]['nbre'] = $nbre;
        $tabJurys[$id_centre][$jury]['jury'] = $jury;
    }

    $cols = array('jury' => 'JURY', 'serie' => 'SERIE', 'nbre' => 'EFFECTIF', 'mini' => 'PREMIER NUMERO', 'maxi' => 'DERNIER NUMERO');
    foreach ($tabJurys as $id_centre => $data) {
        $pdf->ezSetY($height - 25);
        $pdf->addJpegFromFile("../images/logo.png", 270, 760, 45);
        $pdf->ezColumnsStart(array('num' => 3, 'gap' => 10));
        $pdf->ezText(utf8_decode("<b>$ministere\n**********\n$secretariat_general\n**********\n$direction</b>"), 9, array('justification' => 'center'));
        $pdf->ezNewPage();
        $pdf->ezNewPage();
        $pdf->ezText(utf8_decode("<b>REPUBLIQUE TOGOLAISE\nTravail - Liberté - Patrie</b>"), 9, array('justification' => 'center'));
        $pdf->ezColumnsStop();

        $pdf->ezSetY($height - 140);
        $pdf->ezText(utf8_decode("<b>$libelle_examen </b>"), 14, array('justification' => 'center'));
        $mois = moisSession($lien, $annee, $jury);
        $pdf->ezText(utf8_decode("<b>Session de $mois $annee\n</b>"), 10, array('justification' => 'center'));
        $pdf->ezText(utf8_decode("<b>POINT DES JURYS\n$andTitre\n</b>"), 12, array('justification' => 'center'));

        $sql2 = "SELECT jury FROM bg_repartition WHERE annee=$annee AND id_centre=$id_centre GROUP BY jury";
        $result2 = bg_query($lien, $sql2, __FILE__, __LINE__);
        $nbJury = mysqli_num_rows($result2);

        $titre_tab = 'CENTRE : ' . utf8_decode(selectReferentiel($lien, 'etablissement', $id_centre)) . " ($nbJury JURYS)";
        $pdf->ezTable($data, $cols, $titre_tab, $options);
        $pdf->ezNewPage();
    }

}

$pdfG = "pointJury_" . $annee;
$pdf->ezInsertMode(1, 1, 'before');
$pdf->ezInsertMode(0);

$title = "jurys_" . $annee;
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

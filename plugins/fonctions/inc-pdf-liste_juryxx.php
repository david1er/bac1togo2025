<?php
session_start();
include_once 'generales.php';
$lien = lien();
$statut = getStatutUtilisateur();

foreach ($_REQUEST as $key => $val) {
    $$key = mysqli_real_escape_string($lien, trim(addslashes($val)));
}

if ($annee == '') {
    $annee = recupAnnee($lien); //$annee = date("Y");
}

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
$pdf->ezSetMargins(30, 50, 50, 30);
setlocale(LC_TIME, 'fr_FR', 'fr_BE.UTF8', 'fr_FR.UTF8');
$sDateConvoc = utf8_decode(strftime("%A %d %B %Y", mktime(0, 0, 0, date('m'), date('d'), date('Y'))));
$DateConvocPied = "IMPRIME LE " . date('d') . '/' . date('m') . '/' . date('Y');
$pdf->ezStartPageNumbers(10, 20, 8, 'right', "REPUBLIQUE TOGOLAISE - DIRECTION DES EXAMENS, CONCOURS ET CERTIFICATIONS - $DateConvocPied - PAGE {PAGENUM} sur {TOTALPAGENUM}");

$options = array('maxWidth' => 500);
$pdf->ezSetY($height - 25);
$pdf->addJpegFromFile("../images/logo.png", 275, 750, 45);
$pdf->ezColumnsStart(array('num' => 3, 'gap' => 10));
$pdf->ezText(utf8_decode("<b>MINISTERE DE L'ENSEIGNEMENT SUPERIEUR\nET DE LA RECHERCHE\n**********\nOFFICE DU BACCALAUREAT</b>"), 9, array('justification' => 'center'));
$pdf->ezNewPage();
$pdf->ezNewPage();
$pdf->ezText(utf8_decode("<b>REPUBLIQUE TOGOLAISE\nTravail - Liberté - Patrie</b>"), 9, array('justification' => 'center'));
$pdf->ezColumnsStop();

$pdf->ezSetY($height - 100);
$pdf->ezText(utf8_decode("<b>BACCALAUREAT PREMIERE PARTIE (BAC 1)</b>"), 11, array('justification' => 'center'));
$mois = moisSession2($lien, $annee);
$pdf->ezText(utf8_decode("<b>Session de $mois $annee\n</b>"), 10, array('justification' => 'center'));

$texte = "SOUS RESERVES D'ULTIMES VERIFICATIONS ET CONTROLES \nLES CANDIDATS SUIVANTS SONT \n ";

if ($jury > 0) {
    $entete = array('numero' => "NUMERO", 'candidat' => "CANDIDAT", 'etablissement' => "ETABLISSEMENT DE PROVENANCE", 'delib' => "DECISION");
    if ($type == 1) {
        $andWhere = " AND (delib1='Passable' OR delib1='Abien' OR delib1='Bien' OR delib1='TBien')";
        $titre = "$texte ADMIS A LA PREMIERE DELIBERATION ";
    } elseif ($type == 2) {
        $andWhere = " AND delib1='Oral' ";
        $titre = "$texte ADMISSIBLES A LA PREMIERE DELIBERATION";
    } elseif ($type == 3) {
        $andWhere = " AND (delib1='Absent' OR delib1='Abandon') ";
        $titre = "LISTE DES ABSENTS / ABANDONS ";
    } elseif ($type == 4) {
        $andWhere = " AND (delib1='Ajourne') ";
        $titre = "CANDIDATS AJOURNES";
    } elseif ($type == 5) {
        $andWhere = " AND delib1='Oral' AND (delib2='Passable' OR delib2='ABien') ";
        $titre = "$texte ADMIS A LA DEUXIEME DELIBERATION";
    } elseif ($type == 6) {
        $andWhere = " AND delib1='Oral' AND delib2='Reserve' ";
        $titre = "CANDIDATS RESERVES ";
    } elseif ($type == 7) {
        $andWhere = " AND delib1='Oral' AND delib2='Refuse' ";
        $titre = "CANDIDATS REFUSES (DEUXIEME DELIBERATION)";
    } elseif ($type == 0) {
        $andWhere = " AND (delib1='Passable' OR delib1='Abien' OR delib1='Bien' OR delib1='TBien' OR delib1='Oral')";
        $titre = "$texte ADMIS ET ADMISSIBLES A LA PREMIERE DELIBERATION ";
    } elseif ($type == 8) {
        $andWhere = " AND (delib1='Passable' OR delib1='Abien' OR delib1='Bien' OR delib1='TBien')";
        $titre = "$texte ADMIS A LA PREMIERE DELIBERATION ";
    } elseif ($type == 9) {
        $andWhere = " AND delib1='Oral'";
        $titre = "$texte ADMISSIBLES A LA PREMIERE DELIBERATION ";
    } else {
        die('Erreur');
    }

    $pdf->ezText(utf8_decode("\n<b>$titre</b>\n"), 13, array('justification' => 'center'));

    if ($type == 0 || $type == 8 || $type == 9) {
        $sql_ss = "SELECT id_type_session FROM bg_resultats WHERE annee=$annee AND jury='$jury' LIMIT 0,1 ";
        $result_ss = bg_query($lien, $sql_ss, __FILE__, __LINE__);
        $row_ss = mysqli_fetch_array($result_ss);
        $id_type_session = $row_ss['id_type_session'];

        $param = selectCodeAno($lien, $annee, $id_type_session);
        $sql = "SELECT can.nom, can.prenoms, can.sexe, can.num_table, ser.serie, can.ddn, can.ldn, pay.pays, cen.etablissement as centre, rep.jury, res.moyenne, delib1, delib2, eta.etablissement
				FROM bg_candidats can, bg_repartition rep, bg_ref_serie ser, bg_ref_pays pay, bg_ref_etablissement cen, bg_resultats res, bg_ref_etablissement eta
				WHERE can.annee=$annee AND rep.annee=$annee AND res.annee=$annee AND eta.id=can.etablissement
				AND can.num_table=rep.num_table AND res.id_anonyme=rep.id_anonyme AND can.serie=ser.id AND can.pdn=pay.id
				AND cen.id=rep.id_centre AND rep.jury=$jury $andWhere
				ORDER BY ser.serie, can.num_table";
        $pdf->setEncryption("$param", "$param", array('copy', 'print'));

    } else {
        $sql = "SELECT can.nom, can.prenoms, can.sexe, can.num_table, ser.serie, can.ddn, can.ldn, pay.pays, cen.etablissement as centre, rep.jury, res.moyenne, delib1, delib2, eta.etablissement
				FROM bg_candidats can, bg_repartition rep, bg_ref_serie ser, bg_ref_pays pay, bg_ref_etablissement cen, bg_resultats res, bg_ref_etablissement eta
				WHERE can.annee=$annee AND rep.annee=$annee AND res.annee=$annee AND eta.id=can.etablissement
				AND can.num_table=rep.num_table AND can.num_table=res.num_table AND can.serie=ser.id AND can.pdn=pay.id
				AND cen.id=rep.id_centre AND rep.jury=$jury $andWhere
				ORDER BY ser.serie, can.num_table";
    }
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $tab = array('nom', 'prenoms', 'sexe', 'num_table', 'serie', 'ddn', 'ldn', 'pays', 'etablissement', 'jury', 'moyenne', 'delib1', 'delib2', 'centre');
    while ($row = mysqli_fetch_array($result)) {
        foreach ($tab as $val) {
            $$val = html_entity_decode($row[$val]);
        }

        $tNoms = getNoms($lien, $annee, $num_table);
        $data[$i]['numero'] = $num_table;
        $data[$i]['candidat'] = utf8_decode($tNoms['noms']);
        $data[$i]['etablissement'] = utf8_decode($etablissement);
        $data[$i]['serie'] = $serie;
        if ($type <= 4 || $type == 8 || $type == 9) {
            $data[$i]['delib'] = $delib1;
        } else {
            $data[$i]['delib'] = $delib2;
        }

        $i++;
    }
    if (is_array($data)) {
        $pdf->ezTable($data, $entete, "JURY $jury - SERIE $serie", $options);
    } else {
        $pdf->ezText(utf8_decode("<b>NEANT</b>"), 30, array('justification' => 'center'));
    }

}

$date_delib = dateDeliberation($lien, $annee, $jury);
$president_jury = presidentJurys($lien, $annee, $jury);

$pdf->ezText(utf8_decode("\nLOME, le $date_delib"), 11, array('justification' => 'right'));
$pdf->ezText(utf8_decode("\n\n\n\n<b><u>LE PRESIDENT DU JURY DE DELIBERATION</u></b>\n\n\n\n$president_jury\n"), 11, array('justification' => 'center'));

$pdfG = "liste_jury" . $jury . '_serie' . $serie;
$pdf->ezInsertMode(1, 1, 'before');
$pdf->ezInsertMode(0);

$title = "Listes de candidats jury" . $jury . '_serie' . $serie . '_annee' . $annee;
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

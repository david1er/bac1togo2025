<?php
include 'generales.php';
$lien = lien();

foreach ($_REQUEST as $key => $val) {
    $$key = mysqli_real_escape_string($lien, trim(addslashes($val)));
}

if ($annee == '') {
    $annee = recupAnnee($lien); //$annee = date("Y");
}

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
$pdf->ezStartPageNumbers(570, 15, 8, 'left', "$DateConvocPied - Page {PAGENUM} sur {TOTALPAGENUM}");
$pdf->ezStartPageNumbers(50, 15, 7, 'right', "$DateConvocPied REPUBLIQUE TOGOLAISE - DIRECTION DES EXAMENS, CONCOURS ET CERTIFICATIONS - Page {PAGENUM} sur {TOTALPAGENUM}");

if ($type == 0) {
    $options = array(
        'spacing' => 1,
        'justification' => 'full',
        'titleFontSize' => 12,
        'fontSize' => 8,
        'maxWidth' => 815,
        'cols' => array(
            'dldn' => array('justification' => 'left', 'width' => 150),
        ),
    );
    //$cols=array('N°'=>utf8_decode('N°ENR'),'dos'=>utf8_decode('N°DOS'),'num_table'=>utf8_decode('N°TABLE.'),'noms'=>'CANDIDAT','dldn'=>'DATE ET LIEU DE NAISSANCE','pdn'=>'PAYS','nat'=>'NATIONALITE','serie'=>'SERIE','eps'=>'EPS','lv2'=>'LV2','efa'=>'EF. A','efb'=>'EF. B','tel'=>'TELEPHONES','bac1'=>'BAC I ','sig'=>'EMARGEMENT');
    $cols = array('N°' => utf8_decode('N°ENR'), 'dos' => utf8_decode('N°DOS'), 'num_table' => utf8_decode('N°TABLE.'), 'noms' => 'CANDIDAT', 'dldn' => 'DATE ET LIEU DE NAISSANCE', 'sex' => 'Sexe', 'pdn' => 'PAYS', 'nat' => 'NAT.', 'serie' => 'SERIE', 'eps' => 'EPS', 'lv2' => 'LV2', 'efa' => 'EF. A', 'efb' => 'EF. B', 'sig' => 'EMARGEMENT');
    $order = " ORDER BY ser.serie, can.nom, can.prenoms";
    $titre1 = 'LISTE DES CANDIDATS INSCRITS';
} else {
    $options = array('spacing' => 1, 'justification' => 'full', 'titleFontSize' => 12, 'fontSize' => 9, 'maxWidth' => 815);
    $cols = array('N°' => utf8_decode('N°ENR'), 'noms' => 'CANDIDAT', 'dldn' => 'DATE ET LIEU DE NAISSANCE', 'serie' => 'SERIE', 'annee1' => 'ANNEE BAC I ', 'jury1' => 'JURY BAC I ', 'table1' => utf8_decode('N°TABLE BAC I '), 'obs' => 'OBSERVATIONS');
    $order = " ORDER BY can.annee_bac1, can.jury_bac1, can.num_table_bac1";
    $titre1 = 'CONTROLE DU BAC I';
}

if (isset($_GET['id_etablissement'])) {
    $pdf->ezSetY($height - 25);
    $pdf->addJpegFromFile("../images/logo.png", 390, 520, 45);
    $pdf->ezColumnsStart(array('num' => 3, 'gap' => 10));
    $pdf->ezText(utf8_decode("<b>MINISTERE DES ENSEIGNEMENTS PRIMAIRE,\nSECONDAIRE ET TECHNIQUE\n**********\nSECRETARIAT GENERAL\n**********\nDIRECTION DES EXAMENS, CONCOURS ET CERTIFICATIONS</b>"), 9, array('justification' => 'center'));
    $pdf->ezNewPage();
    $pdf->ezNewPage();
    $pdf->ezText(utf8_decode("<b>REPUBLIQUE TOGOLAISE\nTravail - Liberté - Patrie</b>"), 9, array('justification' => 'center'));
    $pdf->ezColumnsStop();

    $pdf->ezSetY($height - 100);
    $pdf->ezText(utf8_decode("<b>BACCALAUREAT PREMIERE PARTIE (BAC 1)</b>"), 14, array('justification' => 'center'));
    $mois = moisSession($lien, $annee, $jury);
    $pdf->ezText(utf8_decode("<b>Session de $mois $annee\n</b>"), 10, array('justification' => 'center'));
    $pdf->ezText(utf8_decode("<b>$titre1\n</b>"), 13, array('justification' => 'center'));
    if ($id_serie != 0) {
        $andWhere = ' AND can.serie=' . $id_serie;
        $andTitre = '- SERIE ' . selectReferentiel($lien, 'serie', $id_serie);
    }

    $sql = "SELECT can.id_candidat, can.nom, can.prenoms, ser.serie, sex.sexe, can.annee_bac1, can.jury_bac1, can.num_table_bac1, can.num_dossier, can.num_table, atelier1, atelier2, atelier3, "
        . " can.ddn, can.ldn, pay.pays as pdn, nat.pays as nationalite, eta.etablissement, ser.id as id_serie, lv.langue_vivante as lv2, "
        . " can.telephone, vil.ville, spo.eps, fac1.epreuve_facultative_a as efa, fac2.epreuve_facultative_b as efb, spo.id as id_eps "
        . " FROM bg_ref_pays pay, bg_ref_pays nat, bg_ref_sexe sex, bg_ref_serie ser, bg_ref_etablissement eta, bg_ref_ville vil, "
        . " bg_ref_eps spo, "
        . " bg_candidats can "
        . " LEFT JOIN bg_ref_langue_vivante lv ON lv.id=can.lv2 "
        . " LEFT JOIN bg_ref_epreuve_facultative_a fac1 ON fac1.id=can.efa "
        . " LEFT JOIN bg_ref_epreuve_facultative_b fac2 ON fac2.id=can.efb "
        . " WHERE can.annee=$annee AND eta.id = can.etablissement AND can.serie=ser.id AND can.sexe=sex.id "
        . " AND pay.id=can.pdn AND nat.id=can.nationalite AND can.etablissement=eta.id AND vil.id=eta.id_ville AND spo.id=can.eps "
        . " AND eta.id =$id_etablissement "
        . $andWhere
        . $order;
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $colonnes = array('id_candidat', 'nom', 'prenoms', 'serie', 'sexe', 'annee_bac1', 'jury_bac1', 'num_table_bac1', 'ddn', 'ldn', 'pdn', 'nationalite', 'etablissement', 'id_serie', 'lv2', 'telephone', 'ville', 'eps', 'efa', 'efb', 'id_eps', 'num_dossier', 'num_table', 'atelier1', 'atelier2', 'atelier3');

    $i = 0;
    while ($row = mysqli_fetch_array($result)) {
        foreach ($colonnes as $col) {
            $$col = utf8_decode(html_entity_decode(($row[$col])));
        }

        if ($sexe == 'M') {
            $civ = 'M.';
            $e = '';
        } else {
            $civ = 'Mlle';
            $e = 'e'; // pour mettre des mots au fminin si c'est une candidate
        }
        if ($annee_bac1 == '') {
            $annee_bac1 = '';
        }

        if ($jury_bac1 == 0) {
            $jury_bac1 = '';
        }

        if ($num_table_bac1 == '') {
            $num_table_bac1 = '';
        }

        $ddn = afficher_date($ddn);
        $candidat = "<b>$nom</b> $prenoms";
        $dldn = $ddn . utf8_decode(html_entity_decode(" à ")) . $ldn;
        $bac1 = "$annee_bac1 / J $jury_bac1 / $num_table_bac1";

        if ($id_eps == 1) {
            $atelier = substr(selectReferentiel($lien, 'atelier', $atelier1), 0, 1) . substr(selectReferentiel($lien, 'atelier', $atelier2), 0, 1) . substr(selectReferentiel($lien, 'atelier', $atelier3), 0, 1);
        } else {
            $atelier = '';
        }

        $i++;
        $data[$i] = array('N°' => $id_candidat, 'dos' => $num_dossier, 'num_table' => $num_table, 'noms' => $candidat, 'sex' => $sexe, 'dldn' => $dldn, 'pdn' => $pdn, 'nat' => $nationalite, 'serie' => $serie, 'eps' => $eps . ' ' . $atelier, 'lv2' => $lv2, 'efa' => $efa, 'efb' => $efb, 'sig' => '', 'annee1' => $annee_bac1, 'jury1' => $jury_bac1, 'table1' => $num_table_bac1);
    }
    $titre_tab = html_entity_decode("<b>ETABLISSEMENT: " . strtoupper($etablissement) . " $andTitre</b>");
    $pdf->ezTable($data, $cols, $titre_tab, $options);
    $pdf->ezText(utf8_decode("\n<b>$i</b> CANDIDATS AU TOTAL"), 14, array('justification' => 'center'));
}

$pdfG = "liste_des_candidats_" . $id_etablissement;
$pdf->ezInsertMode(1, 1, 'before');
$pdf->ezInsertMode(0);

$title = "Liste des candidats inscrits pour le bac $annee";
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

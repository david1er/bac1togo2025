<?php
include 'generales.php';
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

//Recuperation du centre
$sql10 = "SELECT etablissement, si_centre FROM bg_ref_etablissement WHERE id=$id_centre";
$result10 = bg_query($lien, $sql10, __FILE__, __LINE__);
$sol = mysqli_fetch_array($result10);
$centre = $sol['etablissement'];

echo $centre;
//Fin Recuperation du centre

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
$pdf->ezStartPageNumbers(790, 15, 8, 'left', "$DateConvocPied");
$pdf->ezStartPageNumbers(470, 15, 10, 'left', "Page {PAGENUM} sur {TOTALPAGENUM}");
$pdf->ezStartPageNumbers(15, 15, 8, 'right', "Centre d'ecrit : $centre");

if ($type == 0) {
    $options = array(
        'spacing' => 2,
        'justification' => 'full',
        'titleFontSize' => 12,
        'fontSize' => 10,
        'maxWidth' => 815,
        'cols' => array(
            'dldn' => array('justification' => 'left', 'width' => 150),
        ),
    );
    //$cols=array('N°'=>utf8_decode('N°ENR'),'dos'=>utf8_decode('N°DOS'),'num_table'=>utf8_decode('N°TABLE.'),'noms'=>'CANDIDAT','dldn'=>'DATE ET LIEU DE NAISSANCE','pdn'=>'PAYS','nat'=>'NATIONALITE','serie'=>'SERIE','eps'=>'EPS','lv2'=>'LV2','efa'=>'EF. A','efb'=>'EF. B','tel'=>'TELEPHONES','bac1'=>'BAC I ','sig'=>'EMARGEMENT');
    $cols = array('N°' => utf8_decode('N°'), 'nom' => utf8_decode('Nom'), 'prenoms' => utf8_decode('Prénoms'), 'sexe' => 'Sexe',
        'centre' => utf8_decode('Centre d\'écrit.'), 'serie' => 'SERIE', 'etablissement' => utf8_decode('Etablissement.'),
        'motif' => utf8_decode('Motif'));
    $order = " ORDER BY ser.serie, can.nom, can.prenoms";
    $titre1 = 'DOSSIERS EN INSTANCE DE REJET';
} else {
    $options = array('spacing' => 1, 'justification' => 'full', 'titleFontSize' => 12, 'fontSize' => 9, 'maxWidth' => 815);
    $cols = array('N°' => utf8_decode('N°ENR'), 'noms' => 'CANDIDAT', utf8_decode('dldn') => 'DATE ET LIEU DE NAISSANCE', 'serie' => 'SERIE', 'annee1' => 'ANNEE BAC I ', 'jury1' => 'JURY BAC I ', 'table1' => utf8_decode('N°TABLE BAC I '), 'obs' => 'OBSERVATIONS');
    $order = " ORDER BY can.annee_bac1, can.jury_bac1, can.num_table_bac1";
    $titre1 = 'CONTROLE DU BAC I';
}

if (isset($_GET['id_centre'])) {
    $pdf->ezSetY($height - 25);
    $pdf->addJpegFromFile("../images/logo.png", 390, 510, 45);
    $pdf->ezColumnsStart(array('num' => 3, 'gap' => 10));
    $pdf->ezText(utf8_decode("<b>$ministere\n**********\n$secretariat_general\n**********\n$direction</b>"), 9, array('justification' => 'center'));
    $pdf->ezNewPage();
    $pdf->ezNewPage();
    $pdf->ezText(utf8_decode("<b>REPUBLIQUE TOGOLAISE\nTravail - Liberté - Patrie</b>"), 9, array('justification' => 'center'));
    $pdf->ezColumnsStop();

    $pdf->ezSetY($height - 100);
    $pdf->ezText(utf8_decode("<b>$libelle_examen</b>"), 14, array('justification' => 'center'));
    $mois = moisSession2($lien, $annee);
    $pdf->ezText(utf8_decode("<b>Session de $mois $annee\n</b>"), 10, array('justification' => 'center'));
    $pdf->ezText(utf8_decode("<b>$titre1\n</b>"), 13, array('justification' => 'center'));
    if ($id_serie != 0) {
        $andWhere = ' AND can.serie=' . $id_serie;
        $andTitre = '- SERIE ' . selectReferentiel($lien, 'serie', $id_serie);
    }
    // var_dump($id_centre);
    $sql = "SELECT can.id_candidat, can.nom, can.prenoms, ser.serie, sex.sexe,  can.num_table,can.statu,  "
        . " can.ddn, can.ldn, eta.etablissement as etablissementR,cen.etablissement as centreR, ser.id as id_serie,  mtf.motif as motif,"
        . " can.telephone,  can.motif as mot "
        . " FROM  bg_ref_sexe sex, bg_ref_serie ser, bg_ref_etablissement eta, bg_ref_etablissement cen,   "
        . " bg_ref_motif mtf, "
        . " bg_candidats can "
        . " WHERE can.annee=$annee AND cen.id = can.centre AND can.serie=ser.id AND can.sexe=sex.id "
        . " AND   can.centre=cen.id  AND can.etablissement=eta.id  "
        . " AND can.statu=0 AND can.motif=mtf.id AND cen.id =$id_centre "
        . $andWhere
        . $order;
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $colonnes = array('id_candidat', 'motif', 'nom', 'prenoms', 'serie', 'sexe', 'etablissementR', 'centreR', 'id_serie', 'telephone', 'ville', 'num_dossier', 'num_table', 'atelier1', 'atelier2', 'atelier3', 'statu');

    $i = 0;
    while ($row = mysqli_fetch_array($result)) {
        /* foreach($colonnes as $col) $$col=utf8_decode(html_entity_decode(($row[$col]))); */
        foreach ($colonnes as $col) {
            $$col = $row[$col];
        }

        if ($sexe == 'M') {
            $civ = 'M.';
            $e = '';
        } else {
            $civ = 'Mlle';
            $e = 'e'; // pour mettre des mots au fminin si c'est une candidate
        }

        $ddn = afficher_date($ddn);
        // $candidat = "<b>$nom</b> $prenoms";

        $i++;
        $data[$i] = array('N°' => $i, 'nom' => utf8_decode($nom), 'prenoms' => utf8_decode($prenoms), 'sexe' => $sexe, 'centre' => utf8_decode($centreR), 'serie' => $serie, 'etablissement' => utf8_decode($etablissementR), 'motif' => utf8_decode($motif));
    }
    $espace = html_entity_decode("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
    $espace1 = html_entity_decode("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
    $espace2 = html_entity_decode("&nbsp;&nbsp;&nbsp;");
    $espace3 = html_entity_decode("&nbsp;");
    $titre_tab = html_entity_decode("<b>ETABLISSEMENT: " . strtoupper($etablissement) . " $andTitre</b>");
    $pdf->ezText(utf8_decode("<b>CENTRE D'ECRIT: $centre\n</b>"), 13, array('justification' => 'center'));
    $pdf->ezTable($data, $cols, '', $options);
    $pdf->ezText(utf8_decode("\nArrêté la présente liste à <b>$i</b> candidat(s) rejeté(s)"), 13, array('justification' => 'center'));

    $date_impression = afficher_date(date('Y-m-d'));

    $pdf->ezText(utf8_decode("\n\n NB:les candidats sont priés de régulariser leurs dossiers avec :$espace1 $espace1 $espace2 Fait à Lomé, le ..............................................."), 11, $options2);
    $pdf->ezText(utf8_decode(" <b> -La fiche de rejet portant leur nom :</b> $espace1 $espace1 $espace1 $espace1 $espace3 $espace2 <b>Le Directeur des Examens,Concours et Certifications</b> "), 11, $options2);
    $pdf->ezText(utf8_decode(" <b> -Une copie du relevé de notes et l'original:</b> "), 11, $options2);
    $pdf->ezText(utf8_decode(" <b> -Une copie de naissance :</b> \n\n"), 11, $options2);
    $pdf->ezText(utf8_decode(" $espace1 $espace1 $espace1 $espace1 $espace1 $espace1 $espace1 $espace2 $espace2 $espace3 $espace2  <b>Tei Mani PEREZI</b>"), 11, $options2);
    $pdf->ezColumnsStop();
}

$pdfG = "Liste_candidat_instance_rejet" . $id_centre;
$pdf->ezInsertMode(1, 1, 'before');
$pdf->ezInsertMode(0);

$title = "Liste des candidats en instance de rejet $annee";
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

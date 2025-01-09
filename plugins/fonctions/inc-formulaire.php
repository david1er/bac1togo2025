<?php
include_once 'generales.php';
$lien = lien();

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
setlocale(LC_TIME, 'fr_FR', 'fr_BE.UTF8', 'fr_FR.UTF8');
$sDateConvoc = utf8_decode(strftime("%A %d %B %Y", mktime(0, 0, 0, date('m'), date('d'), date('Y'))));
$DateConvocPied = "IMPRIME LE " . date('d') . '/' . date('m') . '/' . date('Y');
$pdf->ezStartPageNumbers(570, 10, 8, 'left', "$DateConvocPied ");

if (isset($_GET['id_etablissement'])) {
    if ($id_serie != 0) {
        $andWhere = ' AND can.serie=' . $id_serie;
    }

    $sql = "SELECT can.id_candidat, can.nom, can.prenoms, ser.serie, sex.sexe, can.annee_bac1, can.jury_bac1, can.num_table_bac1, can.nom_photo, "
        . " can.num_dossier, can.atelier1, can.atelier2, can.atelier3, "
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
        . " ORDER BY ser.serie, can.nom, can.prenoms ";

    $result = bg_query($lien, $sql, __FILE__, __LINE__);
//    echo $sql;

    $colonnes = array('id_candidat', 'nom', 'prenoms', 'serie', 'sexe', 'annee_bac1', 'nom_photo', 'num_dossier', 'atelier1', 'atelier2', 'atelier3', 'jury_bac1', 'num_table_bac1', 'ddn', 'ldn', 'pdn', 'nationalite', 'etablissement', 'id_serie', 'lv2', 'telephone', 'ville', 'eps', 'efa', 'efb', 'id_eps');
    while ($row = mysqli_fetch_array($result)) {
        foreach ($colonnes as $col) {
            $$col = html_entity_decode($row[$col]);
        }

        if ($sexe == 'M') {
            $civ = 'M.';
            $e = '';
        } else {
            $civ = 'Mlle';
            $e = 'e'; // pour mettre des mots au fminin si c'est une candidate
        }
        if ($annee_bac1 == '') {
            $annee_bac1 = '**********';
        }

        if ($jury_bac1 == 0) {
            $jury_bac1 = '**********';
        }

        if ($num_table_bac1 == '') {
            $num_table_bac1 = '**********';
        }

        $ddn = afficher_date($ddn);

        $candidat = utf8_decode(html_entity_decode("$civ <b>$nom</b> $prenoms, ne$e $ddn à $ldn"));
        $can = html_entity_decode("$civ <b>$nom</b> $prenoms, né$e $ddn à $ldn");

        if ($iPage > 0) {$iPage = $pdf->newPage();} else {
            $iPage = 1;
        }

        $pdf->addDestination($iPage, 'FitH');
        $sommaire .= "<c:ilink:" . getRewriteString($iPage) . ">$id_candidat - $candidat, $serie</c:ilink>\n";

        $pdf->ezSetY($height - 25);
        $pdf->addJpegFromFile("../images/logo.png", 270, 750, 45);
        $pdf->rectangle(20, 20, $width - 35, $height - 40);
        $pdf->ezColumnsStart(array('num' => 3, 'gap' => 10));
        $pdf->ezText(utf8_decode("<b>MINISTERE DES ENSEIGNEMENTS PRIMAIRE,\nSECONDAIRE ET TECHNIQUE\n**********\nSECRETARIAT GENERAL\n**********\nDIRECTION DES EXAMENS, CONCOURS ET CERTIFICATIONS</b>"), 9, array('justification' => 'center'));
        $pdf->ezNewPage();
        $pdf->ezNewPage();
        $pdf->ezText(utf8_decode("<b>REPUBLIQUE TOGOLAISE\nTravail - Liberté - Patrie</b>"), 9, array('justification' => 'center'));
        $pdf->ezColumnsStop();

        $pdf->ezSetY($height - 90);
        $pdf->ezText(utf8_decode("<b>BACCALAUREAT PREMIERE PARTIE (BAC 1)</b>"), 11, array('justification' => 'center'));
        $pdf->ezText(utf8_decode("N° Dossier: <b>$num_dossier</b> / N° d'inscription: <b>$id_candidat</b>"), 10, array('justification' => 'right'));
        $pdf->ezText(utf8_decode("<b><u>FORMULAIRE OBLIGATOIRE </u>\nAnnée: $annee</b>"), 16, array('justification' => 'center'));

        $pdf->rectangle(25, 630, 110, 130);
        $lien_photos = "../../photos/$annee/";
        if ($nom_photo != '') {
            $pdf->addJpegFromFile($lien_photos . $nom_photo, 25, 630, 110);
        }

        $pdf->addText(40, 710, 10, utf8_decode("Coller ici votre"));
        $pdf->addText(40, 695, 10, utf8_decode("photo d'identité"));
        $pdf->addText(40, 680, 10, utf8_decode("(3,5cm x 4,5cm)"));

        $pdf->ezText(utf8_decode("\nCENTRE D'EXAMEN: ...........................................................\n"), 11, array('justification' => 'center'));
        $pdf->ezText(utf8_decode("<b>SERIE : $serie</b>"), 18, array('justification' => 'center'));

        $pdf->rectangle(20, 607, $width - 35, 18);
        $pdf->line(80, 607, 80, 625);
        $pdf->ezText(utf8_decode("<b>BAC I  </b>      Année d'obtention : <b>$annee_bac1</b>      Numéro de jury : <b>$jury_bac1</b>       Numéro de table : <b>$num_table_bac1</b>"), 11, array('justification' => 'left', 'spacing' => 1.5));
        $tNoms = getNoms($lien, $annee, '', $id_candidat);
        //print_r($tNoms);
        $noms = $tNoms['noms'];
        $pdf->ezText(utf8_decode("<b>I - CANDIDAT</b>"), 14, array('justification' => 'center', 'spacing' => 1.5));
        $pdf->ezText(utf8_decode("Nom et Prénoms: <b>$noms</b>"), 12, array('justification' => 'left', 'spacing' => 1.5));
        $pdf->ezText(utf8_decode("Date et lieu de naissance : <b>$ddn</b> à <b>$ldn</b>"), 12, array('justification' => 'left', 'spacing' => 1.5));
        $pdf->ezText(utf8_decode(html_entity_decode("Pays de naissance : <b>$pdn</b>            Nationalité : <b>$nationalite</b>")), 12, array('justification' => 'left', 'spacing' => 1.5));
        $pdf->ezText(utf8_decode("Lycée ou Collège : <b>$etablissement</b>"), 12, array('justification' => 'left', 'spacing' => 1.5));
        $pdf->ezText(utf8_decode("Contacts téléphoniques : <b>$telephone</b>"), 12, array('justification' => 'left', 'spacing' => 1.5));

        $pdf->line(20, 477, $width - 18, 477);
        $pdf->ezText(utf8_decode("<b>II - LANGUES VIVANTES, EPREUVES FACULTATIVES ET D'E.P.S.</b>"), 14, array('justification' => 'center', 'spacing' => 1.5));

        $pdf->ezColumnsStart(array('num' => 2, 'gap' => 10));
        $pdf->ezText(utf8_decode("Langue vivante 1 : <b>Anglais</b> "), 12, array('justification' => 'left', 'spacing' => 1.5));
        if ($efa != '') {
            $pdf->ezText(utf8_decode("Epreuve facultative A : <b>$efa</b>"), 12, array('justification' => 'left', 'spacing' => 1.5));
        }

        if ($efa == '' && $efb == '') {
            $pdf->ezText(utf8_decode("Epreuves facultatives : <b>Néant</b>"), 12, array('justification' => 'left', 'spacing' => 1.5));
        }

        $pdf->ezText(utf8_decode("Epreuve d'éducation physique et sportive: <b>$eps</b> $suite_eps"), 12, array('justification' => 'left', 'spacing' => 1.5));
        $pdf->ezNewPage();
        if ($id_serie == '1') {
            $pdf->ezText(utf8_decode("Langue vivante 2 : <b>$lv2</b>"), 12, array('justification' => 'left', 'spacing' => 1.5));
        } else {
            $pdf->ezText(utf8_decode(""), 12, array('justification' => 'left', 'spacing' => 1.5));
        }

        if ($efb != '') {
            $pdf->ezText(utf8_decode("Epreuve facultative B : <b>$efb</b>"), 12, array('justification' => 'left', 'spacing' => 1.5));
        }

        if ($efa == '' && $efb == '') {
            $pdf->ezText(utf8_decode(""), 12, array('justification' => 'left', 'spacing' => 1.5));
        }

        $pdf->ezColumnsStop();

        if ($id_eps == 1) {
            $pdf->rectangle(290, 392, 80, 20);
            $pdf->addText(295, 397, 12, "$atelier1- " . selectReferentiel($lien, 'atelier', $atelier1) . " ");
            $pdf->rectangle(370, 392, 80, 20);
            $pdf->addText(375, 397, 12, "$atelier2- " . selectReferentiel($lien, 'atelier', $atelier2) . " ");
            $pdf->rectangle(450, 392, 80, 20);
            $pdf->addText(455, 397, 12, "$atelier3- " . selectReferentiel($lien, 'atelier', $atelier3) . " ");
        }
        $pdf->line(20, 390, $width - 18, 390);
        $pdf->ezSetY(400);
        $pdf->ezColumnsStart(array('num' => 2, 'gap' => 10));
        $pdf->ezText(utf8_decode("<b>III - CONSTITUTION DE DOSSIER</b> "), 14, array('justification' => 'center', 'spacing' => 1.5));
        $pdf->ezText(utf8_decode("Tout dossier pour être pris en compte doit comporter :\n1. Formulaire obligatoire dûment rempli\n2. Bulletin de naissance ou toute autre pièce en tenant lieu\n3. Deux copies du relevé de notes du BAC 1 (dont une légalisée)\n4. Une quittance de droits d'examen s'élevant à 13.000 FCFA\n5. Droits d'inscription aux épreuves facultatives s'élevant à 500 FCFA par épreuve"), 8, array('justification' => 'full', 'spacing' => 1));
        $pdf->ezText(utf8_decode("<b>N.B.:</b> Le paiement des droits à l'examen du baccalauréat s'effectue soit auprès des économes ou des intendants des lycées et collèges présentant les candidats ou des agences de la B.T.C.I. ou des agences de la Société des postes du Togo\nAucune inscription ne peut être reçue après la date de clôture du registre  fixée chaque année par décision ministérielle"), 8, array('justification' => 'full', 'spacing' => 1));
        $pdf->ezNewPage();
        $pdf->ezText(utf8_decode("<b>IV - INFORMATIONS GENERALES</b>"), 14, array('justification' => 'center', 'spacing' => 1.5));
        $pdf->ezText(utf8_decode("1. Tout candidat devra obligatoirement subir dans le même centre les épreuves d'EPS, facultatives, écrites et orales. \n2. Aucun changement de série, de choix de matières facultatives anticipées ou d'épreuves écrites de langues ne pourra être accepté après le dépôt de ce dossier. \n3. La présentation d'une pièce d'identité nationale, civile ou scolaire est obligatoire pour toutes les épreuves\n4. La session de remplacement est exclusivement réservée aux candidats qui, pour des raisons de force majeure dûment justifiées, n'ont pu subir aucune ou la totalité des épreuves de la session normale de juin. Cette session ne comporte ni l'épreuve d'EPS, ni les épreuves facultatives et ne concerne pas les séries industruelles."), 8, array('justification' => 'full', 'spacing' => 1));
        $pdf->ezColumnsStop();

        $pdf->ezSetY(255);
        $pdf->line(22, 245, 575, 245);
        $pdf->ezText(utf8_decode("<b>DECLARATION A COMPLETER ET A SIGNER</b>"), 14, array('justification' => 'center', 'spacing' => 1.5));
        $pdf->ezText(utf8_decode("\nJe soussigné$e $nom $prenoms, né$e le $ddn à $ldn, titulaire de la carte d'identité ou du passeport n° ................................................ délivré à .................................. le ..............................\nprésente, à Monsieur le Ministre de l'Enseignement Supérieur et de la Recherche, la demande d'être inscrit$e à la session de juin $annee de l'examen du BAC1 de l'Enseignement du secondaire. \nEn outre, je déclare ne m'être inscrit$e dans une autre Institution pour subir ou avoir le même examen pendant la présente session et avoir été prévenu des suites que pourraient avoir pour soi d'après les lois et règlements, les fausses déclarations, les fausses signatures apposées aux actes ainsi que toute autre fraude ou tentative de fraude et toute infraction aux instructions qui figurent sur les convocations aux épreuves écrites et orales."), 11, array('justification' => 'full', 'spacing' => 1));

        $pdf->ezColumnsStart(array('num' => 2, 'gap' => 10));
        $pdf->ezText(utf8_decode("\nSignature du père ou de la mère ou du tuteur\n<i>précédée de la mention lu et approuvé</i>"), 10, array('justification' => 'center', 'spacing' => 1));
        $pdf->ezNewPage();
        $pdf->ezText(utf8_decode("\nA $ville, le ................................ 20.... \n Signature du candidat"), 10, array('justification' => 'center', 'spacing' => 1));
        $pdf->ezColumnsStop();

    }

}

$pdf->ezInsertMode(1, 1, 'before');
$pdf->ezNewPage();
$pdf->ezText("Sommaire des formulaires\n", 12);
$pdf->ezText(utf8_decode("$etablissement\n"), 16);
$pdf->ezText($sommaire, 8);
$pdf->ezInsertMode(0);
$pdfG = "convocations_" . $id_etablissement;

$title = "formulaires d'inscription au bac";
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
}

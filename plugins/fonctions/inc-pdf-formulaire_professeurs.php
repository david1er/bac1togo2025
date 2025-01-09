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
    if ($id_matiere != 0) {
        $andWhere = ' AND mat.matiere=' . $id_matiere;
    }

    $sql = "SELECT id_professeur, sex.sexe, nom, prenoms, ddn, "
        . " ldn, pay.pays as pdn, nat.pays as nationalite, telephone, eta.etablissement, "
        . " diplome, specialite, terminale, anciennete, mat.matiere, matricule, ci, centre, gen.genre "
        . " FROM bg_ref_sexe sex, bg_ref_pays as pay, "
        . " bg_ref_pays nat, bg_ref_etablissement eta,  "
        . " bg_professeur pro "
        . " LEFT JOIN bg_ref_matiere mat ON mat.id=pro.matiere "
        . " LEFT JOIN bg_ref_genre gen ON gen.id=pro.genre "
        . " WHERE pro.annee=$annee AND pro.etablissement=eta.id "
        . " AND pro.sexe=sex.id AND pro.pdn=pay.id AND pro.nationalite=nat.id "
        . " AND eta.id=$id_etablissement $andWhere "
        . " ORDER BY nom, prenoms ";

    $result = bg_query($lien, $sql, __FILE__, __LINE__);
//    echo $sql;

    $tab = array('id_professeur', 'sexe', 'nom', 'prenoms', 'ddn', 'ldn', 'pdn', 'nationalite', 'telephone', 'etablissement', 'diplome', 'specialite', 'matiere', 'terminale', 'anciennete', 'matricule', 'ci', 'centre', 'genre', 'login', 'maj');
    while ($row = mysqli_fetch_array($result)) {
        foreach ($tab as $col) {
            $$col = html_entity_decode($row[$col]);
        }

        if ($sexe == 'M') {
            $civ = 'M.';
            $e = '';
        } else {
            $civ = 'Mlle';
            $e = 'e'; // pour mettre des mots au fminin si c'est une candidate
        }
        $ddn = afficher_date($ddn);

        $professeur = utf8_decode(html_entity_decode("$civ <b>$nom</b> $prenoms, ne$e $ddn à $ldn"));
        $prof = html_entity_decode("$civ <b>$nom</b> $prenoms");

        if ($iPage > 0) {$iPage = $pdf->newPage();} else {
            $iPage = 1;
        }

        $pdf->addDestination($iPage, 'FitH');
        $sommaire .= "<c:ilink:" . getRewriteString($iPage) . ">$id_professeur - $professeur, $matiere</c:ilink>\n";

        $pdf->ezSetY($height - 25);
        $pdf->addJpegFromFile("../images/logo.png", 270, 750, 45);
        $pdf->rectangle(20, 20, $width - 35, $height - 40);
        $pdf->ezColumnsStart(array('num' => 3, 'gap' => 10));
        $pdf->ezText(utf8_decode("<b>$ministere\n**********\n$secretariat_general\n**********\n$direction</b>"), 9, array('justification' => 'center'));
        $pdf->ezNewPage();
        $pdf->ezNewPage();
        $pdf->ezText(utf8_decode("<b>REPUBLIQUE TOGOLAISE\nTravail - Liberté - Patrie</b>"), 9, array('justification' => 'center'));
        $pdf->ezColumnsStop();

        $pdf->ezSetY($height - 90);
        $pdf->ezText(utf8_decode("<b>$libelle_examen</b>"), 11, array('justification' => 'center'));
        $pdf->ezText(utf8_decode("N° d'inscription: <b>$id_professeur</b>"), 10, array('justification' => 'right'));
        $pdf->ezText(utf8_decode("<b><u>FICHE DE RENSEIGNEMENTS </u>\nAnnée: $annee</b>"), 14, array('justification' => 'center'));

        $pdf->ezText(utf8_decode("\nEtablissement: <b>$etablissement</b>\n"), 13, array('justification' => 'left'));
        $pdf->ezText(utf8_decode("Matière : <b>$matiere</b>"), 13, array('justification' => 'left'));

        $pdf->line(20, 630, $width - 18, 630);
        $pdf->ezSetY($height - 200);
        $pdf->ezText(utf8_decode("<b>I - ETAT CIVIL</b>"), 14, array('justification' => 'center', 'spacing' => 1.5));
        $pdf->ezText(utf8_decode("Sexe: <b>$sexe</b>"), 12, array('justification' => 'left', 'spacing' => 1.5));
        $pdf->ezText(utf8_decode("Nom et Prénoms: <b>$sexe $nom $prenoms</b>"), 12, array('justification' => 'left', 'spacing' => 1.5));
        $pdf->ezText(utf8_decode("Date et lieu de naissance : <b>$ddn</b> à <b>$ldn</b>"), 12, array('justification' => 'left', 'spacing' => 1.5));
        $pdf->ezText(utf8_decode(html_entity_decode("Pays de naissance : <b>$pdn</b>            Nationalité : <b>$nationalite</b>")), 12, array('justification' => 'left', 'spacing' => 1.5));
        $pdf->ezText(utf8_decode("Contacts téléphoniques : <b>$telephone</b>"), 12, array('justification' => 'left', 'spacing' => 1.5));
        $pdf->ezText(utf8_decode("Matricule : <b>$matricule</b>"), 12, array('justification' => 'left', 'spacing' => 1.5));
        $pdf->ezText(utf8_decode("Numéro Carte d'Identité : <b>$ci</b>"), 12, array('justification' => 'left', 'spacing' => 1.5));

        $pdf->line(20, 460, $width - 18, 460);
        $pdf->ezSetY($height - 350);
        if ($terminale == 'oui') {
            $type_enseignement = '(Enseignement ' . $genre . ')';
        } else {
            $type_enseignement = '';
        }

        if ($centre != 0) {
            $centre = 'Oui       Centre : ' . selectReferentiel($lien, 'centre', $centre);
        } else {
            $centre = 'Non';
        }

        $pdf->ezText(utf8_decode("<b>\nII - INFORMATIONS SPECIFIQUES</b>"), 14, array('justification' => 'center', 'spacing' => 1.5));
        $pdf->ezText(utf8_decode("Diplôme: <b>$diplome</b>"), 12, array('justification' => 'left', 'spacing' => 1.5));
        $pdf->ezText(utf8_decode("Avez-vous tenu une classe de terminale cette année? <b>$terminale</b> <b>$type_enseignement</b>"), 12, array('justification' => 'left', 'spacing' => 1.5));
        $pdf->ezText(utf8_decode(html_entity_decode("Ancienneté générale : <b>$anciennete </b>")), 12, array('justification' => 'left', 'spacing' => 1.5));
        $pdf->ezText(utf8_decode("Avez-vous corrigé cette l'année passée ? <b>$centre</b>"), 12, array('justification' => 'left', 'spacing' => 1.5));

        $pdf->line(20, 330, $width - 18, 330);
        $pdf->ezSetY($height - 510);
        $pdf->ezText(utf8_decode("<b>III - ENGAGEMENT</b>"), 14, array('justification' => 'center', 'spacing' => 1.5));
        $pdf->ezText(utf8_decode("\nJe soussigné$e $nom $prenoms, né$e le $ddn à $ldn, demande d'être inscrit$e sur la liste des correcteurs du BAC1 II de la session de juin $annee. \nJe reconnais avoir été prévenu des suites que pourraient avoir pour soi d'après les lois et règlements, les fausses déclarations, les fausses signatures apposées aux actes ainsi que toute autre fraude ou tentative de fraude et toute infraction lors du déroulement de l'examen."), 11, array('justification' => 'full', 'spacing' => 1.5));

        $pdf->ezColumnsStart(array('num' => 2, 'gap' => 10));
        $pdf->ezText(utf8_decode("\n\nSignature du Chef d'établissement\n"), 10, array('justification' => 'center', 'spacing' => 1));
        $pdf->ezNewPage();
        $pdf->ezText(utf8_decode("\n\n ............................, le ................................ 20.... \n\n Signature du professeur\n<i>précédée de la mention lu et approuvé</i>"), 10, array('justification' => 'center', 'spacing' => 1));
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

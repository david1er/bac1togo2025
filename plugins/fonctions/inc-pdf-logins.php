<?php
include_once 'generales.php';
$lien = lien();

//$tBddConf=getBddConf();
//mysql_connect($tBddConf['host'], $tBddConf['user'], $tBddConf['pass']) or die('Connection impossible<br/>'.mysql_error());
//mysql_select_db($tBddConf['bdd']) or die('Base inaccessible<br/>'.mysql_error());

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

if (isset($_GET['id'])) {
    $sql = "SELECT *,ins.inspection ,ins.id  FROM bg_ref_etablissement eta , bg_ref_inspection ins WHERE eta.id_inspection=ins.id AND id_region_division=$id AND login_eta!='' AND mdp_eta!='' AND id_etat=1 ORDER BY etablissement ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $colonnes = array('id', 'etablissement', 'login_eta', 'mdp_eta', 'inspection');
    while ($row = mysqli_fetch_array($result)) {
        foreach ($colonnes as $col) {
            $$col = $row[$col];
        }

        if ($iPage > 0) {$iPage = $pdf->newPage();} else {
            $iPage = 1;
        }

        $pdf->addDestination($iPage, 'FitH');
        $sommaire .= "<c:ilink:" . getRewriteString($iPage) . ">$etablissement</c:ilink>\n";

        $pdf->ezSetY($height - 25);
        $pdf->rectangle(20, 20, $width - 35, $height - 40);
        $pdf->addJpegFromFile("../images/logo.png", 270, 750, 45);
        $pdf->ezColumnsStart(array('num' => 3, 'gap' => 10));
        $pdf->ezText(utf8_decode("<b>$ministere \n**********\n$secretariat_general\n**********\n$direction</b>"), 9, array('justification' => 'center'));
        $pdf->ezNewPage();
        $pdf->ezNewPage();
        $pdf->ezText(utf8_decode("<b>REPUBLIQUE TOGOLAISE\nTravail - Liberté - Patrie</b>"), 9, array('justification' => 'center'));
        $pdf->ezColumnsStop();

        $pdf->ezSetY($height - 120);
        $pdf->ezText(utf8_decode("<b>$libelle_examen </b>"), 12, array('justification' => 'center'));
        $pdf->ezText(utf8_decode("\n<b><u>ELEMENTS DE CONNEXION AU SERVEUR </u></b>"), 14, array('justification' => 'center'));
        $pdf->ezText(utf8_decode("\n<b>Etablissement : $etablissement || Inspection : $inspection </b>"), 14, array('justification' => 'center'));

        $pdf->ezText(utf8_decode("\n\nAdresse Web: <b>https://exasco.gouv.tg/</b>"), 14, array('justification' => 'left'));
        $pdf->ezText(utf8_decode("\nLogin: <b>$login_eta</b>"), 14, array('justification' => 'left'));
        $pdf->ezText(utf8_decode("\nMot de passe: <b>$mdp_eta</b>"), 14, array('justification' => 'left'));

        $pdf->ezText(utf8_decode("\n\n\n<b><u>PROCEDURE D'ENREGISTREMENT DES CANDIDATS </u></b>\n"), 14, array('justification' => 'center'));

        $pdf->ezText(utf8_decode("1. Se rendre à l'adresse Web indiquée avec un navigateur. Mozilla Firefox de préférence."), 11, array('justification' => 'left'));
        $pdf->ezText(utf8_decode("2. Cliquer sur le bouton BAC1."), 11, array('justification' => 'left'));
        $pdf->ezText(utf8_decode("3. Saisir le login et le mot de passe puis valider."), 11, array('justification' => 'left'));
        $pdf->ezText(utf8_decode("4. Aller sur le menu <b>Edition</b> puis sur <b> Candidats</b>."), 11, array('justification' => 'left'));
        $pdf->ezText(utf8_decode("5. Aller sur l'onglet <b>Insérer</b> de la nouvelle fenêtre et saisir les dossiers des candidats en parcourant tous les champs puis enregistrer."), 11, array('justification' => 'left'));
        $pdf->ezText(utf8_decode("6. Suivez les instructions à gauche "), 11, array('justification' => 'left'));
        $pdf->ezText(utf8_decode("7. Aller sur le lien <b>Liste</b> et cliquer sur le nom d'un candidat pour modifier ou supprimer son dossier."), 11, array('justification' => 'left'));
        $pdf->ezText(utf8_decode("8. Aller sur le lien <b>Impressions</b> pour imprimer les listes pour correction et émargement."), 11, array('justification' => 'left'));

    }
}

$pdf->ezInsertMode(1, 1, 'before');
$pdf->ezNewPage();
$pdf->ezText("SOMMAIRE DES ETABLISSEMENTS \n", 12);
$pdf->ezText($sommaire, 8);
$pdf->ezInsertMode(0);
$pdfG = "logins_" . $id;

$title = "Elements de connexion au serveur";
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

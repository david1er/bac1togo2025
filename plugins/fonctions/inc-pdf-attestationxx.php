<?php
session_start();
include_once 'generales.php';
$lien = lien();

foreach ($_REQUEST as $key => $val) {
    $$key = mysqli_real_escape_string($lien, trim($val));
}

if ($annee == '') {
    $annee = date('Y');
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

if ($jury > 0) {
    $texte = "ATTESTATIONS DES ADMIS ";
    $titre = "$texte ADMIS A LA PREMIERE DELIBERATION ";
    $fontSize = 10;

    $options2 = array(
        'spacing' => 1.5,
        'justification' => 'center',
    );
    $options3 = array(
        'spacing' => 1.5,
        'justification' => 'full',
    );

    $sql = "SELECT can.nom, can.prenoms, can.sexe, can.num_table, ser.serie, ser.intitule, ser.id as id_serie,
			can.ddn, can.ldn, can.nom_photo, pay.pays, cen.etablissement, rep.jury, res.moyenne, delib1, delib2
			FROM bg_candidats can, bg_repartition rep, bg_ref_serie ser, bg_ref_pays pay,
			bg_ref_etablissement cen, bg_resultats res
			WHERE can.annee=$annee AND rep.annee=$annee AND res.annee=$annee
			AND can.num_table=rep.num_table AND can.num_table=res.num_table AND can.serie=ser.id AND can.pdn=pay.id
			AND cen.id=rep.id_centre AND rep.jury=$jury AND (delib1='Passable' OR delib1='Abien' OR delib1='Bien' OR delib1='TBien' OR delib2='Passable' OR delib2='Abien')
			ORDER BY ser.serie, can.num_table, can.nom, can.prenoms ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $tab = array('nom', 'prenoms', 'sexe', 'num_table', 'serie', 'intitule', 'ddn', 'ldn', 'nom_photo', 'pays', 'etablissement', 'jury', 'moyenne', 'delib1', 'delib2', 'id_serie');
    while ($row = mysqli_fetch_array($result)) {
        foreach ($tab as $val) {
            $$val = html_entity_decode(utf8_decode($row[$val]));
        }

        $tNum[$num_table] = $num_table;
        $tTypes[$num_table][$id_type_note] = $id_type_note;
        $tCandidats[$num_table]['serie'] = $serie;
        $tCandidats[$num_table]['sexe'] = $sexe;
        $tCandidats[$num_table]['intitule'] = $intitule;
        $tCandidats[$num_table]['jury'] = $jury;
        $tCandidats[$num_table]['nom_photo'] = $nom_photo;
        $tCandidats[$num_table]['centre'] = $etablissement;
        $tCandidats[$num_table]['moyenne'] = $moyenne;
        $tCandidats[$num_table]['id_serie'] = $id_serie;
        $tCandidats[$num_table]['delib1'] = $delib1;
        $tCandidats[$num_table]['delib2'] = $delib2;
        $tCandidats[$num_table]['ddn'] = afficher_date($ddn);
        $tCandidats[$num_table]['ldn'] = utf8_encode($ldn . " ($pays)");
        $tCandidats[$num_table]['nom'] = utf8_encode($nom);
        $tCandidats[$num_table]['prenoms'] = utf8_encode($prenoms);
        $i++;
    }

    foreach ($tNum as $num_table) {
        $serie = $tCandidats[$num_table]['serie'];
        $intitule = utf8_encode($tCandidats[$num_table]['intitule']);
        $ddn = $tCandidats[$num_table]['ddn'];
        $ldn = $tCandidats[$num_table]['ldn'];
        $jury = $tCandidats[$num_table]['jury'];
        $sexe = $tCandidats[$num_table]['sexe'];
        $delib1 = $tCandidats[$num_table]['delib1'];
        $delib2 = $tCandidats[$num_table]['delib2'];
        $id_serie = $tCandidats[$num_table]['id_serie'];
        $nom_photo = $tCandidats[$num_table]['nom_photo'];
        $centre = $tCandidats[$num_table]['centre'];
        $nom = $tCandidats[$num_table]['nom'];
        $prenoms = $tCandidats[$num_table]['prenoms'];
        if ($sexe == 1) {
            $e = 'e';
        } else {
            $e = '';
        }

        if ($iPage > 0) {$iPage = $pdf->newPage();} else {
            $iPage = 1;
        }

        $pdf->addDestination($iPage, 'FitH');
        $sommaire .= "<c:ilink:" . $iPage . ">" . $num_table . " - $nom $prenoms - $serie </c:ilink>\n";

        $espace = html_entity_decode("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");

        $pdf->ezSetY($height - 25);
        $pdf->ezSetY($height - 102);
        $pdf->ezText(utf8_decode("\n<b>ATTESTATION DE DIPLOME</b>"), 13, array('justification' => 'center'));
        $pdf->ezText(utf8_decode("<b>\nBACCALAUREAT PREMIERE PARTIE (BAC 1)</b>"), 11, array('justification' => 'center'));
        $pdf->ezText(utf8_decode("<b>SERIE $serie, $intitule</b>"), 10, array('justification' => 'center'));

        $pdf->ezText(utf8_decode("\n\n\nLe Directeur Général de l'Office du BAC1 certifie que : "), 13, $options3);
        $pdf->ezText(utf8_decode("$espace Nom: <b>$nom</b>"), 13, $options3);
        $pdf->ezText(utf8_decode("$espace Prénom(s) : <b>$prenoms</b>"), 13, $options3);
        $pdf->ezText(utf8_decode("$espace Date de naissance : le $ddn"), 13, $options3);
        $pdf->ezText(utf8_decode("$espace Lieu de naissance : $ldn"), 13, $options3);
        $pdf->ezText(utf8_decode("$espace N° de table: <b>$num_table</b>"), 13, $options3);

        if ($delib2 == '') {
            $delib = $delib1;
        } else {
            $delib = $delib2;
        }

        if ($delib2 == 'Abien') {
            $delib = 'Passable';
        }

        if (in_array($delib, array('Abien', 'TBien'))) {
            $mention = strtoupper(str_replace('abien', 'ASSEZ - BIEN', str_replace('tbien', 'TRES - BIEN', strtolower($delib))));
        } else {
            $mention = $delib;
        }

        $mention = strtoupper($delib);
        if ($delib1 == 'Oral') {
            $date_delib = dateDeliberation($lien, $annee, $jury, 2);
        } else {
            $date_delib = dateDeliberation($lien, $annee, $jury, 1);
        }

        $date_impression = afficher_date(date('Y-m-d'));

        $pdf->ezText(utf8_decode("a été jugé digne du BACCALAUREAT PREMIERE PARTIE (BAC 1)"), 13, $options3);
        if ($id_serie <= 3) {
            $pdf->ezText(utf8_decode("ENSEIGNEMENT GENERAL"), 13, $options2);
        } else {
            $pdf->ezText(utf8_decode("ENSEIGNEMENT TECHNIQUE"), 13, $options2);
        }

        $mois = moisSession($lien, $annee, $jury);
        $pdf->ezText(utf8_decode("$espace Session : $mois $annee "), 13, $options3);
        $pdf->ezText(utf8_decode("$espace Mention : $mention le $date_delib"), 13, $options3);
        $pdf->ezText(utf8_decode("\n\nFait à LOME, le $date_impression\n\n"), 12, $options2);

        $lien_photos = "../../photos/$annee/";

//        $pdf->ezSetDy(-5);
        $pdf->ezColumnsStart(array('num' => 2, 'gap' => 10));
        $pdf->ezText(utf8_decode("Signature de l'Impétrant"), 13, $options2);
        $pdf->ezNewPage();
        $pdf->ezText(utf8_decode("Le Directeur Général\n\n\n<b>DJANEYE - BOUNDJOU Gbandi</b>"), 13, $options2);
        $pdf->ezColumnsStop();

        $pdf->ezSetY(40);
        $pdf->ezText(html_entity_decode("<b>Voir Verso avis important </b>"), 8, array('justification' => 'centre'));
    }
}

$pdfG = "liste_jury" . $jury . '_annee' . $annee;

$pdf->ezInsertMode(1, 1, 'before');
$pdf->ezNewPage();
$pdf->ezText($titre, 11);
$pdf->ezText($sommaire, 8);
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

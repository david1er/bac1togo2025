<?php
session_start();
include_once 'generales.php';
$lien = lien();
$statut = getStatutUtilisateur();

foreach ($_REQUEST as $key => $val) {
    $$key = mysqli_real_escape_string($lien, trim(addslashes($val)));
}

if ($annee == '') {
    $annee = date('Y');
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

$pdf->ezSetY($height - 25);
$pdf->addJpegFromFile("../images/logo.png", 275, 750, 45);
$pdf->ezColumnsStart(array('num' => 3, 'gap' => 10));
$pdf->ezText(utf8_decode("<b>$ministere\n**********\n$secretariat_general\n**********\n$direction</b>"), 9, array('justification' => 'center'));
$pdf->ezNewPage();
$pdf->ezNewPage();
$pdf->ezText(utf8_decode("<b>REPUBLIQUE TOGOLAISE\nTravail - Liberté - Patrie</b>"), 9, array('justification' => 'center'));
$pdf->ezColumnsStop();

$pdf->ezSetY($height - 100);
$pdf->ezText(utf8_decode("<b>\n\n$libelle_examen </b>"), 11, array('justification' => 'center'));
// $mois = moisSession($lien, $annee, $jury);
$mois = moisSession2($lien, $annee);
$pdf->ezText(utf8_decode("<b>Session de $mois $annee\n</b>"), 10, array('justification' => 'center'));

if ($jury > 0) {
    $titre = "$texte ADMISSIBLES A L'ORAL - PREMIERE DELIBERATION ";
    $pdf->ezText(utf8_decode("\n<b>$titre</b>\n"), 13, array('justification' => 'center'));

    $sql = "SELECT can.nom, can.prenoms, can.sexe, can.num_table, ser.serie, cen.etablissement, rep.jury, delib1, delib2, note, mat.id as id_matiere, mat.matiere, mat.abreviation
		FROM bg_candidats can, bg_repartition rep, bg_ref_serie ser, bg_ref_pays pay, bg_ref_etablissement cen, bg_resultats res, bg_notes notes, bg_ref_matiere mat, bg_calendrier calend
		WHERE can.annee=$annee AND rep.annee=$annee AND res.annee=$annee AND notes.annee=$annee
		AND can.num_table=rep.num_table AND can.num_table=res.num_table AND can.serie=ser.id AND can.pdn=pay.id AND notes.num_table=can.num_table
		AND cen.id=rep.id_centre AND mat.id=notes.id_matiere AND rep.jury=$jury AND mat.id=calend.id_matiere AND delib1='Oral' AND calend.id_type_note=3
		ORDER BY ser.serie, can.num_table, mat.matiere ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $tab = array('nom', 'prenoms', 'sexe', 'num_table', 'serie', 'etablissement', 'jury', 'delib1', 'delib2', 'note', 'id_matiere', 'matiere', 'abreviation');
    $i = 0;
    while ($row = mysqli_fetch_array($result)) {
        foreach ($tab as $val) {
            $$val = html_entity_decode($row[$val]);
        }

        $tabMatieres[$id_matiere] = $abreviation;
        $tNoms = getNoms($lien, $annee, $num_table);
        $data[$num_table]['numero'] = $num_table;
        $data[$num_table]['candidat'] = utf8_decode($tNoms['noms']);
        $data[$num_table]['serie'] = $serie;
        $data[$num_table]['note'] = $note;
        $data[$num_table]['id_matiere'] = $note;
        $data[$num_table]['delib'] = $delib2;
        $i++;
    }
    $entete = array_merge(array('numero' => "NUMERO", 'candidat' => "CANDIDAT"), $tabMatieres);
    if (is_array($data)) {
        $pdf->ezTable($data, $entete, "JURY $jury - SERIE $serie");
    } else {
        $pdf->ezText(utf8_decode("<b>NEANT</b>"), 30, array('justification' => 'center'));
    }

}
//$pdf->ezText(utf8_decode("\n\n\n\n<b><u>LE PRESIDENT DU JURY DE DELIBERATION</u></b>\n"),11,array('justification'=>'center'));

$pdf->ezStartPageNumbers(10, 20, 8, 'right', "REPUBLIQUE TOGOLAISE - DIRECTION DES EXAMENS, CONCOURS ET CERTIFICATIONS - $DateConvocPied - PAGE {PAGENUM} sur {TOTALPAGENUM}");
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

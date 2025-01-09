<?php
session_start();
include_once 'generales.php';
$lien = lien();

foreach ($_REQUEST as $key => $val) {
    $$key = mysqli_real_escape_string($lien, trim($val));
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
$pdf->ezStartPageNumbers(50, 15, 7, 'right', "$DateConvocPied REPUBLIQUE TOGOLAISE - DIRECTION DES EXAMENS, CONCOURS ET CERTIFICATIONS - Page {PAGENUM} sur {TOTALPAGENUM}");

$options = array(
    'spacing' => 1,
    'justification' => 'full',
    'titleFontSize' => 10,
    'fontSize' => 7,
    'maxWidth' => 810,
);

$statutUtil = getStatutUtilisateur();
if ($statutUtil == 'Etablissement') {
    $cd_eta = stripslashes($tab_auteur['email']);
    $tab_cd_eta = explode('@', $cd_eta);
    $id_etablissement = $tab_cd_eta[1];
}

if (isset($_GET['id_etablissement']) && $_GET['id_etablissement'] > 0) {

    if ($id_matiere != 0) {
        $andWhere = " AND mat.id=$id_matiere ";
    } else {
        $andWhere = '';
    }

    $sql2 = "SELECT id_professeur, sex.sexe, nom, prenoms, ddn, "
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
    $result2 = bg_query($lien, $sql2, __FILE__, __LINE__);
    if (mysqli_num_rows($result2) > 0) {
        $pdf->ezSetY($height - 25);
        $pdf->addJpegFromFile("../images/logo.png", 390, 520, 45);
        $pdf->ezColumnsStart(array('num' => 3, 'gap' => 10));
        $pdf->ezText(utf8_decode("<b>$ministere\n**********\n$secretariat_general\n**********\n$direction</b>"), 9, array('justification' => 'center'));
        $pdf->ezNewPage();
        $pdf->ezNewPage();
        $pdf->ezText(utf8_decode("<b>REPUBLIQUE TOGOLAISE\nTravail - Liberté - Patrie</b>"), 9, array('justification' => 'center'));
        $pdf->ezColumnsStop();

        $pdf->ezSetY($height - 100);
        $pdf->ezText(utf8_decode("<b>$libelle_examen</b>"), 14, array('justification' => 'center'));
        $mois = moisSession($lien, $annee, $jury);
        $pdf->ezText(utf8_decode("<b>Session de $mois $annee\n</b>"), 10, array('justification' => 'center'));
        $tab = array('id_professeur', 'sexe', 'nom', 'prenoms', 'ddn', 'ldn', 'pdn', 'nationalite', 'telephone', 'etablissement', 'diplome', 'specialite', 'matiere', 'terminale', 'anciennete', 'matricule', 'ci', 'centre', 'genre', 'login', 'maj');
        $i = 1;
        $tabCan = array();
        while ($row2 = mysqli_fetch_array($result2)) {
            foreach ($tab as $val) {
                $$val = utf8_decode($row2[$val]);
            }

            if ($sexe == 'F') {
                $civ = 'Mlle ';
            } else {
                $civ = 'M. ';
            }

            $tabCan[$i]['num'] = $i;
            $tabCan[$i]['id'] = $id_professeur;
            $tabCan[$i]['fonc'] = $fonction;
            $tabCan[$i]['tel'] = $telephone;
            $tabCan[$i]['dipl'] = $diplome;
            $tabCan[$i]['spec'] = $specialite;
            $tabCan[$i]['mat'] = $matiere;
//                    $tabCan[$i]['act']=substr($activite,0,-13);
            $tabCan[$i]['ter'] = $terminale;
            $tabCan[$i]['anc'] = $anciennete;
            $tabCan[$i]['noms'] = $civ . $nom . ' ' . $prenoms;
            $tabCan[$i]['dldn'] = afficher_date($ddn) . ' ' . $ldn . ' (' . $pdn . ')';
            $i++;
        }
        $cols = array('num' => utf8_decode('N° '),
            'id' => utf8_decode('N°ENR.'),
            'noms' => 'NOMS ET PRENOMS',
            'dldn' => 'DATE ET LIEU DE NAISSANCE',
            'dipl' => 'DIPLOME',
            'spec' => 'SPECIALITE',
            'ter' => 'TER',
            'anc' => 'ANC',
            'mat' => 'MATIERE',
//                        'act'=>'COMMISSION',
            'tel' => utf8_decode('TELEPHONE'));
        $pdf->ezTable($tabCan, $cols, $etablissement, $options);
        $pdf->ezNewPage();
    }

}

$pdfG = "liste_professeurs_" . getRewriteString($etablissement);

$pdf->ezInsertMode(1, 1, 'before');
$pdf->ezInsertMode(0);

$title = "liste des professeurs $etablissement " . $annee;
$infos = array('Title' => $title, 'Author' => 'DG VLAV', 'CreationDate' => date("d/m/Y"));
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
    $pdf_fic = 'pdf/' . $annee . "_$pdfG" . date("Ymd") . '.pdf';
    $fp = fopen($pdf_fic, 'wb');
    fwrite($fp, $pdfcode);
    fclose($fp);

    echo "<A HREF='$pdf_fic'>Cliquez ici pour lire le fichier $pdf_fic</a>\n";
    echo "<script>document.location='$pdf_fic'</script>";

    session_destroy();
}

<?php
session_start();
//if(isset($_GET['reset'])) session_destroy();
//on rcupere le tableau  afficher, $data, dans la session
//tableau de la forme $data[$num_row]=$row
$pdfG = $_GET['pdf'];
if ($pdfG == '') {
    die('Veuillez préciser le nom du tableau pdf &agrave; générer');
}

//Recuperation du timbre
  
$sql_timbre = "SELECT ministere, secretariat_general, direction, libelle_examen FROM bg_timbre WHERE 1";
$result_timbre = bg_query($lien, $sql_timbre, __FILE__, __LINE__);
$row_timbre = mysqli_fetch_array($result_timbre);

$ministere = $row_timbre['ministere'];
$secretariat_general = $row_timbre['secretariat_general'];
$direction = $row_timbre['direction'];
$libelle_examen = $row_timbre['libelle_examen'];

$data = $_SESSION['data'][$pdfG];
$title = $_SESSION['title'][$pdfG]; // titre au-dessus du tableau
$andTitre = $_SESSION['andTitre'][$pdfG];
$cols = $_SESSION['cols'][$pdfG]; // colonnes  inclure dans la table
$options = $_SESSION['options'][$pdfG]; // options (facultatives) du tableau
$encryption = $_SESSION['encryption'][$pdfG]; // protection par mot de passe
$post = $_SESSION['post'][$pdfG]; // texte a afficher a la fin du document pdf
$annee = $_SESSION['annee'][$pdfG];
$tSalle = $_SESSION['tSalle'][$pdfG];
$tache = $_SESSION['tache'][$pdfG];

//echo "<pre>";print_r($_SESSION);die('</pre>');
//echo "<pre>";print_r($tSalle);die('</pre>');
//echo "<pre>";print_r($data);die('</pre>');

$debug = false;
if ($debug) {
    echo ($pdfG);
    //print_r($_SESSION);
    foreach (array('title', 'pied', 'cols', 'options', 'infos', 'format', 'encryption', 'data') as $var) {
        echo "<hr size='1'/><pre><b>$var</b> ";
        print_r($$var);
        echo "</pre>\n";
    }
    die(OK . ' - fin du debug');
}
$pdf_dir = '../ezpdf/';
include $pdf_dir . 'class.ezpdf.php'; // inclusion du code de la bibliothque
if ($options['orientation'] == 'portrait') {
    $pdf = new Cezpdf('A4', 'portrait');
    $options2 = array(
        'spacing' => 1.5,
        'justification' => 'full',
        'titleFontSize' => 12,
        'fontSize' => 11,
        'maxWidth' => 545,
    );
    $options['maxWidth'] = 500;
} else {
    $pdf = new Cezpdf('A4', 'landscape');
    $options2 = array(
        'spacing' => 1.5,
        'justification' => 'full',
        'titleFontSize' => 12,
        'fontSize' => 10,
        'maxWidth' => 790,
        'cols' => array(
            'noms' => array('justification' => 'left', 'width' => 170),
            'dldn' => array('justification' => 'left', 'width' => 150),
            'eta' => array('justification' => 'left', 'width' => 110),
        ),
    );
}
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

$pdf->ezSetY($height - 25);

if ($options['orientation'] == 'portrait') {
    $pdf->addJpegFromFile("../images/logo.png", 540, 777, 45);
} else {
    $pdf->addJpegFromFile("../images/logo.png", 390, 520, 45);
}

//    $pdf->addJpegFromFile("../images/logo.png",390,520,45);
$pdf->ezColumnsStart(array('num' => 3, 'gap' => 10));
$pdf->ezText(utf8_decode("<b>$ministere\n**********\n$secretariat_general\n**********\n$direction</b>"), 9, array('justification' => 'center'));
$pdf->ezNewPage();
$pdf->ezNewPage();
$pdf->ezText(utf8_decode("<b>REPUBLIQUE TOGOLAISE\nTravail - Liberté - Patrie</b>"), 9, array('justification' => 'center'));
$pdf->ezColumnsStop();

$pdf->ezSetY($height - 140);
$pdf->ezText(utf8_decode("<b>$libelle_examen </b>"), 14, array('justification' => 'center'));
$mois = moisSession2($lien, $annee);
$pdf->ezText(utf8_decode("<b>Session de $mois $annee\n</b>"), 10, array('justification' => 'center'));
$pdf->ezText(utf8_decode("<b>$andTitre</b>"), 16, array('justification' => 'center'));

// make the table
if ($tache == 'notes_etablissement') {
    foreach ($cols as $serie => $t1) {
        $cols2 = array('num' => 'N TABLE', 'noms' => 'NOMS ET PRENOMS');
        foreach ($t1 as $id_matiere => $matiere) {
            $cols2[$id_matiere] = $matiere;
        }
        $cols2['delib'] = 'MENTION';
        $cols2['moy'] = 'MOYENNE';
        $title = 'SERIE ' . $serie;
        $pdf->ezTable($data[$serie], $cols2, $title, $options2);
        $pdf->ezNewPage();
    }
} else {
    if (is_array($tSalle)) {
        foreach ($tSalle as $salle) {
            $titre = $title . " ($salle)";
            $pdf->ezTable($data[$salle], $cols, $titre, $options2);
            $pdf->ezNewPage();
        }
    } else {
        $pdf->ezTable($data, $cols, $title, $options2);
    }
}
$pdf->ezSetDy(-10);
$pdf->ezText($post);
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
//session_destroy();

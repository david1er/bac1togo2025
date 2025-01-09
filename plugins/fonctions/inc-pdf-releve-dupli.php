<?php
session_start();
include_once 'generales.php';
$lien = lien();

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

if ($jury > 0) {
    $texte = "RELEVES DE NOTES DES CANDIDATS ";
    $entete = array('matiere' => "MATIERES", 'note' => "NOTE\nSUR 20", 'coeff_mat' => "COEF.", 'points_mat' => "POINTS\nOBTENUS", 'sur' => "SUR");
    if ($type_releve == 1) {
        $andWhere = " AND (delib1='Passable' OR delib1='Abien' OR delib1='Bien' OR delib1='TBien')";
        $titre = "$texte ADMIS A LA PREMIERE DELIBERATION ";
        $fontSize = 10;
        $date_delib = dateDeliberation($lien, $annee, $jury, 1);
    } elseif ($type_releve == 2) {
        $andWhere = " AND (delib1='REfuse' OR delib1='Ajourne' OR delib1='Abandon' OR delib1='Absent') ";
        $titre = "$texte NON ADMIS A LA PREMIERE DELIBERATION";
        $fontSize = 10;
        $date_delib = dateDeliberation($lien, $annee, $jury, 1);
    } elseif ($type_releve == 3) {
        $andWhere = " AND (delib2='Passable' OR delib2='Abien')";
        $titre = "$texte ADMIS A LA DEUXIEME DELIBERATION ";
        $fontSize = 8;
        $date_delib = dateDeliberation($lien, $annee, $jury, 1);
        //$date_delib=dateDeliberation($lien,$annee,$jury,2);
    } elseif ($type_releve == 4) {
        $andWhere = " AND delib2='Refuse' ";
        $titre = "$texte NON ADMIS A LA DEUXIEME DELIBERATION";
        $date_delib = dateDeliberation($lien, $annee, $jury, 1);
        //$date_delib=dateDeliberation($lien,$annee,$jury,2);
        $fontSize = 8;
    } else {
        die('Erreur');
    }

    $options = array(
        'cols' => array(
            'matiere' => array('justification' => 'left', 'width' => 200),
            'note' => array('justification' => 'center', 'width' => 50),
            'coeff_mat' => array('justification' => 'center', 'width' => 45),
            'points_mat' => array('justification' => 'center', 'width' => 65),
            'sur' => array('justification' => 'center', 'width' => 50),
        ),
        'xPos' => 240,
        'titleFontSize' => 10,
        'fontSize' => $fontSize,

        'showHeadings' => 1,
        'shaded' => 0,
        'showLines' => 10,
    );

    $options1 = array(
        'spacing' => 1,
        'justification' => 'full',
    );
    $options2 = array(
        'spacing' => 1.5,
        'justification' => 'center',
    );
    $options3 = array(
        'spacing' => 1.5,
        'justification' => 'full',
    );
    $options4 = array(
        'spacing' => 1.5,
        'justification' => 'right',
    );

//$pdf->ezText(utf8_decode("\n<b>$titre</b>\n"),13,array('justification'=>'center'));

    $sql = "SELECT can.nom, can.prenoms, can.sexe, can.num_table, ser.serie, ser.intitule, can.ddn, can.eps, can.ldn, can.nom_photo, pay.pays, cen.etablissement, rep.jury, res.moyenne, delib1, delib2, res.coeff as coeff_total,
			mat.matiere, mat.id as id_matiere, notes.note, notes.coeff as coeff_mat, notes.id_type_note, types.type_note, can.lv2, can.efa, can.efb
			FROM bg_candidats can, bg_repartition rep, bg_ref_serie ser, bg_ref_pays pay, bg_ref_etablissement cen, bg_resultats res, bg_notes notes, bg_ref_type_note types, bg_ref_matiere mat
			WHERE can.annee=$annee AND rep.annee=$annee AND res.annee=$annee AND notes.annee=$annee
			AND can.num_table=rep.num_table AND can.num_table=res.num_table AND can.serie=ser.id AND can.pdn=pay.id
			AND notes.num_table=can.num_table AND notes.id_type_note=types.id AND mat.id=notes.id_matiere
			AND cen.id=rep.id_centre AND rep.jury=$jury $andWhere
			ORDER BY ser.serie, can.num_table, can.nom, can.prenoms, types.id, mat.matiere ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $tab = array('nom', 'prenoms', 'sexe', 'num_table', 'serie', 'intitule', 'ddn', 'eps', 'ldn', 'nom_photo', 'pays', 'etablissement', 'jury', 'moyenne', 'delib1', 'delib2', 'coeff_total', 'note', 'matiere', 'id_matiere', 'coeff_mat', 'id_type_note', 'type_note', 'lv2', 'efa', 'efb');
    while ($row = mysqli_fetch_array($result)) {
        foreach ($tab as $val) {
            $$val = html_entity_decode(utf8_decode($row[$val]));
        }

        if ($note == '') {
            $note = 0;
        }

        $tNum[$num_table] = $num_table;
        $tTypes[$num_table][$id_type_note] = $id_type_note;
        $tCandidats[$num_table]['serie'] = $serie;
        $tCandidats[$num_table]['sexe'] = $sexe;
        $tCandidats[$num_table]['intitule'] = $intitule;
        $tCandidats[$num_table]['eps'] = $eps;
        $tCandidats[$num_table]['lv2'] = $lv2;
        $tCandidats[$num_table]['efa'] = $efa;
        $tCandidats[$num_table]['efb'] = $efb;
        $tCandidats[$num_table]['jury'] = $jury;
        $tCandidats[$num_table]['nom_photo'] = $nom_photo;
        $tCandidats[$num_table]['centre'] = $etablissement;
        $tCandidats[$num_table]['moyenne'] = $moyenne;
        $tCandidats[$num_table]['delib1'] = $delib1;
        $tCandidats[$num_table]['delib2'] = $delib2;
        $tCandidats[$num_table]['coeff_total'] = $coeff_total;
        $tCandidats[$num_table]['dldn'] = afficher_date($ddn) . ' à ' . utf8_encode($ldn . " ($pays)");
        $tMatieres[$num_table][$id_type_note][$id_matiere] = $matiere;
        $tNotes[$num_table][$id_type_note][$id_matiere]['note'] = $note;
        $tNotes[$num_table][$id_type_note][$id_matiere]['coeff_mat'] = $coeff_mat;
        $tCandidats[$num_table]['nom'] = utf8_encode($nom);
        $tCandidats[$num_table]['prenoms'] = utf8_encode($prenoms);

        $i++;
    }

    foreach ($tNum as $num_table) {
        $data = array();
        $serie = $tCandidats[$num_table]['serie'];
        $intitule = utf8_encode($tCandidats[$num_table]['intitule']);
        $dldn = $tCandidats[$num_table]['dldn'];
        $jury = $tCandidats[$num_table]['jury'];
        $sexe = $tCandidats[$num_table]['sexe'];
        $delib1 = $tCandidats[$num_table]['delib1'];
        $delib2 = $tCandidats[$num_table]['delib2'];
        $nom_photo = $tCandidats[$num_table]['nom_photo'];
        $centre = $tCandidats[$num_table]['centre'];
        $efa = $tCandidats[$num_table]['efa'];
        $efb = $tCandidats[$num_table]['efb'];
        $lv2 = $tCandidats[$num_table]['lv2'];
        $eps = $tCandidats[$num_table]['eps'];
//        $tNoms=getNoms($lien,$annee,$num_table);
        //        $noms=utf8_encode($tNoms['noms']);
        //        $noms=$tNoms['noms'];
        $nom = $tCandidats[$num_table]['nom'];
        $prenoms = $tCandidats[$num_table]['prenoms'];
        if ($sexe == 1) {
            $e = 'e';
        } else {
            $e = '';
        }

        if ($sexe == 1) {
            $sexe1 = 'F';
        } else {
            $sexe1 = 'M';
        }

        if ($iPage > 0) {$iPage = $pdf->newPage();} else {
            $iPage = 1;
        }

        $pdf->addDestination($iPage, 'FitH');
        $sommaire .= "<c:ilink:" . $iPage . ">" . $num_table . " - $nom $prenoms - $serie </c:ilink>\n";

        $pdf->ezSetY($height - 25);
        //$pdf->addJpegFromFile("../images/togo.jpeg",270,750,45);
        $pdf->ezColumnsStart(array('num' => 3, 'gap' => 10));
        $pdf->ezText(utf8_decode("<b>$ministere\n**********\n$secretariat_general\n**********\n$direction</b>"), 9, array('justification' => 'center'));
        $pdf->ezNewPage();
        $pdf->ezNewPage();
        $pdf->ezText(utf8_decode("<b>REPUBLIQUE TOGOLAISE\nTravail - Liberté - Patrie</b>"), 9, array('justification' => 'center'));
        $pdf->ezColumnsStop();

        $pdf->ezSetY($height - 110);
        $pdf->ezText(utf8_decode("\n\n<u><b>RELEVE DE NOTES</b></u>"), 13, array('justification' => 'center'));
        $pdf->ezText(utf8_decode("<b>\n$libelle_examen </b>"), 11, array('justification' => 'center'));
        $mois = moisSession($lien, $annee, $jury);
        $pdf->ezText(utf8_decode("<b>Session de $mois $annee\n</b>"), 10, array('justification' => 'center'));
        $pdf->ezText(utf8_decode("<b>SERIE $serie, $intitule</b>"), 10, array('justification' => 'center'));

//        $titre_nom=utf8_decode("Nom et Prénoms: ");
        //        $pdf->ezText("$titre_nom <b>$nom $prenoms</b>",11,$options3);
        $pdf->ezText(utf8_decode("Nom et Prénoms: <b>$nom $prenoms</b>"), 11, $options3);
        $pdf->ezText(utf8_decode("Date et lieu de naissance: <b>$dldn</b>"), 11, $options3);
        $pdf->ezText(utf8_decode("Jury: <b>$jury</b>          N° de table: <b>$num_table</b>       Sexe: <b>$sexe1</b>\n"), 11, $options3);
        $tabPoints[4] = 0;

        foreach (array(4, 1, 3) as $id_type_note) {
            if (in_array($id_type_note, $tTypes[$num_table])) {
                $i = 0;
//                $data=array();
                $total_coeff = $total_points = $total_sur = 0;

                foreach ($tMatieres[$num_table][$id_type_note] as $id_matiere => $matiere) {
                    if ($id_type_note == 4) {
                        $data[$id_type_note][$i]['matiere'] = ($matiere) . '**';
//                        $data[$id_type_note][$i]['matiere']=strtoupper($matiere).'**';
                    } else {
                        if ($id_matiere == 51) {$matiere = selectReferentiel($lien, 'langue_vivante', $lv2);
                            $data[$id_type_note][$i]['matiere'] = ('Langue Vivante 2 (' . $matiere . ')');} else {
                            $data[$id_type_note][$i]['matiere'] = ($matiere);
                        }

//                        else $data[$id_type_note][$i]['matiere']=strtoupper($matiere);
                    }
                    if ($tNotes[$num_table][$id_type_note][$id_matiere]['note'] >= 0) {
                        $data[$id_type_note][$i]['note'] = $tNotes[$num_table][$id_type_note][$id_matiere]['note'];
                    } else {
                        $data[$id_type_note][$i]['note'] = 'Absent';
                    }
                    if ($id_type_note == 4) {
//                        $data[$i]['note']='-';
                        $data[$id_type_note][$i]['coeff_mat'] = '-';
                        $data[$id_type_note][$i]['sur'] = '-';
                        if ($tNotes[$num_table][$id_type_note][$id_matiere]['note'] > 10 && $tNotes[$num_table][$id_type_note][$id_matiere]['note'] < 16) {
                            $data[$id_type_note][$i]['points_mat'] = $tNotes[$num_table][$id_type_note][$id_matiere]['note'] - 10;
                            $total_points += $tNotes[$num_table][$id_type_note][$id_matiere]['note'] - 10;

                        } else if ($tNotes[$num_table][$id_type_note][$id_matiere]['note'] > 15) {
                            $data[$id_type_note][$i]['points_mat'] = 5;
                            $total_points += 5;
                        } else {
                            $data[$id_type_note][$i]['points_mat'] = 0;
                        }
                    } else {
                        $data[$id_type_note][$i]['coeff_mat'] = $tNotes[$num_table][$id_type_note][$id_matiere]['coeff_mat'];
                        $data[$id_type_note][$i]['sur'] = 20 * $tNotes[$num_table][$id_type_note][$id_matiere]['coeff_mat'];
                        if ($tNotes[$num_table][$id_type_note][$id_matiere]['note'] >= 0) {
                            $data[$id_type_note][$i]['points_mat'] = $tNotes[$num_table][$id_type_note][$id_matiere]['note'] * $tNotes[$num_table][$id_type_note][$id_matiere]['coeff_mat'];
                        } else {
                            $data[$id_type_note][$i]['points_mat'] = 'Absent';
                        }
                        $total_points += $tNotes[$num_table][$id_type_note][$id_matiere]['note'] * $tNotes[$num_table][$id_type_note][$id_matiere]['coeff_mat'];
                    }
                    $total_coeff += $tNotes[$num_table][$id_type_note][$id_matiere]['coeff_mat'];
                    $total_sur += 20 * $tNotes[$num_table][$id_type_note][$id_matiere]['coeff_mat'];
                    $i++;
                }
                $tabPoints[$id_type_note] = $total_points;
                if ($id_type_note == 3) {
                    $data[$id_type_note][$i]['matiere'] = 'TOTAL';
                    $data[$id_type_note][$i]['points_mat'] = $total_points;
                    $data[$id_type_note][$i]['sur'] = $total_sur;
                    $data[$id_type_note][$i]['coeff_mat'] = $total_coeff;
                }
                if ($id_type_note != 3) {
                    $dataTotal[$id_type_note][$i]['matiere'] = 'TOTAL';
                    $dataTotal[$id_type_note][$i]['points_mat'] = $total_points + $tabPoints[4];
                    $dataTotal[$id_type_note][$i]['sur'] = $total_sur;
                    if ($eps == 2) {
                        $dataTotal[$id_type_note][$i]['coeff_mat'] = $total_coeff - 1;
                    } else {
                        $dataTotal[$id_type_note][$i]['coeff_mat'] = $total_coeff;
                    }

                }
/*
if($id_type_note==4) {
$data[$id_type_note][$i]['sur']='';
$data[$id_type_note][$i]['coeff_mat']='';;
}else {
$data[$id_type_note][$i]['sur']=$total_sur;
$data[$id_type_note][$i]['coeff_mat']=$total_coeff;
}
 */
                if ($id_type_note == 1) {$total_points_ecrits = $total_points;
                    $coeff_ecrit = $total_coeff;}

                if ($id_type_note == 4) {
                    $moyenne = ($total_points + $total_points_ecrits) / $coeff_ecrit;
                } else {
                    $moyenne = $total_points / $total_coeff;
                }

                if ($moyenne < 10) {
                    $long = 4;
                } else {
                    $long = 5;
                }

                $moy[$id_type_note] = str_replace('.', ',', substr($moyenne, 0, $long));

            }
        }

        if (is_array(!$data[4])) {
            $data2 = array_merge($data[1], $dataTotal[1]);
        } else {
            $data2 = array_merge($data[1], $dataTotal[1]);
        }
        // $data2 = array_merge($data[1], $dataTotal[1]);

        $titre_tableau = "EPREUVES ECRITES (A)";
        $pdf->ezText(utf8_decode("Cachet du centre</b>"), 11, array('justification' => 'right'));
        $pdf->ezTable($data2, $entete, $titre_tableau, $options);
        $pdf->ezText(utf8_decode("\n§ : Note éliminatoire (NE) - ** : Matière facultative"), 8, array('justification' => 'left'));
        if (is_array($data[4])) {
            $data4 = array_merge($data[4], $dataTotal[4]);
            $titre_tableau = "Epreuve faculatatives";
            $pdf->ezTable($data4, $entete, $titre_tableau, $options);
            //$pdf->ezText(utf8_decode("Moyenne (B): <b>$moy[4]/20</b>"), 12, array('justification' => 'center'));
        }
        $moyenne_generale = str_replace('.', ',', substr($tCandidats[$num_table]['moyenne'], 0, -1));
//        if($type_releve<3) $pdf->ezText(utf8_decode("MOYENNE GENERALE : <b>$moyenne_generale/20</b>"),12,array('justification'=>'center'));
        $pdf->ezText(utf8_decode("\nMoyenne Global (A): <b>$moyenne_generale/20</b>"), 12, array('justification' => 'left'));
        $pdf->ezSetDy(-10);

        if (is_array($data[3])) {
            $titre_tableau = "EPREUVES ORALES DE CONTROLE (B)";
            $pdf->ezTable($data[3], $entete, $titre_tableau, $options);
            $pdf->ezText(utf8_decode("Moyenne (B): <b>$moy[3]/20</b>"), 12, array('justification' => 'center'));

        }

/*        foreach(array(1,3) as $id_type_note){
if(in_array($id_type_note,$tTypes[$num_table])){
$type=selectReferentiel($lien,'type_note',$id_type_note);
if($id_type_note!=4) $pdf->ezText(utf8_decode("Moyenne: <b>$moyenne[$id_type_note]/20</b>"),12,array('justification'=>'center'));
elseif($id_type_note==4) { $pdf->ezText(utf8_decode("Moyenne: <b>$moyenne[$id_type_note]/20</b>"),12,array('justification'=>'center'));            }
$pdf->ezSetDy(-10);
}}
 */
//Injection de controle du chef lieu des regions en fonction des jurys
$sql_chefLieu = "SELECT region_division, chef_lieu, borne_jury, borne_jury_fin FROM bg_ref_region_division";
$result_chefLieu = bg_query($lien, $sql_chefLieu, __FILE__, __LINE__);

// Vérifie s'il y a des résultats
if (mysqli_num_rows($result_chefLieu) > 0) {
    // Parcourir chaque ligne du résultat
    while ($row_chefLieu = mysqli_fetch_array($result_chefLieu)) {
        // Vérifie si $jury se situe dans la plage déterminée pour cette ligne
        if ($jury >= $row_chefLieu['borne_jury'] && $jury < $row_chefLieu['borne_jury_fin']) {
            $chefLieu = $row_chefLieu['chef_lieu'];
            echo "Le chef-lieu pour le jury $jury est : $chefLieu";
        }
    }
} else {
    echo "Aucun résultat trouvé.";
}

        if ($type_releve <= 2) {
            $delib = $delib1;
        } else {
            $delib = $delib2;
        }

        if ($delib2 == 'Abien') {
            $delib = 'Passable';
        }

        $lien_photos = "../../photos/$annee/";
//        if($delib=='Passable' || $delib=='Abien' || $delib=='Bien' || $delib=='TBien') $pdf->addJpegFromFile($lien_photos.$nom_photo,470,680,75);
        //        else $pdf->addJpegFromFile($lien_photos.$nom_photo,233,60,80);

        if (in_array($delib, array('Refuse', 'Absent', 'Ajourne'))) {
            $delib = strtoupper($delib) . strtoupper($e);
        } elseif ($delib == 'Abandon') {
            $delib = strtoupper($delib);
        }

        if (in_array($delib, array('Abien', 'TBien'))) {
            $mention = strtoupper(str_replace('abien', 'ASSEZ - BIEN', str_replace('tbien', 'TRES - BIEN', strtolower($delib))));
        } else {
            $mention = $delib;
        }

        if (in_array($delib, array('Passable', 'Abien', 'Bien', 'TBien'))) {
            $texteMention = strtoupper("Admis$e avec la mention $mention");
        } else {
            $texteMention = strtoupper($delib);
        }

//        $pdf->ezSetDy(-5);
        $pdf->ezColumnsStart(array('num' => 2, 'gap' => 10));
        if ($president_jury == '') {$president_jury = presidentJurys($lien, $annee, $jury);}
        // $pdf->ezText(utf8_decode("DECISION DU JURY\n<b>$texteMention</b>\nFait à $chefLieu, le $date_delib"), 11, $options2);
        $pdf->ezText(utf8_decode("DECISION DU JURY :<b>$texteMention</b>"), 9, array('justification' => 'left'));

        $pdf->ezText(utf8_decode("\nDélibératin  le $date_delib, à $chefLieu,"), 9, array('justification' => 'left'));
        $pdf->ezText(utf8_decode("\nPrésident du Jury <b>$president_jury</b>"), 9, array('justification' => 'left'));
        $pdf->ezNewPage();
        //  $pdf->ezText(utf8_decode("Signature du Président du Jury\n\n\n<b>$president_jury</b>"), 11, $options2);
        $pdf->ezText(utf8_decode("Duplicata établi le <b> " . date('d') . '/' . date('m') . '/' . date('Y') . "</b> à Lomé"), 11, $options2);
        $pdf->ezColumnsStop();

        //$pdf->ezSetY(40);
        $pdf->ezSetY($height - 770);
        $pdf->ezText(html_entity_decode("Centre d'examen: <b>$centre </b>"), 8, array('justification' => 'centre'));
    }
}

//$pdf->ezStartPageNumbers(10,20,8,'right',"REPUBLIQUE TOGOLAISE - OFFICE DU BACCALAUREAT - $DateConvocPied - PAGE {PAGENUM} sur {TOTALPAGENUM}");
$pdfG = "liste_jury" . $jury . '_serie' . $serie;

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

<?php
if (!defined('_ECRIRE_INC_VERSION')) {
    return;
}

include_spip('inc/presentation'); // alleger les inclusions avec un inc/presentation_mini
include '../plugins/fonctions/generales.php';

setlocale(LC_TIME, "fr_FR");
define('MAX_LIGNES', 10);

function getNoteParAtelier($lien, $id_atelier, $sexe, $perf)
{
    if (in_array($id_atelier, array('1', '2', '4', '7'))) {
        if ($sexe == 1) {
            $andPerf = " AND fille <= $perf ";
        } else {
            $andPerf = " AND garcon <= $perf ";
        }

    } else {
        if ($sexe == 1) {
            $andPerf = " AND fille >= '$perf' ";
        } else {
            $andPerf = " AND garcon >= '$perf' ";
        }

    }
    if ($perf <= 0) {
        $note_perf = 0;
    } else {
        $sql = "SELECT max(note) as note_perf FROM bg_performance WHERE id_atelier='$id_atelier' $andPerf  ";
        $result = bg_query($lien, $sql, __FILE__, __LINE__);
        $row = mysqli_fetch_array($result);
        $note_perf = $row['note_perf'];
    }
    if ($note_perf == '') {
        $note_perf = 0;
    }

    // echo $sql . 'ZZ' . $note_perf;
    return $note_perf;
}

function getPerformanceEPS($lien, $annee, $num_table, $id_atelier)
{
    $sql = "SELECT performance, note_perf FROM bg_notes_performance WHERE annee=$annee AND num_table='$num_table' AND id_atelier='$id_atelier' LIMIT 0,1 ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $row = mysqli_fetch_array($result);
    $performance = $row['performance'];
    $note_perf = $row['note_perf'];
    $tab['performance'] = $performance;
    $tab['note_perf'] = $note_perf;
    return $tab;
}

function exec_sport()
{
    $exec = _request('exec');
    $titre = "exec_$exec";
    $commencer_page = charger_fonction('commencer_page', 'inc');
    $statut = getStatutUtilisateur();
    $lien = lien();
    echo $commencer_page($titre);

    foreach ($_REQUEST as $key => $val) {
        $$key = mysqli_real_escape_string($lien, trim(addslashes($val)));
    }
    $tab_auteur = $GLOBALS["auteur_session"];
    $login = $tab_auteur['login'];
    $lien_photos = "../photos/$annee/";
    if ($statut != 'Admin') {
        switch ($statut) {
            case 'Etablissement':
                $isOperateur = false;
                $isInspection = false;
                $isEncadrant = false;
                $isChefEtablissement = true;
                $cd_eta = stripslashes($tab_auteur['email']);
                $tab_cd_eta = explode('@', $cd_eta);
                $code_etablissement = $tab_cd_eta[1];
                //Recherche de la inspection
                $sql = "SELECT eta.id_inspection FROM bg_ref_etablissement eta
						WHERE eta.id='$code_etablissement' LIMIT 0,1 ";
                $result = bg_query($lien, $sql, __FILE__, __LINE__);
                $row = mysqli_fetch_array($result);
                $_REQUEST['id_inspection'] = $id_inspection = $row['id_inspection'];
                $etab = stripslashes($tab_auteur['nom_site']);
                $stats_etablissement = $code_etablissement;
                $andStatut = " AND etablissement='$code_etablissement'";
                $andEtablissementFormulaire = " AND eta.id=$code_etablissement";
                $andEtablissementFormulaire2 = " AND eta.id=$code_etablissement";
                $mois = date('m');
                if ($annee == '') {
                    $annee = recupAnnee($lien); //$annee = date("Y");
                }

                if ($mois > 10 && $annee == date("Y")) {
                    $annee += 1;
                }

                break;
            case 'Encadrant':
                $cd_enca = stripslashes($tab_auteur['email']);
                $tab_cd_enca = explode('@', $cd_enca);
                $code_encadrant = $tab_cd_enca[1];
                $id_region = $code_encadrant;

                $WhereRegion = " WHERE id=$id_region ";

                $andWhereRegion = "WHERE id_region=$id_region";

                //Recherche des etablissements de la region
                $sql = "SELECT eta.id FROM bg_ref_etablissement eta,bg_ref_inspection ins WHERE  ins.id=eta.id_inspection  AND ins.id_region=$id_region  ";
                $result = bg_query($lien, $sql, __FILE__, __LINE__);
                $tabEta = '(0';
                while ($row = mysqli_fetch_array($result)) {
                    $id = $row['id'];
                    $tabEta .= ',' . $id;
                }
                $tabEta .= ')';
                $andStatut = " AND etablissement IN $tabEta ";
                $andStatut2 = " AND can.etablissement IN $tabEta ";
                $andEtablissementFormulaire = " AND eta.id IN $tabEta ";
                $andEtablissementFormulaire2 = " AND eta.id IN $tabEta ";
                $isChefEtablissement = false;
                $isOperateur = false;
                $isInspection = false;
                $isEncadrant = true;
                $isAdmin = false;
                break;
            case 'Dre':
                $cd_dre = stripslashes($tab_auteur['email']);
                $tab_cd_dre = explode('@', $cd_dre);
                $code_dre = $tab_cd_dre[1];
                $id_region = $code_dre;

                $WhereRegion = " WHERE id=$id_region ";

                $andWhereRegion = "WHERE id_region=$id_region";

                //Recherche des etablissements de la region
                $sql = "SELECT eta.id FROM bg_ref_etablissement eta,bg_ref_inspection ins WHERE  ins.id=eta.id_inspection  AND ins.id_region=$id_region  ";
                $result = bg_query($lien, $sql, __FILE__, __LINE__);
                $tabEta = '(0';
                while ($row = mysqli_fetch_array($result)) {
                    $id = $row['id'];
                    $tabEta .= ',' . $id;
                }
                $tabEta .= ')';

                $andStatut = " AND etablissement IN $tabEta ";
                $andStatut2 = " AND can.etablissement IN $tabEta ";
                $andEtablissementFormulaire = " AND eta.id IN $tabEta ";
                $andEtablissementFormulaire2 = " AND eta.id IN $tabEta ";
                $isChefEtablissement = false;
                $isOperateur = false;
                $isInspection = false;
                $isEncadrant = true;
                $isDre = true;
                $isAdmin = false;
                break;
            case 'Notes':
                $cd_not = stripslashes($tab_auteur['email']);
                $tab_cd_not = explode('@', $cd_not);
                $cd_note = $tab_cd_not[1];
                $id_region = $cd_note;

                $WhereRegion = " WHERE id=$id_region ";

                $andWhereRegion = "WHERE id_region=$id_region";

                //Recherche des etablissements de la region
                $sql = "SELECT eta.id FROM bg_ref_etablissement eta,bg_ref_inspection ins WHERE  ins.id=eta.id_inspection  AND ins.id_region=$id_region  ";
                $result = bg_query($lien, $sql, __FILE__, __LINE__);
                $tabEta = '(0';
                while ($row = mysqli_fetch_array($result)) {
                    $id = $row['id'];
                    $tabEta .= ',' . $id;
                }
                $tabEta .= ')';

                $andStatut = " AND etablissement IN $tabEta ";
                $andStatut2 = " AND can.etablissement IN $tabEta ";
                $andEtablissementFormulaire = " AND eta.id IN $tabEta ";
                $andEtablissementFormulaire2 = " AND eta.id IN $tabEta ";
                $isChefEtablissement = false;
                $isOperateur = false;
                $isInspection = false;
                $isEncadrant = true;
                $isAdmin = false;
                break;
            case 'Eps':
                $cd_not = stripslashes($tab_auteur['email']);
                $tab_cd_not = explode('@', $cd_not);
                $cd_note = $tab_cd_not[1];
                $id_region = $cd_note;

                $WhereRegion = " WHERE id=$id_region ";

                $andWhereRegion = "WHERE id_region=$id_region";

                //Recherche des etablissements de la region
                $sql = "SELECT eta.id FROM bg_ref_etablissement eta,bg_ref_inspection ins WHERE  ins.id=eta.id_inspection  AND ins.id_region=$id_region  ";
                $result = bg_query($lien, $sql, __FILE__, __LINE__);
                $tabEta = '(0';
                while ($row = mysqli_fetch_array($result)) {
                    $id = $row['id'];
                    $tabEta .= ',' . $id;
                }
                $tabEta .= ')';

                $andStatut = " AND etablissement IN $tabEta ";
                $andStatut2 = " AND can.etablissement IN $tabEta ";
                $andEtablissementFormulaire = " AND eta.id IN $tabEta ";
                $andEtablissementFormulaire2 = " AND eta.id IN $tabEta ";
                $isChefEtablissement = false;
                $isOperateur = false;
                $isInspection = false;
                $isEncadrant = true;
                $isAdmin = false;
                break;
            case 'Operateur':
                $andStatut = " AND login='$login'";
                $isOperateur = true;
                $isChefEtablissement = false;
                $isInspection = false;
                break;
            case 'Inspection':
                $insp = stripslashes($tab_auteur['email']);
                $tabInsp = explode('@', $insp);
                $idInsp = $tabInsp[1];
                //Recherche de la region de l'inspection
                $sql1 = "SELECT * FROM bg_ref_inspection WHERE id='$idInsp' ";
                $result1 = bg_query($lien, $sql1, __FILE__, __LINE__);
                $row1 = mysqli_fetch_array($result1);
                $id_region = $row1['id_region'];
                $WhereRegion = " WHERE id='$id_region' ";

                $andWhereInspection = "WHERE id_region=$id_region AND id=$idInsp";

                $id_inspection = $idInsp;

                //Recherche des etablissements de l'inspection
                $sql = "SELECT id FROM bg_ref_etablissement WHERE id_inspection='$idInsp' ";
                $result = bg_query($lien, $sql, __FILE__, __LINE__);
                $tabEta = '(0';
                while ($row = mysqli_fetch_array($result)) {
                    $id = $row['id'];
                    $tabEta .= ',' . $id;
                }
                $tabEta .= ')';
                $andStatut = " AND etablissement IN $tabEta ";
                $andStatut2 = " AND can.etablissement IN $tabEta ";
                $andEtablissementFormulaire = " AND eta.id IN $tabEta ";
                $andEtablissementFormulaire2 = " AND eta.id IN $tabEta ";
                $isChefEtablissement = false;
                $isOperateur = false;
                $isInspection = true;
                $mois = date('m');
                if ($annee == '') {
                    $annee = recupAnnee($lien);
                }

                if ($mois > 10 && $annee == date("Y")) {
                    $annee += 1;
                }

                break;
            case 'Informaticien':
                $isAdmin = true;
                break;
            default:
                die(KO . " - Statut <b>$statut</b> invalide");
        }
    } else {
        $isAdmin = true;
    }
    if ($annee == '') {
        $annee = recupAnnee($lien); //$annee = date("Y");
    }
    if ($id_type_session == '') {
        $id_type_session = recupSession($lien);
    }

    if (isset($_REQUEST['id_atelier'])) {
        if ($id_atelier == 3) {
            $diviseur = 130;
        } elseif ($id_atelier == 5 || $id_atelier == 6) {
            $diviseur = 200;
        } else {
            $diviseur = 400;
        }

    }
    $tab_auteur = $GLOBALS["auteur_session"];
    $login = $tab_auteur['login'];

    echo debut_grand_cadre();

    echo debut_cadre_trait_couleur('', '', '', "Gestion du sport", "", "", false);

    if (isAutorise(array('Admin', 'Dre', 'Notes', 'Eps', 'Encadrant', 'Etablissement', 'Inspection', 'Informaticien'))) {
        if ($statut == 'Admin' || $statut == 'Encadrant') {
            if (isset($_POST['enreg_perf'])) {
                for ($note = 1; $note <= 20; $note++) {
                    $sql = "REPLACE INTO bg_performance (id_atelier, note, fille, garcon) VALUES ($id_atelier, $note, '" . $_REQUEST['fille'][$note] . "', '" . $_REQUEST['garcon'][$note] . "')";
                    bg_query($lien, $sql, __FILE__, __LINE__);
                }
            }
        }

        if (!isset($_REQUEST['etape'])) {
            echo "<div class='onglets_simple clearfix'>
			<ul>";

            if ($statut == 'Admin') {
                echo "<li><a href='./?exec=sport&etape=param' class='ajax'>Paramétrer</a></li>";

            }
            if ($statut == 'Encadrant' || $statut == 'Inspection' || $statut == 'Etablissement' || $statut == 'Admin') {
                echo "<li><a href='./?exec=sport&etape=impres' class='ajax'>Impressions</a></li>";
            }
            if ($statut == 'Encadrant' || $statut == 'Inspection' || $statut == 'Admin') {
                if ($isEncadrant) {
                    $sqlr = "SELECT * FROM bg_ref_region WHERE id=$id_region ORDER BY region ";
                } elseif ($isInspection) {
                    $sqlr = "SELECT * FROM bg_ref_region WHERE id=$id_region ORDER BY region ";
                } else { $sqlr = "SELECT * FROM bg_ref_region  ORDER BY region ";}

                $resultr = bg_query($lien, $sqlr, __FILE__, __LINE__);
                while ($rowr = mysqli_fetch_array($resultr)) {
                    $id_region = $rowr['id'];
                    echo "<li><a href='./?exec=sport&etape=saispate&id_region=$id_region' class='ajax'>" . $rowr['region'] . "</a></li>";
                }
            }
            echo "</ul>
			</div>";
        }

        if (isset($_REQUEST['etape']) && ($_REQUEST['etape'] == 'param' || $_REQUEST['etape'] == 'saispate') && !isset($_REQUEST['id_etablissement'])) {
            echo "<div class='onglets_simple clearfix'><ul>";
            $sql = "SELECT * FROM bg_ref_atelier ORDER BY atelier";
            $result = bg_query($lien, $sql, __FILE__, __LINE__);
            while ($row = mysqli_fetch_array($result)) {
                echo "<li><a href='./?exec=sport&etape=" . $_REQUEST['etape'] . "&id_atelier=" . $row['id'] . "&id_region=" . $_REQUEST['id_region'] . "' class='ajax'>" . $row['atelier'] . "</a></li>";
            }
            echo "<li><a href='./?exec=sport' class='ajax'><b>RETOUR</b></a></li>";
            echo "</ul></div>";
        }
        #Paramétrage des EPS
        if ($statut == 'Admin') {
            if ($_REQUEST['etape'] == 'param' && isset($_REQUEST['id_atelier']) && !isset($_POST['enreg_perf'])) {
                $nbPerf = getNbPerformance($lien, $id_atelier);
                if (($nbPerf < 20) && ($nbPerf == 0)) {
                    for ($i = 1; $i <= 20; $i++) {
                        $sql1 = "INSERT INTO bg_performance (`id_atelier`,`note`,`fille`,`garcon`)
                    VALUES('$id_atelier','$i','','')";
                        bg_query($lien, $sql1, __FILE__, __LINE__);
                    }

                }

                $sql = "SELECT * FROM bg_performance WHERE id_atelier=$id_atelier ORDER BY note ";
                $result = bg_query($lien, $sql, __FILE__, __LINE__);
                $unite = getUniteAtelier($lien, $id_atelier);
                $form = "<center><h2>Barèmes de " . strtoupper(selectReferentiel($lien, 'atelier', $id_atelier)) . " en " . $unite . " </h2></center>";
                $form .= "<table><form name='form_perf' method='POST' >";
                $form .= "<th widht=50 >Note / 20</th><th>Fille</th><th>Garçon</th>";
                while ($row = mysqli_fetch_array($result)) {
                    foreach (array('note', 'fille', 'garcon') as $val) {
                        $$val = $row[$val];
                    }

                    if (in_array($id_atelier, array('1', '4', '7'))) {
                        $form .= "<tr><td><b>$note</b></td><td><input type='number' value='$fille' id='fille[$note]' name='fille[$note]' size='20' required /></td><td><input type='number' value='$garcon' id='garcon[$note]' name='garcon[$note]' size='20' required /></td></tr>";
                    } elseif (in_array($id_atelier, array('5', '6'))) {
                        $form .= "<tr><td><b>$note</b></td><td><input type='text' value='$fille' id='fille[$note]' name='fille[$note]' size='20' pattern='^(?:[0-5][0-9]):[0-9]$' title='59:99' required /></td><td><input type='text' value='$garcon' id='garcon[$note]' name='garcon[$note]' size='20' pattern='^(?:[0-5][0-9]):[0-9]$' title='59:99' required /></td></tr>";
                    } elseif (in_array($id_atelier, array('2'))) {
                        $form .= "<tr><td><b>$note</b></td><td><input type='number' step='0.01' value='$fille' id='fille[$note]' name='fille[$note]' size='20' required /></td><td><input type='number' step='0.01' value='$garcon' id='garcon[$note]' name='garcon[$note]' size='20' required /></td></tr>";
                    } else {
                        $form .= "<tr><td><b>$note</b></td><td><input type='text' value='$fille' id='fille[$note]' name='fille[$note]' size='20' pattern='^(?:[0-9]):[0-5][0-9]:[0-9]$' title='59:59:99' required /></td><td><input type='text' value='$garcon' id='garcon[$note]' name='garcon[$note]' size='20' pattern='^(?:[0-9]):[0-5][0-9]:[0-9]$' title='59:59:99' required /></td></tr>";
                    }
                }
                $form .= "<tr><td><center><input type='reset' value='Annuler' /></center></td><td><center><input type='submit' value='Enregistrer' name='enreg_perf' /></center></td><td><center><input type='button' value='Quitter' onClick=\"window.location='" . generer_url_ecrire('sport') . "&etape=param'; \" /></center></td></tr>";
                $form .= "<tr><td colspan=3><center><a href='../plugins/fonctions/inc-pdf-eps_criteres.php?id_atelier=$id_atelier'>IMPRIMER <img src='../plugins/images/imprimer.png'/> $centre</a></center></td></tr>";
                $form .= "</form></table>";
                echo $form;
            }
        }

        if ($_REQUEST['etape'] == 'saispate' && isset($_REQUEST['id_atelier']) && !isset($_REQUEST['id_etablissement'])) {
            $sql_cent = "SELECT eta.id as id_etablissement, eta.etablissement, count(*) as nbre
				FROM bg_repartition rep, bg_candidats can, bg_ref_etablissement eta, bg_ref_region_division reg, bg_ref_region rg
				WHERE can.annee=$annee AND rep.annee=$annee AND can.num_table=rep.num_table
				AND can.etablissement=eta.id AND reg.id=eta.id_region_division AND rg.id=reg.id_region AND rg.id=$id_region
				AND (can.atelier1=$id_atelier OR can.atelier2=$id_atelier OR can.atelier3=$id_atelier)
				AND si_centre='non'
				GROUP BY eta.id ORDER BY reg.region_division, etablissement ";
            $result_cent = bg_query($lien, $sql_cent, __FILE__, __LINE__);
            $form2 = "<table><form name='form_centre' method='POST' >";
            while ($row_cent = mysqli_fetch_array($result_cent)) {
                foreach (array('id_etablissement', 'etablissement', 'nbre') as $val) {
                    $$val = $row_cent[$val];
                }

                //nbre de notes saisies par centre et par atelier
                $sql_nb = "SELECT notes.num_table
					FROM bg_notes_performance notes, bg_candidats can
					WHERE notes.annee=$annee AND can.annee=$annee AND notes.num_table=can.num_table
					AND notes.id_atelier=$id_atelier AND can.etablissement=$id_etablissement ";
                $result_nb = bg_query($lien, $sql_nb, __FILE__, __LINE__);
                $saisies = mysqli_num_rows($result_nb);
                if ($saisies < $nbre && $saisies == 0) {
                    $color = 'red';
                } elseif ($saisies < $nbre && $saisies > 0) {
                    $color = 'blue';
                } elseif ($saisies == $nbre) {
                    $color = 'black';
                }
                if ($nbre < $diviseur) {
                    $form2 .= "<tr><td><input type='button' value=\"$etablissement\" onClick=\"window.location='" . generer_url_ecrire('sport') . "&etape=saispate&id_atelier=$id_atelier&id_etablissement=$id_etablissement&id_region=$id_region';\" /></td><td><b><font color='$color'>$saisies/$nbre</font></b></td></tr>";
                } else {
                    for ($i = 0; $i < ceil($nbre / $diviseur); $i++) {
                        $j = $i + 1;
                        $l = $i * $diviseur;
                        $form2 .= "<tr><td><input type='button' value=\"$etablissement [$j]\" onClick=\"window.location='" . generer_url_ecrire('sport') . "&etape=saispate&id_atelier=$id_atelier&id_etablissement=$id_etablissement&id_region=$id_region&l=$l';\" /></td><td><b><font color='$color'>$saisies/$nbre</font></b></td></tr>";
                    }
                }
            }
            //echo $sql_cent;
            $form2 .= "</form></table>";
            echo $form2;
        }

        if (isset($_POST['enregistrer_perf'])) {
            foreach ($_POST['tPerf'] as $num_table => $perf) {
                if ($perf != '') {
                    if ($perf < 0 || $perf == '-' || $perf == 0) {
                        $perf = -1;
                    }

                    if ($_POST['tSexe'][$num_table] == 1) {
                        $sexe = 1;
                    } else {
                        $sexe = 2;
                    }

                    if (in_array($id_atelier, array('5', '6'))) {$perf .= ':' . $_POST['tPerf2'][$num_table];}
                    if (in_array($id_atelier, array('3'))) {$perf .= ':' . str_pad($_POST['tPerf2'][$num_table], 2, 0, STR_PAD_LEFT) . ':' . $_POST['tPerf3'][$num_table];}

                    $note_perf = getNoteParAtelier($lien, $id_atelier, $sexe, $perf);

                    $sql2 = "REPLACE INTO bg_notes_performance (num_table, annee, id_atelier, performance, note_perf, login) VALUES ('$num_table','$annee','$id_atelier','$perf','$note_perf','$login') ";
                    bg_query($lien, $sql2, __FILE__, __LINE__);

                    $sql3 = "REPLACE INTO bg_notes_eps (num_table,annee,note) SELECT num_table, annee, SUM(note_perf)/3 note FROM bg_notes_performance WHERE annee=$annee AND num_table='$num_table' ";
                    bg_query($lien, $sql3, __FILE__, __LINE__);
                }
            }
        }

        //Affichage des champs pour la saisie des performances
        if (isset($_GET['id_etablissement']) && isset($_GET['id_atelier'])) {
            $atelier = selectReferentiel($lien, 'atelier', $id_atelier);
            $etablissement = selectReferentiel($lien, 'etablissement', $id_etablissement);
            echo debut_cadre_enfonce(), "<b>Atelier: $atelier &nbsp;	ETABLISSEMENT: $etablissement &nbsp;&nbsp;</b> ", fin_cadre_enfonce();

            //Formulaire de modification de la performance de candidat
            if (isset($_GET['num_table']) && isset($_POST['modif'])) {
                $sql1 = "INSERT INTO bg_histo_notes_performance (`num_table`, `annee`, `id_atelier`, `performance`, `note_perf`, `maj`, `login`)
				SELECT `num_table`, `annee`, `id_atelier`, `performance`, `note_perf`, `maj`, `login`
				FROM bg_notes_performance WHERE annee=$annee AND num_table='$num_table' AND id_atelier='$id_atelier' ";
                bg_query($lien, $sql1, __FILE__, __LINE__);
                if ($performance2 != '') {
                    if ($performance2 < 0 || $performance2 == '-' || $performance2 == 0) {
                        $performance2 = -1;
                    }

                    if (in_array($id_atelier, array('5', '6'))) {$performance2 .= ':' . $_POST['performance22'];}
                    if (in_array($id_atelier, array('3'))) {$performance2 .= ':' . str_pad($_POST['performance22'], 2, 0, STR_PAD_LEFT) . ':' . $_POST['performance23'];}

                    $note_perf = getNoteParAtelier($lien, $id_atelier, $id_sexe, $performance2);

                    $sql2 = "REPLACE INTO bg_notes_performance (num_table, annee, id_atelier, performance, note_perf, login) VALUES ('$num_table','$annee','$id_atelier','$performance2','$note_perf','$login') ";
                    bg_query($lien, $sql2, __FILE__, __LINE__);

                    $sql3 = "REPLACE INTO bg_notes_eps (num_table,annee,note) (SELECT num_table, annee, SUM(note_perf)/3 note FROM bg_notes_performance WHERE annee=$annee AND num_table='$num_table')";
                    bg_query($lien, $sql3, __FILE__, __LINE__);
                }
            }

            if (isset($_GET['num_table']) && !isset($_POST['modif'])) {
                $tabNP = getPerformanceEPS($lien, $annee, $num_table, $id_atelier);
                $tCan = getNoms($lien, $annee, $num_table);
                $noms = $tCan['noms'];
                $id_sexe = $tCan['sexe'];
                $performance1 = $tabNP['performance'];
                if ($performance1 <= 0) {$performance1 = "-";
                    $remarque = "<font color='blue'>ABSENT</font>";} else { $remarque = '';}
                echo debut_cadre_enfonce();
                $formModNote = "<form name='' action='' method='POST'><table>";

                if (in_array($id_atelier, array('5', '6', '3')) && $performance1 != '') {
                    $tabPerf = explode(':', $performance1);
                    $perfor1 = $tabPerf[0];
                    $perfor2 = $tabPerf[1];
                    $perfor3 = $tabPerf[2];
                } else {
                    $perfor1 = $perfor2 = $perfor3 = '';
                }

                if (in_array($id_atelier, array('1', '4'))) {
                    $input = "<input type='number' name='performance2' id='performance2' value='$performance1' selected size=5 maxlength=10 onKeyUp=\"if(this.value!='-' && isNaN(parseInt(this.value))) this.value='';\" autofocus />";
                } elseif (in_array($id_atelier, array('2'))) {
                    $input = "<input type='number' step='any' min='0' max='14' name='performance2' id='performance2' value='$performance1' selected size=5 maxlength=10 onKeyUp=\"if(this.value!='-' && isNaN(parseInt(this.value))) this.value='';\" autofocus />";
                } elseif (in_array($id_atelier, array('5', '6'))) {
                    $input = "<input type='text' min=0 max=59 id='performance2' name='performance2' value=\"$perfor1\" size=1 maxlength=2 onKeyUp=\"if((this.value!='-' && isNaN(parseInt(this.value))) || this.value>59) this.value='';\" autofocus /> : <input type='text' min=0 max=9 id='performance22' name='performance22' value=\"$perfor2\" size=1 maxlength=1 onKeyUp=\"if(this.value!='-' && isNaN(parseInt(this.value))) this.value='';\" autofocus />";
                } elseif (in_array($id_atelier, array('3'))) {
                    $input = "<input type='text' min=0 max=9 id='performance2' name='performance2' value=\"$perfor1\" size=1 maxlength=1 onKeyUp=\"if(this.value!='-' && isNaN(parseInt(this.value))) this.value='';\" autofocus /> : <input type='text' min=0 max=59 id='performance22' name='performance22' value=\"$perfor2\" size=2 maxlength=2 onKeyUp=\"if((this.value!='-' && isNaN(parseInt(this.value))) || this.value>59) this.value='';\" autofocus /> : <input type='text' min=0 max=9 id='performance23' name='performance23' value=\"$perfor3\" size=1 maxlength=1 onKeyUp=\"if(this.value!='-' && isNaN(parseInt(this.value))) this.value='';\" autofocus />";
                }

                $formModNote .= "<th>Candidat : $num_table $noms </th><th>$input $remarque</th><th><input type='hidden' name='modif' /><input type='submit' name='Modifier_note' value='OK' /><input type='hidden' name='id_sexe' value='$id_sexe' /></th>";
                $formModNote .= "</table></form>";
                echo $formModNote;
                echo fin_cadre_enfonce();

            }

            if (isset($_REQUEST['l']) && $l != '') {$limit = " LIMIT $l, $diviseur";}
            $sql = "SELECT eta.id as id_etablissement, eta.etablissement, can.num_table, nom, prenoms, sexe as id_sexe
				FROM bg_repartition rep, bg_candidats can, bg_ref_etablissement eta
				WHERE can.annee=$annee AND rep.annee=$annee AND can.num_table=rep.num_table
				AND can.etablissement=eta.id AND eta.id=$id_etablissement
				AND (can.atelier1=$id_atelier OR can.atelier2=$id_atelier OR can.atelier3=$id_atelier)
				ORDER BY can.num_table, can.nom, prenoms $limit ";
            $result = bg_query($lien, $sql, __FILE__, __LINE__);
            $unite = getUniteAtelier($lien, $id_atelier);
            $formNotes = "<form name='' method='POST' action=''>";
            $formNotes .= "<table>";
            $formNotes .= "<th>Candidat</th><th>Numéro de table</th><th>Performance <br/>($unite)</th><th>Note</th>";
            while ($row = mysqli_fetch_array($result)) {
                foreach (array('id_etablissement', 'etablissement', 'nom', 'prenoms', 'num_table', 'id_sexe') as $val) {
                    $$val = $row[$val];
                }

                $sql_ver = "SELECT * FROM bg_notes_performance WHERE annee='$annee' AND num_table='$num_table' AND id_atelier='$id_atelier' ";
                $result_ver = bg_query($lien, $sql_ver, __FILE__, __LINE__);
                if (mysqli_num_rows($result_ver) == 1) {
                    $row_ver = mysqli_fetch_array($result_ver);
                    $performance = $row_ver['performance'];
                    $note_perf = $row_ver['note_perf'];
                    $disabled = 'disablede';
                } else {
                    $performance = $note_perf = '';
                    $disabled = '';
                    $performance = '';
                }
                if ($performance < 0) {$performance = "N/C";
                    $remarque = "<font color='blue'>ABSENT</font>";} else { $remarque = '';}

                //Format des champs
                if (in_array($id_atelier, array('5', '6', '3')) && $performance != '') {
                    $tabPerf = explode(':', $performance);
                    $performance1 = $tabPerf[0];
                    $performance2 = $tabPerf[1];
                    $performance3 = $tabPerf[2];
                } else {
                    $performance1 = $performance2 = $performance3 = '';
                }
                if (in_array($id_atelier, array('1', '4'))) {
                    $input = "<input type='number' id='tPerf[$num_table]' name='tPerf[$num_table]' value=\"$performance\" $disabled size=5 maxlength=5 onKeyUp=\"if(this.value!='-' && isNaN(parseInt(this.value))) this.value='';\" autofocus />";
                } elseif (in_array($id_atelier, array('2'))) {
                    $input = "<input type='number' step='any' min='0' max='14' id='tPerf[$num_table]' name='tPerf[$num_table]' value=\"$performance\" $disabled size=5 maxlength=5 onKeyUp=\"if(this.value!='-' && isNaN(parseInt(this.value))) this.value='';\" autofocus />";
                } elseif (in_array($id_atelier, array('5', '6'))) {
                    $input = "<input type='text' min=0 max=59 id='tPerf[$num_table]' name='tPerf[$num_table]' value=\"$performance1\" $disabled size=1 maxlength=2 onKeyUp=\"if((this.value!='-' && isNaN(parseInt(this.value))) || this.value>59) this.value=''; else {if (this.value.length == 2 ) { document.getElementById('tPerf2[$num_table]').focus();}} \" autofocus  /> : <input type='text' min=0 max=9 id='tPerf2[$num_table]' name='tPerf2[$num_table]' value=\"$performance2\" $disabled size=1 maxlength=1 onKeyUp=\"if(this.value!='-' && isNaN(parseInt(this.value))) this.value='';\" />";
                } elseif (in_array($id_atelier, array('3'))) {
                    $input = "<input type='text' min=0 max=9 id='tPerf[$num_table]' name='tPerf[$num_table]' value=\"$performance1\" $disabled size=1 maxlength=1 onKeyUp=\"if(this.value!='-' && isNaN(parseInt(this.value))) this.value=''; else {if (this.value.length == 1 ) { document.getElementById('tPerf2[$num_table]').focus();}} \" autofocus /> : <input type='text' min=0 max=59 id='tPerf2[$num_table]' name='tPerf2[$num_table]' value=\"$performance2\" $disabled size=2 maxlength=2 onKeyUp=\"if((this.value!='-' && isNaN(parseInt(this.value))) || this.value>59) this.value=''; else {if (this.value.length == 2 ) { document.getElementById('tPerf3[$num_table]').focus();}} \" /> : <input type='text' min=0 max=9 id='tPerf3[$num_table]' name='tPerf3[$num_table]' value=\"$performance3\" $disabled size=1 maxlength=1 onKeyUp=\"if(this.value!='-' && isNaN(parseInt(this.value))) this.value='';\" />";
                } elseif (in_array($id_atelier, array('7'))) {
                    $input = "<input type='number' id='tPerf[$num_table]' name='tPerf[$num_table]' value=\"$performance\" $disabled size=5 maxlength=5 onKeyUp=\"if(this.value!='-' && isNaN(parseInt(this.value))) this.value='';\" autofocus />";
                }

                if ($statut != 'Admin') {
                    // $note_perf2 = '';
                    $note_perf2 = $note_perf;
                } else {
                    $note_perf2 = $note_perf;
                }

                $formNotes .= "<tr><td><b><a tabindex='-1' href='" . generer_url_ecrire('sport') . "&etape=saispate&id_atelier=$id_atelier&id_etablissement=$id_etablissement&num_table=$num_table&id_region=$id_region'>$num_table</a></b></td><td><a tabindex='-1' href='" . generer_url_ecrire('sport') . "&etape=saispate&id_atelier=$id_atelier&id_etablissement=$id_etablissement&num_table=$num_table&id_region=$id_region'>$nom $prenoms</a></td>
					<td><b>$input</b></td>
					<td><b><input type='text' disabled value='$note_perf2' size=1/> $remarque</b></td></tr>";
                $formNotes .= "<tr><td colspan=3><input type='hidden' name='tSexe[$num_table]' value=\"$id_sexe\"  /></td></tr>";
            }
            $formNotes .= "<tr><td><input type='hidden' name='id_atelier' value='$id_atelier'/></td><td><input type='hidden' name='id_etablissement' value='$id_etablissement' /></td><td></td><td></td></tr>";
            $formNotes .= "<tr><td colspan=2><center><input type='submit' class='spip' value='Enregistrer' name='enregistrer_perf' /></center></td><td colspan=2><center><input type='reset' value='Quitter' onclick=window.location='" . generer_url_ecrire('sport') . "&etape=saispate&id_atelier=$id_atelier&id_region=$id_region' /></center></td></tr>";
            $formNotes .= "</table></form>";
            echo $formNotes;
        }

        if (isset($_REQUEST['tache'])) {
            $cles = array_keys($_REQUEST['tache']);
            $tache = $cles[0];
            $andWhere = '';
            $andWhereCentre = '';
            if ($id_centre != 0) {
                $andWhere .= " AND cen.id=$id_centre ";
                $andWhereCentre .= " AND cen.id=$id_centre ";
            }

            if ($id_serie != 0) {
                $andWhere .= " AND ser.id=$id_serie ";
                $andWhereCentre .= " AND ser.id=$id_serie ";
            }

            if ($id_etablissement != 0) {
                $andWhere .= " AND eta.id=$id_etablissement ";
            }

            if ($id_atelier != 0) {
                $andWhereAtelier = " AND (can.atelier1=$id_atelier || can.atelier2=$id_atelier || can.atelier3=$id_atelier) ";
            }

            if ($id_etablissement == 0) {
                $andWhere .= " AND eta.id=0 ";
            }

            switch ($tache) {
                case 'liste_candidats':
                case 'candidats_atelier':
                    $sql = "SELECT nom, prenoms, can.id_candidat, ser.serie, sex.sexe, can.num_table, sex.id as id_sexe, eta.id_centre
				FROM bg_ref_sexe sex, bg_ref_serie ser, bg_ref_etablissement eta, bg_ref_etablissement cen, bg_candidats can
				LEFT JOIN bg_repartition rep ON (rep.annee=$annee AND rep.num_table=can.num_table)
				WHERE can.annee=$annee AND can.etablissement=eta.id AND sex.id=can.sexe
				AND can.centre=cen.id AND can.serie=ser.id $andWhere $andWhereAtelier
				ORDER BY nom, prenoms $limit ";

                    $result = bg_query($lien, $sql, __FILE__, __LINE__);
                    echo "<a href='" . generer_url_ecrire('sport') . "&etape=impres'><center><b>RETOUR A LA PAGE PRECEDENTE</b></center></a><br/>";
                    if (mysqli_num_rows($result) > 0 || $id_etablissement == 0) {
                        $imprimer = "<center><a href='../plugins/fonctions/inc-liste_eps.php?annee=$annee&t=l&c=$id_centre&e=$id_etablissement&s=$id_serie&a=$id_atelier'><img src='../plugins/images/imprimer1.jpg' width='150px' /></a></center>";
                        $liste = "<h1>Liste des candidats aptes à l'épreuve d'EPS</h1><br/>" . $imprimer;
                        $liste .= "<table>";
                        $liste .= "<th>Numéros</th><th>Noms et prenoms</th><th>Séries</th>";
                        while ($row = mysqli_fetch_array($result)) {
                            foreach (array('nom', 'prenoms', 'serie', 'num_table', 'sexe', 'id_candidat') as $val) {
                                $$val = $row[$val];
                            }

                            if ($sexe == 1) {
                                $civ = 'Mlle';
                            } else {
                                $civ = 'M.';
                            }

                            $liste .= "<tr><td>$id_candidat / $num_table</td><td>$civ $nom $prenoms</td><td>$serie</td></tr>";
                        }
                        $liste .= "</table>" . $imprimer;
                        echo $liste;
                    } else {
                        echo debut_boite_alerte(), "<h2>Aucun candidat pour ces critères</h2>", fin_boite_alerte();
                    }
                    break;
                case 'candidats_centre_atelier':
                    $sql = "SELECT nom, prenoms, can.id_candidat, ser.serie, sex.sexe, can.num_table, sex.id as id_sexe, eta.id_centre
				FROM bg_ref_sexe sex, bg_ref_serie ser, bg_ref_etablissement eta, bg_ref_etablissement cen, bg_candidats can
				LEFT JOIN bg_repartition rep ON (rep.annee=$annee AND rep.num_table=can.num_table)
				WHERE   can.annee=$annee $andWhereCentre $andWhereAtelier AND can.etablissement=eta.id AND sex.id=can.sexe
				AND can.centre=cen.id AND can.serie=ser.id
				ORDER BY nom, prenoms $limit ";

                    $result = bg_query($lien, $sql, __FILE__, __LINE__);
                    echo "<a href='" . generer_url_ecrire('sport') . "&etape=impres'><center><b>RETOUR A LA PAGE PRECEDENTE</b></center></a><br/>";
                    if (mysqli_num_rows($result) > 0 || $id_centre != 0) {
                        $imprimer = "<center><a href='../plugins/fonctions/inc-liste_eps_centre.php?annee=$annee&t=l&centre_id=$id_centre&a=$id_atelier'><img src='../plugins/images/imprimer1.jpg' width='150px' /></a></center>";
                        $liste = "<h1>Liste des candidats aptes à l'épreuve d'EPS</h1><br/>" . $imprimer;
                        $liste .= "<table>";
                        $liste .= "<th>Numéros</th><th>Noms et prenoms</th><th>Séries</th>";
                        while ($row = mysqli_fetch_array($result)) {
                            foreach (array('nom', 'prenoms', 'serie', 'num_table', 'sexe', 'id_candidat') as $val) {
                                $$val = $row[$val];
                            }

                            if ($sexe == 1) {
                                $civ = 'Mlle';
                            } else {
                                $civ = 'M.';
                            }

                            $liste .= "<tr><td>$id_candidat / $num_table</td><td>$civ $nom $prenoms</td><td>$serie</td></tr>";
                        }
                        $liste .= "</table>" . $imprimer;
                        echo $liste;
                    } else {
                        echo debut_boite_alerte(), "<h2>Aucun candidat pour ces critères</h2>", fin_boite_alerte();
                    }
                    break;

                case 'performances_atelier':
                    $sql = "SELECT nom, prenoms, can.num_table, ser.serie, can.sexe, performance, note_perf
					FROM bg_candidats can, bg_ref_serie ser, bg_repartition rep, bg_ref_etablissement eta, bg_ref_etablissement cen, bg_notes_performance perf
					WHERE can.annee=$annee AND rep.annee=$annee AND perf.annee=$annee AND can.num_table=perf.num_table AND can.num_table=rep.num_table AND can.etablissement=eta.id
					AND rep.id_centre=cen.id AND can.serie=ser.id AND perf.id_atelier=$id_atelier $andWhere $andWhereAtelier
					ORDER BY can.num_table ";
                    $result = bg_query($lien, $sql, __FILE__, __LINE__);
                    echo "<a href='" . generer_url_ecrire('sport') . "&etape=impres'><center><b>RETOUR A LA PAGE PRECEDENTE</b></center></a><br/>";
                    if (mysqli_num_rows($result) > 0) {
                        $unite = getUniteAtelier($lien, $id_atelier);
                        $imprimer = "<center><a href='../plugins/fonctions/inc-liste_eps.php?annee=$annee&t=np&c=$id_centre&e=$id_etablissement&s=$id_serie&a=$id_atelier'><img src='../plugins/images/imprimer1.jpg' width='150px' /></a></center>";
                        $liste = "<h1>Performances et Notes des candidats par atelier</h1><br/>" . $imprimer;
                        $liste .= "<table>";
                        $liste .= "<th>Numéros</th><th>Noms et prenoms</th><th>Séries</th><th>Performances<br/>($unite)</th><th>Notes</th>";
                        while ($row = mysqli_fetch_array($result)) {
                            foreach (array('nom', 'prenoms', 'serie', 'num_table', 'sexe', 'performance', 'note_perf') as $val) {
                                $$val = $row[$val];
                            }

                            if ($sexe == 1) {
                                $civ = 'Mlle';
                            } else {
                                $civ = 'M.';
                            }

                            $liste .= "<tr><td>$num_table</td><td>$civ $nom $prenoms</td><td>$serie</td><td>$performance</td><td>$note_perf</td></tr>";
                        }
                        $liste .= "</table>" . $imprimer;
                        echo $liste;
                    } else {
                        echo debut_boite_alerte(), "<h2>Aucun candidat pour ces critères</h2>", fin_boite_alerte();
                    }
                    break;

                case 'performances':
                    $sql = "SELECT nom, prenoms, can.num_table, ser.serie, can.sexe
					FROM bg_candidats can, bg_ref_serie ser, bg_repartition rep, bg_ref_etablissement eta, bg_ref_etablissement cen
					WHERE can.annee=$annee AND rep.annee=$annee AND can.num_table=rep.num_table AND can.etablissement=eta.id
					AND rep.id_centre=cen.id AND can.serie=ser.id $andWhere
					ORDER BY can.num_table ";
                    $result = bg_query($lien, $sql, __FILE__, __LINE__);
                    echo "<a href='" . generer_url_ecrire('sport') . "&etape=impres'><center><b>RETOUR A LA PAGE PRECEDENTE</b></center></a><br/>";
                    if (mysqli_num_rows($result) > 0) {
                        $imprimer = "<center><a href='../plugins/fonctions/inc-liste_eps.php?annee=$annee&t=p&c=$id_centre&e=$id_etablissement&s=$id_serie&a=$id_atelier'><img src='../plugins/images/imprimer1.jpg' width='150px' /></a></center>";
                        $liste = "<h1>Notes des candidats pour pour chaque atelier</h1><br/>" . $imprimer;
                        $liste .= "<table border=2>";
                        $liste .= "<th>Numéros</th><th>Noms et prenoms</th><th>Séries</th>";
                        for ($i = 1; $i <= 7; $i++) {
                            $atelier = selectReferentiel($lien, 'atelier', $i);
                            $liste .= "<th>$atelier</th>";
                        }
                        $liste .= "<th>Notes</th>";
                        while ($row = mysqli_fetch_array($result)) {
                            foreach (array('nom', 'prenoms', 'serie', 'num_table', 'sexe', 'note') as $val) {
                                $$val = $row[$val];
                            }

                            if ($sexe == 1) {
                                $civ = 'Mlle';
                            } else {
                                $civ = 'M.';
                            }

                            $tabNotes = notesEPSParCandidat($lien, $annee, $num_table);
                            $liste .= "<tr><td>$num_table</td><td>$civ $nom $prenoms</td><td>$serie</td>";
                            for ($i = 1; $i <= 7; $i++) {
                                $atelier = selectReferentiel($lien, 'atelier', $i);
                                $performance = $tabNotes[$i]['performance'];
                                $note_perf = $tabNotes[$i]['note_perf'];
                                if ($performance != '') {
                                    $affiche = $performance . '[' . $note_perf . ']';
                                } else {
                                    $affiche = '';
                                }

                                $liste .= "<td>$affiche</td>";
                            }
                            $liste .= "<td>" . $tabNotes['note'] . "</td></tr>";
                        }
                        $liste .= "</table>" . $imprimer;
                        echo $liste;
                    } else {
                        echo debut_boite_alerte(), "<h2>Aucun candidat pour ces critères</h2>", fin_boite_alerte();
                    }
                    break;

                case 'notes':
                    $sql = "SELECT nom, prenoms, can.num_table, ser.serie, can.sexe, note
					FROM bg_candidats can, bg_ref_serie ser, bg_repartition rep, bg_ref_etablissement eta, bg_ref_etablissement cen, bg_notes_eps notes
					WHERE can.annee=$annee AND rep.annee=$annee AND notes.annee=$annee AND can.num_table=notes.num_table AND can.num_table=rep.num_table AND can.etablissement=eta.id
					AND rep.id_centre=cen.id AND can.serie=ser.id $andWhere
					ORDER BY can.num_table ";
                    $result = bg_query($lien, $sql, __FILE__, __LINE__);
                    echo "<a href='" . generer_url_ecrire('sport') . "&etape=impres'><center><b>RETOUR A LA PAGE PRECEDENTE</b></center></a><br/>";
                    if (mysqli_num_rows($result) > 0) {
                        $imprimer = "<center><a href='../plugins/fonctions/inc-liste_eps.php?annee=$annee&t=n&c=$id_centre&e=$id_etablissement&s=$id_serie&a=$id_atelier'><img src='../plugins/images/imprimer1.jpg' width='150px' /></a></center>";
                        $liste = "<h1>Notes des candidats pour l'épreuve d'EPS</h1><br/>" . $imprimer;
                        $liste .= "<table>";
                        $liste .= "<th>Numéros</th><th>Noms et prenoms</th><th>Séries</th><th>Notes</th>";
                        while ($row = mysqli_fetch_array($result)) {
                            foreach (array('nom', 'prenoms', 'serie', 'num_table', 'sexe', 'note') as $val) {
                                $$val = $row[$val];
                            }

                            if ($sexe == 1) {
                                $civ = 'Mlle';
                            } else {
                                $civ = 'M.';
                            }

                            $liste .= "<tr><td>$num_table</td><td>$civ $nom $prenoms</td><td>$serie</td><td>$note</td></tr>";
                        }
                        $liste .= "</table>" . $imprimer;
                        echo $liste;
                    } else {
                        echo debut_boite_alerte(), "<h2>Aucun candidat pour ces critères</h2>", fin_boite_alerte();
                    }
                    break;

            }
        }

        if ($_REQUEST['etape'] == 'impres' && !isset($_REQUEST['id_region']) || isset($_POST['id_region'])) {
            // SELECT eta.etablissement, eta.id as id_etablissement, count(can.etablissement) as nbre
            // FROM bg_ref_etablissement eta, bg_candidats can
            // WHERE   eta.id_inspection=$id_inspection AND can.etablissement=eta.id
            // AND can.annee=$annee $andWhere
            // GROUP BY eta.id, can.etablissement ORDER BY eta.etablissement
            $andWhereEta .= " AND  eta.si_centre='non'";
            $andWhereCen .= " AND  eta.si_centre='oui'";
            if ($isInspection) {
                $andWhereCen = " AND  eta.si_centre='oui' AND ins.id_region='$id_region' AND eta.id_inspection=$id_inspection ";
                $andWhereEta = " AND eta.si_centre='non' AND ins.id_region='$id_region' AND eta.id_inspection=$id_inspection ";
                $andWhereRegion = " WHERE id_region='$id_region' AND id=$id_inspection";
                $WhereRegion = "WHERE id=$id_region";
            }
            if ($isEncadrant) {
                $andWhereRegion = " WHERE id_region='$id_region'";
                $andWhereCen .= " AND  eta.si_centre='oui' AND ins.id=eta.id_inspection AND ins.id_region=$id_region";
                $andWhereEta .= "  AND eta.si_centre='non' AND ins.id=eta.id_inspection AND ins.id_region=$id_region";
            }
            if (isset($_POST['id_region']) && $_POST['id_region'] > 0) {$andWhere .= " AND ins.id_region=$id_region ";
                $andWhereRegion .= "WHERE id_region=$id_region";
                $andWhereEta .= " AND ins.id_region=$id_region AND ins.id=eta.id_inspection";
                $andWhereCen .= "  AND ins.id=eta.id_inspection AND ins.id_region=$id_region";}
            if (isset($_POST['id_centre']) && $_POST['id_centre'] > 0) {
                $andWhere .= " AND can.centre=$id_centre ";
                $andWhereEta = " AND eta.id_centre=$id_centre";
            }
            if (isset($_POST['id_etablissement']) && $_POST['id_etablissement'] > 0) {
                $andWhere .= " AND can.etablissement=$id_etablissement ";
                $andWhereEta .= " ";
            }
            //Formulaire de choix
            $formImp = "<form method='POST' name='formImpres'>";
            $formImp .= debut_cadre_enfonce('', '', '', "Critères");
            $formImp .= " <table>";
            if ($statut == 'Encadrant' || $statut == 'Inspection' || $statut == 'Admin') {
                $formImp .= "<tr><td><center><select style='width:65%' name='annee'>" . optionsAnnee(2020, $annee) . "</select></center></td>
        <td><center><select style='width:65%' name='id_serie'>" . optionsReferentiel($lien, 'serie', $id_serie, 'WHERE id <4') . "</select></center></td>
        <td><center><select style='width:65%' name='id_atelier' id='id_atelier'>" . optionsReferentiel($lien, 'atelier', $id_atelier) . "</select></center></td></tr>";

                $formImp .= "<tr><td><center><select style='width:65%' name='id_region' onchange='document.forms.formImpres.submit()'>" . optionsReferentiel($lien, 'region', $id_region, $WhereRegion) . "</select></center></td>
        <td><center><select style='width:65%' name='id_centre' onchange='document.forms.formImpres.submit()'>" . optionsEtablissement($lien, $id_centre, $andWhereCen, true, ',bg_ref_inspection ins', '', 'Centre de composition') . "</select></center></td>
       	<td><center><select style='width:65%' name='id_etablissement' onchange='document.forms.formImpres.submit()'>" . optionsEtablissement($lien, $id_etablissement, $andWhereEta, true, ',bg_ref_inspection ins') . "</select></center></td></tr>";
            }
            if ($statut == 'Etablissement') {

                $andWhereEta = "AND eta.id=$code_etablissement";
                $formImp .= "<tr><td><center><select style='width:65%' name='annee'>" . optionsAnnee(2020, $annee) . "</select></center></td>
                <td><center><select style='width:65%' name='id_serie'>" . optionsReferentiel($lien, 'serie', $id_serie, 'WHERE id <4') . "</select></center></td>
                </tr>";

                $formImp .= "<tr><td><center><select style='width:65%' name='id_etablissement' onchange='document.forms.formImpres.submit()'>" . optionsEtablissement($lien, $id_etablissement, $andWhereEta, true, ',bg_ref_inspection ins') . "</select></center></td>
                <td><center><select style='width:65%' name='id_atelier' id='id_atelier'>" . optionsReferentiel($lien, 'atelier', $id_atelier) . "</select></center></td></tr>";
            }
            $formImp .= "</table>";
            $formImp .= fin_cadre_enfonce();
            //Liste des actions
            $formImp .= "<table>";
            $formImp .= "<tr><td colspan=4><center><h2><a href='" . generer_url_ecrire('sport') . "'>Revenir au menu principal</a></h2></center></td></tr>";
            //$formImp .= "<th colspan=2><center><h2><a href='" . generer_url_ecrire('sport') . "'>Revenir au menu principal</a></h2></center></th>";
            $formImp .= "<tr><td><input type='image' style='width:100px;' name='tache[liste_candidats]' src='../plugins/images/sport1.jpg' onclick=\"if(id_centre.value!=0 || id_etablissement.value!=0) return true; else {alert('Choisissez un centre et/ou un etablissement'); return false; }\"  /></td>
                        <td>Liste des candidats Aptes à l'Epreuve d'EPS</td>";
            $formImp .= "<td><input type='image' style='width:100px;' name='tache[candidats_atelier]' src='../plugins/images/sport2.jpg' onclick=\"if(id_atelier.value!=0) return true; else {alert('Choisissez un atelier'); return false; }\" /></td>
                        <td>Liste des candidats par Atelier</td></tr>";
            // $formImp .= "<td><input type='image' style='width:100px;' name='tache[candidats_atelier]' src='../plugins/images/sport2.jpg' onclick=\"if(id_etablissement.value!=0) return true; else {alert('Choisissez un atelier'); return false; }\" /></td>
            //             <td>Liste des candidats par Atelier</td></tr>";
            if ($statut == 'Encadrant' || $statut == 'Inspection' || $statut == 'Admin') {
                $formImp .= "<tr><td><input type='image' style='width:100px;' name='tache[performances_atelier]' src='../plugins/images/sport30.png' onclick=\"if(id_atelier.value!=0 && (id_centre.value!=0 || id_etablissement.value!=0)) return true; else {alert('Choisissez un atelier'); return false; }\" /></td>
                        <td>Performances des candidats par Atelier</td>";
                $formImp .= "<td><input type='image' style='width:100px;' name='tache[performances]' src='../plugins/images/sport4.jpg' onclick=\"if(id_centre.value!=0 || id_etablissement.value!=0) return true; else {alert('Choisissez un centre et/ou un etablissement'); return false; }\" /></td>
                        <td>Performances des candidats dans tous les Ateliers</td></tr>";
                // $formImp .= "<td><input type='image' style='width:100px;' name='tache[candidats_centre_atelier]' src='../plugins/images/icone_centre_atelier.png' onclick=\"if(id_atelier.value!=0 && id_centre.value!=0) return true; else {alert('Choisissez un atelier et/ou un centre'); return false; }\" /></td>
                //             <td>Liste des candidats par Centre par Atelier</td></tr>";
                $formImp .= "<tr><td><input type='image' style='width:100px;' name='tache[notes]' src='../plugins/images/sport5.png' onclick=\"if(id_centre.value!=0 || id_etablissement.value!=0) return true; else {alert('Choisissez un centre et/ou un etablissement'); return false; }\" /></td><td>Notes des Candidats pour l'Epreuve d'EPS</td>
            </tr>";
            }
            $formImp .= "</fieldset>";

            $formImp .= "</table></form>";
            echo $formImp;
        }

        echo fin_cadre_trait_couleur();
        echo fin_grand_cadre(), fin_page();

    }
}

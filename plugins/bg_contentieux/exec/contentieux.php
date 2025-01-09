<?php
if (!defined('_ECRIRE_INC_VERSION')) {
    return;
}

include_spip('inc/presentation'); // alleger les inclusions avec un inc/presentation_mini
include '../plugins/fonctions/generales.php';

setlocale(LC_TIME, "fr_FR");

//Affichage de le jury a partir du numéro de table
function getJuryByNumTable($lien, $num_table)
{
    $sql = "SELECT jury FROM bg_resultats WHERE num_table=$num_table ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $row = mysqli_fetch_array($result);
    return $row['jury'];
}
//Affichage de le serie a partir du numéro de table
function getSerieByNumTable($lien, $num_table)
{
    $sql = "SELECT id_serie FROM bg_resultats WHERE num_table=$num_table ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $row = mysqli_fetch_array($result);
    return $row['id_serie'];
}

//Affichage de le eps a partir du numéro de table
function getEpsByNumTable($lien, $num_table)
{
    $sql = "SELECT eps FROM bg_candidats WHERE num_table=$num_table ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $row = mysqli_fetch_array($result);
    return $row['eps'];
}
//Affichage de le moyenne a partir du numéro de table
function getMoyenneBySerie($lien, $id_serie, $eps)
{
    $sql = "SELECT moyenne_apte, moyenne_inapte
                               FROM bg_ref_serie
                               WHERE id = $id_serie
                               LIMIT 1 ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $row = mysqli_fetch_array($result);
    if ($eps == 1) {
        return $row['moyenne_apte'];
    } else {
        return $row['moyenne_inapte'];
    }

}

function exec_contentieux()
{
    $exec = _request('exec');
    $titre = "exec_$exec";
    $commencer_page = charger_fonction('commencer_page', 'inc');
    echo $commencer_page($titre);
    $statut = getStatutUtilisateur();
    $lien = lien();

    foreach ($_REQUEST as $key => $val) {
        $$key = mysqli_real_escape_string($lien, trim($val));
    }

    $tab_auteur = $GLOBALS["auteur_session"];
    $login = $tab_auteur['login'];
    $lien_photos = "../photos/$annee/";
    if ($statut != 'Admin') {
        switch ($statut) {
            case 'Etablissement':
                $isOperateur = false;
                $isInspection = false;
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
            case 'Notes':
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
                $WhereRegion = " WHERE id_region='$id_region' ";

                $andWhereInspection = "WHERE id_region=$id_region AND id=$idInsp";

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
            case 'DExCC':
                $isDexcc = true;
                $idAdmin = false;
                break;
            default:
                die(KO . " - Statut <b>$statut</b> invalide");
        }
    } else {
        $isAdmin = true;
    }
    if ($annee == '') {
        $annee = date("Y");
    }

    $tab_auteur = $GLOBALS["auteur_session"];
    $login = $tab_auteur['login'];

    if (isAutorise(array('Admin', 'Informaticien', 'Encadrant'))) {
        echo debut_grand_cadre();

        echo debut_cadre_enfonce();
        echo "<div class='onglets_simple clearfix'>
		<ul> ";
        echo "	<li><a href='./?exec=contentieux&tache=modifier_notes' class='ajax'>Modifier des notes par candidat</a></li> ";
        if ($statut == "Admin") {
            echo "	<li><a href='./?exec=contentieux&tache=modifier_notes_fac' class='ajax'>Modifier notes fac. par matiere</a></li>";
            echo "	<li><a href='./?exec=contentieux&tache=modifier_notes_fac_jury' class='ajax'>Modifier des notes fac. par jury</a></li>";
        }

        echo "	<li><a href='./?exec=contentieux&action=logout&logout=prive' class='ajax'>Se déconnecter</a></li>";
        echo "	</ul>
		</div>";
        echo fin_cadre_enfonce();

//         if (isset($_POST['valider'])) {
        //             $tabMat = $_POST['tabMat'];
        //             $tabTyp = $_POST['tabTyp'];
        //             $tabNotes = $_POST['tabNotes'];
        //             $tabAnc = $_POST['tabAnc'];
        //             $tabCoef = $_POST['tabCoef'];
        //             echo "<pre>";
        // //        print_r($_POST['tabNotes']);
        //             echo "</pre>";

//             foreach ($tabTyp as $id_matiere => $id_type_note) {
        //                 $note = $tabNotes[$id_matiere];
        //                 if ($note == '') {
        //                     $note = "NULL";
        //                 }

//                 if ($id_matiere > 0) {
        //                     $sql = "UPDATE bg_notes SET note=$note, id_matiere=" . $tabMat[$id_matiere] . ", id_type_note='$id_type_note', coeff=" . $tabCoef[$id_matiere] . "
        //                         WHERE annee=$annee AND num_table='$numero' AND id_matiere='$id_matiere' AND id_type_note=" . $tabAnc[$id_matiere];
        //                 } elseif ($id_matiere == 0 && $tabMat[$id_matiere] > 0 && $tabTyp[$id_matiere] > 0) {
        //                     $sql = "REPLACE INTO bg_notes (`num_table`, `id_anonyme`, `ano`, `annee`, `id_serie`, `jury`, `id_matiere`, `id_type_note`, `coeff`, `id_type_session`, `note`)
        //                         SELECT `num_table`, `id_anonyme`, `ano`, `annee`, `id_serie`, `jury`, $tabMat[0], $id_type_note, $tabCoef[0], `id_type_session`, $note
        //                         FROM bg_notes WHERE annee=$annee AND num_table='$numero' ";
        //                 }
        //                 bg_query($lien, $sql, __FILE__, __LINE__);
        //             }
        //             $sql2 = "DELETE FROM bg_notes WHERE annee=$annee AND num_table='$numero' AND id_matiere=0 ";
        //             bg_query($lien, $sql2, __FILE__, __LINE__);
        //         }
        if (isset($_POST['valider'])) {
            $tabMat = $_POST['tabMat'];
            $tabTyp = $_POST['tabTyp'];
            $tabNotes = $_POST['tabNotes'];
            $tabAnc = $_POST['tabAnc'];
            $tabCoef = $_POST['tabCoef'];
            echo "<pre>";
            //        print_r($_POST['tabNotes']);
            echo "</pre>";

            foreach ($tabTyp as $id_matiere => $id_type_note) {
                $note = $tabNotes[$id_matiere];
                if ($note == '') {
                    $note = "NULL";
                }

                if ($id_matiere > 0) {
                    $sql = "UPDATE bg_notes SET note=$note, id_matiere=" . $tabMat[$id_matiere] . ", id_type_note='$id_type_note', coeff=" . $tabCoef[$id_matiere] . "
                WHERE annee=$annee AND num_table='$numero' AND id_matiere='$id_matiere' AND id_type_note=" . $tabAnc[$id_matiere];

                } elseif ($id_matiere == 0 && $tabMat[$id_matiere] > 0 && $tabTyp[$id_matiere] > 0) {
                    $sql = "REPLACE INTO bg_notes (`num_table`, `id_anonyme`, `ano`, `annee`, `id_serie`, `jury`, `id_matiere`, `id_type_note`, `coeff`, `id_type_session`, `note`, `id_region`)
                SELECT `num_table`, `id_anonyme`, `ano`, `annee`, `id_serie`, `jury`, $tabMat[0], $id_type_note, $tabCoef[0], `id_type_session`, $note, `id_region`
                FROM bg_notes WHERE annee=$annee AND num_table='$numero' ";
                }
                bg_query($lien, $sql, __FILE__, __LINE__);
            }

            $sql2 = "DELETE FROM bg_notes WHERE annee=$annee AND num_table='$numero' AND id_matiere=0 ";
            bg_query($lien, $sql2, __FILE__, __LINE__);

            $jury = getJuryByNumTable($lien, $numero);
            $id_serie = getSerieByNumTable($lien, $numero);
            $eps = getEpsByNumTable($lien, $numero);

            $total_coef = getMoyenneBySerie($lien, $id_serie, $eps);

            // Requêtes pour les résultats des candidats
            $sql_calcul_1 = "REPLACE INTO bg_resultats(
                num_table, id_anonyme, id_type_session, annee, jury, total, coeff, moyenne, nt, id_region, id_serie)
                SELECT num_table, id_anonyme, id_type_session, annee, '$jury',
                       SUM(note * coeff), $total_coef, SUM(note * coeff) / $total_coef AS moyenne,
                       nt, id_region, id_serie
                FROM bg_notes
                WHERE annee = {$annee} AND note >= 0 AND num_table = '$numero'
                      AND id_type_note != 3 AND id_type_note != 4
                GROUP BY num_table, id_anonyme, id_type_session, annee";

            bg_query($lien, $sql_calcul_1, __FILE__, __LINE__);

            // Notes entre 10 et 15 dans les matières facultatives
            $sql_calcul_2 = "REPLACE INTO bg_resultats(
                num_table, id_anonyme, id_type_session, annee, jury, total, coeff, moyenne, nt, id_region, id_serie)
                SELECT notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, '$jury',
                       (res.total + SUM(note - 10)), res.coeff,
                       ((res.total + SUM(note - 10)) / res.coeff) AS moyenne,
                       notes.nt, notes.id_region, notes.id_serie
                FROM bg_notes notes
                JOIN bg_resultats res ON notes.num_table = res.num_table
                WHERE notes.annee = {$annee} AND note > 10 AND (note - 10) <= 5
                      AND notes.num_table = '$numero' AND notes.id_type_note = 4
                GROUP BY notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, res.coeff, res.total";

            bg_query($lien, $sql_calcul_2, __FILE__, __LINE__);

            // Matières facultatives avec une note > 15
            $sql_calcul_3 = "REPLACE INTO bg_resultats(
                num_table, id_anonyme, id_type_session, annee, jury, total, coeff, moyenne, nt, id_region, id_serie)
                SELECT notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, '$jury',
                       (res2.total + SUM(5)), res2.coeff,
                       ((res2.total + SUM(5)) / res2.coeff) AS moyenne,
                       notes.nt, notes.id_region, notes.id_serie
                FROM bg_notes notes
                JOIN bg_resultats res2 ON notes.num_table = res2.num_table
                WHERE notes.annee = $annee AND note > 15 AND notes.num_table = '$numero' AND notes.id_type_note = 4
                GROUP BY notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, res2.coeff, res2.total";

            bg_query($lien, $sql_calcul_3, __FILE__, __LINE__);

            //Attribution des mentions pour la première phase
            $tSqlDelib[] = "UPDATE bg_resultats SET delib1='TBien' WHERE annee=$annee  AND moyenne>=16 AND num_table=$numero ";
            $tSqlDelib[] = "UPDATE bg_resultats SET delib1='Bien' WHERE annee=$annee  AND moyenne>=14 AND moyenne<16 AND num_table=$numero ";
            $tSqlDelib[] = "UPDATE bg_resultats SET delib1='Abien' WHERE annee=$annee  AND moyenne>=12 AND moyenne<14 AND num_table=$numero ";
            $tSqlDelib[] = "UPDATE bg_resultats SET delib1='Passable' WHERE annee=$annee  AND moyenne>=10 AND moyenne<12 AND num_table=$numero ";
            $tSqlDelib[] = "UPDATE bg_resultats SET delib1='Oral' WHERE annee=$annee  AND moyenne>=9 AND moyenne<10 AND num_table=$numero ";
            $tSqlDelib[] = "UPDATE bg_resultats SET delib1='Ajourne' WHERE annee=$annee AND moyenne>0 AND moyenne<9 AND num_table=$numero";

            foreach ($tSqlDelib as $sql2) {
                bg_query($lien, $sql2, __FILE__, __LINE__);
            }
        }

        if (isset($_POST['ok'])) {
            if ($id_matiere > 0) {
                $tabJurys = $_POST['tabJurys'];
                foreach ($tabJurys as $jury) {
                    $tabSsql[] = "DELETE FROM bg_histo_notes WHERE jury=$jury AND id_matiere=$id_matiere AND id_type_note=4 ";
                    $tabSsql[] = "REPLACE INTO bg_histo_notes (`num_table`, `id_anonyme`, `ano`, `annee`, `id_serie`, `jury`, `id_matiere`, `id_type_note`, `coeff`, `id_type_session`, `note`,`login`)
						SELECT `num_table`, `id_anonyme`, `ano`, `annee`, `id_serie`, `jury`, $id_matiere, 4, 0, `id_type_session`, `note` ,'dometo'
						FROM bg_notes WHERE annee=$annee AND jury='$jury' AND id_matiere='$id_matiere' AND id_type_note=4 AND note is not NULL ";
                    $tabSsql[] = "REPLACE INTO bg_notes (`num_table`, `id_anonyme`, `ano`, `annee`, `id_serie`, `jury`, `id_matiere`, `id_type_note`, `coeff`, `id_type_session`, `note`)
						SELECT `num_table`, `id_anonyme`, `ano`, `annee`, `id_serie`, `jury`, $id_matiere, 4, 0, `id_type_session`, NULL
						FROM bg_notes WHERE annee=$annee AND jury='$jury' ";
                    $tabSsql[] = "REPLACE INTO bg_notes (`num_table`, `id_anonyme`, `ano`, `annee`, `id_serie`, `jury`, `id_matiere`, `id_type_note`, `coeff`, `id_type_session`, `note`)
						SELECT `num_table`, `id_anonyme`, `ano`, `annee`, `id_serie`, `jury`, $id_matiere, 4, 0, `id_type_session`, `note`
						FROM bg_histo_notes WHERE annee=$annee AND jury='$jury' AND note is not null AND id_matiere=$id_matiere AND id_type_note=4 ";
                    foreach ($tabSsql as $sql) {
                        bg_query($lien, $sql, __FILE__, __LINE__);
                    }
                    //echo $sql;
                }
                echo debut_cadre_couleur(), "Opération exécutée avec succès", fin_cadre_couleur();
            } else {
                echo debut_cadre_couleur(), "Vous devriez choisir une matiere ", fin_cadre_couleur();
            }

        }

        if (isset($_POST['ok_par_jury'])) {
            if ($jury > 0) {
                $tabMatieres = $_POST['tabMatieres'];
                foreach ($tabMatieres as $id_matiere) {
                    $tabSsql[] = "DELETE FROM bg_histo_notes WHERE jury=$jury AND id_matiere=$id_matiere AND id_type_note=4 ";
                    $tabSsql[] = "REPLACE INTO bg_histo_notes (`num_table`, `id_anonyme`, `ano`, `annee`, `id_serie`, `jury`, `id_matiere`, `id_type_note`, `coeff`, `id_type_session`, `note`,`login`)
						SELECT `num_table`, `id_anonyme`, `ano`, `annee`, `id_serie`, `jury`, $id_matiere, 4, 0, `id_type_session`, `note` ,'dometo'
						FROM bg_notes WHERE annee=$annee AND jury='$jury' AND id_matiere='$id_matiere' AND id_type_note=4 AND note is not NULL ";
                    $tabSsql[] = "REPLACE INTO bg_notes (`num_table`, `id_anonyme`, `ano`, `annee`, `id_serie`, `jury`, `id_matiere`, `id_type_note`, `coeff`, `id_type_session`, `note`)
						SELECT `num_table`, `id_anonyme`, `ano`, `annee`, `id_serie`, `jury`, $id_matiere, 4, 0, `id_type_session`, NULL
						FROM bg_notes WHERE annee=$annee AND jury='$jury' ";
                    $tabSsql[] = "REPLACE INTO bg_notes (`num_table`, `id_anonyme`, `ano`, `annee`, `id_serie`, `jury`, `id_matiere`, `id_type_note`, `coeff`, `id_type_session`, `note`)
						SELECT `num_table`, `id_anonyme`, `ano`, `annee`, `id_serie`, `jury`, $id_matiere, 4, 0, `id_type_session`, `note`
						FROM bg_histo_notes WHERE annee=$annee AND jury='$jury' AND note is not null AND id_matiere=$id_matiere AND id_type_note=4 ";
                    foreach ($tabSsql as $sql) {
                        bg_query($lien, $sql, __FILE__, __LINE__);
                    }
                    //echo $sql;
                }
                echo debut_cadre_couleur(), "Opération exécutée avec succès", fin_cadre_couleur();
            } else {
                echo debut_cadre_couleur(), "Vous devriez choisir une matiere ", fin_cadre_couleur();
            }

        }

        /*
        echo "<pre>";
        print_r($_POST);
        echo "</pre>";
         */
        if (isset($_REQUEST['tache']) && $_REQUEST['tache'] == 'modifier_notes' && !isset($_POST['numero'])) {
            $form = "<FORM method='POST' mane='form_numero' action=''>";
            $form .= "<table><tr><td>Insérer le numéro du candidat</td><td><input type='text' name='numero'/><input type='submit' value='OK'/></td></tr></table>";
            $form .= "</FORM>";
            echo debut_cadre_couleur(), $form, fin_cadre_couleur();
        }

        if (isset($_REQUEST['tache']) && $_REQUEST['tache'] == 'modifier_notes_fac' && !isset($_POST['ok'])) {
            $form = "<FORM method='POST' mane='' action=''>";

            $sql = "SELECT code, rep.jury
				FROM bg_repartition rep, bg_codes cod
				WHERE cod.annee=$annee AND rep.annee=$annee AND rep.jury=cod.jury
				GROUP BY rep.jury, code
				ORDER BY rep.jury, code ";
            $result = bg_query($lien, $sql, __FILE__, __LINE__);
            $form .= "<table>";
            while ($row = mysqli_fetch_array($result)) {
                $jury = $row['jury'];
                $form .= "<tr><td>" . $row['code'] . "</td><td><input type='checkbox' name='tabJurys[$jury]' value='$jury' checked></td></tr>";
            }
            $form .= "<tr><td><select name='id_matiere'>" . optionsReferentiel($lien, 'matiere', '', " WHERE id<=8", true) . "</select></td>";
            $form .= "<td><input type='submit' name='ok' value='VALIDER'/></td></tr>";
            $form .= "</table>";
            $form .= "</FORM>";
            echo debut_cadre_couleur(), $form, fin_cadre_couleur();
        }

        if (isset($_REQUEST['tache']) && $_REQUEST['tache'] == 'modifier_notes_fac_jury' && !isset($_POST['ok'])) {
            $form = "<FORM method='POST' mane='' action=''>";

            $sql = "SELECT * FROM bg_ref_matiere WHERE id<=8 ORDER BY matiere ";
            $result = bg_query($lien, $sql, __FILE__, __LINE__);
            $form .= "<table>";
            while ($row = mysqli_fetch_array($result)) {
                $id = $row['id'];
                $form .= "<tr><td>" . $row['matiere'] . "</td><td><input type='checkbox' name='tabMatieres[$id]' value='$id' ></td></tr>";
            }
            $form .= "<tr><td>EPS</td><td><input type='checkbox' name='tabMatieres[42]' value='42' ></td></tr>";
            $sql2 = "SELECT code, rep.jury
				FROM bg_repartition rep, bg_codes cod
				WHERE cod.annee=$annee AND rep.annee=$annee AND rep.jury=cod.jury
				GROUP BY rep.jury, code
				ORDER BY rep.jury, code ";
            $result2 = bg_query($lien, $sql2, __FILE__, __LINE__);
            $form .= "<tr><td><center><select name='jury'><option value=0>-=[Jurys]=-</option>";
            while ($row2 = mysqli_fetch_array($result2)) {
                $jury = $row2['jury'];
                $form .= "<option value=$jury>" . $row2['code'] . "</option>";
            }
            $form .= "</select></center></td>";
            $form .= "<td><input type='submit' name='ok_par_jury' value='VALIDER'/></td></tr>";
            $form .= "</table>";
            $form .= "</FORM>";
            echo debut_cadre_couleur(), $form, fin_cadre_couleur();
        }

        if (isset($_REQUEST['tache']) && $_REQUEST['tache'] == 'modifier_notes' && isset($_POST['numero'])) {
            $sql = "SELECT * FROM bg_notes WHERE annee=$annee AND num_table='$numero' ORDER BY id_type_note";
            $result = bg_query($lien, $sql, __FILE__, __LINE__);
            $form = "<FORM method='POST' mane='form_numero' action=''>";
            $form .= "<table>";
            $form .= "<th>Matières</th><th>Type</th><th>Note</th><th>Coef.</th>";
            $form .= "<tr><td><input type='hidden' name='numero' value='$numero' /></td><td></td><td></td><td></td></tr>";
            $tab = array('id_matiere', 'coeff', 'note', 'id_type_note');
            while ($row = mysqli_fetch_array($result)) {
                foreach ($tab as $val) {
                    $$val = $row[$val];
                }

                $form .= "<tr><td><select name='tabMat[$id_matiere]'>" . optionsReferentiel($lien, 'matiere', "$id_matiere", '', true) . "</select></td>
						<td><select name='tabTyp[$id_matiere]'>" . optionsReferentiel($lien, 'type_note', "$id_type_note", '', true) . "</select>
							<input type='hidden' name='tabAnc[$id_matiere]' value='$id_type_note'/></td>
						<td><b><input type='number' name='tabNotes[$id_matiere]' value='$note' min=0 /></b></td>
						<td><input type='number' name='tabCoef[$id_matiere]' value='$coeff' min=0 /></td></tr>";
            }
            $form .= "<tr><td colspan=4><center><b>INSERER UNE MATIERE ET UNE NOTE</b></center></td></tr>";
            $form .= "<tr><td><select name='tabMat[0]'>" . optionsReferentiel($lien, 'matiere', '', '', true) . "</select></td>
					<td><select name='tabTyp[0]'>" . optionsReferentiel($lien, 'type_note', '', '', true) . "</select></td>
					<td><input type='number' name='tabNotes[0]' value='' min=0 /></td>
					<td><input type='number' name='tabCoef[0]' value='0' min=0 /></td>
					</tr>";
            $form .= "<tr><td><input type='reset' value='ANNULER'/></td>
				<td colspan=2><center><input type='submit' value='ENREGISTRER' name='valider' /></center></td>
				<td><input type='button' value='QUITTER' onClick=\"window.location='" . generer_url_ecrire('contentieux') . "';\" /></td></tr>";
            $form .= "</table></FORM>";
            echo debut_cadre_couleur(), $form, fin_cadre_couleur();
        }

    }
    echo fin_cadre_trait_couleur();
    echo fin_grand_cadre(), fin_page();
}

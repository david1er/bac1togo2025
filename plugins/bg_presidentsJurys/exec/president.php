<?php
if (!defined('_ECRIRE_INC_VERSION')) {
    return;
}

include_spip('inc/presentation'); // alleger les inclusions avec un inc/presentation_mini
include '../plugins/fonctions/generales.php';

setlocale(LC_TIME, "fr_FR");

function exec_president()
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
                $WhereRegionDivision = " WHERE id_region=$id_region ";
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
                $WhereRegionDivision = " WHERE id_region=$id_region ";
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
            default:
                die(KO . " - Statut <b>$statut</b> invalide");
        }
    } else {
        $isAdmin = true;
    }

    if ($annee == '') {
        $annee = recupAnnee($lien);
    }
    if ($tri_annee == '') {
        $tri_annee = recupAnnee($lien);
    }

    if ($tri_session == '') {
        $tri_session = recupSession($lien);
    }
    if ($type_session == '') {
        $type_session = recupSession($lien);
    }

    if (isAutorise(array('Admin', 'Dre', 'Inspection', 'Encadrant', 'Informaticien'))) {

        echo debut_cadre_trait_couleur('', '', '', "GESTION DES PRESIDENTS DE JURYS $annee", "", "", false);

        if (isset($_POST['valider'])) {
            if ($nom != '' && $prenoms != '') {
                $tabJurys = $_POST['tabJurys'];
                $sql = "INSERT INTO bg_presidents (nom,prenoms,annee) VALUES ('$nom','$prenoms','$annee') ";
                $result = bg_query($lien, $sql, __FILE__, __LINE__);
                $id_president = mysqli_insert_id($lien);
                foreach ($tabJurys as $jury => $code) {
                    $sql2 = "REPLACE INTO bg_presidentjury (id_president,jury,code,annee,id_region_division) VALUES ('$id_president','$jury','$code','$annee','$id_region_division') ";
                    bg_query($lien, $sql2, __FILE__, __LINE__);
                }
            }
        }

        if (isset($_REQUEST['annee']) && isset($_REQUEST['modif']) && isset($_GET['id_president']) && !isset($_POST['valider'])) {
            $sql_del1 = "DELETE FROM bg_presidents WHERE annee=$annee AND id=$id_president ";
            bg_query($lien, $sql_del1, __FILE__, __LINE__);
            $sql_del2 = "DELETE FROM bg_presidentjury WHERE annee=$annee AND id_president=$id_president ";
            bg_query($lien, $sql_del2, __FILE__, __LINE__);
        }

        if (!isset($_REQUEST['annee']) && (!isset($_REQUEST['etape']))) {
            $liste = "<form action='" . generer_url_ecrire('president') . "' method='POST' class='forml spip_xx-small' name='form_annee'>";
            $liste .= "<center><select name='annee' id='annee' onchange='document.forms.form_annee.submit()' >" . optionsAnnee($annee) . "</select>";
            $liste .= "</center></form>";
            echo $liste;
        }

        if (isset($_REQUEST['annee']) && !isset($_GET['modif'])) {
            if (($statut == "Dre") || ($statut == "Encadrant")) {
                $id_region_division = selectRegionDivisionByRegion($lien, $id_region);
                $andWhereRegionDivision = " AND id_region_division=$id_region_division";
                $sql_liste = "SELECT nom, prenoms, id as id_president
                FROM bg_presidents pre,bg_presidentjury pdj
                WHERE pdj.annee=$annee AND pre.id=pdj.id_president AND pdj.id_region_division=$id_region_division
                GROUP BY id_president ORDER BY nom, prenoms ";
            } else {
                $andWhereRegionDivision = "";
                $sql_liste = "SELECT nom, prenoms, id as id_president
                FROM bg_presidents
                WHERE annee=$annee
                ORDER BY nom, prenoms ";
            }

            $table = debut_cadre_couleur('', '', '', "LISTE DES PRESIDENTS DE JURYS");
            $table .= "<table style='overflow:hidden;'><th>PRESIDENTS</th><th>JURYS</th>";
            // $sql_liste = "SELECT nom, prenoms, id as id_president
            //         FROM bg_presidents
            //         WHERE annee=$annee
            //         ORDER BY nom, prenoms ";
            $result_liste = bg_query($lien, $sql_liste, __FILE__, __LINE__);
            $tab = array('id_president', 'nom', 'prenoms');
            while ($row_liste = mysqli_fetch_array($result_liste)) {
                foreach ($tab as $var) {
                    $$var = $row_liste[$var];
                }

                $sql_liste2 = "SELECT jury, code
					FROM bg_presidentjury
					WHERE annee=$annee $andWhereRegionDivision AND  id_president='$id_president'
					ORDER BY code ";
                $result_liste2 = bg_query($lien, $sql_liste2, __FILE__, __LINE__);
                $listeJurys = '';
                while ($row_liste2 = mysqli_fetch_array($result_liste2)) {
                    $code = $row_liste2['code'];
                    $jury = $row_liste2['jury'];

                    $listeJurys .= $code . "[$jury]" . '+';
                }
                //$table .= "<tr><td>$nom $prenoms</td><td style='width=50%;word-wrap: break-word;'>$listeJurys</td></tr>";
                $table .= "<tr><td><a href='" . generer_url_ecrire('president') . "&annee=$annee&modif=supprimer&id_president=$id_president'>$nom $prenoms</a></td><td>$listeJurys</td></tr>";

            }
            $table .= "</table>";
            echo $table;
            $table .= fin_cadre_couleur();

            $form = debut_cadre_enfonce();
            $form .= "<form mane='form_noms' method='POST' action=''><table> ";
            $form .= "<th>NOMS</th><th>PRENOMS</th><th>REGION</th>";
            $form .= "<tr><td><input type='text' name='nom' id='nom' /></td><td><input type='text' name='prenoms' id='prenoms' /></td>
            <td><select   name='id_region_division' id='id_region_division' >" . optionsReferentiel($lien, 'region_division', $id_region_division, $WhereRegionDivision) . "</select></td></tr>";
            $form .= "<tr><td><input type='hidden' name='annee' value='$annee' /></td><td></td><td></td></tr>";

            //Recherche des jurys qui n'ont pas encore de presidents

            if (($statut == "Dre") || ($statut == "Encadrant")) {
                $id_region_division = selectRegionDivisionByRegion($lien, $id_region);
                $sql = "SELECT cod.jury,cod.code FROM bg_repartition rep,bg_ref_etablissement eta,bg_codes cod
            WHERE cod.annee=$annee $andWhere AND rep.id_centre=eta.id AND cod.jury=rep.jury AND  eta.id_region_division=$id_region_division
             GROUP BY jury ORDER BY jury";
            } else {
                $sql = "SELECT * FROM bg_codes WHERE annee=$annee ORDER BY code ";
            }

            $result = bg_query($lien, $sql, __FILE__, __LINE__);
            while ($row = mysqli_fetch_array($result)) {
                $jury = $row['jury'];
                $code = $row['code'];
                $sql2 = "SELECT * FROM bg_presidentjury WHERE jury='$jury' AND annee='$annee' ";
                $result2 = bg_query($lien, $sql2, __FILE__, __LINE__);
                if (mysqli_num_rows($result2) == 0) {
                    $options .= "<tr><td>$code ($jury)</td><td><input type='checkbox' name='tabJurys[$jury]' value='$code' /></td><td></td></tr>";
                } else {
                    $options .= '';
                }

            }
            $form .= $options;
            $form .= "<tr><td><input type='submit' name='valider' value='Continuer' /></td><td><input type='button' value='Quitter' onClick=window.location='" . generer_url_ecrire('president') . "' /></td><td></td></tr>";
            $form .= "</table></form>";
            $form .= fin_cadre_enfonce();
            echo $form;
        }
    }

    echo fin_grand_cadre(), fin_gauche(), fin_cadre_trait_couleur(), fin_page();

}

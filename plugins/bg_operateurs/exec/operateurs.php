<?php
if (!defined('_ECRIRE_INC_VERSION')) {
    return;
}

include_spip('inc/presentation'); // alleger les inclusions avec un inc/presentation_mini
include '../plugins/fonctions/generales.php';

setlocale(LC_TIME, "fr_FR");

function InsererOperateurs($lien, $annee)
{
    foreach ($_REQUEST as $key => $val) {$$key = mysqli_real_escape_string($lien, trim(addslashes($val)));}
    if (isset($_POST['inserer'])) {
        $sql_sup = "DELETE FROM bg_operateur WHERE annee=$annee ";
        bg_query($lien, $sql_sup, __FILE__, __LINE__);
        foreach ($_POST['log'] as $id => $log) {
            if ($log != '') {
                $sql = "REPLACE INTO bg_operateur (`id`,`annee`,`login_operateur`,`mdp`, `jury1`, `jury2`, `jury3`)
					VALUES ('$id','$annee','" . $_POST['log'][$id] . "','" . $_POST['passe'][$id] . "','" . $_POST['j1'][$id] . "','" . $_POST['j2'][$id] . "','" . $_POST['j3'][$id] . "')  ";
                bg_query($lien, $sql, __FILE__, __LINE__);
            }
        }
        if ($login_operateur != '') {
            $sql = "REPLACE INTO bg_operateur (`annee`,`login_operateur`,`mdp`, `jury1`, `jury2`, `jury3`)
					VALUES ('$annee','$login_operateur','$mdp','$jury1','$jury2','$jury3')  ";
            bg_query($lien, $sql, __FILE__, __LINE__);
        }

        $sql2 = "DELETE FROM bg_operateur WHERE annee=$annee AND jury1=0 AND jury2=0 AND jury3=0 ";
//        bg_query($lien,$sql2,__FILE__,__LINE__);
        creerOperateursAuteurs($lien, $annee);

        //echo $sql2;
    }

    $form_ins = "<p></p><form action='' method='POST' class='forml spip_xx-small' name='form_operateurs' onload=\"document.forms.form_operateurs.login_operateur.focus();\">";
    $form_ins .= "<TABLE>";
    $form_ins .= "<tr><td>Login</td><td>Mot de Passe Opérateur</td><td>Jury</td></tr>";

    $sql2 = "SELECT * FROM bg_operateur WHERE annee=$annee ORDER BY jury1, jury2 ";
    $result2 = bg_query($lien, $sql2, __FILE__, __LINE__);
    if (mysqli_num_rows($result2) > 0) {
        $tab2 = array('annee', 'id', 'login_operateur', 'mdp', 'jury1', 'jury2', 'jury3');
        while ($row2 = mysqli_fetch_array($result2)) {
            foreach ($tab2 as $var2) {
                $$var2 = $row2[$var2];
            }

            $form_ins .= "<tr>
					<td><input type='text' name='log[$id]' value='$login_operateur' size=5 /></td>
					<td><input type='text' name='passe[$id]' size=3 value='$mdp' /></td>
					<td>" . optionJurys($lien, $annee, "j1[$id]", $jury1) . "</td>
					<td>" . optionJurys($lien, $annee, "j2[$id]", $jury2) . "</td>
					<td>" . optionJurys($lien, $annee, "j3[$id]", $jury3) . "</td>
					</tr>";
            $form_ins .= "<tr>";
        }
    }

    $form_ins .= "<tr><td colspan=5 ><center><input type='hidden' name='i' value='$i'><h2 style=\"border-width:1; border-style:groove; background-color:#11E3EB; \">AJOUT D'UN NOUVEL OPERATEUR</h2></center></td></tr>";
    $form_ins .= "<tr>
				<td><input type='text' name='login_operateur' size=5 /><input type='hidden' name='annee' value='$annee' /></td>
				<td><input type='text' name='mdp' size=3 /></td>
				<td>" . optionJurys($lien, $annee, 'jury1', $juryChoisi) . "</td>
				<td>" . optionJurys($lien, $annee, 'jury2', $juryChoisi) . "</td>
				<td>" . optionJurys($lien, $annee, 'jury3', $juryChoisi) . "</td>
				</tr>";
    $form_ins .= "<tr>
				<td colspan=3><center><input type='submit' value='INS&Eacute;RER' name='inserer' class='submit'></center></td>
				<td colspan=2><center><input type='button' value='ANNULER & QUITTER' class='submit' onClick=window.location='" . generer_url_ecrire('operateurs') . "'></center></td>
				</tr>";

    $form_ins .= "</tr></table></form>";
    echo $form_ins;
//    echo fin_cadre_relief();
}

function creerOperateursAuteurs($lien, $annee)
{
    $tSql[] = "DELETE FROM spip_auteurs WHERE email like '%notes@%'";
    $tAlphabet = range('a', 'z');
    $tVoyelles = array('a', 'e', 'i', 'o', 'u', 'y');
    $tConsonnes = array_diff($tAlphabet, $tVoyelles);
    $maj = date('Y-m-d h:i:s');
    $tSql[] = "UPDATE bg_operateur SET mdp=concat(ELT(1+floor(RAND()*" . (count($tConsonnes) - 1) . "),'" . implode("','", $tConsonnes) . "'),
					ELT(1+floor(RAND()*" . (count($tVoyelles) - 1) . "),'" . implode("','", $tVoyelles) . "'),
					ELT(1+floor(RAND()*" . (count($tConsonnes) - 1) . "),'" . implode("','", $tConsonnes) . "'),
					ELT(1+floor(RAND()*" . (count($tVoyelles) - 1) . "),'" . implode("','", $tVoyelles) . "'),
					ELT(1+floor(RAND()*" . (count($tConsonnes) - 1) . "),'" . implode("','", $tConsonnes) . "'),
					ELT(1+floor(RAND()*" . (count($tVoyelles) - 1) . "),'" . implode("','", $tVoyelles) . "'),
					ELT(1+floor(RAND()*" . (count($tConsonnes) - 1) . "),'" . implode("','", $tConsonnes) . "'),
					ELT(1+floor(RAND()*" . (count($tVoyelles) - 1) . "),'" . implode("','", $tVoyelles) . "'))
					WHERE mdp='' AND login_operateur!='' AND annee='$annee' ";

    $tSql[] = 'INSERT INTO spip_auteurs(nom, statut, bio, nom_site, email, login, pass, source, lang, en_ligne, url_site, low_sec, pgp, htpass) '
        . " select "
        . " login_operateur, '1comite' statut, 'Notes' bio, login_operateur, concat('notes@',jury1,'@',jury2,'@',jury3), "
        . " login_operateur login, md5(mdp) pass, 'spip' source, 'fr' lang, '$maj' en_ligne, '' url_site , '' low_sec, '' pgp, '' htpass "
        . " FROM bg_operateur "
        . " WHERE login_operateur!='' AND annee='$annee' ORDER BY login_operateur";

    foreach ($tSql as $sql) {
        bg_query($lien, $sql, __FILE__, __LINE__);
    }

    echo debut_cadre_enfonce(), mysqli_affected_rows(), " Lignes affectés <br/>Anciens auteurs SPIP opérateurs de saisie supprimés avec succ&egrave;s <br/>Mots de passe des opérateurs générés avec succ&egrave;s ", fin_cadre_enfonce();
}

function exec_operateurs()
{
    $exec = _request('exec');
    $titre = "exec_$exec";
    $commencer_page = charger_fonction('commencer_page', 'inc');
    echo $commencer_page($titre);
    $lien = lien();
    $statut = getStatutUtilisateur();
    $tab_auteur = $GLOBALS["auteur_session"];
    $login = $tab_auteur['login'];
    $nom = $tab_auteur['nom'];
    $id_auteur = $tab_auteur['id_auteur'];

    foreach ($_REQUEST as $key => $val) {
        $$key = mysqli_real_escape_string($lien, trim(addslashes($val)));
    }
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
            default:
                die(KO . " - Statut <b>$statut</b> invalide");
        }
    } else {
        $isAdmin = true;
    }
    if ($annee == '') {
        $annee = date("Y");
    }

/*
echo "<pre>";
print_r($_POST);
echo "</pre>";
 */

    if (isAutorise(array('Admin', 'Informaticien', 'Encadrant', 'Inspection'))) {
        echo debut_cadre_trait_couleur('', '', '', "GESTION DES OPERATEURS POUR LA SAISIE DE NOTES $annee", "", "", false);

        if (!isset($_REQUEST['annee']) && (!isset($_REQUEST['etape']))) {
            $liste = "<form action='" . generer_url_ecrire('operateurs') . "' method='POST' class='forml spip_xx-small' name='form_annee'>";
            $liste .= "<center><select name='annee' id='annee' onchange='document.forms.form_annee.submit()' >" . optionsAnnee($annee) . "</select>";
            $liste .= "</center></form>";
            echo $liste;
        }
        if (isset($_REQUEST['sauvegarder'])) {
            $password = md5($pass);
            $sql = "UPDATE spip_auteurs SET pass='$password' WHERE id_auteur=$id_auteur";
            bg_query($lien, $sql, __FILE__, __LINE__);

        }
        if (isset($_POST['annee'])) {
            //     InsererOperateurs($lien, $annee);
            // }
            // if (isset($_REQUEST['etape']) && $_REQUEST['etape'] == 'motdepasse') {
            echo "<h5><center><a href=" . generer_url_ecrire('operateurs') . ">Retour au Menu principal</a></center></h5>";
            echo debut_gauche();

            echo debut_boite_info(), "<b>Gestion des Utilisateurs</b>", fin_boite_info();
            echo debut_boite_info(), "<b>Mot de Passe</b><br/><a href='" . generer_url_ecrire('operateurs') . "&etape=motdepasse'>Changer Mot de Passe</a><br/><br/>
             <b>Opérateur</b><br/><a href='" . generer_url_ecrire('operateurs') . "&etape=nouvelop'>Nouvel Opérateur</a><br/><br/>",
            fin_boite_info();
            echo debut_boite_info(), "<b>Déconnection</b>", fin_boite_info();
            echo debut_droite();

            $form = "<div class='formulaire_spip'><form action='' method='POST' class='forml spip_xx-small' name='form_ins' >";

            $form .= "<TABLE>";

            $form .= "<tr><td><label></label></td><td><input type='hidden' value='' name='' id='id_etablissement'></td></tr>";

            $form .= "<tr><td colspan=3><fieldset><legend>Informations sur l'Utilisateur </legend></td></tr>";
            $form .= "<tr><td><label>Nom de l'Utilisateur</label></td><td><input style='width:50%' type='text' name='nom' id='nom' value=\" $nom\"  required disabled /></td></tr>";
            $form .= "<tr><td><label>Login de l'Utilisateur</label></td><td><input style='width:50%' type='text' name='login' id='login' value=\" $login\"   required disabled /></td></tr>";

            $form .= "<tr><td colspan=3><fieldset><legend>Changement de Mot de Passe</legend></td></tr>";
            $form .= "<tr><td><label>Nouveau Mot de Passe</label></td><td><input style='width:50%' type='password' name='pass' id='pass1' value='$pass'  required /></td></tr>";
            $form .= "<tr><td><label>Confirmation Mot de Passe</label></td><td><input style='width:50%' type='password' name='pass' id='pass2' value='$pass'   required /></td></tr>";

            $form .= "<tr><td></td><td><input type='hidden' value='$id_auteur' name='id_auteur'></td></tr>";

            $controle_forms = "onClick=\"if( document.forms['form_ins'].pass1.value!=document.forms['form_ins'].pass2.value )
                        {alert('Les deux mots de passe doivent être les même'); return false; }
                        \"; ";
            // else {alert('Opération effectuée avec succès');return true;}
            $form .= "<td><input type='button' style='width: 50%;  height: 2em;' value='ANNULER & QUITTER' class='submit' onClick=window.location='" . generer_url_ecrire('operateurs') . "&etape=motdepasse'></td>";
            $form .= "<td>&nbsp;<input style='width: 50%;  height: 2em;' type='submit' value='ENREGISTRER' name='sauvegarder' class='submit'  $controle_forms /></td>";

            $form .= "</tr></table></form></div>";
            echo $form;

            echo fin_gauche();
        }
    }
    echo fin_gauche(), fin_cadre_trait_couleur(), fin_page();

}

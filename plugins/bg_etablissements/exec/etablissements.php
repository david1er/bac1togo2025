<?php
if (!defined('_ECRIRE_INC_VERSION')) {
    return;
}

include_spip('inc/presentation'); // alleger les inclusions avec un inc/presentation_mini
include '../plugins/fonctions/generales.php';

setlocale(LC_TIME, "fr_FR");

/** Affiche le tableau des valeurs presentes dans un referentiel
 * @param $refer: referentiel en question
 * */
function listeEtablissements($lien, $id_inspection, $choix_inspection = true)
{

    echo debut_cadre_enfonce("../images/logo.png", true, '', "LISTE DES ETABLISSEMENTS ");
    if ($choix_inspection == true) {
        $checked1 = '';
        $checked2 = 'checked';
        $checked3 = '';
        $andWhere = "AND eta.si_centre='non' AND ins.id=$id_inspection ";
    } else {
        $andWhere = '';
    }
    if (isset($_POST['ets_radio']) && $_POST['ets_radio'] == 'etablissement') {
        $checked1 = '';
        $checked2 = 'checked';
        $checked3 = '';
        $andWhere = " AND eta.si_centre='non' AND ins.id=$id_inspection";
        $tous = 'non';
        echo debut_cadre_enfonce(), "<center><a href='../plugins/fonctions/csvfile/inc-excel-liste_etablissement.php?id_inspection=$id_inspection&centre=$tous'> <b>IMPRIMER EN EXCEL - ETABLISSEMENT</b></a></center>", fin_cadre_couleur();
    }
    if (isset($_POST['ets_radio']) && $_POST['ets_radio'] == 'centre') {
        $checked1 = '';
        $checked2 = '';
        $checked3 = 'checked';
        $andWhere = " AND eta.si_centre='oui' AND ins.id=$id_inspection";
        $tous = 'oui';
        echo debut_cadre_enfonce(), "<center><a href='../plugins/fonctions/csvfile/inc-excel-liste_etablissement.php?id_inspection=$id_inspection&centre=$tous'> <b>IMPRIMER EN EXCEL - CENTRE</b></a></center>", fin_cadre_couleur();
    }
    if (isset($_POST['ets_radio']) && $_POST['ets_radio'] == 'tous') {
        $checked1 = 'checked';
        $checked2 = '';
        $checked3 = '';
        $andWhere = " AND ins.id=$id_inspection";
        $tous = 'tous';
        echo debut_cadre_enfonce(), "<center><a href='../plugins/fonctions/csvfile/inc-excel-liste_etablissement.php?id_inspection=$id_inspection&centre=$tous'> <b>IMPRIMER EN EXCEL - TOUS</b></a></center>", fin_cadre_couleur();
    }
    // $sql = "SELECT eta.* FROM bg_ref_etablissement eta ,bg_ref_inspection ins
    // WHERE eta.id_inspection=ins.id $andWhere ORDER BY eta.etablissement";
    $sql = "SELECT eta.*,id_prefecture FROM bg_ref_etablissement eta, bg_ref_ville vil, bg_ref_prefecture pre,bg_ref_inspection ins
    WHERE pre.id=vil.id_prefecture AND vil.id=eta.id_ville AND eta.id_inspection=ins.id $andWhere ORDER BY eta.etablissement ";

    $result = bg_query($lien, $sql, __FILE__, __LINE__);

    //echo debut_cadre_enfonce(), "<center><a href='../plugins/fonctions/csvfile/inc-excel-liste_etablissement.php?id_inspection=$id_inspection'> <b>IMPRIMER EN EXCEL</b></a></center>", fin_cadre_couleur();
    $tableau = "<FORM name='form_ets' method='POST' action=''><table size=50%>";

    $tableau .= "<th></th><th></th><th></th>";
    $tableau .= "<td>Tous<input onchange='document.forms.form_ets.submit()' type='radio' name='ets_radio' value='tous' $checked1 /></td>
    <td>Etablissement<input onchange='document.forms.form_ets.submit()' type='radio' name='ets_radio' value='etablissement' $checked2 /></td>
    <td>Centre<input onchange='document.forms.form_ets.submit()'type='radio' name='ets_radio' value='centre' $checked3 /></td>
    </tr>";
    $tableau .= "</table></FORM>";
    $tableau .= "<table class='spip liste'><thead>";
    $tableau .= "<th>Etablissement</th><th>Code Etablissement</th><th>Login</th><th>Prefecture</th><th>Commune</th><th>Ville</th><th>Centre</th>";
    $tableau .= "</thead><tbody>";
    $tab = array('id', 'etablissement', 'id_inspection', 'id_prefecture','id_commune', 'id_ville', 'id_type_etablissement', 'id_etat', 'code', 'nom_responsable', 'telephones_eta', 'login_eta', 'mdp_eta', 'id_centre');
    while ($row = mysqli_fetch_array($result)) {
        foreach ($tab as $var) {
            $$var = $row[$var];
        }
        $tableau .= "<tr><td><a href=" . generer_url_ecrire('etablissements') . "&etape=modifier&id_inspection=$id_inspection&id_etablissement=$id>".dSC($etablissement)."</a></td><td>$code</td><td>$login_eta</td>"
        . "<td>" . selectReferentiel($lien, 'prefecture', $id_prefecture) . "</td>"
        . "<td>" . selectReferentiel($lien, 'commune', $id_commune) . "</td>"
        . "<td>" . selectReferentiel($lien, 'ville', $id_ville) . "</td>"
        . "<td>" . dSC(selectReferentiel($lien, 'etablissement', $id_centre)) . "</td></tr>";

    }
    $tableau .= "</tbody><tfoot><tr></tr></tfoot></table>";
    echo $tableau;
    echo fin_cadre_relief(true);
}

function InsererEtablissement($lien, $id_inspection, $id_etablissement = '', $etape = 'inserer')
{
    echo debut_cadre_couleur("../images/logo.png", '', '', "INSERTION DES ETABLISSEMENTS", "", "");
    $id_region = recherche_region($lien, $id_inspection);
    if ($etape == 'inserer') {
        $where = " WHERE id_inspection=$id_inspection ";
        $where2 = " WHERE id_region=$id_region ";
        $where_eta = " AND id_region=$id_region ";
        $where_pre = " AND id_region=$id_region ";
        $where_com = " AND reg.id=pre.id_region AND pre.id=com.id_prefecture AND id_region=$id_region ";
    } else {

        $where = " WHERE id_inspection=$id_inspection ";
        $where2 = " WHERE id_region=$id_region ";
        $where_eta = " AND id_region=$id_region ";
        $where_pre = " AND id_region=$id_region ";
        $where_com = "  ";
        // $where = '';
        // $where2 = '';
    }

    echo '<style>',
    '.disabled-cursor{',
    'cursor: none;',
    '};',
        '</style>'
    ;

    // $form_ins = "<form action='' method='POST' class='forml spip_xx-small' name='form_ins' onload=\"document.forms.form_ins.etablissement.focus();\">";
    $form = "<form action='' method='POST' class='forml spip_xx-small' name='form_ins' >";
    $where_ins = " WHERE id_region=$id_region ";

    $form .= "<TABLE>";
    //$form_ins .= "<fieldset><legend align='center' style='color: #3333DD ; background: #CCCCFF ;'><b>Informations Obligatoires</b></legend>";

    if ($_REQUEST['etape'] && $_REQUEST['etape'] == 'modifier') {
        $sql2 = "SELECT  * FROM bg_ref_etablissement eta,bg_ref_prefecture pre,bg_ref_ville vil
        WHERE eta.id=$id_etablissement AND vil.id=eta.id_ville AND pre.id=vil.id_prefecture ";
        $result2 = bg_query($lien, $sql2, __FILE__, __LINE__);
        $tab = array('etablissement', 'id_ville', 'id_commune', 'id_prefecture', 'id_inspection', 'id_type_etablissement', 'id_etat', 'code', 'nom_responsable', 'telephones_eta', 'nombre_garcon', 'nombre_fille', 'login_eta', 'mdp_eta', 'si_centre', 'id_centre', 'id_region_division');

        $row = mysqli_fetch_array($result2);
        foreach ($tab as $var) {
            $$var = $row[$var];
        }
        $where_com = " AND id_prefecture=$id_prefecture ";

        $form .= "<tr><td><label></label></td><td><input type='hidden' value='$id_etablissement' name='id_etablissement' id='id_etablissement'></td></tr>";
    } else {
        $etablissement = $code = $nom_responsable = $telephones_eta = $login_eta = $mdp_eta = '';
//        $id_ville=$id_inspection=$id_type_etablissement=$id_etat=$id_region_division=0;
        $si_centre = 'non';
    }
    //Mettre le champ en majuscule
    $majuscule = " OnkeyUp=\"javascript:this.value=this.value.toUpperCase();\" ";
    $form .= "<tr><td><label>Région </label></td><td><select style='width:250px' name='id_region_division' >" . optionsReferentiel($lien, 'region_division', $id_region_division, "$where2") . "</select></td></tr>";
    $form .= "<tr><td><label>Inspection</label></td><td><select style='width:250px' name='id_inspections'>" . optionsReferentiel($lien, 'inspection', $id_inspection, "$where_ins") . "</select></td></tr>";
    $form .= "<tr><td><label>Préfecture</label></td><td><select style='width:250px' name='id_prefecture'>" . optionsReferentiel($lien, 'prefecture', $id_prefecture, "$where_ins") . "</select></td></tr>";
    $form .= "<tr><td><label>Commune</label></td><td><select style='width:250px' name='id_commune'>" . optionsCommune($lien, 'commune', $id_commune, $where_com) . "</select></td></tr>";
    $form .= "<tr><td><label>Ville/Canton</label></td><td><select style='width:250px' name='id_ville' >" . optionsVille($lien, 'ville/Canton', $id_ville, $where_pre) . "</select></td></tr>";
    $form .= "<tr><td><label>Type </label></td><td><select style='width:250px' name='id_type_etablissement'>" . optionsReferentiel($lien, 'type_etablissement', $id_type_etablissement, '') . "</select></td></tr>";
    $form .= "<tr><td><label>Etat</label></td><td><select style='width:250px' name='id_etat'>" . optionsReferentiel($lien, 'etat', $id_etat, '') . "</select></td></tr>";
    $form .= "<tr><td><label>Nom de l'établissement</label></td><td><input type='text' name='etablissement' id='etablissement' value=\"$etablissement\" $majuscule size=60 required /></td></tr>";
    //$form_ins .= "</fieldset>";
    $form .= "<tr><td><label>Si Centre</label></td><td>$si_centre<input type='hidden' name='si_centre' id='si_centre' value='$si_centre' size=60></td></td></tr>";
    $form .= "<tr><td><label>Centre proche</label></td><td><select style='width:250px' disabled>" . optionsEtablissement($lien, $id_centre, " AND si_centre='oui' " . $where_eta) . "</select><select hidden style='width:250px' name='id_centre'>" . optionsEtablissement($lien, $id_centre, " AND si_centre='oui' " . $where_eta) . "</select></td></tr>";
    $form .= "<tr><td><label>Code Etablissement</label></td><td><input type='text' name='code' id='code' value='$code' size=30 maxlength=12></td></tr>";
    $form .= "<tr><td><label>Nom du responsable</label></td><td><input type='text' name='nom_responsable' id='nom_responsable' value='$nom_responsable' size=60></td></tr>";
    $form .= "<tr><td><label>Téléphones</label></td><td><input type='text' name='telephones_eta' id='telephones_eta' value='$telephones_eta' size=60></td></tr>";
    $form .= "<tr><td><label>Nombre de filles attendues</label></td><td><input type='number' name='nombre_fille' id='nombre_fille' value='$nombre_fille' size=30></td></tr>";
    $form .= "<tr><td><label>Nombre de garçons attendus</label></td><td><input type='number' name='nombre_garcon' id='nombre_garcon' value='$nombre_garcon' size=30></td></tr>";
    $form .= "<tr><td><label>Login</label><small style='color:red'><br/>(Obligatoire, veuillez adopter une règle de nommage!!!) --- Ex: cpl_mon_ecole</small></td><td><input type='text' name='login_eta' id='login_eta' value='$login_eta'  size=30><div id='errorLogin'></div></td></tr>";
    $form .= "<tr><td><label>Mot de passe</label></td><td><input type='text' name='mdp_eta' id='mdp_eta' value='$mdp_eta' readonly size=30></td></tr>";
    // $form .= "<tr><td></td><td><input type='hidden' value='$id_inspection' name='id_inspection'></td></tr>";

    // || document.forms['form_ins'].login_eta.value==0
    /* if (isset($_POST['login_eta']) && $_POST['login_eta'] != '') {
    if (sql_countsel('bg_ref_etablissement', 'login_eta=' . sql_quote($login_eta)) > 0) {
    //$error="<span style='color:red;'>Login déja existant</span>";
    echo "<script>";
    echo "alert('fffffffffffffffffffffffffff');";
    // echo "document.getElementById(\'errorLogin\').innerHTML = 'Le login existe déja, veuillez ressaisir !!!';";
    echo "alert('FOUTEZ MOI LE CAMP');";
    echo "document.forms['form_ins'].login_eta.value = '00';";
    echo "alert('1000000000000000000000');";
    echo "</script>";
    return false;
    }else{
    var_dump('BOOOOOOOONNNNNNNNNNN');
    $controle_forms;
    }
    }else{

    $controle_forms;
    } */
    if ($si_centre == "oui") {
        $controle_forms = "onClick=\"if(document.forms['form_ins'].id_region_division.value==0
        || document.forms['form_ins'].id_ville.value==0
        || document.forms['form_ins'].id_prefecture.value==0
        || document.forms['form_ins'].id_etat.value==0
        || document.forms['form_ins'].id_type_etablissement.value==0)
                            {alert('Les champs  REGION, VILLE, PREFECTURE doivent être remplis'); return false; }
                            else {alert('Opération effectuée avec succès');return true;}\"; ";
    } else {
        $controle_forms = "onClick=\"if(document.forms['form_ins'].id_region_division.value==0
    || document.forms['form_ins'].id_ville.value==0
    || document.forms['form_ins'].id_prefecture.value==0
    || document.forms['form_ins'].id_etat.value==0
    || document.forms['form_ins'].id_type_etablissement.value==0
    || document.forms['form_ins'].login_eta.value=='')
                        {alert('Les champs  REGION, VILLE, PREFECTURE, LOGIN doivent être remplis'); return false; }
                        else {alert('Opération effectuée avec succès');return true;}\"; ";
    }

    $form_ins .= "";
    if ($_REQUEST['etape'] && $_REQUEST['etape'] == 'modifier') {
        $form .= "<td><input type='submit' value='METTRE A JOUR' name='maj' class='submit' $controle_forms /></td>";
        $form .= "<td><input type='button' value='SUPPRIMER' class='fondo' disabled onclick=\"if(confirm('Souhaitez-vous vraiment supprimer cet établissement?')) window.location='" . generer_url_ecrire('etablissements') . "&etape=supprimer&id_inspection=$id_inspection&id_etablissement=$id_etablissement';\">";
        $form .= "&nbsp;&nbsp;&nbsp;&nbsp;<input type='button' value='ANNULER & QUITTER' disabled class='submit' onClick=window.location='" . generer_url_ecrire('etablissements') . "'></td>";
    } else {
        $form .= "<td><input type='submit' value='INSÉRER' name='inserer' class='submit' $controle_forms /></td>";
        $form .= "<td><input type='button' value='ANNULER & QUITTER' disabled class='submit' onClick=window.location='" . generer_url_ecrire('etablissements') . "'></td>";
    }
    $form .= "</tr></table></form>";
    echo $form;
    echo fin_cadre_couleur();
}

function InsererCapacite($lien, $annee, $id_inspection)
{
    foreach ($_REQUEST as $key => $val) {
        $$key = mysqli_real_escape_string($lien, trim($val));
    }

    if (isset($_REQUEST['enregistrer_salles'])) {
        $tab_etablissements = explode('_', $tab_etablissements);
        foreach ($tab_etablissements as $id_etablissement) {
            if ($id_etablissement > 0) {
                $nom_champ = 'nbre_' . $id_etablissement;
                $capacite = $_REQUEST[$nom_champ];
                if ($capacite >= 0 && $capacite != '') {
                    $sql4 = "REPLACE bg_capacite (id_centre,annee,capacite) VALUES ('$id_etablissement','$annee','$capacite') ";
                    bg_query($lien, $sql4, __FILE__, __LINE__);
                }
            }
        }
        $sql5 = "DELETE FROM bg_capacite WHERE capacite=0";
        bg_query($lien, $sql5, __FILE__, __LINE__);
    }

    if (!isset($_REQUEST['enregistrer_salles'])) {
        $tabCapacite = capacites($lien, $annee);
        $sql = "SELECT ins.id as id_inspection, eta.id as id_etablissement, eta.etablissement "
            . " FROM bg_ref_prefecture pre, bg_ref_ville vil, bg_ref_etablissement eta,bg_ref_inspection ins "
            . " WHERE eta.id_ville=vil.id AND vil.id_prefecture=pre.id AND eta.id_inspection=ins.id AND si_centre='oui' AND ins.id=$id_inspection "
            . " ORDER BY ins.inspection, eta.etablissement";
        $result = bg_query($lien, $sql, __FILE__, __LINE__);
        $tab = array('id_inspection', 'etablissement', 'id_etablissement');
        $table = debut_cadre_enfonce("../images/logo.png", '', '', "CAPACITE DES CENTRES ", "", "");
        $table .= "<form method='POST'><table>";
        while ($row = mysqli_fetch_array($result)) {
            foreach ($tab as $var) {
                $$var = $row[$var];
            }

            $capacite = $tabCapacite[$id_etablissement];
            //if($nbre=='') $nbre=0;
            $nom_champ = 'nbre_' . $id_etablissement;
            $tab_etablissements .= '_' . $id_etablissement;
            $table .= "<tr><td>$etablissement</td><td><input type='number' name='$nom_champ' value='$capacite' size=4 maxlength=4 min=0 ></td></tr>";
        }
        $table .= "<tr><td colspan='2'><input type='hidden' name='tab_etablissements' value='$tab_etablissements'></td></tr>";
        $table .= "<tr><td colspan='2'><center><input type='submit' value='Enregistrer' name='enregistrer_salles'></center></td></tr>";
        $table .= "</form></table>";
        $table .= fin_cadre_enfonce();
        echo $table;
    }
}

function exec_etablissements()
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
    if ($statut != 'Admin') {
        switch ($statut) {

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
    $annee = recupAnnee($lien); //$annee = date("Y");

    foreach ($_REQUEST as $key => $val) {
        $$key = mysqli_real_escape_string($lien, trim(addslashes($val)));
    }

    if (isAutorise(array('Admin', 'Dre', 'Informaticien', 'Encadrant', 'Inspection', 'DExCC'))) {
        echo debut_cadre_trait_couleur('', '', '', "Gestion des établissements", "", "", false);

        if (isset($_REQUEST['etape']) && $_REQUEST['etape'] == 'supprimer') {
            //Ne pas oublier un script de vérification pour voir si cet etablissement est utilise dans les tables candidats, salles, etc
            $id = $_REQUEST['id_etablissement'];
            $sql_sup = "DELETE FROM bg_ref_etablissement WHERE id=$id";
            bg_query($lien, $sql_sup, __FILE__, __LINE__);
        }

        if (isset($_REQUEST['inserer']) || isset($_REQUEST['maj'])) {
         //dSC pour remplacer le caractère \ par du vide , ici on l'a fait 2 fois parceque il envois deux deux le \ supplémentaire 
            $etablissement=dSC(dSC($etablissement));
            if ($etablissement == '' || $id_ville == 0 || $id_inspection == 0 || $id_type_etablissement == 0 || $id_etat == 0) {
                echo debut_boite_alerte();
                echo "Veuillez entrer les informations obligatoires";
                echo fin_boite_alerte();
            } else {
                if (isset($_REQUEST['inserer'])) {
                    $maj = date('Y-m-d h:i:s');
                    $nombre_garcon = $nombre_fille = 0;
                    $mdp_eta = concat(gC(1), gV(1), gC(1), gV(1), gC(1), gV(1), gC(1), gV(1));
                    $sql_ins[] = "INSERT INTO bg_ref_etablissement
						(etablissement,id_ville,id_inspection,id_commune,id_type_etablissement,id_etat,code,nom_responsable,telephones_eta,nombre_garcon,nombre_fille,login_eta,mdp_eta,si_centre,id_centre,id_region_division,login_u)
						VALUES ('$etablissement',$id_ville,$id_inspections,$id_commune,$id_type_etablissement,$id_etat,'$code','$nom_responsable','$telephones_eta',$nombre_garcon,$nombre_fille,'$login_eta','$mdp_eta','$si_centre','$id_centre','$id_region_division','$login')";
                    $last_id = recupLastInsertId($lien, 'bg_ref_etablissement') + 1;
                    $sql_ins[] = "INSERT INTO spip_auteurs( nom, statut, bio, nom_site, email, login, pass, source, lang, en_ligne, url_site, low_sec, pgp, htpass  )
                       VALUE ('$etablissement', '1comite', 'Etablissement', '$etablissement', concat('etablissement','@','$last_id'),'$login_eta', md5('$mdp_eta'), 'spip', 'fr', '$maj', '' , '', '', ''  )";

                } elseif (isset($_REQUEST['maj'])) {
                    $sql_ins[] = "UPDATE bg_ref_etablissement SET etablissement='$etablissement',id_ville='$id_ville',id_inspection='$id_inspections',id_commune='$id_commune',
						id_type_etablissement='$id_type_etablissement',id_etat='$id_etat',code='$code',nom_responsable='$nom_responsable',
						telephones_eta='$telephones_eta',nombre_garcon='$nombre_garcon',nombre_fille='$nombre_fille',login_eta='$login_eta',mdp_eta='$mdp_eta', si_centre='$si_centre', id_centre='$id_centre',id_region_division='$id_region_division' WHERE id='$id_etablissement' ";
                    // $sql_ins[] = "UPDATE spip_auteurs SET login_eta='$login_eta',pass= md5('$mdp_eta') WHERE nom='$etablissement'";
                    //Mettre à jour la table des candidats pour prendre en compte le nouveau centre de composition
                    if ($id_centre != 0) {
                        $sql_cand = "UPDATE bg_candidats SET centre='$id_centre' WHERE annee=$annee AND etablissement='$id_etablissement' ";
                        bg_query($lien, $sql_cand, __FILE__, __LINE__);
                    }
                }
                foreach ($sql_ins as $sql) {
                    bg_query($lien, $sql, __FILE__, __LINE__);
                }
                // bg_query($lien, $sql_ins, __FILE__, __LINE__);
            }
        }

        if (!isset($_REQUEST['id_inspection']) && (!isset($_REQUEST['etape']))) {

            if (!isset($_REQUEST['id_inspection']) && (!isset($_REQUEST['etape']))) {
                if ($isEncadrant) {
                    listeChoix($lien, $andWhereRegion, 'etablissements');
                } elseif ($isInspection) {
                    listeChoix($lien, $andWhereInspection, 'etablissements');
                } else {listeChoix($lien, '', 'etablissements');}

            }
        }

        if (isset($_REQUEST['id_inspection'])) {
            echo "<div class='onglets_simple clearfix'>
		<ul>
			<li><a href='./?exec=etablissements&etape=inserer&id_inspection=$id_inspection' class='ajax'>Insérer</a></li>
            <li><a href='./?exec=etablissements&etape=afficher&id_inspection=$id_inspection' class='ajax'>Voir la liste</a></li>";
            if ($isAdmin) {
                echo " <li><a href='./?exec=etablissements&affiche_salles&id_inspection=$id_inspection' class='ajax'>Capacités</a></li> ";
            }
            if ($isEncadrant) {
                echo "<li><a href='../plugins/fonctions/inc-liste_etablissements.php?id_region=$id_region' class='ajax'>Imprimer</a></li>";

            } else {
                echo "<li><a href='../plugins/fonctions/inc-liste_etablissements.php' class='ajax'>Imprimer</a></li>";

            }
            echo "<li><a href='./?exec=etablissements&etape=generer&id_inspection=$id_inspection' class='ajax'>Impression des Codes </a></li>
			<li><a href='./?exec=etablissements' class='ajax'>Inspections</a></li>
		</ul>
	</div>";
            if (isset($_GET['etape']) && ($_GET['etape'] == 'afficher' || isset($_REQUEST['maj']) || $_GET['etape'] == 'supprimer')) {
                listeEtablissements($lien, $id_inspection, true);
            } elseif (isset($_REQUEST['etape']) && $_REQUEST['etape'] == 'inserer') {
                InsererEtablissement($lien, $id_inspection);
            }
        }

        if (isset($_REQUEST['etape']) && $_REQUEST['etape'] == 'modifier' && !isset($_REQUEST['maj'])) {
            InsererEtablissement($lien, $id_inspection, $id_etablissement, 'modifier');
        }
        if (isset($_REQUEST['etape']) && $_REQUEST['etape'] == 'generer') {

            echo '<script type="text/javascript">function generer_code(){',
            '  window.open("../plugins/fonctions/inc-pdf-login-only.php?id=' . $tri_etablissement . '")};',
                '</script>'
            ;

            // echo '<script type="text/javascript">function generer_code_lot(){',
            // '  window.open("../plugins/fonctions/inc-pdf-logins.php?id=' . $region_imp . '")};',
            //     '</script>'
            // ;
            $andWhereCen = " AND eta.si_centre='oui' AND ins.id=eta.id_inspection AND eta.id_inspection=$id_inspection";
            $andWhereEta = " AND eta.si_centre='non' AND  ins.id=eta.id_inspection AND eta.id_inspection=$id_inspection ";

            if (isset($_POST['tri_centre']) && $_POST['tri_centre'] > 0) {
                $andWhereEta .= "  AND ins.id=eta.id_inspection AND eta.id_inspection=$id_inspection AND eta.id_centre=$tri_centre";
            }
            if (isset($_POST['tri_region']) && $_POST['tri_region'] > 0) {
                $region_imp = selectRegionDivisionByRegion($lien, $tri_region);

                echo '<script type="text/javascript">function generer_code_lot(){',
                '  window.open("../plugins/fonctions/inc-pdf-logins.php?id=' . $region_imp . '")};',
                    '</script>'
                ;
            }

            echo debut_cadre_enfonce("../images/logo.png", true, '', "Impression des Logins pour un Etablissement ");
            $form1 = "<FORM name='form_rep' method='POST' action=''><table size=50%>";
            $form1 .= "<th><center>Centre</center></th><th><center>Etablissement</center></th><th></th>";
            $form1 .= "<tr><td><center><select  name='tri_centre' onchange='document.forms.form_rep.submit()'>" . optionsEtablissement($lien, $tri_centre, $andWhereCen, true, ',bg_ref_inspection ins ', '', 'Centre') . "</select></center></td>";
            $form1 .= "<td><center><select  id='tri_etablissement' name='tri_etablissement' onchange='document.forms.form_rep.submit()'>" . optionsEtablissement($lien, $tri_etablissement, $andWhereEta, true, ',bg_ref_inspection ins ', '', 'Etablissement') . "</select></center></td>";
            $form1 .= "<td><input type='hidden' name='id_type_session' value='$id_type_session' /><input type='hidden' name='annee' value='$annee' /></td>";
            $form1 .= "<td><input type='submit' value='Imprimer' name='generer_codes' onclick='generer_code()' /></td></tr>";
            $form1 .= "</table></FORM>";

            echo $form1;
            echo fin_cadre_enfonce();
            if (!$isInspection) {echo debut_cadre_enfonce("../images/logo.png", true, '', "Impression des Logins des établissements par DRE");
                $form2 = "<FORM name='form_gen' method='POST' action=''><table size=50%>";
                $form2 .= "<th><center>Région</center></th><th></th><th></th>";
                $form2 .= "<tr><td style='width:85%'><center><select style='width:30%' name='tri_region' onchange='document.forms.form_gen.submit()'>" . optionsReferentiel($lien, 'region', $tri_region, $WhereRegion) . "</select></center></td>";
                $form2 .= "<td><input type='hidden' name='id_type_session' value='$id_type_session' /><input type='hidden' name='annee' value='$annee' /></td>";
                $form2 .= "<td><input type='submit' value='Imprimer' name='generer_codes_lot' onclick='generer_code_lot()' /></td></tr>";
                $form2 .= "</table></FORM>";

                echo $form2;
                echo fin_cadre_enfonce();}

        }
        if (isset($_REQUEST['affiche_salles'])) {
            //Verification de l'existence des salles pour l'annee en cours. Sinon copier pour l'annee precedente
            $sql = "SELECT * FROM bg_capacite WHERE annee=$annee ";
            $result = bg_query($lien, $sql, __FILE__, __LINE__);
            if (mysqli_num_rows($result) == 0) {
                $sql2 = "INSERT INTO bg_capacite (`id_centre`, `annee`, `capacite`)
				(SELECT `id_centre`, $annee, `capacite` FROM bg_capacite WHERE annee=" . ($annee - 1) . ')';
                bg_query($lien, $sql2, __FILE__, __LINE__);
            }
            InsererCapacite($lien, $annee, $id_inspection);
        }
    }
    echo fin_gauche(), fin_cadre_trait_couleur(), fin_page();

}

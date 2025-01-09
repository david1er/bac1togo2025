<?php
if (!defined('_ECRIRE_INC_VERSION')) {
    return;
}

include_spip('inc/presentation'); // alleger les inclusions avec un inc/presentation_mini
include '../plugins/fonctions/generales.php';
setlocale(LC_TIME, "fr_FR");
define('MAX_LIGNES', 200);

function exec_statistique()
{
    $exec = _request('exec');
    $titre = "exec_$exec";
    $commencer_page = charger_fonction('commencer_page', 'inc');
    echo $commencer_page($titre);
    $statut = getStatutUtilisateur();
    $lien = lien();
    session_start();

    foreach ($_REQUEST as $key => $val) {
        $$key = mysqli_real_escape_string($lien, trim($val));
        // $$key = mysqli_real_escape_string($lien, trim(addslashes($val)));
    }

    $tab_auteur = $GLOBALS["auteur_session"];
    $login = $tab_auteur['login'];
    $lien_photos = "../photos/$annee/";
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
                $WhereRegion = " WHERE id_region='$id_region' ";

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
        $annee = recupAnnee($lien);
    }
    if ($tri_type_session == '') {
        $tri_type_session = recupSession($lien);
    }
    if (isAutorise(array('Admin', 'Dre', 'Encadrant', 'Informaticien', 'Inspection', 'DExCC'))) {
        echo debut_grand_cadre();
        echo debut_cadre_trait_couleur('', '', '', "GESTION DES STATISTIQUES", "", "", false);

        foreach ($_REQUEST['tache'] as $cle => $tval) {;}

        if (!isset($_REQUEST['id_inspection']) && !isset($_REQUEST['etape'])) {
            echo "<div class='onglets_simple clearfix'><ul>";

            echo "<li><a href='./?exec=statistique&etape=inscription' class='ajax'>Inscription</a></li>";
            echo "<li><a href='./?exec=statistique&etape=repartition' class='ajax'>Répartition</a></li>";
            echo "<li><a href='./?exec=statistique&etape=resultat' class='ajax'>Résultat</a></li>";
            echo "<li><a href='./?exec=statistique&etape=calcul' class='ajax'>Jury</a></li>";
            echo "<li><a href='./?exec=statistique&etape=doc' class='ajax'>Guide</a></li>";
            echo "<li><a href='./?exec=statistique&action=logout&logout=prive' class='ajax'>Se déconnecter</a></li>";
            echo "</ul>
		</div>";
        }
        if (isset($_GET['etape']) && $etape == 'doc') {
            $doc = debut_cadre_couleur('', '', '', "DOCUMENTATION");
            $doc .= "<table>";
            $doc .= "<tr><td colspan=2>La gestion des Statistiques des Candidats  comporte les différernts statistiques des après les inscriptions ,après la réprtition ,après les calculs et celle des jury </td></tr>";
            $doc .= "<tr><td>1. Statistique après Inscription</td><td>Statistiques par Centre après Inscription : La liste des centres et leurs éffectifs totals inscrits<br/>
            Statistiques par établissement après Inscription : La liste des Etablissments et l'éffectif des candidats inscris dans chaque établissement<br/>

            </td></tr>";
            $doc .= "<tr><td>2. Statistique après Répartition</td><td>Impression des listes d'affichage : La liste d'affichage dans les centre après inscription <br/>Impression des listes d'émargement:<br/>
            Impression des listes d'émargement par jour : </br>
            Jurys par centre de composition :Répartitions des jury et des effectifs par centre de composition</br>
            </td></tr>";
            $doc .= "<tr><td>3. Statistique après Résultat</td><td>Taux de réussite : <br/>Liste des admis : </br>
            Classement des candidats : </br> Inscrits par établissement : </br>
            Statistiques des inscrits : </br> Statistiques des présents : </br>
            Statistiques des absents : </br> Statistiques des admis : </br>
            Classement des établissements: </br> Statistiques sur les notes: </br>
            </td></tr>";
            $doc .= "<tr><td>4. Statistique des Jury</td><td>Impressions Affichage Jury : <br/>Emargement par Jury : </br>
            Classement des candidats du Jury: </br> Liste des admis du Jury: </br>
            Stat des Notes du Jury : </br> Liste des absents du Jury : </br>
            <p><a href='./?exec=statistique'>Retour</a></p></td></tr>";

            $doc .= "</table>";
            $doc .= fin_cadre_couleur();
            echo $doc;
        }
        /////////////////////////////// DEBUT APRES INSCRIPTION ////////////////////////////////////////////////////////////
        if ((isset($_REQUEST['etape']) && ($_GET['etape'] == 'inscription')) || isset($_POST['id_region'])) {

            $form = debut_cadre_enfonce('', '', '', "Critères");
            $controle_js = "onclick=\"if(tri_centre.value!=0) return true; else {alert('Veuillez choisir un centre'); return false;}\"";
            if ($isInspection) {
                $andWhereCen = " AND eta.si_centre='oui' AND ins.id_region='$id_region' AND eta.id_inspection=$id_inspection ";
                $andWhereEta = " AND eta.si_centre='non' AND ins.id_region='$id_region' AND eta.id_inspection=$id_inspection ";
                $andWhereRegion = " WHERE id_region='$id_region' AND id=$id_inspection";
                $WhereRegion = "WHERE id=$id_region";
                $where_pre = "AND  pre.id_region=$id_region ";
            }
            if ($isEncadrant) {
                $andWhereCen .= " AND eta.si_centre='oui' AND ins.id=eta.id_inspection AND ins.id_region=$id_region";
                $andWhereEta .= " AND eta.si_centre='non' AND ins.id=eta.id_inspection AND ins.id_region=$id_region";
            }
            $andWhereEta .= " AND eta.si_centre='non'";
            $andWhereCen .= " AND eta.si_centre='oui'";
            if (isset($_POST['tri_centre']) && $_POST['tri_centre'] > 0) {
                $andWhereEta = "AND eta.id_centre=$tri_centre";
            }
            if (isset($_POST['tri_inspection']) && $_POST['tri_inspection'] > 0) {
                $andWhereEta .= " AND eta.id_inspection=$tri_inspection ";
                $andWhereCen .= " AND eta.si_centre='oui' AND ins.id=eta.id_inspection AND eta.id_inspection=$tri_inspection ";
            }
            if (isset($_POST['tri_region']) && $_POST['tri_region'] > 0) {
                // $andWhere .= " AND ins.id_region=$tri_region ";
                if ($isEncadrant) {$andWhereRegion .= " AND id_region=$tri_region";} else {
                    $where_pre .= "AND  id_region=$tri_region ";
                    $andWhereRegion .= "WHERE id_region=$tri_region";
                    $andWhereEta .= " AND ins.id_region=$tri_region AND ins.id=eta.id_inspection";
                    $andWhereCen .= "AND si_centre='oui' AND ins.id=eta.id_inspection AND ins.id_region=$tri_region";
                }
                // $where_pre .= "AND  id_region=$tri_region ";
                // $andWhereRegion .= "WHERE id_region=$tri_region";
                // $andWhereEta .= " AND ins.id_region=$tri_region AND ins.id=eta.id_inspection";
                // $andWhereCen .= "AND si_centre='oui' AND ins.id=eta.id_inspection AND ins.id_region=$tri_region";
            }
            if (isset($_POST['tri_prefecture']) && $_POST['tri_prefecture'] > 0) {
                $andWhereEta .= " AND vil.id_prefecture=$tri_prefecture ";
                $andWhereCen .= " AND eta.si_centre='oui' AND vil.id_prefecture=$tri_prefecture ";
                $where_pre .= "AND vil.id_prefecture=$tri_prefecture";
                $where_com .= " WHERE  id_prefecture=$tri_prefecture";
            }

            if (isset($_POST['tri_commune']) && $_POST['tri_commune'] > 0) {
                $andWhereEta .= " AND vil.id_prefecture=$tri_commune ";
                $andWhereCen .= " AND eta.si_centre='oui' AND vil.id_prefecture=$tri_commune ";
                $where_pre .= " AND id_prefecture=$tri_commune";
            }

            if (isset($_POST['tri_etablissement']) && $_POST['tri_etablissement'] > 0) {

                $andWhereEta .= " ";
            }

            // if ($cle != 'levee_anonymat' && $cle != 'impression_numeros') {
            $form = debut_cadre_enfonce('', '', '', "Critères");
            $controle_js = "onclick=\"if(tri_centre.value!=0) return true; else {alert('Veuillez choisir un centre'); return false;}\"";
            $form .= "<form method='POST' name='form_rep'>";
            $form .= " <table>";
            $form .= "<tr><td><center><select style='width:50%' name='annee' onchange='document.forms.form_rep.submit()'>" . optionsAnnee(2020, $annee) . "</select></center></td>
            <td><center><select style='width:50%' name='tri_type_session' onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'type_session', $tri_type_session) . "</select></center></td></tr>";
            if ($isEncadrant || $isAdmin || !$isInspection) {
                $form .= "<tr><td><center><select style='width:50%'   name='tri_region' onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'region', $tri_region, $WhereRegion) . "</select></center></td>
            <td><center><select style='width:50%' name='tri_prefecture' onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'prefecture', $tri_prefecture, $andWhereRegion) . "</select></center></td></tr>";
            }
            $form .= "<tr><td><center><select style='width:50%' name='tri_inspection' onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'inspection', $tri_inspection, $andWhereRegion) . "</select></center></td>
            <td><center><select style='width:50%' name='tri_commune' onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'commune', $tri_commune, $where_com) . "</select></center></td></tr>";
            $form .= "<tr><td><center><select style='width:50%' name='tri_centre' onchange='document.forms.form_rep.submit()'>" . optionsEtablissement($lien, $tri_centre, $andWhereCen, true, ',bg_ref_inspection ins ', '', 'Centre') . "</select></center></td>
            <td><center><select style='width:50%' name='tri_ville' onchange='document.forms.form_rep.submit()'>" . optionsVille($lien, 'ville', $tri_ville, $where_pre) . "</select></center></td></tr>";
            $form .= "<tr><td><center><select style='width:50%' name='tri_etablissement' onchange='document.forms.form_rep.submit()'>" . optionsEtablissement($lien, $tri_etablissement, $andWhereEta, true, ',bg_ref_inspection ins ') . "</select></center></td>
            <td><center><select style='width:50%' name='tri_langue_vivante' onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'langue_vivante', $tri_langue_vivante) . "</select></center></td></tr>";
            $form .= "<tr> <td><center><select style='width:50%' name='tri_serie' onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'serie', $tri_serie, 'WHERE id <4') . "</select></center></td>
            <td><center><select style='width:50%' name='tri_epreuve_facultative' onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'epreuve_facultative_a', $tri_epreuve_facultative, '', true, 'Epr. Fac.') . "<option value=8>Couture</option></select></center></td></tr>";
            $form .= "<tr><td><center><select style='width:50%' name='tri_sexe' onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'sexe', $tri_sexe) . "</select></center></td>
            <td style='width:50%'><center><select style='width:50%' name='tri_eps'  onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'eps', $tri_eps) . "</select></center></td></tr>";
            $form .= "</table> ";
            $form .= fin_cadre_enfonce();
            $form .= "<p><center><a href='./?exec=statistique'>Retour au Menu principal</a></center></p>";
            $form .= debut_cadre_couleur('', '', '', "Statistiques après Inscription");
            $form .= "<table>";
            $form .= "<tr><td><input type='image' src='../plugins/images/inscription_par_centre.png' width='75px' name='tache[stats_inscription]' value='stats_inscription' /> <br/>Statistiques par Centre après Inscription</td>
                    <td><input type='image' src='../plugins/images/ecole.png' width='75px' name='tache[stats_inscription_etablissements]' value='stats_inscription_etablissements' /> <br/>Statistiques par établissement après Inscription</td></tr>";
            $form .= "</table>";
            $form .= "</form>";
            $form .= fin_cadre_couleur();

        }

        //////////////////////////////// FIN APRES INSCRIPTION//////////////////////////////

        ///////////////////////////////////DEBUT APRES REPARTITION //////////////////////////////////////////////////////
        if ((isset($_REQUEST['etape']) && ($_GET['etape'] == 'repartition')) || isset($_POST['id_region'])) {

            $form = debut_cadre_enfonce('', '', '', "Critères");
            $controle_js = "onclick=\"if(tri_centre.value!=0) return true; else {alert('Veuillez choisir un centre'); return false;}\"";
            if ($isInspection) {
                $andWhereCen = " AND eta.si_centre='oui' AND ins.id_region='$id_region' AND eta.id_inspection=$id_inspection ";
                $andWhereEta = " AND eta.si_centre='non' AND ins.id_region='$id_region' AND eta.id_inspection=$id_inspection ";
                $andWhereRegion = " WHERE id_region='$id_region' AND id=$id_inspection";
                $WhereRegion = "WHERE id=$id_region";
            }
            if ($isEncadrant) {
                $andWhereCen .= " AND eta.si_centre='oui' AND ins.id=eta.id_inspection AND ins.id_region=$id_region";
                $andWhereEta .= " AND eta.si_centre='non' AND ins.id=eta.id_inspection AND ins.id_region=$id_region";
            }
            $andWhereCen .= " AND eta.si_centre='oui'";
            $andWhereEta .= " AND eta.si_centre='non'";
            if (isset($_POST['tri_centre']) && $_POST['tri_centre'] > 0) {
                $andWhereEta = "AND eta.id_centre=$tri_centre";
            }
            if (isset($_POST['tri_inspection']) && $_POST['tri_inspection'] > 0) {
                $andWhereEta .= " AND eta.id_inspection=$tri_inspection ";
                $andWhereCen .= " AND eta.si_centre='oui' AND ins.id=eta.id_inspection AND eta.id_inspection=$tri_inspection ";
            }
            if (isset($_POST['tri_region']) && $_POST['tri_region'] > 0) {
                //  $andWhere .= " AND ins.id_region=$tri_region ";
                if ($isEncadrant) {$andWhereRegion .= " AND id_region=$tri_region";} else {
                    $where_pre .= "AND  id_region=$tri_region ";
                    $andWhereRegion .= "WHERE id_region=$tri_region";
                    $andWhereEta .= " AND ins.id_region=$tri_region AND ins.id=eta.id_inspection";
                    $andWhereCen .= "AND si_centre='oui' AND ins.id=eta.id_inspection AND ins.id_region=$tri_region";
                }
                // $where_pre .= "AND  id_region=$tri_region ";
                // $andWhereRegion .= "WHERE id_region=$tri_region";
                // $andWhereEta .= " AND ins.id_region=$tri_region AND ins.id=eta.id_inspection";
                // $andWhereCen .= "AND si_centre='oui' AND ins.id=eta.id_inspection AND ins.id_region=$tri_region";
            }
            if (isset($_POST['tri_prefecture']) && $_POST['tri_prefecture'] > 0) {
                $andWhereEta .= " AND vil.id_prefecture=$tri_prefecture ";
                $andWhereCen .= " AND eta.si_centre='oui' AND vil.id_prefecture=$tri_prefecture ";
                $where_pre .= "AND vil.id_prefecture=$tri_prefecture";
                $where_com .= " WHERE  id_prefecture=$tri_prefecture";
            }

            if (isset($_POST['tri_commune']) && $_POST['tri_commune'] > 0) {
                $andWhereEta .= " AND vil.id_prefecture=$tri_commune ";
                $andWhereCen .= " AND eta.si_centre='oui' AND vil.id_prefecture=$tri_commune ";
                $where_pre .= " AND id_prefecture=$tri_commune";
            }
            if (isset($_POST['tri_etablissement']) && $_POST['tri_etablissement'] > 0) {

                $andWhereEta .= " ";
            }

            // if ($cle != 'levee_anonymat' && $cle != 'impression_numeros') {
            $form = debut_cadre_enfonce('', '', '', "Critères");
            $controle_js = "onclick=\"if(tri_centre.value!=0) return true; else {alert('Veuillez choisir un centre'); return false;}\"";
            $form .= "<form method='POST' name='form_rep'>";
            $form .= " <table>";
            $form .= "<tr><td><center><select style='width:50%' name='annee' onchange='document.forms.form_rep.submit()'>" . optionsAnnee(2020, $annee) . "</select></center></td>
            <td><center><select style='width:50%' name='tri_type_session' onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'type_session', $tri_type_session) . "</select></center></td></tr>";
            if ($isEncadrant || $isAdmin || !$isInspection) {
                $form .= "<tr><td><center><select style='width:50%'   name='tri_region' onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'region', $tri_region, $WhereRegion) . "</select></center></td>
            <td><center><select style='width:50%' name='tri_prefecture' onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'prefecture', $tri_prefecture, $andWhereRegion) . "</select></center></td></tr>";
            }
            $form .= "<tr><td><center><select style='width:50%' name='tri_inspection' onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'inspection', $tri_inspection, $andWhereRegion) . "</select></center></td>
            <td><center><select style='width:50%' name='tri_commune' onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'commune', $tri_commune, $where_com) . "</select></center></td></tr>";
            $form .= "<tr><td><center><select style='width:50%' name='tri_centre' onchange='document.forms.form_rep.submit()'>" . optionsEtablissement($lien, $tri_centre, $andWhereCen, true, ',bg_ref_inspection ins ', '', 'Centre') . "</select></center></td>
            <td><center><select style='width:50%' name='tri_ville' onchange='document.forms.form_rep.submit()'>" . optionsVille($lien, 'ville', $tri_ville, $where_pre) . "</select></center></td></tr>";
            $form .= "<tr><td><center><select style='width:50%' name='tri_etablissement' onchange='document.forms.form_rep.submit()'>" . optionsEtablissement($lien, $tri_etablissement, $andWhereEta, true, ',bg_ref_inspection ins ') . "</select></center></td>
            <td><center><select style='width:50%' name='tri_langue_vivante' onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'langue_vivante', $tri_langue_vivante) . "</select></center></td></tr>";
            $form .= "<tr> <td><center><select style='width:50%' name='tri_serie' onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'serie', $tri_serie, 'WHERE id <4') . "</select></center></td>
            <td><center><select style='width:50%' name='tri_epreuve_facultative' onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'epreuve_facultative_a', $tri_epreuve_facultative, '', true, 'Epr. Fac.') . "<option value=8>Couture</option></select></center></td></tr>";
            $form .= "<tr><td><center><select style='width:50%' name='tri_sexe' onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'sexe', $tri_sexe) . "</select></center></td>
            <td style='width:50%'><center><select style='width:50%' name='tri_eps'  onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'eps', $tri_eps) . "</select></center></td></tr>";

            $form .= "</table> ";
            $form .= fin_cadre_enfonce();
            $form .= "<p><center><a href='./?exec=statistique'>Retour au Menu principal</a></center></p>";
            $form .= debut_cadre_couleur('', '', '', "Statistiques après Répartition ");
            $form .= "<table>";
            $form .= "<tr><td><input type='image' src='../plugins/images/liste-daffichage.png' width='75px' name='tache[affichage]' value='affichage' $controle_js /> <br/>Impression des listes d'affichage</td>
                    <td><input type='image' src='../plugins/images/emargement.png' width='75px' name='tache[emargement]' value='emargement'  $controle_js /> <br/>Impression des listes d'émargement</td></tr>";
            $form .= "<tr><td><input type='image' src='../plugins/images/liste_jour.png' width='75px' name='tache[emargement_jours]' value='emargement_jours' $controle_js /> <br/>Impression des listes d'émargement par jour</td>
                    <td><input type='image' src='../plugins/images/jury_centre.png' width='75px' name='tache[stats_jurys]' value='stats_jurys' /> <br/>Jurys par centre de composition</td></tr>";
            $form .= "<tr><td><input type='image' src='../plugins/images/liste_jour.png' width='75px' name='tache[liste_additive]' value='liste_additive' $controle_js /> <br/>Impression des listes Additives</td>
            <td><input type='image' src='../plugins/images/liste-daffichage.png' width='75px' name='tache[affichage_definitive]' value='affichage_definitive' /> <br/>Impression des listes d'affichage définitive</td></tr>";
            $form .= "</table>";

            $form .= "</form>";
            $form .= fin_cadre_couleur();

        } ///////////////////////////////////DEBUT APRES RESULTAT //////////////////////////////////////////////////////
        if ((isset($_REQUEST['etape']) && ($_GET['etape'] == 'resultat')) || isset($_POST['id_region'])) {

            $form = debut_cadre_enfonce('', '', '', "Critères");
            $controle_js = "onclick=\"if(tri_centre.value!=0) return true; else {alert('Veuillez choisir un centre'); return false;}\"";
            if ($isInspection) {
                $andWhereCen = " AND eta.si_centre='oui' AND ins.id_region='$id_region' AND eta.id_inspection=$id_inspection ";
                $andWhereEta = " AND eta.si_centre='non' AND ins.id_region='$id_region' AND eta.id_inspection=$id_inspection ";
                $andWhereRegion = " WHERE id_region='$id_region' AND id=$id_inspection";
                $WhereRegion = "WHERE id=$id_region";
            }
            if ($isEncadrant) {
                $andWhereCen .= " AND eta.si_centre='oui' AND ins.id=eta.id_inspection AND ins.id_region=$id_region";
                $andWhereEta .= " AND eta.si_centre='non' AND ins.id=eta.id_inspection AND ins.id_region=$id_region";
            }
            $andWhereCen .= " AND eta.si_centre='oui'";
            $andWhereEta .= " AND eta.si_centre='non'";
            if (isset($_POST['tri_centre']) && $_POST['tri_centre'] > 0) {
                $andWhereEta = "AND eta.id_centre=$tri_centre";
            }
            if (isset($_POST['tri_inspection']) && $_POST['tri_inspection'] > 0) {
                $andWhereEta .= " AND eta.id_inspection=$tri_inspection ";
                $andWhereCen .= " AND eta.si_centre='oui' AND ins.id=eta.id_inspection AND eta.id_inspection=$tri_inspection ";
            }
            if (isset($_POST['tri_region']) && $_POST['tri_region'] > 0) {
                // $andWhere .= " AND ins.id_region=$tri_region ";
                if ($isEncadrant) {$andWhereRegion .= " AND id_region=$tri_region";} else {
                    $where_pre .= "AND  id_region=$tri_region ";
                    $andWhereRegion .= "WHERE id_region=$tri_region";
                    $andWhereEta .= " AND ins.id_region=$tri_region AND ins.id=eta.id_inspection";
                    $andWhereCen .= "AND si_centre='oui' AND ins.id=eta.id_inspection AND ins.id_region=$tri_region";
                }
                // $where_pre .= "AND  id_region=$tri_region ";
                // $andWhereRegion .= "WHERE id_region=$tri_region";
                // $andWhereEta .= " AND ins.id_region=$tri_region AND ins.id=eta.id_inspection";
                // $andWhereCen .= "AND si_centre='oui' AND ins.id=eta.id_inspection AND ins.id_region=$tri_region";

            }
            if (isset($_POST['tri_prefecture']) && $_POST['tri_prefecture'] > 0) {
                $andWhereEta .= " AND vil.id_prefecture=$tri_prefecture ";
                $andWhereCen .= " AND eta.si_centre='oui' AND vil.id_prefecture=$tri_prefecture ";
                $where_pre .= "AND vil.id_prefecture=$tri_prefecture";
                $where_com .= " WHERE  id_prefecture=$tri_prefecture";
            }

            if (isset($_POST['tri_commune']) && $_POST['tri_commune'] > 0) {
                $andWhereEta .= " AND vil.id_prefecture=$tri_commune ";
                $andWhereCen .= " AND eta.si_centre='oui' AND vil.id_prefecture=$tri_commune ";
                $where_pre .= " AND id_prefecture=$tri_commune";
            }
            if (isset($_POST['tri_etablissement']) && $_POST['tri_etablissement'] > 0) {

                $andWhereEta .= " ";
            }

            // if ($cle != 'levee_anonymat' && $cle != 'impression_numeros') {
            $form = debut_cadre_enfonce('', '', '', "Critères");
            $controle_js = "onclick=\"if(tri_centre.value!=0) return true; else {alert('Veuillez choisir un centre'); return false;}\"";
            $form .= "<form method='POST' name='form_rep'>";
            $form .= " <table>";
            $form .= "<tr><td><center><select style='width:50%' name='annee' onchange='document.forms.form_rep.submit()'>" . optionsAnnee(2020, $annee) . "</select></center></td>
            <td><center><select style='width:50%' name='tri_type_session' onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'type_session', $tri_type_session) . "</select></center></td></tr>";
            if ($isEncadrant || $isAdmin || !$isInspection) {
                $form .= "<tr><td><center><select style='width:50%'   name='tri_region' onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'region', $tri_region, $WhereRegion) . "</select></center></td>
            <td><center><select style='width:50%' name='tri_prefecture' onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'prefecture', $tri_prefecture, $andWhereRegion) . "</select></center></td></tr>";
            }
            $form .= "<tr><td><center><select style='width:50%' name='tri_inspection' onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'inspection', $tri_inspection, $andWhereRegion) . "</select></center></td>
            <td><center><select style='width:50%' name='tri_commune' onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'commune', $tri_commune, $where_com) . "</select></center></td></tr>";
            $form .= "<tr><td><center><select style='width:50%' name='tri_centre' onchange='document.forms.form_rep.submit()'>" . optionsEtablissement($lien, $tri_centre, $andWhereCen, true, ',bg_ref_inspection ins ', '', 'Centre') . "</select></center></td>
            <td><center><select style='width:50%' name='tri_ville' onchange='document.forms.form_rep.submit()'>" . optionsVille($lien, 'ville', $tri_ville, $where_pre) . "</select></center></td></tr>";
            $form .= "<tr><td><center><select style='width:50%' name='tri_etablissement' onchange='document.forms.form_rep.submit()'>" . optionsEtablissement($lien, $tri_etablissement, $andWhereEta, true, ',bg_ref_inspection ins ') . "</select></center></td>
            <td><center><select style='width:50%' name='tri_langue_vivante' onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'langue_vivante', $tri_langue_vivante) . "</select></center></td></tr>";
            $form .= "<tr> <td><center><select style='width:50%' name='tri_serie' onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'serie', $tri_serie, 'WHERE id <4') . "</select></center></td>
            <td><center><select style='width:50%' name='tri_epreuve_facultative' onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'epreuve_facultative_a', $tri_epreuve_facultative, '', true, 'Epr. Fac.') . "<option value=8>Couture</option></select></center></td></tr>";
            $form .= "<tr><td><center><select style='width:50%' name='tri_sexe' onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'sexe', $tri_sexe) . "</select></center></td>
            <td style='width:50%'><center><select style='width:50%' name='tri_eps'  onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'eps', $tri_eps) . "</select></center></td></tr>";
            $form .= "<tr><td><center><select style='width:50%' name='tri_type_etablissement' onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'type_etablissement', $tri_type_etablissement) . "</select></center></td>
            <td style='text-align:left'><center>Tri suivant le nombre: <select style='width:15%' name='tri_nbreMax'>
                         <option value='5'>5</option>
                         <option value='10'>10</option>
                         <option value='50'>50</option>
                         <option value='100'>100</option>
                         <option value='200'>200</option>
                         <option value='300'>300</option>
                         <option value='400'>400</option>
                         <option value='500'>500</option>
                         <option value='5000'>+500</option>
                         </select></center></td>
            </tr>";

            $form .= "</table> ";
            $form .= fin_cadre_enfonce();
            $form .= "<p><center><a href='./?exec=statistique'>Retour au Menu principal</a></center></p>";
            $form .= debut_cadre_couleur('', '', '', "Statistiques après Résultat ");
            $form .= "<table>";
            $form .= "<tr><td><input type='image' src='../plugins/images/taux_reussite.png' width='75px' name='tache[taux_reussite]' value='taux_reussite' /> <br/>Taux de réussite</td>
                    <td><input type='image' src='../plugins/images/liste_admis.png' width='75px' name='tache[liste_admis]' value='liste_admis' $controle_js /> <br/>Liste des admis</td></tr>";
            $form .= "<tr><td><input type='image' src='../plugins/images/classement_candidats.png' width='75px' name='tache[classement_candidats]' value='classement_candidats' /> <br/>Classement des candidats</td>
                    <td><input type='image' src='../plugins/images/inscrit_par_etablissement.png' width='75px' name='tache[stats_etablissements]' value='stats_etablissements' /> <br/>Inscrits par établissement</td></tr>";
            $form .= "<tr><td><input type='image' src='../plugins/images/bar-chart.png' width='75px' name='tache[stats_inscrits]' value='stats_inscrits' /> <br/>Statistiques des inscrits</td>
                    <td><input type='image' src='../plugins/images/bar-chart.png' width='75px' name='tache[stats_presents]' value='stats_presents' /> <br/>Statistiques des présents</td></tr>";
            $form .= "<tr><td><input type='image' src='../plugins/images/bar-chart.png' width='75px' name='tache[stats_absents]' value='stats_absents' /> <br/>Statistiques des absents</td>
                    <td><input type='image' src='../plugins/images/bar-chart.png' width='75px' name='tache[stats_admis]' value='stats_admis' /> <br/>Statistiques des admis</td></tr>";
            $form .= "<tr><td><input type='image' src='../plugins/images/bar-chart.png' width='75px' name='tache[taux_etablissements]' value='taux_etablissements' /> <br/>Classement des établissements</td>
                    <td><input type='image' src='../plugins/images/bar-chart.png' width='75px' name='tache[stats_notes]' value='stats_notes' /> <br/>Statistiques sur les notes</td></tr>";
            $form .= "<tr><td><input type='image' src='../plugins/images/bar-chart.png' width='75px' name='tache[stats_notes_globales]' value='stats_notes_globales' /> <br/>Statistiques sur notes globales</td>
                    <td><input type='image' src='../plugins/images/resultats_sms_web.png' width='75px' name='tache[stats_plateformes_externes]' value='stats_plateformes_externes' /> <br/>Résultats plateformes externes</td></tr>";
            if ($isAdmin || $isDexcc) {
                $form .= "<tr><td><input type='image' src='../plugins/images/registre.png' width='75px' name='tache[registre]' value='registre' /> <br/>Régistre par Centre </a></td>
                <td><input type='image' src='../plugins/images/list_candidat_suivant_resultat.png' width='75px' name='tache[stats_candidats_normes_resultats]' value='stats_candidats_normes_resultats' /> <br/>Candidats sans resultats </a></td>
					</tr>";
            }
            $form .= "</table>";
            $form .= "</form>";
            $form .= fin_cadre_couleur();
        }
        //////////////////////////////////////////DEBUT APRES JURY /////////////////////////////////////////////////
        if ((isset($_REQUEST['etape']) && ($_GET['etape'] == 'calcul')) || isset($_POST['id_region'])) {

            $form = debut_cadre_enfonce('', '', '', "Critères");
            $controle_js = "onclick=\"if(jury.value!=0) return true; else {alert('Veuillez entrer le jury'); return false;}\"";
            if ($isInspection) {
                $andWhereCen = " AND eta.si_centre='oui' AND ins.id_region='$id_region' AND eta.id_inspection=$id_inspection ";
                $andWhereEta = " AND eta.si_centre='non' AND ins.id_region='$id_region' AND eta.id_inspection=$id_inspection ";
                $andWhereRegion = " WHERE id_region='$id_region' AND id=$id_inspection";
                $WhereRegion = "WHERE id=$id_region";
            }
            if ($isEncadrant) {
                $andWhereCen .= " AND eta.si_centre='oui' AND ins.id=eta.id_inspection AND ins.id_region=$id_region";
                $andWhereEta .= " AND eta.si_centre='non' AND ins.id=eta.id_inspection AND ins.id_region=$id_region";
            }

            // // if ($cle != 'levee_anonymat' && $cle != 'impression_numeros') {
            $form = debut_cadre_enfonce('', '', '', "Critères");
            $controle_js = "onclick=\"if(jury.value!=0) return true; else {alert('Veuillez entrer le jury'); return false;}\"";
            $form .= "<form method='POST' name='form_rep'>";
            $form .= " <table>";
            $form .= "<tr><td><center><select style='width:50%' name='annee' onchange='document.forms.form_rep.submit()'>" . optionsAnnee(2020, $annee) . "</select></center></td>
            <td><center><select style='width:50%' name='tri_type_session' onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'type_session', $tri_type_session) . "</select></center></td></tr>";
            $form .= "<tr>
            <td style='width:50%'><center>Jury : <input  style='width:42%' type='number' min=0 name='jury'  value='jury'/></center></td>
            <td><center><select style='width:50%'   name='tri_region' onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'region', $tri_region, $WhereRegion) . "</select></center></td>
            </tr>";

            $form .= "</table> ";
            $form .= fin_cadre_enfonce();
            $form .= "<p><center><a href='./?exec=statistique'>Retour au Menu principal</a></center></p>";
            $form .= debut_cadre_couleur('', '', '', "Statistiques Par Jury");
            $form .= "<table>";
            $form .= "<tr><td><input type='image' src='../plugins/images/jury.png' width='75px' name='tache[affichage_jury]' value='affichage_jury' $controle_js /> <br/>Impressions Affichage Jury</td>
                    <td><input type='image' src='../plugins/images/liste_admis.png' width='75px' name='tache[emargement_jury]' value='emargement_jury' $controle_js /> <br/>Emargement  par Jury</td></tr>";
            $form .= "<tr><td><input type='image' src='../plugins/images/classement_candidats.png' width='75px' name='tache[classement_jury]' value='classement_jury' $controle_js/> <br/>Classement des candidats  du Jury</td>
                    <td><input type='image' src='../plugins/images/inscrit_par_etablissement.png' width='75px' name='tache[liste_admi_jury]' value='liste_admi_jury' $controle_js /> <br/>Liste des admis du Jury</td></tr>";
            $form .= "<tr><td><input type='image' src='../plugins/images/bar-chart.png' width='75px' name='tache[stats_notes_jury]' value='stats_notes_jury' $controle_js /> <br/>Stat des Notes du Jury</td>
                    <td><input type='image' src='../plugins/images/jury_absent.png' width='75px' name='tache[liste_absents_jury]' value='liste_absents_jury' $controle_js /> <br/>Liste des absents du Jury</td></tr>";

            $form .= "</table>";
            $form .= fin_cadre_couleur();

            $form .= "</form>";

        }
        ///////////////////////////////////////////////////////////////////////////////////////////
        if (isset($_REQUEST['tache'])) {
            $cles = array_keys($_REQUEST['tache']);
            $tache = $cles[0];

            if ($tri_type_session != 0) {
                $andWhere .= " AND can.type_session=$tri_type_session ";
                $andLien .= "&id_type_session=$tri_type_session";
                $andTitre .= ' || ' . selectReferentiel($lien, 'type_session', $tri_type_session);
            }

            if ($tri_region != 0) {
                $andWhere .= " AND reg.id=$tri_region ";
                $andLien .= "&id_region=$tri_region";
                $andTitre .= ' || ' . selectReferentiel($lien, 'region', $tri_region);
            }
            if ($tri_prefecture != 0) {
                $andWhere .= " AND pre.id=$tri_prefecture ";
                $andLien .= "&id_prefecture=$tri_prefecture";
                $andTitre .= ' || ' . selectReferentiel($lien, 'prefecture', $tri_prefecture);
            }
            if ($tri_ville != 0) {
                $andWhere .= " AND vil.id=$tri_ville ";
                $andLien .= "&id_ville=$tri_ville";
                $andTitre .= ' || ' . selectReferentiel($lien, 'ville', $tri_ville);
            }
            if ($tri_inspection != 0) {
                $andWhere .= " AND insp.id=$tri_inspection ";
                $andLien .= "&id_inspection=$tri_inspection";
                $andTitre .= ' || ' . selectReferentiel($lien, 'inspection', $tri_inspection);
            }
            if ($tri_serie != 0) {
                $andWhere .= " AND ser.id=$tri_serie ";
                $andLien .= "&id_serie=$tri_serie";
                $andTitre .= ' || ' . selectReferentiel($lien, 'serie', $tri_serie);
            }
            if ($tri_type_etablissement != 0) {
                $andWhere .= " AND ser.id=$tri_type_etablissement ";
                $andLien .= "&id_type_etablissement=$tri_type_etablissement";
                $andTitre .= ' || ' . selectReferentiel($lien, 'type_etablissement', $tri_type_etablissement);
            }
            if ($tri_sexe != 0) {
                $andWhere .= " AND can.sexe=$tri_sexe ";
                $andLien .= "&id_sexe=$tri_sexe";
                $andTitre .= ' || ' . selectReferentiel($lien, 'sexe', $tri_sexe);
            }
            if ($tri_eps != 0) {
                $andWhere .= " AND spo.id=$tri_eps ";
                $andLien .= "&id_eps=$tri_eps";
                $andTitre .= ' || ' . selectReferentiel($lien, 'eps', $tri_eps);
            }
            if ($tri_langue_vivante != 0) {
                $andWhere .= " AND lang.id=$tri_langue_vivante ";
                $andLien .= "&id_langue_vivante=$tri_langue_vivante";
                $andTitre .= ' || ' . selectReferentiel($lien, 'langue_vivante', $tri_langue_vivante);
            }
            if ($tri_epreuve_facultative != 0) {
                $andWhere .= " AND (faca.id=$tri_epreuve_facultative OR facb.id=$tri_epreuve_facultative)";
                $andLien .= "&id_fac=$tri_epreuve_facultative";
                $andTitre .= ' || ' . selectReferentiel($lien, 'epreuve_facultative_a', $tri_epreuve_facultative);
            }
            if ($tri_centre != 0) {
                $andWhere .= " AND cent.id=$tri_centre ";
                $andLien .= "&id_centre=$tri_centre";
                $andTitre .= ' || ' . selectReferentiel($lien, 'etablissement', $tri_centre);
            }
            if ($tri_etablissement != 0) {
                $andWhere .= " AND eta.id=$tri_etablissement ";
                $andLien .= "&id_etablissement=$tri_etablissement";
                $andTitre .= ' || ' . selectReferentiel($lien, 'etablissement', $tri_etablissement);
            }
            if ($jury != 0) {
                $andWhere .= " AND rep.jury=$jury ";
                $andLien .= "&jury=$jury";
                $andTitre .= ' || jury ' . $jury;
            }

            switch ($tache) {
                case 'stats_inscrits':
                case 'stats_presents':
                case 'stats_absents':
                case 'stats_admis':
                    if ($tache == 'stats_presents') {
                        $andWhere2 = " AND res.delib1!='Absent' AND res.delib1!='Abandon' ";
                        $title = 'STATISTIQUES DES PRESENTS';
                    } elseif ($tache == 'stats_absents') {
                        $andWhere2 = " AND (res.delib1='Absent' OR res.delib1='Abandon') ";
                        $title = 'STATISTIQUES DES ABSENTS';
                    } elseif ($tache == 'stats_admis') {
                        $andWhere2 = " AND (res.delib1='Passable' OR res.delib1='Abien' OR res.delib1='Bien' OR res.delib1='TBien' OR res.delib1='Oral') ";
                        $title = 'STATISTIQUES DES ADMIS';
                    } else {
                        $andWhere2 = '';
                        $title = 'STATISTIQUES DES INSCRITS';
                    }

                    $sql1 = "SELECT reg.id as id_region, pre.id as id_prefecture, cent.id as id_centre, cent.etablissement as centre, ser.id as id_serie, count(*) as nbre_cand
						FROM bg_ref_region reg,
						bg_ref_prefecture pre,
						bg_ref_ville vil,
						bg_ref_etablissement cent,
						bg_repartition rep,
						bg_ref_serie ser,
						bg_ref_inspection insp,
						bg_ref_etablissement eta,
						bg_ref_eps spo,
						bg_candidats can
						LEFT JOIN bg_ref_langue_vivante lang ON lang.id=can.lv2
						LEFT JOIN bg_ref_epreuve_facultative_a faca ON faca.id=can.efa
						LEFT JOIN bg_ref_epreuve_facultative_b facb ON facb.id=can.efb
						LEFT JOIN bg_resultats res ON (res.num_table=can.num_table AND res.annee=$annee)
						WHERE can.annee=$annee AND rep.annee=$annee AND can.serie=ser.id AND can.num_table=rep.num_table
						AND cent.id_ville=vil.id AND vil.id_prefecture=pre.id AND pre.id_region=reg.id AND rep.id_centre=cent.id
						AND insp.id=eta.id_inspection AND eta.id=can.etablissement AND spo.id=can.eps
						$andWhere $andWhere2
						GROUP BY cent.id, ser.id
						ORDER BY reg.region, cent.etablissement, ser.serie ";

                    $result1 = bg_query($lien, $sql1, __FILE__, __LINE__);
                    $tab = array('id_centre', 'centre', 'id_serie', 'nbre_cand');
                    while ($row1 = mysqli_fetch_array($result1)) {
                        foreach ($tab as $val) {
                            $$val = $row1[$val];
                        }

                        $tCapacite[$id_centre][$id_serie] = $nbre_cand;
                        $tCentres[$id_centre] = $centre;
                        $tTotaux[$id_centre] += $nbre_cand;
                        $tTotSerie[$id_serie] += $nbre_cand;
                        $total_general += $nbre_cand;
                    }
                    $sql2 = "SELECT id as id_serie, serie FROM bg_ref_serie ORDER BY serie";
                    $result2 = bg_query($lien, $sql2, __FILE__, __LINE__);
                    $table = debut_cadre_couleur('', '', '', "Candidats par centre et par série");
                    $table .= "<table>";
                    $table .= "<th>Centre</th>";
                    $cols['centre'] = 'Centre';
                    while ($row2 = mysqli_fetch_array($result2)) {
                        $id_serie = $row2['id_serie'];
                        $serie = $row2['serie'];
                        $tSeries[$id_serie] = $row2['serie'];
                        $table .= "<th>$serie</th>";
                        $cols[$id_serie] = $serie;
                    }
                    $table .= "<th>Total</th>";
                    $cols['total'] = 'Total';
                    foreach ($tCentres as $id_centre => $centre) {
                        $table .= "<tr><td><b>$centre</b></td>";
                        foreach ($tSeries as $id_serie => $serie) {
                            $table .= "<td>" . $tCapacite[$id_centre][$id_serie] . "</td>";
                        }
                        $table .= "<td>" . $tTotaux[$id_centre] . "</td></tr>";
                    }

                    $table .= "<tr><td><b>TOTAL</b></td>";
                    foreach ($tSeries as $id_serie => $serie) {
                        $table .= "<td>" . $tTotSerie[$id_serie] . "</td>";
                    }
                    $table .= "<td>" . $total_general . "</td></tr>";

                    $table .= "</table>";
                    $table .= fin_cadre_couleur();

                    $i = 0;
                    foreach ($tCentres as $id_centre => $centre) {
                        $data[$i]['centre'] = $centre;
                        foreach ($tSeries as $id_serie => $serie) {
                            $data[$i][$id_serie] = $tCapacite[$id_centre][$id_serie];
                        }
                        $data[$i]['total'] = $tTotaux[$id_centre];
                        $i++;
                    }

                    $data[$i]['centre'] = 'TOTAL';
                    foreach ($tSeries as $id_serie => $serie) {
                        $data[$i][$id_serie] = $tTotSerie[$id_serie];
                    }
                    $data[$i]['total'] = $total_general;

                    $nomFichier = 'Stats_centres';
                    $_SESSION['annee'][$nomFichier] = $annee;
                    $_SESSION['data'][$nomFichier] = $data;
                    $_SESSION['title'][$nomFichier] = $title;
                    $_SESSION['andTitre'][$nomFichier] = $andTitre;
                    $_SESSION['cols'][$nomFichier] = $cols;
                    $_SESSION['options'][$nomFichier] = $PDF_A4_PAYSAGE;
                    echo debut_cadre_enfonce(), "<center><a href='../plugins/fonctions/inc-pdf-table.php?pdf=$nomFichier'> <b>IMPRIMER EN VERSION PDF</b></a></center>", fin_cadre_enfonce();
                    echo $table;
                    break;

                case 'stats_inscription':
                    if ($tache == 'stats_inscription') {$orderBy = 'ser.serie,cent.etablissement';
                        $andLien .= "&tache=$tache";} else { $orderBy = 'rep.numero';
                        $andLien .= "&tache=$tache";}
                    $sql1 = "SELECT reg.id as id_region, pre.id as id_prefecture, cent.id as id_centre, cent.etablissement as centre, ser.id as id_serie, count(*) as nbre_cand
						FROM bg_ref_region reg,
						bg_ref_prefecture pre,
						bg_ref_ville vil,
						bg_ref_etablissement cent,
						bg_ref_serie ser,
						bg_ref_inspection insp,
						bg_ref_etablissement eta,
						bg_ref_eps spo,
						bg_candidats can
						LEFT JOIN bg_ref_langue_vivante lang ON lang.id=can.lv2
						LEFT JOIN bg_ref_epreuve_facultative_a faca ON faca.id=can.efa
						LEFT JOIN bg_ref_epreuve_facultative_b facb ON facb.id=can.efb
						WHERE can.annee=$annee AND can.serie=ser.id
						AND cent.id_ville=vil.id AND vil.id_prefecture=pre.id AND pre.id_region=reg.id AND can.centre=cent.id
						AND insp.id=eta.id_inspection AND eta.id=can.etablissement AND spo.id=can.eps
						$andWhere
						GROUP BY cent.id, ser.id
						ORDER BY reg.region, cent.etablissement, ser.serie ";

                    $result1 = bg_query($lien, $sql1, __FILE__, __LINE__);
                    $tab = array('id_centre', 'centre', 'id_serie', 'nbre_cand');
                    while ($row1 = mysqli_fetch_array($result1)) {
                        foreach ($tab as $val) {
                            $$val = $row1[$val];
                        }

                        $tCapacite[$id_centre][$id_serie] = $nbre_cand;
                        $tCentres[$id_centre] = $centre;
                        $tTotaux[$id_centre] += $nbre_cand;
                        $tTotSerie[$id_serie] += $nbre_cand;
                        $total_general += $nbre_cand;
                    }
                    $sql2 = "SELECT id as id_serie, serie FROM bg_ref_serie ORDER BY serie";
                    $result2 = bg_query($lien, $sql2, __FILE__, __LINE__);
                    $table = debut_cadre_couleur('', '', '', "Candidats par centre et par série ");
                    //  $table .= "<p><center>  $andTitre</center></p>";

                    $table .= "<table>";
                    $table .= "<th>Centre</th>";
                    $cols['centre'] = 'Centre';
                    while ($row2 = mysqli_fetch_array($result2)) {
                        $id_serie = $row2['id_serie'];
                        $serie = $row2['serie'];
                        $tSeries[$id_serie] = $row2['serie'];
                        $table .= "<th>$serie</th>";
                        $cols[$id_serie] = $serie;
                    }
                    $table .= "<th>Total</th>";
                    foreach ($tCentres as $id_centre => $centre) {
                        $table .= "<tr><td><b>$centre</b></td>";
                        foreach ($tSeries as $id_serie => $serie) {
                            $table .= "<td> " . $tCapacite[$id_centre][$id_serie] . " </td>";
                        }
                        $table .= "<td> " . $tTotaux[$id_centre] . " </td></tr>";
                    }

                    $table .= "<tr><td><b>TOTAL</b></td>";
                    $cols['total'] = 'Total';
                    foreach ($tSeries as $id_serie => $serie) {
                        $table .= "<td><b>" . $tTotSerie[$id_serie] . "</b></td>";
                    }
                    $table .= "<td><b>" . $total_general . "</b></td></tr>";

                    $table .= "</table>";
                    $table .= fin_cadre_couleur();

                    $i = 0;
                    foreach ($tCentres as $id_centre => $centre) {
                        $data[$i]['centre'] = $centre;
                        foreach ($tSeries as $id_serie => $serie) {
                            $data[$i][$id_serie] = $tCapacite[$id_centre][$id_serie];
                        }
                        $data[$i]['total'] = $tTotaux[$id_centre];
                        $i++;
                    }

                    $data[$i]['centre'] = 'TOTAL';
                    foreach ($tSeries as $id_serie => $serie) {
                        $data[$i][$id_serie] = $tTotSerie[$id_serie];
                    }
                    $data[$i]['total'] = $total_general;

                    // $nomFichier = 'Stats_centres';
                    // $_SESSION['annee'][$nomFichier] = $annee;
                    // $_SESSION['data'][$nomFichier] = $data;
                    // $_SESSION['title'][$nomFichier] = $title;
                    // $_SESSION['andTitre'][$nomFichier] = $andTitre;
                    // $_SESSION['cols'][$nomFichier] = $cols;
                    // $_SESSION['options'][$nomFichier] = $PDF_A4_PAYSAGE;

                    // echo debut_cadre_enfonce(), "<center><a href='../plugins/fonctions/inc-pdf-table.php?pdf=$nomFichier'> <b>IMPRIMER EN VERSION PDF</b></a></center>", fin_cadre_enfonce();
                    echo debut_cadre_enfonce(), "<center><a href='../plugins/fonctions/inc-pdf-stats_etablissements.php?pdf=$nomFichier&annee=$annee$andLien'> <b>IMPRIMER EN VERSION PDF</b></a></center>", fin_cadre_enfonce();

                    echo $table;
                    break;

                case 'stats_etablissements':
                case 'stats_inscription_etablissements':
                    if ($tache == 'stats_inscription_etablissements') {$orderBy = 'ser.serie,eta.etablissement';
                        $andLien .= "&tache=$tache";} else { $orderBy = 'rep.numero';
                        $andLien .= "&tache=$tache";}
                    $sql1 = "SELECT reg.id as id_region, pre.id as id_prefecture, eta.id as id_etablissement, eta.etablissement, ser.id as id_serie, count(*) as nbre_cand
						FROM bg_ref_region reg,
						bg_ref_prefecture pre,
                        bg_ref_etablissement cent,
						bg_ref_ville vil,
						bg_ref_serie ser,
						bg_ref_inspection insp,
						bg_ref_etablissement eta,
						bg_ref_eps spo,
						bg_candidats can
						LEFT JOIN bg_ref_langue_vivante lang ON lang.id=can.lv2
						LEFT JOIN bg_ref_epreuve_facultative_a faca ON faca.id=can.efa
						LEFT JOIN bg_ref_epreuve_facultative_b facb ON facb.id=can.efb
						LEFT JOIN bg_repartition rep ON (rep.num_table=can.num_table AND rep.annee=$annee)
						WHERE can.annee=$annee AND can.serie=ser.id AND eta.id_ville=vil.id
						AND vil.id_prefecture=pre.id AND pre.id_region=reg.id AND can.etablissement=eta.id
						AND insp.id=eta.id_inspection AND spo.id=can.eps AND can.centre=cent.id
						$andWhere
						GROUP BY eta.id, ser.id
						ORDER BY reg.region, eta.etablissement, ser.serie ";
                    $result1 = bg_query($lien, $sql1, __FILE__, __LINE__);
                    $tab = array('id_etablissement', 'etablissement', 'id_serie', 'nbre_cand');
                    while ($row1 = mysqli_fetch_array($result1)) {
                        foreach ($tab as $val) {
                            $$val = $row1[$val];
                        }

                        $tCapacite[$id_etablissement][$id_serie] = $nbre_cand;
                        $tEta[$id_etablissement] = $etablissement;
                        $tTotaux[$id_etablissement] += $nbre_cand;
                        $tTotSerie[$id_serie] += $nbre_cand;
                        $total_general += $nbre_cand;
                    }
                    $sql2 = "SELECT id as id_serie, serie FROM bg_ref_serie ORDER BY serie";
                    $result2 = bg_query($lien, $sql2, __FILE__, __LINE__);
                    $table = debut_cadre_couleur('', '', '', "Candidats par établissement et par série");
                    $table .= "<table>";
                    $table .= "<th>Etablissements</th>";
                    $cols['etablissement'] = 'ETABLISSEMENTS';
                    while ($row2 = mysqli_fetch_array($result2)) {
                        $id_serie = $row2['id_serie'];
                        $serie = $row2['serie'];
                        $tSeries[$id_serie] = $row2['serie'];
                        $table .= "<th>$serie</th>";
                        $cols[$id_serie] = $serie;
                    }
                    $table .= "<th>Total</th>";
                    foreach ($tEta as $id_etablissement => $etablissement) {
                        $table .= "<tr><td><b>$etablissement</b></td>";
                        foreach ($tSeries as $id_serie => $serie) {
                            $table .= "<td>" . $tCapacite[$id_etablissement][$id_serie] . "</td>";
                        }
                        $table .= "<td>" . $tTotaux[$id_etablissement] . "</td></tr>";
                    }

                    $table .= "<tr><td><b>TOTAL</b></td>";
                    foreach ($tSeries as $id_serie => $serie) {
                        $table .= "<td>" . $tTotSerie[$id_serie] . "</td>";
                    }
                    $table .= "<td>" . $total_general . "</td></tr>";

                    $table .= "</table>";
                    $table .= fin_cadre_couleur();

                    $cols['total'] = 'TOTAL';
                    $i = 0;
                    foreach ($tEta as $id_etablissement => $etablissement) {
                        $data[$i]['etablissement'] = $etablissement;
                        foreach ($tSeries as $id_serie => $serie) {
                            $data[$i][$id_serie] = $tCapacite[$id_etablissement][$id_serie];
                        }
                        $data[$i]['total'] = $tTotaux[$id_etablissement];
                        $i++;
                    }
                    $data[$i]['etablissement'] = 'TOTAL';
                    foreach ($tSeries as $id_serie => $serie) {
                        $data[$i][$id_serie] = $tTotSerie[$id_serie];
                    }
                    $data[$i]['total'] = $total_general;

                    $nomFichier = 'Stats_centres';
                    $_SESSION['annee'][$nomFichier] = $annee;
                    $_SESSION['data'][$nomFichier] = $data;
                    $_SESSION['title'][$nomFichier] = "CANDIDATS INSCRITS PAR ETABLISSEMENTS ET PAR SERIE";
                    $_SESSION['andTitre'][$nomFichier] = $andTitre;
                    $_SESSION['cols'][$nomFichier] = $cols;
                    $_SESSION['options'][$nomFichier] = $PDF_A4_PAYSAGE;

                    //  echo debut_cadre_enfonce(), "<center><a href='../plugins/fonctions/inc-pdf-table.php?pdf=$nomFichier'> <b>IMPRIMER EN VERSION PDF</b></a></center>", fin_cadre_enfonce();
                    echo debut_cadre_enfonce(), "<center><a href='../plugins/fonctions/inc-pdf-stats_etablissements.php?pdf=$nomFichier&annee=$annee$andLien'> <b>IMPRIMER EN VERSION PDF</b></a></center>", fin_cadre_enfonce();

                    echo $table;
                    break;

                case 'stats_jurys':
                    $sql = "SELECT reg.region, cent.id as id_centre, ser.serie, rep.jury, cent.etablissement as centre,
						min(rep.numero) as mini_numero, max(rep.numero) as maxi_numero, count(*) as nbre
						FROM bg_repartition rep, bg_ref_region reg, bg_ref_prefecture pre, bg_ref_ville vil,
						bg_ref_etablissement eta, bg_ref_etablissement cent,
						bg_ref_inspection insp, bg_ref_serie ser, bg_ref_eps spo, bg_candidats can
						LEFT JOIN bg_ref_langue_vivante lang ON can.lv2=lang.id
						LEFT JOIN bg_ref_epreuve_facultative_a ef1 ON can.efa=ef1.id
						LEFT JOIN bg_ref_epreuve_facultative_b ef2 ON can.efb=ef2.id
						WHERE can.annee=$annee AND rep.annee=$annee AND can.num_table=rep.num_table AND rep.id_centre=cent.id
						AND cent.id_ville=vil.id AND vil.id_prefecture=pre.id AND pre.id_region=reg.id
						AND eta.id_inspection=insp.id AND can.serie=ser.id AND can.eps=spo.id
						AND eta.id=can.etablissement
						$andWhere
						GROUP BY reg.region, cent.id, rep.jury, ser.serie
						ORDER BY rep.jury ";
                    $result = bg_query($lien, $sql, __FILE__, __LINE__);
                    $tab = array('region', 'id_centre', 'serie', 'jury', 'nbre', 'mini_numero', 'maxi_numero', 'centre');
                    while ($row = mysqli_fetch_array($result)) {
                        foreach ($tab as $val) {
                            $$val = $row[$val];
                        }

                        $tJurys[] = $jury;
                        $tabJurys[$jury]['region'] = $region;
                        $tabJurys[$jury]['centre'] = $centre;
                        $tabJurys[$jury]['serie'] = $serie;
                        $tabJurys[$jury]['mini'] = $mini_numero;
                        $tabJurys[$jury]['maxi'] = $maxi_numero;
                        $tabJurys[$jury]['nbre'] = $nbre;
                    }
                    $table = debut_cadre_enfonce('', '', '', "BAC1 $annee - Point des Jurys");
                    $table .= "<table>";
                    $table .= "<th>REGION</th><th>CENTRE</th><th>N° DE JURY</th><th>SERIE</th><th>EFFECTIF</th><th>1ER NUMERO</th><th>DERNIER NUMERO</th>";

                    foreach ($tJurys as $jury) {
                        $table .= "<tr>";
                        $table .= "<td>" . $tabJurys[$jury]['region'] . "</td>";
                        $table .= "<td>" . $tabJurys[$jury]['centre'] . "</td>";
                        $table .= "<td>$jury</td>";
                        $table .= "<td>" . $tabJurys[$jury]['serie'] . "</td>";
                        $table .= "<td>" . $tabJurys[$jury]['nbre'] . "</td>";
                        $table .= "<td>" . $tabJurys[$jury]['mini'] . "</td>";
                        $table .= "<td>" . $tabJurys[$jury]['maxi'] . "</td>";
                        $table .= "</tr>";
                    }

                    $table .= "</table>";
                    $table .= fin_cadre_enfonce();
                    echo debut_cadre_enfonce(), "<center><a href='../plugins/fonctions/inc-pdf-point_jurys.php?&annee=$annee$andLien'> <b>IMPRIMER EN VERSION PDF</b></a></center>", fin_cadre_couleur();
                    echo $table;
                    break;

                case 'affichage':
                case 'affichage_definitive':
                case 'liste_additive':
                case 'emargement':
                case 'emargement_jours':
                    if ($tache == 'affichage') {$orderBy = 'ser.serie, rep.numero, can.nom, can.prenoms';
                        $andLien .= "&tache=$tache";
                        $andAdd = " AND can.additive=0";} elseif ($tache == 'affichage_definitive') {$orderBy = 'ser.serie, rep.numero, can.nom, can.prenoms';
                        $andLien .= "&tache=$tache";
                        $andAdd = " ";} elseif ($tache == 'liste_additive') {
                        $orderBy = 'ser.serie, rep.numero, can.nom, can.prenoms';
                        $andLien .= "&tache=$tache";
                        $andAdd = " AND can.additive=1";
                    } else { $orderBy = 'rep.numero';
                        $andLien .= "&tache=$tache";
                        $andAdd = "";}
                    //print_r($_POST);
                    $sql = "SELECT can.num_table, can.nom, can.prenoms, can.ddn, can.ldn, can.pdn, eta.etablissement, cent.etablissement as centre,
						ser.serie, rep.jury, lang.langue_vivante, ef1.epreuve_facultative_a, ef2.epreuve_facultative_b, spo.eps
						FROM bg_repartition rep, bg_ref_region reg, bg_ref_prefecture pre, bg_ref_ville vil,
						bg_ref_etablissement eta, bg_ref_etablissement cent,
						bg_ref_inspection insp, bg_ref_serie ser, bg_ref_eps spo, bg_candidats can
						LEFT JOIN bg_ref_langue_vivante lang ON can.lv2=lang.id
						LEFT JOIN bg_ref_epreuve_facultative_a ef1 ON can.efa=ef1.id
						LEFT JOIN bg_ref_epreuve_facultative_b ef2 ON can.efb=ef2.id
						WHERE can.annee=$annee AND rep.annee=$annee AND can.num_table=rep.num_table AND rep.id_centre=cent.id
						AND can.etablissement=eta.id AND eta.id_ville=vil.id AND vil.id_prefecture=pre.id AND pre.id_region=reg.id
						AND eta.id_inspection=insp.id AND can.serie=ser.id AND can.eps=spo.id   AND can.statu=1 $andAdd
						$andWhere
						ORDER BY $orderBy ";
                    //var_dump($andAdd);
                    $result = bg_query($lien, $sql, __FILE__, __LINE__);
                    $tab = array('num_table', 'nom', 'prenoms', 'ddn', 'ldn', 'pdn', 'pays', 'etablissement', 'centre', 'serie', 'jury', 'langue_vivante',
                        'epreuve_facultative_a', 'epreuve_facultative_b', 'eps');
                    $i = 1;
                    while ($row = mysqli_fetch_array($result)) {
                        foreach ($tab as $val) {
                            $$val = $row[$val];
                        }

                        $tabNum[] = $num_table;
                        $tabCand[$num_table]['num'] = $i;
                        $tabCand[$num_table]['noms'] = $nom . ' ' . $prenoms;
                        $tabCand[$num_table]['dldn'] = afficher_date($ddn) . ' ' . $ldn;
                        $tabCand[$num_table]['serie'] = $serie;
                        $tabCand[$num_table]['jury'] = $jury;
                        $tabCand[$num_table]['lv'] = $langue_vivante;
                        $tabCand[$num_table]['eps'] = $eps;
                        $tabCand[$num_table]['ef1'] = $epreuve_facultative_a;
                        $tabCand[$num_table]['ef2'] = $epreuve_facultative_b;
                        $tabCand[$num_table]['etablissement'] = $etablissement;
                        $i++;
                    }
                    $table = debut_cadre_enfonce('', '', '', "BAC1 $annee - Liste d'$tache - Centre $centre");
                    $table .= "<table>";
                    $table .= "<th>N°</th><th>N° DE TABLE</th><th>NOMS ET PRENOMS</th><th>DATE ET LIEU DE NAISSANCE</th><th>SERIE</th><th>JURY</th><th>LANGUE VIVANTE</th><th>EPS</th><th>EF1</th><th>EF2</th><th>ETABLISSEMENT</th>";

                    foreach ($tabNum as $num_table) {
                        $table .= "<tr>";
                        $table .= "<td>" . $tabCand[$num_table]['num'] . "</td>";
                        $table .= "<td>" . $num_table . "</td>";
                        $table .= "<td>" . $tabCand[$num_table]['noms'] . "</td>";
                        $table .= "<td>" . $tabCand[$num_table]['dldn'] . "</td>";
                        $table .= "<td>" . $tabCand[$num_table]['serie'] . "</td>";
                        $table .= "<td>" . $tabCand[$num_table]['jury'] . "</td>";
                        $table .= "<td>" . $tabCand[$num_table]['lv'] . "</td>";
                        $table .= "<td>" . $tabCand[$num_table]['eps'] . "</td>";
                        $table .= "<td>" . $tabCand[$num_table]['ef1'] . "</td>";
                        $table .= "<td>" . $tabCand[$num_table]['ef2'] . "</td>";
                        $table .= "<td>" . $tabCand[$num_table]['etablissement'] . "</td>";
                        $table .= "</tr>";
                    }

                    $table .= "</table>";
                    $table .= fin_cadre_enfonce();
                    echo debut_cadre_enfonce(), "<center><a href='../plugins/fonctions/inc-pdf-affichage_emargement.php?id_centre=$id_centre&annee=$annee$andLien'> <b>IMPRIMER EN VERSION PDF</b></a></center><hr/><center><a href='../plugins/fonctions/csvfile/inc-csv-affichage_emargement.php?id_centre=$id_centre&annee=$annee$andLien'> <b>IMPRIMER EN CSV</b></a></center>", fin_cadre_couleur();
                    echo $table;
                    break;
                case 'emargement_jury':
                case 'affichage_jury':
                    if (($tache == 'affichage_jury') && ($tache == 'emargement_jury')) {$orderBy = 'ser.serie, rep.numero, can.nom, can.prenoms';
                        $andLien .= "&tache=$tache";} else { $orderBy = 'rep.numero';
                        $andLien .= "&tache=$tache";}

                    //print_r($_POST);
                    $sql = "SELECT can.num_table, can.nom, can.prenoms, can.ddn, can.ldn, can.pdn, eta.etablissement, cent.etablissement as centre,
                            ser.serie, rep.jury, lang.langue_vivante, ef1.epreuve_facultative_a, ef2.epreuve_facultative_b, spo.eps
                            FROM bg_repartition rep, bg_ref_region reg, bg_ref_prefecture pre, bg_ref_ville vil,
                            bg_ref_etablissement eta, bg_ref_etablissement cent,
                            bg_ref_inspection insp, bg_ref_serie ser, bg_ref_eps spo, bg_candidats can
                            LEFT JOIN bg_ref_langue_vivante lang ON can.lv2=lang.id
                            LEFT JOIN bg_ref_epreuve_facultative_a ef1 ON can.efa=ef1.id
                            LEFT JOIN bg_ref_epreuve_facultative_b ef2 ON can.efb=ef2.id
                            WHERE can.annee=$annee AND rep.annee=$annee AND can.num_table=rep.num_table AND rep.id_centre=cent.id
                            AND can.etablissement=eta.id AND eta.id_ville=vil.id AND vil.id_prefecture=pre.id AND pre.id_region=reg.id
                            AND eta.id_inspection=insp.id AND can.serie=ser.id AND can.eps=spo.id AND can.statu=1
                            $andWhere
                            ORDER BY $orderBy ";
                    $result = bg_query($lien, $sql, __FILE__, __LINE__);
                    $tab = array('num_table', 'nom', 'prenoms', 'ddn', 'ldn', 'pdn', 'etablissement', 'centre', 'serie', 'jury', 'langue_vivante',
                        'epreuve_facultative_a', 'epreuve_facultative_b', 'eps');
                    $i = 1;
                    while ($row = mysqli_fetch_array($result)) {
                        foreach ($tab as $val) {
                            $$val = $row[$val];
                        }

                        $tabNum[] = $num_table;
                        $tabCand[$num_table]['num'] = $i;
                        $tabCand[$num_table]['noms'] = $nom . ' ' . $prenoms;
                        $tabCand[$num_table]['dldn'] = afficher_date($ddn) . ' ' . $ldn;
                        $tabCand[$num_table]['serie'] = $serie;
                        $tabCand[$num_table]['jury'] = $jury;
                        $tabCand[$num_table]['lv'] = $langue_vivante;
                        $tabCand[$num_table]['eps'] = $eps;
                        $tabCand[$num_table]['ef1'] = $epreuve_facultative_a;
                        $tabCand[$num_table]['ef2'] = $epreuve_facultative_b;
                        $tabCand[$num_table]['etablissement'] = $etablissement;
                        $i++;
                    }
                    $table = debut_cadre_enfonce('', '', '', "BAC1 $annee - Liste d'$tache - Centre $centre");
                    $table .= "<table>";
                    $table .= "<th>N°</th><th>N° DE TABLE</th><th>NOMS ET PRENOMS</th><th>DATE ET LIEU DE NAISSANCE</th><th>SERIE</th><th>JURY</th><th>LANGUE VIVANTE</th><th>EPS</th><th>EF1</th><th>EF2</th><th>ETABLISSEMENT</th>";

                    foreach ($tabNum as $num_table) {
                        $table .= "<tr>";
                        $table .= "<td>" . $tabCand[$num_table]['num'] . "</td>";
                        $table .= "<td>" . $num_table . "</td>";
                        $table .= "<td>" . $tabCand[$num_table]['noms'] . "</td>";
                        $table .= "<td>" . $tabCand[$num_table]['dldn'] . "</td>";
                        $table .= "<td>" . $tabCand[$num_table]['serie'] . "</td>";
                        $table .= "<td>" . $tabCand[$num_table]['jury'] . "</td>";
                        $table .= "<td>" . $tabCand[$num_table]['lv'] . "</td>";
                        $table .= "<td>" . $tabCand[$num_table]['eps'] . "</td>";
                        $table .= "<td>" . $tabCand[$num_table]['ef1'] . "</td>";
                        $table .= "<td>" . $tabCand[$num_table]['ef2'] . "</td>";
                        $table .= "<td>" . $tabCand[$num_table]['etablissement'] . "</td>";
                        $table .= "</tr>";
                    }

                    $table .= "</table>";
                    $table .= fin_cadre_enfonce();
                    echo debut_cadre_enfonce(), "<center><a href='../plugins/fonctions/inc-pdf-affichage_emargement_jury.php?id_centre=$id_centre&annee=$annee$andLien'> <b>IMPRIMER EN VERSION PDF</b></a></center><center><a href='../plugins/fonctions/csvfile/inc-csv-affichage_emargement.php?id_centre=$id_centre&annee=$annee$andLien'> <b>IMPRIMER EN CSV</b></a></center>", fin_cadre_couleur();
                    echo $table;
                    break;

                case 'liste_admis':
                case 'liste_absents_jury':
                case 'liste_admi_jury':
                case 'classement_jury':
                    if ($tache == 'classement_jury') {
                        $andWhere .= " AND (delib1='Passable' OR delib1='Abien' OR delib1='Bien' OR delib1='TBien') ";
                        $limit = " LIMIT 0, 200 ";
                        $orderBy = 'moyenne DESC';
                    } else if ($tache == 'liste_admi_jury') {
                        $andWhere .= " AND ((delib1='Passable' OR delib1='Abien' OR delib1='Bien' OR delib1='TBien') OR (delib1='Oral' AND delib2='Passable'))";
                        $orderBy = 'can.nom, can.prenoms';
                    } else if ($tache == 'liste_absents_jury') {
                        $andWhere .= " AND (res.delib1='Absent' OR res.delib1='Abandon') ";
                        $orderBy = 'can.nom, can.prenoms';
                    } else {
                        $andWhere .= " AND ((delib1='Passable' OR delib1='Abien' OR delib1='Bien' OR delib1='TBien') OR (delib1='Oral' AND delib2='Passable'))";
                        $orderBy = 'can.nom, can.prenoms';
                    }
                    $sql = "SELECT can.num_table, can.nom, can.prenoms, can.ddn, can.ldn, can.pdn, eta.etablissement, cent.etablissement as centre,
						ser.serie, rep.jury, lang.langue_vivante, ef1.epreuve_facultative_a, ef2.epreuve_facultative_b, spo.eps,
						res.moyenne, res.moyenne2, res.delib1, res.delib2,pays
						FROM bg_repartition rep, bg_ref_region reg, bg_ref_prefecture pre, bg_ref_ville vil,
						bg_ref_etablissement eta, bg_ref_etablissement cent,bg_ref_pays pay,
						bg_ref_inspection insp, bg_ref_serie ser, bg_ref_eps spo, bg_resultats res, bg_candidats can
						LEFT JOIN bg_ref_langue_vivante lang ON can.lv2=lang.id
						LEFT JOIN bg_ref_epreuve_facultative_a ef1 ON can.efa=ef1.id
						LEFT JOIN bg_ref_epreuve_facultative_b ef2 ON can.efb=ef2.id
						WHERE can.annee=$annee AND rep.annee=$annee AND res.annee=$annee
						AND can.num_table=rep.num_table AND rep.id_centre=cent.id AND res.num_table=can.num_table
						AND can.etablissement=eta.id AND eta.id_ville=vil.id AND vil.id_prefecture=pre.id AND pre.id_region=reg.id
						AND eta.id_inspection=insp.id AND can.serie=ser.id AND can.eps=spo.id AND can.pdn=pay.id

						$andWhere
						ORDER BY $orderBy
						$limit ";
                    $result = bg_query($lien, $sql, __FILE__, __LINE__);
                    $tab = array('num_table', 'nom', 'prenoms', 'ddn', 'ldn', 'pdn', 'pays', 'etablissement', 'centre', 'serie', 'jury', 'moyenne',
                        'moyenne2', 'delib1', 'delib2');
                    $i = 1;
                    while ($row = mysqli_fetch_array($result)) {
                        foreach ($tab as $val) {
                            $$val = $row[$val];
                        }

                        if ($delib1 == 'Oral') {$delib1 = $delib2;
                            $moyenne = $moyenne2;}
                        $tabNum[] = $num_table;
                        $tabCand[$num_table]['num'] = $i;
                        $tabCand[$num_table]['noms'] = $nom . ' ' . $prenoms;
                        $tabCand[$num_table]['dldn'] = afficher_date($ddn) . ' ' . $ldn . "($pays)";
                        $tabCand[$num_table]['serie'] = $serie;
                        $tabCand[$num_table]['jury'] = $jury;
                        if ($tache != 'liste_absents_jury') {
                            $tabCand[$num_table]['moyenne'] = $moyenne;
                        }
                        $tabCand[$num_table]['delib'] = $delib1;
                        $tabCand[$num_table]['etablissement'] = $etablissement;
                        $i++;
                    }
                    if ($tache == 'classement_jury') {
                        $titre = "BAC1 $annee - Classement des candidats du Jury $jury";
                    } else if ($tache == 'liste_absents_jury') {
                        $titre = "BAC1 $annee - Liste des absents - Jury $jury";
                    } else {
                        $titre = "BAC1 $annee - Liste des admis - Centre $centre";
                    }

                    $table = debut_cadre_enfonce('', '', '', "$titre");
                    $table .= "<table>";
                    $table .= "<th>N°</th><th>N° DE TABLE</th><th>NOMS ET PRENOMS</th><th>DATE ET LIEU DE NAISSANCE</th><th>SERIE</th><th>JURY</th><th>MOYENNE</th><th>MENTION</th><th>ETABLISSEMENT</th>";

                    foreach ($tabNum as $num_table) {
                        $table .= "<tr>";
                        $table .= "<td>" . $tabCand[$num_table]['num'] . "</td>";
                        $table .= "<td>" . $num_table . "</td>";
                        $table .= "<td>" . $tabCand[$num_table]['noms'] . "</td>";
                        $table .= "<td>" . $tabCand[$num_table]['dldn'] . "</td>";
                        $table .= "<td>" . $tabCand[$num_table]['serie'] . "</td>";
                        $table .= "<td>" . $tabCand[$num_table]['jury'] . "</td>";
                        if ($tache != 'liste_absents_jury') {
                            $table .= "<td>" . $tabCand[$num_table]['moyenne'] . "</td>";
                        } else {
                            $table .= "<td>NEANT</td>";
                        }
                        $table .= "<td>" . $tabCand[$num_table]['delib'] . "</td>";
                        $table .= "<td>" . $tabCand[$num_table]['etablissement'] . "</td>";
                        $table .= "</tr>";
                    }

                    $table .= "</table>";
                    $table .= fin_cadre_enfonce();
                    echo $table;
                    if (($tache == 'classement_jury') || ($tache == 'liste_admi_jury') || ($tache == 'liste_absents_jury')) {
                        echo debut_cadre_enfonce(), "<center><a href='../plugins/fonctions/inc-pdf-classement_jury.php?annee=$annee&tache=$tache$andLien'> <b>IMPRIMER EN VERSION PDF</b></a></center>", fin_cadre_couleur();
                    } else {
                        echo debut_cadre_enfonce(), "<center><a href='../plugins/fonctions/inc-pdf-admis.php?annee=$annee&tache=$tache$andLien'> <b>IMPRIMER EN VERSION PDF</b></a></center>", fin_cadre_couleur();

                    }
                    break;
                case 'classement_candidats':
                    if ($tache == 'classement_candidats') {
                        $andWhere .= " AND (delib1='Passable' OR delib1='Abien' OR delib1='Bien' OR delib1='TBien') ";
                        $limit = " LIMIT 0, $tri_nbreMax ";
                        $orderBy = 'moyenne DESC';
                    } else {
                        $andWhere .= " AND ((delib1='Passable' OR delib1='Abien' OR delib1='Bien' OR delib1='TBien') OR (delib1='Oral' AND delib2='Passable'))";
                        $orderBy = 'can.nom, can.prenoms';
                    }
                    $sql = "SELECT can.num_table, can.nom, can.prenoms, can.ddn, can.ldn, can.pdn, eta.etablissement, cent.etablissement as centre,
						ser.serie, rep.jury, lang.langue_vivante, ef1.epreuve_facultative_a, ef2.epreuve_facultative_b, spo.eps,
						res.moyenne, res.moyenne2, res.delib1, res.delib2
						FROM bg_repartition rep, bg_ref_region reg, bg_ref_prefecture pre, bg_ref_ville vil,
						bg_ref_etablissement eta, bg_ref_etablissement cent,
						bg_ref_inspection insp, bg_ref_serie ser, bg_ref_eps spo, bg_resultats res, bg_candidats can
						LEFT JOIN bg_ref_langue_vivante lang ON can.lv2=lang.id
						LEFT JOIN bg_ref_epreuve_facultative_a ef1 ON can.efa=ef1.id
						LEFT JOIN bg_ref_epreuve_facultative_b ef2 ON can.efb=ef2.id
						WHERE can.annee=$annee AND rep.annee=$annee AND res.annee=$annee
						AND can.num_table=rep.num_table AND rep.id_centre=cent.id AND res.num_table=can.num_table
						AND can.etablissement=eta.id AND eta.id_ville=vil.id AND vil.id_prefecture=pre.id AND pre.id_region=reg.id
						AND eta.id_inspection=insp.id AND can.serie=ser.id AND can.eps=spo.id

						$andWhere
						ORDER BY $orderBy
						$limit ";
                    $result = bg_query($lien, $sql, __FILE__, __LINE__);
                    $tab = array('num_table', 'nom', 'prenoms', 'ddn', 'ldn', 'pdn', 'pays', 'etablissement', 'centre', 'serie', 'jury', 'moyenne',
                        'moyenne2', 'delib1', 'delib2');
                    $i = 1;
                    while ($row = mysqli_fetch_array($result)) {
                        foreach ($tab as $val) {
                            $$val = $row[$val];
                        }

                        if ($delib1 == 'Oral') {$delib1 = $delib2;
                            $moyenne = $moyenne2;}
                        $tabNum[] = $num_table;
                        $tabCand[$num_table]['num'] = $i;
                        $tabCand[$num_table]['noms'] = $nom . ' ' . $prenoms;
                        $tabCand[$num_table]['dldn'] = afficher_date($ddn) . ' ' . $ldn . "($pdn)";
                        $tabCand[$num_table]['serie'] = $serie;
                        $tabCand[$num_table]['jury'] = $jury;
                        $tabCand[$num_table]['pays'] = $pays;
                        $tabCand[$num_table]['moyenne'] = $moyenne;
                        $tabCand[$num_table]['delib'] = $delib1;
                        $tabCand[$num_table]['etablissement'] = $etablissement;
                        $i++;
                    }
                    if ($tache == 'classement_candidats') {
                        $titre = "BAC1 $annee - Classement des candidats ";
                    } else {
                        $titre = "BAC1 $annee - Liste des admis - Centre $centre";
                    }

                    $table = debut_cadre_enfonce('', '', '', "$titre");
                    $table .= "<table>";
                    $table .= "<th>N°</th><th>N° DE TABLE</th><th>NOMS ET PRENOMS</th><th>DATE ET LIEU DE NAISSANCE</th><th>SERIE</th><th>JURY</th><th>MOYENNE</th><th>MENTION</th>";

                    foreach ($tabNum as $num_table) {
                        $table .= "<tr>";
                        $table .= "<td>" . $tabCand[$num_table]['num'] . "</td>";
                        $table .= "<td>" . $num_table . "</td>";
                        $table .= "<td>" . $tabCand[$num_table]['noms'] . "</td>";
                        $table .= "<td>" . $tabCand[$num_table]['dldn'] . "</td>";
                        $table .= "<td>" . $tabCand[$num_table]['serie'] . "</td>";
                        $table .= "<td>" . $tabCand[$num_table]['jury'] . "</td>";
                        $table .= "<td>" . $tabCand[$num_table]['moyenne'] . "</td>";
                        $table .= "<td>" . $tabCand[$num_table]['delib'] . "</td>";
                        $table .= "<td>" . $tabCand[$num_table]['etablissement'] . "</td>";
                        $table .= "</tr>";
                    }

                    $table .= "</table>";
                    $table .= fin_cadre_enfonce();
                    echo $table;
                    echo debut_cadre_enfonce(), "<center><a href='../plugins/fonctions/inc-pdf-admis.php?annee=$annee&tache=$tache$andLien'> <b>IMPRIMER EN VERSION PDF</b></a></center>", fin_cadre_couleur();
                    break;

                case 'taux_reussite':
                    $sql1 = "SELECT ser.serie, delib1, count(*) as nbre
					FROM bg_ref_region reg,
					bg_ref_prefecture pre,
					bg_ref_ville vil,
					bg_ref_serie ser,
					bg_ref_inspection insp,
					bg_ref_etablissement eta,
					bg_ref_etablissement cent,
					bg_ref_eps spo,
					bg_resultats res,
					bg_repartition rep,
					bg_candidats can
					LEFT JOIN bg_ref_langue_vivante lang ON lang.id=can.lv2
					WHERE rep.num_table=can.num_table AND rep.annee=$annee AND can.annee=$annee
					AND can.serie=ser.id AND eta.id_ville=vil.id AND res.annee=$annee AND res.num_table=can.num_table
					AND vil.id_prefecture=pre.id AND pre.id_region=reg.id AND can.etablissement=eta.id
					AND insp.id=eta.id_inspection AND spo.id=can.eps AND rep.id_centre=cent.id
					$andWhere
					GROUP BY ser.serie, delib1
					ORDER BY ser.serie ";
                    $result1 = bg_query($lien, $sql1, __FILE__, __LINE__);
                    $tab = array('serie', 'delib1', 'nbre');
                    while ($row1 = mysqli_fetch_array($result1)) {
                        foreach ($tab as $val) {
                            $$val = $row1[$val];
                        }

                        $tNbre[$serie][$delib1] = $nbre;
                    }
                    $table = debut_cadre_enfonce('', '', '', "Taux de réussite");
                    $table .= "<table>";
                    $table .= "<th>SERIE</th><th>INSCRITS</th><th>PRESENTS</th><th>ADMIS</th><th>TAUX</th>";
                    foreach ($tNbre as $serie => $tDelib1) {
                        foreach ($tDelib1 as $delib1 => $nb) {
                            $inscrits += $nb;
                            if ($delib1 != 'Abandon' && $delib1 != 'Absent') {
                                $presents += $nb;
                            }

                            if ($delib1 != 'Abandon' && $delib1 != 'Absent' && $delib1 != 'Ajourne') {
                                $admis += $nb;
                            }

                        }
                        $taux = number_format((($admis * 100) / $presents), 2, ',', '') . '%';
                        $table .= "<tr><td>$serie</td><td>$inscrits</td><td>" . intval($presents) . "</td><td>" . intval($admis) . "</td><td>" . number_format((($admis * 100) / $presents), 2, ',', '') . "%</td></tr>";
                        $tot_inscrits += $inscrits;
                        $tot_presents += $presents;
                        $tot_admis += $admis;
                        $inscrits = $presents = $admis = 0;
                    }
                    $tot_taux = number_format((($tot_admis * 100) / $tot_presents), 2, ',', '') . '%';
                    $table .= "<tr><td>TOTAL</td><td>$tot_inscrits</td><td>$tot_presents</td><td>$tot_admis</td><td>" . number_format((($tot_admis * 100) / $tot_presents), 2, ',', '') . "%</td></tr>";
                    $table .= "</table>";
                    $table .= fin_cadre_enfonce();
                    echo $table;
                    echo debut_cadre_enfonce(), "<center><a href='../plugins/fonctions/inc-pdf-taux.php?annee=$annee$andLien' target='_blank'> <b>IMPRIMER EN VERSION PDF</b></a></center>", fin_cadre_couleur();

                    break;

                case 'taux_etablissements':
                    $sql1 = "SELECT eta.etablissement, delib1, count(*) as nbre
					FROM bg_ref_region reg,
					bg_ref_prefecture pre,
					bg_ref_ville vil,
					bg_ref_serie ser,
					bg_ref_inspection insp,
					bg_ref_etablissement eta,
					bg_ref_etablissement cent,
					bg_ref_eps spo,
					bg_resultats res,
					bg_repartition rep,
					bg_candidats can
					LEFT JOIN bg_ref_langue_vivante lang ON lang.id=can.lv2
					WHERE rep.num_table=can.num_table AND rep.annee=$annee AND can.annee=$annee
					AND can.serie=ser.id AND eta.id_ville=vil.id AND res.annee=$annee AND res.num_table=can.num_table
					AND vil.id_prefecture=pre.id AND pre.id_region=reg.id AND can.etablissement=eta.id AND rep.id_centre=cent.id
					AND insp.id=eta.id_inspection AND spo.id=can.eps
					$andWhere
					GROUP BY eta.id, delib1
					ORDER BY eta.etablissement ";
                    $result1 = bg_query($lien, $sql1, __FILE__, __LINE__);
                    $tab = array('etablissement', 'delib1', 'nbre');
                    while ($row1 = mysqli_fetch_array($result1)) {
                        foreach ($tab as $val) {
                            $$val = $row1[$val];
                        }

                        $tNbre[$etablissement][$delib1] = $nbre;
                    }
                    $table = debut_cadre_enfonce('', '', '', "Taux de réussite");
                    $table .= "<table>";
                    $table .= "<th>ETABLISSEMENTS</th><th>INSCRITS</th><th>PRESENTS</th><th>ADMIS</th><th>TAUX</th>";
                    foreach ($tNbre as $etablissement => $tDelib1) {
                        foreach ($tDelib1 as $delib1 => $nb) {
                            $inscrits += $nb;
                            if ($delib1 != 'Abandon' && $delib1 != 'Absent') {
                                $presents += $nb;
                            }

                            if ($delib1 != 'Abandon' && $delib1 != 'Absent' && $delib1 != 'Ajourne') {
                                $admis += $nb;
                            }

                        }
                        $taux = number_format((($admis * 100) / $presents), 2, ',', '') . '%';
                        $table .= "<tr><td>$etablissement</td><td>$inscrits</td><td>" . intval($presents) . "</td><td>" . intval($admis) . "</td><td>" . number_format((($admis * 100) / $presents), 2, ',', '') . "%</td></tr>";
                        $tot_inscrits += $inscrits;
                        $tot_presents += $presents;
                        $tot_admis += $admis;
                        $inscrits = $presents = $admis = 0;
                    }
                    $tot_taux = number_format((($tot_admis * 100) / $tot_presents), 2, ',', '') . '%';
                    $table .= "<tr><td>TOTAL</td><td>$tot_inscrits</td><td>$tot_presents</td><td>$tot_admis</td><td>" . number_format((($tot_admis * 100) / $tot_presents), 2, ',', '') . "%</td></tr>";
                    $table .= "</table>";
                    $table .= fin_cadre_enfonce();
                    echo debut_cadre_enfonce(), "<center><a href='../plugins/fonctions/inc-pdf-taux_etablissements.php?annee=$annee$andLien'  target='_blank'> <b>IMPRIMER EN VERSION PDF</b></a></center>", fin_cadre_couleur();
                    echo $table;

                    break;

                case 'stats_notes_jury':
                case 'stats_notes':
                    $sql1 = "SELECT ser.serie, mat.matiere, typ.type_note, notes.note, count(*) as nbre
					FROM bg_ref_region reg,
					bg_ref_prefecture pre,
					bg_ref_ville vil,
					bg_ref_serie ser,
					bg_ref_inspection insp,
					bg_ref_etablissement eta,
                    bg_ref_etablissement cent,
					bg_ref_eps spo,
					bg_resultats res,
					bg_repartition rep,
					bg_notes notes,
					bg_ref_type_note typ,
					bg_ref_matiere mat,
					bg_candidats can
					LEFT JOIN bg_ref_langue_vivante lang ON lang.id=can.lv2
					WHERE rep.num_table=can.num_table AND rep.annee=$annee AND can.annee=$annee
					AND can.serie=ser.id AND eta.id_ville=vil.id AND res.annee=$annee AND res.num_table=can.num_table
					AND vil.id_prefecture=pre.id AND pre.id_region=reg.id AND can.etablissement=eta.id
					AND insp.id=eta.id_inspection AND spo.id=can.eps
					AND typ.id=notes.id_type_note AND mat.id=notes.id_matiere AND rep.id_centre=cent.id
					AND notes.annee=$annee AND notes.num_table=can.num_table AND notes.note>=0
					$andWhere
					GROUP BY ser.serie, mat.matiere, typ.type_note, notes.note
					ORDER BY ser.serie, mat.matiere, typ.type_note, notes.note ";
                    $result1 = bg_query($lien, $sql1, __FILE__, __LINE__);
                    $tab = array('serie', 'matiere', 'note', 'type_note', 'nbre');
                    while ($row1 = mysqli_fetch_array($result1)) {
                        foreach ($tab as $val) {
                            $$val = $row1[$val];
                        }

                        $tNbre[$serie][$type_note][$matiere][$note] = $nbre;
                    }
                    $table = debut_cadre_enfonce('', '', '', "Stats sur les notes");
                    foreach ($tNbre as $serie => $t1) {
                        //echo $serie."<br/>";
                        foreach ($t1 as $type_note => $t2) {
                            //echo $type_note."<br/>";
                            foreach ($t2 as $matiere => $t3) {
                                echo 'SERIE ' . $serie . ' - ' . $type_note . ' - ' . $matiere . "<br/>";
                                echo "<table>";
                                echo "<th>Note</th><th>Nombre</th>";
                                foreach ($t3 as $note => $nb) {
                                    echo "<tr><td>$note</td><td>$nb</td></tr>";
                                }
                                echo "</table>";
                            }
                        }
                    }
                    $table .= fin_cadre_enfonce();
                    //                echo debut_cadre_enfonce(), "<center><a href='../plugins/fonctions/inc-pdf-taux.php?annee=$annee$andLien'> <b>IMPRIMER EN VERSION PDF</b></a></center>", fin_cadre_couleur();

                    break;
                case 'stats_notes_globales':
                    $limit = 0;
                    if (!isset($_REQUEST['limit'])) {
                        $limit = 0;
                    }

                    if ($limit < 0) {
                        $limit = 0;
                    }
                    /////////////////////////
                    $tri .= "<form name='form_tri' method='POST' ><table>";
                    $tri .= "<tr><td><input type='hidden' name='limit' value='$limit' /><input type='hidden' name='annee' value='$annee' /><input type='hidden' name='id_inspection' value='$id_inspection' /></td>
                        <td></td></tr>";
                    //$tri .= "<tr><td><input type='submit' value='OK' /></td></tr>";
                    $tri .= "</table></form>";
                    echo $tri;
                    ////////////////////////

                    //Affichage des resultats du tri
                    $pre = ($limit - MAX_LIGNES);

                    $sql1 = "SELECT res.num_table,res.id_anonyme,nom,prenoms,res.annee,ser.serie,sexe,can.ddn,
                    sum(IF(nte.id_matiere=48,note,NULL)) AS LV1,
                    sum(IF(nte.id_matiere=51,note,NULL)) AS LV2,
                    sum(IF(nte.id_matiere=60,note,NULL)) AS ECM,
                    sum(IF(nte.id_matiere=46,note,NULL)) AS Fr,
                    sum(IF(nte.id_matiere=47,note,NULL)) AS HG,
                    sum(IF(nte.id_matiere=29,note,NULL)) AS Maths,
                    sum(IF(nte.id_matiere=49,note,NULL)) AS SVT,
                    sum(IF(nte.id_matiere=50,note,NULL)) AS Physique,
                    sum(IF(nte.id_matiere=8,note,NULL)) AS Cout,
                    sum(IF(nte.id_matiere=61,note,NULL)) AS Agri,
                    sum(IF(nte.id_matiere=42,note,NULL)) AS EPS,
                    sum(IF(nte.id_matiere=3,note,NULL)) AS Ewe_Kabye_Latin,
                    sum(IF(nte.id_matiere=4,note,NULL)) AS Russe,
                    sum(IF(nte.id_matiere=5,note,NULL)) AS Arabe,
                    sum(IF(nte.id_matiere=6,note,NULL)) AS Dessin,
                    sum(IF(nte.id_matiere=7,note,NULL)) AS Musique,
                    nte.jury,total,res.coeff,moyenne,delib1
                    FROM bg_notes as nte
                    join bg_resultats as res
                    on nte.num_table=res.num_table AND nte.id_type_note<>3
                    JOIN bg_candidats can
                    on can.num_table=nte.num_table
                    JOIN  bg_ref_serie ser
                    on ser.id=can.serie
                    GROUP BY num_table ";
                    $result1 = bg_query($lien, $sql1, __FILE__, __LINE__);
                    //Pagination
                    $total = mysqli_num_rows($result1);
                    echo "<p class='center'>";
                    if ($limit > 0) {
                        //echo "<a href=\"javascript:document.forms['form_rep'].elements.tache[stats_notes_globales].limit.value=" . ($limit - MAX_LIGNES) . ";document.forms['form_rep'].elements.tache[stats_notes_globales].submit();\"><<::Précédent </a>";
                        echo "<a href=\"javascript:document.forms['form_rep'].limit.value=" . ($limit - MAX_LIGNES) . ";document.forms['form_rep'].submit();\"><<::Précédent </a>";
                    }

                    $limit2 = ($limit + MAX_LIGNES);
                    if ($total < $limit2) {
                        $limit2 = $total;
                    }

                    echo " $total enregistrements au total [$limit .... $limit2] ";
                    if ($total > ($limit + MAX_LIGNES)) {
                        //echo "<a href=\"javascript:document.forms['form_rep'].elements.tache[stats_notes_globales].limit.value=" . ($limit + MAX_LIGNES) . ";document.forms['form_rep'].elements.tache[stats_notes_globales].submit();\"> Suivant::>></a>";
                        echo "<a href=\"javascript:document.forms['form_rep'].limit.value=" . ($limit + MAX_LIGNES) . ";document.forms['form_rep'].submit();\"> Suivant::>></a>";
                    }

                    ////13-01-2023/////echo "<br/><a href='./?exec=candidats'>Retour au Menu principal</a>";
                    echo "</p>";

                    $sql_aff = "SELECT res.num_table,res.id_anonyme,nom,prenoms,res.annee,ser.serie,sexe,can.ddn,
                    sum(IF(nte.id_matiere=48,note,NULL)) AS LV1,
                    sum(IF(nte.id_matiere=51,note,NULL)) AS LV2,
                    sum(IF(nte.id_matiere=60,note,NULL)) AS ECM,
                    sum(IF(nte.id_matiere=46,note,NULL)) AS Fr,
                    sum(IF(nte.id_matiere=47,note,NULL)) AS HG,
                    sum(IF(nte.id_matiere=29,note,NULL)) AS Maths,
                    sum(IF(nte.id_matiere=49,note,NULL)) AS SVT,
                    sum(IF(nte.id_matiere=50,note,NULL)) AS Physique,
                    sum(IF(nte.id_matiere=8,note,NULL)) AS Cout,
                    sum(IF(nte.id_matiere=61,note,NULL)) AS Agri,
                    sum(IF(nte.id_matiere=42,note,NULL)) AS EPS,
                    sum(IF(nte.id_matiere=3,note,NULL)) AS Ewe_Kabye_Latin,
                    sum(IF(nte.id_matiere=4,note,NULL)) AS Russe,
                    sum(IF(nte.id_matiere=5,note,NULL)) AS Arabe,
                    sum(IF(nte.id_matiere=6,note,NULL)) AS Dessin,
                    sum(IF(nte.id_matiere=7,note,NULL)) AS Musique,
                    nte.jury,total,res.coeff,moyenne,delib1
                    FROM bg_notes as nte
                    join bg_resultats as res
                    on nte.num_table=res.num_table AND nte.id_type_note<>3
                    JOIN bg_candidats can
                    on can.num_table=nte.num_table
                    JOIN  bg_ref_serie ser
                    on ser.id=can.serie
                    GROUP BY num_table  LIMIT $limit, " . MAX_LIGNES;
                    $result_aff = bg_query($lien, $sql_aff, __FILE__, __LINE__);
                    //fin pagination
                    $tab = array(
                        'num_table',
                        'id_anonyme',
                        'nom',
                        'prenoms',
                        'annee',
                        'serie',
                        'sexe',
                        'ddn',
                        'LV1',
                        'LV2', 'ECM', 'Fr', 'HG', 'Maths', 'SVT', 'Physique',
                        'Cout', 'Agri', 'EPS', 'Ewe_Kabye_Latin', 'Russe', 'Arabe', 'Dessin', 'Musique',
                        'jury', 'total', 'coeff', 'moyenne', 'delib1');
                    echo debut_cadre_enfonce(), "<center><a href='../plugins/fonctions/csvfile/inc-excel-stats_notes_globales.php?annee=$annee$andLien'> <b>IMPRIMER EN EXCEL</b></a></center>", fin_cadre_couleur();
                    $tableau = "<table class='spip liste'><thead>";
                    $tableau .= "<th>N° table</th><th>Anonyme</th><th>Nom & prénoms</th><th>Série</th><th>Sexe</th><th>Date naiss</th>
                    <th>LV1</th><th>LV2</th><th>ECM</th><th>Fr</th><th>HG</th><th>Maths</th><th>SVT</th><th>PC</th>
                    <th>Cout</th><th>Agri</th><th>EPS</th><th>Ewe/Kabye/Latin</th><th>Rus.</th><th>Ara.</th><th>Des.</th><th>Mus.</th>
                    <th>Jury</th><th>Total</th><th>Coeff</th><th>Moy.</th><th>delib1</th>";
                    $tableau .= "</thead><tbody>";

                    $i = 1;
                    while ($row = mysqli_fetch_array($result_aff)) {
                        foreach ($tab as $var) {
                            $$var = $row[$var];
                        }
                        if ($row['sexe'] == '1') {
                            $sexe = 'F';
                        } else {
                            $sexe = 'M';
                        }

                        $tableau .= "<tr>"
                            . "<td><b>" . $row['num_table'] . "</b></td>"
                            . "<td>" . $row['id_anonyme'] . "</td>"
                            . "<td>" . $row['nom'] . " " . $row['prenoms'] . "</td>"
                            . "<td>" . $row['serie'] . "</td>"
                            . "<td>" . $sexe . "</td>"
                            . "<td>" . $row['ddn'] . "</td>"
                            . "<td>" . $row['LV1'] . "</td>"
                            . "<td>" . $row['LV2'] . "</td>"
                            . "<td>" . $row['ECM'] . "</td>"
                            . "<td>" . $row['Fr'] . "</td>"
                            . "<td>" . $row['HG'] . "</td>"
                            . "<td>" . $row['Maths'] . "</td>"
                            . "<td>" . $row['SVT'] . "</td>"
                            . "<td>" . $row['Physique'] . "</td>"
                            . "<td>" . $row['Cout'] . "</td>"
                            . "<td>" . $row['Agri'] . "</td>"
                            . "<td>" . $row['EPS'] . "</td>"
                            . "<td>" . $row['Ewe_Kabye_Latin'] . "</td>"
                            . "<td>" . $row['Russe'] . "</td>"
                            . "<td>" . $row['Arabe'] . "</td>"
                            . "<td>" . $row['Dessin'] . "</td>"
                            . "<td>" . $row['Musique'] . "</td>"
                            . "<td>" . $row['jury'] . "</td>"
                            . "<td>" . $row['total'] . "</td>"
                            . "<td>" . $row['coeff'] . "</td>"
                            . "<td>" . $row['moyenne'] . "</td>"
                            . "<td>" . $row['delib1'] . "</td>";
                        $i++;
                    }
                    $table = debut_cadre_enfonce('', '', '', "Stats sur les notes globales");
                    $tableau .= "</tbody><tfoot><tr></tr></tfoot></table>";
                    echo $tableau;
                    $table .= fin_cadre_enfonce();
                    //     echo debut_cadre_enfonce(), "<center><a href='../plugins/fonctions/inc-pdf-taux.php?annee=$annee$andLien'> <b>IMPRIMER EN VERSION PDF</b></a></center>", fin_cadre_couleur();

                    break;
                case 'stats_plateformes_externes':
                    if (isset($_POST['limit'])) {
                        $limit = 0;
                        if (!isset($_REQUEST['limit'])) {
                            $limit = 0;
                        }

                        if ($limit < 0) {
                            $limit = 0;
                        }
                    }

                    /////////////////////////
                    // $tri .= "<form name='form_pla' method='POST' ><table>";
                    // $tri .= "<tr><td><input type='hidden' name='limit' value='$limit' /><input type='hidden' name='annee' value='$annee' /><input type='hidden' name='id_inspection' value='$id_inspection' /></td>
                    //     <td></td></tr>";
                    // $tri .= "</table></form>";
                    // echo $tri;
                    ////////////////////////

                    //Affichage des resultats du tri
                    $pre = ($limit - MAX_LIGNES);

                    $sql1 = "SELECT nom, prenoms,can.sexe,ser.serie, can.id_candidat, can.num_table, ddn, ldn,can.telephone, res.delib1,eta.etablissement
                    FROM bg_candidats can, bg_ref_serie ser, bg_resultats res,bg_repartition rep,bg_ref_etablissement eta
                    WHERE can.annee=res.annee
                    AND can.serie=ser.id
                    AND can.num_table=rep.num_table
                    AND res.id_anonyme=rep.id_anonyme
                    AND can.etablissement=eta.id ";
                    $result1 = bg_query($lien, $sql1, __FILE__, __LINE__);
                    //Pagination
                    $total = mysqli_num_rows($result1);
                    echo "<p class='center'>";
                    if ($limit > 0) {
                        echo "<a href=\"javascript:document.forms['form_rep'].limit.value=" . ($limit - MAX_LIGNES) . ";document.forms['form_rep'].submit();\"><<::Précédent </a>";
                    }

                    $limit2 = ($limit + MAX_LIGNES);
                    if ($total < $limit2) {
                        $limit2 = $total;
                    }
                    echo " $total enregistrements au total [$limit .... $limit2] ";
                    if ($total > ($limit + MAX_LIGNES)) {
                        echo "<a href=\"javascript:document.forms['form_rep'].limit.value=" . ($limit + MAX_LIGNES) . ";document.forms['form_rep'].submit();\"> Suivant::>></a>";
                    }

                    ////13-01-2023/////echo "<br/><a href='./?exec=candidats'>Retour au Menu principal</a>";
                    echo "</p>";

                    $sql_aff = "SELECT nom, prenoms,can.sexe,ser.serie, can.id_candidat, can.num_table, ddn, ldn,can.telephone, res.delib1,eta.etablissement
                    FROM bg_candidats can, bg_ref_serie ser, bg_resultats res,bg_repartition rep,bg_ref_etablissement eta
                    WHERE can.annee=res.annee
                    AND can.serie=ser.id
                    AND can.num_table=rep.num_table
                    AND res.id_anonyme=rep.id_anonyme
                    AND can.etablissement=eta.id LIMIT $limit " . MAX_LIGNES;
                    $result_aff = bg_query($lien, $sql_aff, __FILE__, __LINE__);
                    //fin pagination
                    $tab = array(
                        'nom',
                        'prenoms',
                        'sexe',
                        'serie',
                        'id_candidat',
                        'num_table',
                        'ddn',
                        'ldn',
                        'telephone',
                        'delib1',
                        'etablissement');
                    echo debut_cadre_enfonce(), "<center><a href='../plugins/fonctions/csvfile/inc-csv-stats_plateformes_externes.php?annee=$annee$andLien'> <b>IMPRIMER EN CSV</b></a> <br/> <hr/><a href='../plugins/fonctions/csvfile/inc-csv-stats_plateformes_externes_sms.php?annee=$annee$andLien'> <b>IMPRIMER EN CSV (SMS)</b></a></center>", fin_cadre_couleur();
                    $tableau = "<table class='spip liste'><thead>";
                    $tableau .= "<th>N° table</th><th>Anonyme</th><th>Nom & prénoms</th><th>Série</th><th>Sexe</th><th>Date naiss</th>
                    <th>Lieu</th><th>telephone</th><th>delib1</th><th>etablissement</th>";
                    $tableau .= "</thead><tbody>";

                    $i = 1;
                    while ($row = mysqli_fetch_array($result_aff)) {
                        foreach ($tab as $var) {
                            $$var = $row[$var];
                        }
                        if ($row['sexe'] == '1') {
                            $sexe = 'F';
                        } else {
                            $sexe = 'M';
                        }

                        $tableau .= "<tr>"
                            . "<td><b>" . $row['num_table'] . "</b></td>"
                            . "<td>" . $row['id_anonyme'] . "</td>"
                            . "<td>" . $row['nom'] . " " . $row['prenoms'] . "</td>"
                            . "<td>" . $row['serie'] . "</td>"
                            . "<td>" . $sexe . "</td>"
                            . "<td>" . $row['ddn'] . "</td>"
                            . "<td>" . $row['ldn'] . "</td>"
                            . "<td>" . $row['telephone'] . "</td>"
                            . "<td>" . $row['delib1'] . "</td>"
                            . "<td>" . $row['etablissement'] . "</td>";
                        $i++;
                    }
                    $table = debut_cadre_enfonce('', '', '', "Stats sur les résultats");
                    $tableau .= "</tbody><tfoot><tr></tr></tfoot></table>";
                    echo $tableau;
                    $table .= fin_cadre_enfonce();
                    break;
                case 'stats_candidats_normes_resultats':
                    if (isset($_POST['limit'])) {
                        $limit = 0;
                        if (!isset($_REQUEST['limit'])) {
                            $limit = 0;
                        }

                        if ($limit < 0) {
                            $limit = 0;
                        }
                    }

                    /////////////////////////
                    // $tri .= "<form name='form_pla' method='POST' ><table>";
                    // $tri .= "<tr><td><input type='hidden' name='limit' value='$limit' /><input type='hidden' name='annee' value='$annee' /><input type='hidden' name='id_inspection' value='$id_inspection' /></td>
                    //     <td></td></tr>";
                    // $tri .= "</table></form>";
                    // echo $tri;
                    ////////////////////////

                    //Affichage des resultats du tri
                    $pre = ($limit - MAX_LIGNES);

                    $sql1 = "SELECT nom, prenoms,can.sexe,ser.serie, can.id_candidat, can.num_table, ddn, ldn,can.telephone, can.nom_photo,eta.etablissement
                    FROM bg_candidats can,
                     bg_ref_serie ser,
                     /*bg_resultats res,*/
                     bg_repartition rep,
                     bg_ref_etablissement eta
                    WHERE can.annee=2024
                    AND can.serie=ser.id
                    AND can.num_table=rep.num_table
                    /*AND res.id_anonyme=rep.id_anonyme*/
                    AND can.etablissement=eta.id ";
                    $result1 = bg_query($lien, $sql1, __FILE__, __LINE__);
                    //Pagination
                    $total = mysqli_num_rows($result1);
                    echo "<p class='center'>";
                    if ($limit > 0) {
                        echo "<a href=\"javascript:document.forms['form_rep'].limit.value=" . ($limit - MAX_LIGNES) . ";document.forms['form_rep'].submit();\"><<::Précédent </a>";
                    }

                    $limit2 = ($limit + MAX_LIGNES);
                    if ($total < $limit2) {
                        $limit2 = $total;
                    }
                    echo " $total enregistrements au total [$limit .... $limit2] ";
                    if ($total > ($limit + MAX_LIGNES)) {
                        echo "<a href=\"javascript:document.forms['form_rep'].limit.value=" . ($limit + MAX_LIGNES) . ";document.forms['form_rep'].submit();\"> Suivant::>></a>";
                    }

                    ////13-01-2023/////echo "<br/><a href='./?exec=candidats'>Retour au Menu principal</a>";
                    echo "</p>";

                    $sql_aff = "SELECT nom, prenoms,can.sexe,ser.serie, can.id_candidat, can.num_table, ddn, ldn,can.telephone, can.nom_photo,eta.etablissement
                    FROM bg_candidats can,
                     bg_ref_serie ser,
                     /*bg_resultats res,*/
                     bg_repartition rep,
                     bg_ref_etablissement eta
                    WHERE can.annee=2024
                    AND can.serie=ser.id
                    AND can.num_table=rep.num_table
                    /*AND res.id_anonyme=rep.id_anonyme*/
                    AND can.etablissement=eta.id LIMIT $limit " . MAX_LIGNES;
                    $result_aff = bg_query($lien, $sql_aff, __FILE__, __LINE__);
                    //fin pagination
                    $tab = array(
                        'nom',
                        'prenoms',
                        'sexe',
                        'serie',
                        'id_candidat',
                        'num_table',
                        'ddn',
                        'ldn',
                        'telephone',
                        'delib1',
                        'etablissement');
                    echo debut_cadre_enfonce(), "<center><a href='../plugins/fonctions/csvfile/inc-csv-stats_candidat_normes_resultats.php?annee=$annee$andLien'> <b>IMPRIMER EN CSV</b></a> </center>", fin_cadre_couleur();
                    $tableau = "<table class='spip liste'><thead>";
                    $tableau .= "<th>N° table</th><th>Anonyme</th><th>Nom & prénoms</th><th>Série</th><th>Sexe</th><th>Date naiss</th>
                    <th>Lieu</th><th>telephone</th><th>delib1</th><th>etablissement</th>";
                    $tableau .= "</thead><tbody>";

                    $i = 1;
                    while ($row = mysqli_fetch_array($result_aff)) {
                        foreach ($tab as $var) {
                            $$var = $row[$var];
                        }
                        if ($row['sexe'] == '1') {
                            $sexe = 'F';
                        } else {
                            $sexe = 'M';
                        }

                        $tableau .= "<tr>"
                            . "<td><b>" . $row['num_table'] . "</b></td>"
                            . "<td>" . $row['id_anonyme'] . "</td>"
                            . "<td>" . $row['nom'] . " " . $row['prenoms'] . "</td>"
                            . "<td>" . $row['serie'] . "</td>"
                            . "<td>" . $sexe . "</td>"
                            . "<td>" . $row['ddn'] . "</td>"
                            . "<td>" . $row['ldn'] . "</td>"
                            . "<td>" . $row['telephone'] . "</td>"
                            . "<td>" . $row['delib1'] . "</td>"
                            . "<td>" . $row['etablissement'] . "</td>";
                        $i++;
                    }
                    $table = debut_cadre_enfonce('', '', '', "Stats sur les résultats");
                    $tableau .= "</tbody><tfoot><tr></tr></tfoot></table>";
                    echo $tableau;
                    $table .= fin_cadre_enfonce();
                    break;
                case 'registre':
                    if ($tache == 'stats_presents') {
                        $andWhere2 = " AND res.delib1!='Absent' AND res.delib1!='Abandon' ";
                        $title = 'STATISTIQUES DES PRESENTS';
                    } elseif ($tache == 'stats_absents') {
                        $andWhere2 = " AND (res.delib1='Absent' OR res.delib1='Abandon') ";
                        $title = 'STATISTIQUES DES ABSENTS';
                    } elseif ($tache == 'stats_admis') {
                        $andWhere2 = " AND (res.delib1='Passable' OR res.delib1='Abien' OR res.delib1='Bien' OR res.delib1='TBien' OR res.delib1='Oral') ";
                        $title = 'STATISTIQUES DES ADMIS';
                    } else {
                        $andWhere2 = '';
                        $title = 'STATISTIQUES DES INSCRITS';
                    }
                    ///////////TEST NOTE PAR CANDIDATS PAR MATIERE////////
                    $sql1 = "SELECT reg.id as id_region, pre.id as id_prefecture, cent.id as id_centre, cent.etablissement as centre, ser.id as id_serie,res.num_table,res.id_anonyme,nom,prenoms,res.annee,ser.serie,sexe,can.ddn,
                        sum(IF(nte.id_matiere=48,note,NULL)) AS LV1,
                        sum(IF(nte.id_matiere=51,note,NULL)) AS LV2,
                        sum(IF(nte.id_matiere=60,note,NULL)) AS ECM,
                        sum(IF(nte.id_matiere=46,note,NULL)) AS Fr,
                        sum(IF(nte.id_matiere=47,note,NULL)) AS HG,
                        sum(IF(nte.id_matiere=29,note,NULL)) AS Maths,
                        sum(IF(nte.id_matiere=49,note,NULL)) AS SVT,
                        sum(IF(nte.id_matiere=50,note,NULL)) AS Physique,
                        sum(IF(nte.id_matiere=8,note,NULL)) AS Cout,
                        sum(IF(nte.id_matiere=61,note,NULL)) AS Agri,
                        sum(IF(nte.id_matiere=42,note,NULL)) AS EPS,
                        sum(IF(nte.id_matiere=3,note,NULL)) AS Ewe_Kabye_Latin,
                        sum(IF(nte.id_matiere=4,note,NULL)) AS Russe,
                        sum(IF(nte.id_matiere=5,note,NULL)) AS Arabe,
                        sum(IF(nte.id_matiere=6,note,NULL)) AS Dessin,
                        sum(IF(nte.id_matiere=7,note,NULL)) AS Musique,
                        nte.jury,total,res.coeff,moyenne,delib1,
                        count(*) as nbre_cand
                        FROM bg_ref_region reg,
                        bg_notes as nte,
                        bg_ref_prefecture pre,
                        bg_ref_ville vil,
                        bg_ref_etablissement cent,
                        bg_repartition rep,
                        bg_ref_serie ser,
                        bg_ref_inspection insp,
                        bg_ref_etablissement eta,
                        bg_ref_eps spo,
                        bg_candidats can
                        LEFT JOIN bg_ref_langue_vivante lang ON lang.id=can.lv2
                        LEFT JOIN bg_ref_epreuve_facultative_a faca ON faca.id=can.efa
                        LEFT JOIN bg_ref_epreuve_facultative_b facb ON facb.id=can.efb
                        LEFT JOIN bg_resultats res ON (res.num_table=can.num_table)
                        WHERE can.serie=ser.id AND can.num_table=rep.num_table
                        AND nte.num_table=res.num_table AND nte.id_type_note<>3
                        AND can.num_table=nte.num_table
                        AND ser.id=can.serie
                        AND cent.id_ville=vil.id AND vil.id_prefecture=pre.id AND pre.id_region=reg.id AND rep.id_centre=cent.id
                        AND insp.id=eta.id_inspection AND eta.id=can.etablissement AND spo.id=can.eps
                        $andWhere $andWhere2
                        GROUP BY cent.id, num_table
                        ORDER BY num_table ";
                    ///////////FIN TEST NOTE PAR CANDIDATS PAR MATIERE////////

                    $result1 = bg_query($lien, $sql1, __FILE__, __LINE__);
                    //Pagination
                    $total = mysqli_num_rows($result1);
                    $sql_aff = "SELECT reg.id as id_region, pre.id as id_prefecture, cent.id as id_centre, cent.etablissement as centre, ser.id as id_serie,res.num_table,res.id_anonyme,nom,prenoms,res.annee,ser.serie,sexe,can.ddn,
                        sum(IF(nte.id_matiere=48,note,NULL)) AS LV1,
                        sum(IF(nte.id_matiere=51,note,NULL)) AS LV2,
                        sum(IF(nte.id_matiere=60,note,NULL)) AS ECM,
                        sum(IF(nte.id_matiere=46,note,NULL)) AS Fr,
                        sum(IF(nte.id_matiere=47,note,NULL)) AS HG,
                        sum(IF(nte.id_matiere=29,note,NULL)) AS Maths,
                        sum(IF(nte.id_matiere=49,note,NULL)) AS SVT,
                        sum(IF(nte.id_matiere=50,note,NULL)) AS Physique,
                        sum(IF(nte.id_matiere=8,note,NULL)) AS Cout,
                        sum(IF(nte.id_matiere=61,note,NULL)) AS Agri,
                        sum(IF(nte.id_matiere=42,note,NULL)) AS EPS,
                        sum(IF(nte.id_matiere=3,note,NULL)) AS Ewe_Kabye_Latin,
                        sum(IF(nte.id_matiere=4,note,NULL)) AS Russe,
                        sum(IF(nte.id_matiere=5,note,NULL)) AS Arabe,
                        sum(IF(nte.id_matiere=6,note,NULL)) AS Dessin,
                        sum(IF(nte.id_matiere=7,note,NULL)) AS Musique,
                        nte.jury,total,res.coeff,moyenne,delib1,
                        count(*) as nbre_cand
                        FROM bg_ref_region reg,
                        bg_notes as nte,
                        bg_ref_prefecture pre,
                        bg_ref_ville vil,
                        bg_ref_etablissement cent,
                        bg_repartition rep,
                        bg_ref_serie ser,
                        bg_ref_inspection insp,
                        bg_ref_etablissement eta,
                        bg_ref_eps spo,
                        bg_candidats can
                        LEFT JOIN bg_ref_langue_vivante lang ON lang.id=can.lv2
                        LEFT JOIN bg_ref_epreuve_facultative_a faca ON faca.id=can.efa
                        LEFT JOIN bg_ref_epreuve_facultative_b facb ON facb.id=can.efb
                        LEFT JOIN bg_resultats res ON (res.num_table=can.num_table)
                        WHERE can.serie=ser.id AND can.num_table=rep.num_table
                        AND nte.num_table=res.num_table AND nte.id_type_note<>3
                        AND can.num_table=nte.num_table
                        AND ser.id=can.serie
                        AND cent.id_ville=vil.id AND vil.id_prefecture=pre.id AND pre.id_region=reg.id AND rep.id_centre=cent.id
                        AND insp.id=eta.id_inspection AND eta.id=can.etablissement AND spo.id=can.eps
                        $andWhere $andWhere2
                        GROUP BY cent.id, num_table
                        ORDER BY num_table";
                    $result_aff = bg_query($lien, $sql_aff, __FILE__, __LINE__);
                    //fin pagination
                    $tab = array(
                        'num_table',
                        'id_anonyme',
                        'nom',
                        'prenoms',
                        'annee',
                        'serie',
                        'sexe',
                        'ddn',
                        'LV1',
                        'LV2', 'ECM', 'Fr', 'HG', 'Maths', 'SVT', 'Physique',
                        'Cout', 'Agri', 'EPS', 'Ewe_Kabye_Latin', 'Russe', 'Arabe', 'Dessin', 'Musique',
                        'jury', 'total', 'coeff', 'moyenne', 'delib1');
                    echo debut_cadre_enfonce(), "<center><a href='../plugins/fonctions/inc-pdf-pv_registre.php?annee=$annee$andLien' target='_blank'> <b>IMPRIMER EN PDF</b></a></center>", fin_cadre_couleur();
                    $tableau = "<table class='spip liste'><thead>";
                    $tableau .= "<th>N°</th><th>N° table</th><th>Nom & prénoms</th><th>Série</th><th>Sexe</th><th>Date naiss</th>
                        <th>LV1</th><th>LV2</th><th>ECM</th><th>Fr</th><th>HG</th><th>Maths</th><th>SVT</th><th>PC</th>
                        <th>Cout</th><th>Agri</th><th>EPS</th><th>Ewe/Kabye/Latin</th><th>Rus.</th><th>Ara.</th><th>Des.</th><th>Mus.</th>
                        <th>Jury</th><th>Total</th><th>Coeff</th><th>Moy.</th><th>delib1</th>";
                    $tableau .= "</thead><tbody>";

                    $i = 1;
                    while ($row = mysqli_fetch_array($result_aff)) {
                        foreach ($tab as $var) {
                            $$var = $row[$var];
                        }
                        if ($row['sexe'] == '1') {
                            $sexe = 'F';
                        } else {
                            $sexe = 'M';
                        }

                        $tableau .= "<tr>"
                            . "<td><b>" . $row['num_table'] . "</b></td>"
                            . "<td>" . $row['id_anonyme'] . "</td>"
                            . "<td>" . $row['nom'] . " " . $row['prenoms'] . "</td>"
                            . "<td>" . $row['serie'] . "</td>"
                            . "<td>" . $sexe . "</td>"
                            . "<td>" . $row['ddn'] . "</td>"
                            . "<td>" . $row['LV1'] . "</td>"
                            . "<td>" . $row['LV2'] . "</td>"
                            . "<td>" . $row['ECM'] . "</td>"
                            . "<td>" . $row['Fr'] . "</td>"
                            . "<td>" . $row['HG'] . "</td>"
                            . "<td>" . $row['Maths'] . "</td>"
                            . "<td>" . $row['SVT'] . "</td>"
                            . "<td>" . $row['Physique'] . "</td>"
                            . "<td>" . $row['Cout'] . "</td>"
                            . "<td>" . $row['Agri'] . "</td>"
                            . "<td>" . $row['EPS'] . "</td>"
                            . "<td>" . $row['Ewe_Kabye_Latin'] . "</td>"
                            . "<td>" . $row['Russe'] . "</td>"
                            . "<td>" . $row['Arabe'] . "</td>"
                            . "<td>" . $row['Dessin'] . "</td>"
                            . "<td>" . $row['Musique'] . "</td>"
                            . "<td>" . $row['jury'] . "</td>"
                            . "<td>" . $row['total'] . "</td>"
                            . "<td>" . $row['coeff'] . "</td>"
                            . "<td>" . $row['moyenne'] . "</td>"
                            . "<td>" . $row['delib1'] . "</td>";
                        $i++;
                    }
                    $table = debut_cadre_enfonce('', '', '', "Registre");
                    $tableau .= "</tbody><tfoot><tr></tr></tfoot></table>";
                    echo $tableau;
                    $table .= fin_cadre_enfonce();
                    //     echo debut_cadre_enfonce(), "<center><a href='../plugins/fonctions/inc-pdf-taux.php?annee=$annee$andLien'> <b>IMPRIMER EN VERSION PDF</b></a></center>", fin_cadre_couleur();

                    break;
            }

        }

        echo $form;

        echo fin_cadre_trait_couleur();
        echo fin_grand_cadre(), fin_gauche(), fin_page();
    }

}

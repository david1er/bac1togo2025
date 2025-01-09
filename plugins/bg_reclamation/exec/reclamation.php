<?php
if (!defined('_ECRIRE_INC_VERSION')) {
    return;
}

include_spip('inc/presentation'); // alleger les inclusions avec un inc/presentation_mini
include '../plugins/fonctions/generales.php';

setlocale(LC_TIME, "fr_FR");
define('MAX_LIGNES', 50);

function etablissementsCandidats($lien, $annee, $id_inspection, $id_etablissement = 0, $statut = 0)
{

    $id_serie = $_POST['id_serie'];
    if ($id_etablissement > 0) {
        $andWhere = " AND eta.id=$id_etablissement AND eta.id_inspection=$id_inspection";
    }

    if ($id_serie != 0) {
        $andWhere .= ' AND can.serie=' . $id_serie;
        $andLien = ' &id_serie=' . $id_serie;
    } else {
        $andLien = '';
    }

    $sql = "SELECT eta.etablissement, eta.id as id_etablissement, count(can.etablissement) as nbre
			FROM bg_ref_etablissement eta, bg_candidats can
			WHERE   eta.id_inspection=$id_inspection AND can.etablissement=eta.id
			AND can.annee=$annee $andWhere
			GROUP BY eta.id, can.etablissement ORDER BY eta.etablissement ";

    $result = bg_query($lien, $sql, __FILE__, __LINE__);

    $formAnnee = "<form name='form_annee' method='POST' >";
    $formAnnee .= "<table><tr>
				<td><input type='hidden' name='annee' value='$annee' /><input type='hidden' name='id_inspection' value='$id_inspection' />
				<select name='annee' onchange='document.forms.form_annee.submit()'>" . optionsAnnee(2020, $annee) . "</select></td>
				<td><select name='id_serie' onchange='document.forms.form_annee.submit()'>" . optionsReferentiel($lien, 'serie', $id_serie, 'WHERE id <4') . "</select></td>
				</tr></table>";
    $formAnnee .= "</form>";
    // if (in_array($statut, array('Admin', 'Encadrant'))) {
    //     $andTableau = "<th>Bac I</th>";
    // } else {
    //     $andTableau = '';
    // }

    $tableau = "<table class='spip liste'><thead>";
    $tableau .= "<th>Etablissement</th><th>Nbre</th><th class='center' colspan=2>Listes</th>$andTableau";
    $tableau .= "</thead><tbody>";
    $tab = array('id_etablissement', 'etablissement', 'nbre');
    while ($row = mysqli_fetch_array($result)) {
        foreach ($tab as $var) {
            $$var = $row[$var];
        }

        $tableau .= "<tr><td>$etablissement</td><td align='rigth'>$nbre</td>
						<td class='center'><a href='../plugins/fonctions/inc-liste.php?id_etablissement=$id_etablissement&annee=$annee$andLien&type=0'><img src='../plugins/images/imprimer.png' title='imprimer en pdf'/></a></td>
						<td class='center'><a href='../plugins/fonctions/csvfile/inc-csv-liste.php?id_etablissement=$id_etablissement&annee=$annee$andLien&type=0'><img src='../plugins/images/csvimport-24.png' title='imprimer en csv' /></a></td>
						";
        // if (in_array($statut, array('Admin', 'Encadrant'))) {
        //     $tableau .= "<td class='center'><a href='../plugins/fonctions/inc-liste.php?id_etablissement=$id_etablissement&annee=$annee$andLien&type=1'><img src='../plugins/images/imprimer.png' /></a></td>";
        // } else { $tableau .= '';}
        $tableau .= "</tr>";
    }
    $tableau .= "</tbody></table>";
    echo "<h4><center><a href='./?exec=candidats&id_inspection=$id_inspection'>Retour au Menu principal</a></center></h4>";
    echo $formAnnee;
    echo $tableau;
}

//Affichage de l'inspection avec l'id comme paramètre
function selectInspection($lien, $id_inspection)
{
    $sql = "SELECT inspection FROM bg_ref_inspection WHERE id='$id_inspection' ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $row = mysqli_fetch_array($result);
    return $row['inspection'];
}
function centreEtablissement($lien, $id_etablissement)
{
    $sql = "SELECT id_centre FROM bg_ref_etablissement WHERE id='$id_etablissement' ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $row = mysqli_fetch_array($result);
    return $row['id_centre'];
}

function exec_reclamation()
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
                $isEncadrant = true;
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
        $annee = recupAnnee($lien);
    }
    if ($id_type_session == '') {
        $id_type_session = recupSession($lien);
    }
    if (isAutorise(array('Admin', 'Etablissement', 'Informaticien', 'Operateur', 'Encadrant', 'Inspection', 'DExCC'))) {
        //Enregistrer un candidat
        if (isset($_POST['enregistrer'])) {
            $ddn = formater_date($ddn);
            $centre = centreEtablissement($lien, $etablissement);
            if ($serie != 1) {
                $lv2 = 0;
            }

            if ($eps == 2) {
                $atelier1 = $atelier2 = $atelier3 = 0;
            }

            $sql = "SELECT * FROM bg_candidats WHERE annee=$annee AND etablissement=$etablissement AND num_dossier='$num_dossier' LIMIT 0,1";
            $result = bg_query($lien, $sql, __FILE__, __LINE__);
            if (mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_array($result);
                $noms = $row['nom'] . ' ' . $row['prenoms'];
                echo debut_boite_alerte(), "Enregistrement impossible: \nLe candidat $noms possède déjà ce numéro de dossier $num_dossier  ", fin_boite_alerte();
            } else {
                $prenoms = ucwords($prenoms);
                $sql_ins = "INSERT INTO bg_candidats (`annee`, `serie`, `num_dossier`, `annee_bac1`, `jury_bac1`, `num_table_bac1`, `nom`, `prenoms`, `sexe`, `ddn`, `ldn`, `pdn`, `nationalite`, `telephone`, `lv1`, `lv2`, `efa`, `efb`, `eps`, `etat_physique`, `atelier1`, `atelier2`, `atelier3`, `centre`, `type_session`, `etablissement`, `nom_photo`, `login`)
				VALUES ('$annee','$serie','$num_dossier','$annee_bac1','$jury_bac1','$num_table_bac1','$nom','$prenoms','$sexe','$ddn','$ldn','$pdn','$nationalite','$telephone','1','$lv2','$efa','$efb','$eps','$etat_physique','$atelier1','$atelier2','$atelier3','$centre','$type_session','$etablissement','$nom_photo','$login') ";
                bg_query($lien, $sql_ins, __FILE__, __LINE__);
                $sexe = $pdn = $nationalite = $lv1 = $efa = $efb = $eps = 0;
                $etat_physique = 1;
                $nom = $prenoms = $num_table_bac1 = $ddn = $telephone = $jury_bac1 = $ldn = $num_dossier = '';
            }
        }

        //Mettre a jour un candidat
        if (isset($_POST['modifier'])) {
            $atelier3 = 7;
            $sql = "INSERT INTO bg_histo_candidats
			(`id_candidat`, `annee`, `num_dossier`, `num_table`, `serie`, `annee_bac1`, `jury_bac1`, `num_table_bac1`, `nom`, `prenoms`, `sexe`, `ddn`, `ldn`, `pdn`, `nationalite`, `telephone`, `lv1`, `lv2`, `efa`, `efb`, `eps`, `etat_physique`, `atelier1`, `atelier2`, `atelier3`, `centre`,`type_session`, `etablissement`, `nom_photo`, `login`)
			SELECT `id_candidat`, `annee`, `num_dossier`, `num_table`, `serie`, `annee_bac1`, `jury_bac1`, `num_table_bac1`, `nom`, `prenoms`, `sexe`, `ddn`, `ldn`, `pdn`, `nationalite`, `telephone`, `lv1`, `lv2`, `efa`, `efb`, `eps`, `etat_physique`,`atelier1`, `atelier2`, `atelier3`, `centre`, `type_session`, `etablissement`, `nom_photo`, '" . $login . "' FROM bg_candidats WHERE annee=$annee AND id_candidat='$id_candidat' ";
            $result = bg_query($lien, $sql, __FILE__, __LINE__);

            $ddn = formater_date($ddn);
            if ($serie != 1) {
                $lv2 = 0;
            }

// modification envoyée par pape
            if ($eps == 2) {
                $atelier1 = $atelier2 = $atelier3 = 0;
            }

            $centre = centreEtablissement($lien, $etablissement);
            $sql_maj = "UPDATE bg_candidats SET num_dossier='$num_dossier', serie='$serie',annee_bac1='$annee_bac1',jury_bac1='$jury_bac1',
					num_table_bac1='$num_table_bac1',nom='$nom',prenoms='$prenoms',sexe='$sexe',ddn='$ddn',ldn='$ldn',pdn='$pdn',
					nationalite='$nationalite',telephone='$telephone',lv2='$lv2',efa='$efa',efb='$efb',eps='$eps',
					etat_physique='$etat_physique', atelier1='$atelier1', atelier2='$atelier2', atelier3='$atelier3', type_session='$type_session',etablissement='$etablissement', centre='$centre'
					WHERE annee=$annee AND id_candidat='$id_candidat' ";
            bg_query($lien, $sql_maj, __FILE__, __LINE__);
            echo "<script LANGUAGE='JavaScript'>window.location='" . generer_url_ecrire('reclamation') . "&etape=maj&id_candidat=$id_candidat&id_inspection=$id_inspection&annee=$annee';</script>";

        }

        //Supprimer un candidat
        if (isset($_GET['etape']) && $_GET['etape'] == 'supprimer' && $_GET['id_candidat'] != '') {
            $sql = "INSERT INTO bg_suppr_candidats
			(`id_candidat`, `annee`, `num_dossier`, `num_table`, `serie`, `annee_bac1`, `jury_bac1`, `num_table_bac1`, `nom`, `prenoms`, `sexe`, `ddn`, `ldn`, `pdn`, `nationalite`, `telephone`, `lv1`, `lv2`, `efa`, `efb`, `eps`, `etat_physique`, `atelier1`, `atelier2`, `atelier3`, `centre`,`type_session`, `etablissement`, `nom_photo`, `login`)
			SELECT `id_candidat`, `annee`, `num_dossier`, `num_table`, `serie`, `annee_bac1`, `jury_bac1`, `num_table_bac1`, `nom`, `prenoms`, `sexe`, `ddn`, `ldn`, `pdn`, `nationalite`, `telephone`, `lv1`, `lv2`, `efa`, `efb`, `eps`, `etat_physique`,`atelier1`, `atelier2`, `atelier3`, `centre`, `type_session`, `etablissement`, `nom_photo`, '" . $login . "' FROM bg_candidats WHERE annee=$annee AND id_candidat='$id_candidat' ";
            $result = bg_query($lien, $sql, __FILE__, __LINE__);
            $sql_sup = "DELETE FROM bg_candidats WHERE annee=$annee AND id_candidat='$id_candidat' $andStatut ";
            bg_query($lien, $sql_sup, __FILE__, __LINE__);
        }

        echo debut_grand_cadre();
        echo debut_cadre_trait_couleur('', '', '', "GESTION DE RECLAMATION", "", "", false);

/*
echo "<pre>";
print_r($_POST);
echo "</pre>";
 */

        //Tri des candidats - Formulaire
        // if ((isset($_REQUEST['etape']) && ($_GET['etape'] == 'afficher' || $_GET['etape'] == 'modifier' || $_GET['etape'] == 'supprimer')) || isset($_POST['modifier']) || isset($_POST['tri_numero'])) {
        if (!isset($_REQUEST['etape']) || isset($_POST['tri_numero']) || isset($_POST['tri_nom'])) {
            $tri = debut_cadre_enfonce('', '', '', 'Tri des candidats');

            if (isset($_POST['tri_numero']) && $_POST['tri_numero'] != '') {
                $andWhere .= " AND  can.num_table='$tri_numero' ";
            }

            if (isset($_POST['tri_nom']) && $_POST['tri_nom'] != '') {
                $andWhere .= " AND can.nom LIKE '%$tri_nom%' ";
            }
            if (isset($_POST['tri_prenom']) && $_POST['tri_prenom'] != '') {
                $andWhere .= " AND can.prenoms LIKE '%$tri_prenom%' ";
            }
            if ($isInspection) {
                $andWhereEta = " AND id_region='$id_region' ";
            }

            if (!isset($_REQUEST['limit'])) {
                $limit = 0;
            }

            if ($limit < 0) {
                $limit = 0;
            }

            $tri .= "<form name='form_recla' method='POST' ><table>";

            $tri .= "<tr><td><center>N° de Table :<input type='hidden' name='limit' value='$limit' /> <input type='text' name='tri_numero' / size='30'></center></td>
                <td><center>Nom: <input type='text' name='tri_nom' size='30' /></center></td>
                <td><center>Prenom: <input type='text' name='tri_prenom' / size='30'><input size='5' type='submit' value='OK' /></center></td>
                </tr>";
            $tri .= "</table></form>";
            $tri .= fin_cadre_enfonce();
            echo $tri;

            //Affichage des resultats du tri
            $pre = ($limit - MAX_LIGNES);
            if ($isChefEtablissement) {
                $andStatut = " AND can.etablissement='$code_etablissement' ";
            }

            if ($isInspection) {
                $andStatut = $andStatut2;
            }

            $sql_aff1 = "SELECT can.*,res.delib1  FROM bg_candidats can, bg_ref_etablissement eta,bg_resultats res
                 WHERE can.annee='$annee' AND can.etablissement=eta.id AND can.num_table=res.num_table $andWhere $andStatut ";

            $result_aff1 = bg_query($lien, $sql_aff1, __FILE__, __LINE__);
            $total = mysqli_num_rows($result_aff1);
            echo "<p class='center'>";
            if ($limit > 0) {
                echo "<a href=\"javascript:document.forms['form_recla'].limit.value=" . ($limit - MAX_LIGNES) . ";document.forms['form_recla'].submit();\"><<::Précédent </a>";
            }

            $limit2 = ($limit + MAX_LIGNES);
            if ($total < $limit2) {
                $limit2 = $total;
            }

            echo " $total enregistrements au total [$limit .... $limit2] ";
            if ($total > ($limit + MAX_LIGNES)) {
                echo "<a href=\"javascript:document.forms['form_recla'].limit.value=" . ($limit + MAX_LIGNES) . ";document.forms['form_recla'].submit();\"> Suivant::>></a>";
            }

            // echo "<br/><a href='./?exec=reclamation&id_inspection=$id_inspection'>Retour au Menu principal</a>";
            echo "</p>";
            if ($isChefEtablissement) {
                $andStatut = " AND can.etablissement='$code_etablissement' ";
            }

            $sql_aff = "SELECT can.*,eta.id_inspection ,res.delib1 FROM bg_candidats can, bg_ref_etablissement eta,bg_resultats res
                 WHERE can.annee='$annee' AND can.etablissement=eta.id AND can.num_table=res.num_table $andWhere $andStatut
                 ORDER BY nom, prenoms LIMIT $limit, " . MAX_LIGNES;
            $result_aff = bg_query($lien, $sql_aff, __FILE__, __LINE__);

            //echo $sql_aff;
            $tab = array('id_candidat', 'id_inspection', 'num_dossier', 'num_table', 'serie', 'delib1', 'annee_bac1', 'jury_bac1', 'num_table_bac1', 'nom', 'prenoms', 'sexe', 'ddn', 'ldn', 'pdn', 'nationalite', 'telephone', 'lv1', 'lv2', 'efa', 'efb', 'eps', 'etat_physique', 'type_session', 'etablissement', 'nom_photo', 'login', 'maj');
            echo "<table class='spip liste'>";
            $thead = "<th>N°</th><th>N° table</th><th>Noms</th><th>Etablissement</th><th>Série</th><th>Délibération</th>";
            while ($row_aff = mysqli_fetch_array($result_aff)) {
                foreach ($tab as $var) {
                    $$var = $row_aff[$var];
                    //Decommenter la ligne precedente et commenter la ligne suivante pour gerer l'encodage sur la lsite des candidats
                    //                $$var=utf8_encode($row_aff[$var]);
                }
                $sexe = selectReferentiel($lien, 'sexe', $sexe);
                $serie = selectReferentiel($lien, 'serie', $serie);
                $etablissement = selectReferentiel($lien, 'etablissement', $etablissement);
                if ($sexe == 'F') {
                    $civ = 'Mlle';
                } else {
                    $civ = 'M.';
                }

                $tbody .= "<tr><td>$id_candidat</td><td>$num_table</td><td><a href='./?exec=reclamation&etape=maj&id_candidat=$id_candidat&id_inspection=$id_inspection&annee=$annee' title=\"Saisi par $login le $maj\">$civ $nom $prenoms</a></td><td>$etablissement</td><td>$serie</td><td>$delib1</td></tr>";
            }
            echo $thead, $tbody;
            echo "</table>";
        }

        if (isset($_REQUEST['etape']) && ($_GET['etape'] == 'maj') && (!isset($_POST['modifier'])) && !isset($_POST['tri_numero'])) {
            echo debut_gauche();
            echo debut_boite_info(), "<b>N°</b><br/>Numéro Identifiant du Candidat<br/>", fin_boite_info();
            echo debut_boite_info(), "<b>Etablissement</b><br/>Choisissez votre Etablissement dans la liste et vérifiez si elle correspond au centre et à l'inspection affiché", fin_boite_info();
            echo debut_boite_info(), "<b>N° de dossier</b><br/>Entrez le Numéro de dossier correspondand au numéro d'ordre situé sur la fiche de saisie, ce numéro ne doit pas se répéter dans un même établissement", fin_boite_info();
            echo debut_boite_info(), "<b>Nom</b><br/>Le formatage MAJUSCULE est activé sur ce champs, si vous voulez ajouter du texte en minuscule veuillez cliquer sur la case à cocher à droite", fin_boite_info();
            echo debut_boite_info(), "<b>Prénoms</b><br/>Débutez tous les prénoms par une lettre Majuscule", fin_boite_info();
            echo debut_boite_info(), "<b>Date de Naissance</b><br/>Respectez le formatage obligatoire de la date <br/> Ex: <strong>31/12/1950</strong>", fin_boite_info();
            echo debut_boite_info(), "<b>Pays de Naissance /Nationalité</b><br/>Le pays de naissance ne correspond pas forcément à la Nationalité, suivez donc scrupuleusement les données inscrites sur la fiche ", fin_boite_info();
            echo debut_boite_info(), "<b>Téléphone</b><br/>Entrée obligatoire du numéro de téléphone du parent ou tuteur du candidat", fin_boite_info();
            echo debut_boite_info(), "<b>Langue vivante 2</b><br/>Ce champ concerne les candidats de la série A4", fin_boite_info();
            echo debut_boite_info(), "<b>Epr. fac. A/B</b><br/>Ne pas choisir la même matière dans les deux champs ", fin_boite_info();
            echo debut_boite_info(), "<b>EPS</b><br/>'Apte si son Etat Physique est 'Valide' <br/> 'Inapte' dans le cas contraire'", fin_boite_info();
            echo debut_boite_info(), "<b>Choix EPS</b><br/><b>7-L'oral</b> du Sport est Obligatoire pour un candidat apte et est sélectionné par défaut, entrez les reférences des deux autres  disciplines choisies par le candidat<br/> <strong>1-Longueur &nbsp;&nbsp;&nbsp;&nbsp; 2-Poids &nbsp;&nbsp;&nbsp;&nbsp; 3-Résistance <br/> 4-Hauteur  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  5-Vitesse &nbsp;&nbsp;  6-Grimper </strong>", fin_boite_info();
            echo debut_boite_info(), "<b>Session</b><br>Choisir la Session 'Normale' ou 'Malade'", fin_boite_info();

            echo debut_droite();
            $disabled = '';
            if ($isAdmin) {
                $disabledAnnee = '';
            } else {
                $disabledAnnee = 'disabled';
            }
            $photo = '../photos/defaut/sans-visage2.png';
            $whereEta = " AND id_centre!=0 AND si_centre='non'" . $andEtablissementFormulaire2;
            $etat_physique = $type_session = $pdn = $nationalite = 1;
            $atelier1 = $atelier2 = 0;
            $atelier3 = 7;
            $eps_ch1 = $eps_ch2 = $eps_ch3 = 0;
            $inspection = selectInspection($lien, $id_inspection);
            echo "<h4><center><a href='./?exec=reclamation&id_inspection=$id_inspection'>Retour au Menu principal</a></center></h4>";

            $whereEta = ' id_inspection=' . $id_inspection;

            if ($_GET['etape'] == 'maj') {
                $sql_mod = "SELECT * FROM bg_candidats WHERE annee=$annee AND id_candidat=$id_candidat $andStatut ";
                $result_mod = bg_query($lien, $sql_mod, __FILE__, __LINE__);
                $tab = array('id_candidat', 'num_dossier', 'num_table', 'serie', 'annee_bac1', 'jury_bac1', 'num_table_bac1', 'nom', 'prenoms', 'sexe', 'ddn', 'ldn', 'pdn', 'nationalite', 'telephone', 'lv1', 'lv2', 'efa', 'efb', 'eps', 'etat_physique', 'atelier1', 'atelier2', 'atelier3', 'centre', 'jury', 'type_session', 'etablissement', 'nom_photo', 'login', 'maj');
                $row_mod = mysqli_fetch_array($result_mod);
                foreach ($tab as $var) {
                    $$var = $row_mod[$var];
                }
                $whereEta = 'AND  eta.id_inspection=' . $id_inspection;
                $ddn = afficher_date($ddn);
                $whereEta .= " AND id_centre!=0 AND si_centre='non'" . $andEtablissementFormulaire;
                if ($nom_photo != '') {$photo = "../photos/$annee" . '/' . $nom_photo;} else {if ($sexe == 1) {
                    $photo = '../photos/defaut/sans-visage1.png';
                } elseif ($sexe == 2) {
                    $photo = '../photos/defaut/sans-visage2.png';
                }
                }
                if ($eps == 2) {
                    $disabled2 = 'disabled';
                } else {
                    $disabled2 = '';
                }

            }
            $form = "<div class='formulaire_spip'><form method='POST' action='' name='form_saisie' >";
            $form .= "<table>";
            //$form.="<tr><td colspan=3 class='center'><img src='$photo' width=100 onload=\"if(document.forms['form_saisie'].serie.value!=1) {document.forms['form_saisie'].lv2.disabled=true; }\" /></td></tr>";
            $form .= "<tr><td>Année</td><td colspan=2><select name='annee' $disabledAnnee >" . optionsAnnee(2018, $annee) . "</td></tr>";
//        $form.="<tr><td>Année</td><td colspan=2><input type='text' name='annee' value='$annee' disabled></td></tr>";
            $form .= "<tr><td>N°</td><td colspan=2><input type='text' name='id_candidat' value='$id_candidat' disabled></td></tr>";
            $form .= "<tr><td>N° de table</td><td colspan=2><input type='text' name='num_table' id='num_table' value='$num_table' disabled></td></tr>";
            $form .= "<tr><td>Inspection</td><td colspan=2><input size='40' type='text' name='inspection' value='$inspection' disabled></td></tr>";

            $form .= "<tr><td>Etablissement</td><td colspan=2><select name='etablissement'>" . optionsEtablissement($lien, $etablissement, $whereEta) . "</select></td></tr>";
            $form .= "<tr><td>Série</td><td colspan=2><select name='serie' onBlur=\"if(this.value==1) {document.forms['form_saisie'].lv2.disabled=false; }else{document.forms['form_saisie'].lv2.disabled=true;document.forms['form_saisie'].lv2.value=0;}\" onChange=\"if(this.value==1) {document.forms['form_saisie'].lv2.disabled=false; }else{document.forms['form_saisie'].lv2.disabled=true;document.forms['form_saisie'].lv2.value=0;}\">" . optionsReferentiel($lien, 'serie', $serie, 'WHERE id<4 ') . "</select></td></tr>";
            $form .= "<tr><td>N° de dossier</td><td colspan=2><input size='40' type='text' name='num_dossier' id='num_dossier' value='$num_dossier' autofocus required /></td></tr>";

            $form .= "<tr><td colspan=3><fieldset><legend>Identité du candidat</legend></td></tr>";

            if ($_GET['etape'] == 'inserer') {
                $majuscule = " OnkeyUp=\"javascript:this.value=this.value.toUpperCase();\" ";
            } else {
                $majuscule = '';
            }

            //Empecher espace en debut de saisie
            if ($_GET['etape'] == 'inserer') {
                $NoSpace = " OnkeyUp=\"javascript:this.value=this.value.trimStart();\" ";
            } else {
                $first = '';
            }

            $form .= "<tr><td>Nom</td><td colspan=2><input type='text' id='nom' name='nom' value=\"$nom\" $NoSpace required /></td></tr>";
            $form .= "<tr><td>Prénoms</td><td colspan=2><input type='text' id='prenoms' name='prenoms' value=\"$prenoms\" $NoSpace required /></td></tr>";
            $form .= "<tr><td>Sexe</td><td colspan=2><select name='sexe'>" . optionsReferentiel($lien, 'sexe', $sexe) . "</select></td></tr>";
            $form .= "<tr><td>Etat Physique</td><td colspan=2><select name='etat_physique'>" . optionsReferentiel($lien, 'etat_physique', $etat_physique) . "</select></td></tr>";
            $form .= "<tr><td>Date de naissance</td><td colspan=2><input type='text' id='ddn' placeholder='31/12/1990' name='ddn' value='$ddn' pattern='^(0?[1-9]|[12][0-9]|3[01])[\/](0?[1-9]|1[012])[\/]\d{4}$' required /></td></td></tr>";
            $form .= "<tr><td>Lieu de naissance</td><td colspan=2><input type='text' id='ldn' name='ldn' value=\"$ldn\" required /></td></td></tr>";
            $form .= "<tr><td>Pays de naissance</td><td colspan=2><select name='pdn' onBlur=\"document.forms['form_saisie'].nationalite.value=this.value; \">" . optionsReferentiel($lien, 'pays', $pdn) . "</select></td></tr>";
            $form .= "<tr><td>Nationalité</td><td colspan=2><select name='nationalite'>" . optionsReferentiel($lien, 'pays', $nationalite) . "</select></td></tr>";
            $form .= "<tr><td>Téléphone</td><td colspan=2><input type='tel' id='telephone' name='telephone' value=\"$telephone\"></td></td></tr>";
            $form .= "<tr><td colspan=3></fieldset></td></tr>";

            $form .= "<tr><td colspan=3><fieldset><legend>Choix du candidat</legend></td></tr>";
            $form .= "<tr><td>Langue vivante 1</td><td colspan=2><select name='lv1' disabled>" . optionsReferentiel($lien, 'langue_vivante', 3, 'WHERE id=3 ') . "</select></td></tr>";
            $form .= "<tr><td>Langue vivante 2</td><td colspan=2><select name='lv2' $disabled4>" . optionsReferentiel($lien, 'langue_vivante', $lv2, 'WHERE id<3 ') . "</select></td></tr>";
            $form .= "<tr><td>Epr. fac.  A</td><td colspan=2><select name='efa' onBlur=\"if(this.value==lv2.value && serie.value==1 && this.value!=0) {alert('Choix EF invalide'); this.value=0; return false; efa.focus();} \">" . optionsReferentiel($lien, 'epreuve_facultative_a', $efa) . "</select></td></tr>";
            $form .= "<tr><td>Epr. fac. B</td><td colspan=2><select name='efb'>" . optionsReferentiel($lien, 'epreuve_facultative_b', $efb) . "</select></td></tr>";
            $form .= "<tr><td>E. P. S.</td><td colspan=2><select name='eps' onBlur=\"if(this.value==2 || this.value==0){eps_ch3.value=0;atelier3.value=0;atelier1.disabled=atelier2.disabled=atelier3.disabled=true;}else{eps_ch3.value=1;atelier3.value=7;atelier1.disabled=atelier2.disabled=false;} \" onChange=\"if(this.value==2  || this.value==0){eps_ch3.value=0;atelier3.value=0;atelier1.disabled=atelier2.disabled=atelier3.disabled=true;}else{eps_ch3.value=7;atelier3.value=7;atelier1.disabled=atelier2.disabled=false;} \" >" . optionsReferentiel($lien, 'eps', $eps, '', false) . "</select></td></tr>";
            $form .= "<tr><td>Choix EPS</td><td colspan=2><input type='text' name='atelier1' value='$atelier1' $disabled2 size=1 maxlength=1 onBlur=\"eps_ch1.value=this.value; if((this.value!=0 && isNaN(parseInt(this.value))) || (this.value!=0 && (this.value==atelier2.value || this.value==atelier3.value))) {this.value=0; alert('Saisissez une valeur valide'); return false; atelier1.focus(); this.value=0;} \" />
				<input type='text' name='atelier2' value='$atelier2' $disabled2 size=1 maxlength=1 onBlur=\"eps_ch2.value=this.value; if((this.value!=0 && isNaN(parseInt(this.value))) || (this.value!=0 && (this.value==atelier1.value || this.value==atelier3.value))) {this.value=0; alert('Saisissez une valeur valide'); return false; atelier2.focus(); this.value=0;} \" />
				<input type='text' name='atelier3' value='$atelier3' disabled size=1 maxlength=1 onBlur=\"eps_ch3.value=this.value; if((this.value!=0 && isNaN(parseInt(this.value))) || (this.value!=0 && (this.value==atelier1.value || this.value==atelier2.value))) {this.value=0; alert('Saisissez une valeur valide'); return false; atelier3.focus(); this.value=0;} \" /></td></tr>";
            $form .= "<tr><td></td><td colspan=2><select name='eps_ch1' disabled >" . optionsReferentiel($lien, 'atelier', $atelier1) . "</select>";
            $form .= "<select name='eps_ch2' disabled >" . optionsReferentiel($lien, 'atelier', $atelier2) . "</select>";
            $form .= "<select name='eps_ch3' disabled >" . optionsReferentiel($lien, 'atelier', $atelier3) . "</select></td></tr>";

            $form .= "<tr><td colspan=3></fieldset></td></tr>";

            $form .= "<tr><td colspan=3><fieldset><legend>Autres</legend></td></tr>";
//        $form.="<tr><td>Photo</td><td colspan=2><input type='file' name='nom_photo' value='$nom_photo'></td></td></tr>";
            if ($isChefEtablissement) {
                $form .= "<tr><td colspan=3><input type='hidden' name='type_session' value=1 /></td></tr>";
            } else {
                $form .= "<tr><td>Session</td><td colspan=2><select name='type_session'>" . optionsReferentiel($lien, 'type_session', $type_session) . "</select></td></tr>";
            }

            $form .= "<tr><td colspan=3></fieldset></td></tr>";

            $form .= "<tr><td colspan=3><input type='hidden' name='id_prefecture' value='$id_prefecture' /><input type='hidden' name='MAX_FILE_SIZE' value='2000000'></td></tr>";

            $js = "onClick=\"if(document.forms['form_saisie'].etablissement.value==0
						|| document.forms['form_saisie'].serie.value==0
						|| document.forms['form_saisie'].sexe.value==0
						|| document.forms['form_saisie'].pdn.value==0
						|| document.forms['form_saisie'].nationalite.value==0
						|| document.forms['form_saisie'].eps.value==0
						|| document.forms['form_saisie'].type_session.value==0
						|| (document.forms['form_saisie'].serie.value==1 && document.forms['form_saisie'].lv2.value==0)
						|| (document.forms['form_saisie'].eps.value==1 && (document.forms['form_saisie'].atelier1.value==0 || document.forms['form_saisie'].atelier2.value==0 || document.forms['form_saisie'].atelier3.value==0))
						)
			{alert('Remplir les champs obligatoires'); return false; }
            else {alert('Opération effectuée avec succès');return true;}\"; ";

            if ($isAdmin) {
                echo '<script type="text/javascript">',
                'function getData(){',
                'var num_table = document.getElementById("num_table").value;',
                '//garder la valeur dans le local storage
            localStorage.setItem("txtValue", num_table);',
                'window.open("./?exec=contentieux&tache=modifier_notes")',
                '};',
                    '</script>'
                ;
            }

            echo '<script type="text/javascript">',
            'function getDataForReleve(){',
            'var num_table = document.getElementById("num_table").value;',
            '//garder la valeur dans le local storage
            localStorage.setItem("txtValue", num_table);',
            'window.open("./?exec=utilitaires&tache=releve_notes")',
            '};',
                '</script>'
            ;
            echo '<script type="text/javascript">',
            'function getDataForDuplicata(){',
            'var num_table = document.getElementById("num_table").value;',
            '//garder la valeur dans le local storage
            localStorage.setItem("txtValue", num_table);',
            'window.open("./?exec=utilitaires&tache=duplicata_notes")',
            '};',
                '</script>'
            ;

            if ($_GET['etape'] == 'maj') {
                if ($isInspection) {
                    $form .= "<tr><td></td><td></td>";
                } else {

                    $form .= "<tr><td colspan=3><fieldset><legend>Actions</legend></td></tr>";
                    $form .= "<tr><td><center><input  type='submit' name='modifier' style='width: 14em;  height: 2em;' value='Modifier' onClick=\"window.location='" . generer_url_ecrire('reclamation') . "&etape=maj&id_candidat=$id_candidat&id_inspection=$id_inspection&annee=$annee';\"/></center></td>";
                    // $form .= "<td><center><input type='button' value='Supprimer' name='supprimer' style='width: 14em;  height: 2em;' onClick=\"if(confirm('Souhaitez-vous vraiment supprimer ce candidat?')) window.location='" . generer_url_ecrire('reclamation') . "&id_inspection=$id_inspection&etape=supprimer&id_candidat=$id_candidat&annee=$annee';\" /></center></td>";
                    $form .= "<td><center><input type='button' name='quitter' style='width: 14em;  height: 2em;' value='Quitter' onClick=\"window.location='" . generer_url_ecrire('reclamation') . "';\" /></center></td></tr>";

                    $form .= "<tr><td colspan=3><fieldset><legend>Traitement</legend></td></tr>";
                    if ($isAdmin) {
                        $form .= "<tr><td ><center><input  type='submit' name='modifier_note' style='width: 14em;  height: 2em;' value='Modifier Note' onclick='getData()' /></center></td>";
                    } else {
                        $form .= "</tr>";
                    }
                    $form .= "<td ><center><input  type='submit' name='imprimer_releve' style='width: 14em;  height: 2em;' value='Imprimer releve' onclick='getDataForReleve()' /></center></td>
					<td ><center><input  type='submit' name='imprimer_duplicata' style='width: 14em;  height: 2em;' value='Imprimer duplicata' onclick='getDataForDuplicata()' /></center></td>
                    </tr>";

                }
            }
            $form .= "</table></form></div>";
            $form .= "<script LANGUAGE='JavaScript'> document.forms.form_saisie.num_dossier.focus();</script>";
            echo $form;

            //Affichage des historiques s'il en existe
            if ($_GET['etape'] == 'maj') {
                echo "<div class='spip_doc_descriptif spip_code'><table>";
                $sql = "SELECT nom, prenoms, ser.serie, eta.etablissement, num_dossier, num_table, ddn, ldn, pay.pays, spo.eps, can.maj
					FROM bg_ref_serie ser, bg_ref_etablissement eta, bg_histo_candidats can
					LEFT JOIN bg_ref_pays pay ON pay.id=can.pdn
					LEFT JOIN bg_ref_eps spo ON spo.id=can.eps
					WHERE can.annee=$annee AND can.id_candidat='$id_candidat' AND can.etablissement=eta.id AND can.serie=ser.id
					ORDER BY maj desc";
                $result = bg_query($lien, $sql, __FILE__, __LINE__);
                $tab = array('nom', 'prenoms', 'serie', 'etablissement', 'num_dossier', 'num_table', 'ddn', 'ldn', 'pays', 'eps', 'maj');
                while ($row = mysqli_fetch_array($result)) {
                    foreach ($tab as $val) {
                        $$val = $row[$val];
                    }

                    echo "<tr><td>$nom $prenoms</td><td>" . afficher_date($ddn) . " $ldn / $pays</td><td>$etablissement</td><td>$serie</td><td>$eps</td><td>$num_dossier</td><td>$maj</td></tr>";
                }
                echo "</table></div>";

            }
            echo fin_gauche();

        }

        echo fin_cadre_trait_couleur();
        echo fin_grand_cadre(), fin_page();
    }
}

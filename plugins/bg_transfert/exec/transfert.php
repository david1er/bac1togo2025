<?php
if (!defined('_ECRIRE_INC_VERSION')) {
    return;
}

include_spip('inc/presentation'); // alleger les inclusions avec un inc/presentation_mini
include '../plugins/fonctions/generales.php';

setlocale(LC_TIME, "fr_FR");
define('MAX_LIGNES', 100);

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

    // $sql="SELECT eta.etablissement, eta.id as id_etablissement, count(*) as nbre
    //         FROM bg_ref_etablissement eta, bg_ref_ville vil, bg_ref_prefecture pre, bg_candidats can
    //         WHERE pre.id=vil.id_prefecture AND vil.id=eta.id_ville AND pre.id=$id_prefecture AND can.etablissement=eta.id
    //         AND can.annee=$annee AND pre.id=$id_prefecture $andWhere
    //         GROUP BY eta.id ORDER BY eta.etablissement ";
    $sql = "SELECT eta.etablissement, eta.id as id_etablissement, count(can.etablissement) as nbre
			FROM bg_ref_etablissement eta, bg_candidats can
			WHERE   eta.id_inspection=$id_inspection AND can.etablissement=eta.id
			AND can.annee=$annee $andWhere
			GROUP BY eta.id, can.etablissement ORDER BY eta.etablissement ";

    $result = bg_query($lien, $sql, __FILE__, __LINE__);

    $formAnnee = "<form name='form_annee' method='POST' >";
    $formAnnee .= "<table><tr>
				<td><input type='hidden' name='annee' value='$annee' /><input type='hidden' name='id_inspection' value='$id_inspection' />
				<select name='annee' onchange='document.forms.form_annee.submit()'>" . optionsAnnee(2020, $annee) . "</select></center></td>
				<td><select name='id_serie' onchange='document.forms.form_annee.submit()'>" . optionsReferentiel($lien, 'serie', $id_serie, 'WHERE id <4') . "</select></center></td>
				</tr></table>";
    $formAnnee .= "</form>";
    //if(in_array($statut,array('Admin','Encadrant'))) $andTableau="<th>Bac I</th>";
    //else $andTableau='';
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
        //if(in_array($statut,array('Admin','Encadrant'))) {
        //    $tableau.="<td class='center'><a href='../plugins/fonctions/inc-liste.php?id_etablissement=$id_etablissement&annee=$annee$andLien&type=1'><img src='../plugins/images/imprimer.png' /></a></td>";
        //}else {$tableau.='';}
        $tableau .= "</tr>";
    }
    $tableau .= "</tbody></table>";
    echo "<h4><center><a href='./?exec=candidats&id_inspection=$id_inspection'>Retour au Menu principal</a></center></h4>";
    echo $formAnnee;
    echo $tableau;
}

//Mise a jour du 21/11/2022
//Liste des centres en fonction d'une Inspection
function centreInspections($lien, $selected = '', $andWhere = '', $titre = true, $autreTable = '', $condJointure = '', $entete = '')
{
    $sql = "SELECT eta.id,eta.etablissement, eta.si_centre, eta.id_centre,reg.region
		  FROM bg_ref_etablissement eta,bg_ref_inspection ins,bg_ref_region reg
		  WHERE reg.id=ins.id_region AND ins.id=eta.id_inspection AND si_centre='oui' AND
		  $andWhere $condJointure
		  GROUP BY eta.etablissement";

//etablissement, si_centre, id_centre,region,eta.id
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    if ($entete == '') {
        $entete = 'Etablissement';
    }

    if ($titre == true) {
        $options .= "<option value=0>-=[$entete]=-</option>";
    } else {
        $options .= '';
    }

    $i = 1;
    while ($row = mysqli_fetch_array($result)) {
        $region = $row['region'];

        // $id_centre=$row['id'];

        //  echo($id_centre);

        $affi = ucfirst($row[1]);
        if (strlen($affi) > 25) {
            $affi2 = '';
            $tTmp = explode(' ', $affi);
            foreach ($tTmp as $mot) {
                if (strlen($mot) > 6) {
                    $mot = substr($mot, 0, 6) . '.';
                }
                $mot .= ' ';
                $affi2 .= $mot;
            }
            $affi = $affi2;
        }
        if ($i == 1 || $region != $old_region) {
            $options .= "<optgroup label='$region'>";
        }

        if ($selected == $row[0]) {
            $options .= "<option value=$row[0] selected>" . $affi . "</option>";
        } else {
            $options .= "<option value=$row[0]>" . $affi . "</option>";
        }
        $old_region = $region;
        if ($i > 1 && $region != $old_region) {
            $options .= "</optgroup>";
        }

        $i++;
    }
    return $options;
}

// function centreEts($lien, $id_etablissement)
// {
//     $sql10 = "SELECT etablissement, si_centre, id_centre FROM bg_ref_etablissement WHERE id=$id_etablissement";
//     $result10 = bg_query($lien, $sql10, __FILE__, __FILE__);
//     $sol = mysqli_fetch_array($result10);
//     $id_centre = $sol['id_centre'];
//     $etab = $sol['etablissement'];
//     // var_dump($sol['si_centre']);

//     if ($sol['si_centre'] == 'non') {
//         $sql11 = "SELECT etablissement FROM bg_ref_etablissement WHERE id=$id_centre";
//         $result11 = bg_query($lien, $sql11, __FILE__, __FILE__);
//         $sol11 = mysqli_fetch_array($result11);
//         $centre = ($sol11['etablissement']);
//         //  var_dump($centre);
//     } else if ($sol['si_centre'] == 'oui') {
//         $centre = ($sol['etablissement']);
//     }
//     return $centre;
// }
function etablissementAndCentre($lien, $id_inspection)
{
    $sql = "SELECT eta.etablissement,eta.id
		  FROM bg_ref_etablissement eta,bg_ref_inspection ins,bg_ref_region reg
		  WHERE reg.id=ins.id_region AND id_centre!=0 AND eta.id_inspection=$id_inspection
		  GROUP BY eta.id_centre,eta.etablissement";

    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $tab = array('etablissement', 'id');
    if (mysqli_num_rows($result) > 0) {

        if ($_REQUEST['exec'] == 'candidats') {
            echo "<center><a href='./?exec=candidats'>Retour au Menu principal</a></center>";
        }

        echo "<h2><center>Liste des Etablissements de l'inspection : " . selectInspection($lien, $id_inspection) . "<center></h2>",
        debut_boite_info(), "<table>";
        echo "<th>Etablissement</th><th>Centre d'écrit</th>";
        while ($row = mysqli_fetch_array($result)) {

            foreach ($tab as $val) {
                $$val = addslashes($row[$val]);
            }

            echo "<tr><td>$etablissement </td><td><strong>" . centreEts($lien, $id) . "</strong</td></tr>";

        }
        echo "</table>", fin_boite_info();
    } else {
        echo "<h2><center>~~~~Aucun Etablissement trouvé~~~~</center></h2></br>";
    }

}

//Mise a jour du 21/11/2022
//Liste des Etablissements en Fonction du centre
function etablissementCentre($lien, $selected = '', $andWhere = '', $titre = true, $autreTable = '', $condJointure = '', $entete = '')
{
    $sql = "SELECT *
		  FROM bg_ref_etablissement eta,bg_ref_inspection ins,bg_ref_region reg
		  WHERE reg.id=ins.id_region AND eta.si_centre='non' AND
		  $andWhere $condJointure
		  GROUP BY eta.etablissement";

    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    if ($entete == '') {
        $entete = 'Etablissement => (Centre) ';
    }

    if ($titre == true) {
        $options .= "<option value=0>-=[$entete]=-</option>";
    } else {
        $options .= '';
    }

    $i = 1;
    while ($row = mysqli_fetch_array($result)) {
        $region = $row['region'];
        $id_etablissement = $row[0];

        $affi = ucfirst($row[1]);
        $affi = $affi . '     =>  (' . centreEts($lien, $id_etablissement) . ')';
        if (strlen($affi) > 60) {
            $affi2 = '';
            $tTmp = explode(' ', $affi);
            foreach ($tTmp as $mot) {
                if (strlen($mot) > 6) {
                    $mot = substr($mot, 0, 6) . '.';
                }
                $mot .= ' ';
                $affi2 .= $mot;
            }
            $affi = $affi2;
        }
        //    if($i==1 || $region!=$old_region)     $options.="<optgroup label='$region'>";
        if ($selected == $row[0]) {
            $options .= "<option value=$row[0] selected>" . $affi . "</option>";
        } else {
            $options .= "<option value=$row[0]>" . $affi . "</option>";
        }
        $old_region = $region;
        //if($i>1 && $region!=$old_region)    $options.="</optgroup>";
        $i++;
    }
    return $options;
}

function centreEtablissement($lien, $id_etablissement)
{
    $sql = "SELECT id_centre FROM bg_ref_etablissement WHERE id='$id_etablissement' ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $row = mysqli_fetch_array($result);
    return $row['id_centre'];
}

function selectCentreFromEtablissement($id_etablissement)
{
    /* $sql="SELECT etablissement FROM bg_ref_etablissement WHERE id_centre='$id_etablissement' ";
    $result=bg_query($lien,$sql,__FILE__,__LINE__);
    $row=mysqli_fetch_array($result);
    return $row['etablissement']; */
    //$id_etablissement=7;
    $tBddConf = getBddConf($conf = '');
    $lien = mysqli_connect($tBddConf['host'], $tBddConf['user'], $tBddConf['pass'], $tBddConf['bdd']);

    $sql10 = "SELECT etablissement, si_centre, id_centre FROM bg_ref_etablissement WHERE id=$id_etablissement";
    $result10 = bg_query($lien, $sql10, __FILE__, __LINE__);
    $sol = mysqli_fetch_array($result10);
    $id_centre = $sol['id_centre'];
    $etab = $sol['etablissement'];
    var_dump($sol['si_centre']);

    if ($sol['si_centre'] == 'non') {
        $sql11 = "SELECT etablissement FROM bg_ref_etablissement WHERE id=$id_centre";
        $result11 = bg_query($lien, $sql11, __FILE__, __LINE__);
        $sol11 = mysqli_fetch_array($result11);
        $centre = ($sol11['etablissement']);
        var_dump($centre);
    } else if ($sol['si_centre'] == 'oui') {
        $centre = ($sol['etablissement']);

    }
    return $centre;

}

// Mise a jour du 21/11/2022
//Affichage de l'inspection avec l'id comme paramètre
function selectInspection($lien, $id_inspection)
{
    $sql = "SELECT inspection FROM bg_ref_inspection WHERE id='$id_inspection' ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $row = mysqli_fetch_array($result);
    return $row['inspection'];
}

// Mise a jour du 01/12/2022
//Affichage de l'id de l'inspection à partir du nom de l'établissement
function selectInspectionByEtablissement($lien, $etablissement)
{
    $sql = "SELECT id_inspection FROM bg_ref_etablissement WHERE id=$etablissement ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $row = mysqli_fetch_array($result);
    return $row['id_inspection'];
}
// Mise a jour du 21/11/2022
//Affichage de l'inspection avec l'id comme paramètre

function exec_transfert()
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
        //$annee = date("Y");
        $annee = recupAnnee($lien);
    }

    if (isAutorise(array('Admin', 'Informaticien', 'Encadrant', 'Inspection'))) {
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
        echo debut_cadre_trait_couleur('', '', '', "GESTION DE TRANSFERT DE CANDIDATS", "", "", false);

        if (!isset($_REQUEST['id_inspection']) && (!isset($_REQUEST['etape']))) {

            if (!isset($_REQUEST['id_inspection']) && (!isset($_REQUEST['etape']))) {
                if ($isEncadrant) {
                    listeChoix($lien, $andWhereRegion, 'transfert');
                } elseif ($isInspection) {
                    listeChoix($lien, $andWhereInspection, 'transfert');
                } else {listeChoix($lien, '', 'transfert');}

            }
        }

        if (isset($_REQUEST['id_inspection']) && !isset($_REQUEST['etape'])) {
            echo "<div class='onglets_simple clearfix'><ul>";

            echo "<li><a href='./?exec=transfert&etape=transfer&id_inspection=$id_inspection' class='ajax'>Transfert Etablissement</a></li>";
            echo "<li><a href='./?exec=transfert&etape=transfert&id_inspection=$id_inspection' class='ajax'>Transfert Groupé =>Etablissement</a></li>";
            echo "<li><a href='./?exec=transfert&etape=transfertcentre&id_inspection=$id_inspection' class='ajax'>Transfert Groupé => Centre</a></li>";

            echo "<li><a href='./?exec=transfert&etape=doc&id_inspection=$id_inspection' class='ajax'>Guide</a></li>";
            echo "<li><a href='./?exec=transfert&action=logout&logout=prive' class='ajax'>Se déconnecter</a></li>";
            echo "</ul>
		</div>";
        }

        if (isset($_REQUEST['etape']) && $_REQUEST['etape'] == 'transfer') {
            if ($isEncadrant) {
                $whereEta .= " AND ins.id=eta.id_inspection AND ins.id_region=$id_region";
            }if ($isInspection) {
                $whereEta .= " AND ins.id=eta.id_inspection AND ins.id_region=$id_region AND id_inspection=$id_inspection";
            }

            $whereEta .= " AND eta.si_centre='non'";
            echo "<h4><center><a href='./?exec=transfert&id_inspection=$id_inspection'>Retour au Menu principal</a></center></h4>";

            echo "<form method='POST' action=''>
                        <h2>TRANSFERER LES CANDIDATS D'UN ETABLISSEMENT A UN AUTRE </h2>
                        <p>
                            <label>Etablissement source &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: &nbsp;&nbsp;</label>


                  <select name='etab_source'>" . optionsEtablissement($lien, $etablissement, $whereEta, true, ',bg_ref_inspection ins ') . "</select>
            </p>

                        <p>
                            <label>Etablissement destination &nbsp;: &nbsp;</label>
                            <select name='etab_destination'>" . optionsEtablissement($lien, $etablissement, $whereEta, true, ',bg_ref_inspection ins ') . "</select>
                        </p>

                        <center>
                        <input type='submit' class='spip' value='Candidats Etab. source' name='view_etab_source'/>
                        <input type='submit' class='spip' value='Candidats Etab. destination' name='view_etab_destination'/>
                        <input type='submit' class='spip' value='Transferer' name='transferer'/>
                        </center>
                    </form>";
        }

        if ((isset($_POST['view_etab_source'])) && ($_POST['etab_source'] > 0)) {
            $etab_source = $_POST['etab_source'];
            $etab_destination = $_POST['etab_destination'];

            $sql_aff_source = "SELECT * FROM bg_candidats can, bg_ref_etablissement eta, bg_ref_serie ser, bg_ref_sexe sex
		                 WHERE can.etablissement=eta.id AND can.serie=ser.id AND can.sexe=sex.id AND can.etablissement = $etab_source
						 ORDER BY can.serie ASC, can.nom ";
            $result_source1 = bg_query($lien, $sql_aff_source, __FILE__, __LINE__);

            if (mysqli_num_rows($result_source1) > 0) {
                $tableau = "</br><h2 align='center'>LISTE DES CANDIDATS DE L'ETABLISSEMENT SOURCE </br></br> Total: " . mysqli_num_rows($result_source1) . " CANDIDATS </h2> <table class='spip liste'><thead>";
                $tableau .= "<th>Nom</th><th>Prénoms</th><th>Sexe</th><th>Serie</th><th>Etablissement</th>";
                $tableau .= "</thead><tbody>";
                $tab = array('nom', 'prenoms', 'sexe', 'serie', 'etablissement');
                while ($row = mysqli_fetch_array($result_source1)) {
                    foreach ($tab as $var) {
                        $$var = $row[$var];
                    }

                    $tableau .= "<tr><td>$nom</td><td align='rigth'>$prenoms</td><td align='rigth'>$sexe</td><td align='rigth'>$serie</td><td align='rigth'>$etablissement</td>
                						</tr>";
                }
                $tableau .= "</tbody></table>";
                echo $formAnnee;
                echo $tableau;
            } else {
                echo "<h2></br><center>~~~~Aucun Candidats trouvé pour l'etablissement source ~~~~</center></h2></br>";
            }

        }

        if ((isset($_POST['view_etab_destination'])) && ($_POST['etab_destination'] > 0)) {
            $etab_source = $_POST['etab_source'];
            $etab_destination = $_POST['etab_destination'];

            $sql_aff_dest = "SELECT * FROM bg_candidats can, bg_ref_etablissement eta, bg_ref_serie ser, bg_ref_sexe sex
		WHERE can.etablissement=eta.id AND can.serie=ser.id AND can.sexe=sex.id AND can.etablissement = $etab_destination
		ORDER BY can.serie ASC, can.nom ";
            $result_dest1 = bg_query($lien, $sql_aff_dest, __FILE__, __LINE__);

            if (mysqli_num_rows($result_dest1) > 0) {
                $tableau = "</br><h2 align='center'>LISTE DES CANDIDATS DE L'ETABLISSEMENT DESTINATION </br></br> Total: " . mysqli_num_rows($result_dest1) . " CANDIDATS </h2> <table class='spip liste'><thead>";
                $tableau .= "<th>Nom</th><th>Prénoms</th><th>Sexe</th><th>Serie</th><th>Etablissement</th>";
                $tableau .= "</thead><tbody>";
                $tab = array('nom', 'prenoms', 'sexe', 'serie', 'etablissement');
                while ($row = mysqli_fetch_array($result_dest1)) {
                    foreach ($tab as $var) {
                        $$var = $row[$var];
                    }

                    $tableau .= "<tr><td>$nom</td><td align='rigth'>$prenoms</td><td align='rigth'>$sexe</td><td align='rigth'>$serie</td><td align='rigth'>$etablissement</td>
                						</tr>";
                }
                $tableau .= "</tbody></table>";
                echo $formAnnee;
                echo $tableau;
            } else {
                echo "<h2></br><center>~~~~Aucun Candidats trouvé pour l'etablissement destination ~~~~</center></h2></br>";
            }

        }

        if (isset($_POST['transferer'])) {
            $etab_source = $_POST['etab_source'];
            $etab_destination = $_POST['etab_destination'];

            $sql_can_source = "SELECT * FROM bg_candidats can, bg_ref_etablissement eta, bg_ref_serie ser, bg_ref_sexe sex
		WHERE can.etablissement=eta.id AND can.serie=ser.id AND can.sexe=sex.id AND can.etablissement = $etab_source
		ORDER BY can.nom";
            $result_can_source = bg_query($lien, $sql_can_source, __FILE__, __LINE__);

            while ($row1 = mysqli_fetch_array($result_can_source)) {
                $tCandidats[] = $row1['id_candidat'];
            }

            foreach ($tCandidats as $candidat) {
                if ($etab_destination > 0) {
                    $sql = "UPDATE bg_candidats SET etablissement='$etab_destination' WHERE id_candidat=$candidat";
                    bg_query($lien, $sql, __FILE__, __LINE__);
                }
            }

            //Debut Affichage des candidats de l'etablissement destination apres transfert
            $sql_aff_dest = "SELECT * FROM bg_candidats can, bg_ref_etablissement eta, bg_ref_serie ser, bg_ref_sexe sex
		WHERE can.etablissement=eta.id AND can.serie=ser.id AND can.sexe=sex.id AND can.etablissement = $etab_destination
		ORDER BY can.serie ASC, can.nom ";
            $result_dest1 = bg_query($lien, $sql_aff_dest, __FILE__, __LINE__);

            if (mysqli_num_rows($result_dest1) > 0) {
                $tableau = "</br><h2 align='center'>LISTE DES CANDIDATS DE L'ETABLISSEMENT DESTINATION </br></br> Total: " . mysqli_num_rows($result_dest1) . " CANDIDATS </h2> <table class='spip liste'><thead>";
                $tableau .= "<th>Nom</th><th>Prénoms</th><th>Sexe</th><th>Serie</th><th>Etablissement</th>";
                $tableau .= "</thead><tbody>";
                $tab = array('nom', 'prenoms', 'sexe', 'serie', 'etablissement');
                while ($row = mysqli_fetch_array($result_dest1)) {
                    foreach ($tab as $var) {
                        $$var = $row[$var];
                    }

                    $tableau .= "<tr><td>$nom</td><td align='rigth'>$prenoms</td><td align='rigth'>$sexe</td><td align='rigth'>$serie</td><td align='rigth'>$etablissement</td>
                						</tr>";
                }
                $tableau .= "</tbody></table>";
                echo $formAnnee;
                echo $tableau;
            } else {
                echo "<h2></br><center>~~~~Aucun Candidats trouvé pour l'etablissement destination ~~~~</center></h2></br>";
            }

//Fin Affichage des candidats de l'etablissement destination apres transfert

        }

        if (isset($_REQUEST['etape']) && $_REQUEST['etape'] == 'transfert') {

            $tri = debut_cadre_enfonce('', '', '', 'Transfert de candidats Vers un Autre Etablissement');
            // if (isset($_POST['tri_annee_e']) && $_POST['tri_annee_e'] > 0) {
            //     $annee = $tri_annee_e;
            // }
            if (isset($_POST['tri_serie_e']) && $_POST['tri_serie_e'] > 0) {
                $andWhere .= " AND can.serie=$tri_serie_e ";
            }
            if (isset($_POST['tri_centre_e']) && $_POST['tri_centre_e'] > 0) {$andWhere .= " AND can.centre=$tri_centre_e ";
                $andWhereEta = "AND eta.id_centre=$tri_centre_e";}
            if (isset($_POST['tri_inspection_e']) && $_POST['tri_inspection_e'] > 0) {$andWhere .= " AND eta.id_inspection=$tri_inspection_e ";
                $andWhereEta .= " AND eta.id_inspection=$tri_inspection_e ";
                $andWhereCen .= " AND eta.si_centre='oui' AND ins.id=eta.id_inspection AND eta.id_inspection=$tri_inspection_e ";}
            if (isset($_POST['tri_region_e']) && $_POST['tri_region_e'] > 0) {$andWhere .= " AND ins.id_region=$tri_region_e ";
                $andWhereRegion .= "WHERE id_region=$tri_region_e";
                $andWhereEta .= " AND ins.id_region=$tri_region_e AND ins.id=eta.id_inspection";
                $andWhereCen .= "AND si_centre='oui' AND ins.id=eta.id_inspection AND ins.id_region=$tri_region_e";}
            if (isset($_POST['tri_etablissement_e']) && $_POST['tri_etablissement_e'] > 0) {$andWhere .= " AND can.etablissement=$tri_etablissement_e ";
                $andWhereEta .= " ";}

            if (isset($_POST['tri_serie_d']) && $_POST['tri_serie_d'] > 0) {
                // $andWhere .= " AND can.serie=$tri_serie_d ";
                $andWhere .= " ";
            }
            if (isset($_POST['tri_centre_d']) && $_POST['tri_centre_d'] > 0) {
                //  $andWhere .= " AND can.centre=$tri_centre_d ";
                $andWhere .= " ";
                $andWhereEta_d = "AND eta.id_centre=$tri_centre_d";}
            if (isset($_POST['tri_inspection_d']) && $_POST['tri_inspection_d'] > 0) {
                // $andWhere .= " AND eta.id_inspection=$tri_inspection_d ";
                $andWhere .= "  ";
                $andWhereEta_d .= " AND eta.id_inspection=$tri_inspection_d ";
                $andWhereCen_d .= " AND eta.si_centre='oui' AND ins.id=eta.id_inspection AND eta.id_inspection=$tri_inspection_d ";}
            if (isset($_POST['tri_region_d']) && $_POST['tri_region_d'] > 0) {
                // $andWhere .= " AND ins.id_region=$tri_region_d ";
                $andWhere .= " ";
                $andWhereRegion_d .= "WHERE id_region=$tri_region_d";
                $andWhereEta_d .= " AND ins.id_region=$tri_region_d AND ins.id=eta.id_inspection";
                $andWhereCen_d .= "AND si_centre='oui' AND ins.id=eta.id_inspection AND ins.id_region=$tri_region_d";}
            if (isset($_POST['tri_etablissement_d']) && $_POST['tri_etablissement_d'] > 0) {
                //$andWhere .= " AND can.etablissement=$tri_etablissement_d ";
                $andWhere .= "  ";
                $andWhereEta_d .= " ";}

            if ($isInspection) {
                $andWhereRegion = " WHERE id_region='$id_region' AND id=$id_inspection";
                $andWhereRegion_d = " WHERE id_region='$id_region' AND id=$id_inspection";
                $andWhereEta = " AND reg.id='$id_region'AND ins.id=$id_inspection AND si_centre='non'";
                $andWhereEta_d = " AND reg.id='$id_region' AND ins.id=$id_inspection AND si_centre='non'";
                $andWhereCen = " AND reg.id='$id_region'AND ins.id=$id_inspection AND si_centre='oui'";
                $andWhereCen_d = " AND reg.id='$id_region' AND ins.id=$id_inspection AND si_centre='oui'";
            }
            if ($isEncadrant) {
                $andWhereRegion = " WHERE id_region=$id_region ";
                $andWhereRegion_d = " WHERE id_region=$id_region ";
                $andWhereEta = " AND reg.id='$id_region'AND  si_centre='non'";
                $andWhereEta_d = " AND reg.id='$id_region' AND si_centre='non'";
                $andWhereCen = " AND reg.id='$id_region' AND si_centre='oui'";
                $andWhereCen_d = " AND reg.id='$id_region' AND si_centre='oui'";
            }

            if (!isset($_REQUEST['limit'])) {
                $limit = 0;
            }

            if ($limit < 0) {
                $limit = 0;
            }
            echo "<form name='form_tri' method='POST' >";
            $controle_ets_js = "onclick=\"if(tri_etablissement_d.value!=0) return true; else {alert('Veuillez choisir l'établissement de destination'); return false;}\"";

            $tri .= "<table>";

            $tri .= "<tr><td><input type='hidden' name='limit' value='$limit' /><input type='hidden' name='annee' value='$annee' /><input type='hidden' name='id_prefecture' value='$id_prefecture' /></td>
			<td></td></tr>";
            $tri .= " <tr><td><center> <strong> Expéditeur </strong></center></td> <td > <center><strong>Destinataire </strong></center> <td></tr>";
            if ($isEncadrant) {
                $tri .= "<tr><td><center><select style='width:50%' name='tri_region_e' onchange='document.forms.form_tri.submit()'>" . optionsReferentiel($lien, 'region', $tri_region_e, $WhereRegion) . "</select></center></td>
    <td><center><select style='width:50%' name='tri_region_d' onchange='document.forms.form_tri.submit()'>" . optionsReferentiel($lien, 'region', $tri_region_d, $WhereRegion) . "</select></center></td>
      </tr>";
            }

            $tri .= "<tr><td><center><select style='width:50%' name='tri_inspection_e' onchange='document.forms.form_tri.submit()'>" . optionsReferentiel($lien, 'inspection', $tri_inspection_e, $andWhereRegion) . "</select></center></td>
<td><center><select style='width:50%' name='tri_inspection_d' onchange='document.forms.form_tri.submit()'>" . optionsReferentiel($lien, 'inspection', $tri_inspection_d, $andWhereRegion_d) . "</select></center></td>
 </tr>";
            $tri .= "<tr><td><center><select style='width:50%' name='tri_centre_e' onchange='document.forms.form_tri.submit()'>" . optionsEtablissement($lien, $tri_centre_e, $andWhereCen, true, ',bg_ref_inspection ins ', '', 'Centre') . "</select></center></td>
 <td><center><select style='width:50%' name='tri_centre_d' onchange='document.forms.form_tri.submit()'>" . optionsEtablissement($lien, $tri_centre_d, $andWhereCen_d, true, ',bg_ref_inspection ins ', '', 'Centre') . "</select></center></td>
 </tr>";
            $tri .= "<tr><td><center><select style='width:50%' name='tri_etablissement_e' onchange='document.forms.form_tri.submit()'>" . optionsEtablissement($lien, $tri_etablissement_e, $andWhereEta, true, ',bg_ref_inspection ins ') . "</select></center></td>
<td><center><select style='width:50%' name='tri_etablissement_d' onchange='document.forms.form_tri.submit()'>" . optionsEtablissement($lien, $tri_etablissement_d, $andWhereEta_d, true, ',bg_ref_inspection ins ') . "</select></center></td>
 </tr>";
            $tri .= "<tr><td><center><select style='width:50%' name='tri_serie_e' onchange='document.forms.form_tri.submit()'>" . optionsReferentiel($lien, 'serie', $tri_serie_e, 'WHERE id <4') . "</select></center></td>
<td><center><select disabled style='width:50%' name='tri_serie_d' onchange='document.forms.form_tri.submit()'>" . optionsReferentiel($lien, 'serie', $tri_serie_d, 'WHERE id <4') . "</select></center></td>
  </tr>";
            $tri .= "<tr> <td colspan=2> <center><input name='valider' type='submit' value='Transférer'  $controle_ets_js /></center></td></tr>";

            $tri .= "</table>";
            $tri .= fin_cadre_enfonce();
            echo $tri;

            //Affichage des resultats du tri
            $pre = ($limit - MAX_LIGNES);
            if ($isChefEtablissement) {
                $andStatut = " AND can.etablissement='$code_etablissement' ";
            }

            if ($isInspection || $isEncadrant) {
                $andStatut = $andStatut2;
            }
            $sql_aff1 = "SELECT can.*  FROM bg_candidats can, bg_ref_etablissement eta, bg_ref_ville vil,bg_ref_inspection ins
	WHERE can.annee='$annee' AND can.etablissement=eta.id AND vil.id=eta.id_ville AND eta.id_inspection=ins.id $andWhere $andStatut ";
            $result_aff1 = bg_query($lien, $sql_aff1, __FILE__, __LINE__);
            $total = mysqli_num_rows($result_aff1);
            echo "<p class='center'>";
            if ($limit > 0) {
                echo "<a href=\"javascript:document.forms['form_tri'].limit.value=" . ($limit - MAX_LIGNES) . ";document.forms['form_tri'].submit();\"><<::Précédent </a>";
            }

            $limit2 = ($limit + MAX_LIGNES);
            if ($total < $limit2) {
                $limit2 = $total;
            }

            echo " $total enregistrements au total [$limit .... $limit2] ";
            if ($total > ($limit + MAX_LIGNES)) {
                echo "<a href=\"javascript:document.forms['form_tri'].limit.value=" . ($limit + MAX_LIGNES) . ";document.forms['form_tri'].submit();\"> Suivant::>></a>";
            }

            echo "</p>";
            if ($isChefEtablissement) {
                $andStatut = " AND can.etablissement='$code_etablissement' ";
            }

            $sql_aff = "SELECT can.* FROM bg_candidats can, bg_ref_etablissement eta, bg_ref_ville vil,bg_ref_inspection ins
						WHERE can.annee='$annee' AND can.etablissement=eta.id AND vil.id=eta.id_ville  AND eta.id_inspection=ins.id $andWhere $andStatut
						ORDER BY nom, prenoms LIMIT $limit, " . MAX_LIGNES;

            $result_aff = bg_query($lien, $sql_aff, __FILE__, __LINE__);

            $tab = array('id_candidat', 'num_dossier', 'num_table', 'serie', 'annee_bac1', 'jury_bac1', 'num_table_bac1', 'nom', 'prenoms', 'sexe', 'ddn', 'ldn', 'pdn', 'nationalite', 'telephone', 'lv1', 'lv2', 'efa', 'efb', 'eps', 'etat_physique', 'type_session', 'etablissement', 'nom_photo', 'login', 'maj');
            echo "<center><a href='./?exec=transfert&id_inspection=$id_inspection'>Retour au Menu principal</a></center>";
            echo "<table class='spip liste'>";

            $thead = "<th>Check</th><th>N°</th><th>N° table</th><th>Noms</th><th>Etablissement</th><th>Série</th>";
            while ($row_aff = mysqli_fetch_array($result_aff)) {
                foreach ($tab as $var) {
                    $$var = $row_aff[$var];
//Decommenter la ligne precedente et commenter la ligne suivante pour gerer l'encodage sur la lsite des candidats

                }
                $sexe = selectReferentiel($lien, 'sexe', $sexe);
                $serie = selectReferentiel($lien, 'serie', $serie);
                $etablissementRef = selectReferentiel($lien, 'etablissement', $etablissement);
                $id_inspection = selectInspectionByEtablissement($lien, $etablissement);
                if ($sexe == 'F') {
                    $civ = 'Mlle';
                } else {
                    $civ = 'M.';
                }

                $tbody .= "<tr><td><input type='checkbox' name='tabCandidat[$id_candidat]' value='$id_candidat' /></td><td>$id_candidat</td><td>$num_table</td><td><a href='./?exec=candidats&etape=maj&id_candidat=$id_candidat&id_inspection=$id_inspection&annee=$annee' title=\"Saisi par $login le $maj\">$civ $nom $prenoms</a></td>
		<td>$etablissementRef</td><td>$serie</td></tr>";

            }
            echo $thead, $tbody;
            echo "</table>";
            echo "</form>";
        }

        //Exécution de la commande qui va permettre de modifier les etablissements

        if (isset($_POST['valider'])) {
            $etab_expediteur = $_POST['tri_etablissement_e'];
            $etab_destinataire = $_POST['tri_etablissement_d'];
            $tabCandidat = $_POST['tabCandidat'];
            // var_dump($tabCandidat);
            // var_dump($etab_destinataire);

            if (!empty($_POST['tabCandidat'])) {

                foreach ($tabCandidat as $candidat) {
                    $sql = "UPDATE bg_candidats SET etablissement=$etab_destinataire WHERE id_candidat=$candidat";
                    bg_query($lien, $sql, __FILE__, __LINE__);
                }
            } else {
                echo ("nothing");
            }

        }

        if ($_REQUEST['appliquer_statut']) {
            $andSET = '';
            if ($_POST['nouvel_centre'] > 0) {
                $andSET .= ", centre=" . $_POST['nouvel_centre'];
            } else {
                $centre = centreEtablissement($lien, $_POST['nouvel_etablissement']);
                $andSET .= ", centre=" . $centre;
            }
            if ($_POST['nouvel_serie'] > 0) {
                $andSET .= ", serie=" . $_POST['nouvel_serie'];
            }

            if ($_POST['nouvel_serie'] != 1) {
                $andSET .= ", lv2=0 ";
            }

            if ($_POST['nouvel_serie'] == 1 && $_POST['nouvel_lv2'] != 0) {
                $andSET .= ", lv2=" . $_POST['nouvel_lv2'];
            }

            foreach ($_POST['listeCandidats'] as $id_candidat) {
                $sql = "UPDATE bg_candidats SET centre='$centre', etablissement=" . $_POST['nouvel_etablissement'] . " $andSET WHERE annee=$annee AND id_candidat='$id_candidat' ";
                bg_query($lien, $sql, __FILE__, __LINE__);
            }
        }

        //################# TRANSFERT DE CANDIDAT GROUPE VERS CENTRE ###############################
        if (isset($_REQUEST['etape']) && $_REQUEST['etape'] == 'transfertcentre') {

            $tri = debut_cadre_enfonce('', '', '', 'Transfert de candidats Vers un Autre Centre');
            // if (isset($_POST['tri_annee_ec']) && $_POST['tri_annee_ec'] > 0) {
            //     $annee = $tri_annee_ec;
            // }

            if (isset($_POST['tri_serie_ec']) && $_POST['tri_serie_ec'] > 0) {
                $andWhere .= " AND can.serie=$tri_serie_ec ";
            }
            if (isset($_POST['tri_centre_ec']) && $_POST['tri_centre_ec'] > 0) {$andWhere .= " AND can.centre=$tri_centre_ec ";
                $andWhereEta = "AND eta.id_centre=$tri_centre_ec";}
            if (isset($_POST['tri_inspection_ec']) && $_POST['tri_inspection_ec'] > 0) {$andWhere .= " AND eta.id_inspection=$tri_inspection_ec ";
                $andWhereEta .= " AND eta.id_inspection=$tri_inspection_ec ";
                $andWhereCen .= " AND eta.si_centre='oui' AND ins.id=eta.id_inspection AND eta.id_inspection=$tri_inspection_ec ";}
            if (isset($_POST['tri_region_ec']) && $_POST['tri_region_ec'] > 0) {$andWhere .= " AND ins.id_region=$tri_region_ec ";
                $andWhereRegion .= "WHERE id_region=$tri_region_ec";
                $andWhereEta .= " AND ins.id_region=$tri_region_ec AND ins.id=eta.id_inspection";
                $andWhereCen .= "AND si_centre='oui' AND ins.id=eta.id_inspection AND ins.id_region=$tri_region_ec";}
            if (isset($_POST['tri_etablissement_ec']) && $_POST['tri_etablissement_ec'] > 0) {$andWhere .= " AND can.etablissement=$tri_etablissement_ec ";
                $andWhereEta .= " ";}

            if (isset($_POST['tri_centre_dc']) && $_POST['tri_centre_dc'] > 0) {

                $andWhere .= " ";
                $andWhereEta_d = "AND eta.id_centre=$tri_centre_dc";}
            if (isset($_POST['tri_inspection_dc']) && $_POST['tri_inspection_dc'] > 0) {

                $andWhere .= "  ";
                $andWhereEta_d .= " AND eta.id_inspection=$tri_inspection_dc ";
                $andWhereCen_d .= " AND eta.si_centre='oui' AND ins.id=eta.id_inspection AND eta.id_inspection=$tri_inspection_dc ";}
            if (isset($_POST['tri_region_dc']) && $_POST['tri_region_dc'] > 0) {
                // $andWhere .= " AND ins.id_region=$tri_region_d ";
                $andWhere .= " ";
                $andWhereRegion_d .= "WHERE id_region=$tri_region_dc";
                $andWhereEta_d .= " AND ins.id_region=$tri_region_dc AND ins.id=eta.id_inspection";
                $andWhereCen_d .= "AND si_centre='oui' AND ins.id=eta.id_inspection AND ins.id_region=$tri_region_dc";}
            if (isset($_POST['tri_etablissement_dc']) && $_POST['tri_etablissement_dc'] > 0) {
                //$andWhere .= " AND can.etablissement=$tri_etablissement_dc ";
                $andWhere .= "  ";
                $andWhereEta_d .= " ";}

            if ($isInspection) {
                $andWhereRegion = " WHERE id_region='$id_region' AND id=$id_inspection";
                $andWhereRegion_d = " WHERE id_region='$id_region' AND id=$id_inspection";
                $andWhereEta = " AND reg.id='$id_region'AND ins.id=$id_inspection AND si_centre='non'";
                $andWhereEta_d = " AND reg.id='$id_region' AND ins.id=$id_inspection AND si_centre='non'";
                $andWhereCen = " AND reg.id='$id_region'AND ins.id=$id_inspection AND si_centre='oui'";
                $andWhereCen_d = " AND reg.id='$id_region' AND ins.id=$id_inspection AND si_centre='oui'";

            }
            if ($isEncadrant) {
                $andWhereRegion = " WHERE id_region=$id_region ";
                $andWhereRegion_d = " WHERE id_region=$id_region ";
                $andWhereEta = " AND reg.id='$id_region'AND  si_centre='non'";
                $andWhereEta_d = " AND reg.id='$id_region' AND si_centre='non'";
                $andWhereCen = " AND reg.id='$id_region' AND si_centre='oui'";
                $andWhereCen_d = " AND reg.id='$id_region' AND si_centre='oui'";
            }
            if (!isset($_REQUEST['limit'])) {
                $limit = 0;
            }

            if ($limit < 0) {
                $limit = 0;
            }
            echo "<form name='form_tri' method='POST' >";
            $tri .= "<table>";

            $tri .= "<tr><td><input type='hidden' name='limit' value='$limit' /><input type='hidden' name='annee' value='$annee' /><input type='hidden' name='id_prefecture' value='$id_prefecture' /></td>
			<td></td></tr>";
            $tri .= " <tr><td> <center><strong> Expéditeur </strong></center></td> <td > <center><strong>Destinataire </strong></center> <td></tr>";
            if ($isEncadrant) {
                $tri .= "<tr><td><center><select style='width:50%' name='tri_region_ec' onchange='document.forms.form_tri.submit()'>" . optionsReferentiel($lien, 'region', $tri_region_ec, $WhereRegion) . "</select></center></td>
<td><center><select style='width:50%' name='tri_region_dc' onchange='document.forms.form_tri.submit()'>" . optionsReferentiel($lien, 'region', $tri_region_dc, $WhereRegion) . "</select></center></td>
  </tr>";
            }
            $tri .= "<tr><td><center><select style='width:50%' name='tri_inspection_ec' onchange='document.forms.form_tri.submit()'>" . optionsReferentiel($lien, 'inspection', $tri_inspection_ec, $andWhereRegion) . "</select></center></td>
<td><center><select style='width:50%' name='tri_inspection_dc' onchange='document.forms.form_tri.submit()'>" . optionsReferentiel($lien, 'inspection', $tri_inspection_dc, $andWhereRegion_d) . "</select></center></td>
 </tr>";
            $tri .= "<tr><td><center><select style='width:50%' name='tri_centre_ec' onchange='document.forms.form_tri.submit()'>" . optionsEtablissement($lien, $tri_centre_ec, $andWhereCen, true, ',bg_ref_inspection ins ', '', 'Centre') . "</select></center></td>
 <td><center><select style='width:50%' name='tri_centre_dc' onchange='document.forms.form_tri.submit()'>" . optionsEtablissement($lien, $tri_centre_dc, $andWhereCen_d, true, ',bg_ref_inspection ins ', '', 'Centre') . "</select></center></td>
 </tr>";
            $tri .= "<tr><td><center><select style='width:50%' name='tri_etablissement_ec' onchange='document.forms.form_tri.submit()'>" . optionsEtablissement($lien, $tri_etablissement_ec, $andWhereEta, true, ',bg_ref_inspection ins ') . "</select></center></td>
<td><center><select disabled style='width:50%' name='tri_etablissement_dc' onchange='document.forms.form_tri.submit()'>" . optionsEtablissement($lien, $tri_etablissement_dc, $andWhereEta_d, true, ',bg_ref_inspection ins ') . "</select></center></td>
 </tr>";
            $tri .= "<tr><td><center><select style='width:50%' name='tri_serie_ec' onchange='document.forms.form_tri.submit()'>" . optionsReferentiel($lien, 'serie', $tri_serie_ec, 'WHERE id <4') . "</select></center></td>
<td><center><select disabled style='width:50%' name='tri_serie_d' onchange='document.forms.form_tri.submit()'>" . optionsReferentiel($lien, 'serie', $tri_serie_d, 'WHERE id <4') . "</select></center></td>
  </tr>";
            $tri .= "<tr> <td colspan=2> <center><input name='validercentre' type='submit' value='Transférer' /></center></td></tr>";

            $tri .= "</table>";
            $tri .= fin_cadre_enfonce();
            echo $tri;

            //Affichage des resultats du tri
            $pre = ($limit - MAX_LIGNES);
            if ($isChefEtablissement) {
                $andStatut = " AND can.etablissement='$code_etablissement' ";
            }

            if ($isInspection || $isEncadrant) {
                $andStatut = $andStatut2;
            }

            $sql_aff1 = "SELECT can.*  FROM bg_candidats can, bg_ref_etablissement eta, bg_ref_ville vil,bg_ref_inspection ins
	WHERE can.annee='$annee' AND can.etablissement=eta.id AND vil.id=eta.id_ville AND eta.id_inspection=ins.id $andWhere $andStatut ";
            $result_aff1 = bg_query($lien, $sql_aff1, __FILE__, __LINE__);
            $total = mysqli_num_rows($result_aff1);
            echo "<p class='center'>";
            if ($limit > 0) {
                echo "<a href=\"javascript:document.forms['form_tri'].limit.value=" . ($limit - MAX_LIGNES) . ";document.forms['form_tri'].submit();\"><<::Précédent </a>";
            }

            $limit2 = ($limit + MAX_LIGNES);
            if ($total < $limit2) {
                $limit2 = $total;
            }

            echo " $total enregistrements au total [$limit .... $limit2] ";
            if ($total > ($limit + MAX_LIGNES)) {
                echo "<a href=\"javascript:document.forms['form_tri'].limit.value=" . ($limit + MAX_LIGNES) . ";document.forms['form_tri'].submit();\"> Suivant::>></a>";
            }

            echo "</p>";
            echo "<center><a href='./?exec=transfert&id_inspection=$id_inspection'>Retour au Menu principal</a></center>";
            if ($isChefEtablissement) {
                $andStatut = " AND can.etablissement='$code_etablissement' ";
            }

            $sql_aff = "SELECT can.* FROM bg_candidats can, bg_ref_etablissement eta, bg_ref_ville vil,bg_ref_inspection ins
						WHERE can.annee='$annee' AND can.etablissement=eta.id AND vil.id=eta.id_ville  AND eta.id_inspection=ins.id $andWhere $andStatut
						ORDER BY nom, prenoms LIMIT $limit, " . MAX_LIGNES;

            $result_aff = bg_query($lien, $sql_aff, __FILE__, __LINE__);

            $tab = array('id_candidat', 'num_dossier', 'num_table', 'serie', 'annee_bac1', 'jury_bac1', 'num_table_bac1', 'nom', 'prenoms', 'sexe', 'ddn', 'ldn', 'pdn', 'nationalite', 'telephone', 'lv1', 'lv2', 'efa', 'efb', 'eps', 'etat_physique', 'type_session', 'etablissement', 'centre', 'nom_photo', 'login', 'maj');
            // echo "<form name='form_tri_x' method='POST' >";

            echo "<table class='spip liste'>";

            $thead = "<th><input type='checkbox'/></th><th>N°</th><th>Noms</th><th>Etablissement</th><th>Centre</th><th>Série</th>";
            while ($row_aff = mysqli_fetch_array($result_aff)) {
                foreach ($tab as $var) {
                    $$var = $row_aff[$var];
//Decommenter la ligne precedente et commenter la ligne suivante pour gerer l'encodage sur la lsite des candidats

                }
                $sexe = selectReferentiel($lien, 'sexe', $sexe);
                $serie = selectReferentiel($lien, 'serie', $serie);
                $etablissementRef = selectReferentiel($lien, 'etablissement', $etablissement);
                $centre = selectReferentiel($lien, 'etablissement', $centre);
                $id_inspection = selectInspectionByEtablissement($lien, $etablissement);
                if ($sexe == 'F') {
                    $civ = 'Mlle';
                } else {
                    $civ = 'M.';
                }

                $tbody .= "<tr><td style='width:0px'><input type='checkbox' name='tabCandidat[$id_candidat]' value='$id_candidat' /></td><td>$id_candidat</td><td><a href='./?exec=candidats&etape=maj&id_candidat=$id_candidat&id_inspection=$id_inspection&annee=$annee' title=\"Saisi par $login le $maj\">$civ $nom $prenoms</a></td>
                <td>$etablissementRef</td><td>$centre</td><td>$serie</td></tr>";

            }
            echo $thead, $tbody;
            echo "</table>";
            echo "</form>";
        }

        if (isset($_POST['validercentre'])) {
            $etab_expediteur = $_POST['tri_etablissement_ec'];
            $centre_destinataire = $_POST['tri_centre_dc'];
            $tabCandidat = $_POST['tabCandidat'];

            if (!empty($_POST['tabCandidat'])) {

                foreach ($tabCandidat as $candidat) {
                    $sql = "UPDATE bg_candidats SET centre=$centre_destinataire WHERE id_candidat=$candidat";
                    bg_query($lien, $sql, __FILE__, __LINE__);
                    echo debut_boite_info();
                    echo "Déplacemet éffectué avec succès";
                    echo fin_boite_info();
                }
            } else {
                // echo ("nothing");

            }

        }
        //################# FIN DE TRANSFERT DE CANDIDAT GROUPE VERS CENTRE ###############################

        if (isset($_GET['etape']) && $etape == 'doc') {
            $doc = debut_cadre_couleur('', '', '', "DOCUMENTATION");
            $doc .= "<table>";
            $doc .= "<tr><td colspan=2>La gestion des candidats comporte l'enregistrement des candidats (lien 'Insérer'), la visualisation et la mise à jour des candidats déjà enregistrés (lien 'Liste'),
				l'impression des listes des candidats enregistrés (lien 'Impressions') et la vérification des doublons (lien 'Doublons'). </td></tr>";
            $doc .= "<tr><td>1. Transfert Etablissement</td><td>Cliquer sur le lien 'Insérier'<br/>Renseigner les divers champs<br/>Cliquer sur 'Enregistrer'<br>A la fin des saisies, cliquer sur 'Quitter'</td></tr>";
            $doc .= "<tr><td>2. Transfert Groupé vers Etablissement</td><td>Cliquer sur le lien 'Liste'<br/>Cette liste peut être triée par série et/ou par sexe<br/>Un candidat peut être recherché par son numéros ou par son nom</td></tr>";
            $doc .= "<tr><td>3. Transfert Groupé vers Centre</td><td>Pour modifier ou supprimer un dossier, il suffit de cliquer sur le nom du candidat<br/>Le formulaire apparaît et la modification ou la suppression peut se faire</td></tr>";
            $doc .= "</table>";
            $doc .= fin_cadre_couleur();
            echo $doc;
        }

        echo fin_cadre_trait_couleur();
        echo fin_grand_cadre(), fin_page();
    }
}

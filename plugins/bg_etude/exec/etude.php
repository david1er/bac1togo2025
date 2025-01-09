<?php
if (!defined('_ECRIRE_INC_VERSION')) {
    return;
}

include_spip('inc/presentation'); // alleger les inclusions avec un inc/presentation_mini
include '../plugins/fonctions/generales.php';

setlocale(LC_TIME, "fr_FR");
define('MAX_LIGNES', 200);

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
			WHERE   eta.id_inspection=$id_inspection AND can.etablissement=eta.id AND can.statu=1
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

    $tableau = "<table class='spip liste'><thead>";
    $tableau .= "<th>Etablissement</th><th>Nbre</th><th class='center' colspan=2>Listes</th>$andTableau<th>Liste Additive</th>";
    $tableau .= "</thead><tbody>";
    $tab = array('id_etablissement', 'etablissement', 'nbre');
    while ($row = mysqli_fetch_array($result)) {
        foreach ($tab as $var) {
            $$var = $row[$var];
        }

        $tableau .= "<tr><td>$etablissement</td><td align='rigth'>$nbre</td>
						<td class='center'><a href='../plugins/fonctions/inc-liste.php?id_etablissement=$id_etablissement&annee=$annee$andLien&type=0'><img src='../plugins/images/imprimer.png' title='imprimer en pdf'/></a></td>
						<td class='center'><a href='../plugins/fonctions/csvfile/inc-csv-liste.php?id_etablissement=$id_etablissement&annee=$annee$andLien&type=0'><img src='../plugins/images/csvimport-24.png' title='imprimer en csv' /></a></td>

                        <td class='center' width='10%'><a href='../plugins/fonctions/inc-liste-additive.php?id_etablissement=$id_etablissement&annee=$annee$andLien&type=0'><img src='../plugins/images/imprimer.png' title='imprimer liste additive en pdf'/></a></td>
					";

        $tableau .= "</tr>";
    }
    $tableau .= "</tbody></table>";
    echo "<h4><center><a href='./?exec=etude&id_inspection=$id_inspection'>Retour au Menu principal</a></center></h4>";
    echo $formAnnee;
    echo $tableau;
}

function etablissementRejetCandidats($lien, $annee, $id_inspection, $id_etablissement = 0, $statut = 0)
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
			WHERE   eta.id_inspection=$id_inspection AND can.etablissement=eta.id AND can.statu=0
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
    echo "<h4><center><a href='./?exec=etude&id_inspection=$id_inspection'>Retour au Menu principal</a></center></h4>";
    echo $formAnnee;
    echo $tableau;
}
function centreRejetCandidats($lien, $annee, $id_inspection, $id_etablissement = 0, $statut = 0)
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

    $sql = "SELECT eta.etablissement, eta.id as id_centre, count(can.centre) as nbre
			FROM bg_ref_etablissement eta, bg_candidats can
			WHERE   eta.id_inspection=$id_inspection AND can.centre=eta.id AND can.statu=0
			AND can.annee=$annee $andWhere
			GROUP BY eta.id, can.centre ORDER BY eta.etablissement ";

    $result = bg_query($lien, $sql, __FILE__, __LINE__);

    $formAnnee = "<form name='form_annee' method='POST' >";
    $formAnnee .= "<table><tr>
				<td><input type='hidden' name='annee' value='$annee' /><input type='hidden' name='id_inspection' value='$id_inspection' />
				<select name='annee' onchange='document.forms.form_annee.submit()'>" . optionsAnnee(2020, $annee) . "</select></td>
				<td><select name='id_serie' onchange='document.forms.form_annee.submit()'>" . optionsReferentiel($lien, 'serie', $id_serie, 'WHERE id <4') . "</select></td>
				</tr></table>";
    $formAnnee .= "</form>";

    $tableau = "<table class='spip liste'><thead>";
    $tableau .= "<th>Centre</th><th>Nbre</th><th class='center' colspan=2>Listes</th>$andTableau";
    $tableau .= "</thead><tbody>";
    $tab = array('id_centre', 'etablissement', 'nbre');
    while ($row = mysqli_fetch_array($result)) {
        foreach ($tab as $var) {
            $$var = $row[$var];
        }
        $tableau .= "<tr><td>$etablissement</td><td align='rigth'>$nbre</td>
						<td class='center'><a href='../plugins/fonctions/inc-liste-centre_rejet.php?id_centre=$id_centre&annee=$annee$andLien&type=0'><img src='../plugins/images/imprimer.png' title='imprimer en pdf'/></a></td>


					";
        // if (in_array($statut, array('Admin', 'Encadrant'))) {
        //     $tableau .= "<td class='center'><a href='../plugins/fonctions/inc-liste.php?id_etablissement=$id_etablissement&annee=$annee$andLien&type=1'><img src='../plugins/images/imprimer.png' /></a></td>";
        // } else { $tableau .= '';}
        $tableau .= "</tr>";
    }
    $tableau .= "</tbody></table>";
    echo "<h4><center><a href='./?exec=etude&id_inspection=$id_inspection'>Retour au Menu principal</a>
    --------
    <a href='../plugins/fonctions/inc-pdf-centre_rejet_inspection.php?id_inspection=$id_inspection$andLien' target='_blank()'>Imprimer Liste</a></center></h4>";
    echo $formAnnee;
    echo $tableau;
}

function tableau_Instance($lien, $id_inspection, $limit)
{ //Affichage des resultats du tri
    $pre = ($limit - MAX_LIGNES);
    if ($isChefEtablissement) {
        $andStatut = " AND can.etablissement='$code_etablissement' ";
    }

    if ($isAdmin) {$andStatut = $andStatut2;}

    $sql_aff1 = "SELECT can.*  FROM bg_candidats can, bg_ref_etablissement eta, bg_ref_ville vil,bg_ref_inspection ins
WHERE can.annee='$annee' AND can.etablissement=eta.id AND vil.id=eta.id_ville AND eta.id_inspection=ins.id  AND can.statu=0 $andWhere $andStatut ";

    // mysqli_set_charset($lien, "utf8mb4");
    $result_aff1 = bg_query($lien, $sql_aff1, __FILE__, __LINE__);
    $total = mysqli_num_rows($result_aff1);
    $tableau = "<p class='center'>";
    if ($limit > 0) {
        $tableau .= "<a href=\"javascript:document.forms['form_tri_rejet'].limit.value=" . ($limit - MAX_LIGNES) . ";document.forms['form_tri_rejet'].submit();\"><<::Précédent </a>";
    }

    $limit2 = ($limit + MAX_LIGNES);
    if ($total < $limit2) {
        $limit2 = $total;
    }

    $tableau .= " $total enregistrements au total [$limit .... $limit2] ";
    if ($total > ($limit + MAX_LIGNES)) {
        $tableau .= "<a href=\"javascript:document.forms['form_tri_rejet'].limit.value=" . ($limit + MAX_LIGNES) . ";document.forms['form_tri_rejet'].submit();\"> Suivant::>></a>";
    }

    $tableau .= "<br/><a href='./?exec=etude&id_inspection=$id_inspection'>Retour au Menu Principale</a>";
    $tableau .= "</p>";

    $sql_aff = "SELECT can.* FROM bg_candidats can, bg_ref_etablissement eta, bg_ref_ville vil,bg_ref_inspection ins
 WHERE can.annee='$annee' AND can.etablissement=eta.id AND vil.id=eta.id_ville  AND eta.id_inspection=ins.id
 AND can.statu=0 $andWhere $andStatut
 ORDER BY nom, prenoms LIMIT $limit, " . MAX_LIGNES;
    //mysqli_set_charset($lien, "utf8mb4");
    $result_aff = bg_query($lien, $sql_aff, __FILE__, __LINE__);

    //echo $sql_aff;
    $tab = array('id_candidat', 'num_dossier', 'num_table', 'serie', 'annee_bac1', 'jury_bac1', 'num_table_bac1', 'nom', 'prenoms', 'sexe', 'ddn', 'ldn', 'pdn', 'nationalite', 'telephone', 'lv1', 'lv2', 'efa', 'efb', 'eps', 'etat_physique', 'type_session', 'etablissement', 'nom_photo', 'login', 'statu', 'maj');
    $tableau .= "<table class='spip liste'>";
    $tableau .= "<th>Check</th><th>N°</th><th>N° table</th><th>Noms</th><th>Sexe</th><th>Etablissement</th><th>Centre</th><th>Série</th><th>Statu</th>";
    while ($row_aff = mysqli_fetch_array($result_aff)) {
        foreach ($tab as $var) {
            $$var = $row_aff[$var];
//Decommenter la ligne precedente et commenter la ligne suivante pour gerer l'encodage sur la lsite des candidats
            //                $$var=utf8_encode($row_aff[$var]);
        }
        $sexe = selectReferentiel($lien, 'sexe', $sexe);
        $serie = selectReferentiel($lien, 'serie', $serie);
        $etablissementRef = selectReferentiel($lien, 'etablissement', $etablissement);
        $id_inspection = selectInspectionByEtablissement($lien, $etablissement);
        $centreEts = centreEts($lien, $etablissement);

        if ($sexe == 'F') {
            $civ = 'Mlle';
            $sexe = 'F';
        } else {
            $civ = 'M.';
            $sexe = 'M';
        }
        if ($statu == 1) {
            $statu = 'Actif';
        } else {
            $statu = 'Inactif';
        }

        $tableau .= "<tr><td><input type='checkbox' name='tabCandidat[$id_candidat]' value='$id_candidat' /></td><td>$id_candidat</td><td>$num_table</td><td><a href='./?exec=etude&etape=maj&id_candidat=$id_candidat&id_inspection=$id_inspection&annee=$annee' title=\"Saisi par $login le $maj\">$nom $prenoms</a><td>$sexe</td></td><td>$etablissementRef</td><td>$centreEts</td><td>$serie</td><td>$statu</td></tr>";

    }
    // echo $thead, $tbody;
    $tableau .= "</table>";
    echo $tableau;

}

function ModifierEtablissement($lien, $id_inspection, $id_etablissement)
{

    // echo debut_cadre_couleur("", '', '', "CONFIGURATION D'UN ETABLISSEMENT", "", "");
    $id_region = recherche_region($lien, $id_inspection);

    // if ($etape == 'modifier') {
    $where = " WHERE id_inspection=$id_inspection ";
    $where2 = " WHERE id_region=$id_region ";
    $where_eta = " AND id_region=$id_region ";
    $where_pre = " AND id_region=$id_region ";

    $etablissementRef = selectReferentiel($lien, 'etablissement', $id_etablissement);
    // }

    // // $form_ins = "<form action='' method='POST' class='forml spip_xx-small' name='form_ins' onload=\"document.forms.form_ins.etablissement.focus();\">";

    $form = "<div class='formulaire_spip'><form action='' method='POST' class='forml spip_xx-small' name='form_ins' >";
    $where_ins = " WHERE id_region=$id_region ";

    $form .= "<TABLE>";
    //$form_ins .= "<fieldset><legend align='center' style='color: #3333DD ; background: #CCCCFF ;'><b>Informations Obligatoires</b></legend>";

    // if ($_REQUEST['etape'] && $_REQUEST['etape'] == 'modifier') {
    $sql2 = "SELECT  * FROM bg_ref_etablissement eta,bg_ref_prefecture pre,bg_ref_ville vil
            WHERE eta.id=$id_etablissement AND vil.id=eta.id_ville AND pre.id=vil.id_prefecture ";
    $result2 = bg_query($lien, $sql2, __FILE__, __LINE__);
    $tab = array('etablissement', 'id_ville', 'id_prefecture', 'id_type_etablissement', 'id_etat', 'code', 'nom_responsable', 'telephones_eta', 'login_eta', 'mdp_eta', 'si_centre', 'id_centre', 'id_region_division');
    $row = mysqli_fetch_array($result2);
    foreach ($tab as $var) {
        $$var = $row[$var];
    }

    $form .= "<tr><td><label></label></td><td><input type='hidden' value='$id_etablissement' name='id_etablissement' id='id_etablissement'></td></tr>";
    //     } else {
    //         $etablissement = $code = $nom_responsable = $telephones_eta = $login_eta = $mdp_eta = '';
    // //        $id_ville=$id_inspection=$id_type_etablissement=$id_etat=$id_region_division=0;
    //         $si_centre = 'non';
    //     }
    //Mettre le champ en majuscule
    $majuscule = " OnkeyUp=\"javascript:this.value=this.value.toUpperCase();\" ";
    $form .= "<tr><td colspan=3><fieldset><legend>Informations sur le Nom de l'Etablissement </legend></td></tr>";
    $form .= "<tr><td><label>Nom de l'établissement</label></td><td><input style='width:50%' type='text' name='etablissement' id='etablissement' value=\" $etablissementRef\" $majuscule  required disabled /></td></tr>";

    $form .= "<tr><td colspan=3><fieldset><legend>Informations Administratives</legend></td></tr>";
    $form .= "<tr><td><label>Région </label></td><td><select style='width:50%' name='id_region_division' >" . optionsReferentiel($lien, 'region_division', $id_region_division, "$where2") . "</select></td></tr>";
    $form .= "<tr><td><label>Préfecture</label></td><td><select style='width:50%' name='id_prefecture'>" . optionsReferentiel($lien, 'prefecture', $id_prefecture, "$where_ins") . "</select></td></tr>";
    $form .= "<tr><td><label>Ville</label></td><td><select style='width:50%' name='id_ville' >" . optionsVille($lien, 'ville', $id_ville, $where_pre) . "</select></td></tr>";
    $form .= "<tr><td><label>Type </label></td><td><select style='width:50%' name='id_type_etablissement'>" . optionsReferentiel($lien, 'type_etablissement', $id_type_etablissement, '') . "</select></td></tr>";

    $form .= "<tr><td></td><td><input type='hidden' value='$id_inspection' name='id_inspection'></td></tr>";

    $controle_forms = "onClick=\"if(document.forms['form_ins'].id_region_division.value==0
    || document.forms['form_ins'].id_ville.value==0
    || document.forms['form_ins'].id_prefecture.value==0
    || document.forms['form_ins'].id_commune.value==0
    || document.forms['form_ins'].id_etat.value==0
    || document.forms['form_ins'].id_type_etablissement.value==0)
                        {alert('Les champs  REGION, COMMUNE, VILLE, PREFECTURE, doivent être remplis'); return false; }
                        else {alert('Opération effectuée avec succès');return true;}\"; ";
    // $form_ins .= "";

    $form .= "<td><input type='button' style='width: 50%;  height: 2em;' value='ANNULER & QUITTER' class='submit' onClick=window.location='" . generer_url_ecrire('etude') . "&id_inspection=$id_inspection'></td>";
    $form .= "<td>&nbsp;<input style='width: 50%;  height: 2em;' type='submit' value='ENREGISTRER' name='maj_eta' class='submit' onClick=window.location='" . generer_url_ecrire('etude') . "&id_inspection=$id_inspection' $controle_forms /></td>";

    $form .= "</tr></table></form></div>";
    echo $form;
    if (isset($_REQUEST['maj_eta']) && (isset($_POST['id_ville']))) {
        $id_ville = $_POST['id_ville'];
        $id_type_etablissement = $_POST['id_type_etablissement'];
        $sql_mj = "UPDATE bg_ref_etablissement SET etablissement='$etablissement',id_ville=$id_ville,id_inspection=$id_inspection,
            id_type_etablissement=$id_type_etablissement ,id_region_division=$id_region_division WHERE id=$id_etablissement ";
        //  echo $sql_mj;
        bg_query($lien, $sql_mj, __FILE__, __LINE__);

    }

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

function effectifEts($lien, $id_etablissement)
{
    $sql_effectif = "SELECT COUNT(id_candidat) AS effectif FROM bg_candidats WHERE etablissement = $id_etablissement";
    $result_effectif = bg_query($lien, $sql_effectif, __FILE__, __FILE__);
    $sol_effectif = mysqli_fetch_array($result_effectif);
    return $sol_effectif['effectif'];
}
function effectifEtudeEts($lien, $id_etablissement)
{
    $sql_effectif = "SELECT COUNT(id_candidat) AS effectif FROM bg_candidats WHERE statu=0 AND etablissement = $id_etablissement";
    $result_effectif = bg_query($lien, $sql_effectif, __FILE__, __FILE__);
    $sol_effectif = mysqli_fetch_array($result_effectif);
    return $sol_effectif['effectif'];
}

function CentreAndMatFacultatives($lien, $id_inspection)
{
    if ($_REQUEST['exec'] == 'etude') {
        echo "<h3><center><a href='./?exec=etude'>Changer l'inspection</a></center></h3>";
    }

    $sql = "SELECT centre,insp.inspection, etab.etablissement,
    COUNT(id_candidat) AS Effectif,
    COUNT(IF(eps=1,eps,NULL)) AS EPS,
    COUNT(IF(efa=1,efa,NULL)) AS Allemand,
    COUNT(IF(efa=5,efa,NULL)) AS Arabe,
    COUNT(IF(efa=2,efa,NULL)) AS Espagnol,
    COUNT(IF(efa=8,efa,NULL)) AS Ewe,
    COUNT(IF(efa=9,efa,NULL)) AS Kabye,
    COUNT(IF(efa=4,efa,NULL)) AS Russe,
    COUNT(IF(efb=6,efb,NULL) OR IF(efa=6,efa,NULL)) AS Dessin,
    COUNT(IF(efb=8,efb,NULL)) AS EM,
    COUNT(IF(efb=7,efb,NULL) OR IF(efa=7,efa,NULL)) AS Musique
    FROM bg_candidats cand, bg_ref_etablissement etab, bg_ref_inspection insp
    WHERE cand.etablissement=etab.id AND etab.id_inspection=insp.id AND etab.id_inspection=$id_inspection
    GROUP BY centre";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $tableau = "<table class='spip liste'><caption class='center'><a href='../plugins/fonctions/inc-pdf-matiere_fac.php?id_inspection=$id_inspection$andLien' target='_blank()'>IMPRIMER EN PDF</a></caption><thead>";
    $tableau .= "<th>Centre</th><th>Effectif</th><th>EPS</th><th>Allemand</th><th>Arabe</th><th>Espagnol</th><th>Ewe</th><th>Kabye</th><th>Russe</th><th>Agri</th><th>Dessin</th><th>EM</th><th>Musique</th>";
    $tableau .= "</thead><tbody>";
    $tab = array('centre', 'Effectif', 'EPS', 'Allemand', 'Arabe', 'Espagnol', 'Ewe', 'Kabye', 'Russe', 'Agri', 'Dessin', 'EM', 'Musique');
    while ($row = mysqli_fetch_array($result)) {
        foreach ($tab as $var) {
            $$var = $row[$var];
        }
        $tableau .= "<tr>"
        . "<td><b>" . centreEts($lien, $row['centre']) . "</b></td>"
            . "<td>" . $row['Effectif'] . "</td>"
            . "<td>" . $row['EPS'] . "</td>"
            . "<td>" . $row['Allemand'] . "</td>"
            . "<td>" . $row['Arabe'] . "</td>"
            . "<td>" . $row['Espagnol'] . "</td>"
            . "<td>" . $row['Ewe'] . "</td>"
            . "<td>" . $row['Kabye'] . "</td>"
            . "<td>" . $row['Russe'] . "</td>"
            . "<td>" . $row['Agri'] . "</td>"
            . "<td>" . $row['Dessin'] . "</td>"
            . "<td>" . $row['EM'] . "</td>"
            . "<td>" . $row['Musique'] . "</td>";

    }
    $tableau .= "</tbody><tfoot><tr></tr></tfoot></table>";
    echo $tableau;
    echo fin_cadre_relief(true);

}

function etablissementAndCentre($lien, $id_inspection, $uStatus)
{
    $sql = "SELECT eta.etablissement,eta.id
		  FROM bg_ref_etablissement eta,bg_ref_inspection ins,bg_ref_region reg
		  WHERE reg.id=ins.id_region AND id_centre!=0 AND eta.si_centre='non'  AND eta.id_inspection=$id_inspection
		  GROUP BY eta.id_centre,eta.etablissement";

    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $tab = array('etablissement', 'id');
    if (mysqli_num_rows($result) > 0) {

        if ($_REQUEST['exec'] == 'etude') {
            echo "<h3><center><a href='./?exec=etude'>Changer l'inspection</a> </center></h3>";
        }
        if ($uStatus) {
            //  echo "<h2><center><a href='../plugins/fonctions/inc-all-etablissement_centre.php'>Imprimer tous les Etablissements </a><center></h2>";
        }
        echo "<h2><center>Effectif par Etablissement des Candidats en Instance de rejet : " . selectInspection($lien, $id_inspection) . "<center></h2>",
        debut_boite_info(), "<table>";

        echo "<h2><center><a href='../plugins/fonctions/inc-etablissement_centre-rejet.php?id_inspection=$id_inspection'>Imprimer en pdf</a><center></h2>";

        echo "<th>Centre d'écrit</th><th>Etablissement</th><th>Effectif</th>";
        while ($row = mysqli_fetch_array($result)) {

            foreach ($tab as $val) {
                $$val = addslashes($row[$val]);
            }
            if (effectifEtudeEts($lien, $id)[0] == 0) {
                $color = 'green';
            } else {
                $color = 'red';
            }

            echo "<tr><td><strong>" . centreEts($lien, $id) . "</strong></td><td>" . $etablissement . "</td><td ><font color='$color'>" . effectifEtudeEts($lien, $id) . "</td></tr>";

            // echo "<tr><td>$etablissement </td><td><strong>" . centreEts($lien, $id) . "</strong</td></tr>";

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
		  WHERE reg.id=ins.id_region AND  eta.si_centre='non' AND
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

function exec_etude()
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
            case 'Etude':
                $isEtude = true;
                $idAdmin = false;
                break;
            default:
                die(KO . " - Statut <b>$statut</b> invalide");
        }
    } else {
        $isAdmin = true;
    }
    $login_etablissement = recupEtablissement($lien);
    $login_inspection = recupInspection($lien);
    $login_encadrant = recupEncadrant($lien);
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

    if (isAutorise(array('Admin', 'DExCC', 'Etude'))) {
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
                if ($eps == 2) {
                    $atelier3 = 0;
                } else {
                    $atelier3 = 7;
                }
                $prenoms = ucwords($prenoms);
                $sql_ins = "INSERT INTO bg_candidats (`annee`, `serie`, `num_dossier`, `annee_bac1`, `jury_bac1`, `num_table_bac1`, `nom`, `prenoms`, `sexe`, `ddn`, `ldn`, `pdn`, `nationalite`, `telephone`, `lv1`, `lv2`, `efa`, `efb`, `eps`, `etat_physique`, `atelier1`, `atelier2`, `atelier3`, `centre`, `type_session`, `etablissement`, `nom_photo`, `login`, `statu`, `motif`)
				VALUES ('$annee','$serie','$num_dossier','$annee_bac1','$jury_bac1','$num_table_bac1','$nom','$prenoms','$sexe','$ddn','$ldn','$pdn','$nationalite','$telephone','1','$lv2','$efa','$efb','$eps','$etat_physique','$atelier1','$atelier2','$atelier3','$centre','$type_session','$etablissement','$nom_photo','$login','$statu','$motif') ";
                //  mysqli_set_charset($lien, "utf8mb4");
                bg_query($lien, $sql_ins, __FILE__, __LINE__);
                $sexe = $pdn = $nationalite = $lv1 = $efa = $efb = $eps = 0;
                $etat_physique = $statu = 1;
                $nom = $prenoms = $num_table_bac1 = $ddn = $telephone = $jury_bac1 = $ldn = $num_dossier = '';
            }
        }

        //Mettre a jour un candidat
        if (isset($_POST['modifier'])) {
            $sql = "INSERT INTO bg_histo_candidats
			(`id_candidat`, `annee`, `num_dossier`, `num_table`, `serie`, `annee_bac1`, `jury_bac1`, `num_table_bac1`, `nom`, `prenoms`, `sexe`, `ddn`, `ldn`, `pdn`, `nationalite`, `telephone`, `lv1`, `lv2`, `efa`, `efb`, `eps`, `etat_physique`, `atelier1`, `atelier2`, `atelier3`, `centre`,`type_session`, `etablissement`, `nom_photo`, `login`, `statu`, `motif`, `additive`)
			SELECT `id_candidat`, `annee`, `num_dossier`, `num_table`, `serie`, `annee_bac1`, `jury_bac1`, `num_table_bac1`, `nom`, `prenoms`, `sexe`, `ddn`, `ldn`, `pdn`, `nationalite`, `telephone`, `lv1`, `lv2`, `efa`, `efb`, `eps`, `etat_physique`,`atelier1`, `atelier2`, `atelier3`, `centre`, `type_session`, `etablissement`, `nom_photo`, '" . $login . "' , `statu`, `motif`, `additive` FROM bg_candidats WHERE annee=$annee AND id_candidat='$id_candidat' ";
            //  mysqli_set_charset($lien, "utf8mb4");
            $result = bg_query($lien, $sql, __FILE__, __LINE__);

            $ddn = formater_date($ddn);
            if ($serie != 1) {
                $lv2 = 0;
            }

            // modification envoyée par pape
            if ($eps == 2) {
                $atelier1 = $atelier2 = $atelier3 = 0;
            } else {
                $atelier3 = 7;
            }

            $centre = centreEtablissement($lien, $etablissement);
            $sql_maj = "UPDATE bg_candidats SET num_dossier='$num_dossier', serie='$serie',annee_bac1='$annee_bac1',jury_bac1='$jury_bac1',
					num_table_bac1='$num_table_bac1',nom='$nom',prenoms='$prenoms',sexe='$sexe',ddn='$ddn',ldn='$ldn',pdn='$pdn',
					nationalite='$nationalite',telephone='$telephone',lv2='$lv2',efa='$efa',efb='$efb',eps='$eps',
					etat_physique='$etat_physique', atelier1='$atelier1', atelier2='$atelier2', atelier3='$atelier3', type_session='$type_session',etablissement='$etablissement', centre='$centre'
					WHERE annee=$annee AND id_candidat='$id_candidat' ";
            // mysqli_set_charset($lien, "utf8mb4");
            bg_query($lien, $sql_maj, __FILE__, __LINE__);

        }

        //Etudier un candidat
        if (isset($_POST['etudier'])) {

            $sql = "INSERT INTO bg_histo_candidats
			(`id_candidat`, `annee`, `num_dossier`, `num_table`, `serie`, `annee_bac1`, `jury_bac1`, `num_table_bac1`, `nom`, `prenoms`, `sexe`, `ddn`, `ldn`, `pdn`, `nationalite`, `telephone`, `lv1`, `lv2`, `efa`, `efb`, `eps`, `etat_physique`, `atelier1`, `atelier2`, `atelier3`, `centre`,`type_session`, `etablissement`, `nom_photo`, `login`, `statu`, `motif`)
			SELECT `id_candidat`, `annee`, `num_dossier`, `num_table`, `serie`, `annee_bac1`, `jury_bac1`, `num_table_bac1`, `nom`, `prenoms`, `sexe`, `ddn`, `ldn`, `pdn`, `nationalite`, `telephone`, `lv1`, `lv2`, `efa`, `efb`, `eps`, `etat_physique`,`atelier1`, `atelier2`, `atelier3`, `centre`, `type_session`, `etablissement`, `nom_photo`, '" . $login . "' , `statu`, `motif` FROM bg_candidats WHERE annee=$annee AND id_candidat='$id_candidat' ";
            //  mysqli_set_charset($lien, "utf8mb4");
            $result = bg_query($lien, $sql, __FILE__, __LINE__);

            $ddn = formater_date($ddn);
            if ($serie != 1) {
                $lv2 = 0;
            }
            $date_liste_additive = recupAdditiveDate($lien);
            if ($date_liste_additive == null) {
                $additive = 0;
            } else {
                if (checkThePast($date_liste_additive)) {
                    $additive = 1;
                } else {
                    $additive = 0;
                }
            }
            // // modification envoyée par pape
            // if ($eps == 2) {
            //     $atelier1 = $atelier2 = $atelier3 = 0;
            // } else {
            //     $atelier3 = 7;
            // }
            if ($motif == '') {
                $motif = 0;
            }

            $centre = centreEtablissement($lien, $etablissement);
            $sql_maj = "UPDATE bg_candidats SET statu='$statu',
					  motif='$motif',additive='$additive'
					WHERE annee=$annee AND id_candidat='$id_candidat' ";
            // mysqli_set_charset($lien, "utf8mb4");
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
        echo debut_cadre_trait_couleur('', '', '', "GESTION D'ETUDE DE DOSSIERS DES CANDIDATS", "", "", false);

        if (!isset($_REQUEST['id_inspection']) && (!isset($_REQUEST['etape']))) {
            if ($isEncadrant) {
                listeChoix($lien, $andWhereRegion, 'etude');
            } elseif ($isInspection) {
                listeChoix($lien, $andWhereInspection, 'etude');
            } else {listeChoix($lien, '', 'etude');}

        }

        if (isset($_REQUEST['id_inspection']) && !isset($_REQUEST['etape'])) {
            echo "<div class='onglets_simple clearfix'><ul>";

            echo "<li><a href='./?exec=etude&etape=afficher&id_inspection=$id_inspection' class='ajax'>Etude Dossier</a></li>";
            echo "<li><a href='./?exec=etude&etape=etablissement&id_inspection=$id_inspection' class='ajax'>Etablissement</a></li>";
            if (!$isEtude && !$isInspection) {
                echo "<li><a href='./?exec=etude&etape=imprimer_rejet&id_inspection=$id_inspection' class='ajax'>Imprimer Rejet</a></li>";

                echo "<li><a href='./?exec=etude&etape=rejet&id_inspection=$id_inspection' class='ajax'>Instance de Rejet</a></li>";
                echo "<li><a href='./?exec=etude&etape=trop_age&id_inspection=$id_inspection' class='ajax'>Candiats trops Agé(e)s</a></li>";
            }

            echo "<li><a href='./?exec=etude' class='ajax'>Inspections</a></li>";

            //  echo "<li><a href='./?exec=etude&etape=doc&id_inspection=$id_inspection' class='ajax'>Guide</a></li>";
            echo "<li><a href='./?exec=etude&action=logout&logout=prive' class='ajax'>Se déconnecter</a></li>";
            echo "</ul>
		</div>";
        }

        if (isset($_REQUEST['etape']) && $_REQUEST['etape'] == 'imprimer') {
            echo etablissementsCandidats($lien, $annee, $id_inspection, $code_etablissement, $statut);
        }
        if (isset($_REQUEST['etape']) && $_REQUEST['etape'] == 'imprimer_rejet') {
            echo centreRejetCandidats($lien, $annee, $id_inspection, $code_etablissement, $statut);
        }
        if (isset($_REQUEST['etape']) && $_REQUEST['etape'] == 'trop_age') {
            $age_limte_date = recupAgeLimiteDate($lien);

            $tri_et = debut_cadre_enfonce('', '', '', 'Tri des candidats Trop agée à Etudier');
            $andWhereEta = " AND eta.si_centre='non' AND ins.id=eta.id_inspection AND eta.id_inspection=$id_inspection ";
            $andWhereCen = " AND eta.si_centre='oui' AND ins.id=eta.id_inspection AND eta.id_inspection=$id_inspection ";

            if (isset($_POST['tri_annee']) && $_POST['tri_annee'] > 0) {
                $annee = $tri_annee;
            }

            if (isset($_POST['tri_session']) && $_POST['tri_session'] > 0) {
                $andWhere .= " AND can.type_session=$tri_session ";
            }

            if (isset($_POST['tri_serie']) && $_POST['tri_serie'] > 0) {
                $andWhere .= " AND can.serie=$tri_serie ";
            }

            if (isset($_POST['tri_sexe']) && $_POST['tri_sexe'] > 0) {
                $andWhere .= " AND can.sexe=$tri_sexe ";
            }

            if (isset($_POST['tri_centre']) && $_POST['tri_centre'] > 0) {$andWhere .= " AND can.centre=$tri_centre ";
                $andWhereEta .= " AND eta.id_centre=$tri_centre";}

            if (isset($_POST['tri_etablissement']) && $_POST['tri_etablissement'] > 0) {$andWhere .= " AND can.etablissement=$tri_etablissement ";
                $andWhereEta .= " ";}
            if (isset($_POST['tri_numero_et']) && $_POST['tri_numero_et'] != '') {
                $andWhere .= " AND (can.id_candidat='$tri_numero_et' OR can.num_table='$tri_numero_et') ";
            }

            if (isset($_POST['tri_nom']) && $_POST['tri_nom'] != '') {
                $andWhere .= " AND can.nom LIKE '%$tri_nom%' ";
            }

            if (isset($_POST['tri_ef']) && $_POST['tri_ef'] > 0) {
                $andWhere .= " AND (can.efa=$tri_ef OR can.efb=$tri_ef)";
            }

            if (!isset($_REQUEST['limit'])) {
                $limit = 0;
            }

            if ($limit < 0) {
                $limit = 0;
            }

            $tri_et .= "<form name='form_tri' method='POST' ><table>";
            $tri_et .= "<tr><td><input type='hidden' name='limit' value='$limit' /><input type='hidden' name='annee' value='$annee' /><input type='hidden' name='id_inspection' value='$id_inspection' /></td>
				<td></td></tr>";
            $tri_et .= "<tr><td><center><select style='width:50%' name='tri_annee' onchange='document.forms.form_tri.submit()'>" . optionsAnnee(2020, $tri_annee) . "</select></center></td>
				<td><center><select style='width:50%' name='tri_session' onchange='document.forms.form_tri.submit()'>" . optionsReferentiel($lien, 'type_session', $tri_session) . "</select></center></td></tr>";
            $tri_et .= "<tr> <td><center><select style='width:50%' name='tri_centre' onchange='document.forms.form_tri.submit()'>" . optionsEtablissement($lien, $tri_centre, $andWhereCen, true, ',bg_ref_inspection ins ', '', 'Centre') . "</select></center></td>
            <td><center><select style='width:50%' name='tri_etablissement' onchange='document.forms.form_tri.submit()'>" . optionsEtablissement($lien, $tri_etablissement, $andWhereEta, true, ',bg_ref_inspection ins ') . "</select></center></td></tr>";

            $tri_et .= "<tr><td><center><select style='width:50%' name='tri_sexe' onchange='document.forms.form_tri.submit()'>" . optionsReferentiel($lien, 'sexe', $tri_sexe) . "</select></center></td>
                <td><center><select style='width:50%' name='tri_serie' onchange='document.forms.form_tri.submit()'>" . optionsReferentiel($lien, 'serie', $tri_serie, 'WHERE id <4') . "</select></center></td></tr>";

            $tri_et .= "<tr><td style='width:50%'><center>N°: <input type='text' name='tri_numero_et' style='width:42%' /></center></td>
		<td style='width:50%'><center>Nom: <input type='text' name='tri_nom'  style='width:35%' /><input type='submit' value='OK' /></td></center></tr>";
            $tri_et .= "</table></form>";
            $tri_et .= fin_cadre_enfonce();
            echo $tri_et;

            //Affichage des resultats du tri
            $pre = ($limit - MAX_LIGNES);
            if ($isChefEtablissement) {
                $andStatut = " AND can.etablissement='$code_etablissement' ";
            }

            if ($isInspection || $isEncadrant) {
                $andStatut = $andStatut2;
            }
            if ($isAdmin) {$andStatut = $andStatut2;}

            $sql_aff1_et = "SELECT can.*  FROM bg_candidats can, bg_ref_etablissement eta, bg_ref_ville vil,bg_ref_inspection ins
		WHERE can.annee='$annee' AND ddn < '$age_limte_date' AND can.etablissement=eta.id AND vil.id=eta.id_ville AND eta.id_inspection=ins.id AND ins.id=$id_inspection $andWhere $andStatut ";

            // mysqli_set_charset($lien, "utf8mb4");
            $result_aff1_et = bg_query($lien, $sql_aff1_et, __FILE__, __LINE__);
            $total = mysqli_num_rows($result_aff1_et);
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

            echo "<br/><a href='./?exec=etude&id_inspection=$id_inspection'>Retour au Menu Principale</a>";
            echo "<br/> <a style='color:blue;' href='../plugins/fonctions/inc-liste-age_rejet.php?id_inspection=$id_inspection&annee=$annee '>Imprimer en PDF</a>
             ";
            if ($isChefEtablissement) {
                $andStatut = " AND can.etablissement='$code_etablissement' ";
            }

            $sql_aff_et = "SELECT can.* FROM bg_candidats can, bg_ref_etablissement eta, bg_ref_ville vil,bg_ref_inspection ins
		 WHERE can.annee='$annee' AND ddn < '$age_limte_date' AND can.etablissement=eta.id AND vil.id=eta.id_ville  AND eta.id_inspection=ins.id AND ins.id=$id_inspection $andWhere $andStatut
		 ORDER BY nom, prenoms LIMIT $limit, " . MAX_LIGNES;
            //mysqli_set_charset($lien, "utf8mb4");
            $result_aff_et = bg_query($lien, $sql_aff_et, __FILE__, __LINE__);

            //echo $sql_aff;
            $tab_et = array('id_candidat', 'num_dossier', 'num_table', 'serie', 'annee_bac1', 'jury_bac1', 'num_table_bac1', 'nom', 'prenoms', 'sexe', 'ddn', 'ldn', 'pdn', 'nationalite', 'telephone', 'lv1', 'lv2', 'efa', 'efb', 'eps', 'etat_physique', 'type_session', 'etablissement', 'nom_photo', 'login', 'statu', 'maj');
            echo "<table class='spip liste'>";
            $thead_et = "<th>N°</th><th>N° table</th><th>Noms</th><th>Sexe</th><th>Date de Naissance</th><th>Etablissement</th><th>Centre</th><th>Série</th><th>Statu</th><th>Etudier</th>";
            while ($row_aff_et = mysqli_fetch_array($result_aff_et)) {
                foreach ($tab_et as $var) {
                    $$var = $row_aff_et[$var];
//Decommenter la ligne precedente et commenter la ligne suivante pour gerer l'encodage sur la lsite des candidats
                    //                $$var=utf8_encode($row_aff[$var]);
                }
                $sexe = selectReferentiel($lien, 'sexe', $sexe);
                $serie = selectReferentiel($lien, 'serie', $serie);
                $etablissementRef = selectReferentiel($lien, 'etablissement', $etablissement);
                $id_inspection = selectInspectionByEtablissement($lien, $etablissement);
                $centreEts = centreEts($lien, $etablissement);

                if ($sexe == 'F') {
                    $civ = 'Mlle';
                    $sexe = 'F';
                } else {
                    $civ = 'M.';
                    $sexe = 'M';
                }
                if ($statu == 1) {
                    $statu = 'Actif';
                } else {
                    $statu = 'Inactif';
                }

                $tbody_et .= "<tr><td>$id_candidat</td><td>$num_table</td><td><a href='./?exec=etude&etape=etudier&id_candidat=$id_candidat&id_inspection=$id_inspection&annee=$annee' title=\"Saisi par $login le $maj\">$nom $prenoms</a><td>$sexe</td><td>" . afficher_date($ddn) . "</td></td><td>$etablissementRef</td><td>$centreEts</td><td>$serie</td><td>$statu</td><td><a href='./?exec=etude&etape=etudier&id_candidat=$id_candidat&id_inspection=$id_inspection&annee=$annee'>Etudier</a></td></tr>";

            }
            echo $thead_et, $tbody_et;
            echo "</table>";
        }

        if (isset($_REQUEST['etape']) && $_REQUEST['etape'] == 'doublons') {
            $tri_doublons = debut_cadre_enfonce('', '', '', 'Recherche de doublons');
            $andStatut = '';
            //$andInspection='';

            if (isset($_POST['tri_doublons_region']) && $_POST['tri_doublons_region'] > 0) {$andStatut = " AND ins.id_region=$tri_doublons_region ";
                $andInspection = " WHERE id_region=$tri_doublons_region ";}
            if (isset($_POST['tri_doublons_inspection']) && $_POST['tri_doublons_inspection'] > 0) {$andStatut = " AND eta.id_inspection=$tri_doublons_inspection ";}

            $tri_doublons .= "<form name='form_tri_doublons' method='POST' ><table>";
            if ($isAdmin) {
                $tri_doublons .= "<tr>";

                $tri_doublons .= "<td><center><select style='width:50%' name='tri_doublons_region' onchange='document.forms.form_tri_doublons.submit()'>" . optionsReferentiel($lien, 'region', $tri_doublons_region, $WhereRegion) . "</select></td>";

                $tri_doublons .= "  <td><center><select style='width:50%' name='tri_doublons_inspection' onchange='document.forms.form_tri_doublons.submit()'>" . optionsReferentiel($lien, 'inspection', $tri_doublons_inspection, $andInspection) . "</select></td>
	   </tr>";
            }
            if ($isEncadrant) {
                $andInspection = " WHERE id_region=$id_region ";
                $andStatut .= "AND ins.id_region=$id_region";
                $tri_doublons .= "<tr>";

                $tri_doublons .= "  <td><center><select style='width:30%' name='tri_doublons_inspection' onchange='document.forms.form_tri_doublons.submit()'>" . optionsReferentiel($lien, 'inspection', $tri_doublons_inspection, $andInspection) . "</select></td>
	   </tr>";
            }
            if ($isInspection) {
                $andInspection = " WHERE id_region=$id_region ";
                $andStatut .= "AND ins.id_region=$id_region AND ins.id=$id_inspection";
                $tri_doublons .= "<tr>";

                $tri_doublons .= "  <td><center><select style='width:30%' name='tri_doublons_inspection' onchange='document.forms.form_tri_doublons.submit()'>" . optionsReferentiel($lien, 'inspection', $tri_doublons_inspection, $andInspection) . "</select></td>
	   </tr>";
            }

            $tri_doublons .= "</table></form>";
            $tri_doublons .= fin_cadre_enfonce();
            echo $tri_doublons;
            echo "<h4><center><a href='./?exec=etude&id_inspection=$id_inspection'>Retour au Menu principal</a></center></h4>";

            doublons($lien, $annee, $andStatut);

        }

        if (isset($_REQUEST['etape']) && $_REQUEST['etape'] == 'etablissement') {
            echo "<h4><center><a href='./?exec=etude&id_inspection=$id_inspection'>Retour au Menu principal</a></center></h4>";
            etablissementAndCentre($lien, $id_inspection, $isAdmin);

        }
        if (isset($_REQUEST['etape']) && $_REQUEST['etape'] == 'propriete') {
            echo "<h5><center><a href='./?exec=etude&id_inspection=$id_inspection'>Retour au Menu principal</a></center></h5>";
            echo debut_gauche();

            echo debut_boite_info(), "<b>Propriétés d'un Etablissement</b>",
            fin_boite_info();
            echo debut_boite_info(), "<b>Nom de l'Etablissement</b><br/>Nom de l'Etablissement<br/><br/>
             <b>Region</b><br/>Ce champs désigne la région Educative dans laquelle se situe l'établissement<br/><br/>
            <b>Préfecture</b><br/>Choisisez la Préfecture dans laquelle se situe cet établissement<br/><br/>
             <b>Ville</b><br/>Choisissez la ville dans laquelle est l'Etablissement<br/><br/>
           <b>Type Etablissement</b><br/>Choisir le Type d'établissement<br/>
           <b>Confetionnel</b><br/> <b>Privée</b><br/> <b>Public</b><br/>",
            fin_boite_info();

            echo debut_droite();

            ModifierEtablissement($lien, $id_inspection, $code_etablissement);

            echo fin_gauche();
        }

        if (isset($_REQUEST['etape']) && $_REQUEST['etape'] == 'matiere_facultatives') {
            echo "<h2><center>STATISTIQUES DES EPREUVES FACULTATIVES AU BAC 1</center></h2>";
            echo "<h4><center><a href='./?exec=etude&id_inspection=$id_inspection'>Retour au Menu principal</a></center></h4>";
            CentreAndMatFacultatives($lien, $id_inspection, $andStatut = '');

        }

        if (isset($_REQUEST['etape']) && $_REQUEST['etape'] == 'jury') {

            $tri_jury = debut_cadre_enfonce('', '', '', 'Traitement de Jury de candidats');

            if (isset($_POST['tri_jury_numero']) && $_POST['tri_jury_numero'] != '') {
                $andWhere .= " AND (can.id_candidat='$tri_jury_numero' OR can.num_table='$tri_jury_numero') ";
            }

            if (isset($_POST['tri_jury_nom']) && $_POST['tri_jury_nom'] != '') {
                $andWhere .= " AND can.nom LIKE '%$tri_jury_nom%' ";
            }
            if (isset($_POST['tri_jury_jury']) && $_POST['tri_jury_jury'] != '') {

                $andWhere .= " AND rep.jury=$tri_jury_jury ";
            }
            if ($isInspection) {
                $andWhereCen = " AND eta.si_centre='oui' AND ins.id_region='$id_region' AND eta.id_inspection=$id_inspection ";
                $andWhereEta = " AND eta.si_centre='non' AND ins.id_region='$id_region' AND eta.id_inspection=$id_inspection ";
                $andWhereRegion = " WHERE id_region='$id_region' AND id=$id_inspection";
                $WhereRegion = "WHERE id=$id_region";
            }
            if ($isEncadrant) {
                $andStatut2 = "";
                $andWhereRegion = " WHERE id_region='$id_region'";

            }

            if (!isset($_REQUEST['limit'])) {
                $limit = 0;
            }

            if ($limit < 0) {
                $limit = 0;
            }

            $tri_jury .= "<form name='form_tri_jury' method='POST' ><table>";

            $tri_jury .= "<tr><td><input type='hidden' name='limit' value='$limit' /><input type='hidden' name='annee' value='$annee' /><input type='hidden' name='id_inspection' value='$id_inspection' /></td>
			<td></td></tr>";

            $tri_jury .= "<tr style='width:30%'><td>N°: <input type='text' name='tri_jury_numero'   style='width:50%'/></td>
            <td style='width:30%'>Jury: <input type='number' name='tri_jury_jury'   style='width:50%'/></td>
            <td style='width:40%'>Nom: <input type='text' name='tri_jury_nom'  style='width:50%'/><input type='submit' value='OK' /></td></tr>";

            $tri_jury .= "</table></form>";
            $tri_jury .= fin_cadre_enfonce();
            echo $tri_jury;

            //resultats du tri des jury
            $pre = ($limit - MAX_LIGNES);
            if ($isChefEtablissement) {
                $andStatut = " AND can.etablissement='$code_etablissement' ";
            }

            if ($isInspection || $isEncadrant) {
                $andStatut = $andStatut2;
            }

            $sql_aff1 = "SELECT can.*  FROM bg_candidats can, bg_ref_etablissement eta, bg_repartition rep
	        WHERE can.annee='$annee' AND can.etablissement=eta.id AND can.id_candidat=rep.id_candidat $andWhere $andStatut ";
            $result_aff1 = bg_query($lien, $sql_aff1, __FILE__, __LINE__);
            $total = mysqli_num_rows($result_aff1);
            echo "<p><center><a href='./?exec=etude&id_inspection=$id_inspection'>Retour au Menu principal</a></center><p>";
            echo "<p class='center'>";
            if ($limit > 0) {
                echo "<a href=\"javascript:document.forms['form_tri_jury'].limit.value=" . ($limit - MAX_LIGNES) . ";document.forms['form_tri_jury'].submit();\"><<::Précédent </a>";
            }

            $limit2 = ($limit + MAX_LIGNES);
            if ($total < $limit2) {
                $limit2 = $total;
            }

            echo " $total enregistrements au total [$limit .... $limit2] ";
            if ($total > ($limit + MAX_LIGNES)) {
                echo "<a href=\"javascript:document.forms['form_tri_jury'].limit.value=" . ($limit + MAX_LIGNES) . ";document.forms['form_tri_jury'].submit();\"> Suivant::>></a>";
            }

            echo "</p>";
            if ($isChefEtablissement) {
                $andStatut = " AND can.etablissement='$code_etablissement' ";
            }

            $sql_aff = "SELECT can.*,rep.jury  FROM bg_candidats can, bg_ref_etablissement eta, bg_repartition rep
             WHERE can.annee='$annee' AND can.etablissement=eta.id AND can.id_candidat=rep.id_candidat $andWhere $andStatut
             ORDER BY rep.jury,nom, prenoms LIMIT $limit, " . MAX_LIGNES;

            $result_aff = bg_query($lien, $sql_aff, __FILE__, __LINE__);

            $tab = array('id_candidat', 'num_dossier', 'num_table', 'serie', 'annee_bac1', 'jury_bac1', 'num_table_bac1', 'nom', 'prenoms', 'sexe', 'ddn', 'ldn', 'pdn', 'nationalite', 'telephone', 'lv1', 'lv2', 'efa', 'efb', 'eps', 'etat_physique', 'type_session', 'etablissement', 'nom_photo', 'login', 'maj');
            echo "<table class='spip liste'>";
            $thead = "<th>Jury</th><th>N°</th><th>N° table</th><th>Noms</th><th>Sexe</th><th>Etablissement</th><th>Centre</th><th>Série</th>";
            while ($row_aff = mysqli_fetch_array($result_aff)) {
                foreach ($tab as $var) {
                    $$var = $row_aff[$var];
//Decommenter la ligne precedente et commenter la ligne suivante pour gerer l'encodage sur la lsite des candidats

                }
                $sexe = selectReferentiel($lien, 'sexe', $sexe);
                $serie = selectReferentiel($lien, 'serie', $serie);
                $etablissementRef = selectReferentiel($lien, 'etablissement', $etablissement);
                $jury = $row_aff['jury'];
                $id_inspection = selectInspectionByEtablissement($lien, $etablissement);
                $centreEts = centreEts($lien, $etablissement);

                if ($sexe == 'F') {
                    $civ = 'Mlle';
                    $sexe = 'F';
                } else {
                    $civ = 'M.';
                    $sexe = 'M';
                }

                $tbody .= "<tr><td>$jury</td><td>$id_candidat</td><td>$num_table</td><td><a href='./?exec=etude&etape=maj&id_candidat=$id_candidat&id_inspection=$id_inspection&annee=$annee' title=\"Saisi par $login le $maj\">$nom $prenoms</a></td><td>$sexe</td>
		<td>$etablissementRef</td><td>$centreEts</td><td>$serie</td></tr>";

            }
            echo $thead, $tbody;
            echo "</table>";
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

        //Tri des candidats - Formulaire
        if ((isset($_REQUEST['etape']) && ($_GET['etape'] == 'afficher' || $_GET['etape'] == 'modifier' || $_GET['etape'] == 'supprimer')) || isset($_POST['modifier']) || isset($_POST['etudier']) || isset($_POST['tri_numero_et'])) {
            $tri_et = debut_cadre_enfonce('', '', '', 'Tri des candidats à Etudier');
            $andWhereEta = " AND eta.si_centre='non' AND ins.id=eta.id_inspection AND eta.id_inspection=$id_inspection ";
            $andWhereCen = " AND eta.si_centre='oui' AND ins.id=eta.id_inspection AND eta.id_inspection=$id_inspection ";

            if (isset($_POST['tri_annee']) && $_POST['tri_annee'] > 0) {
                $annee = $tri_annee;
            }

            if (isset($_POST['tri_session']) && $_POST['tri_session'] > 0) {
                $andWhere .= " AND can.type_session=$tri_session ";
            }

            if (isset($_POST['tri_serie']) && $_POST['tri_serie'] > 0) {
                $andWhere .= " AND can.serie=$tri_serie ";
            }

            if (isset($_POST['tri_sexe']) && $_POST['tri_sexe'] > 0) {
                $andWhere .= " AND can.sexe=$tri_sexe ";
            }

            if (isset($_POST['tri_centre']) && $_POST['tri_centre'] > 0) {$andWhere .= " AND can.centre=$tri_centre ";
                $andWhereEta .= " AND eta.id_centre=$tri_centre";}

            if (isset($_POST['tri_etablissement']) && $_POST['tri_etablissement'] > 0) {$andWhere .= " AND can.etablissement=$tri_etablissement ";
                $andWhereEta .= " ";}
            if (isset($_POST['tri_numero_et']) && $_POST['tri_numero_et'] != '') {
                $andWhere .= " AND (can.id_candidat='$tri_numero_et' OR can.num_table='$tri_numero_et') ";
            }

            if (isset($_POST['tri_nom']) && $_POST['tri_nom'] != '') {
                $andWhere .= " AND can.nom LIKE '%$tri_nom%' ";
            }

            if (isset($_POST['tri_ef']) && $_POST['tri_ef'] > 0) {
                $andWhere .= " AND (can.efa=$tri_ef OR can.efb=$tri_ef)";
            }

            if (!isset($_REQUEST['limit'])) {
                $limit = 0;
            }

            if ($limit < 0) {
                $limit = 0;
            }
            // 2nde option avec "se positionne sur la derniere opption du select
            echo '<script type="text/javascript">',
            '$(document).ready(function() {',
            '$(\'#tri_centre\').on(\'change\', function() {',
            'document.getElementById(\'tri_centre\').value=$(\'#tri_centre option:last\').val();',
            '});',
            '});',
                '</script>'
            ;

            $tri_et .= "<form name='form_tri' method='POST' ><table>";
            $tri_et .= "<tr><td><input type='hidden' name='limit' value='$limit' /><input type='hidden' name='annee' value='$annee' /><input type='hidden' name='id_inspection' value='$id_inspection' /></td>
				<td></td></tr>";
            $tri_et .= "<tr><td><center><select style='width:50%' name='tri_annee' onchange='document.forms.form_tri.submit()'>" . optionsAnnee(2020, $tri_annee) . "</select></center></td>
				<td><center><select style='width:50%' name='tri_session' onchange='document.forms.form_tri.submit()'>" . optionsReferentiel($lien, 'type_session', $tri_session) . "</select></center></td></tr>";
            $tri_et .= "<tr> <td><center><select style='width:50%' name='tri_centre' id='tri_centre' onchange='document.forms.form_tri.submit()'>" . optionsEtablissement($lien, $tri_centre, $andWhereCen, true, ',bg_ref_inspection ins ', '', 'Centre') . "</select></center></td>
            <td><center><select style='width:50%' name='tri_etablissement' id='tri_etablissement' onchange='document.forms.form_tri.submit()'>" . optionsEtablissement($lien, $tri_etablissement, $andWhereEta, true, ',bg_ref_inspection ins ') . "</select></center></td></tr>";

            $tri_et .= "<tr><td><center><select style='width:50%' name='tri_sexe' onchange='document.forms.form_tri.submit()'>" . optionsReferentiel($lien, 'sexe', $tri_sexe) . "</select></center></td>
                <td><center><select style='width:50%' name='tri_serie' onchange='document.forms.form_tri.submit()'>" . optionsReferentiel($lien, 'serie', $tri_serie, 'WHERE id <4') . "</select></center></td></tr>";

            $tri_et .= "<tr><td style='width:50%'><center>N°: <input type='text' name='tri_numero_et' style='width:42%' /></center></td>
		<td style='width:50%'><center>Nom: <input type='text' name='tri_nom'  style='width:35%' /><input type='submit' value='OK' /></td></center></tr>";
            $tri_et .= "</table></form>";
            $tri_et .= fin_cadre_enfonce();
            echo $tri_et;

            //Affichage des resultats du tri
            $pre = ($limit - MAX_LIGNES);
            if ($isChefEtablissement) {
                $andStatut = " AND can.etablissement='$code_etablissement' ";
            }

            if ($isInspection || $isEncadrant) {
                $andStatut = $andStatut2;
            }
            if ($isAdmin) {$andStatut = $andStatut2;}

            $sql_aff1_et = "SELECT can.*  FROM bg_candidats can, bg_ref_etablissement eta, bg_ref_ville vil,bg_ref_inspection ins
		WHERE can.annee='$annee' AND can.etablissement=eta.id AND vil.id=eta.id_ville AND eta.id_inspection=ins.id AND ins.id=$id_inspection $andWhere $andStatut ";

            // mysqli_set_charset($lien, "utf8mb4");
            $result_aff1_et = bg_query($lien, $sql_aff1_et, __FILE__, __LINE__);
            $total = mysqli_num_rows($result_aff1_et);
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

            echo "<br/><a href='./?exec=etude&id_inspection=$id_inspection'>Retour au Menu Principale</a>";
            echo "</p>";
            if ($isChefEtablissement) {
                $andStatut = " AND can.etablissement='$code_etablissement' ";
            }

            $sql_aff_et = "SELECT can.* FROM bg_candidats can, bg_ref_etablissement eta, bg_ref_ville vil,bg_ref_inspection ins
		 WHERE can.annee='$annee' AND can.etablissement=eta.id AND vil.id=eta.id_ville  AND eta.id_inspection=ins.id AND ins.id=$id_inspection $andWhere $andStatut
		 ORDER BY nom, prenoms LIMIT $limit, " . MAX_LIGNES;
            //mysqli_set_charset($lien, "utf8mb4");
            $result_aff_et = bg_query($lien, $sql_aff_et, __FILE__, __LINE__);

            //echo $sql_aff;
            $tab_et = array('id_candidat', 'num_dossier', 'num_table', 'serie', 'annee_bac1', 'jury_bac1', 'num_table_bac1', 'nom', 'prenoms', 'sexe', 'ddn', 'ldn', 'pdn', 'nationalite', 'telephone', 'lv1', 'lv2', 'efa', 'efb', 'eps', 'etat_physique', 'type_session', 'etablissement', 'nom_photo', 'login', 'statu', 'maj');
            echo "<table class='spip liste'>";
            $thead_et = "<th>N°</th><th>N° table</th><th>Noms</th><th>Sexe</th><th>Etablissement</th><th>Centre</th><th>Série</th><th>Statu</th><th>Etudier</th>";
            while ($row_aff_et = mysqli_fetch_array($result_aff_et)) {
                foreach ($tab_et as $var) {
                    $$var = $row_aff_et[$var];
//Decommenter la ligne precedente et commenter la ligne suivante pour gerer l'encodage sur la lsite des candidats
                    //                $$var=utf8_encode($row_aff[$var]);
                }
                $sexe = selectReferentiel($lien, 'sexe', $sexe);
                $serie = selectReferentiel($lien, 'serie', $serie);
                $etablissementRef = selectReferentiel($lien, 'etablissement', $etablissement);
                $id_inspection = selectInspectionByEtablissement($lien, $etablissement);
                $centreEts = centreEts($lien, $etablissement);

                if ($sexe == 'F') {
                    $civ = 'Mlle';
                    $sexe = 'F';
                } else {
                    $civ = 'M.';
                    $sexe = 'M';
                }
                if ($statu == 1) {
                    $statu = 'Actif';
                } else {
                    $statu = 'Inactif';
                }

                $tbody_et .= "<tr><td>$id_candidat</td><td>$num_table</td><td><a href='./?exec=etude&etape=etudier&id_candidat=$id_candidat&id_inspection=$id_inspection&annee=$annee's title=\"Saisi par $login le $maj\">$nom $prenoms</a><td>$sexe</td></td><td>$etablissementRef</td><td>$centreEts</td><td>$serie</td><td>$statu</td><td><a href='./?exec=etude&etape=etudier&id_candidat=$id_candidat&id_inspection=$id_inspection&annee=$annee'>Etudier</a></td></tr>";

            }
            echo $thead_et, $tbody_et;
            echo "</table>";
        }

        if ((isset($_REQUEST['etape']) && ($_GET['etape'] == 'rejet')) || isset($_POST['reactiver'])) {
            $tri = debut_cadre_enfonce('', '', '', 'Tri des candidats en Instance de Rejet');

            $andWhereCen .= " AND eta.si_centre='oui'";
            $andWhereEta .= " AND eta.si_centre='non'";
            if (isset($_POST['tri_annee']) && $_POST['tri_annee'] > 0) {
                $annee = $tri_annee;
            }

            if (isset($_POST['tri_session']) && $_POST['tri_session'] > 0) {
                $andWhere .= " AND can.type_session=$tri_session ";
            }

            if (isset($_POST['tri_serie']) && $_POST['tri_serie'] > 0) {
                $andWhere .= " AND can.serie=$tri_serie ";
            }

            if (isset($_POST['tri_sexe']) && $_POST['tri_sexe'] > 0) {
                $andWhere .= " AND can.sexe=$tri_sexe ";
            }

            if (isset($_POST['tri_centre']) && $_POST['tri_centre'] > 0) {$andWhere .= " AND can.centre=$tri_centre ";
                $andWhereEta .= " AND eta.id_centre=$tri_centre";}
            if (isset($_POST['tri_inspection']) && $_POST['tri_inspection'] > 0) {$andWhere .= " AND eta.id_inspection=$tri_inspection ";
                if ($isEncadrant) {$andWhereRegion .= " AND id_region=$tri_region";} elseif ($isInspection) {
                    // $andWhereRegion .= "WHERE id_region=$tri_region";
                    // $andWhereEta .= " AND ins.id_region=$tri_region AND ins.id=eta.id_inspection AND eta.id_inspection=$tri_inspection";
                    $andWhereCen .= "AND si_centre='oui' AND ins.id=eta.id_inspection  AND eta.id_inspection=$tri_inspection";
                } else {
                    $andWhereEta .= " AND ins.id=eta.id_inspection AND eta.id_inspection=$tri_inspection";
                    $andWhereCen .= "AND si_centre='oui' AND ins.id=eta.id_inspection AND ins.id_region=$tri_region AND eta.id_inspection=$tri_inspection";
                }
                $andWhereEta = " AND eta.si_centre='non' AND eta.id_inspection=$tri_inspection ";
                $andWhereCen = " AND eta.si_centre='oui' AND ins.id=eta.id_inspection AND eta.id_inspection=$tri_inspection ";}

            if (isset($_POST['tri_etablissement']) && $_POST['tri_etablissement'] > 0) {$andWhere .= " AND can.etablissement=$tri_etablissement ";
                $andWhereEta .= " ";}
            if (isset($_POST['tri_motif']) && $_POST['tri_motif'] != 0) {
                $andWhere .= " AND   can.motif=$tri_motif ";
            }

            if (isset($_POST['tri_nom']) && $_POST['tri_nom'] != '') {
                $andWhere .= " AND can.nom LIKE '%$tri_nom%' ";
            }

            if ($isInspection) {
                $andWhereCen = " AND eta.si_centre='oui' AND ins.id_region=$id_region AND eta.id_inspection=$id_inspection ";
                $andWhereEta = " AND eta.si_centre='non' AND ins.id_region=$id_region AND eta.id_inspection=$id_inspection ";
                $andWhereRegion = " WHERE id_region=$id_region AND id=$id_inspection";
                //$WhereRegion = "WHERE id=$id_region";
            }
            if ($isEncadrant) {
                $andWhereRegion = " WHERE id_region='$id_region'";
                $andWhereCen .= " AND eta.si_centre='oui' AND ins.id=eta.id_inspection AND ins.id_region=$id_region";
                $andWhereEta .= " AND eta.si_centre='non' AND ins.id=eta.id_inspection AND ins.id_region=$id_region";
            }
            if (!isset($_REQUEST['limit'])) {
                $limit = 0;
            }

            if ($limit < 0) {
                $limit = 0;
            }

            $tri .= "<form action='' name='form_tri_rejet' method='POST' >
            <table>";
            echo '<script type="text/javascript">',
            'function refrechPage(){',
            'window.location("./?exec=etude&etape=rejet&id_inspection=' . $id_inspection . '")',
            '};',
                '</script>'
            ;
            $tri .= "<tr><td><input type='hidden' name='limit' value='$limit' /><input type='hidden' name='annee' value='$annee' /><input type='hidden' name='id_inspection' value='$id_inspection' /></td>
				<td></td></tr>";

            if (!$isChefEtablissement || $isInspection || $isAdmin) {
                $tri .= "<tr><td><center><select style='width:50%' name='tri_annee' onchange='document.forms.form_tri_rejet.submit()'>" . optionsAnnee(2020, $tri_annee) . "</select></center></td>
				<td><center><select style='width:50%' name='tri_session' onchange='document.forms.form_tri_rejet.submit()'>" . optionsReferentiel($lien, 'type_session', $tri_session) . "</select></center></td></tr>";
                $tri .= "<tr><td><center><select style='width:50%' name='tri_inspection' onchange='document.forms.form_tri_rejet.submit()'>" . optionsReferentiel($lien, 'inspection', $tri_inspection, $WhereRegion) . "</select></td>
                <td><center><select style='width:50%' name='tri_centre' onchange='document.forms.form_tri_rejet.submit()'>" . optionsEtablissement($lien, $tri_centre, $andWhereCen, true, ',bg_ref_inspection ins ', '', 'Centre') . "</select></center></td></tr>";

            }

            $tri .= "<tr><td><center><select style='width:50%' name='tri_motif' onchange='document.forms.form_tri_rejet.submit()'>" . optionsReferentielTxt($lien, 'motif', $tri_motif) . "</select></center></td>
		<td style='width:50%'><center>Nom: <input type='text' name='tri_nom'  style='width:35%' /><input type='submit' value='OK' /></td></center></tr>";
            $tri .= "<tr> <td colspan=2> <center><input name='reactiver' type='submit' value='Réactiver'  onclick='refrechPage()' /></center></td></tr>";

            $tri .= "</table>";
            $tri .= fin_cadre_enfonce();
            echo $tri;

            // Affichage des resultats du tri
            $pre = ($limit - MAX_LIGNES);
            if ($isChefEtablissement) {
                $andStatut = " AND can.etablissement='$code_etablissement' ";
            }

            if ($isAdmin) {$andStatut = $andStatut2;}

            $sql_aff1 = "SELECT can.*,mtf.motif  as motifcan FROM bg_candidats can, bg_ref_etablissement eta,bg_ref_inspection ins,bg_ref_motif mtf
                    WHERE can.motif=mtf.id AND can.annee='$annee' AND can.etablissement=eta.id   AND eta.id_inspection=ins.id  AND can.statu=0 $andWhere $andStatut ";

            // mysqli_set_charset($lien, "utf8mb4");
            $result_aff1 = bg_query($lien, $sql_aff1, __FILE__, __LINE__);
            $total = mysqli_num_rows($result_aff1);
            echo "<p class='center'>";
            if ($limit > 0) {
                echo "<a href=\"javascript:document.forms['form_tri_rejet'].limit.value=" . ($limit - MAX_LIGNES) . ";document.forms['form_tri_rejet'].submit();\"><<::Précédent </a>";
            }

            $limit2 = ($limit + MAX_LIGNES);
            if ($total < $limit2) {
                $limit2 = $total;
            }

            echo " $total enregistrements au total [$limit .... $limit2] ";
            if ($total > ($limit + MAX_LIGNES)) {
                echo "<a href=\"javascript:document.forms['form_tri_rejet'].limit.value=" . ($limit + MAX_LIGNES) . ";document.forms['form_tri_rejet'].submit();\"> Suivant::>></a>";
            }

            echo "<br/><a href='./?exec=etude&id_inspection=$id_inspection'>Retour au Menu Principale</a>";
            echo "<br/><a style='color:red;' href='./?exec=etude&etape=rejet&id_inspection=$id_inspection'>Recharger la Page</a>";
            echo "</p>";

            $sql_aff = "SELECT can.*,mtf.motif as motifcan FROM bg_candidats can, bg_ref_etablissement eta,  bg_ref_inspection ins,bg_ref_motif mtf
            WHERE can.motif=mtf.id AND can.annee='$annee' AND can.etablissement=eta.id   AND eta.id_inspection=ins.id
                     AND can.statu=0 $andWhere $andStatut
                     ORDER BY nom, prenoms LIMIT $limit, " . MAX_LIGNES;
            //mysqli_set_charset($lien, "utf8mb4");

            $result_aff = bg_query($lien, $sql_aff, __FILE__, __LINE__);

            //echo $sql_aff;
            $tab = array('id_candidat', 'num_dossier', 'num_table', 'motifcan', 'serie', 'annee_bac1', 'jury_bac1', 'num_table_bac1', 'nom', 'prenoms', 'sexe', 'ddn', 'ldn', 'pdn', 'nationalite', 'telephone', 'lv1', 'lv2', 'efa', 'efb', 'eps', 'etat_physique', 'type_session', 'etablissement', 'nom_photo', 'login', 'statu', 'maj');
            echo "<table class='spip liste'>";
            $thead = "<th>Check</th><th>N°</th><th>N° table</th><th>Noms</th><th>Sexe</th><th>Etablissement</th><th>Centre</th><th>Série</th><th>Statu</th><th>Motif</th>";
            while ($row_aff = mysqli_fetch_array($result_aff)) {
                foreach ($tab as $var) {
                    $$var = $row_aff[$var];
                    //Decommenter la ligne precedente et commenter la ligne suivante pour gerer l'encodage sur la lsite des candidats
                    //                $$var=utf8_encode($row_aff[$var]);
                }
                $sexe = selectReferentiel($lien, 'sexe', $sexe);
                $serie = selectReferentiel($lien, 'serie', $serie);
                $etablissementRef = selectReferentiel($lien, 'etablissement', $etablissement);
                $id_inspection = selectInspectionByEtablissement($lien, $etablissement);
                $centreEts = centreEts($lien, $etablissement);

                if ($sexe == 'F') {
                    $civ = 'Mlle';
                    $sexe = 'F';
                } else {
                    $civ = 'M.';
                    $sexe = 'M';
                }
                if ($statu == 1) {
                    $statu = 'Actif';
                } else {
                    $statu = 'Inactif';
                }

                $tbody .= "<tr><td><input type='checkbox' name='tabCandidat[$id_candidat]' value='$id_candidat' /></td><td>$id_candidat</td><td>$num_table</td><td><a href='./?exec=etude&etape=etudier&id_candidat=$id_candidat&id_inspection=$id_inspection&annee=$annee' title=\"Saisi par $login le $maj\">$nom $prenoms</a><td>$sexe</td></td><td>$etablissementRef</td><td>$centreEts</td><td>$serie</td><td>$statu</td><td>$motifcan</td></tr>";

            }
            echo $thead, $tbody;
            echo "</table>";

            echo "</form>";

        }
        //Exécution de la commande qui va permettre de modifier les etablissements

        if (isset($_POST['reactiver'])) {

            $tabCandidat = $_POST['tabCandidat'];
            if (!empty($_POST['tabCandidat'])) {
                $date_liste_additive = recupAdditiveDate($lien);
                if ($date_liste_additive == null) {
                    $additive = 0;
                    foreach ($tabCandidat as $candidat) {
                        $sql = "UPDATE bg_candidats SET statu=1 WHERE id_candidat=$candidat";
                        bg_query($lien, $sql, __FILE__, __LINE__);
                    }
                } else {
                    if (checkThePast($date_liste_additive)) {
                        foreach ($tabCandidat as $candidat) {
                            $sql = "UPDATE bg_candidats SET statu=1, additive =1  WHERE id_candidat=$candidat";
                            bg_query($lien, $sql, __FILE__, __LINE__);
                        }
                    } else {
                        foreach ($tabCandidat as $candidat) {
                            $sql = "UPDATE bg_candidats SET statu=1 WHERE id_candidat=$candidat";
                            bg_query($lien, $sql, __FILE__, __LINE__);
                        }
                    }
                }
                // echo ("oki");
            } else {
                // echo ("nothing");
            }

        }

        if (isset($_REQUEST['etape']) && ($_GET['etape'] == 'inserer' || $_GET['etape'] == 'maj') && (!isset($_POST['modifier'])) && !isset($_POST['tri_numero_et'])) {

            echo debut_gauche();
            echo debut_boite_info(), "<b>N° et N° de table</b><br/>",
            '<span style="color:red;">Ne rien renseigner dans ces champs. Merci !!!</span><br/>',
            fin_boite_info();

            //echo debut_boite_info(), "<b>N° et N° de table</b><br/>Ne rien renseigner dans ces champs<br/>", fin_boite_info();
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
            if ($serie != 1) {
                $disabled4 = 'disabled';
            } else {
                $disabled4 = '';
            }

            $photo = '../photos/defaut/sans-visage2.png';
            $whereEta = " AND id_centre!=0 " . $andEtablissementFormulaire2;
            $etat_physique = $type_session = $pdn = $nationalite = 1;
            $atelier1 = $atelier2 = 0;
            $atelier3 = 7;
            $eps_ch1 = $eps_ch2 = $eps_ch3 = 0;
            $additive = 0;
            $inspection = selectInspection($lien, $id_inspection);
            echo "<h4><center><a href='./?exec=etude&id_inspection=$id_inspection'>Retour au Menu principal</a></center></h4>";

            $whereEta = ' id_inspection=' . $id_inspection;

            /**Variable aleatoire */
            //Mettre le champ en majuscule si la case reste cochée
            echo '<script type="text/javascript">',
            '$(document).ready(function() {',
            '$(\'#nom\').keyup(function(e) {',
            'if ($("#majusculeok").is(":checked")) {',
            'this.value=this.value.toUpperCase();',
            '}',
            '});',
            '});',
                '</script>'
            ;
            // 2nde option avec "se positionne sur la derniere opption du select
            echo '<script type="text/javascript">',
            '$(document).ready(function() {',
            '$(\'#type_session\').on(\'change\', function() {',
            'document.getElementById(\'type_session\').value=$(\'#type_session option:last\').val();',
            '});',
            '});',
                '</script>'
            ;

            //AJAX
            //$selectCentreFromEtablissement=selectCentreFromEtablissement($lien,7);
            echo '<script>',
            '$(document).ready(function(){',
            '$(\'#etablissement\').change(function(){',
            //Selected value
            'var inputValue = $(this).val();',
            'alert("value in js "+inputValue);',

            //Ajax for calling php function
            '$.ajax({',
            'type: "_POST",',
            'url: ".",',
            'data: {"value":inputValue},',
            'success: function(result) {',
            'alert(result);',
            'alert($(\'#type_session\').val());',
            //'    $("#centre").html(result);',
            'document.getElementById(\'centre\').value=result;',
            '}',
            '});',
            '});',
            '});',
                '</script>';

            function processDrpdown($selectedVal)
            {
                echo "Selected value in php " . $selectedVal;
            }

            /** Fin Variable aleatoire */

            if ($_GET['etape'] == 'maj') {
                $sql_mod = "SELECT * FROM bg_candidats WHERE annee=$annee AND id_candidat=$id_candidat $andStatut ";
                $result_mod = bg_query($lien, $sql_mod, __FILE__, __LINE__);
                $tab = array('id_candidat', 'num_dossier', 'num_table', 'serie', 'annee_bac1', 'jury_bac1', 'num_table_bac1', 'nom', 'prenoms', 'sexe', 'ddn', 'ldn', 'pdn', 'nationalite', 'telephone', 'lv1', 'lv2', 'efa', 'efb', 'eps', 'etat_physique', 'atelier1', 'atelier2', 'atelier3', 'centre', 'jury', 'type_session', 'etablissement', 'nom_photo', 'login', 'maj', 'statu', 'motif');
                $row_mod = mysqli_fetch_array($result_mod);
                foreach ($tab as $var) {
                    $$var = $row_mod[$var];
                }
                if ($statu == 1) {
                    $statutSelected1 = 'selected';
                    $statutSelected2 = '';
                } else {
                    $statutSelected1 = '';
                    $statutSelected2 = 'selected';
                }
                $ddn = afficher_date($ddn);
                $whereEta = ' AND id_centre!=0 ' . $andEtablissementFormulaire;
                if ($nom_photo != '') {
                    $photo = "../photos/$annee" . '/' . $nom_photo;
                } else {
                    if ($sexe == 1) {
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
                if ($serie != 1) {
                    $disabled4 = 'disabled';
                } else {
                    $disabled4 = '';
                }
                $inspection = selectInspection($lien, $id_inspection);
                $whereEta = ' id_inspection=' . $id_inspection;

            }
            $form = "<div class='formulaire_spip'><form method='POST' action='' name='form_saisie' >";
            $form .= "<table>";
            //$form.="<tr><td colspan=3 class='center'><img src='$photo' width=100 onload=\"if(document.forms['form_saisie'].serie.value!=1) {document.forms['form_saisie'].lv2.disabled=true; }\" /></td></tr>";
            $form .= "<tr><td>Année</td><td colspan=2><select name='annee' $disabledAnnee >" . optionsAnnee(2018, $annee) . "</td></tr>";
            //        $form.="<tr><td>Année</td><td colspan=2><input type='text' name='annee' value='$annee' disabled></td></tr>";
            $form .= "<tr><td>N°</td><td colspan=2><input size='40' type='text' name='id_candidat' value='$id_candidat' disabled></td></tr>";
            $form .= "<tr><td>N° de table</td><td colspan=2><input size='40' type='text' name='num_table' value='$num_table' disabled></td></tr>";
            $form .= "<tr><td>Inspection</td><td colspan=2><input size='40' type='text' name='inspection' value='$inspection' disabled></td></tr>";
            $etablissementRef = selectReferentiel($lien, 'etablissement', $etablissement);
            if ($isChefEtablissement) {
                $whereEta = "eta.id=$code_etablissement";
                $form .= "<tr><td>Etablissement</td><td colspan=2><select name='etablissement'>" . etablissementCentre($lien, $etablissement, $whereEta) . "</select></td></tr>";
            } else {
                $form .= "<tr><td>Etablissement</td><td colspan=2><select name='etablissement' onChange=\"\">" . etablissementCentre($lien, $etablissement, $whereEta) . "</select></td></tr>";

            }

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

            //Mettre la premiere lettre en majuscule
            if ($_GET['etape'] == 'inserer') {
                $FirstMajuscule = " OnkeyUp=\"javascript:this.value=this.value.charAt(0).toUpperCase() + this.value.substr(1);\" ";
            } else {
                $first = '';
            }

            //Mettre la premiere lettre en majuscule + Empecher espace en début de saisie
            if ($_GET['etape'] == 'inserer') {
                $NoSpaceFirstMajuscule = " OnkeyUp=\"javascript:this.value=this.value.trimStart();this.value=this.value.charAt(0).toUpperCase() + this.value.substr(1);\" ";
            } else {
                $first = '';
            }

            $form .= "<tr><td>Nom</td><td colspan=2><input size='40' type='text' id='nom' name='nom' value=\"$nom\" $NoSpace required /><input type='checkbox' id='majusculeok' name='majusculeok' checked /><label><i>Décochez pour activer le champ 'Nom' en minuscule</i></label></td></tr>";
            $form .= "<tr><td>Prénoms</td><td colspan=2><input size='40' type='text' id='prenoms' name='prenoms' value=\"$prenoms\" $NoSpaceFirstMajuscule required /></td></tr>";
            $form .= "<tr><td>Sexe</td><td colspan=2><select name='sexe'>" . optionsReferentiel($lien, 'sexe', $sexe) . "</select></td></tr>";
            $form .= "<tr><td>Etat Physique</td><td colspan=2><select name='etat_physique' onBlur=\"if(this.value!=1){eps_ch3.value=0;eps.value=2;atelier3.value=0;atelier1.disabled=atelier2.disabled=atelier3.disabled=true;}else{eps_ch3.value=7;eps.value=1;atelier3.value=7;atelier1.disabled=atelier2.disabled=false;} \" onChange=\"if(this.value!=1){eps_ch3.value=0;eps.value=2;atelier3.value=0;atelier1.disabled=atelier2.disabled=atelier3.disabled=true;}else{eps_ch3.value=7;eps.value=1;atelier3.value=7;atelier1.disabled=atelier2.disabled=false;}\"  >" . optionsReferentiel($lien, 'etat_physique', $etat_physique) . "</select></td></tr>";
            $form .= "<tr><td>Date de naissance</td><td colspan=2><input size='40' type='text' id='ddn' placeholder='31/12/1990' name='ddn' value='$ddn' pattern='^(0?[1-9]|[12][0-9]|3[01])[\/](0?[1-9]|1[012])[\/]\d{4}$' required /></td></td></tr>";
            $form .= "<tr><td>Lieu de naissance</td><td colspan=2><input size='40' type='text' id='ldn' name='ldn' value=\"$ldn\" $FirstMajuscule required /></td></td></tr>";
            $form .= "<tr><td>Pays de naissance</td><td colspan=2><select name='pdn' onBlur=\"document.forms['form_saisie'].nationalite.value=this.value; \">" . optionsReferentiel($lien, 'pays', $pdn) . "</select></td></tr>";
            $form .= "<tr><td>Nationalité</td><td colspan=2><select name='nationalite'>" . optionsReferentiel($lien, 'pays', $nationalite) . "</select></td></tr>";
            $form .= "<tr><td>Téléphone</td><td colspan=2><input size='40' type='tel' minlength='8' maxlength='8' id='telephone' name='telephone' value=\"$telephone\"  pattern='[0-9]{8}' required></td></td></tr>";
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
            $form .= " <input type='hidden' name='additive' value='$additive' />";
            //        $form.="<tr><td>Photo</td><td colspan=2><input type='file' name='nom_photo' value='$nom_photo'></td></td></tr>";
            if ($isChefEtablissement) {
                $form .= "<tr><td colspan=3><input type='hidden' name='type_session' value=1 /></td></tr>";
            } else {
                $form .= "<tr id='c_type_session'><td>Session</td><td colspan=2><select name='type_session' id='type_session'>" . optionsReferentiel($lien, 'type_session', $type_session) . "</select></td></tr>";

                if ($statut == "Admin") {
                    $form .= "<tr><td>Statut candidat</td><td><select value='$statu' name='statu' id='statu'>
                <option value=1 $statutSelected1 >Actif</option>
                <option value=0 $statutSelected2 >Inactif</option>
        </select></td></tr>";
                } else {
                    $form .= " <input type='hidden' name='statu' value='$statu' />";
                }
            }

            $form .= "<tr><td colspan=3></fieldset></td></tr>";

            $form .= "<tr><td colspan=3><input type='hidden' name='id_inspection' value='$id_inspection' /><input type='hidden' name='MAX_FILE_SIZE' value='2000000'></td></tr>";

            $js = "onClick=\"if(document.forms['form_saisie'].etablissement.value==0
						|| document.forms['form_saisie'].serie.value==0
						|| document.forms['form_saisie'].sexe.value==0
						|| document.forms['form_saisie'].pdn.value==0
						|| document.forms['form_saisie'].nationalite.value==0
						|| document.forms['form_saisie'].telephone.value==0
						|| document.forms['form_saisie'].eps.value==0
						|| document.forms['form_saisie'].type_session.value==0
						|| (document.forms['form_saisie'].serie.value==1 && document.forms['form_saisie'].lv2.value==0)
						|| (document.forms['form_saisie'].eps.value==1 && (document.forms['form_saisie'].atelier1.value==0
                        || document.forms['form_saisie'].atelier2.value==0
                        || document.forms['form_saisie'].atelier3.value==0))
						)
			{alert('Remplir les champs obligatoires'); return false; }
			else {alert('Opération effectuée avec succès');return true;}\"; ";
            if ($_GET['etape'] == 'inserer') {
                $form .= "<tr><td class='center'><input class='submit' type='submit' name='enregistrer' value='Enregistrer' $js /></td><td class='center'><input type='reset' value='Annuler' /></td>";
            }
            if ($_GET['etape'] == 'maj') {
                if ($isInspection) {
                    $form .= "<tr><td></td><td></td>";
                } else {
                    $form .= "<tr>
					<td class='center'><input class='' type='submit' name='modifier' value='Modifier' $js /></td> <td></td>";
                }
            }
            $form .= "<td class='center'><input type='button' name='quitter' value='Quitter' onClick=\"window.location='" . generer_url_ecrire('etude') . "&id_inspection=$id_inspection&etape=afficher&annee=$annee';\" /></td></tr>";
            $form .= "</table></form></div>";
            $form .= "<SCRIPT LANGUAGE='JavaScript'> document.forms.form_saisie.num_dossier.focus();</SCRIPT>";
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

        if (isset($_REQUEST['etape']) && ($_GET['etape'] == 'etudier') && (!isset($_POST['etudier']) && (!isset($_POST['tri_numero_et'])))) {

            echo debut_gauche();
            echo debut_boite_info(), "<b>Etablissement de Provenance</b><br/>Cette rubrique comporte l'etablissement ,le centre et l'inspection du candidat<br/>", fin_boite_info();
            echo debut_boite_info(), "<b>Identité du Candidat</b><br/>Cette partie comporte le Nom,Prénoms,la date et lieu de Naissance ainsi que le numéro de Téléphone du candidat", fin_boite_info();
            echo debut_boite_info(), "<b>Statu</b><br/>Sélectionnez le Statu 'Actif' si le candidat est autorisé à composer et 'Inactif' si le dossier comporte des irégularités", fin_boite_info();
            echo debut_boite_info(), "<b>Motif</b><br/>Choisissez le motif de la Désactivation du candidat,Champs obligatoire", fin_boite_info();

            echo debut_droite();

            $disabled = '';
            if ($isAdmin) {
                $disabledAnnee = '';
            } else {
                $disabledAnnee = 'disabled';
            }
            if ($serie != 1) {
                $disabled4 = 'disabled';
            } else { $disabled4 = '';}

            $photo = '../photos/defaut/sans-visage2.png';
            $whereEta = " AND id_centre!=0 " . $andEtablissementFormulaire2;
            $etat_physique = $type_session = $pdn = $nationalite = 1;
            $atelier1 = $atelier2 = 0;
            $atelier3 = 7;
            $eps_ch1 = $eps_ch2 = $eps_ch3 = 0;
            $inspection = selectInspection($lien, $id_inspection);
            echo "<h4><center><a href='./?exec=etude&etape=afficher&id_inspection=$id_inspection'>Retour à La liste des Candidats</a></h4>";

            $whereEta = ' id_inspection=' . $id_inspection;

            /**Variable aleatoire */
            //Mettre le champ en majuscule si la case reste cochée
            echo '<script type="text/javascript">',
            '$(document).ready(function() {',
            '$(\'#nom\').keyup(function(e) {',
            'if ($("#majusculeok").is(":checked")) {',
            'this.value=this.value.toUpperCase();',
            '}',
            '});',
            '});',
                '</script>'
            ;
            // 2nde option avec "se positionne sur la derniere opption du select
            echo '<script type="text/javascript">',
            '$(document).ready(function() {',
            '$(\'#type_session\').on(\'change\', function() {',
            'document.getElementById(\'type_session\').value=$(\'#type_session option:last\').val();',
            '});',
            '});',
                '</script>'
            ;

            //AJAX
            //$selectCentreFromEtablissement=selectCentreFromEtablissement($lien,7);
            echo '<script>',
            '$(document).ready(function(){',
            '$(\'#etablissement\').change(function(){',
            //Selected value
            'var inputValue = $(this).val();',
            'alert("value in js "+inputValue);',

            //Ajax for calling php function
            '$.ajax({',
            'type: "_POST",',
            'url: ".",',
            'data: {"value":inputValue},',
            'success: function(result) {',
            'alert(result);',
            'alert($(\'#type_session\').val());',
            //'    $("#centre").html(result);',
            'document.getElementById(\'centre\').value=result;',
            '}',
            '});',
            '});',
            '});',
                '</script>';

            function processDrpdown($selectedVal)
            {
                echo "Selected value in php " . $selectedVal;
            }

            /** Fin Variable aleatoire */

            if ($_GET['etape'] == 'etudier') {
                $sql_mod = "SELECT * FROM bg_candidats WHERE annee=$annee AND id_candidat=$id_candidat $andStatut ";
                $result_mod = bg_query($lien, $sql_mod, __FILE__, __LINE__);
                $tab = array('id_candidat', 'num_dossier', 'num_table', 'serie', 'annee_bac1', 'jury_bac1', 'num_table_bac1', 'nom', 'prenoms', 'sexe', 'ddn', 'ldn', 'pdn', 'nationalite', 'telephone', 'lv1', 'lv2', 'efa', 'efb', 'eps', 'etat_physique', 'atelier1', 'atelier2', 'atelier3', 'centre', 'jury', 'type_session', 'etablissement', 'nom_photo', 'login', 'maj', 'statu', 'motif');
                $row_mod = mysqli_fetch_array($result_mod);
                foreach ($tab as $var) {
                    $$var = $row_mod[$var];
                }
                if ($statu == 1) {
                    $statutSelected1 = 'selected';
                    $statutSelected2 = '';
                } else {
                    $statutSelected1 = '';
                    $statutSelected2 = 'selected';
                }
                $ddn = afficher_date($ddn);
                $whereEta = ' AND id_centre!=0 ' . $andEtablissementFormulaire;
                if ($nom_photo != '') {$photo = "../photos/$annee" . '/' . $nom_photo;} else {if ($sexe == 1) {
                    $photo = '../photos/defaut/sans-visage1.png';
                } elseif ($sexe == 2) {
                    $photo = '../photos/defaut/sans-visage2.png';
                }}
                if ($eps == 2) {
                    $disabled2 = 'disabled';
                } else {
                    $disabled2 = '';
                }
                if ($serie != 1) {
                    $disabled4 = 'disabled';
                } else { $disabled4 = '';}
                if ($statu == 0) {
                    $disableMotif = '';
                } else {
                    $disableMotif = 'disabled';
                }

                $inspection = selectInspection($lien, $id_inspection);
                $whereEta = ' id_inspection=' . $id_inspection;

            }
            $form = "<div class='formulaire_spip'><form method='POST' action='' name='form_saisie' >";
            $form .= "<table>";

            $form .= "<tr><td>Inspection</td><td><input size='40' type='text' name='inspection' value='$inspection' disabled></td>
            <td><select name='etablissement' disabled >" . etablissementCentre($lien, $etablissement, $whereEta) . "</select></td>
            </tr>";
            $etablissementRef = selectReferentiel($lien, 'etablissement', $etablissement);

            $form .= "<tr><td colspan=3><fieldset><legend>Identité du candidat</legend></td></tr>";

            $form .= "<tr><td>Nom</td><td><input size='40' type='text' id='nom' name='nom' value=\"$nom\" disabled/></td>
             <td colspan=2><input size='70%' type='text' id='prenoms' name='prenoms' value=\"$prenoms\" disabled/></td>
            </tr>";

            $form .= "<tr><td>Telephone</td><td><input size='40' type='tel' minlength='8' maxlength='8' id='telephone' name='telephone' value=\"$telephone\" disabled></td>
             <td colspan=2><input size='70%' type='text' id='ddn'   name='ddn' value='née le $ddn à $ldn'  disabled /></td>
         </tr>";
            $form .= "<tr><td colspan=3></fieldset></td></tr>";

            $form .= "<tr><td colspan=3><fieldset><legend>Autres</legend></td></tr>";

            if (($statut == "Admin") || ($statut == "DExCC") || ($statut == "Etude")) {
                $form .= "<tr><td>Statut candidat</td><td colspan=2><select value='$statu' name='statu' id='statu' onBlur=\"if(this.value==1){motif.value=0;motif.disabled=true;}else{motif.disabled=false}\" onChange=\"if(this.value==1){motif.value=0;motif.disabled=true;}else{motif.disabled=false}\">
                <option value=1 $statutSelected1 >Actif</option>
                <option value=0 $statutSelected2 >Inactif</option>
        </select></td></tr>";
            } else {
                $form .= " <input type='hidden' name='statu' value='$statu' />";
            }
            $form .= "<tr><td>Motif</td><td colspan=2><select name='motif' value='$motif'   $disableMotif>" . optionsReferentielTxt($lien, 'motif', $motif) . "</select></td></tr>";

            // }

            $form .= "<tr><td colspan=3></fieldset></td></tr>";

            $form .= "<tr><td colspan=3><input type='hidden' name='id_inspection' value='$id_inspection' /><input type='hidden' name='MAX_FILE_SIZE' value='2000000'></td></tr>";

            $js = "onClick=\"if(document.forms['form_saisie'].statu.value==0 && document.forms['form_saisie'].motif.value==0)
            {alert('Le Choix du Motif est obligatoire si le Candidat est Inactif'); return false; }
            // else {alert('Le Choix du Motif est obligatoire si le Candidat est Inactif');return true;}\"; ";
            if ($_GET['etape'] == 'inserer') {
                $form .= "<tr><td class='center'><input class='submit' type='submit' name='enregistrer' value='Enregistrer' $js /></td><td class='center'><input type='reset' value='Annuler' /></td>";
            }
            if ($_GET['etape'] == 'etudier') {

                $form .= "<tr>

					<td class='center'><input class='' type='submit' name='etudier' value='Modifier' $js /></td>
                    <td></td>";

            }
            $form .= "<td class='center'><input type='button' name='quitter' value='Quitter' onClick=\"window.location='" . generer_url_ecrire('etude') . "&id_inspection=$id_inspection&etape=afficher&annee=$annee';\" /></td></tr>";
            $form .= "</table></form></div>";
            $form .= "<SCRIPT LANGUAGE='JavaScript'> document.forms.form_saisie.statu.focus();</SCRIPT>";
            echo $form;

            //Affichage des historiques s'il en existe
            if ($_GET['etape'] == 'etudier') {
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

        if (!$isChefEtablissement) {
            if (isset($_GET['etape']) && $etape == 'scan') {
                if (!isset($_POST['id_candidat']) || isset($_POST['upload'])) {
                    $form = "<FORM name='form_candidat' method='POST' action=''>";
                    $form .= "<TABLE><tr><td><input type='number' id='id_candidat' name='id_candidat' min=1 required size=10 maxlength=15  /></td><td><input type='submit' class='fondo' value='OK' /></td></tr>
						<tr><td colspan=2><center><input type='button' value='Quitter' onClick=\"window.location='" . generer_url_ecrire('etude') . "&id_inspection=$id_inspection';\"/></center></td></tr></TABLE>";
                    $form .= "<SCRIPT LANGUAGE='JavaScript'> document.forms.form_candidat.id_candidat.focus();</SCRIPT>";
                    $form .= "</FORM>";
                    echo $form;
                }
                if (isset($_POST['id_candidat']) && !isset($_POST['upload'])) {
                    $id_candidat = $_POST['id_candidat'];
                    if (strpos($id_candidat, '-') > 0) {
                        $andWhere = " AND can.num_table='$num_table' ";
                    } else {
                        $andWhere = " AND can.id_candidat='$id_candidat' ";
                    }

                    $sql = "SELECT sexe, eta.etablissement, ser.serie, nom, prenoms, can.num_table, can.id_candidat "
                        . " FROM bg_ref_serie ser, bg_ref_etablissement eta, bg_candidats can "
                        . " WHERE can.etablissement=eta.id AND can.serie=ser.id $andWhere "
                        . " LIMIT 0,1 ";
                    $result = bg_query($lien, $sql, __FILE__, __LINE__);
                    if ($result and mysqli_num_rows($result) > 0) {

                        $row = mysqli_fetch_array($result);
                        $tab = array('sexe', 'etablissement', 'serie', 'nom', 'prenoms', 'num_table', 'id_candidat');
                        foreach ($tab as $val) {
                            $$val = $row[$val];
                        }

                        $eta = getRewriteString($etablissement);
                        $noms = $nom . ' ' . $prenoms;

                        $fichier_ss_extension = $annee . '_' . $eta . '_' . $serie . '_' . $id_candidat;
                        $chemin = $lien_photos . $fichier_ss_extension;
                        $sql_rech = "SELECT * FROM bg_candidats WHERE annee='$annee' AND id_candidat='$id_candidat' AND nom_photo!='' ";
                        $result_rech = bg_query($lien, $sql_rech, __FILE__, __LINE__);
                        if (($result_rech and mysqli_num_rows($result_rech) > 0) || file_exists($lien_photos . $fichier_ss_extension . '.JPG')
                            || file_exists($lien_photos . $fichier_ss_extension . '.jpg')
                            || file_exists($lien_photos . $fichier_ss_extension . '.JPEG')
                            || file_exists($lien_photos . $fichier_ss_extension . '.jpeg')
                            || file_exists($lien_photos . $fichier_ss_extension . '.PNG')
                            || file_exists($lien_photos . $fichier_ss_extension . '.png')
                            || file_exists($lien_photos . $fichier_ss_extension . '.GIF')
                            || file_exists($lien_photos . $fichier_ss_extension . '.gif')) {
                            $row_rech = mysqli_fetch_array($result_rech);
                            $chemin = $lien_photos . $row_rech['nom_photo'];
                            $affiche_photo = "<img src='$chemin' width=150>";

                        } else {
                            if ($sexe == 1) {
                                $affiche_photo = "<img src='../photos/defaut/visage.jpg' width=150 alt='Photo' title='photo'>";
                            } else {
                                $affiche_photo = "<img src='../photos/defaut/visage.jpg' width=120>";
                            }

                        }
                        $form1 = "<FORM name='form_photos' method='POST' action='' enctype='multipart/form-data'>";
                        $form1 .= "<TABLE>";
                        $form1 .= "<tr><td colspan=2 class='center' >$affiche_photo</td></tr>";
                        $form1 .= "<tr><td>Numéro</td><td><input type='text' size=10 disabled value='$id_candidat'/></td></tr>";
                        $form1 .= "<tr><td>Nom et prénoms</td><td><input type='text' value=\"$noms\" disabled/></td></tr>";
                        $form1 .= "<tr><td>Etablissement</td><td><input type='text' value=\"$etablissement\" disabled/></td></tr>";
                        $form1 .= "<tr><td>Série</td><td><input type='text' value='$serie' disabled/></td><td></td></tr>";
                        $form1 .= "<tr><td>Photo</td><td><input type='file' value='' name='photo_candidat' class='fondo'/></td></tr>";
                        $form1 .= "<tr><td><input type='hidden' name='MAX_FILE_SIZE' value='2000000'></td><td><input type='hidden' name='id_table' value='$id_table'><input type='hidden' name='id_table_humain' value='$id_table_humain'></td></tr>";
                        $form1 .= "<tr><td><input type='hidden' name='id_candidat' value='$id_candidat'></td><td><input type='hidden' name='type_session' value='$type_session'></td></tr>";
                        $form1 .= "<tr><td><input type='hidden' name='serie' value='$serie'></td><td><input type='hidden' name='etablissement' value='$etablissement'></td></tr>";

                        $form1 .= "<tr><td class='center'><input type='submit' class='fondo' value='Télécharger' name='upload'/></td><td class='center'><input type='button' value='Quitter' onClick=\"window.location='" . generer_url_ecrire('etude') . "&etape=scan&id_inspection=$id_inspection';\"/></td></tr>";
                        $form1 .= "</TABLE>";
                        $form1 .= "</FORM>";
                        echo $form1;
                    } else {
                        echo "Candidat de numéro <font color='red'>$id_candidat</font> inexistant. <a href='" . generer_url_ecrire('etude') . "&etape=scan&id_inspection=$id_inspection&annee=$annee' >Cliquer ici pour recommencer</a>";
                    }

                }

                if (isset($_POST['upload']) && $_FILES['photo_candidat']['size'] > 0 && $_FILES['photo_candidat']['error'] == 0) {
                    if ($_FILES['photo_candidat']['size'] <= 2000000) {
                        $infosFichier = pathinfo($_FILES['photo_candidat']['name']);
                        $extension = $infosFichier['extension'];
                        $extensions_autorisees = array('gif', 'png', 'jpeg', 'jpg', 'GIF', 'PNG', 'JPEG', 'JPG');
                        if (in_array($extension, $extensions_autorisees)) {
                            // On peut valider le fichier et le stocker définitivement
                            move_uploaded_file($_FILES['photo_candidat']['tmp_name'], $lien_photos . basename($_FILES['photo_candidat']['name']));
                            echo "L'envoi a bien été effectué !";
                            if (file_exists($lien_photos . $_FILES['photo_candidat']['name'])) {
                                $eta = getRewriteString($etablissement);
                                $oldName = $_FILES['photo_candidat']['name'];
                                $newName = $annee . '_' . $eta . '_' . $serie . '_' . $id_candidat . '.' . $extension;
                                rename($lien_photos . $oldName, $lien_photos . $newName);
                            }
                        }

                        $fileName = $_FILES['photo_candidat']['name'];
                        $tmpName = $_FILES['photo_candidat']['tmp_name'];
                        $fileSize = $_FILES['photo_candidat']['size'];
                        $fileType = $_FILES['photo_candidat']['type'];

                        if (!get_magic_quotes_gpc()) {
                            $fileName = addslashes($fileName);
                        }
                        $sql2 = "UPDATE bg_candidats SET nom_photo='$newName' WHERE id_candidat='$id_candidat' AND annee=$annee ";
                        bg_query($lien, $sql2, __FILE__, __LINE__);

                    }
                }
            }
        }

        if (isset($_GET['etape']) && $etape == 'doc') {
            $doc = debut_cadre_couleur('', '', '', "DOCUMENTATION");
            $doc .= "<table>";
            $doc .= "<tr><td colspan=2>La gestion des candidats comporte l'enregistrement des candidats (lien 'Insérer'), la visualisation et la mise à jour des candidats déjà enregistrés (lien 'Liste'),
				l'impression des listes des candidats enregistrés (lien 'Impressions') et la vérification des doublons (lien 'Doublons'). </td></tr>";
            $doc .= "<tr><td>1. Enregistrement des candidats</td><td>Cliquer sur le lien 'Insérier'<br/>Renseigner les divers champs<br/>Cliquer sur 'Enregistrer'<br>A la fin des saisies, cliquer sur 'Quitter'</td></tr>";
            $doc .= "<tr><td>2. Visualisation</td><td>Cliquer sur le lien 'Liste'<br/>Cette liste peut être triée par série et/ou par sexe<br/>Un candidat peut être recherché par son numéros ou par son nom</td></tr>";
            $doc .= "<tr><td>3. Mise à jour</td><td>Pour modifier ou supprimer un dossier, il suffit de cliquer sur le nom du candidat<br/>Le formulaire apparaît et la modification ou la suppression peut se faire</td></tr>";
            $doc .= "<tr><td>4. Impressions</td><td>Par ce lien, la liste des candidats peut être imprimée pour correction et signature des candidats</td></tr>";
            $doc .= "<tr><td>5. Doublons</td><td>A la fin des saisies, il faudra cliquer sur le lien 'Doublons'<br/>Et supprimer le dossier double</td></tr>";
            $doc .= "<tr><td>6. Divers</td><td>Pour autres questions, bien vouloir appeler le Centre National de Documentation Pédagogique et des Technologies de l’Information et de la Communication pour l’Education (CNDP-TICE)</td></tr>";
            $doc .= "</table>";
            $doc .= fin_cadre_couleur();
            echo $doc;
        }
        // echo fin_cadre_trait_couleur();
        // echo fin_grand_cadre(), fin_gauche(), fin_page();
        echo fin_cadre_trait_couleur();
        echo fin_grand_cadre(), fin_page();
    }
}

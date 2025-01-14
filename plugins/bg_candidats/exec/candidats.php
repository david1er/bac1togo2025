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
    // var_dump($sql);

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

        $tableau .= "<tr><td>" . dSC($etablissement) . "</td><td align='rigth'>$nbre</td>
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
    || document.forms['form_ins'].id_etat.value==0
    || document.forms['form_ins'].id_type_etablissement.value==0)
                        {alert('Les champs  REGION, VILLE, PREFECTURE, doivent être remplis'); return false; }
                        else {alert('Opération effectuée avec succès');return true;}\"; ";
    // $form_ins .= "";

    $form .= "<td><input type='button' style='width: 50%;  height: 2em;' value='ANNULER & QUITTER' class='submit' onClick=window.location='" . generer_url_ecrire('candidats') . "&id_inspection=$id_inspection'></td>";
    $form .= "<td>&nbsp;<input style='width: 50%;  height: 2em;' type='submit' value='ENREGISTRER' name='maj_eta' class='submit' onClick=window.location='" . generer_url_ecrire('candidats') . "&id_inspection=$id_inspection' $controle_forms /></td>";

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
function selectEtablissementByCode($lien, $id_code)
{
    $sql = "SELECT id FROM bg_ref_etablissement WHERE code='$id_code' AND si_centre='non'";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $row = mysqli_fetch_array($result);
    return $row['id'];
}
function selectSerie($lien, $serie)
{
    $sql = "SELECT id FROM bg_ref_serie WHERE serie='$serie'";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $row = mysqli_fetch_array($result);
    return $row['id'];
}
function selectSexe($sexe)
{
    if ($sexe == 'F') {
        return 1;
    } else {
        return 2;
    }

}
function selectCentreByEtablissement($lien, $id)
{
    $sql = "SELECT id_centre FROM bg_ref_etablissement WHERE id='$id' ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $row = mysqli_fetch_array($result);
    return $row['id_centre'];

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
    $sql_effectif = "SELECT COUNT(id_candidat) AS effectif,COUNT(case when sexe='1' then 1 end) as fille_count,COUNT(case when sexe='2' then 1 end) as garcon_count FROM bg_candidats WHERE etablissement = $id_etablissement";
    $result_effectif = bg_query($lien, $sql_effectif, __FILE__, __FILE__);
    $sol_effectif = mysqli_fetch_array($result_effectif);
    return array($sol_effectif['effectif'], $sol_effectif['fille_count'], $sol_effectif['garcon_count']);
}

function effectifEtsAttendu($lien, $id_etablissement)
{
    $sql_effectifAttendu = "SELECT SUM(nombre_garcon+nombre_fille) AS effectifAttendu,nombre_fille,nombre_garcon FROM bg_ref_etablissement WHERE id = $id_etablissement  GROUP BY id";
    $result_effectifAttendu = bg_query($lien, $sql_effectifAttendu, __FILE__, __FILE__);
    $sol_effectifAttendu = mysqli_fetch_array($result_effectifAttendu);
    return array($sol_effectifAttendu['effectifAttendu'], $sol_effectifAttendu['nombre_fille'], $sol_effectifAttendu['nombre_garcon']);
}

function CentreAndMatFacultatives($lien, $id_inspection)
{
    if ($_REQUEST['exec'] == 'candidats') {
        echo "<h3><center><a href='./?exec=candidats'>Changer l'inspection</a></center></h3>";
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
		  WHERE reg.id=ins.id_region AND id_centre!=0 AND eta.si_centre='non' AND eta.id_inspection=$id_inspection
		  GROUP BY eta.id_centre,eta.etablissement";

    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $tab = array('etablissement', 'id');
    if (mysqli_num_rows($result) > 0) {

        if ($_REQUEST['exec'] == 'candidats') {
            echo "<h3><center><a href='./?exec=candidats'>Changer l'inspection</a> </center></h3>";
        }
        if ($uStatus) {
            echo "<h2><center><a href='../plugins/fonctions/inc-all-etablissement_centre.php'>Imprimer tous les Etablissements </a><center></h2>";
        }
        echo "<h2><center>Liste des Etablissements de l'inspection : " . selectInspection($lien, $id_inspection) . "<center></h2>",
        debut_boite_info(), "<table>";

        echo "<h2><center><a href='../plugins/fonctions/inc-etablissement_centre.php?id_inspection=$id_inspection'>Imprimer en pdf</a><center></h2>";

        echo "<th>Centre d'écrit</th><th>Etablissement</th><th>Effectif/Effectif attendu</th><th>Filles</th><th>Garçons</th>";
        while ($row = mysqli_fetch_array($result)) {

            foreach ($tab as $val) {
                $$val = addslashes($row[$val]);
            }
            /** Effectif etablissement se change en 0 lorsque le champ est null */
            /*  if(effectifEts($lien, $id)[0] == NULL){
            effectifEts($lien, $id)[0]=0;
            $color='red';
            }
            if(effectifEts($lien, $id)[1] == NULL){
            effectifEts($lien, $id)[1]=0;
            $color='red';
            }
            if(effectifEts($lien, $id)[2] == NULL){
            effectifEts($lien, $id)[2]=0;
            $color='red';
            } */
            /** Effectif Attendu etablissement se change en 0 lorsque le champ est null */
            /* if(effectifEtsAttendu($lien, $id)[0] == NULL){
            $transforme_val = effectifEtsAttendu($lien, $id)[0];
            $transforme_val = $transforme_val??0;
            //effectifEtsAttendu($lien, $id)[0]= isset($transforme_val) ? $transforme_val: 0;
            var_dump(gettype($transforme_val));
            var_dump(effectifEtsAttendu($lien, $id)[0]);
            $color='blue';
            }
            if(effectifEtsAttendu($lien, $id)[1] == NULL){
            effectifEtsAttendu($lien, $id)[1]=(int)effectifEtsAttendu($lien, $id)[1]=0;
            $color='blue';
            }
            if(effectifEtsAttendu($lien, $id)[2] == NULL){
            effectifEtsAttendu($lien, $id)[2] = (int)effectifEtsAttendu($lien, $id)[2]= 0;
            $color='blue';
            } */

            if (effectifEts($lien, $id)[0] == effectifEtsAttendu($lien, $id)[0]) {
                $color = 'green';
            }
            if (effectifEts($lien, $id)[1] == effectifEtsAttendu($lien, $id)[1]) {
                $color = 'green';
            }
            if (effectifEts($lien, $id)[2] == effectifEtsAttendu($lien, $id)[2]) {
                $color = 'green';
            }
            if (effectifEts($lien, $id)[0] == 0 && effectifEtsAttendu($lien, $id)[0] == 0 && effectifEts($lien, $id)[0] == effectifEtsAttendu($lien, $id)[0]) {
                $color = 'orange';
            }
            if (effectifEts($lien, $id)[1] == 0 && effectifEtsAttendu($lien, $id)[1] == 0 && effectifEts($lien, $id)[1] == effectifEtsAttendu($lien, $id)[1]) {
                $color = 'orange';
            }
            if (effectifEts($lien, $id)[2] == 0 && effectifEtsAttendu($lien, $id)[2] == 0 && effectifEts($lien, $id)[2] == effectifEtsAttendu($lien, $id)[2]) {
                $color = 'orange';
            }
            if (effectifEts($lien, $id)[0] < effectifEtsAttendu($lien, $id)[0]) {
                $color = 'blue';
            }
            if (effectifEts($lien, $id)[1] < effectifEtsAttendu($lien, $id)[1]) {
                $color = 'blue';
            }
            if (effectifEts($lien, $id)[2] < effectifEtsAttendu($lien, $id)[2]) {
                $color = 'blue';
            }
            if (effectifEts($lien, $id)[0] > effectifEtsAttendu($lien, $id)[0] || effectifEts($lien, $id)[1] > effectifEtsAttendu($lien, $id)[1] || effectifEts($lien, $id)[2] > effectifEtsAttendu($lien, $id)[2]) {
                $color = 'red';
            }
            echo "<tr>
                        <td><strong>" . centreEts($lien, $id) . "</strong></td>
                        <td>" . $etablissement . "</td>
                        <td><font color='$color'>" . effectifEts($lien, $id)[0] . "/</font><font color='$color'>" . effectifEtsAttendu($lien, $id)[0] . "</font></td>
                        <td><font color='$color'>" . effectifEts($lien, $id)[1] . "/</font><font color='$color'>" . effectifEtsAttendu($lien, $id)[1] . "</font></td>
                        <td><font color='$color'>" . effectifEts($lien, $id)[2] . "/</font><font color='$color'>" . effectifEtsAttendu($lien, $id)[2] . "</font></td>
                </tr>";

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
    //var_dump($sol['si_centre']);

    if ($sol['si_centre'] == 'non') {
        $sql11 = "SELECT etablissement FROM bg_ref_etablissement WHERE id=$id_centre";
        $result11 = bg_query($lien, $sql11, __FILE__, __LINE__);
        $sol11 = mysqli_fetch_array($result11);
        $centre = ($sol11['etablissement']);
        //  var_dump($centre);
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

function exec_candidats()
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
    if ($login_etablissement == 1) {
        $login_etablissement_view = 'Etablissement';
    } else {
        $login_etablissement_view = '';
    }
    if ($login_inspection == 1) {
        $login_inspection_view = 'Inspection';
    } else {
        $login_inspection_view = '';
    }
    if ($login_encadrant == 1) {
        $login_encadrant_view = 'Encadrant';
    } else {
        $login_encadrant_view = '';
    }

    if (isAutorise(array('Admin', 'Dre', 'Notes', 'Informaticien', $login_etablissement_view, $login_inspection_view, $login_encadrant_view, 'Operateur'))) {
        // if (isAutorise(array('Admin', 'Etablissement', 'Operateur', 'Encadrant', 'Inspection'))) {
        //Enregistrer un candidat
        if (isset($_POST['enregistrer'])) {
            $additive_date = recupAdditiveDate($lien);
            if ($additive_date == null) {
                $additive = 0;
            } else {
                if (checkThePast($additive_date)) {
                    $additive = 1;
                } else {
                    $additive = 0;
                }
            }

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
                $statu = 1;
                $motif = 0;
                $prenoms = ucwords($prenoms);
                $sql_ins = "INSERT INTO bg_candidats (`annee`, `serie`, `num_dossier`, `annee_bac1`, `jury_bac1`, `num_table_bac1`, `nom`, `prenoms`, `sexe`, `ddn`, `ldn`, `pdn`, `nationalite`, `telephone`, `lv1`, `lv2`, `efa`, `efb`, `eps`, `etat_physique`, `atelier1`, `atelier2`, `atelier3`, `centre`, `type_session`, `etablissement`, `nom_photo`, `login`, `statu`, `motif`, `additive`)
				VALUES ('$annee','$serie','$num_dossier','$annee_bac1','$jury_bac1','$num_table_bac1','$nom','$prenoms','$sexe','$ddn','$ldn','$pdn','$nationalite','$telephone','1','$lv2','$efa','$efb','$eps','$etat_physique','$atelier1','$atelier2','$atelier3','$centre','$type_session','$etablissement','$nom_photo','$login','$statu', '$motif', '$additive') ";
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

        //Supprimer un candidat
        if (isset($_GET['etape']) && $_GET['etape'] == 'supprimer' && $_GET['id_candidat'] != '') {
            $repartition = detectSiSupprimable($lien, $id_candidat);
            if ($repartition == 0) {
                $sql = "INSERT INTO bg_suppr_candidats
                (`id_candidat`, `annee`, `num_dossier`, `num_table`, `serie`, `annee_bac1`, `jury_bac1`, `num_table_bac1`, `nom`, `prenoms`, `sexe`, `ddn`, `ldn`, `pdn`, `nationalite`, `telephone`, `lv1`, `lv2`, `efa`, `efb`, `eps`, `etat_physique`, `atelier1`, `atelier2`, `atelier3`, `centre`,`type_session`, `etablissement`, `nom_photo`, `login`)
                SELECT `id_candidat`, `annee`, `num_dossier`, `num_table`, `serie`, `annee_bac1`, `jury_bac1`, `num_table_bac1`, `nom`, `prenoms`, `sexe`, `ddn`, `ldn`, `pdn`, `nationalite`, `telephone`, `lv1`, `lv2`, `efa`, `efb`, `eps`, `etat_physique`,`atelier1`, `atelier2`, `atelier3`, `centre`, `type_session`, `etablissement`, `nom_photo`, '" . $login . "' FROM bg_candidats WHERE annee=$annee AND id_candidat='$id_candidat' ";
                $result = bg_query($lien, $sql, __FILE__, __LINE__);
                $sql_sup = "DELETE FROM bg_candidats WHERE annee=$annee AND id_candidat='$id_candidat' $andStatut ";
                bg_query($lien, $sql_sup, __FILE__, __LINE__);
            } elseif ($repartition == 1) {
                $sql = "INSERT INTO bg_suppr_candidats
                (`id_candidat`, `annee`, `num_dossier`, `num_table`, `serie`, `annee_bac1`, `jury_bac1`, `num_table_bac1`, `nom`, `prenoms`, `sexe`, `ddn`, `ldn`, `pdn`, `nationalite`, `telephone`, `lv1`, `lv2`, `efa`, `efb`, `eps`, `etat_physique`, `atelier1`, `atelier2`, `atelier3`, `centre`,`type_session`, `etablissement`, `nom_photo`, `login`)
                SELECT `id_candidat`, `annee`, `num_dossier`, `num_table`, `serie`, `annee_bac1`, `jury_bac1`, `num_table_bac1`, `nom`, `prenoms`, `sexe`, `ddn`, `ldn`, `pdn`, `nationalite`, `telephone`, `lv1`, `lv2`, `efa`, `efb`, `eps`, `etat_physique`,`atelier1`, `atelier2`, `atelier3`, `centre`, `type_session`, `etablissement`, `nom_photo`, '" . $login . "' FROM bg_candidats WHERE annee=$annee AND id_candidat='$id_candidat' ";
                $result = bg_query($lien, $sql, __FILE__, __LINE__);
                $sql_sup = "DELETE FROM bg_candidats WHERE annee=$annee AND id_candidat='$id_candidat' $andStatut ";
                bg_query($lien, $sql_sup, __FILE__, __LINE__);

                $sql_sup_rep = "DELETE FROM bg_repartition WHERE annee=$annee AND id_candidat='$id_candidat' ";
                bg_query($lien, $sql_sup_rep, __FILE__, __LINE__);

                echo debut_boite_info(), "Suppression réussie:
                Le candidat a été supprimé avec succès ", fin_boite_info();
            } elseif ($repartition == 2) {
                echo debut_boite_alerte(), "Suppression impossible:
                Vous ne pouvez pas supprimer ce candidat car il a déja une note de sport ", fin_boite_alerte();

            }

        }

        echo debut_grand_cadre();
        echo debut_cadre_trait_couleur('', '', '', "GESTION DES CANDIDATS", "", "", false);

        if (!isset($_REQUEST['id_inspection']) && (!isset($_REQUEST['etape']))) {
            if ($isEncadrant) {
                listeChoix($lien, $andWhereRegion, 'candidats');
            } elseif ($isInspection) {
                listeChoix($lien, $andWhereInspection, 'candidats');
            } else {
                listeChoix($lien, '', 'candidats');
            }

        }

        if (isset($_REQUEST['id_inspection']) && !isset($_REQUEST['etape'])) {
            echo "<div class='onglets_simple clearfix'><ul>";
            echo "<li><a href='./?exec=candidats&etape=nombre_candidats_attendus&id_inspection=$id_inspection' class='ajax'>Candidats attendus</a></li>";

            if (!$isInspection) {
                echo "<li><a href='./?exec=candidats&etape=inserer&id_inspection=$id_inspection' class='ajax'>Insérer</a></li>";
            }
            echo "<li><a href='./?exec=candidats&etape=afficher&id_inspection=$id_inspection' class='ajax'>Liste</a></li>
			<li><a href='./?exec=candidats&etape=imprimer&id_inspection=$id_inspection' class='ajax'>Impressions</a></li>";
            // if ($isChefEtablissement) {
            //     echo "<li><a href='./?exec=candidats&etape=propriete&id_inspection=$id_inspection' class='ajax'>Configuration</a></li>";}

            if (!$isChefEtablissement && !$isInspection) {
                echo "<li><a href='./?exec=candidats&etape=doublons&id_inspection=$id_inspection' class='ajax'>Doublons</a></li>";
                echo "<li><a href='./?exec=candidats&etape=etablissement&id_inspection=$id_inspection' class='ajax'>Etablissement</a></li>";
                echo "<li><a href='./?exec=candidats&etape=matiere_facultatives&id_inspection=$id_inspection' class='ajax'>Mat. Facultatives</a></li>";
                echo "<li><a href='./?exec=candidats&etape=jury&id_inspection=$id_inspection' class='ajax'>Jury</a></li>";
                // if ($statut == "Admin") {
                //     echo "<li><a href='./?exec=candidats&etape=rejet&id_inspection=$id_inspection' class='ajax'>Instance de Rejet</a></li>";}
                echo "<li><a href='./?exec=candidats&etape=scan&id_inspection=$id_inspection' class='ajax'>Photos</a></li>
					<li><a href='./?exec=candidats&etape=ch_statut&id_inspection=$id_inspection' class='ajax'>Statut</a></li>";
            }
            if (!$isChefEtablissement) {
                echo "<li><a href='./?exec=candidats' class='ajax'>Inspections</a></li>";
            }
            if ($statut == 'Admin'|| $statut == 'Informaticien') {
                echo "<li><a href='./?exec=candidats&etape=import&id_inspection=$id_inspection' class='ajax'>Importation</a></li>";

            }
            echo "<li><a href='./?exec=candidats&etape=doc&id_inspection=$id_inspection' class='ajax'>Guide</a></li>";
            echo "<li><a href='./?exec=candidats&action=logout&logout=prive' class='ajax'>Se déconnecter</a></li>";
            echo "</ul>
		</div>";
        }

        if (isset($_REQUEST['etape']) && $_REQUEST['etape'] == 'imprimer') {
            echo etablissementsCandidats($lien, $annee, $id_inspection, $code_etablissement, $statut);
        }

        if (isset($_REQUEST['etape']) && $_REQUEST['etape'] == 'doublons') {
            $tri_doublons = debut_cadre_enfonce('', '', '', 'Recherche de doublons');
            $andStatut = '';
            //$andInspection='';

            if (isset($_POST['tri_doublons_region']) && $_POST['tri_doublons_region'] > 0) {
                $andStatut = " AND ins.id_region=$tri_doublons_region ";
                $andInspection = " WHERE id_region=$tri_doublons_region ";
            }
            if (isset($_POST['tri_doublons_inspection']) && $_POST['tri_doublons_inspection'] > 0) {
                $andStatut = " AND eta.id_inspection=$tri_doublons_inspection ";
            }

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
            echo "<h4><center><a href='./?exec=candidats&id_inspection=$id_inspection'>Retour au Menu principal</a></center></h4>";

            doublons($lien, $annee, $andStatut);

        }

        if (isset($_REQUEST['etape']) && $_REQUEST['etape'] == 'etablissement') {
            echo "<h4><center><a href='./?exec=candidats&id_inspection=$id_inspection'>Retour au Menu principal</a></center></h4>";
            etablissementAndCentre($lien, $id_inspection, $isAdmin);

        }
        if (isset($_REQUEST['etape']) && $_REQUEST['etape'] == 'propriete') {
            echo "<h5><center><a href='./?exec=candidats&id_inspection=$id_inspection'>Retour au Menu principal</a></center></h5>";
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
            echo "<h4><center><a href='./?exec=candidats&id_inspection=$id_inspection'>Retour au Menu principal</a></center></h4>";
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
            echo "<p><center><a href='./?exec=candidats&id_inspection=$id_inspection'>Retour au Menu principal</a></center><p>";
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

                $tbody .= "<tr><td>$jury</td><td>$id_candidat</td><td>$num_table</td><td><a href='./?exec=candidats&etape=maj&id_candidat=$id_candidat&id_inspection=$id_inspection&annee=$annee' title=\"Saisi par $login le $maj\">$nom $prenoms</a></td><td>$sexe</td>
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

        //Changer le statut de candidats
        if ((isset($_REQUEST['etape']) && ($_GET['etape'] == 'ch_statut' || $_GET['etape'] == 'changer')) || isset($_POST['changer'])) {
            $tri = debut_cadre_enfonce('', '', '', 'Tri des candidats');
            $andWhereEta = " AND eta.id_inspection=$id_inspection ";
            if (isset($_POST['tri_annee']) && $_POST['tri_annee'] > 0) {
                $annee = $tri_annee;
            }

            if (isset($_POST['tri_serie']) && $_POST['tri_serie'] > 0) {
                $andWhere .= " AND can.serie=$tri_serie ";
            }

            if (isset($_POST['tri_etablissement']) && $_POST['tri_etablissement'] > 0) {
                $andWhere .= " AND can.etablissement=$tri_etablissement ";
            }

            $tri .= "<form name='form_tri' method='POST' ><table>";
            $tri .= "<tr><td><input type='hidden' name='limit' value='$limit' /><input type='hidden' name='annee' value='$annee' />
            <center><select style='width:50%' name='tri_annee' onchange='document.forms.form_tri.submit()'>" . optionsAnnee(2020, $tri_annee) . "</select></center></td><td></td></tr>";
            $tri .= "<tr><td><center><select style='width:50%' name='tri_etablissement' onchange='document.forms.form_tri.submit()'>" . optionsEtablissement($lien, $tri_etablissement, $andWhereEta) . "</select></center></td>
				<td><center><select style='width:50%' name='tri_serie' onchange='document.forms.form_tri.submit()'>" . optionsReferentiel($lien, 'serie', $tri_serie, 'WHERE id <4') . "</select></center></td></tr>";
            $tri .= "</table></form>";
            $tri .= fin_cadre_enfonce();
            echo "<h4><center><a href='./?exec=candidats&id_inspection=$id_inspection'>Retour au Menu principal</a></center></h4>";
            echo $tri;

            //Affichage des resultats du tri
            if (isset($_POST['tri_etablissement']) && $_POST['tri_etablissement'] > 0) {
                if ($isChefEtablissement) {
                    $andStatut = " AND can.etablissement='$code_etablissement' ";
                }

                $sql_aff = "SELECT can.* FROM bg_candidats can, bg_ref_etablissement eta, bg_ref_ville vil WHERE can.annee='$annee' AND can.etablissement=eta.id AND vil.id=eta.id_ville $andWhere $andStatut ORDER BY nom, prenoms ";
                //  mysqli_set_charset($lien, "utf8mb4");
                $result_aff = bg_query($lien, $sql_aff, __FILE__, __LINE__);
                echo "<center>" . mysqli_num_rows($result_aff) . " enregistrements au total ";
                echo "<br/><a href='./?exec=candidats&id_inspection=$id_inspection'>Retour au Menu principal</a></center>";

                $tab = array('id_candidat', 'num_dossier', 'num_table', 'serie', 'annee_bac1', 'jury_bac1', 'num_table_bac1', 'nom', 'prenoms', 'sexe', 'ddn', 'ldn', 'pdn', 'nationalite', 'telephone', 'lv1', 'lv2', 'efa', 'efb', 'eps', 'etat_physique', 'type_session', 'etablissement', 'nom_photo', 'login', 'maj');
                echo "<form name='form_statut' method='POST' ><table class='spip liste'>";
                $thead = "<th>N°</th><th>N° table</th><th>Noms</th><th>Sexe</th><th>Etablissement</th><th>Centre</th><th>Série</th><th>Cocher</th>";
                while ($row_aff = mysqli_fetch_array($result_aff)) {
                    foreach ($tab as $var) {
                        $$var = $row_aff[$var];
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

                    $tbody .= "<tr><td>$id_candidat</td><td>$num_table</td><td><a href='./?exec=candidats&etape=maj&id_candidat=$id_candidat&id_inspection=$id_inspection&annee=$annee' title=\"Saisi par $login le $maj\">$nom $prenoms</a></td><td>$sexe</td><td>$etablissementRef</td><td>$centreEts</td><td>$serie</td></tr>";
                }
                echo $thead, $tbody;
                echo "</table>";

                if (mysqli_num_rows($result_aff) > 0) {
                    echo debut_cadre_enfonce('', '', '', 'Selection de critères de Recherche de doublons');
                    echo "<table>";
                    echo "<tr><td>Etablissement </td><td><select name='nouvel_etablissement'>" . optionsEtablissement($lien, "$tri_etablissement", '', '', false) . "</select></td>
						<td><select name='nouvel_serie'>" . optionsReferentiel($lien, 'serie', $_POST['tri_serie'], '') . "</select></td></tr>";
                    echo "<tr><td>Centre </td><td><select name='nouvel_centre'>" . optionsReferentiel($lien, 'etablissement', '', " WHERE si_centre='oui'") . "</select></td>
						<td><select name='nouvel_lv2'>" . optionsReferentiel($lien, 'langue_vivante', '', ' WHERE id!=3') . "</select><td></tr>";

                    echo "<tr><td colspan=3><center><input type='hidden' name='ancien_serie' value='" . $_POST['tri_serie'] . "' />
						<input type='submit' name='appliquer_statut' value='Changer Statut' onClick=\"if(nouvel_serie.value==1 && ancien_serie.value!=1 && nouvel_lv2.value==0) {alert('Bien vouloir vérifier le choix de la langue vivante'); return false;} \" /></center></td></tr>";
                    echo "</table></form>";
                    echo fin_cadre_enfonce();
                }
            }
        }

        //Tri des candidats - Formulaire
        if ((isset($_REQUEST['etape']) && ($_GET['etape'] == 'afficher' || $_GET['etape'] == 'modifier' || $_GET['etape'] == 'supprimer')) || isset($_POST['modifier']) || isset($_POST['tri_numero'])) {
            $tri = debut_cadre_enfonce('', '', '', 'Tri des candidats');

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

            if (isset($_POST['tri_centre']) && $_POST['tri_centre'] > 0) {
                $andWhere .= " AND can.centre=$tri_centre ";
                $andWhereEta .= " AND eta.id_centre=$tri_centre";
            }
            if (isset($_POST['tri_inspection']) && $_POST['tri_inspection'] > 0) {
                $andWhere .= " AND eta.id_inspection=$tri_inspection ";
                if ($isEncadrant) {
                    $andWhereRegion .= " AND id_region=$tri_region";
                } elseif ($isInspection) {
                    $andWhereRegion .= "WHERE id_region=$tri_region";
                    $andWhereEta .= " AND ins.id_region=$tri_region AND ins.id=eta.id_inspection AND eta.id_inspection=$tri_inspection";
                    $andWhereCen .= "AND si_centre='oui' AND ins.id=eta.id_inspection AND ins.id_region=$tri_region AND eta.id_inspection=$tri_inspection";
                } else {
                    $andWhereEta .= " AND ins.id=eta.id_inspection AND eta.id_inspection=$tri_inspection";
                    $andWhereCen .= "AND si_centre='oui' AND ins.id=eta.id_inspection AND ins.id_region=$tri_region AND eta.id_inspection=$tri_inspection";
                }
                $andWhereEta = " AND eta.si_centre='non' AND eta.id_inspection=$tri_inspection ";
                $andWhereCen = " AND eta.si_centre='oui' AND ins.id=eta.id_inspection AND eta.id_inspection=$tri_inspection ";
            }
            if (isset($_POST['tri_region']) && $_POST['tri_region'] > 0) {
                $andWhere .= " AND ins.id_region=$tri_region ";
                if ($isEncadrant) {
                    $andWhereRegion .= " AND id_region=$tri_region";
                } else {
                    $WhereRegion = "";
                    $andWhereRegion .= "WHERE id_region=$tri_region";
                    $andWhereEta .= " AND eta.si_centre='non' AND ins.id_region=$tri_region AND ins.id=eta.id_inspection";
                    $andWhereCen .= " AND si_centre='oui' AND ins.id=eta.id_inspection AND ins.id_region=$tri_region";

                }
            }
            if (isset($_POST['tri_prefecture']) && $_POST['tri_prefecture'] > 0) {
                $andWhere .= " AND vil.id_prefecture=$tri_prefecture ";
                $andWhereEta .= " AND vil.id_prefecture=$tri_prefecture ";
                $andWhereCen .= " AND eta.si_centre='oui' AND vil.id_prefecture=$tri_prefecture ";
            }
            if (isset($_POST['tri_etablissement']) && $_POST['tri_etablissement'] > 0) {
                $andWhere .= " AND can.etablissement=$tri_etablissement ";
                $andWhereEta .= " ";
            }
            if (isset($_POST['tri_numero']) && $_POST['tri_numero'] != '') {
                $andWhere .= " AND (can.id_candidat='$tri_numero' OR can.num_table='$tri_numero') ";
            }

            if (isset($_POST['tri_nom']) && $_POST['tri_nom'] != '') {
                $andWhere .= " AND can.nom LIKE '%$tri_nom%' ";
            }

            if (isset($_POST['tri_ef']) && $_POST['tri_ef'] > 0) {
                $andWhere .= " AND (can.efa=$tri_ef OR can.efb=$tri_ef)";
            }

            if ($isInspection) {
                $andWhereCen = " AND eta.si_centre='oui' AND ins.id_region=$id_region AND eta.id_inspection=$id_inspection ";
                $andWhereEta = " AND eta.si_centre='non' AND ins.id_region=$id_region AND eta.id_inspection=$id_inspection ";
                $andWhereRegion = " WHERE id_region=$id_region AND id=$id_inspection";
                $WhereRegion = "WHERE id=$id_region";
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

            $tri .= "<form name='form_tri' method='POST' ><table>";
            $tri .= "<tr><td><input type='hidden' name='limit' value='$limit' /><input type='hidden' name='annee' value='$annee' /><input type='hidden' name='id_inspection' value='$id_inspection' /></td>
				<td></td></tr>";

            if (!$isChefEtablissement || $isInspection || $isAdmin) {
                $tri .= "<tr><td><center><select style='width:50%' name='tri_annee' onchange='document.forms.form_tri.submit()'>" . optionsAnnee(2020, $tri_annee) . "</select></center></td>
				<td><center><select style='width:50%' name='tri_session' onchange='document.forms.form_tri.submit()'>" . optionsReferentiel($lien, 'type_session', $tri_session) . "</select></center></td></tr>";
                $tri .= "<tr><td><center><select style='width:50%' name='tri_inspection' onchange='document.forms.form_tri.submit()'>" . optionsReferentiel($lien, 'inspection', $tri_inspection, $andWhereRegion) . "</select></center></td>
				<td><center><select style='width:50%' name='tri_centre' onchange='document.forms.form_tri.submit()'>" . optionsEtablissement($lien, $tri_centre, $andWhereCen, true, ',bg_ref_inspection ins ', '', 'Centre') . "</select></center></td></tr>";

                if (!$isChefEtablissement) {
                    $tri .= "<tr><td><center><select style='width:50%' name='tri_prefecture' onchange='document.forms.form_tri.submit()'>" . optionsReferentiel($lien, 'prefecture', $tri_prefecture, $andWhereRegion) . "</select></center></td>
				<td><center><select style='width:50%' name='tri_etablissement' onchange='document.forms.form_tri.submit()'>" . optionsEtablissement($lien, $tri_etablissement, $andWhereEta, true, ',bg_ref_inspection ins ') . "</select></center></td></tr>";
                    $tri .= "<tr><td><center><select  style='width:50%' name='tri_ef' onchange='document.forms.form_tri.submit()'>" . optionsReferentiel($lien, 'epreuve_facultative_a', $tri_ef, '', true, 'Epr. Fac.') . "<option value=8>Couture</option></select></center></td>";
                    if ($isEncadrant || $isAdmin || $isInspection) {
                        $tri .= " <td><center><select style='width:50%' name='tri_region' onchange='document.forms.form_tri.submit()'>" . optionsReferentiel($lien, 'region', $tri_region, $WhereRegion) . "</select></td></tr>";
                    }

                }
            }
            $tri .= "<tr><td><center><select style='width:50%' name='tri_sexe' onchange='document.forms.form_tri.submit()'>" . optionsReferentiel($lien, 'sexe', $tri_sexe) . "</select></center></td>
				<td><center><select style='width:50%' name='tri_serie' onchange='document.forms.form_tri.submit()'>" . optionsReferentiel($lien, 'serie', $tri_serie, 'WHERE id <4') . "</select></center></td></tr>";

            $tri .= "<tr><td style='width:50%'><center>N°: <input type='text' name='tri_numero' style='width:42%' /></center></td>
		<td style='width:50%'><center>Nom: <input type='text' name='tri_nom'  style='width:35%' /><input type='submit' value='OK' /></td></center></tr>";
            $tri .= "</table></form>";
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
            if ($isAdmin) {
                $andStatut = $andStatut2;
            }

            $sql_aff1 = "SELECT can.*  FROM bg_candidats can, bg_ref_etablissement eta, bg_ref_ville vil,bg_ref_inspection ins
		WHERE can.annee='$annee' AND can.etablissement=eta.id AND vil.id=eta.id_ville AND eta.id_inspection=ins.id $andWhere $andStatut ";

            // mysqli_set_charset($lien, "utf8mb4");
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

            echo "<br/><a href='./?exec=candidats&id_inspection=$id_inspection'>Retour au Menu principal</a>";
            echo "</p>";
            if ($isChefEtablissement) {
                $andStatut = " AND can.etablissement='$code_etablissement' ";
            }

            $sql_aff = "SELECT can.* FROM bg_candidats can, bg_ref_etablissement eta, bg_ref_ville vil,bg_ref_inspection ins
		 WHERE can.annee='$annee' AND can.etablissement=eta.id AND vil.id=eta.id_ville  AND eta.id_inspection=ins.id $andWhere $andStatut
		 ORDER BY nom, prenoms LIMIT $limit, " . MAX_LIGNES;
            //mysqli_set_charset($lien, "utf8mb4");
            $result_aff = bg_query($lien, $sql_aff, __FILE__, __LINE__);

            //echo $sql_aff;
            $tab = array('id_candidat', 'num_dossier', 'num_table', 'serie', 'annee_bac1', 'jury_bac1', 'num_table_bac1', 'nom', 'prenoms', 'sexe', 'ddn', 'ldn', 'pdn', 'nationalite', 'telephone', 'lv1', 'lv2', 'efa', 'efb', 'eps', 'etat_physique', 'type_session', 'etablissement', 'nom_photo', 'login', 'maj');
            echo "<table class='spip liste'>";
            $thead = "<th>N°</th><th>N° table</th><th>Noms</th><th>Sexe</th><th>Etablissement</th><th>Centre</th><th>Série</th>";
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

                $tbody .= "<tr><td>$id_candidat</td><td>$num_table</td><td><a href='./?exec=candidats&etape=maj&id_candidat=$id_candidat&id_inspection=$id_inspection&annee=$annee' title=\"Saisi par $login le $maj\">$nom $prenoms</a><td>$sexe</td></td><td>$etablissementRef</td><td>$centreEts</td><td>$serie</td></tr>";

            }
            echo $thead, $tbody;
            echo "</table>";
        }

        if (isset($_REQUEST['etape']) && ($_GET['etape'] == 'inserer' || $_GET['etape'] == 'maj') && (!isset($_POST['modifier'])) && !isset($_POST['tri_numero'])) {

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
            echo "<h4><center><a href='./?exec=candidats&id_inspection=$id_inspection'>Retour au Menu principal</a></center></h4>";

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
					<td class='center'><input class='' type='submit' name='modifier' value='Modifier' $js /></td>
					<td class='center'><input type='button' value='Supprimer' name='supprimer' onClick=\"if(confirm('Souhaitez-vous vraiment supprimer ce candidat?')) window.location='" . generer_url_ecrire('candidats') . "&id_inspection=$id_inspection&etape=supprimer&id_candidat=$id_candidat&annee=$annee';\" /></td>";
                }
            }
            $form .= "<td class='center'><input type='button' name='quitter' value='Quitter' onClick=\"window.location='" . generer_url_ecrire('candidats') . "&id_inspection=$id_inspection&etape=afficher&annee=$annee';\" /></td></tr>";
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

        /* Methode qui permet de gerer le nombre de candidats attendus */

        if (isset($_REQUEST['etape']) && ($_GET['etape'] == 'nombre_candidats_attendus')) {
            if ($isChefEtablissement) {

                $inspection = selectInspection($lien, $id_inspection);
                $etablissementRef = selectReferentiel($lien, 'etablissement', $code_etablissement);

                echo debut_gauche();
                echo debut_boite_info(), "<b>Nombre filles attendus</b><br/>Chaque établissement entre le nombre de candidats de sexe féminin qu'il est sensé enregistrer", fin_boite_info();
                echo debut_boite_info(), "<b>Nombre garçons attendus</b><br/>Chaque établissement entre le nombre de candidats de sexe masculin qu'il est sensé enregistrer", fin_boite_info();

                echo debut_droite();

                echo "<h4><center><a href='./?exec=candidats&id_inspection=$id_inspection'>Retour au Menu principal</a></center></h4>";

                /** Fin Variable aleatoire */

                $form = "<div class='formulaire_spip'><form method='POST' action='' name='form_etablissement' >";
                $form .= "<table>";
                $form .= "<tr><td>Année</td><td colspan=2><select name='annee' disabled >" . optionsAnnee(2022, $annee) . "</td></tr>";
                $form .= "<tr><td>Inspection</td><td colspan=2><input size='60' type='text' name='inspection' value='$inspection' disabled></td></tr>";

                $form .= "<tr><td><label>Nom de l'établissement</label></td><td><input type='text' name='etablissement' id='etablissement' value=\"$etablissementRef\" $majuscule size=60 disabled /></td></tr>";
                $form .= "<tr><td><label>Nombre de filles attendues</label></td><td><input type='number' name='nombre_fille' id='nombre_fille' value='$nombre_fille' required size=30></td></tr>";
                $form .= "<tr><td><label>Nombre de garçons attendus</label></td><td><input type='number' name='nombre_garcon' id='nombre_garcon' value='$nombre_garcon' required size=30></td></tr>";
                $form .= "<tr><td colspan=3></fieldset></td></tr>";
                $form .= "<tr><td colspan=3><input type='hidden' name='id_inspection' value='$id_inspection' /><input type='hidden' name='MAX_FILE_SIZE' value='2000000'></td></tr>";

                $jsNombreAttendu = "onClick=\"if(document.forms['form_etablissement'].nombre_garcon.value==0
                || document.forms['form_etablissement'].nombre_fille.value==0)
                )
                {alert('Remplir les champs obligatoires'); return false; }
                else {alert('Opération effectuée avec succès');return true;}\"; ";

                $form .= "<tr><td class='center'><input class='' type='submit' name='maj_etablissement' value='Modifier' $jsNombreAttendu /></td>";
                $form .= "<td class='center'><input type='button' name='quitter' value='Quitter' onClick=\"window.location='" . generer_url_ecrire('candidats') . "';\" /></td></tr>";
                $form .= "</table></form></div>";

                echo $form;

                echo fin_gauche();
            } else {
                echo " Voir cette rubrique dans le menu etablissement => ";
                echo "<a href='./?exec=candidats&id_inspection=$id_inspection'>Retour au Menu principal</a>";
            }

        }
        if (isset($_REQUEST['maj_etablissement'])) {

            $sql_mod = "UPDATE bg_ref_etablissement
                        SET nombre_fille=$nombre_fille,nombre_garcon=$nombre_garcon
                        WHERE id=$code_etablissement";
            $row_mod = bg_query($lien, $sql_mod, __FILE__, __LINE__);

        }
        /* Fin de methode qui permet de gerer le nombre de candidats attendus */

        if (!$isChefEtablissement) {
            if (isset($_GET['etape']) && $etape == 'scan') {
                if (!isset($_POST['id_candidat']) || isset($_POST['upload'])) {
                    $form = "<FORM name='form_candidat' method='POST' action=''>";
                    $form .= "<TABLE><tr><td><input type='number' id='id_candidat' name='id_candidat' min=1 required size=10 maxlength=15  /></td><td><input type='submit' class='fondo' value='OK' /></td></tr>
						<tr><td colspan=2><center><input type='button' value='Quitter' onClick=\"window.location='" . generer_url_ecrire('candidats') . "&id_inspection=$id_inspection';\"/></center></td></tr></TABLE>";
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
                        if (
                            ($result_rech and mysqli_num_rows($result_rech) > 0) || file_exists($lien_photos . $fichier_ss_extension . '.JPG')
                            || file_exists($lien_photos . $fichier_ss_extension . '.jpg')
                            || file_exists($lien_photos . $fichier_ss_extension . '.JPEG')
                            || file_exists($lien_photos . $fichier_ss_extension . '.jpeg')
                            || file_exists($lien_photos . $fichier_ss_extension . '.PNG')
                            || file_exists($lien_photos . $fichier_ss_extension . '.png')
                            || file_exists($lien_photos . $fichier_ss_extension . '.GIF')
                            || file_exists($lien_photos . $fichier_ss_extension . '.gif')
                        ) {
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
                        $form1 .= "<tr><td>Nom et prénoms</td><td><input size='50' type='text' value=\"$noms\" disabled/></td></tr>";
                        $form1 .= "<tr><td>Etablissement</td><td><input size='50' type='text' value=\"$etablissement\" disabled/></td></tr>";
                        $form1 .= "<tr><td>Série</td><td><input size='50' type='text' value='$serie' disabled/></td><td></td></tr>";
                        $form1 .= "<tr><td>Photo</td><td><input size='50' type='file' value='' name='photo_candidat' class='fondo'/></td></tr>";
                        $form1 .= "<tr><td><input type='hidden' name='MAX_FILE_SIZE' value='2000000'></td><td><input type='hidden' name='id_table' value='$id_table'><input type='hidden' name='id_table_humain' value='$id_table_humain'></td></tr>";
                        $form1 .= "<tr><td><input type='hidden' name='id_candidat' value='$id_candidat'></td><td><input type='hidden' name='type_session' value='$type_session'></td></tr>";
                        $form1 .= "<tr><td><input type='hidden' name='serie' value='$serie'></td><td><input type='hidden' name='etablissement' value='$etablissement'></td></tr>";

                        $form1 .= "<tr><td class='center'><input type='submit' class='fondo' value='Télécharger' name='upload'/></td><td class='center'><input type='button' value='Quitter' onClick=\"window.location='" . generer_url_ecrire('candidats') . "&etape=scan&id_inspection=$id_inspection';\"/></td></tr>";
                        $form1 .= "</TABLE>";
                        $form1 .= "</FORM>";
                        echo $form1;
                    } else {
                        echo "Candidat de numéro <font color='red'>$id_candidat</font> inexistant. <a href='" . generer_url_ecrire('candidats') . "&etape=scan&id_inspection=$id_inspection&annee=$annee' >Cliquer ici pour recommencer</a>";
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
        if (isset($_POST['import_can'])) {
            // var_dump($_FILES['csv_file']);
            $output = '
            <h1> Le nombre de candidats non enregistré </h1>
            <table class="spip liste"><thead>
           <th> Code Etabliss.</th><th>Etablissement</th><th>Niveau</th><th>Serie</th>
           <th>Classe</th><th>Statu </th><th>Nom</th><th>Prénom</th>
           <th>Sexe</th><th>Date Naissance</th><th>Lieu Naissance</th><th>Situation Famille</th>
           <th>Adresse</th><th>Mail</th><th>Préfecture </th><th>Pays </th>
           <th>Nationalité</th><th>Nom Tuteur</th><th>Prénom Tuteur</th><th>Telephone Tuteur</th>

            </thead><tbody> ';
            // Vérifiez si un fichier a été téléchargé
            if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0) {
                // var_dump("ok");
                // Récupérer le chemin temporaire du fichier téléchargé
                $chemin_fichier = $_FILES['csv_file']['tmp_name'];

                // Ouvrir le fichier en lecture
                if (($handle = fopen($chemin_fichier, 'r')) !== false) {
                    // var_dump("ok 2");
                    // Lire la première ligne pour ignorer l'en-tête
                    fgetcsv($handle);
                    $i = 0;
                    $j = 0;

                    // Boucle à travers chaque ligne du fichier CSV
                    while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                        // Assigner les valeurs des colonnes à des variables
                        // $id = $data[0];
                        // var_dump("ok    3");

                        $etablissement = selectEtablissementByCode($lien, $data[0]);
                        if ($etablissement != null) {
                            $serie = selectSerie($lien, $data[3]);
                            $num_dossier = $i;
                            $annee_bac1 = $jury_bac1 = $num_table_bac1 = $nom_photo = '';
                            // $nom = addslashes($data[6]);
                            $nom = addslashes($data[6]);

                            $prenoms = addslashes($data[7]);

                            // $ddn = date('Y-m-d', $data[9]);
                            // $ddn = date('Y-m-d', strtotime($data[9]));
                            $ddn = $data[9];
                            $ldn = addslashes($data[10]);

                            $pdn = 1;
                            $nationalite = 1;
                            $telephone = $data[19];
                            $lv1 = 3;
                            $lv2 = 0;
                            $efa = 0;
                            $efb = 0;
                            $eps = 1;
                            $etat_physique = 1;
                            $atelier1 = 0;
                            $atelier2 = 0;
                            $atelier3 = 7;

                            $centre = centreEtablissement($lien, $etablissement);
                            $type_session = 1;

                            $login = 'Importation';
                            $statu = 1;
                            $motif = 0;
                            $additive = 0;
                            $sexe = selectSexe($data[8]);
                            $sql_ins = "INSERT INTO bg_candidats (`annee`, `serie`, `num_dossier`, `annee_bac1`, `jury_bac1`, `num_table_bac1`, `nom`, `prenoms`, `sexe`, `ddn`, `ldn`, `pdn`, `nationalite`, `telephone`, `lv1`, `lv2`, `efa`, `efb`, `eps`, `etat_physique`, `atelier1`, `atelier2`, `atelier3`, `centre`, `type_session`, `etablissement`, `nom_photo`, `login`, `statu`, `motif`, `additive`)
                        VALUES ('$annee','$serie','$num_dossier','$annee_bac1','$jury_bac1','$num_table_bac1','$nom','$prenoms','$sexe','$ddn','$ldn','$pdn','$nationalite','$telephone','1','$lv2','$efa','$efb','$eps','$etat_physique','$atelier1','$atelier2','$atelier3','$centre','$type_session','$etablissement','$nom_photo','$login','$statu', '$motif', '$additive') ";

                            try {
                                bg_query($lien, $sql_ins, __FILE__, __LINE__);

                            } catch (Exception $e) {
                                // En cas d'erreur, ajouter un message au tableau des erreurs
                                $erreurs[] = "Erreur lors de l'importation du candidat : $nom $prenoms. Détails : " . $e->getMessage();
                            }
                            $i++;

                        } else {
                            $j++;

                            $output .= '

                           <tr>
                            <td>' . $data[0] . '</td>   <td>' . $data[1] . '</td>   <td>' . $data[2] . '</td>   <td>' . $data[3] . '</td>   <td>' . $data[4] . '</td>
                            <td>' . $data[5] . '</td>   <td>' . $data[6] . '</td>   <td>' . $data[7] . '</td>   <td>' . $data[8] . '</td>   <td>' . $data[9] . '</td>
                            <td>' . $data[10] . '</td>   <td>' . $data[11] . '</td>   <td>' . $data[12] . '</td>   <td>' . $data[13] . '</td>   <td>' . $data[14] . '</td>
                            <td>' . $data[15] . '</td>   <td>' . $data[16] . '</td>   <td>' . $data[17] . '</td> <td>' . $data[18] . '</td>   <td>' . $data[19] . '</td>

                        </tr>';

                        }

                    }

                    // Fermer le fichier après lecture
                    fclose($handle);
                    // Message de succès
                    // echo "Importation réussie!";
                    $output .= '</tbody><tfoot><tr></tr></tfoot></table>';
                    $filename = "iesg_tsevie.xls";
                    header('Content-Type: application/xls');
                    header('Content-Disposition: attachment; filename=' . $filename);
                    echo $output;
                } else {
                    echo "Erreur : impossible d'ouvrir le fichier.";
                }
            } else {
                echo "Erreur : aucun fichier n'a été téléchargé.";
            }

        }
        if (isset($_GET['etape']) && $etape == 'import') {
            echo "<h4><center><a href='./?exec=candidats&id_inspection=$id_inspection'>Retour au Menu principal</a></center></h4>";

           
            $form = debut_cadre_enfonce('', '', '', "Importation de Candidats");
            $form .= "<form action='./?exec=candidats&etape=import' method='POST' name='form_import' enctype='multipart/form-data'>";
            $form .= " <table>";
            $form .= "<tr>
                <td><input type='file' name='csv_file' id='csv_file' accept='.csv' required></td>

                <td> <button type='submit'  name='import_can'>Importer</button></td>
                </tr>";

            $form .= "</table> ";

            $form .= "</form> ";

            // $form .= "</form>";
            $form .= fin_cadre_enfonce();

            echo $form;
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

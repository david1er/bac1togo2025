<?php
error_reporting(0);
function lien()
{
    $tBddConf = getBddConf($conf = '');
    $link = mysqli_connect($tBddConf['host'], $tBddConf['user'], $tBddConf['pass'], $tBddConf['bdd']);
    mysqli_set_charset($link, "utf8mb4");
    /* check connection */
    if (mysqli_connect_errno()) {
        printf("Connect failed: %s\n", mysqli_connect_error());
        exit();
    }
    return $link;
}

//Affichage de la note apte selon la série
function getApteBySerie($lien, $id_serie)
{
    $sql = "SELECT moyenne_apte FROM bg_ref_serie WHERE id=$id_serie ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $row = mysqli_fetch_array($result);
    return $row['moyenne_apte'];
}
//Affichage de la note inapte selon la série
function getInapteBySerie($lien, $id_serie)
{
    $sql = "SELECT moyenne_inapte FROM bg_ref_serie WHERE id=$id_serie ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $row = mysqli_fetch_array($result);
    return $row['moyenne_inapte'];
}
function recherche_region($lien, $id_inspection)
{
    $sql = "SELECT id_region FROM bg_ref_inspection WHERE id=$id_inspection ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $row = mysqli_fetch_array($result);
    return $row['id_region'];
}
function get_matiere_by_id($lien, $id_matiere)
{
    $sql = "SELECT matiere FROM bg_ref_matiere WHERE id=$id_matiere ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $row = mysqli_fetch_array($result);
    return $row['matiere'];
}
/** Recupere les informations de connection bdd du fichier de conf spip
 *
 * @param string $conf : (facultatif) : element de la configuration a recuperer : host, user, pass, bdd
 * @return array : host, user, pass, bdd (ou element si specifie)
 */
function getBddConf($conf = '')
{
    //echo realpath('.');
    //include_once('../../../ecrire/inc/utils.php');
    //$fichier=find_in_path ('config/connect.php');
    $chemin = '';
    for ($cpt = 0; $cpt < 5; $cpt++) {
        if (file_exists($chemin . 'config/connect.php')) {
            $fichier = $chemin . 'config/connect.php';
        }

        $chemin .= '../';
    }

    $connect = file_get_contents($fichier);
    $connect = substr($connect, strpos($connect, "spip_connect_db"));
    $tab = explode("'", $connect);
    $host = $tab[1];
    $user = $tab[5];
    $pass = $tab[7];
    $bdd = $tab[9];
    if ($conf != '') {
        return $$conf;
    }

    foreach (array('host', 'user', 'pass', 'bdd') as $col) {
        $tBddConf[$col] = $$col;
    }

    return $tBddConf;
}

//Affiche les options d'un referentiel
//$referentiel: Le referentiel en question
//$selected: Le referentiel selectionne par defaut (id)
//$where: Pour restreindre les options du referentiel
function optionsReferentiel($lien, $referentiel, $selected = '', $where = '', $titre = true, $entete = '')
{
    $referentiel = strtolower($referentiel);
    $sql = "SELECT id, $referentiel FROM bg_ref_" . $referentiel . " $where ORDER BY $referentiel";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    if ($titre == true && $entete == '') {
        $options .= "<option value=0>-=[" . ucfirst(str_replace('_', ' ', $referentiel)) . "]=-</option>";
    } elseif ($titre == true && $entete != '') {
        $options .= "<option value=0>-=[$entete]=-</option>";
    } else {
        $options .= '';
    }

    while ($row = mysqli_fetch_array($result)) {
        $affi = ucfirst($row[1]);
        if (strlen($affi) > 20) {
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
        if ($selected == $row[0]) {
            $options .= "<option value=$row[0] selected>" . $affi . "</option>";
        } else {
            $options .= "<option value=$row[0]>" . $affi . "</option>";
        }

    }
    return $options;
}

function optionsCommune($lien, $referentiel, $selected = '', $where = '', $titre = true, $entete = '')
{
    $referentiel = strtolower($referentiel);
    //  $sql = "SELECT id, $referentiel FROM bg_ref_" . $referentiel . " $where ORDER BY $referentiel";
    $sql = "SELECT com.* FROM bg_ref_commune com,bg_ref_prefecture pre,bg_ref_region reg
    WHERE  reg.id=pre.id_region AND pre.id=com.id_prefecture  $where  ORDER BY com.commune";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    if ($titre == true && $entete == '') {
        $options .= "<option value=0>-=[" . ucfirst(str_replace('_', ' ', $referentiel)) . "]=-</option>";
    } elseif ($titre == true && $entete != '') {
        $options .= "<option value=0>-=[$entete]=-</option>";
    } else {
        $options .= '';
    }

    while ($row = mysqli_fetch_array($result)) {
        $affi = ucfirst($row[1]);
        if (strlen($affi) > 20) {
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
        if ($selected == $row[0]) {
            $options .= "<option value=$row[0] selected>" . $affi . "</option>";
        } else {
            $options .= "<option value=$row[0]>" . $affi . "</option>";
        }

    }
    return $options;
}
function optionsReferentielTxt($lien, $referentiel, $selected = '', $where = '', $titre = true, $entete = '')
{
    $referentiel = strtolower($referentiel);
    $sql = "SELECT id, $referentiel FROM bg_ref_" . $referentiel . " $where ORDER BY $referentiel";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    if ($titre == true && $entete == '') {
        $options .= "<option value=0>-=[" . ucfirst(str_replace('_', ' ', $referentiel)) . "]=-</option>";
    } elseif ($titre == true && $entete != '') {
        $options .= "<option value=0>-=[$entete]=-</option>";
    } else {
        $options .= '';
    }

    while ($row = mysqli_fetch_array($result)) {
        $affi = ucfirst($row[1]);
        if (strlen($affi) > 200) {
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
        if ($selected == $row[0]) {
            $options .= "<option value=$row[0] selected>" . $affi . "</option>";
        } else {
            $options .= "<option value=$row[0]>" . $affi . "</option>";
        }

    }
    return $options;
}

//Affiche les checkbox d'un referentiel
//$referentiel: Le referentiel en question
//$selected: Le referentiel selectionne par defaut (id)
//$where: Pour restreindre les options du referentiel
function checkboxReferentiel($referentiel, $selected = '', $where = '')
{
    $referentiel = strtolower($referentiel);
    $sql = "SELECT id, $referentiel FROM bg_ref_" . $referentiel . " $where ORDER BY $referentiel";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_array($result)) {
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
        if ($selected == $row[0]) {
            $checkbox .= "<input type='checkbox' name='$referentiel' value=$row[0] selected />" . $affi . "<br/>";
        } else {
            $checkbox .= "<input type='checkbox' name='$referentiel' value=$row[0] />" . $affi . "<br/>";
        }

    }
    return $checkbox;
}

//Affichage de la région division a partir de la région
function selectRegionDivisionByRegion($lien, $id_region)
{
    $sql = "SELECT id FROM bg_ref_region_division WHERE id_region=$id_region ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $row = mysqli_fetch_array($result);
    return $row['id'];
}
//Affichage de la région division a partir de la région
function selectRegionByRegionDivision($lien, $id_region_division)
{
    $sql = "SELECT id_region FROM bg_ref_region_division WHERE id=$id_region_division ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $row = mysqli_fetch_array($result);
    return $row['id_region'];
}
//Générer les chaines de caractère de façon aléatoire
function genererChaineAleatoire($longueur)
{
    $listeCar = 'abcdefghijklmnopqrstuvwxyz';
    $chaine = '';
    $max = mb_strlen($listeCar, '8bit') - 1;
    for ($i = 0; $i < $longueur; ++$i) {
        $chaine .= $listeCar[random_int(0, $max)];
    }
    return $chaine;
}
//Générer les chaines de consonnes de façon aléatoire
function gC($longueur)
{
    $listeCar = 'bcdfghjklmnpqrstvwxz';
    $chaine = '';
    $max = mb_strlen($listeCar, '8bit') - 1;
    for ($i = 0; $i < $longueur; ++$i) {
        $chaine .= $listeCar[random_int(0, $max)];
    }
    return $chaine;
}
//Générer les chaines de voyelle de façon aléatoire
function gV($longueur)
{
    $listeCar = 'aeiouy';
    $chaine = '';
    $max = mb_strlen($listeCar, '8bit') - 1;
    for ($i = 0; $i < $longueur; ++$i) {
        $chaine .= $listeCar[random_int(0, $max)];
    }
    return $chaine;
}

//Utilisation de la fonction
// echo genererChaineAleatoire();
// echo genererChaineAleatoire(20, 'abcdefghijklmnopqrstuvwxyz');

//Tableau des capacites des centres de composition
function capacites($lien, $annee)
{
    $sql = "SELECT id_centre, capacite FROM bg_capacite WHERE annee=$annee";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    while ($row = mysqli_fetch_array($result)) {
        $capacite = $row['capacite'];
        $id_centre = $row['id_centre'];
        $tabCapacite[$id_centre] = $capacite;
    }
    return $tabCapacite;
}

function centreEts($lien, $id_etablissement)
{
    $sql10 = "SELECT etablissement, si_centre, id_centre FROM bg_ref_etablissement WHERE id=$id_etablissement";
    $result10 = bg_query($lien, $sql10, __FILE__, __FILE__);
    $sol = mysqli_fetch_array($result10);
    $id_centre = $sol['id_centre'];
    $etab = $sol['etablissement'];
    // var_dump($sol['si_centre']);

    if ($sol['si_centre'] == 'non') {
        $sql11 = "SELECT etablissement FROM bg_ref_etablissement WHERE id=$id_centre";
        $result11 = bg_query($lien, $sql11, __FILE__, __FILE__);
        $sol11 = mysqli_fetch_array($result11);
        $centre = ($sol11['etablissement']);
        //  var_dump($centre);
    } else if ($sol['si_centre'] == 'oui') {
        $centre = ($sol['etablissement']);
    }
    return $centre;
}
function selectEtablissementById($lien, $id_etablissement)
{
    $sql = "SELECT eta.etablissement,eta.id
		  FROM bg_ref_etablissement eta
		  WHERE eta.id=$id_etablissement";

    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $tab = array('etablissement', 'id');
    if (mysqli_num_rows($result) > 0) {

        while ($row = mysqli_fetch_array($result)) {

            foreach ($tab as $val) {
                $$val = addslashes($row[$val]);
            }

            return $etablissement . "<strong> ( " . centreEts($lien, $id) . " )</strong>";

        }

    }

}
function listeChoix($lien, $WhereRegion, $plugin)
{
    $liste = "<form action='" . generer_url_ecrire($plugin) . "' method='POST' class='forml spip_xx-small' name='form_" . $plugin . "'>";

    $liste .= "<center><select name='id_inspection' onchange='document.forms.form_" . $plugin . ".submit()'>";

    $liste .= optionsReferentiel($lien, 'inspection', '', " $WhereRegion ");

    $liste .= "</select></center>";
    $liste .= "</form>";
    echo $liste;
}
function recupAnnee($lien)
{
    $sql = "SELECT annee FROM bg_configuration";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    while ($row = mysqli_fetch_array($result)) {
        $annee = $row['annee'];

    }
    return $annee;
}
function recupEtablissement($lien)
{
    $sql = "SELECT etablissement FROM bg_configuration";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    while ($row = mysqli_fetch_array($result)) {
        $etablissement = $row['etablissement'];

    }
    return $etablissement;
}
function recupInspection($lien)
{
    $sql = "SELECT inspection FROM bg_configuration";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    while ($row = mysqli_fetch_array($result)) {
        $inspection = $row['inspection'];

    }
    return $inspection;
}
function recupEncadrant($lien)
{
    $sql = "SELECT encadrant FROM bg_configuration";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    while ($row = mysqli_fetch_array($result)) {
        $encadrant = $row['encadrant'];

    }
    return $encadrant;
}
function recupLastInsertId($lien, $table)
{
    $sql = "SELECT id FROM $table ORDER BY id DESC limit 0,1";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    while ($row = mysqli_fetch_array($result)) {
        $id = $row['id'];

    }
    return $id;
}

function recupSession($lien)
{
    $sql = "SELECT id_type_session FROM bg_configuration";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    while ($row = mysqli_fetch_array($result)) {
        $id_type_session = $row['id_type_session'];

    }
    return $id_type_session;
}
function recupUtilitaire($lien)
{
    $sql = "SELECT utilitaire FROM bg_configuration";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    while ($row = mysqli_fetch_array($result)) {
        $utilitaire = $row['utilitaire'];

    }
    return $utilitaire;
}

function recupRepechage($lien)
{
    $sql = "SELECT repechage FROM bg_configuration";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    while ($row = mysqli_fetch_array($result)) {
        $repechage = $row['repechage'];

    }
    return $repechage;
}
function recupNbRepechage($lien)
{
    $sql = "SELECT COUNT(*) as nb_rep FROM bg_configuration con,bg_resultats res WHERE res.moyenne>=con.repechage AND res.moyenne<9";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    while ($row = mysqli_fetch_array($result)) {
        $nb_rep = $row['nb_rep'];

    }
    return $nb_rep;
}

function afficherUtilitaire($lien)
{
    $sql = "SELECT utilitaire FROM bg_configuration";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    while ($row = mysqli_fetch_array($result)) {
        $utilitaire = $row['utilitaire'];

    }
    if ($utilitaire == 0) {
        return "<b style='color:green';>( Activé )</b>";
    } else {
        return "<b style='color:red';>( Désactivé )</b>";
    }
}
function afficherEtablissement($lien)
{
    $sql = "SELECT etablissement FROM bg_configuration";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    while ($row = mysqli_fetch_array($result)) {
        $etablissement = $row['etablissement'];

    }
    if ($etablissement == 1) {
        return "<b style='color:green';>( Activé )</b>";
    } else {
        return "<b style='color:red';>( Désactivé )</b>";
    }
}
function afficherInspection($lien)
{
    $sql = "SELECT inspection FROM bg_configuration";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    while ($row = mysqli_fetch_array($result)) {
        $inspection = $row['inspection'];

    }
    if ($inspection == 1) {
        return "<b style='color:green';>( Activé )</b>";
    } else {
        return "<b style='color:red';>( Désactivé )</b>";
    }
}function afficherEncadrant($lien)
{
    $sql = "SELECT encadrant FROM bg_configuration";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    while ($row = mysqli_fetch_array($result)) {
        $encadrant = $row['encadrant'];

    }
    if ($encadrant == 1) {
        return "<b style='color:green';>( Activé )</b>";
    } else {
        return "<b style='color:red';>( Désactivé )</b>";
    }
}
function statusUtilitaire($lien)
{
    $sql = "SELECT utilitaire FROM bg_configuration";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    while ($row = mysqli_fetch_array($result)) {
        $utilitaire = $row['utilitaire'];

    }
    return $utilitaire;
}
function afficherModifNote($lien)
{
    $sql = "SELECT statu_modif_note FROM bg_configuration";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    while ($row = mysqli_fetch_array($result)) {
        $statu_modif_note = $row['statu_modif_note'];
    }
    if ($statu_modif_note == 1) {
        return "<b style='color:green';>( Activé )</b>";
    } else {
        return "<b style='color:red';>( Désactivé )</b>";
    }
}

function afficherAdditiveDate($lien)
{
    $sql = "SELECT date_liste_additive FROM bg_configuration";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    while ($row = mysqli_fetch_array($result)) {
        $date_liste_additive = $row['date_liste_additive'];
    }
    return "<b style='color:green';>" . afficher_date($date_liste_additive) . "</b>";

}
function afficherAgeLimiteDate($lien)
{
    $sql = "SELECT date_age_limite FROM bg_configuration";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    while ($row = mysqli_fetch_array($result)) {
        $date_age_limite = $row['date_age_limite'];
    }
    return "<b style='color:blue';>" . afficher_date($date_age_limite) . "</b>";

}
function recupAdditiveDate($lien)
{
    $sql = "SELECT date_liste_additive FROM bg_configuration";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    while ($row = mysqli_fetch_array($result)) {
        $date_liste_additive = $row['date_liste_additive'];
    }
    return $date_liste_additive;

}
function recupAgeLimiteDate($lien)
{
    $sql = "SELECT date_age_limite FROM bg_configuration";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    while ($row = mysqli_fetch_array($result)) {
        $date_age_limite = $row['date_age_limite'];
    }
    return $date_age_limite;

}
function dSC($str)
{

    // remplacer tous les caractères spéciaux par une chaîne vide
    $res = str_replace(array("\'"), "'", $str);

    return $res;
}
function recupDateDeliberation($lien, $annee, $id_type_session)
{
    $sql = "SELECT dates FROM bg_ref_dates where annee=$annee AND id_type_session=$id_type_session";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    while ($row = mysqli_fetch_array($result)) {
        $dates = $row['dates'];
    }
    return $dates;

}
function recupModifnote($lien)
{
    $sql = "SELECT statu_modif_note FROM bg_configuration";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    while ($row = mysqli_fetch_array($result)) {
        $statu_modif_note = $row['statu_modif_note'];

    }
    return $statu_modif_note;
}
function recupCentreById($lien, $id_centre)
{
    $sql = "SELECT etablissement FROM bg_ref_etablissement Where id=$id_centre";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    // while ($row = mysqli_fetch_array($result)) {
    $row = mysqli_fetch_array($result);
    $centre = $row['etablissement'];

    //  }
    return $centre;
}
function optionsEtablissement($lien, $selected = '', $andWhere = '', $titre = true, $autreTable = '', $condJointure = '', $entete = '')
{

    $sql = "SELECT eta.id, eta.etablissement, reg.region
				FROM bg_ref_etablissement eta, bg_ref_ville vil, bg_ref_prefecture pref, bg_ref_region reg $autreTable
				WHERE eta.id_ville=vil.id AND vil.id_prefecture=pref.id AND pref.id_region=reg.id $andWhere $condJointure
				GROUP BY eta.id
				ORDER BY reg.region,eta.etablissement";
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
function optionsVille($lien, $referentiel, $selected = '', $where = '', $titre = true, $entete = '')
{

    $sql = "SELECT vil.id, ville,pre.id_region FROM bg_ref_ville vil,bg_ref_prefecture pre WHERE pre.id=vil.id_prefecture $where  ORDER BY vil.ville";

    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    if ($titre == true && $entete == '') {
        $options .= "<option value=0>-=[" . ucfirst(str_replace('_', ' ', $referentiel)) . "]=-</option>";
    } elseif ($titre == true && $entete != '') {
        $options .= "<option value=0>-=[$entete]=-</option>";
    } else {
        $options .= '';
    }

    while ($row = mysqli_fetch_array($result)) {
        $affi = ucfirst($row[1]);
        if (strlen($affi) > 20) {
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
        if ($selected == $row[0]) {
            $options .= "<option value=$row[0] selected>" . $affi . "</option>";
        } else {
            $options .= "<option value=$row[0]>" . $affi . "</option>";
        }

    }
    return $options;
}
function optionsJury($lien, $selected = '', $andWhere = '', $titre = true, $autreTable = '', $condJointure = '', $entete = '')
{

    $sql = "SELECT DISTINCT jury FROM bg_repartition";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    if ($entete == '') {
        $entete = 'Jury';
    }

    if ($titre == true) {
        $options .= "<option value=0>-=[$entete]=-</option>";
    } else {
        $options .= '';
    }

    $i = 1;
    while ($row = mysqli_fetch_array($result)) {
        $region = $row['region'];
        $affi = ucfirst($row[0]);
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

function optionsLogique($selected = '')
{
//    if($selected=='')    $options.="<option value='' selected>-=[Inconnu]=-</option>";
    //    else $options.="<option value=''>-=['']=-</option>";
    if ($selected == 'oui') {
        $options .= "<option value='oui' selected>Oui</option>";
    } else {
        $options .= "<option value='oui'>Oui</option>";
    }

    if ($selected == 'non') {
        $options .= "<option value='non' selected>Non</option>";
    } else {
        $options .= "<option value='non'>Non</option>";
    }

    return $options;
}
function optionsLogiques($selected = '', $choix1, $choix2)
{
//    if($selected=='')    $options.="<option value='' selected>-=[Inconnu]=-</option>";
    //    else $options.="<option value=''>-=['']=-</option>";
    if ($selected == $choix1) {
        $options .= "<option value=$choix1 selected>$choix1</option>";
    } else {
        $options .= "<option value=$choix1>$choix1</option>";
    }

    if ($selected == $choix2) {
        $options .= "<option value=$choix2 selected>$choix2</option>";
    } else {
        $options .= "<option value=$choix2>$choix2</option>";
    }

    return $options;
}

function formater_date($date)
{
    $tDate = explode('/', $date);
    $date = $tDate[2] . "-" . $tDate[1] . "-" . $tDate[0];
    return $date;
}

function checkThePast($time)
{
    $convertToUNIXtime = strtotime($time);
    return $convertToUNIXtime < time();
}

// if (checkThePast('2021-12-13 22:00:00')) {
//     echo "The date is in the past";
// } else {
//     echo "No, it's not in the past";
// }

function afficher_date($date)
{
    if ($date == '') {
        return $date;
    } else {
        $tDate = explode('-', $date);
        $date = $tDate[2] . "/" . $tDate[1] . "/" . $tDate[0];
        return $date;
    }
}

/**
 * rend une chaine compatible url-rewriting
 *
 * @see http://www.php.net/manual/en/function.strtr.php#51862
 * @param string $sString : chaine a traiter
 * @return string
 */
function getRewriteString($sString)
{
    $string = htmlentities(strtolower($sString));
    $string = preg_replace("/&(.)(acute|cedil|circ|ring|tilde|uml);/", "$1", $string);
    $string = preg_replace("/([^a-z0-9]+)/", "-", html_entity_decode($string));
    $string = trim($string, "-");
    //echo "$sString&rarr;$string<br>";
    return $string;
}

/** Determine le type d'utilisateur
 *
 * @return string : 'Operateur', 'Encadrant', 'Admin'
 */
function getStatutUtilisateur()
{
    if ($GLOBALS["auteur_session"]['statut'] == '0minirezo') {
        return 'Admin';
    } else {
        list($email, $serveur) = explode('@', $GLOBALS["auteur_session"]['email']);
        switch (strtolower($email)) {
            case 'encadrant':
                return 'Encadrant';
                break;
            case 'dre':
                return 'Dre';
                break;
            case 'inspection':
                return 'Inspection';
                break;
            case 'operateur':
                return 'Operateur';
                break;
            case 'notes':
                return 'Notes';
                break;
            case 'eps':
                return 'Eps';
                break;
            case 'etablissement':
                return 'Etablissement';
                break;
            case 'presidents':
                return 'Presidents';
                break;
            case 'anonymat':
                return 'Anonymat';
                break;
            case 'informaticien':
                return 'Informaticien';
                break;
            case 'dexcc':
                return 'DExCC';
                break;
            case 'etude':
                return 'Etude';
                break;
            default:
                return 'Inconnu';
        }
    }
}

/**  Interdit l'acces a un utilisateur qui n'est pas dans le tableau $tOK
 *
 * @param array $tOK : tableau des types d'utilisateur autorises
 * @return boolean : true si autorise (sinon, die)
 */
function isAutorise($tOK)
{
    if (!in_array(getStatutUtilisateur(), $tOK)) {
        die(KO . "<font color='red'> - Vous n'êtes pas autorisé(e) à accéder à ce module</font>");
    }

    return true;
}

function siAdmin()
{
    $tab_auteur = $GLOBALS["auteur_session"];
    if ($tab_auteur['statut'] != '0minirezo') {
        die("<h3><font color='red'>Vous n'êtes pas autorisé à accéder à cette page !!!</font></h3>");
    }

}

function selectReferentiel($lien, $referentiel, $id)
{
    if ($id != 0) {
        $ref = strtolower($referentiel);
        $sql = "SELECT $ref FROM bg_ref_$ref WHERE id=$id";
        $result = bg_query($lien, $sql, __FILE__, __LINE__);
        $row = mysqli_fetch_array($result);
        $ref = $row[$ref];
        return $ref;
    }
}

/* affiche les options sur l'annee
 * Prends en parametre l'annee et affiche annee-5 a annee+5
 * */
function optionsAnnee($annee_debut, $selected = 0)
{
    $options = '';
    $annee = date("Y") + 1;
    $options .= "<option value=''>-=[Année]=-</option>";
    for ($i = $annee_debut; $i <= $annee; $i++) {
        if ($i == $selected) {
            $options .= "<option value=$i selected>$i</option>";
        } else {
            $options .= "<option value=$i>$i</option>";
        }

    }
    return $options;
}
/* affiche les options sur la modification des notes
 * Prends en parametre l'annee et affiche annee-5 a annee+5
 * */
function optionsNotes($annee_debut, $selected = 0)
{
    $options = '';

    $annee = date("Y") + 1;
    $options .= "<option value=''>-=[Notes]=-</option>";
    for ($i = $annee_debut; $i <= $annee; $i++) {
        if ($i == $selected) {
            $options .= "<option value=$i selected>$i</option>";
        } else {
            $options .= "<option value=$i>$i</option>";
        }

    }
    return $options;
}

//Retourne un tableau de jurys dont on a acces.
//Reste à faire pour chaque opérateur
function getJurys($lien, $annee, $id_type_session = 0)
{
    if ($id_type_session > 0) {
        $andWhere1 = " AND id_type_session=$id_type_session ";
        $andWhere2 = " AND rep.id_type_session=$id_type_session AND codes.id_type_session=$id_type_session";
    } else {
        $andWhere1 = $andWhere2 = '';
    }
//    $sql="SELECT jury FROM bg_repartition WHERE annee=$annee $andWhere1 GROUP BY jury ORDER BY jury";
    $sql = "SELECT rep.jury, code
			FROM bg_repartition rep
			LEFT JOIN bg_codes codes ON (codes.jury=rep.jury AND codes.annee=$annee)
			WHERE rep.annee=$annee
			$andWhere2
			GROUP BY rep.jury,code
			ORDER BY rep.jury,code ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    while ($row = mysqli_fetch_array($result)) {
        $tJurys[] = $row['jury'];
    }
/*
$sql="SELECT min(jury) as mini, max(jury) as maxi FROM bg_repartition WHERE annee=$annee";
$result=mysql_query($sql);
$row=mysql_fetch_array($result);
$tJurys=range((int)$row['mini'],(int)$row['maxi']);
 */
    return $tJurys;
}

//Retourne un tableau de series par jury
function getSeries($lien, $annee)
{
    $sql = "SELECT DISTINCT jury, ser.id id_serie, ser.serie\n FROM bg_ref_serie ser, bg_repartition rep, bg_candidats can\n" .
        " WHERE can.num_table=rep.num_table AND ser.id=can.serie and rep.annee=$annee and can.annee=$annee\n ORDER BY rep.jury, ser.serie";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    while ($row = mysqli_fetch_array($result)) {
        $jury = $row['jury'];
        $id_serie = $row['id_serie'];
        $tSeries[$jury][$id_serie] = $row['serie'];
    }
    return $tSeries;
}

function detectDeliberation($lien, $annee, $jury)
{
    $sql = "SELECT jury FROM bg_deliberation WHERE annee=$annee AND jury=$jury";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    if (mysqli_num_rows($result) == 0) {
        $deliberation = 0;
    } elseif (mysqli_num_rows($result) == 1) {
        $sql2 = "SELECT * FROM bg_notes WHERE annee=$annee AND jury=$jury AND id_type_note=3 AND note IS NOT NULL";
        $result2 = bg_query($lien, $sql2, __FILE__, __LINE__);
        if (mysqli_num_rows($result2) > 0) {
            $deliberation = 2;
        } else {
            $deliberation = 1;
        }

    }
    return $deliberation;
}
function detectSiSupprimable($lien, $id_candidat)
{
    $sql = "SELECT num_table FROM bg_repartition WHERE id_candidat=$id_candidat";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    if (mysqli_num_rows($result) == 0) {
        $repartition = 0;
    } elseif (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_array($result);
        $num_table = $row['num_table'];
        $sql2 = "SELECT * FROM bg_notes_eps WHERE num_table=$num_table";
        $result2 = bg_query($lien, $sql2, __FILE__, __LINE__);
        if (mysqli_num_rows($result2) > 0) {
            $repartition = 2;
        } else {
            $repartition = 1;
        }

    }
    return $repartition;
}

/** Execute une requete sql
 *
 * @param string $sql : code SQL a executer
 * @param string $fichier : nom du fichier (par exemple, passer __FILE__)
 * @param int $ligne : ligne (passer __LINE__)
 * @param string $obsc : texte a dissimuler (mot de passe, par exemple)
 * @return resource : resultset correspondant
 */
function bg_query($lien, $sql, $fichier, $ligne, $obsc = '****')
{
    $cherche = '/plugins/';
    if (substr_count($sql, 'DECODE(') > 0 && $obsc == '****') {
        $tmp = stristr($sql, 'decode(');
        $tmp = substr($tmp, 0, strpos($tmp, ')'));
        list($rien, $obsc) = explode(',', $tmp);
        $obsc = trim(str_replace(array('\'', '"'), '', $obsc));
    }
    $fichier = substr($fichier, strpos($fichier, $cherche));
    $result = mysqli_query($lien, $sql) or die("<div style='margin:5px;border:1px outset red;background-color:#ddf;'>"
        . "<div style='border:1px none red;background-color:#bbf;'>" . KO . " - Erreur dans la requete</div><pre>"
        . wordwrap(str_replace($obsc, '****', $sql), 65)
        . "</pre><small>$fichier<b>[$ligne]</b></small><br/><div style='border:1px none red;background-color:#bbf;'>"
        . htmlentities(str_replace($obsc, '****', mysqli_error($lien))) . "</div></div>");
    //echo "<br/>$sql (<b>$fichier</b>:$ligne)";
    return $result;
}

// ---------------------------------------------------------------------
//    Générer un mot de passe aléatoire
// ---------------------------------------------------------------------
function genererMDP($longueur = 8)
{
    // initialiser la variable $mdp
    $mdp = "";

    // Définir tout les caractères possibles dans le mot de passe,
    // Il est possible de rajouter des voyelles ou bien des caractères spéciaux
    $possible = "ABCDEFGHJKLMNPQRTVWXYZ";

    // obtenir le nombre de caractères dans la chaîne précédente
    // cette valeur sera utilisé plus tard
    $longueurMax = strlen($possible);

    if ($longueur > $longueurMax) {
        $longueur = $longueurMax;
    }

    // initialiser le compteur
    $i = 0;

    // ajouter un caractère aléatoire à $mdp jusqu'à ce que $longueur soit atteint
    while ($i < $longueur) {
        // prendre un caractère aléatoire
        $caractere = substr($possible, mt_rand(0, $longueurMax - 1), 1);

        // vérifier si le caractère est déjà utilisé dans $mdp
        if (!strstr($mdp, $caractere)) {
            // Si non, ajouter le caractère à $mdp et augmenter le compteur
            $mdp .= $caractere;
            $i++;
        }
    }

    // retourner le résultat final
    return $mdp;
}

function selectCodeAno($lien, $annee, $id_type_session)
{
    $sql = "SELECT parametre FROM bg_ref_parametre WHERE annee=$annee AND id_type_session=$id_type_session ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_array($result);
        $param = $row['parametre'];
    } else {
        $param = '';
    }

    return $param;
}
// Mise a jour du 01/12/2022
//Affichage de l'id de l'inspection à partir du nom de l'établissement
function selectInspectionByIdEtablissement($lien, $id_etablissement)
{
    $sql = "SELECT id_inspection FROM bg_ref_etablissement WHERE id=$id_etablissement ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $row = mysqli_fetch_array($result);
    return $row['id_inspection'];
}
function doublonsByWhere($lien, $annee, $id_inspection, $andStatut = '')
{
    $sql = "SELECT nom, prenoms, ddn,ldn, type_session,sexe,eta.id_inspection,eta.id, count(*) as nbre
	FROM bg_candidats can,bg_ref_etablissement eta
	WHERE  eta.id_inspection=$id_inspection AND  annee=$annee $andStatut  AND eta.id=can.etablissement
	GROUP BY nom, prenoms, ddn,ldn, type_session,sexe
	HAVING nbre>1 ";

    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $tab = array('nom', 'prenoms', 'ddn', 'ldn', 'sexe', 'id_candidat', 'type_session');
    if (mysqli_num_rows($result) > 0) {

        if ($_REQUEST['exec'] == 'candidats') {
            echo "<center><a href='./?exec=candidats'>Retour au Menu principal</a></center>";
        }

        echo "<h2>Liste des doublons</h2><p>Critères de recherche de doublons : Nom, Prénoms, Date de naissance, Lieu de Naissance, Sexe</p>", debut_boite_alerte(), "<table>";
        echo "<th>Noms et prénoms</th><th>Dates de naissance</th><th>Numéros des candidats</th><th>Lieu de Naissance</th><th>Sexe</th>";
        while ($row = mysqli_fetch_array($result)) {
            foreach ($tab as $val) {
                $$val = addslashes($row[$val]);
            }

            $sql2 = "SELECT id_candidat
			FROM bg_candidats
			WHERE annee=$annee $andStatut AND nom='$nom' AND prenoms='$prenoms'
			 AND ldn='$ldn' AND ddn='$ddn' AND type_session='$type_session' AND sexe=$sexe ";
            $result2 = bg_query($lien, $sql2, __FILE__, __LINE__);
            $ids = '';

            while ($row2 = mysqli_fetch_array($result2)) {
                $id_candidat = $row2['id_candidat'];
                // $id_etablissement=$row2['etablissement'];
                // $id_inspection=selectInspectionByIdEtablissement($lien,$id_etablissement);
                $ids .= "<a href='./?exec=candidats&etape=maj&id_inspection=$id_inspection&id_candidat=$id_candidat'>$id_candidat</a> &nbsp;&nbsp;";
                //$ldn=$row2['ldn'];
            }
            echo "<tr><td>$nom $prenoms</td><td>" . afficher_date($ddn) . "</td><td>$ids</td><td>$ldn</td><td>$sexe</td></tr>";
        }
        echo "</table>", fin_boite_alerte();
    } else {
        echo "<h2><center>~~~~Aucun doublon trouvé~~~~</center></h2>";
    }

}
function afficheSexe($sexe)
{
    if ($sexe == '2') {
        $x = 'M';
    } else { $x = 'F';}
    return $x;
}
function doublons($lien, $annee, $andStatut = '')
{
    $sql = "SELECT nom, prenoms, ddn,ldn, type_session,sexe,eta.id_inspection,eta.id, count(*) as nbre
	FROM bg_candidats can,bg_ref_etablissement eta,bg_ref_inspection ins
	WHERE   annee=$annee $andStatut  AND eta.id=can.etablissement AND ins.id=eta.id_inspection
	GROUP BY nom, prenoms, ddn,ldn, type_session,sexe
	HAVING nbre>1 ";

    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $tab = array('nom', 'prenoms', 'ddn', 'ldn', 'sexe', 'id_candidat', 'type_session');
    if (mysqli_num_rows($result) > 0) {

        // if ($_REQUEST['exec'] == 'candidats') {
        //     echo "<center><a href='./?exec=candidats'>Retour au Menu principal</a></center>";
        // }

        echo "<h2>Liste des doublons</h2><p>Critères de recherche de doublons : Nom, Prénoms, Date de naissance, Lieu de Naissance, Sexe</p>";
        echo debut_boite_alerte(), "<table>";
        echo "<th>Noms et prénoms</th><th>Dates de naissance</th><th>Numéros des candidats</th><th>Etablissement (Centre)</th>";
        while ($row = mysqli_fetch_array($result)) {
            foreach ($tab as $val) {
                $$val = addslashes($row[$val]);
            }
            // $etablissement = $row2['ets'];
            // echo "<tr><td>$nom $prenoms</td><td>" . afficher_date($ddn) . "</td><td>$id</td><td>" . selectEtablissementById($lien, $etablissement) . "</td></tr>";
            $sql2 = "SELECT id_candidat,id_inspection,can.etablissement as ets
                FROM bg_candidats can,bg_ref_etablissement eta,bg_ref_inspection ins
                WHERE annee=$annee $andStatut AND nom='$nom' AND prenoms='$prenoms'
                 AND ldn='$ldn' AND ddn='$ddn' AND type_session='$type_session' AND sexe=$sexe
                 AND eta.id=can.etablissement AND ins.id=eta.id_inspection";
            $result2 = bg_query($lien, $sql2, __FILE__, __LINE__);
            $ids = '';
            $etablissements = '';

            while ($row2 = mysqli_fetch_array($result2)) {
                $id_candidat = $row2['id_candidat'];
                // $id_etablissement=$row2['etablissement'];
                $id_inspection = $row2['id_inspection'];
                $etablissement = $row2['ets'];
                //$requete = "eta.id=$etablissement";
                //var_dump($id_inspection);
                $ids .= "<a href='./?exec=candidats&etape=maj&id_inspection=$id_inspection&id_candidat=$id_candidat'>$id_candidat</a> &nbsp;&nbsp;";
                $etablissements .= selectEtablissementById($lien, $etablissement) . "&nbsp;&nbsp;";
                //var_dump($etablissements);
                //$ldn=$row2['ldn'];
                // echo "<tr style='border: 2px groove #FFD384;'><td>$nom $prenoms</td><td>" . afficher_date($ddn) . "</td><td><a href='./?exec=candidats&etape=maj&id_inspection=$id_inspection&id_candidat=$id_candidat'>$id_candidat</a> &nbsp;&nbsp;</td><td>" . selectEtablissementById($lien, $etablissement) . "</td></tr>";
                echo "<tr><td>$nom $prenoms</td><td>" . afficher_date($ddn) . "</td><td><a href='./?exec=candidats&etape=maj&id_inspection=$id_inspection&id_candidat=$id_candidat'>$id_candidat</a> &nbsp;&nbsp;</td><td>" . selectEtablissementById($lien, $etablissement) . "</td></tr>";
            }
            //echo "<tr><td>$nom $prenoms</td><td>" . afficher_date($ddn) . "</td><td>$ids</td><td>$etablissements</td></tr>";
        }
        echo "</table>", fin_boite_alerte();
    } else {
        echo "<h2><center>~~~~Aucun doublon trouvé~~~~</center></h2>";
    }

}

//Recuperer l'unite de mesure de chaque atelier
function getUniteAtelier($lien, $id_atelier)
{
    $sql = "SELECT unite_mesure FROM bg_ref_atelier WHERE id=$id_atelier";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $row = mysqli_fetch_array($result);
    $unite = $row['unite_mesure'];
    return $unite;
}

//Recuperer le nombre de performance
function getNbPerformance($lien, $id_atelier)
{
    $sql = "SELECT COUNT(*) as nbre FROM bg_performance WHERE id_atelier=$id_atelier";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $row = mysqli_fetch_array($result);
    $nb = $row['nbre'];
    return $nb;
}

//Recuperer la note et la performance d'un candidat pour chacun des ateliers
function notesEPSParCandidat($lien, $annee, $num_table)
{
    $sql = "SELECT id_atelier, performance, note_perf, note
			FROM bg_notes_performance perf, bg_notes_eps notes
			WHERE perf.annee=$annee AND notes.annee=$annee AND notes.num_table=perf.num_table AND notes.num_table='$num_table' AND perf.num_table='$num_table' ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    while ($row = mysqli_fetch_array($result)) {
        foreach (array('id_atelier', 'performance', 'note_perf', 'note') as $val) {
            $$val = $row[$val];
        }

        $tab[$id_atelier]['performance'] = $performance;
        $tab[$id_atelier]['note_perf'] = $note_perf;
        $tab['note'] = $note;
    }
    return $tab;
}

// Générer un nombre aléatoire entre 2 et 20
function generateRandomNumber()
{
    $randomNumber = rand(2, 20);

    return $randomNumber;
}

//Retourne un tableau avec le sexe et les nom, prenoms
function getNoms($lien, $annee, $num_table = '', $id_candidat = 0)
{
    $andWhere = '';
    if ($id_candidat > 0) {
        $andWhere .= " AND id_candidat='$id_candidat' ";
    }

    if ($num_table != '') {
        $andWhere .= " AND num_table='$num_table' ";
    }

    $sql = "SELECT nom, prenoms, sexe FROM bg_candidats WHERE annee=$annee $andWhere LIMIT 0,1 ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $row = mysqli_fetch_array($result);
    foreach (array('nom', 'prenoms', 'sexe') as $val) {
        $$val = $row[$val];
    }

    $tCan['sexe'] = $sexe;
    $tCan['nom'] = $nom;
    $tCan['prenoms'] = $prenoms;
    if ($sexe == 1) {
        $tCan['noms'] = "$nom $prenoms";
    } else {
        $tCan['noms'] = "$nom $prenoms";
    }

    return $tCan;
}

function optionJurys($lien, $annee, $nom_champ, $juryChoisi)
{
    $sql = "SELECT DISTINCT jury FROM bg_repartition WHERE annee=$annee ORDER BY jury ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $options = "<select name='$nom_champ'>";
    $options .= "<option value=0>-=[Jury]=-</option>";
    while ($row = mysqli_fetch_array($result)) {
        $jury = $row['jury'];
        if ($juryChoisi == $jury) {
            $selected = 'selected';
        } else {
            $selected = '';
        }

        $options .= "<option value='$jury' $selected>$jury</option>";
    }
    $option .= "</select>";
    return $options;
}

function presidentJurys($lien, $annee, $jury)
{
    $sql = "SELECT concat(nom, ' ', prenoms) as president
		from bg_presidents pres, bg_presidentjury prej
		WHERE pres.annee=$annee AND prej.annee=$annee AND pres.id=prej.id_president AND prej.jury=$jury ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $row = mysqli_fetch_array($result);
    return $row['president'];
}

function dateDeliberation($lien, $annee, $jury, $type = 1)
{
    $sql = "SELECT id_type_session FROM bg_repartition WHERE annee=$annee AND jury=$jury LIMIT 0,1 ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $row = mysqli_fetch_array($result);
    $id_type_session = $row['id_type_session'];

    if ($type == 1) {
        $sql2 = "SELECT * FROM bg_ref_dates WHERE annee=$annee AND id_type_session=$id_type_session LIMIT 0,1";
    } else {
        $sql2 = "SELECT * FROM bg_ref_dates WHERE annee=$annee AND id_type_session=$id_type_session LIMIT 1,1 ";
    }

    $result2 = bg_query($lien, $sql2, __FILE__, __LINE__);
    $row2 = mysqli_fetch_array($result2);
    $dates = afficher_date($row2['dates']);
/*
$tTmp=explode('-',$dates);
$jour=(int)$tTmp[2];
$mois=(int)$tTmp[1];
$annee=(int)$tTmp[0];
setlocale(LC_TIME, 'fr_FR','fr_BE.UTF8','fr_FR.UTF8');
$dates=strftime("%A %d %B %Y", mktime(0, 0, 0, $mois, $jour, $annee));
 */
    return $dates;
}

function moisSession($lien, $annee, $jury)
{
    $sql = "SELECT id_type_session FROM bg_repartition WHERE annee=$annee AND jury=$jury LIMIT 0,1 ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $row = mysqli_fetch_array($result);
    $id_type_session = $row['id_type_session'];

    $sql2 = "SELECT * FROM bg_ref_mois WHERE annee=$annee AND id_type_session=$id_type_session ";
    $result2 = bg_query($lien, $sql2, __FILE__, __LINE__);
    $row2 = mysqli_fetch_array($result2);
    $mois = $row2['mois'];
    return $mois;
}

function moisSession2($lien, $annee)
{
    /* $sql = "SELECT id_type_session FROM bg_repartition WHERE annee=$annee LIMIT 0,1 ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $row = mysqli_fetch_array($result);
    $id_type_session = $row['id_type_session']; */

    $sql2 = "SELECT * FROM bg_ref_mois WHERE annee=$annee AND id_type_session=1 ";
    $result2 = bg_query($lien, $sql2, __FILE__, __LINE__);
    $row2 = mysqli_fetch_array($result2);
    $mois = $row2['mois'];
    return $mois;
}

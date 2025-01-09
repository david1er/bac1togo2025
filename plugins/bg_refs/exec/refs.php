<?php
if (!defined('_ECRIRE_INC_VERSION')) {
    return;
}
include_spip('inc/presentation'); // alleger les inclusions avec un inc/presentation_mini
include '../plugins/fonctions/generales.php';
$lien = lien();

setlocale(LC_TIME, "fr_FR");

/** Affiche le tableau des valeurs presentes dans un referentiel
 * @param $refer: referentiel en question
 * */
function tableau_referentiels($lien, $refer, $sql)
{
    //tableau des valeurs du referentiel
    echo debut_cadre_enfonce('', true, '', "TABLEAU " . strtoupper(str_replace('_', ' ', $refer)) . "");
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $tabChamps = mysqli_fetch_fields($result);
    $tableau = "<table class=''><thead>";
    $i = 0;
    foreach ($tabChamps as $champs) {
        $champ = $champs->name;
        $colonne = ucwords($champ);
        if (stristr($colonne, "id_")) {
            $champ_ref[$i] = substr($colonne, 3);
            $j[$i] = $i;
            $tableau .= "<th>" . strtoupper(substr($colonne, 3)) . "</th>";
        } else {
            $tableau .= "<th>" . str_replace('_', ' ', strtoupper($colonne)) . "</th>";
        }

        $i++;
    }
    $tableau .= "</thead><tbody>";
    $champ = count($tabChamps);
    while ($row = mysqli_fetch_array($result)) {
        //$modifier="modifier";
        $id = $row[0];
        if (isset($_GET['idc'])) {$idc = $_GET['idc'];
            $complement = "&idc=$idc";}
        $tableau .= "<tr><td><a href='" . generer_url_ecrire('refs') . "&refer=$refer&etape=" . "modifier" . "$complement&id=$id'>$id</a></td>";
        for ($k = 1; $k < $champ; $k++) {
            $valeur = $row[$k];
            if (is_array($j)) {
                foreach ($j as $key) {
                    if ($k == $key) {
                        $valeur = selectReferentiel($lien, $champ_ref[$key], $valeur);
                    }
                }
            }

            if ($refer == 'parametre' && $k == 1) {
                $valeur = '**********';
            }
            //Pour rendre invisible le parametre pour anonymer
            $tableau .= "<td>$valeur</td>";
        }
        $tableau .= "</tr>";
    }
    $tableau .= "</tbody><tfoot><tr></tr></tfoot></table>";
    echo $tableau;
    echo fin_cadre_enfonce();
}

function InsererReferentiel($referentiel, $lien, $sql)
{
    $annee = date('Y');
    echo debut_cadre_enfonce("../images/siou_carre.png", '', '', "INSERTION DE " . strtoupper(str_replace('_', ' ', $referentiel)), "", "");
    $referentiel = strtolower($referentiel);
    $form_ins = "<p></p><form action='' method='POST' class='forml spip_xx-small' name='form_ins'>";
    $form_ins .= "<TABLE>";
//    echo $sql;
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $form_ins .= "<TR>";
    $TabChamps = mysqli_fetch_fields($result);
    foreach ($TabChamps as $champs) {
        $champ = $champs->name;
        if ($champ != 'id') {
            if (stristr($champ, 'id_')) {
                $champ_ss_id = substr($champ, 3);
                $form_ins .= "<td><label>" . strtoupper(str_replace('_', ' ', $champ_ss_id)) . "</label>";
                $form_ins .= "<select $disabled name='$champ' value='-=$champ=-'>";
                $form_ins .= optionsReferentiel($lien, $champ_ss_id, '', '');
                $form_ins .= "</select>";
            } elseif ($champ == 'annee') {
                $form_ins .= "<td><label>" . strtoupper(str_replace('_', ' ', $champ)) . "</label>";
                $form_ins .= "<select $disabled name='$champ' value='-=$champ=-'>";
                $form_ins .= optionsAnnee($annee, '');
                $form_ins .= "</select>";
            } elseif ($champ == 'parametre') {
                $form_ins .= "<td><label>" . strtoupper(str_replace('_', ' ', $champ)) . "</label>";
                $form_ins .= "<input type='PASSWORD' name='$champ' value=''></td>";
            } elseif ($champ == 'dates') {
                $form_ins .= "<td><label>" . strtoupper(str_replace('_', ' ', $champ)) . "</label>";
                $form_ins .= "<input type='date' name='$champ' value=''></td>";
            } else {
                $form_ins .= "<td><label>" . strtoupper(str_replace('_', ' ', $champ)) . "</label>";
                if (in_array($champ, array('borne_numero', 'borne_jury'))) {$size = 6;} else {
                    $size = 10;
                }

                $form_ins .= "<input type='text' id='$champ' name='$champ' value='' size=$size autofocus /></td>";
            }
        }
    }
    $form_ins .= "</TR></TABLE>";
    $form_ins .= "<table>";
    $form_ins .= "<tr><td><input type='hidden' value='$referentiel' name='refer'></td>";
    $form_ins .= "<td><input type='submit' value='INS&Eacute;RER " . strtoupper(str_replace('_', ' ', $referentiel)) . "' name='inserer' class='submit' onClick=\"if(form_ins.$referentiel.value=='') {alert('Vous ne pouvez pas enregistrer un élément ($referentiel) vide'); return false;}  \" ></td>";
    $form_ins .= "<td><input type='button' value='ANNULER & QUITTER' class='submit' onClick=window.location='" . generer_url_ecrire('refs') . "'></td>";

    $form_ins .= "</tr></form></table>";
    echo $form_ins;
    echo fin_cadre_enfonce();
}

function modifierReferentiel($lien, $referentiel)
{
    $annee = date('Y');
    $referentiel = strtolower($referentiel);
    $id = $_REQUEST['id'];
    $sql = "SELECT * FROM bg_ref_" . $referentiel . " WHERE id=$id";
    echo debut_cadre_couleur("../images/siou_carre.png", '', '', "Formulaire de modification de $referentiel", "", "");
    $form_ins = "<p></p><form action='' method='POST' class='forml spip_xx-small'>";
    $form_ins .= "<TABLE>";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $tabChamps = mysqli_fetch_fields($result);
    $nbre_champs = count($tabChamps);
    $form_ins .= "<TR>";
    $row = mysqli_fetch_array($result);
    foreach ($tabChamps as $champs) {
        $champ = $champs->name;
        if ($champ != 'id') {
            $var = $row[$champ];
            $form_ins .= "<td><label>" . strtoupper(str_replace('_', ' ', $champ)) . "</label>";
            if (stristr($champ, 'id_')) {
                $champ_ss_id = substr($champ, 3);
                $form_ins .= "<select $disabled name='$champ' value='-=$ref=-'>";
                $form_ins .= optionsReferentiel($lien, $champ_ss_id, $var, '');
                $form_ins .= "</select>";
            } elseif ($champ == 'annee') {
                $form_ins .= "<select $disabled name='$champ' value='-=$ref=-'>";
                $form_ins .= optionsAnnee($annee, $var);
                $form_ins .= "</select>";
            } elseif ($champ == 'parametre') {
                $form_ins .= "<input type='password' name='$champ' value=\"$var\"></td>";
            } elseif ($champ == 'dates') {
                $form_ins .= "<input type='date' name='$champ' value=\"$var\"></td>";
            } else {
                if (in_array($champ, array('borne_numero', 'borne_jury'))) {$size = 6;} else {
                    $size = 10;
                }

                $form_ins .= "<input type='text' id='$champ' name='$champ' value=\"$var\" size=$size /></td>";
            }
        }
    }
    if (isset($_GET['idc'])) {$idc = $_GET['idc'];
        $complement = "&idc=$idc";}
    $form_ins .= "<td><input type='hidden' name='modification' value='$id'> </td>";
    $form_ins .= "<td><input type='hidden' name='refer' value='$referentiel'> </td>";
    $form_ins .= "</TR></TABLE>";
    $form_ins .= "<table>";
    $form_ins .= "<tr><td><input type='hidden' value='$referentiel' name='refer'></td>";
    $form_ins .= "<td><input type='submit' value='MODIFIER " . strtoupper(str_replace('_', ' ', $referentiel)) . "'></td>";
    $form_ins .= "<td><input type='button' class='fondo' value='SUPPRIMER' onclick=\"if(confirm('Souhaitez-vous vraiment supprimer ce(tte) $referentiel?')) window.location='" . generer_url_ecrire('refs') . "&refer=$referentiel&etape=" . "supprimer" . "&id=$id';\"></td>";
    $form_ins .= "<td><input type='button' class='fondo' value='ANNULER' onClick=window.location='" . generer_url_ecrire('refs') . "&refer=$referentiel$complement'></td>";
    $form_ins .= "</tr></form></table>";
    echo $form_ins;
    echo fin_cadre_couleur();

}

function exec_refs()
{
    $exec = _request('exec');

    $titre = "exec_$exec";
    $navigation = "";
    $extra = "";
    $commencer_page = charger_fonction('commencer_page', 'inc');
    echo $commencer_page($titre);

    $statut = getStatutUtilisateur();
    $lien = lien();
    $annee = date("Y");
    foreach ($_REQUEST as $key => $val) {
        $$key = mysqli_real_escape_string($lien, trim($val));
    }

/*
echo "<pre>";
print_r($_POST);
echo "</pre>";
 */

    if (isAutorise(array('Admin'))) {

        if (!isset($_REQUEST['refer'])) {
            echo debut_gauche();

            echo debut_droite();
            echo debut_cadre_enfonce('', '', '', 'Choisir un référentiel', "", "");

            $tBddConf = getBddConf($conf = '');
            $base = $tBddConf['bdd'];
            $sql = "SHOW tables from $base LIKE 'bg_ref_%'";
            $result = bg_query($lien, $sql, __FILE__, __LINE__);
//        echo $sql;

            $liste = "<form action='" . generer_url_ecrire('refs') . "' method='POST' class='forml spip_xx-small' name='referentiels'>";
            $liste .= "<center><select name='refer' onchange='document.forms.referentiels.submit()'><option>-=[Référentiels]=-</option>";
            while ($row = mysqli_fetch_array($result)) {
                $ref = $row[0];
                //$ref=explode('_',$ref);
                //$ref=ucwords($ref[2]);
                $ref = ucwords(substr($ref, 7));
                if ($ref != 'Etablissement' && $ref != 'Type_salle') {
                    $liste .= "<option value='$ref'>";
                    $liste .= strtoupper(str_replace('_', ' ', $ref));
                    $liste .= "</option>";
                }
            }
            $liste .= "</select></center>";
            $liste .= "</form>";

            echo $liste;

            echo fin_cadre_enfonce();

        }

        if (isset($_REQUEST['refer'])) {

            echo debut_gauche('', true);
            echo debut_boite_info();
            echo "<ul>";
            echo "<li>Pour modifier ou supprimer un enregistrement, cliquer sur son <b>ID</b></li>";
            echo "<li>Pour revenir &agrave; la liste des référentiels, cliquer <a href='" . generer_url_ecrire('refs') . "'>ici</a></li>";
            echo " </ul>";
            echo fin_boite_info();
            fin_gauche();

            $refer = strtolower($refer);
            $val_ref = $$refer;
            switch ($refer) {

                case 'epreuve_facultative_a':
                case 'epreuve_facultative_b':
                case 'eps':
                case 'etat':
                case 'pays':
                case 'region':
                case 'sexe':
                case 'type_etablissement':
                case 'type_note':
                case 'type_session':
                case 'langue_vivante':
                case 'etat_physique':
                case 'motif':
                case 'activite':
                case 'genre':
                case 'centre':
                case 'plugins':
                    echo debut_droite('', true);
                    if ($_REQUEST['etape'] == 'supprimer') {
                        $id = $_REQUEST['id'];
                        $sql_sup = "DELETE FROM bg_ref_$refer WHERE id=$id";
                        mysqli_query($lien, $sql_sup);
                    }

                    if ($_REQUEST['modification'] != '') {
                        $id = $_REQUEST['modification'];
                        $sql_maj = "UPDATE bg_ref_$refer SET $refer='$val_ref' WHERE id=$id";
                        mysqli_query($lien, $sql_maj);
                    }

                    if (isset($_REQUEST['inserer'])) {
                        if ($refer == '') {
                            echo "Veuillez entrer le nom du $refer";
                        } else {
                            $sql_ins = "INSERT INTO bg_ref_$refer ($refer) VALUES ('$val_ref')";
                            bg_query($lien, $sql_ins, __FILE__, __LINE__);
                        }
                    }
                    $sql = "SELECT * FROM bg_ref_$refer ORDER BY $refer \n";
                    if ($_REQUEST['etape'] == 'modifier') {
                        modifierReferentiel($lien, $refer);
                    } else {
                        InsererReferentiel($refer, $lien, $sql);
                    }
                    tableau_referentiels($lien, $refer, $sql);
                    break;

                case 'matiere':
//            case 'type_salle':
                case 'directeur':
                    if ($refer == 'matiere') {
                        $attribut2 = 'abreviation';
                    }

//            elseif($refer=='type_salle')    $attribut2='capacite';
                    elseif ($refer == 'directeur') {
                        $attribut2 = 'annee';
                    }

                    echo debut_droite('', true);

//            if($_REQUEST['etape']=='supprimer'){
                    //                $id=$_REQUEST['id'];
                    //                $sql_sup="DELETE FROM bg_ref_$refer WHERE id=$id";
                    //                mysql_query($sql_sup);
                    //            }

                    if ($_REQUEST['modification'] != '') {
                        $id = $_REQUEST['modification'];
                        $sql_maj = "UPDATE bg_ref_$refer SET $refer='$val_ref', $attribut2='" . $$attribut2 . "' WHERE id=$id";
                        bg_query($lien, $sql_maj, __FILE__, __LINE__);

                    }

                    if (isset($_REQUEST['inserer'])) {
                        if ($refer == '') {
                            echo "Veuillez entrer le nom du $refer";
                        } else {
                            $sql_ins = "INSERT INTO bg_ref_$refer ($refer,$attribut2) VALUES ('$val_ref','" . $$attribut2 . "')";
                            bg_query($lien, $sql_ins, __FILE__, __LINE__);
                        }
                    }
                    $sql = "SELECT * FROM bg_ref_$refer ORDER BY $refer \n";
                    if ($_REQUEST['etape'] == 'modifier') {
                        modifierReferentiel($lien, $refer);
                    } else {
                        InsererReferentiel($refer, $lien, $sql);
                    }
                    tableau_referentiels($lien, $refer, $sql);
                    break;

                case 'parametre':
                    $attribut2 = 'id_type_session';
                    echo debut_droite('', true);
//            if($_REQUEST['etape']=='supprimer'){
                    //                $id=$_REQUEST['id'];
                    //                $sql_sup="DELETE FROM bg_ref_$refer WHERE id=$id";
                    //                mysql_query($sql_sup);
                    //            }

                    if ($_REQUEST['modification'] != '') {
                        $id = $_REQUEST['modification'];
                        $sql_maj = "UPDATE bg_ref_$refer SET $refer='$val_ref', $attribut2='" . $$attribut2 . "' WHERE id=$id";
                        bg_query($lien, $sql_maj, __FILE__, __LINE__);
                    }

                    if (isset($_REQUEST['inserer'])) {
                        if ($refer == '') {
                            echo "Veuillez entrer le nom du $refer";
                        } else {
                            $sql_ins = "INSERT INTO bg_ref_$refer ($refer,$attribut2,annee) VALUES ('$val_ref','" . $$attribut2 . "',$annee)";
                            bg_query($lien, $sql_ins, __FILE__, __LINE__);
                        }
                    }
                    $sql = "SELECT * FROM bg_ref_$refer ORDER BY annee, id_type_session \n";
                    if ($_REQUEST['etape'] == 'modifier') {
                        modifierReferentiel($lien, $refer);
                    } else {
                        InsererReferentiel($refer, $lien, $sql);
                    }
                    tableau_referentiels($lien, $refer, $sql);
                    break;

                case 'dates':
                case 'mois':
                    $attribut2 = 'id_type_session';
                    echo debut_droite('', true);
//            if($_REQUEST['etape']=='supprimer'){
                    //                $id=$_REQUEST['id'];
                    //                $sql_sup="DELETE FROM bg_ref_$refer WHERE id=$id";
                    //                mysql_query($sql_sup);
                    //            }

                    if ($_REQUEST['modification'] != '') {
                        $id = $_REQUEST['modification'];
                        $sql_maj = "UPDATE bg_ref_$refer SET $refer='$val_ref', $attribut2='" . $$attribut2 . "' WHERE id=$id";
                        bg_query($lien, $sql_maj, __FILE__, __LINE__);
                    }

                    if (isset($_REQUEST['inserer'])) {
                        if ($refer == '') {
                            echo "Veuillez entrer le nom du $refer";
                        } else {
                            $sql_ins = "INSERT INTO bg_ref_$refer ($refer,$attribut2,annee) VALUES ('$val_ref','" . $$attribut2 . "',$annee)";
                            bg_query($lien, $sql_ins, __FILE__, __LINE__);
                        }
                    }
                    $sql = "SELECT * FROM bg_ref_$refer ORDER BY annee, id_type_session \n";
                    if ($_REQUEST['etape'] == 'modifier') {
                        modifierReferentiel($lien, $refer);
                    } else {
                        InsererReferentiel($refer, $lien, $sql);
                    }
                    tableau_referentiels($lien, $refer, $sql);
                    break;

                case 'prefecture':
                case 'inspection':
                case 'ville':
                case 'region_division':
                    if ($refer == 'prefecture') {$attribut2 = 'id_region';} elseif ($refer == 'inspection') {$attribut2 = 'id_region';} elseif ($refer == 'ville') {$attribut2 = 'id_prefecture';} elseif ($refer == 'region_division') {$attribut2 = 'id_region';
                        $attribut3 = '';}
                    echo debut_droite('', true);
                    if ($_REQUEST['etape'] == 'supprimer') {
                        $sql3 = "DELETE FROM bg_ref_$refer WHERE id=$id";
                        bg_query($lien, $sql3, __FILE__, __LINE__);
                    }

                    if ($_REQUEST['modification'] != '') {
                        $id = $_REQUEST['modification'];
                        if ($refer == 'region_division') {
                            $sql_maj = "UPDATE bg_ref_$refer SET $refer='$val_ref', $attribut2='" . $$attribut2 . "', borne_numero='$borne_numero', borne_jury='$borne_jury' WHERE id=$id";
                        } else {
                            $sql_maj = "UPDATE bg_ref_$refer SET $refer='$val_ref', $attribut2='" . $$attribut2 . "' WHERE id=$id";
                        }
                        bg_query($lien, $sql_maj, __FILE__, __LINE__);
                    }
                    if (isset($_REQUEST['inserer'])) {
                        if (($refer == '' || $$attribut2 == 0)) {
                            echo debut_boite_alerte();
                            echo "Veuillez entrer: $refer et/ou " . substr($attribut2, 3);
                            echo fin_boite_alerte();
                        } else {
                            if ($refer == 'region_division') {
                                $sql2 = "INSERT INTO bg_ref_$refer ($refer,$attribut2,borne_numero,borne_jury) VALUES ('$val_ref','" . $$attribut2 . "','$borne_numero','$borne_jury')";
                            } else {
                                $sql2 = "INSERT INTO bg_ref_$refer ($refer,$attribut2) VALUES ('$val_ref','" . $$attribut2 . "')";
                            }
                            bg_query($lien, $sql2, __FILE__, __LINE__);
                        }
                    }
                    //Tri par
                    $attribut2_ss_id = substr($attribut2, 3);
                    echo debut_cadre_enfonce('', true, '', "");
                    $tri = "<form action='" . generer_url_ecrire('refs') . "' method='POST' name='tri'><label>Tri par: </label><select name='par_attribut2' onchange='document.forms.tri.submit(); document.forms.tri.par_attribut2.value=this.value;'>"; //<option value='tous'>-=[Régions]=-</option>";
                    $tri .= optionsReferentiel($lien, "$attribut2_ss_id", '', '');
                    $tri .= "</select><input type='hidden' name='refer' value='$refer'><input type='submit' value='OK'></form>";
                    echo $tri;
                    echo fin_cadre_relief(true);

                    $where = " WHERE $attribut2=";
                    (isset($_POST['par_attribut2']) and $_POST['par_attribut2'] != 0) ? $where .= $_POST['par_attribut2'] : $where = '';
                    //Fin tri

                    $sql = "SELECT * FROM bg_ref_$refer $where ORDER BY $refer";
                    if ($_REQUEST['etape'] == 'modifier') {
                        modifierReferentiel($lien, $refer);
                    } else {
                        InsererReferentiel($refer, $lien, $sql);
                    }

                    tableau_referentiels($lien, $refer, $sql);
                    break;

                case 'serie':
                case 'atelier':
                    if ($refer == 'serie') {$attribut2 = 'intitule';} elseif ($refer == 'atelier') {$attribut2 = 'unite_mesure';}
                    echo debut_droite('', true);
                    if ($_REQUEST['etape'] == 'supprimer') {
                        $id = $_REQUEST['id'];
                        $sql_sup = "DELETE FROM bg_ref_$refer WHERE id=$id";
                        bg_query($lien, $sql_sup, __FILE__, __LINE__);
                    }

                    if ($_REQUEST['modification'] != '') {
                        $id = $_REQUEST['modification'];
                        $sql_maj = "UPDATE bg_ref_$refer set $refer='$val_ref', $attribut2='" . $$attribut2 . "' WHERE id=$id";
                        bg_query($lien, $sql_maj, __FILE__, __LINE__);
                    }

                    if (isset($_REQUEST['inserer'])) {
                        if ($refer == '') {
                            echo "Veuillez entrer $refer";
                        } else {
                            $sql_ins = "INSERT INTO bg_ref_$refer ($refer, $attribut2) VALUES ('$serie','" . $$attribut2 . "')";
                            bg_query($lien, $sql_ins, __FILE__, __LINE__);
                        }
                    }
                    $sql = "SELECT * FROM bg_ref_$refer ORDER BY $refer \n";
                    if ($_REQUEST['etape'] == 'modifier') {
                        modifierReferentiel($lien, $refer);
                    } else {
                        InsererReferentiel($refer, $lien, $sql);
                    }

                    tableau_referentiels($lien, $refer, $sql);

                    break;
                case 'commune':
                    if ($refer == 'commune') {$attribut2 = 'id_prefecture';}
                    $attribut3 = '';
                    echo debut_droite('', true);
                    if ($_REQUEST['etape'] == 'supprimer') {
                        $sql3 = "DELETE FROM bg_ref_$refer WHERE id=$id";
                        bg_query($lien, $sql3, __FILE__, __LINE__);
                    }

                    if ($_REQUEST['modification'] != '') {
                        $id = $_REQUEST['modification'];
                        // if ($refer == 'prefecture') {
                        //     $sql_maj = "UPDATE bg_ref_$refer SET $refer='$val_ref', $attribut2='" . $$attribut2 . "', borne_numero='$borne_numero', borne_jury='$borne_jury' WHERE id=$id";
                        // } else {
                        $sql_maj = "UPDATE bg_ref_$refer SET $refer='$val_ref', $attribut2='" . $$attribut2 . "' WHERE id=$id";
                        // }
                        bg_query($lien, $sql_maj, __FILE__, __LINE__);
                    }
                    if (isset($_REQUEST['inserer'])) {
                        if (($refer == '' || $$attribut2 == 0)) {
                            echo debut_boite_alerte();
                            echo "Veuillez entrer: $refer et/ou " . substr($attribut2, 3);
                            echo fin_boite_alerte();
                        } else {
                            // if ($refer == 'prefecture') {
                            //     $sql2 = "INSERT INTO bg_ref_$refer ($refer,$attribut2,borne_numero,borne_jury) VALUES ('$val_ref','" . $$attribut2 . "','$borne_numero','$borne_jury')";
                            // } else {
                            $sql2 = "INSERT INTO bg_ref_$refer ($refer,$attribut2) VALUES ('$val_ref','" . $$attribut2 . "')";
                            // }
                            bg_query($lien, $sql2, __FILE__, __LINE__);
                        }
                    }
                    //Tri par
                    $attribut2_ss_id = substr($attribut2, 3);
                    echo debut_cadre_enfonce('', true, '', "");
                    $tri = "<form action='" . generer_url_ecrire('refs') . "' method='POST' name='tri'><label>Tri par: </label><select name='par_attribut2' onchange='document.forms.tri.submit(); document.forms.tri.par_attribut2.value=this.value;'>"; //<option value='tous'>-=[Régions]=-</option>";
                    $tri .= optionsReferentiel($lien, "$attribut2_ss_id", '', '');
                    $tri .= "</select><input type='hidden' name='refer' value='$refer'><input type='submit' value='OK'></form>";
                    echo $tri;
                    echo fin_cadre_relief(true);

                    $where = " WHERE $attribut2=";
                    (isset($_POST['par_attribut2']) and $_POST['par_attribut2'] != 0) ? $where .= $_POST['par_attribut2'] : $where = '';
                    //Fin tri

                    $sql = "SELECT * FROM bg_ref_$refer $where ORDER BY $refer";
                    if ($_REQUEST['etape'] == 'modifier') {
                        modifierReferentiel($lien, $refer);
                    } else {
                        InsererReferentiel($refer, $lien, $sql);
                    }

                    tableau_referentiels($lien, $refer, $sql);
                    break;

            }
        }

    }
    echo fin_gauche(), fin_page();

/*
echo debut_gauche("404_$exec", true);
echo pipeline('affiche_gauche', array('args' => array('exec' => '404', 'exec_erreur' => $exec), 'data' => ''));

echo creer_colonne_droite("404", true);
echo pipeline('affiche_droite', array('args' => array('exec' => '404', 'exec_erreur' => $exec), 'data' => ''));

echo debut_droite("404", true);
echo "<h1 class='grostitre'>" . _T('fichier_introuvable', array('fichier' => $exec)) . "</h1>";
echo pipeline('affiche_milieu', array('args' => array('exec' => '404', 'exec_erreur' => $exec), 'data' => ''));

echo fin_gauche(), fin_page();
 */

}

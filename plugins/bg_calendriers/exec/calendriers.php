<?php
if (!defined('_ECRIRE_INC_VERSION')) {
    return;
}

include_spip('inc/presentation'); // alleger les inclusions avec un inc/presentation_mini
include '../plugins/fonctions/generales.php';

setlocale(LC_TIME, "fr_FR");

function InsererCalendrier($lien, $id_serie, $annee)
{
    foreach ($_REQUEST as $key => $val) {
        $$key = mysqli_real_escape_string($lien, trim(addslashes($val)));
    }

    if (isset($_POST['inserer'])) {
        $sql_sup = "DELETE FROM bg_calendrier WHERE annee=$annee AND id_serie=$id_serie ";
        bg_query($lien, $sql_sup);
        if ($id_matiere != 0 && $id_type_note != 0) {
//            $date=formater_date($date);
            $sql = "INSERT INTO bg_calendrier (`id_serie`, `id_matiere`, `id_type_note`, `annee`, `date`, `heure`, `duree`, `coeff`)
					VALUES ('$id_serie', '$id_matiere','$id_type_note','$annee','$date','$heure','$duree','$coeff') ";
            bg_query($lien, $sql, __FILE__, __LINE__);
        }
        for ($j = 1; $j < $i; $j++) {
            $champ_matiere = 'id_matiere_' . $j;
            $champ_type_note = 'id_type_note_' . $j;
            $champ_date = 'date_' . $j;
            $champ_heure = 'heure_' . $j;
            $champ_duree = 'duree_' . $j;
            $champ_coeff = 'coeff_' . $j;
            $id_matiere_maj = $$champ_matiere;
            $id_type_note_maj = $$champ_type_note;
            $date_maj = $$champ_date;
            $heure_maj = $$champ_heure;
            $duree_maj = $$champ_duree;
            $coeff_maj = $$champ_coeff;
//            if($id_matiere_maj!=0 && $id_type_note_maj!=0){
            //                $date_maj=formater_date($date_maj);
            $sql = "INSERT INTO bg_calendrier (`id_serie`, `id_matiere`, `id_type_note`, `annee`, `date`, `heure`, `duree`, `coeff`)
					VALUES ('$id_serie', '$id_matiere_maj','$id_type_note_maj','$annee','$date_maj','$heure_maj','$duree_maj','$coeff_maj') ";
            bg_query($lien, $sql, __FILE__, __LINE__);
            //echo $sql."<br/>";
            //            }
        }
        $sql2 = "DELETE FROM bg_calendrier WHERE annee=$annee AND id_serie=$id_serie AND id_matiere=0 ";
        bg_query($lien, $sql2, __FILE__, __LINE__);
        //echo $sql2;
    }
    $serie = selectReferentiel($lien, 'serie', $id_serie);
    echo debut_cadre_relief("../images/logo.png", '', '', "INSERTION ET MIS A JOUR DU CALENDRIER - SERIE $serie", "", "");
    $form_ins = "<p></p><form action='' method='POST' class='forml spip_xx-small' name='form_calendrier' onload=\"document.forms.form_calendrier.id_matiere.focus();\">";
    $form_ins .= "<TABLE>";
    $form_ins .= "<tr><td>Date</td><td>Démar.</td><td>Mati&egrave;re</td><td>type</td><td>Durée</td><td>Coeff</td></tr>";
//    $form_ins.="<tr><td><input type_note='hidden' name='id_serie' value='$id_serie'></td></tr>";

    $sql2 = "SELECT * FROM bg_calendrier WHERE annee=$annee AND id_serie=$id_serie ORDER BY id_type_note, date, heure";
    $result2 = bg_query($lien, $sql2, __FILE__, __LINE__);

    $i = 1;
    if (mysqli_num_rows($result2) > 0) {
        $tab2 = array('id_serie', 'id_matiere', 'id_type_note', 'date', 'heure', 'duree', 'coeff');
        while ($row2 = mysqli_fetch_array($result2)) {
            foreach ($tab2 as $var2) {
                $$var2 = $row2[$var2];
            }

//            $date=afficher_date($date);
            if ($id_type_note == 1) {
                if ($heure == '00:00:00') {
                    $texte1 = "Pour les mati&egrave;res écrites vous devez choisir l'heure de démarrage <br/>";
                }

                if ($duree == 0) {
                    $texte2 = "Pour les mati&egrave;res écrites vous devez choisir la durée <br/>";
                }

            }
            if ($coeff <= 0) {
                $texte3 = "Toutes les mati&egrave;res doivent avoir un coefficient <br/> ";
            }
            $form_ins .= "<tr>
				<td><input type='date' id='date_$i' name='date_$i' value='$date' size=4 maxlength=10 /></td>
				<td><input type='time' id='heure_$i' name='heure_$i' pattern='^(?:[01][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$' title='hh:mm:ss' value='$heure' size=1 maxlength=8 /></td>
				<td><select name='id_matiere_$i'>" . optionsReferentiel($lien, 'matiere', $id_matiere) . "</select></td>
				<td><select name='id_type_note_$i'>" . optionsReferentiel($lien, 'type_note', $id_type_note, ' WHERE id>0') . "</select></td>
				<td><input type='text' name='duree_$i' value='$duree' size=1 maxlength=3></td>
				<td><input type='text' name='coeff_$i' value='$coeff' size=1 maxlength=3></td>
				</tr>";
            $i++;
        }
    }
    $form_ins .= "<tr><td colspan=6 ><center><input type='hidden' name='i' value='$i'><h2 style=\"border-width:1; border-style:groove; background-color:#11E3EB; \">AJOUT D'UN NOUVEL ELEMENT</h2></center></td></tr>";
    $form_ins .= "<tr>
				<td><input type='date' id='date' name='date' value='" . date('Y-m-d') . "' size=4 maxlength=10 title='jj/mm/aaaa' required autofocus /></td>
				<td><input type='time' id='heure' name='heure' value='00:00:00' size=1 maxlength=8 pattern='^(?:[01][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$' title='hh:mm:ss' /></td>
				<td><select name='id_matiere' >" . optionsReferentiel($lien, 'matiere') . "</select></td>
				<td><select name='id_type_note'>" . optionsReferentiel($lien, 'type_note', 1, ' WHERE id>0') . "</select></td>
				<td><input type='text' name='duree' value='0.0' size=1 maxlength=3 title='Durée' /></td>
				<td><input type='text' name='coeff' value='0.0' size=1 maxlength=3 title='Coefficient' /></td>
				</tr>";
/*
$controle_forms=" onClick=\"if(form_calendrier.id_matiere.value==0
|| form_calendrier.id_type_note.value==0
) {alert('Les champs matiere et type_note doivent etre remplis obligatoirement'); return false;} \" ";
 */
    $form_ins .= "<tr>
				<td colspan=3><center><input type='submit' value='INS&Eacute;RER / METTRE A JOUR' name='inserer' class='submit' $controle_forms ></center></td>
				<td colspan=3><center><input type='button' value='ANNULER & QUITTER' class='submit' onClick=window.location='" . generer_url_ecrire('calendriers') . "'></center></td>
				</tr>";

    $form_ins .= "</tr></table></form>";
    if ($texte1 != '' || $texte2 != '' || $texte3 != '') {
        echo debut_boite_alerte();
        echo $texte1, $texte2, $texte3;
        echo fin_boite_alerte();
    }
    echo $form_ins;
    echo fin_cadre_relief();
}

function exec_calendriers()
{
    $exec = _request('exec');
    $titre = "exec_$exec";
    $commencer_page = charger_fonction('commencer_page', 'inc');
    echo $commencer_page($titre);
    $lien = lien();

    foreach ($_REQUEST as $key => $val) {
        $$key = mysqli_real_escape_string($lien, trim(addslashes($val)));
    }

    if ($annee == '') {
        $annee = recupAnnee($lien); //$annee = date("Y");
    }
    if ($id_type_session == '') {
        $id_type_session = recupSession($lien);
    }

    //    $annee=2018;
    /*
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
     */

    echo debut_cadre_trait_couleur('', '', '', "Calendrier de déroulement ", "", "", false);

    //Verification du calendrier de l'annee en cours. Sinon copier pour l'annee precedente
    $sql = "SELECT * FROM bg_calendrier WHERE annee=$annee ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    if (mysqli_num_rows($result) == 0) {
        $sql2 = "INSERT INTO bg_calendrier (`id_serie`, `id_matiere`, `id_type_note`, `annee`, `date`, `heure`, `duree`, `coeff`)
				(SELECT `id_serie`, `id_matiere`, `id_type_note`, $annee, CURDATE(), `heure`, `duree`, `coeff` FROM bg_calendrier WHERE annee=" . ($annee - 1) . ')';
        bg_query($lien, $sql2, __FILE__, __LINE__);
    }

    if (!isset($_REQUEST['id_serie']) && (!isset($_REQUEST['etape']))) {

        $sql = "SELECT id, serie FROM bg_ref_serie ";
        $result = bg_query($lien, $sql, __FILE__, __LINE__);

        $liste = "<form action='" . generer_url_ecrire('calendriers') . "' method='POST' class='forml spip_xx-small' name='form_series'>";
        $liste .= "<center><select name='id_serie' onchange='document.forms.form_series.submit()'><option>-=[Série]=-</option>";
        while ($row = mysqli_fetch_array($result)) {
            $id = $row['id'];
            $serie = $row['serie'];
            $liste .= "<option value='$id'>";
            $liste .= strtoupper($serie);
            $liste .= "</option>";
        }
        $liste .= "</select></center>";
        $liste .= "</form>";
        echo $liste;
    }

    if (isset($_REQUEST['id_serie'])) {
        echo "<div class='onglets_simple clearfix'>
			<ul>
				<li title='Définition des mati&egrave;res, coefficients par série'><a href='./?exec=calendriers&id_serie=$id_serie&annee=$annee' class='ajax'>Insérer</a></li>
				<li title='Retour &agrave; la liste des séries'><a href='./?exec=calendriers' class='ajax'>Séries</a></li>
				</ul>
			</div>";
        if (isset($_GET['id_serie'])) {
            InsererCalendrier($lien, $id_serie, $annee);
        }
    }

    echo fin_gauche(), fin_cadre_trait_couleur(), fin_page();

}

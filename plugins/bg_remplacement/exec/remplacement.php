<?php
if (!defined('_ECRIRE_INC_VERSION')) {
    return;
}

include_spip('inc/presentation'); // alleger les inclusions avec un inc/presentation_mini
include '../plugins/fonctions/generales.php';

setlocale(LC_TIME, "fr_FR");

function exec_remplacement()
{
    $exec = _request('exec');
    $titre = "exec_$exec";
    $commencer_page = charger_fonction('commencer_page', 'inc');
    echo $commencer_page($titre);
    $lien = lien();
    $statut = getStatutUtilisateur();

    foreach ($_REQUEST as $key => $val) {
        $$key = mysqli_real_escape_string($lien, trim($val));
    }

    if ($annee == '') {
        $annee = date("Y");
    }

    $tab_auteur = $GLOBALS["auteur_session"];
    $login = $tab_auteur['login'];

    if (isAutorise(array('Admin', 'Informaticien', 'Encadrant'))) {
        echo debut_grand_cadre();
        echo debut_cadre_relief("../images/logo.png", '', '', "INSERTION DES NUMEROS DE TABLE POUR ENREGISTRER LES CANDIDATS DE LA SESSION DE REMPLACEMENT", "", "");
        /*
        echo "<pre>";
        print_r($_POST);
        echo "</pre>";
         */
        ///////////////////////////////////////////////////////////////////////////////////////////
        if (isset($_REQUEST['valider'])) {
            $tabNum_table = $_POST['tabNum_table'];
            $i = 0;
            foreach ($tabNum_table as $num_table) {
                $sql = "INSERT INTO bg_candidats (`annee`, `num_dossier`, `serie`, `annee_bac1`, `jury_bac1`, `num_table_bac1`, `nom`, `prenoms`, `sexe`, `ddn`, `ldn`, `pdn`, `nationalite`, `telephone`, `lv1`, `lv2`, `efa`, `efb`, `eps`, `etat_physique`, `atelier1`, `atelier2`, `atelier3`, `type_session`, `etablissement`, `centre`, `nom_photo`, `login`, `maj`, `motif`, `additive`)
					SELECT `annee`, `num_dossier`, `serie`, `annee_bac1`, `jury_bac1`, `num_table_bac1`, `nom`, `prenoms`, `sexe`, `ddn`, `ldn`, `pdn`, `nationalite`, `telephone`, `lv1`, `lv2`, `efa`, `efb`, `eps`, `etat_physique`, `atelier1`, `atelier2`, `atelier3`, 2, `etablissement`, `centre`, `nom_photo`, `login`, `maj`, `motif`, `additive`
						FROM bg_candidats WHERE num_table='$num_table'";
                $result = bg_query($lien, $sql, __FILE__, __LINE__);
                $i++;
            }
            echo debut_boite_alerte(), "$i Candidats enregistrés", fin_boite_alerte();
        }

        if (!isset($_REQUEST['continuer']) || isset($_REQUEST['valider'])) {
            unset($tabNum_table);
            $form = "<form name='form_numeros' method='POST' action=''>";
            $form .= "<table>";
            $form .= "<tr><td>Saisie des numéros de table (Un numéro par ligne) </td></tr>
            <tr> <td> <textarea style='color:#555;float: right; width: 100%; min-height: 120px;
            outline: none; resize: none;border-radius: 5px;border: 1px solid grey;' name='numeros'></textarea></td></tr>";
            $form .= "<tr><td colspan=2><center><input type='submit' name='continuer' value='CONTINUER'></center></td></tr>";
            $form .= "</table></form>";
            echo $form;
        }

        if (isset($_REQUEST['continuer'])) {
            $tabNumeros = explode('\r\n', $numeros);
            $form = "<form name='form_noms' method='POST' action=''>";
            $form .= "<table>";
            foreach ($tabNumeros as $num_table) {
                $sql = "SELECT can.*, eta.etablissement FROM bg_candidats can, bg_ref_etablissement eta
						WHERE can.etablissement=eta.id AND can.num_table='$num_table' LIMIT 0,1";
                $result = bg_query($lien, $sql, __FILE__, __LINE__);
                $tab = array('num_dossier', 'serie', 'annee_bac1', 'jury_bac1', 'num_table_bac1', 'nom', 'prenoms',
                    'sexe', 'ddn', 'ldn', 'pdn', 'nationalite', 'telephone', 'lv1', 'lv2', 'efa', 'efb',
                    'eps', 'etat_physique', 'atelier1', 'atelier2', 'atelier3', 'type_session', 'etablissement',
                    'centre', 'nom_photo', 'login', 'maj');
                if (mysqli_num_rows($result) > 0) {
                    $row = mysqli_fetch_array($result);
                    foreach ($tab as $val) {
                        $$val = $row[$val];
                    }

                    $form .= "<tr><td>$num_table</td><td>$nom $prenoms </td><td>$etablissement</td><td><input type=checkbox name='tabNum_table[]' value='$num_table' checked></td></tr>";
                }
            }
            $form .= "<tr><td colspan=4><center><input type='submit' name='valider' value='ENREGISTRER'></center></td></tr>";
            $form .= "</table></form>";
            echo $form;
        }
        echo fin_cadre_relief();

    }
    echo fin_cadre_trait_couleur();
    echo fin_grand_cadre(), fin_gauche(), fin_page();
}

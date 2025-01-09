<?php
if (!defined('_ECRIRE_INC_VERSION')) {
    return;
}

include_spip('inc/presentation'); // alleger les inclusions avec un inc/presentation_mini
include '../plugins/fonctions/generales.php';

setlocale(LC_TIME, "fr_FR");
define('MAX_LIGNES', 200);

// Mise a jour du 21/11/2022
//Affichage de l'inspection avec l'id comme paramètre
function selectInspection($lien, $id_inspection)
{
    $sql = "SELECT inspection FROM bg_ref_inspection WHERE id='$id_inspection' ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $row = mysqli_fetch_array($result);
    return $row['inspection'];
}

function afficherIdentifiants($lien)
{
    echo debut_cadre_enfonce('', true, '', "LISTE DES CODES D'ACCES ");
    $sql = "SELECT * FROM bg_code_resultat,bg_ref_region_division
		WHERE id_region_division=id ORDER BY region_division ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);

    $tableau = "<table class='spip liste'><thead>";
    $tableau .= "<th>Region</th><th>Code de Résultat</th>";
    $tableau .= "</thead><tbody>";
    $tab = array('id', 'region_division', 'code_resultat', 'id_region_division');
    while ($row = mysqli_fetch_array($result)) {
        foreach ($tab as $var) {
            $$var = $row[$var];
        }
        $tableau .= "<tr><td><a href=" . generer_url_ecrire('configuration') . "&etape=resultat&id_region_division=$id_region_division>$region_division</a></td><td>$code_resultat</td>
       </tr>";

    }
    $tableau .= "</tbody><tfoot><tr></tr></tfoot></table>";
    echo $tableau;
    echo fin_cadre_enfonce();
}
function afficherSeries($lien)
{
    echo debut_cadre_enfonce('', true, '', "Coéficients par séries ");

    $sql = "SELECT * FROM bg_ref_serie ORDER BY serie ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $tableau = "<table class='spip liste'><thead>";
    $tableau .= "<th>Série</th><th>Coéfficient Apte</th><th>Coéfficient Inapte</th>";
    $tableau .= "</thead><tbody>";
    $tab = array('id', 'serie', 'moyenne_apte', 'moyenne_inapte');
    while ($row = mysqli_fetch_array($result)) {
        foreach ($tab as $var) {
            $$var = $row[$var];
        }
        $tableau .= "<tr><td><a href=" . generer_url_ecrire('configuration') . "&etape=calcul&etape=maj&id=$id>$serie</a></td>
        <td>$moyenne_apte</td><td>$moyenne_inapte</td>
       </tr>";

    }
    $tableau .= "</tbody><tfoot><tr></tr></tfoot></table>";
    echo $tableau;
    echo fin_cadre_enfonce();
}

function afficherEtapeExamen($lien)
{
    echo debut_cadre_enfonce('', true, '', "Liste des étapes ");
    $sql = "SELECT * FROM bg_chronogramme ORDER BY date_debut ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);

    $tableau = "<table class='spip liste'><thead>";
    $tableau .= "<th>ID</th><th>Etape</th><th>Date début</th><th>Date Fin</th>";
    $tableau .= "</thead><tbody>";
    $tab = array('id', 'libelle_etape', 'date_debut', 'date_fin');
    while ($row = mysqli_fetch_array($result)) {
        foreach ($tab as $var) {
            $$var = $row[$var];
        }
        $tableau .= "<tr><td><a href=" . generer_url_ecrire('configuration') . "&etape=chronogramme&id=$id>$id</a></td>
        <td>$libelle_etape</td><td>$date_debut</td><td>$date_fin</td>
       </tr>";

    }
    $tableau .= "</tbody><tfoot><tr></tr></tfoot></table>";
    echo $tableau;
    echo fin_cadre_enfonce();
}

function insererEtapeExamen($lien)
{
    echo '<script type="text/javascript">',

        '</script>'
    ;

    $form = debut_cadre_enfonce('', '', '', "Etapes de l'examen");
    $form .= "<form action='" . generer_url_ecrire('configuration') . "&etape=chronogramme&tache=inserer' method='POST' name='form_etape_examen' id='form_etape_examen'>";
    $js_reset = "onClick=\"document.getElementById('form_etape_examen').reset();\"; ";
    $form .= " <table>";
    $form .= " <tr><th></th><th>Libellé de l'étape</th><th>Date début</th><th>Date fin</th><th> </th></tr>";
    $form .= "<tr>
        <td style='width:5%'></td>
        <td style='text-align: left;'><input type='text' name='libelle_etape' value='$libelle_etape' $majuscule /></td>
        <td style='text-align: left;'><input type='date' name='date_debut' value='$date_debut'/></td>
        <td style='text-align: left;'><input type='date' name='date_fin'  value='$date_fin'/></td>
        <td><input type='submit' value='Valider'  name='chronogramme'
        onClick=\"window.location.reload();\"
        $js_reset /></td>
        </tr>";

    $form .= "</table> ";
    $form .= "</form> ";
    $form .= fin_cadre_enfonce();

    echo $form;
}

function modifierEtapeExamen($lien, $id)
{
    $sql_conf_modification_chronogramme = "SELECT * FROM bg_chronogramme WHERE id=$id";
    //$sql_conf_modification_chronogramme = "UPDATE bg_chronogramme SET libelle_etape=$libelle_etape, date_debut=$date_debut, date_fin=$date_fin WHERE id=$id";
    $result = bg_query($lien, $sql_conf_modification_chronogramme, __FILE__, __LINE__);
    $tabChamps = mysqli_fetch_fields($result);
    $nbre_champs = count($tabChamps);
    //$form_ins .= "<TR>";

    $form = debut_cadre_enfonce('', '', '', "Modification des étapes de l'examen");
    $form .= "<form action='' method='POST' name='form_etape_examen' id='form_etape_examen'>";
    $js_reset = "onClick=\"document.getElementById('form_etape_examen').reset();\"; ";
    $form .= " <table>";
    $form .= "<tr>";

    $row = mysqli_fetch_array($result);
    foreach ($tabChamps as $champs) {
        $champ = $champs->name;
        if ($champ != 'id') {
            $var = $row[$champ];
            $form .= "<td><label>" . strtoupper(str_replace('_', ' ', $champ)) . "</label>";
            if (substr($champ, 0, strlen('date')) === 'date') {
                $form .= "<input type='date' name='$champ' value=\"$var\"></td>";
            } else {

                $form .= "<input type='text' id='$champ' name='$champ' value=\"$var\" size=$size /></td>";
                /*  $form = debut_cadre_enfonce('', '', '', "Modification des étapes de l'examen");
            $form .= "<form action='' method='POST' name='form_etape_examen' id='form_etape_examen'>";
            $js_reset = "onClick=\"document.getElementById('form_etape_examen').reset();\"; ";
            $form .= " <table>";
            $form .= " <tr><th></th><th>Libellé de l'étape</th><th>Date début</th><th>Date fin</th><th> </th></tr>";
            $form .= "<tr>
            <td style='width:5%'></td>
            <td style='text-align: left;'><input type='text' name='$champ' value='$var' $majuscule /></td>
            <td style='text-align: left;'><input type='date' name='$champ' value='$var'/></td>
            <td style='text-align: left;'><input type='date' name='$champ'  value='$var'/></td>
            <td><input type='submit' value='Valider'  name='chronogramme'
            onClick=\"window.location='". generer_url_ecrire('configuration'). "&etape=chronogramme';\"
            $js_reset /></td>
            </tr>";

            $form .= "</table> ";
            $form .= "</form> ";
            $form .= fin_cadre_enfonce();

            echo $form; */
            }
        }
    }
    if (isset($_GET['idc'])) {$idc = $_GET['idc'];
        $complement = "&idc=$idc";}
    $form .= "<td><input type='hidden' name='modification' value='$id'> </td>";
    $form .= "</TR></TABLE>";
    $form .= "<table>";
    $form .= "<td><input type='submit' value='MODIFIER " . strtoupper(str_replace('_', ' ', $referentiel)) . "' name='maj'></td>";
    $form .= "<td><input type='button' class='fondo' value='SUPPRIMER' onclick=\"if(confirm('Souhaitez-vous vraiment supprimer cette étape?')) window.location='" . generer_url_ecrire('configuration') . "&etape=chronogramme&tache=" . "supprimer" . "&id=$id';\"></td>";
    $form .= "<td><input type='button' class='fondo' value='ANNULER' onClick=window.location='" . generer_url_ecrire('configuration') . "&op=$referentiel$complement'></td>";
    $form .= "</tr></form></table>";
    echo $form;
    echo fin_cadre_couleur();
}

function exec_configuration()
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

            case 'Encadrant':
                $isEncadrant = true;
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
    if ($id_type_session == null) {
        $id_type_session = recupSession($lien);
    }
    if ($statu_modif_note == '') {
        $statu_modif_note = recupModifnote($lien);
    }

    if (isAutorise(array('Admin'))) {
        echo '<script type="text/javascript">',
        '$(document).ready(function() {',
        'var selectregion = document.getElementById ("id_region");',
        'var newOption = new Option ("NATIONAL", "-1");',
        'selectregion.options.add (newOption);',
        '});',
            '</script>';
        echo debut_grand_cadre();
        echo debut_gauche();
        echo debut_cadre_enfonce();
        echo "<h5>Configurations</h5>";
        echo fin_cadre_enfonce();
        echo debut_boite_info();

        echo "<p><a href='" . generer_url_ecrire('configuration') . "&etape=etude'>Configuration Etude de Dossiers </a></p>";
        echo "<p><a href='" . generer_url_ecrire('configuration') . "&etape=note'>Configuration des Notes </a></p>";
        echo "<p><a href='" . generer_url_ecrire('configuration') . "&etape=utilisateur'>Configuration des Utilisateurs </a></p>";
        echo "<p><a href='" . generer_url_ecrire('configuration') . "&etape=calcul'>Configuration des Calculs </a></p>";
        //  echo "<p><a href='" . generer_url_ecrire('configuration') . "&etape=resultat'>Configuration des Résultats </a></p>";
        echo "<p><a href='" . generer_url_ecrire('configuration') . "&etape=chronogramme&tache=inserer'>Configuration du Chronogramme </a></p>";
        echo "<p><a href='" . generer_url_ecrire('configuration') . "&etape=profile'>Configuration des Profiles  </a></p>";

        echo fin_boite_info();

        echo debut_boite_info(), "<p><a href='" . generer_url_ecrire('configuration') . "&action=logout&logout=prive' class='ajax'>Se déconnecter</a></p>", fin_boite_info();

        echo debut_droite();
        if (!isset($_REQUEST['etape'])) {
            echo debut_cadre_trait_couleur('', '', '', "GESTION DES CONFIGURATIONS", "", "", false);
            echo debut_boite_info(), "<b>Configuration Année Scolaire</b><br/>Réservé uniquement au super admin ,il enrégistre l'année en cours", fin_boite_info();
            echo debut_boite_info(), "<b>Configuration  du type de session </b><br/> Il enrégistre par défaut la session en cours ", fin_boite_info();
            echo debut_boite_info(), "<b>Configuration  Utilitaire</b><br/>Permet d'activer ou de désactiver lea suppression des  <b>Codes Etablissement</b>", fin_boite_info();
            echo debut_boite_info(), "<b>Configuration Modification de Notes</b><br/>Il permet de donner la possibilité de modifier les notes déja saisie lors de la saisie de notes", fin_boite_info();
            echo debut_boite_info(), "<b>Configuration du chronogramme des étapes de l'examen</b><br/>Il permet de paramétrer les dates de début et fin des differentes étapes de l'examen", fin_boite_info();

            echo fin_cadre_trait_couleur();
            echo fin_grand_cadre(), fin_gauche();
        }
        if (isset($_REQUEST['etape']) && ($_GET['etape'] == 'note')) {
            echo debut_cadre_trait_couleur('', '', '', "GESTION DES CONFIGURATIONS", "", "", false);

            $form = debut_cadre_enfonce('', '', '', "Année en Cours");
            $form .= "<form method='POST' name='form_an'>";
            $form .= " <table>";
            $form .= "<tr><td><center><select style='width:50%' name='annee' >" . optionsAnnee(2021, $annee) . "</select></center></td>
                <td><center><select style='width:50%'' name='id_type_session'>" . optionsReferentiel($lien, 'type_session', $id_type_session) . "</select></center></td>
                <td><input type='submit' value='Valider'  name='conf_an' /></td>
                </tr>";

            $form .= "</table> ";
            $form .= "</form> ";
            $form .= fin_cadre_enfonce();
            // $form .= "<p><center> Retour au Menu principal </center></p>";
            $form .= debut_cadre_couleur('', '', '', "Configuration des Activations de Utilitaires " . afficherUtilitaire($lien));
            $form .= "<form method='POST' name='form_uti'>";
            $form .= "<table>";
            $form .= "<tr ><td colspan=2 style='text-align: right;'><center>
            <select style='width:100%;' name='utilitaire' value=$utilitaire >

            <option value=''>-=[Utilitaire]=-</option>

            <option value=0 $selected>Activé</option>
            <option value=1 $selected>Désactivé</option>
            </select>
            </center></td>
                <td style='text-align: center;'><input type='submit' value='Valider' name='conf_uti' /></td></tr>";
            $form .= "</table>";
            $form .= "</form>";
            $form .= fin_cadre_enfonce();
            $form .= debut_cadre_enfonce('', '', '', "Modification de Notes " . afficherModifNote($lien));
            $form .= "<form method='POST' name='form_note'>";
            $form .= " <table>";
            $form .= "<tr><td style='text-align:right;'><center><select  style='width:100%' name='statu_modif_note' value=$statu_modif_note >
            <option value=''>-=[Note]=-</option>
            <option value=0>Ne pas modifier</option>
            <option value=1 >Modifier</option>
            </select></center></td>
                <td style='text-align: center;'><input type='submit' value='Valider' name='conf_note' /></td>  </tr>";
            $form .= "</table> ";
            $form .= "</form>";
            $form .= fin_cadre_couleur();
            echo $form;
            echo fin_cadre_trait_couleur();
            echo fin_grand_cadre(), fin_gauche();
        }
        if (isset($_REQUEST['etape']) && ($_GET['etape'] == 'utilisateur')) {
            echo debut_cadre_trait_couleur('', '', '', "GESTION DES CONFIGURATIONS", "", "", false);

            $form = debut_cadre_enfonce('', '', '', "Accès par un Etablissement" . afficherEtablissement($lien));
            $form .= "<form method='POST' name='form_an'>";
            $form .= " <table>";
            $form .= "<tr ><td colspan=2 style='text-align: right;'><center>
            <select style='width:100%;' name='etablissement' value=$etablissement >

            <option value=''>-=[Etablissement]=-</option>
            <option value=1 $selected>Activer</option>
            <option value=0 $selected>Désactiver</option>
            </select>
            </center></td>
                <td style='text-align: center;'><input type='submit' value='Valider' name='conf_eta' onClick=\"window.location='" . generer_url_ecrire('configuration') . "&etape=utilisateur';\" /></td></tr>";

            $form .= "</table> ";
            $form .= "</form> ";
            $form .= fin_cadre_enfonce();

            $form .= debut_cadre_couleur('', '', '', "Accès par une Inspection" . afficherInspection($lien));
            $form .= "<form method='POST' name='form_ins'>";
            $form .= " <table>";
            $form .= "<tr ><td colspan=2 style='text-align: right;'><center>
            <select style='width:100%;' name='inspection' value=$inspection >

            <option value=''>-=[Inspection]=-</option>

            <option value=1 $selected>Activer</option>
            <option value=0 $selected>Désactiver</option>
            </select>
            </center></td>
                <td style='text-align: center;'><input type='submit' value='Valider' name='conf_ins' onClick=\"window.location='" . generer_url_ecrire('configuration') . "&etape=utilisateur';\" /></td></tr>";

            $form .= "</table> ";
            $form .= "</form> ";
            $form .= fin_cadre_couleur();

            $form .= debut_cadre_enfonce('', '', '', "Accès par une DRE" . afficherEncadrant($lien));
            $form .= "<form method='POST' name='form_enc'>";
            $form .= "<table>";
            $form .= "<tr ><td colspan=2 style='text-align: right;'><center>
            <select style='width:100%;' name='encadrant' value=$encadrant >

            <option value=''>-=[Encadrant]=-</option>

            <option value=1 $selected>Activer</option>
            <option value=0 $selected>Désactiver</option>
            </select>
            </center></td>
                <td style='text-align: center;'><input type='submit' value='Valider' name='conf_enc' onClick=\"window.location='" . generer_url_ecrire('configuration') . "&etape=utilisateur';\"/></td></tr>";
            $form .= "</table>";
            $form .= "</form>";
            $form .= fin_cadre_enfonce();

            echo $form;
            echo fin_cadre_trait_couleur();
            echo fin_grand_cadre(), fin_gauche();
        }
        if (isset($_REQUEST['etape']) && ($_GET['etape'] == 'resultat')) {
            echo debut_cadre_trait_couleur('', '', '', "GESTION DES CONFIGURATIONS", "", "", false);

            $form .= debut_cadre_enfonce('', '', '', "Mot de passe des DRE");
            $form .= "<form method='POST' name='form_mdpdre'>";
            $form .= " <table>";
            $form .= "<tr> <td><center><select style='width:50%'' name='id_region_division' id='id_region_division'>" . optionsReferentiel($lien, 'region_division', $id_region_division) . "</select></center></td>

                <td><input type='submit' value='Générer'  name='conf_mdpdre'
                onClick=\"window.location='" . generer_url_ecrire('configuration') . "&etape=resultat';\"/></td>
                <td><input type='submit' value='Imprimer'  name='imp_mdpdre'
                onClick=\"window.location='" . generer_url_ecrire('configuration') . "&etape=resultat';\"/></td>
                <td><input type='submit' value='Supprimer'  name='sup_mdpdre'
                onClick=\"window.location='" . generer_url_ecrire('configuration') . "&etape=resultat';\"/></td>
                </tr>";

            $form .= "</table> ";
            $form .= "</form> ";
            $form .= fin_cadre_enfonce();
            echo $form;
            echo debut_cadre_couleur();

            afficherIdentifiants($lien);

            echo fin_cadre_enfonce();
            echo fin_cadre_trait_couleur();
            echo fin_grand_cadre(), fin_gauche();
        }
        if ((isset($_REQUEST['etape']) && ($_GET['etape'] == 'calcul')) || $_GET['etape'] == 'maj') {
            echo debut_cadre_trait_couleur('', '', '', "GESTION DES CONFIGURATIONS", "", "", false);
            if ($_GET['etape'] == 'maj') {
                $id_serie = $_GET['id'];

                $sql = "SELECT * FROM bg_ref_serie WHERE id=$id_serie ";
                $result = bg_query($lien, $sql, __FILE__, __LINE__);
                $tab = array('id');
                while ($row = mysqli_fetch_array($result)) {
                    foreach ($tab as $var) {
                        $$var = $row[$var];
                    }
                }
            }
            $form = debut_cadre_enfonce('', '', '', "Point des séries");
            $form .= "<form method='POST' name='form_an'>";
            $form .= " <table>";
            $form .= " <tr><th>Série</th><th></th><th>Coefficient Apte</th><th>Coefficient Inatpte</th><th> </th></tr>";
            $form .= "<tr>
                <td><center><select style='width:100%' name='id_serie'>" . optionsReferentiel($lien, 'serie', $id_serie) . "</select></center></td>
                <td style='width:5%'></td>
                <td style='text-align: left;'><input type='text' name='moyenne_apte' value='$moyenne_apte' /></td>
                <td style='text-align: left;'><input type='text' name='moyenne_inapte' value='$moyenne_inapte' /></td>";

            $form .= " <td><input type='submit' value='Valider'  name='conf_moyenne'
                        onClick=\"window.location='" . generer_url_ecrire('configuration') . "&etape=calcul';\"
                        /></td>
                        </tr>";

            $form .= "</table> ";
            $form .= "</form> ";
            $form .= fin_cadre_enfonce();

            echo $form;
            echo debut_cadre_couleur();

            afficherSeries($lien);

            echo fin_cadre_enfonce();
            echo fin_cadre_trait_couleur();
            echo fin_grand_cadre(), fin_gauche();
        }

        if (isset($_REQUEST['etape']) && ($_GET['etape'] == 'profile')) {
            echo debut_cadre_trait_couleur('', '', '', "GESTION DES CONFIGURATIONS", "", "", false);

            $form = debut_cadre_enfonce('', '', '', "Modification des droits");
            $form .= "<form method='POST' name='form_pro'>";
            $form .= " <table>";
            $form .= " <tr><th>Plugins</th><th>Encadrant</th><th>Dre</th><th>Inspection</th><th>Notes</th><th>Etablissement</th><th>Informaticen</th><th>Dexcc</th><th>Anonymat</th><th> </th></tr>";
            $form .= "<tr>
                <td><center><select style='width:100%' name='id_plugins' id='id_plugins'>" . optionsReferentiel($lien, 'plugins', $id_plugins) . "</select></center></td>

                <td style='text-align: left;'><input type='checkbox' name='encadrant' value='$encadrant' /></td>
                <td style='text-align: left;'><input type='checkbox' name='dre' value='$dre' /></td>
                <td style='text-align: left;'><input type='checkbox' name='inspection' value='$inspection' /></td>
                <td style='text-align: left;'><input type='checkbox' name='notes' value='$notes' /></td>
                <td style='text-align: left;'><input type='checkbox' name='etablissement' value='$etablissement' /></td>
                <td style='text-align: left;'><input type='checkbox' name='informaticien' value='$informaticien' /></td>
                <td style='text-align: left;'><input type='checkbox' name='dexcc' value='$dexcc' /></td>
                <td style='text-align: left;'><input type='checkbox' name='anonymat' value='$anonymat' /></td>

                <td><input type='submit' value='Valider'  name='conf_profile'
                onClick=\"window.location='" . generer_url_ecrire('configuration') . "&etape=profile';\"
                /></td>
                </tr>";

            $form .= "</table> ";
            $form .= "</form> ";
            $form .= fin_cadre_enfonce();

            echo $form;

            echo debut_cadre_enfonce('', true, '', "Plugins et leur profile ");
            $sql = "SELECT * , id as id_plugins FROM bg_ref_plugins ORDER BY plugins ";
            $result = bg_query($lien, $sql, __FILE__, __LINE__);

            $tableau = "<table class='spip liste'><thead>";
            $tableau .= "<th>Plug-ins</th><th align='center'>Encadrant</th><th align='center'>Dre</th>
                              <th align='center'>Inspection</th><th align='center'>Notes</th><th align='center'>Etablissement</th>
                              <th align='center'>Informaticien</th><th align='center'>DExCC</th><th align='center'>Anonymat</th>";
            $tableau .= "</thead><tbody>";
            $tab = array('id_plugins', 'plugins', 'encadrant', 'dre', 'inspection', 'notes', 'etablissement', 'informaticien', 'dexcc', 'anonymat');
            while ($row = mysqli_fetch_array($result)) {
                foreach ($tab as $var) {
                    $$var = $row[$var];
                }
                $tableau .= "<tr><td><a href=" . generer_url_ecrire('configuration') . "&etape=profile&id_plugins=$id_plugins>$plugins</a></td>
                <td  class='center' style='width:8%' >$encadrant</td><td  class='center' style='width:8%'>$dre</td>
                <td  class='center' style='width:8%'>$inspection</td><td  class='center' style='width:8%'>$notes</td>
                <td  class='center' style='width:8%' >$etablissement</td><td  class='center' style='width:8%'>$informaticien</td>
                <td  class='center' style='width:8%'>$dexcc</td><td  class='center' style='width:8%'>$anonymat</td>
               </tr>";

            }
            $tableau .= "</tbody><tfoot><tr></tr></tfoot></table>";
            echo $tableau;

            echo fin_cadre_enfonce();
            echo fin_cadre_trait_couleur();
            echo fin_grand_cadre(), fin_gauche();
        }
        if (isset($_REQUEST['etape']) && ($_GET['etape'] == 'chronogramme')) {

            echo debut_cadre_trait_couleur('', '', '', "GESTION DES CONFIGURATIONS", "", "", false);
            if ($_GET['etape'] == 'chronogramme' && !isset($_GET['id'])) {
                $majuscule = " OnkeyUp=\"javascript:this.value=this.value.toUpperCase();\" ";
                insererEtapeExamen($lien);
            } else {
                $majuscule = '';
            }
            if (isset($_GET['id'])) {
                modifierEtapeExamen($lien, $id);
                if (isset($_REQUEST['maj'])) {
                    $sql_conf_modification_chronogramme = "UPDATE bg_chronogramme SET libelle_etape='$libelle_etape', date_debut='$date_debut', date_fin='$date_fin' WHERE id=$id";
                    $result = bg_query($lien, $sql_conf_modification_chronogramme, __FILE__, __LINE__);
                    $tabChamps = mysqli_fetch_fields($result);
                }
                if ($_REQUEST['tache'] == 'supprimer') {
                    $id = $_REQUEST['id'];
                    $sql_sup = "DELETE FROM bg_chronogramme WHERE id=$id";
                    $result = bg_query($lien, $sql_sup, __FILE__, __LINE__);
                    $tabChamps = mysqli_fetch_fields($result);
                }

            }

            echo debut_cadre_couleur();
            afficherEtapeExamen($lien);

            echo fin_cadre_enfonce();
            echo fin_cadre_trait_couleur();
            echo fin_grand_cadre(), fin_gauche();

        }

        if (isset($_REQUEST['etape']) && ($_GET['etape'] == 'etude')) {

            echo debut_cadre_trait_couleur('', '', '', "GESTION DES CONFIGURATIONS", "", "", false);
            $form .= debut_cadre_couleur('', '', '', "Configuration date d'Etude de Dossier ");

            $form .= "<form method='POST' name='form_etude'>";
            $form .= " <table>";
            $form .= "<tr>

                <td><center><input type='date' value='date_liste_additive'  name='date_liste_additive' id='date_liste_additive' pattern='\d{4}-\d{2}-\d{2}' /></center></td>

                 <td><input type='submit' value='Valider'  name='conf_etu' /></td>
                </tr>";

            $form .= "</table> ";
            $form .= "</form> ";
            $form .= fin_cadre_enfonce();
            $form .= debut_cadre_couleur('', '', '', "");
            $form .= " <h4>\n<center>La Date limite pour l'Entrée en liste additive " . afficherAdditiveDate($lien) . " </center>\n</h4>";
            $form .= fin_cadre_enfonce();
            $form .= debut_cadre_couleur('', '', '', "Configuration date de limite d'age ");

            $form .= "<form method='POST' name='limit_age'>";
            $form .= " <table>";
            $form .= "<tr>

                <td><center><input type='date' value='date_age_limite'  name='date_age_limite' id='date_age_limite' pattern='\d{4}-\d{2}-\d{2}' /></center></td>

                 <td><input type='submit' value='Valider'  name='conf_age_limit' /></td>
                </tr>";

            $form .= "</table> ";
            $form .= "</form> ";
            $form .= fin_cadre_enfonce();
            $form .= debut_cadre_couleur('', '', '', "");
            $form .= " <h4>\n<center>La Date limite pour l'age limite " . afficherAgeLimiteDate($lien) . " </center>\n</h4>";
            $form .= fin_cadre_enfonce();

            echo $form;

            // echo fin_cadre_enfonce();
            echo fin_cadre_trait_couleur();
            echo fin_grand_cadre(), fin_gauche();

        }

        echo fin_page();

        if (isset($_POST['conf_note'])) {
            $sql_conf_note = "UPDATE bg_configuration SET statu_modif_note=$statu_modif_note";
            bg_query($lien, $sql_conf_note, __FILE__, __LINE__);
        }
        if (isset($_POST['conf_an'])) {
            $sql_conf_an = "UPDATE bg_configuration SET annee='$annee',id_type_session=$id_type_session";
            bg_query($lien, $sql_conf_an, __FILE__, __LINE__);
        }
        if (isset($_POST['conf_etu'])) {
            $sql_conf_etu = "UPDATE bg_configuration SET date_liste_additive='$date_liste_additive'";
            bg_query($lien, $sql_conf_etu, __FILE__, __LINE__);
        }
        if (isset($_POST['conf_age_limit'])) {
            $sql_conf_age_limit = "UPDATE bg_configuration SET date_age_limite='$date_age_limite'";
            bg_query($lien, $sql_conf_age_limit, __FILE__, __LINE__);
        }
        if (isset($_POST['conf_profile'])) {
            // <td  class='center' style='width:8%' >$encadrant</td><td  class='center' style='width:8%'>$dre</td>
            // <td  class='center' style='width:8%'>$inspection</td><td  class='center' style='width:8%'>$notes</td>
            // <td  class='center' style='width:8%' >$etablissement</td><td  class='center' style='width:8%'>$informaticien</td>
            // <td  class='center' style='width:8%'>$dexcc</td><td  class='center' style='width:8%'>$anonymat</td>
            var_dump($_POST['encadrant']);
            if (!empty($_POST['encadrant'])) {
                $sql = "UPDATE bg_ref_plugins SET encadrant=1 WHERE id=$id_plugins";
                bg_query($lien, $sql, __FILE__, __LINE__);

            }

        }

        if (isset($_POST['conf_uti'])) {
            $sql_conf_mdp = "UPDATE bg_configuration SET utilitaire=$utilitaire";
            bg_query($lien, $sql_conf_mdp, __FILE__, __LINE__);
        }
        if (isset($_POST['conf_eta'])) {
            $sql_conf_eta = "UPDATE bg_configuration SET etablissement=$etablissement";
            bg_query($lien, $sql_conf_eta, __FILE__, __LINE__);
        }
        if (isset($_POST['conf_ins'])) {
            $sql_conf_ins = "UPDATE bg_configuration SET inspection=$inspection";
            bg_query($lien, $sql_conf_ins, __FILE__, __LINE__);
        }
        if (isset($_POST['conf_enc'])) {
            $sql_conf_enc = "UPDATE bg_configuration SET encadrant=$encadrant";
            bg_query($lien, $sql_conf_enc, __FILE__, __LINE__);
        }
        if (isset($_POST['conf_moyenne'])) {
            $sql_conf_mdp = "UPDATE bg_ref_serie SET moyenne_apte=$moyenne_apte, moyenne_inapte=$moyenne_inapte WHERE id=$id_serie";
            bg_query($lien, $sql_conf_mdp, __FILE__, __LINE__);
        }
        if (isset($_POST['conf_mdpdre'])) {
            $tAlphabet = range('a', 'z');
            $tVoyelles = array('a', 'e', 'i', 'o', 'u', 'y');
            $tConsonnes = array_diff($tAlphabet, $tVoyelles);
            $tSql[] = "UPDATE bg_code_resultat SET code_resultat=concat(ELT(1+floor(RAND()*" . (count($tConsonnes) - 1) . "),'" . implode("','", $tConsonnes) . "'),
					ELT(1+floor(RAND()*" . (count($tVoyelles) - 1) . "),'" . implode("','", $tVoyelles) . "'),
					ELT(1+floor(RAND()*" . (count($tConsonnes) - 1) . "),'" . implode("','", $tConsonnes) . "'),
					ELT(1+floor(RAND()*" . (count($tVoyelles) - 1) . "),'" . implode("','", $tVoyelles) . "'),
					ELT(1+floor(RAND()*" . (count($tConsonnes) - 1) . "),'" . implode("','", $tConsonnes) . "'),
					ELT(1+floor(RAND()*" . (count($tVoyelles) - 1) . "),'" . implode("','", $tVoyelles) . "'),
					ELT(1+floor(RAND()*" . (count($tConsonnes) - 1) . "),'" . implode("','", $tConsonnes) . "'),
					ELT(1+floor(RAND()*" . (count($tVoyelles) - 1) . "),'" . implode("','", $tVoyelles) . "'))
                    WHERE code_resultat=''  ";

            foreach ($tSql as $sql) {
                bg_query($lien, $sql, __FILE__, __LINE__);
            }
            echo ' <script type="text/javascript">',

            'window.open("./?exec=configuration&etape=resultat")',

                '</script>';
        }
        if (isset($_POST['sup_mdpdre'])) {

            $tSql[] = "UPDATE bg_code_resultat SET code_resultat=''
            WHERE code_resultat!='' ";

            foreach ($tSql as $sql) {
                bg_query($lien, $sql, __FILE__, __LINE__);
            }
            echo ' <script type="text/javascript">',

            'window.open("./?exec=configuration&etape=resultat")',

                '</script>';
        }
        if (isset($_POST['chronogramme'])) {
            $sql_conf_chronogramme = "INSERT INTO bg_chronogramme (`libelle_etape`, `date_debut`, `date_fin`)
            VALUES ('$libelle_etape','$date_debut','$date_fin') ";
            $result = bg_query($lien, $sql_conf_chronogramme, __FILE__, __LINE__);
            $tabChamps = mysqli_fetch_fields($result);

        }
    }
}

<?php
if (!defined('_ECRIRE_INC_VERSION')) {
    return;
}

include_spip('inc/presentation'); // alleger les inclusions avec un inc/presentation_mini
include '../plugins/fonctions/generales.php';

setlocale(LC_TIME, "fr_FR");
define('MAX_LIGNES', 200);

function afficherRepechage($lien)
{

    echo debut_cadre_enfonce('', true, '', "Moyenne Retenue : (" . recupRepechage($lien) . ")");
    $sql = "SELECT * FROM bg_ref_serie ORDER BY serie ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);

    $tableau = "<table class='spip liste'><thead>";
    $tableau .= "<th>Série</th><th>Point Total Apte</th><th>Point Total Inapte</th>";
    $tableau .= "</thead><tbody>";
    $tab = array('id', 'serie', 'moyenne_apte', 'moyenne_inapte');
    while ($row = mysqli_fetch_array($result)) {
        foreach ($tab as $var) {
            $$var = $row[$var];
        }

        $tableau .= "<tr><td><a href=" . generer_url_ecrire('repechage') . "&tache=param&id=$id_serie>$serie</a></td>
        <td>" . ($moyenne_apte * 9) . "</td> <td>" . ($moyenne_inapte * 9) . "</td>
       </tr>";

    }
    $tableau .= "</tbody><tfoot><tr></tr></tfoot></table>";
    echo $tableau;
    echo fin_cadre_enfonce();
}
function exec_repechage()
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
        $annee = recupAnnee($lien);
    }
    if ($id_type_session == '') {
        $id_type_session = recupSession($lien);
    }
    if (isAutorise(array('Admin', 'Encadrant'))) {

        if ((!isset($_REQUEST['etape']))) {
            echo debut_cadre_trait_couleur('', '', '', "GESTION DES REPECHAGES", "", "", false);

            echo debut_gauche();

            if ($statut == 'Admin') {
                echo debut_boite_info();
                echo "<p>Gestion des Contoles</p>";
                echo "<p><a href='" . generer_url_ecrire('repechage') . "&tache=param'>Paramètrage</a></p>";
                echo "<p><a href='" . generer_url_ecrire('repechage') . "&tache=liste'>Liste des Candidats à Repécher</a></p>";
                echo "<p><a href='" . generer_url_ecrire('repechage') . "&tache=coefficient'>Repéchage</a></p>";
                echo fin_boite_info();

            }

            echo debut_boite_info();
            echo "<p><a href='" . generer_url_ecrire('repechage') . "' class='ajax'>Retour au Menu Principale</a></p>";

            //echo "<form method='POST'><input name='detruire_session' type='submit' value='Changer de session' /></form>";
            echo fin_boite_info();

            //  }
            echo debut_droite();

            echo debut_cadre_trait_couleur('', '', '', "REPECHAGES", "", "", false);

            if (isset($_REQUEST['tache']) && ($_REQUEST['tache'] == 'param')) {
                $form = debut_cadre_enfonce('', '', '', "Moyenne Retenue pour le repéchage");
                $form .= "<form method='POST' name='form_moy'>";
                $form .= "<table>";
                $form .= "<tr >
                <td>Moyenne Retenue</td> <td> " . recupRepechage($lien) . "</td>
                <td colspan=2 style='text-align: right;'><center>
            <select style='width:50%;' name='repechage' value=$repechage >

            <option value=''>-=[Moyenne]=-</option>

            <option value=9.00 >9.00</option>
            <option value=8.75 >8.75</option>
            <option value=8.50 >8.50</option>
            <option value=8.25 >8.25</option>
            <option value=8.00 >8.00</option>
            <option value=7.75 >7.75</option>
            <option value=7.50 >7.50</option>
            <option value=7.25 >7.25</option>
            <option value=7.00 >7.00</option>
            <option value=6.75 >6.75</option>
            <option value=6.50 >6.50</option>
            <option value=6.25 >6.25</option>
            <option value=6.00 >6.00</option>

            </select>
            </center></td>
                <td style='text-align: center;'><input type='submit' value='Valider' name='moyenne_rep' onClick=\"window.location='" . generer_url_ecrire('repechage') . "&tache=param';\"/></td></tr>";
                $form .= "</table>";
                $form .= "</form>";
                $form .= fin_cadre_enfonce();

                echo $form;
                echo debut_cadre_couleur();

                afficherRepechage($lien);

                echo fin_cadre_enfonce();

            }
            if (isset($_POST['moyenne_rep'])) {
                $sql_moyenne_rep = "UPDATE bg_configuration SET repechage=$repechage";
                bg_query($lien, $sql_moyenne_rep, __FILE__, __LINE__);
                echo ' <script type="text/javascript">',
                'window.location=' . generer_url_ecrire('repechage') . '&tache=param',
                    '</script>';
            }

            if (isset($_REQUEST['tache']) && ($_REQUEST['tache'] == 'coefficient')) {

                echo "mettre ici le code du coefficient 0";

            }
            if (isset($_REQUEST['tache']) && ($_REQUEST['tache'] == 'liste')) {
                echo "<div class='onglets_simple clearfix'>
                <ul>";
                echo "<li><a href='./?exec=repechage&tache=listes' class='ajax'>ALL</a></li>";
                $sqlr = "SELECT * FROM bg_ref_region_division  ORDER BY id ";

                $resultr = bg_query($lien, $sqlr, __FILE__, __LINE__);
                while ($rowr = mysqli_fetch_array($resultr)) {
                    $id_inspection = $rowr['id'];
                    echo "<li><a href='./?exec=repechage&tache=liste&id_region_division=$id_region_division' class='ajax'>" . $rowr['region_division'] . "</a></li>";
                }
                echo "</ul>
                </div>";

            }
            if (isset($_REQUEST['tache']) && ($_REQUEST['tache'] == 'listes')) {
                $repechage = recupRepechage($lien);
                echo debut_cadre_enfonce('', true, '', "Liste des Candisats a repécher");
                $sql = "SELECT res.nt,ser.serie,res.moyenne,res.total,res.coeff FROM bg_resultats res,bg_ref_serie ser
                WHERE res.id_serie=ser.id AND res.annee=$annee  AND res.moyenne>=$repechage AND res.moyenne<9";
                $tableau = "<table class='spip liste'><thead>";
                $tableau .= "<th>Moyenne a repécher:  (" . recupRepechage($lien) . ")</th><th> Nombre de Repéché : (" . recupNbRepechage($lien) . " )</th>";
                $tableau .= "</thead>
                </table>";

                // $sql = "SELECT * FROM bg_resultats res
                // WHERE  res.moyenne>=$repechage AND res.moyenne<9";
                $result = bg_query($lien, $sql, __FILE__, __LINE__);

                $tableau .= "<table class='spip liste'><thead>";
                $tableau .= "<th>Numéro de Table</th><th>Série</th><th>Moyenne Obtenu</th><th>Point Obtenu</th><th>Point à Obtenir</th><th>Point à Rajouter</th>";
                $tableau .= "</thead><tbody>";
                $tab = array('nt', 'coeff', 'serie', 'total', 'moyenne');
                while ($row = mysqli_fetch_array($result)) {
                    foreach ($tab as $var) {
                        $$var = $row[$var];
                    }

                    $tableau .= "<tr><td><a href=" . generer_url_ecrire('repechage') . "&tache=param&id=$nt>$nt</a></td>
                    <td>$serie</td><td>$moyenne</td> <td>$total</td> <td><strong>" . (9 * $coeff) . "</strong></td><td><strong>" . ((9 * $coeff) - $total) . "</strong></td>
                   </tr>";

                }
                $tableau .= "</tbody><tfoot><tr></tr></tfoot></table>";
                echo $tableau;
                echo fin_cadre_enfonce();
            }

            echo fin_gauche();
            echo fin_gauche();

        }

        echo fin_cadre_trait_couleur();
        echo fin_grand_cadre(), fin_page();
    }

}

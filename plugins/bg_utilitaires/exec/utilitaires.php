<?php
if (!defined('_ECRIRE_INC_VERSION')) {
    return;
}

include_spip('inc/presentation'); // alleger les inclusions avec un inc/presentation_mini
include '../plugins/fonctions/generales.php';

setlocale(LC_TIME, "fr_FR");
function toLowerEtablissement($lien, $code_etablissement)
{
    $sql1 = "SELECT etablissement FROM bg_ref_etablissement WHERE code='$code_etablissement'";
    $result1 = bg_query($lien, $sql1, __FILE__, __LINE__);
    while ($row1 = mysqli_fetch_array($result1)) {
        $nom_etablissement = $row1['etablissement'];
        // $tCodeEtablissements[] = $row1['code_etablissement'];

    }

    $nom_etablissement = str_replace(' ', '', $nom_etablissement); // Supprimer les espaces et les remplacer par un vide
    $nom_etablissement = str_replace('-', '', $nom_etablissement); // Supprimer les espaces et les remplacer par un vide
    $nom_etablissement = preg_replace('/[^A-Za-z0-9\-]/', '', $nom_etablissement); // Retirer les caractères spéciaux et les accents
    $nom_etablissement = strtolower($nom_etablissement); // Retirer les caractères spéciaux et les accents

    if (strlen($nom_etablissement) > 20) {
        $nom_etablissement = substr($nom_etablissement, 0, 20);
    }

    return $nom_etablissement;
}

function genererLoginEtablissement($lien)
{

    //Rechercher les etablissements
    $sql1 = "SELECT etablissement,code FROM bg_ref_etablissement WHERE si_centre='non' AND login_eta=''  ";
    $result1 = bg_query($lien, $sql1, __FILE__, __LINE__);
    while ($row1 = mysqli_fetch_array($result1)) {
        // $tEtablissements[] = $row1['etablissement'];
        $tCodeEtablissements[] = $row1['code'];

    }

    //mettre le login sur l'etablissement
    foreach ($tCodeEtablissements as $code_etablissement) {

        $etablissement_lowed = toLowerEtablissement($lien, $code_etablissement);
        $sql2 = "UPDATE bg_ref_etablissement SET login_eta='$etablissement_lowed' WHERE si_centre='non' AND code='$code_etablissement' ";
        bg_query($lien, $sql2, __FILE__, __LINE__);

    }

}
function exec_utilitaires()
{
    $exec = _request('exec');
    $titre = "exec_$exec";
    $commencer_page = charger_fonction('commencer_page', 'inc');
    echo $commencer_page($titre);
    $lien = lien();

    $statut = getStatutUtilisateur();

    foreach ($_REQUEST as $key => $val) {
        $$key = mysqli_real_escape_string($lien, trim(addslashes($val)));
    }

    // foreach ($_REQUEST as $key => $val) {
    //     $$key = mysqli_real_escape_string($lien, trim($val));
    // }

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
        $annee = recupAnnee($lien); //$annee = date("Y");
    }
    if ($id_type_session == '') {
        $id_type_session = recupSession($lien);
    }
    $utilitaire = recupUtilitaire($lien);

    $tab_auteur = $GLOBALS["auteur_session"];
    $login = $tab_auteur['login'];

    if (isAutorise(array('Admin', 'Encadrant', 'DExCC'))) {
        echo debut_grand_cadre();
        echo debut_gauche();
        if ($statut == "DExCC") {
            echo debut_cadre_enfonce();
            echo "<ul><li><a href='./?exec=utilitaires&tache=releve_notes'>Réclamation</a></li></ul>";
            echo fin_cadre_enfonce();
        } else {
            echo debut_cadre_enfonce();
            if (($utilitaire == 0) || $isEncadrant) {
                echo "<ul><li><a href='./?exec=utilitaires&tache=crenewmdpeta'>Créer Login aux Etablissements</a></li> ";
                echo "<br/><li style='color:red;'><a style='color:red;'href='./?exec=utilitaires&tache=cremdpeta'>Créer MPD aux Etablissements</a></li>";
                echo "<br/><li style='color:red;'><a style='color:red;' href='./?exec=utilitaires&tache=supmdpeta'>Supprimer anciens MDP des Etablissements</a></li>";
            }
            echo "<br/><li><a href='./?exec=utilitaires&tache=impmdpeta'>Imprimer MPD des Etablissements</a></li></ul>";
            echo fin_cadre_enfonce();

            echo debut_cadre_enfonce();
            echo "<ul><li><a href='./?exec=utilitaires&tache=etaposcentre'>Vérifier Si établissement possède de centre</a></li>";
            echo "<br/><li><a href='./?exec=utilitaires&tache=suppressions'>Liste des dossiers supprimés</a></li>";
//        echo "<br/><li><a href='./?exec=utilitaires&tache=importer'>Importer dossiers externes</a></li>";
            echo "<br/><li><a href='./?exec=utilitaires&tache=stats_ano'>Stats Anonymat</a></li></ul>";
            echo fin_cadre_enfonce();
        }

        echo debut_droite();

        /*
        echo "<pre>";
        print_r($_POST);
        echo "</pre>";
         */
        //    $sql="UPDATE bg_repartition SET id_anonyme2=DECODE(id_anonyme,'30mars74'), ano2=DECODE(ano,'30mars74') WHERE annee=2018 ";
        //    $sql="UPDATE bg_repartition SET id_anonyme=id_anonyme2, ano=ano2 WHERE annee=2018 ";
        //  $sql="UPDATE bg_repartition SET id_anonyme2=id_anonyme, ano2=ano WHERE num_table='12487' ";
        //    bg_query($lien,$sql,__FILE__,__LINE__);

        if (isset($_REQUEST['tache']) && $_REQUEST['tache'] == 'supmdpeta') {
            $sql = "UPDATE bg_ref_etablissement SET mdp_eta='' WHERE 1";
            bg_query($lien, $sql, __FILE__, __LINE__);
            $sql2 = "DELETE FROM spip_auteurs WHERE bio='Etablissement' ";
            bg_query($lien, $sql2, __FILE__, __LINE__);
            echo debut_cadre_enfonce(), mysqli_affected_rows($lien), " Mots de passe initialisés ", fin_cadre_enfonce();
        }

        if (isset($_REQUEST['tache']) && $_REQUEST['tache'] == 'cremdpeta') {
            $tSql[] = "DELETE FROM spip_auteurs WHERE email like '%etablissement@%'";
            $tAlphabet = range('a', 'z');
            $tVoyelles = array('a', 'e', 'i', 'o', 'u', 'y');
            $tConsonnes = array_diff($tAlphabet, $tVoyelles);
            $maj = date('Y-m-d h:i:s');
            $tSql[] = "UPDATE bg_ref_etablissement SET mdp_eta=concat(ELT(1+floor(RAND()*" . (count($tConsonnes) - 1) . "),'" . implode("','", $tConsonnes) . "'),
					ELT(1+floor(RAND()*" . (count($tVoyelles) - 1) . "),'" . implode("','", $tVoyelles) . "'),
					ELT(1+floor(RAND()*" . (count($tConsonnes) - 1) . "),'" . implode("','", $tConsonnes) . "'),
					ELT(1+floor(RAND()*" . (count($tVoyelles) - 1) . "),'" . implode("','", $tVoyelles) . "'),
					ELT(1+floor(RAND()*" . (count($tConsonnes) - 1) . "),'" . implode("','", $tConsonnes) . "'),
					ELT(1+floor(RAND()*" . (count($tVoyelles) - 1) . "),'" . implode("','", $tVoyelles) . "'),
					ELT(1+floor(RAND()*" . (count($tConsonnes) - 1) . "),'" . implode("','", $tConsonnes) . "'),
					ELT(1+floor(RAND()*" . (count($tVoyelles) - 1) . "),'" . implode("','", $tVoyelles) . "'))
					WHERE mdp_eta='' AND login_eta!='' AND id_etat=1 ";

            $tSql[] = 'INSERT INTO spip_auteurs( nom, statut, bio, nom_site, email, login, pass, source, lang, en_ligne, url_site, low_sec, pgp, htpass  ) '
                . " select "
                . "  etablissement, '1comite' statut, 'Etablissement' bio, etablissement, concat('etablissement@',id), "
                . " login_eta login, md5(mdp_eta) pass, 'spip' source, 'fr' lang, '$maj' en_ligne, '' url_site , '' low_sec, '' pgp, '' htpass "
                . " FROM bg_ref_etablissement "
                . " WHERE login_eta!='' AND id_etat=1 ORDER BY etablissement";

            foreach ($tSql as $sql) {
                bg_query($lien, $sql, __FILE__, __LINE__);
            }

            echo debut_cadre_enfonce(), mysqli_affected_rows($lien), " Lignes affectés <br/>Anciens auteurs SPIP opérateurs de saisie supprimés avec succ&egrave;s <br/>Mots de passe des opérateurs générés avec succ&egrave;s ", fin_cadre_enfonce();
        }
        if (isset($_REQUEST['tache']) && $_REQUEST['tache'] == 'crenewmdpeta') {

            $tSql[] = "UPDATE `bg_ref_etablissement` SET `login_eta` = '' WHERE `bg_ref_etablissement`.`id` != 0;";

            $tSql[] = "UPDATE `bg_ref_etablissement` SET `nombre_garcon` = 0,`nombre_fille` = 0 WHERE `bg_ref_etablissement`.`si_centre` ='non';";

            foreach ($tSql as $sql) {
                bg_query($lien, $sql, __FILE__, __LINE__);
            }
            echo debut_cadre_enfonce(),  " Anciens Logins Etablissements supprimés avec succ&egrave;s <br/>Login des Etablissements générés avec succ&egrave;s ", fin_cadre_enfonce();

            genererLoginEtablissement($lien);

        }

        if (isset($_REQUEST['tache']) && $_REQUEST['tache'] == 'impmdpeta') {

            echo debut_cadre_relief('', '', '', 'Liste des régions');
            $liste = "<ol>";
            $sql = "SELECT * FROM bg_ref_region_division ORDER BY region_division ";
            $result = bg_query($lien, $sql, __FILE__, __LINE__);
            while ($row = mysqli_fetch_array($result)) {
                $id = $row['id'];
                $region = $row['region_division'];
                $liste .= "<li><a href='../plugins/fonctions/inc-pdf-logins.php?id=$id'>$region</a></li>";
            }
            $liste .= "</ol>";
            echo $liste;
            echo fin_cadre_relief();
        }

        echo '<script type="text/javascript">',
        '$(document).ready(function() {',
        'document.getElementById(\'num_table\').value=localStorage.getItem("txtValue");',
        '});',
            '</script>';
        if (isset($_REQUEST['tache']) && $_REQUEST['tache'] == 'releve_notes') {
            echo debut_cadre_enfonce('', '', '', 'Impressions de relevés de notes et attestations');
            $liste = "<form action='../plugins/fonctions/inc-pdf-reclamations.php?&num_table=$num_table' method='GET' name='form_releve'>";
            $liste .= "<table>";
            $liste .= "<tr><td>Année</td><td><input name='annee' id='annee' value='2023'></input></td></tr>";
            $liste .= "<tr><td>Numéro de table</td><td><input type='number' name='num_table' id='num_table' /></td></tr>";
            $liste .= "<tr><td><center><input type='submit' name='' value='IMPRIMER' /></center></td><td><center><input type='button' value='QUITTER' /></center></td></tr>";
            $liste .= "</table></form>";
            echo $liste;
            echo fin_cadre_enfonce();
        }
        if (isset($_REQUEST['tache']) && $_REQUEST['tache'] == 'duplicata_notes') {
            echo debut_cadre_enfonce('', '', '', 'Impressions de duplicata');
            $liste = "<form action='../plugins/fonctions/inc-pdf-reclamations-duplicata.php?&num_table=$num_table' method='GET' name='form_duplicata'>";
            $liste .= "<table>";
            $liste .= "<tr><td>Année</td><td><input name='annee' id='annee' value='2023'></input></td></tr>";
            $liste .= "<tr><td>Numéro de table</td><td><input type='number' name='num_table' id='num_table' /></td></tr>";
            $liste .= "<tr><td><center><input type='submit' name='' value='IMPRIMER' /></center></td><td><center><input type='button' value='QUITTER' /></center></td></tr>";
            $liste .= "</table></form>";
            echo $liste;
            echo fin_cadre_enfonce();
        }
        ///////////////////////////////////////////////////////////////////////////////////////////
        if (isset($_REQUEST['tache']) && $_REQUEST['tache'] == 'etaposcentre') {
            $sql = "SELECT etablissement FROM bg_ref_etablissement "
                . " WHERE id_centre=0 "
                . " ORDER BY etablissement ";
            $result = bg_query($lien, $sql, __FILE__, __LINE__);

            echo debut_boite_alerte();
            echo "<h3>Etablissements sans centre</h3>";
            echo "<table>";
            while ($row = mysqli_fetch_array($result)) {
                $etablissement = $row['etablissement'];
                $nbre = $row['nbre'];
                echo "<tr><td>$etablissement</td></tr>";
            }
            echo "</table>";
            echo fin_boite_alerte();
        }

        if (isset($_REQUEST['tache']) && $_REQUEST['tache'] == 'suppressions') {
            echo debut_cadre_couleur('', '', '', 'Liste des dossiers supprimés');
            echo "<table>";
            $sql = "SELECT nom, prenoms, ser.serie, eta.etablissement, id_candidat, num_table, ddn, ldn
					FROM bg_suppr_candidats can, bg_ref_serie ser, bg_ref_etablissement eta
					WHERE can.annee=$annee AND can.etablissement=eta.id AND can.serie=ser.id ";
            $result = bg_query($lien, $sql, __FILE__, __LINE__);
            $tab = array('nom', 'prenoms', 'serie', 'etablissement', 'id_candidat', 'num_table', 'ddn', 'ldn');
            while ($row = mysqli_fetch_array($result)) {
                foreach ($tab as $val) {
                    $$val = $row[$val];
                }

                echo "<tr><td>$nom $prenoms</td><td>" . afficher_date($ddn) . " $ldn</td><td>$etablissement</td><td>$serie</td><td>$id_candidat</td></tr>";
            }
            echo "</table>", fin_cadre_couleur();
        }

        if (isset($_REQUEST['tache']) && $_REQUEST['tache'] == 'stats_ano') {
            $sql = "SELECT delib1, count(*) as nbre FROM bg_resultats
					WHERE annee=$annee
					GROUP BY delib1
					ORDER BY delib1 ";
            $result = bg_query($lien, $sql, __FILE__, __LINE__);
            while ($row = mysqli_fetch_array($result)) {
                $delib1 = $row['delib1'];
                $nbre = $row['nbre'];
                if ($delib1 == 'Ajourne') {$totAjournes += $nbre;}
                if ($delib1 != 'Abandon' && $delib1 != 'Absent') {$total += $nbre;}
                if (in_array($delib1, array('Passable', 'Abien', 'Bien', 'TBien'))) {$totalAdmis += $nbre;}
                if (in_array($delib1, array('Oral'))) {$totalAdmissibles += $nbre;}
                if (in_array($delib1, array('Oral', 'Passable', 'Abien', 'Bien', 'TBien'))) {$totalAdmisAdmissibles += $nbre;}
            }
            $taux = ($totalAdmisAdmissibles * 100) / $total;
            echo "<table>";
            echo "<th>TYPE</th><th>NOMBRE</th>";
            echo "<tr><td>Total des candidats présents</td><td>$total</td></tr>";
            echo "<tr><td>Total des candidats Admis</td><td>$totalAdmis</td></tr>";
            echo "<tr><td>Total des candidats Admissibles </td><td>$totalAdmissibles</td></tr>";
            echo "<tr><td>Total des candidats Admis et Admissibles </td><td>$totalAdmisAdmissibles</td></tr>";
            echo "<tr><td>Total des candidats Ajournés </td><td>$totAjournes</td></tr>";
            echo "<tr><td>Taux de réussite </td><td>$taux</td></tr>";
            echo "</table>";

            $sql2 = "SELECT * FROM bg_ref_serie ORDER BY serie ";
            $result2 = bg_query($lien, $sql2, __FILE__, __LINE__);
            while ($row2 = mysqli_fetch_array($result2)) {
                $serie = $row2['serie'];
                $id_serie = $row2['id'];

                $totAjournes = 0;
                $nbre = 0;
                $total = 0;
                $totalAdmis = 0;
                $totalAdmissibles = 0;
                $totalAdmisAdmissibles = 0;
                $param = selectCodeAno($lien, $annee, 1);
                $sql3 = "SELECT ser.serie, delib1, count(*) as nbre
					FROM bg_resultats res, bg_ref_serie ser, bg_repartition rep, bg_candidats can
					WHERE res.annee=$annee AND rep.annee=$annee AND can.annee=$annee AND ser.id=$id_serie AND can.serie=$id_serie
					AND can.serie=ser.id AND can.num_table=rep.num_table AND res.id_anonyme=rep.id_anonyme
					GROUP BY ser.serie, delib1
					ORDER BY ser.serie, delib1 ";
                $result3 = bg_query($lien, $sql3, __FILE__, __LINE__);
                while ($row3 = mysqli_fetch_array($result3)) {
                    $delib1 = $row3['delib1'];
                    $nbre = $row3['nbre'];
                    if ($delib1 == 'Ajourne') {$totAjournes += $nbre;}
                    if ($delib1 != 'Abandon' && $delib1 != 'Absent') {$total += $nbre;}
                    if (in_array($delib1, array('Oral'))) {$totalAdmissibles += $nbre;}
                    if (in_array($delib1, array('Passable', 'Abien', 'Bien', 'TBien'))) {$totalAdmis += $nbre;}
                    if (in_array($delib1, array('Oral', 'Passable', 'Abien', 'Bien', 'TBien'))) {$totalAdmisAdmissibles += $nbre;}
                }
                $taux = ($totalAdmisAdmissibles * 100) / $total;
                echo "<h1>$serie</h1>";
                echo "<table>";
                echo "<th>TYPE</th><th>NOMBRE</th>";
                echo "<tr><td>Total des candidats présents</td><td>$total</td></tr>";
                echo "<tr><td>Total des candidats Admis</td><td>$totalAdmis</td></tr>";
                echo "<tr><td>Total des candidats Admissibles </td><td>$totalAdmissibles</td></tr>";
                echo "<tr><td>Total des candidats Admis et Admissibles </td><td>$totalAdmisAdmissibles</td></tr>";
                echo "<tr><td>Total des candidats Ajournés </td><td>$totAjournes</td></tr>";
                echo "<tr><td>Taux de réussite (série $serie)</td><td>$taux</td></tr>";
                echo "</table>";

            }
        }

        if (isset($_REQUEST['tache']) && $_REQUEST['tache'] == 'importer') {
            echo debut_cadre_couleur('', '', '', 'Liste des ');
            $sql = "SELECT * FROM `bg_tampon` WHERE 1";
            $result = bg_query($lien, $sql, __FILE__, __LINE__);
            $tab = array('num', 'nom', 'prenoms', 'ddn', 'ldn', 'nationalite', 'num_table', 'jury', 'serie', 'sexe', 'eps', 'etablissement', 'centre', 'num_dossier', 'lv1', 'lv2', 'ef1', 'ef2', 'ef3', 'centre_ecrit', 'code_ces', 'resultat', 'annee_odae', 'identifiant_odae');
            while ($row = mysqli_fetch_array($result)) {
                foreach ($tab as $val) {
                    $$val = addslashes($row[$val]);
                }

                if ($serie == 'A4') {
                    $serie = 1;
                }

                if ($serie == 'D') {
                    $serie = 4;
                }

                // if ($serie == 'F1') {
                //     $serie = 5;
                // }

                // if ($serie == 'G2') {
                //     $serie = 10;
                // }

                // if ($serie == 'G3') {
                //     $serie = 11;
                // }

                if ($nationalite == 'BELGIQUE') {
                    $nationalite = 20;
                }

                if ($nationalite == 'GABON') {
                    $nationalite = 15;
                }

                if ($nationalite == 'MALI') {
                    $nationalite = 8;
                }

                if ($nationalite == 'TOGO') {
                    $nationalite = 2;
                }

                $ddn = formater_date($ddn);

                if ($sexe == 'F') {
                    $sexe = 1;
                }

                if ($sexe == 'M') {
                    $sexe = 2;
                }

                if ($eps == 'A') {
                    $eps = 1;
                }

                if ($eps == 'I') {
                    $seps = 2;
                }

                if ($ef1 == 'ALLEMAND') {
                    $efa = 1;
                }

                if ($ef1 == 'ARABE') {
                    $efa = 5;
                }

                if ($ef1 == 'ESPAGNOL') {
                    $efa = 2;
                }

                if ($ef1 == 'LATIN') {
                    $efa = 3;
                }

                if ($ef1 == 'RUSSE') {
                    $efa = 4;
                }

                if ($ef2 == 'DESSIN (Art)') {
                    $efb = 2;
                }

                if ($ef2 == 'ENSEIGNEMENT MENAGER') {
                    $efb = 1;
                }

                if ($ef3 == 'MUSIQUE') {
                    $efb = 3;
                }

                if ($lv2 == 'ALLEMAND') {
                    $lv2 = 1;
                }

                if ($lv2 == 'ESPAGNOL') {
                    $lv2 = 2;
                }

                $sql2 = "INSERT INTO bg_candidats (`id_candidat`, `annee`, `num_table`, `num_dossier`, `serie`, `annee_bac1`, `jury_bac1`, `num_table_bac1`, `nom`, `prenoms`, `sexe`, `ddn`, `ldn`, `pdn`, `nationalite`, `telephone`, `lv1`, `lv2`, `efa`, `efb`, `eps`, `etat_physique`, `atelier1`, `atelier2`, `atelier3`, `type_session`, `etablissement`, `centre`, `nom_photo`, `login`)
							VALUES('$num','$annee','','$num_dossier','$serie','$annee_odae','','$identifiant_odae','$nom','$prenoms','$sexe','$ddn','$ldn','$nationalite','$nationalite','','3','$lv2','$efa','$efb','$eps','1','1','2','3','1','5','5','','ghislain')";
                bg_query($lien, $sql2, __FILE__, __LINE__);
            }
            echo "</table>", fin_cadre_couleur();

        }

    }
    echo fin_cadre_trait_couleur();
    echo fin_grand_cadre(), fin_gauche(), fin_page();
}

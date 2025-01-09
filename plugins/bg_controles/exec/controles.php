<?php
if (!defined('_ECRIRE_INC_VERSION')) {
    return;
}

include_spip('inc/presentation'); // alleger les inclusions avec un inc/presentation_mini
include '../plugins/fonctions/generales.php';

setlocale(LC_TIME, "fr_FR");
define('MAX_LIGNES', 200);

function getNoteParAtelier($lien, $id_atelier, $sexe, $perf)
{
    if (in_array($id_atelier, array('1', '2', '4', '7'))) {
        if ($sexe == 1) {
            $andPerf = " AND fille <= $perf ";
        } else {
            $andPerf = " AND garcon <= $perf ";
        }

    } else {
        if ($sexe == 1) {
            $andPerf = " AND fille >= '$perf' ";
        } else {
            $andPerf = " AND garcon >= '$perf' ";
        }

    }
    if ($perf <= 0) {
        $note_perf = 0;
    } else {
        $sql = "SELECT max(note) as note_perf FROM bg_performance WHERE id_atelier='$id_atelier' $andPerf  ";
        $result = bg_query($lien, $sql, __FILE__, __LINE__);
        $row = mysqli_fetch_array($result);
        $note_perf = $row['note_perf'];
    }
    if ($note_perf == '') {
        $note_perf = 0;
    }

    // echo $sql . 'ZZ' . $note_perf;
    return $note_perf;
}
function getAllNotesEps($lien, $num_table, $annee)
{
    $sql = "SELECT nom, prenoms, can.num_table, ser.serie, can.sexe
                FROM bg_candidats can, bg_ref_serie ser, bg_repartition rep, bg_ref_etablissement eta, bg_ref_etablissement cen
                WHERE can.annee=$annee AND rep.annee=$annee AND can.num_table=rep.num_table AND can.etablissement=eta.id
                AND rep.id_centre=cen.id AND can.serie=ser.id AND can.num_table=$num_table
                ORDER BY can.num_table ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    //   echo "<a href='" . generer_url_ecrire('sport') . "&etape=impres'><center><b>RETOUR A LA PAGE PRECEDENTE</b></center></a><br/>";
    if (mysqli_num_rows($result) > 0) {
        // $imprimer = "<center><a href='../plugins/fonctions/inc-liste_eps.php?annee=$annee&t=p&c=$id_centre&e=$id_etablissement&s=$id_serie&a=$id_atelier'><img src='../plugins/images/imprimer1.jpg' width='150px' /></a></center>";
        // $liste = "<h1>Notes des candidats pour pour chaque atelier</h1><br/>";
        $liste = "<table border=2>";
        $liste .= "<th>Numéros</th><th>Noms et prenoms</th><th>Séries</th>";
        for ($i = 1; $i <= 7; $i++) {
            $atelier = selectReferentiel($lien, 'atelier', $i);
            $liste .= "<th>$atelier</th>";
        }
        $liste .= "<th>Notes</th>";
        while ($row = mysqli_fetch_array($result)) {
            foreach (array('nom', 'prenoms', 'serie', 'num_table', 'sexe', 'note') as $val) {
                $$val = $row[$val];
            }

            if ($sexe == 1) {
                $civ = 'Mlle';
            } else {
                $civ = 'M.';
            }

            $tabNotes = notesEPSParCandidat($lien, $annee, $num_table);
            $liste .= "<tr><td>$num_table</td><td>$civ $nom $prenoms</td><td>$serie</td>";
            for ($i = 1; $i <= 7; $i++) {
                $atelier = selectReferentiel($lien, 'atelier', $i);
                $performance = $tabNotes[$i]['performance'];
                $note_perf = $tabNotes[$i]['note_perf'];
                if ($performance != '') {
                    $affiche = $performance . '[' . $note_perf . ']';
                } else {
                    $affiche = '';
                }

                $liste .= "<td>$affiche</td>";
            }
            $liste .= "<td>" . $tabNotes['note'] . "</td></tr>";
        }
        $liste .= "</table>" . $imprimer;
        echo $liste;
    } else {
        echo debut_boite_alerte(), "<h2>Aucun candidat pour ces critères</h2>", fin_boite_alerte();
    }
}
function getPerformanceEPS($lien, $annee, $num_table, $id_atelier)
{
    $sql = "SELECT performance, note_perf FROM bg_notes_performance WHERE annee=$annee AND num_table='$num_table' AND id_atelier='$id_atelier' LIMIT 0,1 ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $row = mysqli_fetch_array($result);
    $performance = $row['performance'];
    $note_perf = $row['note_perf'];
    $tab['performance'] = $performance;
    $tab['note_perf'] = $note_perf;
    return $tab;
}
function exec_controles()
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
    if (isAutorise(array('Admin', 'Informaticien', 'Encadrant'))) {

        if ((!isset($_REQUEST['etape']))) {
            echo debut_cadre_trait_couleur('', '', '', "GESTION DES CONTROLES", "", "", false);

            echo debut_gauche();

            if (($statut == 'Admin') || ($statut == 'Informaticien')) {
                echo debut_boite_info();
                echo "<p>Contole du Sport</p>";
                echo "<p><a href='" . generer_url_ecrire('controles') . "&tache=sportsurplus'>Gestion de Sport en Surplus </a></p>";
                echo "<p><a href='" . generer_url_ecrire('controles') . "&tache=sportinapte'>Gestion de Sport en Inaptes</a></p>";
                echo "<p><a href='" . generer_url_ecrire('controles') . "&tache=sportapte'>Gestion de Sport Aptes sans Notes</a></p>";
                echo "<p><a href='" . generer_url_ecrire('controles') . "&tache=transnote'>Gestions de Transfert de Notes de Sport </a></p>";
                echo fin_boite_info();
                echo debut_boite_info();
                echo "<p>Contole des Notes</p>";
                echo "<p><a href='" . generer_url_ecrire('controles') . "&tache=coefficient'>Gestions de Coefficient 0</a></p>";
                echo "<p><a href='" . generer_url_ecrire('controles') . "&tache=notesup'>Gestions de Notes Supérieur à 20</a></p>";
                echo "<p><a href='" . generer_url_ecrire('controles') . "&tache=matfac'>Gestions de Matières facultatives Sup 2  </a></p>";
                echo fin_boite_info();

            }

            echo debut_boite_info();
            echo "<p><a href='" . generer_url_ecrire('controles') . "' class='ajax'>Retour au Menu Principale</a></p>";

            //echo "<form method='POST'><input name='detruire_session' type='submit' value='Changer de session' /></form>";
            echo fin_boite_info();

            //  }
            echo debut_droite();

            echo debut_cadre_trait_couleur('', '', '', "CONTROLES", "", "", false);

            if (isset($_REQUEST['tache']) && ($_REQUEST['tache'] == 'sportsurplus')) {
                echo "<div class='onglets_simple clearfix'>
                <ul>";

                echo "<li><a href='./?exec=controles&etapes=operation' class='ajax'>NATIONAL</a></li>";
                $sqlr = "SELECT * FROM bg_ref_region  ORDER BY id ";

                $resultr = bg_query($lien, $sqlr, __FILE__, __LINE__);
                while ($rowr = mysqli_fetch_array($resultr)) {
                    $id_region = $rowr['id'];
                    echo "<li><a href='./?exec=controles&etapes=operationreg&id_region=$id_region' class='ajax'>" . $rowr['region'] . "</a></li>";
                }
                echo "</ul>
                </div>";

            }
            if (isset($_REQUEST['tache']) && ($_REQUEST['tache'] == 'sportinapte')) {
                echo "<div class='onglets_simple clearfix'>
                <ul>";

                echo "<li><a href='./?exec=controles&etapes=opeinapte' class='ajax'>NATIONAL</a></li>";
                $sqlr = "SELECT * FROM bg_ref_region  ORDER BY id ";

                $resultr = bg_query($lien, $sqlr, __FILE__, __LINE__);
                while ($rowr = mysqli_fetch_array($resultr)) {
                    $id_region = $rowr['id'];
                    echo "<li><a href='./?exec=controles&etapes=opeinaptereg&id_region=$id_region' class='ajax'>" . $rowr['region'] . "</a></li>";
                }
                echo "</ul>
                </div>";

            }
            if (isset($_REQUEST['tache']) && ($_REQUEST['tache'] == 'sportapte')) {

                echo "<center><h2>Candidats aptes sans Notes de Sport</h2></center>";

                $sql = "SELECT can.num_table,can.nom,can.prenoms,eta.etablissement,insp.inspection,reg.region
                FROM bg_candidats can,bg_ref_etablissement eta,bg_ref_inspection insp,bg_ref_region reg
                WHERE can.annee=$annee AND can.etablissement=eta.id AND eta.id_inspection=insp.id
                AND insp.id_region=reg.id AND can.eps=1 AND can.statu=1 AND can.num_table 
                NOT IN (SELECT num_table FROM bg_notes_eps )
                ORDER BY reg.region ASC";
                $result = bg_query($lien, $sql, __FILE__, __LINE__);
                $tab = array('num_table', 'etablissement', 'inspection', 'region', 'nom', 'prenoms');

                if (mysqli_num_rows($result) > 0) {
                    echo "<table class='spip liste'>";
                    $thead = "<th>N° de Table</th><th>Nom & Prenoms</th><th>Etablissement</th><th>Inspection</th><th>Region</th>";
                    while ($row_aff = mysqli_fetch_array($result)) {
                        foreach ($tab as $var) {
                            $$var = $row_aff[$var];
                            //Decommenter la ligne precedente et commenter la ligne suivante pour gerer l'encodage sur la lsite des candidats

                        }

                        $tbody .= "<tr> <td>$num_table</td><td> $nom $prenoms </td>
                <td>$etablissement</td><td>$inspection</td><td>$region</td></tr>";

                    }
                    echo $thead, $tbody;
                    echo "</table>";
                } else {
                    echo debut_boite_alerte(), "<h2>Aucun candidat pour ces critères</h2>", fin_boite_alerte();
                }
            }
            if (isset($_REQUEST['tache']) && ($_REQUEST['tache'] == 'notesup')) {
                echo "<div class='onglets_simple clearfix'>
                <ul>";

                // echo "<li><a href='./?exec=controles&etapes=operation' class='ajax'>SUPPRIMER SURPLUS</a></li>";
                echo "<li><a href='./?exec=controles&etapes=openotesup' class='ajax'>NATIONAL</a></li>";
                $sqlr = "SELECT * FROM bg_ref_region  ORDER BY id ";

                $resultr = bg_query($lien, $sqlr, __FILE__, __LINE__);
                while ($rowr = mysqli_fetch_array($resultr)) {
                    $id_region = $rowr['id'];
                    echo "<li><a href='./?exec=controles&etapes=openotesupreg&id_region=$id_region' class='ajax'>" . $rowr['region'] . "</a></li>";
                }
                echo "</ul>
                </div>";

            }

            if (isset($_REQUEST['tache']) && ($_REQUEST['tache'] == 'coefficient')) {
                echo "<div class='onglets_simple clearfix'>
                <ul>";

                // echo "<li><a href='./?exec=controles&etapes=operation' class='ajax'>SUPPRIMER SURPLUS</a></li>";
                echo "<li><a href='./?exec=controles&etapes=opecoefficient' class='ajax'>NATIONAL</a></li>";
                $sqlr = "SELECT * FROM bg_ref_region  ORDER BY id ";

                $resultr = bg_query($lien, $sqlr, __FILE__, __LINE__);
                while ($rowr = mysqli_fetch_array($resultr)) {
                    $id_region = $rowr['id'];
                    echo "<li><a href='./?exec=controles&etapes=opecoefficientreg&id_region=$id_region' class='ajax'>" . $rowr['region'] . "</a></li>";
                }
                echo "</ul>
                </div>";

            }
            if (isset($_REQUEST['tache']) && ($_GET['tache'] == 'matfac')) {
                echo "<div class='onglets_simple clearfix'>
                <ul>";
                echo "<li><a href='./?exec=controles&etapes=opematfac' class='ajax'>NATIONAL</a></li>";
                $sqlr = "SELECT * FROM bg_ref_region  ORDER BY id ";

                $resultr = bg_query($lien, $sqlr, __FILE__, __LINE__);
                while ($rowr = mysqli_fetch_array($resultr)) {
                    $id_region = $rowr['id'];
                    echo "<li><a href='./?exec=controles&etapes=opematfacreg&id_region=$id_region' class='ajax'>" . $rowr['region'] . "</a></li>";
                }
                echo "</ul>
                </div>";

            }
            if (isset($_REQUEST['tache']) && ($_GET['tache'] == 'transnote')) {

                $tri = debut_cadre_enfonce('', '', '', 'Transfert des Notes de Sport');

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
                    $andWhereEta_d = "AND eta.id_centre=$tri_centre_dc";
                }
                if (isset($_POST['tri_inspection_dc']) && $_POST['tri_inspection_dc'] > 0) {

                    $andWhere .= "  ";
                    $andWhereEta_d .= " AND eta.id_inspection=$tri_inspection_dc ";
                    $andWhereCen_d .= " AND eta.si_centre='oui' AND ins.id=eta.id_inspection AND eta.id_inspection=$tri_inspection_dc ";
                }
                if (isset($_POST['tri_region_dc']) && $_POST['tri_region_dc'] > 0) {
                    // $andWhere .= " AND ins.id_region=$tri_region_d ";
                    $andWhere .= " ";
                    $andWhereRegion_d .= "WHERE id_region=$tri_region_dc";
                    $andWhereEta_d .= " AND ins.id_region=$tri_region_dc AND ins.id=eta.id_inspection";
                    $andWhereCen_d .= "AND si_centre='oui' AND ins.id=eta.id_inspection AND ins.id_region=$tri_region_dc";
                }
                if (isset($_POST['tri_etablissement_dc']) && $_POST['tri_etablissement_dc'] > 0) {
                    //$andWhere .= " AND can.etablissement=$tri_etablissement_dc ";
                    $andWhere .= "  ";
                    $andWhereEta_d .= " ";
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
			</tr>";
                $tri .= " <tr><td> <center><strong> Numero Initiale </strong></center></td>
                <td > <center><strong>Numéro Final </strong></center> <td></tr>";

                $tri .= "<tr><td><center><input type='number' required name='numero_initial' style='width:50%' /></center></td>
<td><center><input type='number' required name='numero_final' style='width:50%' /></center></td>
 </tr>";

                $tri .= "<tr> <td colspan=2> <center><input name='transferereps' type='submit' value='Transférer' /></center></td><td></td></tr>";

                $tri .= "</table>";
                $tri .= fin_cadre_enfonce();
                echo $tri;

            }
            if (isset($_REQUEST['etapes']) && ($_GET['etapes'] == 'opematfac')) {
                $sql_noteFac = "SELECT * ,MIN(note) AS min_note, id_matiere AS mat FROM `bg_notes` WHERE id_type_note=4 GROUP BY num_table HAVING (COUNT(note)>2) ORDER BY jury";

                $result_noteFac = bg_query($lien, $sql_noteFac, __FILE__, __LINE__);
                if ($result_noteFac > 0) {
                    $imprimer = "<center><input  type='submit' name='supprimer_matfac' style='width: 20em;  height: 2em;' value='Supprimer les Notes Supplémentaires' onClick=\"window.location='" . generer_url_ecrire('controles') . "&etapes=operation';\"  /></center>
                    ";
                    $liste = "<form name='' method='POST' action=''>";
                    $liste .= "<center><h4>Candidats ayant plus de 2 Notes facultatives</h4></center><br/>" . $imprimer;
                    $liste .= "<table  class='spip liste'>";
                    $liste .= "<thead><th>Num Jury</th><th>Num Table</th><th>Matière</th><th>Note</th></thead>";
                    while ($row1 = mysqli_fetch_array($result_noteFac)) {
                        $num_tableFac = $row1['num_table'];
                        $id_type_session = $row1['id_type_session'];
                        $id_matiere = $row1['mat'];
                        $id_type_note = $row1['id_type_note'];
                        $jury = $row1['jury'];
                        $noteFac = $row1['min_note'];

                        $sqlMat = "SELECT * FROM `bg_notes` WHERE num_table='$num_tableFac' AND id_type_session=$id_type_session AND id_type_note=4 AND jury=$jury AND note=$noteFac";
                        $resultMat = bg_query($lien, $sqlMat, __FILE__, __LINE__);
                        $matF = mysqli_fetch_array($resultMat);
                        $id_matiere = $matF['id_matiere'];

                        //  echo debut_boite_alerte();

                        $liste .= "<tbody><tr><td>" . $jury . "</td><td>" . $num_tableFac . "</td><td>" . get_matiere_by_id($lien, $id_matiere) . "</td><td>" . $noteFac . "</td></tr></tbody>";

                        //  echo fin_boite_alerte();

                    }
                    $liste .= "</table></form>";
                    echo $liste;

                } else {echo "<h2 style='color:red';><center>Aucun Candidat avec Notes de matiere facultative supérieure à 2 détecté</center></h2>";}

            }
            if (isset($_REQUEST['etapes']) && ($_GET['etapes'] == 'opematfacreg')) {

                // $sql_noteFac = "SELECT * ,MIN(note) AS min_note, id_matiere AS mat FROM `bg_notes` WHERE id_type_note=4 GROUP BY num_table HAVING (COUNT(note)>2) ORDER BY jury";
                $sql_noteFac = "SELECT * ,MIN(note) AS min_note, id_matiere AS mat FROM `bg_notes`
                WHERE id_type_note=4 AND  id_region=$id_region GROUP BY num_table HAVING (COUNT(note)>2) ORDER BY jury";

                $result_noteFac = bg_query($lien, $sql_noteFac, __FILE__, __LINE__);
                if ($result_noteFac > 0) {
                    $imprimer = "<center><input  type='submit' name='supprimer_matfac' style='width: 20em;  height: 2em;' value='Supprimer les Notes Supplémentaires' onClick=\"window.location='" . generer_url_ecrire('controles') . "&etapes=operation';\"  />
                    <input type='hidden' name='id_region' value='$id_region' /></center>
                    ";
                    $liste = "<form name='' method='POST' action=''>";
                    $liste .= "<center><h4>Candidats ayant plus de 2 Notes facultatives</h4></center><br/>" . $imprimer;
                    $liste .= "<table  class='spip liste'>";
                    $liste .= "<thead><th>Num Jury</th><th>Num Table</th><th>Matière</th><th>Note</th></thead>";
                    while ($row1 = mysqli_fetch_array($result_noteFac)) {
                        $num_tableFac = $row1['num_table'];
                        $id_type_session = $row1['id_type_session'];
                        $id_matiere = $row1['mat'];
                        $id_type_note = $row1['id_type_note'];
                        $jury = $row1['jury'];
                        $noteFac = $row1['min_note'];

                        $sqlMat = "SELECT * FROM `bg_notes` WHERE num_table='$num_tableFac' AND id_type_session=$id_type_session AND id_type_note=4 AND jury=$jury AND note=$noteFac";
                        $resultMat = bg_query($lien, $sqlMat, __FILE__, __LINE__);
                        $matF = mysqli_fetch_array($resultMat);
                        $id_matiere = $matF['id_matiere'];

                        //  echo debut_boite_alerte();

                        $liste .= "<tbody><tr><td>" . $jury . "</td><td>" . $num_tableFac . "</td><td>" . get_matiere_by_id($lien, $id_matiere) . "</td><td>" . $noteFac . "</td></tr></tbody>";

                        //  echo fin_boite_alerte();

                    }
                    $liste .= "</table></form>";
                    echo $liste;

                } else {echo "<h2 style='color:red';><center>Aucun Candidat avec Notes de matiere facultative supérieure à 2 détecté</center></h2>";}

            }
            if (isset($_REQUEST['etapes']) && ($_GET['etapes'] == 'openotesup')) {
                $sql_noteSup = "SELECT * , id_matiere AS mat FROM `bg_notes` WHERE note>20 GROUP BY num_table  ORDER BY jury";

                $result_notesup = bg_query($lien, $sql_noteSup, __FILE__, __LINE__);
                if ($result_notesup > 0) {
                    // $imprimer = "<center><input  type='submit' name='supprimer_matfac' style='width: 20em;  height: 2em;' value='Supprimer les Notes Supplémentaires' onClick=\"window.location='" . generer_url_ecrire('controles') . "&etapes=operation';\"  /></center>
                    // ";
                    $liste = "<form name='' method='POST' action=''>";
                    $liste .= "<center><h4>Candidats ayant une note supérieure à 20</h4></center>";
                    $liste .= "<table  class='spip liste'>";
                    $liste .= "<thead><th>Num Jury</th><th>Num Table</th><th>Matière</th><th>Note</th></thead>";
                    while ($row1 = mysqli_fetch_array($result_notesup)) {
                        $num_tableSup = $row1['num_table'];
                        $id_type_session = $row1['id_type_session'];
                        $id_matiere = $row1['mat'];
                        $id_type_note = $row1['id_type_note'];
                        $jury = $row1['jury'];
                        $noteSup = $row1['note'];

                        $sqlMat = "SELECT * FROM `bg_notes` WHERE num_table='$num_tableSup' AND id_type_session=$id_type_session AND id_type_note=4 AND jury=$jury";
                        $resultMat = bg_query($lien, $sqlMat, __FILE__, __LINE__);
                        $matF = mysqli_fetch_array($resultMat);
                        $id_matiere = $matF['id_matiere'];

                        //  echo debut_boite_alerte();

                        $liste .= "<tbody><tr><td>" . $jury . "</td><td>" . $num_tableSup . "</td><td>" . get_matiere_by_id($lien, $id_matiere) . "</td><td>" . $noteSup . "</td></tr></tbody>";

                        //  echo fin_boite_alerte();

                    }
                    $liste .= "</table></form>";
                    echo $liste;

                } else {echo "<h2 style='color:red';><center>Aucun Candidat avec Notes de matiere facultative supérieure à 2 détecté</center></h2>";}

            }
            if (isset($_REQUEST['etapes']) && ($_GET['etapes'] == 'openotesupreg')) {

                // $sql_noteFac = "SELECT * ,MIN(note) AS min_note, id_matiere AS mat FROM `bg_notes` WHERE id_type_note=4 GROUP BY num_table HAVING (COUNT(note)>2) ORDER BY jury";
                // $sql_noteFac = "SELECT * ,MIN(note) AS min_note, id_matiere AS mat FROM `bg_notes`
                // WHERE id_type_note=4 AND  id_region=$id_region GROUP BY num_table HAVING (COUNT(note)>2) ORDER BY jury";
                $sql_noteSup = "SELECT * , id_matiere AS mat FROM `bg_notes` WHERE note>20 AND  id_region=$id_region GROUP BY num_table  ORDER BY jury";
                $result_noteSup = bg_query($lien, $sql_noteSup, __FILE__, __LINE__);
                if ($result_noteSup > 0) {
                    $imprimer = "<center><input  type='submit' name='supprimer_matfac' style='width: 20em;  height: 2em;' value='Supprimer les Notes Supplémentaires' onClick=\"window.location='" . generer_url_ecrire('controles') . "&etapes=operation';\"  />
                    <input type='hidden' name='id_region' value='$id_region' /></center>
                    ";
                    $liste = "<form name='' method='POST' action=''>";
                    $liste .= "<center><h4>Candidats ayant une note supérieure à 20</h4></center>";
                    $liste .= "<table  class='spip liste'>";
                    $liste .= "<thead><th>Num Jury</th><th>Num Table</th><th>Matière</th><th>Note</th></thead>";
                    while ($row1 = mysqli_fetch_array($result_noteSup)) {
                        $num_tableSup = $row1['num_table'];
                        $id_type_session = $row1['id_type_session'];
                        $id_matiere = $row1['mat'];
                        $id_type_note = $row1['id_type_note'];
                        $jury = $row1['jury'];
                        $noteSup = $row1['note'];

                        $sqlMat = "SELECT * FROM `bg_notes` WHERE num_table='$num_tableSup' AND id_type_session=$id_type_session AND id_type_note=4 AND jury=$jury ";
                        $resultMat = bg_query($lien, $sqlMat, __FILE__, __LINE__);
                        $matF = mysqli_fetch_array($resultMat);
                        $id_matiere = $matF['id_matiere'];

                        //  echo debut_boite_alerte();

                        $liste .= "<tbody><tr><td>" . $jury . "</td><td>" . $num_tableSup . "</td><td>" . get_matiere_by_id($lien, $id_matiere) . "</td><td>" . $noteSup . "</td></tr></tbody>";

                        //  echo fin_boite_alerte();

                    }
                    $liste .= "</table></form>";
                    echo $liste;

                } else {echo "<h2 style='color:red';><center>Aucun Candidat avec Notes de matiere facultative supérieure à 2 détecté</center></h2>";}

            }
            if (isset($_REQUEST['etapes']) && ($_GET['etapes'] == 'opecoefficient')) {
                //$sql_coef  = "SELECT * ,MIN(note) AS min_note, id_matiere AS mat FROM `bg_resultats` WHERE id_type_note=4 GROUP BY num_table HAVING (COUNT(note)>2) ORDER BY jury";
                // $sql_coef  = "SELECT * FROM `bg_notes` WHERE `id_type_note` = 1 AND `coeff` = 0";
                $sql_coef = "SELECT *, id_matiere AS mat FROM `bg_notes` WHERE `id_type_note` = 1 AND `coeff` = 0";

                $result_coef = bg_query($lien, $sql_coef, __FILE__, __LINE__);
                if ($result_coef > 0) {
                    $imprimer = "<center><input  type='submit' name='supprimer_matfac' style='width: 20em;  height: 2em;' value='Supprimer les Notes Supplémentaires' onClick=\"window.location='" . generer_url_ecrire('controles') . "&etapes=operation';\"  /></center>
                    ";
                    $liste = "<form name='' method='POST' action=''>";
                    $liste .= "<center><h4>Candidats ayant un coefficiant égale à zero</h4></center> ";
                    $liste .= "<table  class='spip liste'>";
                    $liste .= "<thead><th>Num Jury</th><th>Num Table</th><th>Matière</th><th>Note</th></thead>";
                    while ($row1 = mysqli_fetch_array($result_coef)) {
                        $num_tableFac = $row1['num_table'];
                        $id_type_session = $row1['id_type_session'];
                        $id_matiere = $row1['mat'];
                        $id_type_note = $row1['id_type_note'];
                        $jury = $row1['jury'];
                        $noteCoef = $row1['note'];

                        $sqlMat = "SELECT *  FROM `bg_notes` WHERE num_table='$num_tableFac' AND id_type_session=$id_type_session AND id_type_note=4 AND jury=$jury  ";
                        $resultMat = bg_query($lien, $sqlMat, __FILE__, __LINE__);
                        $matF = mysqli_fetch_array($resultMat);
                        // $id_matiere = $matF['id_matiere'];

                        //  echo debut_boite_alerte();

                        $liste .= "<tbody><tr><td>" . $jury . "</td><td>" . $num_tableFac . "</td><td>" . get_matiere_by_id($lien, $id_matiere) . "</td><td>" . $noteCoef . "</td></tr></tbody>";

                        //  echo fin_boite_alerte();

                    }
                    $liste .= "</table></form>";
                    echo $liste;

                } else {echo "<h2 style='color:red';><center>Aucun Candidat avec Notes de matiere facultative supérieure à 2 détecté</center></h2>";}

            }
            if (isset($_REQUEST['etapes']) && ($_GET['etapes'] == 'opecoefficientreg')) {

                // $sql_noteFac = "SELECT * ,MIN(note) AS min_note, id_matiere AS mat FROM `bg_notes` WHERE id_type_note=4 GROUP BY num_table HAVING (COUNT(note)>2) ORDER BY jury";
                $sql_noteFac = "SELECT * ,MIN(note) AS min_note, id_matiere AS mat FROM `bg_notes`
                WHERE id_type_note=4 AND  id_region=$id_region GROUP BY num_table HAVING (COUNT(note)>2) ORDER BY jury";

                $result_noteFac = bg_query($lien, $sql_noteFac, __FILE__, __LINE__);
                if ($result_noteFac > 0) {
                    $imprimer = "<center><input  type='submit' name='supprimer_matfac' style='width: 20em;  height: 2em;' value='Supprimer les Notes Supplémentaires' onClick=\"window.location='" . generer_url_ecrire('controles') . "&etapes=operation';\"  />
                    <input type='hidden' name='id_region' value='$id_region' /></center>
                    ";
                    $liste = "<form name='' method='POST' action=''>";
                    $liste .= "<center><h4>Candidats ayant plus de 2 Notes facultatives</h4></center><br/>" . $imprimer;
                    $liste .= "<table  class='spip liste'>";
                    $liste .= "<thead><th>Num Jury</th><th>Num Table</th><th>Matière</th><th>Note</th></thead>";
                    while ($row1 = mysqli_fetch_array($result_noteFac)) {
                        $num_tableFac = $row1['num_table'];
                        $id_type_session = $row1['id_type_session'];
                        $id_matiere = $row1['mat'];
                        $id_type_note = $row1['id_type_note'];
                        $jury = $row1['jury'];
                        $noteFac = $row1['min_note'];

                        $sqlMat = "SELECT * FROM `bg_notes` WHERE num_table='$num_tableFac' AND id_type_session=$id_type_session AND id_type_note=4 AND jury=$jury AND note=$noteFac";
                        $resultMat = bg_query($lien, $sqlMat, __FILE__, __LINE__);
                        $matF = mysqli_fetch_array($resultMat);
                        $id_matiere = $matF['id_matiere'];

                        //  echo debut_boite_alerte();

                        $liste .= "<tbody><tr><td>" . $jury . "</td><td>" . $num_tableFac . "</td><td>" . get_matiere_by_id($lien, $id_matiere) . "</td><td>" . $noteFac . "</td></tr></tbody>";

                        //  echo fin_boite_alerte();

                    }
                    $liste .= "</table></form>";
                    echo $liste;

                } else {echo "<h2 style='color:red';><center>Aucun Candidat avec Notes de matiere facultative supérieure à 2 détecté</center></h2>";}

            }
            if (isset($_REQUEST['etapes']) && ($_REQUEST['etapes'] == 'operation')) {

                $sql = "SELECT num_table, COUNT(*) as nb_tab FROM bg_notes_performance WHERE id_atelier!=7 GROUP BY num_table HAVING COUNT(*) >2";
                $result = bg_query($lien, $sql, __FILE__, __LINE__);
                echo "<a href='" . generer_url_ecrire('controles') . "&tache=sportsurplus'><center><b>RETOUR A LA PAGE PRECEDENTE</b></center></a><br/>";
                if (mysqli_num_rows($result) > 0) {
                    $imprimer = "<center><input  type='submit' name='supprimer_notes' style='width: 20em;  height: 2em;' value='Supprimer les Notes Supplémentaires' onClick=\"window.location='" . generer_url_ecrire('controles') . "&etapes=operation';\"  /></center>

                    ";
                    $liste = "<form name='' method='POST' action=''>";
                    $liste .= "<center><h1>Candidats ayant plus de 3 Notes en Sport</h1></center><br/>" . $imprimer;

                    $liste .= "<table border=2>";
                    $liste .= "<th>Numéros</th>";
                    for ($i = 1; $i <= 7; $i++) {
                        $atelier = selectReferentiel($lien, 'atelier', $i);
                        $liste .= "<th>$atelier</th>";
                    }
                    $liste .= "<th>Notes</th>";
                    while ($row = mysqli_fetch_array($result)) {
                        foreach (array('num_table', 'note') as $val) {
                            $$val = $row[$val];
                        }

                        if ($sexe == 1) {
                            $civ = 'Mlle';
                        } else {
                            $civ = 'M.';
                        }

                        $tabNotes = notesEPSParCandidat($lien, $annee, $num_table);
                        $liste .= "<tr><td>$num_table</td>";
                        for ($i = 1; $i <= 7; $i++) {
                            $atelier = selectReferentiel($lien, 'atelier', $i);
                            $performance = $tabNotes[$i]['performance'];
                            $note_perf = $tabNotes[$i]['note_perf'];
                            if ($performance != '') {
                                $affiche = $performance . '[' . $note_perf . ']';
                            } else {
                                $affiche = '';
                            }

                            $liste .= "<td>$affiche</td>";
                        }
                        $liste .= "<td>" . $tabNotes['note'] . "</td></tr>";
                    }
                    $liste .= "</table></form>";
                    echo $liste;
                } else {
                    echo debut_boite_alerte(), "<h2>Aucun candidat pour ces critères</h2>", fin_boite_alerte();
                }
            }

            if (isset($_REQUEST['etapes']) && ($_REQUEST['etapes'] == 'operationreg')) {

                //  $sqls = "SELECT num_table, COUNT(*) as nb_tab FROM bg_notes_performance WHERE id_atelier!=7 GROUP BY num_table HAVING COUNT(*) >2";
                $sql = "SELECT nop.num_table, COUNT(*) as nb_tab FROM bg_notes_performance nop,bg_repartition rep
                WHERE id_atelier!=7 AND rep.numero=nop.num_table AND id_region=$id_region GROUP BY num_table HAVING COUNT(*) >2";
                $result = bg_query($lien, $sql, __FILE__, __LINE__);
                echo "<a href='" . generer_url_ecrire('controles') . "&tache=sportsurplus'><center><b>RETOUR A LA PAGE PRECEDENTE</b></center></a><br/>";
                if (mysqli_num_rows($result) > 0) {
                    $imprimer = "<center><input  type='submit' name='supprimer_notes' style='width: 20em;  height: 2em;' value='Supprimer les Notes Supplémentaires' onClick=\"window.location='" . generer_url_ecrire('controles') . "&etapes=operationreg';\"  />
                    <input type='hidden' name='id_region' value='$id_region' /></center>
                    ";
                    $liste = "<form name='' method='POST' action=''>";
                    $liste .= "<center><h2>Candidats ayant plus de 3 Notes en Sport</h2></center><br/>" . $imprimer;

                    $liste .= "<table border=2>";
                    $liste .= "<th>Numéros</th>";
                    for ($i = 1; $i <= 7; $i++) {
                        $atelier = selectReferentiel($lien, 'atelier', $i);
                        $liste .= "<th>$atelier</th>";
                    }
                    $liste .= "<th>Notes</th>";
                    while ($row = mysqli_fetch_array($result)) {
                        foreach (array('num_table', 'note') as $val) {
                            $$val = $row[$val];
                        }

                        if ($sexe == 1) {
                            $civ = 'Mlle';
                        } else {
                            $civ = 'M.';
                        }

                        $tabNotes = notesEPSParCandidat($lien, $annee, $num_table);
                        $liste .= "<tr><td>$num_table</td>";
                        for ($i = 1; $i <= 7; $i++) {
                            $atelier = selectReferentiel($lien, 'atelier', $i);
                            $performance = $tabNotes[$i]['performance'];
                            $note_perf = $tabNotes[$i]['note_perf'];
                            if ($performance != '') {
                                $affiche = $performance . '[' . $note_perf . ']';
                            } else {
                                $affiche = '';
                            }

                            $liste .= "<td>$affiche</td>";
                        }
                        $liste .= "<td>" . $tabNotes['note'] . "</td></tr>";
                    }
                    $liste .= "</table></form>";
                    echo $liste;
                } else {
                    echo debut_boite_alerte(), "<h2>Aucun candidat pour ces critères</h2>", fin_boite_alerte();
                }
            }
            if (isset($_REQUEST['etapes']) && ($_REQUEST['etapes'] == 'opeinapte')) {

                $sql = "SELECT can.num_table,nep.note FROM bg_candidats can,bg_notes_eps nep
                WHERE can.annee=$annee AND can.eps=2 AND nep.num_table=can.num_table";
                $result = bg_query($lien, $sql, __FILE__, __LINE__);
                echo "<a href='" . generer_url_ecrire('controles') . "&tache=sport'><center><b>RETOUR A LA PAGE PRECEDENTE</b></center></a><br/>";
                if (mysqli_num_rows($result) > 0) {
                    $imprimer = "<center><input  type='submit' name='supprimer_inaptes' style='width: 20em;  height: 2em;' value='Supprimer les Notes Inaptes' onClick=\"window.location='" . generer_url_ecrire('controles') . "&etapes=operation';\"  /></center>
                    ";
                    $liste = "<form name='' method='POST' action=''>";
                    $liste .= "<center><h1>Candidats Inapte ayant une Notes en Sport</h1></center><br/>" . $imprimer;

                    $liste .= "<table border=2>";
                    $liste .= "<th>Numéros</th>";
                    for ($i = 1; $i <= 7; $i++) {
                        $atelier = selectReferentiel($lien, 'atelier', $i);
                        $liste .= "<th>$atelier</th>";
                    }
                    $liste .= "<th>Notes</th>";
                    while ($row = mysqli_fetch_array($result)) {
                        foreach (array('num_table', 'note') as $val) {
                            $$val = $row[$val];
                        }

                        if ($sexe == 1) {
                            $civ = 'Mlle';
                        } else {
                            $civ = 'M.';
                        }

                        $tabNotes = notesEPSParCandidat($lien, $annee, $num_table);
                        $liste .= "<tr><td>$num_table</td>";
                        for ($i = 1; $i <= 7; $i++) {
                            $atelier = selectReferentiel($lien, 'atelier', $i);
                            $performance = $tabNotes[$i]['performance'];
                            $note_perf = $tabNotes[$i]['note_perf'];
                            if ($performance != '') {
                                $affiche = $performance . '[' . $note_perf . ']';
                            } else {
                                $affiche = '';
                            }

                            $liste .= "<td>$affiche</td>";
                        }
                        $liste .= "<td>" . $tabNotes['note'] . "</td></tr>";
                    }
                    $liste .= "</table></form>";
                    echo $liste;
                } else {
                    echo debut_boite_alerte(), "<h2>Aucun candidat pour ces critères</h2>", fin_boite_alerte();
                }
            }

            if (isset($_REQUEST['etapes']) && ($_REQUEST['etapes'] == 'opeinaptereg')) {

                $sql = "SELECT can.num_table,nep.note FROM bg_candidats can,bg_notes_eps nep,bg_repartition rep
                WHERE can.annee=$annee AND can.eps=2 AND nep.num_table=can.num_table AND rep.numero=nep.num_table AND rep.id_region=$id_region";

                //  $sql = "SELECT nop.num_table, COUNT(*) as nb_tab FROM bg_notes_performance nop,bg_repartition rep
                //  WHERE id_atelier!=7 AND rep.numero=nop.num_table AND rep.id_region=$id_region GROUP BY num_table HAVING COUNT(*) >2";
                $result = bg_query($lien, $sql, __FILE__, __LINE__);
                echo "<a href='" . generer_url_ecrire('controles') . "&tache=sportinapte'><center><b>RETOUR A LA PAGE PRECEDENTE</b></center></a><br/>";
                if (mysqli_num_rows($result) > 0) {
                    $imprimer = "<center><input  type='submit' name='supprimer_inaptes' style='width: 20em;  height: 2em;' value='Supprimer les Notes Inaptes' onClick=\"window.location='" . generer_url_ecrire('controles') . "&etapes=operation';\"  />
                    <input type='hidden' name='id_region' value='$id_region' />
                    </center>
                    ";
                    $liste = "<form name='' method='POST' action=''>";
                    $liste .= "<center><h1>Candidats Inapte ayant une Notes en Sport</h1></center><br/>" . $imprimer;

                    $liste .= "<table border=2>";
                    $liste .= "<th>Numéros</th>";
                    for ($i = 1; $i <= 7; $i++) {
                        $atelier = selectReferentiel($lien, 'atelier', $i);
                        $liste .= "<th>$atelier</th>";
                    }
                    $liste .= "<th>Notes</th>";
                    while ($row = mysqli_fetch_array($result)) {
                        foreach (array('num_table', 'note') as $val) {
                            $$val = $row[$val];
                        }

                        if ($sexe == 1) {
                            $civ = 'Mlle';
                        } else {
                            $civ = 'M.';
                        }

                        $tabNotes = notesEPSParCandidat($lien, $annee, $num_table);
                        $liste .= "<tr><td>$num_table</td>";
                        for ($i = 1; $i <= 7; $i++) {
                            $atelier = selectReferentiel($lien, 'atelier', $i);
                            $performance = $tabNotes[$i]['performance'];
                            $note_perf = $tabNotes[$i]['note_perf'];
                            if ($performance != '') {
                                $affiche = $performance . '[' . $note_perf . ']';
                            } else {
                                $affiche = '';
                            }

                            $liste .= "<td>$affiche</td>";
                        }
                        $liste .= "<td>" . $tabNotes['note'] . "</td></tr>";
                    }
                    $liste .= "</table></form>";
                    echo $liste;
                } else {
                    echo debut_boite_alerte(), "<h2>Aucun candidat pour ces critères</h2>", fin_boite_alerte();
                }
            }

            if (isset($_REQUEST['etapes']) && ($_REQUEST['etapes'] == 'opeapte')) {
                echo "<center><h2>Candidats aptes sans Notes de Sport</h2></center> <br/>";

                $sql = "SELECT can.num_table,can.nom,can.prenoms,eta.etablissement,insp.inspection,reg.region
                FROM bg_candidats can,bg_ref_etablissement eta,bg_ref_inspection insp,bg_ref_region reg
                WHERE can.annee=$annee AND can.etablissement=eta.id AND eta.id_inspection=insp.id
                AND insp.id_region=reg.id AND can.eps=1 AND  can.num_table
                NOT IN (SELECT num_table FROM bg_notes_eps )
                ORDER BY reg.region ASC";
                $result = bg_query($lien, $sql, __FILE__, __LINE__);
                $tab = array('num_table', 'etablissement', 'inspection', 'region', 'nom', 'prenoms');

                if (mysqli_num_rows($result) > 0) {
                    echo "<table class='spip liste'>";
                    $thead = "<th>N° de Table</th><th>Nom & Prenoms</th><th>Etablissement</th><th>Inspection</th><th>Region</th>";
                    while ($row_aff = mysqli_fetch_array($result)) {
                        foreach ($tab as $var) {
                            $$var = $row_aff[$var];
                            //Decommenter la ligne precedente et commenter la ligne suivante pour gerer l'encodage sur la lsite des candidats

                        }

                        $tbody .= "<tr> <td>$num_table</td><td> $nom $prenoms </td>
                <td>$etablissement</td><td>$inspection</td><td>$region</td></tr>";

                    }
                    echo $thead, $tbody;
                    echo "</table>";
                } else {
                    echo debut_boite_alerte(), "<h2>Aucun candidat pour ces critères</h2>", fin_boite_alerte();
                }
            }

//             if (isset($_REQUEST['etapes']) && ($_REQUEST['etapes'] == 'opeaptereg')) {

//                 $sql = "SELECT can.num_table,can.nom,can.prenoms,eta.etablissement,insp.inspection,reg.region
            //                 FROM bg_candidats can,bg_ref_etablissement eta,bg_ref_inspection insp,bg_ref_region reg
            //                 WHERE can.annee=$annee AND can.etablissement=eta.id AND eta.id_inspection=insp.id
            //                 AND insp.id_region=reg.id AND can.eps=1 AND  can.num_table AND reg.id=$id_region
            //                 NOT IN (SELECT num_table FROM bg_notes_eps )
            //                 ORDER BY reg.region ASC";
            //                 $result = bg_query($lien, $sql, __FILE__, __LINE__);
            //                 echo "<a href='" . generer_url_ecrire('controles') . "&tache=sport'><center><b>RETOUR A LA PAGE PRECEDENTE</b></center></a><br/>";
            //                 $tab = array('num_table', 'etablissement', 'inspection', 'region', 'nom', 'prenoms');

//                 if (mysqli_num_rows($result) > 0) {
            //                     echo "<table class='spip liste'>";
            //                     $thead = "<th>N° de Table</th><th>Nom & Prenoms</th><th>Etablissement</th><th>Inspection</th><th>Region</th>";
            //                     while ($row_aff = mysqli_fetch_array($result)) {
            //                         foreach ($tab as $var) {
            //                             $$var = $row_aff[$var];
            //                             //Decommenter la ligne precedente et commenter la ligne suivante pour gerer l'encodage sur la lsite des candidats

//                         }

//                         $tbody .= "<tr> <td>$num_table</td><td> $nom $prenoms </td>
            //  <td>$etablissement</td><td>$inspection</td><td>$region</td></tr>";

//                     }
            //                     echo $thead, $tbody;
            //                     echo "</table>";
            //                 } else {
            //                     echo debut_boite_alerte(), "<h2>Aucun candidat pour ces critères</h2>", fin_boite_alerte();
            //                 }
            //             }
            if (isset($_REQUEST['etapes']) && ($_REQUEST['etapes'] == 'saispate') && isset($_REQUEST['id_inspection'])) {
                echo "<div class='onglets_simple clearfix'><ul>";
                $sql = "SELECT * FROM bg_ref_atelier ORDER BY atelier";
                $result = bg_query($lien, $sql, __FILE__, __LINE__);
                while ($row = mysqli_fetch_array($result)) {
                    echo "<li><a href='./?exec=controles&etapes=" . $_REQUEST['etapes'] . "&id_atelier=" . $row['id'] . "&id_inspection=" . $_REQUEST['id_inspection'] . "' class='ajax'>" . $row['atelier'] . "</a></li>";
                }
                echo "<li><a href='./?exec=controles&tache=sport' class='ajax'><b>RETOUR</b></a></li>";
                echo "</ul></div>";
            }

            //Affichage des champs pour la saisie des performances
            if (isset($_REQUEST['etapes']) && ($_REQUEST['etapes'] == 'saispate') && isset($_REQUEST['id_inspection']) && isset($_GET['id_inspection']) && isset($_GET['id_atelier'])) {
                $atelier = selectReferentiel($lien, 'atelier', $id_atelier);
                $inspection = selectReferentiel($lien, 'inspection', $id_inspection);
                echo debut_cadre_enfonce(), "<b>Atelier: $atelier &nbsp;	INSPECTION: $inspection &nbsp;&nbsp;</b> ", fin_cadre_enfonce();

                //Formulaire de modification de la performance de candidat
                if (isset($_GET['num_table']) && isset($_POST['modif'])) {
                    $sql1 = "INSERT INTO bg_histo_notes_performance (`num_table`, `annee`, `id_atelier`, `performance`, `note_perf`, `maj`, `login`)
				SELECT `num_table`, `annee`, `id_atelier`, `performance`, `note_perf`, `maj`, `login`
				FROM bg_notes_performance WHERE annee=$annee AND num_table='$num_table' AND id_atelier='$id_atelier' ";
                    bg_query($lien, $sql1, __FILE__, __LINE__);
                    if ($performance2 != '') {
                        if ($performance2 < 0 || $performance2 == '-' || $performance2 == 0) {
                            $performance2 = -1;
                        }

                        if (in_array($id_atelier, array('5', '6'))) {$performance2 .= ':' . $_POST['performance22'];}
                        if (in_array($id_atelier, array('3'))) {$performance2 .= ':' . str_pad($_POST['performance22'], 2, 0, STR_PAD_LEFT) . ':' . $_POST['performance23'];}

                        $note_perf = getNoteParAtelier($lien, $id_atelier, $id_sexe, $performance2);

                        $sql2 = "REPLACE INTO bg_notes_performance (num_table, annee, id_atelier, performance, note_perf) VALUES ('$num_table','$annee','$id_atelier','$performance2','$note_perf') ";
                        bg_query($lien, $sql2, __FILE__, __LINE__);

                        $sql3 = "REPLACE INTO bg_notes_eps (num_table,annee,note) (SELECT num_table, annee, SUM(note_perf)/3 note FROM bg_notes_performance WHERE annee=$annee AND num_table='$num_table')";
                        bg_query($lien, $sql3, __FILE__, __LINE__);
                    }
                }

                if (isset($_GET['num_table']) && !isset($_POST['modif'])) {
                    $tabNP = getPerformanceEPS($lien, $annee, $num_table, $id_atelier);
                    $tCan = getNoms($lien, $annee, $num_table);
                    $noms = $tCan['noms'];
                    $id_sexe = $tCan['sexe'];
                    $performance1 = $tabNP['performance'];
                    if ($performance1 <= 0) {$performance1 = "-";
                        $remarque = "<font color='blue'>ABSENT</font>";} else { $remarque = '';}
                    echo debut_cadre_enfonce();
                    $formModNote = "<form name='' action='' method='POST'><table>";

                    if (in_array($id_atelier, array('5', '6', '3')) && $performance1 != '') {
                        $tabPerf = explode(':', $performance1);
                        $perfor1 = $tabPerf[0];
                        $perfor2 = $tabPerf[1];
                        $perfor3 = $tabPerf[2];
                    } else {
                        $perfor1 = $perfor2 = $perfor3 = '';
                    }

                    if (in_array($id_atelier, array('1', '4'))) {
                        $input = "<input type='number' name='performance2' id='performance2' value='$performance1' selected size=5 maxlength=10 onKeyUp=\"if(this.value!='-' && isNaN(parseInt(this.value))) this.value='';\" autofocus />";
                    } elseif (in_array($id_atelier, array('2'))) {
                        $input = "<input type='number' step='any' min='0' max='14' name='performance2' id='performance2' value='$performance1' selected size=5 maxlength=10 onKeyUp=\"if(this.value!='-' && isNaN(parseInt(this.value))) this.value='';\" autofocus />";
                    } elseif (in_array($id_atelier, array('5', '6'))) {
                        $input = "<input type='text' min=0 max=59 id='performance2' name='performance2' value=\"$perfor1\" size=1 maxlength=2 onKeyUp=\"if((this.value!='-' && isNaN(parseInt(this.value))) || this.value>59) this.value='';\" autofocus /> : <input type='text' min=0 max=9 id='performance22' name='performance22' value=\"$perfor2\" size=1 maxlength=1 onKeyUp=\"if(this.value!='-' && isNaN(parseInt(this.value))) this.value='';\" autofocus />";
                    } elseif (in_array($id_atelier, array('3'))) {
                        $input = "<input type='text' min=0 max=9 id='performance2' name='performance2' value=\"$perfor1\" size=1 maxlength=1 onKeyUp=\"if(this.value!='-' && isNaN(parseInt(this.value))) this.value='';\" autofocus /> : <input type='text' min=0 max=59 id='performance22' name='performance22' value=\"$perfor2\" size=2 maxlength=2 onKeyUp=\"if((this.value!='-' && isNaN(parseInt(this.value))) || this.value>59) this.value='';\" autofocus /> : <input type='text' min=0 max=9 id='performance23' name='performance23' value=\"$perfor3\" size=1 maxlength=1 onKeyUp=\"if(this.value!='-' && isNaN(parseInt(this.value))) this.value='';\" autofocus />";
                    }

                    $formModNote .= "<th>Candidat : $num_table $noms </th><th>$input $remarque</th><th><input type='hidden' name='modif' /><input type='submit' name='Modifier_note' value='OK' /><input type='hidden' name='id_sexe' value='$id_sexe' /></th>";
                    $formModNote .= "</table></form>";
                    echo $formModNote;
                    echo fin_cadre_enfonce();

                }

                //  if (isset($_REQUEST['l']) && $l != '') {$limit = " LIMIT $l, $diviseur";}
                $sql = "SELECT eta.id as id_etablissement, eta.etablissement, can.num_table, nom, prenoms, sexe as id_sexe
				FROM bg_repartition rep, bg_candidats can, bg_ref_etablissement eta,bg_ref_inspection ins
				WHERE can.annee=$annee AND rep.annee=$annee AND can.num_table=rep.num_table
				AND can.etablissement=eta.id AND eta.id_inspection=ins.id AND ins.id=$id_inspection
				AND (can.atelier1=$id_atelier OR can.atelier2=$id_atelier OR can.atelier3=$id_atelier)
				ORDER BY can.num_table, can.nom, prenoms $limit ";
                $result = bg_query($lien, $sql, __FILE__, __LINE__);
                $unite = getUniteAtelier($lien, $id_atelier);
                $formNotes = "<form name='' method='POST' action=''>";
                $formNotes .= "<table>";
                $formNotes .= "<th>Candidat</th><th>Numéro de table</th><th>Performance <br/>($unite)</th><th>Note</th>";
                while ($row = mysqli_fetch_array($result)) {
                    foreach (array('id_etablissement', 'etablissement', 'nom', 'prenoms', 'num_table', 'id_sexe') as $val) {
                        $$val = $row[$val];
                    }

                    $sql_ver = "SELECT * FROM bg_notes_performance WHERE annee='$annee' AND num_table='$num_table' AND id_atelier='$id_atelier' ";
                    $result_ver = bg_query($lien, $sql_ver, __FILE__, __LINE__);
                    if (mysqli_num_rows($result_ver) == 1) {
                        $row_ver = mysqli_fetch_array($result_ver);
                        $performance = $row_ver['performance'];
                        $note_perf = $row_ver['note_perf'];
                        $disabled = 'disablede';
                    } else {
                        $performance = $note_perf = '';
                        $disabled = '';
                        $performance = '';
                    }
                    if ($performance < 0) {$performance = "N/C";
                        $remarque = "<font color='blue'>ABSENT</font>";} else { $remarque = '';}

                    //Format des champs
                    if (in_array($id_atelier, array('5', '6', '3')) && $performance != '') {
                        $tabPerf = explode(':', $performance);
                        $performance1 = $tabPerf[0];
                        $performance2 = $tabPerf[1];
                        $performance3 = $tabPerf[2];
                    } else {
                        $performance1 = $performance2 = $performance3 = '';
                    }
                    if (in_array($id_atelier, array('1', '4'))) {
                        $input = "<input type='number' id='tPerf[$num_table]' name='tPerf[$num_table]' value=\"$performance\" $disabled size=5 maxlength=5 onKeyUp=\"if(this.value!='-' && isNaN(parseInt(this.value))) this.value='';\" autofocus />";
                    } elseif (in_array($id_atelier, array('2'))) {
                        $input = "<input type='number' step='any' min='0' max='14' id='tPerf[$num_table]' name='tPerf[$num_table]' value=\"$performance\" $disabled size=5 maxlength=5 onKeyUp=\"if(this.value!='-' && isNaN(parseInt(this.value))) this.value='';\" autofocus />";
                    } elseif (in_array($id_atelier, array('5', '6'))) {
                        $input = "<input type='text' min=0 max=59 id='tPerf[$num_table]' name='tPerf[$num_table]' value=\"$performance1\" $disabled size=1 maxlength=2 onKeyUp=\"if((this.value!='-' && isNaN(parseInt(this.value))) || this.value>59) this.value=''; else {if (this.value.length == 2 ) { document.getElementById('tPerf2[$num_table]').focus();}} \" autofocus  /> : <input type='text' min=0 max=9 id='tPerf2[$num_table]' name='tPerf2[$num_table]' value=\"$performance2\" $disabled size=1 maxlength=1 onKeyUp=\"if(this.value!='-' && isNaN(parseInt(this.value))) this.value='';\" />";
                    } elseif (in_array($id_atelier, array('3'))) {
                        $input = "<input type='text' min=0 max=9 id='tPerf[$num_table]' name='tPerf[$num_table]' value=\"$performance1\" $disabled size=1 maxlength=1 onKeyUp=\"if(this.value!='-' && isNaN(parseInt(this.value))) this.value=''; else {if (this.value.length == 1 ) { document.getElementById('tPerf2[$num_table]').focus();}} \" autofocus /> : <input type='text' min=0 max=59 id='tPerf2[$num_table]' name='tPerf2[$num_table]' value=\"$performance2\" $disabled size=2 maxlength=2 onKeyUp=\"if((this.value!='-' && isNaN(parseInt(this.value))) || this.value>59) this.value=''; else {if (this.value.length == 2 ) { document.getElementById('tPerf3[$num_table]').focus();}} \" /> : <input type='text' min=0 max=9 id='tPerf3[$num_table]' name='tPerf3[$num_table]' value=\"$performance3\" $disabled size=1 maxlength=1 onKeyUp=\"if(this.value!='-' && isNaN(parseInt(this.value))) this.value='';\" />";
                    } elseif (in_array($id_atelier, array('7'))) {
                        $input = "<input type='number' id='tPerf[$num_table]' name='tPerf[$num_table]' value=\"$performance\" $disabled size=5 maxlength=5 onKeyUp=\"if(this.value!='-' && isNaN(parseInt(this.value))) this.value='';\" autofocus />";
                    }

                    if ($statut != 'Admin') {
                        // $note_perf2 = '';
                        $note_perf2 = $note_perf;
                    } else {
                        $note_perf2 = $note_perf;
                    }

                    $formNotes .= "<tr><td><b><a tabindex='-1' href='" . generer_url_ecrire('controles') . "&etapes=saispate&id_atelier=$id_atelier&id_etablissement=$id_etablissement&num_table=$num_table&id_region=$id_region'>$num_table</a></b></td><td><a tabindex='-1' href='" . generer_url_ecrire('controles') . "&etapes=saispate&id_atelier=$id_atelier&id_etablissement=$id_etablissement&num_table=$num_table&id_region=$id_region'>$nom $prenoms</a></td>
					<td><b>$input</b></td>
					<td><b><input type='text' disabled value='$note_perf2' size=1/> $remarque</b></td></tr>";
                    $formNotes .= "<tr><td colspan=3><input type='hidden' name='tSexe[$num_table]' value=\"$id_sexe\"  /></td></tr>";
                }
                $formNotes .= "<tr><td><input type='hidden' name='id_atelier' value='$id_atelier'/></td><td><input type='hidden' name='id_inspection' value='$id_inspection' /></td><td></td><td></td></tr>";
                $formNotes .= "<tr><td colspan=2><center><input type='submit' class='spip' value='Enregistrer' name='enregistrer_perf' /></center></td><td colspan=2><center><input type='reset' value='Quitter' onclick=window.location='" . generer_url_ecrire('controles') . "&etapes=saispate&id_atelier=$id_atelier&id_inspection=$id_inspection' /></center></td></tr>";
                $formNotes .= "</table></form>";
                echo $formNotes;
            }
            if (isset($_POST['enregistrer_perf'])) {
                foreach ($_POST['tPerf'] as $num_table => $perf) {
                    if ($perf != '') {
                        if ($perf < 0 || $perf == '-' || $perf == 0) {
                            $perf = -1;
                        }

                        if ($_POST['tSexe'][$num_table] == 1) {
                            $sexe = 1;
                        } else {
                            $sexe = 2;
                        }

                        if (in_array($id_atelier, array('5', '6'))) {$perf .= ':' . $_POST['tPerf2'][$num_table];}
                        if (in_array($id_atelier, array('3'))) {$perf .= ':' . str_pad($_POST['tPerf2'][$num_table], 2, 0, STR_PAD_LEFT) . ':' . $_POST['tPerf3'][$num_table];}

                        $note_perf = getNoteParAtelier($lien, $id_atelier, $sexe, $perf);

                        $sql2 = "REPLACE INTO bg_notes_performance (num_table, annee, id_atelier, performance, note_perf) VALUES ('$num_table','$annee','$id_atelier','$perf','$note_perf') ";
                        bg_query($lien, $sql2, __FILE__, __LINE__);

                        $sql3 = "REPLACE INTO bg_notes_eps (num_table,annee,note) SELECT num_table, annee, SUM(note_perf)/3 note FROM bg_notes_performance WHERE annee=$annee AND num_table='$num_table' ";
                        bg_query($lien, $sql3, __FILE__, __LINE__);
                    }
                }
            }
            if (isset($_POST['supprimer_matfac'])) {
                $id_region = $_POST['id_region'];
                if ($id_region == null) {
                    $sql_noteFac = "SELECT * ,MIN(note) AS min_note, id_matiere AS mat FROM `bg_notes` WHERE id_type_note=4 GROUP BY num_table HAVING (COUNT(note)>2)";
                } else {
                    $sql_noteFac = "SELECT * ,MIN(note) AS min_note, id_matiere AS mat FROM `bg_notes`
                    WHERE id_type_note=4 AND id_region=$id_region GROUP BY num_table HAVING (COUNT(note)>2)";
                }

                //$sql_noteFac = "SELECT * ,MIN(note) AS min_note, id_matiere AS mat FROM `bg_notes` WHERE id_type_note=4 GROUP BY num_table HAVING (COUNT(note)>2)";
                $result_noteFac = bg_query($lien, $sql_noteFac, __FILE__, __LINE__);
                if ($result_noteFac > 0) {
                    while ($row1 = mysqli_fetch_array($result_noteFac)) {
                        $num_tableFac = $row1['num_table'];
                        $id_type_session = $row1['id_type_session'];
                        $id_matiere = $row1['mat'];
                        $id_type_note = $row1['id_type_note'];
                        $jury = $row1['jury'];
                        $noteFac = $row1['min_note'];

                        $sqlMat = "SELECT * FROM `bg_notes` WHERE num_table='$num_tableFac' AND id_type_session=$id_type_session AND id_type_note=4 AND jury=$jury AND note=$noteFac";
                        $resultMat = bg_query($lien, $sqlMat, __FILE__, __LINE__);
                        $matF = mysqli_fetch_array($resultMat);
                        $id_matiere = $matF['id_matiere'];

                        echo debut_boite_alerte();

                        $sqlFac2 = "DELETE FROM `bg_notes` WHERE num_table='$num_tableFac' AND id_type_session=$id_type_session AND id_matiere=$id_matiere AND id_type_note=$id_type_note AND jury=$jury AND note=$noteFac";
                        // $sqlFac2 = "UPDATE `bg_notes` SET note=NULL WHERE num_table='$num_tableFac' AND id_type_session=$id_type_session AND id_matiere=$id_matiere AND id_type_note=$id_type_note AND jury=$jury AND note=$noteFac";
                        $resultRep1 = bg_query($lien, $sqlFac2, __FILE__, __LINE__);

                        if ($resultMat) {
                            echo "  Opération de Suppression de la note du Numéro de Table <strong>" . $num_tableFac . "</strong> de la matière <strong>" . get_matiere_by_id($lien, $id_matiere) . "</strong> éffectuée avec succès !!!!";
                        }
                        echo fin_boite_alerte();
                    }
                } else {
                    echo "<h2 style='color:red';><center>Tous Les candidats ayants déja une note dans plus de deux matières facultatives ont déja été supprimés </center></h2>";
                    echo "<h2 style='color:red';><center>Ou Il n'exciste plus de candidat dont le nombre de matières facultative pris est supérieure a 2</center></h2>";
                }
            }

            if (isset($_POST['transferereps'])) {
                $num_table_initial = $_POST['numero_initial'];
                $num_table_final = $_POST['numero_final'];

                $sql_eps = "SELECT `num_table`, `annee`, `id_atelier`, `performance`, `note_perf`, `maj`, `login` FROM bg_notes_performance
                WHERE annee=$annee AND num_table='$num_table_initial' ";
                // $sql_eps = "SELECT `num_table`, `annee`, `note`
                // FROM bg_notes_performance WHERE annee=$annee AND num_table='$num_table_initial' ";
                $result_eps = bg_query($lien, $sql_eps, __FILE__, __LINE__);

                if ($result_eps > 0) {
                    while ($row1 = mysqli_fetch_array($result_eps)) {
                        $num_table_eps = $row1['num_table'];
                        $note_perf = $row1['note_perf'];
                        $annee = $row1['annee'];
                        $id_atelier = $row1['id_atelier'];
                        $performance = $row1['performance'];
                        $maj = $row1['maj'];
                        $login = $row1['login'];

                        $sql_ins = "INSERT INTO bg_notes_performance (`num_table`, `annee`, `id_atelier`, `performance`, `note_perf`, `maj`, `login`)
				VALUES ('$num_table_final','$annee','$id_atelier','$performance','$note_perf','$maj','$login') ";

                        bg_query($lien, $sql_ins, __FILE__, __LINE__);

                        $sql3 = "REPLACE INTO bg_notes_eps (num_table,annee,note) SELECT num_table, annee, SUM(note_perf)/3 note FROM bg_notes_performance WHERE annee=$annee AND num_table='$num_table_final' ";
                        bg_query($lien, $sql3, __FILE__, __LINE__);

                    }
                    echo debut_boite_info(), "Opération réussie:
                    Les notes de sport du candidat <strong>$num_table_final</strong> ont été transféré avec succès ", fin_boite_info();
                    getAllNotesEps($lien, $num_table_final, $annee);
                } else {
                    echo "<h2 style='color:red';><center>Tous Les candidats ayants déja une note dans plus de deux matières facultatives ont déja été supprimés </center></h2>";
                    echo "<h2 style='color:red';><center>Ou Il n'exciste plus de candidat dont le nombre de matières facultative pris est supérieure a 2</center></h2>";
                }

            }

            if (isset($_POST['supprimer_notes'])) {
                $id_region = $_POST['id_region'];
                if ($id_region == null) {
                    $sql_notePerf = "SELECT MIN(note_perf) AS min_note,num_table FROM bg_notes_performance WHERE id_atelier!=7 GROUP BY num_table HAVING (COUNT(note_perf) >2)";
                } else {
                    $sql_notePerf = "SELECT MIN(nop.note_perf) AS min_note,nop.num_table FROM bg_notes_performance nop, bg_repartition rep
                    WHERE id_atelier!=7 AND rep.numero=nop.num_table AND id_region=$id_region GROUP BY num_table HAVING (COUNT(note_perf) >2)";

                }
                //  $sql_notePerf = "SELECT * ,MIN(note) AS min_note, id_matiere AS mat FROM bg_notes_performance  GROUP BY num_table HAVING (COUNT(note)>3)";
                $result_notePerf = bg_query($lien, $sql_notePerf, __FILE__, __LINE__);
                if ($result_notePerf > 0) {
                    while ($row1 = mysqli_fetch_array($result_notePerf)) {
                        $num_tablePerf = $row1['num_table'];
                        $notePerf = $row1['min_note'];

                        $sqlMat = "SELECT * FROM bg_notes_performance WHERE num_table='$num_tablePerf' AND note_perf=$notePerf";
                        $resultMat = bg_query($lien, $sqlMat, __FILE__, __LINE__);
                        $matF = mysqli_fetch_array($resultMat);
                        // $id_matiere = $matF['id_matiere'];

                        // echo debut_boite_alerte();

                        $sqlFac2 = "DELETE FROM bg_notes_performance WHERE num_table='$num_tablePerf' AND note_perf=$notePerf";
                        $resultRep1 = bg_query($lien, $sqlFac2, __FILE__, __LINE__);

                        $sql3 = "REPLACE INTO bg_notes_eps (num_table,annee,note) SELECT num_table, annee, SUM(note_perf)/3 note FROM bg_notes_performance WHERE annee=$annee AND num_table='$num_tablePerf' ";
                        bg_query($lien, $sql3, __FILE__, __LINE__);

                        // if ($resultRep1) {
                        //     echo "  Opération de Suppression de la note du Numéro de Table <strong>" . $num_tablePerf . "</strong> de la matière <strong>Suppression ok</strong> éffectuée avec succès !!!!";
                        // }

                        //  echo fin_boite_alerte();
                    }
                } else {
                    echo "<h2 style='color:red';><center>Tous Les candidats ayants déja une note dans plus de deux matières facultatives ont déja été supprimés </center></h2>";
                    echo "<h2 style='color:red';><center>Ou Il n'exciste plus de candidat dont le nombre de matières facultative pris est supérieure a 2</center></h2>";
                }

            }

            if (isset($_POST['supprimer_inaptes'])) {
                $id_region = $_POST['id_region'];
                if ($id_region == null) {
                    $sql_noteOps = "SELECT can.num_table,nep.note_perf FROM bg_candidats can,bg_notes_performance nep
                WHERE can.annee=$annee AND can.eps=2 AND nep.num_table=can.num_table";
                } else {
                    $sql_noteOps = "SELECT can.num_table,nep.note_perf FROM bg_candidats can,bg_notes_performance nep, bg_repartition rep
                        WHERE can.annee=$annee AND can.eps=2 AND nep.num_table=can.num_table AND rep.numero=nep.num_table AND id_region=$id_region ";

                }

                $result_noteOps = bg_query($lien, $sql_noteOps, __FILE__, __LINE__);
                if ($result_noteOps > 0) {
                    while ($row1 = mysqli_fetch_array($result_noteOps)) {
                        $num_tableOps = $row1['num_table'];
                        $noteOps = $row1['note_perf'];

                        $sqlMat = "SELECT * FROM bg_notes_performance WHERE num_table='$num_tableOps' AND note_perf=$noteOps";
                        $resultMat = bg_query($lien, $sqlMat, __FILE__, __LINE__);
                        $matF = mysqli_fetch_array($resultMat);
                        // $id_matiere = $matF['id_matiere'];

                        // echo debut_boite_alerte();

                        $sqlOps1 = "DELETE FROM bg_notes_performance WHERE num_table='$num_tableOps' AND note_perf=$noteOps";
                        $resultRep1 = bg_query($lien, $sqlOps1, __FILE__, __LINE__);

                        $sqlOps2 = "DELETE FROM bg_notes_eps WHERE num_table='$num_tableOps'";
                        $resultRep1 = bg_query($lien, $sqlOps2, __FILE__, __LINE__);

                        // $sql3 = "REPLACE INTO bg_notes_eps (num_table,annee,note) SELECT num_table, annee, SUM(note_perf)/3 note FROM bg_notes_performance WHERE annee=$annee AND num_table='$num_tablePerf' ";
                        // bg_query($lien, $sql3, __FILE__, __LINE__);

                        // if ($resultRep1) {
                        //     echo "  Opération de Suppression de la note du Numéro de Table <strong>" . $num_tablePerf . "</strong> de la matière <strong>Suppression ok</strong> éffectuée avec succès !!!!";
                        // }

                        //  echo fin_boite_alerte();
                    }
                } else {
                    echo "<h2 style='color:red';><center>Tous Les candidats ayants déja une note dans plus de deux matières facultatives ont déja été supprimés </center></h2>";
                    echo "<h2 style='color:red';><center>Ou Il n'exciste plus de candidat dont le nombre de matières facultative pris est supérieure a 2</center></h2>";
                }

            }

            echo fin_gauche();
            echo fin_gauche();

        }

        echo fin_cadre_trait_couleur();
        echo fin_grand_cadre(), fin_page();
    }

}

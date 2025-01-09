<?php
if (!defined('_ECRIRE_INC_VERSION')) {
    return;
}

include_spip('inc/presentation'); // alleger les inclusions avec un inc/presentation_mini
include '../plugins/fonctions/generales.php';

setlocale(LC_TIME, "fr_FR");
define('MAX_LIGNES', 100);

//Affichage de la region avec l'id comme paramètre
function selectRegion($lien, $id_region)
{
    $sql = "SELECT region FROM bg_ref_region WHERE id='$id_region' ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $row = mysqli_fetch_array($result);
    return $row['region'];
}

function tendancesRegion($lien, $annee, $id_region)
{
    $region = selectRegion($lien, $id_region);
    if ($id_region == -1) {
        $region = 'NATIONAL';
    }
    echo debut_cadre_enfonce("../images/logo.png", true, '', "TENDANCE PAR SERIE '$region'");
    if ($id_region != -1) {
        $andWhere = " AND res.id_region=$id_region ";
    } else {
        $andWhere = '';
        $region = 'NATIONAL';
    }

    $sql = "SELECT ser.serie,
    COUNT(IF(moyenne>0,moyenne,NULL)) AS Present,
    COUNT(res.id_anonyme) AS 'Inscrit',
    COUNT(IF(moyenne>=10,moyenne,NULL)) AS Admis,
    COUNT(IF(moyenne>=9,moyenne,NULL)) AS '9,00',
    COUNT(IF(moyenne>=8.75,moyenne,NULL)) AS '8.75',
    COUNT(IF(moyenne>=8.5,moyenne,NULL)) AS '8.5',
    COUNT(IF(moyenne>=8.25,moyenne,NULL)) AS '8.25',
    COUNT(IF(moyenne>=8,moyenne,NULL)) AS '8',
    COUNT(IF(moyenne>=7.75,moyenne,NULL)) AS '7.75',
    COUNT(IF(moyenne>=7.5,moyenne,NULL)) AS '7.5',
    COUNT(IF(moyenne>=7.25,moyenne,NULL)) AS '7.25',
    COUNT(IF(moyenne>=7,moyenne,NULL)) AS '7',
    COUNT(IF(moyenne>=6.75,moyenne,NULL)) AS '6.75',
    COUNT(IF(moyenne>=6.5,moyenne,NULL)) AS '6.5',
    COUNT(IF(moyenne>6,moyenne,NULL)) AS '6-',
    COUNT(IF(moyenne<=0,moyenne,NULL)) AS Abs,
    COUNT(IF(delib1='Fraude',delib1,NULL)) AS Exclu
    FROM bg_resultats AS res
    JOIN bg_ref_region reg ON reg.id = res.id_region
    JOIN bg_ref_serie ser ON ser.id = res.id_serie
    $andWhere
    GROUP BY ser.serie
    UNION ALL
    SELECT 'TOTAL',
    COUNT(IF(moyenne>0,moyenne,NULL)) AS Present,
    COUNT(res.id_anonyme) AS 'Inscrit',
    COUNT(IF(moyenne>=10,moyenne,NULL)) AS Admis,
    COUNT(IF(moyenne>=9,moyenne,NULL)) AS '9,00',
    COUNT(IF(moyenne>=8.75,moyenne,NULL)) AS '8.75',
    COUNT(IF(moyenne>=8.5,moyenne,NULL)) AS '8.5',
    COUNT(IF(moyenne>=8.25,moyenne,NULL)) AS '8.25',
     COUNT(IF(moyenne>=8,moyenne,NULL)) AS '8',
    COUNT(IF(moyenne>=7.75,moyenne,NULL)) AS '7.75',
    COUNT(IF(moyenne>=7.5,moyenne,NULL)) AS '7.5',
    COUNT(IF(moyenne>=7.25,moyenne,NULL)) AS '7.25',
    COUNT(IF(moyenne>=7,moyenne,NULL)) AS '7',
    COUNT(IF(moyenne>=6.75,moyenne,NULL)) AS '6.75',
    COUNT(IF(moyenne>=6.5,moyenne,NULL)) AS '6.5',
    COUNT(IF(moyenne>6,moyenne,NULL)) AS '6-',
    COUNT(IF(moyenne<=0,moyenne,NULL)) AS Abs,
	COUNT(IF(delib1='Fraude',delib1,NULL)) AS Exclu
    FROM bg_resultats AS res
    JOIN bg_ref_region reg ON reg.id = res.id_region
    JOIN bg_ref_serie ser ON ser.id = res.id_serie
    $andWhere";

    /* $sql = "SELECT ser.serie,
    COUNT(IF(moyenne>0,moyenne,NULL)) AS Present,
	COUNT(can.num_table) AS 'Inscrit',
    COUNT(IF(moyenne>=10,moyenne,NULL)) AS Admis,
    COUNT(IF(moyenne>=9,moyenne,NULL)) AS '9,00',
    COUNT(IF(moyenne>=8.75,moyenne,NULL)) AS '8.75',
    COUNT(IF(moyenne>=8.5,moyenne,NULL)) AS '8.5',
    COUNT(IF(moyenne>=8.25,moyenne,NULL)) AS '8.25',
    COUNT(IF(moyenne>=8,moyenne,NULL)) AS '8',
    COUNT(IF(moyenne>=7.75,moyenne,NULL)) AS '7.75',
    COUNT(IF(moyenne>=7.5,moyenne,NULL)) AS '7.5',
    COUNT(IF(moyenne>=7.25,moyenne,NULL)) AS '7.25',
    COUNT(IF(moyenne>=7,moyenne,NULL)) AS '7',
    COUNT(IF(moyenne>=6.75,moyenne,NULL)) AS '6.75',
    COUNT(IF(moyenne>=6.5,moyenne,NULL)) AS '6.5',
    COUNT(IF(moyenne>6,moyenne,NULL)) AS '6-',
    COUNT(IF(moyenne<=0,moyenne,NULL)) AS Abs,
    COUNT(IF(delib1='Fraude',delib1,NULL)) AS Exclu
    FROM bg_resultats as res
    JOIN bg_repartition rep
    on rep.id_anonyme=res.id_anonyme
    JOIN bg_candidats can
    on can.num_table=rep.num_table
    JOIN bg_ref_etablissement etab
    on etab.id=can.etablissement
    JOIN bg_ref_region_division reg
    on reg.id=etab.id_region_division
    JOIN bg_ref_serie ser
    on ser.id=can.serie $andWhere
    GROUP BY can.serie
    UNION ALL
    SELECT 'TOTAL',
    COUNT(IF(moyenne>0,moyenne,NULL)) AS Present,
	COUNT(can.num_table) AS 'Inscrit',
    COUNT(IF(moyenne>=10,moyenne,NULL)) AS Admis,
    COUNT(IF(moyenne>=9,moyenne,NULL)) AS '9,00',
    COUNT(IF(moyenne>=8.75,moyenne,NULL)) AS '8.75',
    COUNT(IF(moyenne>=8.5,moyenne,NULL)) AS '8.5',
    COUNT(IF(moyenne>=8.25,moyenne,NULL)) AS '8.25',
    COUNT(IF(moyenne>=8,moyenne,NULL)) AS '8',
    COUNT(IF(moyenne>=7.75,moyenne,NULL)) AS '7.75',
    COUNT(IF(moyenne>=7.5,moyenne,NULL)) AS '7.5',
    COUNT(IF(moyenne>=7.25,moyenne,NULL)) AS '7.25',
    COUNT(IF(moyenne>=7,moyenne,NULL)) AS '7',
    COUNT(IF(moyenne>=6.75,moyenne,NULL)) AS '6.75',
    COUNT(IF(moyenne>=6.5,moyenne,NULL)) AS '6.5',
    COUNT(IF(moyenne>6,moyenne,NULL)) AS '6-',
    COUNT(IF(moyenne<=0,moyenne,NULL)) AS Abs,
	COUNT(IF(delib1='Fraude',delib1,NULL)) AS Exclu
    FROM bg_resultats as res
    JOIN bg_repartition rep
    on rep.id_anonyme=res.id_anonyme
    JOIN bg_candidats can
    on can.num_table=rep.num_table
	JOIN bg_ref_etablissement etab
    on etab.id=can.etablissement
    JOIN bg_ref_region_division reg
    on reg.id=etab.id_region_division
    JOIN bg_ref_serie ser
    on ser.id=can.serie $andWhere"; */
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    //$tableau = "<table class='spip liste'><caption class='center'><a href='../plugins/fonctions/inc-pdf-tendance.php?id_region=$id_region&tache=affichage$andLien' target='_blank()'>IMPRIMER EN PDF</a></caption><thead>";

    echo debut_cadre_enfonce(), "<center><a href='../plugins/fonctions/inc-pdf-tendance.php?id_region=$id_region&tache=affichage$andLien' target='_blank()'>IMPRIMER EN PDF</a></center> <hr/> <center><a href='../plugins/fonctions/inc-pdf-tendance-sexe.php?id_region=$id_region&tache=affichage$andLien' target='_blank()'>IMPRIMER SELON SEXE EN PDF</a></center> ", fin_cadre_enfonce();
    $tableau = "<table class='spip liste'><thead>";
    $tableau .= "<th>Série</th><th>Inscrits</th><th>Present</th><th>Admis</th><th>9,00</th><th>8.75</th><th>8.5</th><th>8.25</th><th>8</th><th>7.75</th><th>7.5</th><th>7.25</th><th>7.00</th><th>6.75</th><th>6.5</th><th>6<</th><th>Abs.</th><th>Exclu.</th>";
    $tableau .= "</thead><tbody>";
    $tab = array('serie','Inscrit','Present', 'Admis', '9,00', '8.75', '8.5', '8.25', '8', '7.75', '7.5', '7.25', '7', '6.75', '6.5', '6-', 'Abs', 'Exclu');
    while ($row = mysqli_fetch_array($result)) {
        foreach ($tab as $var) {
            $$var = $row[$var];
        }
        $tableau .= "<tr>"
        . "<td>" . $row['serie'] . "</td>"
        . "<td>" . $row['Inscrit'] . "</td>"
        . "<td>" . $row['Present'] . "</td>"
        . "<td>" . $row['Admis'] . "<br><i><font size='1'><b>" . number_format((($row['Admis'] * 100) / $row['Present']), 2, ',', '') . "%</b></font></i>" . "</td>"
        . "<td>" . $row['9,00'] . "<br><i><font size='1'><b>" . number_format((($row['9,00'] * 100) / $row['Present']), 2, ',', '') . "%</b></font></i>" . "</td>"
        . "<td>" . $row['8.75'] . "<br><i><font size='1'><b>" . number_format((($row['8.75'] * 100) / $row['Present']), 2, ',', '') . "%</b></font></i> " . "</td>"
        . "<td>" . $row['8.5'] . "<br><i><font size='1'><b>" . number_format((($row['8.5'] * 100) / $row['Present']), 2, ',', '') . "%</b></font></i> " . "</td>"
        . "<td>" . $row['8.25'] . "<br><i><font size='1'><b>" . number_format((($row['8.25'] * 100) / $row['Present']), 2, ',', '') . "%</b></font></i> " . "</td>"
        . "<td>" . $row['8'] . "<br><i><font size='1'><b>" . number_format((($row['8'] * 100) / $row['Present']), 2, ',', '') . "%</b></font></i> " . "</td>"
        . "<td>" . $row['7.75'] . "<br><i><font size='1'><b>" . number_format((($row['7.75'] * 100) / $row['Present']), 2, ',', '') . "%</b></font></i> " . "</td>"
        . "<td>" . $row['7.5'] . "<br><i><font size='1'><b>" . number_format((($row['7.5'] * 100) / $row['Present']), 2, ',', '') . "%</b></font></i> " . "</td>"
        . "<td>" . $row['7.25'] . "<br><i><font size='1'><b>" . number_format((($row['7.25'] * 100) / $row['Present']), 2, ',', '') . "%</b></font></i> " . "</td>"
        . "<td>" . $row['7'] . "<br><i><font size='1'><b>" . number_format((($row['7'] * 100) / $row['Present']), 2, ',', '') . "%</b></font></i> " . "</td>"
        . "<td>" . $row['6.75'] . "<br><i><font size='1'><b>" . number_format((($row['6.75'] * 100) / $row['Present']), 2, ',', '') . "%</b></font></i> " . "</td>"
        . "<td>" . $row['6.5'] . "<br><i><font size='1'><b>" . number_format((($row['6.5'] * 100) / $row['Present']), 2, ',', '') . "%</b></font></i> " . "</td>"
        . "<td>" . $row['6-'] . "<br><i><font size='1'><b>" . number_format((($row['6-'] * 100) / $row['Present']), 2, ',', '') . "%</b></font></i> " . "</td>"
            . "<td>" . $row['Abs'] . "</td>"
            . "<td>" . $row['Exclu'] . "</td></tr>";

    }
    $tableau .= "</tbody><tfoot><tr></tr></tfoot></table>";
    echo $tableau;
    echo fin_cadre_relief(true);

}

function exec_tendances()
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
    if ($statut != 'Admin') {
        switch ($statut) {

            case 'Encadrant':
                $isEncadrant = true;
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
                $andStatut2 = " AND pro.etablissement IN $tabEta ";
                $andEtablissementFormulaire = " AND eta.id IN $tabEta ";
                $andEtablissementFormulaire2 = " AND eta.id IN $tabEta ";
                $isChefEtablissement = false;
                $isOperateur = false;
                $isInspection = true;
                $mois = date('m');
                if ($annee == '') {
                    $annee = recupAnnee($lien); //$annee = date("Y");
                }

                if ($mois > 10 && $annee == date("Y")) {
                    $annee += 1;
                }

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
        $annee = recupAnnee($lien); //$annee = date("Y");
    }
    if ($id_type_session == '') {
        $id_type_session = recupSession($lien);
    }

    if (isAutorise(array('Admin', 'Dre', 'Encadrant', 'Informaticien', 'Inspection'))) {

        $verif = true;
        if (!$verif_doublons) {
            $verif = false;
        }

        if (!$verif_champs) {
            $verif = false;
        }

        if($statut=='Admin'){
        echo '<script type="text/javascript">',
        '$(document).ready(function() {',
        'var selectregion = document.getElementById ("id_region");',
        'var newOption = new Option ("NATIONAL", "-1");',
        'selectregion.options.add (newOption);',
        '});',
            '</script>'
        ;
    }

        echo debut_grand_cadre();
        echo debut_cadre_trait_couleur('', '', '', "GESTION DES TENDANCES", "", "", false);

        if (!isset($_REQUEST['id_region']) && (!isset($_REQUEST['etape']))) {
            $liste = "<form action='" . generer_url_ecrire('tendances') . "' method='POST' class='forml spip_xx-small' name='form_tendances'>";
            $liste .= "<center><select name='id_region' id='id_region' onchange='document.forms.form_tendances.submit()'>";
            if ($isDre) {
                $liste .= optionsReferentiel($lien, 'region', '', " $WhereRegion ");
            } else {
                $liste .= optionsReferentiel($lien, 'region');
            }

            $liste .= "</select></center>";
            $liste .= "</form>";
            echo $liste;
        }

        if (isset($_REQUEST['id_region']) && !isset($_REQUEST['etape'])) {
            echo tendancesRegion($lien, $annee, $id_region);
            echo "<div class='onglets_simple clearfix'><ul>";
        }

        // if (isset($_GET['etape']) && $etape == 'doc') {
        //     $doc = debut_cadre_couleur('', '', '', "DOCUMENTATION");
        //     $doc .= "<table>";
        //     $doc .= "<tr><td colspan=2>La gestion des professeurs comporte l'enregistrement (lien 'Insérer'), la visualisation et la mise à jour (lien 'Liste')
        //         et l'impression des listes (lien 'Impressions'). </td></tr>";
        //     $doc .= "<tr><td>1. Enregistrement </td><td>Cliquer sur le lien 'Insérier'<br/>Renseigner les divers champs<br/>Cliquer sur 'Enregistrer'<br>A la fin des saisies, cliquer sur 'Quitter'</td></tr>";
        //     $doc .= "<tr><td>2. Visualisation</td><td>Cliquer sur le lien 'Liste'<br/>Cette liste peut être triée par série et/ou par sexe<br/>Un professeur peut être recherché par son numéros ou par son nom</td></tr>";
        //     $doc .= "<tr><td>3. Mise à jour</td><td>Pour modifier ou supprimer, il suffit de cliquer sur le nom <br/>Le formulaire apparaît et la modification ou la suppression peut se faire</td></tr>";
        //     $doc .= "<tr><td>4. Impressions</td><td>Par ce lien, la liste peut être imprimée pour correction</td></tr>";
        //     $doc .= "<tr><td>5. Divers</td><td>Pour autres questions, bien vouloir appeler le Centre National de Documentation Pédagogique et des Technologies de l’Information et de la Communication pour l’Education (CNDP-TICE)</td></tr>";
        //     $doc .= "</table>";
        //     $doc .= fin_cadre_couleur();
        //     echo $doc;
        // }

        echo fin_cadre_trait_couleur();
        echo fin_grand_cadre(), fin_gauche(), fin_page();

    }

}

<?php
if (!defined('_ECRIRE_INC_VERSION')) {
    return;
}

include_spip('inc/presentation'); // alleger les inclusions avec un inc/presentation_mini
include '../plugins/fonctions/generales.php';

setlocale(LC_TIME, "fr_FR");
define('MAX_CANDIDATS', 150);

function detectJuryEtTable($lien, $annee, $id_centre)
{
    //Recherche du max numero de table et de jury dans si centre contient deja des candidats
    //Dans le cas contraire et s'il existe un centre déjà pourvu dans la region, rechercher sa region_division et ajouter 1 jurys et 10 numeros de table vides
    //Sinon prendre les premiers numeros de table et de jury dans la region
    $sql2 = 'SELECT id_region_division FROM bg_ref_etablissement WHERE id=' . $id_centre;
    $result2 = bg_query($lien, $sql2, __FILE__, __LINE__);
    $row2 = mysqli_fetch_array($result2);
    $id_region_division = $row2['id_region_division'];

    $sql1 = "SELECT max(numero) as max_num, max(jury) as max_jury FROM bg_repartition WHERE annee=$annee AND id_centre=$id_centre ";
/*
$sql1='SELECT max(numero) as max_num, max(jury) as max_jury FROM bg_repartition rep, bg_ref_region_division reg, bg_ref_etablissement eta '
.'WHERE rep.annee='.$annee.' AND rep.id_centre=eta.id AND reg.id=eta.id_region_division AND reg.id='.$id_region_division;
 */
    $result1 = bg_query($lien, $sql1, __FILE__, __LINE__);
    $row1 = mysqli_fetch_array($result1);
    $numTable = $row1['max_num'];
    if ($numTable != '') {
        $numTable = $row1['max_num'] + 5;
        $numJury = $row1['max_jury'] + 1;
//        echo $sql1;
    } else {

        $sql3 = 'SELECT max(numero) as max_num, max(jury) as max_jury FROM bg_repartition rep, bg_ref_region_division reg, bg_ref_etablissement eta '
            . 'WHERE rep.annee=' . $annee . ' AND rep.id_centre=eta.id AND reg.id=eta.id_region_division AND eta.id!=' . $id_centre . ' AND reg.id=' . $id_region_division;
        $result3 = bg_query($lien, $sql3, __FILE__, __LINE__);
        $row3 = mysqli_fetch_array($result3);
        $numTable = $row3['max_num'];
        if ($numTable != '') {
            $numTable = $row3['max_num'] + 50;
            $numJury = $row3['max_jury'] + 1;
        } else {
            $sql4 = 'SELECT borne_numero, borne_jury FROM bg_ref_region_division '
                . 'WHERE id=' . $id_region_division;
            $result4 = bg_query($lien, $sql4, __FILE__, __LINE__);
            $row4 = mysqli_fetch_array($result4);
            $numTable = $row4['borne_numero'];
            $numJury = $row4['borne_jury'];
        }
    }
    //Recherche des max du numero de table et de jury dans la region

    $sql_borne = "SELECT borne_numero FROM bg_ref_region_division
			WHERE id='$id_region_division' ";
    $result_borne = bg_query($lien, $sql_borne, __FILE__, __LINE__);
    $row_borne = mysqli_fetch_array($result_borne);
    $borne_numero = $row_borne['borne_numero'];

    $sql5 = 'SELECT min(borne_numero) as maxNumTable, min(borne_jury) as maxNumJury '
        . ' FROM bg_ref_region_division WHERE id!=' . $id_region_division . " AND borne_numero>'$borne_numero'GROUP BY borne_numero, borne_jury ";
    $result5 = bg_query($lien, $sql5, __FILE__, __LINE__);
    $row5 = mysqli_fetch_array($result5);
    if ($row5['maxNumTable'] > 0) {
        $maxNumTable = $row5['maxNumTable'] - 1;
        $maxNumJury = $row5['maxNumJury'] - 1;
    } else {
        $maxNumTable = 10000000;
        $maxNumJury = 10000;
    }
    return array('numTable' => $numTable, 'numJury' => $numJury, 'maxNumTable' => $maxNumTable, 'maxNumJury' => $maxNumJury);
}

function repartir_candidats($lien, $annee, $id_centre, $id_serie = 0, $premierNumero, $totCan = '', $tReserve, $tabEta, $tCan2 = array(), $id_langue_vivante = 0, $id_region = 0, $id_prefecture = 0, $id_ville = 0, $id_eps = 0, $nbre_cand = 0)
{
    $tabCapacite = capacites($lien, $annee);
    $tabPlaces = places_occupees($lien, $annee);
    $capacite = $tabCapacite[$id_centre];
    $places_utilisees = $tabPlaces[$id_centre];
    $reste = $capacite - $places_utilisees;
    $centre = selectReferentiel($lien, 'etablissement', $id_centre);

    $sql = 'SELECT id_region_division FROM bg_ref_etablissement WHERE id=' . $id_centre;
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $row = mysqli_fetch_array($result);
    $id_region_division = $row['id_region_division'];
    $id_region = selectRegionByRegionDivision($lien, $id_region_division);
    $affiche = 0;

    //Cas de la repartition avec la liste des etablissements: Par centre
    $tCan = $tCan2;
    if (!empty($tCan)) {
        foreach ($totCan as $key => $val) {
            $tCan[$key] = $val;
        }
        $totCan = $tCan;
    }

    foreach ($totCan as $jury => $nbreCanParJury) {
        $total_a_repartir += $nbreCanParJury;
    }
    if (array_sum($tabEta) > $reste) {
        echo $reste . '/' . array_sum($tabEta);
        echo debut_boite_alerte(), "Opération impossible: Le nombre de candidats à répartir ($total_a_repartir) dans le centre $centre est supérieur au nombre de places disponibles ($reste)", fin_boite_alerte();
    } else {
        $sql1 = "SELECT max(numero) as max_num FROM bg_repartition WHERE annee=$annee AND id_centre=$id_centre ";
        $result1 = bg_query($lien, $sql1, __FILE__, __LINE__);
        $row1 = mysqli_fetch_array($result1);
        $max_num = $row1['max_num'];
        if ($max_num == '') {
            $max_num = 1;
        }

        $numero = $premierNumero;

        //Rechercher les candidats a repartir par date de naissance
        if ($id_serie == 1 and $id_langue_vivante != 0) {
            $andLangue = " AND lv2=$id_langue_vivante ";
        } else {
            $andLangue = '';
        }

        foreach ($tabEta as $id_etablissement => $cand_a_repartir) {
            $cand_a_repartir = floatval($cand_a_repartir);
            $sql_cand = "SELECT * FROM bg_candidats
				WHERE annee=$annee AND num_table='' AND serie=$id_serie
				AND etablissement=$id_etablissement $andLangue
				LIMIT 0, $cand_a_repartir";
            $result_cand = bg_query($lien, $sql_cand, __FILE__, __LINE__);
            while ($row_cand = mysqli_fetch_array($result_cand)) {
                $id_candidat = $row_cand['id_candidat'];
                $tabCanARepartir[$id_candidat] = $row_cand['nom'];
            }
        }
        asort($tabCanARepartir);

        foreach ($totCan as $jury => $nbreCanParJury) {
            if ($nbreCanParJury > 0) {

                //Rechercher numero de table mini dans un autre centre de la region afin de ne pas chevaucher sur les numeros
                /*                    $sql_num_min="SELECT max(numero) as min_num
                FROM bg_repartition rep, bg_ref_etablissement eta
                WHERE rep.annee=$annee AND eta.id_region_division='$id_region_division' AND rep.id_centre!='$id_centre' ";
                 */
                $sql_num_min = "SELECT max(numero) as min_num
						FROM bg_repartition rep, bg_ref_etablissement eta
						WHERE rep.annee=$annee AND rep.id_centre='$id_centre' AND jury='($jury-1)' ";
                $result_num_min = bg_query($lien, $sql_num_min, __FILE__, __LINE__);
                $row_num_min = mysqli_fetch_array($result_num_min);
                $min_num = $row_num_min['min_num'];
                if ($min_num == '') {
                    $min_num = 0;
                }

                $sql_max_num = "SELECT min(borne_numero) as max_borne FROM bg_ref_region_division
						WHERE id!='$id_region_division' AND borne_numero>'$premierNumero'";
                $result_max_num = bg_query($lien, $sql_max_num, __FILE__, __LINE__);
                $row_max_num = mysqli_fetch_array($result_max_num);
                if ($row_max_num['max_borne'] > 0) {
                    $max_borne = $row_max_num['max_borne'] - 1;
                } else {
                    $max_borne = 10000000;
                }

                $i = 0;
                foreach ($tabCanARepartir as $id_candidat => $nom) {
                    //Rechercher numero de table max dans un ancien jury pour completer candidats
                    if (!empty($tCan2) && array_key_exists($jury, $tCan2)) {
                        $sql_num_max = "SELECT max(numero) as max_num FROM bg_repartition WHERE annee=$annee AND id_centre=$id_centre AND jury='$jury' ";
                        $result_num_max = bg_query($lien, $sql_num_max, __FILE__, __LINE__);
                        $row_num_max = mysqli_fetch_array($result_num_max);
                        $numero = $row_num_max['max_num'] + 1;
                    }

//                    echo $numero.'ZZZZZZ'.$nbreCanParJury.'ZZZZZZ'.$mini."($controle)<br/>";
                    if (($numero <= $min_num && $min_num > 0 && !empty($tCan2)) || $numero >= $max_borne) {
                        echo debut_boite_alerte(), "Opération impossible: <br/>Il n'y a plus de numéros disponibles dans ce centre ou <br/> pas assez de numéros disponibles dans ce jury <br/> <center><h2><a href='./?exec=repartition&etape=repart'>Retour aux centres</a></h2></center> ", fin_boite_alerte();
                        exit;
                    } elseif (($numero > $min_num && $min_num > 0 && $numero < $max_borne) || ($numero > $min_num && $min_num == 0 && $numero < $max_borne) || empty($tCan2)) {
                        $sql = "SELECT type_session FROM bg_candidats WHERE annee=$annee AND id_candidat='$id_candidat' AND serie=$id_serie ";
                        $result = bg_query($lien, $sql, __FILE__, __LINE__);
                        $row = mysqli_fetch_array($result);
                        $id_type_session = $row['type_session'];

                        //                        $tCandidats[$id_candidat]=$numero;
                        $sql3 = "INSERT INTO bg_repartition (num_table,annee,id_candidat,id_centre,numero,jury,id_type_session,id_region) VALUES ('$numero','$annee','$id_candidat','$id_centre','$numero','$jury',$id_type_session,$id_region)";
                        bg_query($lien, $sql3, __FILE__, __LINE__);
                        $sql4 = "UPDATE bg_candidats SET num_table='$numero' WHERE annee=$annee AND id_candidat='$id_candidat' ";
                        bg_query($lien, $sql4, __FILE__, __LINE__);
                        $numero++;
                        $affiche++;
                    }
                    unset($tabCanARepartir[$id_candidat]);
                    $i++;
                    if ($i >= $nbreCanParJury) {
                        break;
                    }

                }
            }
            $numero += $tReserve[$jury];
        }
        echo "<hr /> <b>$affiche Candidats répartis</b> <hr/>";
/*
foreach($tCandidats as $id_candidat=>$num_table){
$sql4="UPDATE bg_candidats SET num_table='$num_table' WHERE annee='$annee' AND id_candidat='$id_candidat' ";
bg_query($lien,$sql4,__FILE__,__LINE__);
echo $sql4."<br/>";
}
 */
    }

/*

if($tabEta!=''){
$total_a_repartir+=$nbre_cand;
$sql="SELECT * FROM bg_candidats "
." WHERE annee=$annee AND num_table='' AND serie=$id_serie AND etablissement=$id_eta AND centre='$id_centre' "
." ORDER BY ddn  ";
$result=bg_query($lien,$sql,__FILE__,__LINE__);
while($row=mysqli_fetch_array($result)){
$id_candidat=$row['id_candidat'];
$ddn=$row['ddn'];
$tabCan[$id_candidat]=$ddn;
$type_session=$row['type_session'];
$tabSession[$id_candidat]=$type_session;
}
}
//Cas de la répartition globale
else{
if($id_serie!=0) $andWhere.=" AND ser.id='$id_serie' ";
if($id_region!=0) $andWhere.=" AND reg.id='$id_region' ";
if($id_prefecture!=0) $andWhere.=" AND pre.id='$id_prefecture' ";
if($id_ville!=0) $andWhere.=" AND vil.id='$id_ville' ";
if($id_eps!=0) $andWhere.=" AND spo.id='$id_eps' ";
if($id_langue_vivante!=0) $andWhere.=" AND lan.id='$id_langue_vivante' ";
if($nbre_cand=='*') {
$limit='';
}elseif($nbre_cand>0) $limit=" LIMIT 0,$nbre_cand ";

$sql="SELECT can.id_candidat, can.ddn, can.nom, can.prenoms, can.type_session "
." FROM bg_ref_region reg, bg_ref_prefecture pre, bg_ref_ville vil, bg_ref_eps spo, "
." bg_ref_etablissement eta, bg_ref_serie ser, bg_candidats can "
." LEFT JOIN bg_ref_langue_vivante lan ON lan.id=can.lv2"
." WHERE can.annee=$annee AND can.num_table='' "
." AND can.serie=ser.id AND can.eps=spo.id "
." AND can.etablissement=eta.id AND eta.id_ville=vil.id AND vil.id_prefecture=pre.id AND pre.id_region=reg.id "
." $andWhere "
." ORDER BY can.ddn "
." $limit ";
$result=bg_query($lien,$sql,__FILE__,__LINE__);
$total_a_repartir=mysqli_num_rows($result);
while($row=mysqli_fetch_array($result)){
$id_candidat=$row['id_candidat'];
$ddn=$row['ddn'];
$tabCan[$id_candidat]=$ddn;
$type_session=$row['type_session'];
$tabSession[$id_candidat]=$type_session;
}
}

if($total_a_repartir>$reste){
echo debut_boite_alerte(),"Opération impossible: Le nombre de candidats à répartir ($total_a_repartir) dans le centre $centre est supérieur au nombre de places disponibles ($reste)",fin_boite_alerte();
}else{
asort($tabCan);

$totCan=$_POST['totCan'];
foreach($totCan as $jury=>$nbreCanJury){
if($nbreCanJury>0){
$sql="SELECT * FROM bg_candidats "
." WHERE annee=$annee AND num_table='' AND serie=$id_serie AND etablissement=$id_eta AND centre='$id_centre' "
." ORDER BY ddn  LIMIT 0,$nbreCanJury ";
$result=bg_query($lien,$sql,__FILE__,__LINE__);
$num_table=$premierNumero;
while($row=mysqli_fetch_array($result)){
$id_candidat=$row['id_candidat'];
$type_session=$row['type_session'];
$sql3="REPLACE INTO bg_repartition (num_table,annee,id_candidat,id_centre,numero,jury,id_type_session) VALUES ('$num_table','$annee','$id_candidat','$id_centre','$numero','$jury',$id_type_session)";
//            bg_query($lien,$sql3,__FILE__,__LINE__);
$sql4="UPDATE bg_candidats SET num_table='$num_table' WHERE annee=$annee AND id_candidat='$id_candidat' ";
//                bg_query($lien,$sql4,__FILE__,__LINE__);
$num_table++;
}
}

}

$sql="SELECT borne_inf, borne_sup FROM bg_paramrepart WHERE annee=$annee AND id_centre=$id_centre ";
$result=bg_query($lien,$sql,__FILE__,__LINE__);
$row=mysqli_fetch_array($result);
$borne_inf=$row['borne_inf'];
$borne_sup=$row['borne_sup'];

$sql1="SELECT max(numero) as max_num FROM bg_repartition WHERE annee=$annee AND id_centre=$id_centre ";
$result1=bg_query($lien,$sql1,__FILE__,__LINE__);
$row1=mysqli_fetch_array($result1);
$max_num=$row1['max_num'];
if($max_num=='') $max_num=0;

if($borne_sup>$max_num){
if($borne_inf>$max_num) $numero=$borne_inf;
else $numero=$max_num + 1;
$numero=$premierNumero;
$i=1;
//Mis a jour des table repartition et candidat
foreach($tabCan as $id_candidat=>$val){
if($i>=$nbreCanJury){
$jury=
}
$num_table=$numero;
$id_type_session=$tabSession[$id_candidat];
$sql3="REPLACE INTO bg_repartition (num_table,annee,id_candidat,id_centre,numero,jury,id_type_session) VALUES ('$num_table','$annee','$id_candidat','$id_centre','$numero','$jury',$id_type_session)";
//                bg_query($lien,$sql3,__FILE__,__LINE__);
$sql4="UPDATE bg_candidats SET num_table='$num_table' WHERE annee=$annee AND id_candidat='$id_candidat' ";
//                bg_query($lien,$sql4,__FILE__,__LINE__);
$numero++;
}
}else{
echo debut_boite_alerte(),'Répartition impossible car le centre est déjà rempli',fin_boite_alerte();
}
}*/
}

//Cette fonction retourne un tableau des places occupees par centre de composition
function places_occupees($lien, $annee)
{
    $sql = "SELECT id_centre, count(*) as nbre FROM bg_repartition WHERE annee=$annee GROUP BY id_centre";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    while ($row = mysqli_fetch_array($result)) {
        $nbre = $row['nbre'];
        $id_centre = $row['id_centre'];
        $tabPlaces[$id_centre] = $nbre;
    }
    return $tabPlaces;
}

//Liste des candidats à repartir par serie
function CandidatsSerie($lien, $annee)
{
    $sql = "SELECT id as id_serie, ser.serie FROM bg_ref_serie ser, bg_candidats can WHERE can.annee=$annee AND can.serie=ser.id GROUP BY ser.id ORDER BY serie ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    echo "<table>";
    while ($row = mysqli_fetch_array($result)) {
        $id_serie = $row['id_serie'];
        $serie = $row['serie'];

        $sql2 = "SELECT * FROM bg_candidats WHERE annee=$annee AND serie=$id_serie";
        $result2 = bg_query($lien, $sql2, __FILE__, __LINE__);
        $total_serie = mysqli_num_rows($result2);

        $sql3 = "SELECT * FROM bg_candidats WHERE annee=$annee AND serie=$id_serie AND num_table='' ";
        $result3 = bg_query($lien, $sql3, __FILE__, __LINE__);
        $reste_serie = mysqli_num_rows($result3);

        echo "<tr><td>$serie</td> <td>$reste_serie / $total_serie </td></tr>";
    }
    echo "</table>";
}

//Liste des candidats à repartir par region
function CandidatsRegion($lien, $annee)
{
    $sql = "SELECT reg.id as id_region_division, reg.region_division "
        . " FROM bg_ref_region_division reg, bg_ref_etablissement cent, bg_candidats can "
        . " WHERE can.annee=$annee AND can.centre=cent.id AND cent.id_region_division=reg.id "
        . " GROUP BY reg.id ORDER BY region_division ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    echo "<table>";
    while ($row = mysqli_fetch_array($result)) {
        $id_region_division = $row['id_region_division'];
        $region_division = $row['region_division'];

        $sql2 = "SELECT reg.id as id_region_division, reg.region_division "
            . " FROM bg_ref_region_division reg, bg_ref_etablissement cent, bg_candidats can "
            . " WHERE can.annee=$annee AND can.centre=cent.id AND cent.id_region_division=reg.id "
            . " AND reg.id=$id_region_division ";
        $result2 = bg_query($lien, $sql2, __FILE__, __LINE__);
        $total_region = mysqli_num_rows($result2);

        $sql3 = "SELECT reg.id as id_region_division, reg.region_division "
            . " FROM bg_ref_region_division reg, bg_ref_etablissement cent, bg_candidats can "
            . " WHERE can.annee=$annee AND can.centre=cent.id AND cent.id_region_division=reg.id "
            . " AND reg.id=$id_region_division AND can.num_table='' ";
        $result3 = bg_query($lien, $sql3, __FILE__, __LINE__);
        $reste_region = mysqli_num_rows($result3);

        echo "<tr><td>$region_division</td> <td>$reste_region / $total_region </td></tr>";
    }
    echo "</table>";
}

function exec_repartition()
{
    $exec = _request('exec');
    $titre = "exec_$exec";
    $commencer_page = charger_fonction('commencer_page', 'inc');
    echo $commencer_page($titre);
    $lien = lien();

    foreach ($_REQUEST as $key => $val) {
        $$key = mysqli_real_escape_string($lien, trim(addslashes($val)));
    }

    if (isset($_POST['id_region'])) {
        $andRegion = " AND ( divi.id_region=0 ";
        foreach ($_POST['id_region'] as $id_region) {
            $hidden .= "<input type=hidden name=id_region[$id_region] value='$id_region' />";
            $andRegion .= " OR divi.id_region=$id_region ";
        }
        $andRegion .= ")";
    } else {
        $andRegion = '';
        $hidden = '';
    }

    if ($annee == '') {
        $annee = recupAnnee($lien); //$annee = date("Y");
    }
    if ($id_type_session == '') {
        $id_type_session = recupSession($lien);
    }

    //    $annee=2018;
    $tab_auteur = $GLOBALS["auteur_session"];
    $login = $tab_auteur['login'];
/*
if(isset($_POST['RepGlobal'])){
repartir_candidats($lien,$annee,$id_centre,$id_serie,$premierNumero,$totCan='',$tReserve,$id_region,$id_prefecture,$id_ville,$id_eps,$id_langue_vivante,$nbre_cand);
}
 */

    if (isset($_POST['repartir'])) {
        $totCan = $_POST['totCan'];
        $tReserve = $_POST['tReserve'];
        $tCan2 = $_POST['tCan2'];
        $tabEta = $_POST['tabEta'];
        repartir_candidats($lien, $annee, $id_centre, $id_serie, $premierNumero, $totCan, $tReserve, $tabEta, $tCan2, $id_langue_vivante);
    }
    if (isset($_REQUEST['id_centre']) && $_REQUEST['id_centre'] > 0 && isset($_REQUEST['vider_centre'])) {
        if ($id_serie > 0) {
            $andSerie = " AND can.serie=$id_serie ";
        } else {
            $andSerie = '';
        }

        $tabSql[] = "UPDATE bg_candidats can, bg_repartition rep SET can.num_table=''
					WHERE can.annee=$annee AND rep.annee=$annee AND can.num_table=rep.num_table
					AND rep.id_centre=$id_centre $andSerie ";
        $tabSql[] = "DELETE rep
					 FROM bg_repartition rep, bg_candidats can
					 WHERE can.annee=$annee
					 AND rep.annee=$annee
					 AND can.id_candidat=rep.id_candidat
					 AND rep.id_centre='$id_centre'
					$andSerie ";
        foreach ($tabSql as $sql) {
            bg_query($lien, $sql, __FILE__, __LINE__);
        }
    }

    echo debut_grand_cadre();
    echo debut_cadre($s), CandidatsSerie($lien, $annee), fin_cadre();
    echo debut_cadre($s), CandidatsRegion($lien, $annee), fin_cadre();

    echo debut_cadre_trait_couleur('', '', '', "REPARTITION DES CANDIDATS", "", "", false);
//    doublons($lien,$annee);

    if (!isset($_REQUEST['etape']) && !isset($_REQUEST['repart'])) {
        echo "<div class='onglets_simple clearfix'>
			<ul>
				<li><a href='./?exec=repartition&etape=param' class='ajax'>Vérification des capacités</a></li>
				<li><a href='./?exec=repartition&etape=repart' class='ajax'>Répartir</a></li>
				<li><a href='./?exec=repartition&etape=stats' class='ajax'>Statistiques</a></li>
				<li><a href='./?exec=repartition&etape=vider' class='ajax'>Vider centre</a></li>
			</ul>
			</div>";
    }

    if (isset($_REQUEST['etape']) && $_REQUEST['etape'] == 'param') {
        echo "<a href='./?exec=repartition'><h2><center>Retour au menu principal</center></h2></a>";
        $sql = "SELECT * FROM bg_capacite WHERE annee=$annee ";
        $result = bg_query($lien, $sql, __FILE__, __LINE__);
        while ($row = mysqli_fetch_array($result)) {
            $id_centre = $row['id_centre'];
            $tCapacite[$id_centre] = $row['capacite'];
        }
        $sql1 = "SELECT id as id_region, region FROM bg_ref_region ORDER BY region ";
        $result1 = bg_query($lien, $sql1, __FILE__, __LINE__);
        while ($row1 = mysqli_fetch_array($result1)) {
            $id_region = $row1['id_region'];
            $region = $row1['region'];

            $liste = debut_cadre_couleur('', '', '', $region);
            $liste .= "<table>";
            $liste .= "<th>Centre d'écrit</th><th>Capacité</th><th>Nombre de candidats</th>";
            $sql2 = "SELECT pre.id as id_prefecture, eta.id as id_centre, eta.etablissement, count(*) as nbre_cand
				FROM bg_ref_prefecture pre, bg_ref_ville vil, bg_ref_etablissement eta, bg_candidats can
				WHERE can.annee=$annee AND can.centre=eta.id AND eta.id_ville=vil.id AND vil.id_prefecture=pre.id AND pre.id_region='$id_region'
				GROUP BY eta.id
				ORDER BY eta.etablissement";

            $result2 = bg_query($lien, $sql2, __FILE__, __LINE__);
            $tab = array('id_centre', 'etablissement', 'nbre_cand');
            while ($row2 = mysqli_fetch_array($result2)) {
                foreach ($tab as $val) {
                    $$val = $row2[$val];
                }

                if ($tCapacite[$id_centre] < $nbre_cand) {
                    $font = 'red';
                } else {
                    $font = '';
                }

                $liste .= "<tr><td><font color='$font'>$etablissement</font></td><td><font color='$font'>" . $tCapacite[$id_centre] . "</font></td><td><font color='$font'>$nbre_cand</font></td></tr>";

            }
            $liste .= "</table>";
            $liste .= fin_cadre_couleur();
            $liste .= "<br/>";
            echo $liste;
        }
    }

    if (isset($_REQUEST['etape']) && $_REQUEST['etape'] == 'stats') {
        echo "<a href='./?exec=repartition'><h2><center>Retour au menu principal</center></h2></a>";
        $sql1 = "SELECT reg.id as id_region, pre.id as id_prefecture, eta.id as id_centre, eta.etablissement as centre, ser.id as id_serie, count(*) as nbre_cand
				FROM bg_ref_region reg, bg_ref_prefecture pre, bg_ref_ville vil, bg_ref_etablissement eta, bg_candidats can, bg_repartition rep, bg_ref_serie ser
				WHERE can.annee=$annee AND rep.annee=$annee AND can.serie=ser.id AND can.num_table=rep.num_table
				AND eta.id_ville=vil.id AND vil.id_prefecture=pre.id AND pre.id_region=reg.id AND rep.id_centre=eta.id
				GROUP BY eta.id, ser.id
				ORDER BY reg.region, eta.etablissement, ser.serie ";

        $result1 = bg_query($lien, $sql1, __FILE__, __LINE__);
        $tab = array('id_centre', 'centre', 'id_serie', 'nbre_cand');
        while ($row1 = mysqli_fetch_array($result1)) {
            foreach ($tab as $val) {
                $$val = $row1[$val];
            }

            $tCapacite[$id_centre][$id_serie] = $nbre_cand;
            $tCentres[$id_centre] = $centre;
            $tTotaux[$id_centre] += $nbre_cand;
            $tTotSerie[$id_serie] += $nbre_cand;
            $total_general += $nbre_cand;
        }
        $sql2 = "SELECT id as id_serie, serie FROM bg_ref_serie ORDER BY serie";
        $result2 = bg_query($lien, $sql2, __FILE__, __LINE__);
        $imprimer = "<center><a href='../plugins/fonctions/inc-pdf-stats_repartition.php'><img src='../plugins/images/imprimer1.jpg' width='100px' /></a></center>";
        $table = debut_cadre_couleur('', '', '', "Candidats répartis par centre et par série");
        $table .= $imprimer;
        $table .= "<table>";
        $table .= "<th>Centre</th>";
        while ($row2 = mysqli_fetch_array($result2)) {
            $id_serie = $row2['id_serie'];
            $serie = $row2['serie'];
            $tSeries[$id_serie] = $row2['serie'];
            $table .= "<th>$serie</th>";
        }
        $table .= "<th>Total</th>";
        foreach ($tCentres as $id_centre => $centre) {
            $table .= "<tr><td><b>$centre</b></td>";
            foreach ($tSeries as $id_serie => $serie) {
                $table .= "<td>" . $tCapacite[$id_centre][$id_serie] . "</td>";
            }
            $table .= "<td>" . $tTotaux[$id_centre] . "</td></tr>";
        }

        $table .= "<tr><td><b>TOTAL</b></td>";
        foreach ($tSeries as $id_serie => $serie) {
            $table .= "<td>" . $tTotSerie[$id_serie] . "</td>";
        }
        $table .= "<td>" . $total_general . "</td></tr>";

        $table .= "</table>";
        $table .= $imprimer;
        $table .= fin_cadre_couleur();
        echo $table;
    }

    //Formulaire pour series et centres de composition
    if (isset($_REQUEST['etape']) && !isset($_REQUEST['id_centre']) && ($_REQUEST['etape'] == 'repart' || $_REQUEST['etape'] == 'vider')) {
        echo "<a href='./?exec=repartition'><h2><center>Retour au menu principal</center></h2></a>";
        $liste = "<form action='' method='POST' class='forml spip_xx-small' name='form_centres'>";
        $liste .= "<table>";
        if ($_REQUEST['etape'] == 'repart') {
            $liste .= "<tr><td><center>Etablissements proches<input type=radio name='si_global' checked value='non'/></center></td><td><center>Tous les établissements<input type=radio name='si_global' value='oui'/></center></td></tr>";
        }
        $liste .= "<tr><td><center>SERIE <select name='id_serie'>" . optionsReferentiel($lien, 'serie', '', '', true) . "</select></center></td>";
        $liste .= "<td><center>CENTRE <select name='id_centre' >";
        $liste .= optionsEtablissement($lien, '', " AND si_centre='oui' ");
        $liste .= "</select>";
        if ($_REQUEST['etape'] == 'repart') {
            $liste .= "REGION <select name='id_region[]'>" . optionsReferentiel($lien, 'region', '', '', true) . "</select></center>";
        }
        $liste .= "</td></tr>";
        $liste .= "<tr><td colspan=2><center><input type=submit name='valider' value='OK' onClick=\"if(document.forms['form_centres'].id_serie.value==0 || document.forms['form_centres'].id_centre.value==0) {alert('Choisissez une série et un centre'); return false; } else return true;\"; /></center></td></tr>";
        $liste .= "</table></form>";
        echo $liste;
    }

    //Supprimer des numeros de table
    if (isset($_REQUEST['etape']) && $_REQUEST['etape'] == 'vider' && $_REQUEST['id_centre']) {
        $centre = selectReferentiel($lien, 'etablissement', $id_centre);
        echo "<a href='./?exec=repartition'><h2><center>Retour au menu principal</center></h2></a>";

        $sql = "SELECT ser.id, ser.serie, count(*) as nbre "
            . " FROM bg_ref_serie ser, bg_candidats can, bg_ref_etablissement eta, bg_repartition rep "
            . " WHERE rep.annee=$annee AND can.annee=$annee AND can.num_table=rep.num_table AND can.serie=ser.id AND rep.id_centre=eta.id
					AND rep.id_centre=$id_centre AND ser.id=$id_serie "
            . " GROUP BY ser.id ORDER BY ser.serie ";
        $result = bg_query($lien, $sql, __FILE__, __LINE__);
        if (mysqli_num_rows($result) > 0) {
            echo debut_cadre_couleur('', '', '', "$centre");
            echo "<table><th>Série</th><th>Nombre de candidats répartis</th>";
            while ($row = mysqli_fetch_array($result)) {
                $total += $row['nbre'];
                echo "<tr><td>" . $row['serie'] . "</td><td>" . $row['nbre'] . "</td></tr>";
            }
            echo "<tr><td><b>Total</b></td><td><b>$total</b></td></tr>";
            echo "</table>";
            echo fin_cadre_couleur();
            echo "<form method='POST'>
					<center><br/><br/>
					<input type='hidden' name='id_centre' value='$id_centre' />
					<input type='hidden' name='id_serie' value='$id_serie' />
					<h2><input type='submit' class='submit' name='vider_centre' value='Vider ce centre' onClick=\"if(confirm('Etes vous sûr de vouloir vider ce centre?')) return true; else return false; \" /></h2><br/><br/></center></form>";
        } else {
            echo "<h2>Aucun candidat n'est dans ce centre</h2>";
            echo "<h2><a href='./?exec=repartition&etape=vider'><h2><center>Continuer par vider </center></h2></a>";
        }
    }

/*
echo "<pre>";
print_r($_POST);
echo "</pre>";
 */

    //Paramétrer les débuts de jurys et de numéro de table
    if (isset($_REQUEST['etape']) && $_REQUEST['etape'] == 'repart' && $_REQUEST['id_centre']) {
        if ($si_global == 'oui') {
            $andWhere = '';
        } else {
            $andWhere = " AND can.centre=$id_centre ";
        }

/*
if($id_region!=0) $andRegion=" AND divi.id_region=$id_region ";
else $andRegion='';
 */
        // $sql_etas = "SELECT eta.id as id_etablissement, eta.etablissement, count(*) as nbre_cand "
        //     . " FROM bg_ref_etablissement eta, bg_candidats can, bg_ref_region_division divi "
        //     . " WHERE can.annee=$annee AND can.etablissement=eta.id
		// 			AND eta.id_region_division=divi.id
		// 			AND can.serie='$id_serie' AND num_table='' $andWhere $andRegion "
        //     . " GROUP BY eta.id ORDER BY eta.etablissement ";  
        $sql_eta = "SELECT eta.id as id_etablissement, eta.etablissement, count(*) as nbre_cand "
            . " FROM bg_ref_etablissement eta, bg_candidats can, bg_ref_region_division divi "
            . " WHERE can.annee=$annee AND can.etablissement=eta.id
					AND eta.id_region_division=divi.id
					AND can.serie='$id_serie' AND num_table='' $andWhere "
            . " GROUP BY eta.id ORDER BY eta.etablissement ";
        $result_eta = bg_query($lien, $sql_eta, __FILE__, __LINE__);
        $tabCapacite = capacites($lien, $annee);
        $tabPlaces = places_occupees($lien, $annee);
        $capacite = $tabCapacite[$id_centre];
        $places_utilisees = $tabPlaces[$id_centre];
        $reste = $capacite - $places_utilisees;
        $centre = selectReferentiel($lien, 'etablissement', $id_centre);
        $serie = selectReferentiel($lien, 'serie', $id_serie);
        echo debut_cadre_enfonce('', '', '', "Centre: $centre "),
        "<ul><li>Capacité totale: $capacite</li><li>Capacité restante: $reste</li></ul>",
        "<center><h2><a href='./?exec=repartition&etape=repart'>Retour aux centres</a></h2></center>",
        fin_cadre_enfonce();
        $tab = array('id_etablissement', 'etablissement', 'nbre_cand');
        $liste = "<center><h2>SERIE " . selectReferentiel($lien, 'serie', $id_serie) . "</h2></center>";
        $liste .= "<table><form name='form_param' method='POST' action='' >";
        $liste .= "<th>Etablissement</th><th>Total des candidats</th><th>A répartir</th>";
        $total_cand = 0;
        if ($id_serie == 1 && isset($_POST['continuer'])) {
            $liste .= "<tr><td colspan=3><center>Langue Vivante: <select name=id_langue_vivante>" . optionsReferentiel($lien, 'langue_vivante', '', " WHERE id!=3") . "</select></center></td></tr>";
        }

        while ($row_eta = mysqli_fetch_array($result_eta)) {
            foreach ($tab as $val) {
                $$val = $row_eta[$val];
            }
            $tabTampon[$id_etablissement]['nb'] = $nbre_cand;
            $tabTampon[$id_etablissement]['eta'] = $etablissement;
            $somTotal .= "+parseInt(document.getElementById('tabEta[$id_etablissement]').value)";
        }
        foreach ($tabTampon as $id_etablissement => $tabTampon2) {
            $nbre_cand = $tabTampon2['nb'];
            $liste .= "<tr><td>" . $tabTampon2['eta'] . "</td>"
                . "<td>$nbre_cand</td>
					<td>";
                   
            if (isset($_POST['continuer'])) {
//                $somTotal.="+parseInt(document.getElementById('tabEta[$id_etablissement]').value)";
                $liste .= "<input type='number' id='tabEta[$id_etablissement]' name='tabEta[$id_etablissement]' value=$nbre_cand min=0 max=$nbre_cand onBlur=\"if(this.value=='')this.value=0; document.getElementById('totalARepartir').value=(0 $somTotal); \" />";
            }

            $liste .= "</td></tr>";
            $total_cand += $nbre_cand;
        }
        $liste .= "<tr><td><b>TOTAL DES CANDIDATS</b></td><td><b>$total_cand</b></td><td>";
        if (isset($_POST['continuer'])) {
            $liste .= "<input type=number id='totalARepartir' name='totalARepartir' value='$total_cand' max='$total_cand' min=0 onFocus=\"{document.getElementById('totalARepartir').value=(0 $somTotal); } \" />";
        }
        $liste .= "</td></tr>";
        $liste .= "</table>";

        if (!isset($_POST['continuer'])) {
            $liste .= debut_cadre_enfonce('', '', '', "");
            $tabParam = detectJuryEtTable($lien, $annee, $id_centre);
            $numJury = $tabParam['numJury'];
            $maxNumJury = $tabParam['maxNumJury'];
            $numTable = $tabParam['numTable'];
            $maxNumTable = $tabParam['maxNumTable'];
            if ($numTable >= $maxNumTable || $numJury >= $maxNumJury) {
                echo debut_boite_alerte(), " Num = $numTable / Max = $maxNumTable <br/> Opération impossible: <br/>Il n'y a plus de numéros disponibles dans cette région <br/>", fin_boite_alerte();
                exit;
            } else {
                $liste .= "<table>";
                $liste .= "<tr><td><b>N° du premier jury</b></td>
				<td><input type='number' name='premierJury' min='$numJury'  value='$numJury' max='$maxNumJury' /></td></tr>";
                $liste .= "<tr><td><b>Premier numéro de table</b></td>
				<td><input type='number' name='premierNumero' min='$numTable'  value='$numTable' max='$maxNumTable' /></td></tr>";
                $liste .= "<tr><td><b>Réserve</b></td>
				<td><input type='number' name='reserve' min='10' max='' value='50' /></td></tr>";
                $liste .= "<tr><td><input type='hidden' name='id_centre' value='$id_centre' />$hidden</td>
							<td><input type='hidden' name='id_serie' value='$id_serie' /></td></tr>";
                $liste .= "<tr><td colspan=2><center><input type='submit' name='continuer' value='CONTINUER' />
						<input type='hidden' name='si_global' value='$si_global' /> </center></td></tr>";
                $liste .= "</form></table>";
            }
            $liste .= fin_cadre_enfonce();
        }
        if (isset($_POST['continuer'])) {
            //Affichage des jurys existants
            if ($si_global == 'oui') {
                $andWhere = '';
            } else {
                $andWhere = " AND can.centre=$id_centre ";
            }

/*            if($id_region!=0) $andRegion=" AND divi.id_region=$id_region ";
else $andRegion='';
 */
            // $sql_ress = "SELECT * "
            //     . " FROM bg_candidats can, bg_ref_etablissement eta, bg_ref_region_division divi "
            //     . " WHERE annee=$annee AND serie='$id_serie' AND eta.id=can.etablissement
			// 		AND eta.id_region_division=divi.id AND num_table='' $andWhere $andRegion "; 
            $sql_res = "SELECT * "
                . " FROM bg_candidats can, bg_ref_etablissement eta, bg_ref_region_division divi "
                . " WHERE annee=$annee AND serie='$id_serie' AND eta.id=can.etablissement
					AND eta.id_region_division=divi.id AND num_table='' $andWhere  ";
            $result_res = bg_query($lien, $sql_res, __FILE__, __LINE__);
            $nbre_cand_restant = mysqli_num_rows($result_res);

            // $sqls = "SELECT jury, count(*) as nb_cand_rep, min(rep.num_table) as numero
			// 		FROM bg_candidats can, bg_repartition rep, bg_ref_region_division divi, bg_ref_etablissement eta "
            //     . " WHERE can.annee=$annee AND rep.annee=$annee "
            //     . " AND can.id_candidat=rep.id_candidat AND can.serie='$id_serie' AND eta.id=can.etablissement AND rep.id_centre='$id_centre'
			// 			AND eta.id_region_division=divi.id "
            //     . " AND rep.num_table!=''  "
            //     . " GROUP BY rep.jury ORDER BY rep.jury"; 
            $sql = "SELECT jury, count(*) as nb_cand_rep, min(rep.num_table) as numero
					FROM bg_candidats can, bg_repartition rep, bg_ref_region_division divi, bg_ref_etablissement eta "
                . " WHERE can.annee=$annee AND rep.annee=$annee "
                . " AND can.id_candidat=rep.id_candidat AND can.serie='$id_serie' AND eta.id=can.etablissement AND rep.id_centre='$id_centre'
						AND eta.id_region_division=divi.id "
                . " AND rep.num_table!='' $andRegion "
                . " GROUP BY rep.jury ORDER BY rep.jury";
            $result = bg_query($lien, $sql, __FILE__, __LINE__);
            $tab = array('jury', 'nb_cand_rep', 'numero');
            if (mysqli_num_rows($result) > 0) {
                $ligne = "<th>Séries</th><th>jury</th><th>1er N° de Table</th><th>Candidats répartis</th><th>Candidats à répartir</th>";
            }
            while ($row = mysqli_fetch_array($result)) {
                foreach ($tab as $val) {
                    $$val = $row[$val];
                }

                $sql1 = "SELECT min(numero) as mini FROM bg_repartition WHERE annee=$annee AND id_centre='$id_centre' AND jury=" . ($jury + 1);
                $result1 = bg_query($lien, $sql1, __FILE__, __LINE__);
                $row1 = mysqli_fetch_array($result1);
                $mini = $row1['mini'];

                $sql11 = "SELECT max(numero) as maxi FROM bg_repartition WHERE annee=$annee AND id_centre='$id_centre' AND jury=" . $jury;
                $result11 = bg_query($lien, $sql11, __FILE__, __LINE__);
                $row11 = mysqli_fetch_array($result11);
                $maxi = $row11['maxi'];

//                echo $mini.'ZZZ'.$maxi."<br/>";
                if ($mini != '') {
                    $reserve1 = $mini - $maxi - 1;
                }
                if ($nbre_cand_restant < $reserve1) {
                    $reserve1 = $nbre_cand_restant;
                }

                if ($reserve1 < (150 - $nb_cand_rep)) {
                    $reserve1 = $nbre_cand_restant;
                }

                $ligne .= "<tr><td>$serie</td>"
                    . "<td><input type='text' disabled='disable' size=1 name='premierJury' value='$jury' /></td>"
                    . "<td><input type='text' disabled='disable' size=1 name='premierNumero' value='$numero' /></td>"
                    . "<td><input type='text' disabled='disable' name=\"totCan[$jury]\" value=\"$nb_cand_rep\" size=1 /></td>"
                    . "<td><input type='number' name=\"tCan2[$jury]\" min='0' max=\"$reserve1\" value='0' size=1  /></td>"
                    . "</tr>";
            }
            if ($si_global == 'oui') {
                $andWhere = '';
            } else {
                $andWhere = " AND can.centre=$id_centre ";
            }

/*            if($id_region!=0) $andRegion=" AND divi.id_region=$id_region ";
else $andRegion='';
 */
            $ligne .= "<tr><td colspan=5><b><center>Candidats non encore répartis</center></b></td></tr>";

            $sql_eta2 = "SELECT ser.id as id_serie, ser.serie, count(*) as nbre_cand "
                . " FROM bg_ref_serie ser, bg_candidats can, bg_ref_region_division divi, bg_ref_etablissement eta "
                . " WHERE can.annee=$annee AND can.serie=ser.id $andWhere AND can.serie='$id_serie' AND eta.id=can.etablissement AND num_table=''
						AND eta.id_region_division=divi.id AND eta.id=can.etablissement  "
                . " GROUP BY ser.id ORDER BY ser.serie ";
            // $sql_eta2s = "SELECT ser.id as id_serie, ser.serie, count(*) as nbre_cand "
            //     . " FROM bg_ref_serie ser, bg_candidats can, bg_ref_region_division divi, bg_ref_etablissement eta "
            //     . " WHERE can.annee=$annee AND can.serie=ser.id $andWhere AND can.serie='$id_serie' AND eta.id=can.etablissement AND num_table=''
			// 			AND eta.id_region_division=divi.id AND eta.id=can.etablissement $andRegion "
            //     . " GROUP BY ser.id ORDER BY ser.serie ";
            $result_eta2 = bg_query($lien, $sql_eta2, __FILE__, __LINE__);
            $tab2 = array('id_serie', 'serie', 'nbre_cand');

            $liste .= "<form name='form_etablissements' method='POST'>";
            $liste .= "<table>";
            $liste .= $ligne;

            $liste .= "<th>Séries</th><th>jury</th><th>1er N° de Table</th><th>Nbre de candidats</th><th>Réserve</th>";
            $liste .= "<tr><td colspan=5><input type='hidden' name='premierNumero' value='$premierNumero' /></td></tr>";

            $canParJury = MAX_CANDIDATS;
            $jury = $premierJury;
            $numero = $premierNumero;
            while ($row_eta2 = mysqli_fetch_array($result_eta2)) {
                foreach ($tab2 as $val) {
                    $$val = $row_eta2[$val];
                }

                $cand_restant = $total_cand;
                for ($i = 0; $i < $total_cand; $i = $i + $canParJury) {
                    if ($canParJury > $total_cand) {
                        $nbre = $total_cand;
                    } elseif ($canParJury < $nbre_cand && $cand_restant > $canParJury) {
                        $nbre = $canParJury;
                    } else {
                        $nbre = $cand_restant;
                    }

                    $liste .= "<tr><td>$serie</td>"
                        . "<td><input type='text' disabled='disable' size=5 name='premierJury' value='$jury' /></td>"
                        . "<td><input type='text' disabled='disable' size=5 name='premierNumero' value='$numero' /></td>"
                        . "<td><input type='number' min=0 max=200 name=\"totCan[$jury]\" value=\"$nbre\" size=5 /></td>"
                        . "<td><input type='number' name=\"tReserve[$jury]\" min=\"$reserve\" value='$reserve' size=5  /></td>"
                        . "</tr>";

                    $cand_restant = $nbre_cand - $i - $canParJury;
                    $jury++;
                    $numero += $nbre + $reserve;
                }
            }
            $liste .= "<tr>
			<td><input type='hidden' name='id_centre' value=\"$id_centre\" />$hidden</td>
			<td><input type='hidden' name='id_serie' value='$id_serie' /></td>
			<td><input type='hidden' name='id_region' value='$id_region' /></td>
			<td><input type='hidden' name='si_global' value='$si_global' /></td>
			<td colspan=2><input type='hidden' name='reste' value=\"$reste\" />
			</td></tr>";

//        $js="onClick=\"if(form_etablissements.reste.value<$total_cand) {
            //                    alert('Le nombre de candidats à répartir est supérieur au nombre de places disponibles'); return false;
            //                    }\" ";

            $liste .= "<tr><td colspan=5><center><input type='submit' name='repartir' value='Répartir ces candidats' class='submit' $js /></center></td></tr>";
            $liste .= "</table></form>";
        }
        echo $liste;
    }

    echo fin_cadre_trait_couleur();
    echo fin_grand_cadre(), fin_gauche(), fin_page();

}

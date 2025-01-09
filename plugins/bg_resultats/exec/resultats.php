<?php
if (!defined('_ECRIRE_INC_VERSION')) {
    return;
}

include_spip('inc/presentation'); // alleger les inclusions avec un inc/presentation_mini
include '../plugins/fonctions/generales.php';

setlocale(LC_TIME, "fr_FR");
define('MAX_LIGNES', 200);
function getJuryByCode($lien, $annee, $code)
{
    $sql = "SELECT jury FROM bg_codes WHERE annee=$annee AND code='$code' LIMIT 0,1";
    echo $sql;
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $row = mysqli_fetch_array($result);
    $jury = $row['jury'];
    return $jury;
}
function getCentreByRegionDivision($lien, $id_region_division)
{
    $sql = "SELECT id FROM bg_ref_etablissement WHERE  si_centre='oui' AND id_region_division=$id_region_division ";
    //  echo $sql;
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    while ($row = mysqli_fetch_array($result)) {
        $tCentres[] = $row['id'];
    }

    return $tCentres;
}
function getCentreByInspection($lien, $id_inspection)
{
    $sql = "SELECT id FROM bg_ref_etablissement WHERE  si_centre='oui' AND id_inspection=$id_inspection";
    //  echo $sql;
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    while ($row = mysqli_fetch_array($result)) {
        $tCentres[] = $row['id'];
    }

    return $tCentres;
}

function getCodeByJury($lien, $annee)
{
    $sql = "SELECT jury, code FROM bg_codes WHERE annee=$annee ORDER BY jury";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    while ($row = mysqli_fetch_array($result)) {
        $jury = $row['jury'];
        $codes[$jury] = $row['code'];
    }
    return $codes;
}

function calculMargeJuryAp($lien, $annee, $jury1, $jury2)
{
    $moyenne_apte_a4 = getApteBySerie($lien, 1);
    $moyenne_apte_c = getApteBySerie($lien, 2);
    $moyenne_apte_d = getApteBySerie($lien, 3);
    $moyenne_inapte_a4 = getInapteBySerie($lien, 1);
    $moyenne_inapte_c = getInapteBySerie($lien, 2);
    $moyenne_inapte_d = getInapteBySerie($lien, 3);

    if ($jury2 == false) {
        $sql1 = "SELECT num_table, min(note) as mini, max(note) as maxi FROM bg_notes WHERE annee=$annee AND jury= $jury1 $andNumTable GROUP BY num_table ";

    } else {
        $sql1 = "SELECT num_table, min(note) as mini, max(note) as maxi FROM bg_notes WHERE annee=$annee AND jury BETWEEN $jury1 AND $jury2 $andNumTable GROUP BY num_table ";

    }
    //Rechercher les candidats absents et ceux presents
    $result1 = bg_query($lien, $sql1, __FILE__, __LINE__);

    while ($row1 = mysqli_fetch_array($result1)) {
        if ($row1['maxi'] > 0) {
            $tCandidats[] = $row1['num_table'];
        } else {
            $tAbsents[] = $row1['num_table'];
        }

    }

    //Calcul de la moyenne des candidats présents pour les épreuves facultatives, pratiques et écrites
    foreach ($tCandidats as $num_table) {

        //Recuperation des info du candidat
        $sql10 = "SELECT * FROM bg_notes WHERE num_table='$num_table' LIMIT 1";
        $result10 = bg_query($lien, $sql10, __FILE__, __LINE__);
        $sol = mysqli_fetch_array($result10);
        // var_dump($sol['jury']);
        //$liste = '';

        $jury = $sol['jury'];
        $tJurys[] = $jury;

        //Recuperation des info du candidat
        $sql10 = "SELECT * FROM bg_notes WHERE num_table='$num_table' LIMIT 1";
        $result10 = bg_query($lien, $sql10, __FILE__, __LINE__);
        $sol = mysqli_fetch_array($result10);

        //Recuperation des info du candidat dans repartition en fonction de son anonymat
        $sql11 = "SELECT * FROM bg_repartition WHERE id_anonyme='$num_table' LIMIT 1";
        $result11 = bg_query($lien, $sql11, __FILE__, __LINE__);
        $sol11 = mysqli_fetch_array($result11);
        $num_table_reel = $sol11['numero'];

        //Recuperation des info du candidat
        $sql12 = "SELECT * FROM bg_candidats WHERE num_table='$num_table_reel' LIMIT 1";
        $result12 = bg_query($lien, $sql12, __FILE__, __LINE__);
        $sol12 = mysqli_fetch_array($result12);

        if ($sol['id_serie'] == 1) { //Si la serie est A4

            if ($sol12['eps'] == 1) { //Si le candidat est apte
                $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`,`nt`,`id_region`,`id_serie`)
        SELECT num_table, id_anonyme, id_type_session, annee, jury, SUM(note*coeff), $moyenne_apte_a4, SUM(note*coeff)/$moyenne_apte_a4 as moyenne ,notes.nt,notes.id_region,notes.id_serie \n
        FROM bg_notes notes WHERE annee=$annee AND jury=$jury AND note>=0 AND num_table='$num_table' AND id_type_note!=3 AND id_type_note!=4
        GROUP BY num_table, id_anonyme, id_type_session, annee, jury";
            }
            if ($sol12['eps'] == 2) { //Si le candidat est inapte
                $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`,`nt`,`id_region`,`id_serie`)
            SELECT num_table, id_anonyme, id_type_session, annee, jury, SUM(note*coeff), $moyenne_inapte_a4, SUM(note*coeff)/$moyenne_inapte_a4 as moyenne ,notes.nt,notes.id_region,notes.id_serie \n
            FROM bg_notes notes WHERE annee=$annee AND jury=$jury AND note>=0 AND num_table='$num_table' AND id_type_note!=3 AND id_type_note!=4
            GROUP BY num_table, id_anonyme, id_type_session, annee, jury";
            }
        } else if ($sol['id_serie'] == 3) { //Si la serie est D
            if ($sol12['eps'] == 1) { //Si le candidat est apte
                $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`,`nt`,`id_region`,`id_serie`)
        SELECT num_table, id_anonyme, id_type_session, annee, jury, SUM(note*coeff), $moyenne_apte_d, SUM(note*coeff)/$moyenne_apte_d as moyenne ,notes.nt,notes.id_region,notes.id_serie \n
        FROM bg_notes notes WHERE annee=$annee AND jury=$jury AND note>=0 AND num_table='$num_table' AND id_type_note!=3 AND id_type_note!=4
        GROUP BY num_table, id_anonyme, id_type_session, annee, jury";
            }
            if ($sol12['eps'] == 2) { //Si le candidat est inapte
                $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`,`nt`,`id_region`,`id_serie`)
        SELECT num_table, id_anonyme, id_type_session, annee, jury, SUM(note*coeff), $moyenne_inapte_d, SUM(note*coeff)/$moyenne_inapte_d as moyenne ,notes.nt,notes.id_region,notes.id_serie \n
        FROM bg_notes notes WHERE annee=$annee AND jury=$jury AND note>=0 AND num_table='$num_table' AND id_type_note!=3 AND id_type_note!=4
        GROUP BY num_table, id_anonyme, id_type_session, annee, jury";
            }

        } else if ($sol['id_serie'] == 2) { //Si la serie est C
            if ($sol12['eps'] == 1) { //Si le candidat est apte
                $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`,`nt`,`id_region`,`id_serie`)
        SELECT num_table, id_anonyme, id_type_session, annee, jury, SUM(note*coeff), $moyenne_apte_c, SUM(note*coeff)/$moyenne_apte_c as moyenne ,notes.nt,notes.id_region,notes.id_serie \n
        FROM bg_notes notes WHERE annee=$annee AND jury=$jury AND note>=0 AND num_table='$num_table' AND id_type_note!=3 AND id_type_note!=4
        GROUP BY num_table, id_anonyme, id_type_session, annee, jury";
            }
            if ($sol12['eps'] == 2) { //Si le candidat est inapte
                $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`,`nt`,`id_region`,`id_serie`)
        SELECT num_table, id_anonyme, id_type_session, annee, jury, SUM(note*coeff), $moyenne_inapte_c, SUM(note*coeff)/$moyenne_inapte_c as moyenne ,notes.nt,notes.id_region,notes.id_serie \n
        FROM bg_notes notes WHERE annee=$annee AND jury=$jury AND note>=0 AND num_table='$num_table' AND id_type_note!=3 AND id_type_note!=4
        GROUP BY num_table, id_anonyme, id_type_session, annee, jury";
            }
        }

        $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`,`nt`,`id_region`,`id_serie`)
    SELECT notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, notes.jury, (res.total+SUM(note-10)), res.coeff, ((res.total+SUM(note-10))/res.coeff) as moyenne ,notes.nt,notes.id_region,notes.id_serie \n
    FROM bg_notes notes, bg_resultats res WHERE notes.annee=$annee AND res.annee=$annee AND notes.num_table=res.num_table AND notes.jury=$jury AND note>10 AND (note-10) <=5 AND notes.num_table='$num_table' AND notes.id_type_note=4
    GROUP BY notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, notes.jury, res.coeff, res.total";

        //Pour matiere facultative superieur a 15
        $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`,`nt`,`id_region`,`id_serie`)
   SELECT notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, notes.jury, (res2.total+SUM(5)), res2.coeff, ((res2.total+SUM(5))/res2.coeff) as moyenne ,notes.nt,notes.id_region,notes.id_serie \n
   FROM bg_notes notes, bg_resultats res2 WHERE notes.annee=$annee AND res2.annee=$annee AND notes.num_table=res2.num_table AND notes.jury=$jury AND note>15 AND notes.num_table='$num_table' AND notes.id_type_note=4
   GROUP BY notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, notes.jury, res2.coeff, res2.total";

    }

    //Indication des candidats absents
    foreach ($tAbsents as $num_table) {
        //Recuperation des info du candidat
        $sql10 = "SELECT * FROM bg_notes WHERE num_table='$num_table' LIMIT 1";
        $result10 = bg_query($lien, $sql10, __FILE__, __LINE__);
        $sol = mysqli_fetch_array($result10);
        // var_dump($sol['jury']);
        //$liste = '';

        $jury = $sol['jury'];
        $tJurys[] = $jury;

        $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`, `delib1`,`nt`,`id_region`,`id_serie`)
    SELECT num_table, id_anonyme, id_type_session, annee, jury, 0, SUM(coeff), -1, 'Absent' ,nt,id_region,id_serie \n
    FROM bg_notes WHERE annee=$annee AND jury=$jury AND num_table='$num_table'
    GROUP BY num_table, id_anonyme, id_type_session, annee, jury";
    }

    if ($jury2 == false) {
        //Attribution des mentions pour la première phase
        $tSql2[] = "UPDATE bg_resultats SET delib1='TBien' WHERE annee=$annee AND jury= $jury1 AND moyenne>=16 $andNumTable ";
        $tSql2[] = "UPDATE bg_resultats SET delib1='Bien' WHERE annee=$annee AND jury= $jury1 AND moyenne>=14 AND moyenne<16 $andNumTable ";
        $tSql2[] = "UPDATE bg_resultats SET delib1='Abien' WHERE annee=$annee AND jury= $jury1 AND moyenne>=12 AND moyenne<14 $andNumTable ";
        $tSql2[] = "UPDATE bg_resultats SET delib1='Passable' WHERE annee=$annee AND jury= $jury1 AND moyenne>=10 AND moyenne<12 $andNumTable ";
        $tSql2[] = "UPDATE bg_resultats SET delib1='Oral' WHERE annee=$annee AND jury= $jury1 AND moyenne>=9 AND moyenne<10 $andNumTable ";
        //    $tSql2[]="UPDATE bg_resultats SET delib1='Refuse' WHERE annee=$annee AND jury=$jury AND moyenne>=5 AND moyenne<9 $andNumTable ";
        //    $tSql2[]="UPDATE bg_resultats SET delib1='Ajourne' WHERE annee=$annee AND jury=$jury AND moyenne>0 AND moyenne<5 $andNumTable ";
        $tSql2[] = "UPDATE bg_resultats SET delib1='Ajourne' WHERE annee=$annee AND jury= $jury1 AND moyenne>0 AND moyenne<9 $andNumTable";
        //    $tSql2[]="UPDATE bg_notes notes, bg_resultats res SET delib1='Abandon' WHERE notes.annee=$annee AND res.annee=$annee AND res.num_table=notes.num_table AND res.jury=$jury AND notes.id_type_note!=4 AND notes.note<0 AND moyenne>=0 ";
        $tSql2[] = "UPDATE bg_notes notes, bg_resultats res SET delib1='Absent' WHERE notes.annee=$annee AND res.annee=$annee AND res.num_table=notes.num_table AND res.jury= $jury1 AND notes.id_type_note!=4 AND notes.note=0 AND moyenne<=0 ";
        $tSql2[] = "DELETE notes FROM bg_notes notes, bg_resultats res WHERE notes.annee=$annee AND res.annee=$annee AND res.num_table=notes.num_table AND res.jury= $jury1 AND id_type_note=3 AND delib1!='Oral' ";

    } else {
//Attribution des mentions pour la première phase
        $tSql2[] = "UPDATE bg_resultats SET delib1='TBien' WHERE annee=$annee AND jury BETWEEN $jury1 AND $jury2 AND moyenne>=16 $andNumTable ";
        $tSql2[] = "UPDATE bg_resultats SET delib1='Bien' WHERE annee=$annee AND jury BETWEEN $jury1 AND $jury2 AND moyenne>=14 AND moyenne<16 $andNumTable ";
        $tSql2[] = "UPDATE bg_resultats SET delib1='Abien' WHERE annee=$annee AND jury BETWEEN $jury1 AND $jury2 AND moyenne>=12 AND moyenne<14 $andNumTable ";
        $tSql2[] = "UPDATE bg_resultats SET delib1='Passable' WHERE annee=$annee AND jury BETWEEN $jury1 AND $jury2 AND moyenne>=10 AND moyenne<12 $andNumTable ";
        $tSql2[] = "UPDATE bg_resultats SET delib1='Oral' WHERE annee=$annee AND jury BETWEEN $jury1 AND $jury2 AND moyenne>=9 AND moyenne<10 $andNumTable ";
//    $tSql2[]="UPDATE bg_resultats SET delib1='Refuse' WHERE annee=$annee AND jury=$jury AND moyenne>=5 AND moyenne<9 $andNumTable ";
        //    $tSql2[]="UPDATE bg_resultats SET delib1='Ajourne' WHERE annee=$annee AND jury=$jury AND moyenne>0 AND moyenne<5 $andNumTable ";
        $tSql2[] = "UPDATE bg_resultats SET delib1='Ajourne' WHERE annee=$annee AND jury BETWEEN $jury1 AND $jury2 AND moyenne>0 AND moyenne<9 $andNumTable";
//    $tSql2[]="UPDATE bg_notes notes, bg_resultats res SET delib1='Abandon' WHERE notes.annee=$annee AND res.annee=$annee AND res.num_table=notes.num_table AND res.jury=$jury AND notes.id_type_note!=4 AND notes.note<0 AND moyenne>=0 ";
        $tSql2[] = "UPDATE bg_notes notes, bg_resultats res SET delib1='Absent' WHERE notes.annee=$annee AND res.annee=$annee AND res.num_table=notes.num_table AND res.jury BETWEEN $jury1 AND $jury2 AND notes.id_type_note!=4 AND notes.note=0 AND moyenne<=0 ";
        $tSql2[] = "DELETE notes FROM bg_notes notes, bg_resultats res WHERE notes.annee=$annee AND res.annee=$annee AND res.num_table=notes.num_table AND res.jury BETWEEN $jury1 AND $jury2 AND id_type_note=3 AND delib1!='Oral' ";

    }

    foreach ($tSql2 as $sql2) {
        bg_query($lien, $sql2, __FILE__, __LINE__);
    }
    $distinct = array_unique($tJurys);

    foreach ($distinct as $val) {
        $id_serie = getSerie($lien, $annee, $val);
        $deliberation[$val] = detectDeliberation($lien, $annee, $val);
        $tri = 'oui';
        echo ("<h2><center><a href='../plugins/fonctions/inc-pdf-grands_releves.php?jury=$val&id_serie=$id_serie&deliberation=$deliberation[$jury]&tri=$si_tous'>Le jury " . $val . "\n calculé avec succès!!!! Cliquez pour imprimer </a></center></h2>");

    }

}
function calculMargeJuryAd($lien, $annee, $jury1, $jury2)
{

    $moyenne_apte_a4 = getApteBySerie($lien, 1);
    $moyenne_apte_c = getApteBySerie($lien, 2);
    $moyenne_apte_d = getApteBySerie($lien, 3);
    $moyenne_inapte_a4 = getInapteBySerie($lien, 1);
    $moyenne_inapte_c = getInapteBySerie($lien, 2);
    $moyenne_inapte_d = getInapteBySerie($lien, 3);

    if ($jury2 == false) {
        $sql1 = "SELECT num_table, min(note) as mini, max(note) as maxi FROM bg_notes WHERE annee=$annee AND jury= $jury1 $andNumTable GROUP BY num_table ";

    } else {
        $sql1 = "SELECT num_table, min(note) as mini, max(note) as maxi FROM bg_notes WHERE annee=$annee AND jury BETWEEN $jury1 AND $jury2 $andNumTable GROUP BY num_table ";

    }
    //Rechercher les candidats absents et ceux presents
    $result1 = bg_query($lien, $sql1, __FILE__, __LINE__);

    while ($row1 = mysqli_fetch_array($result1)) {
        if ($row1['maxi'] > 0) {
            $tCandidats[] = $row1['num_table'];
        } else {
            $tAbsents[] = $row1['num_table'];
        }

    }

    //Calcul de la moyenne des candidats présents pour les épreuves facultatives, pratiques et écrites
    foreach ($tCandidats as $num_table) {

        //Recuperation des info du candidat
        $sql10 = "SELECT * FROM bg_notes WHERE num_table='$num_table' LIMIT 1";
        $result10 = bg_query($lien, $sql10, __FILE__, __LINE__);
        $sol = mysqli_fetch_array($result10);
        // var_dump($sol['jury']);
        //$liste = '';

        $jury = $sol['jury'];
        $tJurys[] = $jury;

        //Recuperation des info du candidat
        $sql10 = "SELECT * FROM bg_notes WHERE num_table='$num_table' LIMIT 1";
        $result10 = bg_query($lien, $sql10, __FILE__, __LINE__);
        $sol = mysqli_fetch_array($result10);

        //Recuperation des info du candidat dans repartition en fonction de son anonymat
        $sql11 = "SELECT * FROM bg_repartition WHERE id_anonyme='$num_table' LIMIT 1";
        $result11 = bg_query($lien, $sql11, __FILE__, __LINE__);
        $sol11 = mysqli_fetch_array($result11);
        $num_table_reel = $sol11['numero'];

        //Recuperation des info du candidat
        $sql12 = "SELECT * FROM bg_candidats WHERE num_table='$num_table_reel' LIMIT 1";
        $result12 = bg_query($lien, $sql12, __FILE__, __LINE__);
        $sol12 = mysqli_fetch_array($result12);

        if ($sol['id_serie'] == 1) { //Si la serie est A4

            if ($sol12['eps'] == 1) { //Si le candidat est apte
                $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`,`nt`,`id_region`,`id_serie`)
        SELECT num_table, id_anonyme, id_type_session, annee, jury, SUM(note*coeff), $moyenne_apte_a4, SUM(note*coeff)/$moyenne_apte_a4 as moyenne ,notes.nt,notes.id_region,notes.id_serie \n
        FROM bg_notes notes WHERE annee=$annee AND jury=$jury AND note>=0 AND num_table='$num_table' AND id_type_note!=3 AND id_type_note!=4
        GROUP BY num_table, id_anonyme, id_type_session, annee, jury";
            }
            if ($sol12['eps'] == 2) { //Si le candidat est inapte
                $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`,`nt`,`id_region`,`id_serie`)
            SELECT num_table, id_anonyme, id_type_session, annee, jury, SUM(note*coeff), $moyenne_inapte_a4, SUM(note*coeff)/$moyenne_inapte_a4 as moyenne ,notes.nt,notes.id_region,notes.id_serie \n
            FROM bg_notes notes WHERE annee=$annee AND jury=$jury AND note>=0 AND num_table='$num_table' AND id_type_note!=3 AND id_type_note!=4
            GROUP BY num_table, id_anonyme, id_type_session, annee, jury";
            }
        } else if ($sol['id_serie'] == 3) { //Si la serie est D
            if ($sol12['eps'] == 1) { //Si le candidat est apte
                $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`,`nt`,`id_region`,`id_serie`)
        SELECT num_table, id_anonyme, id_type_session, annee, jury, SUM(note*coeff), $moyenne_apte_d, SUM(note*coeff)/$moyenne_apte_d as moyenne ,notes.nt,notes.id_region,notes.id_serie \n
        FROM bg_notes notes WHERE annee=$annee AND jury=$jury AND note>=0 AND num_table='$num_table' AND id_type_note!=3 AND id_type_note!=4
        GROUP BY num_table, id_anonyme, id_type_session, annee, jury";
            }
            if ($sol12['eps'] == 2) { //Si le candidat est inapte
                $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`,`nt`,`id_region`,`id_serie`)
        SELECT num_table, id_anonyme, id_type_session, annee, jury, SUM(note*coeff), $moyenne_inapte_d, SUM(note*coeff)/$moyenne_inapte_d as moyenne ,notes.nt,notes.id_region,notes.id_serie \n
        FROM bg_notes notes WHERE annee=$annee AND jury=$jury AND note>=0 AND num_table='$num_table' AND id_type_note!=3 AND id_type_note!=4
        GROUP BY num_table, id_anonyme, id_type_session, annee, jury";
            }

        } else if ($sol['id_serie'] == 2) { //Si la serie est C
            if ($sol12['eps'] == 1) { //Si le candidat est apte
                $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`,`nt`,`id_region`,`id_serie`)
        SELECT num_table, id_anonyme, id_type_session, annee, jury, SUM(note*coeff), $moyenne_apte_c, SUM(note*coeff)/$moyenne_apte_c as moyenne ,notes.nt,notes.id_region,notes.id_serie \n
        FROM bg_notes notes WHERE annee=$annee AND jury=$jury AND note>=0 AND num_table='$num_table' AND id_type_note!=3 AND id_type_note!=4
        GROUP BY num_table, id_anonyme, id_type_session, annee, jury";
            }
            if ($sol12['eps'] == 2) { //Si le candidat est inapte
                $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`,`nt`,`id_region`,`id_serie`)
        SELECT num_table, id_anonyme, id_type_session, annee, jury, SUM(note*coeff), $moyenne_inapte_c, SUM(note*coeff)/$moyenne_inapte_c as moyenne ,notes.nt,notes.id_region,notes.id_serie \n
        FROM bg_notes notes WHERE annee=$annee AND jury=$jury AND note>=0 AND num_table='$num_table' AND id_type_note!=3 AND id_type_note!=4
        GROUP BY num_table, id_anonyme, id_type_session, annee, jury";
            }
        }

        $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`,`nt`,`id_region`,`id_serie`)
    SELECT notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, notes.jury, (res.total+SUM(note-10)), res.coeff, ((res.total+SUM(note-10))/res.coeff) as moyenne ,notes.nt,notes.id_region,notes.id_serie \n
    FROM bg_notes notes, bg_resultats res WHERE notes.annee=$annee AND res.annee=$annee AND notes.num_table=res.num_table AND notes.jury=$jury AND note>10 AND (note-10) <=5 AND notes.num_table='$num_table' AND notes.id_type_note=4
    GROUP BY notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, notes.jury, res.coeff, res.total";

        //Pour matiere facultative superieur a 15
        $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`,`nt`,`id_region`,`id_serie`)
   SELECT notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, notes.jury, (res2.total+SUM(5)), res2.coeff, ((res2.total+SUM(5))/res2.coeff) as moyenne ,notes.nt,notes.id_region,notes.id_serie \n
   FROM bg_notes notes, bg_resultats res2 WHERE notes.annee=$annee AND res2.annee=$annee AND notes.num_table=res2.num_table AND notes.jury=$jury AND note>15 AND notes.num_table='$num_table' AND notes.id_type_note=4
   GROUP BY notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, notes.jury, res2.coeff, res2.total";

    }

    //Indication des candidats absents
    foreach ($tAbsents as $num_table) {
        //Recuperation des info du candidat
        $sql10 = "SELECT * FROM bg_notes WHERE num_table='$num_table' LIMIT 1";
        $result10 = bg_query($lien, $sql10, __FILE__, __LINE__);
        $sol = mysqli_fetch_array($result10);
        // var_dump($sol['jury']);
        //$liste = '';

        $jury = $sol['jury'];
        $tJurys[] = $jury;

        $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`, `delib1`,`nt`,`id_region`,`id_serie`)
    SELECT num_table, id_anonyme, id_type_session, annee, jury, 0, SUM(coeff), -1, 'Absent' ,nt,id_region,id_serie \n
    FROM bg_notes WHERE annee=$annee AND jury=$jury AND num_table='$num_table'
    GROUP BY num_table, id_anonyme, id_type_session, annee, jury";
    }

    if ($jury2 == false) {
        //Attribution des mentions pour la première phase
        $tSql2[] = "UPDATE bg_resultats SET delib1='TBien' WHERE annee=$annee AND jury= $jury1 AND moyenne>=16 $andNumTable ";
        $tSql2[] = "UPDATE bg_resultats SET delib1='Bien' WHERE annee=$annee AND jury= $jury1 AND moyenne>=14 AND moyenne<16 $andNumTable ";
        $tSql2[] = "UPDATE bg_resultats SET delib1='Abien' WHERE annee=$annee AND jury= $jury1 AND moyenne>=12 AND moyenne<14 $andNumTable ";
        $tSql2[] = "UPDATE bg_resultats SET delib1='Passable' WHERE annee=$annee AND jury= $jury1 AND moyenne>=10 AND moyenne<12 $andNumTable ";
        $tSql2[] = "UPDATE bg_resultats SET delib1='Oral' WHERE annee=$annee AND jury= $jury1 AND moyenne>=9 AND moyenne<10 $andNumTable ";
        //    $tSql2[]="UPDATE bg_resultats SET delib1='Refuse' WHERE annee=$annee AND jury=$jury AND moyenne>=5 AND moyenne<9 $andNumTable ";
        //    $tSql2[]="UPDATE bg_resultats SET delib1='Ajourne' WHERE annee=$annee AND jury=$jury AND moyenne>0 AND moyenne<5 $andNumTable ";
        $tSql2[] = "UPDATE bg_resultats SET delib1='Ajourne' WHERE annee=$annee AND jury= $jury1 AND moyenne>0 AND moyenne<9 $andNumTable";
        //    $tSql2[]="UPDATE bg_notes notes, bg_resultats res SET delib1='Abandon' WHERE notes.annee=$annee AND res.annee=$annee AND res.num_table=notes.num_table AND res.jury=$jury AND notes.id_type_note!=4 AND notes.note<0 AND moyenne>=0 ";
        $tSql2[] = "UPDATE bg_notes notes, bg_resultats res SET delib1='Absent' WHERE notes.annee=$annee AND res.annee=$annee AND res.num_table=notes.num_table AND res.jury= $jury1 AND notes.id_type_note!=4 AND notes.note=0 AND moyenne<=0 ";
        $tSql2[] = "DELETE notes FROM bg_notes notes, bg_resultats res WHERE notes.annee=$annee AND res.annee=$annee AND res.num_table=notes.num_table AND res.jury= $jury1 AND id_type_note=3 AND delib1!='Oral' ";

    } else {
//Attribution des mentions pour la première phase
        $tSql2[] = "UPDATE bg_resultats SET delib1='TBien' WHERE annee=$annee AND jury BETWEEN $jury1 AND $jury2 AND moyenne>=16 $andNumTable ";
        $tSql2[] = "UPDATE bg_resultats SET delib1='Bien' WHERE annee=$annee AND jury BETWEEN $jury1 AND $jury2 AND moyenne>=14 AND moyenne<16 $andNumTable ";
        $tSql2[] = "UPDATE bg_resultats SET delib1='Abien' WHERE annee=$annee AND jury BETWEEN $jury1 AND $jury2 AND moyenne>=12 AND moyenne<14 $andNumTable ";
        $tSql2[] = "UPDATE bg_resultats SET delib1='Passable' WHERE annee=$annee AND jury BETWEEN $jury1 AND $jury2 AND moyenne>=10 AND moyenne<12 $andNumTable ";
        $tSql2[] = "UPDATE bg_resultats SET delib1='Oral' WHERE annee=$annee AND jury BETWEEN $jury1 AND $jury2 AND moyenne>=9 AND moyenne<10 $andNumTable ";
//    $tSql2[]="UPDATE bg_resultats SET delib1='Refuse' WHERE annee=$annee AND jury=$jury AND moyenne>=5 AND moyenne<9 $andNumTable ";
        //    $tSql2[]="UPDATE bg_resultats SET delib1='Ajourne' WHERE annee=$annee AND jury=$jury AND moyenne>0 AND moyenne<5 $andNumTable ";
        $tSql2[] = "UPDATE bg_resultats SET delib1='Ajourne' WHERE annee=$annee AND jury BETWEEN $jury1 AND $jury2 AND moyenne>0 AND moyenne<9 $andNumTable";
//    $tSql2[]="UPDATE bg_notes notes, bg_resultats res SET delib1='Abandon' WHERE notes.annee=$annee AND res.annee=$annee AND res.num_table=notes.num_table AND res.jury=$jury AND notes.id_type_note!=4 AND notes.note<0 AND moyenne>=0 ";
        $tSql2[] = "UPDATE bg_notes notes, bg_resultats res SET delib1='Absent' WHERE notes.annee=$annee AND res.annee=$annee AND res.num_table=notes.num_table AND res.jury BETWEEN $jury1 AND $jury2 AND notes.id_type_note!=4 AND notes.note=0 AND moyenne<=0 ";
        $tSql2[] = "DELETE notes FROM bg_notes notes, bg_resultats res WHERE notes.annee=$annee AND res.annee=$annee AND res.num_table=notes.num_table AND res.jury BETWEEN $jury1 AND $jury2 AND id_type_note=3 AND delib1!='Oral' ";

    }

    foreach ($tSql2 as $sql2) {
        bg_query($lien, $sql2, __FILE__, __LINE__);
    }
    $distinct = array_unique($tJurys);

    foreach ($distinct as $val) {
        $id_serie = getSerie($lien, $annee, $val);
        $deliberation[$val] = detectDeliberation($lien, $annee, $val);
        $tri = 'oui';
        echo ("<h2><center><a href='../plugins/fonctions/inc-pdf-grands_releves.php?jury=$val&id_serie=$id_serie&deliberation=$deliberation[$jury]&tri=$si_tous'>Le jury " . $val . "\n calculé avec succès!!!! Cliquez pour imprimer </a></center></h2>");

    }

}

function verifierAnonymat($lien, $annee)
{
    $sql = "SELECT code, rep.jury
			FROM bg_repartition rep
			LEFT JOIN bg_codes cod ON (cod.annee=$annee AND rep.annee=$annee AND rep.jury=cod.jury)
			GROUP BY rep.jury, code ORDER BY rep.jury, code ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $table = "<table>";
    while ($row = mysqli_fetch_array($result)) {
        if ($row['code'] == '') {
            $font = 'red';
        } else {
            $font = '';
        }

        $table .= "<tr><td><font color=$font>" . $row['jury'] . "</font></td><td><font color=$font>" . $row['code'] . "</font></td></tr>";
    }
    $table .= "</table>";
    echo $table;
}

function resultatJuryAp($lien, $annee, $jury, $deliberation, $num_table = '')
{
    $moyenne_apte_a4 = getApteBySerie($lien, 1);
    $moyenne_apte_c = getApteBySerie($lien, 2);
    $moyenne_apte_d = getApteBySerie($lien, 3);
    $moyenne_inapte_a4 = getInapteBySerie($lien, 1);
    $moyenne_inapte_c = getInapteBySerie($lien, 2);
    $moyenne_inapte_d = getInapteBySerie($lien, 3);

    if ($num_table != '') {
        $andNumTable = " AND num_table='$num_table' ";
    }

    //Supprimer dans resultats le jury en paramètre
    if ($deliberation == 0) {
        $sql = "DELETE FROM bg_resultats WHERE annee='$annee' AND jury='$jury' $andNumTable ";
        bg_query($lien, $sql, __FILE__, __LINE__);
    }

    //Rechercher les candidats absents et ceux presents
    $sql1 = "SELECT num_table, min(note) as mini, max(note) as maxi FROM bg_notes WHERE annee=$annee AND jury=$jury $andNumTable GROUP BY num_table ";
    $result1 = bg_query($lien, $sql1, __FILE__, __LINE__);
    while ($row1 = mysqli_fetch_array($result1)) {
        if ($row1['maxi'] > 0) {
            $tCandidats[] = $row1['num_table'];
        } else {
            $tAbsents[] = $row1['num_table'];
        }

    }

    //Calcul de la moyenne des candidats présents pour les épreuves facultatives, pratiques et écrites
    foreach ($tCandidats as $num_table) {
        //Recuperation des info du candidat
        $sql10 = "SELECT * FROM bg_notes WHERE num_table='$num_table' LIMIT 1";
        $result10 = bg_query($lien, $sql10, __FILE__, __LINE__);
        $sol = mysqli_fetch_array($result10);

        //Recuperation des info du candidat dans repartition en fonction de son anonymat
        $sql11 = "SELECT * FROM bg_repartition WHERE id_anonyme='$num_table' LIMIT 1";
        // $sql11 = "SELECT * FROM bg_repartition WHERE num_table='$num_table' LIMIT 1";
        $result11 = bg_query($lien, $sql11, __FILE__, __LINE__);
        $sol11 = mysqli_fetch_array($result11);
        $num_table_reel = $sol11['numero'];

        //Recuperation des info du candidat
        $sql12 = "SELECT * FROM bg_candidats WHERE num_table='$num_table_reel' LIMIT 1";
        $result12 = bg_query($lien, $sql12, __FILE__, __LINE__);
        $sol12 = mysqli_fetch_array($result12);

        if ($sol['id_serie'] == 1) { //Si la serie est A4

            if ($sol12['eps'] == 1) { //Si le candidat est apte
                $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`,`nt`,`id_region`,`id_serie`)
        SELECT num_table, id_anonyme, id_type_session, annee, jury, SUM(note*coeff), $moyenne_apte_a4, SUM(note*coeff)/$moyenne_apte_a4 as moyenne ,notes.nt,notes.id_region,notes.id_serie \n
        FROM bg_notes notes WHERE annee=$annee AND jury=$jury AND note>=0 AND num_table='$num_table' AND id_type_note!=3 AND id_type_note!=4
        GROUP BY num_table, id_anonyme, id_type_session, annee, jury";
            }
            if ($sol12['eps'] == 2) { //Si le candidat est inapte
                $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`,`nt`,`id_region`,`id_serie`)
            SELECT num_table, id_anonyme, id_type_session, annee, jury, SUM(note*coeff), $moyenne_inapte_a4, SUM(note*coeff)/$moyenne_inapte_a4 as moyenne ,notes.nt,notes.id_region,notes.id_serie \n
            FROM bg_notes notes WHERE annee=$annee AND jury=$jury AND note>=0 AND num_table='$num_table' AND id_type_note!=3 AND id_type_note!=4
            GROUP BY num_table, id_anonyme, id_type_session, annee, jury";
            }
        } else if ($sol['id_serie'] == 3) { //Si la serie est D
            if ($sol12['eps'] == 1) { //Si le candidat est apte
                $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`,`nt`,`id_region`,`id_serie`)
        SELECT num_table, id_anonyme, id_type_session, annee, jury, SUM(note*coeff), $moyenne_apte_d, SUM(note*coeff)/$moyenne_apte_d as moyenne ,notes.nt,notes.id_region,notes.id_serie \n
        FROM bg_notes notes WHERE annee=$annee AND jury=$jury AND note>=0 AND num_table='$num_table' AND id_type_note!=3 AND id_type_note!=4
        GROUP BY num_table, id_anonyme, id_type_session, annee, jury";
            }
            if ($sol12['eps'] == 2) { //Si le candidat est inapte
                $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`,`nt`,`id_region`,`id_serie`)
        SELECT num_table, id_anonyme, id_type_session, annee, jury, SUM(note*coeff), $moyenne_inapte_d, SUM(note*coeff)/$moyenne_inapte_d as moyenne ,notes.nt,notes.id_region,notes.id_serie \n
        FROM bg_notes notes WHERE annee=$annee AND jury=$jury AND note>=0 AND num_table='$num_table' AND id_type_note!=3 AND id_type_note!=4
        GROUP BY num_table, id_anonyme, id_type_session, annee, jury";
            }

        } else if ($sol['id_serie'] == 2) { //Si la serie est C
            if ($sol12['eps'] == 1) { //Si le candidat est apte
                $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`,`nt`,`id_region`,`id_serie`)
        SELECT num_table, id_anonyme, id_type_session, annee, jury, SUM(note*coeff), $moyenne_apte_c, SUM(note*coeff)/$moyenne_apte_c as moyenne ,notes.nt,notes.id_region,notes.id_serie \n
        FROM bg_notes notes WHERE annee=$annee AND jury=$jury AND note>=0 AND num_table='$num_table' AND id_type_note!=3 AND id_type_note!=4
        GROUP BY num_table, id_anonyme, id_type_session, annee, jury";
            }
            if ($sol12['eps'] == 2) { //Si le candidat est inapte
                $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`,`nt`,`id_region`,`id_serie`)
        SELECT num_table, id_anonyme, id_type_session, annee, jury, SUM(note*coeff), $moyenne_inapte_c, SUM(note*coeff)/$moyenne_inapte_c as moyenne ,notes.nt,notes.id_region,notes.id_serie \n
        FROM bg_notes notes WHERE annee=$annee AND jury=$jury AND note>=0 AND num_table='$num_table' AND id_type_note!=3 AND id_type_note!=4
        GROUP BY num_table, id_anonyme, id_type_session, annee, jury";
            }
        }

        if (is_numeric($num_table)) {
            $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`,`nt`,`id_region`,`id_serie`)
            SELECT notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, notes.jury, (res.total+SUM(note-10)), res.coeff, ((res.total+SUM(note-10))/res.coeff) as moyenne ,notes.nt,notes.id_region,notes.id_serie \n
            FROM bg_notes notes, bg_resultats res WHERE notes.annee=$annee AND res.annee=$annee AND notes.num_table=res.num_table AND notes.jury=$jury AND note>10 AND (note-10) <=5 AND notes.num_table='$num_table' AND notes.id_type_note=4
            GROUP BY notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, notes.jury, res.coeff, res.total";

            //Pour matiere facultative superieur a 15
            $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`,`nt`,`id_region`,`id_serie`)
            SELECT notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, notes.jury, (res2.total+SUM(5)), res2.coeff, ((res2.total+SUM(5))/res2.coeff) as moyenne ,notes.nt,notes.id_region,notes.id_serie \n
            FROM bg_notes notes, bg_resultats res2 WHERE notes.annee=$annee AND res2.annee=$annee AND notes.num_table=res2.num_table AND notes.jury=$jury AND note>15 AND notes.num_table='$num_table' AND notes.id_type_note=4
            GROUP BY notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, notes.jury, res2.coeff, res2.total";

        } else {
            $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`,`nt`,`id_region`,`id_serie`)
             SELECT notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, notes.jury, (res.total+SUM(note-10)), res.coeff, ((res.total+SUM(note-10))/res.coeff) as moyenne ,notes.nt,notes.id_region,notes.id_serie \n
             FROM bg_notes notes, bg_resultats res WHERE notes.annee=$annee AND res.annee=$annee AND notes.num_table=res.num_table AND notes.jury=$jury AND note>10 AND (note-10) <=5 AND notes.num_table='$num_table' AND notes.id_type_note=4
             GROUP BY notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, notes.jury, res.coeff, res.total";

            //Pour matiere facultative superieur a 15
            $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`,`nt`,`id_region`,`id_serie`)
             SELECT notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, notes.jury, (res2.total+SUM(5)), res2.coeff, ((res2.total+SUM(5))/res2.coeff) as moyenne ,notes.nt,notes.id_region,notes.id_serie \n
             FROM bg_notes notes, bg_resultats res2 WHERE notes.annee=$annee AND res2.annee=$annee AND notes.num_table=res2.num_table AND notes.jury=$jury AND note>15 AND notes.num_table='$num_table' AND notes.id_type_note=4
             GROUP BY notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, notes.jury, res2.coeff, res2.total";

        }

    }
    //Indication des candidats absents
    foreach ($tAbsents as $num_table) {
        //Recuperation des info du candidat
        $sql10 = "SELECT * FROM bg_notes WHERE num_table='$num_table' LIMIT 1";
        $result10 = bg_query($lien, $sql10, __FILE__, __LINE__);
        $sol = mysqli_fetch_array($result10);
        $id_anonyme_reel = $sol['id_anonyme'];
        //var_dump($id_anonyme_reel);
        //$liste = '';

        //Recuperation des info du candidat dans repartition en fonction de son anonymat
        $sql110 = "SELECT * FROM bg_repartition WHERE id_anonyme='$id_anonyme_reel' LIMIT 1";
        $result110 = bg_query($lien, $sql110, __FILE__, __LINE__);
        $sol110 = mysqli_fetch_array($result110);
        $num_table_reel = $sol110['numero'];
        //var_dump($num_table_reel);

        $jury = $sol['jury'];
        $tJurys[] = $jury;

        $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`, `delib1`,`nt`,`id_region`,`id_serie`)
    SELECT num_table, id_anonyme, id_type_session, annee, jury, 0, SUM(coeff), -1, 'Absent' ,$num_table_reel,id_region,id_serie \n
    FROM bg_notes WHERE annee=$annee AND jury=$jury AND num_table='$num_table'
    GROUP BY num_table, id_anonyme, id_type_session, annee, jury";
    }

    //Attribution des mentions pour la première phase
    $tSql2[] = "UPDATE bg_resultats SET delib1='TBien' WHERE annee=$annee AND jury=$jury AND moyenne>=16 $andNumTable ";
    $tSql2[] = "UPDATE bg_resultats SET delib1='Bien' WHERE annee=$annee AND jury=$jury AND moyenne>=14 AND moyenne<16 $andNumTable ";
    $tSql2[] = "UPDATE bg_resultats SET delib1='Abien' WHERE annee=$annee AND jury=$jury AND moyenne>=12 AND moyenne<14 $andNumTable ";
    $tSql2[] = "UPDATE bg_resultats SET delib1='Passable' WHERE annee=$annee AND jury=$jury AND moyenne>=10 AND moyenne<12 $andNumTable ";
    //    $tSql2[]="UPDATE bg_resultats SET delib1='Oral' WHERE annee=$annee AND jury=$jury AND moyenne>=9 AND moyenne<10 $andNumTable ";
    //    $tSql2[]="UPDATE bg_resultats SET delib1='Refuse' WHERE annee=$annee AND jury=$jury AND moyenne>=5 AND moyenne<9 $andNumTable ";
    //    $tSql2[]="UPDATE bg_resultats SET delib1='Ajourne' WHERE annee=$annee AND jury=$jury AND moyenne>0 AND moyenne<5 $andNumTable ";
    $tSql2[] = "UPDATE bg_resultats SET delib1='Ajourne' WHERE annee=$annee AND jury=$jury AND moyenne>0 AND moyenne<9 $andNumTable";
    $tSql2[] = "UPDATE bg_resultats SET delib1='Oral' WHERE annee=$annee AND jury=$jury AND moyenne>=9 AND moyenne<10 $andNumTable";
    //    $tSql2[]="UPDATE bg_notes notes, bg_resultats res SET delib1='Abandon' WHERE notes.annee=$annee AND res.annee=$annee AND res.num_table=notes.num_table AND res.jury=$jury AND notes.id_type_note!=4 AND notes.note<0 AND moyenne>=0 ";
    $tSql2[] = "UPDATE bg_notes notes, bg_resultats res SET delib1='Absent' WHERE notes.annee=$annee AND res.annee=$annee AND res.num_table=notes.num_table AND res.jury=$jury AND notes.id_type_note!=4 AND notes.note=0 AND moyenne<=0 ";
    $tSql2[] = "DELETE notes FROM bg_notes notes, bg_resultats res WHERE notes.annee=$annee AND res.annee=$annee AND res.num_table=notes.num_table AND res.jury=$jury AND id_type_note=3 AND delib1!='Oral' ";

    foreach ($tSql2 as $sql2) {
        bg_query($lien, $sql2, __FILE__, __LINE__);
    }

}

function resultatJuryAd($lien, $annee, $jury, $deliberation, $num_table = '')
{
    $moyenne_apte_a4 = getApteBySerie($lien, 1);
    $moyenne_apte_c = getApteBySerie($lien, 2);
    $moyenne_apte_d = getApteBySerie($lien, 3);
    $moyenne_inapte_a4 = getInapteBySerie($lien, 1);
    $moyenne_inapte_c = getInapteBySerie($lien, 2);
    $moyenne_inapte_d = getInapteBySerie($lien, 3);

    if ($num_table != '') {
        $andNumTable = " AND num_table='$num_table' ";
    }

    //Supprimer dans resultats le jury en paramètre
    if ($deliberation == 0) {
        $sql = "DELETE FROM bg_resultats WHERE annee='$annee' AND jury='$jury' $andNumTable ";
        bg_query($lien, $sql, __FILE__, __LINE__);
    }

    //Rechercher les candidats absents et ceux presents
    $sql1 = "SELECT num_table, min(note) as mini, max(note) as maxi FROM bg_notes WHERE annee=$annee AND jury=$jury $andNumTable GROUP BY num_table ";
    $result1 = bg_query($lien, $sql1, __FILE__, __LINE__);
    while ($row1 = mysqli_fetch_array($result1)) {
        if ($row1['maxi'] > 0) {
            $tCandidats[] = $row1['num_table'];
        } else {
            $tAbsents[] = $row1['num_table'];
        }

    }

    //Calcul de la moyenne des candidats présents pour les épreuves facultatives, pratiques et écrites
    foreach ($tCandidats as $num_table) {
        //Recuperation des info du candidat
        $sql10 = "SELECT * FROM bg_notes WHERE num_table='$num_table' LIMIT 1";
        $result10 = bg_query($lien, $sql10, __FILE__, __LINE__);
        $sol = mysqli_fetch_array($result10);

        //Recuperation des info du candidat dans repartition en fonction de son anonymat
        //$sql11 = "SELECT * FROM bg_repartition WHERE id_anonyme='$num_table' LIMIT 1";
        $sql11 = "SELECT * FROM bg_repartition WHERE num_table='$num_table' LIMIT 1";
        $result11 = bg_query($lien, $sql11, __FILE__, __LINE__);
        $sol11 = mysqli_fetch_array($result11);
        $num_table_reel = $sol11['numero'];

        //Recuperation des info du candidat
        $sql12 = "SELECT * FROM bg_candidats WHERE num_table='$num_table_reel' LIMIT 1";
        $result12 = bg_query($lien, $sql12, __FILE__, __LINE__);
        $sol12 = mysqli_fetch_array($result12);

        if ($sol['id_serie'] == 1) { //Si la serie est A4

            if ($sol12['eps'] == 1) { //Si le candidat est apte
                $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`,`nt`,`id_region`,`id_serie`)
        SELECT num_table, id_anonyme, id_type_session, annee, jury, SUM(note*coeff), $moyenne_apte_a4, SUM(note*coeff)/$moyenne_apte_a4 as moyenne ,notes.nt,notes.id_region,notes.id_serie \n
        FROM bg_notes notes WHERE annee=$annee AND jury=$jury AND note>=0 AND num_table='$num_table' AND id_type_note!=3 AND id_type_note!=4
        GROUP BY num_table, id_anonyme, id_type_session, annee, jury";
            }
            if ($sol12['eps'] == 2) { //Si le candidat est inapte
                $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`,`nt`,`id_region`,`id_serie`)
            SELECT num_table, id_anonyme, id_type_session, annee, jury, SUM(note*coeff), $moyenne_inapte_a4, SUM(note*coeff)/$moyenne_inapte_a4 as moyenne ,notes.nt,notes.id_region,notes.id_serie \n
            FROM bg_notes notes WHERE annee=$annee AND jury=$jury AND note>=0 AND num_table='$num_table' AND id_type_note!=3 AND id_type_note!=4
            GROUP BY num_table, id_anonyme, id_type_session, annee, jury";
            }
        } else if ($sol['id_serie'] == 3) { //Si la serie est D
            if ($sol12['eps'] == 1) { //Si le candidat est apte
                $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`,`nt`,`id_region`,`id_serie`)
        SELECT num_table, id_anonyme, id_type_session, annee, jury, SUM(note*coeff), $moyenne_apte_d, SUM(note*coeff)/$moyenne_apte_d as moyenne ,notes.nt,notes.id_region,notes.id_serie \n
        FROM bg_notes notes WHERE annee=$annee AND jury=$jury AND note>=0 AND num_table='$num_table' AND id_type_note!=3 AND id_type_note!=4
        GROUP BY num_table, id_anonyme, id_type_session, annee, jury";
            }
            if ($sol12['eps'] == 2) { //Si le candidat est inapte
                $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`,`nt`,`id_region`,`id_serie`)
        SELECT num_table, id_anonyme, id_type_session, annee, jury, SUM(note*coeff), $moyenne_inapte_d, SUM(note*coeff)/$moyenne_inapte_d as moyenne ,notes.nt,notes.id_region,notes.id_serie \n
        FROM bg_notes notes WHERE annee=$annee AND jury=$jury AND note>=0 AND num_table='$num_table' AND id_type_note!=3 AND id_type_note!=4
        GROUP BY num_table, id_anonyme, id_type_session, annee, jury";
            }

        } else if ($sol['id_serie'] == 2) { //Si la serie est C
            if ($sol12['eps'] == 1) { //Si le candidat est apte
                $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`,`nt`,`id_region`,`id_serie`)
        SELECT num_table, id_anonyme, id_type_session, annee, jury, SUM(note*coeff), $moyenne_apte_c, SUM(note*coeff)/$moyenne_apte_c as moyenne ,notes.nt,notes.id_region,notes.id_serie \n
        FROM bg_notes notes WHERE annee=$annee AND jury=$jury AND note>=0 AND num_table='$num_table' AND id_type_note!=3 AND id_type_note!=4
        GROUP BY num_table, id_anonyme, id_type_session, annee, jury";
            }
            if ($sol12['eps'] == 2) { //Si le candidat est inapte
                $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`,`nt`,`id_region`,`id_serie`)
        SELECT num_table, id_anonyme, id_type_session, annee, jury, SUM(note*coeff), $moyenne_inapte_c, SUM(note*coeff)/$moyenne_inapte_c as moyenne ,notes.nt,notes.id_region,notes.id_serie \n
        FROM bg_notes notes WHERE annee=$annee AND jury=$jury AND note>=0 AND num_table='$num_table' AND id_type_note!=3 AND id_type_note!=4
        GROUP BY num_table, id_anonyme, id_type_session, annee, jury";
            }
        }

        if (is_numeric($num_table)) {
            $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`,`nt`,`id_region`,`id_serie`)
            SELECT notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, notes.jury, (res.total+SUM(note-10)), res.coeff, ((res.total+SUM(note-10))/res.coeff) as moyenne ,notes.nt,notes.id_region,notes.id_serie \n
            FROM bg_notes notes, bg_resultats res WHERE notes.annee=$annee AND res.annee=$annee AND notes.num_table=res.num_table AND notes.jury=$jury AND note>10 AND (note-10) <=5 AND notes.num_table='$num_table' AND notes.id_type_note=4
            GROUP BY notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, notes.jury, res.coeff, res.total";

            //Pour matiere facultative superieur a 15
            $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`,`nt`,`id_region`,`id_serie`)
            SELECT notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, notes.jury, (res2.total+SUM(5)), res2.coeff, ((res2.total+SUM(5))/res2.coeff) as moyenne ,notes.nt,notes.id_region,notes.id_serie \n
            FROM bg_notes notes, bg_resultats res2 WHERE notes.annee=$annee AND res2.annee=$annee AND notes.num_table=res2.num_table AND notes.jury=$jury AND note>15 AND notes.num_table='$num_table' AND notes.id_type_note=4
            GROUP BY notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, notes.jury, res2.coeff, res2.total";

        } else {
            // $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`,`nt`,`id_region`,`id_serie`)
            //  SELECT notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, notes.jury, (res.total+SUM(note-10)), res.coeff, ((res.total+SUM(note-10))/res.coeff) as moyenne ,notes.nt,notes.id_region,notes.id_serie \n
            //  FROM bg_notes notes, bg_resultats res WHERE notes.annee=$annee AND res.annee=$annee AND notes.num_table=res.num_table AND notes.jury=$jury AND note>10 AND (note-10) <=5 AND notes.num_table='$num_table' AND notes.id_type_note=4
            //  GROUP BY notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, notes.jury, res.coeff, res.total";

            // //Pour matiere facultative superieur a 15
            // $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`,`nt`,`id_region`,`id_serie`)
            //  SELECT notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, notes.jury, (res2.total+SUM(5)), res2.coeff, ((res2.total+SUM(5))/res2.coeff) as moyenne ,notes.nt,notes.id_region,notes.id_serie \n
            //  FROM bg_notes notes, bg_resultats res2 WHERE notes.annee=$annee AND res2.annee=$annee AND notes.num_table=res2.num_table AND notes.jury=$jury AND note>15 AND notes.num_table='$num_table' AND notes.id_type_note=4
            //  GROUP BY notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, notes.jury, res2.coeff, res2.total";

        }

    }
    //Indication des candidats absents
    foreach ($tAbsents as $num_table) {
        //Recuperation des info du candidat
        $sql10 = "SELECT * FROM bg_notes WHERE num_table='$num_table' LIMIT 1";
        $result10 = bg_query($lien, $sql10, __FILE__, __LINE__);
        $sol = mysqli_fetch_array($result10);
        $id_anonyme_reel = $sol['id_anonyme'];
        //var_dump($id_anonyme_reel);
        //$liste = '';

        //Recuperation des info du candidat dans repartition en fonction de son anonymat
        $sql110 = "SELECT * FROM bg_repartition WHERE id_anonyme='$id_anonyme_reel' LIMIT 1";
        $result110 = bg_query($lien, $sql110, __FILE__, __LINE__);
        $sol110 = mysqli_fetch_array($result110);
        $num_table_reel = $sol110['numero'];
        //var_dump($num_table_reel);

        $jury = $sol['jury'];
        $tJurys[] = $jury;

        $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`, `delib1`,`nt`,`id_region`,`id_serie`)
        SELECT num_table, id_anonyme, id_type_session, annee, jury, 0, SUM(coeff), -1, 'Absent' ,$num_table_reel,id_region,id_serie \n
        FROM bg_notes WHERE annee=$annee AND jury=$jury AND num_table='$num_table'
        GROUP BY num_table, id_anonyme, id_type_session, annee, jury";
    }

    //Attribution des mentions pour la première phase
    $tSql2[] = "UPDATE bg_resultats SET delib1='TBien' WHERE annee=$annee AND jury=$jury AND moyenne>=16 $andNumTable ";
    $tSql2[] = "UPDATE bg_resultats SET delib1='Bien' WHERE annee=$annee AND jury=$jury AND moyenne>=14 AND moyenne<16 $andNumTable ";
    $tSql2[] = "UPDATE bg_resultats SET delib1='Abien' WHERE annee=$annee AND jury=$jury AND moyenne>=12 AND moyenne<14 $andNumTable ";
    $tSql2[] = "UPDATE bg_resultats SET delib1='Passable' WHERE annee=$annee AND jury=$jury AND moyenne>=10 AND moyenne<12 $andNumTable ";
    //    $tSql2[]="UPDATE bg_resultats SET delib1='Oral' WHERE annee=$annee AND jury=$jury AND moyenne>=9 AND moyenne<10 $andNumTable ";
    //    $tSql2[]="UPDATE bg_resultats SET delib1='Refuse' WHERE annee=$annee AND jury=$jury AND moyenne>=5 AND moyenne<9 $andNumTable ";
    //    $tSql2[]="UPDATE bg_resultats SET delib1='Ajourne' WHERE annee=$annee AND jury=$jury AND moyenne>0 AND moyenne<5 $andNumTable ";
    $tSql2[] = "UPDATE bg_resultats SET delib1='Ajourne' WHERE annee=$annee AND jury=$jury AND moyenne>0 AND moyenne<9 $andNumTable";
    $tSql2[] = "UPDATE bg_resultats SET delib1='Oral' WHERE annee=$annee AND jury=$jury AND moyenne>=9 AND moyenne<10 $andNumTable";
    //    $tSql2[]="UPDATE bg_notes notes, bg_resultats res SET delib1='Abandon' WHERE notes.annee=$annee AND res.annee=$annee AND res.num_table=notes.num_table AND res.jury=$jury AND notes.id_type_note!=4 AND notes.note<0 AND moyenne>=0 ";
    $tSql2[] = "UPDATE bg_notes notes, bg_resultats res SET delib1='Absent' WHERE notes.annee=$annee AND res.annee=$annee AND res.num_table=notes.num_table AND res.jury=$jury AND notes.id_type_note!=4 AND notes.note=0 AND moyenne<=0 ";
    $tSql2[] = "DELETE notes FROM bg_notes notes, bg_resultats res WHERE notes.annee=$annee AND res.annee=$annee AND res.num_table=notes.num_table AND res.jury=$jury AND id_type_note=3 AND delib1!='Oral' ";

    foreach ($tSql2 as $sql2) {
        bg_query($lien, $sql2, __FILE__, __LINE__);
    }

    //Apres la levée de l'anonymat remplir à vide les matieres de l'oral
    if ($deliberation >= 1) {
        if ($deliberation == 2) {
            $sql = "UPDATE bg_resultats res, bg_notes notes SET delib2='-' WHERE notes.annee=$annee AND res.annee=$annee AND res.num_table=notes.num_table AND res.jury=$jury AND notes.id_type_note=3 AND delib1='Oral' AND note IS NOT NULL ";
            bg_query($lien, $sql, __FILE__, __LINE__);
        }
        $sql_ss = "SELECT id_type_session FROM bg_resultats WHERE annee=$annee AND jury='$jury' LIMIT 0,1 ";
        $result_ss = bg_query($lien, $sql_ss, __FILE__, __LINE__);
        $row_ss = mysqli_fetch_array($result_ss);
        $id_type_session = $row_ss['id_type_session'];

        $param = selectCodeAno($lien, $annee, $id_type_session);
        $sql3 = "REPLACE into bg_notes (`num_table`,`id_anonyme`, `ano`, `annee`, `id_serie`, `jury`, `id_matiere`, `id_type_note`, `coeff`, `id_type_session`, `nt`,`id_region`)\n"
            . "SELECT res.num_table, res.id_anonyme, rep.ano, res.annee, can.serie, res.jury, cal.id_matiere, cal.id_type_note, cal.coeff, res.id_type_session ,res.nt,res.id_region \n"
            . " FROM bg_calendrier cal, bg_candidats can, bg_resultats res, bg_repartition rep \n"
            . " WHERE cal.annee=$annee and can.annee=$annee AND res.annee=$annee AND rep.annee=$annee AND rep.num_table=can.num_table \n"
            . " and cal.id_type_note=3 AND delib1='Oral' AND delib2='' "
            . " AND res.num_table=can.num_table and can.serie=cal.id_serie and res.jury='$jury' "
        ;
        bg_query($lien, $sql3, __FILE__, __LINE__);
    }

    //Mis a jour de delib2
    if ($deliberation == 2) {
        $sql5 = "SELECT num_table FROM bg_resultats WHERE annee=$annee AND jury=$jury AND delib1='Oral' $andNumTable ";
        $result5 = bg_query($lien, $sql5, __FILE__, __LINE__);
        while ($row5 = mysqli_fetch_array($result5)) {
            $tCandidatsOral[] = $row5['num_table'];
        }

        foreach ($tCandidatsOral as $num_table) {
            $sql = "SELECT (SUM(note*coeff)+0.000)/(SUM(coeff)+0.000) as moyenne2 FROM bg_notes WHERE annee=$annee AND jury='$jury' AND num_table='$num_table' AND id_type_note=3 AND note IS NOT NULL ";
            //            echo $sql;
            $result = bg_query($lien, $sql, __FILE__, __LINE__);
            $row = mysqli_fetch_array($result);
            $moyenne2 = $row['moyenne2'];
            if ($moyenne2 == '') {
                $moyenne2 = 0;
            }

            $tSql4[] = "UPDATE bg_notes notes, bg_resultats res SET moyenne2='$moyenne2', delib2='-' WHERE notes.annee=$annee AND delib1='Oral' AND res.annee=$annee AND res.num_table=notes.num_table AND res.jury=$jury AND notes.id_type_note=3 AND note IS NOT NULL ";

/*            $tSql4[]="REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`, `delib1`, `delib2`)
(SELECT num_table, id_anonyme, id_type_session, annee, jury, SUM(note*coeff), SUM(coeff), SUM(note*coeff)/SUM(coeff) as moyenne, 'Oral','-' \n
FROM bg_notes WHERE annee=$annee AND jury=$jury AND note>=0 AND num_table='$num_table') ";
 */
        }

        $tSql4[] = "UPDATE bg_notes notes, bg_resultats res SET delib2='Oral' WHERE notes.annee=$annee AND delib1='Oral' AND res.annee=$annee AND res.num_table=notes.num_table AND res.jury=$jury AND notes.id_type_note=3 AND note IS NOT NULL AND moyenne2<10 AND moyenne2>=9";
        $tSql4[] = "UPDATE bg_notes notes, bg_resultats res SET delib2='Passable' WHERE notes.annee=$annee AND delib1='Oral' AND res.annee=$annee AND res.num_table=notes.num_table AND res.jury=$jury AND notes.id_type_note=3 AND note IS NOT NULL AND moyenne2>=10 ";
        $tSql4[] = "UPDATE bg_notes notes, bg_resultats res SET delib2='Ajourne' WHERE notes.annee=$annee AND delib1='Oral' AND res.annee=$annee AND res.num_table=notes.num_table AND res.jury=$jury AND notes.id_type_note=3 AND note IS NOT NULL AND moyenne2<9 AND moyenne2>0";
        $tSql4[] = "UPDATE bg_notes notes, bg_resultats res SET delib2='Reserve' WHERE notes.annee=$annee AND delib1='Oral' AND res.annee=$annee AND res.num_table=notes.num_table AND res.jury=$jury AND notes.note<0 AND notes.id_type_note=3 AND note IS NOT NULL AND moyenne>=0 ";

//            $tSql4[]="UPDATE bg_notes notes, bg_resultats res SET delib2='-' WHERE notes.annee=$annee AND delib1='Oral' AND res.annee=$annee AND res.num_table=notes.num_table AND res.jury=$jury AND notes.id_type_note=3 AND note IS NULL ";

        foreach ($tSql4 as $sql4) {
            bg_query($lien, $sql4, __FILE__, __LINE__);
        }
    }
}

function siAbsent($lien, $annee, $num_candidat)
{
    $sql = "SELECT * FROM bg_notes WHERE annee=$annee AND num_table='$num_table' AND id_type_note<=3";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $ret = false;
    while ($row = mysqli_fetch_array($result)) {
        if ($row['note'] < 0) {
            $ret = true;
        }

    }
    return $ret;
}

//Retourne l'ensemble des matieres pour chaque serie d'un jury donné
function getMatieres($lien, $annee, $jury)
{
    $sql = "SELECT mat.matiere, mat.id as id_matiere, ser.serie, ser.id as id_serie, notes.id_type_note \n
			FROM bg_ref_matiere mat, bg_ref_serie ser, bg_notes notes \n
			WHERE notes.annee=$annee AND notes.jury=$jury AND notes.id_serie=ser.id AND notes.id_matiere=mat.id \n
			GROUP BY ser.id, mat.id, notes.id_type_note ORDER BY ser.serie, mat.matiere ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    while ($row = mysqli_fetch_array($result)) {
        $id_serie = $row['id_serie'];
        $id_matiere = $row['id_matiere'];
        $id_type_note = $row['id_type_note'];
        $tMatieres[$id_type_note][$id_serie][$id_matiere] = $row['matiere'];
    }
    return $tMatieres;
}

//Retourne la série d'un jury donné
function getSerie($lien, $annee, $jury)
{
    $sql = "SELECT  ser.id as id_serie \n
			FROM  bg_ref_serie ser, bg_notes notes \n
			WHERE notes.annee=$annee AND notes.jury=$jury AND notes.id_serie=ser.id  \n
			GROUP BY  notes.id_type_note ORDER BY ser.serie ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    while ($row = mysqli_fetch_array($result)) {
        $id_serie = $row['id_serie'];

    }
    return $id_serie;
}

//Retourne le nombre de notes saisies par jury, par série et par type
function getNbNotesSaisies($lien, $annee, $jury)
{
    $sql = "SELECT id_serie, id_matiere, id_type_note, count(*) as nbre \n
			FROM bg_notes \n
			WHERE annee=$annee AND jury=$jury AND note IS NOT NULL
			GROUP BY id_serie, id_matiere, id_type_note ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    while ($row = mysqli_fetch_array($result)) {
        $id_serie = $row['id_serie'];
        $id_matiere = $row['id_matiere'];
        $id_type_note = $row['id_type_note'];
        $tNbNotesSaisies[$id_serie][$id_matiere][$id_type_note] = $row['nbre'];
        $tNbNotesSaisies[$id_serie]['total'][$id_type_note] += $row['nbre'];
    }
    return $tNbNotesSaisies;
}

//Retourne le nombre de candidats à saisir par jury, par série et par type
function getNbNotesASaisir($lien, $annee, $jury)
{
//    if($id_type_note<=2){
    $sql = "SELECT id_serie, id_matiere, id_type_note, count(*) as nbre \n
			FROM bg_notes \n
			WHERE annee=$annee AND jury=$jury
			GROUP BY id_serie, id_matiere, id_type_note ";
//    }
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    while ($row = mysqli_fetch_array($result)) {
        $id_serie = $row['id_serie'];
        $id_matiere = $row['id_matiere'];
        $id_type_note = $row['id_type_note'];
        $tNbNotesASaisir[$id_serie][$id_matiere][$id_type_note] = $row['nbre'];
        $tNbNotesASaisir[$id_serie]['total'][$id_type_note] += $row['nbre'];
    }
    // var_dump($tNbNotesASaisir);
    return $tNbNotesASaisir;

}

function getNote($lien, $annee, $num_table, $id_matiere, $id_type_note)
{
    $sql = "SELECT note FROM bg_notes WHERE annee=$annee AND num_table='$num_table' AND id_matiere='$id_matiere' AND id_type_note='$id_type_note' ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $row = mysqli_fetch_array($result);
    $note = $row['note'];
    return $note;
}

function getJurysTri($lien, $annee, $id_type_session = 0, $id_region = -1)
{
    if ($id_type_session > 0) {
        $andWhere = " AND id_type_session=$id_type_session ";
    } else {
        $andWhere = '';
    }
    if ($id_region != -1) {
        $id_region_division = selectRegionDivisionByRegion($lien, $id_region);
        $sql = "SELECT jury FROM bg_repartition rep,bg_ref_etablissement eta
        WHERE annee=$annee $andWhere AND rep.id_centre=eta.id AND eta.id_region_division=$id_region_division
         GROUP BY jury ORDER BY jury";

    } else {
        $sql = "SELECT jury FROM bg_repartition WHERE annee=$annee $andWhere GROUP BY jury ORDER BY jury";

    }

    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    while ($row = mysqli_fetch_array($result)) {
        $tJurys[] = $row['jury'];
    }
    return $tJurys;

}
function getJurysTriDelib($lien, $annee, $id_type_session = 0, $id_region = -1, $deliberation)
{
    if ($id_type_session > 0) {
        $andWhere = " AND cod.id_type_session=$id_type_session ";
    } else {
        $andWhere = '';
    }
    if ($id_region != -1) {
        $id_region_division = selectRegionDivisionByRegion($lien, $id_region);
        $sql = "SELECT cod.jury FROM bg_repartition rep,bg_ref_etablissement eta,bg_codes cod
        WHERE cod.annee=$annee AND rep.jury=cod.jury AND cod.delib >=$deliberation
        $andWhere AND rep.id_centre=eta.id AND eta.id_region_division=$id_region_division
         GROUP BY cod.jury ORDER BY cod.jury";

    } else {
        $sql = "SELECT cod.jury FROM bg_repartition rep,bg_codes cod
        WHERE cod.annee=$annee AND rep.jury=cod.jury AND cod.delib >=$deliberation $andWhere
        GROUP BY cod.jury ORDER BY cod.jury";

    }

    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    while ($row = mysqli_fetch_array($result)) {
        $tJurys[] = $row['jury'];
    }
    return $tJurys;

}

//Pour s'assurer que tous les jurys sont désanonymes afin de montrer les numeros de jury
function siDelib($lien, $annee)
{
    $sql = "SELECT jury, count(*) FROM bg_repartition WHERE annee=$annee GROUP BY jury";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);

    $sql1 = "SELECT * FROM bg_deliberation WHERE annee=$annee";
    $result1 = bg_query($lien, $sql1, __FILE__, __LINE__);

//    echo mysqli_num_rows($result) .'ZZZ'. mysqli_num_rows($result1)." <br/>";
    if (mysqli_num_rows($result) != mysqli_num_rows($result1)) {
        return false;
    } else {
        return true;
    }

}
function exec_resultats()
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
            case 'Etablissement':
                $isOperateur = false;
                $isInspection = false;
                $isChefEtablissement = true;
                $cd_eta = stripslashes($tab_auteur['email']);
                $tab_cd_eta = explode('@', $cd_eta);
                $code_etablissement = $tab_cd_eta[1];
                //Recherche de la inspection
                $sql = "SELECT eta.id_inspection FROM bg_ref_etablissement eta
						WHERE eta.id='$code_etablissement' LIMIT 0,1 ";
                $result = bg_query($lien, $sql, __FILE__, __LINE__);
                $row = mysqli_fetch_array($result);
                $_REQUEST['id_inspection'] = $id_inspection = $row['id_inspection'];
                $etab = stripslashes($tab_auteur['nom_site']);
                $stats_etablissement = $code_etablissement;
                $andStatut = " AND etablissement='$code_etablissement'";
                $andEtablissementFormulaire = " AND eta.id=$code_etablissement";
                $andEtablissementFormulaire2 = " AND eta.id=$code_etablissement";
                $mois = date('m');
                if ($annee == '') {
                    $annee = recupAnnee($lien); //$annee = date("Y");
                }

                if ($mois > 10 && $annee == date("Y")) {
                    $annee += 1;
                }

                break;
            case 'Encadrant':
                $cd_enca = stripslashes($tab_auteur['email']);
                $tab_cd_enca = explode('@', $cd_enca);
                $code_encadrant = $tab_cd_enca[1];
                $id_region = $code_encadrant;

                $WhereRegion = " WHERE id=$id_region ";

                $andWhereRegion = "WHERE id_region=$id_region";

                //$WhereRegionDivision = "AND ";

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

                $andWhereInspection = "WHERE id_region=$id_region AND id=$idInsp";

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

    if (isAutorise(array('Admin', 'Dre', 'Encadrant', 'Informaticien', 'Inspection'))) {
        $date_delib = recupDateDeliberation($lien, $annee, $id_type_session);
        if ($date_delib == null) {
            $disable_result = " href='" . generer_url_ecrire('resultats') . "&etape=calculap'";
        } else {
            if (checkThePast($date_delib)) {
                $disable_result = "";
            } else {
                $disable_result = "href='" . generer_url_ecrire('resultats') . "&etape=calculap'";
            }
        }
        if ((!isset($_REQUEST['etape']))) {
            echo debut_cadre_trait_couleur('', '', '', "GESTION DES CALCULS /RESULTATS", "", "", false);
            if (($statut == 'Admin') || ($statut == 'Dre') || ($statut == 'Encadrant')) {
                echo debut_boite_info();

                echo "<h1><center><a $disable_result >CALCUL AVANT PROCLAMMATION</a></center></h1>";
                echo fin_boite_info();
                echo debut_boite_info();

                echo "<h1><center><a href='" . generer_url_ecrire('resultats') . "&etape=calculad'>CALCUL APRES DESHANONYMAT</a></center></h1>";
                echo fin_boite_info();

            }

            echo debut_boite_info();

            echo "<h1><center><a href='" . generer_url_ecrire('resultats') . "&etape=resultat'>RESULTATS </a></center></h1>";
            echo fin_boite_info();

        }
        if (isset($_REQUEST['tache']) && ($_GET['tache'] == 'liste') && (isset($_REQUEST['etape'])) && ($_GET['etape'] == 'calculerap')) {
            if ($statut == "Dre") {
                $tJurys = getJurysTri($lien, $annee, $id_type_session, $id_region);
            } else {
                $tJurys = getJurysTri($lien, $annee, $id_type_session, -1);
            }
            //$tJurys = getJurysTri($lien, $annee, $id_type_session);
            $code_jury = getCodeByJury($lien, $annee);

            if ($isOperateurNotes) {$tJurys = $tabJurysOperateur;if (!is_array($tJurys)) {
                if ($statut == "Dre") {
                    $tJurys = getJurysTri($lien, $annee, $id_type_session, $id_region);
                } else {
                    $tJurys = getJurysTri($lien, $annee, $id_type_session, -1);
                }
            }} else {
                if ($statut == "Dre") {
                    $tJurys = getJurysTri($lien, $annee, $id_type_session, $id_region);
                } else {
                    $tJurys = getJurysTri($lien, $annee, $id_type_session, -1);
                }
            }

            $tSeries = getSeries($lien, $annee);

            if (isset($_REQUEST['jury']) && $_REQUEST['jury'] > 0) {
                $deliberation[$jury] = detectDeliberation($lien, $annee, $jury);
                $tMatieres = getMatieres($lien, $annee, $jury);
                $tNbNotesSaisies = getNbNotesSaisies($lien, $annee, $jury);
                $tNbNotesASaisir = getNbNotesASaisir($lien, $annee, $jury);
            }
            $JuryEnclair = siDelib($lien, $annee);
            $code_jury = getCodeByJury($lien, $annee);
            if ($JuryEnclair) {
                foreach ($code_jury as $j => $code) {
                    $code_jury[$j] = $j;
                }
            } else {
                //asort($code_jury);
            }
            $tSeriesJury = $tSeries[$jury];
            echo debut_cadre_trait_couleur('', '', '', "GESTION DES CALCULS DE MOYENNE", "", "", false);

            // $form1 = debut_cadre_enfonce('', '', '', "Tri ");
            // $form1 .= "<form method='POST' name='form_rep'>";
            // $form1 .= " <table>";
            // $form1 .= "<tr><td><center><select style='width:100%'   name='tri_region' onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'region', $tri_region) . "</select></center></td>";
            // $form1 .= "<td><center><select style='width:100%' name='tri_inspection' onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'inspection', $tri_inspection, $andWhereRegion) . "</select></center></td>";
            // $form1 .= "<td><center><select style='width:100%' name='tri_centre' onchange='document.forms.form_rep.submit()'>" . optionsEtablissement($lien, $tri_centre, $andWhereCen, true, ',bg_ref_inspection ins ', '', 'Centre') . "</select></center></td></tr>";

            // $form1 .= "</table> ";

            echo "<center><a href='" . generer_url_ecrire('resultats') . "&etape=calculap';>Retour à mes Calculs</a>";

            // $form1 .= fin_cadre_enfonce();

            $form1 .= debut_cadre_enfonce('', '', '', "Calcul Par Jury ");
            $form1 .= "<FORM name='form_resultats' method='POST' action=''><table size=50%>";
            $form1 .= "<tr><th>Jury</th><th>Série</th><th></th><th></th><th></th></tr> ";
            $form1 .= "<tr><td style='text-align: center;'><select style='width:5em;' name='jury' onchange=\"document.forms['form_resultats'].submit();\">";
            foreach ($tJurys as $j) {
                if ($j == $jury) {
                    $selected = 'selected';
                } else {
                    $selected = '';
                }

                // $form1 .= "<option value='$j' $selected >" . $j . "</option>";
                $form1 .= "<option value='$j' $selected >" . $code_jury[$j] . "</option>";
            }

            $form1 .= "</select></td>";

            $form1 .= "<td style='text-align: center;'><select name='id_serie' onchange=\"document.forms['form_resultats'].submit();\">";
            $form1 .= "<option value='0'>-=[Série]=-</option>";

            foreach ($tSeriesJury as $ser_id2 => $ser) {
                if ($ser_id2 == $id_serie) {
                    $selected = 'selected';
                } else {
                    $selected = '';
                }

                $form1 .= "<option value='$ser_id2' $selected >$ser</option></select></td>";
            }
            $form1 .= "<td><input type='hidden' name='id_type_session' value='$id_type_session' /><input type='hidden' name='annee' value='$annee' /></td>";
            $form1 .= "<td>Tous<input type='radio' name='si_tous' value='non' checked /></td><td><input type='submit' value='OK' name='' /></td></tr>";
            $form1 .= "</table></FORM>";
            $form1 .= fin_cadre_enfonce();
            echo $form1;

            if (isset($_POST['jury']) && $_POST['jury'] > 0 && isset($_POST['id_serie']) && $_POST['id_serie'] > 0) {
                if ($deliberation[$jury] <= 1) {
                    if (($tNbNotesASaisir[$id_serie]['total'][1] > $tNbNotesSaisies[$id_serie]['total'][1])
                    ) {
                        echo debut_boite_alerte() . "Vous n'avez pas fini de saisir les notes des épreuves écrites " . fin_boite_alerte();
                    }
                }
                if ($deliberation[$jury] == 2) {
                    if ($tNbNotesASaisir[$id_serie]['total'][3] > $tNbNotesSaisies[$id_serie]['total'][3]) {
                        echo debut_boite_alerte() . "Vous n'avez pas fini de saisir les notes des épreuves orales " . fin_boite_alerte();
                    }
                    //Suppression des notes des candidats anciennement aptes en EPS et en EF
                    $sql_eps = "DELETE notes FROM bg_notes notes, bg_candidats can
						WHERE can.annee=$annee AND notes.annee=$annee AND can.num_table=notes.num_table
						AND jury='$jury' AND id_type_note=4 AND id_matiere=42 AND can.eps=2";
                    bg_query($lien, $sql_eps, __FILE__, __LINE__);
                }
                $serie = selectReferentiel($lien, 'serie', $id_serie);
                $lien_impressions = "<center><a href='../plugins/fonctions/inc-pdf-grands_releves.php?jury=$jury&id_serie=$id_serie&deliberation=$deliberation[$jury]&tri=$si_tous'>Imprimer le PV du Jury $code_jury[$jury] et de la série $serie</a></center>";

                // $param = selectCodeAno($lien, $annee, $id_type_session);

                resultatJuryAp($lien, $annee, $jury, $deliberation[$jury]);

                if ($deliberation[$jury] == 2) {
                    $tri = " AND res.delib1='Oral'";
                }

                if ($si_tous == 'oui') {
                    $tri = '';
                }

                $sql = "SELECT notes.num_table, notes.id_anonyme, notes.note, notes.id_matiere, notes.id_type_note, mat.matiere, mat.abreviation, notes.coeff, res.total, res.moyenne, res.moyenne2, res.delib1, res.delib2 \n
						FROM bg_notes notes, bg_ref_matiere mat, bg_resultats res \n
						WHERE notes.annee=$annee AND res.annee=$annee AND notes.id_anonyme=res.id_anonyme AND notes.id_matiere=mat.id AND notes.id_type_note!=5 \n
						AND notes.jury=$jury AND res.jury=$jury AND notes.id_serie=$id_serie $tri
						ORDER BY notes.ano, notes.id_type_note, mat.matiere";
                $result = bg_query($lien, $sql, __FILE__, __LINE__);
                $tab = array('num_table', 'id_anonyme', 'note', 'id_matiere', 'id_type_note', 'matiere', 'abreviation', 'coeff', 'total', 'moyenne', 'moyenne2', 'delib1', 'delib2');
                while ($row = mysqli_fetch_array($result)) {
                    foreach ($tab as $val) {
                        $$val = $row[$val];
                    }

                    if ($note >= 0) {
                        $tNotes[$id_anonyme][$id_matiere][$id_type_note]['note'] = $note;
                    } else {
                        $tNotes[$id_anonyme][$id_matiere][$id_type_note]['note'] = 'N/C';
                    }
                    if ($moyenne >= 0) {
                        if ($delib1 == 'Oral' && $moyenne2 > 0) {
                            $tMoyennes[$id_anonyme] = "$moyenne ($moyenne2)";
                        } else {
                            $tMoyennes[$id_anonyme] = $moyenne;
                        }

                        $tTotaux[$id_anonyme] = $total;
                    } else {
                        $tMoyennes[$id_anonyme] = 'N/C';
                        $tTotaux[$id_anonyme] = 'N/C';
                        $tFont[$id_anonyme] = 'red';
                    }
                    if ($delib1 == 'Abandon') {
                        $tFont[$id_anonyme] = 'blue';
                    }

                    if ($deliberation[$jury] <= 1) {
                        $tDecis[$id_anonyme] = $delib1;
                    }

                    if ($deliberation[$jury] == 2 || ($si_tous == 'oui' && $delib1 == 'Oral')) {
                        $tDecis[$id_anonyme] = $delib2;
                    }

                    if ($deliberation[$jury] == 2 && ($si_tous == 'oui' && $delib1 != 'Oral')) {
                        $tDecis[$id_anonyme] = $delib1;
                    }

                    if ($id_type_note == 4) {
                        $type = 'Fac.';
                    } else {
                        $type = selectReferentiel($lien, 'type_note', $id_type_note);
                    }

                    $tCand[$id_anonyme] = $num_table;
                    $tMat[$id_type_note][$id_matiere] = $abreviation . "<br/> $type<br/>$coeff";
                    $tCoeffs[$id_matiere][$id_type_note] = $coeff;
                }

                echo debut_cadre_relief('', '', '', "$lien_impressions");
                echo "<table>";
                $liste = '';
                foreach ($tCand as $id_anonyme => $num_table) {
                    $coeff_total = 0;
                    $font = $tFont[$id_anonyme];
                    if ($deliberation[$jury] == 0) {
                        $candidat = $id_anonyme;
                    } else {
                        $sql = "SELECT nom, prenoms from bg_candidats where annee=$annee AND num_table='$num_table'";
                        //echo $sql;
                        $tNoms = getNoms($lien, $annee, $num_table);
                        $candidat = $num_table . ' ' . $tNoms['noms'];
                    }
                    $liste .= "<tr><td><font color=$font>$candidat</font></td>";
                    $entete = "<th>Candidat</th>";
                    foreach (array('4', '1', '2', '3') as $id_type_note) {
                        foreach ($tMat[$id_type_note] as $id_matiere => $matiere) {
                            $coeff_total += $tCoeffs[$id_matiere][$id_type_note];
                            $entete .= "<th>$matiere</th>";
                            $liste .= "<td><font color=$font>" . $tNotes[$id_anonyme][$id_matiere][$id_type_note]['note'] . "</font></td>";
                        }
                    }
                    $liste .= "<td><font color=$font>" . $tTotaux[$id_anonyme] . "</font></td>";
                    $liste .= "<td><font color=$font>" . $tMoyennes[$id_anonyme] . "</font></td>";
                    $liste .= "<td><font color=$font>" . $tDecis[$id_anonyme] . "</font></td></tr>";
                    $entete .= "<th>Total</th><th>Moyenne</th><th>Résultat</th>";
                }
                echo $entete, $liste;
                echo "</table>";
                echo $lien_impressions;
                echo fin_cadre_relief();
                if (isset($_REQUEST['jury']) && $_REQUEST['jury'] > 0) {
                    echo "<center><a href='" . generer_url_ecrire('resultats') . "&etape=calculerap&tache=liste&jury=$jury&annee=$annee&id_type_session=$id_type_session';>Retour aux matières du Jury $code_jury[$jury]</a></center>";
                }
            }
            echo fin_gauche();

        }

        if (isset($_REQUEST['tache']) && ($_GET['tache'] == 'marge') && (isset($_REQUEST['etape'])) && ($_GET['etape'] == 'calculerap')) {
            if ($statut == "Dre") {
                $tJurys = getJurysTri($lien, $annee, $id_type_session, $id_region);
            } else {
                $tJurys = getJurysTri($lien, $annee, $id_type_session, -1);
            }
            //$tJurys = getJurysTri($lien, $annee, $id_type_session);
            $code_jury = getCodeByJury($lien, $annee);

            if ($isOperateurNotes) {$tJurys = $tabJurysOperateur;if (!is_array($tJurys)) {
                if ($statut == "Dre") {
                    $tJurys = getJurysTri($lien, $annee, $id_type_session, $id_region);
                } else {
                    $tJurys = getJurysTri($lien, $annee, $id_type_session, -1);
                }
            }} else {
                if ($statut == "Dre") {
                    $tJurys = getJurysTri($lien, $annee, $id_type_session, $id_region);
                } else {
                    $tJurys = getJurysTri($lien, $annee, $id_type_session, -1);
                }
            }

            // if ($statut == "Dre") {
            //     $tJurys = getJurysTriDelib($lien, $annee, $id_type_session, $id_region, 0);
            // } else {
            //     $tJurys = getJurysTriDelib($lien, $annee, $id_type_session, -1, 0);
            // }
            // //$tJurys = getJurysTri($lien, $annee, $id_type_session);
            // $code_jury = getCodeByJury($lien, $annee);

            // if ($isOperateurNotes) {$tJurys = $tabJurysOperateur;if (!is_array($tJurys)) {
            //     if ($statut == "Dre") {
            //         $tJurys = getJurysTriDelib($lien, $annee, $id_type_session, $id_region, 0);
            //     } else {
            //         $tJurys = getJurysTriDelib($lien, $annee, $id_type_session, -1, 0);
            //     }
            // }} else {
            //     if ($statut == "Dre") {
            //         $tJurys = getJurysTriDelib($lien, $annee, $id_type_session, $id_region, 0);
            //     } else {
            //         $tJurys = getJurysTriDelib($lien, $annee, $id_type_session, -1, 0);
            //     }
            // }

            $tSeries = getSeries($lien, $annee);

            if (isset($_REQUEST['jury']) && $_REQUEST['jury'] > 0) {
                $deliberation[$jury] = detectDeliberation($lien, $annee, $jury);
                $tMatieres = getMatieres($lien, $annee, $jury);
                $tNbNotesSaisies = getNbNotesSaisies($lien, $annee, $jury);
                $tNbNotesASaisir = getNbNotesASaisir($lien, $annee, $jury);
            }
            $JuryEnclair = siDelib($lien, $annee);
            $code_jury = getCodeByJury($lien, $annee);
            if ($JuryEnclair) {
                foreach ($code_jury as $j => $code) {
                    $code_jury[$j] = $j;
                }
            } else {
                //asort($code_jury);
            }
            $tSeriesJury = $tSeries[$jury];
            echo debut_cadre_trait_couleur('', '', '', "GESTION DES CALCULS DE MOYENNE", "", "", false);

            echo "<center><a href='" . generer_url_ecrire('resultats') . "&etape=calculap';>Retour à mes Calculs</a>";

            // $form1 .= fin_cadre_enfonce();

            $form1 .= debut_cadre_enfonce('', '', '', "Calcul Par Marge de Jury ");
            $form1 .= "<FORM name='form_resultats' method='POST' action=''><table size=50%>";
            $form1 .= " <tr><th>Jury 1</th><th>Jury 2</th><th></th><th></th><th></th> </tr> ";

            $form1 .= "<tr><td style='text-align: center;' ><input type='text' name='jury1' value='$jury1' /></td>
            <td style='text-align: center;'><input type='text' name='jury2' value='$jury2' /></td>";
            $form1 .= "<td><input type='hidden' name='id_type_session' value='$id_type_session' /><input type='hidden' name='annee' value='$annee' />";
            $form1 .= "<td>Tous<input type='radio' name='si_tous' value='non' checked /></td>
            <td><input type='submit' value='OK' name='validator' /></td> </tr>";
            //<td><input type='submit' value='Imprimer' name='imp_marge_jury' /></td>

            $form1 .= "</table></FORM>";
            $form1 .= fin_cadre_enfonce();
            echo $form1;

            if (isset($_POST['jury1']) && $_POST['jury1'] > 0 && isset($_POST['validator'])) {
                echo debut_cadre_trait_couleur(),
                calculMargeJuryAp($lien, $annee, $jury1, $jury2);
                fin_cadre_couleur();

            }
            if (isset($_POST['jury1']) && $_POST['jury1'] > 0 && isset($_POST['imp_marge_jury'])) {
                echo debut_cadre_trait_couleur(),

                $serie = selectReferentiel($lien, 'serie', $id_serie);
                $lien_impressions = "<center><a href='../plugins/fonctions/inc-pdf-grands_releves.php?jury=$jury&id_serie=$id_serie&deliberation=$deliberation[$jury]&tri=$si_tous'>Imprimer le PV du Jury $code_jury[$jury] et de la série $serie</a></center>";

                calculMargeJuryAp($lien, $annee, $jury1, $deliberation[$jury1], $num_table);

                if ($deliberation[$jury] == 2) {
                    $tri = " AND res.delib1='Oral'";
                }

                if ($si_tous == 'oui') {
                    $tri = '';
                }

                $sql = "SELECT notes.num_table, notes.id_anonyme, notes.note, notes.id_matiere, notes.id_type_note, mat.matiere, mat.abreviation, notes.coeff, res.total, res.moyenne, res.moyenne2, res.delib1, res.delib2 \n
						FROM bg_notes notes, bg_ref_matiere mat, bg_resultats res \n
						WHERE notes.annee=$annee AND res.annee=$annee AND notes.id_anonyme=res.id_anonyme AND notes.id_matiere=mat.id AND notes.id_type_note!=5 \n
						AND notes.jury=$jury AND res.jury=$jury AND notes.id_serie=$id_serie $tri
						ORDER BY notes.ano, notes.id_type_note, mat.matiere";
                $result = bg_query($lien, $sql, __FILE__, __LINE__);
                $tab = array('num_table', 'id_anonyme', 'note', 'id_matiere', 'id_type_note', 'matiere', 'abreviation', 'coeff', 'total', 'moyenne', 'moyenne2', 'delib1', 'delib2');
                while ($row = mysqli_fetch_array($result)) {
                    foreach ($tab as $val) {
                        $$val = $row[$val];
                    }

                    if ($note >= 0) {
                        $tNotes[$id_anonyme][$id_matiere][$id_type_note]['note'] = $note;
                    } else {
                        $tNotes[$id_anonyme][$id_matiere][$id_type_note]['note'] = 'N/C';
                    }
                    if ($moyenne >= 0) {
                        if ($delib1 == 'Oral' && $moyenne2 > 0) {
                            $tMoyennes[$id_anonyme] = "$moyenne ($moyenne2)";
                        } else {
                            $tMoyennes[$id_anonyme] = $moyenne;
                        }

                        $tTotaux[$id_anonyme] = $total;
                    } else {
                        $tMoyennes[$id_anonyme] = 'N/C';
                        $tTotaux[$id_anonyme] = 'N/C';
                        $tFont[$id_anonyme] = 'red';
                    }
                    if ($delib1 == 'Abandon') {
                        $tFont[$id_anonyme] = 'blue';
                    }

                    if ($deliberation[$jury] <= 1) {
                        $tDecis[$id_anonyme] = $delib1;
                    }

                    if ($deliberation[$jury] == 2 || ($si_tous == 'oui' && $delib1 == 'Oral')) {
                        $tDecis[$id_anonyme] = $delib2;
                    }

                    if ($deliberation[$jury] == 2 && ($si_tous == 'oui' && $delib1 != 'Oral')) {
                        $tDecis[$id_anonyme] = $delib1;
                    }

                    if ($id_type_note == 4) {
                        $type = 'Fac.';
                    } else {
                        $type = selectReferentiel($lien, 'type_note', $id_type_note);
                    }

                    $tCand[$id_anonyme] = $num_table;
                    $tMat[$id_type_note][$id_matiere] = $abreviation . "<br/> $type<br/>$coeff";
                    $tCoeffs[$id_matiere][$id_type_note] = $coeff;
                }
                echo debut_cadre_relief('', '', '', "$lien_impressions");
                echo "<table>";
                $liste = '';
                foreach ($tCand as $id_anonyme => $num_table) {
                    $coeff_total = 0;
                    $font = $tFont[$id_anonyme];
                    if ($deliberation[$jury] == 0) {
                        $candidat = $id_anonyme;
                    } else {
                        $sql = "SELECT nom, prenoms from bg_candidats where annee=$annee AND num_table='$num_table'";
                        //echo $sql;
                        $tNoms = getNoms($lien, $annee, $num_table);
                        $candidat = $num_table . ' ' . $tNoms['noms'];
                    }
                    $liste .= "<tr><td><font color=$font>$candidat</font></td>";
                    $entete = "<th>Candidat</th>";
                    foreach (array('4', '1', '2', '3') as $id_type_note) {
                        foreach ($tMat[$id_type_note] as $id_matiere => $matiere) {
                            $coeff_total += $tCoeffs[$id_matiere][$id_type_note];
                            $entete .= "<th>$matiere</th>";
                            $liste .= "<td><font color=$font>" . $tNotes[$id_anonyme][$id_matiere][$id_type_note]['note'] . "</font></td>";
                        }
                    }
                    $liste .= "<td><font color=$font>" . $tTotaux[$id_anonyme] . "</font></td>";
                    $liste .= "<td><font color=$font>" . $tMoyennes[$id_anonyme] . "</font></td>";
                    $liste .= "<td><font color=$font>" . $tDecis[$id_anonyme] . "</font></td></tr>";
                    $entete .= "<th>Total</th><th>Moyenne</th><th>Résultat</th>";
                }
                echo $entete, $liste;
                echo "</table>";
                echo $lien_impressions;
                echo fin_cadre_relief();

                fin_cadre_couleur();

            }
            echo fin_gauche();

        }

        if (isset($_REQUEST['tache']) && ($_GET['tache'] == 'liste') && (isset($_REQUEST['etape'])) && ($_GET['etape'] == 'calculerad')) {

            if ($statut == "Dre") {
                $tJurys = getJurysTriDelib($lien, $annee, $id_type_session, $id_region, 1);
            } else {
                $tJurys = getJurysTriDelib($lien, $annee, $id_type_session, -1, 1);
            }
            //$tJurys = getJurysTri($lien, $annee, $id_type_session);
            $code_jury = getCodeByJury($lien, $annee);

            if ($isOperateurNotes) {$tJurys = $tabJurysOperateur;if (!is_array($tJurys)) {
                if ($statut == "Dre") {
                    $tJurys = getJurysTriDelib($lien, $annee, $id_type_session, $id_region, 1);
                } else {
                    $tJurys = getJurysTriDelib($lien, $annee, $id_type_session, -1, 1);
                }
            }} else {
                if ($statut == "Dre") {
                    $tJurys = getJurysTriDelib($lien, $annee, $id_type_session, $id_region, 1);
                } else {
                    $tJurys = getJurysTriDelib($lien, $annee, $id_type_session, -1, 1);
                }
            }

            $tSeries = getSeries($lien, $annee);

            if (isset($_REQUEST['jury']) && $_REQUEST['jury'] > 0) {
                $deliberation[$jury] = detectDeliberation($lien, $annee, $jury);
                $tMatieres = getMatieres($lien, $annee, $jury);
                $tNbNotesSaisies = getNbNotesSaisies($lien, $annee, $jury);
                $tNbNotesASaisir = getNbNotesASaisir($lien, $annee, $jury);
            }
            $JuryEnclair = siDelib($lien, $annee);
            $code_jury = getCodeByJury($lien, $annee);
            if ($JuryEnclair) {
                foreach ($code_jury as $j => $code) {
                    $code_jury[$j] = $j;
                }
            } else {
                //asort($code_jury);
            }
            $tSeriesJury = $tSeries[$jury];
            echo debut_cadre_trait_couleur('', '', '', "GESTION DES CALCULS DE MOYENNE", "", "", false);

            // $form1 = debut_cadre_enfonce('', '', '', "Tri ");
            // $form1 .= "<form method='POST' name='form_rep'>";
            // $form1 .= " <table>";
            // $form1 .= "<tr><td><center><select style='width:100%'   name='tri_region' onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'region', $tri_region) . "</select></center></td>";
            // $form1 .= "<td><center><select style='width:100%' name='tri_inspection' onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'inspection', $tri_inspection, $andWhereRegion) . "</select></center></td>";
            // $form1 .= "<td><center><select style='width:100%' name='tri_centre' onchange='document.forms.form_rep.submit()'>" . optionsEtablissement($lien, $tri_centre, $andWhereCen, true, ',bg_ref_inspection ins ', '', 'Centre') . "</select></center></td></tr>";

            // $form1 .= "</table> ";

            echo "<center><a href='" . generer_url_ecrire('resultats') . "&etape=calculad';>Retour à mes Calculs</a>";

            // $form1 .= fin_cadre_enfonce();

            $form1 .= debut_cadre_enfonce('', '', '', "Calcul Par Jury ");
            $form1 .= "<FORM name='form_resultats' method='POST' action=''><table size=50%>";
            $form1 .= "<tr><th>Jury</th><th>Série</th><th></th><th></th><th></th></tr> ";
            $form1 .= "<tr><td style='text-align: center;'><select style='width:5em;' name='jury' onchange=\"document.forms['form_resultats'].submit();\">";
            foreach ($tJurys as $j) {
                if ($j == $jury) {
                    $selected = 'selected';
                } else {
                    $selected = '';
                }

                // $form1 .= "<option value='$j' $selected >" . $j . "</option>";
                $form1 .= "<option value='$j' $selected >" . $code_jury[$j] . "</option>";
            }

            $form1 .= "</select></td>";

            $form1 .= "<td style='text-align: center;'><select name='id_serie' onchange=\"document.forms['form_resultats'].submit();\">";
            $form1 .= "<option value='0'>-=[Série]=-</option>";

            foreach ($tSeriesJury as $ser_id2 => $ser) {
                if ($ser_id2 == $id_serie) {
                    $selected = 'selected';
                } else {
                    $selected = '';
                }

                $form1 .= "<option value='$ser_id2' $selected >$ser</option></select></td>";
            }
            $form1 .= "<td><input type='hidden' name='id_type_session' value='$id_type_session' /><input type='hidden' name='annee' value='$annee' /></td>";
            $form1 .= "<td>Tous<input type='radio' name='si_tous' value='non' checked /></td><td><input type='submit' value='OK' name='' /></td></tr>";
            $form1 .= "</table></FORM>";
            $form1 .= fin_cadre_enfonce();
            echo $form1;

            if (isset($_POST['jury']) && $_POST['jury'] > 0 && isset($_POST['id_serie']) && $_POST['id_serie'] > 0) {
                if ($deliberation[$jury] <= 1) {
                    if (($tNbNotesASaisir[$id_serie]['total'][1] > $tNbNotesSaisies[$id_serie]['total'][1])
                    ) {
                        echo debut_boite_alerte() . "Vous n'avez pas fini de saisir les notes des épreuves écrites " . fin_boite_alerte();
                    }
                }
                if ($deliberation[$jury] == 2) {
                    if ($tNbNotesASaisir[$id_serie]['total'][3] > $tNbNotesSaisies[$id_serie]['total'][3]) {
                        echo debut_boite_alerte() . "Vous n'avez pas fini de saisir les notes des épreuves orales " . fin_boite_alerte();
                    }
                    //Suppression des notes des candidats anciennement aptes en EPS et en EF
                    $sql_eps = "DELETE notes FROM bg_notes notes, bg_candidats can
						WHERE can.annee=$annee AND notes.annee=$annee AND can.num_table=notes.num_table
						AND jury='$jury' AND id_type_note=4 AND id_matiere=42 AND can.eps=2";
                    bg_query($lien, $sql_eps, __FILE__, __LINE__);
                }
                $serie = selectReferentiel($lien, 'serie', $id_serie);
                $lien_impressions = "<center><a href='../plugins/fonctions/inc-pdf-grands_releves.php?jury=$jury&id_serie=$id_serie&deliberation=$deliberation[$jury]&tri=$si_tous'>Imprimer le PV du Jury $code_jury[$jury] et de la série $serie</a></center>";

                // $param = selectCodeAno($lien, $annee, $id_type_session);
                resultatJuryAd($lien, $annee, $jury, $deliberation[$jury]);
                if ($deliberation[$jury] == 2) {
                    $tri = " AND res.delib1='Oral'";
                }

                if ($si_tous == 'oui') {
                    $tri = '';
                }

                $sql = "SELECT notes.num_table, notes.id_anonyme, notes.note, notes.id_matiere, notes.id_type_note, mat.matiere, mat.abreviation, notes.coeff, res.total, res.moyenne, res.moyenne2, res.delib1, res.delib2 \n
						FROM bg_notes notes, bg_ref_matiere mat, bg_resultats res \n
						WHERE notes.annee=$annee AND res.annee=$annee AND notes.id_anonyme=res.id_anonyme AND notes.id_matiere=mat.id AND notes.id_type_note!=5 \n
						AND notes.jury=$jury AND res.jury=$jury AND notes.id_serie=$id_serie $tri
						ORDER BY notes.ano, notes.id_type_note, mat.matiere";
                $result = bg_query($lien, $sql, __FILE__, __LINE__);
                $tab = array('num_table', 'id_anonyme', 'note', 'id_matiere', 'id_type_note', 'matiere', 'abreviation', 'coeff', 'total', 'moyenne', 'moyenne2', 'delib1', 'delib2');
                while ($row = mysqli_fetch_array($result)) {
                    foreach ($tab as $val) {
                        $$val = $row[$val];
                    }

                    if ($note >= 0) {
                        $tNotes[$id_anonyme][$id_matiere][$id_type_note]['note'] = $note;
                    } else {
                        $tNotes[$id_anonyme][$id_matiere][$id_type_note]['note'] = 'N/C';
                    }
                    if ($moyenne >= 0) {
                        if ($delib1 == 'Oral' && $moyenne2 > 0) {
                            $tMoyennes[$id_anonyme] = "$moyenne ($moyenne2)";
                        } else {
                            $tMoyennes[$id_anonyme] = $moyenne;
                        }

                        $tTotaux[$id_anonyme] = $total;
                    } else {
                        $tMoyennes[$id_anonyme] = 'N/C';
                        $tTotaux[$id_anonyme] = 'N/C';
                        $tFont[$id_anonyme] = 'red';
                    }
                    if ($delib1 == 'Abandon') {
                        $tFont[$id_anonyme] = 'blue';
                    }

                    if ($deliberation[$jury] <= 1) {
                        $tDecis[$id_anonyme] = $delib1;
                    }

                    if ($deliberation[$jury] == 2 || ($si_tous == 'oui' && $delib1 == 'Oral')) {
                        $tDecis[$id_anonyme] = $delib2;
                    }

                    if ($deliberation[$jury] == 2 && ($si_tous == 'oui' && $delib1 != 'Oral')) {
                        $tDecis[$id_anonyme] = $delib1;
                    }

                    if ($id_type_note == 4) {
                        $type = 'Fac.';
                    } else {
                        $type = selectReferentiel($lien, 'type_note', $id_type_note);
                    }

                    $tCand[$id_anonyme] = $num_table;
                    $tMat[$id_type_note][$id_matiere] = $abreviation . "<br/> $type<br/>$coeff";
                    $tCoeffs[$id_matiere][$id_type_note] = $coeff;
                }

                echo debut_cadre_relief('', '', '', "$lien_impressions");
                echo "<table>";
                $liste = '';
                foreach ($tCand as $id_anonyme => $num_table) {
                    $coeff_total = 0;
                    $font = $tFont[$id_anonyme];
                    if ($deliberation[$jury] == 0) {
                        $candidat = $id_anonyme;
                    } else {
                        $sql = "SELECT nom, prenoms from bg_candidats where annee=$annee AND num_table='$num_table'";
                        //echo $sql;
                        $tNoms = getNoms($lien, $annee, $num_table);
                        $candidat = $num_table . ' ' . $tNoms['noms'];
                    }
                    $liste .= "<tr><td><font color=$font>$candidat</font></td>";
                    $entete = "<th>Candidat</th>";
                    foreach (array('4', '1', '2', '3') as $id_type_note) {
                        foreach ($tMat[$id_type_note] as $id_matiere => $matiere) {
                            $coeff_total += $tCoeffs[$id_matiere][$id_type_note];
                            $entete .= "<th>$matiere</th>";
                            $liste .= "<td><font color=$font>" . $tNotes[$id_anonyme][$id_matiere][$id_type_note]['note'] . "</font></td>";
                        }
                    }
                    $liste .= "<td><font color=$font>" . $tTotaux[$id_anonyme] . "</font></td>";
                    $liste .= "<td><font color=$font>" . $tMoyennes[$id_anonyme] . "</font></td>";
                    $liste .= "<td><font color=$font>" . $tDecis[$id_anonyme] . "</font></td></tr>";
                    $entete .= "<th>Total</th><th>Moyenne</th><th>Résultat</th>";
                }
                echo $entete, $liste;
                echo "</table>";
                echo $lien_impressions;
                echo fin_cadre_relief();
                if (isset($_REQUEST['jury']) && $_REQUEST['jury'] > 0) {
                    echo "<center><a href='" . generer_url_ecrire('resultats') . "&etape=calculerad&tache=liste&jury=$jury&annee=$annee&id_type_session=$id_type_session';>Retour aux matières du Jury $code_jury[$jury]</a></center>";
                }
            }
            echo fin_gauche();

        }

        if (isset($_REQUEST['tache']) && ($_GET['tache'] == 'marge') && (isset($_REQUEST['etape'])) && ($_GET['etape'] == 'calculer')) {
            echo debut_cadre_trait_couleur('', '', '', "GESTION DES CALCULS DE MOYENNE", "", "", false);

            echo "<center><a href='" . generer_url_ecrire('resultats') . "&etape=calculap';>Retour à mes Calculs</a>";

            // $form1 .= fin_cadre_enfonce();

            $form1 .= debut_cadre_enfonce('', '', '', "Calcul Par Marge de Jury ");
            $form1 .= "<FORM name='form_resultats' method='POST' action=''><table size=50%>";
            $form1 .= " <tr><th>Jury 1</th><th>Jury 2</th><th></th><th></th><th></th> </tr> ";

            $form1 .= "<tr><td style='text-align: center;' ><input type='text' name='jury1' value='$jury1' /></td>
            <td style='text-align: center;'><input type='text' name='jury2' value='$jury2' /></td>";
            $form1 .= "<td><input type='hidden' name='id_type_session' value='$id_type_session' /><input type='hidden' name='annee' value='$annee' />";
            $form1 .= "<td>Tous<input type='radio' name='si_tous' value='non' checked /></td>
            <td><input type='submit' value='OK' name='validator' /></td> </tr>";
            //<td><input type='submit' value='Imprimer' name='imp_marge_jury' /></td>

            $form1 .= "</table></FORM>";
            $form1 .= fin_cadre_enfonce();
            echo $form1;

            if (isset($_POST['jury1']) && $_POST['jury1'] > 0 && isset($_POST['validator'])) {
                echo debut_cadre_trait_couleur(),
                calculMargeJuryAp($lien, $annee, $jury1, $jury2);
                fin_cadre_couleur();

            }
            if (isset($_POST['jury1']) && $_POST['jury1'] > 0 && isset($_POST['imp_marge_jury'])) {
                echo debut_cadre_trait_couleur(),

                $serie = selectReferentiel($lien, 'serie', $id_serie);
                $lien_impressions = "<center><a href='../plugins/fonctions/inc-pdf-grands_releves.php?jury=$jury&id_serie=$id_serie&deliberation=$deliberation[$jury]&tri=$si_tous'>Imprimer le PV du Jury $code_jury[$jury] et de la série $serie</a></center>";

                calculMargeJuryAp($lien, $annee, $jury1, $deliberation[$jury1], $num_table);

                if ($deliberation[$jury] == 2) {
                    $tri = " AND res.delib1='Oral'";
                }

                if ($si_tous == 'oui') {
                    $tri = '';
                }

                $sql = "SELECT notes.num_table, notes.id_anonyme, notes.note, notes.id_matiere, notes.id_type_note, mat.matiere, mat.abreviation, notes.coeff, res.total, res.moyenne, res.moyenne2, res.delib1, res.delib2 \n
						FROM bg_notes notes, bg_ref_matiere mat, bg_resultats res \n
						WHERE notes.annee=$annee AND res.annee=$annee AND notes.id_anonyme=res.id_anonyme AND notes.id_matiere=mat.id AND notes.id_type_note!=5 \n
						AND notes.jury=$jury AND res.jury=$jury AND notes.id_serie=$id_serie $tri
						ORDER BY notes.ano, notes.id_type_note, mat.matiere";
                $result = bg_query($lien, $sql, __FILE__, __LINE__);
                $tab = array('num_table', 'id_anonyme', 'note', 'id_matiere', 'id_type_note', 'matiere', 'abreviation', 'coeff', 'total', 'moyenne', 'moyenne2', 'delib1', 'delib2');
                while ($row = mysqli_fetch_array($result)) {
                    foreach ($tab as $val) {
                        $$val = $row[$val];
                    }

                    if ($note >= 0) {
                        $tNotes[$id_anonyme][$id_matiere][$id_type_note]['note'] = $note;
                    } else {
                        $tNotes[$id_anonyme][$id_matiere][$id_type_note]['note'] = 'N/C';
                    }
                    if ($moyenne >= 0) {
                        if ($delib1 == 'Oral' && $moyenne2 > 0) {
                            $tMoyennes[$id_anonyme] = "$moyenne ($moyenne2)";
                        } else {
                            $tMoyennes[$id_anonyme] = $moyenne;
                        }

                        $tTotaux[$id_anonyme] = $total;
                    } else {
                        $tMoyennes[$id_anonyme] = 'N/C';
                        $tTotaux[$id_anonyme] = 'N/C';
                        $tFont[$id_anonyme] = 'red';
                    }
                    if ($delib1 == 'Abandon') {
                        $tFont[$id_anonyme] = 'blue';
                    }

                    if ($deliberation[$jury] <= 1) {
                        $tDecis[$id_anonyme] = $delib1;
                    }

                    if ($deliberation[$jury] == 2 || ($si_tous == 'oui' && $delib1 == 'Oral')) {
                        $tDecis[$id_anonyme] = $delib2;
                    }

                    if ($deliberation[$jury] == 2 && ($si_tous == 'oui' && $delib1 != 'Oral')) {
                        $tDecis[$id_anonyme] = $delib1;
                    }

                    if ($id_type_note == 4) {
                        $type = 'Fac.';
                    } else {
                        $type = selectReferentiel($lien, 'type_note', $id_type_note);
                    }

                    $tCand[$id_anonyme] = $num_table;
                    $tMat[$id_type_note][$id_matiere] = $abreviation . "<br/> $type<br/>$coeff";
                    $tCoeffs[$id_matiere][$id_type_note] = $coeff;
                }
                echo debut_cadre_relief('', '', '', "$lien_impressions");
                echo "<table>";
                $liste = '';
                foreach ($tCand as $id_anonyme => $num_table) {
                    $coeff_total = 0;
                    $font = $tFont[$id_anonyme];
                    if ($deliberation[$jury] == 0) {
                        $candidat = $id_anonyme;
                    } else {
                        $sql = "SELECT nom, prenoms from bg_candidats where annee=$annee AND num_table='$num_table'";
                        //echo $sql;
                        $tNoms = getNoms($lien, $annee, $num_table);
                        $candidat = $num_table . ' ' . $tNoms['noms'];
                    }
                    $liste .= "<tr><td><font color=$font>$candidat</font></td>";
                    $entete = "<th>Candidat</th>";
                    foreach (array('4', '1', '2', '3') as $id_type_note) {
                        foreach ($tMat[$id_type_note] as $id_matiere => $matiere) {
                            $coeff_total += $tCoeffs[$id_matiere][$id_type_note];
                            $entete .= "<th>$matiere</th>";
                            $liste .= "<td><font color=$font>" . $tNotes[$id_anonyme][$id_matiere][$id_type_note]['note'] . "</font></td>";
                        }
                    }
                    $liste .= "<td><font color=$font>" . $tTotaux[$id_anonyme] . "</font></td>";
                    $liste .= "<td><font color=$font>" . $tMoyennes[$id_anonyme] . "</font></td>";
                    $liste .= "<td><font color=$font>" . $tDecis[$id_anonyme] . "</font></td></tr>";
                    $entete .= "<th>Total</th><th>Moyenne</th><th>Résultat</th>";
                }
                echo $entete, $liste;
                echo "</table>";
                echo $lien_impressions;
                echo fin_cadre_relief();

                fin_cadre_couleur();

            }
            echo fin_gauche();

        }
        if (isset($_REQUEST['tache']) && ($_GET['tache'] == 'centre') && (isset($_REQUEST['etape'])) && ($_GET['etape'] == 'calculer')) {
            echo debut_cadre_trait_couleur('', '', '', "GESTION DES CALCULS DE MOYENNE", "", "", false);

            echo "<center><a href='" . generer_url_ecrire('resultats') . "&etape=calculap';>Retour à mes Calculs</a>";
            $andWhereCen .= " AND eta.si_centre='oui' ";
            if (($statut == 'Dre') || ($statut == 'Encadrant')) {
                $andWhereCen .= " AND eta.si_centre='oui' AND ins.id_region=reg.id AND reg.id=$id_region";
            }
            if (isset($_POST['tri_centre']) && $_POST['tri_centre'] > 0) {

            }

            if (isset($_POST['tri_inspection']) && $_POST['tri_inspection'] > 0) {
                $andWhereCen .= " AND eta.si_centre='oui' AND ins.id=eta.id_inspection AND eta.id_inspection=$tri_inspection ";
            }

            $form1 .= debut_cadre_enfonce('', '', '', "Calcul Par Centre");
            $form1 .= "<FORM name='form_rep' method='POST' action=''><table size=50%>";
            $form1 .= "<th>Inspection</th><th>Centre</th><th></th><th></th><th></th> ";

            // $form1 .= "<tr><td><input type='text' name='jury1' value='$jury1' /></td>
            // <td><input type='text' name='jury2' value='$jury2' />";
            if ($statut == "Admin") {
                $form1 .= "<tr><td><center><select   name='tri_inspection' onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'inspection', $tri_inspection) . "</select></center></td>";
                $form1 .= "<td><center><select  name='tri_centre' onchange='document.forms.form_rep.submit()'>" . optionsEtablissement($lien, $tri_centre, $andWhereCen, true, ',bg_ref_inspection ins ', '', 'Centre') . "</select></center></td>";

            }
            if (($statut == 'Dre') || ($statut == 'Encadrant')) {
                $andInspection = " WHERE id_region=$id_region ";
                $form1 .= "<tr><td><center><select   name='tri_inspection' onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'inspection', $tri_inspection, $andInspection) . "</select></center></td>";
                $form1 .= "<td><center><select  name='tri_centre' onchange='document.forms.form_rep.submit()'>" . optionsEtablissement($lien, $tri_centre, $andWhereCen, true, ',bg_ref_inspection ins', '', 'Centre') . "</select></center></td>";

            }

            $form1 .= "<td><input type='hidden' name='id_type_session' value='$id_type_session' /><input type='hidden' name='annee' value='$annee' /></td>";
            $form1 .= "<td>Tous<input type='radio' name='si_tous' value='non' checked /></td>
            <td><input type='submit' value='OK' name='valide_centre' /></td></tr>";
            $form1 .= "</table></FORM>";
            $form1 .= fin_cadre_enfonce();
            echo $form1;
            if (isset($_POST['valide_centre'])) {
                echo debut_cadre_trait_couleur();
                // var_dump($tri_centre);

                //selectionner les jury du centre choisie
                $sql1 = "SELECT *  FROM bg_repartition WHERE id_centre=$tri_centre GROUP BY jury ";
                $result1 = bg_query($lien, $sql1, __FILE__, __LINE__);
                while ($row1 = mysqli_fetch_array($result1)) {
                    $jury = $row1['jury'];
                    //  var_dump("jury : " . $jury);
                    $tabJury[] .= $jury;

                }

                // var_dump("first tab " . $tabJury[0]);
                // var_dump("last tab " . $tabJury[count($tabJury) - 1]);
                $jury1 = $tabJury[0];
                $jury2 = $tabJury[count($tabJury) - 1];

                calculMargeJuryAp($lien, $annee, $jury1, $jury2);
                echo fin_cadre_trait_couleur();

            }
            echo fin_gauche();

        }
        if (isset($_REQUEST['tache']) && ($_GET['tache'] == 'inspection') && (isset($_REQUEST['etape'])) && ($_GET['etape'] == 'calculer')) {
            echo debut_cadre_trait_couleur('', '', '', "GESTION DES CALCULS DE MOYENNE", "", "", false);

            echo "<center><a href='" . generer_url_ecrire('resultats') . "&etape=calculap';>Retour à mes Calculs</a>";

            if (isset($_POST['tri_region']) && $_POST['tri_region'] > 0) {
                $andRegion .= " WHERE id_region=$tri_region ";
            }

            $form1 .= debut_cadre_enfonce('', '', '', "Calcul Par Inspection");
            $form1 .= "<FORM name='form_rep' method='POST' action=''><table size=50%>";
            $form1 .= "<th>Region</th><th>Inspection</th><th></th><th></th><th></th> ";

            // $form1 .= "<tr><td><input type='text' name='jury1' value='$jury1' /></td>
            // <td><input type='text' name='jury2' value='$jury2' />";
            if ($statut == "Admin") {
                $form1 .= "<tr><td><center><select   name='tri_region' onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'region', $tri_region) . "</select></center></td>";
                $form1 .= "<td><center><select   name='tri_inspection' onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'inspection', $tri_inspection, $andRegion) . "</select></center></td>";

            }
            if (($statut == 'Dre') || ($statut == 'Encadrant')) {
                $andIdRegion = " WHERE id=$id_region ";
                $andInspection = " WHERE id_region=$id_region";
                $form1 .= "<tr><td><center><select   name='tri_region' onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'region', $tri_region, $andIdRegion) . "</select></center></td>";
                $form1 .= "<td><center><select   name='tri_inspection' onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'inspection', $tri_inspection, $andInspection) . "</select></center></td>";

            }

            $form1 .= "<td><input type='hidden' name='id_type_session' value='$id_type_session' /><input type='hidden' name='annee' value='$annee' /></td>";
            $form1 .= "<td>Tous<input type='radio' name='si_tous' value='non' checked /></td>
            <td><input type='submit' value='OK' name='valide_inspection' /></td></tr>";
            $form1 .= "</table></FORM>";
            $form1 .= fin_cadre_enfonce();
            echo $form1;
            if (isset($_POST['valide_inspection'])) {
                echo debut_cadre_trait_couleur();
                $listeCentres = getCentreByInspection($lien, $tri_inspection);

                foreach ($listeCentres as $valeur) {
                    echo ("\n Centre : " . recupCentreById($lien, $valeur) . "\n");
                    //  $tabJury[] = $jury;
                    //selectionner les jury du centre choisie
                    $sql1 = "SELECT *  FROM bg_repartition WHERE id_centre=$valeur GROUP BY jury ";
                    $result1 = bg_query($lien, $sql1, __FILE__, __LINE__);

                    while ($row1 = mysqli_fetch_array($result1)) {
                        $jury = $row1['jury'];
                        // var_dump("jury : " . $jury . "\n");
                        calculMargeJuryAp($lien, $annee, $jury, false);
                        // $tabJury[] = $jury;

                    }

                }

                echo fin_cadre_trait_couleur();

            }
            echo fin_gauche();

        }
        if (isset($_REQUEST['tache']) && ($_GET['tache'] == 'region') && (isset($_REQUEST['etape'])) && ($_GET['etape'] == 'calculer')) {

            echo debut_cadre_trait_couleur('', '', '', "GESTION DES CALCULS DE MOYENNE", "", "", false);
            echo '<script type="text/javascript">',
            '$(document).ready(function() {',
            'var selectregion = document.getElementById ("id_region_division");',
            'var newOption = new Option ("NATIONAL", "-1");',
            'selectregion.options.add (newOption);',
            '});',
                '</script>'
            ;
            echo "<center><a href='" . generer_url_ecrire('resultats') . "&etape=calculap';>Retour à mes Calculs</a>";

            if (isset($_POST['id_region_division']) && $_POST['id_region_division'] > 0) {

            }

            $form1 .= debut_cadre_enfonce('', '', '', "Calcul Par Region");
            $form1 .= "<FORM name='form_reg' method='POST' action=''><table size=50%>";
            $form1 .= "<th>Region</th><th></th><th></th><th></th> ";
            // $form1 .= "<tr><td><center><select   name='tri_region' onchange='document.forms.form_reg.submit()'>" . optionsReferentiel($lien, 'region', $tri_region) . "</select></center></td>";
            $form1 .= "<tr><td><center><select   name='id_region_division' id='id_region_division' onchange='document.forms.form_reg.submit()'>" . optionsReferentiel($lien, 'region_division', $id_region_division) . "</select></center></td>";

            $form1 .= "<td><input type='hidden' name='id_type_session' value='$id_type_session' /><input type='hidden' name='annee' value='$annee' /></td>";
            $form1 .= "<td>Tous<input type='radio' name='si_tous' value='non' checked /></td><td><input type='submit' value='OK' name='valide_region' /></td></tr>";
            $form1 .= "</table></FORM>";
            $form1 .= fin_cadre_enfonce();
            echo $form1;
            if (isset($_POST['valide_region'])) {
                echo debut_cadre_trait_couleur();
                $listeCentres = getCentreByRegionDivision($lien, $id_region_division);

                foreach ($listeCentres as $valeur) {
                    echo ("\n Centre : " . recupCentreById($lien, $valeur) . "\n");
                    //  $tabJury[] = $jury;
                    //selectionner les jury du centre choisie
                    $sql1 = "SELECT *  FROM bg_repartition WHERE id_centre=$valeur GROUP BY jury ";
                    $result1 = bg_query($lien, $sql1, __FILE__, __LINE__);

                    while ($row1 = mysqli_fetch_array($result1)) {
                        $jury = $row1['jury'];
                        // var_dump("jury : " . $jury . "\n");
                        calculMargeJuryAp($lien, $annee, $jury, false);
                        // $tabJury[] = $jury;

                    }

                }
                echo fin_cadre_trait_couleur();

            }
            echo fin_gauche(), fin_gauche();

        }
        if (isset($_REQUEST['etape']) && ($_REQUEST['etape'] == 'calculap')) {
            echo debut_gauche();

            if (($statut == 'Admin') || ($statut == 'Dre') || ($statut == 'Encadrant')) {
                if ($statut == 'Admin') {
                    echo debut_boite_info();
                    echo "<p>Gestion des Matières</p>";
                    echo "<p><a href='" . generer_url_ecrire('resultats') . "&etape=calculap&tache=importsport'>Importation Notes Sport </a></p>";
                    // echo "<p><a href='" . generer_url_ecrire('resultats') . "&etape=calculap&tache=matfac'>Listes Notes MF Exédentaires </a></p>";
                    // echo "<p><a href='" . generer_url_ecrire('resultats') . "&etape=calculap&tache=supfac'>Supprimer Notes MF Exédentaires  </a></p>";
                    echo fin_boite_info();
                }
                echo debut_boite_info();
                echo "<p>Gestion des Calculs</p>";
                echo "<p><a href='" . generer_url_ecrire('resultats') . "&etape=calculerap&tache=liste'>Calcul par Jury </a></p>";
                if ($statut == 'Admin') {echo "<p><a href='" . generer_url_ecrire('resultats') . "&etape=calculerap&tache=marge' class='ajax'>Calcul par marge jury</a></p>";}
                echo fin_boite_info();

                echo debut_boite_info();
                echo "<p>Gestion des Fraudes/Abandons/Absences</p>";
                echo "<p><a href='" . generer_url_ecrire('resultats') . "&etape=calculap&tache=fraude'>Attribution des Faudeurs </a></p>";
                echo fin_boite_info();
            }

            echo debut_boite_info();
            echo "<p><a href='" . generer_url_ecrire('resultats') . "' class='ajax'>Retour au Menu Principale</a></p>";

            //echo "<form method='POST'><input name='detruire_session' type='submit' value='Changer de session' /></form>";
            echo fin_boite_info();

            //  }
            echo debut_droite();

            echo debut_cadre_trait_couleur('', '', '', "CALCULS AVANT PROCLAMMATION", "", "", false);
            if (isset($_REQUEST['etape']) && ($_GET['etape'] == 'calculap') && !isset($_REQUEST['tache'])) {
                $form .= "<p><center><a href='./?exec=resultats'>Retour au Menu principal</a></center></p>";
                $form .= debut_cadre_couleur('', '', '', "Calcul des Résultats ");
                $form .= "<table>";
                $form .= "<tr><td><input type='image' src='../plugins/images/calcul1.jpg' width='75px' /> <br/><a href='" . generer_url_ecrire('resultats') . "&etape=calculerap&tache=liste'>Calcul par Jury </a></td>
                  <td><input type='image' src='../plugins/images/calcul2.jpg' width='75px'  /> <br/><a href='" . generer_url_ecrire('resultats') . "&etape=calculerap&tache=marge'>Calcul par Marge de Jury </a></td></tr>";
                // $form .= "<tr><td><input type='image' src='../plugins/images/calcul3.jpg' width='75px'  /> <br/><a href='" . generer_url_ecrire('resultats') . "&etape=calculerap&tache=centre'>Calcul par Centre de Jury </a></td>
                //         <td><input type='image' src='../plugins/images/calcul8.jpg' width='75px' /> <br/><a href='" . generer_url_ecrire('resultats') . "&etape=calculerap&tache=inspection'>Calcul par inspection </a></td></tr>";
                // if ($statut == 'Admin') {
                //     $form .= "<tr><td><input type='image' src='../plugins/images/calcul7.jpg' width='75px'  /> <br/><a href='" . generer_url_ecrire('resultats') . "&etape=calculap&tache=fraude'>Attribution de Fraudeur </a></td>
                //         <td><input type='image' src='../plugins/images/calcul5.jpg' width='75px' /> <br/><a href='" . generer_url_ecrire('resultats') . "&etape=calculerap&tache=region'>Calcul par Region </a></td></tr>";
                // } else {
                //     $form .= "<tr><td><input type='image' src='../plugins/images/calcul7.jpg' width='75px'  /> <br/><a href='" . generer_url_ecrire('resultats') . "&etape=calculerap&tache=fraude'>Attribution de Fraudeur </a></td>
                //         </tr>";

                // }
                $form .= "</table>";

                $form .= "</form>";
                $form .= fin_cadre_couleur();
                echo $form;
            }

            if (isset($_POST['connect_conf'])) {
                $md5_mdp = recupMdp($lien);
                $md5_mdp1 = md5($mot_de_passe);

                if ($md5_mdp == $md5_mdp1) {
                    $_SESSION['mot_de_passe'] = $md5_mdp1;
                } else {
                    $mot_de_passe = '';
                }

            }

            // }
            // if (isset($_REQUEST['tache']) && ($_GET['tache'] == 'matfac') && (isset($_REQUEST['etape'])) && ($_GET['etape'] == 'calculap')) {

            //     $sql_noteFac = "SELECT * ,MIN(note) AS min_note, id_matiere AS mat FROM `bg_notes` WHERE id_type_note=4 GROUP BY num_table HAVING (COUNT(note)>2)";

            //     $result_noteFac = bg_query($lien, $sql_noteFac, __FILE__, __LINE__);
            //     if ($result_noteFac > 0) {

            //         echo "<table  class='spip liste'>";
            //         echo "<thead><th>Num Jury</th><th>Num Table</th><th>Matière</th><th>Note</th></thead>";
            //         while ($row1 = mysqli_fetch_array($result_noteFac)) {
            //             $num_tableFac = $row1['num_table'];
            //             $id_type_session = $row1['id_type_session'];
            //             $id_matiere = $row1['mat'];
            //             $id_type_note = $row1['id_type_note'];
            //             $jury = $row1['jury'];
            //             $noteFac = $row1['min_note'];

            //             $sqlMat = "SELECT * FROM `bg_notes` WHERE num_table='$num_tableFac' AND id_type_session=$id_type_session AND id_type_note=4 AND jury=$jury AND note=$noteFac";
            //             $resultMat = bg_query($lien, $sqlMat, __FILE__, __LINE__);
            //             $matF = mysqli_fetch_array($resultMat);
            //             $id_matiere = $matF['id_matiere'];

            //             //  echo debut_boite_alerte();

            //             echo "<tbody><tr><td>" . $jury . "</td><td>" . $num_tableFac . "</td><td>" . get_matiere_by_id($lien, $id_matiere) . "</td><td>" . $noteFac . "</td></tr></tbody>";

            //             //  echo fin_boite_alerte();

            //         }
            //         echo "</table>";
            //     } else {echo "<h2 style='color:red';><center>Aucun Candidat avec Notes de matiere facultative supérieure à 2 détecté</center></h2>";}

            // }

            // if (isset($_REQUEST['tache']) && ($_GET['tache'] == 'supfac') && (isset($_REQUEST['etape'])) && ($_GET['etape'] == 'calculap')) {

            //     $sql_noteFac = "SELECT * ,MIN(note) AS min_note, id_matiere AS mat FROM `bg_notes` WHERE id_type_note=4 GROUP BY num_table HAVING (COUNT(note)>2)";
            //     $result_noteFac = bg_query($lien, $sql_noteFac, __FILE__, __LINE__);
            //     if ($result_noteFac > 0) {
            //         while ($row1 = mysqli_fetch_array($result_noteFac)) {
            //             $num_tableFac = $row1['num_table'];
            //             $id_type_session = $row1['id_type_session'];
            //             $id_matiere = $row1['mat'];
            //             $id_type_note = $row1['id_type_note'];
            //             $jury = $row1['jury'];
            //             $noteFac = $row1['min_note'];

            //             $sqlMat = "SELECT * FROM `bg_notes` WHERE num_table='$num_tableFac' AND id_type_session=$id_type_session AND id_type_note=4 AND jury=$jury AND note=$noteFac";
            //             $resultMat = bg_query($lien, $sqlMat, __FILE__, __LINE__);
            //             $matF = mysqli_fetch_array($resultMat);
            //             $id_matiere = $matF['id_matiere'];

            //             echo debut_boite_alerte();

            //             $sqlFac2 = "DELETE FROM `bg_notes` WHERE num_table='$num_tableFac' AND id_type_session=$id_type_session AND id_matiere=$id_matiere AND id_type_note=$id_type_note AND jury=$jury AND note=$noteFac";
            //             // $sqlFac2 = "UPDATE `bg_notes` SET note=NULL WHERE num_table='$num_tableFac' AND id_type_session=$id_type_session AND id_matiere=$id_matiere AND id_type_note=$id_type_note AND jury=$jury AND note=$noteFac";
            //             $resultRep1 = bg_query($lien, $sqlFac2, __FILE__, __LINE__);

            //             if ($resultMat) {
            //                 echo "  Opération de Suppression de la note du Numéro de Table <strong>" . $num_tableFac . "</strong> de la matière <strong>" . get_matiere_by_id($lien, $id_matiere) . "</strong> éffectuée avec succès !!!!";
            //             }
            //             echo fin_boite_alerte();
            //         }
            //     } else {
            //         echo "<h2 style='color:red';><center>Tous Les candidats ayants déja une note dans plus de deux matières facultatives ont déja été supprimés </center></h2>";
            //         echo "<h2 style='color:red';><center>Ou Il n'exciste plus de candidat dont le nombre de matières facultative pris est supérieure a 2</center></h2>";
            //     }

            // }

            if (isset($_REQUEST['tache']) && ($_GET['tache'] == 'importsport') && (isset($_REQUEST['etape'])) && ($_GET['etape'] == 'calculap')) {

                echo debut_cadre_enfonce("../images/logo.png", true, '', "Importation des Notes de Sport par Région");
                $form2 = "<FORM name='form_gen' method='POST' action=''><table size=50%>";
                $form2 .= "<th><center>Région</center></th><th></th><th></th>";
                $form2 .= "<tr><td style='width:75%'><center><select style='width:40%' name='tri_region_division'  >" . optionsReferentiel($lien, 'region_division', $tri_region_division) . "</select></center></td>";
                $form2 .= "<td><input type='submit' value='Importer' name='importer_notes_sport'  size=20 /></td></tr>";
                $form2 .= "</table></FORM>";
                echo $form2;

                if (isset($_POST['importer_notes_sport'])) {

                    // Liste des centres de la region
                    $listeCentres = getCentreByRegionDivision($lien, $tri_region_division);
                    $param = selectCodeAno($lien, $annee, $id_type_session);
                    echo "<table>";
                    echo "<th>Centre</th><th>Jury</th><th>Option</th>";

                    foreach ($listeCentres as $valeur) {
                        $param = selectCodeAno($lien, $annee, $id_type_session);
                        $sql1 = "SELECT *  FROM bg_repartition WHERE id_centre=$valeur GROUP BY jury ";
                        $result1 = bg_query($lien, $sql1, __FILE__, __LINE__);
                        $param = selectCodeAno($lien, $annee, $id_type_session);
                        while ($row1 = mysqli_fetch_array($result1)) {
                            $jury = $row1['jury'];
                            var_dump($jury);
                            // echo "import " . $jury . "\n";
                            $tSql[] = "UPDATE bg_notes notes, bg_notes_eps eps, bg_repartition rep SET notes.note=(eps.note)
					WHERE notes.annee=$annee AND eps.annee=$annee AND rep.annee=$annee AND rep.id_anonyme=notes.id_anonyme
					AND rep.num_table=eps.num_table AND eps.note>=0 AND notes.id_matiere=42 AND notes.id_type_note=1 AND rep.jury=$jury AND notes.jury=$jury";

                            //         $tSql[] = "UPDATE bg_notes notes, bg_notes_eps eps, bg_repartition rep SET notes.note=(eps.note)
                            // WHERE notes.annee=$annee AND eps.annee=$annee AND rep.annee=$annee AND rep.id_anonyme=notes.id_anonyme
                            // AND rep.num_table=eps.num_table AND eps.note>=0 AND notes.id_matiere=42 AND notes.id_type_note=1 AND rep.jury=$jury AND notes.jury=$jury";
                            echo "<tr><td><strong>" . recupCentreById($lien, $valeur) . "</strong></td><td>" . $jury . "</td><td>ok</td></tr>";
                            foreach ($tSql as $sql) {
                                bg_query($lien, $sql, __FILE__, __LINE__);

                            }

                        }

                    }

                    echo "</table>";
                    //  echo fin_cadre_trait_couleur();

                }
                echo fin_cadre_enfonce();

            }
            #Bouton qui modifier la deliberation en fraude
            if (isset($_POST['conf_delib1'])) {
                $sql_conf_deliberation_fraude = "UPDATE bg_resultats SET delib1='Fraude' WHERE jury='$jury' AND num_table='$num_table'";
                bg_query($lien, $sql_conf_deliberation_fraude, __FILE__, __LINE__);
            }

            if (isset($_REQUEST['tache']) && ($_GET['tache'] == 'fraude') && (isset($_REQUEST['etape'])) && ($_GET['etape'] == 'calculap')) {

                $form = debut_cadre_enfonce('', '', '', "Attributions de décisions de fraude au candidat");
                $form .= "<form method='POST' name='form_fraude'>";
                $form .= " <table>";
                $form .= " <tr><th>Jury</th><th></th><th>Anonymat</th><th> </th><th>Décision</th></tr>";
                $form .= "<tr>
                    <td style='text-align: left;'><input type='text' name='jury' value='$jury' /></td>
                    <td style='width:5%'></td>
                    <td style='text-align: left;'><input type='text' name='num_table' value='$num_table' /></td>
                    <td style='width:5%'></td>
                    <td><center><select style='width:100%' name='delib1'> <option value='Fraude' $selected>Fraude</option></select></center></td>
                    <td style='width:5%'></td>
                    <td><input type='submit' type='reset' value='Valider' name='conf_delib1' onClick=\"if(confirm('Souhaitez-vous vraiment mettre ce candidat de jury $jury et de numero d\'anonymat $num_table a fraudé?')) window.location='" . generer_url_ecrire('resultats') . "&etape=calculap&tache=fraude';\"/></td></tr>";
                $form .= "</table> ";
                $form .= "</form> ";
                $form .= fin_cadre_enfonce();

                echo $form;
                echo debut_cadre_couleur();
                $sql = "SELECT * FROM bg_resultats WHERE delib1='Fraude' ORDER BY jury ";
                $result = bg_query($lien, $sql, __FILE__, __LINE__);

                $tableau = "<table class='spip liste'><thead>";
                $tableau .= "<th>Jury</th><th>Anonymat</th><th>Statut</th><th>Action</th>";
                $tableau .= "</thead><tbody>";
                $tab = array('jury', 'num_table', 'delib1');
                while ($row = mysqli_fetch_array($result)) {
                    foreach ($tab as $var) {
                        $$var = $row[$var];
                    }
                    $tableau .= "<tr><td>$jury</td><td>$num_table</td><td>$delib1</td><td> <a href='" . generer_url_ecrire('resultats') . "&etape=calculerap&tache=liste&$jury'> Annuler</a></td></tr>";

                }
                $tableau .= "</tbody><tfoot><tr></tr></tfoot></table>";
                echo $tableau;
                echo fin_grand_cadre();
            }

            echo fin_gauche();
            echo fin_gauche();
            echo fin_gauche();

        }

        if (isset($_REQUEST['etape']) && ($_REQUEST['etape'] == 'calculad')) {

            echo debut_gauche();

            if (($statut == 'Admin') || ($statut == 'Dre') || ($statut == 'Encadrant')) {

                echo debut_boite_info();
                echo "<p>Gestion des Calculs après désanonymat</p>";
                echo "<p><a href='" . generer_url_ecrire('resultats') . "&etape=calculerad&tache=liste'>Calcul par Jury </a></p>";
                // if ($statut == 'Admin') {echo "<p><a href='" . generer_url_ecrire('resultats') . "&etape=calculerad&tache=marge' class='ajax'>Calcul par marge jury</a></p>";}
                echo fin_boite_info();

            }

            echo debut_boite_info();
            echo "<p><a href='" . generer_url_ecrire('resultats') . "' class='ajax'>Retour au Menu Principale</a></p>";

            //echo "<form method='POST'><input name='detruire_session' type='submit' value='Changer de session' /></form>";
            echo fin_boite_info();

            //  }
            echo debut_droite();

            echo debut_cadre_trait_couleur('', '', '', "CALCULS APRES DESHANONYMAT", "", "", false);
            if (isset($_REQUEST['etape']) && ($_GET['etape'] == 'calculad') && !isset($_REQUEST['tache'])) {
                $form .= "<p><center><a href='./?exec=resultats'>Retour au Menu principal</a></center></p>";
                $form .= debut_cadre_couleur('', '', '', "Calcul des Résultats ");
                $form .= "<table>";
                $form .= "<tr><td><input type='image' src='../plugins/images/calcul1.jpg' width='75px' /> <br/><a href='" . generer_url_ecrire('resultats') . "&etape=calculerad&tache=liste'>Calcul par Jury </a></td></tr>";
                // <td><input type='image' src='../plugins/images/calcul2.jpg' width='75px'  /> <br/><a href='" . generer_url_ecrire('resultats') . "&etape=calculerad&tache=marge'>Calcul par Marge de Jury </a></td>
                // $form .= "<tr><td><input type='image' src='../plugins/images/calcul3.jpg' width='75px'  /> <br/><a href='" . generer_url_ecrire('resultats') . "&etape=calculerad&tache=centre'>Calcul par Centre de Jury </a></td>
                //         <td><input type='image' src='../plugins/images/calcul8.jpg' width='75px' /> <br/><a href='" . generer_url_ecrire('resultats') . "&etape=calculerad&tache=inspection'>Calcul par inspection </a></td></tr>";
                // if ($statut == 'Admin') {
                //     $form .= "<tr><td><input type='image' src='../plugins/images/calcul7.jpg' width='75px'  /> <br/><a href='" . generer_url_ecrire('resultats') . "&etape=calculap&tache=fraude'>Attribution de Fraudeur </a></td>
                //         <td><input type='image' src='../plugins/images/calcul5.jpg' width='75px' /> <br/><a href='" . generer_url_ecrire('resultats') . "&etape=calculerad&tache=region'>Calcul par Region </a></td></tr>";
                // } else {
                //     $form .= "<tr><td><input type='image' src='../plugins/images/calcul7.jpg' width='75px'  /> <br/><a href='" . generer_url_ecrire('resultats') . "&etape=calculerad&tache=fraude'>Attribution de Fraudeur </a></td>
                //         </tr>";

                // }
                $form .= "</table>";

                $form .= "</form>";
                $form .= fin_cadre_couleur();
                echo $form;
            }

            if (isset($_POST['connect_conf'])) {
                $md5_mdp = recupMdp($lien);
                $md5_mdp1 = md5($mot_de_passe);

                if ($md5_mdp == $md5_mdp1) {
                    $_SESSION['mot_de_passe'] = $md5_mdp1;
                } else {
                    $mot_de_passe = '';
                }

            }

            #Bouton qui modifier la deliberation en fraude
            if (isset($_POST['conf_delib1'])) {
                $sql_conf_deliberation_fraude = "UPDATE bg_resultats SET delib1='Fraude' WHERE jury='$jury' AND num_table='$num_table'";
                bg_query($lien, $sql_conf_deliberation_fraude, __FILE__, __LINE__);
            }

            echo fin_gauche();
            echo fin_gauche();
            echo fin_gauche();

        }

        if ($_GET['etape'] == 'resultat') {
            echo debut_gauche();

            if (($statut == 'Admin') || ($statut == 'Encadrant')) {

                echo debut_boite_info();
                echo "<p>Gestion des Résultats</p>";
                echo "<p><a href='" . generer_url_ecrire('resultats') . "&etape=resultat&tache=liste'>Résultat par Jury </a></p>";
                echo "<p><a href='" . generer_url_ecrire('resultats') . "&etape=resultat&tache=stat'>Statistique Admissible par Centre </a></p>";
                // echo "<p><a href='" . generer_url_ecrire('resultats') . "&etape=resultat&tache=marge' class='ajax'>Résultat par marge jury</a></p>";
                // echo "<p><a href='" . generer_url_ecrire('resultats') . "&etape=resultat&tache=region' class='ajax'>Résultat par Région</a></p>";
                echo fin_boite_info();
            }

            echo debut_boite_info();
            echo "<p><a href='" . generer_url_ecrire('resultats') . "' class='ajax'>Retour au Menu Principale</a></p>";

            //echo "<form method='POST'><input name='detruire_session' type='submit' value='Changer de session' /></form>";
            echo fin_boite_info();

            //  }
            echo debut_droite();

            echo debut_cadre_trait_couleur('', '', '', "RESULTATS", "", "", false);
            if (isset($_REQUEST['etape']) && ($_GET['etape'] == 'resultat') && !isset($_REQUEST['tache'])) {

                $form .= "<p><center><a href='./?exec=resultats'>Retour au Menu principal</a></center></p>";
                $form .= debut_cadre_couleur('', '', '', "Impression des Résultats ");
                $form .= "<table>";
                $form .= "<tr><td><input type='image' src='../plugins/images/liste1.jpg' width='75px' /> <br/><a href='" . generer_url_ecrire('resultats') . "&etape=resultat&tache=liste'>Résultat par Jury </a></td>
                        </tr>";
                //      <td><input type='image' src='../plugins/images/calcul2.jpg' width='75px'  /> <br/><a href='" . generer_url_ecrire('resultats') . "&etape=calculer&tache=marge'>Calcul par Marge de Jury </a></td>

                $form .= "</table>";

                $form .= "</form>";
                $form .= fin_cadre_couleur();
                echo $form;
            }
            if (isset($_POST['connect_conf'])) {
                $md5_mdp = recupMdp($lien);
                $md5_mdp1 = md5($mot_de_passe);

                if ($md5_mdp == $md5_mdp1) {
                    $_SESSION['mot_de_passe'] = $md5_mdp1;
                } else {
                    $mot_de_passe = '';
                }

            }
            if (isset($_REQUEST['tache']) && ($_GET['tache'] == 'liste') && (isset($_REQUEST['etape'])) && ($_GET['etape'] == 'resultat')) {
                if ($statut == "Dre") {
                    $tJurys = getJurysTri($lien, $annee, $id_type_session, $id_region);
                } else {
                    $tJurys = getJurysTri($lien, $annee, $id_type_session, -1);
                }
                //$tJurys = getJurysTri($lien, $annee, $id_type_session);
                $code_jury = getCodeByJury($lien, $annee);

                if ($isOperateurNotes) {$tJurys = $tabJurysOperateur;if (!is_array($tJurys)) {
                    if ($statut == "Dre") {
                        $tJurys = getJurysTri($lien, $annee, $id_type_session, $id_region);
                    } else {
                        $tJurys = getJurysTri($lien, $annee, $id_type_session, -1);
                    }
                }} else {
                    if ($statut == "Dre") {
                        $tJurys = getJurysTri($lien, $annee, $id_type_session, $id_region);
                    } else {
                        $tJurys = getJurysTri($lien, $annee, $id_type_session, -1);
                    }
                }

                // if ($statut == "Dre") {
                //     $tJurys = getJurysTriDelib($lien, $annee, $id_type_session, $id_region, 0);
                // } else {
                //     $tJurys = getJurysTriDelib($lien, $annee, $id_type_session, -1, 0);
                // }
                // //$tJurys = getJurysTri($lien, $annee, $id_type_session);
                // $code_jury = getCodeByJury($lien, $annee);

                // if ($isOperateurNotes) {$tJurys = $tabJurysOperateur;if (!is_array($tJurys)) {
                //     if ($statut == "Dre") {
                //         $tJurys = getJurysTriDelib($lien, $annee, $id_type_session, $id_region, 0);
                //     } else {
                //         $tJurys = getJurysTriDelib($lien, $annee, $id_type_session, -1, 0);
                //     }
                // }} else {
                //     if ($statut == "Dre") {
                //         $tJurys = getJurysTriDelib($lien, $annee, $id_type_session, $id_region, 0);
                //     } else {
                //         $tJurys = getJurysTriDelib($lien, $annee, $id_type_session, -1, 0);
                //     }
                // }

                $tSeries = getSeries($lien, $annee);

                if (isset($_REQUEST['jury']) && $_REQUEST['jury'] > 0) {
                    $deliberation[$jury] = detectDeliberation($lien, $annee, $jury);
                    $tMatieres = getMatieres($lien, $annee, $jury);
                    $tNbNotesSaisies = getNbNotesSaisies($lien, $annee, $jury);
                    $tNbNotesASaisir = getNbNotesASaisir($lien, $annee, $jury);
                }
                $JuryEnclair = siDelib($lien, $annee);
                $code_jury = getCodeByJury($lien, $annee);
                if ($JuryEnclair) {
                    foreach ($code_jury as $j => $code) {
                        $code_jury[$j] = $j;
                    }
                } else {
                    //asort($code_jury);
                }
                $tSeriesJury = $tSeries[$jury];

                $aujourdhui = date('xx/m/Y');
                $tSeriesJury = $tSeries[$jury];
                $form1 = debut_cadre_enfonce();
                $form1 .= "<FORM name='form_listes' method='POST' action=''><table size=50%>";
                $form1 .= "<th>Jury</th><th></th>";
                $form1 .= "<tr><td><center><select name='jury' onchange=\"document.forms['form_listes'].submit();\">";
                $form1 .= "<option value='0' $selected >-=[Jurys]=-</option>";
                foreach ($tJurys as $j) {
                    if ($j == $jury) {
                        $selected = 'selected';
                    } else {
                        $selected = '';
                    }

                    $form1 .= "<option value='$j' $selected >$code_jury[$j]</option>";
                }
                $form1 .= "</select></center></td><td><input type='hidden' name='id_type_session' value='$id_type_session' /><input type='hidden' name='annee' value='$annee' /></td>";
                $form1 .= "</table></FORM>";
                $form1 .= fin_cadre_enfonce();

                if ($jury > 0) {
                    echo "<center><a href='" . generer_url_ecrire('resultats') . "&etape=resultat';>Retour aux matières du Jury $code_jury[$jury]</a></center>";
                    if (($statut == 'Admin') || ($statut == 'Dre') || ($statut == 'Encadrant')) {
                        if ($deliberation[$jury] >= 0) {
                            $form1 .= debut_cadre_trait_couleur('', '', '', "Candidats Admis avec Anonymat");
                            $form1 .= "<table>";
                            $form1 .= "<tr><td ><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury.php?annee=$annee&jury=$jury&type=0'>Candidats Admis et Admissibles</a></td></tr>";
                            $form1 .= "<tr><td ><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury.php?annee=$annee&jury=$jury&type=8'>Candidats Admis</a></td></tr>";
                            $form1 .= "<tr><td ><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury.php?annee=$annee&jury=$jury&type=9'>Candidats Admissibles</a></td></tr>";
                            $form1 .= "<tr><td width=40%><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury_oral2.php?annee=$annee&jury=$jury'>Candidats Admissibles et Matières d'Oral</a></td></tr>";
                            $form1 .= "</table>";
                            $form1 .= fin_cadre_trait_couleur();
                        }
                    }
                    if ($deliberation[$jury] > 0) {
                        $form1 .= debut_cadre_trait_couleur('', '', '', "Résultats de la Première délibération");
                        $form1 .= "<table>";
                        $form1 .= "<tr><td ><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury.php?annee=$annee&jury=$jury&type=1'>Candidats Admis à la première délibération</a></td></tr>";
                        $form1 .= "<tr><td ><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury.php?annee=$annee&jury=$jury&type=2'>Candidats Admissibles à la première délibération</a></td></tr>";
                        $form1 .= "<tr><td width=40%><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury.php?annee=$annee&jury=$jury&type=3'>Candidats Absents / Abandons</a></td></tr>";
                        $form1 .= "<tr><td width=40%><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury.php?annee=$annee&jury=$jury&type=4'>Candidats Ajournés à la première délibération</a></td></tr>";
                        $form1 .= "<tr><td width=40%><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury_oral.php?annee=$annee&jury=$jury'>Candidats Admissibles et Matières d'Oral</a></td></tr>";
                        $form1 .= "</table>";
                        $form1 .= fin_cadre_trait_couleur();
                    }

                    if ($deliberation[$jury] > 1) {
                        $form1 .= debut_cadre_trait_couleur('', '', '', "Résultats de la Deuxième délibération");
                        $form1 .= "<table>";
                        $form1 .= "<tr><td width=40%><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury.php?annee=$annee&jury=$jury&type=5'>Candidats Admis à la deuxième délibération</a></td></tr>";
                        $form1 .= "<tr><td width=40%><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury.php?annee=$annee&jury=$jury&type=6'>Candidats Absents / Abandons</a></td></tr>";
                        $form1 .= "<tr><td width=40%><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury.php?annee=$annee&jury=$jury&type=7'>Candidats Ajournés à la deuxième délibération</a></td></tr>";
                        $form1 .= "</table>";
                        $form1 .= fin_cadre_trait_couleur();
                    }

                    if ($deliberation[$jury] > 0) {
                        $form1 .= debut_cadre_trait_couleur('', '', '', "Relevés de notes");
                        $form1 .= "<table><form method='POST' action='../plugins/fonctions/inc-pdf-releve.php?'>";
                        $form1 .= "<tr><td><input type='hidden' name='jury' value='$jury' /></td>
                    <td><input type='hidden' name='annee' value='$annee' /><input type='hidden' name='id_type_session' value='$id_type_session' /></td>
                    <td><input type='hidden' name='deliberation' value='$deliberation[$jury]' /></td></tr>";
                        $form1 .= "<tr><td>Type de relevé</td><td><select name='type_releve'>
                    <option value='0'>-=[Choisir]=-</option>
                    <option value='1'>Admis d'emblée</option>
                    <option value='2'>Ajournés 1ère Délibération</option>";
                        if ($deliberation[$jury] > 1) {
                            $form1 .= "<option value='3'>Admis après oral</option>
                    <option value='4'>Ajournés après oral</option>";
                        }
                        $form1 .= "</select></td></tr>";
                        $form1 .= "<tr><td>Président du Jury</td><td><input type='text' name='president_jury' value='' onFocus=\"this.value=''\" size=17 /></td></tr>";
                        //                $form1.="<tr><td>Lieu de délibération</td><td><input type='text' name='lieu_delib' value='' onFocus=\"this.value=''\" size=17 required /></td></tr>";
                        //                $form1.="<tr><td>Date de délibération</td><td><input type='date' name='date_delib' value='$aujourdhui' onFocus=\"this.select();\" size=8 pattern='^[0-9]{1,2}\/[01]?[0-9]\/[0-9]{4}$' required /></td></tr>";
                        $form1 .= "<tr><td colspan=2><center><input type='submit' value='IMPRIMER LES \nRELEVES DE NOTES' /></center></td></tr>";
                        $form1 .= "</form></table>";
                        $form1 .= fin_cadre_trait_couleur();
                    }
                    /* if($deliberation[$jury]>0){
                $form1.=debut_cadre_trait_couleur('','','',"ATTESTATIONS DE DIPLOME");
                $form1.="<table><form method='POST' action='../plugins/fonctions/inc-pdf-attestation.php?'>";
                $form1.="<tr><td><input type='hidden' name='jury' value='$jury' /></td>
                <td><input type='hidden' name='annee' value='$annee' /><input type='hidden' name='id_type_session' value='$id_type_session' /></td>
                <td><input type='hidden' name='deliberation' value='$deliberation[$jury]' /></td></tr>";
                $form1.="<tr><td colspan=2><center><input type='submit' value='IMPRIMER LES \nATTESTATIONS DE DIPLOME' /></center></td></tr>";
                $form1.="</form></table>";
                $form1.=fin_cadre_trait_couleur();
                } */

                }
                echo $form1;
            }

            if (isset($_REQUEST['tache']) && ($_GET['tache'] == 'stat') && (isset($_REQUEST['etape'])) && ($_GET['etape'] == 'resultat')) {
                if (isset($_POST['id_region_division']) && $_POST['id_region_division'] > 0) {

                }

                $form1 = debut_cadre_enfonce('', '', '', "Statistique des Admissibles  Par Region");
                $form1 .= "<FORM name='form_reg' method='POST' action=''><table size=50%>";
                $form1 .= "<th>Region</th><th></th><th></th><th></th> ";
                // $form1 .= "<tr><td><center><select   name='tri_region' onchange='document.forms.form_reg.submit()'>" . optionsReferentiel($lien, 'region', $tri_region) . "</select></center></td>";
                if ($statut == "Admin") {
                    $form1 .= "<tr><td><center><select   name='id_region_division' id='id_region_division' onchange='document.forms.form_reg.submit()'>" . optionsReferentiel($lien, 'region_division', $id_region_division) . "</select></center></td>";

                } elseif ($statut == "Encadrant") {
                    $id_region_division = selectRegionDivisionByRegion($lien, $id_region);
                    $form1 .= "<tr><td><center><select   name='id_region_division' id='id_region_division' onchange='document.forms.form_reg.submit()'>" . optionsReferentiel($lien, 'region_division', $id_region_division, $andWhereRegion) . "</select></center></td>";

                }

                $form1 .= "<td><input type='hidden' name='id_type_session' value='$id_type_session' /><input type='hidden' name='annee' value='$annee' /></td>";
                $form1 .= "<td>Tous<input type='radio' name='si_tous' value='non' checked /></td><td><input type='submit' value='OK' name='valide_admissible_region' /></td></tr>";
                $form1 .= "</table></FORM>";
                $form1 .= fin_cadre_enfonce();

                if (isset($_POST['valide_admissible_region'])) {
                    $form1 .= debut_cadre_trait_couleur();
                    $listeCentres = getCentreByRegionDivision($lien, $id_region_division);
                    $form1 .= "<table>";
                    $form1 .= "<th>Centre</th><th>Nombre de Candidat Admissible</th>";
                    foreach ($listeCentres as $valeur) {
                        //  echo ("\n Centre : " . recupCentreById($lien, $valeur) . "\n");

                        //selectionner les jury du centre choisie
                        $sql1 = "SELECT COUNT(*) as nb
                        FROM bg_candidats can, bg_ref_serie ser, bg_resultats res,bg_ref_etablissement eta
                        WHERE can.serie=ser.id
                        AND can.num_table=res.nt
                        AND can.centre=eta.id
                        AND eta.id=$valeur
                        AND delib1 ='Oral'";
                        // $sql1 = "SELECT *  FROM bg_repartition WHERE id_centre=$valeur GROUP BY jury ";
                        $result1 = bg_query($lien, $sql1, __FILE__, __LINE__);

                        while ($row1 = mysqli_fetch_array($result1)) {
                            $nombre = $row1['nb'];
                            // var_dump("Nombre: " . $nombre . "\n");
                            // echo ("Nombre: " . $nombre . " \n");
                            $form1 .= "<tr><td><strong>" . recupCentreById($lien, $valeur) . "</strong></td><td>" . $nombre . "</td></tr>";

                        }

                    }
                    $form1 .= "</table>";
                    $form1 .= fin_cadre_trait_couleur();

                }
                echo $form1;
                echo fin_gauche(), fin_gauche();

            }

            if (isset($_REQUEST['tache']) && ($_GET['tache'] == 'centre') && (isset($_REQUEST['etape'])) && ($_GET['etape'] == 'resultat')) {
                $aujourdhui = date('xx/m/Y');
                $tSeriesJury = $tSeries[$jury];
                $andWhereCen .= " AND eta.si_centre='oui' ";
                if (isset($_POST['tri_centre']) && $_POST['tri_centre'] > 0) {
                    var_dump($tri_centre);

                    //selectionner les jury du centre choisie
                    $sql1 = "SELECT *  FROM bg_repartition WHERE id_centre=$tri_centre GROUP BY jury ";
                    $result1 = bg_query($lien, $sql1, __FILE__, __LINE__);
                    while ($row1 = mysqli_fetch_array($result1)) {
                        $jury = $row1['jury'];
                        var_dump("jury : " . $jury);
                        $tabJury[] .= $jury;

                    }

                    var_dump("first tab " . $tabJury[0]);
                    var_dump("last tab " . $tabJury[count($tabJury) - 1]);
                    $jury1 = $tabJury[0];
                    $jury2 = $tabJury[count($tabJury) - 1];

                    calculMargeJuryAp($lien, $annee, $jury1, $jury2);

                }

                if (isset($_POST['tri_inspection']) && $_POST['tri_inspection'] > 0) {
                    $andWhereCen .= " AND eta.si_centre='oui' AND ins.id=eta.id_inspection AND eta.id_inspection=$tri_inspection ";
                }

                $form1 .= debut_cadre_enfonce('', '', '', "Résultats Par Centre");
                $form1 .= "<FORM name='form_rep' method='POST' action=''><table size=50%>";
                $form1 .= "<th>Inspection</th><th>Centre</th><th></th> ";

                $form1 .= "<tr><td><center><select   name='tri_inspection' onchange='document.forms.form_rep.submit()'>" . optionsReferentiel($lien, 'inspection', $tri_inspection) . "</select></center></td>";
                $form1 .= "<td><center><select  name='tri_centre' onchange='document.forms.form_rep.submit()'>" . optionsEtablissement($lien, $tri_centre, $andWhereCen, true, ',bg_ref_inspection ins ', '', 'Centre') . "</select></center></td>";

                $form1 .= "<td><input type='hidden' name='id_type_session' value='$id_type_session' /><input type='hidden' name='annee' value='$annee' /></td></tr>";
                $form1 .= "</table></FORM>";
                $form1 .= fin_cadre_enfonce();

                if ($jury > 0) {
                    echo "<center><a href='" . generer_url_ecrire('resultats') . "&etape=resultat';>Retour aux matières du Jury $code_jury[$jury]</a></center>";
                    if (($statut == 'Admin')) {
                        if ($deliberation[$jury] >= 0) {
                            $form1 .= debut_cadre_trait_couleur('', '', '', "Candidats Admis avec Anonymat");
                            $form1 .= "<table>";
                            $form1 .= "<tr><td ><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury.php?annee=$annee&jury=$jury&type=0'>Candidats Admis et Admissibles</a></td></tr>";
                            $form1 .= "<tr><td ><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury.php?annee=$annee&jury=$jury&type=8'>Candidats Admis</a></td></tr>";
                            $form1 .= "<tr><td ><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury.php?annee=$annee&jury=$jury&type=9'>Candidats Admissibles</a></td></tr>";
                            $form1 .= "<tr><td width=40%><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury_oral2.php?annee=$annee&jury=$jury'>Candidats Admissibles et Matières d'Oral</a></td></tr>";
                            $form1 .= "</table>";
                            $form1 .= fin_cadre_trait_couleur();
                        }
                    }
                    if ($deliberation[$jury] > 0) {
                        $form1 .= debut_cadre_trait_couleur('', '', '', "Résultats de la Première délibération");
                        $form1 .= "<table>";
                        $form1 .= "<tr><td ><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury.php?annee=$annee&jury=$jury&type=1'>Candidats Admis à la première délibération</a></td></tr>";
                        $form1 .= "<tr><td ><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury.php?annee=$annee&jury=$jury&type=2'>Candidats Admissibles à la première délibération</a></td></tr>";
                        $form1 .= "<tr><td width=40%><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury.php?annee=$annee&jury=$jury&type=3'>Candidats Absents / Abandons</a></td></tr>";
                        $form1 .= "<tr><td width=40%><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury.php?annee=$annee&jury=$jury&type=4'>Candidats Ajournés à la première délibération</a></td></tr>";
                        $form1 .= "<tr><td width=40%><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury_oral.php?annee=$annee&jury=$jury'>Candidats Admissibles et Matières d'Oral</a></td></tr>";
                        $form1 .= "</table>";
                        $form1 .= fin_cadre_trait_couleur();
                    }

                    if ($deliberation[$jury] > 1) {
                        $form1 .= debut_cadre_trait_couleur('', '', '', "Résultats de la Deuxième délibération");
                        $form1 .= "<table>";
                        $form1 .= "<tr><td width=40%><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury.php?annee=$annee&jury=$jury&type=5'>Candidats Admis à la deuxième délibération</a></td></tr>";
                        $form1 .= "<tr><td width=40%><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury.php?annee=$annee&jury=$jury&type=6'>Candidats Absents / Abandons</a></td></tr>";
                        $form1 .= "<tr><td width=40%><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury.php?annee=$annee&jury=$jury&type=7'>Candidats Ajournés à la deuxième délibération</a></td></tr>";
                        $form1 .= "</table>";
                        $form1 .= fin_cadre_trait_couleur();
                    }

                    if ($deliberation[$jury] > 0) {
                        $form1 .= debut_cadre_trait_couleur('', '', '', "Relevés de notes");
                        $form1 .= "<table><form method='POST' action='../plugins/fonctions/inc-pdf-releve.php?'>";
                        $form1 .= "<tr><td><input type='hidden' name='jury' value='$jury' /></td>
								<td><input type='hidden' name='annee' value='$annee' /><input type='hidden' name='id_type_session' value='$id_type_session' /></td>
								<td><input type='hidden' name='deliberation' value='$deliberation[$jury]' /></td></tr>";
                        $form1 .= "<tr><td>Type de relevé</td><td><select name='type_releve'>
								<option value='0'>-=[Choisir]=-</option>
								<option value='1'>Admis d'emblée</option>
								<option value='2'>Ajournés 1ère Délibération</option>";
                        if ($deliberation[$jury] > 1) {
                            $form1 .= "<option value='3'>Admis après oral</option>
								<option value='4'>Ajournés après oral</option>";
                        }
                        $form1 .= "</select></td></tr>";
                        $form1 .= "<tr><td>Président du Jury</td><td><input type='text' name='president_jury' value='' onFocus=\"this.value=''\" size=17 /></td></tr>";
                        //                $form1.="<tr><td>Lieu de délibération</td><td><input type='text' name='lieu_delib' value='' onFocus=\"this.value=''\" size=17 required /></td></tr>";
                        //                $form1.="<tr><td>Date de délibération</td><td><input type='date' name='date_delib' value='$aujourdhui' onFocus=\"this.select();\" size=8 pattern='^[0-9]{1,2}\/[01]?[0-9]\/[0-9]{4}$' required /></td></tr>";
                        $form1 .= "<tr><td colspan=2><center><input type='submit' value='IMPRIMER LES \nRELEVES DE NOTES' /></center></td></tr>";
                        $form1 .= "</form></table>";
                        $form1 .= fin_cadre_trait_couleur();
                    }
                    /* if($deliberation[$jury]>0){
                $form1.=debut_cadre_trait_couleur('','','',"ATTESTATIONS DE DIPLOME");
                $form1.="<table><form method='POST' action='../plugins/fonctions/inc-pdf-attestation.php?'>";
                $form1.="<tr><td><input type='hidden' name='jury' value='$jury' /></td>
                <td><input type='hidden' name='annee' value='$annee' /><input type='hidden' name='id_type_session' value='$id_type_session' /></td>
                <td><input type='hidden' name='deliberation' value='$deliberation[$jury]' /></td></tr>";
                $form1.="<tr><td colspan=2><center><input type='submit' value='IMPRIMER LES \nATTESTATIONS DE DIPLOME' /></center></td></tr>";
                $form1.="</form></table>";
                $form1.=fin_cadre_trait_couleur();
                } */

                }
                echo $form1;

            }
            if (isset($_REQUEST['tache']) && ($_GET['tache'] == 'marge') && (isset($_REQUEST['etape'])) && ($_GET['etape'] == 'resultat')) {
                $aujourdhui = date('xx/m/Y');
                $tSeriesJury = $tSeries[$jury];
                // echo "<center><a href='" . generer_url_ecrire('resultats') . "&etape=calculap';>Retour à mes Calculs</a>";

                // $form1 .= fin_cadre_enfonce();

                $form1 .= debut_cadre_enfonce('', '', '', "Résultats Par Jury ");
                $form1 .= "<FORM name='form_resultats' method='POST' action=''><table size=50%>";
                $form1 .= " <tr><th>Jury 1</th><th>Jury 2</th><th></th><th></th><th></th> </tr> ";

                $form1 .= "<tr><td style='text-align: center;' ><input type='text' name='jury1' value='$jury1' /></td>
                <td style='text-align: center;'><input type='text' name='jury2' value='$jury2' /></td>";
                $form1 .= "<td><input type='hidden' name='id_type_session' value='$id_type_session' /><input type='hidden' name='annee' value='$annee' />";
                $form1 .= "<td>Tous<input type='radio' name='si_tous' value='non' checked /></td><td><input type='submit' value='OK' name='validator' /></td></tr>";
                $form1 .= "</table></FORM>";
                $form1 .= fin_cadre_enfonce();

                if ($jury > 0) {
                    echo "<center><a href='" . generer_url_ecrire('resultats') . "&etape=resultat';>Retour aux matières du Jury $code_jury[$jury]</a></center>";
                    if ($statut == 'Admin') {
                        if ($deliberation[$jury] >= 0) {
                            $form1 .= debut_cadre_trait_couleur('', '', '', "Candidats Admis avec Anonymat");
                            $form1 .= "<table>";
                            $form1 .= "<tr><td ><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury.php?annee=$annee&jury=$jury&type=0'>Candidats Admis et Admissibles</a></td></tr>";
                            $form1 .= "<tr><td ><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury.php?annee=$annee&jury=$jury&type=8'>Candidats Admis</a></td></tr>";
                            $form1 .= "<tr><td ><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury.php?annee=$annee&jury=$jury&type=9'>Candidats Admissibles</a></td></tr>";
                            $form1 .= "<tr><td width=40%><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury_oral2.php?annee=$annee&jury=$jury'>Candidats Admissibles et Matières d'Oral</a></td></tr>";
                            $form1 .= "</table>";
                            $form1 .= fin_cadre_trait_couleur();
                        }
                    }
                    if ($deliberation[$jury] > 0) {
                        $form1 .= debut_cadre_trait_couleur('', '', '', "Résultats de la Première délibération");
                        $form1 .= "<table>";
                        $form1 .= "<tr><td ><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury.php?annee=$annee&jury=$jury&type=1'>Candidats Admis à la première délibération</a></td></tr>";
                        $form1 .= "<tr><td ><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury.php?annee=$annee&jury=$jury&type=2'>Candidats Admissibles à la première délibération</a></td></tr>";
                        $form1 .= "<tr><td width=40%><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury.php?annee=$annee&jury=$jury&type=3'>Candidats Absents / Abandons</a></td></tr>";
                        $form1 .= "<tr><td width=40%><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury.php?annee=$annee&jury=$jury&type=4'>Candidats Ajournés à la première délibération</a></td></tr>";
                        $form1 .= "<tr><td width=40%><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury_oral.php?annee=$annee&jury=$jury'>Candidats Admissibles et Matières d'Oral</a></td></tr>";
                        $form1 .= "</table>";
                        $form1 .= fin_cadre_trait_couleur();
                    }

                    if ($deliberation[$jury] > 1) {
                        $form1 .= debut_cadre_trait_couleur('', '', '', "Résultats de la Deuxième délibération");
                        $form1 .= "<table>";
                        $form1 .= "<tr><td width=40%><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury.php?annee=$annee&jury=$jury&type=5'>Candidats Admis à la deuxième délibération</a></td></tr>";
                        $form1 .= "<tr><td width=40%><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury.php?annee=$annee&jury=$jury&type=6'>Candidats Absents / Abandons</a></td></tr>";
                        $form1 .= "<tr><td width=40%><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury.php?annee=$annee&jury=$jury&type=7'>Candidats Ajournés à la deuxième délibération</a></td></tr>";
                        $form1 .= "</table>";
                        $form1 .= fin_cadre_trait_couleur();
                    }

                    if ($deliberation[$jury] > 0) {
                        $form1 .= debut_cadre_trait_couleur('', '', '', "Relevés de notes");
                        $form1 .= "<table><form method='POST' action='../plugins/fonctions/inc-pdf-releve.php?'>";
                        $form1 .= "<tr><td><input type='hidden' name='jury' value='$jury' /></td>
								<td><input type='hidden' name='annee' value='$annee' /><input type='hidden' name='id_type_session' value='$id_type_session' /></td>
								<td><input type='hidden' name='deliberation' value='$deliberation[$jury]' /></td></tr>";
                        $form1 .= "<tr><td>Type de relevé</td><td><select name='type_releve'>
								<option value='0'>-=[Choisir]=-</option>
								<option value='1'>Admis d'emblée</option>
								<option value='2'>Ajournés 1ère Délibération</option>";
                        if ($deliberation[$jury] > 1) {
                            $form1 .= "<option value='3'>Admis après oral</option>
								<option value='4'>Ajournés après oral</option>";
                        }
                        $form1 .= "</select></td></tr>";
                        $form1 .= "<tr><td>Président du Jury</td><td><input type='text' name='president_jury' value='' onFocus=\"this.value=''\" size=17 /></td></tr>";
                        //                $form1.="<tr><td>Lieu de délibération</td><td><input type='text' name='lieu_delib' value='' onFocus=\"this.value=''\" size=17 required /></td></tr>";
                        //                $form1.="<tr><td>Date de délibération</td><td><input type='date' name='date_delib' value='$aujourdhui' onFocus=\"this.select();\" size=8 pattern='^[0-9]{1,2}\/[01]?[0-9]\/[0-9]{4}$' required /></td></tr>";
                        $form1 .= "<tr><td colspan=2><center><input type='submit' value='IMPRIMER LES \nRELEVES DE NOTES' /></center></td></tr>";
                        $form1 .= "</form></table>";
                        $form1 .= fin_cadre_trait_couleur();
                    }
                    /* if($deliberation[$jury]>0){
                $form1.=debut_cadre_trait_couleur('','','',"ATTESTATIONS DE DIPLOME");
                $form1.="<table><form method='POST' action='../plugins/fonctions/inc-pdf-attestation.php?'>";
                $form1.="<tr><td><input type='hidden' name='jury' value='$jury' /></td>
                <td><input type='hidden' name='annee' value='$annee' /><input type='hidden' name='id_type_session' value='$id_type_session' /></td>
                <td><input type='hidden' name='deliberation' value='$deliberation[$jury]' /></td></tr>";
                $form1.="<tr><td colspan=2><center><input type='submit' value='IMPRIMER LES \nATTESTATIONS DE DIPLOME' /></center></td></tr>";
                $form1.="</form></table>";
                $form1.=fin_cadre_trait_couleur();
                } */

                }
                echo $form1;

            }
            if (isset($_REQUEST['tache']) && ($_GET['tache'] == 'region') && (isset($_REQUEST['etape'])) && ($_GET['etape'] == 'resultat')) {
                $aujourdhui = date('xx/m/Y');
                $tSeriesJury = $tSeries[$jury];
                if (isset($_POST['tri_region']) && $_POST['tri_region'] > 0) {
                    var_dump($tri_region);

                    // //selectionner les jury du centre choisie
                    // $sql1 = "SELECT *  FROM bg_repartition WHERE id_centre=$tri_centre GROUP BY jury ";
                    // $result1 = bg_query($lien, $sql1, __FILE__, __LINE__);
                    // while ($row1 = mysqli_fetch_array($result1)) {
                    //     $jury = $row1['jury'];
                    //     var_dump("jury : " . $jury);
                    // }

                }

                $form1 .= debut_cadre_enfonce('', '', '', "Résultats Par Region");
                $form1 .= "<FORM name='form_reg' method='POST' action=''><table size=50%>";
                $form1 .= "<th>Region</th><th></th> ";
                $form1 .= "<tr><td><center><select   name='tri_region' onchange='document.forms.form_reg.submit()'>" . optionsReferentiel($lien, 'region', $tri_region) . "</select></center></td>";

                $form1 .= "<td><input type='hidden' name='id_type_session' value='$id_type_session' /><input type='hidden' name='annee' value='$annee' /></td></tr>";
                $form1 .= "</table></FORM>";
                $form1 .= fin_cadre_enfonce();

                if ($jury > 0) {
                    echo "<center><a href='" . generer_url_ecrire('resultats') . "&etape=resultat';>Retour aux matières du Jury $code_jury[$jury]</a></center>";
                    if ($statut == 'Admin') {
                        if ($deliberation[$jury] >= 0) {
                            $form1 .= debut_cadre_trait_couleur('', '', '', "Candidats Admis avec Anonymat");
                            $form1 .= "<table>";
                            $form1 .= "<tr><td ><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury.php?annee=$annee&jury=$jury&type=0'>Candidats Admis et Admissibles</a></td></tr>";
                            $form1 .= "<tr><td ><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury.php?annee=$annee&jury=$jury&type=8'>Candidats Admis</a></td></tr>";
                            $form1 .= "<tr><td ><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury.php?annee=$annee&jury=$jury&type=9'>Candidats Admissibles</a></td></tr>";
                            $form1 .= "<tr><td width=40%><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury_oral2.php?annee=$annee&jury=$jury'>Candidats Admissibles et Matières d'Oral</a></td></tr>";
                            $form1 .= "</table>";
                            $form1 .= fin_cadre_trait_couleur();
                        }
                    }
                    if ($deliberation[$jury] > 0) {
                        $form1 .= debut_cadre_trait_couleur('', '', '', "Résultats de la Première délibération");
                        $form1 .= "<table>";
                        $form1 .= "<tr><td ><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury.php?annee=$annee&jury=$jury&type=1'>Candidats Admis à la première délibération</a></td></tr>";
                        $form1 .= "<tr><td ><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury.php?annee=$annee&jury=$jury&type=2'>Candidats Admissibles à la première délibération</a></td></tr>";
                        $form1 .= "<tr><td width=40%><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury.php?annee=$annee&jury=$jury&type=3'>Candidats Absents / Abandons</a></td></tr>";
                        $form1 .= "<tr><td width=40%><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury.php?annee=$annee&jury=$jury&type=4'>Candidats Ajournés à la première délibération</a></td></tr>";
                        $form1 .= "<tr><td width=40%><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury_oral.php?annee=$annee&jury=$jury'>Candidats Admissibles et Matières d'Oral</a></td></tr>";
                        $form1 .= "</table>";
                        $form1 .= fin_cadre_trait_couleur();
                    }

                    if ($deliberation[$jury] > 1) {
                        $form1 .= debut_cadre_trait_couleur('', '', '', "Résultats de la Deuxième délibération");
                        $form1 .= "<table>";
                        $form1 .= "<tr><td width=40%><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury.php?annee=$annee&jury=$jury&type=5'>Candidats Admis à la deuxième délibération</a></td></tr>";
                        $form1 .= "<tr><td width=40%><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury.php?annee=$annee&jury=$jury&type=6'>Candidats Absents / Abandons</a></td></tr>";
                        $form1 .= "<tr><td width=40%><img src='../plugins/images/pdf.png' /></td><td valign='middle'><a href='../plugins/fonctions/inc-pdf-liste_jury.php?annee=$annee&jury=$jury&type=7'>Candidats Ajournés à la deuxième délibération</a></td></tr>";
                        $form1 .= "</table>";
                        $form1 .= fin_cadre_trait_couleur();
                    }

                    if ($deliberation[$jury] > 0) {
                        $form1 .= debut_cadre_trait_couleur('', '', '', "Relevés de notes");
                        $form1 .= "<table><form method='POST' action='../plugins/fonctions/inc-pdf-releve.php?'>";
                        $form1 .= "<tr><td><input type='hidden' name='jury' value='$jury' /></td>
								<td><input type='hidden' name='annee' value='$annee' /><input type='hidden' name='id_type_session' value='$id_type_session' /></td>
								<td><input type='hidden' name='deliberation' value='$deliberation[$jury]' /></td></tr>";
                        $form1 .= "<tr><td>Type de relevé</td><td><select name='type_releve'>
								<option value='0'>-=[Choisir]=-</option>
								<option value='1'>Admis d'emblée</option>
								<option value='2'>Ajournés 1ère Délibération</option>";
                        if ($deliberation[$jury] > 1) {
                            $form1 .= "<option value='3'>Admis après oral</option>
								<option value='4'>Ajournés après oral</option>";
                        }
                        $form1 .= "</select></td></tr>";
                        $form1 .= "<tr><td>Président du Jury</td><td><input type='text' name='president_jury' value='' onFocus=\"this.value=''\" size=17 /></td></tr>";
                        //                $form1.="<tr><td>Lieu de délibération</td><td><input type='text' name='lieu_delib' value='' onFocus=\"this.value=''\" size=17 required /></td></tr>";
                        //                $form1.="<tr><td>Date de délibération</td><td><input type='date' name='date_delib' value='$aujourdhui' onFocus=\"this.select();\" size=8 pattern='^[0-9]{1,2}\/[01]?[0-9]\/[0-9]{4}$' required /></td></tr>";
                        $form1 .= "<tr><td colspan=2><center><input type='submit' value='IMPRIMER LES \nRELEVES DE NOTES' /></center></td></tr>";
                        $form1 .= "</form></table>";
                        $form1 .= fin_cadre_trait_couleur();
                    }
                    /* if($deliberation[$jury]>0){
                $form1.=debut_cadre_trait_couleur('','','',"ATTESTATIONS DE DIPLOME");
                $form1.="<table><form method='POST' action='../plugins/fonctions/inc-pdf-attestation.php?'>";
                $form1.="<tr><td><input type='hidden' name='jury' value='$jury' /></td>
                <td><input type='hidden' name='annee' value='$annee' /><input type='hidden' name='id_type_session' value='$id_type_session' /></td>
                <td><input type='hidden' name='deliberation' value='$deliberation[$jury]' /></td></tr>";
                $form1.="<tr><td colspan=2><center><input type='submit' value='IMPRIMER LES \nATTESTATIONS DE DIPLOME' /></center></td></tr>";
                $form1.="</form></table>";
                $form1.=fin_cadre_trait_couleur();
                } */

                }
                echo $form1;

            }

            echo fin_gauche();
            echo fin_gauche();

        }

        echo fin_cadre_trait_couleur();
        echo fin_grand_cadre(), fin_page();
    }

}

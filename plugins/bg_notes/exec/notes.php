<?php
if (!defined('_ECRIRE_INC_VERSION')) {
    return;
}

include_spip('inc/presentation'); // alleger les inclusions avec un inc/presentation_mini
include '../plugins/fonctions/generales.php';

setlocale(LC_TIME, "fr_FR");
define('MAX_LIGNES', 10);

function getJuryByCode($lien, $annee, $code)
{
    $sql = "SELECT jury FROM bg_codes WHERE annee=$annee AND code='$code' LIMIT 0,1";
    echo $sql;
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $row = mysqli_fetch_array($result);
    $jury = $row['jury'];
    return $jury;
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

function resultatJury($lien, $annee, $jury, $deliberation, $num_table = '')
{
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
        $result11 = bg_query($lien, $sql11, __FILE__, __LINE__);
        $sol11 = mysqli_fetch_array($result11);
        $num_table_reel = $sol11['numero'];

        //Recuperation des info du candidat
        $sql12 = "SELECT * FROM bg_candidats WHERE num_table='$num_table_reel' LIMIT 1";
        $result12 = bg_query($lien, $sql12, __FILE__, __LINE__);
        $sol12 = mysqli_fetch_array($result12);

        if ($sol['id_serie'] == 1) { //Si la serie est A4

            if ($sol12['eps'] == 1) { //Si le candidat est apte
                $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`)
			SELECT num_table, id_anonyme, id_type_session, annee, jury, SUM(note*coeff), 19, SUM(note*coeff)/19 as moyenne \n
			FROM bg_notes WHERE annee=$annee AND jury=$jury AND note>=0 AND num_table='$num_table' AND id_type_note!=3 AND id_type_note!=4
			GROUP BY num_table, id_anonyme, id_type_session, annee, jury";
            }
            if ($sol12['eps'] == 2) { //Si le candidat est inapte
                $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`)
				SELECT num_table, id_anonyme, id_type_session, annee, jury, SUM(note*coeff), 18, SUM(note*coeff)/18 as moyenne \n
				FROM bg_notes WHERE annee=$annee AND jury=$jury AND note>=0 AND num_table='$num_table' AND id_type_note!=3 AND id_type_note!=4
				GROUP BY num_table, id_anonyme, id_type_session, annee, jury";
            }

        } else if ($sol['id_serie'] == 3) { //Si la serie est D
            if ($sol12['eps'] == 1) { //Si le candidat est apte
                $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`)
			SELECT num_table, id_anonyme, id_type_session, annee, jury, SUM(note*coeff), 19, SUM(note*coeff)/19 as moyenne \n
			FROM bg_notes WHERE annee=$annee AND jury=$jury AND note>=0 AND num_table='$num_table' AND id_type_note!=3 AND id_type_note!=4
			GROUP BY num_table, id_anonyme, id_type_session, annee, jury";
            }
            if ($sol12['eps'] == 2) { //Si le candidat est inapte
                $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`)
			SELECT num_table, id_anonyme, id_type_session, annee, jury, SUM(note*coeff), 18, SUM(note*coeff)/18 as moyenne \n
			FROM bg_notes WHERE annee=$annee AND jury=$jury AND note>=0 AND num_table='$num_table' AND id_type_note!=3 AND id_type_note!=4
			GROUP BY num_table, id_anonyme, id_type_session, annee, jury";
            }

        } else if ($sol['id_serie'] == 2) { //Si la serie est C
            if ($sol12['eps'] == 1) { //Si le candidat est apte
                $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`)
			SELECT num_table, id_anonyme, id_type_session, annee, jury, SUM(note*coeff), 20, SUM(note*coeff)/20 as moyenne \n
			FROM bg_notes WHERE annee=$annee AND jury=$jury AND note>=0 AND num_table='$num_table' AND id_type_note!=3 AND id_type_note!=4
			GROUP BY num_table, id_anonyme, id_type_session, annee, jury";
            }
            if ($sol12['eps'] == 2) { //Si le candidat est inapte
                $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`)
			SELECT num_table, id_anonyme, id_type_session, annee, jury, SUM(note*coeff), 19, SUM(note*coeff)/19 as moyenne \n
			FROM bg_notes WHERE annee=$annee AND jury=$jury AND note>=0 AND num_table='$num_table' AND id_type_note!=3 AND id_type_note!=4
			GROUP BY num_table, id_anonyme, id_type_session, annee, jury";
            }
        }

        if (is_numeric($num_table)) {

        } else {
            $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`)
			 SELECT notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, notes.jury, (res.total+SUM(note-10)), res.coeff, ((res.total+SUM(note-10))/res.coeff) as moyenne \n
			 FROM bg_notes notes, bg_resultats res WHERE notes.annee=$annee AND res.annee=$annee AND notes.num_table=res.num_table AND notes.jury=$jury AND note>10 AND (note-10) <=5 AND notes.num_table='$num_table' AND notes.id_type_note=4
			 GROUP BY notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, notes.jury, res.coeff, res.total";

            //Pour matiere facultative superieur a 15
            $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`)
			 SELECT notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, notes.jury, (res2.total+SUM(5)), res2.coeff, ((res2.total+SUM(5))/res2.coeff) as moyenne \n
			 FROM bg_notes notes, bg_resultats res2 WHERE notes.annee=$annee AND res2.annee=$annee AND notes.num_table=res2.num_table AND notes.jury=$jury AND note>15 AND notes.num_table='$num_table' AND notes.id_type_note=4
			 GROUP BY notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, notes.jury, res2.coeff, res2.total";

        }

        /* $tSql2[]="REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`)
    SELECT notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, notes.jury, (res.total+SUM(note-10)), res.coeff, ((res.total+SUM(note-10))/res.coeff) as moyenne \n
    FROM bg_notes notes, bg_resultats res WHERE notes.annee=$annee AND res.annee=$annee AND notes.num_table=res.num_table AND notes.jury=$jury AND note>10 AND (note-10) <=5 AND notes.num_table='$num_table' AND notes.id_type_note=4
    GROUP BY notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, notes.jury, res.coeff, res.total";

    //Pour matiere facultative superieur a 15
    $tSql2[]="REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`)
    SELECT notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, notes.jury, (res2.total+SUM(5)), res2.coeff, ((res2.total+SUM(5))/res2.coeff) as moyenne \n
    FROM bg_notes notes, bg_resultats res2 WHERE notes.annee=$annee AND res2.annee=$annee AND notes.num_table=res2.num_table AND notes.jury=$jury AND note>15 AND notes.num_table='$num_table' AND notes.id_type_note=4
    GROUP BY notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, notes.jury, res2.coeff, res2.total";
     */
    }
    //Indication des candidats fraudes //29/05/2023
    // foreach ($tFraudes as $num_table) {
    //     $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`, `delib1`)
    //         SELECT num_table, id_anonyme, id_type_session, annee, jury, 0, SUM(coeff), -2, 'Fraude' \n
    //         FROM bg_notes WHERE annee=$annee AND jury=$jury AND num_table='$num_table'
    //         GROUP BY num_table, id_anonyme, id_type_session, annee, jury";
    // }
    //Indication des candidats absents
    foreach ($tAbsents as $num_table) {
        $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`, `delib1`)
			SELECT num_table, id_anonyme, id_type_session, annee, jury, 0, SUM(coeff), -1, 'Absent' \n
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
        $sql3 = "REPLACE into bg_notes (`num_table`,`id_anonyme`, `ano`, `annee`, `id_serie`, `jury`, `id_matiere`, `id_type_note`, `coeff`, `id_type_session`)\n"
            . "SELECT res.num_table, res.id_anonyme, rep.ano, res.annee, can.serie, res.jury, cal.id_matiere, cal.id_type_note, cal.coeff, res.id_type_session\n"
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

function getJurysTri($lien, $annee, $id_type_session = 0)
{
    if ($id_type_session > 0) {
        $andWhere = " AND id_type_session=$id_type_session ";
    } else {
        $andWhere = '';
    }
    $sql = "SELECT jury FROM bg_repartition WHERE annee=$annee $andWhere GROUP BY jury ORDER BY jury";
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

function exec_notes()
{
    $exec = _request('exec');
    $titre = "exec_$exec";
    $commencer_page = charger_fonction('commencer_page', 'inc');
    echo $commencer_page($titre);
    $lien = lien();

    foreach ($_REQUEST as $key => $val) {
        $$key = mysqli_real_escape_string($lien, trim($val));
    }

    if ($annee == '') {
        $annee = recupAnnee($lien); //$annee = date("Y");
    }
    if ($id_type_session == '') {
        $id_type_session = recupSession($lien);
    }

    if (isset($_REQUEST['etape']) && $_REQUEST['etape'] == 'detruire_session') {
        $annee = '';
        $id_type_session = '';
    }

    $statut = getStatutUtilisateur();
    $tab_auteur = $GLOBALS["auteur_session"];
    $login = $tab_auteur['login'];

    if ($statut != 'Admin') {
        switch ($statut) {
            case 'Notes':
                $isOperateurNotes = true;
                $cd_operateur = stripslashes($tab_auteur['email']);
                $tab_cd_operateur = explode('@', $cd_operateur);
                if ($tab_cd_operateur[1] > 0) {
                    $tabJurysOperateur[] = $tab_cd_operateur[1];
                }

                if ($tab_cd_operateur[2] > 0) {
                    $tabJurysOperateur[] = $tab_cd_operateur[2];
                }

                if ($tab_cd_operateur[3] > 0) {
                    $tabJurysOperateur[] = $tab_cd_operateur[3];
                }

                break;
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
    if (isAutorise(array('Admin', 'Dre', 'Notes', 'Encadrant', 'Informaticien', 'Inspection'))) {
        if ($_REQUEST['annee'] == "" and $_REQUEST['id_type_session'] == "") {
            $liste = debut_cadre_enfonce();
            $liste .= "<table><form method='POST' >";
            $liste .= "<tr><td><select name='annee'>" . optionsAnnee(2020, $annee) . "</select></td><td><select name='id_type_session'>" . optionsReferentiel($lien, 'type_session', $id_type_session, '', false) . "</select></td></tr>";
            $liste .= "<tr><td colspan=2><center><input type='submit' name='param_saisie' value='CONTINUER' /></center></td></tr>";
            $liste .= "</form></table>";
            $liste .= fin_cadre_enfonce();
            echo $liste;
        } else {
            if ($isOperateurNotes) {$tJurys = $tabJurysOperateur;if (!is_array($tJurys)) {
                $tJurys = getJurys($lien, $annee, $id_type_session);
            }
            } else {
                $tJurys = getJurys($lien, $annee, $id_type_session);
            }

            $tJurys = getJurysTri($lien, $annee, $id_type_session);
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
            echo debut_grand_cadre();
            if ($_GET['etape'] != 'resultats') {
                echo debut_gauche();
                if (isset($_REQUEST['jury'])) {
                    echo debut_boite_info();
                    echo "<p>Gestion des notes</p>";
                    // echo "<p><a href='" . generer_url_ecrire('notes') . "&etape=resultats&jury=$jury&annee=$annee&id_type_session=$id_type_session'>$deliberation[$jury] Résultats du jury $code_jury[$jury]</a></p>";
                    echo "<p><a href='" . generer_url_ecrire('notes') . "&etape=listes&jury=$jury&annee=$annee&id_type_session=$id_type_session'>Listes et relevés: jury $code_jury[$jury]</a></p>";
                    // echo "<p><a href='" . generer_url_ecrire('notes') . "&etape=repechage' class='ajax'>Repéchage</a></p>";
                    // echo "<p><a href='" . generer_url_ecrire('notes') . "&etape=calculMoy&jury1=500&jury2=599' class='ajax'>CalculM</a></p>";
                    // echo "<p><a href='" . generer_url_ecrire('notes') . "&etape=MatFac' class='ajax'>MatFac</a></p>";
                    //echo "<p><a href='".generer_url_ecrire('notes')."&etape=mention' class='ajax'>Mention</a></p>";
                    echo "<p><a href='" . generer_url_ecrire('notes') . "&action=logout&logout=prive' class='ajax'>Se déconnecter</a></p>";
                    echo fin_boite_info();
                }
                if ($statut == 'Admin') {
                    echo debut_boite_info();
                    //    $lien_impressions= "<center><a href='../plugins/fonctions/inc-pdf-grands_releves.php?jury=$jury&id_serie=$id_serie&deliberation=$deliberation[$jury]&tri=$si_tous'>Imprimer les résultats du Jury $code_jury[$jury] et de la série $serie</a></center>";
                    // echo "<p><a href='../plugins/fonctions/inc-pdf-tendance.php?region=GRAND-LOME'>Tendance GRAND-LOME</a></p>";
                    // echo "<p><a href='../plugins/fonctions/inc-pdf-tendance.php?region=MARITIME'>Tendance MARITIME</a></p>";
                    // echo "<p><a href='../plugins/fonctions/inc-pdf-tendance.php?region=PLATEAUX-OUEST'>Tendance PLATEAUX-OUEST</a></p>";
                    // echo "<p><a href='../plugins/fonctions/inc-pdf-tendance.php?region=PLATEAUX-EST'>Tendance PLATEAUX-EST</a></p>";
                    // echo "<p><a href='../plugins/fonctions/inc-pdf-tendance.php?region=CENTRALE'>Tendance CENTRALE</a></p>";
                    // echo "<p><a href='../plugins/fonctions/inc-pdf-tendance.php?region=KARA'>Tendance KARA</a></p>";
                    // echo "<p><a href='../plugins/fonctions/inc-pdf-tendance.php?region=SAVANE'>Tendance SAVANE</a></p>";
                    // echo "<p><a href='../plugins/fonctions/inc-pdf-tendance.php?region=NATIONAL'>Tendance NATIONAL</a></p>";

                    echo "<p><a href='" . generer_url_ecrire('notes') . "&etape=verifAno'>Vérifier les anonymats</a></p>";
                    echo fin_boite_info();
                }

                echo debut_boite_info();
                echo "<form method='POST'><input name='detruire_session' type='submit' value='Changer de session' /></form>";
                echo fin_boite_info();

                echo debut_droite();
            }

            echo debut_cadre_trait_couleur('', '', '', "GESTION DES NOTES", "", "", false);

            /*
            echo "<pre>";
            print_r($_POST);
            echo "</pre>";
             */

//Repechage
            if (isset($_POST['lancerRepechage'])) {
                alert($moyenneRepech);
            }
            if (isset($_REQUEST['etape']) && $_REQUEST['etape'] == 'repechage') {
                echo "<form method='POST' action=''>
                    <h1>REPECHAGE </h1>
                    <p>
                        <label>Moyenne de repechage &nbsp;: &nbsp;</label>
                        <input name='moyenneRepech' type='text' autofocus  />
                    </p>

                	<center><input type='submit' class='spip' value='Afficher les candidats' name='enregistrer_not_ano' />
                	<input type='submit' class='spip' value='Lancer le repechage' name='lancerRepechage' /></center>
                </form>";

                $sql_aff_rep = "SELECT * FROM bg_resultats WHERE total >= 135 AND total < 150 ORDER BY num_table";
                $result_aff1 = bg_query($lien, $sql_aff_rep, __FILE__, __LINE__);

                $tableau = "<table class='spip liste'><thead>";
                $tableau .= "<th>N° table</th><th>Moyenne</th><th>Total</th><th>Jury</th>";
                $tableau .= "</thead><tbody>";
                $tab = array('num_table', 'moyenne', 'total', 'jury');
                while ($row = mysqli_fetch_array($result_aff1)) {
                    foreach ($tab as $var) {
                        $$var = $row[$var];
                    }

                    $tableau .= "<tr><td>$num_table</td><td align='rigth'>$moyenne</td><td align='rigth'>$total</td><td align='rigth'>$jury</td>
                						</tr>";
                }
                $tableau .= "</tbody></table>";
                echo $formAnnee;
                echo $tableau;

            }

            if (isset($_REQUEST['etape']) && $_REQUEST['etape'] == 'MatFac') {

                $sql_noteFac = "SELECT * ,MIN(note) AS min_note, id_matiere AS mat FROM `bg_notes` WHERE id_type_note=4 GROUP BY num_table HAVING (COUNT(note)>2)";
                $result_noteFac = bg_query($lien, $sql_noteFac, __FILE__, __LINE__);
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

                    var_dump($id_matiere);
                    var_dump($noteFac);

                    $sqlFac2 = "UPDATE `bg_notes` SET note=NULL WHERE num_table='$num_tableFac' AND id_type_session=$id_type_session AND id_matiere=$id_matiere AND id_type_note=$id_type_note AND jury=$jury AND note=$noteFac";
                    $resultRep1 = bg_query($lien, $sqlFac2, __FILE__, __LINE__);
                }
            }

            if (isset($_REQUEST['etape']) && $_REQUEST['etape'] == 'calculMoy') {
                $jury1 = $_GET['jury1'];
                $jury2 = $_GET['jury2'];

                //Rechercher les candidats absents et ceux presents
                $sql1 = "SELECT num_table, min(note) as mini, max(note) as maxi FROM bg_notes WHERE annee=$annee AND jury BETWEEN $jury1 AND $jury2 $andNumTable GROUP BY num_table ";
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
                    var_dump($sol['jury']);
                    $jury = $sol['jury'];

                    //Recuperation des info du candidat
                    $sql12 = "SELECT * FROM bg_candidats WHERE num_table='$num_table' LIMIT 1";
                    $result12 = bg_query($lien, $sql12, __FILE__, __LINE__);
                    $sol12 = mysqli_fetch_array($result12);

                    if ($sol['id_serie'] == 1) { //Si la serie est A4

                        if ($sol12['eps'] == 1) { //Si le candidat est apte
                            $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`)
				SELECT num_table, id_anonyme, id_type_session, annee, jury, SUM(note*coeff), 19, SUM(note*coeff)/19 as moyenne \n
				FROM bg_notes WHERE annee=$annee AND jury=$jury AND note>=0 AND num_table='$num_table' AND id_type_note!=3 AND id_type_note!=4
				GROUP BY num_table, id_anonyme, id_type_session, annee, jury";
                        }
                        if ($sol12['eps'] == 2) { //Si le candidat est inapte
                            $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`)
					SELECT num_table, id_anonyme, id_type_session, annee, jury, SUM(note*coeff), 18, SUM(note*coeff)/18 as moyenne \n
					FROM bg_notes WHERE annee=$annee AND jury=$jury AND note>=0 AND num_table='$num_table' AND id_type_note!=3 AND id_type_note!=4
					GROUP BY num_table, id_anonyme, id_type_session, annee, jury";
                        }
                    } else if ($sol['id_serie'] == 3) { //Si la serie est D
                        if ($sol12['eps'] == 1) { //Si le candidat est apte
                            $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`)
				SELECT num_table, id_anonyme, id_type_session, annee, jury, SUM(note*coeff), 19, SUM(note*coeff)/19 as moyenne \n
				FROM bg_notes WHERE annee=$annee AND jury=$jury AND note>=0 AND num_table='$num_table' AND id_type_note!=3 AND id_type_note!=4
				GROUP BY num_table, id_anonyme, id_type_session, annee, jury";
                        }
                        if ($sol12['eps'] == 2) { //Si le candidat est inapte
                            $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`)
				SELECT num_table, id_anonyme, id_type_session, annee, jury, SUM(note*coeff), 18, SUM(note*coeff)/19 as moyenne \n
				FROM bg_notes WHERE annee=$annee AND jury=$jury AND note>=0 AND num_table='$num_table' AND id_type_note!=3 AND id_type_note!=4
				GROUP BY num_table, id_anonyme, id_type_session, annee, jury";
                        }

                    } else if ($sol['id_serie'] == 2) { //Si la serie est C
                        if ($sol12['eps'] == 1) { //Si le candidat est apte
                            $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`)
				SELECT num_table, id_anonyme, id_type_session, annee, jury, SUM(note*coeff), 20, SUM(note*coeff)/20 as moyenne \n
				FROM bg_notes WHERE annee=$annee AND jury=$jury AND note>=0 AND num_table='$num_table' AND id_type_note!=3 AND id_type_note!=4
				GROUP BY num_table, id_anonyme, id_type_session, annee, jury";
                        }
                        if ($sol12['eps'] == 2) { //Si le candidat est inapte
                            $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`)
				SELECT num_table, id_anonyme, id_type_session, annee, jury, SUM(note*coeff), 19, SUM(note*coeff)/19 as moyenne \n
				FROM bg_notes WHERE annee=$annee AND jury=$jury AND note>=0 AND num_table='$num_table' AND id_type_note!=3 AND id_type_note!=4
				GROUP BY num_table, id_anonyme, id_type_session, annee, jury";
                        }
                    }

                    $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`)
			SELECT notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, notes.jury, (res.total+SUM(note-10)), res.coeff, ((res.total+SUM(note-10))/res.coeff) as moyenne \n
			FROM bg_notes notes, bg_resultats res WHERE notes.annee=$annee AND res.annee=$annee AND notes.num_table=res.num_table AND notes.jury=$jury AND note>10 AND (note-10) <=5 AND notes.num_table='$num_table' AND notes.id_type_note=4
			GROUP BY notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, notes.jury, res.coeff, res.total";

                    //Pour matiere facultative superieur a 15
                    $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`)
           SELECT notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, notes.jury, (res2.total+SUM(5)), res2.coeff, ((res2.total+SUM(5))/res2.coeff) as moyenne \n
           FROM bg_notes notes, bg_resultats res2 WHERE notes.annee=$annee AND res2.annee=$annee AND notes.num_table=res2.num_table AND notes.jury=$jury AND note>15 AND notes.num_table='$num_table' AND notes.id_type_note=4
           GROUP BY notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, notes.jury, res2.coeff, res2.total";

                    /*     $tSql2[]="REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`)
                SELECT num_table, id_anonyme, id_type_session, annee, jury, SUM(note*coeff), 18, SUM(note*coeff)/18 as moyenne \n
                FROM bg_notes WHERE annee=$annee AND jury=$jury AND note>=0 AND num_table='$num_table' AND id_type_note!=3 AND id_type_note!=4
                GROUP BY num_table, id_anonyme, id_type_session, annee, jury";

                $tSql2[]="REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`)
                SELECT notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, notes.jury, (res.total+SUM(note-10)), res.coeff, ((res.total+SUM(note-10))/res.coeff) as moyenne \n
                FROM bg_notes notes, bg_resultats res WHERE notes.annee=$annee AND res.annee=$annee AND notes.num_table=res.num_table AND notes.jury=$jury AND note>10 AND (note-10) <=5 AND notes.num_table='$num_table' AND notes.id_type_note=4
                GROUP BY notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, notes.jury, res.coeff, res.total";

                //Pour matiere facultative superieur a 15
                $tSql2[]="REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`)
                SELECT notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, notes.jury, (res2.total+SUM(5)), res2.coeff, ((res2.total+SUM(5))/res2.coeff) as moyenne \n
                FROM bg_notes notes, bg_resultats res2 WHERE notes.annee=$annee AND res2.annee=$annee AND notes.num_table=res2.num_table AND notes.jury=$jury AND note>15 AND notes.num_table='$num_table' AND notes.id_type_note=4
                GROUP BY notes.num_table, notes.id_anonyme, notes.id_type_session, notes.annee, notes.jury, res2.coeff, res2.total";
                 */
                }
                //Indication des candidats fraude 29/05/2023
                // foreach ($tFraudes as $num_table) {
                //     $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`, `delib1`)
                // SELECT num_table, id_anonyme, id_type_session, annee, jury, 0, SUM(coeff), -2, 'Fraude' \n
                // FROM bg_notes WHERE annee=$annee AND jury=$jury AND num_table='$num_table'
                // GROUP BY num_table, id_anonyme, id_type_session, annee, jury";
                // }

                //Indication des candidats absents
                foreach ($tAbsents as $num_table) {
                    $tSql2[] = "REPLACE INTO bg_resultats(`num_table`, `id_anonyme`, `id_type_session`, `annee`, `jury`, `total`, `coeff`, `moyenne`, `delib1`)
			SELECT num_table, id_anonyme, id_type_session, annee, jury, 0, SUM(coeff), -1, 'Absent' \n
			FROM bg_notes WHERE annee=$annee AND jury=$jury AND num_table='$num_table'
			GROUP BY num_table, id_anonyme, id_type_session, annee, jury";
                }
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

                foreach ($tSql2 as $sql2) {
                    bg_query($lien, $sql2, __FILE__, __LINE__);
                }

            }

            if (isset($_REQUEST['etape']) && $_REQUEST['etape'] == 'verifAno') {
                verifierAnonymat($lien, $annee);
            }

            //Impression des listes et des releves de notes
            if (isset($_REQUEST['etape']) && $_REQUEST['etape'] == 'listes') {
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
                    echo "<center><a href='" . generer_url_ecrire('notes') . "&jury=$jury&annee=$annee&id_type_session=$id_type_session';>Retour aux matières du Jury $code_jury[$jury]</a></center>";
                    if (($statut == 'Admin') || ($statut == 'Dre')) {
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

            //Imprimer les grands releves
            if (isset($_REQUEST['etape']) && $_REQUEST['etape'] == 'resultats') {
                $tSeriesJury = $tSeries[$jury];

                $form1 = debut_cadre_enfonce();
                echo "<center><a href='" . generer_url_ecrire('notes') . "&jury=$jury&annee=$annee&id_type_session=$id_type_session';>Retour aux matières du Jury $code_jury[$jury]</a>";
                echo "<br/><a href='" . generer_url_ecrire('notes') . "&etape=listes&jury=$jury&annee=$annee&id_type_session=$id_type_session'>Listes et relevés: jury $code_jury[$jury]</a></center>";
                $form1 .= "<FORM name='form_resultats' method='POST' action=''><table size=50%>";
                $form1 .= "<th>Jury</th><th>Série</th><th></th><th></th><th></th>";
                $form1 .= "<tr><td><select name='jury' onchange=\"document.forms['form_resultats'].submit();\">";
                foreach ($tJurys as $j) {
                    if ($j == $jury) {
                        $selected = 'selected';
                    } else {
                        $selected = '';
                    }

                    $form1 .= "<option value='$j' $selected >" . $code_jury[$j] . "</option>";
                }
                $form1 .= "</select></td>";
                $form1 .= "<td><select name='id_serie' onchange=\"document.forms['form_resultats'].submit();\">";
                $form1 .= "<option value='0'>-=[Série]=-</option>";
                //        print_r($tSeriesJury);
                foreach ($tSeriesJury as $ser_id2 => $ser) {
                    if ($ser_id2 == $id_serie) {
                        $selected = 'selected';
                    } else {
                        $selected = '';
                    }

                    $form1 .= "<option value='$ser_id2' $selected >$ser</option>";
                }
                $form1 .= "</select><input type='hidden' name='id_type_session' value='$id_type_session' /><input type='hidden' name='annee' value='$annee' /></td>";
                $form1 .= "<td>Tous<input type='radio' name='si_tous' value='oui' /></td><td>En cours<input type='radio' name='si_tous' value='non' checked /></td><td><input type='submit' value='OK' name='' /></td></tr>";
                $form1 .= "</table></FORM>";
                $form1 .= fin_cadre_enfonce();
                echo $form1;
                if (isset($_POST['jury']) && $_POST['jury'] > 0 && isset($_POST['id_serie']) && $_POST['id_serie'] > 0) {
                    if ($deliberation[$jury] <= 1) {
                        if (($tNbNotesASaisir[$id_serie]['total'][1] > $tNbNotesSaisies[$id_serie]['total'][1])
                            || ($tNbNotesASaisir[$id_serie]['total'][2] > $tNbNotesSaisies[$id_serie]['total'][2])
                            || ($tNbNotesASaisir[$id_serie]['total'][4] > $tNbNotesSaisies[$id_serie]['total'][4])) {
                            echo debut_boite_alerte() . "Vous n'avez pas fini de saisir les notes des épreuves écrites et/ou pratiques et/ou facultatives " . fin_boite_alerte();
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
                    $lien_impressions = "<center><a href='../plugins/fonctions/inc-pdf-grands_releves.php?jury=$jury&id_serie=$id_serie&deliberation=$deliberation[$jury]&tri=$si_tous'>Imprimer les résultats du Jury $code_jury[$jury] et de la série $serie</a></center>";
                    resultatJury($lien, $annee, $jury, $deliberation[$jury], $num_table);

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
                        }
                        // 29/05/2023
                        // else if ($note == -2){
                        //     $tNotes[$id_anonyme][$id_matiere][$id_type_note]['note'] = 'F';
                        // }
                        else {
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
                        echo "<center><a href='" . generer_url_ecrire('notes') . "&jury=$jury&annee=$annee&id_type_session=$id_type_session';>Retour aux matières du Jury $code_jury[$jury]</a></center>";
                    }
                }

            }

            //Affichage de la liste des jurys puis séries puis matières
            if (!isset($_GET['id_matiere']) && !isset($_GET['etape'])) {
                $formJurys = "<FORM name='' method='POST' action=''>";
                $formJurys .= "<table>";
                $formJurys .= "<th colspan=4>Jurys</th>";
                foreach ($tJurys as $jury) {
                    $rowspan = 1;
                    $tr = '';
                    $td = '';
                    $juryChoisi = $_GET['jury'];
                    $tSeriesJury = $tSeries[$juryChoisi];
                    if ($_GET['jury'] && !isset($_GET['id_matiere']) && $juryChoisi == $jury) {
                        //Affichage des séries par jury
                        foreach ($tSeriesJury as $id_serie => $serie) {
                            $td = "<td>Série</td><td>Matières</td><td>Taux</td>";
                            $rowspan++;
                            $tr2 = '';
                            foreach (array('4', '1', '2', '3') as $id_type_note) {
                                $type = selectReferentiel($lien, 'type_note', $id_type_note);
                                $cc = 0;
                                //Affichage des matieres par série
                                foreach ($tMatieres[$id_type_note][$id_serie] as $id_matiere => $matiere) {
                                    $rowspan++;
                                    $notesSaisies = $tNbNotesSaisies[$id_serie][$id_matiere][$id_type_note];
                                    $notesASaisir = $tNbNotesASaisir[$id_serie][$id_matiere][$id_type_note];
                                    if ($notesSaisies == '') {$notesSaisies = 0;
                                        $color = "red";}
                                    if ($notesSaisies > 0 && $notesSaisies < $notesASaisir) {
                                        $color = "blue";
                                    } elseif ($notesSaisies == $notesASaisir) {
                                        $color = "green";
                                    }

                                    //Affichage du type de matière
                                    if ($cc == 0) {
                                        $affiche_type = $type;
                                    } else {
                                        $affiche_type = '';
                                    }

                                    //    echo $deliberation[$jury].'ZZZZZZ';
                                    if ($deliberation[$jury] == 0) {
                                        $tr2 .= "<tr><td><b>$affiche_type</b></td><td><input type='button' value='$matiere' onClick=window.location='" . generer_url_ecrire('notes') . "&jury=$jury&id_serie=$id_serie&id_matiere=$id_matiere&id_type=$id_type_note&id_type_session=$id_type_session&annee=$annee';  /></td><td><font color='$color'>" . $notesSaisies . '/' . $notesASaisir . "</font></td></tr>";
                                    } else {
                                        if ($statut == 'Admin') {
                                            $tr2 .= "<tr><td><b>$affiche_type</b></td><td><input type='button' value='$matiere' onClick=window.location='" . generer_url_ecrire('notes') . "&jury=$jury&id_serie=$id_serie&id_matiere=$id_matiere&id_type=$id_type_note&id_type_session=$id_type_session&annee=$annee';  /></td><td><font color='$color'>" . $notesSaisies . '/' . $notesASaisir . "</font></td></tr>";
                                        } elseif ($statut != 'Admin' && $id_type_note == 3 || $jury == 151 || $jury == 817) {
                                            $tr2 .= "<tr><td><b>$affiche_type</b></td><td><input type='button' value='$matiere' onClick=window.location='" . generer_url_ecrire('notes') . "&jury=$jury&id_serie=$id_serie&id_matiere=$id_matiere&id_type=$id_type_note&id_type_session=$id_type_session&annee=$annee';  /></td><td><font color='$color'>" . $notesSaisies . '/' . $notesASaisir . "</font></td></tr>";
                                        } else {
                                            $tr2 .= '';
                                        }
                                    }
                                    $cc++;
                                }
                                $type_ancien = $type;
                            }
                            $tr .= "<tr><td><input type='button' name='serie' value='Série $serie'/></td></tr>" . $tr2;
                        }
                    }
                    $formJurys .= "<tr><td rowspan=$rowspan>$code_jury[$jury]</td><td rowspan=$rowspan><input type='button' name='jury' value=\"Jury $code_jury[$jury]\" onClick=window.location='" . generer_url_ecrire('notes') . "&jury=$jury&id_type_session=$id_type_session&annee=$annee'; /></td>$td</tr>";
                    $formJurys .= $tr;
                }
                $formJurys .= "</table>";
                $formJurys .= "</FORM>";
                echo $formJurys;
            }

            if (isset($_POST['enregistrer_notes'])) {
                foreach ($_POST['tNotes'] as $num_table => $note) {
                    if ($note != '') {
                        if ($note < 0 || $note == '-') {
                            $note = -1;
                        }
                        # F pour Fraude au cas où un cas de fraude se produit 29/05/2023
                        // if ($note == 'F'){
                        //     $note = -2;
                        // }

                        $sql = "UPDATE bg_notes SET note='$note', login='$login' WHERE annee=$annee AND num_table='$num_table' AND id_matiere='$id_matiere' AND id_type_note=$id_type ";
                        bg_query($lien, $sql, __FILE__, __LINE__);
                    }
                }
            }

            //Affichage des champs pour la saisie des notes
            if (isset($_GET['jury']) && isset($_GET['id_serie']) && isset($_GET['id_matiere'])) {
                $serie = selectReferentiel($lien, 'serie', $id_serie);
                $matiere = selectReferentiel($lien, 'matiere', $id_matiere);

                echo debut_cadre_enfonce(), "<b>Jury: $code_jury[$jury] &nbsp;	Série: $serie &nbsp;&nbsp;	Matière: $matiere (" . selectReferentiel($lien, 'type_note', $id_type) . ")<b/> ", fin_cadre_enfonce();

                //Formulaire de modification de note de candidat
                if (isset($_GET['num_table']) && isset($_POST['modif'])) {
                    $id_type_note = $id_type;
                    $sql1 = "INSERT INTO bg_histo_notes (`num_table`, `id_anonyme`, `ano`, `id_type_session`, `annee`, `id_matiere`, `id_type_note`, `id_serie`, `note`, `coeff`, `jury`, `maj`, `login`)
					(SELECT `num_table`, `id_anonyme`, `id_type_session`, `annee`, `id_matiere`, `id_type_note`, `id_serie`, `note`, `coeff`, `jury`, `maj`, `login`
					FROM bg_notes WHERE annee=$annee AND num_table='$num_table' AND id_matiere='$id_matiere' AND id_type_note=$id_type_note) ";
                    //            bg_query($lien,$sql1,__FILE__,__LINE__);
                    $note2 = $_POST['note2'];
                    if ($note2 < 0 || $note2 == '-') {
                        $note2 = -1;
                    } # F pour Fraude au cas où un cas de fraude se produit 29/05/2023
                    // if($note2 == 'F'){
                    //     $note2 = -2;
                    // }

                    $sql2 = "UPDATE bg_notes SET note='$note2', login='$login' WHERE annee=$annee AND num_table='$num_table' AND id_matiere='$id_matiere' AND id_type_note=$id_type ";
                    bg_query($lien, $sql2, __FILE__, __LINE__);
                    resultatJury($lien, $annee, $jury, $deliberation[$jury], $num_table);
                }

                if (isset($_GET['num_table']) && !isset($_POST['modif'])) {
                    $note1 = getNote($lien, $annee, $num_table, $id_matiere, $id_type);
                    if ($note1 < 0) {$note1 = "-";
                        $remarque = "<font color='blue'>ABSENT</font>";} else { $remarque = '';}
                    if ($id_type == 4) {
                        $note_sup = 20;
                    } else {
                        $note_sup = 20;
                    }

                    echo debut_cadre_enfonce();
                    $formModNote = "<form name='' action='' method='POST'><table>";
                    //$formModNote .= "<th>Candidat : $num_table</th><th><input type='text' name='note2' value='$note1' selected size=2 maxlength=2 onKeyUp=\"if(this.value!='-' && this.value!='F' && isNaN(parseInt(this.value))) this.value='';if(this.value>$note_sup) {alert('Un candidat ne peut avoir plus de $note_sup, veuillez saisir cette note de nouveau');this.value='';}\" autofocus />$remarque</th><th><input type='hidden' name='modif' /><input type='hidden' name='id_type_session' value='$id_type_session' /><input type='hidden' name='annee' value='$annee' /><input type='submit' name='Modifier_note' value='OK' /></th>";
                    $formModNote .= "<th>Candidat : $num_table</th><th><input type='text' name='note2' value='$note1' selected size=2 maxlength=2 onKeyUp=\"if(this.value!='-' && isNaN(parseInt(this.value))) this.value='';if(this.value>$note_sup) {alert('Un candidat ne peut avoir plus de $note_sup, veuillez saisir cette note de nouveau');this.value='';}\" autofocus />$remarque</th><th><input type='hidden' name='modif' /><input type='hidden' name='id_type_session' value='$id_type_session' /><input type='hidden' name='annee' value='$annee' /><input type='submit' name='Modifier_note' value='OK' /></th>";
                    $formModNote .= "</table></form>";
                    echo $formModNote;
                    echo fin_cadre_enfonce();
                }
                $sql = "SELECT * FROM bg_notes WHERE annee=$annee AND jury=$jury AND id_serie=$id_serie AND id_matiere=$id_matiere AND id_type_note='$id_type' ORDER BY ano ";
                $result = bg_query($lien, $sql, __FILE__, __LINE__);

                $disabled_conf = recupModifnote($lien);

                $formNotes = "<form name='' method='POST' action=''>";
                $formNotes .= "<table >";
                $formNotes .= "<th>Candidats</th><th>Notes</th>";
                while ($row = mysqli_fetch_array($result)) {
                    $note = $row['note'];
                    $num_table = $row['num_table'];

                    if ($deliberation[$jury] > 0) {
                        $tNoms = getNoms($lien, $annee, $num_table);
                        $nom_candidat = $tNoms['noms'];
                    }
                    if ($note == '') {
                        $disabled = '';
                        $note = '';
                    } else if ($disabled_conf != 0) {$disabled = '';} else { $disabled = 'disabled';}
                    //if (($statut == "Encadrant") || ($statut == "Informaticien")) {$disabled = 'disabled';}
                    if (($statut == "Informaticien")) {$disabled = 'disabled';}
                    if ($note < 0) {
                        //$note = "N/C";
                        $note = "-";
                        $remarque = "<font color='blue'>ABSENT</font>";
                    } else { $remarque = '';}
                    // 29/05/2023
                    // if ($note == -2) {
                    //     $note = "F";
                    //     $remarque = "<font color='red'>FRAUDE</font>";
                    // } else { $remarque = '';}
                    if ($id_type == 4) {
                        $note_sup = 20;
                    } else {
                        $note_sup = 20;
                    }

                    //$formNotes .= "<tr><td><b><a tabindex='-1' href='" . generer_url_ecrire('notes') . "&jury=$jury&id_serie=$id_serie&id_matiere=$id_matiere&id_type=$id_type&num_table=$num_table'>" . $row['num_table'] . " $nom_candidat</a></b></td><td><b><input type='text' name='tNotes[$num_table]' value=\"$note\" $disabled size=2 maxlength=2 onKeyUp=\"if(this.value!='-' && this.value!='F' && isNaN(parseInt(this.value))) this.value='';if(this.value>$note_sup) {alert('Un candidat ne peut avoir plus de $note_sup, veuillez saisir cette note de nouveau');this.value='';}\" autofocus />$remarque</b></td></tr>";
                    $formNotes .= "<tr><td><b><a tabindex='-1' href='" . generer_url_ecrire('notes') . "&jury=$jury&id_serie=$id_serie&id_matiere=$id_matiere&id_type=$id_type&num_table=$num_table'>" . $row['num_table'] . " $nom_candidat</a></b></td><td><b><input type='text' name='tNotes[$num_table]' value=\"$note\" $disabled size=2 maxlength=2 onKeyUp=\"if(this.value!='-' && isNaN(parseInt(this.value))) this.value='';if(this.value>$note_sup) {alert('Un candidat ne peut avoir plus de $note_sup, veuillez saisir cette note de nouveau');this.value='';}\" autofocus />$remarque</b></td></tr>";
                }
                $formNotes .= "<tr><td><input type='hidden' name='jury' value='$jury'/></td><td><input type='hidden' name='id_matiere' value='$id_matiere' /><input type='hidden' name='id_type' value='$id_type' /><input type='hidden' name='id_type_session' value='$id_type_session' /><input type='hidden' name='annee' value='$annee' /></td></tr>";

                $formNotes .= "<tr><td><center><input type='submit' class='spip' value='Enregistrer' name='enregistrer_notes' /></center></td><td><center><input type='reset' value='Quitter' onClick=\"if(confirm('Avez-vous enregistrer les notes?'))  return window.location='" . generer_url_ecrire('notes') . "&jury=$jury'; else return false; \" /></center></td></tr>";

                $formNotes .= "</table></form>";
                echo $formNotes;
            }
            echo fin_cadre_trait_couleur();
        }
        echo fin_grand_cadre(), fin_gauche(), fin_page();
    }
}

<?php
include_once '../generales.php';
$lien = lien();


foreach ($_REQUEST as $key => $val) $$key = mysqli_real_escape_string ($lien, trim(addslashes($val)));
if ($annee == '') $annee = recupAnnee($lien); //$annee = date("Y");


$DateConvocPied = "IMPRIME LE " . date('d') . '/' . date('m') . '/' . date('Y');

if (isset($_GET['id_centre'])) {
    if($id_type_session != 0) $andWhere .= " AND can.type_session=$id_type_session ";
    if($id_region != 0) $andWhere .= " AND reg.id=$id_region ";
    if($id_prefecture != 0) $andWhere .= " AND pref.id=$id_prefecture ";
    if($id_ville != 0) $andWhere .= " AND vil.id=$id_ville ";
    if($id_inspection != 0) $andWhere .= " AND insp.id=$id_inspection ";
    if($id_serie != 0) $andWhere .= " AND ser.id=$id_serie ";
    if($id_sexe != 0) $andWhere .= " AND can.sexe=$id_sexe ";
    if($id_eps != 0) $andWhere .= " AND spo.id=$id_eps ";
    if($id_langue_vivante != 0) $andWhere .= " AND lang.id=$id_langue_vivante ";
    if($id_centre != 0) $andWhere .= " AND cent.id=$id_centre ";
    if($id_etablissement != 0) $andWhere .= " AND eta.id=$id_etablissement ";
    if($jury != 0) $andWhere .= " AND rep.jury=$jury ";

    if($tache == 'affichage') {
        $orderBy = 'ser.serie, rep.numero, can.nom ';
        $andTitre = " D'AFFICHAGE";
        $cols = array('num' => utf8_decode('N° TABLE'), 'noms' => 'CANDIDAT', 'dldn' => 'DATE ET LIEU DE NAISSANCE',
         'sexe' => 'SEXE', 'eta' => 'ETABLISSEMENT', 'serie' => 'SERIE', 'jr' => 'JURY',
          'eps' => 'EPS', 'lv2' => 'LV2', 'efa' => 'EF. A', 'efb' => 'EF. B');
    }elseif($tache == 'emargement') {
        $orderBy = 'rep.numero';
        $andTitre = " D'EMARGEMENT";
        $cols = array('num' => utf8_decode('N° TABLE'), 'noms' => 'CANDIDAT', 'dldn' => 'DATE ET LIEU DE NAISSANCE', 
        'sexe' => 'SEXE', 'eta' => 'ETABLISSEMENT', 'serie' => 'SERIE', 'jr' => 'JURY', 
        'eps' => 'EPS', 'lv2' => 'LV2', 'efa' => 'EF. A', 'efb' => 'EF. B', 'sig' => '     EMARGEMENT   ');
    }else{
        $orderBy = 'rep.numero';
        $andTitre = " D'EMARGEMENT";
        $cols = array('num' => utf8_decode('N° TABLE'), 'noms' => 'CANDIDAT', 'dldn' => 'DATE ET LIEU DE NAISSANCE', 
        'sexe' => 'SEXE', 'emarg1' => '1ERE DEMI JOURNEE', 'emarg2' => '2IEME DEMI JOURNEE', 'emarg3' => '3IEME DEMI JOURNEE', 
        'emarg4' => '4IEME DEMI JOURNEE', 'emarg5' => '5IEME DEMI JOURNEE', 'emarg6' => '6IEME DEMI JOURNEE');
    }

    $sql = "SELECT can.num_table, can.nom, can.prenoms, can.ddn, can.ldn, can.pdn, eta.etablissement, cent.etablissement as centre, spo.id as id_eps,
		can.sexe, ser.serie, rep.jury, lang.langue_vivante, ef1.epreuve_facultative_a, ef2.epreuve_facultative_b, spo.eps, can.atelier1, can.atelier2, can.atelier3
		FROM bg_repartition rep, bg_ref_region reg, bg_ref_prefecture pref, bg_ref_ville vil,
		bg_ref_etablissement eta, bg_ref_etablissement cent,
		bg_ref_inspection insp, bg_ref_serie ser, bg_ref_eps spo, bg_candidats can
		LEFT JOIN bg_ref_langue_vivante lang ON can.lv2=lang.id
		LEFT JOIN bg_ref_epreuve_facultative_a ef1 ON can.efa=ef1.id
		LEFT JOIN bg_ref_epreuve_facultative_b ef2 ON can.efb=ef2.id
		WHERE can.annee=$annee AND rep.annee=$annee
		AND can.num_table=rep.num_table
		AND rep.id_centre=cent.id
		AND can.etablissement=eta.id
		AND eta.id_ville=vil.id
		AND vil.id_prefecture=pref.id
		AND pref.id_region=reg.id
		AND eta.id_inspection=insp.id
		AND can.serie=ser.id AND can.eps=spo.id
		$andWhere
		ORDER BY $orderBy ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    $tab = array('num_table', 'nom', 'prenoms', 'ddn', 'ldn', 'pdn', 'etablissement', 'centre', 'serie', 'jury', 'langue_vivante', 'sexe','epreuve_facultative_a', 'epreuve_facultative_b', 'eps', 'atelier1', 'atelier2', 'atelier3', 'id_eps');

	/********************************* */
	$delimiter=","; 
	$filename="candidats-data_".$tache."_".$id_centre."_".date('Y-m-d').".csv";
	// Créer un pointeur de fichier
	$fp=fopen('php://memory','w');
	//  les en-têtes de colonne sont chargés de $cols;
	fputcsv($fp,$cols,$delimiter);
	/*************************************** */
	while($row=mysqli_fetch_array($result)){
		foreach($tab as $val)	$$val=$row[$val];
		if($sexe=='2') {
			$civ='M.';
			$sex='M';
			$e='';
		}else {
			$civ='Mlle';
			$sex='F';
			$e='e'; // pour mettre des mots au fminin si c'est une candidate
		}			
		$candidat="$nom $prenoms";
		$ddn=afficher_date($ddn);
		$dldn=$ddn.html_entity_decode(" à ").$ldn;
		if($pdn!=1) $dldn.='('.selectReferentiel($lien,'pays',$pdn).')';
		if($id_eps==1) $atelier='('.substr(selectReferentiel($lien,'atelier',$atelier1),0,1).substr(selectReferentiel($lien,'atelier',$atelier2),0,1).substr(selectReferentiel($lien,'atelier',$atelier3),0,1).')';
		else $atelier='';

		$eps.=' '.$atelier;
		$data[]=array('num'=>$num_table,'noms'=>utf8_decode($candidat),'dldn'=>utf8_decode($dldn),'sexe'=>$sex,'eta'=>$etablissement,'serie'=>$serie,'jr'=>$jury,'eps'=>$eps,'lv2'=>$langue_vivante,'efa'=>$epreuve_facultative_a,'efb'=>$epreuve_facultative_b);
		/********************************* */
		$lineData=array($num_table,utf8_decode($candidat),utf8_decode($dldn),$sex,$etablissement,$serie,$jury,$eps,$langue_vivante,
                    utf8_decode($epreuve_facultative_a),utf8_decode($epreuve_facultative_b));
		fputcsv($fp,$lineData,$delimiter);
		/********************************* */
	}
	
    $titre_tab = utf8_decode(html_entity_decode("<b>LISTE $andTitre || CENTRE : " . strtoupper($centre) . " </b>"));
    //$pdf->ezTable($data,$cols,$titre_tab,$options);

    /********************************* */
    //Déplacer au début du fichier
    fseek($fp, 0);
    //Configurer les écouteurs pour qu'ils téléchargent le fichier plutôt que de l'afficher
    $title = "liste_" . $tache . "_" . $centre;
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '";');

    //sortir toutes les données restantes sur le pointeur de fichier
    fpassthru($fp);
    /********************************* */
}
exit;
?>
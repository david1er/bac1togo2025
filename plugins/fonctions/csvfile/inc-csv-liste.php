<?php
include('../generales.php');
$lien=lien();

foreach($_REQUEST as $key=>$val) $$key=mysqli_real_escape_string ($lien, trim(addslashes($val))); 
if($annee=='') $annee=date('Y');


setlocale(LC_TIME, 'fr_FR','fr_BE.UTF8','fr_FR.UTF8');
$DateConvocPied="IMPRIME LE ".date('d').'/'.date('m').'/'.date('Y');

if($type==0){
	$options=array(
		   'spacing'=>1,
		   'justification'=>'full',
		   'titleFontSize'=> 12,
			'fontSize'=>8,
			'maxWidth'=>815,
			'cols'=>array(
			'dldn'=>array('justification'=>'left','width'=>150),
			)
		);
	//$cols=array('N°'=>utf8_decode('N°ENR'),'dos'=>utf8_decode('N°DOS'),'num_table'=>utf8_decode('N°TABLE.'),'noms'=>'CANDIDAT','dldn'=>'DATE ET LIEU DE NAISSANCE','pdn'=>'PAYS','nat'=>'NATIONALITE','serie'=>'SERIE','eps'=>'EPS','lv2'=>'LV2','efa'=>'EF. A','efb'=>'EF. B','tel'=>'TELEPHONES','bac1'=>'BAC I ','sig'=>'EMARGEMENT');
	$cols=array('N°'=>utf8_decode('N°ENR'),'num_table'=>utf8_decode('N°TABLE.'),'noms'=>'CANDIDAT','dldn'=>'DATE ET LIEU DE NAISSANCE','sex'=>'Sexe','pdn'=>'PAYS','nat'=>'NAT.','serie'=>'SERIE','eps'=>'EPS','lv2'=>'LV2','efa'=>'EF. A','efb'=>'EF. B','sig'=>'EMARGEMENT');
	$order=" ORDER BY ser.serie, can.nom, can.prenoms";
	$titre1='LISTE DES CANDIDATS INSCRITS';
}else {
	$options=array('spacing'=>1,'justification'=>'full','titleFontSize'=> 12,'fontSize'=>9,'maxWidth'=>815);
	$cols=array('N°'=>utf8_decode('N°ENR'),'noms'=>'CANDIDAT','dldn'=>'DATE ET LIEU DE NAISSANCE','serie'=>'SERIE','annee1'=>'ANNEE BAC I ','jury1'=>'JURY BAC I ','table1'=>utf8_decode('N°TABLE BAC I '),'obs'=>'OBSERVATIONS');
	$order=" ORDER BY can.annee_bac1, can.jury_bac1, can.num_table_bac1";
	$titre1='CONTROLE DU BAC I';
}

if(isset($_GET['id_etablissement'])) {
	
	if($id_serie!=0) {
		$andWhere=' AND can.serie='.$id_serie;
		$andTitre='- SERIE '.selectReferentiel($lien,'serie',$id_serie);
	}

	$sql= "SELECT can.id_candidat, can.nom, can.prenoms, ser.serie, sex.sexe, can.annee_bac1, can.jury_bac1, can.num_table_bac1, can.num_dossier, can.num_table, atelier1, atelier2, atelier3, "
			." can.ddn, can.ldn, pay.pays as pdn, nat.pays as nationalite, eta.etablissement, ser.id as id_serie, lv.langue_vivante as lv2, "
			." can.telephone, vil.ville, spo.eps, fac1.epreuve_facultative_a as efa, fac2.epreuve_facultative_b as efb, spo.id as id_eps "
			." FROM bg_ref_pays pay, bg_ref_pays nat, bg_ref_sexe sex, bg_ref_serie ser, bg_ref_etablissement eta, bg_ref_ville vil, "
			." bg_ref_eps spo, "
			." bg_candidats can "
			." LEFT JOIN bg_ref_langue_vivante lv ON lv.id=can.lv2 "
			." LEFT JOIN bg_ref_epreuve_facultative_a fac1 ON fac1.id=can.efa "
			." LEFT JOIN bg_ref_epreuve_facultative_b fac2 ON fac2.id=can.efb "
			." WHERE can.annee=$annee AND eta.id = can.etablissement AND can.serie=ser.id AND can.sexe=sex.id "
			." AND pay.id=can.pdn AND nat.id=can.nationalite AND can.etablissement=eta.id AND vil.id=eta.id_ville AND spo.id=can.eps "
			." AND eta.id =$id_etablissement "
			.$andWhere 
			.$order;
	$result=bg_query($lien,$sql,__FILE__,__LINE__);
	$colonnes=array('id_candidat','nom','prenoms','serie','sexe','annee_bac1','jury_bac1','num_table_bac1','ddn','ldn','pdn','nationalite','etablissement','id_serie','lv2','telephone','ville','eps','efa','efb','id_eps','num_dossier','num_table','atelier1','atelier2','atelier3');
	$fields=array('N°ENR','N°TABLE.','CANDIDAT','DATE ET LIEU DE NAISSANCE','Sexe','PAYS NAT.','SERIE','EPS','ATELIER','LV2','EF. A',' EF. B','EMARGEMENT');
	/********************************* */
	$delimiter=","; 
	$filename="liste-etablissement-data_".$id_etablissement.".csv";
	// Créer un pointeur de fichier
	$fp=fopen('php://memory','w');
	//  les en-têtes de colonne sont chargés de $colonnes;
	fputcsv($fp,$cols,$delimiter);
	/*************************************** */

	$i=0;
	while($row=mysqli_fetch_array($result)) {	
		/*foreach($colonnes as $col) $$col=utf8_decode(html_entity_decode(($row[$col])));*/
		foreach ($colonnes as $col) {
            $$col = $row[$col];
        }
		
		if($sexe=='M') {
			$civ='M.';
			$sex='M';
			$e='';
		}
		else {
			$civ='Mlle';
			$sex='F';
			$e='e'; // pour mettre des mots au fminin si c'est une candidate
		}
		if($annee_bac1==''){
		 	$annee_bac1='';}
		if($jury_bac1==0){
			$jury_bac1='';}
		if($num_table_bac1==''){
			$num_table_bac1='';}
			
		$ddn=afficher_date($ddn);
		$candidat="$nom $prenoms";
		//$eps = "$eps ".'('."$atelier".')'."";
		$dldn=$ddn.utf8_decode(html_entity_decode(" à ")).$ldn;
		$bac1="$annee_bac1 / J $jury_bac1 / $num_table_bac1";
			
		if($id_eps==1) $atelier=substr(selectReferentiel($lien,'atelier',$atelier1),0,1).substr(selectReferentiel($lien,'atelier',$atelier2),0,1).substr(selectReferentiel($lien,'atelier',$atelier3),0,1);
		else $atelier='';
		$i++;
		$data[$i]=array('N°'=>$id_candidat,'dos'=>$num_dossier,'num_table'=>$num_table,'noms'=>$candidat,'sex'=>$sexe,'dldn'=>$dldn,'pdn'=>$pdn,'nat'=>$nationalite,'serie'=>$serie,'eps'=>$eps.' '.$atelier,'lv2'=>$lv2,'efa'=>$efa,'efb'=>$efb,'sig'=>'','annee1'=>$annee_bac1,'jury1'=>$jury_bac1,'table1'=>$num_table_bac1);			
		/********************************* */
		$lineData=array($id_candidat,$num_table,$candidat,$dldn,$sexe,$pdn,$nationalite,$serie,$eps.' '.$atelier,$lv2,utf8_decode($efa),utf8_decode($efb),'',$annee_bac1,$jury_bac1,$num_table_bac1);
		fputcsv($fp,$lineData,$delimiter);
		/********************************* */
	}
	//$titre_tab=html_entity_decode("<b>ETABLISSEMENT: ".strtoupper($etablissement)." $andTitre</b>");
/********************************* */
	//Déplacer au début du fichier
	fseek($fp,0);
	//Configurer les écouteurs pour qu'ils téléchargent le fichier plutôt que de l'afficher
	header('Content-Type: text/csv');
	header('Content-Disposition: attachment; filename="'.$filename.'";'); 

	//sortir toutes les données restantes sur le pointeur de fichier
	fpassthru($fp);
	/********************************* */
}

exit;
?>

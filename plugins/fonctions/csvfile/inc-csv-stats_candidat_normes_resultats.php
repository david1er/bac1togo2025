<?php
include_once('../generales.php');
$lien=lien();

//$tBddConf=getBddConf();
//mysql_connect($tBddConf['host'], $tBddConf['user'], $tBddConf['pass']) or die('Connection impossible<br/>'.mysql_error());
//mysql_select_db($tBddConf['bdd']) or die('Base inaccessible<br/>'.mysql_error());
foreach($_REQUEST as $key=>$val) $$key=mysqli_real_escape_string ($lien, trim(addslashes($val))); 
if($annee=='') $annee=date('Y');
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
	$cols=array('nom'=>'nom_prenom',
                'annee'=>'annee',
                'sexe'=>'sexe',
                'serie'=>'serie_filiere',
                'type_session'=>'session',
                'jury'=>'jury',
                'num_table'=>'num_table',
                'examen'=>'examen',
                'centre_decrit'=>'centre_decrit',
                'ddn'=>'date_naissance',
                'ldn'=>'lieu_naiss',
                'telephone'=>'telephone',
                'region_division'=>'region',
                'type_enseignement'=>'type_enseignement',
                'decision'=>'decision',
                'mention'=>'mention',
                'moyenne'=>'moyenne',
                'delib1'=>'config',
                'etablissement'=>'ets_provenance');
	
}else {
	$options=array('spacing'=>1,'justification'=>'full','titleFontSize'=> 12,'fontSize'=>9,'maxWidth'=>815);
	$cols=array('nom'=>'nom',
                'prenoms'=>'prenoms',
                'sexe'=>'sexe',
                'serie'=>'serie',
                'jury'=>'jury',
                'id_candidat'=>'id_candidat',
                'num_table'=>'num_table',
                'ddn'=>'ddn',
                'ldn'=>'ldn',
                'telephone'=>'telephone',
                'delib1'=>'nom_photo',
                'etablissement'=>'etablissement');
	$titre1='CONTROLE DU BAC I';
}

$DateConvocPied="IMPRIME LE ".date('d').'/'.date('m').'/'.date('Y');
$sql="SELECT nom, prenoms,can.annee,can.sexe,ser.serie,rep.jury, can.id_candidat,type_sess.type_session, can.num_table, ddn, ldn,can.telephone, can.nom_photo,reg.region_division,can.etablissement
        FROM bg_candidats can,
             bg_ref_serie ser,
             bg_repartition rep,
             bg_ref_type_session type_sess,
             bg_ref_region_division reg,
             bg_ref_etablissement eta
        WHERE can.annee=2024 
        AND can.serie=ser.id 
        AND can.num_table=rep.num_table 
        /*AND res.id_anonyme=rep.id_anonyme*/
        AND can.type_session=type_sess.id
        AND eta.id_region_division = reg.id
        AND can.etablissement=eta.id";
	$result=bg_query($lien,$sql,__FILE__,__LINE__);
	$colonnes=array('nom',
                    'prenoms',
                    'sexe',
                    'serie',
                    'type_session',
                    'jury',
                    'id_candidat',
                    'num_table',
                    'ddn',
                    'ldn',
                    'telephone',
                    'region_division',
                    'delib1',
                    'etablissement');
	$fields=array('nom',
                    'prenoms',
                    'sexe',
                    'serie',
                    'jury',
                    'id_candidat',
                    'num_table',
                    'ddn',
                    'ldn',
                    'telephone',
                    'delib1',
                    'etablissement');
	/********************************* */
	$delimiter=","; 
	$filename="liste_candidats_normes_resultats.csv";
	// Créer un pointeur de fichier
	$fp=fopen('php://memory','w');
	//  les en-têtes de colonne sont chargés de $colonnes;
	fputcsv($fp,$cols,$delimiter);
	/*************************************** */
	$i=0;
	while($row=mysqli_fetch_array($result)) {	
		foreach($colonnes as $col) $$col=utf8_decode(html_entity_decode(($row[$col])));
        $etablissementRef = selectReferentiel($lien, 'etablissement', $etablissement);
		$centreEts = centreEts($lien, $etablissement);
        if ($row['sexe'] == '1') {
            $sexe = 'F';
        } else {
            $sexe = 'M';
        }
		$ddn=afficher_date($ddn);
		$candidat="$nom $prenoms";
		$dldn=$ddn.utf8_decode(html_entity_decode(" à ")).$ldn;
		$bac1="$annee_bac1 / J $jury_bac1 / $num_table_bac1";
		$i++;
		$data[$i]=array('nom_prenom'=>$nom. ' ' .$prenoms,
                        'annee'=>$annee,
                        'sexe'=>$sexe,
                        'serie_filiere'=>$serie,
                        'type_session'=>$type_session,
                        'jury'=>$jury,
                        'id_candidat'=>$id_candidat,
                        'num_table'=>$num_table,
                        'date_naissance'=>$ddn,
                        'lieu_naiss'=>$ldn,
                        'telephone'=>$telephone,
                        'region_division'=>$region_division,
                        'decision'=>$delib1,
                        'etablissement'=>$etablissementRef
                    );			
		/********************************* */
		$lineData=array($nom.' '.$prenoms,
        $annee,
        $sexe,
        $serie,
        $type_session,
        $jury,
        $num_table,
        'BAC1',
        $centreEts,
        $ddn,
        utf8_decode($ldn),
        $telephone,
        $region_division,
        'GENERAL',
        $delib1,
        $delib1,
        $delib1,
        $delib1,
        $etablissementRef);
		fputcsv($fp,$lineData,$delimiter);
		/********************************* */
	}
/********************************* */
	//Déplacer au début du fichier
	fseek($fp,0);
	//Configurer les écouteurs pour qu'ils téléchargent le fichier plutôt que de l'afficher
	header('Content-Type: text/csv');
	header('Content-Disposition: attachment; filename="'.$filename.'";'); 

	//sortir toutes les données restantes sur le pointeur de fichier
	fpassthru($fp);
	/********************************* */


exit;
?>
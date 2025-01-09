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
	$cols=array('examen'=>'examen',
                'num_table'=>'num_table',
                'jury'=>'jury',
                'libelle'=>'libelle',
                'etablissement'=>'etablissement');
	
}else {
	$options=array('spacing'=>1,'justification'=>'full','titleFontSize'=> 12,'fontSize'=>9,'maxWidth'=>815);
	$cols=array('examen'=>'examen',
                'num_table'=>'num_table',
                'jury'=>'jury',
                'libelle'=>'libelle',
                'etablissement'=>'etablissement');
	$titre1='CONTROLE DU BAC I';
}

$DateConvocPied="IMPRIME LE ".date('d').'/'.date('m').'/'.date('Y');
$sql="SELECT nom, prenoms,can.sexe,ser.serie,res.jury,can.id_candidat, can.num_table, ddn, ldn,can.telephone, res.delib1,eta.etablissement
        FROM bg_candidats can, bg_ref_serie ser, bg_resultats res,bg_repartition rep,bg_ref_etablissement eta
        WHERE can.annee=res.annee 
        AND can.serie=ser.id 
        AND can.num_table=rep.num_table 
        AND res.id_anonyme=rep.id_anonyme 
        AND can.etablissement=eta.id";
	$result=bg_query($lien,$sql,__FILE__,__LINE__);
	$colonnes=array('nom',
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
	$filename="plateforme_externe_sms.csv";
	// Créer un pointeur de fichier
	$fp=fopen('php://memory','w');
	//  les en-têtes de colonne sont chargés de $colonnes;
	fputcsv($fp,$cols,$delimiter);
	/*************************************** */
	$i=0;
	while($row=mysqli_fetch_array($result)) {	
		foreach($colonnes as $col) $$col=utf8_decode(html_entity_decode(($row[$col])));
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
		$data[$i]=array('examen'=>'PROBA',
                        'num_table'=>$num_table,
                        'jury'=>$jury,
		                'nom'=>$nom,
                        'prenoms'=>$prenoms,
                        'sexe'=>$sexe,
                        'serie'=>$serie,
                        'ddn'=>$ddn,
                        'ldn'=>$ldn,
                        'id_candidat'=>$id_candidat,
                        'delib1'=>$delib1,
                        'etablissement'=>$etablissement
                    );			
		/********************************* */
		$lineData=array('PROBA',$num_table,$jury,$nom.' '.$prenoms.utf8_decode(html_entity_decode('- Né(e) le ')).$ddn. utf8_decode(html_entity_decode(' à ')).$ldn.' Num Table - '.$num_table.' -Jr '.$jury.'/'.$serie.' '.$delib1,$etablissement);
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
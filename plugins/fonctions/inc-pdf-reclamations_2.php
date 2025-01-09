<?php
session_start();
include_once('generales.php');
$lien=lien();

foreach($_REQUEST as $key=>$val) $$key=mysqli_real_escape_string ($lien, trim(addslashes($val))); 
if($annee=='') $annee=date('Y');


$pdf_dir='../ezpdf/';
include $pdf_dir.'class.ezpdf.php';	// inclusion du code de la bibliothque
$pdf = new Cezpdf('A4','portrait'); // 595.28 x 841.89
$pdf->selectFont($pdf_dir.'fonts/Helvetica.afm');
$options=array('b'=>'Helvetica-Bold.afm');
$family='Helvetica';
$pdf->setFontFamily($family,$options);
$pdf->setStrokeColor(0,0,0);
$pdf->setLineStyle(1,'round','round');
$width=$pdf->ez['pageWidth'];
$height=$pdf->ez['pageHeight'];
setlocale(LC_TIME, 'fr_FR','fr_BE.UTF8','fr_FR.UTF8');
$sDateConvoc=utf8_decode(strftime("%A %d %B %Y", mktime(0, 0, 0, date('m'), date('d'), date('Y'))));
$DateConvocPied="IMPRIME LE ".date('d').'/'.date('m').'/'.date('Y');
			
if($num_table>0){
	$texte="RELEVES DE NOTES DES CANDIDATS ";
	$entete=array('matiere'=>"MATIERES", 'note'=>"NOTE\nSUR 20", 'coeff_mat'=>"COEF.", 'points_mat'=>"NOUVELLE NOTE\nRELEVEE SUR 20");
$options=array(
		'cols'=>array(
			'matiere'=>array('justification'=>'left','width'=>300),
			'note'=>array('justification'=>'center','width'=>50),
			'coeff_mat'=>array('justification'=>'center','width'=>45),
			'points_mat'=>array('justification'=>'center','width'=>100)
		),
		'xPos'=>round($width/2+5,0),
		'titleFontSize'=> 10,
		'fontSize'=>10
	);

$options1=array(
       'spacing'               =>1,
       'justification'=>'full'
  );
$options2=array(
       'spacing'               =>1.5,
       'justification'=>'center'
  );
$options3=array(
       'spacing'               =>1.5,
       'justification'=>'full'
  );
$options4=array(
       'spacing'               =>1.5,
       'justification'=>'right'
  );

//$pdf->ezText(utf8_decode("\n<b>$titre</b>\n"),13,array('justification'=>'center'));
			
	$sql="SELECT can.nom, can.prenoms, can.sexe, can.num_table, ser.serie, ser.intitule, can.ddn, can.ldn, can.nom_photo, pay.pays, cen.etablissement, rep.jury, res.moyenne, delib1, delib2, res.coeff as coeff_total, 
			mat.matiere, mat.id as id_matiere, notes.note, notes.coeff as coeff_mat, notes.id_type_note, types.type_note, can.lv2, can.efa, can.efb   
			FROM bg_candidats can, bg_repartition rep, bg_ref_serie ser, bg_ref_pays pay, bg_ref_etablissement cen, bg_resultats res, bg_notes notes, bg_ref_type_note types, bg_ref_matiere mat  
			WHERE can.annee=$annee AND rep.annee=$annee AND res.annee=$annee AND notes.annee=$annee
			AND can.num_table=rep.num_table AND can.num_table=res.num_table AND can.serie=ser.id AND can.pdn=pay.id 
			AND notes.num_table=can.num_table AND notes.id_type_note=types.id AND mat.id=notes.id_matiere
			AND cen.id=rep.id_centre AND can.num_table='$num_table'
			ORDER BY ser.serie, can.num_table, can.nom, can.prenoms, types.id, mat.matiere ";
	$result=bg_query($lien,$sql,__FILE__,__LINE__);
	$tab=array('nom','prenoms','sexe','num_table','serie','intitule','ddn','ldn','nom_photo','pays','etablissement','jury','moyenne','delib1','delib2','coeff_total','note','matiere','id_matiere','coeff_mat','id_type_note','type_note','lv2','efa','efb');
	while($row=mysqli_fetch_array($result)){
		foreach($tab as $val) $$val=html_entity_decode(utf8_decode($row[$val]));
		if($note=='') $note=0;			
		$tNum[$num_table]=$num_table;
		$tTypes[$num_table][$id_type_note]=$id_type_note;
		$tCandidats[$num_table]['serie']=$serie;
		$tCandidats[$num_table]['sexe']=$sexe;
		$tCandidats[$num_table]['intitule']=$intitule;
		$tCandidats[$num_table]['lv2']=$lv2;
		$tCandidats[$num_table]['efa']=$efa;
		$tCandidats[$num_table]['efb']=$efb;
		$tCandidats[$num_table]['jury']=$jury;
		$tCandidats[$num_table]['centre']=$etablissement;
		$tCandidats[$num_table]['moyenne']=$moyenne;
		$tCandidats[$num_table]['delib1']=$delib1;
		$tCandidats[$num_table]['delib2']=$delib2;
		$tCandidats[$num_table]['coeff_total']=$coeff_total;
		$tCandidats[$num_table]['dldn']=afficher_date($ddn).' à '.utf8_encode($ldn." ($pays)");
		$tMatieres[$num_table][$id_type_note][$id_matiere]=$matiere;
		$tNotes[$num_table][$id_type_note][$id_matiere]['note']=$note;
		$tNotes[$num_table][$id_type_note][$id_matiere]['coeff_mat']=$coeff_mat;	
		$tCandidats[$num_table]['nom']=utf8_encode($nom);
		$tCandidats[$num_table]['prenoms']=utf8_encode($prenoms);
	
		$i++;
	}
		
	foreach($tNum as $num_table){		
		$data=array();
		$serie=$tCandidats[$num_table]['serie'];
		$intitule=utf8_encode($tCandidats[$num_table]['intitule']);
		$dldn=$tCandidats[$num_table]['dldn'];
		$jury=$tCandidats[$num_table]['jury'];
		$sexe=$tCandidats[$num_table]['sexe'];
		$delib1=$tCandidats[$num_table]['delib1'];
		$delib2=$tCandidats[$num_table]['delib2'];
		$centre=$tCandidats[$num_table]['centre'];
		$efa=$tCandidats[$num_table]['efa'];
		$efb=$tCandidats[$num_table]['efb'];
		$lv2=$tCandidats[$num_table]['lv2'];
		$nom=$tCandidats[$num_table]['nom'];
		$prenoms=$tCandidats[$num_table]['prenoms'];
		if($sexe==1) $e='e'; else $e='';
	
//		if($iPage>0)	{$iPage=$pdf->newPage();}
//		else $iPage=1;
//		$pdf->addDestination($iPage,'FitH');
//		$sommaire.="<c:ilink:".$iPage.">".$num_table." - $nom $prenoms - $serie </c:ilink>\n";

		$pdf->ezSetY($height-25);
		$pdf->ezSetY($height-102);
		$pdf->ezText(utf8_decode("\n<u><b>RELEVE DE NOTES</b></u>"),13,array('justification'=>'center'));
		$pdf->ezText(utf8_decode("<b>\nBACCALAUREAT PREMIERE PARTIE (BAC 1)</b>"),11,array('justification'=>'center'));
		$mois=moisSession($lien,$annee,$jury);	
		$pdf->ezText(utf8_decode("<b>Session de $mois $annee</b>"),10,array('justification'=>'center'));			
		$pdf->ezText(utf8_decode("<b>SERIE $serie, $intitule</b>"),10,array('justification'=>'center'));			
			
		$pdf->ezText(utf8_decode("Nom et Prénoms: <b>$nom $prenoms</b>"),11,$options3);			
		$pdf->ezText(utf8_decode("Date et lieu de naissance: <b>$dldn</b>"),11,$options3);			
		$pdf->ezText(utf8_decode("Jury: <b>$jury</b>          N° de table: <b>$num_table</b>"),11,$options3);	
		$tabPoints[4]=0;	
		foreach(array(4,1,3) as $id_type_note){
			if(in_array($id_type_note,$tTypes[$num_table])){
				$i=0;
//				$data=array();
				$total_coeff=$total_points=$total_sur=0;
					
				foreach($tMatieres[$num_table][$id_type_note] as $id_matiere=>$matiere){
					if($id_type_note==4){
						$data[$id_type_note][$i]['matiere']=strtoupper($matiere).'**';
					}else{
						if($id_matiere==51) {$matiere=selectReferentiel($lien,'langue_vivante',$lv2); $data[$id_type_note][$i]['matiere']=strtoupper('LANGUE VIVANTE 2 ('.$matiere.')');}
						else $data[$id_type_note][$i]['matiere']=strtoupper($matiere);
					}
					if($tNotes[$num_table][$id_type_note][$id_matiere]['note']>=0){
						$data[$id_type_note][$i]['note']=$tNotes[$num_table][$id_type_note][$id_matiere]['note'];
					}else{
						$data[$id_type_note][$i]['note']='Absent';
					}
					if($id_type_note==4) {
//						$data[$i]['note']='-';
						$data[$id_type_note][$i]['coeff_mat']='-';
						$data[$id_type_note][$i]['sur']='-';
						if($tNotes[$num_table][$id_type_note][$id_matiere]['note']>10){
							$data[$id_type_note][$i]['points_mat']='';
//							$data[$id_type_note][$i]['points_mat']=$tNotes[$num_table][$id_type_note][$id_matiere]['note']-10;
							$total_points+=$tNotes[$num_table][$id_type_note][$id_matiere]['note']-10;
						}else{
//							$data[$id_type_note][$i]['points_mat']=0;
							$data[$id_type_note][$i]['points_mat']=' ';
						}
					}else{
						$data[$id_type_note][$i]['coeff_mat']=$tNotes[$num_table][$id_type_note][$id_matiere]['coeff_mat'];
						$data[$id_type_note][$i]['sur']=20*$tNotes[$num_table][$id_type_note][$id_matiere]['coeff_mat'];
						if($tNotes[$num_table][$id_type_note][$id_matiere]['note']>=0){
//							$data[$id_type_note][$i]['points_mat']=$tNotes[$num_table][$id_type_note][$id_matiere]['note']*$tNotes[$num_table][$id_type_note][$id_matiere]['coeff_mat'];
							$data[$id_type_note][$i]['points_mat']=' ';
						}else{
//							$data[$id_type_note][$i]['points_mat']='Absent';
							$data[$id_type_note][$i]['points_mat']=' ';
						}
						$total_points+=$tNotes[$num_table][$id_type_note][$id_matiere]['note']*$tNotes[$num_table][$id_type_note][$id_matiere]['coeff_mat'];
					}
					$total_coeff+=$tNotes[$num_table][$id_type_note][$id_matiere]['coeff_mat'];
					$total_sur+=20*$tNotes[$num_table][$id_type_note][$id_matiere]['coeff_mat'];
					$i++;
				}
				$tabPoints[$id_type_note]=$total_points;
				if($id_type_note==3) {
//					$data[$id_type_note][$i]['matiere']='TOTAL';			
//					$data[$id_type_note][$i]['points_mat']=$total_points;
					$data[$id_type_note][$i]['points_mat']=' ';
					$data[$id_type_note][$i]['sur']=$total_sur;
					$data[$id_type_note][$i]['coeff_mat']=$total_coeff;
				}
				if($id_type_note!=3) {
//					$dataTotal[$id_type_note][$i]['matiere']='TOTAL';
//					$dataTotal[$id_type_note][$i]['points_mat']=$total_points+$tabPoints[4];
					$dataTotal[$id_type_note][$i]['points_mat']=' ';
					$dataTotal[$id_type_note][$i]['sur']=$total_sur;
					$dataTotal[$id_type_note][$i]['coeff_mat']=$total_coeff;
				}

				if($id_type_note==1)	{$total_points_ecrits=$total_points; $coeff_ecrit=$total_coeff;}
				
				if($id_type_note==4) $moyenne=($total_points+$total_points_ecrits)/$coeff_ecrit;
				else $moyenne=$total_points/$total_coeff;
				if($moyenne<10) $long=4; else $long=5;
				
				$moy[$id_type_note]=str_replace('.',',',substr($moyenne,0,$long));

			}	
		}

		if(is_array($data[4])){			
			$data2=array_merge($data[1],$data[4],$dataTotal[1]);
		}else {
			$data2=array_merge($data[1],$dataTotal[1]);		
		}
		$titre_tableau="EPREUVES ECRITES (A)";
		$pdf->ezTable($data2,$entete,$titre_tableau,$options);

/*		$pdf->ezText(utf8_decode("\n§ : Note éliminatoire (NE) - ** : Matière facultative"),8,array('justification'=>'left'));
		$moyenne_generale=str_replace('.',',',substr($tCandidats[$num_table]['moyenne'],0,-1));
		$pdf->ezText(utf8_decode("Moyenne Global (A): <b>$moyenne_generale/20</b>"),12,array('justification'=>'center'));
*/		$pdf->ezSetDy(-10);

		if(is_array($data[3])){			
			$titre_tableau="EPREUVES ORALES DE CONTROLE (B)";
			$pdf->ezTable($data[3],$entete,$titre_tableau,$options);
//			$pdf->ezText(utf8_decode("Moyenne (B): <b>$moy[3]/20</b>"),12,array('justification'=>'center'));
		}		

		if($type_releve<=2) $delib=$delib1; else $delib=$delib2;
		if($delib2=='Abien') $delib='Passable';
					
		if(in_array($delib,array('Refuse','Absent','Ajourne'))) $delib=strtoupper($delib).strtoupper($e);
		elseif($delib=='Abandon') $delib=strtoupper($delib);
			
		if(in_array($delib,array('Abien','TBien'))) $mention=strtoupper(str_replace('abien','ASSEZ - BIEN',str_replace('tbien','TRES - BIEN',strtolower($delib))));
		else $mention=$delib;
		if(in_array($delib,array('Passable','Abien','Bien','TBien'))) $texteMention=strtoupper("Admis$e avec la mention \n$mention");
		else $texteMention=strtoupper($delib);			

		$pdf->ezText(utf8_decode("\n\nLE SUPERVISEUR\n\n\n<b></b>"),11,$options2);			

//		$pdf->ezSetDy(-5);
/*
		$pdf->ezColumnsStart(array('num'=>2,'gap'=>10));
		if($president_jury=='') {	$president_jury=presidentJurys($lien,$annee,$jury);}
		$pdf->ezText(utf8_decode("DECISION DU JURY\n<b>$texteMention</b>\nFait à LOME, le $date_delib"),11,$options2);
		$pdf->ezNewPage();
		$pdf->ezText(utf8_decode("Signature du Président du Jury\n\n\n<b>$president_jury</b>"),11,$options2);			
		$pdf->ezColumnsStop();

		$pdf->ezSetY(40);
		$pdf->ezText(html_entity_decode("Centre d'examen: <b>$centre </b>"),8,array('justification'=>'centre'));			
*/
	}
}

$pdfG="liste_jury".$jury.'_serie'.$serie;

$pdf->ezInsertMode(1,1,'before');
//$pdf->ezText($titre,11);
//$pdf->ezText($sommaire,8);
$pdf->ezInsertMode(0);


$title="Listes de candidats jury".$jury.'_serie'.$serie.'_annee'.$annee;
$infos=array('Title'=>$title,'Author'=>'CNDP-TICE','CreationDate'=>date("d/m/Y"));
$pdf->addInfo($infos);

// do the output, this is my standard testing output code, adding ?d=1
// to the url puts the pdf code to the screen in raw form, good for
// checking
// for parse errors before you actually try to generate the pdf file.
if(isset($_REQUEST['d'])) $d=$_REQUEST['d'];
if (isset($d) && $d){
   $pdfcode = $pdf->output(1);
   $pdfcode = str_replace("\n","\n<br>",htmlspecialchars($pdfcode));
   echo '<html><body>';
   echo trim($pdfcode);
   echo '</body></html>';
} else {
   //$pdf->ezStream();

   $pdfcode = $pdf->ezOutput();
   $pdf_fic="pdf/".$annee."_$pdfG.pdf";
   $fp=fopen($pdf_fic,'wb');
   fwrite($fp,$pdfcode);
   fclose($fp);

	echo "<A HREF='$pdf_fic'>Cliquez ici pour lire le fichier $pdf_fic</a>\n";
	echo "<script>document.location='$pdf_fic'</script>";  
	
	session_destroy();
}
?>

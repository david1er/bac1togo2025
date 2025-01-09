<?php
if (!defined('_ECRIRE_INC_VERSION')) return;
include_spip('inc/presentation'); // alleger les inclusions avec un inc/presentation_mini
include('../plugins/fonctions/generales.php');

setlocale(LC_TIME, "fr_FR");

function exec_tables(){
	$exec = _request('exec');
	$titre = "exec_$exec";
	$commencer_page = charger_fonction('commencer_page','inc');
	echo $commencer_page($titre);
	$lien=lien();
	$statut=getStatutUtilisateur();

	foreach($_REQUEST as $key=>$val) $$key=mysqli_real_escape_string ($lien, trim(addslashes($val))); 
	if($annee=='') $annee=date("Y");
	$tab_auteur=$GLOBALS["auteur_session"];
	$login=$tab_auteur['login'];	
	
	if(isAutorise(array('Admin'))){
		echo debut_grand_cadre(); 
		echo debut_gauche();
		echo debut_cadre_enfonce();

		echo fin_cadre_enfonce(); 

		echo debut_droite();

	/*
		echo "<pre>";
		print_r($_POST);
		echo "</pre>";
	*/
		if(!isset($_POST['suivant']) && !isset($_POST['valider'])){
			echo debut_cadre_couleur('','','','Connexion à la base de données');
			$form1="<form name='connexion' method='POST' action='' ><table>";
			$form1.="<tr><td>URL de la base à copier:</td><td><input type='text' name='base' id='base'></td></tr>";
			$form1.="<tr><td>Utilisateur de la base de données</td><td><input type='text' name='user' id='user'></td></tr>";
			$form1.="<tr><td>Mot de passe d'accès à la base de données:</td><td><input type='password' name='mdp' id='mdp'></td></tr>";
			$form1.="<tr><td>Nom de la base</td><td><input type='text' name='nom_base' id='nom_base'></td></tr>";
			$form1.="<tr><td>Nom de la table</td><td><input type='text' name='nom_table' id='nom_table'></td></tr>";
			$form1.="<tr><td colspan=2><center><input type='submit' name='suivant' value='SUIVANT' /></center></tr>";
			$form1.="</table></form>";
			echo $form1;
			echo fin_cadre_couleur();
		}
		
		if(isset($_POST['suivant']) || isset($_POST['valider'])){
			echo debut_cadre_couleur('','','','Chargement des données');
			$connexion=mysqli_connect("$base","$user","$mdp","$nom_base") or die('Connexion impossible');
			$_SESSION['connexion']=$connexion;
			$form2="<form name='form_parametres' method='POST' action='' ><table>";
			$sql="SELECT jury FROM bg_repartition WHERE annee=$annee GROUP BY jury ORDER BY jury";
			$result=bg_query($lien,$sql,__FILE__,__LINE__);
			while($row=mysqli_fetch_array($result)){
				$options.="<option value='".$row['jury']."'>".$row['jury']."</option>";
			}
			$form2.="<tr><td>Jury</td><td><select name='jury'><option value='0'>-=[Jury]=-</option>$options</select></td></tr>";
			$form2.="<tr><td>Matière</td><td><select name='id_matiere'>".optionsReferentiel($lien,'matiere')."</select></td></tr>";
			$form2.="<tr><td>Type</td><td><select name='id_type_note'>".optionsReferentiel($lien,'type_note')."</select></td></tr>";
			$form2.="<tr><td colspan=2><center>
					<input type='submit' name='valider' value='VALIDER' />
					<input type='hidden' name='base' value='$base' />
					<input type='hidden' name='user' value='$user' />
					<input type='hidden' name='mdp' value='$mdp' />
					<input type='hidden' name='nom_base' value='$nom_base' />
					<input type='hidden' name='nom_table' value='$nom_table' />
					</center></tr>"; 
			$form2.="</table></form>";
			echo $form2;
			echo fin_cadre_couleur();
		}
		
		if(isset($_POST['valider'])){

			//$connexion=$_SESSION['connexion'];
			$connexion=mysqli_connect("$base","$user","$mdp","$nom_base") or die('Connexion impossible');
			$sql2="SELECT * 
				FROM $nom_table 
				WHERE annee=$annee AND jury=$jury AND id_type_note='$id_type_note' AND id_matiere='$id_matiere' AND note IS NOT NULL";
			$result2=bg_query($connexion,$sql2,__FILE__,__LINE__);
			$tab=array('num_table','id_anonyme','ano','id_type_session','annee','id_matiere','id_type_note','id_serie','note', 'coeff','jury','maj','login');
			while($row2=mysqli_fetch_array($result2)){
				foreach($tab as $val) $$val=$row2[$val];
				$sql3="REPLACE INTO bg_notes (num_table, id_anonyme, ano, id_type_session, annee, id_matiere, id_type_note, id_serie, note, coeff, jury, maj, login)
					VALUES ('$num_table','$id_anonyme','$ano','$id_type_session','$annee','$id_matiere','$id_type_note','$id_serie','$note','$coeff','$jury','$maj','$login')";
				bg_query($lien,$sql3,__FILE__,__LINE__);
			}
		}	
	}			
		echo fin_cadre_trait_couleur();
		echo fin_grand_cadre(), fin_gauche(), fin_page();	
}

?>

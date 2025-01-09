<?php
if (!defined('_ECRIRE_INC_VERSION')) {
    return;
}

include_spip('inc/presentation'); // alleger les inclusions avec un inc/presentation_mini
include '../plugins/fonctions/generales.php';

setlocale(LC_TIME, "fr_FR");

function exec_confidentiel()
{
    $exec = _request('exec');
    $titre = "exec_$exec";
    $commencer_page = charger_fonction('commencer_page', 'inc');
    echo $commencer_page($titre);
    $lien = lien();
    $statut = getStatutUtilisateur();

    foreach ($_REQUEST as $key => $val) {
        $$key = mysqli_real_escape_string($lien, trim(addslashes($val)));
    }
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
        $annee = recupAnnee($lien); //$annee = date("Y");
    }
    if ($id_type_session == '') {
        $id_type_session = recupSession($lien);
    }

//    $annee=2018;
    $tab_auteur = $GLOBALS["auteur_session"];
    $login = $tab_auteur['login'];

    if (isAutorise(array('Admin', 'Informaticien'))) {
        echo debut_grand_cadre();
        echo debut_gauche();
//        echo debut_boite_info(), "<b>Anonymat</b><br/>Choisir l'année, la session et le pas, puis cliquer sur la création des numéros anonymes", fin_boite_info();
        echo debut_boite_info(), "<b>Impression des anonymats</b><br/>Choisir l'année et la session. <br/>Cliquer sur l'impression des numéros anonymes. <br/>Choisir le centre puis imprimer", fin_boite_info();
//        echo debut_boite_info(), "<b>Préparation des notes</b><br/>Choisir l'année et la session. <br/>Cliquer sur préparation pour la saisie des notes.", fin_boite_info();
        echo debut_boite_info(), "<b>Délibération</b><br/>Choisir l'année et la session. <br/>Cliquer sur gestion de la délibération. <br/>Cocher les jurys dont l'anonymat doit être levé puis valider", fin_boite_info();

        /*
        echo "<pre>";
        print_r($_POST);
        echo "</pre>";
         */

        if (isset($_POST['anonymer'])) {
            $param = selectCodeAno($lien, $annee, $id_type_session);

            foreach ($_POST['tJurys'] as $jury) {
                 $tSql[] = "UPDATE bg_notes notes, bg_resultats res, bg_repartition rep SET notes.num_table=rep.num_table, res.num_table=rep.num_table \n
                WHERE notes.annee=$annee AND res.annee=$annee AND rep.annee=$annee
                AND rep.id_anonyme=notes.id_anonyme AND notes.id_anonyme=res.id_anonyme AND
                notes.jury=$jury AND res.jury=$jury AND rep.jury=$jury "; 

               // $tSql[] = "UPDATE bg_notes SET num_table = nt WHERE annee = $annee AND jury = $jury";
                $tSql[] = "UPDATE bg_resultats SET num_table = nt WHERE annee = $annee AND jury = $jury";

                //$tSql[]="DELETE FROM bg_deliberation WHERE annee=$annee AND jury=$jury ";
                $tSql[] = "DELETE FROM bg_notes WHERE annee=$annee AND jury=$jury AND note IS NULL AND id_type_note=4 ";
                $tSql[] = "UPDATE bg_notes SET note=0 WHERE annee=$annee AND jury=$jury AND note IS NULL AND id_type_note=1 ";

                $tSql[] = "UPDATE bg_codes SET delib=1 WHERE   jury=$jury ";

/*
$tSql[]="UPDATE bg_notes notes, bg_repartition rep SET notes.num_table=rep.num_table \n
WHERE notes.annee=$annee AND rep.annee=$annee AND rep.id_anonyme=notes.id_anonyme AND notes.jury=$jury AND rep.jury=$jury ";

$tSql[]="UPDATE bg_resultats res, bg_repartition rep SET res.num_table=rep.num_table \n
WHERE res.annee=$annee AND rep.annee=$annee AND rep.id_anonyme=res.id_anonyme AND res.jury=$jury AND rep.jury=$jury ";
 */
                $tSql[] = "REPLACE bg_deliberation (annee,jury) VALUES ($annee,$jury)";
            }
            foreach ($tSql as $sql) {
                bg_query($lien, $sql, __FILE__, __LINE__);
                // echo $sql;
            }
            echo debut_boite_info(), "Opération éffectuée avec succes", fin_boite_alerte();
            //alert('Opération effectuée avec succès');
        }
        echo debut_droite();

        if (isset($_POST['notesEpsValidees'])) {
            $param = selectCodeAno($lien, $annee, $id_type_session);
            foreach ($_POST['tJurys'] as $jury) {
                $tSql[] = "UPDATE bg_notes notes, bg_notes_eps eps, bg_repartition rep SET notes.note=(eps.note),notes.login='impt" . $login . "'
					WHERE notes.annee=$annee AND eps.annee=$annee AND rep.annee=$annee AND rep.id_anonyme=notes.id_anonyme
					AND rep.num_table=eps.num_table AND eps.note>=0 AND notes.id_matiere=42 AND notes.id_type_note=1
                    AND rep.jury=$jury AND notes.jury=$jury";
            }
            foreach ($tSql as $sql) {
                bg_query($lien, $sql, __FILE__, __LINE__);
            }

            echo debut_cadre_enfonce(), "Opération exécutée avec succès", fin_cadre_enfonce();
        }

        foreach ($_REQUEST['tache'] as $cle => $tval) {;}
        if ($cle != 'levee_anonymat' && $cle != 'impression_numeros' && $cle != 'maj_sport') {
            $controle_js = "onclick=\"if(confirm('Aviez-vous inséré le BON MOT DE PASSE?')) return true; else return false;\"";
            echo debut_cadre_trait_couleur('', '', '', "GESTION DES TACHES CONFIDENTIELLES", "", "", false);
            $liste = "<form action='" . generer_url_ecrire('confidentiel') . "' method='POST' class='forml spip_xx-small' name='form_taches'>";
            $liste .= "<table>";
            $liste .= "<tr><td><select name='annee_session'>" . optionsAnnee(2020, $annee) . "</select></td><td colspan=2><select name='id_type_session'>" . optionsReferentiel($lien, 'type_session', 1) . "</select></td></tr>";
/*                    $liste.="<tr><td><input type='image' src='../plugins/images/logo.png' width='75px' name='tache[creation_numeros]' value='creation_numeros' $controle_js /></td>
<td>PAS  <select name='pas'>";
for($k=1;$k<11;$k++){$liste.="<option value='$k'>$k</option>";}
$liste.="</select></td>
<td>Création des numéros anonymes</td></tr>";
 */
            $liste .= "<tr><td colspan=2><input type='image' src='../plugins/images/imprimante.jpg' width='75px' name='tache[impression_numeros]' value='impression_numeros' $controle_js /></td><td>Impression des numéros anonymes</td></tr>";
//                    $liste.="<tr><td colspan=2><input type='image' src='../plugins/images/liste1.jpg' width='75px' name='tache[preparation_notes]' value='preparation_notes' $controle_js /></td><td>Préparation pour la saisie des notes</td></tr>";
            $liste .= "<tr><td colspan=2><input type='image' src='../plugins/images/sport1.png' width='75px' name='tache[maj_sport]' value='maj_sport' $controle_js /></td><td>Notes de l'épreuve d'EPS avant délibération</td></tr>";
            $liste .= "<tr><td colspan=2><input type='image' src='../plugins/images/logo.png' width='75px' name='tache[levee_anonymat]' value='levee_anonymat' $controle_js /></td><td>Gestion de la délibération</td></tr>";

            $liste .= "</table></form>";
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        if (isset($_REQUEST['tache'])) {
            $sql_controle = "SELECT * FROM bg_repartition WHERE annee=$annee AND id_type_session=$id_type_session AND jury is NULL ";
            $result_controle = bg_query($lien, $sql_controle, __FILE__, __LINE__);
            $controle = mysqli_num_rows($result_controle);
            if ($controle == 0) {
                $cles = array_keys($_REQUEST['tache']);
                $tache = $cles[0];
                //creation de code par centre
                switch ($tache) {
/*                case 'creation_numeros':
$param=selectCodeAno($lien,$annee,$id_type_session);
if($param!=''){
$sql="DELETE from bg_notes WHERE annee=$annee and id_type_session=$id_type_session ";
bg_query($lien,$sql, __FILE__,__LINE__);
$sql="DELETE FROM bg_codes WHERE annee=$annee AND id_type_session=$id_type_session ";
bg_query($lien,$sql, __FILE__,__LINE__);
$sql2="SELECT id_centre FROM bg_repartition WHERE annee=$annee AND id_type_session=$id_type_session GROUP BY id_centre ";
$result2=bg_query($lien,$sql2, __FILE__,__LINE__);
$code=genererMDP (2);
while($row2=mysqli_fetch_array($result2)){
$id_centre=$row2['id_centre'];

$debut=rand(100,300);
$sql3="INSERT INTO bg_codes (annee,id_centre,id_type_session,code,debut,pas) VALUES ('$annee',$id_centre,$id_type_session,'$code',$debut,$pas)";
bg_query($lien,$sql3, __FILE__,__LINE__);
$sql4="SELECT id_candidat, numero FROM bg_repartition WHERE annee=$annee AND id_centre=$id_centre AND id_type_session=$id_type_session ORDER BY numero";
$result4=bg_query($lien,$sql4, __FILE__,__LINE__);
$ano=$debut;
//echo $sql4."<br/>";
while($row4=mysqli_fetch_array($result4)){
$id_candidat=$row4['id_candidat'];
$ano=$ano+$pas;
$id_anonyme=$code.$ano;
$sql5="UPDATE bg_repartition SET id_anonyme='$id_anonyme', ano='$ano' WHERE annee=annee AND id_candidat='$id_candidat' AND id_type_session=$id_type_session ";
bg_query($lien,$sql5, __FILE__,__LINE__);
//echo $sql5."<br/>";
}
$tInterdit=array();
while(!in_array($code,$tInterdit)){
$tInterdit[]=$code;
$code=genererMDP (2);
}
}
$sql6="UPDATE bg_repartition SET id_anonyme=ENCODE(id_anonyme,'$param'), ano=ENCODE(ano,'$param') WHERE annee=annee AND id_type_session=$id_type_session";
bg_query($lien,$sql6, __FILE__,__LINE__);
echo debut_cadre_couleur_foncee(), "Opération exécutée avec succès", fin_cadre_couleur_foncee() ;
}else{
echo debut_boite_alerte(),"Opération impossible: \nVous n'avez pas enregistré de CODE pour garantir l'anonymat",fin_boite_alerte();
}
break;

case 'impression_numeros':
$sql="SELECT cent.id as id_centre, cent.etablissement as centre, count(*) as nbre
FROM bg_repartition rep, bg_candidats can, bg_ref_etablissement cent, bg_ref_region_division reg
WHERE can.annee=$annee AND rep.annee=$annee AND can.num_table=rep.num_table
AND rep.id_centre=cent.id AND reg.id=cent.id_region_division
AND can.type_session=$id_type_session AND rep.id_type_session=$id_type_session
GROUP BY id_centre ORDER BY reg.region_division, centre ";
$result=bg_query($lien,$sql,__FILE__,__LINE__);
echo debut_cadre_relief('','','',"Impression des numéros anonymes");
$table="<table><th>Centres</th><th>Nbre de candidats</th>";
while($row=mysqli_fetch_array($result)){
foreach(array('id_centre','centre','nbre') as $val) $$val=$row[$val];
$table.= "<tr><td><a href='../plugins/fonctions/inc-anonymat.php?annee=$annee&c=$id_centre&s=$id_type_session'><img src='../plugins/images/imprimer.png'/> $centre</a></td><td>$nbre</td></tr>";
}
$table.="<tr><td colspan=2><center><a href='".generer_url_ecrire('confidentiel')."'><img src='../plugins/images/btn_quitter.gif'/></a></center></td></tr>";
$table.="</table>";
echo $table;
echo fin_cadre_relief();
break;

case 'preparation_notes':
$param=selectCodeAno($lien,$annee,$id_type_session);
if($param!=''){
$sql="DELETE from bg_notes WHERE annee=$annee and id_type_session=$id_type_session ";
bg_query($lien,$sql, __FILE__,__LINE__);
echo "<b>".mysqli_affected_rows($lien)."</b> notes supprimées<br/>";
$tSql[]="insert into bg_notes (`num_table`, `id_anonyme`, `ano`, `annee`, `id_serie`, `jury`, `id_matiere`, `id_type_note`, `coeff`, `id_type_session`)\n"
. "SELECT DECODE(id_anonyme,'$param'), DECODE(id_anonyme,'$param'), DECODE(ano,'$param'), $annee, can.serie, rep.jury, cal.id_matiere, cal.id_type_note, cal.coeff, $id_type_session\n"
. " FROM bg_repartition rep, bg_calendrier cal, bg_candidats can\n"
. " WHERE rep.annee=$annee and cal.annee=$annee and can.type_session=$id_type_session and can.annee=$annee and (cal.id_type_note=1 OR cal.id_type_note=2)"
. " and can.num_table=rep.num_table and can.serie=cal.id_serie AND jury='$jury' and jury is not null"
;
$tSql[]="insert into bg_notes (`num_table`,`id_anonyme`, `ano`, `annee`, `id_serie`, `jury`, `id_matiere`, `id_type_note`, `coeff`, `id_type_session`)\n"
. "SELECT DECODE(id_anonyme,'$param'), DECODE(id_anonyme,'$param'), DECODE(ano,'$param'), $annee, can.serie, rep.jury, 42, 4, 0, $id_type_session\n"
. " FROM bg_repartition rep, bg_candidats can\n"
. " WHERE rep.annee=$annee and can.type_session=$id_type_session and can.annee=$annee AND can.eps=1 "
. " and can.num_table=rep.num_table AND jury='$jury' and jury is not null"
;

$tSql[]="insert into bg_notes (`num_table`,`id_anonyme`, `ano`, `annee`, `id_serie`, `jury`, `id_matiere`, `id_type_note`, `coeff`, `id_type_session`)\n"
. "SELECT DECODE(id_anonyme,'$param'), DECODE(id_anonyme,'$param'), DECODE(ano,'$param'), $annee, can.serie, rep.jury, mat.id, 4, 0, $id_type_session\n"
. " FROM bg_repartition rep, bg_candidats can, bg_ref_matiere mat, bg_ref_epreuve_facultative_a ef1\n"
. " WHERE rep.annee=$annee and can.type_session=$id_type_session and can.annee=$annee AND can.efa!=0 AND mat.id=ef1.id AND ef1.id=can.efa "
. " and can.num_table=rep.num_table AND jury='$jury' and jury is not null"
;
$tSql[]="insert into bg_notes (`num_table`,`id_anonyme`, `ano`, `annee`, `id_serie`, `jury`, `id_matiere`, `id_type_note`, `coeff`, `id_type_session`)\n"
. "SELECT DECODE(id_anonyme,'$param'), DECODE(id_anonyme,'$param'), DECODE(ano,'$param'), $annee, can.serie, rep.jury, mat.id, 4, 0, $id_type_session\n"
. " FROM bg_repartition rep, bg_candidats can, bg_ref_matiere mat, bg_ref_epreuve_facultative_b ef2\n"
. " WHERE rep.annee=$annee and can.type_session=$id_type_session and can.annee=$annee AND can.efb!=0 AND mat.id=ef2.id AND ef2.id=can.efb "
. " and can.num_table=rep.num_table AND jury='$jury' and jury is not null"
;
$nbre=0;
foreach($tSql as $sql) {
bg_query($lien,$sql,__FILE__,__LINE__);
$nbre+=mysqli_affected_rows($lien);
}
echo debut_cadre_couleur_foncee(), "<b>$nbre</b> notes créées<br/> Opération exécutée avec succès", fin_cadre_couleur_foncee() ;
}else{
echo debut_boite_alerte(),"Opération impossible: \nVous n'avez pas enregistré de CODE pour garantir l'anonymat",fin_boite_alerte();
}
break;
 */
                    case 'maj_sport':
                        $param = selectCodeAno($lien, $annee, $id_type_session);
                        echo debut_cadre_relief('', '', '', "Notes EPS");
                        $form = "<FORM method='POST' name='' action=''><table>";
                        $form .= "<th colspan=2>Jurys</th><th>Cocher</th>";
//                $tJurys=getJurys($lien,$annee);

                        $sql = "SELECT code, rep.jury
				FROM bg_repartition rep, bg_codes cod
				WHERE cod.annee=$annee AND rep.annee=$annee AND rep.jury=cod.jury
				GROUP BY rep.jury, code
				ORDER BY rep.jury, code ";
                        $result = bg_query($lien, $sql, __FILE__, __LINE__);
                        $form .= "<table>";
                        while ($row = mysqli_fetch_array($result)) {
                            $jury = $row['jury'];
                            $code = $row['code'];
                            $tJurys[$jury] = $code;
                        }

                        foreach ($tJurys as $jury => $code) {
                            /*        $sql="SELECT * FROM bg_notes WHERE jury=$jury AND id_matiere=42 AND id_type_note=4 AND note is not null";
                            $result=bg_query($lien,$sql,__FILE__,__LINE__);
                            $nb=mysqli_num_rows($result);
                            if($nb==0) $checked='';
                            else $checked='checked';
                             */$form .= "<tr><td colspan=2>Jury $jury ($code)</td><td><input type='checkbox' name=\"tJurys[$jury]\" value='$jury' $checked /></td></tr>";
                        }
                        $form .= "<tr><td colspan=2><input type='hidden' name='exec' value='confidentiel' /><input type='hidden' name='tache[maj_sport]' value='maj_sport' /></td><td><input name='annee' type='hidden' value='$annee' /><input name='id_type_session' type='hidden' value='$id_type_session' /></td></tr>";
                        $form .= "<tr><td><center><input name='notesEpsValidees' type='submit' value='Valider' /></center></td><td><center><input type='reset' Value='Annuler' /></center></td><td><center><a href='" . generer_url_ecrire('confidentiel') . "'><img src='../plugins/images/btn_quitter.gif' width='60px' /></a></td></tr>";
                        $form .= "</table></FORM>";
                        echo $form;
                        echo fin_cadre_relief();
                        break;

                    case 'levee_anonymat':
                        echo debut_boite_alerte(), "Assurez-vous que toutes les notes ont été saisies pour chacun des jurys dont l'anonymat doit être levé.", fin_boite_alerte();
                        $param = selectCodeAno($lien, $annee, $id_type_session);
                        echo debut_cadre_relief('', '', '', "Levée de l'anonymat");
                        $form = "<FORM method='POST' name='' action=''><table>";
                        $form .= "<th colspan=2>Jurys</th><th>Cocher</th>";
                        $tJurys = getJurys($lien, $annee);

                        foreach ($tJurys as $jury) {
                            $deliberation = detectDeliberation($lien, $annee, $jury);
                            if ($deliberation == 0) {
                                $checked = '';
                                $font = '';
                            } else {
                                //$checked='checked';
                                $checked = '';
                                $font = 'blue';
                            }
                            $form .= "<tr><td colspan=2><font color='$font' >Jury $jury</font></td><td><input type='checkbox' name=\"tJurys[$jury]\" value='$jury' $checked /></td></tr>";
                        }
                        $form .= "<tr><td colspan=2><input type='hidden' name='exec' value='confidentiel' /><input type='hidden' name='tache[levee_anonymat]' value='levee_anonymat' /></td><td><input name='annee' type='hidden' value='$annee' /><input name='id_type_session' type='hidden' value='$id_type_session' /></td></tr>";
                        $form .= "<tr><td><center><input name='anonymer' type='submit' value='Valider' /></center></td><td><center><input type='reset' Value='Annuler' /></center></td><td><center><a href='" . generer_url_ecrire('confidentiel') . "'><img src='../plugins/images/btn_quitter.gif' width='60px' /></a></td></tr>";
                        $form .= "</table></FORM>";
                        echo $form;
                        echo fin_cadre_relief();
                        break;

                }
            } else {
                echo debut_boite_alerte(), "Opération impossible: \nVous n'avez pas attribué de jury à tous les candidats ", fin_boite_alerte();
            }
        }
        echo $liste;
    }
    echo fin_cadre_trait_couleur();
    echo fin_grand_cadre(), fin_gauche(), fin_page();
}

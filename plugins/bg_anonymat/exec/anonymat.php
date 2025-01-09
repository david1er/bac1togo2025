<?php
if (!defined('_ECRIRE_INC_VERSION')) {
    return;
}

include_spip('inc/presentation'); // alleger les inclusions avec un inc/presentation_mini
include '../plugins/fonctions/generales.php';

setlocale(LC_TIME, "fr_FR");

function exec_anonymat()
{
    $exec = _request('exec');
    $titre = "exec_$exec";
    $commencer_page = charger_fonction('commencer_page', 'inc');
    echo $commencer_page($titre);
    $lien = lien();
    $statut = getStatutUtilisateur();

    foreach ($_REQUEST as $key => $val) {
        $$key = mysqli_real_escape_string($lien, trim($val));
    }

    if ($annee == '') {
        $annee = recupAnnee($lien); //$annee = date("Y");
    }
    if ($id_type_session == '') {
        $id_type_session = recupSession($lien);
    }

    $tab_auteur = $GLOBALS["auteur_session"];
    $login = $tab_auteur['login'];

    if ($statut != 'Admin') {
        switch ($statut) {
            case 'Presidents':
                $isPresident = true;
                $jurysPres = stripslashes($tab_auteur['email']);
                $tJurys = explode('@', $jurysPres);
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
            case 'Anonymat':
                $tJurys = getJurys($lien, $annee);
                break;
            case 'Informaticien':
                $tJurys = getJurys($lien, $annee);
                $isAdmin = true;
                break;
            default:
                die(KO . " - Statut <b>$statut</b> invalide");
        }
    } else {
        $isAdmin = true;
        $tJurys = getJurys($lien, $annee);
        asort($tJurys);
    }

    //Liste des jurys accessibles
    $options .= "<option value=0>-=[JURYS]=-</option>";
    foreach ($tJurys as $j) {
        if ($j > 0) {
            if (isset($_POST['jury']) && $_POST['jury'] == $j) {
                $options .= "<option value='$j' selected >$j</option>";
                $sql_nums = "SELECT numero FROM bg_repartition WHERE jury='$j' ORDER BY numero";
                $result_nums = bg_query($lien, $sql_nums, __FILE__, __LINE__);
                while ($rows_nums = mysqli_fetch_array($result_nums)) {
                    $optionsNumero .= "<option value='" . $rows_nums['numero'] . "'>" . $rows_nums['numero'] . "</option>";
                }
                $champPremierTable = "Premier numéro de table <select name='premier_table' >$optionsNumero</select>";
            } else {
                $options .= "<option value='$j'>$j</option>";
                //$champPremierTable='';
            }
        }
    }

    if (isAutorise(array('Admin', 'Presidents', 'Informaticien', 'Anonymat'))) {
        echo debut_grand_cadre();
        echo debut_gauche();
        echo debut_boite_info(), "<b>Anonymat</b><br/>Choisir l'année, la session, le jury et le pas. <br/>Indiquer le code et le début des anonymats. <br/>Puis cliquer sur la création des numéros anonymes (LOGO)", fin_boite_info();
        echo debut_boite_info(), "<b>Impression des anonymats</b><br/>Choisir l'année, la session et le jury. <br/>Cliquer sur l'impression des numéros anonymes. <br/>Choisir le jury puis imprimer", fin_boite_info();

        echo debut_boite_info();
        echo "<p><a href='../plugins/fonctions/inc-centre_effectif.php?region=1' class='ajax'>Statistique des annonymats GRAND-LOME</a></p>
          <p><a href='../plugins/fonctions/inc-centre_effectif.php?region=2' class='ajax'>Statistique des annonymats Maritime</a></p>
         <p><a href='../plugins/fonctions/inc-centre_effectif.php?region=3' class='ajax'>Statistique des annonymats Plateaux Est</a></p>
         <p><a href='../plugins/fonctions/inc-centre_effectif.php?region=4' class='ajax'>Statistique des annonymats Centrale</a></p>
         <p><a href='../plugins/fonctions/inc-centre_effectif.php?region=5' class='ajax'>Statistique des annonymats Kara</a></p>
         <p><a href='../plugins/fonctions/inc-centre_effectif.php?region=6' class='ajax'>Statistique des annonymats Savanes</a></p>
     <p><a href='../plugins/fonctions/inc-centre_effectif.php?region=8' class='ajax'>Statistique des annonymats Plateaux Ouest</a></p>";
        echo fin_boite_info();

        echo debut_boite_info(), "<p><a href='" . generer_url_ecrire('anonymat') . "&action=logout&logout=prive' class='ajax'>Se déconnecter</a></p>", fin_boite_info();
/*
if(isset($_POST['anonymer'])){
$param=selectCodeAno($lien,$annee,$id_type_session);
foreach($_POST['tJurys'] as $jury){
$tSql[]="DELETE FROM bg_deliberation WHERE annee=$annee AND jury=$jury ";

$tSql[]="UPDATE bg_notes notes, bg_resultats res, bg_repartition rep SET notes.num_table=rep.num_table, res.num_table=rep.num_table \n
WHERE notes.annee=$annee AND res.annee=$annee AND rep.annee=$annee AND rep.id_anonyme=notes.id_anonyme AND notes.id_anonyme=res.id_anonyme AND notes.jury=$jury ";
$tSql[]="REPLACE bg_deliberation (annee,jury) VALUES ($annee,$jury)";
}
foreach($tSql as $sql) bg_query($lien,$sql, __FILE__,__LINE__);
}
 */
        echo debut_droite();

        foreach ($_REQUEST['tache'] as $cle => $tval) {;}
        if ($cle != 'impression_numeros') {
            //$controle_js="onclick=\"if(confirm('Aviez-vous inséré le BON MOT DE PASSE et choisi UN JURY?') && form_taches.jury.value!=0) return true; else return false;\"";
            echo debut_cadre_trait_couleur('', '', '', "GESTION DES TACHES CONFIDENTIELLES", "", "", false);
            $liste = "<form action='" . generer_url_ecrire('anonymat') . "' method='POST' class='forml spip_xx-small' name='form_taches'>";
            $liste .= "<table>";
            $liste .= "<tr><td><select name='annee_session'>" . optionsAnnee(2016, $annee) . "</select></td><td><select name='id_type_session'>" . optionsReferentiel($lien, 'type_session', 1) . "</select></td><td><select name='jury' onchange='document.forms.form_taches.submit()'>" . $options . "</select></td></tr>";
            $liste .= "<tr><td><input type='image' src='../plugins/images/logo.png' width='75px' name='tache[creation_numeros]' value='creation_numeros' $controle_js /></td>
						<td>PAS  <select name='pas'>";
            for ($k = 1; $k < 11; $k++) {$liste .= "<option value='$k'>$k</option>";}
            $liste .= "</select></td>
					<td>Création des numéros anonymes</td></tr>";
            $liste .= "<tr><td>Code <input type='text' name='code_jury' size=1 OnkeyUp=\"javascript:this.value=this.value.toUpperCase();\" required /></td><td colspan=2>Début numéro <input type=number name='debut_ano' min=1 value=1 required /></td></tr>";
            $liste .= "<tr><td colspan=3>$champPremierTable</td></tr>";
            $liste .= "<tr><td><input type='image' src='../plugins/images/imprimante.jpg' width='75px' name='tache[impression_numeros]' value='impression_numeros' $controle_js /></td><td colspan=2>Impression des numéros anonymes</td></tr>";
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
                //creation de code par jury
                switch ($tache) {
                    case 'creation_numeros':
                        $param = selectCodeAno($lien, $annee, $id_type_session);
                        $sql_c = "SELECT * FROM bg_codes WHERE annee=$annee AND jury!='$jury' AND code='$code_jury' ";
                        $result_c = bg_query($lien, $sql_c, __FILE__, __LINE__);
                        $isCodeExisteDeja = mysqli_num_rows($result_c);
                        if ($isCodeExisteDeja > 0) {$row_c = mysqli_fetch_array($result_c);
                            $code_existant = $row_c['jury'];}

                        if ($param != '' && $isCodeExisteDeja == 0) {
                            $sql = "DELETE from bg_notes WHERE annee=$annee and id_type_session=$id_type_session AND jury='$jury' ";
                            bg_query($lien, $sql, __FILE__, __LINE__);
                            $sql = "DELETE FROM bg_codes WHERE annee=$annee AND id_type_session=$id_type_session AND jury='$jury' ";
                            bg_query($lien, $sql, __FILE__, __LINE__);
                            $sql2 = "SELECT id_centre FROM bg_repartition WHERE annee=$annee AND id_type_session=$id_type_session GROUP BY id_centre ";
                            $sql3 = "REPLACE INTO bg_codes (annee,jury,id_type_session,code,debut,pas,delib) VALUES ('$annee',$jury,$id_type_session,'$code_jury',$debut_ano,$pas,0)";
                            bg_query($lien, $sql3, __FILE__, __LINE__);

                            //Recherche des numeros superieur ou égal au premier numero choisi
                            if ($premier_table == '') {
                                $premier_table = 0;
                            }

                            $sql4 = "SELECT id_candidat, numero, num_table
						FROM bg_repartition
						WHERE annee=$annee AND jury='$jury' AND id_type_session=$id_type_session
						AND numero>=$premier_table
						ORDER BY numero";
                            $result4 = bg_query($lien, $sql4, __FILE__, __LINE__);
                            $tab = array('id_candidat', 'numero', 'num_table');
                            while ($row4 = mysqli_fetch_array($result4)) {
                                foreach ($tab as $val) {
                                    $$val = $row4[$val];
                                }

                                $tabCand[$numero]['num_table'] = $num_table;
                                $tabCand[$numero]['id_candidat'] = $id_candidat;
                            }

                            //Recherche des numeros inferieur au premier numero choisi
                            $sql5 = "SELECT id_candidat, numero, num_table
						FROM bg_repartition
						WHERE annee=$annee AND jury='$jury' AND id_type_session=$id_type_session
						AND numero<$premier_table
						ORDER BY numero";
                            $result5 = bg_query($lien, $sql5, __FILE__, __LINE__);
                            while ($row5 = mysqli_fetch_array($result5)) {
                                foreach ($tab as $val) {
                                    $$val = $row5[$val];
                                }

                                $tabCand[$numero]['num_table'] = $num_table;
                                $tabCand[$numero]['id_candidat'] = $id_candidat;
                            }
                            $ano = $debut_ano;
                            $i = 1;
                            foreach ($tabCand as $numero => $tabCand2) {
                                $num_table = $tabCand2['num_table'];
                                $id_candidat = $tabCand2['id_candidat'];
                                $id_anonyme = $code_jury . $ano;
                                $sql5 = "UPDATE bg_repartition SET id_anonyme='$id_anonyme', ano='$ano' WHERE annee=annee AND id_candidat='$id_candidat' AND id_type_session=$id_type_session AND jury='$jury' ";
                                bg_query($lien, $sql5, __FILE__, __LINE__);
                                if ($i == 1) {
                                    $premier_numero = $num_table;
                                    $premier_ano = $id_anonyme;
                                } else {
                                    $dernier_numero = $num_table;
                                    $dernier_ano = $id_anonyme;
                                }
                                $ano = $ano + $pas;
                                $i++;
                            }

                            echo debut_boite_alerte();
                            echo "<table><tr><th>PREMIER/DERNIER</th><th>NUMERO DE TABLE</th><th>ANONYMAT</th></tr>";
                            echo "<tr><td>PREMIER NUMERO</td><td>$premier_numero</td><td>$premier_ano</td></tr>";
                            echo "<tr><td>DERNIER NUMERO</td><td>$dernier_numero</td><td>$dernier_ano</td></tr>";
                            echo "</table>";
                            echo fin_boite_alerte();

//                    $sql6="UPDATE bg_repartition SET id_anonyme=ENCODE(id_anonyme,'$param'), ano=ENCODE(ano,'$param') WHERE annee=annee AND id_type_session=$id_type_session  AND jury='$jury'";
                            //                    bg_query($lien,$sql6, __FILE__,__LINE__);

                            //Création des notes NULL dans la table
                            $sql = "DELETE from bg_notes WHERE annee=$annee and id_type_session=$id_type_session AND jury='$jury'";
                            bg_query($lien, $sql, __FILE__, __LINE__);
//                    echo "<b>".mysqli_affected_rows($lien)."</b> notes supprimées<br/>";
                            $tSql[] = "insert into bg_notes (`num_table`, `id_anonyme`, `ano`, `annee`, `id_serie`, `jury`, `id_matiere`, `id_type_note`, `coeff`, `id_type_session`, `nt`,`id_region`)\n"
                                . "SELECT id_anonyme, id_anonyme, ano, $annee, can.serie, rep.jury, cal.id_matiere, cal.id_type_note, cal.coeff, $id_type_session,rep.num_table,rep.id_region\n"
                                . " FROM bg_repartition rep, bg_calendrier cal, bg_candidats can\n"
                                . " WHERE rep.annee=$annee and cal.annee=$annee and can.type_session=$id_type_session and can.annee=$annee and (cal.id_type_note=1 OR cal.id_type_note=2)"
                                . " and can.num_table=rep.num_table and can.serie=cal.id_serie AND jury='$jury' and jury is not null"
                            ;
                            //Suppression des notes du sport pour les candidats inaptes dans la table
                            $tSql[] = "DELETE bg_notes FROM bg_notes INNER JOIN bg_candidats ON bg_notes.nt = bg_candidats.num_table
                                        WHERE bg_candidats.eps=2 AND bg_notes.id_matiere=42 AND id_type_note=1 AND jury='$jury'"
                            ;
                            for ($ef = 1; $ef < 9; $ef++) {
                                $tSql[] = "insert into bg_notes (`num_table`,`id_anonyme`, `ano`, `annee`, `id_serie`, `jury`, `id_matiere`, `id_type_note`, `coeff`, `id_type_session`, `nt`,`id_region`)\n"
                                    . "SELECT id_anonyme, id_anonyme, ano, $annee, can.serie, rep.jury, $ef, 4, 0, $id_type_session,rep.num_table,rep.id_region\n"
                                    . " FROM bg_repartition rep, bg_candidats can\n"
                                    . " WHERE rep.annee=$annee and can.type_session=$id_type_session and can.annee=$annee "
                                    . " and can.num_table=rep.num_table AND jury='$jury' and jury is not null"
                                ;
                            }

                            $nbre = 0;
                            $tSql[] = "DELETE FROM bg_resultats WHERE annee=$annee AND  id_type_session ='$id_type_session' AND jury='$jury' ";
                            $tSql[] = "DELETE FROM bg_deliberation WHERE annee=$annee AND  jury='$jury' ";
                            foreach ($tSql as $sql) {
                                bg_query($lien, $sql, __FILE__, __LINE__);
                                $nbre += mysqli_affected_rows($lien);
                            }

                            $tSql2[] = "DELETE FROM bg_resultats WHERE annee=$annee AND  id_type_session ='$id_type_session' AND jury='$jury' ";
                            $tSql2[] = "DELETE FROM bg_deliberation WHERE annee=$annee AND  jury='$jury' ";
                            foreach ($tSql2 as $sql2) {
                                bg_query($lien, $sql2, __FILE__, __LINE__);
                            }
                            echo debut_cadre_enfonce(), "<b>$nbre</b> notes créées<br/> Opération exécutée avec succès", fin_cadre_enfonce();
                        } else {
                            echo debut_boite_alerte(), "Opération impossible: \nVous n'avez pas enregistré de CODE pour garantir l'anonymat <br/> Ou bien Ce code existe déjà pour le jury $code_existant ", fin_boite_alerte();
                        }
                        break;

                    case 'impression_numeros':
                        echo debut_cadre_relief('', '', '', "Impression des numéros anonymes");
                        $table = "<table><th>Jurys</th>";
                        foreach ($tJurys as $j) {
                            if ($j > 0) {
                                $table .= "<tr><td><a href='../plugins/fonctions/inc-anonymat_jury.php?annee=$annee&j=$j&s=$id_type_session'><img src='../plugins/images/imprimer.png'/>JURY $j</a></td></tr>";
                            }

                        }
                        $table .= "<tr><td colspan=2><center><a href='" . generer_url_ecrire('anonymat') . "'><img src='../plugins/images/btn_quitter.gif'/></a></center></td></tr>";
                        $table .= "</table>";
                        echo $table;
                        echo fin_cadre_relief();
                        break;

                }
            } else {
                echo debut_boite_alerte(), "Opération impossible: \nCe jury ne comporte pas de candidats ", fin_boite_alerte();
            }
        }
        echo $liste;
    }
    echo fin_cadre_trait_couleur();
    echo fin_grand_cadre(), fin_gauche(), fin_page();
}

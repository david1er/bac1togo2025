<?php
if (!defined('_ECRIRE_INC_VERSION')) {
    return;
}

include_spip('inc/presentation'); // alleger les inclusions avec un inc/presentation_mini
include '../plugins/fonctions/generales.php';

setlocale(LC_TIME, "fr_FR");
define('MAX_LIGNES', 100);

function etablissementsProfesseurs($lien, $annee, $id_inspection, $id_etablissement = 0)
{
    $id_matiere = $_POST['id_matiere'];
    if ($id_etablissement > 0) {
        $andWhere = " AND eta.id=$id_etablissement ";
    }

    if ($id_matiere != 0) {
        $andWhere .= ' AND pro.matiere=' . $id_matiere;
        $andLien = ' &id_matiere=' . $id_matiere;
    } else {
        $andLien = '';
    }

    $sql = "SELECT eta.etablissement, eta.id as id_etablissement, count(*) as nbre
			FROM bg_ref_etablissement eta,bg_professeur pro
			WHERE eta.id_inspection=$id_inspection AND pro.etablissement=eta.id
			AND pro.annee=$annee  $andWhere
			GROUP BY eta.id ORDER BY eta.etablissement ";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);

    $formAnnee = "<form name='form_annee' method='POST' >";
    $formAnnee .= "<table><tr>
				<td><input type='hidden' name='annee' value='$annee' /><input type='hidden' name='id_inspection' value='$id_inspection' />
				<select name='annee' onchange='document.forms.form_annee.submit()'>" . optionsAnnee(2020, $annee) . "</select></td>
				<td><select name='id_matiere' onchange='document.forms.form_annee.submit()'>" . optionsReferentiel($lien, 'matiere', $id_matiere) . "</select></td>
				</tr></table>";
    $formAnnee .= "</form>";

    $tableau = "<table class='spip liste'><thead>";
    $tableau .= "<th>Etablissement</th><th>Nombre de professeurs</th><th>Listes</th><th>Formulaires</th>";
    $tableau .= "</thead><tbody>";
    $tab = array('id_etablissement', 'etablissement', 'nbre');
    while ($row = mysqli_fetch_array($result)) {
        foreach ($tab as $var) {
            $$var = $row[$var];
        }

        $tableau .= "<tr><td>$etablissement</td><td align='rigth'>$nbre</td>
						<td class='center'><a href='../plugins/fonctions/inc-pdf-liste_professeurs.php?id_etablissement=$id_etablissement&annee=$annee$andLien'><img src='../plugins/images/imprimer.png' /></a></td>
						<td class='center'><a href='../plugins/fonctions/inc-pdf-formulaire_professeurs.php?id_etablissement=$id_etablissement&annee=$annee$andLien'><img src='../plugins/images/imprimer.png' /></a></td>
						</tr>";
    }
    $tableau .= "</tbody></table>";
    echo "<h4><center><a href='./?exec=professeurs&id_inspection=$id_inspection'>Retour au Menu principal</a></center></h4>";
    echo $formAnnee;
    echo $tableau;
}

function exec_professeurs()
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
    if ($statut != 'Admin') {
        switch ($statut) {
            case 'Etablissement':
                $isOperateur = false;
                $isInspection = false;
                $isChefEtablissement = true;
                $cd_eta = stripslashes($tab_auteur['email']);
                $tab_cd_eta = explode('@', $cd_eta);
                $code_etablissement = $tab_cd_eta[1];
                //Recherche de la prefecture
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
                $andStatut2 = " AND pro.etablissement IN $tabEta ";
                $andEtablissementFormulaire = " AND eta.id IN $tabEta ";
                $andEtablissementFormulaire2 = " AND eta.id IN $tabEta ";
                $isChefEtablissement = false;
                $isOperateur = false;
                $isInspection = false;
                $isEncadrant = true;
                $isAdmin = false;
                $mois = date('m');
                if ($annee == '') {
                    $annee = recupAnnee($lien); //$annee = date("Y");
                }

                if ($mois > 10 && $annee == date("Y")) {
                    $annee += 1;
                }
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
                $WhereRegion = " WHERE id='$id_region' ";

                $andWhereInspection = "WHERE id_region=$id_region AND id=$idInsp";

                $id_inspection = $idInsp;

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
                $andStatut2 = " AND pro.etablissement IN $tabEta ";
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

    if (isAutorise(array('Admin', 'Etablissement', 'Operateur', 'Encadrant', 'Informaticien', 'Inspection'))) {

        if (isset($_POST['enregistrer']) || isset($_POST['modifier'])) {
            $ddn = formater_date($ddn);
            $sql_doublons = "SELECT * FROM bg_professeur WHERE nom='$nom' AND ddn='$ddn' AND ldn='$ldn' AND ci='$ci' AND matricule='$matricule' LIMIT 0,1";
            $result_doublons = bg_query($lien, $sql_doublons, __FILE__, __LINE__);
//        echo $sql_doublons;
            if (mysqli_num_rows($result_doublons) > 0) {$verif_doublons = false;
                $texte = "Ce professeur est déjà enregistré";} else { $verif_doublons = true;}

            list($jour, $mois, $an) = explode('/', $ddn);
            if ($sexe == 0 || $nom == '' || $prenoms == '' || $ldn == '' || $pdn == 0 || $nationalite == 0 || $etablissement == 0 || $matiere == 0 || $diplome == '' || $specialite == '') {
                { $verif_champs = false;
                    $texte .= "<br/>Les informations sont mal saisies.";}
            } else { $verif_champs = true;}
        }

        $verif = true;
        if (!$verif_doublons) {
            $verif = false;
        }

        if (!$verif_champs) {
            $verif = false;
        }

        //!checkdate($mois,$jour,$an) ||
        //Enregistrer un candidat
        if (isset($_POST['enregistrer'])) {
            if (!$verif) {
                $row = mysqli_fetch_array($result);
                $noms = $row['nom'] . ' ' . $row['prenoms'];
                echo debut_boite_alerte(), "Enregistrement impossible: $texte  ", fin_boite_alerte();
                $ddn = afficher_date($ddn);
            } else {
                $prenoms = ucwords($prenoms);
                $sql_ins = "INSERT INTO bg_professeur (`sexe`, `nom`, `prenoms`, `ddn`, `ldn`, `pdn`, `nationalite`, `telephone`, `etablissement`, `diplome`, `specialite`, `terminale`, `anciennete`, `matiere`, `activite`, `matricule`, `ci`, `genre`, `centre`, `annee`, `login`)
				VALUES ('$sexe','$nom','$prenoms','$ddn','$ldn','$pdn','$nationalite','$telephone','$etablissement','$diplome','$specialite','$terminale','$anciennete','$matiere','$activite','$matricule','$ci','$genre','$centre','$annee','$login') ";
                bg_query($lien, $sql_ins, __FILE__, __LINE__);
                $sexe = $pdn = $nationalite = $matiere = $anciennete = $centre = $genre = 0;
                $nom = $prenoms = $ddn = $telephone = $ldn = $diplome = $specialite = $matricule = $ci = '';
            }
        }

        //Mettre a jour un candidat
        if (isset($_POST['modifier'])) {
            $sql = "INSERT INTO bg_histo_professeur
			(`id_professeur`, `sexe`, `nom`, `prenoms`, `ddn`, `ldn`, `pdn`, `nationalite`, `telephone`, `etablissement`, `diplome`, `specialite`, `terminale`, `anciennete`, `matiere`, `activite`, `matricule`, `ci`, `genre`, `centre`, `annee`, `login`)
			SELECT `id_professeur`, `sexe`, `nom`, `prenoms`, `ddn`, `ldn`, `pdn`, `nationalite`, `telephone`, `etablissement`, `diplome`, `specialite`, `terminale`, `anciennete`, `matiere`, `activite`, `matricule`, `ci`, `genre`, `centre`, `annee`, '" . $login . "'
				FROM bg_professeur
				WHERE annee=$annee AND id_professeur='$id_professeur' ";
            $result = bg_query($lien, $sql, __FILE__, __LINE__);

            if (!$verif_champs) {
                $row = mysqli_fetch_array($result);
                $noms = $row['nom'] . ' ' . $row['prenoms'];
                echo debut_boite_alerte(), "Enregistrement impossible: $texte  ", fin_boite_alerte();
                $ddn = afficher_date($ddn);
            } else {
                $sql_maj = "UPDATE bg_professeur
					SET nom='$nom',prenoms='$prenoms',sexe='$sexe',ddn='$ddn',
					ldn='$ldn',pdn='$pdn', nationalite='$nationalite',telephone='$telephone',
					etablissement='$etablissement',diplome='$diplome',specialite='$specialite',terminale='$terminale',
					matiere='$matiere',activite='$activite',anciennete='$anciennete',
					matricule='$matricule', ci='$ci', genre='$genre', centre='$centre'
					WHERE annee=$annee AND id_professeur='$id_professeur' ";
                bg_query($lien, $sql_maj, __FILE__, __LINE__);
            }
        }

        //Supprimer un candidat
        if (isset($_GET['etape']) && $_GET['etape'] == 'supprimer' && $_GET['id_professeur'] != '') {
            $sql = "INSERT INTO bg_suppr_professeur
			(`id_professeur`, `sexe`, `nom`, `prenoms`, `ddn`, `ldn`, `pdn`, `nationalite`, `telephone`, `etablissement`, `diplome`, `specialite`, `terminale`, `anciennete`, `matiere`, `activite`, `matricule`, `ci`, `genre`, `centre`, `annee`, `login`)
			SELECT `id_professeur`, `sexe`, `nom`, `prenoms`, `ddn`, `ldn`, `pdn`, `nationalite`, `telephone`, `etablissement`, `diplome`, `specialite`, `terminale`, `anciennete`, `matiere`, `activite`, `matricule`, `ci`, `genre`, `centre`, `annee`, '" . $login . "'
				FROM bg_professeur
				WHERE annee=$annee AND id_professeur='$id_professeur' ";
            $result = bg_query($lien, $sql, __FILE__, __LINE__);
            $sql_sup = "DELETE FROM bg_professeur WHERE annee=$annee AND id_professeur='$id_professeur' $andStatut ";
            bg_query($lien, $sql_sup, __FILE__, __LINE__);
        }

        echo debut_grand_cadre();
        echo debut_cadre_trait_couleur('', '', '', "GESTION DES PROFESSEURS", "", "", false);

        if (!isset($_REQUEST['id_inspection']) && (!isset($_REQUEST['etape']))) {
            if ($isEncadrant) {
                listeChoix($lien, $andWhereRegion, 'professeurs');
            } elseif ($isInspection) {
                listeChoix($lien, $andWhereInspection, 'professeurs');
            } else {listeChoix($lien, '', 'professeurs');}

        }

        if (isset($_REQUEST['id_inspection']) && !isset($_REQUEST['etape'])) {
            echo "<div class='onglets_simple clearfix'><ul>";

            if (!$isInspection) {
                echo "<li><a href='./?exec=professeurs&etape=inserer&id_inspection=$id_inspection' class='ajax'>Insérer</a></li>";
            }
            echo "<li><a href='./?exec=professeurs&etape=afficher&id_inspection=$id_inspection' class='ajax'>Liste</a></li>
			<li><a href='./?exec=professeurs&etape=imprimer&id_inspection=$id_inspection' class='ajax'>Impressions</a></li>
			";
            if (!$isChefEtablissement) {
                echo "<li><a href='./?exec=professeurs' class='ajax'>Inspections</a></li>";
            }
            echo "<li><a href='./?exec=professeurs&etape=doc&id_inspection=$id_inspection' class='ajax'>Guide</a></li>";
            echo "<li><a href='./?exec=professeurs&action=logout&logout=prive' class='ajax'>Se déconnecter</a></li>";
            echo "</ul>
		</div>";
        }

/*
echo "<pre>";
print_r($_POST);
echo "</pre>";
 */

        if (isset($_REQUEST['etape']) && $_REQUEST['etape'] == 'imprimer') {
            echo etablissementsProfesseurs($lien, $annee, $id_inspection, $code_etablissement);
        }

        if (isset($_REQUEST['etape']) && $_REQUEST['etape'] == 'doublons') {
            echo "<h4><center><a href='./?exec=professeurs&id_inspection=$id_inspection'>Retour au Menu principal</a></center></h4>";
            doublons($lien, $annee, $andStatut);
        }

        //Tri des candidats - Formulaire
        if ((isset($_REQUEST['etape']) && ($_GET['etape'] == 'afficher' || $_GET['etape'] == 'modifier' || $_GET['etape'] == 'supprimer')) || isset($_POST['modifier']) || isset($_POST['tri_numero'])) {
            $tri = debut_cadre_enfonce('', '', '', 'Tri des professeurs');
            if ($isEncadrant) {
                $andWhereRegion = " WHERE id_region='$id_region'";
                $andWhereCen .= " AND eta.si_centre='oui' AND ins.id=eta.id_inspection AND ins.id_region=$id_region";
                $andWhereEta .= " AND eta.si_centre='non' AND ins.id=eta.id_inspection AND ins.id_region=$id_region";
            }
            if ($isInspection) {
                $andWhereRegion = " WHERE id_region='$id_region' AND id=$id_inspection";
                $andWhereCen .= " AND eta.si_centre='oui' AND ins.id=eta.id_inspection AND ins.id_region=$id_region AND eta.id_inspection=$id_inspection";
                $andWhereEta .= " AND eta.si_centre='non' AND ins.id=eta.id_inspection AND ins.id_region=$id_region AND eta.id_inspection=$id_inspection";
            }
            $andWhereCen .= " AND eta.si_centre='oui'";
            $andWhereEta .= " AND eta.si_centre='non'";

            if (isset($_POST['tri_annee']) && $_POST['tri_annee'] > 0) {
                $annee = $tri_annee;
            }

            if (isset($_POST['tri_matiere']) && $_POST['tri_matiere'] > 0) {
                $andWhere .= " AND pro.matiere=$tri_matiere ";
            }

            if (isset($_POST['tri_sexe']) && $_POST['tri_sexe'] > 0) {
                $andWhere .= " AND pro.sexe=$tri_sexe ";
            }

            if (isset($_POST['tri_inspection']) && $_POST['tri_inspection'] > 0) {
                $andWhere .= " AND eta.id_inspection=$tri_inspection ";
            }

            if (isset($_POST['tri_prefecture']) && $_POST['tri_prefecture'] > 0) {$andWhere .= " AND vil.id_prefecture=$tri_prefecture ";
                $andWhereEta = " AND vil.id_prefecture=$tri_prefecture ";}
            if (isset($_POST['tri_etablissement']) && $_POST['tri_etablissement'] > 0) {
                $andWhere .= " AND pro.etablissement=$tri_etablissement ";
            }

            if (isset($_POST['tri_numero']) && $_POST['tri_numero'] != '') {
                $andWhere .= " AND pro.id_professeur='$tri_numero' ";
            }

            if (isset($_POST['tri_nom']) && $_POST['tri_nom'] != '') {
                $andWhere .= " AND pro.nom LIKE '%$tri_nom%' ";
            }

            if ($isInspection) {
                $andWhereEta = " AND reg.id='$id_region' ";
            }

            if (!isset($_REQUEST['limit'])) {
                $limit = 0;
            }

            if ($limit < 0) {
                $limit = 0;
            }

            $tri .= "<form name='form_tri' method='POST' ><table>";
            $tri .= "<tr><td><input type='hidden' name='limit' value='$limit' /><input type='hidden' name='annee' value='$annee' /><input type='hidden' name='id_inspection' value='$id_inspection' /></center></td>
				<td></td></tr>";
            if (!$isChefEtablissement) {
                $tri .= "<tr><td><center><select style='width:50%' name='tri_annee' onchange='document.forms.form_tri.submit()'>" . optionsAnnee(2020, $tri_annee) . "</select></td>
				<td><center><select style='width:50%' name='tri_inspection' onchange='document.forms.form_tri.submit()'>" . optionsReferentiel($lien, 'inspection', $tri_inspection, $andWhereRegion) . "</select></center></td></tr>";
            }
            if (!$isChefEtablissement) {
                $tri .= "<tr><td><center><select style='width:50%'  name='tri_prefecture' onchange='document.forms.form_tri.submit()'>" . optionsReferentiel($lien, 'prefecture', $tri_prefecture, $andWhereRegion) . "</select></center></td>
				<td><center><select style='width:50%' name='tri_etablissement' onchange='document.forms.form_tri.submit()'>" . optionsEtablissement($lien, $tri_etablissement, $andWhereEta, true, ',bg_ref_inspection ins ') . "</select></center></td></tr>";
            }
            $tri .= "<tr><td><center><select style='width:50%' name='tri_sexe' onchange='document.forms.form_tri.submit()'>" . optionsReferentiel($lien, 'sexe', $tri_sexe) . "</select></center></td>
				<td><center><select style='width:50%' name='tri_matiere' onchange='document.forms.form_tri.submit()'>" . optionsReferentiel($lien, 'matiere', $tri_matiere) . "</select></center></td></tr>";
            $tri .= "<tr><td style='width:50%'><center>N°: <input style='width:45%'type='text' name='tri_numero' /></center></td><td style='width:50%'><center>Nom: <input style='width:40%' type='text' name='tri_nom' /><input type='submit' value='OK' /></center></td></tr>";
            $tri .= "</table></form>";
            $tri .= fin_cadre_enfonce();
            echo $tri;

            //Affichage des resultats du tri
            $pre = ($limit - MAX_LIGNES);
            if ($isChefEtablissement) {
                $andStatut = " AND pro.etablissement='$code_etablissement' ";
            }

            if ($isInspection) {
                $andStatut = $andStatut2;
            }if ($isEncadrant) {
                $andStatut = $andStatut2;
            }

            $sql_aff1 = "SELECT pro.*
					FROM bg_professeur pro, bg_ref_etablissement eta, bg_ref_ville vil
					WHERE pro.annee='$annee' AND pro.etablissement=eta.id
					AND vil.id=eta.id_ville $andWhere $andStatut ";
            $result_aff1 = bg_query($lien, $sql_aff1, __FILE__, __LINE__);
            $total = mysqli_num_rows($result_aff1);
            echo "<p class='center'>";
            if ($limit > 0) {
                echo "<a href=\"javascript:document.forms['form_tri'].limit.value=" . ($limit - MAX_LIGNES) . ";document.forms['form_tri'].submit();\"><<::Précédent </a>";
            }

            $limit2 = ($limit + MAX_LIGNES);
            if ($total < $limit2) {
                $limit2 = $total;
            }

            echo " $total enregistrements au total [$limit .... $limit2] ";
            if ($total > ($limit + MAX_LIGNES)) {
                echo "<a href=\"javascript:document.forms['form_tri'].limit.value=" . ($limit + MAX_LIGNES) . ";document.forms['form_tri'].submit();\"> Suivant::>></a>";
            }

            echo "<br/><a href='./?exec=professeurs&id_inspection=$id_inspection'>Retour au Menu principal</a>";
            echo "</p>";
            if ($isChefEtablissement) {
                $andStatut = " AND pro.etablissement='$code_etablissement' ";
            }

            $sql_aff = "SELECT pro.* FROM bg_professeur pro, bg_ref_etablissement eta, bg_ref_ville vil WHERE pro.annee='$annee' AND pro.etablissement=eta.id AND vil.id=eta.id_ville $andWhere $andStatut ORDER BY nom, prenoms LIMIT $limit, " . MAX_LIGNES;
            $result_aff = bg_query($lien, $sql_aff, __FILE__, __LINE__);

            //echo $sql_aff;
            $tab = array('id_professeur', 'sexe', 'nom', 'prenoms', 'ddn', 'ldn', 'pdn', 'nationalite', 'telephone', 'etablissement', 'diplome', 'specialite', 'matiere', 'terminale', 'anciennete', 'activite', 'annee', 'matricule', 'ci', 'genre', 'centre', 'login', 'maj');
            echo "<table class='spip liste'>";
            $thead = "<th>N°</th><th>Noms</th><th>Etablissement</th><th>Matière</th>";
            while ($row_aff = mysqli_fetch_array($result_aff)) {
                foreach ($tab as $var) {
                    // $$var = utf8_encode($row_aff[$var]);
                    $$var = $row_aff[$var];
                }

                $sexe = selectReferentiel($lien, 'sexe', $sexe);
                $matiere = selectReferentiel($lien, 'matiere', $matiere);
                $etablissement = selectReferentiel($lien, 'etablissement', $etablissement);
                if ($sexe == 'F') {
                    $civ = 'Mme';
                } else {
                    $civ = 'M.';
                }

                $tbody .= "<tr><td>$id_professeur</td><td><a href='./?exec=professeurs&etape=maj&id_professeur=$id_professeur&id_inspection=$id_inspection&annee=$annee' title=\"Saisi par $login le $maj\">$civ $nom $prenoms</a></td><td>$etablissement</td><td>$matiere</td></tr>";
            }
            echo $thead, $tbody;
            echo "</table>";
        }

        if (isset($_REQUEST['etape']) && ($_GET['etape'] == 'inserer' || $_GET['etape'] == 'maj') && (!isset($_POST['modifier'])) && !isset($_POST['tri_numero'])) {
            if ($cycle == 1) {
                $whereAct = " WHERE id=1 ";
            } else {
                $whereAct = '';
            }

            $disabled = '';
            $whereEta = " AND eta.id_inspection=$id_inspection AND id_centre!=0 " . $andEtablissementFormulaire2;
            $pdn = $nationalite = 1;
            if ($_GET['etape'] == 'maj') {
                $sql_mod = "SELECT * FROM bg_professeur WHERE annee=$annee AND id_professeur=$id_professeur $andStatut LIMIT 0,1";
                $result_mod = bg_query($lien, $sql_mod, __FILE__, __LINE__);
                $tab = array('id_professeur', 'sexe', 'nom', 'prenoms', 'ddn', 'ldn', 'pdn', 'nationalite', 'telephone', 'etablissement', 'diplome', 'specialite', 'matiere', 'terminale', 'anciennete', 'activite', 'annee', 'matricule', 'ci', 'genre', 'centre', 'login', 'maj');
                $row_mod = mysqli_fetch_array($result_mod);
                foreach ($tab as $var) {
                    $$var = $row_mod[$var];
                }

                $ddn = afficher_date($ddn);
                $whereEta = $andEtablissementFormulaire;
            }
            $form = "<div class='formulaire_spip'><form method='POST' action='' name='form_saisie' >";
            $form .= "<table>";
            $form .= "<tr><td colspan=3><fieldset><legend>Informations Générales</legend></td></tr>";
            $form .= "<tr><td colspan=3 class='center'></td></tr>";
            $form .= "<tr><td>Année</td><td colspan=2><input type='text' id='annee' name='annee' value='$annee' disabled></td></tr>";
            $form .= "<tr><td>N°</td><td colspan=2><input type='text' id='id_professeur' name='id_professeur' value='$id_professeur' disabled></td></tr>";
            $form .= "<tr><td>Etablissement</td><td colspan=2><select name='etablissement'>" . optionsEtablissement($lien, $etablissement, $whereEta) . "</select></td></tr>";

            if ($_GET['etape'] == 'inserer') {
                $majuscule = " OnkeyUp=\"javascript:this.value=this.value.toUpperCase();\" ";
            } else {
                $majuscule = '';
            }

            $form .= "<tr><td>Nom</td><td colspan=2><input type='text' id='nom' name='nom' value=\"$nom\" $majuscule required autofocus /></td></tr>";
            $form .= "<tr><td>Prénoms</td><td colspan=2><input type='text' id='prenoms' name='prenoms' value=\"$prenoms\" required /></td></tr>";
            $form .= "<tr><td>Sexe</td><td colspan=2><select id='sexe' name='sexe'>" . optionsReferentiel($lien, 'sexe', $sexe) . "</select></td></tr>";
            $form .= "<tr><td>Date de naissance</td><td colspan=2><input type='text' id='ddn' placeholder='31/12/1990' name='ddn' value='$ddn' pattern='^(0?[1-9]|[12][0-9]|3[01])[\/](0?[1-9]|1[012])[\/]\d{4}$' required /></td></tr>";
            $form .= "<tr><td>Lieu de naissance</td><td colspan=2><input type='text' id='ldn' name='ldn' value=\"$ldn\" required /></td></tr>";
            $form .= "<tr><td>Pays de naissance</td><td colspan=2><select id='pdn' name='pdn' onBlur=\"document.forms['form_saisie'].nationalite.value=this.value; \">" . optionsReferentiel($lien, 'pays', $pdn) . "</select></td></tr>";
            $form .= "<tr><td>Nationalité</td><td colspan=2><select id='nationalite' name='nationalite'>" . optionsReferentiel($lien, 'pays', $nationalite) . "</select></td></tr>";
            $form .= "<tr><td>Téléphone</td><td colspan=2><input type='text' id='telephone' name='telephone' value=\"$telephone\"></td></tr>";
            $form .= "<tr><td>Matricule</td><td colspan=2><input type='text' id='matricule' name='matricule' value=\"$matricule\"></td></tr>";
            $form .= "<tr><td>Numéro carte d'identité</td><td colspan=2><input type='text' id='ci' name='ci' value=\"$ci\"></td></tr>";
            $form .= "<tr><td colspan=3></fieldset></td></tr>";

            $form .= "<tr><td colspan=3><fieldset><legend>Autres</legend></td></tr>";
            $form .= "<tr><td>Diplôme</td><td colspan=2><input type='text' id='diplome' name='diplome' value=\"$diplome\"></td></tr>";
            $form .= "<tr><td>Spécialité</td><td colspan=2><input type='text' id='specialite' name='specialite' value=\"$specialite\"></td></tr>";
            $form .= "<tr><td>Première gardée?</td><td><select name='terminale'>" . optionsLogique($terminale) . "</select></td></td></tr>";
            $form .= "<tr><td>Préciser le type de classe première</td><td colspan=2><select id='genre' name='genre' >" . optionsReferentiel($lien, 'genre', $genre, " ", false) . "</select></td></tr>";
            $form .= "<tr><td>Ancienneté</td><td colspan=2><input type='number' id='anciennete' name='anciennete' min=0 value=\"$anciennete\" /></td></td></tr>";
            $form .= "<tr><td>Matière</td><td colspan=2><select id='matiere' name='matiere' ><option value=0>Aucune</option>" . optionsReferentiel($lien, 'matiere', $matiere, " ", false) . "</select></td></tr>";
            $form .= "<tr><td>Activité</td><td colspan=2><select id='activite' name='activite' >" . optionsReferentiel($lien, 'activite', $activite, " $whereAct") . "</select></td></tr>";
            $form .= "<tr><td>Centre de correction année passée</td><td colspan=2><select id='centre' name='centre' ><option value=0>Aucun</option>" . optionsReferentiel($lien, 'centre', $centre, " ", false) . "</select></td></tr>";
            $form .= "<tr><td colspan=3></fieldset></td></tr>";
            $form .= "<tr><td colspan=3><input type='hidden' name='id_inspection' value='$id_inspection' /></td></tr>";

            $js = "onClick=\"if(document.getElementById('etablissement').value==0
						|| document.getElementById('sexe').value==0
						|| document.getElementById('pdn').value==0
						|| document.getElementById('nationalite').value==0
						|| document.getElementById('diplome').value==''
						|| document.getElementById('specialite').value==''
						|| document.getElementById('matiere').value==0
						)
			{alert('Remplir les champs obligatoires'); return false; }
			else return true;\"; ";

            if ($_GET['etape'] == 'inserer') {
                $form .= "<tr><td class='center'><input class='submit' type='submit' name='enregistrer' value='Enregistrer' $js /></td><td class='center'><input type='reset' value='Annuler' /></td><td class='center'><input type='button' name='quitter' value='Quitter' onClick=\"window.location='http:./?exec=professeurs&id_inspection=$id_inspection&annee=$annee';\" /></td></tr>";
            } elseif ($_GET['etape'] == 'maj') {
                $form .= "<tr><td class='center'><input class='' type='submit' name='modifier' value='Modifier' $js /></td><td class='center'><input type='button' value='Supprimer' name='supprimer' onClick=\"if(confirm('Souhaitez-vous vraiment supprimer ce professeur?')) window.location='http:./?exec=professeurs&id_inspection=$id_inspection&etape=supprimer&id_professeur=$id_professeur&annee=$annee';\" /></td><td class='center'><input type='button' name='quitter' value='Quitter' onClick=\"window.location='http:./?exec=professeurs&id_inspection=$id_inspection&etape=afficher&annee=$annee';\" /></td></tr>";
            }
            $form .= "</table></form></div>";
            $form .= "<SCRIPT LANGUAGE='JavaScript'> document.forms.form_saisie.num_ravip.focus();</SCRIPT>";
            echo $form;

            //Affichage des historiques s'il en existe
            if ($_GET['etape'] == 'maj') {
                echo "<div class='spip_doc_descriptif spip_code'><table>";
                $sql = "SELECT nom, prenoms, eta.etablissement, ddn, ldn, pay.pays, pro.maj
					FROM bg_ref_etablissement eta, bg_histo_professeur pro
					LEFT JOIN bg_ref_pays pay ON pay.id=pro.pdn
					WHERE pro.annee=$annee AND pro.id_professeur='$id_professeur' AND pro.etablissement=eta.id
					ORDER BY maj desc";
                $result = bg_query($lien, $sql, __FILE__, __LINE__);
                $tab = array('nom', 'prenoms', 'etablissement', 'ddn', 'ldn', 'pays', 'maj');
                while ($row = mysqli_fetch_array($result)) {
                    foreach ($tab as $val) {
                        $$val = $row[$val];
                    }

                    echo "<tr><td>$nom $prenoms</td><td>" . afficher_date($ddn) . " $ldn / $pays</td><td>$etablissement</td><td>$maj</td></tr>";
                }
                echo "</table></div>";
            }
        }

        if (isset($_GET['etape']) && $etape == 'doc') {
            $doc = debut_cadre_couleur('', '', '', "DOCUMENTATION");
            $doc .= "<table>";
            $doc .= "<tr><td colspan=2>La gestion des professeurs comporte l'enregistrement (lien 'Insérer'), la visualisation et la mise à jour (lien 'Liste')
				et l'impression des listes (lien 'Impressions'). </td></tr>";
            $doc .= "<tr><td>1. Enregistrement </td><td>Cliquer sur le lien 'Insérier'<br/>Renseigner les divers champs<br/>Cliquer sur 'Enregistrer'<br>A la fin des saisies, cliquer sur 'Quitter'</td></tr>";
            $doc .= "<tr><td>2. Visualisation</td><td>Cliquer sur le lien 'Liste'<br/>Cette liste peut être triée par série et/ou par sexe<br/>Un professeur peut être recherché par son numéros ou par son nom</td></tr>";
            $doc .= "<tr><td>3. Mise à jour</td><td>Pour modifier ou supprimer, il suffit de cliquer sur le nom <br/>Le formulaire apparaît et la modification ou la suppression peut se faire</td></tr>";
            $doc .= "<tr><td>4. Impressions</td><td>Par ce lien, la liste peut être imprimée pour correction</td></tr>";
            $doc .= "<tr><td>5. Divers</td><td>Pour autres questions, bien vouloir appeler le Centre National de Documentation Pédagogique et des Technologies de l’Information et de la Communication pour l’Education (CNDP-TICE)</td></tr>";
            $doc .= "</table>";
            $doc .= fin_cadre_couleur();
            echo $doc;
        }

        echo fin_cadre_trait_couleur();
        echo fin_grand_cadre(), fin_page();
    }
}

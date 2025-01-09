<?php
error_reporting(0);
if (!defined('_ECRIRE_INC_VERSION')) {
    return;
}

include 'inc-traitements.php';
include_spip('inc/presentation'); // alleger les inclusions avec un inc/presentation_mini

setlocale(LC_TIME, "fr_FR");
define(OK, "<SPAN style='color:#3C3;font-weight:bold;'>[OK]</SPAN>");
define(KO, "<SPAN style='color:#C33;font-weight:bold;'>[KO]</SPAN>");

function exec_ezsql()
{
    $exec = _request('exec');
    $titre = "exec_$exec";
    $commencer_page = charger_fonction('commencer_page', 'inc');
    echo $commencer_page($titre);
    $lien = lien();

    $annee = date("Y");
    $aide = "Cliquez sur une des tables &agrave; gauche (vous pouvez commencer par cliquer sur \"spip\" par exemple) ou tapez une requ&ecirc;te ici puis cliquez sur le bouton [Exécuter]";
    $requeteExemple = "SELECT *\\n FROM spip_articles\\n LIMIT 0,10";
    $aideEnregistrer = html_entity_decode_utf8("Nom de la requête");

    //FIXME normalement ca devrait etre dans le script d'installation automatique du plugin. Mais je comprends pas comment ca marche :(
    //cf http://www.spip-contrib.net/Plugin-xml
    $sql = "CREATE TABLE IF NOT EXISTS `ez_sql` (\n"
        . "`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,\n"
        . "`nomRequete` VARCHAR( 64 ) NOT NULL ,\n"
        . "`requete` TEXT NOT NULL ,\n"
        . "`login` VARCHAR( 64 ) NOT NULL\n"
        . ") COMMENT = 'Requetes ezSQL';"
    ;
    bg_query($lien, $sql, __FILE__, __LINE__);

    if (isset($_REQUEST['table'])) {
        $isExecute = true;
        $sqlNormale = "SELECT *\n FROM " . $_REQUEST['table'] . "\n LIMIT 0,50";
    } elseif (isset($_REQUEST['requete'])) {
        $isExecute = true;
        $sqlNormale = stripslashes($_REQUEST['requete']);
    } else {
        $isExecute = false;
        $sqlNormale = $aide;
    }
    $nomTable = 'resultat';

    if (isset($_REQUEST['enregistrer'])) {
        $nomRequete = $_REQUEST['nomRequete'];
        bg_query($lien, "DELETE FROM ez_sql WHERE nomRequete='$nomRequete'", __FILE__, __LINE__);
        if (isset($_REQUEST['public'])) {
            $login = '*';
        } else {
            $login = $GLOBALS['auteur_session']['login'];
        }

        $sql = "INSERT INTO ez_sql(nomRequete,requete,login) VALUES ('$nomRequete','$sqlNormale','$login')";
        bg_query($lien, $sql, __FILE__, __LINE__);
    }

    if ($isExecute) {
        $sqlNormale = ez_propre($sqlNormale);
        $sqlAff = str_replace('=', "<b style='color:#e70;'>=</b>", $sqlNormale);
        foreach (array(')', 'distinct(', 'uncompress(', 'compress(', 'encode(', 'decode(') as $mot) {
            // mots sans espace avant/apres => ne pas mettre n'importe quoi !
            $sqlAff = str_ireplace("$mot", strtoupper("<b style='color:#d90;'>$mot</b>"), $sqlAff);
        }
        foreach (array('IN', '*') as $mot) {
            $sqlAff = str_ireplace(" $mot ", strtoupper(" <b style='color:#d90;'>$mot</b> "), $sqlAff);
        }
        foreach (array('select', 'insert', 'update', 'delete', 'replace', 'truncate', 'left join',
            'from', 'where', 'into', 'set', 'values', 'limit', 'and', 'table', 'order by', 'group by', 'having',
        ) as $mot) {
            $sqlAff = str_ireplace(" $mot ", strtoupper("<br/> <b style='color:#c33;'> $mot</b> "), $sqlAff);
            $sqlNormale = str_ireplace(" $mot ", strtoupper("\n $mot "), $sqlNormale);
        }
        $sqlNormale = trim($sqlNormale);
        //$sqlAff=substr($sqlAff,6);
        list($typeSQL, $rien) = explode(' ', trim($sqlAff), 2);
        $sqlAff = "<b style='color:#c33;'>" . strtoupper($typeSQL) . "</b> $rien";

        list($typeSQL, $rien) = explode(' ', trim($sqlNormale), 2);
        $sqlNormale = strtoupper($typeSQL) . " $rien";
        switch (trim(strtolower($typeSQL))) {
            case 'select':
                $isSelect = true;
                $tmp = trim(stristr($sqlNormale, 'from')); //requete apres from
                list($rien, $nomTable, $reste) = explode(' ', $tmp, 3);
                $nomTable = trim($nomTable);
                break;
            default:
                $isSelect = false;
                break;
        }
        $nomFichier = "$nomTable.csv";
    }
    echo debut_gauche();
    echo debut_boite_info();
    $r = bg_query($lien, "SELECT DATABASE()", __FILE__, __LINE__);
    $row = mysqli_fetch_row($r);
    $base = $row[0];
    echo "Base <b>$base</b><br/><small>" . mysqli_get_host_info() . "<br/>\n" . mysqli_get_server_info() . "<br/>\n</small>\n";
    $sql = "SHOW tables";
    $result = bg_query($lien, $sql, __FILE__, __LINE__);
    while ($row = mysqli_fetch_row($result)) {
        $table = $row[0];
        if (substr_count($table, '_') > 0) {
            list($prefixe, $reste) = explode(strrchr($table, '_'), $table);
        } else {
            $prefixe = $table;
        }

        if (substr_count($prefixe, 'spip') > 0) {
            $prefixe = 'spip';
        }

        if ((trim(strtolower($table))) == trim(strtolower($nomTable))) {
            $sTableEnCours = $table;
            $table = "<A class='table' HREF='" . generer_url_ecrire('ezsql') . "&table=$table' style='color:#000;'><b>$table</b></a>";
            $sql = "SHOW columns from $nomTable";
            $result2 = bg_query($lien, $sql, __FILE__, __LINE__);
            $cpt = 0;
            while ($row2 = mysqli_fetch_row($result2)) {
                $table .= "<br/><span title='" . $row2[1] . "'>&nbsp;&nbsp;" . $row2[0] . "</span>\n";
                switch (trim($row2[3])) {
                    case 'PRI':
                        $html = "style='cursor:pointer;font-weight:bold;border-bottom:1px dotted #000;' title='Clé primaire'";
                        break;
                    case '':
                        $html = "style='cursor:pointer;'";
                        break;
                    default:
                        $html = "style='cursor:pointer;border-bottom:1px dotted #000;' title='Clé $row2[3]'";
                }
                $tBody[$cpt] = "<td><span onclick=\"champ=document.forms['formRequete'].requete;champ.value+='$row2[0], ';champ.focus();\" $html>" . $row2[0] . "</span></td><td>" . $row2[1] . "</td><td>" . $row2[3] . "</td>";
                $cpt++;
            }
        } else {
            $table = "<A class='table' HREF='" . generer_url_ecrire('ezsql') . "&table=$table' style='color:#999;'>$table</A>";
        }

        $tTable[$prefixe][] = $table;

    }

    echo "<dl id='groupes'>\n<div style='background-color:#ddd;padding:2px;'>Cliquez sur un groupe de tables ci-dessous (par exemple <b>spip</b>)</div>\n";
    foreach ($tTable as $prefixe => $t1) {
        echo "<br/>\n\t<dt style='font-weight:bold;font-size:12px;cursor:help;'>$prefixe</dt>\n";
        echo "\t<dd style='border:dotted 1px black;background-color:#ddd;'>" . join('<br/>', $t1) . "</dd>\n";
    }
    echo "</dl>\n";
    echo fin_boite_info();

    echo debut_droite();
    $result = bg_query($lien, "SELECT nomRequete, requete from ez_sql WHERE login='*' OR login='" . $GLOBALS['auteur_session']['login'] . "'", __FILE__, __LINE__);
    $nb = mysqli_num_rows($result);
    if ($nb > 0) {
        $selectHistorique = "<SELECT NAME='historique'>\n"
            . "<OPTION VALUE=''>-=[Historique]=-</OPTION>\n";
        while ($row = mysqli_fetch_array($result)) {
            foreach (array('nomRequete', 'requete') as $col) {
                $$col = addslashes($row[$col]);
            }

            //if($nomRequete==$_REQUEST['nomRequete']) $selected='SELECTED';
            //else $selected='';
            $selectHistorique .= "<OPTION value='" . $requete . "' $selected>$nomRequete</OPTION>\n";
            echo $requete;
        }
        $selectHistorique .= "</SELECT>\n";
    } else {
        $selectHistorique = '';
    }

//    echo debut_droite();
    echo debut_cadre_relief();
    echo "<form name='formRequete' method='POST' action='" . generer_url_ecrire('ezsql') . "'>\n";
    if ($isExecute) {
        echo debut_cadre_relief('', '', '', "Requête");
        echo "<small style='font-family:monospace;'>$sqlAff</small>\n";
        echo fin_cadre_relief();
    }

    echo "<textarea name='requete'  class='form' style='color:#555;float: left; width: 100%; min-height: 120px;
    outline: none; resize: none;border-radius: 5px;border: 1px solid grey;'>\n"
        . ($sqlNormale) . "</textarea></br>"
    ;

    echo "<input name='executer' type='submit' value='Exécuter' class='submit'>";

    echo "</form>\n";

    if ($isExecute) {
        $result = bg_query($lien, $sqlNormale, __FILE__, __LINE__);
        if ($isSelect) {
            $nbLignes = mysqli_num_rows($result);

            if ($nbLignes > 0) {
                //FIXME Preciser le repertoire du fichier csv
                $destination = "../tmp/";
                $fichier = fopen($destination . $nomFichier, 'w+');
                $tRow = array();
                while ($tRow[] = mysqli_fetch_assoc($result));
                foreach ($tRow[0] as $k => $v) {
                    $tCol[$k] = ucwords(str_replace('_', ' ', $k));
                }

                fputcsv($fichier, (array) $tCol, "\t");

                $compteur = 0;
                $min = min($nbLignes, 150);
                $tbody = array();
                foreach ($tRow as $ligne) {
                    fputcsv($fichier, (array) $ligne, "\t");
                    if ($compteur++ < $min) {
                        $cptCol = 0;
                        foreach ($ligne as $col) {
                            if ($cptCol < 15) {
                                $tbody[$compteur] .= "<td><small>" . wordwrap(utf8_encode($col), 100, '<br/>', true) . "</small></td>";
                            }

                            $cptCol++;
                        }
                    }
                    //$tbody[] = "<td><small>".join('</small></td><td><small>',$ligne)."</small></td>";
                }
                fclose($fichier);
            } else {
                echo "Aucun enregistrement";
                $isSelect = false;
            }
        }
    }
    if ($isExecute) {
        echo "<small>" . htmlentities(mysqli_info()) . "</small><br/>";
        if ($isSelect) {
            echo "<A HREF='$destination$nomFichier'>"
                . "<IMG SRC='../plugins/ezSQL/images/csvimport-24.png' ALIGN='absmiddle'/>"
                . " Télécharger <b>$nomFichier</b> ($nbLignes lignes)</A>\n"
            ;
        } else {
            $nb = mysqli_affected_rows($lien);
            $s = $nb > 1 ? 's' : '';
            echo "<b>$nb</b> ligne$s affectée$s<br/>";
        }
    }
    echo fin_cadre_relief();
    if ($isExecute && $isSelect)

//        echo '<br/>'.ez_html_table("Aper&ccedil;u de la requ&ecirc;te",$tbody,"<th><small>".join('</small></th><th><small>',array_slice($tCol,0,5))."</small></th>",'statistiques-24.gif');
    {
        echo '<br/>' . ez_html_table("Aper&ccedil;u de la requ&ecirc;te", $tbody, "<th><small>" . join('</small></th><th><small>', array_slice($tCol, 0, 15)) . "</small></th>", 'statistiques-24.gif');
    }

    $aide = html_entity_decode_utf8($aide);

    $jquery = <<<FINSCRIPT

$(document).ready(function() {
	$('#groupes').find('dd').hide().end().find('dt').click(function() {
		var suivant = $(this).next();
		suivant.slideToggle();
	});
	$("a[@class=table]").hover(function() {
		$(this).css("color","#222");
	}, function() {
		$(this).css("color","#999");
	});

	$("textarea[@name*=requete]").hover(function() {
		$(this).css("color", "#000");
		if(this.value=='$aide') this.value='';
		this.focus();
		//alert(this.value);
	}, function() {
		$(this).css("color", "#555");
		if(this.value=='') {
			this.value='$aide';
			this.blur();
		}
	});

	$("select[@name=historique]").change(function() {
		forml=document.forms['formRequete'];
		forml.requete.value=this.value;
		forml.nomRequete.value='';
	});

	$("form[@name=formRequete]").submit(function() {
		if(this.requete.value=='$aide') {
			alert('Veuillez saisir une requete valide, par exemple :\\n\\n$requeteExemple');
			this.requete.value='$requeteExemple';
			return false;
		}
	});

	$("dd").hover(function() {
		$(this).css("border-style", "solid");
	}, function() {
		$(this).css("border-style", "dotted");
	});
});
FINSCRIPT;
    echo "<script type='text/javascript'><!--\n$jquery\n//-->\n</script>\n";

    echo fin_grand_cadre(), fin_gauche(), fin_page();

}

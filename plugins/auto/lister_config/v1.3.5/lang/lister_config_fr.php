<?php

// This is a SPIP language file  --  Ceci est un fichier langue de SPIP
// Fichier source, a modifier dans svn://zone.spip.org/spip-zone/_plugins_/autorite/lang/
if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

$GLOBALS[$GLOBALS['idx_lang']] = array(

	// D
	'description_page' => 'Vous pouvez trouver ci-dessous la liste des plugins actifs du site proposant une page de configuration du type <code>?exec=configurer_prefixe-plugin</code>.',
	'description_lister_config_plugins' => 'Vous retrouverez sur cette page tous les formulaires de configuration livrés par les plugins.',
	'description_lister_objets' => 'On va lister ici tous les objets principaux de SPIP ayant un tableau. Vous pourrez trouver en bas de page les objets n\'ayant pas de tableau.',
	'description_lister_plugins' => 'Génération du fichier d\'appel des plugins nécessaires au site ',
	'description_utiliser_plugins' => 'Vous pourriez utiliser cette trame pour batir un fichier <em>paquet.xml</em>,<br /> Utilisable comme pseudo-plugin pour reconfigurer votre site (cf. mes_fichiers) !<br /> (vous pouvez aussi passer vos squelettes dans ce plugin, qui pourra faciliter vos migrations).',

	// I
	'icone_page_plugin' => 'Page',

	// O
	'objets_sans_tableaux' => 'Voici les objets n\'ayant pas de tableau (cf. <em>prive/objets/liste/nom_objet.html</em>)',
	'objets_vides' => 'Voici la liste des objets ayant un tableau ne retournant aucun résultat, ou résultat alternatif.',

	// R
	'refus_acces' => 'Seuls les webmestres du site (@lister_webmestres@) sont autorisés à consulter cette page.',

	// T
	'titre_lister_config' => 'Liste des plugins configurables',
	'titre_lister_config_plugins' => 'Configurer les plugins',
	'titre_lister_objets' => 'Liste des objets principaux',
	'titre_lister_plugins' => 'Les plugins nécessaires au site',
	'titre_objets_sans_tableaux' => 'Objets sans tableaux',
	'titre_objets_vides' => 'Objets vides',
	'titre_page' => 'Liste des plugins configurables',
	'titre_page_lister_config_plugins' => 'Configurer les plugins actifs',

);

<?php

/**
 * Plugin Lister les fonctions PHP
 * Licence GPL
 * Auteur : Teddy Payet.
 */
if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function lister_fonctions_listermenu($flux) {
	$flux['data']['lister_fonctionsuser'] = array(
		'titre' => _T('lister_fonctions:titre_lister_fonctions_utilisateur'),
		'icone' => 'prive/themes/spip/images/lister_fonctions-16.png',
	);
	$flux['data']['lister_fonctionscompletes'] = array(
		'titre' => _T('lister_fonctions:titre_lister_fonctions_completes'),
		'icone' => 'prive/themes/spip/images/lister_fonctions-16.png',
	);

	return $flux;
}

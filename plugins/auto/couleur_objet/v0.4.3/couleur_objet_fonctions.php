<?php

/**
 * Compile la balise #COULEUR
 *
 * Renvoie la couleur associée à un objet
 *
 * Elle accepte 2 paramètres :
 * - #COULEUR{parent} pour prendre la couleur du parent en fallback
 * - #COULEUR{parent,recursif} même chose, mais cherche le parent récursivement
 * Attention, ne fonctionne que s'il y a l'API de déclaration des parents.
 *
 * @see
 * https://programmer.spip.net/Recuperer-objet-et-id_objet
 *
 * @param Champ $p
 *     Pile au niveau de la balise
 * @return Champ
 *     Pile complétée par le code à générer
 */
function balise_COULEUR($p) {

	// On prend nom de la clé primaire de l'objet pour calculer sa valeur
	$_id_objet = $p->boucles[$p->id_boucle]->primary;
	$id_objet  = champ_sql($_id_objet, $p);
	$objet     = $p->boucles[$p->id_boucle]->id_table;
	// 1er paramètre : prendre le parent en fallback
	// On vérifie juste si le texte est présent, peu importe la valeur
	$_parent = "false";
	if (($v = interprete_argument_balise(1, $p)) !== null) {
		$_parent = "strlen($v) ? true : false";
	}
	// 2ème paramètre : chercher le parent récursivement
	// On vérifie juste si le texte est présent, peu importe la valeur
	$_recursif = "false";
	if (($v2 = interprete_argument_balise(2, $p)) !== null) {
		$_recursif = "strlen($v2) ? true : false";
	}

	$p->code = "objet_couleur('$objet', $id_objet, $_parent, $_recursif)";

	return $p;
}

/**
 * Trouver la couleur d'un objet ou de son parent
 *
 * @uses objet_couleur_parent()
 *
 * @param string $objet
 *     Type de l'objet
 * @param int $id_objet
 *     Identifiant de l'objet
 * @param boolean $fallback_parent
 *     true pour chercher la couleur du parent en fallback
 * @param boolean $fallback_recursif
 *     true pour chercher les parents récursivement
 * @return string|false
 *     La couleur ou false si rien trouvé
 */
function objet_couleur($objet, $id_objet, $fallback_parent = false, $fallback_recursif = false) {

	include_spip('base/objets');

	$objet = objet_type($objet);
	$couleur_objet = sql_getfetsel(
		'couleur_objet',
		'spip_couleur_objet_liens',
		array(
			'objet=' . sql_quote($objet),
			'id_objet=' . intval($id_objet)
		)
	);

	// Si besoin, on prend la couleur du parent
	if (
		!$couleur_objet
		and $fallback_parent
	) {
		$couleur_objet = objet_couleur_parent($objet, $id_objet, $fallback_recursif);
	}

	return $couleur_objet;
}

/**
 * Trouver la couleur du parent d'un objet
 *
 * @note
 * Nécéssite l'API de déclaration des parents
 *
 * @uses objet_trouver_parent()
 * 
 * @param string $objet
 *     Type de l'objet
 * @param int $id_objet
 *     Identifiant de l'objet
 * @param boolean $recursif
 *     true pour chercher les parents récursivement
 * @return string|null
 *     La couleur ou null si rien trouvé
 */
function objet_couleur_parent($objet, $id_objet, $recursif = false) {

	$couleur = false;

	// Uniquement si l'API existe
	if (
		include_spip('base/objets_parents')
		and $parent = objet_trouver_parent($objet, $id_objet)
	) {
		$couleur = sql_getfetsel(
			'couleur_objet',
			'spip_couleur_objet_liens',
			array (
				'objet = ' . sql_quote($parent['objet']),
				'id_objet = ' . intval($parent['id_objet']),
			)
		);

		// Si besoin on cherche récursivement
		if (
			!$couleur
			and $recursif
		) {
			$couleur = objet_couleur_parent($parent['objet'], $parent['id_objet'], true);
		}

	}

	return $couleur;
}
<?php
/**
 * Plugin Gestion du BAC1
 * (c) 2015 Ghislain VLAVONOU
 * Licence GNU/GPL
 */

if (!defined('_ECRIRE_INC_VERSION')) {
    return;
}

/*
 * Un fichier d'autorisations permet de regrouper
 * les fonctions d'autorisations de votre plugin
 */

// declaration vide pour ce pipeline.
function refs_autoriser()
{}

// -----------------
// Objet classes

// bouton de menu
function autoriser_refs_menu_dist($faire, $type, $id, $qui, $opts)
{
    return in_array($qui['statut'], array('0minirezo'));
}

// creer
function autoriser_refs_creer_dist($faire, $type, $id, $qui, $opt)
{
    return in_array($qui['statut'], array('0minirezo', '1comite'));
}

// voir les fiches completes
function autoriser_refs_voir_dist($faire, $type, $id, $qui, $opt)
{
    //return ($id == $qui['id_auteur']);
    return true;
}

// modifier
function autoriser_refs_modifier_dist($faire, $type, $id, $qui, $opt)
{
    return $qui['statut'] == '0minirezo' and !$qui['restreint'];
}

// supprimer
function autoriser_refs_supprimer_dist($faire, $type, $id, $qui, $opt)
{
    return autoriser('webmestre', '', '', $qui);
}

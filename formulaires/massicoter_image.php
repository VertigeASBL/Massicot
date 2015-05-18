<?php
/**
 * Traitements du formulaire de massicotage
 *
 * @plugin     Massicot
 * @copyright  2015
 * @author     Michel @ Vertige ASBL
 * @licence    GNU/GPL
 */

if (!defined('_ECRIRE_INC_VERSION')) return;


/**
 * Saisies du formulaire de massicotage
 *
 * @return array
 *     Tableau des saisies du formulaire
 */
function formulaires_massicoter_image_saisies_dist ($objet, $id_objet, $redirect) {

    $saisies = array(
        array(
            'saisie' => 'hidden',
            'options' => array(
                'nom' => 'zoom',
            ),
        ),
        array(
            'saisie' => 'hidden',
            'options' => array(
                'nom' => 'x',
            ),
        ),
        array(
            'saisie' => 'hidden',
            'options' => array(
                'nom' => 'xx',
            ),
        ),
        array(
            'saisie' => 'hidden',
            'options' => array(
                'nom' => 'y',
            ),
        ),
        array(
            'saisie' => 'hidden',
            'options' => array(
                'nom' => 'yy',
            ),
        ),
    );

    return $saisies;
}

/**
 * Chargement du formulaire de massicotage
 *
 * Déclarer les champs postés et y intégrer les valeurs par défaut
 *
 * @return array
 *     Environnement du formulaire
 */
function formulaires_massicoter_image_charger_dist ($objet, $id_objet, $redirect) {

    // TODO
    $valeurs = array();

    return $valeurs;
}

/**
 * Vérifications du formulaire de massicotage
 *
 * Vérifier les champs postés et signaler d'éventuelles erreurs
 *
 * @return array
 *     Tableau des erreurs
 */
function formulaires_massicoter_image_verifier_dist ($objet, $id_objet, $redirect) {

    // TODO
    $erreurs = array();

    return $erreurs;

}

/**
 * Traitement du formulaire de massicotage
 *
 * Traiter les champs postés
 *
 * @return array
 *     Retours des traitements
 */
function formulaires_massicoter_image_traiter_dist ($objet, $id_objet, $redirect) {

    // TODO
    $retour = array();

    return $retour;
}

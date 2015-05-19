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

    $chemin_image = massicot_chemin_image($objet, $id_objet);
    list($width, $height) = getimagesize($chemin_image);

    // TODO prendre en compte un éventuel massicotage existant

    $valeurs = array(
        'zoom' => 1,
        'x'    => 0,
        'xx'   => $width,
        'y'    => 0,
        'yy'   => $height,
    );

    return $valeurs;
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

    $parametres = array(
        'zoom' => _request('zoom'),
        'x1' => _request('x'),
        'x2' => _request('xx'),
        'y1' => _request('y'),
        'y2' => _request('yy'),
    );

    if ($err = massicot_enregistrer($objet, $id_objet, $parametres)) {
        spip_log($err, 'massicot.'._LOG_ERREUR);
    }
}

<?php
/**
 * Fonctions utiles au plugin Massicot
 *
 * @plugin     Massicot
 * @copyright  2015
 * @author     Michel @ Vertige ASBL
 * @licence    GNU/GPL
 * @package    SPIP\Massicot\Fonctions
 */

if (!defined('_ECRIRE_INC_VERSION')) return;

/**
 * Retrouver le chemin d'une image donnée par un couple objet, id_objet
 *
 * Si le type d'objet est un document, on retourne le chemin du
 * fichier, sinon on cherche un éventuel logo pour l'objet
 *
 * @param string $objet : Le type d'objet
 * @param integer $id_objet : L'identifiant de l'objet
 *
 * @return string : le chemin vers l'image, un string vide sinon
 */
function massicot_chemin_image ($objet, $id_objet) {

    include_spip('base/abstract_sql');
    include_spip('base/objets');

    if (objet_type($objet) === 'document') {
        $fichier = sql_getfetsel('fichier', 'spip_documents', 'id_document='.intval($id_objet));
        return $fichier ? find_in_path(_NOM_PERMANENTS_ACCESSIBLES . $fichier) : '';
    } else {
        // TODO gestion des logos
        return '';
    }
}

/**
 * Enregistre un massicotage dans la base de données
 *
 * @param string $objet : le type d'objet
 * @param integer $id_objet : l'identifiant de l'objet
 * @param array parametres : Un tableau de parametres pour le
 *                           massicotage, doit contenir les clés
 *                           'zoom', 'x1', 'x2', 'y1', et 'y2'
 *
 * @return mixed   Rien si tout s'est bien passé, un message d'erreur
 *                 sinon
 */
function massicot_enregistrer ($objet, $id_objet, $parametres) {

    include_spip('action/editer_objet');
    include_spip('action/editer_liens');

    /* Tester l'existence des parametres nécessaires */
    if ( ! isset($parametres['zoom'])) {
        return _T('massicot:erreur_parametre_manquant', array('parametre' => 'zoom'));
    } else if ( ! isset($parametres['x1'])) {
        return _T('massicot:erreur_parametre_manquant', array('parametre' => 'x1'));
    } else if ( ! isset($parametres['x2'])) {
        return _T('massicot:erreur_parametre_manquant', array('parametre' => 'x2'));
    } else if ( ! isset($parametres['y1'])) {
        return _T('massicot:erreur_parametre_manquant', array('parametre' => 'y1'));
    } else if ( ! isset($parametres['y2'])) {
        return _T('massicot:erreur_parametre_manquant', array('parametre' => 'y2'));
    }

    $chemin_image = massicot_chemin_image($objet, $id_objet);
    list($width, $height) = getimagesize($chemin_image);

    $id_massicotage = sql_getfetsel('id_massicotage', 'spip_massicotages_liens',
                                    array(
                                        'objet='.sql_quote($objet),
                                        'id_objet='.intval($id_objet),
                                    ));

    if ( ! $id_massicotage) {
        $id_massicotage = objet_inserer('massicotage');
        objet_associer(array('massicotage' => $id_massicotage),
                       array($objet => $id_objet));
    }

    if ($err = objet_modifier('massicotage', $id_massicotage,
                              array('traitements' => serialize($parametres)))) {
        return $err;
    }
}
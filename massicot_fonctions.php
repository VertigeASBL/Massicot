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

        $fichier = sql_getfetsel('fichier', 'spip_documents',
                                 'id_document='.intval($id_objet));
        return $fichier ?
            find_in_path(_NOM_PERMANENTS_ACCESSIBLES . $fichier) : '';

    } else {

        $chercher_logo = charger_fonction('chercher_logo', 'inc');
        $logo = $chercher_logo($id_objet, id_table_objet($objet), 'on');
        if (is_array($logo)) {
            return array_shift($logo);
        }
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

/**
 * Retourne les paramètres de massicotage d'une image
 *
 * S'il n'y a pas de massicotage défini pour cet objet, on retourne
 * un tableau vide.
 *
 * @param string $objet : le type d'objet
 * @param integer $id_objet : l'identifiant de l'objet
 *
 * @return array : Un tableau avec les paramètres de massicotage
 */
function massicot_get_parametres ($objet, $id_objet) {

    include_spip('base/abstract_sql');

    $traitements = sql_getfetsel(
        'traitements',
        'spip_massicotages as M' .
        ' INNER JOIN spip_massicotages_liens as L ON M.id_massicotage=L.id_massicotage',
        array('L.objet='.sql_quote($objet),
              'L.id_objet='.intval($id_objet)));

    if ($traitements) {
        return unserialize($traitements);
    } else {
        return array();
    }
}

/**
 * Massicoter un fichier image
 *
 * La fonction générale qui d'occupe du recadrage des images
 *
 * @param string $fichier : Le fichier
 * @param array $parametres : le tableau des paramètres de massicotage
 *
 * @return string : Un fichier massicoté
 */
function massicoter_fichier ($fichier, $parametres) {

    include_spip('inc/filtres');
    include_spip('inc/filtres_images_mini');
    include_spip('filtres/images_transforme');

    /* ne rien faire s'il n'y a pas de massicotage défini */
    if ( ! $parametres) {
        return $fichier;
    }

    /* on vire un éventuel query string */
    $fichier = parse_url($fichier);
    $fichier = $fichier['path'];

    list($width, $height) = getimagesize($fichier);

    $fichier = extraire_attribut(
        image_reduire($fichier,
                      $parametres['zoom'] * $width,
                      $parametres['zoom'] * $height),
        'src');

    /* on vire un éventuel query string */
    $fichier = parse_url($fichier);
    $fichier = $fichier['path'];

    list($width, $height) = getimagesize($fichier);

    $fichier = extraire_attribut(
        image_recadre($fichier,
                      $width  - $parametres['x1'],
                      $height - $parametres['y1'],
                      'bottom right'),
        'src');

    $fichier = extraire_attribut(
        image_recadre($fichier,
                      $parametres['x2'] - $parametres['x1'],
                      $parametres['y2'] - $parametres['y1'],
                      'top left'),
        'src');

    return $fichier;
}

/**
 * Massicoter un document
 *
 * À utiliser comme filtre sur les balises #FICHIER ou #URL_DOCUMENT
 *
 * @param string $fichier : Le fichier du document
 *
 * @return string : Un fichier massicoté
 */
function massicoter_document ($fichier) {

    include_spip('base/abstract_sql');
    include_spip('inc/documents');

    $parametres = sql_getfetsel(
        'traitements',
        'spip_massicotages as M' .
        ' INNER JOIN spip_massicotages_liens as L ON L.id_massicotage = M.id_massicotage' .
        ' INNER JOIN spip_documents as D ON (D.id_document = L.id_objet AND L.objet="document")',
        'D.fichier='.sql_quote(set_spip_doc($fichier)));

    return massicoter_fichier($fichier, unserialize($parametres));
}

/**
 * Massicoter un objet
 *
 * À utiliser comme filtre sur les balises #LOGO_*. Pour les balises
 * #LOGO_DOCUMENT, il faut utiliser la fonction
 * massicoter_logo_document
 *
 * @param string $fichier : Le fichier à massicoter
 * @param string $objet : Le type d'objet
 * @param string $id_obejt : L'identifiant de l'objet
 *
 * @return string : Un fichier massicoté
 */
function massicoter_objet ($fichier, $objet, $id_objet) {

    return massicoter_fichier($fichier, massicot_get_parametres($objet, $id_objet));
}

/**
 * Massicoter un logo document
 *
 * Traitement automatique sur les balises #LOGO_DOCUMENT
 *
 * @param string $fichier : Le logo
 *
 * @return string : Un logo massicoté
 */
function massicoter_logo_document ($logo, $connect = null, $doc = array()) {

    include_spip('inc/filtres');
    include_spip('inc/filtres_images_mini');

    /* Si le document en question n'est pas une image, on ne fait rien */
    if (( ! $logo) OR
        ( ! in_array($doc['extension'], array('jpg','jpeg','png','gif')))) {

        return $logo;
    }

    /* S'il y a un lien sur le logo, on le met de côté pour le
       remettre après massicotage */
    if (preg_match('#(<a.*?>)<img.*$#', $logo) === 1) {
        $lien = preg_replace('#(<a.*?>)<img.*$#', '$1', $logo);
    }

    list($largeur_logo, $hauteur_logo) =
        getimagesize(extraire_attribut($logo, 'src'));

    $balise_img = charger_filtre('balise_img');

    $fichier_massicote = massicoter_document(get_spip_doc($doc['fichier']));

    /* Comme le logo reçu en paramètre peut avoir été réduit grâce aux
       paramètres de la balise LOGO_, il faut s'assurer que l'image
       qu'on renvoie fait bien la même taille que le logo qu'on a
       reçu. */
    $balise = image_reduire(
        $balise_img($fichier_massicote, '', 'spip_logos'),
        $largeur_logo, $hauteur_logo);

    if ($lien) {
        $balise = $lien . $balise . '</a>';
    }

    return $balise;
}

/**
 * Massicoter un logo
 *
 * Traitement automatique sur les balises #LOGO_*
 *
 * @param string $fichier : Le logo
 *
 * @return string : Un logo massicoté
 */
function massicoter_logo ($logo, $connect = null, $objet = array()) {

    include_spip('inc/filtres');

    if ( ! $logo) {
        return $logo;
    }

    $fichier = extraire_attribut($logo, 'src');

    /* Pour deviner le type d'objet, on cherche une entrée du type
       id_objet dans le tableau de l'objet, et on s'en sert pour
       déduire son type et son id */
    foreach ($objet as $cle => $valeur) {
        if (strpos($cle, 'id_') === 0) {
            /* id_trad ne correspond pas à un objet */
            if ( $cle === 'id_trad' ) {
                continue;
            } else {
                $objet_type = objet_type($cle);
                $id_objet = $valeur;
                break;
            }
        }
    }

    $parametres = massicot_get_parametres($objet_type, $id_objet);

    $fichier = massicoter_fichier($fichier, $parametres);

    $balise_img = charger_filtre('balise_img');

    return $balise_img($fichier, '', 'spip_logos');
}

/**
 * Traitement auto sur les balises #LARGEUR
 *
 * @param string $largeur : La largeur renvoyée par la balise
 *
 * @return string : La largeur de l'image après massicotage
 */
function massicoter_largeur ($largeur, $connect = null, $doc = array()) {

    if (( ! $largeur) OR ( ! isset($doc['id_document']))) {
        return $largeur;
    }

    $parametres = massicot_get_parametres('document', $doc['id_document']);

    return (string) round(($parametres['x2'] - $parametres['x1']) * $parametres['zoom']);
}

/**
 * Traitement auto sur les balises #HAUTEUR
 *
 * @param string $hauteur : La hauteur renvoyée par la balise
 *
 * @return string : La hauteur de l'image après massicotage
 */
function massicoter_hauteur ($hauteur, $connect = null, $doc = array()) {

    if (( ! $hauteur) OR ( ! isset($doc['id_document']))) {
        return $hauteur;
    }

    $parametres = massicot_get_parametres('document', $doc['id_document']);

    return (string) round(($parametres['y2'] - $parametres['y1']) * $parametres['zoom']);
}
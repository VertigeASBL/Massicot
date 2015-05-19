<?php
/**
 * Utilisations de pipelines par Massicot
 *
 * @plugin     Massicot
 * @copyright  2015
 * @author     Michel @ Vertige ASBL
 * @licence    GNU/GPL
 * @package    SPIP\Massicot\Pipelines
 */

if (!defined('_ECRIRE_INC_VERSION')) return;

/**
 * Ajoute le plugins jqueryui Slider
 *
 * @pipeline jqueryui_plugins
 * @param  array $scripts  Plugins jqueryui à charger
 * @return array       Liste des plugins jquerui complétée
 */
function massicot_jqueryui_plugins ($scripts) {

    $scripts[] = 'jquery.ui.slider';

    return $scripts;
}


/**
 * Ajouter un brin de CSS
 *
 * @pipeline header_prive
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
 */
function massicot_header_prive ($flux) {

    $flux .= '<link rel="stylesheet" href="' . find_in_path('css/massicot.css') . '" type="text/css" media="all" />';

    return $flux;
}
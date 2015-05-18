<?php
/**
 * Définitions des tables du plugin Massicot
 *
 * @plugin     Massicot
 * @copyright  2015
 * @author     Michel @ Vertige ASBL
 * @licence    GNU/GPL
 */

if (!defined('_ECRIRE_INC_VERSION')) return;


/**
 * Déclaration des alias de tables et filtres automatiques de champs
 *
 * @pipeline declarer_tables_interfaces
 * @param array $interfaces
 *     Déclarations d'interface pour le compilateur
 * @return array
 *     Déclarations d'interface pour le compilateur
 */
function massicot_declarer_tables_interfaces($interfaces) {

    return $interfaces;
}

/**
 * Création de la table spip_massicotages
 *
 * @pipeline declarer_tables_principales
 * @param  array $tables  Tables principales
 * @return array          Tables principales
 */
function massicot_declarer_tables_principales ($tables_principales) {

    $tables_principales['spip_massicotages'] = array(
        'field' => array(
            'id_massicotage' => "bigint(21) NOT NULL",
            'traitements'    => "text NOT NULL",
        ),
        'key' => array(
            'PRIMARY KEY' => "id_massicotage",
        ),
    );

    $tables_principales['spip_massicotages']['tables_jointures'][] = 'spip_massicotages_liens';

    return $tables_principales;
}

/**
 * Création de la table spip_massicotages_liens
 *
 * @pipeline declarer_tables_auxiliaires
 * @param  array $tables  Tables auxiliaires
 * @return array          Tables auxiliaires
 */
function massicot_declarer_tables_auxiliaires ($tables_auxiliaires) {

	$tables_auxiliaires['spip_massicotages_liens'] = array(
		'field' => array(
			"id_massicotage" => "bigint(21) DEFAULT '0' NOT NULL",
			"id_objet"       => "bigint(21) DEFAULT '0' NOT NULL",
			"objet"          => "VARCHAR(25) DEFAULT '' NOT NULL",
			"vu"             => "VARCHAR(6) DEFAULT 'non' NOT NULL"
		),
		'key' => array(
			"PRIMARY KEY"        => "id_massicotage,id_objet,objet",
			"KEY id_massicotage" => "id_massicotage",
		),
	);

    return $tables_auxiliaires;
}
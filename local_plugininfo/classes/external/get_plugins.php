<?php
// This file is part of Moodle - http://moodle.org/
namespace local_plugininfo\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->libdir . '/adminlib.php');

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use external_multiple_structure;

/**
 * Webservice: Gibt alle installierten Plugins zurück.
 */
class get_plugins extends external_api {

    /**
     * Keine Eingabeparameter benötigt.
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([]);
    }

    /**
     * Liest alle installierten Plugins aus dem Plugin-Manager aus.
     */
    public static function execute(): array {
        global $CFG;

        // Berechtigungsprüfung
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('moodle/site:config', $context);

        $pluginman = \core_plugin_manager::instance();
        $allplugins = $pluginman->get_plugins();

        $result = [];
        foreach ($allplugins as $type => $plugins) {
            foreach ($plugins as $name => $plugin) {
                $result[] = [
                    'component'  => $plugin->component,
                    'name'       => $plugin->name,
                    'type'       => $plugin->type,
                    'version'    => isset($plugin->versiondb) ? (string)$plugin->versiondb : '',
                    'release'    => isset($plugin->release) ? (string)$plugin->release : '',
                    'enabled'    => (int)($plugin->is_enabled() !== false),
                    'source'     => ($plugin->source === \core_plugin_manager::PLUGIN_SOURCE_STANDARD)
                                    ? 'standard' : 'extension',
                    'path'       => $plugin->rootdir ?? '',
                    'requires'   => isset($plugin->versionrequires) ? (string)$plugin->versionrequires : '',
                    'maturity'   => isset($plugin->maturity) ? (string)$plugin->maturity : '',
                ];
            }
        }

        return $result;
    }

    /**
     * Rückgabe-Struktur definieren.
     */
    public static function execute_returns(): external_multiple_structure {
        return new external_multiple_structure(
            new external_single_structure([
                'component' => new external_value(PARAM_TEXT,    'Vollständiger Komponentenname, z.B. mod_assign'),
                'name'      => new external_value(PARAM_TEXT,    'Kurzname des Plugins'),
                'type'      => new external_value(PARAM_TEXT,    'Plugin-Typ, z.B. mod, block, auth'),
                'version'   => new external_value(PARAM_TEXT,    'Installierte Versionsnummer'),
                'release'   => new external_value(PARAM_TEXT,    'Versions-Release-String'),
                'enabled'   => new external_value(PARAM_INT,     '1 wenn aktiviert, 0 wenn deaktiviert'),
                'source'    => new external_value(PARAM_TEXT,    'standard oder extension'),
                'path'      => new external_value(PARAM_TEXT,    'Pfad im Dateisystem'),
                'requires'  => new external_value(PARAM_TEXT,    'Mindest-Moodle-Version'),
                'maturity'  => new external_value(PARAM_TEXT,    'Reifegrad des Plugins'),
            ])
        );
    }
}

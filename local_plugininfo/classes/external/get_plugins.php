<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * External function: get_plugins
 * Gibt alle installierten Plugins dieser Moodle-Instanz zurück.
 *
 * @package    local_plugininfo
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3 or later
 */

namespace local_plugininfo\external;

defined('MOODLE_INTERNAL') || die();

// Moodle 5 nutzt externe Funktionen via core\external\* – externallib.php
// bleibt für Abwärtskompatibilität weiterhin erforderlich.
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
     *
     * @return array
     */
    public static function execute(): array {
        global $CFG;

        // Berechtigungsprüfung: nur Site-Admins / Manager mit site:config.
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('moodle/site:config', $context);

        $pluginman = \core_plugin_manager::instance();
        $allplugins = $pluginman->get_plugins();

        $result = [];
        foreach ($allplugins as $type => $plugins) {
            foreach ($plugins as $name => $plugin) {
                $result[] = [
                    'component' => clean_param($plugin->component, PARAM_COMPONENT),
                    'name'      => clean_param($plugin->name, PARAM_ALPHANUMEXT),
                    'type'      => clean_param($plugin->type, PARAM_ALPHANUMEXT),
                    'version'   => isset($plugin->versiondb)       ? (string)$plugin->versiondb       : '',
                    'release'   => isset($plugin->release)         ? clean_param($plugin->release, PARAM_TEXT) : '',
                    'enabled'   => (int)($plugin->is_enabled() !== false),
                    'source'    => ($plugin->source === \core_plugin_manager::PLUGIN_SOURCE_STANDARD)
                                    ? 'standard' : 'extension',
                    'path'      => isset($plugin->rootdir)         ? clean_param($plugin->rootdir, PARAM_PATH) : '',
                    'requires'  => isset($plugin->versionrequires) ? (string)$plugin->versionrequires : '',
                    'maturity'  => isset($plugin->maturity)        ? (string)$plugin->maturity        : '',
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
                'component' => new external_value(PARAM_TEXT, 'Vollständiger Komponentenname, z.B. mod_assign'),
                'name'      => new external_value(PARAM_TEXT, 'Kurzname des Plugins'),
                'type'      => new external_value(PARAM_TEXT, 'Plugin-Typ, z.B. mod, block, auth'),
                'version'   => new external_value(PARAM_TEXT, 'Installierte Versionsnummer'),
                'release'   => new external_value(PARAM_TEXT, 'Versions-Release-String'),
                'enabled'   => new external_value(PARAM_INT,  '1 wenn aktiviert, 0 wenn deaktiviert'),
                'source'    => new external_value(PARAM_TEXT, 'standard oder extension'),
                'path'      => new external_value(PARAM_TEXT, 'Pfad im Dateisystem'),
                'requires'  => new external_value(PARAM_TEXT, 'Mindest-Moodle-Version'),
                'maturity'  => new external_value(PARAM_TEXT, 'Reifegrad des Plugins'),
            ])
        );
    }
}

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
 * Web Services Definitionen für local_plugininfo.
 *
 * @package    local_plugininfo
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_plugininfo_get_plugins' => [
        'classname'   => 'local_plugininfo\external\get_plugins',
        'methodname'  => 'execute',
        'description' => 'Gibt eine Liste aller installierten Plugins dieser Moodle-Instanz zurück.',
        'type'        => 'read',
        'capabilities'=> 'moodle/site:config',
        'ajax'        => false,
        'loginrequired' => true,
    ],
    'local_plugininfo_get_usage' => [
        'classname'   => 'local_plugininfo\external\get_usage',
        'methodname'  => 'execute',
        'description' => 'Gibt die tatsächliche Nutzung von Aktivitäten, Kursformaten, Fragetypen und Blöcken zurück.',
        'type'        => 'read',
        'capabilities'=> 'moodle/site:config',
        'ajax'        => false,
        'loginrequired' => true,
    ],
];

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
 * External function: get_usage
 * Gibt die tatsächliche Nutzung von Plugins in Kursen zurück.
 *
 * @package    local_plugininfo
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3 or later
 */

namespace local_plugininfo\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use external_multiple_structure;

/**
 * Webservice: Gibt die tatsächliche Nutzung von Plugins in Kursen zurück.
 * Zählt genutzte Aktivitäten, Kursformate, Fragetypen und Blöcke direkt aus der DB.
 */
class get_usage extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([]);
    }

    /**
     * @return array
     */
    public static function execute(): array {
        global $DB;

        // Berechtigungsprüfung: nur Site-Admins / Manager mit site:config.
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('moodle/site:config', $context);

        // ── 1. Aktivitäten (Kursmodule) ──────────────────────────────────
        $activities = [];
        $sql = "SELECT m.name AS modname, COUNT(cm.id) AS usage_count
                FROM {course_modules} cm
                JOIN {modules} m ON m.id = cm.module
                WHERE cm.deletioninprogress = 0
                GROUP BY m.name
                ORDER BY usage_count DESC";
        foreach ($DB->get_records_sql($sql) as $row) {
            $activities[] = [
                'name'        => clean_param($row->modname, PARAM_ALPHANUMEXT),
                'component'   => 'mod_' . clean_param($row->modname, PARAM_ALPHANUMEXT),
                'usage_count' => (int)$row->usage_count,
            ];
        }

        // ── 2. Kursformate ───────────────────────────────────────────────
        $formats = [];
        $sql = "SELECT format, COUNT(id) AS usage_count
                FROM {course}
                WHERE format != ''
                GROUP BY format
                ORDER BY usage_count DESC";
        foreach ($DB->get_records_sql($sql) as $row) {
            $formats[] = [
                'name'        => clean_param($row->format, PARAM_ALPHANUMEXT),
                'component'   => 'format_' . clean_param($row->format, PARAM_ALPHANUMEXT),
                'usage_count' => (int)$row->usage_count,
            ];
        }

        // ── 3. Fragetypen ────────────────────────────────────────────────
        $questiontypes = [];
        $sql = "SELECT qtype, COUNT(id) AS usage_count
                FROM {question}
                WHERE qtype != 'random'
                GROUP BY qtype
                ORDER BY usage_count DESC";
        foreach ($DB->get_records_sql($sql) as $row) {
            $questiontypes[] = [
                'name'        => clean_param($row->qtype, PARAM_ALPHANUMEXT),
                'component'   => 'qtype_' . clean_param($row->qtype, PARAM_ALPHANUMEXT),
                'usage_count' => (int)$row->usage_count,
            ];
        }

        // ── 4. Blöcke ────────────────────────────────────────────────────
        $blocks = [];
        $sql = "SELECT blockname, COUNT(id) AS usage_count
                FROM {block_instances}
                GROUP BY blockname
                ORDER BY usage_count DESC";
        foreach ($DB->get_records_sql($sql) as $row) {
            $blocks[] = [
                'name'        => clean_param($row->blockname, PARAM_ALPHANUMEXT),
                'component'   => 'block_' . clean_param($row->blockname, PARAM_ALPHANUMEXT),
                'usage_count' => (int)$row->usage_count,
            ];
        }

        // ── 5. Filter (aktive) ───────────────────────────────────────────
        $filters = [];
        $sql = "SELECT filter, COUNT(id) AS usage_count
                FROM {filter_active}
                WHERE active = 1
                GROUP BY filter
                ORDER BY usage_count DESC";
        foreach ($DB->get_records_sql($sql) as $row) {
            $filters[] = [
                'name'        => clean_param($row->filter, PARAM_ALPHANUMEXT),
                'component'   => 'filter_' . clean_param($row->filter, PARAM_ALPHANUMEXT),
                'usage_count' => (int)$row->usage_count,
            ];
        }

        return [
            'activities'    => $activities,
            'formats'       => $formats,
            'questiontypes' => $questiontypes,
            'blocks'        => $blocks,
            'filters'       => $filters,
        ];
    }

    public static function execute_returns(): external_single_structure {
        $item = new external_multiple_structure(
            new external_single_structure([
                'name'        => new external_value(PARAM_TEXT, 'Kurzname'),
                'component'   => new external_value(PARAM_TEXT, 'Komponentenname'),
                'usage_count' => new external_value(PARAM_INT,  'Anzahl Nutzungen'),
            ])
        );

        return new external_single_structure([
            'activities'    => $item,
            'formats'       => $item,
            'questiontypes' => $item,
            'blocks'        => $item,
            'filters'       => $item,
        ]);
    }
}

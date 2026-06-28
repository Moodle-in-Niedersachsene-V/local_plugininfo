<?php
// This file is part of Moodle - http://moodle.org/
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

    public static function execute(): array {
        global $DB;

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
                'name'        => $row->modname,
                'component'   => 'mod_' . $row->modname,
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
                'name'        => $row->format,
                'component'   => 'format_' . $row->format,
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
                'name'        => $row->qtype,
                'component'   => 'qtype_' . $row->qtype,
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
                'name'        => $row->blockname,
                'component'   => 'block_' . $row->blockname,
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
                'name'        => $row->filter,
                'component'   => 'filter_' . $row->filter,
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

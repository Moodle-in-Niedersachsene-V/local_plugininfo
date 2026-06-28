<?php
// This file is part of Moodle - http://moodle.org/
defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_plugininfo_get_plugins' => [
        'classname'     => 'local_plugininfo\external\get_plugins',
        'methodname'    => 'execute',
        'description'   => 'Gibt eine Liste aller installierten Plugins dieser Moodle-Instanz zurück.',
        'type'          => 'read',
        'capabilities'  => 'moodle/site:config',
        'ajax'          => false,
        'services'      => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
    'local_plugininfo_get_usage' => [
        'classname'     => 'local_plugininfo\external\get_usage',
        'methodname'    => 'execute',
        'description'   => 'Gibt die tatsächliche Nutzung von Aktivitäten, Kursformaten, Fragetypen und Blöcken zurück.',
        'type'          => 'read',
        'capabilities'  => 'moodle/site:config',
        'ajax'          => false,
        'services'      => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
];

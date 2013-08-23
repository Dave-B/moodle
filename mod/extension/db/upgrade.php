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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file keeps track of upgrades to the extension module
 *
 * Sometimes, changes between versions involve alterations to database
 * structures and other major things that may break installations. The upgrade
 * function in this file will attempt to perform all the necessary actions to
 * upgrade your older installation to the current version. If there's something
 * it cannot do itself, it will tell you what you need to do.  The commands in
 * here will all be database-neutral, using the functions defined in DLL libraries.
 *
 * @package    mod
 * @subpackage extension
 * @copyright  2012 David Balch
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute extension upgrade from the given old version
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_extension_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes

    if ($oldversion < 2012032301) {
    /// Changing nullability of field lengthgranted on table assignment_submissions to allow null when importing
        $table = new xmldb_table('extension');
        $field = new xmldb_field('lengthgranted', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'lengthrequested');
        $dbman->change_field_precision($table, $field);

    /// Changing nullability of field studentmessage on table assignment_submissions to allow null when importing
        $table = new xmldb_table('extension');
        $field = new xmldb_field('studentmessage', XMLDB_TYPE_TEXT, null, null, null, null, null, 'privatenotes');
        $dbman->change_field_precision($table, $field);
    }

    if ($oldversion < 2012032302) {
    /// Re-state field, to ensure status field has a default value on study
        $table = new xmldb_table('extension');
        $field = new xmldb_field('status', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'evidencefile');
        $dbman->change_field_precision($table, $field);
    }

    /// Extra extension request details - ticket #3088
    if ($oldversion < 2013082300) {

        // Define field sharedetails to be added to extension.
        $table = new xmldb_table('extension');
        $field = new xmldb_field('sharedetails', XMLDB_TYPE_TEXT, null, null, null, null, null, 'timeconfirmed');

        // Conditionally launch add field sharedetails.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }


        // Define field howunforeseen to be added to extension.
        $table = new xmldb_table('extension');
        $field = new xmldb_field('howunforeseen', XMLDB_TYPE_TEXT, null, null, null, null, null, 'sharedetails');

        // Conditionally launch add field howunforeseen.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }


        // Define field impact to be added to extension.
        $table = new xmldb_table('extension');
        $field = new xmldb_field('impact', XMLDB_TYPE_TEXT, null, null, null, null, null, 'howunforeseen');

        // Conditionally launch add field impact.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }


        // Define field circumstancedate to be added to extension.
        $table = new xmldb_table('extension');
        $field = new xmldb_field('circumstancedate', XMLDB_TYPE_TEXT, null, null, null, null, null, 'impact');

        // Conditionally launch add field circumstancedate.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }


        // Define field progress to be added to extension.
        $table = new xmldb_table('extension');
        $field = new xmldb_field('progress', XMLDB_TYPE_TEXT, null, null, null, null, null, 'circumstancedate');

        // Conditionally launch add field progress.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }


        // Extension savepoint reached.
        upgrade_mod_savepoint(true, 2013082300, 'extension');
    }


    // Final return of upgrade result (true, all went good) to Moodle.
    return true;
}

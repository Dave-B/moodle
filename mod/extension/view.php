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
 * Prints a particular instance of extension
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod
 * @subpackage extension
 * @author     David Balch <david.balch@conted.ox.ac.uk>
 * @copyright  2012 The University of Oxford
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/// (Replace extension with the name of your module and remove this line)

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/locallib.php');

$id = required_param('id', PARAM_INT);  // Extension ID

if ($id) {
    if (! $cm = extension_get_cm_by_id($id)) {
        error("Extension ID was incorrect");
    }
} else {
    error('You must specify an extension ID');
}
//print_object($cm);

require_login($cm->course, false);
$context = get_context_instance(CONTEXT_COURSE, $cm->course);

$strextensions = get_string('modulenameplural', 'extension');

$can_viewanyextension = has_capability('mod/extension:viewanyextension', $context);
$can_grade = has_capability('mod/assignment:grade', $context);
$can_viewownextension = has_capability('mod/extension:viewownextension', $context);


/// Print the page header

$PAGE->set_url('/mod/extension/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($cm->extension->name));
$PAGE->set_heading(format_string($COURSE->fullname));
$PAGE->set_context($context);


// Output starts here
echo $OUTPUT->header();

if ( ! ($can_viewanyextension || $can_grade ||
        ($can_viewownextension && $USER->id == $cm->extension->userid))
    ) {
     // No permission to view this extension
     error(get_string('nopermission', 'extension'));

} else {
    // View this extension
    // log access
    add_to_log($id, "extension", "view", "view.php?id=$id", '', $cm->id);

    /// Print the main part of the page
    echo $OUTPUT->box_start('center', '', '', 0, 'generalbox', 'dates');
    echo $cm->extension->view();
    echo $OUTPUT->box_end();

    echo $cm->extension->view_approval_form($context);

}

// Finish the page
echo $OUTPUT->footer();

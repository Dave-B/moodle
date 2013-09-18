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
 *
 * @package    mod
 * @subpackage extension
 * @author     David Balch <david.balch@conted.ox.ac.uk>
 * @copyright  2012 The University of Oxford
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/locallib.php');

$id = required_param('id', PARAM_INT);   // Course id
$a = optional_param('a', NULL, PARAM_INT);   // Optional activity filter
$status = optional_param('status', NULL, PARAM_INT);   // Optional extension status filter
$excludeStatus = optional_param('exclude', NULL, PARAM_INT);   // Optional extension status exclusion filter
$confirmed = optional_param('confirmed', NULL, PARAM_INT);   // Optional extension (un)confirmed filter
$u = optional_param('u', NULL, PARAM_INT);   // Optional user filter

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

require_course_login($course);
$context = get_context_instance(CONTEXT_COURSE, $id);

add_to_log($course->id, 'extension', 'view all', 'index.php?id='.$course->id, '');

$PAGE->set_url('/mod/extension/index.php', array('id' => $id));
$PAGE->set_title(format_string($course->fullname));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_pagelayout('standard');

if ($u || $a || isset($status) || isset($excludeStatus)) {
    $PAGE->navbar->add(get_string('extensions', 'extension'), new moodle_url('/mod/extension/index.php', array('id' => $course->id)));

    $filter_parts = array();
    if ($u) {
        $filter_parts[] = get_string('user');;
    }
    if ($a) {
        $filter_parts[] = get_string('activity');
    }
    if (isset($status)) {
        $filter_parts[] = '"'.$extension_requeststatus[$status].'"';
    }
    if (isset($excludeStatus)) {
        $filter_parts[] = get_string('not', 'extension').': "'.$extension_requeststatus[$excludeStatus].'"';
    }
    $label = get_string('filter', 'extension');
    $label .= implode(', ', $filter_parts);

    $PAGE->navbar->add($label);
} else {
    $PAGE->navbar->add(get_string('extensions', 'extension'));
}

echo $OUTPUT->header();
echo $OUTPUT->heading($course->fullname.': '.get_string('extensions', 'extension'));

/// Get strings
$strextensions = get_string('modulenameplural', 'extension');
$strextension  = get_string('modulename', 'extension');

/// Get all the appropriate data
if (has_capability('mod/extension:approveextension', $context) ||
	has_capability('mod/extension:extensionalert', $context) ||
	has_capability('mod/assignment:grade', $context)
) {
    // Can approve extensions
    // View specified user, or leave as NULL to see all
    $userid = $u ? $u : NULL;

} else if (has_capability('mod/extension:request', $context)) {
    // Can view own extensions
    $userid = $USER->id;
} else {
     // No permission to view the extension list
     print_error(get_string('nopermission', 'extension'));
}

$collection = new course_extension_collection($id, $a, $userid, $status, $confirmed);
//print_object($collection->assignments);

if (!$collection->assignments) {
    notice('There are no extensions matching.', "../../course/view.php?id=$course->id");
    die;
}

echo $OUTPUT->heading($strextensions);

echo $OUTPUT->box_start('boxaligncenter boxwidthnormal centerpara informationbox');
echo $collection->describe_filter($context);
echo $OUTPUT->box_end();
echo '<br/>';

/// Print the table of instances
$collection->view_table($context, $excludeStatus);

echo $OUTPUT->footer();

<?php // $Id: index.php,v 1.7.2.3 2009/08/31 22:00:00 mudrd8mz Exp $

/**
 * This page lists all the instances of extension in a particular course
 *
 * @author  David Balch <david.balch@conted.ox.ac.uk>
 * @version $Id: index.php,v 1.7.2.3 2009/08/31 22:00:00 mudrd8mz Exp $
 * @package mod/extension
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = required_param('id', PARAM_INT);   // Course id
$a = optional_param('a', NULL, PARAM_INT);   // Optional activity filter
$status = optional_param('status', NULL, PARAM_INT);   // Optional extension status filter
$excludeStatus = optional_param('exclude', NULL, PARAM_INT);   // Optional extension status exclusion filter
$confirmed = optional_param('confirmed', NULL, PARAM_INT);   // Optional extension (un)confirmed filter
$u = optional_param('u', NULL, PARAM_INT);   // Optional user filter

if (! $course = get_record('course', 'id', $id)) {
    error('Course ID is incorrect');
}

require_course_login($course);
$context = get_context_instance(CONTEXT_COURSE, $id);

add_to_log($course->id, 'extension', 'view all', "index.php?id=$course->id", '');


/// Get strings
$strextensions = get_string('modulenameplural', 'extension');
$strextension  = get_string('modulename', 'extension');

/// Get all the appropriate data
if (has_capability('mod/extension:viewanyextension', $context)) {
    // Can view any extensions
    // View specified user, or leave as NULL to see all
    $userid = $u ? $u : NULL;

} else if (has_capability('mod/extension:viewownextension', $context)) {
    // Can view own extensions
    $userid = $USER->id;
} else {
    $userid = null;
}

$collection = new course_extension_collection($id, $a, $userid, $status, $confirmed);
//print_object($collection);

if (!$collection->assignments) {
    notice('There are no extensions matching.', "../../course/view.php?id=$course->id");
    die;
}

/// Print the header
$navlinks = array();
$navlinks[] = array('name' => $strextensions, 'link' => '?id='.$course->id, 'type' => 'activity');
if($a) {
    $navlinks[] = array('name' => $collection->activityname, 'link' => '?id='.$course->id.'&amp;a='.$a, 'type' => 'activity');
}
if($confirmed === 0) {
    $navlinks[] = array('name' => get_string('unconfirmed', 'extension'), 'link' => '?id='.$course->id.'&amp;confirmed=0');
} else if ($confirmed === 1) {
    $navlinks[] = array('name' => get_string('confirmed', 'extension'), 'link' => '?id='.$course->id.'&amp;confirmed=1');
}
if($status !== NULL) {
    $navlinks[] = array('name' => $extension_requeststatus[$status], 'link' => '', 'type' => 'activity');
}

$navigation = build_navigation($navlinks);
print_header_simple($strextensions, '', $navigation, '', '', true, '', navmenu($course));

print_heading($strextensions);

print_box_start('boxaligncenter boxwidthnormal centerpara informationbox');
echo $collection->describe_filter($context);
print_box_end();
echo '<br/>';

/// Print the table of instances
$collection->view_table($context, $excludeStatus);

/// Finish the page
print_footer($course);

?>

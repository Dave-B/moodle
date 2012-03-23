<?php  // $Id: view.php,v 1.6.2.3 2009/04/17 22:06:25 skodak Exp $

/**
 * This page prints a particular instance of extension
 *
 * @author  David Balch <david.balch@conted.ox.ac.uk>
 * @version $Id: view.php,v 1.6.2.3 2009/04/17 22:06:25 skodak Exp $
 * @package mod/extension
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

require_once(dirname(__FILE__).'/lib.php');

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
$strextensions = get_string('modulenameplural', 'extension');
/// Print the page header
$navlinks = array();
$navlinks[] = array('name' => $strextensions, 'link' => "index.php?id=$cm->course", 'type' => 'activity');
$navlinks[] = array('name' => format_string($cm->name), 'link' => '', 'type' => 'activityinstance');

$navigation = build_navigation($navlinks);

print_header_simple(format_string($cm->name), '', $navigation, '', '', true);

$context = get_context_instance(CONTEXT_COURSE, $cm->course);

$can_viewanyextension = has_capability('mod/extension:viewanyextension', $context);
$can_grade = has_capability('mod/assignment:grade', $context);
$can_viewownextension = has_capability('mod/extension:viewownextension', $context);

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
    print_simple_box_start('center', '', '', 0, 'generalbox', 'dates');
	echo $cm->extension->view();
    print_simple_box_end();

	echo $cm->extension->view_approval_form($context);

}

/// Finish the page
print_footer($course);

?>

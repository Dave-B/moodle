<?php
/**
 * This page displays a student's activity extensions.
 *
 * @author  David Balch <david.balch@conted.ox.ac.uk>
 * @version $Id: view.php,v 1.6.2.3 2012/05/25 22:06:25 skodak Exp $
 * @package mod/extension
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot . '/my/lib.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once($CFG->libdir.'/tablelib.php');

$userid = optional_param('id', 0, PARAM_INT);
$edit   = optional_param('edit', null, PARAM_BOOL);    // Turn editing on and off

$userid = $userid ? $userid : $USER->id;       // Owner of the page
$user = $DB->get_record('user', array('id' => $userid));

$userfullname = fullname($user, true);
$strextensions = get_string('extensions', 'extension');

$navlinks = array();
$navlinks[] = array('name' => $userfullname,
                    'link' => "/user/profile.php?id=$userid",
                    'type' => 'user');
$navlinks[] = array('name' => $strextensions, 'link' => null, 'type' => 'misc');
$navigation = build_navigation($navlinks);

$PAGE->set_url('/mode/extension/userlist.php', array('id'=>$userid));

if (!empty($CFG->forceloginforprofiles)) {
    require_login();
    if (isguestuser()) {
        $SESSION->wantsurl = $PAGE->url->out(false);
        redirect(get_login_url());
    }
} else if (!empty($CFG->forcelogin)) {
    require_login();
}

if ($user->deleted) {
    $PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('userdeleted'));
    echo $OUTPUT->footer();
    die;
}

$currentuser = ($user->id == $USER->id);
$context = $usercontext = get_context_instance(CONTEXT_USER, $userid, MUST_EXIST);

if (!$currentuser &&
    !empty($CFG->forceloginforprofiles) &&
    !has_capability('moodle/user:viewdetails', $context) &&
    !has_coursecontact_role($userid)) {

    // Course managers can be browsed at site level. If not forceloginforprofiles, allow access (bug #4366)
    $struser = get_string('user');
    $PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));
    $PAGE->set_title($userfullname.': '.get_string('extensions', 'extension'));
    $PAGE->set_heading(format_string($COURSE->fullname));
    $PAGE->set_url('/mod/extension/userlist.php', array('id' => $userid, 'course' => $courseid));

//    $PAGE->set_title("$SITE->shortname: $struser");  // Do not leak the name
 //   $PAGE->set_heading("$SITE->shortname: $struser");
  //  $PAGE->set_url('/user/profile.php', array('id'=>$userid));
    $PAGE->navbar->add($struser.'xxx');
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('usernotavailable', 'error'));
    echo $OUTPUT->footer();
    exit;
}

// Get the profile page.  Should always return something unless the database is broken.
if (!$currentpage = my_get_page($userid, MY_PAGE_PUBLIC)) {
    print_error('mymoodlesetup');
}

if (!$currentpage->userid) {
    $context = get_context_instance(CONTEXT_SYSTEM);  // A trick so that we even see non-sticky blocks
}

$PAGE->set_context($context);
$PAGE->set_pagelayout('mydashboard');
$PAGE->set_pagetype('user-profile');

// Set up block editing capabilities
if (isguestuser()) {     // Guests can never edit their profile
    $USER->editing = $edit = 0;  // Just in case
    $PAGE->set_blocks_editing_capability('moodle/my:configsyspages');  // unlikely :)
} else {
    if ($currentuser) {
        $PAGE->set_blocks_editing_capability('moodle/user:manageownblocks');
    } else {
        $PAGE->set_blocks_editing_capability('moodle/user:manageblocks');
    }
}

// Start setting up the page
$PAGE->blocks->add_region('content');
$PAGE->set_subpage($currentpage->id);
$PAGE->set_title($userfullname.': '.get_string('extensions', 'extension'));
$PAGE->set_heading(format_string($COURSE->fullname));

if (!$currentuser) {
    $PAGE->navigation->extend_for_user($user);
    if ($node = $PAGE->settingsnav->get('userviewingsettings'.$user->id)) {
        $node->forceopen = true;
    }
} else if ($node = $PAGE->settingsnav->get('usercurrentsettings', navigation_node::TYPE_CONTAINER)) {
    $node->forceopen = true;
}
if ($node = $PAGE->settingsnav->get('root')) {
    $node->forceopen = false;
}

// HACK WARNING!  This loads up all this page's blocks in the system context
if ($currentpage->userid == 0) {
    $CFG->blockmanagerclass = 'my_syspage_block_manager';
}

// TODO WORK OUT WHERE THE NAV BAR IS!

echo $OUTPUT->header();
echo '<div class="userextensions">';


// Print the standard content of this page, the basic profile info

echo $OUTPUT->heading($strextensions);

// Get list of extensions for the specified user
$extensionlist = $DB->get_records('extension', array('userid' => $userid));
if(sizeof($extensionlist) < 1) {
    echo get_string('noextensionsfound', 'extension');
} else {
    foreach ($extensionlist as $key => $extension) {
        // Check current users's capabilities to view extensions on each course found. 
        $context = get_context_instance(CONTEXT_COURSE, $extension->course);

        if (has_capability('mod/extension:approveextension', $context)) {
            // Can view any extension for this course context
            $viewanyextension = true;
            // User can view this extension, so put it into an array grouped by course
            $extensionsbycourse[$extension->name][] = $extension;
        } else if ($userid == $USER->id && has_capability('mod/extension:request', $context)) {
            // Is current user, who can view own extensions
            // User can view this extension, so put it into an array grouped by course
            $extensionsbycourse[$extension->name][] = $extension;
        } else {
            // User can't view own or any extensions for this course, so remove extension from list
            unset($extensionlist[$key]);
        }
    }

    //print_object($extensionsbycourse);
    //echo sizeof($extensionsbycourse);
    if(sizeof($extensionsbycourse) < 1) {
        error("You do not have permission to view this information.");
    } else {
        // Show extensions grouped by course
        foreach ($extensionsbycourse as $name => $extensionlist) {
            echo $OUTPUT->heading($name, 3);
            doTable ($extensionlist);
        }
    }
}

function doTable ($extensionlist) {
    global $DB, $PAGE, $extension_requeststatus, $viewanyextension;
    // Set up table
    $tablecolumns = array('course', 'name', 'timecreated', 'lengthrequested', 'status', 'lengthgranted');
    $tableheaders = array(get_string('course'),
                          get_string('assignmentname', 'assignment'),
                          get_string('extensionsubmitted', 'extension'),
                          get_string('extensionrequested', 'extension'),
                          get_string('status'),
                          get_string('extensiongranted', 'extension')
                         );

    $table = new flexible_table('mod-extension-user-extensions');

    $table->define_columns($tablecolumns);
    $table->define_headers($tableheaders);
    $table->define_baseurl($PAGE->url);

    $table->pageable(true);
    // attributes in the table tag
    $table->set_attribute('cellspacing', '0');
    $table->set_attribute('id', 'attempts');
    $table->set_attribute('class', 'generaltable generalbox');
    $table->set_attribute('align', 'center');
    //$table->set_attribute('width', '50%');

    // get it ready!
    $table->setup();

    $courses = array();
    foreach ($extensionlist as $extension) {
        $a = array(); // Convert Object to Array
        foreach ($extension as $key => $val) {
            if ($key == 'course') {
                if (!isset($courses[$val])) {
                    $courses[$val] = $DB->get_record('course', array('id' => $val));
                }
                $val = '<a href="/course/view.php?id='.$val.'">'.$courses[$val]->fullname.'</a>';
            }
            else if ($key == 'name') {
                $val = '<a href="/mod/assignment/view.php?id='.$extension->activitycmid.'">'.$val.'</a>';
            }
            else if ($key == 'timecreated') {
                $val = userdate($val, get_string('strftimedatetimeshort'));
            }
            else if ($key == 'status') {
                if($courses[$extension->course]->registryworkflow) {
                    // Workflow = modify status depending on who's viewing
                    if ($viewanyextension) {
                        // Admin user
                        $val = '<a href="/mod/extension/view.php?id='.$extension->id.'">'.
                                $extension_requeststatus[$val].'</a>';
                        if($extension->approvalconfirmed) {
                            $val .= ' ('.get_string('confirmed', 'extension').')';
                        } else if ($extension->status != 0) {
                            $val .= ' ('.get_string('awaitingconfirmation', 'extension').')';
                        }
                    } else {
                        // Non-admin user
                        if ($extension->approvalconfirmed) {
                            $val = '<a href="/mod/extension/view.php?id='.$extension->id.'">'.
                                    $extension_requeststatus[$val].'</a>';
                        } else {
                            $val = '<a href="/mod/extension/view.php?id='.$extension->id.'">'.
                                    $extension_requeststatus[0].'</a>';
                        }
                    }
                } else {
                    // No workflow = display status as is
                    $val = '<a href="/mod/extension/view.php?id='.$extension->id.'">'.
                            $extension_requeststatus[$val].'</a>';
                }
            }
            else if ($key == 'lengthgranted') {
                if($courses[$extension->course]->registryworkflow && !$extension->approvalconfirmed) {
                    $val = '-';
                } else {
                    $val = $val;
                }
            }

            $a[$key] = $val;
        }
        $table->add_data_keyed($a);
    }
    //print_object($courses);

    $table->print_html();
}



echo $OUTPUT->blocks_for_region('content');


if ($CFG->debugdisplay && debugging('', DEBUG_DEVELOPER) && $currentuser) {  // Show user object
    echo '<br /><br /><hr />';
    echo $OUTPUT->heading('DEBUG MODE:  User session variables');
    print_object($USER);
}

echo '</div>';  // userextensions class
echo $OUTPUT->footer();


function print_row($left, $right) {
    echo "\n<tr><td class=\"label c0\">$left</td><td class=\"info c1\">$right</td></tr>\n";
}

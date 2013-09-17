<?php  // $Id: view.php,v 1.6.2.3 2009/04/17 22:06:25 skodak Exp $

/**
 * This page allows the addition of a time extension to an activity
 *
 * @author  David Balch <david.balch@conted.ox.ac.uk>
 * @version $Id: view.php,v 1.6.2.3 2009/04/17 22:06:25 skodak Exp $
 * @package mod/extension
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$courseid = optional_param('course', 0, PARAM_INT);  // Course ID
$type = optional_param('type', 0, PARAM_ALPHA);  // Module type
$id = optional_param('id', 0, PARAM_INT);  // Course Module ID

if ($id) {
    if (! $cm = get_coursemodule_from_id($type, $id)) {
        print_error("Course Module ID was incorrect");
    }

    if (! $assignment = $DB->get_record($type, array("id" => $cm->instance))) {
        print_error("Activity ID was incorrect");
    }

} else {
    print_error('You must specify an activity ID');
}

require_login($cm->course, true, $cm);
//require_capability('mod/extension:request', get_context_instance(CONTEXT_COURSE, $cm->id));

$PAGE->set_url('/mod/extension/add.php', array('course' => $courseid, 'type' => $type, 'id' => $id));
$PAGE->set_title(format_string($assignment->name).': '.get_string('requestextension', 'extension'));
$PAGE->set_heading(format_string($COURSE->fullname));
$PAGE->navbar->add(get_string('requestextension', 'extension'));

echo $OUTPUT->header();

// log access
add_to_log($courseid, "extension", "add", "add.php?course=$courseid&type=$type&id=$id", '', $cm->id, $USER->id);

/// Print the main part of the page
echo $OUTPUT->heading($cm->name.': '.get_string('requestextension', 'extension'));

//print_object($cm);
//print_object($assignment);
//print_object($USER);
//print_object($COURSE);

echo $OUTPUT->box_start('center', '', '', 0, 'generalbox', 'dates');
echo '<table>';
if ($assignment->timeavailable) {
    echo '<tr><td class="c0">'.get_string('availabledate','assignment').':</td>';
    echo '    <td class="c1">'.userdate($assignment->timeavailable).'</td></tr>';
}
if ($assignment->timedue) {
    echo '<tr><td class="c0">'.get_string('duedate','assignment').':</td>';
    echo '    <td class="c1">'.userdate($assignment->timedue);
    echo '</td></tr>';
}
echo '</table>';
echo $OUTPUT->box_end();

include_once(dirname(dirname(dirname(__FILE__))).'/lib/formslib.php');
include_once('mod_form.php');
$mform = new mod_extension_form();

if ($mform->is_cancelled()){
    //you need this section if you have a cancel button on your form
    redirect("$CFG->wwwroot/mod/assignment/view.php?id=$cm->id", get_string('cancelled'));
} else if ($fromform=$mform->get_data()){
    // This branch is where you process validated data.
    // Log submission
    add_to_log($cm->course, 'extension', 'add', "add.php?course=$cm->course&type=$type&id=$cm->id", '');
    $context = get_context_instance(CONTEXT_COURSE, $COURSE->id);

    // Save request
    $fromform->course = $assignment->course;
    $fromform->timecreated = time();
    $fromform->userid = $USER->id;
    $fromform->activitycmid = $cm->id;
    if(isset($_FILES['evidenceupload']['name'])) {
        // Save any uploaded file
        // TODO: Replace with a funciton in the Extension object
        $dest = $assignment->course.'/'.$CFG->moddata.'/extension/'.$fromform->activityid.'/'.$USER->id;
        $mform->save_files($dest);
        $fromform->evidencefile = clean_filename($_FILES['evidenceupload']['name']);
    }
    //print_object($fromform);

    $extensionid = $DB->insert_record('extension', $fromform, true);
    if(!$extensionid){
        print_error(get_string('inserterror' , 'extension'));
    } else {
        // Notify staff that extension requested
        $student = $DB->get_record('user', array('id' => $fromform->userid));
        $studentname = $student->firstname.' '.$student->lastname;
        $from = $SITE->fullname.': '.get_string('modulenameplural', 'extension');

        $messagedata = new object();
        $messagedata->studentname = $studentname;
        $messagedata->coursename = $COURSE->fullname;
        $messagedata->assignmentname = $assignment->name;
        $messagedata->extensionurl = $CFG->wwwroot.'/mod/extension/index.php?id='.$cm->course.'&a='.$cm->id;
        $messagedata->siteurl = $CFG->wwwroot.'/';

        $subject = get_string('newextensionrequest', 'extension').': '.$assignment->name.', '.$studentname;

        // Who ya gonna call?
        $userstoexclude = get_admins(); // We'll exclude admins from these emails.

        if ($COURSE->registryworkflow) {
            $userstoalert = get_extension_users_by_role($cm, 'mod/extension:extensionalert', $USER, $userstoexclude);
            //echo "userstoalert:<br/>";
            //print_object($userstoalert);
            foreach ($userstoalert as $key => $user) {

                // Alert via email that an extension was requested
                $messagedata->username = $user->firstname.' '.$user->lastname;
                $messagedata->extensionurl = $CFG->wwwroot.'/mod/extension/view.php?id='.$extensionid;

                $messagetext = get_string('newextensionnotification_workflow', 'extension', $messagedata);
                email_to_user($user, $from, $subject, $messagetext, '', '', false);
            }
        }

        $approvers = get_extension_users_by_role($cm, 'mod/extension:approveextension', $USER, $userstoexclude);
        // Notify Extension approvers (Course directors) that an extension was requested
        foreach ($approvers as $approver) {
            $messagedata->approvername = $approver->firstname.' '.$approver->lastname;
            $messagedata->extensionurl = $CFG->wwwroot.'/mod/extension/view.php?id='.$extensionid;
            $messagedata->extensionlisturl = $CFG->wwwroot.'/mod/extension/index.php?id='.$cm->course.'&a='.$cm->id;

            if ($COURSE->registryworkflow) {
                $messagetext = get_string('approvenewextensionnotification_workflow', 'extension', $messagedata);
            } else {
                $messagetext = get_string('approvernewextensionmessage', 'extension', $messagedata);
            }
            email_to_user($approver, $from, $subject, $messagetext, '', '', false);
        }

        // Redirect page
        echo $OUTPUT->box(get_string('extensionsubmitted', 'extension'));
        echo $OUTPUT->continue_button(new moodle_url($CFG->wwwroot.'/mod/assignment/view.php', array('id' => $cm->id)));
    }

} else {
    // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
    // or on the first display of the form. 
    // Put data you want to fill out in the form into array $toform here then :
    $toform = array();
 
    $mform->set_data($toform);
    $mform->display();
 
}

/// Finish the page
echo $OUTPUT->footer();
?>

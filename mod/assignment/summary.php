<?php  // $Id$

    require_once("../../config.php");
    require_once("lib.php");

    $id   = optional_param('id', 0, PARAM_INT);          // Course module ID
    $userid = required_param('userid', PARAM_INT);

    if ($id) {
        if (! $cm = get_coursemodule_from_id('assignment', $id)) {
            print_error("Course Module ID was incorrect");
        }

        if (! $assignment = $DB->get_record("assignment", array("id" => $cm->instance))) {
            print_error("assignment ID was incorrect");
        }

        if (! $course = $DB->get_record("course", array("id" => $assignment->course))) {
            print_error("Course is misconfigured");
        }
    }

    require_login($course->id, false, $cm);

    require_capability('mod/assignment:grade', get_context_instance(CONTEXT_MODULE, $cm->id));

/// Load up the required assignment code
    require($CFG->dirroot.'/mod/assignment/type/'.$assignment->assignmenttype.'/assignment.class.php');
    $assignmentclass = 'assignment_'.$assignment->assignmenttype;
    $assignmentinstance = new $assignmentclass($cm->id, $assignment, $cm, $course);
    //print_object($assignmentinstance);

    if (!$user = $DB->get_record('user', array("id" => $userid))) {
        print_error('No such user!');
    }
    if (!$submission = $assignmentinstance->get_submission($user->id)) {
        $submission = $assignmentinstance->prepare_new_submission($userid);
    }

    // Output user's assignment summary as an HTML attachment
    header('Content-Type: text/html; charset utf-8;');
    header('Content-Disposition: attachment;filename="details_'.$user->username.'.html";');
    echo $assignmentinstance->get_html_summary($user, $submission);

?>

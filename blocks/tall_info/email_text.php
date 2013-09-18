<?php // $Id$

// TALL information page

require_once('../../config.php');
$course = required_param('id', PARAM_INT);

require_login($course);
require_capability('moodle/course:viewhiddenactivities', $PAGE->context);

$PAGE->set_url('/blocks/tall_info/block_tall_info.php');
$PAGE->set_title(get_string('pluginname', 'block_tall_info'));
$PAGE->set_heading(get_string('pluginname', 'block_tall_info'));
$PAGE->set_pagelayout('standard');

function messages_to_html($messages, $module) {
    $html = "<dl>";

    foreach ($messages as $key => $label) {
        $html .= '<dt><a href="#" onclick="toggle(\''.$key.'\'); return false;">'.$label.'</a>:</dt>';
        $html .= "<dd id=\"$key\"><pre>".get_string($key, $module)."</pre></dd>";
    }

    $html .= "</dl>";
    return $html;
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('emailnotificationtext', 'block_tall_info'), 1);
echo "<p>Click a link to show the email text sent for that event.</p>";
echo "<p>The message shown is the template used for the actual messages, so this page will always be correct for this server.</p>";

echo '<style type="text/css">dt {margin-top: 1em; font-weight: bold;} dd {display: none; overflow: auto; border: 1px solid silver; margin: 1em; padding: .3em; font-size: .8em;}</style>';
echo '<script lank="text/javascript">
function toggle (id) {
    if (document.getElementById(id).style.display == "none" || document.getElementById(id).style.display == "") {
        document.getElementById(id).style.display = "block";
    } else {
        document.getElementById(id).style.display = "none";
    }
}
</script>';

// Extensions
echo $OUTPUT->heading(get_string('modulenameplural', 'extension'), 2);
$messages_extension = array(
    'approvernewextensionmessage' => 'When: New extension | Send to: Approver (without registry workflow = Tutor)',
    'approvenewextensionnotification_workflow' => 'When: New extension | Send to: Approver (with registry workflow = Registry)',
    'newextensionnotification_workflow' => 'When: New extension | Send to: Course Director',
    'extensiondecisionalertmessage' => 'When: Extension approved/rejected | Send to: Course Director',
    'studentextensiondecisionmessage' => 'When: Extension approved/rejected | Send to: Student'
);
echo messages_to_html($messages_extension, 'extension');

echo '<hr/>';


// Assignments
echo $OUTPUT->heading(get_string('modulenameplural', 'assignment'), 2);
$messages_assignment = array(
    'emailstudentmail' => 'When: Assignment submitted | Send to: Student',
    'emailstudentmailhtml' => 'When: Assignment submitted | Send to: Student | (HTML version)',
    'emailteachermail' => 'When: Assignment submitted | Send to: Tutor',
    'emailteachermailhtml' => 'When: Assignment submitted | Send to: Tutor | (HTML version)',
    'confirmerprovisionalgrademessage' => 'When: Assignment graded | Sent to: Registry',
    'provisionalgradeconfirmedmessage' => 'When: Assignment grade confirmed | Sent to: Tutor',
    'assignmentmail' => 'When: Assignment graded (without registry workflow) | Send to: Student',
    'assignmentmailhtml' => 'When: Assignment graded (without registry workflow) | Send to: Student | (HTML version)',
    'assignmentmailregistry' => 'When: Assignment graded (with registry workflow) | Send to: Student',
    'assignmentmailregistryhtml' => 'When: Assignment graded (with registry workflow) | Send to: Student (HTML version)',
    //'assignmentmailsmall' => 'When: Assignment graded (????) | Send to: Student',
);
echo messages_to_html($messages_assignment, 'assignment');


echo $OUTPUT->footer();

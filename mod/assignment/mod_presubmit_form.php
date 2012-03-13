<?php
require_once("$CFG->libdir/formslib.php");

class mod_presubmit_form extends moodleform {

    function definition() {
        global $CFG, $USER, $assignment;

        $mform =& $this->_form;

        // Include posted options
        $mform->addElement('hidden', 'id', $_POST['id']);
        $mform->addElement('hidden', 'action', $_POST['action']);

        // Ask extra questions
        if ($assignment->requiredeclaration && !empty($CFG->assignment_uploadtext)) {
            // Student statement
            $mform->addElement('header', 'displayinfo', get_string('confirmstatement', 'assignment'));
            $mform->addElement('html', format_text('<div class="boxaligncenter boxwidthwide">'.sprintf($CFG->assignment_uploadtext, $USER->firstname.' '.$USER->lastname).'</div>'));
            $mform->addElement('checkbox', 'confirmuploadtext', get_string('readconfirm','assignment'));
            $mform->addRule('confirmuploadtext', null, 'required', null, 'client');
        }

        if($assignment->requirewordcount){
            // Word count options
            $mform->addElement('header', 'typedesc', get_string('wordcount', 'assignment'));
            $mform->addElement('text', 'wordcount', get_string('wordcount', 'assignment'), array('size'=>'10'));

            $mform->addRule('wordcount', null, 'required', null, 'client');

            $mform->registerRule('positivenum','regex', '/^[\d]+$/');
            $mform->addRule('wordcount', get_string('positivenumber', 'assignment'), 'positivenum', null, 'client');
        }

        // Buttons
        $this->add_action_buttons(true, get_string('sendformarking', 'assignment'));
    }

}
?>

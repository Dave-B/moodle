<?php //$Id: mod_form.php,v 1.2.2.3 2009/03/19 12:23:11 mudrd8mz Exp $

/**
 * This file defines the main extension configuration form
 * It uses the standard core Moodle (>1.8) formslib. For
 * more info about them, please visit:
 *
 * http://docs.moodle.org/en/Development:lib/formslib.php
 *
 */

require_once($CFG->dirroot.'/lib/formslib.php');

class mod_extension_approval_form extends moodleform {

    // Allow access to the notify student checkbox, so we can freeze it if
    // user hasn't permission to change it.
    private $notifyElement;
    public function getNotifyElement() {
        return $this->notifyElement;
    }

    function definition() {
        global $COURSE, $USER, $cm, $extension_requeststatus;
        $mform =& $this->_form;

    /// State
        $mform->addElement('hidden', 'id', $cm->extension->id);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'actid', $cm->id);
        $mform->setType('actid', PARAM_INT);
        $mform->addElement('hidden', 'courseid', $cm->course);
        $mform->setType('courseid', PARAM_INT);

    /// Status
        if($cm->extension->approvalconfirmed == 1) {
            $mform->addElement('hidden', 'status', 0);
            $mform->setType('status', PARAM_INT);
            $mform->addElement('html',
                '<div class="fitem"><div class="fitemtitle">'.
                      get_string('status', 'moodle').
                      '</div><div class="felement ftextarea">'.
                      $extension_requeststatus[$cm->extension->status].
                      '</div></div>'
                );
        } else {
            $mform->addElement('select', 'status', get_string('status', 'moodle'), $extension_requeststatus);
        }

    /// Extension to grant
        if($cm->extension->approvalconfirmed == 1) {
            $granted = '<div class="fitem"><div class="fitemtitle">'.
                      get_string('grantextension', 'extension').' ('.
                      get_string($cm->assignment->extensionunits, 'extension').')'.
                      '</div><div class="felement ftextarea">'.$cm->extension->lengthgranted.'</div></div>';
            $mform->addElement('html', $granted);
        } else {
            $range = range(1, max($cm->assignment->maxextensionstandard, $cm->assignment->maxextensionexceptional));
            $mform->addElement('select', 'lengthgranted',
                get_string('grantextension', 'extension').' ('.get_string($cm->assignment->extensionunits, 'extension').')',
                array_combine($range, $range));
            $mform->setType('lengthgranted', PARAM_INT, 'nonzero');
            $mform->addRule('lengthgranted', get_string('maximumchars', '', 3), 'maxlength', 3, 'client', 'x');
        }

    /// Private notes
        if($cm->extension->privatenotes) {
            // Show existing notes
            $notes = '<div class="fitem"><div class="fitemtitle">'.
                      get_string('privatenotesexisting', 'extension').
                      '</div><div class="felement ftextarea existingprivatenotes">'.$cm->extension->privatenotes.'</div></div>';
            $mform->addElement('html', $notes);
            
            // Include existing notes
            $mform->addElement('hidden', 'existingprivatenotes', $cm->extension->privatenotes);
            $mform->setType('existingprivatenotes', PARAM_RAW);
        }
        // Add a new note
        $mform->addElement('textarea', 'privatenotes', get_string('privatenotes', 'extension'), 'wrap="virtual" rows="10" cols="70"');

    /// Message to student
        if($cm->extension->approvalconfirmed == 1) {
            $notified = '<div class="fitem"><div class="fitemtitle">'.
                      get_string('feedback').
                      '</div><div class="felement ftextarea">'.$cm->extension->studentmessage.'</div></div>';
            $mform->addElement('html', $notified);
        } else {
            $mform->addElement('textarea', 'studentmessage', get_string('feedback'), 'wrap="virtual" rows="10" cols="70"');
        }

    /// Notify student
        if($cm->extension->approvalconfirmed == 1) {
            $notified = '<div class="fitem"><div class="fitemtitle">'.
                      get_string('studentnotified', 'extension').
                      '</div><div class="felement ftextarea">'.
                      userdate($cm->extension->timeconfirmed, get_string('strftimedatetime')).
                      '</div></div>';
            $mform->addElement('html', $notified);
        } else if ($COURSE->registryworkflow) {
            $this->notifyElement = $mform->addElement('checkbox', 'approvalconfirmed', get_string('notifystudent', 'extension'), get_string('notifystudentguidance', 'extension'));
        } else {
            $mform->addElement('checkbox', 'approvalconfirmed', get_string('notifystudent', 'extension'));
        }

    /// Defaults
        $length = $cm->extension->lengthgranted == 0 ? $cm->extension->lengthrequested : $cm->extension->lengthgranted;
        $mform->setDefaults(array(
            'status'=>$cm->extension->status,
            'lengthgranted'=>$length,
            'studentmessage'=>$cm->extension->studentmessage,
            'approvalconfirmed'=>1
        ));

    /// Only allow student notification if status isn't pending
        $mform->disabledIf('approvalconfirmed', 'status', 'eq', 0);


//-------------------------------------------------------------------------------
        // add standard elements, common to all modules
//        $this->standard_coursemodule_elements();
//-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons();

    }
}

?>

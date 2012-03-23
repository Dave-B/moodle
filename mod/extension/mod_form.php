<?php //$Id: mod_form.php,v 1.2.2.3 2009/03/19 12:23:11 mudrd8mz Exp $

/**
 * This file defines the main extension configuration form
 * It uses the standard core Moodle (>1.8) formslib. For
 * more info about them, please visit:
 *
 * http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * The form must provide support for, at least these fields:
 *   - name: text element of 64cc max
 *
 * Also, it's usual to use these fields:
 *   - intro: one htmlarea element to describe the activity
 *            (will be showed in the list of activities of
 *             extension type (index.php) and in the header
 *             of the extension main page (view.php).
 *   - introformat: The format used to write the contents
 *             of the intro field. It automatically defaults
 *             to HTML when the htmleditor is used and can be
 *             manually selected if the htmleditor is not used
 *             (standard formats are: MOODLE, HTML, PLAIN, MARKDOWN)
 *             See lib/weblib.php Constants and the format_text()
 *             function for more info
 */

require_once($CFG->dirroot.'/lib/formslib.php');
require_once("../extension/lib.php");

class mod_extension_form extends moodleform {

    function definition() {

        global $COURSE, $USER, $cm, $assignment;
        $mform =& $this->_form;
    //print_object($mform->getRegisteredRules());
    //print_object($assignment);

    /// State
        $type = optional_param('type', 0, PARAM_ALPHA);   // Activity type

        $mform->addElement('hidden', 'id', $cm->id);
        $mform->addElement('hidden', 'type', $type);
        $mform->addElement('hidden', 'activityid', $assignment->id);

    /// Name
        $mform->addElement('hidden', 'name', $cm->name);

    /// Adding the field for extension length
        // Max ext length        
        $range = range(1, max($assignment->maxextensionstandard, $assignment->maxextensionexceptional));

        $mform->addElement('select', 'lengthrequested',
            get_string('extensionlength', 'extension').' ('.get_string($assignment->extensionunits, 'extension').')',
            array_combine($range, $range));
        $mform->setType('lengthrequested', PARAM_INT, 'nonzero');
        $mform->addRule('lengthrequested', null, 'required', null, 'client');
        $mform->addRule('lengthrequested', get_string('maximumchars', '', 3), 'maxlength', 3, 'client', 'x');
        $mform->addElement('html', '<div class="fitem"><div class="fitemtitle"> </div><div class="felement">'.get_string('extensionlengthguidance', 'extension').'</div></div.');

    /// Reason for the extension
        $mform->addElement('textarea', 'reason', get_string('reasonforrequest', 'extension'), 'wrap="virtual" rows="10" cols="70"');
        $mform->addRule('reason', null, 'required', null, 'client');

    /// Optional file upload to support the request
        $mform->addElement('file', 'evidenceupload', get_string('supportingevidence', 'extension'));
        $mform->addElement('html', '<div class="fitem"><div class="fitemtitle"> </div><div class="felement">'.get_string('supportingevidenceguidance', 'extension').'</div></div.');


//-------------------------------------------------------------------------------
        // add standard elements, common to all modules
//        $this->standard_coursemodule_elements();
//-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons(true, get_string('submitrequest', 'extension'));

    }
}

?>

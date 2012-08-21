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
 * The main extension configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod
 * @subpackage extension
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once("../extension/lib.php");

/**
 * Module instance settings form
 */

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
        if ($COURSE->registryworkflow) {
            /// Instruct users to contact Registry for long extensions
            $mform->addElement('html', '<div class="fitem"><div class="fitemtitle"> </div><div class="felement">'.get_string('extensionlengthguidance', 'extension').'</div></div.');
        }

    /// Reason for the extension
        $mform->addElement('textarea', 'reason', get_string('reasonforrequest', 'extension'), 'wrap="virtual" rows="10" cols="70"');
        $mform->addRule('reason', null, 'required', null, 'client');

        if ($COURSE->registryworkflow) {
            /// Instruct users to send evidence files to Registry
            $info = new StdClass();
            $info->coursename = htmlentities($COURSE->fullname);
            $info->assignmentname = htmlentities($assignment->name);
            $info->username = htmlentities($USER->firstname.' '.$USER->lastname);
            $mform->addElement('html', '<div class="fitem"><div class="fitemtitle"> </div><div class="felement">'.
                                        get_string('supportingevidenceguidance', 'extension', $info).
                                        '</div></div.');
        }

//-------------------------------------------------------------------------------
        // add standard elements, common to all modules
//        $this->standard_coursemodule_elements();
//-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons(true, get_string('submitrequest', 'extension'));

    }
}

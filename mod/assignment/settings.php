<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot.'/mod/assignment/lib.php');

    if (isset($CFG->maxbytes)) {
        $maxbytes = 0;
        if (isset($CFG->assignment_maxbytes)) {
            $maxbytes = $CFG->assignment_maxbytes;
        }
        $settings->add(new admin_setting_configselect('assignment_maxbytes', get_string('maximumsize', 'assignment'),
                           get_string('configmaxbytes', 'assignment'), 1048576, get_max_upload_sizes($CFG->maxbytes, 0, 0, $maxbytes)));
    }

    $options = array(ASSIGNMENT_COUNT_WORDS   => trim(get_string('numwords', '', '?')),
                     ASSIGNMENT_COUNT_LETTERS => trim(get_string('numletters', '', '?')));
    $settings->add(new admin_setting_configselect('assignment_itemstocount', get_string('itemstocount', 'assignment'),
                       get_string('configitemstocount', 'assignment'), ASSIGNMENT_COUNT_WORDS, $options));

    $settings->add(new admin_setting_configcheckbox('assignment_showrecentsubmissions', get_string('showrecentsubmissions', 'assignment'),
                       get_string('configshowrecentsubmissions', 'assignment'), 1));

    $settings->add(new admin_setting_configtextarea('assignment_uploadtext', get_string('uploadtext','assignment'),
                       get_string('configuploadtext','assignment'),
                       '<p>I, %s, declare that:</p>
<ul><li>I am aware of the University\'s disciplinary regulations concerning conduct in examinations pertaining to submission of assignments and long essays and, in particular, of the regulations on plagiarism.</li>
<li>The work I am submitting is entirely my own work except where otherwise indicated.</li>
<li>It has not been submitted, either wholly or substantially, for another course of this Department or University, or for a course at any other institution.</li>
<li>I have clearly signalled the presence of quoted or paraphrased material and referenced all sources.</li>
<li>I have acknowledged appropriately any assistance I have received in addition to that provided by my tutor.</li>
<li>I have not sought assistance from any professional agency.</li>
<li>I agree to make available any electronic version of the work on request from the Chair of Examiners should this be required in order to confirm my word count or to check for plagiarism.</li></ul>' ));
}

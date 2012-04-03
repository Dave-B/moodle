<?php

/**
 * This is the TALL Extensions block - providing a summary of extensions
 * on a course (requested, approved, and rejected), and links to view
 * the extension details.
 *
 * @package    block
 * @subpackage extension
 * @copyright  2012 University of Oxford
 *
*/

class block_extension extends block_base {

    function init() {
        $this->title = get_string('extensions', 'block_extension');
        $this->version = 2012032300;
    }

    // only one instance of this block is required
    function instance_allow_multiple() {
        return false;
    }

    // label and button values can be set in admin
    function has_config() {
        return false;
    }

    function get_content() {
        global $DB, $COURSE, $USER;
        if ($this->content !== NULL) {
          return $this->content;
        }
     
        $this->content = new stdClass;
        // Look for any extensions in the current course
        $extensions = array();
        $extensions[0]['name'] = get_string('pending', 'extension');
        $extensions[1]['name'] = get_string('approved', 'extension');
        $extensions[2]['name'] = get_string('rejected', 'extension');

        /// Get all the appropriate data
        $context = get_context_instance(CONTEXT_COURSE, $COURSE->id);

        if (has_capability('mod/extension:viewanyextension', $context)
             || has_capability('mod/extension:approveextension', $context)
             || has_capability('mod/extension:confirmextension', $context)
             || has_capability('mod/assignment:grade', $context)) {

            // Can view any extensions, or is a tutor, so separate confirmed/unconfirmed as well
            $extensions[3]['name'] = get_string('awaitingconfirmation', 'extension');
            $whostext = get_string('allcourseextensions', 'extension');

            // All pending extensions should be shown as pending to staff
            $extensions[0]['count'] = $DB->count_records('extension', array('course' => $COURSE->id, 'status' => 0, 'approvalconfirmed' => 0));
            //$extensions[1]['count'] = count_records('extension', 'course', $COURSE->id, 'status', 1, 'approvalconfirmed', 1, 'userid', $USER->id); // Odd bug seems to report "4" when it should be "0" 2010-02-15
            $extensions[1]['count'] = $DB->count_records_select('extension', "course = $COURSE->id AND status = 1 AND approvalconfirmed = 1");
            $extensions[2]['count'] = $DB->count_records('extension', array('course' => $COURSE->id, 'status' => 2, 'approvalconfirmed' => 1));
            // Extensions awaiting confirmation - these will be shown to the student as "Pending".
            $extensions[3]['count'] = $DB->count_records_select('extension', "course = $COURSE->id AND status <> 0 AND approvalconfirmed = 0");
            $totalextensions = $extensions[0]['count'] + $extensions[1]['count'] + $extensions[2]['count'] + $extensions[3]['count'];

        } else if (has_capability('mod/extension:viewownextension', $context)) {
            // Can view own extensions (student)
            $whostext = get_string('yourextensions', 'extension');

            $pendingcount = $DB->count_records_select('extension', "course =  $COURSE->id AND status = 0 AND userid = $USER->id");
            $unconfirmedcount = $DB->count_records_select('extension', "course = $COURSE->id AND approvalconfirmed = 0 AND status != 0 AND userid = $USER->id");
            $extensions[0]['count'] = $pendingcount + $unconfirmedcount;

            $extensions[1]['count'] = $DB->count_records_select('extension', "course = $COURSE->id AND status = 1 AND approvalconfirmed = 1 AND userid = $USER->id");
            $extensions[2]['count'] = $DB->count_records_select('extension', "course = $COURSE->id AND status = 2 AND approvalconfirmed = 1 AND userid = $USER->id");
            $totalextensions = $extensions[0]['count'] + $extensions[1]['count'] + $extensions[2]['count'];

        }

        if ($totalextensions < 1) {
            $this->content->text = '<div class="blocksubtitle">'.$whostext.':</div> <p>'.get_string('noextensions', 'block_extension').'</p>';
        } else {
            $items = '';
            foreach($extensions as $key => $group) {
                if($group['count'] > 0) {
                    if($key == 3) {
                        // Any unconfirmed requests
                        // TODO: filter out pending requests
                        $filter = '&amp;confirmed=0&amp;exclude=0';
                    } else if( $key == 0) {
                        // All pending requests
                        $filter = '&amp;status='.$key;
                    } else{
                        // All confirmed requests
                        $filter = '&amp;confirmed=1&amp;status='.$key;
                    }
                    $items .= '<li><a href="/mod/extension/index.php?id='.$COURSE->id.$filter.'">'.$group['name'].' ('.$group['count'].')</a></li>';
                } else {
                    $items .= '<li>'.$group['name'].' ('.$group['count'].')</li>';
                }
            }
            $this->content->text = '<div class="blocksubtitle">'.$whostext.':</div> <ul class="list">'.$items.'</ul>';
        }
        $this->content->footer = '';
     
        return $this->content;
    }
}
?>

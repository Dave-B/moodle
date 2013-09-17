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
        $this->version = 2013091700;
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

        if (has_capability('mod/extension:approveextension', $context)
             || has_capability('mod/assignment:grade', $context)) {

            // Can approve extensions, so view all extensions in this course
            $whostext = get_string('allcourseextensions', 'extension');

            // All pending extensions should be shown as pending to staff
            $extensions[0]['count'] = $DB->count_records('extension', array('course' => $COURSE->id, 'status' => 0));
            $extensions[1]['count'] = $DB->count_records('extension', array('course' => $COURSE->id, 'status' => 1));
            $extensions[2]['count'] = $DB->count_records('extension', array('course' => $COURSE->id, 'status' => 2));
            $totalextensions = $extensions[0]['count'] + $extensions[1]['count'] + $extensions[2]['count'];

        } else if (has_capability('mod/extension:request', $context)) {
            // Can view own extensions (student)
            $whostext = get_string('yourextensions', 'extension');

            $pendingcount = $DB->count_records_select('extension', "course =  $COURSE->id AND status = 0 AND userid = $USER->id");
            $unconfirmedcount = $DB->count_records_select('extension', "course = $COURSE->id AND approvalconfirmed = 0 AND status != 0 AND userid = $USER->id");
            $extensions[0]['count'] = $pendingcount + $unconfirmedcount;

            $extensions[1]['count'] = $DB->count_records_select('extension', "course = $COURSE->id AND status = 1 AND userid = $USER->id");
            $extensions[2]['count'] = $DB->count_records_select('extension', "course = $COURSE->id AND status = 2 AND userid = $USER->id");
            $totalextensions = $extensions[0]['count'] + $extensions[1]['count'] + $extensions[2]['count'];
$totalextensions = 50;
        }

        if ($totalextensions < 1) {
            $this->content->text = '<div class="blocksubtitle">'.$whostext.':</div> <p>'.get_string('noextensions', 'block_extension').'</p>';
        } else {
            $items = '';
            foreach($extensions as $key => $group) {
                if($group['count'] > 0) {
                    if ( $key == 0) {
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

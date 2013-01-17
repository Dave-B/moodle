<?php

/**
 * This is the TALL information block - providing easy access to
 * admin-type information.
 *
 * Version information
 *
 * @package    block
 * @copyright  2013 University of Oxford
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

class block_tall_info extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_tall_info');
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
        global $PAGE, $COURSE;
        $canviewhidden = has_capability('moodle/course:viewhiddenactivities', $PAGE->context);
        if (!$canviewhidden) {
            $this->content = "";
        } else {
            //cache block contents
            if ($this->content !== NULL) {
                return $this->content;
            }

            $this->content = new stdClass;
            $this->content->text =
                  '<div style="font-size: 0.9em"><ul>'
                . '<li><a href="/blocks/tall_info/email_text.php?id='.$COURSE->id.'">'.get_string('emailnotificationtext', 'block_tall_info').'</a></li>'
                . '</ul></div>';

            //no footer, thanks
            $this->content->footer = '';
        }

      return $this->content;
    }
}
?>

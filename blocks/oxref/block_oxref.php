<?php

/**
 * This is the Oxford Reference Online block - providing an intoduction to the
 * service, and a link to the lauch page.
 *
 * Version information
 *
 * @package    block
 * @subpackage extension
 * @copyright  2012 University of Oxford
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

class block_oxref extends block_base {

    function init() {
        $this->title = get_string('oro', 'block_oxref');
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
        //cache block contents
        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = <<<EOT
<div style="font-size: 0.9em">
<p>Studying this course entitles you to access a selection of online resources from <span title="Oxford University Press">OUP</span>, including the <span title="Oxford English Dictionary">OED</span>, Grove Art Online, and many more reference titles and full-text OUP books.</p>
<p><b><a href="/oxref.php" target="_blank">Oxford Reference Online</a></b></p>
</div>
EOT;

        //no footer, thanks
        $this->content->footer = '';

      return $this->content;
    }
}
?>

<?php
defined('MOODLE_INTERNAL') || die();

class filter_oxref extends moodle_text_filter {
    function filter($text, array $options = array()){
        global $SESSION;

        $refsites = array(
                       'oxref' => 'http://www.oxfordreference.com/',
                       'oed' => 'http://www.oed.com/',
                       'oxdnb' => 'http://www.oxforddnb.com/',
                       'anb' => 'http://www.anb.org/',
                       'oxscholar' => 'http://www.oxfordscholarship.com/',
                       'groveart' => 'http://www.oxfordartonline.com/',
                       'grovemusic' => 'http://www.oxfordmusiconline.com/'
                    );

        foreach ($refsites as $refkey => $refsite) {
            // If there's no record of connecting to this site previously in this browser session,
            // rewrite the link to redirect via Oxref registered page.
            if (!isset($SESSION->oxref[$refkey])) {
                $words[] = new filterobject($refsite, '', '', false, false, '/oxref.php?redirect='.$refkey.'&amp;dest=');
            }
        }

        $filterignoretagsopen  = array('<head>' , '<nolink>' , '<span class="nolink">',
                '<script(\s[^>]*?)?>', '<textarea(\s[^>]*?)?>',
                '<select(\s[^>]*?)?>');
        $filterignoretagsclose = array('</head>', '</nolink>', '</span>',
                 '</script>', '</textarea>', '</select>');

        return filter_link_attributes($text, $words, $filterignoretagsopen, $filterignoretagsclose, true);
    }
}

<?php

defined('MOODLE_INTERNAL') || die();

class filter_mathjax extends moodle_text_filter {
    /** will be true if a sane local copy of mathjax was detected */
    private static $localkit = null;
    
    public function __construct($context, array $localconfig) {
        global $CFG;
        
        if (is_null(self::$localkit)) {
            self::$localkit = (
                file_exists($CFG->dirroot . '/filter/mathjax/js/MathJax.js') &&
                file_exists($CFG->dirroot . '/filter/mathjax/js/config') &&
                file_exists($CFG->dirroot . '/filter/mathjax/js/jax') &&
                file_exists($CFG->dirroot . '/filter/mathjax/js/fonts') &&
                file_exists($CFG->dirroot . '/filter/mathjax/js/images') &&
                file_exists($CFG->dirroot . '/filter/mathjax/js/extensions')
            );
        }

        parent::__construct($context, $localconfig);
    }

    public function setup($page, $context) {
        global $CFG;
        static $jsinitialised = false;

        if ($jsinitialised) {
            return;
        }

        if (substr($page->url, 0, 6) === 'https:' && !self::$localkit) {
            if (!empty($CFG->filter_mathjax_distroothttps)) {
                $path = $CFG->filter_mathjax_distroothttps;
            } else {
                $path = 'https://c328740.ssl.cf1.rackcdn.com/mathjax/latest';
            }
        }

        if (!isset($path)) {
            if (self::$localkit) {
                $path = '/filter/mathjax/js';
            } else if (!empty($CFG->filter_mathjax_distroothttp)) {
                $path = $CFG->filter_mathjax_distroothttp;
            } else {
                $path = 'http://cdn.mathjax.org/mathjax/latest';
            }
        }

        $url = new moodle_url($path . '/MathJax.js',
              array('config' => 'TeX-AMS-MML_HTMLorMML',
                    'delayStartupUntil' => 'configured'));
            
        $page->requires->js($url);
        $page->requires->js_init_call('M.filter_mathjax.init',
            array('mathjaxroot' => (string)(new moodle_url($path))));

        $jsinitialised = true;
    }
    
    public function filter($text, array $options = array()) {
        // The presence of these indicates MathJax ought to process the block:
        //   inline: \( ... \)
        //   block: $$ ... $$, \[ ... \]
        //   tex environments: \begin{...} ... \end{...}
        //   mathml: <math> ... </math>
        
        if (preg_match('/\$\$.+?\$\$|\\\\\\[.+?\\\\\\]|\\\\\\(.+?\\\\\\)|\\\\begin\\{|<math/s', $text)) {
            return '<div class="filter-mathjax">' . $text . '</div>';
        }
        return $text;
    }
}

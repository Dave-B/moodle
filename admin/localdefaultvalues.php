<?php
    // localdefaultvalues.php - Values to apply in localdefaults.php

/// Create Profile field "Administration" category
    $courseidsdata = new stdClass();
    $courseidsdata->shortname = 'courseids';
    $courseidsdata->name = 'Course id numbers';
    $courseidsdata->datatype = 'text';
    $courseidsdata->description = 'A list of course id numbers reflecting the InfoSys record.';
    $courseidsdata->categoryid = 'Administration'; // Specify category name here, then convert to categoryid when we've looked it up/created it.
    $courseidsdata->sortorder = '1';
    $courseidsdata->required = '0';
    $courseidsdata->locked = '1';
    $courseidsdata->visible = '1';
    $courseidsdata->forceunique = '0';
    $courseidsdata->signup = '0';
    $courseidsdata->defaultdata = '';
    $courseidsdata->param1 = '30';
    $courseidsdata->param2 = '512';
    $courseidsdata->param3 = '0';


// Piwik web stats JS loader: http://webstats.conted.ox.ac.uk/
$webstats = '';
$shortcourseurls = '~^http://(hilary)|(trinity)|(michaelmas)\d{4,}.conted.ox.ac.uk$~';
if ($CFG->wwwroot == 'http://study.conted.ox.ac.uk') {
    $piwik_id = 2;
} else if (preg_match($shortcourseurls, $CFG->wwwroot)) {
    $piwik_id = 3;
} else {
    $piwik_id = null;
}

if ($piwik_id) {
    $webstats = <<<EOT
<script type="text/javascript">
  var _paq = _paq || [];
  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);
  (function() {
    var u=(("https:" == document.location.protocol) ? "https" : "http") + "://webstats.conted.ox.ac.uk//";
    _paq.push(['setTrackerUrl', u+'piwik.php']);
    _paq.push(['setSiteId', 
EOT;
    $webstats .= $piwik_id;
    $webstats .= <<<EOT
]);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0]; g.type='text/javascript';
    g.defer=true; g.async=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
  })();

</script>
<noscript><p><img src="http://webstats.conted.ox.ac.uk/piwik.php?idsite=1" style="border:0" alt="" /></p></noscript>
EOT;
}

$onlinesupportmenu = <<<EOT
Online support|http://onlinesupport.conted.ox.ac.uk/
-Courseware Guide|http://onlinesupport.conted.ox.ac.uk/CoursewareGuide/
-Learning Support|http://onlinesupport.conted.ox.ac.uk/nml/
-Technical support|http://onlinesupport.conted.ox.ac.uk/TechnicalSupport/
EOT;

// Array of settings
    $settings = array(
        // Site settings. Each setting has a has, and is an array of: 
        //   0 - component, pluginname, 'profilefield', 'block' (0/1 for visibility), or 'core'
        //   1 - setting name
        //   2 - value(s)

      /// Core
        'header:core'=>array('header', 'Core settings'),

        'core:additionalhtmlfooter'=>array('core', 'additionalhtmlfooter', $webstats),
        'core:allowcoursethemes'=>array('core', 'allowcoursethemes', '1'),
        'core:bloglevel'=>array('core', 'bloglevel', '1'),
        'core:cachetext'=>array('core', 'cachetext', '1800'),
        'core:custommenuitems'=>array('core', 'custommenuitems', $onlinesupportmenu),
        'core:debug'=>array('core', 'debug', '15'),
        'core:enableajax'=>array('core', 'enableajax', '1'),
        'core:enableavailability'=>array('core', 'enableavailability', '1'),
        'core:filteruploadedfiles'=>array('core', 'filteruploadedfiles', '2'),
        'core:legacyfilesinnewcourses'=>array('core', 'legacyfilesinnewcourses', '1'),
        'core:smtphosts'=>array('core', 'smtphosts', 'smtp.ox.ac.uk'),
        'core:updateautocheck'=>array('core', 'updateautocheck', '0'),
        'core:usetags'=>array('core', 'usetags', '0'),

        'moodlecourse:format'=>array('moodlecourse', 'format', 'units'),
        'moodlecourse:maxbytes'=>array('moodlecourse', 'maxbytes', '5242880'),


      /// Blocks
        'header:block'=>array('header', 'Block settings'),

//        'block:navigation'=>array('block', 'navigation', '0'), // Disable block
        'blockweight:settings'=>array('blockweight', 'settings', '9'), // Default Settings block weight
        'blockweight:navigation'=>array('blockweight', 'navigation', '10'), // Default Nav block weight

      /// Filters "1" is enabled by default, "-1" is off but available, "-9999" is disabled
        'header:filters'=>array('header', 'Enabled filters'),

        'filter:activitynames'=>array('filter', 'activitynames', '1'),
        'filter:emoticon'=>array('filter', 'emoticon', '1'),
        'filter:glossary'=>array('filter', 'glossary', '1'),
        'filter:mathjax'=>array('filter', 'mathjax', '-1'),
        'filter:mediaplugin'=>array('filter', 'mediaplugin', '-1'),
        'filter:oxref'=>array('filter', 'oxref', '1'),


      /// Modules
        'header:modules'=>array('header', 'Module settings'),

        'book:requiremodintro'=>array('book', 'requiremodintro', '0'),

        'core:assignment_maxbytes'=>array('core', 'assignment_maxbytes', '5242880'),
        'assignsubmission_file:maxbytes'=>array('assignsubmission_file', 'maxbytes', '5242880'),

        'folder:requiremodintro'=>array('folder', 'requiremodintro', '0'),

        'imscp:requiremodintro'=>array('imscp', 'requiremodintro', '0'),

        'profilefield:courseids'=>array('profilefield', 'courseids', $courseidsdata),

        'page:requiremodintro'=>array('page', 'requiremodintro', '0'),

        'resource:display'=>array('resource', 'display', '2'),
        'resource:displayoptions'=>array('resource', 'displayoptions', '0,1,2,4,5,6'),
        'resource:filterfiles'=>array('resource', 'filterfiles', '2'),
        'resource:framesize'=>array('resource', 'framesize', '108'),
        'resource:requiremodintro'=>array('resource', 'requiremodintro', '0'),

        'editor_tinymce:customtoolbar'=>array('editor_tinymce', 'customtoolbar', 'fontselect,fontsizeselect,formatselect,|,undo,redo,|,search,replace,|,fullscreen

bold,italic,underline,strikethrough,sub,sup,|,justifyleft,justifycenter,justifyright,|,cleanup,removeformat,pastetext,pasteword,|,forecolor,backcolor,|,ltr,rtl

bullist,numlist,outdent,indent,|,hr,|,link,unlink,|,image,nonbreaking,charmap,table,|,code'),

        'url:framesize'=>array('url', 'framesize', '108'),
        'url:requiremodintro'=>array('url', 'requiremodintro', '0'),
    );

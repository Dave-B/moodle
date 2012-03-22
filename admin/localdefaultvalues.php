<?php
    // localdefaultvalues.php - Values to apply in localdefaults.php

/// Create Profile field "Administration" category
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

// Array of settings
    $settings = array(
        // Site settings. Each setting has a has, and is an array of: 
        //   0 - component, pluginname, 'profilefield', 'block' (0/1 for visibility), or 'core'
        //   1 - setting name
        //   2 - value(s)
        'block:navigation'=>array('block', 'navigation', '0'),
        'core:allowcoursethemes'=>array('core', 'allowcoursethemes', '1'),
        'core:bloglevel'=>array('core', 'bloglevel', '4'),
        'core:cachetext'=>array('core', 'cachetext', '1800'),
        'core:custommenuitems'=>array('core', 'custommenuitems', 'Online support|http://onlinesupport.conted.ox.ac.uk/
-Courseware Guide|http://onlinesupport.conted.ox.ac.uk/CoursewareGuide/
-Learning Support|http://onlinesupport.conted.ox.ac.uk/nml/
-Technical support|http://onlinesupport.conted.ox.ac.uk/TechnicalSupport/'),
        'core:enableavailability'=>array('core', 'enableavailability', '1'),
        'core:filteruploadedfiles'=>array('core', 'filteruploadedfiles', '2'),
        'core:legacyfilesinnewcourses'=>array('core', 'legacyfilesinnewcourses', '1'),
        'core:smtphosts'=>array('core', 'smtphosts', 'smtp.ox.ac.uk'),
        'folder:requiremodintro'=>array('folder', 'requiremodintro', '0'),
        'imscp:requiremodintro'=>array('imscp', 'requiremodintro', '0'),
        'moodlecourse:format'=>array('moodlecourse', 'format', 'unmarked'),
        'profilefield:courseids'=>array('profilefield', 'courseids', $courseidsdata),
        'page:requiremodintro'=>array('page', 'requiremodintro', '0'),
        'resource:display'=>array('resource', 'display', '2'),
        'resource:displayoptions'=>array('resource', 'displayoptions', '0,1,2,4,5,6'),
        'resource:filterfiles'=>array('resource', 'filterfiles', '2'),
        'resource:framesize'=>array('resource', 'framesize', '108'),
        'resource:requiremodintro'=>array('resource', 'requiremodintro', '0'),
        'url:framesize'=>array('url', 'framesize', '108'),
        'url:requiremodintro'=>array('url', 'requiremodintro', '0'),
    );

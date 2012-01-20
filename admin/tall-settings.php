<?php
       // phpinfo.php - shows phpinfo for the current server

    require_once("../config.php");
    require_once($CFG->libdir.'/adminlib.php');

    admin_externalpage_setup('tallsettings');


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
        //   0 - component, pluginname, 'profilefield', or '' for core settings
        //   1 - setting name
        //   2 - value(s)
        'core:allowcoursethemes'=>array('core', 'allowcoursethemes', '1'),
        'core:bloglevel'=>array('core', 'bloglevel', '4'),
        'core:cachetext'=>array('core', 'cachetext', '1800'),
        'core:custommenuitems'=>array('core', 'custommenuitems', 'Online support|http://onlinesupport.conted.ox.ac.uk/
-Courseware Guide|http://onlinesupport.conted.ox.ac.uk/CoursewareGuide/
-Learning Support|http://onlinesupport.conted.ox.ac.uk/nml/
-Technical support|http://onlinesupport.conted.ox.ac.uk/TechnicalSupport/'),
        'core:filteruploadedfiles'=>array('core', 'filteruploadedfiles', '0'),
        'resource:framesize'=>array('resource', 'framesize', '108'),
        'url:framesize'=>array('url', 'framesize', '108'),
        'profilefield:courseids'=>array('profilefield', 'courseids', $courseidsdata),
        'core:smtphosts'=>array('core', 'smtphosts', 'smtp.ox.ac.uk'),
    );

    if(isset($_POST['apply'])) {
        $updates = array();

        foreach($_POST as $key=>$var) {
            if (array_key_exists($key, $settings)) {
                $a_setting = $settings[$key];
                $updates[$key][0] = $a_setting;

                if ($a_setting[0] == 'profilefield') {
                    // Set up a profile field 
                    $updates[$key][1] = add_profile_field($a_setting[1], $a_setting[2]);
                } else if ($a_setting[0] == 'core') {
                    // Set up a core setting
                    $updates[$key][1] = set_config($a_setting[1], $a_setting[2]);
                } else {
                    // Set a plugin setting
                    $updates[$key][1] = set_config($a_setting[1], $a_setting[2], $a_setting[0]);
                }
            }
        }
    }

// Output
    echo $OUTPUT->header();

    $tablestart  = '<table><thead><tr><th>Apply <input type="checkbox" id="selectall" value="1" title="Select/Clear all"/></th>';
    $tablestart .= '<th>Component</th><th>Setting</th><th>Current value</th><th>Value to apply</th></tr></thead><tbody>';
    $tableend = "</tbody></table>\n";

    ?>
    
    <div class="tallsettings">

    <script type="text/javascript">
YUI().use('node', function (Y) {
    var settingcheckboxes = Y.all('input.setting');
    Y.one("#selectall").on("click", function(e) {
        if (e.target.get('checked')) {
            settingcheckboxes.set('checked', 'checked');
        } else {
            settingcheckboxes.set('checked', '');
        }
    });
});
    </script>

    <h1>TALL settings</h1>

    <?php

    if(isset($updates)) {
        $updatehtml = '<ul>';
        foreach ($updates as $update) {
            $updatehtml .= '<li>';
            if ($update[1]) {
                $updatehtml .= 'Success: ';
            } else {
                $updatehtml .= 'Failure: ';
            }
            $updatehtml .= $update[0][0].', '.
            $update[0][1];
            $updatehtml .= "</li>\n";
        }
        $updatehtml .= '</ul>';
        echo $updatehtml;
    }

    echo '<form action="" method="post">';
    echo '<fieldset><legend>Settings</legend>';
    $tablerows = '';
    foreach ($settings as $key=>$a_setting) {
        if ($a_setting[0] == 'profilefield') {
            if ($catid = find_profile_field($a_setting[1])) {
                $currentvalue = "[Profile field '$name' exists (id $catid)]";
            } else {
                $currentvalue = '[Profile field does not exist]';
            }
        } else {
            $currentvalue = get_config($a_setting[0], $a_setting[1]);
        }
        $tablerows .= '<tr><td><input type="checkbox" name="'.$key.'" value="1" class="setting"/></td><td>'.$a_setting[0].'</td><td>'.$a_setting[1];
        $tablerows .= '</td><td>'.$currentvalue.'</td><td>';
        if ($a_setting[0] == 'profilefield') {
            $tablerows .= '[Create profile field]';
        } else {
            $tablerows .= $a_setting[2];
        }
        $tablerows .= "</td></tr>\n";
    }
    echo $tablestart.$tablerows.$tableend;
    echo '</fieldset>';

    echo '<p><input type="submit" name="apply" value="Apply settings"/></p>';

    echo '</form></div>';

    echo $OUTPUT->footer();


// Functions
function find_profile_field($name) {
    global $DB;

    $record = $DB->get_record('user_info_category', array('name'=>$name), 'id');
    echo $record;
    
    return $record;
}



function add_profile_category($name, $sortorder=1) {
    global $DB;

    $data->name = $name;
    $data->sortorder = $sortorder;

    // Check if Category exists
    if($catid = find_profile_field($name)) {
        return $catid;
    } else if ($catid = $DB->insert_record('user_info_category', $data)) {
        return $catid;
    } else {
        return false;
    }
}

function add_profile_field($data) {
/// Create Profile field "Administration" category
    global $DB;

    if (!$catid = add_profile_category($data->categoryid, true)) {
        error('There was a problem adding "'.$data->categoryid.'" Profile category.<br/>');
    } else {
        print('Using "'.$data->categoryid." Profile category (id: $catid.)<br/>\n");
        $data->categoryid = $catid;

        if (!$DB->insert_record('user_info_field', $data, false)) {
            error('There was a problem adding "'.$data->name.'" Profile field.<br/>');
        } else {
            print ('Added "'.$data->name.'" Profile field.<br/>');
        }
    }
}


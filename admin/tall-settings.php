<?php
       // phpinfo.php - shows phpinfo for the current server

    require_once("../config.php");
    require_once($CFG->libdir.'/adminlib.php');

    admin_externalpage_setup('tallsettings');

    // Setup

    $site  = optional_param('sitesettings', 0, PARAM_BOOL);
    $module  = optional_param('modulesettings', 0, PARAM_BOOL);
    $profile  = optional_param('profile', 0, PARAM_BOOL);

    $sitesettings = array(
        'allowcoursethemes'=>'1',
        'bloglevel'=>'1',
        'cachetext'=>'1800',
        'filteruploadedfiles'=>'2'
    );

    $modulesettings = array(
        'allowcoursethemes'=>'1',
    );

    if($site) {
        apply_settings($sitesettings);
    }


// Output
    echo $OUTPUT->header();

    $tablestart = "<table><thead><tr><th>Setting</th><th>Old value</th><th>Value to apply</th></tr></thead><tbody>\n";
    $tableend = "</tbody></table>\n";

    echo '<div class="tallsettings">';

    echo '<h1>TALL settings</h1>';
    echo '<form action="" method="get">';

    if(!($site || $module || $profile)) {
        echo '<p>Select a settings group to apply it.</p>';
    }

    echo '<h2>Site-wide settings</h2>';
    $tablerows = show_settings($sitesettings);
    echo $tablestart.$tablerows.$tableend;
    echo '<p><label><input type="checkbox" name="sitesettings" value="1"/> Site-wide settings</label></p>';

    echo '<hr/>';

    echo '<h2>Module settings</h2>';
    $tablerows = show_settings($modulesettings);
    echo $tablestart.$tablerows.$tableend;
    echo '<p><label><input type="checkbox" name="modulesettings" value="1"/> Module settings</label></p>';

    echo '<hr/>';


    echo '<h2>Profile fields</h2>';
    if($profile) {
        add_profile_fields();
    }
    echo '<p><label><input type="checkbox" name="profile" value="1"/> Course ids ("courseids")</label></p>';

?>
<br/>
<input type="submit" id="apply" value="Apply settings"/>
</form>
<?php

    echo '</div>';
    echo $OUTPUT->footer();


// Functions

function show_settings($settings) {
    $tablerows = '';
    foreach ($settings as $setting => $value) {
        $oldvalue = get_config('', $setting);
        $tablerows .= '<tr><td>'.$setting.':</td><td>'.$oldvalue.'</td><td>'.$value."</td></tr>\n";
    }
    return $tablerows;
}

function apply_settings($settings) {
    foreach ($settings as $setting => $value) {
        set_config($setting, $value);
    }
}

function add_profile_fields() {
/// Create Profile field "Administration" category
    global $DB;

    $data->name = 'Administration';
    $data->sortorder = 1;

    if (!$catid = $DB->insert_record('user_info_category', $data, true)) {
        error('There was a problem adding "Administration" Profile category.<br/>');
    } else {
        print("Added \"Administration\" Profile category, id: $catid.<br/>\n");
    }

/// Create Profile field "Administration" category
    $data->shortname = 'courseids';
    $data->name = 'Course id numbers';
    $data->datatype = 'text';
    $data->description = 'A list of course id numbers reflecting the InfoSys record.';
    $data->categoryid = $catid;
    $data->sortorder = '1';
    $data->required = '0';
    $data->locked = '1';
    $data->visible = '1';
    $data->forceunique = '0';
    $data->signup = '0';
    $data->defaultdata = '';
    $data->param1 = '30';
    $data->param2 = '512';
    $data->param3 = '0';

    if (!$DB->insert_record('user_info_field', $data, false)) {
        error('There was a problem adding "Course id numbers" Profile field.<br/>');
    } else {
        print ('Added "Course id numbers" Profile field.<br/>');
    }    
}


<?php
    // localdefaults.php - Selectively apply collated settings 

    require_once("../config.php");
    require_once($CFG->libdir.'/adminlib.php');

    require_login();
    $systemcontext = get_context_instance(CONTEXT_SYSTEM);
    require_capability('moodle/site:config', $systemcontext);

    admin_externalpage_setup('localdefaults');

    // Load the values
    require_once("localdefaultvalues.php");

    if(isset($_POST['apply'])) {
        $updates = array();

        foreach($_POST as $key=>$var) {
            if (array_key_exists($key, $settings)) {
                $a_setting = $settings[$key];
                $updates[$key][0] = $a_setting;

                if ($a_setting[0] == 'profilefield') {
                    // Set up a profile field 
                    $updates[$key][1] = add_profile_field($a_setting[2]);
                } else if ($a_setting[0] == 'core') {
                    // Set up a core setting
                    $updates[$key][1] = set_config($a_setting[1], $a_setting[2]);
                } else if ($a_setting[0] == 'block') {
                    if (ctype_digit($a_setting[2])) {
                        // Set a block visibility (Site administration ► Plugins ► Blocks ► Manage blocks)
                        $updates[$key][1] = $DB->set_field('block', 'visible', $a_setting[2], array('name'=>$a_setting[1]));
                    }
                } else if ($a_setting[0] == 'blockweight') {
                    if (ctype_digit($a_setting[2])) {
                        // Set a block default weight (Site administration ► Plugins ► Blocks ► Manage blocks)
                        $updates[$key][1] = $DB->set_field('block_instances', 'defaultweight', $a_setting[2], array('blockname'=>$a_setting[1]));
                    }
                } else if ($a_setting[0] == 'filter') {
                    if (is_numeric($a_setting[2])) {
                        // En/dis-able a filter (Site administration ► Plugins ► Filters ► Manage filters)
                        $updates[$key][1] = $DB->set_field('filter_active', 'active', $a_setting[2], array('filter'=>$a_setting[1]));
                    }
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
    
    <div class="localsettings">

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

    <h1>Local defaults</h1>

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
        $class='';
        $tablerows .= '<tr>';
        if ($a_setting[0] == 'header') {
            $tablerows .= '<th colspan="5" style="border-top:1px solid #eee;">'.$a_setting[1].'</th>';
        } else {
            if ($a_setting[0] == 'profilefield') {
                if ($catid = find_profile_field($a_setting[1])) {
                    $currentvalue = '[Profile field "'.$a_setting[2]->name.'" exists (id '.$catid.')]';
                } else {
                    $currentvalue = '[Profile field does not exist]';
                    $class=' class="attention"';
                }
            } else if ($a_setting[0] == 'block') {
                if ($block = $DB->get_record('block', array('name'=>$a_setting[1]))) {
                    $currentvalue = $block->visible;
                } else {
                    $currentvalue = '[Block does not exist]';
                    $class=' class="attention"';
                }
                if ($currentvalue != $a_setting[2]) {
                    $class=' class="attention"';
                }
            } else if ($a_setting[0] == 'blockweight') {
                if ($block = $DB->get_record('block_instances', array('blockname'=>$a_setting[1]))) {
                    $currentvalue = $block->defaultweight;
                } else {
                    $currentvalue = '[Block does not exist]';
                    $class=' class="attention"';
                }
                if ($currentvalue != $a_setting[2]) {
                    $class=' class="attention"';
                }
            } else if ($a_setting[0] == 'filter') {
                if ($filter = $DB->get_record('filter_active', array('filter'=>$a_setting[1]))) {
                    $currentvalue = $filter->active;
                } else {
                    $currentvalue = '[Filter does not exist]';
                    $class=' class="attention"';
                }
                if ($currentvalue != $a_setting[2]) {
                    $class=' class="attention"';
                }
            } else {
                $currentvalue = get_config($a_setting[0], $a_setting[1]);
                if ($currentvalue != $a_setting[2]) {
                    $class=' class="attention"';
                }
            }
            $tablerows .= '<td><input type="checkbox" name="'.$key.'" value="1" class="setting"/></td><td>'.$a_setting[0].'</td>';
            $tablerows .= '<td>'.$a_setting[1].'</td>';
            $tablerows .= '<td>'.htmlentities($currentvalue).'</td><td'.$class.'>';
            if ($a_setting[0] == 'profilefield') {
                $tablerows .= '[Create profile field]';
            } else {
                $tablerows .= htmlentities($a_setting[2]);
            }
            $tablerows .= "</td>\n";
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

    if($record = $DB->get_record('user_info_field', array('shortname'=>$name), 'id')) {
        return $record->id;
    } else {
        return false;
    }
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
        return false;
    } else {
        $data->categoryid = $catid;

        if (!$DB->insert_record('user_info_field', $data, false)) {
            return false;
        } else {
            return true;
        }
    }
}


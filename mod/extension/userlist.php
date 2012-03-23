<?php
    require_once('../../config.php');
    require_once($CFG->libdir.'/tablelib.php');
    require_once('lib.php');

    $userid = optional_param('id', $USER->id, PARAM_INT);    // user id
    $course = optional_param('course', SITEID, PARAM_INT);   // course id (defaults to Site)

    if (!$course = get_record('course', 'id', $course)) {
        error('Course ID was incorrect');
    }

    if ($course->id != SITEID) {
        require_login($course);
    } else if (!isloggedin()) {
        if (empty($SESSION->wantsurl)) {
            $SESSION->wantsurl = $CFG->httpswwwroot.'/user/edit.php';
        }
        redirect($CFG->httpswwwroot.'/login/index.php');
    }

/// Display page header
    $userfullname     = fullname($userid, true);
    $strviewextensions = get_string('viewextensions', 'extension');

    $navlinks = array();
    $navlinks[] = array('name' => $userfullname,
                        'link' => "view.php?id=$userid",
                        'type' => 'misc');
    $navlinks[] = array('name' => $strviewextensions, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);
    print_header("$course->shortname: $strviewextensions", $course->fullname, $navigation, "");

    // Check user exists
    if (!$user = get_record('user', 'id', $userid)) {
        error('User ID was incorrect');
    }

    /// Print tabs at the top
    $showroles = 1;
    $currenttab = 'extensions';
    require($CFG->dirroot.'/user/tabs.php');
    print_heading(get_string('extensions', 'extension'), '', 2);


    $extensionsbycourse = array();
    $viewanyextension = false;
    // Get list of extensions for the specified user
    $extensionlist = get_records('extension', 'userid', $userid, "'course', 'activitycmid', 'timemodified'");
    if(sizeof($extensionlist) < 1) {
        echo get_string('noextensionsfound', 'extension');
    } else {
        foreach ($extensionlist as $key => $extension) {
            // Check current users's capabilities to view extensions on each course found. 
            $context = get_context_instance(CONTEXT_COURSE, $extension->course);

            if (has_capability('mod/extension:viewanyextension', $context)) {
                // Can view any extension for this course context
                $viewanyextension = true;
                // User can view this extension, so put it into an array grouped by course
                $extensionsbycourse[$extension->name][] = $extension;
            } else if ($userid == $USER->id && has_capability('mod/extension:viewownextension', $context)) {
                // Is current user, who can view own extensions
                // User can view this extension, so put it into an array grouped by course
                $extensionsbycourse[$extension->name][] = $extension;
            } else {
                // User can't view own or any extensions for this course, so remove extension from list
                unset($extensionlist[$key]);
            }
        }

        //print_object($extensionsbycourse);
        //echo sizeof($extensionsbycourse);
        if(sizeof($extensionsbycourse) < 1) {
            error("You do not have permission to view this information.");
        } else {
            // Show extensions grouped by course
            foreach ($extensionsbycourse as $name => $extensionlist) {
                print_heading($name, '', 3);
                doTable ($extensionlist);
            }
        }
    }

    function doTable ($extensionlist) {
        global $extension_requeststatus, $viewanyextension;
        // Set up table
        $tablecolumns = array('course', 'name', 'timecreated', 'lengthrequested', 'status', 'lengthgranted');
        $tableheaders = array(get_string('course'),
                              get_string('assignmentname', 'assignment'),
                              get_string('extensionsubmitted', 'extension'),
                              get_string('extensionrequested', 'extension'),
                              get_string('status'),
                              get_string('extensiongranted', 'extension')
                             );

        $table = new flexible_table('mod-extension-user-extensions');

        $table->define_columns($tablecolumns);
        $table->define_headers($tableheaders);
        //$table->define_baseurl($this->viewurl->out());

        $table->pageable(true);
        // attributes in the table tag
        $table->set_attribute('cellspacing', '0');
        $table->set_attribute('id', 'attempts');
        $table->set_attribute('class', 'generaltable generalbox');
        $table->set_attribute('align', 'center');
        //$table->set_attribute('width', '50%');

        // get it ready!
        $table->setup();

        $courses = array();
        foreach ($extensionlist as $extension) {
            $a = array(); // Convert Object to Array
            foreach ($extension as $key => $val) {
                if ($key == 'course') {
                    if (!isset($courses[$val])) {
                        $courses[$val] = get_record('course', 'id', $val);
                    }
                    $val = '<a href="/course/view.php?id='.$val.'">'.$courses[$val]->fullname.'</a>';
                }
                else if ($key == 'name') {
                    $val = '<a href="/mod/assignment/view.php?id='.$extension->activitycmid.'">'.$val.'</a>';
                }
                else if ($key == 'timecreated') {
                    $val = userdate($val, get_string('strftimedatetimeshort', 'moodle'));
                }
                else if ($key == 'status') {
                    if($courses[$extension->course]->registryworkflow) {
                        // Workflow = modify status depending on who's viewing
                        if ($viewanyextension) {
                            // Admin user
                            $val = '<a href="/mod/extension/view.php?id='.$extension->id.'">'.
                                    $extension_requeststatus[$val].'</a>';
                            if($extension->approvalconfirmed) {
                                $val .= ' ('.get_string('confirmed', 'extension').')';
                            } else if ($extension->status != 0) {
                                $val .= ' ('.get_string('awaitingconfirmation', 'extension').')';
                            }
                        } else {
                            // Non-admin user
                            if ($extension->approvalconfirmed) {
                                $val = '<a href="/mod/extension/view.php?id='.$extension->id.'">'.
                                        $extension_requeststatus[$val].'</a>';
                            } else {
                                $val = '<a href="/mod/extension/view.php?id='.$extension->id.'">'.
                                        $extension_requeststatus[0].'</a>';
                            }
                        }
                    } else {
                        // No workflow = display status as is
                        $val = '<a href="/mod/extension/view.php?id='.$extension->id.'">'.
                                $extension_requeststatus[$val].'</a>';
                    }
                }
                else if ($key == 'lengthgranted') {
                    if($courses[$extension->course]->registryworkflow && !$extension->approvalconfirmed) {
                        $val = '-';
                    } else {
                        $val = $val;
                    }
                }

                $a[$key] = $val;
            }
            $table->add_data_keyed($a);
        }
        //print_object($courses);

        $table->print_html();
    }

/// and proper footer
    print_footer($course);
?>

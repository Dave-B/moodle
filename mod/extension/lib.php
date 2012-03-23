<?php  // $Id: lib.php,v 1.7.2.5 2009/04/22 21:30:57 skodak Exp $

/**
 * Library of functions and constants for module extension
 * This file should have two well differenced parts:
 *   - All the core Moodle functions, neeeded to allow
 *     the module to work integrated in Moodle.
 *   - All the extension specific functions, needed
 *     to implement all the module logic. Please, note
 *     that, if the module become complex and this lib
 *     grows a lot, it's HIGHLY recommended to move all
 *     these module specific functions to a new php file,
 *     called "locallib.php" (see forum, quiz...). This will
 *     help to save some memory when Moodle is performing
 *     actions across all modules.
 */

$extension_requeststatus = array(
    // Status ints, with strings
    0 =>  get_string('pending', 'extension'),
    1 =>  get_string('approved', 'extension'),
    2 =>  get_string('rejected', 'extension')
);

$extension_displaymultipliers = array(
	// Numbers of seconds multipliers
	'days' => 86400,
	'hours' => 3600,
	'minutes' => 60
);

/**
 * Standard Class for a single extension to an activity.
 */
class extension {
	// Database values
    var $id;
    var $course;
    var $name;
    var $userid;
    var $activitycmid;
    var $reason;
    var $status;
    var $lengthrequested;
    var $lengthgranted;
    var $privatenotes;
    var $studentmessage;
    var $approvalconfirmed;
    var $timeconfirmed;
    var $modname;
    // More data
    var $cm;
    var $user;
    var $activity;
    var $strunits;
    var $unitmultiplier;

    // FIXME: Should access global version, but it's not passed into this object
    var $extension_displaymultipliers = array(
            // Numbers of seconds multipliers
            'days' => 86400,
            'hours' => 3600,
            'minutes' => 60
        );

    /**
     * Constructor for the extension class
     * @param activity array, array of extension details
     * @param cm object, optional activity course module object
     * @param activity object, optional activity object
     * @param user object, optional user object
     *
     */
    function extension($record, &$cm=NULL, &$activity=NULL, &$user=NULL) {
		//global $extension_displaymultipliers;
        $extension_displaymultipliers = $this->extension_displaymultipliers;

		foreach($record as $key=>$val) {
			$this->$key = $val;
		}

		if($cm) {
			$this->cm &= $cm;
		} else {
			$this->cm = get_coursemodule_from_id('assignment', $this->activitycmid);
		}

		if($activity) {
			$this->activity &= $activity;
		} else {
			$this->activity = get_record('assignment', 'id', $this->cm->instance);
		}

		if($user) {
			$this->user &= $user;
		} else {
			$this->user = get_record('user', 'id', $this->userid);
		}

		$this->strunits = get_string($this->activity->extensionunits, 'extension');
		$this->unitmultiplier = $extension_displaymultipliers[$this->activity->extensionunits];

		//print_object($this);
    }

    /**
     * View extension details
     *
     */
    function view() {
		global $extension_requeststatus;

		$assignmentLink = '<a href="/mod/assignment/view.php?id='.$this->activitycmid.'">'.format_string($this->name).'</a>';
		$studentLink = '<a href="/user/view.php?id='.$this->user->id.'">'.format_string($this->user->firstname.' '.$this->user->lastname).'</a>';
		if ($this->approvalconfirmed) {
			// Approval confirmed, so we can notify.
            // TODO: Verify this approach is correct with multiple extensions
			$status = $extension_requeststatus[$this->status];
			$effectivedateRaw = extension_get_extended_date($this->activity->timedue,
                                                            $this->lengthgranted*$this->unitmultiplier);
			$effectivedate = userdate($effectivedateRaw, get_string('strftimedatetimeshort', 'moodle'));
		} else {
			// No notification yet, so just show "Pending" status.
			$status = $extension_requeststatus[0];
		}

		$output = '<table>';
        $output .= '<tr><td class="c0">'.get_string('modulename', 'assignment').':</td>';
        $output .= '    <td class="c1">'.$assignmentLink.'</td></tr>';

        $output .= '<tr><td class="c0">'.get_string('user', 'moodle').':</td>';
        $output .= '    <td class="c1">'.$studentLink.'</td></tr>';
        $output .= '<tr><td class="c0">'.get_string('originalduedate','extension').':</td>';
        $output .= '    <td class="c1">'.userdate($this->activity->timedue).'</td></tr>';
        $output .= '<tr><td class="c0">'.get_string('extensionrequested','extension').':</td>';
        $output .= '    <td class="c1">'.$this->lengthrequested.' '.$this->strunits.'</td></tr>';

        if($this->approvalconfirmed) {
            $output .= '<tr><td class="c0">'.get_string('extensiongranted', 'extension').':</td>';
            $output .= '    <td class="c1">'.$this->lengthgranted.' '.$this->strunits.'</td></tr>';
        }

        $output .= '<tr><td class="c0">'.get_string('reasonforrequest','extension').':</td>';
        $output .= '    <td class="c1">'.$this->reason.'</td></tr>';

        $output .= '<tr><td class="c0">'.get_string('supportingevidence','extension').':</td>';
        $output .= '    <td class="c1">';
        if ($this->evidencefile) {
            $output .= $this->evidence_file_link();
        } else {
            $output .= ' - ';
        }
        $output .= '</td></tr>';

        $output .= '<tr><td class="c0">'.get_string('status','moodle').':</td>';
        $output .= '    <td class="c1">'.$status.'</td></tr>';
        if($this->approvalconfirmed) {
            $output .= '<tr><td class="c0">'.get_string('feedback','moodle').':</td>';
            $output .= '    <td class="c1">'.$this->studentmessage.'</td></tr>';
        }
        $output .= '<tr><td class="c0">'.get_string('newduedate','extension').':</td>';
        $output .= '    <td class="c1">';
        $effectivedate = extension_get_effective_date_by_cm($this->cm, $this->user->id, $this->activity);
        $output .= userdate($effectivedate->timedue);
        if ($effectivedate->numapproved == 1) {
            $output .= ' ('.get_string('includes1extensionfor', 'extension');
        } else if ($effectivedate->numapproved > 0) {
            $output .= ' ('.get_string('includesextensionstotaling', 'extension', $effectivedate->numapproved);
        }
        $output .= $effectivedate->totalextensionunits.' ';
        if ($effectivedate->numapproved == 1) {
            // Singular
            $output .= get_string(substr($this->cm->activity->extensionunits, 0, -1), 'extension').')';
        } else if ($effectivedate->numapproved > 0) {
            $output .= $this->strunits.')';
        }
        $output .= '</td></tr>';
        $output .= '</table>';

        return $output;
    }

    /**
     * View approval form
     *
     */
    function view_approval_form($context) {
		global $CFG, $SITE, $COURSE, $extension_requeststatus;

		// Check permissions for approval form
		if (has_capability('mod/extension:approveextension', $context)
            || has_capability('mod/extension:confirmextension', $context) ) {
			include_once('mod_approval_form.php');
			$mform = new mod_extension_approval_form();

			if ($mform->is_cancelled()){
				//you need this section if you have a cancel button on your form
				redirect("$CFG->wwwroot/mod/extension/index.php?id=$cm->course&amp;a=$cm->id", get_string('cancelled'));

			} else if ($fromform=$mform->get_data()){
				// This branch is where you process validated data.
				// Log the edit
				add_to_log($this->course, 'extension', 'update', "view.php?id=$this->course&actid=$this->activitycmid");

				$fromform->timemodified = time();

				if(!isset($fromform->existingprivatenotes)) {
					$fromform->existingprivatenotes = '';
				}
				// TODO: Better recording of each note - user & datetime. Incorporate into log?
                // Combine old notes with any new notes
                if($fromform->privatenotes) {
                    $fromform->privatenotes = $fromform->existingprivatenotes."\n<div>".$fromform->privatenotes.'</div>';
                } else {
                    $fromform->privatenotes = $fromform->existingprivatenotes;
                }

                if(!$COURSE->registryworkflow) {
                    // No workflow
                    $fromform->timeconfirmed = time();
                } else {
                    // Workflow
                    if (isset($fromform->approvalconfirmed) && $fromform->approvalconfirmed == 1) {
                        $fromform->timeconfirmed = time();
                    }
                    //print_object($fromform);
                    //print_object($cm);

                    $messagedata = new object();

                    $approvers = get_extension_users_by_role($this->cm, 'mod/extension:approveextension', $this->user);
                    foreach ($approvers as $approver) {
                        if(!isset($firstapprover)) {
                            // TODO: Find a better way of choosing from more than one user in the relevant role
                            $firstapprover = $approver;
                            $messagedata->approvername = $firstapprover->firstname.' '.$firstapprover->lastname;
                        }
                    }
                    //echo "approvers:<br/>";
                    //print_object($approvers);

                    $confirmers = get_extension_users_by_role($this->cm, 'mod/extension:confirmextension', $this->user);
                    foreach ($confirmers as $confirmer) {
                        if(!isset($firstconfirmer)) {
                            // TODO: Find a better way of choosing from more than one user in the relevant role
                            $firstconfirmer = $confirmer;
                            $messagedata->confirmername = $firstconfirmer->firstname.' '.$firstconfirmer->lastname;
                        }
                    }
                    //echo "confirmers:<br/>";
                    //print_object($confirmers);
                }

                if($fromform->status == 2) {
                    $fromform->lengthgranted = 0;
                }

                // Update Extension record
                if(!update_record('extension', $fromform)) {
                    error(get_string('inserterror' , 'extension'));
                } else if ($fromform->status > 0) {
                    // Success
                    // Extension accepted or rejected - so send notifications

                    // Prepare common message info
                    $from = $SITE->fullname.': '.get_string('modulenameplural', 'extension');

                    $messagedata->coursename = $COURSE->fullname;
                    $messagedata->assignmentname = $this->cm->activity->name;
                    $messagedata->studentname = $this->user->firstname.' '.$this->user->lastname;

                    $messagedata->status = $extension_requeststatus[$fromform->status];
                    $messagedata->lengthrequested = $this->lengthrequested.' '.$this->strunits;
                    $messagedata->lengthgranted = $fromform->lengthgranted.' '.$this->strunits;

                    $edate = extension_get_effective_date_by_cm($this->cm, $this->user->id, $this->activity);
                    $messagedata->effectivedate = userdate($edate->timedue);
                    $messagedata->siteurl = $CFG->wwwroot.'/';
                    $messagedata->extensionurl = $CFG->wwwroot.'/mod/extension/view.php?id='.$this->id;

                    if ($fromform->studentmessage) {
                        $messagedata->studentmessage = trim($fromform->studentmessage);
                    } else if ($this->studentmessage) {
                        $messagedata->studentmessage = trim($this->studentmessage);
                    } else {
                        $messagedata->studentmessage = ' - ';
                    }

                    if (!$COURSE->registryworkflow) {
                        // Not Registry workflow - notify Student
                        // Send notification to student
                        $user = get_record('user', 'id', $this->userid);

                        if($fromform->status == 1) {
                            $subject = get_string('extensionapproved', 'extension');
                        } else {
                            $subject = get_string('extensionrejected', 'extension');
                        }

                        $messagetext = get_string('studentextensiondecisionmessage', 'extension', $messagedata);
                        email_to_user($user, $from, $subject, $messagetext, '', '', false);

                    } else {
                        // Registry workflow
                        if (!isset($fromform->approvalconfirmed) || $fromform->approvalconfirmed < 1) {
                            // Status changed but not confirmed - notify confirmers (Registry)
                            $subject = get_string('extensionawaitingconfirmation', 'extension');

                            foreach ($confirmers as $confirmer) {
                                $messagedata->approvername = $firstapprover->firstname.' '.$firstapprover->lastname; // TODO: Approver is current user, right?
                                $messagedata->confirmername = $confirmer->firstname.' '.$confirmer->lastname;

                                $messagetext = get_string('extensionawaitingconfirmationmessage', 'extension', $messagedata);

                                email_to_user($confirmer, $from, $subject, $messagetext, '', '', '', false);
                            }

                        } else {
                            // Status confirmed - notify Student, grader, and approver
                            // Notify Student
                            $user = get_record('user', 'id', $this->userid);

                            if($fromform->status == 1) {
                                $subject = get_string('extensionapproved', 'extension');
                            } else {
                                $subject = get_string('extensionrejected', 'extension');
                            }

                            $messagetext = get_string('studentextensiondecisionmessage', 'extension', $messagedata);
                            email_to_user($user, $from, $subject, $messagetext, '', '', false);


                            // Notify grader (Tutor)
                            $exclude = $approvers + $confirmers;
                            $graders = get_extension_users_by_role($this->cm, 'mod/assignment:grade', $this->user, $exclude);
                            //print_object($graders);
                            foreach ($graders as $id => $grader) {
                                // Email grader
                                $messagedata->gradername = $grader->firstname.' '.$grader->lastname;
                                $messagetext = get_string('graderextensiondecisionmessage', 'extension', $messagedata);
                                email_to_user($grader, $from, $subject, $messagetext, '', '', false);
                            }

                            // Notify approver (Course Director)
                            //print_object($approvers);
                            foreach ($approvers as $id => $approver) {
                                // Email approver
                                $messagedata->gradername = $approver->firstname.' '.$approver->lastname;
                                $messagetext = get_string('graderextensiondecisionmessage', 'extension', $messagedata);
                                email_to_user($approver, $from, $subject, $messagetext, '', '', false);
                            }
                        }
                    }

                } else {
                    // Still Pending
                    // No email notifications neccessary
				}

				redirect("$CFG->wwwroot/mod/extension/view.php?id=$fromform->id", get_string('updated'));

            } else {
                // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
                // or on the first display of the form.
                // Put data you want to fill out in the form into array $toform here
                $toform = array();
                $mform->set_data($toform);

                // Limit notification/approval to registry capabilities
                if ($COURSE->registryworkflow
                    && isset($mform->_form->_elementIndex['approvalconfirmed'])
                    && !has_capability('mod/extension:confirmextension', $context))
                    {
                    $myObj = $mform->_form->_elements[$mform->_form->_elementIndex['approvalconfirmed']];
                    $myObj->freeze();
                }

                $mform->display();
                }
        }
    }


    function file_area_name() {
        global $CFG;
        return $this->course.'/'.$CFG->moddata.'/extension/'.$this->activity->id.'/'.$this->userid;
    }

    /**
     * Return an HTML link to the evidence file.
     */
    function evidence_file_link() {
       return '<a href="/file.php/'.$this->file_area_name().'/'.clean_filename($this->evidencefile).'">'.$this->evidencefile.'</a>';
    }
}

/**
 * Standard Class for all extensions to an activity (whatever status).
 */
class extension_group {

    var $course;
    var $activitycmid;
    var $strextension;
    var $strextensions;
    var $extensions;

    var $requeststatus = array(
        // Status ints, with strings
        0 =>  'pending',
        1 =>  'approved',
        2 =>  'rejected'
    );

    var $displaymultipliers;

    /**
     * Constructor for the extension_group class
     *
     * @param activitycmid   integer, the activity cm id
     * @param userid   integer, optional user owning extensions to look up, otherwise get all for the activity
     * @param extensions   object, if we have it we pass it to save db access
     * @param activity   object, if we have it we pass it to save db access
     */
    function extension_group($activitycmid, $userid=NULL, $extensions=NULL, $activity=NULL) {
        global $CFG, $COURSE, $extension_displaymultipliers;

		$this->activitycmid = $activitycmid;
        $this->displaymultipliers = $extension_displaymultipliers;

        $userselect = ($userid) ? 'userid = '.intval($userid).' AND ' : '';
        // Load extensions
        $sql = "SELECT * FROM {$CFG->prefix}extension
                          WHERE $userselect
                                course = $COURSE->id AND
                                activitycmid = $activitycmid
                          ORDER BY timecreated";
        if ($extensions = get_records_sql($sql)) {
			foreach ($extensions as $ext) {
				$this->extensions[] = new extension($ext);
			}
		} else {
            $this->extensions = NULL;
        }

        $this->strextension = get_string('modulename', 'extension');
        $this->strextensions = get_string('modulenameplural', 'extension');
        $this->strsubmissions = get_string('submissions', 'assignment');
        $this->strlastmodified = get_string('lastmodified');

        if ($activity) {
            $this->activity = $activity;
        } else {
            // TODO: Get module name from course module
            $modulename = 'assignment';
            if (! $this->activity = get_record($modulename, 'id', $activitycmid)) {
                error('activity ID was incorrect');
            }
        }

        $this->course = $this->activity->course;
//        print_object($this);
//        print_object(debug_backtrace());
//        die();
    }

    /**
     * Calculates the effective due date for this extension_group's activity
     *
     * @return int $newdate new date as timestamp
     */
    public function get_effective_date($userid = NULL, $onlyextension = false) {
        $totalextensiontime = 0;

        if($this->extensions) {
            foreach($this->extensions as $extension) {
                // For each extension
                if($extension->approvalconfirmed && $extension->status == 1) {
                    // If the extension is confirmed and approved
                    if(!$userid || $userid == $extension->user->id) {
                        // Multiply the length granted with the extension unit multiplier and add to the total
                        $totalextensiontime += $extension->lengthgranted * $extension->unitmultiplier;
                    }
                }
            }
        }

        if(!$onlyextension) {
            // Add the original due date and the extension, to give the effective due date
            $totalextensiontime += $this->activity->timedue;
        }

        return $totalextensiontime;
    }

    /**
     * Calculates the extension for this extension_group's activity
     * @param userid   integer, user owning extensions to look up, otherwise get all for the activity
     *
     * @return int $newdate new date as timestamp
     */
    public function get_extension_time($userid) {
        $totalextensiontime = 0;

        if($this->extensions) {
            foreach($this->extensions as $extension) {
                // For each extension
                if($userid == $extension->userid) {
                    // If the specified user
                    if($extension->approvalconfirmed && $extension->status == 1) {
                        // If confirmed and approved, multiply the length granted with
                        // the extension unit multiplier and add to the total
                        $totalextensiontime += $extension->lengthgranted * $extension->unitmultiplier;
                    }
                }
            }
        }

        return $totalextensiontime;
    }

    /**
     * Calculates the total verified extensions pending, approved, and rejected
     * (indexed 0,1,2 respectively) for this extension_group.
     * Unverified extensions are always read as pending.
     *
     * @return array
     */
    public function get_extension_counts() {
        $results = array(0=>0,1=>0,2=>0);

        if($this->extensions) {
            foreach($this->extensions as $extension) {
                if($extension->approvalconfirmed == 1) {
                    $results[$extension->status]++;
                } else {
                    $results[0]++;
                }
            }
            return $results;
        } else {
            return NULL;
        }

    }

    /**
     * Count the total length of verified approved extensions pending
     * Value is value relating to displayunits
     *
     * @return array
     */
    public function get_extension_length() {
        $results = 0;

        if($this->extensions) {
            foreach($this->extensions as $extension) {
                if($extension->status == 1 && $extension->approvalconfirmed == 1) {
                    $results += $extension->lengthgranted;
                }
            }
        }

        return $results;
    }

    /**
     * Print number of extensions of each status
     *
     * @return array
     */
    public function get_extension_summary() {

        if($results = $this->get_extension_counts()) {
            $output = '';
            foreach($results as $key=>$val) {
                if($val) {
                    $output .= '<a href="/mod/extension/index.php?id='.$this->course.'&amp;a='.$this->activitycmid.'&amp;status='.$key.'">'.
                               $val.' '.get_string($this->requeststatus[$key], 'extension').'</a> ';
                }
            }
            return trim($output);
        } else {
            return '-';
        }
    }

}

/**
 * Class to load all extensions for a course, with filtering for activity, user, and extension status
 **/
class course_extension_collection {
    var $course;
    var $activitycmid;
    var $activityname;
    var $userid;
    var $username;
    var $status;
    var $confirmed;
    var $activities;

    /**
     * Constructor for the course_extension_collection class
     * It actually loads all extensions on an activity from the database,
     * and filters unwanted results in view_table().
     *
     * @param course integer, course id
     * @param activitycmid   integer, the activity cm id
     * @param userid   integer, optional user owning extensions to look up, otherwise get all
     * @param status   integer, optionally filter by status
     */
    function course_extension_collection($course, $activitycmid=NULL, $userid=NULL, $status=NULL, $confirmed=NULL) {
        global $CFG, $COURSE;

        $this->course = $course;
        $this->activitycmid = $activitycmid;
        $this->userid = $userid;
        $this->status = $status;
        $this->confirmed = $confirmed;

        if ($this->activitycmid) {
            // TODO: Verify this 'fast path' for loading one activity is actually faster.
            // Load just the specified activity
            $cm = get_coursemodule_from_id('assignment', $this->activitycmid);

            if (! $assignment = get_record("assignment", "id", $cm->instance)) {
                error("assignment ID was incorrect");
            }

            require_once ("$CFG->dirroot/mod/assignment/lib.php");
            require_once ("$CFG->dirroot/mod/assignment/type/$assignment->assignmenttype/assignment.class.php");
            $assignmentclass = "assignment_$assignment->assignmenttype";
            $activities[] = new $assignmentclass($cm->id, $assignment, $cm, $COURSE, $this->userid);
            $this->assignments = $activities;
            $this->activityname = $activities[0]->assignment->name;
        } else {
            // Any/all activities
            // Lookup all extensions matching the given criteria
            $activitycmidSelect = $this->activitycmid ? "ext.activitycmid = $this->activitycmid AND " : '';
            $useridSelect = $this->userid ? "ext.userid = $this->userid AND " : '';
            $statusSelect = $this->status ? "ext.status = $this->status AND " : '';

            $sql = "SELECT DISTINCT cm.*, ext.activitycmid
                    FROM {$CFG->prefix}course_modules cm, {$CFG->prefix}extension ext
                    WHERE
                    $activitycmidSelect
                    $useridSelect
                    $statusSelect
                    cm.id = ext.activitycmid";
            //echo $sql.'<br/>';
            if(!$cms = get_records_sql($sql)) {
                $error = get_string('noextensionsfound', 'extension');
                $error .= $this->activitycmid ? get_string('invalidactivityq', 'extension') : '';
                $error .= $this->userid ? get_string('invaliduserq', 'extension') : '';
                $error .= $this->status !== NULL ? get_string('invalidstatusq', 'extension') : '';
                error($error);
            }
            //print_object($cms);

            require_once ("$CFG->dirroot/mod/assignment/lib.php");
            foreach($cms as $cm) {
                $assignment = get_record("assignment", "id", $cm->instance);
                require_once ("$CFG->dirroot/mod/assignment/type/$assignment->assignmenttype/assignment.class.php");
                $assignmentclass = "assignment_$assignment->assignmenttype";
                $activities[] = new $assignmentclass($cm->id, $assignment, $cm, $COURSE, $this->userid);
                $this->assignments = $activities;
            }
//            print_object($activities);
//            print_object($this);
        }
    }

    /**
     * View the course_extension_collection as a table
     *
     */
    function view_table($context, $excludeStatus = NULL) {
		// XXX: Future: Group into module types
		global $CFG, $COURSE, $USER;

		$table->head  = array (get_string('modulename', 'assignment'),
							   get_string('user', 'moodle'),
							   get_string('originalduedate', 'extension'),
							   get_string('extensionrequested', 'extension'),
							   get_string('extensiongranted', 'extension'),
							   get_string('requestdate', 'extension'),
							   get_string('status', 'moodle'),
							   get_string('newduedate', 'extension'),
							   get_string('studentnotified', 'extension'),
							  );

		if($this->userid) {
			// Extension list is filtered by user - load one user name
			$select = 'SELECT u.id, u.firstname, u.lastname ';
			$sql = 'FROM '.$CFG->prefix.'user u '.
				   'WHERE u.id = '.$this->userid;
			$result = get_record_sql($select.$sql);
			$users[$result->id] = $result;
		} else {
			// Get all user names
			$users = array();
			foreach($this->assignments as $assignment) {
                // TODO: Get rid of Warning caused by using foreach on an object.
                if($assignment->extensiongroup->extensions) {
                    foreach ($assignment->extensiongroup->extensions as $extension) {
                        $users[$extension->userid] = $extension->userid;
                    }
				}
			}
            if(count($users)) {
                $select = 'SELECT u.id, u.firstname, u.lastname ';
                $sql = 'FROM '.$CFG->prefix.'user u '.
                           'WHERE u.id IN ('.implode(',',$users).') ';
                $users = get_records_sql($select.$sql);
            }
		}

        if(!count($users)) {
            // No users = no extensions
            print_box_start('boxaligncenter boxwidthnormal centerpara informationbox');
            echo get_string('noresults', 'extension');
            print_box_end();
        } else {
            foreach($this->assignments as $assignment) {
                if($assignment->extensiongroup->extensions) {
                    $assignmentLink = '<a href="/mod/assignment/view.php?id='.$assignment->cm->id.'">'.
                                      format_string($assignment->assignment->name).'</a>';
                    $origDate = userdate($assignment->assignment->timedue, get_string('strftimedatetimeshort', 'moodle'));

                    $cm = $this->assignments[0]->cm;
                    if (groups_get_activity_groupmode($cm) > 0) {   // Groups are being used
                        $groupmode = true;
                        if ($groups = groups_get_all_groups($cm->course, $USER->id)) {
                            // Get all groups the current user is in
                            $havegroups = true;
                        } else {
                            // User isn't in any groups. Get all groups instead.
                            $groups = groups_get_all_groups($cm->course);
                        }
                        // Get list of users in the groups
                        $members = array();
                        foreach ($groups as $group) {
                            $members += groups_get_members($group->id, $fields='u.id');
                        }
                    } else {
                        $groupmode = false;
                    }

                    // Address each extension
                    // Group by student
                    $studentsExtensions = array();

                    foreach ($assignment->extensiongroup->extensions as $extension) {
                        if ($groupmode) {
                            if (!empty($havegroups)) {
                                if (!array_key_exists($extension->userid, $members)) {
                                    // Skip extensions for users not in this user's group(s)
                                    continue;
                                }
                            } else if (false) { // Disabled, as the continue prevents display of any extensions on an assignment with groups
                                // Not using groups
                                // Skip extensions for users in groups
                                if (array_key_exists($extension->userid, $members)) {
                                    // Skip extensions for users in group(s), as this user isn't in one.
                                    continue;
                                }
                            }
                        }

                        // Filter by confirmed, if set.
                        if($this->confirmed === NULL || $extension->approvalconfirmed == $this->confirmed) {
                            // Filter by status, if set.
                            if ($excludeStatus === NULL || $extension->status != $excludeStatus) {
                                // No statuses to exclude, or extension has different status

                                if (has_capability('mod/extension:viewanyextension', $context)
                                    || has_capability('mod/extension:approveextension', $context)
                                    || has_capability('mod/extension:confirmextension', $context)
                                   ) {
                                    $extensionstaff = true;
                                } else {
                                    $extensionstaff = false;
                                }

                                if ($COURSE->registryworkflow && !$extensionstaff
                                    && !$extension->approvalconfirmed && $this->status == 0) {
                                    // When student has unconfirmed extension, allow viewing as status=0
                                    $viewAwaitingConfAsPending = true;
                                }

                                if ($this->status === NULL || $extension->status == $this->status || isset($viewAwaitingConfAsPending))  {
                                    $studentLink = '<a href="/user/view.php?id='.$extension->userid.'">'.
                                                    $users[$extension->userid]->firstname.' '.$users[$extension->userid]->lastname.
                                                   '</a>';
                                    $durationRequested = $extension->lengthrequested.' '.get_string($assignment->assignment->extensionunits, 'extension');

                                    if($extension->approvalconfirmed) {
                                        $durationGranted = $extension->lengthgranted.' '.get_string($assignment->assignment->extensionunits, 'extension');
                                    } else {
                                        $durationGranted = ' - ';
                                    }

                                    $requestDate = userdate($extension->timecreated, get_string('strftimedatetimeshort', 'moodle'));

                                    $viewQS = "?id=$extension->id";
                                    if ($extension->approvalconfirmed) {
                                        // Approval confirmed
                                        if ($extensionstaff) {
                                            // Staff view, so we can notify.
                                            $title = get_string('clicktoviewapprove', 'extension');
                                            $strstatus = "<a href=\"view.php$viewQS\" title=\"$title\">".
                                                         get_string($assignment->extensiongroup->requeststatus[$extension->status], 'extension').'</a>';

                                            if($COURSE->registryworkflow) {
                                                if($extension->approvalconfirmed) {
                                                    $strstatus .= ' ('.get_string('confirmed', 'extension').')';
                                                } else if($extension->status != 0) {
                                                    $strstatus .= ' ('.get_string('awaitingconfirmation', 'extension').')';
                                                }
                                            }

                                        } else {
                                            // Student view
                                            $title = get_string('clicktoview', 'extension');
                                            $strstatus = "<a href=\"view.php$viewQS\" title=\"$title\">".
                                                         get_string($assignment->extensiongroup->requeststatus[$extension->status], 'extension').'</a>';
                                        }
                                    } else if ($extensionstaff) {
                                        // Approval not confirmed, but staff so show status.
                                        $title = get_string('clicktoviewapprove', 'extension');
                                        $strstatus = "<a href=\"view.php$viewQS\" title=\"Click to view/approve\">".
                                                     get_string($assignment->extensiongroup->requeststatus[$extension->status], 'extension')."</a>";
                                        if($COURSE->registryworkflow) {
                                            if($extension->approvalconfirmed) {
                                                $strstatus .= ' ('.get_string('confirmed', 'extension').')';
                                            } else if($extension->status != 0) {
                                                $strstatus .= ' ('.get_string('awaitingconfirmation', 'extension').')';
                                            }
                                        } else {
                                            $durationGranted = ' - ';
                                        }

                                    } else {
                                        // Approval not confirmed, so just show "Pending" status.
                                        $title = get_string('clicktoview', 'extension');
                                        $strstatus = "<a href=\"view.php$viewQS\" title=\"Click to view/approve\">".
                                                     get_string($assignment->extensiongroup->requeststatus[0], 'extension')."</a>";
                                    }
                                    // Effective due date for student including all extensions
                                    $timestamp = $assignment->extensiongroup->get_effective_date($extension->userid);
                                    $effectivedate = userdate($timestamp, get_string('strftimedatetimeshort', 'moodle'));

                                    if ($extension->approvalconfirmed) {
                                        $studentNotified = userdate($extension->timeconfirmed, get_string('strftimedatetimeshort', 'moodle'));
                                    } else {
                                        $studentNotified = '-';
                                    }

                                    if ($extension->approvalconfirmed) {
                                        $studentNotified = userdate($extension->timeconfirmed, get_string('strftimedatetimeshort', 'moodle'));
                                    } else {
                                        $studentNotified = '-';
                                    }

                                    $table->data[] = array (
                                        $assignmentLink,
                                        $studentLink,
                                        $origDate,
                                        $durationRequested,
                                        $durationGranted,
                                        $requestDate,
                                        $strstatus,
                                        $effectivedate,
                                        $studentNotified
                                    );
                                }
                            }
                        }
                    }
                }
            }

            print_table($table);
        }

	}

    /**
     * Get a string indicating the filtering options
     **/
     public function describe_filter($context) {
        global $COURSE, $extension_requeststatus;
        if($this->activitycmid || $this->userid || $this->status !== NULL || $this->confirmed !== NULL) {
            if($this->userid) {
                $user = get_record('user', 'id', $this->userid);
                $user = $user->firstname.' '.$user->lastname.', ';
            } else {
                $user = '';
            }
            $match = get_string('extensionsmatching', 'extension');
            $act = $this->activitycmid ? get_string('activity').': '.$this->activityname.', ' : '';

            if($COURSE->registryworkflow && !
               (has_capability('mod/extension:viewanyextension', $context) ||
                has_capability('mod/extension:approveextension', $context) ||
                has_capability('mod/extension:confirmextension', $context)||
                has_capability('mod/assignment:grade', $context))
              ) {
                $conf = '';
            } else if ($this->confirmed === NULL) {
                $conf = '';
            } else if ($this->confirmed == 0){
                $conf = get_string('unconfirmed', 'extension').', ';
            } else if ($this->confirmed == 1){
                $conf = get_string('confirmed', 'extension').', ';
            }
            $stat = $this->status !== NULL ? get_string('status').': '.$extension_requeststatus[$this->status].', ' : '';

            $filters = $match.$user.$act.$conf.$stat;
            $filters = substr($filters, 0, -2).'.';
        } else {
            $filters = get_string('allcourseextensions', 'extension').'.';
        }

        return $filters;
     }
}


/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $extension An object from the form in mod_form.php
 * @return int The id of the newly inserted extension record
 */
function extension_add_instance($extension) {

    $extension->timecreated = time();

    # You may have to add extra stuff in here #

    return insert_record('extension', $extension);
}


/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $extension An object from the form in mod_form.php
 * @return boolean Success/Fail
 */
function extension_update_instance($extension) {

    $extension->timemodified = time();
    $extension->id = $extension->instance;

    # You may have to add extra stuff in here #

    return update_record('extension', $extension);
}


/**
 * Given an ID of an activity,
 * this function will permanently delete all the related extensions
 *
 * @param object $activity An activity object
 * @param string $modulename Name of the activity module
 * @return boolean Success/Failure
 */
function extension_delete_by_activity($activity, $modulename) {

    if (! $cm = get_coursemodule_from_instance($modulename, $activity->id, $activity->course)) {
        return false;
    }

    $result = true;

    if (! delete_records('extension', 'course', $cm->course, 'activitycmid', $cm->id)) {
        $result = false;
    }

    return $result;
}


/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function extension_delete_instance($id) {

    if (! $extension = get_record('extension', 'id', $id)) {
        return false;
    }

    $result = true;

    # Delete any dependent records here #

    if (! delete_records('extension', 'id', $extension->id)) {
        $result = false;
    }

    return $result;
}


/**
 * Return a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return null
 * @todo Finish documenting this function
 */
function extension_user_outline($course, $user, $mod, $extension) {
    return $return;
}


/**
 * Print a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @return boolean
 * @todo Finish documenting this function
 */
function extension_user_complete($course, $user, $mod, $extension) {
    return true;
}


/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in extension activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 * @todo Finish documenting this function
 */
function extension_print_recent_activity($course, $isteacher, $timestart) {
    return false;  //  True if anything was printed, otherwise false
}


/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function extension_cron () {
    return true;
}


/**
 * Must return an array of user records (all data) who are participants
 * for a given instance of extension. Must include every user involved
 * in the instance, independient of his role (student, teacher, admin...)
 * See other modules as example.
 *
 * @param int $extensionid ID of an instance of this module
 * @return mixed boolean/array of students
 */
function extension_get_participants($extensionid) {
    return false;
}


/**
 * This function returns if a scale is being used by one extension
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $extensionid ID of an instance of this module
 * @return mixed
 * @todo Finish documenting this function
 */
function extension_scale_used($extensionid, $scaleid) {
    $return = false;

    //$rec = get_record("extension","id","$extensionid","scale","-$scaleid");
    //
    //if (!empty($rec) && !empty($scaleid)) {
    //    $return = true;
    //}

    return $return;
}


/**
 * Checks if scale is being used by any instance of extension.
 * This function was added in 1.9
 *
 * This is used to find out if scale used anywhere
 * @param $scaleid int
 * @return boolean True if the scale is used by any extension
 */
function extension_scale_used_anywhere($scaleid) {
    if ($scaleid and record_exists('extension', 'grade', -$scaleid)) {
        return true;
    } else {
        return false;
    }
}


/**
 * Execute post-install custom actions for the module
 * This function was added in 1.9
 *
 * @return boolean true if success, false on error
 */
function extension_install() {
    return true;
}


/**
 * Execute post-uninstall custom actions for the module
 * This function was added in 1.9
 *
 * @return boolean true if success, false on error
 */
function extension_uninstall() {
    return true;
}


//////////////////////////////////////////////////////////////////////////////////////
/// Any other extension functions go here.  Each of them must have a name that
/// starts with extension_
/// Remember (see note in first lines) that, if this section grows, it's HIGHLY
/// recommended to move all funcions below to a new "localib.php" file.


/**
 * 
 */
function extension_get_extended_date($origTime, $extension) {
    return $origTime + $extension;
}


/**
 * Given an extension id, finds the extension
 *
 * @param int $id extension id
 * @return object extension record
 */

function extension_get_by_id($id) {
    global $CFG;

    $record = get_record_sql("SELECT cm.*, ext.*, md.name as modname
                           FROM {$CFG->prefix}course_modules cm,
                                {$CFG->prefix}extension ext,
                                {$CFG->prefix}modules md
                           WHERE ext.id = $id AND
                                 cm.id = ext.activitycmid AND
                                 md.id = cm.module");

    return new extension($record);
}

/**
 * Given an extension id, loads the activity and related extension details
 *
 * @param int $id extension id
 * @return object activity record with extension details in 'extension' property
 */
function extension_get_cm_by_id($id) {
    global $CFG;

    $cm = null;

    if($extension = extension_get_by_id($id)) {
        $cm = get_coursemodule_from_id($extension->modname, $extension->activitycmid, $extension->course);
        $cm->extension = $extension;

        if ($assignment = get_record("assignment", "id", $cm->instance)) {
            $cm->assignment = $assignment;
        }
    }

    return $cm;
}

/**
 * Calculates the effective due date including all approved extensions for a given activity course module and user.
 *
 * @param object $cm Course module object with activity and single extension details
 * @param int $userid user id
 * @return array(int $newdate, int $numextensions, int totalextensionunits) new date as timestamp, and number of confirmed approved extensions
 */
function extension_get_effective_date_by_cm($cm, $userid, $activity = NULL) {
    if($activity != NULL) {
        $cm->activity = $activity;
    }
    //print_object($cm);
    $course = $cm->course;
    $activitycmid = $cm->id;
    $displayunits = $cm->activity->extensionunits;
    $timedue = $cm->activity->timedue;

    return extension_get_effective_date($course, $activitycmid, $displayunits, $timedue, $userid);
}

/**
 * Calculates the effective due date including all approved extensions for a given activity details and user.
 *
 * @param int $courseid Course id
 * @param int $activitycmid Activity course module id
 * @param int $displayunits, a $extension_displaymultipliers key name
 * @param int $timedue original activity due date timestamp
 * @param int $userid user id
 * @return array(int $numextensions, int $newdate, int totalextensionunits) number of confirmed approved extensions, new date as timestamp, and the string of extension units (e.g. Days)
 */
function extension_get_effective_date($courseid, $activitycmid, $displayunits, $timedue, $userid) {
    global $CFG, $extension_displaymultipliers;
    // Load any existing extensions
    $sql = "SELECT * FROM {$CFG->prefix}extension 
            WHERE course = {$courseid} AND activitycmid = {$activitycmid}
            AND userid = {$userid}";
//            echo $sql.'<br/>';
   
    $results = new object();
    $results->numpending = 0;
    $results->numapproved = 0;
    $results->numrejected = 0;
    $results->totalextensiontime = 0;
    $results->totalextensionunits = '';
    $results->timedue = $timedue; // Default to no extension time.

    if($extensions = get_records_sql($sql)) {
        //print_object($extensions);

        foreach($extensions as $extension) {
            if($extension->approvalconfirmed) {
                if($extension->status == 1) {
                    // Count approved
                    $results->numapproved++;
                    // Calculate the time granted
                    $extensiontime = $extension->lengthgranted * $extension_displaymultipliers[$displayunits];
                    // Cumulatively add extension time and units
                    $results->totalextensiontime = $results->totalextensiontime + $extensiontime;
                    $results->totalextensionunits = $results->totalextensionunits + $extension->lengthgranted;
                } else if ($extension->status == 2) {
                    // Count rejections
                    $results->numrejected++;
                } else {
                    // Count pending
                    $results->numpending++;
                }
            }
        }

        $results->timedue = $timedue + $results->totalextensiontime;
        //print_object($results);

        return $results;
    } else {
        return NULL;
    }
}

/**
 * Returns an array of users who can work with extensions for a specified user
 * @param object $cm Course module object
 * @param string $capability Name of capability to look up
 * @param int $user user object - only return approvers relevant to this user's group (if any)
 * @param optional array $exclude Array of users to exclude from results
 */
function get_extension_users_by_role($cm, $capability, $user, $exclude = NULL) {
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);
    // Get an array of potential users with given capability
    $potentialusers = get_users_by_capability($context, $capability, '', '', '', '', '', '', false, false);

    $users = array();
    if (groups_get_activity_groupmode($cm) > 0) {   // Groups are being used
        if ($groups = groups_get_all_groups($cm->course, $user->id)) {  // Try to find all groups
            foreach ($groups as $group) {
                foreach ($potentialusers as $t) {
                    if ($t->id == $user->id) {
                        continue; // do not send self
                    }
                    if (!empty($exclude) && array_key_exists($t->id, $exclude)) {
                        continue; // do not send excluded users
                    }
                    if (groups_is_member($group->id, $t->id)) {
                        $users[$t->id] = $t;
                    }
                }
            }
        } else {
            // user not in group, try to find users without group
            foreach ($potentialusers as $t) {
                if ($t->id == $user->id) {
                    continue; // do not send self
                }
                if (!empty($exclude) && array_key_exists($t->id, $exclude)) {
                    continue; // do not send excluded users
                }
                if (!groups_get_all_groups($cm->course, $t->id)) { //ugly hack
                    $users[$t->id] = $t;
                }
            }
        }
    } else {
        // No groups
        foreach ($potentialusers as $t) {
            if ($t->id == $user->id) {
                continue; // do not send self
            }
            if (!empty($exclude) && array_key_exists($t->id, $exclude)) {
                continue; // do not send excluded users
            }
            $users[$t->id] = $t;
        }
    }
    return $users;
}
?>

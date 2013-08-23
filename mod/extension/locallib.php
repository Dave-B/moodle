<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Internal library of functions for module extension
 *
 * All the extension specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package    mod
 * @subpackage extension
 * @author     David Balch <david.balch@conted.ox.ac.uk>
 * @copyright  2012 The University of Oxford
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

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
        global $DB;
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
            $this->activity = $DB->get_record('assignment', array('id' => $this->cm->instance));
        }

        if($user) {
            $this->user &= $user;
        } else {
            $this->user = $DB->get_record('user', array('id' => $this->userid));
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
            $effectivedate = userdate($effectivedateRaw, get_string('strftimedatetimeshort'));
        } else {
            // No notification yet, so just show "Pending" status.
            $status = $extension_requeststatus[0];
        }

        $output = '<table>';
        $output .= '<tr><td class="c0">'.get_string('modulename', 'assignment').':</td>';
        $output .= '    <td class="c1">'.$assignmentLink.'</td></tr>';

        $output .= '<tr><td class="c0">'.get_string('user', 'moodle').':</td>';
        $output .= '    <td class="c1">'.$studentLink.'</td></tr>';

        $output .= '<tr><td class="c0">'.get_string('studentpermitssharing','extension').':</td>';
        $output .= '    <td class="c1">'.get_string($this->sharedetails,'extension').'</td></tr>';


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

        $output .= '<tr><td class="c0">'.get_string('howunforeseen','extension').':</td>';
        $output .= '    <td class="c1">'.$this->howunforeseen.'</td></tr>';
        $output .= '<tr><td class="c0">'.get_string('impact','extension').':</td>';
        $output .= '    <td class="c1">'.$this->impact.'</td></tr>';
        $output .= '<tr><td class="c0">'.get_string('whencircumstances','extension').':</td>';
        $output .= '    <td class="c1">'.$this->circumstancedate.'</td></tr>';
        $output .= '<tr><td class="c0">'.get_string('progress','extension').':</td>';
        $output .= '    <td class="c1">'.$this->progress.'</td></tr>';

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
        if ($effectivedate->totalextensionunits == 1) {
            // Singular
            $output .= get_string(substr($this->cm->activity->extensionunits, 0, -1), 'extension').')';
        } else if ($effectivedate->totalextensionunits > 0) {
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
        global $CFG, $DB, $SITE, $COURSE, $USER, $extension_requeststatus;

        // Check permissions for approval form
        if (has_capability('mod/extension:approveextension', $context)
            || has_capability('mod/extension:confirmextension', $context) ) {
            include_once('mod_approval_form.php');
            $mform = new mod_extension_approval_form();

            if ($mform->is_cancelled()){
                // You need this section if you have a cancel button on your form
                redirect("$CFG->wwwroot/mod/extension/index.php?id=$this->course&amp;a=$this->activitycmid", get_string('cancelled'));

            } else if ($fromform=$mform->get_data()){
                // This branch is where you process validated data.
                // Log the edit
                add_to_log($this->course, 'extension', 'update', "view.php?id=$this->course&actid=$this->activitycmid");

                $fromform->timemodified = time();

                if (!isset($fromform->existingprivatenotes)) {
                    $fromform->existingprivatenotes = '';
                }
                // TODO: Better recording of each note - user & datetime. Incorporate into log?
                // Combine old notes with any new notes
                if($fromform->privatenotes) {
                    $fromform->privatenotes = $fromform->existingprivatenotes."\n<div>".$fromform->privatenotes.'</div>';
                } else {
                    $fromform->privatenotes = $fromform->existingprivatenotes;
                }

                $userstoexclude = get_admins(); // We'll exclude admins from notification emails.

                if (!$COURSE->registryworkflow) {
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

                    $confirmers = get_extension_users_by_role($this->cm, 'mod/extension:confirmextension', $this->user, $userstoexclude);
                    foreach ($confirmers as $confirmer) {
                        if(!isset($firstconfirmer)) {
                            // TODO: #3010 Find a better way of choosing from more than one user in the relevant role
                            //   * Check old submission record for last staff modification?
                            $firstconfirmer = $confirmer;
                            $messagedata->confirmername = $firstconfirmer->firstname.' '.$firstconfirmer->lastname;
                        }
                    }
                    //echo "confirmers:<br/>";
                    //print_object($confirmers);

                    $approvers = get_extension_users_by_role($this->cm, 'mod/extension:approveextension', $this->user, $userstoexclude+$confirmers);
                    foreach ($approvers as $approver) {
                        if(!isset($firstapprover)) {
                            // TODO: #3010 Find a better way of choosing from more than one user in the relevant role
                            //   * Check old submission record for last staff modification?
                            $firstapprover = $approver;
                            $messagedata->approvername = $firstapprover->firstname.' '.$firstapprover->lastname;
                        }
                    }
                    //echo "approvers:<br/>";
                    //print_object($approvers);
                }

                if($fromform->status == 2) {
                    $fromform->lengthgranted = 0;
                }

                // Update Extension record
                if(!$DB->update_record('extension', $fromform)) {
                    print_error(get_string('inserterror' , 'extension'));
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
                        $user = $DB->get_record('user', array('id' => $this->userid));

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
                                $messagedata->approvername = $USER->firstname.' '.$USER->lastname;
                                $messagedata->confirmername = $confirmer->firstname.' '.$confirmer->lastname;

                                $messagetext = get_string('extensionawaitingconfirmationmessage', 'extension', $messagedata);

                                email_to_user($confirmer, $from, $subject, $messagetext, '', '', '', false);
                            }

                        } else {
                            // Status confirmed - notify Student, grader, and approver
                            // Notify Student
                            $user = $DB->get_record('user', array('id' => $this->userid));

                            if($fromform->status == 1) {
                                $subject = get_string('extensionapproved', 'extension');
                            } else {
                                $subject = get_string('extensionrejected', 'extension');
                            }
                            $messagetext = get_string('studentextensiondecisionmessage', 'extension', $messagedata);
                            email_to_user($user, $from, $subject, $messagetext, '', '', false);

/* Don't notify grader, per http://trac.conted.ox.ac.uk/course-qa/ticket/2826
                            // Notify grader (Tutor)
                            $exclude = $approvers + $confirmers + $userstoexclude;
                            //print_object($exclude);
                            $graders = get_extension_users_by_role($this->cm, 'mod/assignment:grade', $this->user, $exclude);
                            //print_object($graders);
                            foreach ($graders as $id => $grader) {
                                // Email grader
                                $messagedata->gradername = $grader->firstname.' '.$grader->lastname;
                                $messagetext = get_string('graderextensiondecisionmessage', 'extension', $messagedata);
                                email_to_user($grader, $from, $subject, $messagetext, '', '', false);
                            }
*/

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

                redirect("$CFG->wwwroot/mod/extension/view.php?id=$fromform->id", get_string('updated', 'moodle', get_string('extensionlabel', 'extension')));

            } else {
                // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
                // or on the first display of the form.

                // Limit notification/approval to registry capabilities
                if ($COURSE->registryworkflow && !has_capability('mod/extension:confirmextension', $context)) {
                    if($notify = $mform->getNotifyElement()) {
                        $notify->freeze();
                    }
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
        global $DB, $CFG, $COURSE, $extension_displaymultipliers;

        $this->activitycmid = $activitycmid;
        $this->displaymultipliers = $extension_displaymultipliers;

        $userselect = ($userid) ? 'userid = '.intval($userid).' AND ' : '';
        // Load extensions
        $sql = "SELECT * FROM {$CFG->prefix}extension
                          WHERE $userselect
                                course = $COURSE->id AND
                                activitycmid = $activitycmid
                          ORDER BY timecreated";
        if ($extensions = $DB->get_records_sql($sql)) {
            foreach ($extensions as $ext) {
                // Group extensions by userid
                $this->extensions[$ext->userid][] = new extension($ext);
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
            if (! $this->activity = $DB->get_record($modulename, 'id', $activitycmid)) {
                print_error('activity ID was incorrect');
            }
        }

        $this->course = $this->activity->course;
//        print_object($this);
//        print_object(debug_backtrace());
//        die();
    }

    /**
     * Calculates the extension for this extension_group's activity
     *
     * @param userid   integer, user owning extensions to look up
     * @param duedate   boolean, option to only return the time
     * @return int $extensiontime new date as timestamp
     */
    public function get_extension_time($userid, $duedate=false) {
        $extensiontime = 0;

        if (isset($this->extensions[$userid])) {
            foreach ($this->extensions[$userid] as $extension) {
                // For each of the user's extensions
                if($extension->approvalconfirmed && $extension->status == 1) {
                    // If confirmed and approved, multiply the length granted with
                    // the extension unit multiplier and add to the total
                    $extensiontime += $extension->lengthgranted * $extension->unitmultiplier;
                }
            }
        }

        if ($duedate) {
            // Add the original due date and the extension, to give the effective due date
            $extensiontime += $this->activity->timedue;
        }

        return $extensiontime;
    }

    /**
     * Calculates the total verified extensions pending, approved, and rejected
     * (indexed 0,1,2 respectively) for this extension_group.
     * Unverified extensions are always read as pending.
     *
     * @return array
     */
    public function get_extension_counts() {
        global $COURSE, $context;
        $results = array(0=>0,1=>0,2=>0,'toconfirm'=>0);

        if (!$context) {
            $context = get_context_instance(CONTEXT_COURSE, $COURSE->id);
        }

        $extensionstaff = false;
        if($COURSE->registryworkflow) {
            if (has_capability('mod/extension:viewanyextension', $context)
                || has_capability('mod/extension:approveextension', $context)
                || has_capability('mod/extension:confirmextension', $context)
               ) {
                $extensionstaff = true;
            }
        }

        if ($this->extensions) {
            foreach($this->extensions as $extensionsuser) {
                foreach($extensionsuser as $extension) {
                    if ($COURSE->registryworkflow) {
                        if($extension->status == 0 || $extension->approvalconfirmed == 1) {
                            $results[$extension->status]++;
                        } else {
                            if ( $extensionstaff) {
                                $results['toconfirm']++;
                            } else {
                                $results[0]++;
                            }
                        }
                    } else {
                        $results[$extension->status]++;
                    }
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
    public function get_extension_length($userid) {
        $results = 0;

        if (isset($this->extensions[$userid])) {
            foreach($this->extensions[$userid] as $extension) {
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
        if ($results = $this->get_extension_counts()) {
            $output = '';
            foreach($results as $key=>$val) {
                if ($key === 'toconfirm' && $val) {
                    $output .= '<a href="/mod/extension/index.php?id='.$this->course.'&amp;a='.$this->activitycmid.'&amp;exclude=0&amp;confirmed=0">'.
                               $val.' '.get_string('awaitingconfirmation', 'extension').'</a>, ';
                } else if ($val) {
                    $output .= '<a href="/mod/extension/index.php?id='.$this->course.'&amp;a='.$this->activitycmid.'&amp;status='.$key.'">'.
                               $val.' '.get_string($this->requeststatus[$key], 'extension').'</a>, ';
                }
            }
            return substr($output,0, -2);
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
        global $CFG, $DB, $COURSE;

        $this->course = $course;
        $this->activitycmid = $activitycmid;
        $this->userid = $userid;
        $this->status = $status;
        $this->confirmed = $confirmed;

        if ($this->activitycmid) {
            // TODO: Verify this 'fast path' for loading one activity is actually faster.
            // Load just the specified activity
            $cm = get_coursemodule_from_id('assignment', $this->activitycmid);

            if (! $assignment = $DB->get_record("assignment", array("id" => $cm->instance))) {
                print_error("assignment ID was incorrect");
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
            if(!$cms = $DB->get_records_sql($sql)) {
                $error = get_string('noextensionsfound', 'extension');
                $error .= $this->activitycmid ? get_string('invalidactivityq', 'extension') : '';
                $error .= $this->userid ? get_string('invaliduserq', 'extension') : '';
                $error .= $this->status !== NULL ? get_string('invalidstatusq', 'extension') : '';
                print_error($error);
            }
            //print_object($cms);

            require_once ("$CFG->dirroot/mod/assignment/lib.php");
            foreach($cms as $cm) {
                $assignment = $DB->get_record("assignment", array("id" => $cm->instance));
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
        global $CFG, $DB, $OUTPUT, $COURSE, $USER;

        $table = new html_table();

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
            $result = $DB->get_record_sql($select.$sql);
            $users[$result->id] = $result;
        } else {
            // Get all user names
            $users = array();
            foreach($this->assignments as $assignment) {
                // TODO: Get rid of Warning caused by using foreach on an object.
                if ($assignment->extensiongroup->extensions) {
                    foreach ($assignment->extensiongroup->extensions as $userid => $extension) {
                        $users[$userid] = $userid;
                    }
                }
            }
            if(count($users)) {
                $select = 'SELECT u.id, u.firstname, u.lastname ';
                $sql = 'FROM '.$CFG->prefix.'user u '.
                           'WHERE u.id IN ('.implode(',',$users).') ';
                $users = $DB->get_records_sql($select.$sql);
            }
        }

        if(!count($users)) {
            // No users = no extensions
            $OUTPUT->box_start('boxaligncenter boxwidthnormal centerpara informationbox');
            echo get_string('noresults', 'extension');
            $OUTPUT->box_end();
        } else {
            foreach($this->assignments as $assignment) {
                if($assignment->extensiongroup->extensions) {
                    $assignmentLink = '<a href="/mod/assignment/view.php?id='.$assignment->cm->id.'">'.
                                      format_string($assignment->assignment->name).'</a>';
                    $origDate = userdate($assignment->assignment->timedue, get_string('strftimedatetimeshort'));

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

                    foreach ($assignment->extensiongroup->extensions as $extensionuser) {
                        foreach ($extensionuser as $extension) {
                            $viewAwaitingConfAsPending = null;
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

                                    $extensionstaff = false;
                                    if (has_capability('mod/extension:viewanyextension', $context)
                                        || has_capability('mod/extension:approveextension', $context)
                                        || has_capability('mod/extension:confirmextension', $context)
                                       ) {
                                        $extensionstaff = true;
                                    }

                                    if ($COURSE->registryworkflow && !$extensionstaff
                                        && !$extension->approvalconfirmed && $this->status == 0) {
                                        // When student has unconfirmed extension, allow viewing as status=0
                                        $viewAwaitingConfAsPending = true;
                                    }
                                    // Show extensions with relevant status
                                    if ($this->status === NULL || isset($viewAwaitingConfAsPending) ||
                                        ($extension->status == $this->status && ($extension->approvalconfirmed || $extensionstaff) )
                                       )  {
                                        $studentLink = '<a href="/user/view.php?id='.$extension->userid.'">'.
                                                        $users[$extension->userid]->firstname.' '.$users[$extension->userid]->lastname.
                                                       '</a>';
                                        $durationRequested = $extension->lengthrequested.' '.get_string($assignment->assignment->extensionunits, 'extension');

                                        if($extension->approvalconfirmed) {
                                            $durationGranted = $extension->lengthgranted.' '.get_string($assignment->assignment->extensionunits, 'extension');
                                        } else {
                                            $durationGranted = ' - ';
                                        }

                                        $requestDate = userdate($extension->timecreated, get_string('strftimedatetimeshort'));

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
                                        $timestamp = $assignment->extensiongroup->get_extension_time($extension->userid, true);
                                        $effectivedate = userdate($timestamp, get_string('strftimedatetimeshort'));

                                        if ($extension->approvalconfirmed) {
                                            $studentNotified = userdate($extension->timeconfirmed, get_string('strftimedatetimeshort'));
                                        } else {
                                            $studentNotified = '-';
                                        }

                                        if ($extension->approvalconfirmed) {
                                            $studentNotified = userdate($extension->timeconfirmed, get_string('strftimedatetimeshort'));
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
            }

            //print_table($table);
            //echo $OUTPUT->table($table);
            echo html_writer::table($table);
        }

    }

    /**
     * Get a string indicating the filtering options
     **/
     public function describe_filter($context) {
        global $DB, $COURSE, $extension_requeststatus;
        if($this->activitycmid || $this->userid || $this->status !== NULL || $this->confirmed !== NULL) {
            if($this->userid) {
                $user = $DB->get_record('user', array('id' => $this->userid));
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
    global $CFG, $DB;

    $record = $DB->get_record_sql("SELECT cm.*, ext.*, md.name as modname
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
    global $CFG, $DB;

    $cm = null;

    if($extension = extension_get_by_id($id)) {
        $cm = get_coursemodule_from_id($extension->modname, $extension->activitycmid, $extension->course);
        $cm->extension = $extension;

        if ($assignment = $DB->get_record("assignment", array("id" => $cm->instance))) {
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
    global $CFG, $DB, $extension_displaymultipliers;
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

    if($extensions = $DB->get_records_sql($sql)) {
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
    global $COURSE;
    $context = get_context_instance(CONTEXT_COURSE, $COURSE->id);
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

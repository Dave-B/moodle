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
 * English strings for extension
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package mod
 * @subpackage extension
 * @author  David Balch <david.balch@conted.ox.ac.uk>
 * @copyright The University of Oxford
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['allcourseextensions'] = 'All extensions in this course';
$string['allowextension'] = 'Allow extension';
$string['approved'] = 'Approved';
$string['awaitingconfirmation'] = 'Awaiting confirmation';
$string['clicktoview'] = 'Click to view extension';
$string['clicktoviewapprove'] = 'Click to view/approve extension';
$string['confirmed'] = 'Confirmed';
$string['day'] = 'Day';
$string['days'] = 'Days';
$string['approvernewextensionmessage'] = 'Dear {$a->approvername},\n
The student {$a->studentname} has applied for an extension on the
following assignment:

      Course: {$a->coursename}
  Assignment: {$a->assignmentname}

Please check the course website to approve/reject the extension:
  {$a->extensionurl}

All extensions for this assignment are listed here:
  {$a->extensionlisturl}

Best regards,
The Department for Continuing Education

{$a->siteurl}
';
$string['approvernewextensionmessage_workflow'] = 'Dear {$a->approvername},\n
The student {$a->studentname} has applied for an extension on the
following assignment:

      Course: {$a->coursename}
  Assignment: {$a->assignmentname}

Please check the course website to approve/reject the extension:
  {$a->extensionurl}

All extensions for this assignment are listed here:
  {$a->extensionlisturl}

Once you have approved/rejected the extension, {$a->confirmername}
will be asked to confirm the extension, which will send the student
notice of the decision.

Best regards,
The Department for Continuing Education

{$a->siteurl}
';
$string['confirmernewextensionmessage_workflow'] = 'Dear {$a->confirmername},\n
The student {$a->studentname} has applied for an extension on the
following assignment:

      Course: {$a->coursename}
  Assignment: {$a->assignmentname}

{$a->approvername} has been asked to approve/reject the extension:
  {$a->extensionurl}

Once they have done this, you will be asked to confirm the decision.

All extensions for this assignment are listed here:
  {$a->extensionlisturl}

Best regards,
The Department for Continuing Education

{$a->siteurl}
';
$string['excludestatus'] = 'Exclude status';
$string['extensionapplicationdeadline'] = 'Deadline for extension application';
$string['extension'] = 'Extension';
$string['extensionapproved'] = 'Extension approved';
$string['extensionawaitingconfirmation'] = 'Extension awaiting confirmation';
$string['extensionawaitingconfirmationmessage'] = 'Dear {$a->confirmername},\n
The student {$a->studentname}\'s extension request has been {$a->status} by
{$a->approvername}.

      Course: {$a->coursename}
  Assignment: {$a->assignmentname}
    Decision: {$a->status}

Please confirm the decision here:
  {$a->extensionurl}

Once you have done this, the student will be notified of the decision.

Best regards,
The Department for Continuing Education

{$a->siteurl}
';
$string['extensionfieldset'] = 'Custom example fieldset';
$string['extensionintro'] = 'Extension Intro';
$string['extensionlabel'] = 'Extension';
$string['extensionlength'] = 'Extension length';
$string['extensionlengthguidance'] = 'If you require an extension of more than 2 weeks then you should contact the Registry at <a href=\"mailto:registry@conted.ox.ac.uk\">registry@conted.ox.ac.uk</a> for an application form. Please see your course handbook for further details.';
$string['extensionname'] = 'Extension Name';
$string['extensionname_help'] = 'This is the content of the help tooltip associated with the extensionname field. Markdown syntax is supported.';
$string['extensionrejected'] = 'Extension rejected';
$string['extensionrequested'] = 'Extension requested';
$string['extensionrequestrecieved'] = 'Your extension request has been recieved. It will be reviewed and you will be notified when it is approved or rejected.';
$string['extensions'] = 'Extensions';
$string['extensiongranted'] = 'Extension granted';
$string['extensionsubmitted'] = 'Extension submitted';
$string['extensionunits'] = 'Extension units';
$string['extensionsmatching'] = 'Extensions matching: ';
$string['graderextensiondecisionmessage'] = 'Dear {$a->gradername},\n
A student\'s extension application has been confirmed as {$a->status}:

        Course: {$a->coursename}
    Assignment: {$a->assignmentname}
       Student: {$a->studentname}
        Status: {$a->status}

  New due date: {$a->effectivedate}
      Comments: {$a->studentmessage}

Details are available here:
  {$a->extensionurl}

Best regards,
The Department for Continuing Education

{$a->siteurl}
';
$string['grantextension'] = 'Grant extension';
$string['hour'] = 'Hour';
$string['hours'] = 'Hours';
$string['includesextension'] = 'Includes an extension of ';
$string['includes1extensionfor'] = 'Includes 1 extension for ';
$string['includesextensionstotaling'] = 'Includes {$a} extensions totaling ';
$string['incweekendsholidays'] = '(including weekends and holidays)';
$string['inserterror'] = 'Could not record extension.';
$string['invalidactivityq'] = ' Invalid activity? ';
$string['invalidstatusq'] = ' Invalid status? ';
$string['invaliduserq'] = ' Invalid user? ';
$string['maxextensionexceptional'] = 'Maximum exceptional extension';
$string['maxextensionstandard'] = 'Maximum standard extension';
$string['minute'] = 'Minute';
$string['minutes'] = 'Minutes';
$string['modulename'] = 'Extension';
$string['modulename_help'] = 'The extension module augments the assignment module, allowing students to request time extensions for their work.';
$string['modulenameplural'] = 'Extensions';
$string['newduedate'] = 'New due date';
$string['newextensionrequest'] = 'New extension request';
$string['noextensionsapproved'] = 'No extensions approved.';
$string['noextensionsfound'] = 'No extensions found.';
$string['notenabled'] = 'Not enabled.';
$string['nopermission'] = 'You do not have permission to view this extension.';
$string['noresults'] = 'No results';
$string['notifystudent'] = 'Notify student';
$string['onlyregistry'] = '(Can only be set by Registry.)';
$string['originalduedate'] = 'Original due date';
$string['pending'] = 'Pending';
$string['pendingextensions'] = 'Pending extensions';
$string['pluginadministration'] = 'extension administration';
$string['pluginname'] = 'extension';
$string['privatenotes'] = 'Private notes';
$string['privatenotesexisting'] = 'Existing private notes';
$string['reasonforrequest'] = 'Reason for request';
$string['rejected'] = 'Rejected';
$string['requestdate'] = 'Request date';
$string['requestextension'] = 'Request time extension';
$string['requestspending'] = '{$a} extensions pending';
$string['studentextensiondecisionmessage'] = 'Dear {$a->studentname},\n
Your application for an extension has been {$a->status}:

        Course: {$a->coursename}
    Assignment: {$a->assignmentname}
        Status: {$a->status}

Time requested: {$a->lengthrequested}
  Time granted: {$a->lengthgranted}

  New due date: {$a->effectivedate}
      Comments: {$a->studentmessage}

Please check the course website to confirm the due date:
  {$a->extensionurl}

Best regards,
The Department for Continuing Education

{$a->siteurl}
';
$string['studentnotified'] = 'Student notified';
$string['submitrequest'] = 'Submit extension request';
$string['supportingevidenceguidance'] = 'If you have evidence to support your extension request (e.g. medical certificate, employer\'s letter), please upload it here.';
$string['supportingevidence'] = 'Supporting evidence';
$string['unconfirmed'] = 'Unconfirmed';
$string['viewextensions'] = 'View extensions';
$string['withextensionto'] = 'With extension to';
$string['yourextensions'] = 'Your extensions';

$string['extension:request'] = 'Request extensions';
$string['extension:approveextension'] = 'Approve extensions';
$string['extension:confirmextension'] = 'Confirm extensions';
$string['extension:viewanyextension'] = 'View extensions';
$string['extension:viewownextension'] = 'View own extensions';
?>

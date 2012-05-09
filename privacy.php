<?php

require_once("config.php");

$PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));
$PAGE->set_url('/privacy.php');

$title = 'Privacy policy';
$PAGE->set_title($SITE->fullname.': '.$title);
$PAGE->set_heading($SITE->fullname);

$PAGE->navbar->add($title);

echo $OUTPUT->header();
echo $OUTPUT->heading($title);


echo $OUTPUT->box_start('generalbox', 'intro');
?>

<p>This policy explains what types of personal information will be gathered when you visit the <i><?php echo $SITE->fullname; ?></i> website, and how this information will be used.</p>

<p>Please note that although this website provides links to other websites, this policy only applies to the <i><?php echo $SITE->fullname; ?></i> webpages.</p>

<h2>Information collected</h2>
<p>Usually we will have already loaded your name and email address to create your account; if we have not already created your account, you will need to supply that information to access the site and courses. On some parts of the website, you may be asked to provide additional personal information in order to facilitate the functioning of the site.</p>
<p>By supplying this information you are consenting to the University holding and using it for the purposes for which it was provided. Information provided will be kept for as long as is necessary to fulfil that purpose. Personal information collected and/or processed by the University is held in accordance with the provisions of the Data Protection Act 1998.</p>


<h2 id="cookies">Cookies</h2>

<p>Cookies are small text files that are placed on your computer by websites that you visit. They are widely used in order to make websites work, or work more efficiently, as well as to provide information to the owners of the site. For more information about cookies you can visit <a href="http://www.allaboutcookies.org">http://www.allaboutcookies.org</a>.</p>

The following table explains the cookies we use and why.

<table class="generaltable">
    <thead>
        <tr>
            <th>Cookie</th>
            <th>Name</th>
            <th>Purpose</th>
            <th>More information</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Moodle username</td>
            <td>MOODLEID1_</td>
            <td>Remembers your username for the login form, so you don't have to type it in every time.</td>
            <td>Expires after two months.</td>
        </tr>
        <tr>
            <td>Moodle session</td>
            <td>MoodleSession</td>
            <td>Required to keep you logged in whilst using the site.</td>
            <td>Expires when you close your browser.</td>
        </tr>
    </tbody>
</table>


<h2>How the information collected is used</h2>

<p>Personal information provided by you will only be used for the purpose stated when the information is collected. The personal information that you provide when you register to use the website may be used:</p>
<ul>
    <li>to deal with your enquiry or registration and to provide you with appropriate services, which may include sending you further information about the activities of the University of Oxford;</li>
    <li>for the purpose of our research to help us plan and improve our services.</li>
</ul>

</p>Your personal information will not be sold, rented or otherwise transferred to a third party without your explicit consent. Personal data will be: processed fairly and lawfully; processed for limited purposes and not in any manner incompatible with those purposes; adequate, relevant and not excessive; accurate; not kept longer than is necessary; processed in line with data subjects’ rights; secure and not transferred to countries that do not protect personal data adequately.</p>

<p>While we take all reasonable precautions to ensure that other organisations with whom we deal have good security practices, we cannot be held responsible for the privacy practices of those organisations whose websites may be linked to our services.</p>

<p>Any queries or concerns about the privacy of this website should be sent to the <a href="mailto:data.protection@admin.ox.ac.uk?subject=<?php echo $CFG->wwwroot; ?>+Privacy+policy">Data Protection Officer</a>.</p>

<?php
echo $OUTPUT->box_end();
echo $OUTPUT->footer();

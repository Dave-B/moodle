<?php  // $Id$

require_once('../../config.php');


$PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));
$PAGE->set_url('/login/climateeducation.net/privacypolicy.php');

$title = 'Privacy Policy';

$PAGE->set_title($SITE->fullname.': '.$title);

$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add($title);

echo $OUTPUT->header();

echo $OUTPUT->heading($title);

echo $OUTPUT->box_start('generalbox', 'intro');
?>

<h1>Introduction</h1>

<p>This policy explains what types of personal information will be gathered when you visit the climate<i>education</i>.net website, and how this information will be used.</p>
<p>Please note that although this website provides links to other websites, this policy only applies to the climate<i>education</i>.net webpages (i.e. those ending in climate<i>education</i>.net).</p>
<h2>Information collected</h2>
<p>On some parts of the climate<i>education</i>.net website, you will be asked to provide some limited personal information in order to facilitate the functioning of the climate<i>education</i>.net site, including the operation of the web-based knowledge sharing community. The University may store this information manually or electronically and share it with the project funding body, the Natural Environment Research Council (NERC) and the project partner, the Met Office. By supplying this information you are consenting to the University holding and using it for the purposes for which it was provided. Information provided will be kept for as long as is necessary to fulfil that purpose. Personal information collected and/or processed by the University is held in accordance with the provisions of the Data Protection Act 1998.</p>
<p>When you visit some pages on the site, your computer may be issued with a small file (a "cookie") for the purposes of managing and improving the services on the website. Cookies do not contain any personally identifying information. You can set your browser to refuse cookies or warn you before accepting them. For more information about cookies you can visit <a href="http://www.allaboutcookies.org">www.allaboutcookies.org</a>.</p>
<h2>How the information collected is used</h2>
<p>Personal information provided by you will only be used for the purpose stated when the information is collected. The personal information that you provide when you register to use the website may be used:</p>
<ul>
    <li>to deal with your enquiry or registration and to provide you with appropriate services, which may include sending you further information about courses;</li>
    <li>for the purpose of our research to help us plan and improve our services.</li>
</ul>
<p>Your personal information will not be sold, rented or otherwise transferred to a third party without your explicit consent. Personal data will be: processed fairly and lawfully; processed for limited purposes and not in any manner incompatible with those purposes; adequate, relevant and not excessive; accurate; not kept longer than is necessary; processed in line with data subjects’ rights; secure and not transferred to countries that do not protect personal data adequately.</p>
<p>While we take all reasonable precautions to ensure that other organisations with whom we deal have good security practices, we cannot be held responsible for the privacy practices of those organisations whose websites may be linked to our services.</p>
<p>Any queries or concerns about the privacy of this website should be sent to the Data Protection Officer at <a href="mailto:data.protection@admin.ox.ac.uk">data.protection@admin.ox.ac.uk</a>.</p>

<?php
echo $OUTPUT->box_end();
echo $OUTPUT->footer();

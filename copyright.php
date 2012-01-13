<?php

require_once("config.php");

$PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));
$PAGE->set_url('/copyright.php');

$copyright = 'Copyright statement';

$PAGE->set_title($SITE->fullname.': '.$copyright);

$PAGE->navbar->add($copyright);
$PAGE->set_heading($copyright);

echo $OUTPUT->header();

echo $OUTPUT->heading($copyright);

echo $OUTPUT->box_start('generalbox', 'intro');
?>

<h3>Course content</h3>
<p>All rights, including copyright, in the content of these Web pages are owned or controlled for these purposes by the Department for Continuing Education at the University of Oxford.</p>
<p>In accessing these Web pages, you agree that you may only download the content for your own personal non-commercial use.</p>
<p>You are not permitted to copy, broadcast, download, store (in any medium), transmit, show or play in public, adapt or change in any way the content of these Web pages for any other purpose whatsoever without the prior written permission of the Department for Continuing Education at the University of Oxford.</p>

<p>For rights clearance please contact Technology-Assisted Lifelong Learning, University of Oxford:</p>
<ul>
  <li>Email: <a href="mailto:tall@conted.ox.ac.uk">tall@conted.ox.ac.uk</a></li>
  <li>Phone: +44 (0)1865 280978</li>
</ul>

<h3>User contributions</h3>
<p>The copyright for all original user contributions within courses (including but not limited to forum posts, blogs, and assignments), remains with the contributor.</p>
<p>You are not permitted to copy, broadcast, download, store (in any medium), transmit, show or play in public, adapt or change in any way, or for any purpose whatsoever, the contributions of others, without having first obtained prior written permission from the individuals concerned.</p>

<?php
echo $OUTPUT->box_end();
echo $OUTPUT->footer();



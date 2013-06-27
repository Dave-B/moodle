<?php

require_once("config.php");

$PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));
$PAGE->set_url('/copyright.php');

$title = 'Copyright statement';

$PAGE->set_title($SITE->fullname.': '.$title);

$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add($title);

echo $OUTPUT->header();

echo $OUTPUT->heading($title);

echo $OUTPUT->box_start('generalbox', 'intro');
?>

<h3>Course content</h3>
<p>All rights, including copyright, in the content of these Web pages are owned or controlled for these purposes by the University of Oxford.</p>
<p>In accessing these Web pages, you agree that you may only download the content for your own personal non-commercial use.</p>
<p>You are not permitted to copy, broadcast, download, store (in any medium), transmit, show or play in public, adapt or change in any way the content of these Web pages for any other purpose whatsoever without the prior written permission of the University of Oxford.</p>

<p>For rights clearance please contact the Bridging team at MPLS, University of Oxford:</p>
<ul>
  <li>Email: <a href="mailto:bridging@mpls.ox.ac.uk">bridging@mpls.ox.ac.uk</a></li>
</ul>

<?php
echo $OUTPUT->box_end();
echo $OUTPUT->footer();



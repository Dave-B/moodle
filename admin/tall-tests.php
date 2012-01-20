<?php // $Id$

// Set TALL config options 

require_once('../config.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('talltests');

echo $OUTPUT->header();

echo '<h1>TALL tests</h1>';


echo $OUTPUT->footer();

<?php  /// Moodle Configuration File 

unset($CFG);

$CFG = new stdClass();
$CFG->dbtype    = 'mysql';
$CFG->dbhost    = 'localhost';
$CFG->dbname    = 'moodledev';
$CFG->dbuser    = 'moodledev';
include_once('lib/git.php');
$CFG->dbpass  = git_get_db_pass($gitcurrentbranch); // Current git branch name
$CFG->dbpersist =  false;
$CFG->prefix    = 'moodle_19_stable_';

$CFG->wwwroot   = 'http://localmoodle';
$CFG->dirroot   = '/var/www/moodledev/moodle';
$CFG->dataroot  = '/var/www/moodledev/mdata_moodle_19_stable';
$CFG->admin     = 'admin';

$CFG->directorypermissions = 00777;  // try 02777 on a server in Safe Mode

$CFG->passwordsaltmain = 'eDLWFV!*+*lK2OX[U{2uB+&u-[v ';

require_once("$CFG->dirroot/lib/setup.php");
// MAKE SURE WHEN YOU EDIT THIS FILE THAT THERE ARE NO SPACES, BLANK LINES,
// RETURNS, OR ANYTHING ELSE AFTER THE TWO CHARACTERS ON THE NEXT LINE.
?>

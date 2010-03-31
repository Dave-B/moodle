<?php

/**
 * Help Moodle development in a Git SCM environment
 *
 * This library is made to simplify using multiple Git branches when
 * modifying Moodle.
 *
 * @author David Balch
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

if ($_SERVER['SCRIPT_NAME'] == '/lib/git.php') {
    // Script called directly, so prepare for debugging.
    error_reporting(E_ALL & ~E_NOTICE);
    ini_set('display_errors', 'On');

    header('Content-Type: text/plain');
}

$gitpath = '/usr/bin/git';
$repospath = $_SERVER['DOCUMENT_ROOT'];
//echo $repospath."\n";
$passfile = $repospath.'/pass.csv';

function git_get_status() {
    global $gitpath, $repospath;

    $command = 'cd '.$repospath.'; '.$gitpath.' status';
    //echo '$command: `'.$command."`\n";

    exec($command, $output, $return_var);
    //echo '$return_var: '.$return_var."\n";

    return $output;
}

function git_get_current_branch() {
    $status = git_get_status();
    return substr($status[0], 12);
}

function git_get_current_branch_clean() {
    // Make lowercase, remove spaces.
    $branch = git_get_current_branch();
    $branch = str_replace(' ', '', $branch);
    $branch = strtolower($branch);
    return $branch;
}
//echo git_get_current_branch();

function git_set_db_pass($branch, $pass) {
    global $passfile;
    $fh = fopen($passfile, 'a');
    fwrite($fh, $branch.','.$pass."\n");
    fclose($fh);
}
//git_set_db_pass('branch3', 'pass3');

function git_get_db_pass($branch) {
    global $passfile;
    $fh = fopen($passfile, 'r');
    $contents = fread($fh, filesize($passfile));
    fclose($fh);

    $lines = explode("\n", $contents);
    foreach($lines as $line) {
        if (strstr($line, $branch.',')) {
            $line = explode(",", $line);
            return $line[1];
        }
    }
    return false;
}
//echo git_get_db_pass('branch');
?>

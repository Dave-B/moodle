<?php

require_once("config.php");
require_login();

$redirect = optional_param('redirect', NULL, PARAM_TEXT);
$dest     = optional_param('dest', '', PARAM_TEXT);
$redirectdelay = 1500;

$refsites = array(
               'oxref' => array('http://www.oxfordreference.com/', 'Oxford Reference Online Core Collection'),
               'oed' => array('http://www.oed.com/', 'Oxford English Dictionary'),
               'oxdnb' => array('http://www.oxforddnb.com/', 'Dictionary of National Biography'),
               'anb' => array('http://www.anb.org/', 'American National Biography'),
               'oxscholar' => array('http://www.oxfordscholarship.com/', 'Oxford Scholarship Online'),
               'groveart' => array('http://www.oxfordartonline.com/', 'Grove Art Online'),
               'grovemusic' => array('http://www.oxfordmusiconline.com/', 'Grove Music Online')
            );


if ($redirect && $dest) {
    // We have querystring instructions to go to a particular oxref resource
    $site = $refsites[$redirect];
    if($site) {
        // Looks like a valid reference site, set session var so we can skip the referer check after this redirect.
        //echo $redirect;
        $SESSION->oxref[$redirect] = true;

        // Redirect
        $encodedurl = $site[0].$dest; // urlencode($site[0].$dest);
        $redirectmessage = '<p>This page will redirect you to the <i>'.$site[1].'</i> resource in a few seconds (<a href="'.$encodedurl.'">Click here if you are not redirected</a>).</p>';
        redirect($encodedurl, $redirectmessage, $redirectdelay/1000);
    }
}

$PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));
$PAGE->set_url('/oxref.php');

$title = 'Oxford Reference Online';
$PAGE->set_title($SITE->fullname.': '.$title);
$PAGE->set_heading($SITE->fullname);

$PAGE->navbar->add($title);


echo $OUTPUT->header();
echo $OUTPUT->heading($title);

echo $OUTPUT->box_start('generalbox', 'intro');
?>

<p>Whilst studying this course you have access to a selection of subscription-based online resources from Oxford University Press (OUP), at no additional cost. This comprehensive collection contains over 250 dictionaries and reference titles, and nearly 9,000 full-text books, covering a wide range of subjects.</p>
<p>You will be logged in to each resource collection when you visit it via the relevant link on this page.</p>
<p>Full access is provided to:</p>

<ul>
<?php
    foreach($refsites as $sitekey => $sitedetails) {
        if ($sitekey != 'oxref' && $sitekey != 'oxscholar') {
            if ($sitekey == $redirect) {
                echo '<li><strong><a href="'.$sitedetails[0].$dest.'">'.$sitedetails[1]."</a></strong></li>\n";
            } else {
                echo '<li><a href="'.$sitedetails[0].'">'.$sitedetails[1]."</a></li>\n";
            }
        }
    }
?>
</ul>

<p>Access is also provided to the majority of resources in the following collections:</p>

<ul>
<?php
    foreach($refsites as $sitekey => $sitedetails) {
        if ($sitekey == 'oxref' || $sitekey == 'oxscholar') {
            if ($sitekey == $redirect) {
                echo '<li><strong><a href="'.$sitedetails[0].$dest.'">'.$sitedetails[1]."</a></strong></li>\n";
            } else {
                echo '<li><a href="'.$sitedetails[0].'">'.$sitedetails[1]."</a></li>\n";
            }
        }
    }
?>
</ul>

<p>For more information about the OUP online resources available to you and tips for searching the collections, see the Oxford Reference Online <a href="http://onlinesupport.conted.ox.ac.uk/nml/oxref.php">support page</a>.</p>

<p><strong>Please note</strong>: If you close your web browser you must either return to this page and use the links above or click on a link to a resource directly from your online course materials to ensure you are logged on and can access it.</p>

<?php
echo $OUTPUT->box_end();
echo $OUTPUT->footer();



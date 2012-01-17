<?php

require_once("config.php");

$redirect = optional_param('redirect', NULL, PARAM_TEXT);
$dest     = optional_param('dest', '', PARAM_TEXT);

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
    $site = $refsites[$redirect];

    $encodedurl = $site[0].$dest; // urlencode($site[0].$dest);
    @header('Location: '.$encodedurl);
    $redirectmessage = '<p>This page will redirect you to the <i><a href="'.$encodedurl.'">'.$site[1].'</a></i> resource in a few seconds.</p>';
    //redirect($encodedurl, $redirectmessage, 5);
    //<meta http-equiv="refresh" content="5; url=http://www.oed.com/view/Entry/64893" />
    //$OUTPUT->metarefreshtag = '<meta http-equiv="refresh" content="5; url='.$encodedurl.'" />';
}

$PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));
$PAGE->set_url('/oxref.php');

$title = 'Oxford Reference Online';
$PAGE->set_title($SITE->fullname.': '.$title);
$PAGE->set_heading($SITE->fullname);

$PAGE->navbar->add($title);


echo $OUTPUT->header();
echo $OUTPUT->heading($title);


if ($redirect && $dest) {
    echo $OUTPUT->box_start('generalbox', 'intro');
    echo $redirectmessage;
    echo $OUTPUT->box_end();
}

echo $OUTPUT->box_start('generalbox', 'intro');
?>

<p>Oxford Reference Online is a huge and comprehensive resource that contains over 120 dictionaries and reference titles covering the complete subject spectrum: from General Reference and Language to Science and Medicine, and from Humanities and Social Sciences to Business and Professional.</p>
<ul>
<?php
    foreach($refsites as $sitekey => $sitedetails) {
        if ($sitekey == $redirect) {
            echo '<li><strong><a href="'.$sitedetails[0].$dest.'">'.$sitedetails[1]."</a></strong></li>\n";
        } else {
            echo '<li><a href="'.$sitedetails[0].'">'.$sitedetails[1]."</a></li>\n";
        }
    }
?>
</ul>

<p>Whilst studying this course you have access to Oxford Reference Online at no additional cost.</p>
<p>You will be logged in to each resource when you visit them via the relevant link on this page - if you close your browser you must use the link on this page again.</p>

<?php
echo $OUTPUT->box_end();
echo $OUTPUT->footer();



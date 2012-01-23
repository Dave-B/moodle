<?php  // $Id$

    require_once('../../config.php');

$PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));
$PAGE->set_url('/login/climateeducation.net/noticeandtakedownpolicy.php');

$title = 'Notice and Take Down Policy';

$PAGE->set_title($SITE->fullname.': '.$title);

$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add($title);

echo $OUTPUT->header();

echo $OUTPUT->heading($title);

echo $OUTPUT->box_start('generalbox', 'intro');
?>

<h1>Introduction</h1>
<p>Should you discover any content on the climate<i>education</i>.net website which you believe infringes any UK law or rights you may possess, please contact the site manager, following the process outlined below.</p>

<h2>How to complain</h2>
<p>Please write to the site manager stating the following information:</p>
<ol>
    <li><p></p>Your contact details (name, address, telephone number and email address).</li>
    <li><p></p>Full details of the material concerned, including its URL on the website.</li>
    <li><p></p>An explanation of the basis of your complaint.</li>
    <li><p></p>Action requested.</li>
    <li><p></p>An assertion that your complaint is made in good faith and is accurate.</li>
    <li><p></p>If your complaint is about breach of your own copyright, please include a statement that, under penalty or perjury, you are the rights owner or are authorised to act for the rights holder.</li>
</ol>

<p>Please send your complaint in writing to:</p>
<address><p>
FAO: climate<i>education</i>.net Site Manager<br />
Re: Notice and Take Down<br />
Continuing Professional Development Centre<br />
University of Oxford<br />
Department for Continuing Education<br />
Littlegate House<br />
16-17 St Ebbes Street<br />
Oxford OX1 1PT<br />
United Kingdom</p>
</address>

<p>In addition, please send an email with the message header “climate<i>education</i>.net Notice and Take Down” to the following email address:  <a href="mailto:reciprocate@conted.ox.ac.uk">reciprocate@conted.ox.ac.uk</a>.</p>

<h2>What happens next</h2>
<p>On receipt of your complaint our ‘Notice and Take Down’ procedure is invoked as follows and we will:</p>
<ol>
    <li><p>Acknowledge your complaint.</p></li>
    <li>
        <p>Make an initial judgement of the validity of the complaint and:</p>
        <ol type="i">
            <li><p>if your complaint is plausible, based on UK law, the material concerned will be removed from the website, or access suspended, pending verification of the complaint;</p></li>
            <li><p>if we judge the complaint implausible or incorrect, we will inform you of this and our reasons.</p></li>
        </ol>
    </li>
    <li>Where necessary, seek professional legal advice on your complaint.</li>
    <li><p>Contact the individual contributor who published the material about which you are complaining.  The contributor will be notified that the material is subject to a complaint, under what allegations, and be given the opportunity to refute the complaint.</p></li>
    <li><p>Investigate your complaint and notify you of the result of this investigation and what actions we have taken/will take.</p></li>
    <li><p>If the investigation finds in your favour we will remove the material permanently from the climate<i>education</i>.net website unless you give us permission to use it.  A record of the material may remain on the website logs.</p></li>
</ol>

<?php
echo $OUTPUT->box_end();
echo $OUTPUT->footer();

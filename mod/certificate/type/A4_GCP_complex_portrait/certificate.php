<?php
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from view.php in mod/tracker
}

// Date formatting - can be customized if necessary
$certificatedate = '';
if ($certrecord->certdate > 0) {
    $certdate = $certrecord->certdate;
} else $certdate = certificate_generate_date($certificate, $course);

if ($certificate->printdate > 0) {
    if ($certificate->datefmt == 1) {
        $certificatedate = str_replace(' 0', ' ', strftime('%B %d, %Y', $certdate));
    } if ($certificate->datefmt == 2) {
        $certificatedate = date('F jS, Y', $certdate);
    } if ($certificate->datefmt == 3) {
        $certificatedate = str_replace(' 0', '', strftime('%d %B %Y', $certdate));
    } if ($certificate->datefmt == 4) {
        $certificatedate = strftime('%B %Y', $certdate);
    } if ($certificate->datefmt == 5) {
        $timeformat = get_string('strftimedate');
        $certificatedate = userdate($certdate, $timeformat);
    }
}

//Grade formatting
$grade = '';
//Print the course grade
$coursegrade = certificate_print_course_grade($course);
if ($certificate->printgrade == 1 && $certrecord->reportgrade == !null) {
    $reportgrade = $certrecord->reportgrade;
    $grade = $strcoursegrade.':  '.$reportgrade;
} else if ($certificate->printgrade > 0) {
    if ($certificate->printgrade == 1) {
        if ($certificate->gradefmt == 1) {
            $grade = $strcoursegrade.':  '.$coursegrade->percentage;
        } if ($certificate->gradefmt == 2) {
            $grade = $strcoursegrade.':  '.$coursegrade->points;
        } if ($certificate->gradefmt == 3) {
            $grade = $strcoursegrade.':  '.$coursegrade->letter;
        }
    } else {
        //Print the mod grade
        $modinfo = certificate_print_mod_grade($course, $certificate->printgrade);
        if ($certrecord) {
            $modgrade = $certrecord->reportgrade;
            $grade = $modinfo->name.' '.$strgrade.': '.$modgrade;
        } else if($certificate->printgrade > 1) {
            if ($certificate->gradefmt == 1) {
                $grade = $modinfo->name.' '.$strgrade.': '.$modinfo->percentage;
            }
            if ($certificate->gradefmt == 2) {
                $grade = $modinfo->name.' '.$strgrade.': '.$modinfo->points;
            }
            if($certificate->gradefmt == 3) {
                $grade = $modinfo->name.' '.$strgrade.': '.$modinfo->letter;
            }
        }
    }
}
//Print the outcome
$outcome = '';
$outcomeinfo = certificate_print_outcome($course, $certificate->printoutcome);
if ($certificate->printoutcome > 0) {
    $outcome = $outcomeinfo->name.': '.$outcomeinfo->grade;
}

// Print the code number
$code = '';
if ($certificate->printnumber) {
    $code = $certrecord->code;
}

//Print the student name
$studentname = '';
$studentname = fullname($USER);
$classname = '';
$classname = $course->fullname;


//Print the credit hours (abused to display credits)
if ($certificate->printhours == 1) {
    $info->credit = " and approval is for 1 credit";
} else if ($certificate->printhours > 1) {
    $info->credit = " and approval is for $certificate->printhours credits";
} else {
    $info->credit = '';
}

$info->code = $COURSE->shortname;
$info->score = $grade;
$info->issuedate = date('j F Y', $certrecord->timecreated);

// Use the first expirydate where timestamp is smaller than when the cert was created.
$expirydates = array(
    array('effectivefrom' => 1333242061, 'expirydate' => '01/10/2012'), // Effectivefrom  = 2012-04-01T01:01:01
    array('effectivefrom' => 1312160461, 'expirydate' => '31/03/2012'), // Effectivefrom  = 2011-08-01T01:01:01
    array('effectivefrom' => 0, 'expirydate' => '16/03/2011'), // Effectivefrom  = before we started hosting
);
foreach ($expirydates as $val) {
    if ($certrecord->timecreated > $val['effectivefrom']) {
        $info->expiry = $val['expirydate'];
        break;
    }
}

$customtext = $certificate->customtext;
$orientation = 'P';
$pdf = new TCPDF($orientation, 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetProtection(array('print'));
$pdf->SetTitle($certificate->name);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetAutoPageBreak(false, 0);
$pdf->AddPage();
// Pre-drawn fancy background
$pdf->ImageEps("$CFG->dirroot/mod/certificate/type/A4_GCP_complex_portrait/GCP_cert_ox_aiv3.eps", 0, 0, 210, 296);

//Define variables

//Landscape
if ($orientation == 'L') {
    $x = 10;
    $y = 30;
    $sealx = 230;
    $sealy = 150;
    $sigx = 47;
    $sigy = 155;
    $custx = 47;
    $custy = 155;
    $wmarkx = 40;
    $wmarky = 31;
    $wmarkw = 212;
    $wmarkh = 148;
    $brdrx = 0;
    $brdry = 0;
    $brdrw = 297;
    $brdrh = 210;
    $codey = 175;
} else {
//Portrait
    $x = 10;
    $y = 40;
    $sealx = 150;
    $sealy = 220;
    $sigx = 30;
    $sigy = 230;
    $custx = 30;
    $custy = 230;
    $wmarkx = 26;
    $wmarky = 58;
    $wmarkw = 158;
    $wmarkh = 170;
    $brdrx = 0;
    $brdry = 0;
    $brdrw = 210;
    $brdrh = 297;
    $codey = 250;
}

// Add text
// 1mm ~= 2.83464567pt
// Print the Cert number
if($certificate->printnumber) {
    $pdf->SetTextColor(79,118,156);
    cert_printtext(93, 144, 'L', 'Helvetica', '', 8, 'Number '.$certrecord->code);
}

// Student name
$pdf->SetTextColor(0,56,107);
cert_printtext(38.5, 182, 'L', 'Helvetica', '', 33, utf8_decode($studentname));

// Institution name
//$pdf->SetTextColor(79,118,156);
//cert_printtext(38.5, 200, 'L', 'Helvetica', '', 10, utf8_decode('of the University of Oxford'));

// Course title
$pdf->SetTextColor(92,161,214);
$maxlength = 25;
$textOffset = 0;
if(strlen($classname) < $maxlength) {
    // Short course name - one line
    cert_printtext(38, 225, 'L', 'Helvetica', '', 33, utf8_decode($classname));
} else {
    // Long course name - two lines
    $textOffset = 8;
    // Split string at word boundaries (spaces)
    $words = explode(' ', $classname);
    // Reconstitute string in two parts
    $classname_a = '';
    $classname_b = '';
    for ($i = 0; $i < sizeof($words); $i++) {
        if ((strlen($classname_a) + strlen($words[$i])) < $maxlength) {
            $classname_a .= ' '.$words[$i];
        } else {
            $classname_b .= ' '.$words[$i];
        }
    }

    // Print
    cert_printtext(37, 222, 'L', 'Helvetica', '', 33, utf8_decode($classname_a));
    cert_printtext(37, 234, 'L', 'Helvetica', '', 33, utf8_decode($classname_b));
}

// Score and details
$text = 'with a score of '.$info->score.'. ';
$text .= 'This module has been approved for Distance-Learning Credits for the CPD scheme of the federation of Royal College of Physicians of the UK. ';
$text .= 'The approval code is '.$info->code.$info->credit.'. The expiry date is for CPD approval is '.$info->expiry.'. Issued '.$info->issuedate;

$pdf->SetTextColor(79,118,156);
$pdf->SetFillColor(255, 255, 255);
$pdf->setFont('Helvetica', '', 11);
$pdf->SetXY(38.5, 244+$textOffset);
$pdf->MultiCell(150, 6, $text, 0, 1, 'L');

?>

<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Calendar events filter settings
 *
 * @package    filter_calendarevents
 * @author     2016 David Balch <david.balch@conted.ox.ac.uk>
 * @copyright  2016 The Chancellor Masters and Scholars of the University of Oxford.
 * @copyright  2014 Damyon Wiese
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$settings->add(new admin_setting_configtext('filter_calendarevents/lookaheadmonths',
    get_string('lookaheadmonths', 'filter_calendarevents'),
    get_string('lookaheadmonths_desc', 'filter_calendarevents'), 12, PARAM_INT));


$formats = array(1 => get_string('siteevents', 'filter_calendarevents'),
                 2 => get_string('courseevents', 'filter_calendarevents'),
                 3 => get_string('siteandcourseevents', 'filter_calendarevents'));

$settings->add(new admin_setting_configselect('filter_calendarevents/eventtypes',
                                get_string('eventtypes', 'filter_calendarevents'),
                                get_string('eventtypes_desc', 'filter_calendarevents'), 3, $formats));

$settings->add(new admin_setting_configcheckbox('filter_calendarevents/showdate',
                                get_string('showdate', 'filter_calendarevents'),
                                get_string('showdate_desc', 'filter_calendarevents'), true));

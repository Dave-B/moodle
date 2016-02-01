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
 * Autolink filter for course course and site calendar events.
 *
 * @package    filter_calendarevents
 * @author     2016 David Balch <david.balch@conted.ox.ac.uk>
 * @copyright  2016 The Chancellor Masters and Scholars of the University of Oxford.
 * @copyright  2004 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/calendar/lib.php');

/**
 * Autolink filter for course course and site calendar events.
 *
 * @author     2016 David Balch <david.balch@conted.ox.ac.uk>
 * @copyright  2016 The Chancellor Masters and Scholars of the University of Oxford.
 * @copyright  2004 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class filter_calendarevents extends moodle_text_filter {
    // Trivial-cache - keyed on $cachedcourseid and $cacheduserid.
    private static $eventlist = null;
    private static $cachedcourseid;
    private static $cacheduserid;

    /**
     * Calendar event name filter.
     *
     * @param string $text The text to filter.
     * @param array $options The filter options.
     */
    public function filter($text, array $options = array()) {
        global $CFG, $DB, $USER;

        $coursectx = $this->context->get_course_context(false);
        if (!$coursectx) {
            return $text;
        }
        $courseid = $coursectx->instanceid;

        // Get basic course info.
        if (!$courseinfo = $DB->get_record("course", array("id" => $courseid), 'startdate')) {
            print_error("invalidcourseid");
        }

        // Initialise/invalidate our trivial cache if dealing with a different course.
        if (!isset(self::$cachedcourseid) || self::$cachedcourseid !== (int)$courseid) {
            self::$eventlist = null;
        }
        self::$cachedcourseid = (int)$courseid;
        // And the same for user id.
        if (!isset(self::$cacheduserid) || self::$cacheduserid !== (int)$USER->id) {
            self::$eventlist = null;
        }
        self::$cacheduserid = (int)$USER->id;

        // It may be cached.
        if (is_null(self::$eventlist)) {
            self::$eventlist = array();

            // End date = start date plus n months.
            $enddate = $courseinfo->startdate + (get_config('filter_calendarevents', 'lookaheadmonths') * 2592000);
            $eventtypes = get_config('filter_calendarevents', 'eventtypes');
            if ($eventtypes == 1) { // Site events.
                $courses = 1;
            } else if ($eventtypes == 2) { // Course events.
                $courses = $courseid;
            } else if ($eventtypes == 3) { // Course and site events.
                $courses = true;
            }

            // Get events.
            $events = calendar_get_events($courseinfo->startdate, $enddate, false, true, $courses);
            if (empty($events)) {
                // No events found, so exit.
                return $text;
            }

            // Create array of visible events sorted by the name length.
            $sortedevents = array();
            $calendarurl = CALENDAR_URL .'view.php?course=' . $courseid . '&view=day&time=';
            $now = time();
            foreach ($events as $event) {
                // Exclude activity events.
                if ($event->modulename == '0') {
                    if (get_config('filter_calendarevents', 'showdate')) {
                        $formattedtime = ' (' . clean_param(calendar_format_event_time($event, $now), PARAM_NOTAGS) . ').';
                    } else {
                        $formattedtime = '';
                    }
                    $sortedevents[] = (object)array(
                        'name' => $event->name,
                        'id' => $event->id,
                        'courseid' => $event->courseid,
                        'url' => $calendarurl . $event->timestart . '#event_' . $event->id,
                        'formattedtime' => $formattedtime,
                        'namelen' => -strlen($event->name), // Negative value for reverse sorting.
                    );
                }
            }

            // Sort events by the length of the event name in reverse order.
            core_collator::asort_objects_by_property($sortedevents, 'namelen', core_collator::SORT_NUMERIC);

            foreach ($sortedevents as $event) {
                $title = s(trim(strip_tags($event->name)));
                $currentname = trim($event->name);
                $entitisedname  = s($currentname);
                // Avoid empty or unlinkable event names.
                if (!empty($title)) {
                    $hreftagbegin = html_writer::start_tag('a',
                                                    array('class' => 'autolink', 'title' => $title,
                                                                    'href' => $event->url));
                    self::$eventlist[$event->id] = new filterobject($currentname, $hreftagbegin,
                            '</a>' . $event->formattedtime, false, true);
                    if ($currentname != $entitisedname) {
                        // If name has some entity (&amp; &quot; &lt; &gt;) add that filter too. MDL-17545.
                        self::$eventlist[$event->id.'-e'] = new filterobject($entitisedname, $hreftagbegin,
                                '</a>' . $event->formattedtime, false, true);
                    }
                }
            }

        }

        $filterslist = array();
        if (self::$eventlist) {
            $cmid = $this->context->instanceid;
            if ($this->context->contextlevel == CONTEXT_MODULE && isset(self::$eventlist[$cmid])) {
                // Remove filterobjects for the current module.
                $filterslist = array_values(array_diff_key(self::$eventlist, array($cmid => 1, $cmid.'-e' => 1)));
            } else {
                $filterslist = array_values(self::$eventlist);
            }
        }

        if ($filterslist) {
            return $text = filter_phrases($text, $filterslist);
        } else {
            return $text;
        }
    }
}

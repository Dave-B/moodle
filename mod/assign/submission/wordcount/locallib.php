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
 * This file contains the definition for the library class for wordcount submission plugin
 *
 * This class provides all the functionality for the new assign module.
 *
 * @package assignsubmission_wordcount
 * @author     David Balch
 * @copyright  2014 TALL, University of Oxford {@link http://www.tall.ox.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * library class for wordcount submission plugin extending submission plugin base class
 *
 * @package assignsubmission_wordcount
 * @author     David Balch
 * @copyright  2014 TALL, University of Oxford {@link http://www.tall.ox.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assign_submission_wordcount extends assign_submission_plugin {

    /**
     * Get the name of the online text submission plugin
     * @return string
     */
    public function get_name() {
        return get_string('wordcount', 'assignsubmission_wordcount');
    }

    /**
     * Get submission wordcount from the database
     *
     * @param  int $submissionid
     * @return mixed
     */
    private function get_wordcount_submission($submissionid) {
        global $DB;

        return $DB->get_record('assignsubmission_wordcount', array('submission' => $submissionid));
    }

    /**
     * Add form elements for settings
     *
     * @param mixed $submission can be null
     * @param MoodleQuickForm $mform
     * @param stdClass $data
     * @return true if elements were added to the form
     */
    public function get_form_elements($submission, MoodleQuickForm $mform, stdClass $data) {
        $elements = array();

        $submissionid = $submission ? $submission->id : 0;

        if (!isset($data->wordcount)) {
            $data->wordcount = '';
        }

        if ($submission) {
            $wordcountsubmission = $this->get_wordcount_submission($submission->id);
            if ($wordcountsubmission) {
                $data->wordcount = $wordcountsubmission->wordcount;
            }

        }

        $mform->addElement('text', 'wordcount', $this->get_name(), null);
        $mform->setType('wordcount', PARAM_INT);
        $mform->addRule('wordcount', get_string('wordcount_help', 'assignsubmission_wordcount'), 'required', null, 'client');
        $mform->addRule('wordcount', get_string('wordcount_help', 'assignsubmission_wordcount'), 'nonzero', null, 'client');
        $mform->addRule('wordcount', get_string('wordcount_help', 'assignsubmission_wordcount'), 'regex', '/^[0-9]{1,8}$/', 'client');
        $mform->addHelpButton('wordcount', 'wordcount', 'assignsubmission_wordcount');

        return true;
    }

    /**
     * Save word count data to the database.
     *
     * @param stdClass $submission
     * @param stdClass $data
     * @return bool
     */
    public function save(stdClass $submission, stdClass $data) {
        global $USER, $DB;

        $wordcountsubmission = $this->get_wordcount_submission($submission->id);

        if ($wordcountsubmission) {

            $wordcountsubmission->wordcount = $data->wordcount;
            $updatestatus = $DB->update_record('assignsubmission_wordcount', $wordcountsubmission);
            return $updatestatus;
        } else {

            $wordcountsubmission = new stdClass();
            $wordcountsubmission->wordcount = $data->wordcount;

            $wordcountsubmission->submission = $submission->id;
            $wordcountsubmission->assignment = $this->assignment->get_instance()->id;
            $wordcountsubmission->id = $DB->insert_record('assignsubmission_wordcount', $wordcountsubmission);
            return $wordcountsubmission->id > 0;
        }
    }

     /**
      * Display word count
      *
      * @param stdClass $submission
      * @param bool $showviewlink - If the summary has been truncated set this to true
      * @return string
      */
    public function view_summary(stdClass $submission, &$showviewlink) {
        $wordcountsubmission = $this->get_wordcount_submission($submission->id);

        if ($wordcountsubmission) {
            return $wordcountsubmission->wordcount;
        }
        return '';
    }

    /**
     * Display word count
     *
     * @param stdClass $submission
     * @return string
     */
    public function view(stdClass $submission) {
        // TODO: Verify if this is actually needed 2014-05-02.
        $wordcountsubmission = $this->get_wordcount_submission($submission->id);

        if ($wordcountsubmission) {
            return $wordcountsubmission->wordcount;
        }
        return '';
    }

    /**
     * Formatting for log info
     *
     * @param stdClass $submission The new submission
     * @return string
     */
    public function format_for_log(stdClass $submission) {
        // Format the info for each submission plugin (will be logged).
        $wordcountsubmission = $this->get_wordcount_submission($submission->id);
        $wordcountloginfo = get_string('wordcountforlog',
                                         'assignsubmission_wordcount',
                                         $wordcountsubmission->wordcount);

        return $wordcountloginfo;
    }

    /**
     * The assignment has been deleted - cleanup
     *
     * @return bool
     */
    public function delete_instance() {
        global $DB;
        $DB->delete_records('assignsubmission_wordcount',
                            array('assignment' => $this->assignment->get_instance()->id));

        return true;
    }

    /**
     * No text is set for this plugin
     *
     * @param stdClass $submission
     * @return bool
     */
    public function is_empty(stdClass $submission) {
        $wordcountsubmission = $this->get_wordcount_submission($submission->id);

        return empty($wordcountsubmission->wordcount);
    }

    /**
     * Copy the student's submission from a previous submission. Used when a student opts to base their resubmission
     * on the last submission.
     * @param stdClass $sourcesubmission
     * @param stdClass $destsubmission
     */
    public function copy_submission(stdClass $sourcesubmission, stdClass $destsubmission) {
        global $DB;

        // Copy the assignsubmission_wordcount record.
        $wordcountsubmission = $this->get_wordcount_submission($sourcesubmission->id);
        if ($wordcountsubmission) {
            unset($wordcountsubmission->id);
            $wordcountsubmission->submission = $destsubmission->id;
            $DB->insert_record('assignsubmission_wordcount', $wordcountsubmission);
        }
        return true;
    }

}

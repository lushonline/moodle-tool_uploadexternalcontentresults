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
 * Links and settings
 *
 * Class containing a set of helpers, based on admin\tool\uploadcourse by 2013 Frédéric Massart.
 *
 * @package    tool_uploadexternalcontentresults
 * @copyright  2019-2020 LushOnline
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

global $CFG;
require_once($CFG->dirroot . '/mod/externalcontent/lib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->libdir . '/enrollib.php');
require_once($CFG->dirroot . '/course/lib.php');

/**
 * Class containing a set of helpers.
 *
 * @package   tool_uploadexternalcontentresults
 * @copyright 2019-2020 LushOnline
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_uploadexternalcontentresults_helper {
    /**
     * Validate we have the minimum info to create/update course result
     *
     * @param object $record The record we imported
     * @return bool true if validated
     */
    public static function validate_import_record($record) {
        // As a minimum we need.
        // course idnumber.
        // user username.

        if (empty($record->course_idnumber)) {
            return false;
        }

        if (empty($record->user_username)) {
            return false;
        }

        return true;
    }

    /**
     * Retrieve a externalcontent by its id.
     *
     * @param int $externalcontentid externalcontent identifier
     * @return object externalcontent.
     */
    public static function get_externalcontent_by_id($externalcontentid) {
        global $DB;

        $params = array('id' => $externalcontentid);
        if ($externalcontent = $DB->get_record('externalcontent', $params)) {
            return $externalcontent;
        } else {
             return null;
        }
    }

    /**
     * Retrieve a course by its idnumber.
     *
     * @param string $courseidnumber course idnumber
     * @return object course or null
     */
    public static function get_course_by_idnumber($courseidnumber) {
        global $DB;

        $params = array('idnumber' => $courseidnumber);
        if ($course = $DB->get_record('course', $params)) {
            return $course;
        } else {
            return null;
        }
    }

    /**
     * Retrieve course module $cm by course idnumber.
     *
     * use modinfolib.php
     *
     * @param string $course course object
     * @return stdClass $cm Activity or null if none found
     */
    public static function get_coursemodule_from_course_idnumber($course) {
        global $DB;

        $cm = null;
        $params = array('idnumber' => $course->idnumber, 'course' => $course->id);
        $cm = $DB->get_record('course_modules', $params);

        return $cm;
    }

    /**
     * Update externalcontent activity viewed and completion if needed
     *
     * This will show a developer debug warning when run in Moodle UI because
     * of the function set_module_viewed in completionlib.php details copied below:
     *
     * Note that this function must be called before you print the externalcontent header because
     * it is possible that the navigation block may depend on it. If you call it after
     * printing the header, it shows a developer debug warning.
     *
     * @param object $record Validated Imported Record
     * @return object $response contains details of processing
     */
    public static function mark_externalcontent_as_completed($record) {
        global $DB;

        $response = new \stdClass();
        $response->added = 0;
        $response->skipped = 0;
        $response->error = 0;
        $response->message = null;

        // Student role to use when enroling user.
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $message = '';
        // Get the course by the idnumber.
        if ($course = self::get_course_by_idnumber($record->course_idnumber)) {
            $response->course = $course;
            if ($user = $DB->get_record('user', array('username' => $record->user_username), 'id,username')) {
                $response->user = $user;

                if ($cm = self::get_coursemodule_from_course_idnumber($course)) {

                    // Execute real Moodle enrolment for user.
                    enrol_try_internal_enrol($course->id, $user->id, $studentrole->id);

                    $updateresponse = externalcontent_update_completion_state($course, $cm, null, $user->id,
                                                                              $record->user_score, $record->user_completed);
                    $response->message = $updateresponse->message;

                    if ($updateresponse->status) {
                        $response->skipped = 0;
                        $response->added = 1;
                    } else {
                        $response->skipped = 1;
                        $response->added = 0;
                    }
                } else {
                    $message .= 'External content with idnumber '.$record->course_idnumber.' does not exist.';
                    $response->message = $message;
                    $response->skipped = 1;
                    $response->added = 0;
                }
            } else {
                $response->skipped = 1;
                $response->message = 'User with username '.$record->user_username.' does not exist.';
            }
        } else {
            // Course does not exist so skip.
            $message .= 'Course with idnumber '.$record->course_idnumber.' does not exist.';
            $response->message = $message;
            $response->skipped = 1;
        }

        return $response;
    }
}
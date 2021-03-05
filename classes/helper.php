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

        $isvalid = true;
        $isvalid = $isvalid && !empty($record->course_idnumber);
        $isvalid = $isvalid && !empty($record->user_username);

        return $isvalid;
    }

    /**
     * Retrieve the course module with idnumber
     *
     * @param string $idnumber Unique idnumber
     * @return object|null coursemodule or null
     */
    public static function get_cm_by_idnumber($idnumber) {
        global $DB;

        $params = array('courseidnumber' => $idnumber, 'modulename' => 'externalcontent');

        $sql = "SELECT cm.*
                FROM {course_modules} cm
                JOIN {modules} md ON md.id = cm.module
                WHERE cm.idnumber = :courseidnumber AND md.name = :modulename";

        if ($cm = $DB->get_record_sql($sql, $params)) {
            return $cm;
        } else {
            return null;
        }
    }

    /**
     * Retrieve a course by its id.
     *
     * @param string $courseid course id.
     * @return object|null course or null.
     */
    public static function get_course_by_id($courseid) {
        global $DB;

        $params = array('id' => $courseid);
        if ($course = $DB->get_record('course', $params)) {
            return $course;
        } else {
            return null;
        }
    }

    /**
     * Retrieve a user by username.
     *
     * @param string $username Moodle username.
     * @return object|null user or null.
     */
    public static function get_user_by_username($username) {
        global $DB;

        $params = array('username' => $username);

        if ($user = $DB->get_record('user', $params, 'id,username')) {
            return $user;
        } else {
            return null;
        }
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

        $cm = null;
        $course = null;
        $user = null;

        // Check the user exists.
        if ($user = self::get_user_by_username($record->user_username)) {
            // Get the course module by the idnumber.
            if ($cm = self::get_cm_by_idnumber($record->course_idnumber)) {
                // If we have a course module we must have a course, so get the course.
                if ($course = self::get_course_by_id($cm->course)) {
                    // Student role to use when enroling user.
                    $studentrole = $DB->get_record('role', array('shortname' => 'student'));

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
                    // Course does not exist so skip.
                    $response->message = get_string('coursedoesnotexist', 'tool_uploadexternalcontentresults', $record->course_idnumber);
                    $response->skipped = 1;
                };
            } else {
                    $response->message = get_string('externalcontentdoesnotexist',
                                                    'tool_uploadexternalcontentresults',
                                                    $record->course_idnumber);
                    $response->skipped = 1;
                    $response->added = 0;
                }
        } else {
            // User doesn't exist.
            $response->skipped = 1;
            $response->message = get_string('userdoesnotexist', 'tool_uploadexternalcontentresults', $record->user_username);
        }

        return $response;
    }
}
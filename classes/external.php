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
 * @package     local_demo
 * @copyright   2020 Joseph Conradt
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_demo;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/externallib.php');

class external extends \external_api
{
    #region get_inactive_courses

    public static function get_inactive_courses_parameters() {
        return new \external_function_parameters([
            'days_since_activity' => new \external_value(PARAM_INT, 'Days since the last activity occurred in courses.', VALUE_REQUIRED),
        ]);
    }

    public static function get_inactive_courses($days_since_activity) {
        global $DB;

        $params = self::validate_parameters(self::get_inactive_courses_parameters(), [
            'days_since_activity' => $days_since_activity
        ]);

        $since = time() - ($params['days_since_activity'] * 24 * 60 * 60);

        $courses = $DB->get_records_sql('SELECT c.id, c.fullname, c.shortname, c.idnumber FROM {course} c
                                         JOIN {enrol} AS e ON e.course = c.id
                                         JOIN {user_enrolments} AS ue ON ue.enrolid = e.id
                                         JOIN {user_lastaccess} ul ON ul.courseid = c.id AND ul.userid = ue.userid
                                         WHERE MAX(ul.timeaccess) >= :since
                                         GROUP BY c.id', [
                                             'since' => $since
        ]);

        return [
            'courses' => $courses
        ];
    }

    public static function get_inactive_courses_returns() {
        return new \external_single_structure([
            'courses' => new \external_multiple_structure(new \external_single_structure([
                'id' => new \external_value(PARAM_INT),
                'fullname' => new \external_value(PARAM_TEXT),
                'shortname' => new \external_value(PARAM_TEXT),
                'idnumber' => new \external_value(PARAM_TEXT)
            ]))
        ]);
    }

    #endregion
}

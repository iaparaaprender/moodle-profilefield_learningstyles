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
 * This class contains the get styles webservice functions.
 *
 * @package     profilefield_learningstyles
 * @copyright   2024 David Herney - cirano
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace profilefield_learningstyles\external;

use external_api;
use external_function_parameters;
use external_single_structure;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/user/profile/lib.php');

/**
 * Get the current user learning styles.
 *
 * @copyright   2024 David Herney - cirano
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() : external_function_parameters {
        return new \external_function_parameters([
            'userid' => new \external_value(PARAM_INT, 'User id', VALUE_DEFAULT, 0),
        ]);
    }

    /**
     * Get styles.
     *
     * @param int $userid User id
     *
     * @return object
     */
    public static function execute($userid = 0): ?object {
        global $USER, $PAGE, $DB;

        if (!isloggedin() || isguestuser()) {
            require_login(null, false);
        }
        $syscontext = \context_system::instance();
        $PAGE->set_context($syscontext);

        if ($userid === 0) {
            $userid = $USER->id;
        }

        if ($userid != $USER->id
                && !has_capability('profilefield/learningstyles:report', $syscontext)
                && !has_capability('profilefield/learningstyles:manage', $syscontext)
        ) {
            throw new \moodle_exception('errornotpermission', 'profilefield_learningstyles');
        }

        $select = "datatype = 'learningstyles' AND visible > 0";
        $infofields = $DB->get_records_select('user_info_field', $select);

        foreach ($infofields as $infofield) {
            if ($infofield->visible > 0) {
                $lsfield = $infofield;
                break;
            }
        }

        if (empty($lsfield)) {
            throw new \moodle_exception('invaliduserfield', 'profilefield_learningstyles');
        }

        $styles = null;
        if ($data = $DB->get_field('user_info_data', 'data', ['userid' => $userid, 'fieldid' => $lsfield->id])) {
            $data = @json_decode($data);

            if (!empty($data) && property_exists($data, 'affinity')) {
                $styles = $data->affinity;
            }
        }

        return $styles;
    }

    /**
     * Return user learning styles.
     *
     * @example
     *     {
     *       "processing": -11,
     *       "understanding": -1,
     *       "perception": 5,
     *       "input": -3
     *     }
     *
     * @return \external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure(
            [
                'processing' => new \external_value(PARAM_INT, 'Processing dimension'),
                'understanding' => new \external_value(PARAM_INT, 'Understanding dimension'),
                'perception' => new \external_value(PARAM_INT, 'Perception dimension'),
                'input' => new \external_value(PARAM_INT, 'Input dimension'),
            ],
            'User learning styles', VALUE_DEFAULT, null
        );
    }
}

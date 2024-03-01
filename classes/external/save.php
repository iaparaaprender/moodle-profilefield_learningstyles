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
 * This class contains the save webservice functions.
 *
 * @package     profilefield_learningstyles
 * @copyright   2024 David Herney - cirano
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace profilefield_learningstyles\external;

use external_api;
use external_function_parameters;
use external_value;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/user/profile/lib.php');

/**
 * Save service implementation.
 *
 * @copyright   2024 David Herney - cirano
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class save extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new \external_function_parameters(
            [
                'sesskey' => new \external_value(PARAM_TEXT, 'A secure session key', VALUE_REQUIRED),
                'answers' => new \external_value(PARAM_TEXT, 'User answers', VALUE_REQUIRED)
            ]
        );
    }

    /**
     * Returns the transaction information about the buy.
     *
     * @param string $sesskey
     * @param string $answers
     * @return object
     */
    public static function execute(string $sesskey, string $answers): bool {
        global $USER, $PAGE, $DB;

        if (!isloggedin() || isguestuser()) {
            require_login(null, false);
        }
        $syscontext = \context_system::instance();
        $PAGE->set_context($syscontext);

        self::validate_parameters(self::execute_parameters(), [
            'sesskey' => $sesskey,
            'answers' => $answers
        ]);

        if (!confirm_sesskey($sesskey)) {
            throw new \moodle_exception('invalidsesskey', 'profilefield_learningstyles');
        }

        $infofields = $DB->get_records('user_info_field', ['datatype' => 'learningstyles', 'locked' => 0]);

        foreach ($infofields as $infofield) {
            if ($infofield->visible > 0) {
                $lsfield = $infofield;
                break;
            }
        }

        if (empty($lsfield)) {
            throw new \moodle_exception('invaliduserfield', 'profilefield_learningstyles');
        }

        $answers = json_decode($answers, true);

        if (empty($answers) || !is_array($answers) || count($answers) !== \profilefield_learningstyles\styles::STYLES_LENGTH) {
            throw new \moodle_exception('invalidanswers', 'profilefield_learningstyles');
        }

        foreach ($answers as $answer) {
            $answer = (object)$answer;
            if (!is_object($answer) || !property_exists($answer, 'value') || !property_exists($answer, 'view')
                    || !in_array($answer->value, ['a', 'b'])
                    || !in_array($answer->view, \profilefield_learningstyles\styles::VIEWS)) {
                throw new \moodle_exception('invalidanswers', 'profilefield_learningstyles');
            }
        }

        $affinity = \profilefield_learningstyles\styles::calculate_affinity($answers);

        $data = new \stdClass();
        $data->userid = $USER->id;
        $data->fieldid = $lsfield->id;
        $data->data = json_encode([
            'answers' => $answers,
            'affinity' => $affinity,
        ]);

        if ($dataid = $DB->get_field('user_info_data', 'id', ['userid' => $USER->id, 'fieldid' => $lsfield->id])) {
            $data->id = $dataid;
            $DB->update_record('user_info_data', $data);
        } else {
            $DB->insert_record('user_info_data', $data);
        }

        profile_load_custom_fields($USER);

        return true;
    }

    /**
     * Returns description of method result value.
     *
     * @return external_value
     */
    public static function execute_returns(): external_value {
        return new \external_value(PARAM_BOOL, 'True if was saved, false in other case.');
    }
}

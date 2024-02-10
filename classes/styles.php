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
 * Styles representation.
 *
 * @package     profilefield_learningstyles
 * @copyright   2024 David Herney - cirano
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace profilefield_learningstyles;

/**
 * Class with general styles controller.
 *
 * @package     profilefield_learningstyles
 * @copyright   2024 David Herney - cirano
 */
class styles {

    /**
     * Available views.
     * @var array
     */
    const VIEWS = ['fys', 'udea'];

    /**
     * Length of the styles.
     * @var int
     */
    const STYLES_LENGTH = 44;

    /**
     * Questions structure defined by Felder-Silverman.
     * @var array
     */
    const QUESTIONS_STRUCTURE = [
        [
            'key' => 'processing',
            'questions' => [1, 5, 9, 13, 17, 21, 25, 29, 33, 37, 41],
            'affinity' => 0,
            'styles' => ['active', 'reflective'],
        ],
        [
            'key' => 'understanding',
            'questions' => [2, 6, 10, 14, 18, 22, 26, 30, 34, 38, 42],
            'affinity' => 0,
            'styles' => ['sequential', 'global'],
        ],
        [
            'key' => 'perception',
            'questions' => [3, 7, 11, 15, 19, 23, 27, 31, 35, 39, 43],
            'affinity' => 0,
            'styles' => ['sensing', 'intuitive'],
        ],
        [
            'key' => 'input',
            'questions' => [4, 8, 12, 16, 20, 24, 28, 32, 36, 40, 44],
            'affinity' => 0,
            'styles' => ['visual', 'verbal'],
        ]
    ];

    /**
     * Get data for the views template.
     *
     * @return array
     */
    public static function get_data() : array {

        // Load the views from language file.
        $styles = [];
        for ($k = 1; $k <= self::STYLES_LENGTH; $k++) {
            $items = [];
            foreach (self::VIEWS as $view) {

                $items[] = [
                    'key' => $view,
                    'name' => get_string($view . '_name', 'profilefield_learningstyles'),
                    'question' => get_string($view . '_' . $k, 'profilefield_learningstyles'),
                    'a' => get_string($view . '_' . $k . '_a', 'profilefield_learningstyles'),
                    'b' => get_string($view . '_' . $k . '_b', 'profilefield_learningstyles'),
                    'urla' => new \moodle_url("/user/profile/field/learningstyles/views/{$view}/{$k}a.png"),
                    'urlb' => new \moodle_url("/user/profile/field/learningstyles/views/{$view}/{$k}b.png"),
                ];
            }

            $styles[] = [
                'index' => $k,
                'items' => $items,
                'active' => false //$k === 40,
            ];
        }

        $views = [];
        foreach (self::VIEWS as $view) {
            $views[] = [
                'key' => $view,
                'name' => get_string($view . '_name', 'profilefield_learningstyles'),
                'label' => get_string($view . '_label', 'profilefield_learningstyles'),
                'description' => get_string($view . '_description', 'profilefield_learningstyles'),
            ];
        }

        return [
            'views' => $views,
            'styles' => $styles,
            'helpstyles' => self::get_helpstyles(),
            'feedbacks' => self::get_feedbacks(),
        ];
    }

    /**
     * Get helps styles to use in template.
     *
     * @return array
     */
    public static function get_helpstyles() : array {
        $helpstyles = [];
        foreach (self::QUESTIONS_STRUCTURE as $style) {
            foreach ($style['styles'] as $s) {
                $helpstyles[$s] = [
                    'text' => get_string('helpstyle_' . $s, 'profilefield_learningstyles'),
                    'alt' => get_string('style_' . $s, 'profilefield_learningstyles'),
                ];
            }
        }

        return $helpstyles;
    }

    /**
     * Get feedbacks for each style.
     * @return array [[level, text], ...]
     */
    public static function get_feedbacks() : array {
        $feedbacks = [];
        foreach (self::QUESTIONS_STRUCTURE as $style) {
            foreach ($style['styles'] as $s) {

                $levels = [];
                for ($i = 1; $i <= 3; $i++) {
                    $levels[] = [
                        'level' => $i,
                        'text' => get_string('feedback_' . $s . '_level' . $i, 'profilefield_learningstyles'),
                    ];
                }

                $feedbacks[] = [
                    'style' => $s,
                    'label' => get_string('style_' . $s, 'profilefield_learningstyles'),
                    'levels' => $levels
                ];
            }
        }

        return $feedbacks;
    }

    /**
     * Calculate the affinity for each style according the test answers.
     *
     * @param array $answers
     * @return array [style => affinity, ...]
     */
    public static function calculate_affinity(array $answers) : array {
        $affinity = [];
        foreach (self::QUESTIONS_STRUCTURE as $style) {
            $a = 0;
            $b = 0;
            foreach ($style['questions'] as $question) {
                if ($answers[$question - 1]['value'] === 'a') {
                    $a++;
                } else {
                    $b++;
                }
            }
            $affinity[$style['key']] = $b - $a;
        }
        return $affinity;
    }

    /**
     * Get the affinity for the user.
     *
     * @param int $userid Optional, if not provided the current user will be used.
     * @return object|null Affinity object or null if not found.
     */
    public static function get_affinity(int $userid = 0) : ?object {
        global $DB, $USER;

        if (empty($userid)) {
            $userid = $USER->id;
        }

        $infofields = $DB->get_records('user_info_field', ['datatype' => 'learningstyles', 'locked' => 0]);

        $fieldids = [];
        foreach ($infofields as $infofield) {
            if ($infofield->visible > 0) {
                $fieldids[] = $infofield->id;
                break;
            }
        }

        if (count($fieldids) == 0) {
            return null;
        }

        list($insql, $inparams) = $DB->get_in_or_equal($fieldids, SQL_PARAMS_NAMED);
        $params = array_merge($inparams, ['userid' => $userid]);

        $infodata = $DB->get_records_select('user_info_data', "fieldid $insql AND userid = :userid", $params, 'id DESC');

        foreach ($infodata as $inforecord) {
            $value = json_decode($inforecord->data);

            if (!is_object($value) || !property_exists($value, 'affinity') || !is_object($value->affinity)) {
                continue;
            }

            return $value->affinity;
        }

        return null;
    }

}

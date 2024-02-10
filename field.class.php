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
 * Profile field implementation.
 *
 * @package     profilefield_learningstyles
 * @copyright   2024 David Herney - cirano
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class profile_field_learningstyles
 *
 * @copyright   2024 David Herney - cirano
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class profile_field_learningstyles extends profile_field_base {

    /**
     * Check if the field should appear on the signup page
     * @internal This method should not generally be overwritten by child classes.
     * @return bool
     */
    public function is_signup_field() {
        return false;
    }

    /**
     * Add elements for editing the profile field value.
     * @param moodleform $mform
     */
    public function edit_field_add($mform) {

        $label = empty($this->data) ? 'gototest' : 'retaketest';
        $html = \html_writer::link(
            new \moodle_url('/user/profile/field/learningstyles/index.php'),
            get_string($label, 'profilefield_learningstyles') . ' '
            . \html_writer::tag('i', '', ['class' => 'icon fa fa-external-link']),
            ['target' => '_blank']
        );

        // Create the form field.
        $mform->addElement('static', $this->inputname . '_static', format_string($this->field->name), $html);

    }

    /**
     * Display the data for this field.
     *
     * @return string HTML.
     */
    public function display_data() {
        global $PAGE, $USER;
        $data = json_decode($this->data, true);

        $html = '';

        if (empty($data) || empty($data['affinity'])) {

            if ($USER->id == $PAGE->context->instanceid) {
                $html .= \html_writer::link(
                    new \moodle_url('/user/profile/field/learningstyles/index.php'),
                    get_string('starttest', 'profilefield_learningstyles'),
                    ['class' => 'btn btn-link'],
                );
            }
        } else {

            foreach ($data['affinity'] as $key => $value) {
                $data['affinity'][$key] = ($value + 11) * 100 / 22;
            }

            $renderdata = $data['affinity'];
            $renderdata['helpstyles'] = \profilefield_learningstyles\styles::get_helpstyles();

            $renderer = $PAGE->get_renderer('core');
            $html .= $renderer->render_from_template('profilefield_learningstyles/results', $renderdata);

            if ($USER->id == $PAGE->context->instanceid) {
                $html .= \html_writer::link(
                    new \moodle_url('/user/profile/field/learningstyles/index.php'),
                    get_string('retaketest', 'profilefield_learningstyles'),
                    ['class' => 'btn btn-link'],
                );
            }
        }

        $html .= \html_writer::link(
            new \moodle_url('/user/profile/field/learningstyles/explanation.php'),
            get_string('explanationlabel', 'profilefield_learningstyles') . ' '
            . \html_writer::tag('i', '', ['class' => 'icon fa fa-external-link']),
            ['class' => 'btn btn-link', 'target' => '_blank'],
        );

        return $html;
    }

    /**
     * Return the field type and null properties.
     * This will be used for validating the data submitted by a user.
     *
     * @return array the param type and null property
     * @since Moodle 3.2
     */
    public function get_field_properties() {
        return [PARAM_RAW, NULL_ALLOWED];
    }

}



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
 * Global explanation.
 *
 * @package     profilefield_learningstyles
 * @copyright   2024 David Herney - cirano
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../../config.php');

require_login();

$context = context_system::instance();

$PAGE->set_url('/user/profile/field/learningstyles/explanation.php');
$PAGE->set_context($context);
$PAGE->set_pagelayout('report');
$PAGE->add_body_class('profilefield_learningstyles-content');

echo $OUTPUT->header();

$lang = current_language();

$path = $CFG->dirroot . '/user/profile/field/learningstyles/assets/explanations/' . $lang . '/index.html';

echo html_writer::tag('h1', get_string('explanationlabel', 'profilefield_learningstyles'));

echo html_writer::start_tag('div', ['class' => 'explanationpage']);
if (file_exists($path)) {
    echo file_get_contents($path);
} else {
    echo file_get_contents($CFG->dirroot . '/user/profile/field/learningstyles/assets/explanations/en/index.html');
}
echo html_writer::end_tag('div');

echo $OUTPUT->footer();

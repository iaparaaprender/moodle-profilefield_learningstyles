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
 * Learning styles test.
 *
 * @package     profilefield_learningstyles
 * @copyright   2024 David Herney - cirano
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../../config.php');

require_login(null, false);

$context = context_user::instance($USER->id);
require_capability('profilefield/learningstyles:complete', $context);

$PAGE->set_url('/user/profile/field/learningstyles/index.php');
$PAGE->set_context($context);
$PAGE->set_pagelayout('report');
$PAGE->add_body_class('profilefield_learningstyles-content');

$questionstructure = json_encode(\profilefield_learningstyles\styles::QUESTIONS_STRUCTURE);
$PAGE->requires->js_call_amd('profilefield_learningstyles/main', 'init', [$questionstructure]);

echo $OUTPUT->header();

$infofields = $DB->get_records('user_info_field', ['datatype' => 'learningstyles', 'locked' => 0]);

$lsfield = null;
foreach ($infofields as $infofield) {
    if ($infofield->visible > 0) {
        $lsfield = $infofield;
        break;
    }
}

if (empty($lsfield)) {
    throw new \moodle_exception('invaliduserfield', 'profilefield_learningstyles');
}

$pagedata = \profilefield_learningstyles\styles::get_data();
$pagedata['sesskey'] = sesskey();
$pagedata['wwwroot'] = $CFG->wwwroot;

$renderer = $PAGE->get_renderer('core');
echo $renderer->render_from_template('profilefield_learningstyles/main', $pagedata);

echo $OUTPUT->footer();

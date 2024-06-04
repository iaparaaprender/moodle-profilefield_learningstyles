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
 * Test report.
 *
 * @package     profilefield_learningstyles
 * @copyright   2024 David Herney - cirano
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use core_reportbuilder\system_report_factory;

$inpopup = optional_param('popup', 0, PARAM_BOOL);

require_login(null, false);
$systemcontext = context_system::instance();

// Redirect if the user is a guest.
if (isguestuser()) {
    $url = new moodle_url($CFG->wwwroot);
    redirect($url);
    die();
}

$PAGE->set_context($systemcontext);
$PAGE->set_url('/user/profile/field/learningstyles/history.php');
$PAGE->set_title(get_string('historytitle', 'profilefield_learningstyles'));
$PAGE->set_heading(get_string('historytitle', 'profilefield_learningstyles'));

if ($inpopup) {
    $PAGE->set_pagelayout('popup');
} else {
    $PAGE->set_pagelayout('report');
}

echo $OUTPUT->header();

$report = system_report_factory::create(profilefield_learningstyles\systemreports\history::class, $systemcontext);

echo $report->output();

echo $OUTPUT->footer();

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
 * Page to quickly get the current user's style.
 *
 * @package     profilefield_learningstyles
 * @copyright   2024 David Herney - cirano
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require_once('../../../../config.php');
require_once($CFG->libdir . '/filelib.php');

if ($SESSION->profilefield_learningstyles_nullstyles > 5) {
    echo 'null';
    exit;
}

$ws = new \profilefield_learningstyles\external\get();
$localstyles = $ws->execute();

if (!$localstyles || !is_object($localstyles)) {
    $localstyles = null;
}

// Get the field settings.
$select = "datatype = 'learningstyles' AND visible > 0";
$infofields = $DB->get_records_select('user_info_field', $select);

foreach ($infofields as $infofield) {
    if ($infofield->visible > 0) {
        $lsfield = $infofield;
        break;
    }
}

$trendfieldid = $DB->get_field('user_info_field', 'id', ['shortname' => 'learning_trend'], IGNORE_MULTIPLE);

$usedgroupskeys = ['base', 'encuesta', 'balanceado', 'ia'];
$courseid = null;
$sourcetype = 'encuesta';
if (!empty($lsfield) && !empty($lsfield->param3)) {
    $courseid = (int)$lsfield->param3;
    $usergroups = groups_get_user_groups($courseid, $USER->id);

    // If the user is not enrolled in the course, the usergroups will be empty.
    // In this case, the user will not be able to access the learning styles test.
    if (empty($usergroups) || empty($usergroups[0])) {
        $SESSION->profilefield_learningstyles_styles = 'null';
        echo 'null';
        exit;
    }

    foreach ($usergroups as $grouping) {
        foreach ($grouping as $groupid) {
            $group = groups_get_group($groupid);
            if ($group && in_array($group->idnumber, $usedgroupskeys)) {
                $sourcetype = $group->idnumber;
                break 2;
            }
        }
    }
}

// The param1 has the WS service URL and the param2 the authorization hash.
$remotestyles = null;
if (!empty($lsfield) && !empty($lsfield->param1)) {

    try {
        $curl = new \curl();

        if (!empty($lsfield->param2)) {
            $curl->setHeader('Authorization: Basic ' . $lsfield->param2);
        }

        $curlresponse = $curl->post($lsfield->param1, ['email' => $USER->email]);

        if ($curlresponse) {
            $response = @json_decode($curlresponse);

            if (!is_object($response)) {
                debugging('<pre>' . (string)$curlresponse . '</pre>');
            } else {

                $allowedvalues = [-11, -9, -7, -5, -3, -1, 1, 3, 5, 7, 9, 11];
                if (property_exists($response, 'input') && in_array($response->input, $allowedvalues)
                        && property_exists($response, 'perception') && in_array($response->perception, $allowedvalues)
                        && property_exists($response, 'processing') && in_array($response->processing, $allowedvalues)
                        && property_exists($response, 'understanding') && in_array($response->understanding, $allowedvalues)) {

                    $remotestyles = $response;
                }

            }

        }
    } catch (Exception $e) {
        debugging($e->getMessage());
    }

} else {
    // Allways use the localstyles if the remote service is not configured.
    $SESSION->profilefield_learningstyles_styles = @json_encode($localstyles);
    echo $SESSION->profilefield_learningstyles_styles;
    exit;
}

// To prevent the service from being overused when there is no response.
if (!isset($SESSION->profilefield_learningstyles_nullstyles)) {
    $SESSION->profilefield_learningstyles_nullstyles = 1;
}

if ($SESSION->profilefield_learningstyles_nullstyles > 5) {
    $SESSION->profilefield_learningstyles_styles = @json_encode(null);
}

$difference = 0;

if ($localstyles && $remotestyles) {
    $stylesnames = ['input', 'perception', 'processing', 'understanding'];
    foreach ($stylesnames as $name) {
        $difference += abs($localstyles->{$name} - $remotestyles->{$name});
    }
}

// Save current request information.
$data = new stdClass();
$data->userid = $USER->id;
$data->localanswers = @json_encode($localstyles);
$data->remoteanswers = @json_encode($remotestyles);
$data->difference = $difference;
$data->timerequest = time();
$trendvalue = '';

try {
    $DB->insert_record('profilefield_ls_getlog', $data);
} catch (Exception $e) {
    debugging($e->getMessage());
}

if ($sourcetype == 'base' || $sourcetype == 'balanceado') {

    $SESSION->profilefield_learningstyles_styles = 'null';
    echo 'null';

} else if ($sourcetype == 'encuesta' && $localstyles) {

    $SESSION->profilefield_learningstyles_styles = @json_encode($localstyles);
    echo @json_encode($localstyles);
    $trendvalue = \profilefield_learningstyles\styles::generate_tagsstring((array)$localstyles);

} else if ($sourcetype == 'ia' && $remotestyles) {

    $SESSION->profilefield_learningstyles_styles = @json_encode($remotestyles);
    echo @json_encode($remotestyles);
    $trendvalue = \profilefield_learningstyles\styles::generate_tagsstring((array)$remotestyles);

} else {

    $SESSION->profilefield_learningstyles_nullstyles++;
    echo 'null';
}

if ($trendfieldid) {
    $datatrend = new \stdClass();
    $datatrend->userid = $USER->id;
    $datatrend->fieldid = $trendfieldid;
    $datatrend->data = $trendvalue;

    if ($dataid = $DB->get_field('user_info_data', 'id', ['userid' => $USER->id, 'fieldid' => $trendfieldid])) {
        $datatrend->id = $dataid;
        $DB->update_record('user_info_data', $datatrend);
    } else {
        $DB->insert_record('user_info_data', $datatrend);
    }
}

exit;

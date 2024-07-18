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

if (isset($SESSION->profilefield_learningstyles_styles)) {
    echo $SESSION->profilefield_learningstyles_styles;
    exit;
}

$ws = new \profilefield_learningstyles\external\get();
$localstyles = $ws->execute();

if (!$localstyles || !is_object($localstyles)) {
    $localstyles = null;
}

// Check if exist a profile in remote service.
$select = "datatype = 'learningstyles' AND visible > 0";
$infofields = $DB->get_records_select('user_info_field', $select);

foreach ($infofields as $infofield) {
    if ($infofield->visible > 0) {
        $lsfield = $infofield;
        break;
    }
}

// The param1 has the WS service URL and the param2 the authorization hash.
$remotestyles = null;
if (!empty($lsfield) && !empty($lsfield->param1)) {

    $curl = new \curl();

    if (!empty($lsfield->param2)) {
        $curl->setHeader('Authorization: Basic ' . $lsfield->param2);
    }

    $curlresponse = $curl->get($lsfield->param1, ['email' => $USER->email]);

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

} else {
    // Allways use the localstyles if the remote service is not configured.
    echo $localstyles;
    $SESSION->profilefield_learningstyles_styles = $localstyles;
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

try {
    $DB->insert_record('profilefield_ls_getlog', $data);
} catch (Exception $e) {
    debugging($e->getMessage());
}

if ($localstyles) {
    $SESSION->profilefield_learningstyles_styles = $localstyles;
    echo @json_encode($localstyles);
} else if ($remotestyles) {
    $SESSION->profilefield_learningstyles_styles = $remotestyles;
    echo @json_encode($remotestyles);
} else {
    $SESSION->profilefield_learningstyles_nullstyles++;
    echo 'null';
}

exit;

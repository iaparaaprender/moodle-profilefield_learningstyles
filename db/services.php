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
 * External functions and service definitions.
 *
 * @package     profilefield_learningstyles
 * @copyright   2024 David Herney - cirano
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'profilefield_learningstyles_save' => [
        'classname' => '\profilefield_learningstyles\external\save',
        'classpath' => '',
        'description' => 'Save the test results.',
        'type' => 'write',
        'ajax' => true,
        'loginrequired' => true,
    ],

    'profilefield_learningstyles_get' => [
        'classname' => '\profilefield_learningstyles\external\get',
        'classpath' => '',
        'description' => 'Get the current user learning styles.',
        'type' => 'read',
        'ajax' => true,
        'loginrequired' => true,
    ],

];

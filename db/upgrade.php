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
 * Upgrade scripts
 *
 * @package     profilefield_learningstyles
 * @category    upgrade
 * @copyright   2024 David Herney - cirano
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade script for profile field: learningstyles
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_profilefield_learningstyles_upgrade($oldversion) {
    global $CFG, $DB;

    require_once($CFG->libdir.'/db/upgradelib.php'); // Core Upgrade-related functions.

    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

    if ($oldversion < 2024012203.06) {

        // Define table to store answers history.
        $table = new xmldb_table('profilefield_learningstyles');

        // Adding fields to the table.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('answers', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('processing', XMLDB_TYPE_INTEGER, '3', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('understanding', XMLDB_TYPE_INTEGER, '3', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('perception', XMLDB_TYPE_INTEGER, '3', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('input', XMLDB_TYPE_INTEGER, '3', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to the table.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);

        // Adding indexes to the table.
        $table->add_index('timecreated', XMLDB_INDEX_NOTUNIQUE, ['timecreated']);

        // Conditionally launch create table for infected_files.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        $currentfields = $DB->get_records('user_info_field', ['datatype' => 'learningstyles'], 'id', 'id');

        if (!empty($currentfields)) {
            $ids = implode(',', array_keys($currentfields));
            $currentanswers = $DB->get_records_select('user_info_data', 'fieldid IN (' . $ids . ')', [], 'id', 'id, userid, data');

            foreach ($currentanswers as $data) {
                $olddata = json_decode($data->data, true);

                if (is_array($olddata)) {
                    $olddata = (object)$olddata;
                }

                if ($olddata && property_exists($olddata, 'answers') && property_exists($olddata, 'affinity')) {

                    if (is_array($olddata->answers)) {
                        $olddata->answers = (object)$olddata->answers;
                    }

                    if (is_array($olddata->affinity)) {
                        $olddata->affinity = (object)$olddata->affinity;
                    }

                    $newdata = new \stdClass();
                    $newdata->userid = $data->userid;
                    $newdata->answers = json_encode($olddata->answers);
                    $newdata->processing = $olddata->affinity->processing;
                    $newdata->understanding = $olddata->affinity->understanding;
                    $newdata->perception = $olddata->affinity->perception;
                    $newdata->input = $olddata->affinity->input;
                    $newdata->timecreated = 0;

                    $DB->insert_record('profilefield_learningstyles', $newdata);
                }
            }
        }

        upgrade_plugin_savepoint(true, 2024012203.06, 'profilefield', 'learningstyles');
    }

    return true;
}



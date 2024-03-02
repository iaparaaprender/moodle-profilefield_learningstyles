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

namespace profilefield_learningstyles\systemreports;

use profilefield_learningstyles\entities\test as entitytest;
use core_reportbuilder\local\helpers\database;
use core_reportbuilder\system_report;
use core_reportbuilder\local\report\action;

/**
 * Test report.
 *
 * @package     profilefield_learningstyles
 * @copyright   2024 David Herney - cirano
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class test extends system_report {

    /**
     * Initialise report, we need to set the main table, load our entities and set columns/filters
     */
    protected function initialise(): void {
        global $PAGE, $USER, $DB;

        // We need to ensure page context is always set, as required by output and string formatting.
        $PAGE->set_context($this->get_context());

        // Our main entity, it contains all of the column definitions that we need.
        $entitymain = new entitytest();
        $entitymainalias = $entitymain->get_table_alias('user_info_data');

        $this->set_main_table('user_info_data', $entitymainalias);
        $this->add_entity($entitymain);

        // Add the base condition to the report.
        $fields = $DB->get_records('user_info_field', ['datatype' => 'learningstyles'], 'sortorder ASC');

        if (empty($fields)) {
            return;
        }

        $fieldids = implode(',', array_keys($fields));

        $params = [];
        $where = [
            "$entitymainalias.fieldid IN ($fieldids)",
        ];

        if (!has_capability('profilefield/learningstyles:report', $this->get_context())) {

            $param = database::generate_param_name();
            $where[] = "$entitymainalias.userid = :$param";
            $params[$param] = $USER->id;

        }

        $wheresql = implode(' AND ', $where);

        $this->add_base_condition_sql($wheresql, $params);

        // Now we can call our helper methods to add the content we want to include in the report.
        $this->add_columns();
        $this->add_filters();
        $this->add_base_fields("{$entitymainalias}.id");

        // Set if report can be downloaded.
        $this->set_downloadable(true);
    }

    /**
     * Validates access to view this report
     *
     * @return bool
     */
    protected function can_view(): bool {
        return !isguestuser();
    }

    /**
     * Adds the columns we want to display in the report
     *
     * They are all provided by the entities we previously added in the {@see initialise} method, referencing each by their
     * unique identifier
     */
    public function add_columns(): void {
        $columns = [
            'test:id',
            'test:userid',
            'test:affinity',
            'test:data',
        ];

        $this->add_columns_from_entities($columns);
    }

    /**
     * Adds the filters we want to display in the report
     *
     * They are all provided by the entities we previously added in the {@see initialise} method, referencing each by their
     * unique identifier
     */
    protected function add_filters(): void {
        $filters = [
            'test:userid',
            'test:view',
        ];

        $this->add_filters_from_entities($filters);
    }
}

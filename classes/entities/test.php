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

declare(strict_types=1);

namespace profilefield_learningstyles\entities;

use lang_string;
use core_reportbuilder\local\entities\base;
use core_reportbuilder\local\filters\date;
use core_reportbuilder\local\filters\text;
use core_reportbuilder\local\filters\number;
use core_reportbuilder\local\filters\select;
use core_reportbuilder\local\helpers\format;
use core_reportbuilder\local\report\column;
use core_reportbuilder\local\report\filter;

/**
 * Transaction entity
 *
 * @package     profilefield_learningstyles
 * @copyright   2024 David Herney - cirano
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class test extends base {

    /**
     * Database tables that this entity uses and their default aliases
     *
     * @return array
     */
    protected function get_default_table_aliases(): array {
        return [
            'user_info_data' => 'uid'
        ];
    }

    /**
     * The default title for this entity
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('testtitle', 'profilefield_learningstyles');
    }

    /**
     * Initialise the entity, add all user fields and all 'visible' user profile fields
     *
     * @return base
     */
    public function initialise(): base {

        $columns = $this->get_all_columns();

        foreach ($columns as $column) {
            $this->add_column($column);
        }

        $filters = $this->get_all_filters();
        foreach ($filters as $filter) {
            $this
                ->add_filter($filter)
                ->add_condition($filter);
        }

        return $this;
    }

    /**
     * Add extra columns to report.
     * @return array
     * @throws \coding_exception
     */
    protected function get_all_columns(): array {
        $testalias = $this->get_table_alias('user_info_data');

        $columns[] = (new column(
            'id',
            new lang_string('id', 'profilefield_learningstyles'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_fields("$testalias.id")
            ->set_type(column::TYPE_INTEGER)
            ->set_is_sortable(true);

        $columns[] = (new column(
            'userid',
            new lang_string('reportuserid', 'profilefield_learningstyles'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_fields("$testalias.userid")
            ->set_type(column::TYPE_INTEGER)
            ->set_is_sortable(true);

        $columns[] = (new column(
            'affinity',
            new lang_string('affinity', 'profilefield_learningstyles'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_fields("$testalias.data")
            ->set_type(column::TYPE_TEXT)
            ->set_is_sortable(false)
            ->set_callback(static function(?string $data): string {
                $json = json_decode($data, true);
                if (empty($json) || empty($json['affinity'])) {
                    return '';
                }
                $affinity = [];
                foreach ($json['affinity'] as $key => $value) {
                    $affinity[] = $key . ': ' . $value;
                }

                return implode(', ', $affinity);
            });

        $columns[] = (new column(
            'data',
            new lang_string('reportdata', 'profilefield_learningstyles'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_fields("$testalias.data")
            ->set_type(column::TYPE_TEXT)
            ->set_is_sortable(false);


        return $columns;
    }

    /**
     * Return list of all available filters
     *
     * @return filter[]
     */
    protected function get_all_filters(): array {

        $filters = [];
        $testalias = $this->get_table_alias('user_info_data');

        $filters[] = (new filter(
            number::class,
            'userid',
            new lang_string('reportuserid', 'profilefield_learningstyles'),
            $this->get_entity_name(),
            "$testalias.userid",
        ))
            ->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'view',
            new lang_string('reportfilterview', 'profilefield_learningstyles'),
            $this->get_entity_name(),
            "$testalias.data",
        ))
            ->add_joins($this->get_joins());

        return $filters;
    }

}

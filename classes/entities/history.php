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
use core_reportbuilder\local\report\column;
use core_reportbuilder\local\report\filter;

/**
 * History entity
 *
 * @package     profilefield_learningstyles
 * @copyright   2024 David Herney - cirano
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class history extends base {

    /**
     * Database tables that this entity uses and their default aliases
     *
     * @return array
     */
    protected function get_default_table_aliases(): array {
        return [
            'profilefield_learningstyles' => 'pls'
        ];
    }

    /**
     * The default title for this entity
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('historytitle', 'profilefield_learningstyles');
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
        $testalias = $this->get_table_alias('profilefield_learningstyles');

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
            'processing',
            new lang_string('dimension_processing', 'profilefield_learningstyles'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_fields("$testalias.processing")
            ->set_type(column::TYPE_INTEGER)
            ->set_is_sortable(true);

        $columns[] = (new column(
            'understanding',
            new lang_string('dimension_understanding', 'profilefield_learningstyles'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_fields("$testalias.understanding")
            ->set_type(column::TYPE_INTEGER)
            ->set_is_sortable(true);

        $columns[] = (new column(
            'perception',
            new lang_string('dimension_perception', 'profilefield_learningstyles'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_fields("$testalias.perception")
            ->set_type(column::TYPE_INTEGER)
            ->set_is_sortable(true);

        $columns[] = (new column(
            'input',
            new lang_string('dimension_input', 'profilefield_learningstyles'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_fields("$testalias.input")
            ->set_type(column::TYPE_INTEGER)
            ->set_is_sortable(true);

        $columns[] = (new column(
            'timecreated',
            new lang_string('timecreated', 'profilefield_learningstyles'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_fields("$testalias.timecreated")
            ->set_type(column::TYPE_TIMESTAMP)
            ->set_is_sortable(true)
            ->set_callback(static function(?int $timecreated): string {
                return empty($timecreated) ? '' : userdate($timecreated);
            });

        $columns[] = (new column(
            'answers',
            new lang_string('reportdata', 'profilefield_learningstyles'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_fields("$testalias.answers")
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
        $testalias = $this->get_table_alias('profilefield_learningstyles');

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
            'answers',
            new lang_string('reportfilterview', 'profilefield_learningstyles'),
            $this->get_entity_name(),
            "$testalias.answers",
        ))
            ->add_joins($this->get_joins());

        $filters[] = (new filter(
            number::class,
            'processing',
            new lang_string('dimension_processing', 'profilefield_learningstyles'),
            $this->get_entity_name(),
            "$testalias.processing",
        ))
            ->add_joins($this->get_joins());

        $filters[] = (new filter(
            number::class,
            'understanding',
            new lang_string('dimension_understanding', 'profilefield_learningstyles'),
            $this->get_entity_name(),
            "$testalias.understanding",
        ))
            ->add_joins($this->get_joins());

        $filters[] = (new filter(
            number::class,
            'perception',
            new lang_string('dimension_perception', 'profilefield_learningstyles'),
            $this->get_entity_name(),
            "$testalias.perception",
        ))
            ->add_joins($this->get_joins());

        $filters[] = (new filter(
            number::class,
            'input',
            new lang_string('dimension_input', 'profilefield_learningstyles'),
            $this->get_entity_name(),
            "$testalias.input",
        ))
            ->add_joins($this->get_joins());

        $filters[] = (new filter(
            date::class,
            'timecreated',
            new lang_string('timecreated', 'profilefield_learningstyles'),
            $this->get_entity_name(),
            "$testalias.timecreated",
        ))
            ->add_joins($this->get_joins());

        return $filters;
    }

}

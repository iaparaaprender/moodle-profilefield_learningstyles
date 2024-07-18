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
 * Getlog entity
 *
 * @package     profilefield_learningstyles
 * @copyright   2024 David Herney - cirano
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class getlog extends base {

    /**
     * Database tables that this entity uses and their default aliases
     *
     * @return array
     */
    protected function get_default_table_aliases(): array {
        return [
            'profilefield_ls_getlog' => 'plg'
        ];
    }

    /**
     * The default title for this entity
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('getlogtitle', 'profilefield_learningstyles');
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
        $getlogalias = $this->get_table_alias('profilefield_ls_getlog');

        $columns[] = (new column(
            'id',
            new lang_string('id', 'profilefield_learningstyles'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_fields("$getlogalias.id")
            ->set_type(column::TYPE_INTEGER)
            ->set_is_sortable(true);

        $columns[] = (new column(
            'userid',
            new lang_string('reportuserid', 'profilefield_learningstyles'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_fields("$getlogalias.userid")
            ->set_type(column::TYPE_INTEGER)
            ->set_is_sortable(true);

        $columns[] = (new column(
            'difference',
            new lang_string('getlog_difference', 'profilefield_learningstyles'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_fields("$getlogalias.difference")
            ->set_type(column::TYPE_INTEGER)
            ->set_is_sortable(true);

        $columns[] = (new column(
            'timerequest',
            new lang_string('getlog_timerequest', 'profilefield_learningstyles'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_fields("$getlogalias.timerequest")
            ->set_type(column::TYPE_TIMESTAMP)
            ->set_is_sortable(true)
            ->set_callback(static function(?int $timerequest): string {
                return empty($timerequest) ? '' : userdate($timerequest, '%Y-%m-%d %H:%M:%S');
            });

        $columns[] = (new column(
            'localanswers',
            new lang_string('getlog_localanswers', 'profilefield_learningstyles'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_fields("$getlogalias.localanswers")
            ->set_type(column::TYPE_TEXT)
            ->set_is_sortable(false);

        $columns[] = (new column(
            'remoteanswers',
            new lang_string('getlog_remoteanswers', 'profilefield_learningstyles'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_fields("$getlogalias.remoteanswers")
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
        $getlogalias = $this->get_table_alias('profilefield_ls_getlog');

        $filters[] = (new filter(
            number::class,
            'userid',
            new lang_string('reportuserid', 'profilefield_learningstyles'),
            $this->get_entity_name(),
            "$getlogalias.userid",
        ))
            ->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'localanswers',
            new lang_string('getlog_localanswers', 'profilefield_learningstyles'),
            $this->get_entity_name(),
            "$getlogalias.localanswers",
        ))
            ->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'remoteanswers',
            new lang_string('getlog_remoteanswers', 'profilefield_learningstyles'),
            $this->get_entity_name(),
            "$getlogalias.remoteanswers",
        ))
            ->add_joins($this->get_joins());

        $filters[] = (new filter(
            number::class,
            'difference',
            new lang_string('getlog_difference', 'profilefield_learningstyles'),
            $this->get_entity_name(),
            "$getlogalias.difference",
        ))
            ->add_joins($this->get_joins());

        $filters[] = (new filter(
            date::class,
            'timerequest',
            new lang_string('getlog_timerequest', 'profilefield_learningstyles'),
            $this->get_entity_name(),
            "$getlogalias.timerequest",
        ))
            ->add_joins($this->get_joins());

        return $filters;
    }

}

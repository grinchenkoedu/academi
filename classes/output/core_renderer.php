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
 * Course renderer.
 *
 * @package theme_academi
 * @copyright 2023 onwards LMSACE Dev Team (http://www.lmsace.com)
 * @author LMSACE Dev Team
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_academi\output;

use moodle_url;
use core_course_category;

/**
 * The core course renderer.
 *
 * Can be retrieved with the following:
 * $renderer = $PAGE->get_renderer('core','course');
 */
class core_renderer extends \theme_boost\output\core_renderer
{
    /**
     * Returns the moodle_url for the favicon.
     *
     * This renderer function is copied and modified from /lib/outputrenderers.php
     *
     * @return moodle_url The moodle_url for the favicon
     * @since Moodle 2.5.1 2.6
     */
    public function favicon()
    {
        if (!empty($this->page->theme->settings->favicon)) {
            return $this->page->theme->setting_file_url('favicon', 'favicon');
        }

        return parent::favicon();
    }

    public function navbar(): string
    {
        return $this->render_from_template('theme_academi/breadcrumbs', [
            'list' => $this->get_breadcrumb_list()
        ]);
    }

    private function get_breadcrumb_list(): array
    {
        $page = $this->page;
        $items = [$this->build_home_item()];

        $navitems = $page->navbar->get_items();

        // Standard breadcrumb for non-course pages.
        if (count($navitems) > 1 && strpos($page->pagetype, 'course-view') !== 0) {
            $items = array_merge($items, $this->build_standard_nav_items($navitems));
            return $this->wrap_single_breadcrumb_trail($items);
        }

        // Course view breadcrumb: home > courses > categories > current course.
        if (strpos($page->pagetype, 'course-view') === 0 && !empty($page->course->id)) {
            $items = array_merge($items, $this->build_course_view_items($page->course));
            return $this->wrap_single_breadcrumb_trail($items);
        }

        return $this->wrap_single_breadcrumb_trail($items);
    }

    private function build_home_item(): array
    {
        return [
            'text' => get_string('home'),
            'url' => (new moodle_url('/'))->out(false)
        ];
    }

    private function build_standard_nav_items(array $navitems): array
    {
        $items = [];

        // The first navbar item is Home; skip it because we already prepend our own Home item.
        array_shift($navitems);

        foreach ($navitems as $item) {
            $items[] = [
                'text' => $item->text,
                'url' => $item->action ? $item->action->out(false) : null
            ];
        }

        $this->make_last_item_non_clickable($items);

        return $items;
    }

    private function build_course_view_items(\stdClass $course): array
    {
        $items = [
            [
                'text' => get_string('courses'),
                'url' => (new moodle_url('/course/index.php'))->out(false)
            ]
        ];

        if (!empty($course->category)) {
            $items = array_merge($items, $this->build_category_items_from_id((int)$course->category));
        }

        $items[] = [
            'text' => $course->fullname,
            'url' => null
        ];

        return $items;
    }

    private function build_category_items_from_id(int $categoryid): array
    {
        $items = [];
        $category = core_course_category::get($categoryid);

        // get_parents() returns IDs, so resolve each one to display readable category names.
        foreach ($category->get_parents() as $parentid) {
            $items[] = $this->build_category_item((int)$parentid);
        }

        $items[] = $this->build_category_item($category->id, $category->name);

        return $items;
    }

    private function build_category_item(int $categoryid, $name = null): array
    {
        if ($name === null) {
            try {
                $resolvedcategory = core_course_category::get($categoryid);
                $name = $resolvedcategory->name;
            } catch (\Exception $exception) {
                $name = (string)$categoryid;
            }
        }

        return [
            'text' => $name,
            'url' => (new moodle_url('/course/index.php', [
                'categoryid' => $categoryid
            ]))->out(false)
        ];
    }

    private function make_last_item_non_clickable(array &$items)
    {
        $lastitemindex = count($items) - 1;
        if ($lastitemindex >= 0) {
            $items[$lastitemindex]['url'] = null;
        }
    }

    private function wrap_single_breadcrumb_trail(array $items): array
    {
        return [['items' => $items]];
    }
}

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
use custom_menu;

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
        $logo = $this->image_url('favicon', 'theme');
        if (!empty($this->page->theme->settings->favicon)) {
            $logo = $this->page->theme->setting_file_url('favicon', 'favicon');
        } else {
            $logo = parent::favicon();
        }
        return $logo;
    }

    /**
     * Custom menu in header.
     * @param custom_menu $menu
     * @return string
     */
    public function navbar(): string
    {
        return $this->render_from_template('theme_academi/breadcrumbs', [
            'list' => $this->get_breadcrumb_list()
        ]);
    }

    private function get_breadcrumb_list(): array
    {
        $page = $this->page;

        $items = [];

        // --- Home ---
        $items[] = [
            'text' => get_string('home'),
            'url' => (new moodle_url('/'))->out(false)
        ];

        $navitems = $page->navbar->get_items();

        // --- 1. standard breadcrumb ---
        if (count($navitems) > 1 && strpos($page->pagetype, 'course-view') !== 0) {

            array_shift($navitems);

            foreach ($navitems as $item) {
                $items[] = [
                    'text' => $item->text,
                    'url' => $item->action ? $item->action->out(false) : null
                ];
            }

            // Keep the current page breadcrumb non-clickable for consistent UX/styling.
            $lastitemindex = count($items) - 1;
            if ($lastitemindex >= 0) {
                $items[$lastitemindex]['url'] = null;
            }

            return [['items' => $items]];
        }

        // --- 2. COURSE VIEW ---
        if (strpos($page->pagetype, 'course-view') === 0 && !empty($page->course->id)) {

            $course = $page->course;

            // --- categories ---
            $items[] = [
                'text' => get_string('courses'),
                'url' => (new moodle_url('/course/index.php'))->out(false)
            ];

            if (!empty($course->category)) {
                $cat = \core_course_category::get($course->category);

                $cats = $cat->get_parents();
                $cats[] = $cat;

                foreach ($cats as $c) {
                    $items[] = [
                        'text' => $c->name ?? $c,
                        'url' => (new moodle_url('/course/index.php', [
                            'categoryid' => $c->id ?? $c
                        ]))->out(false)
                    ];
                }
            }

            $items[] = [
                'text' => $course->fullname,
                'url' => null
            ];

            // --- separate My courses line ---
            $my = [];

            $my[] = [
                'text' => get_string('home'),
                'url' => (new moodle_url('/'))->out(false)
            ];

            $my[] = [
                'text' => get_string('mycourses'),
                'url' => (new moodle_url('/my/courses.php'))->out(false)
            ];

            $my[] = [
                'text' => $course->fullname,
                'url' => null
            ];

            return [
                ['items' => $items],
                ['items' => $my]
            ];
        }

        // --- fallback ---
        return [['items' => $items]];
    }
}

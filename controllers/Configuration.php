<?php

/**
 * MemberData-Forms Configuration Controller
 * 
 * @package             memberdata-forms
 * @author              Michiel Uitdehaag
 * @copyright           2020 Michiel Uitdehaag for muis IT
 * @licenses            GPL-3.0-or-later
 *
 * This file is part of memberdata-forms.
 *
 * memberdata-forms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * memberdata-forms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with memberdata-forms.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace MemberDataForms\Controllers;

use MemberDataForms\Lib\Display;

class Configuration extends Base
{
    public function index()
    {
        $this->authenticate();
        $data = [];
        return $data;
    }

    public function basic($data)
    {
        $sheet = intval($data['model']['sheet'] ?? 0);
        $configuration = \apply_filters('memberdata_configuration', ['sheet' => $sheet, 'configuration' => []]);
        $attributes = $configuration['configuration'];
        $sheets = \apply_filters('memberdata_find_sheets', []);
        $pages = array_map(function ($post) {
            return [
                "id" => $post->ID,
                "title" => $post->post_title,
                "slug" => get_page_uri($post),
            ];
        }, get_pages());

        return [
            "attributes" => array_column($attributes, 'name'),
            "sheets" => $sheets,
            "pages" => $pages
        ];
    }

    public function save($data)
    {
        $this->authenticate();
        $config = self::getFormsConfig();
        update_option(Display::PACKAGENAME . '_values', json_encode($config));
        return $config;
    }
}

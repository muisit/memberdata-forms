<?php

/**
 * MemberData-Forms API Interface
 *
 * @package             memberdata-forms
 * @author              Michiel Uitdehaag
 * @copyright           2020 - 2023 Michiel Uitdehaag for muis IT
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

namespace MemberDataForms\Lib;

use MemberDataForms\Controllers\Configuration;
use MemberDataForms\Controllers\Forms;

class API extends \MemberData\Lib\API
{
    protected $routes = [
        'configuration.post' => [Configuration::class, 'index'],
        'configuration.basic.post' => [Configuration::class, 'basic'],
        'configuration.save.post' => [Configuration::class, 'save'],
        'forms.post' => [Forms::class, 'index'],
        'forms.get.post' => [Forms::class, 'get'],
        'forms.save.post' => [Forms::class, 'save'],
        'forms.result.post' => [Forms::class, 'result'],
    ];

    public static function register($plugin)
    {
        add_action('wp_ajax_' . Display::PACKAGENAME, fn($page) => self::ajaxHandler($page));
        add_action('wp_ajax_nopriv_' . Display::PACKAGENAME, fn($page) => self::ajaxHandler($page));
    }

    protected static function ajaxHandler($page)
    {
        $dat = new static();
        $dat->resolve();
    }
}

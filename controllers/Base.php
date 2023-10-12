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

use MemberData\Controllers\Base as BaseController;
use MemberDataForms\Lib\Display;

class Base extends BaseController
{
    public static function getFormsConfig()
    {
        $config = json_decode(get_option(Display::PACKAGENAME . "_values"));
        if (empty($config)) {
            $config = (object)[];
            add_option(Display::PACKAGENAME . '_values', json_encode($config));
        }
        return $config;
    }
}

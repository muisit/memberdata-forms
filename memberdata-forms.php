<?php

/**
 * MemberData Forms
 *
 * @package             memberdata-forms
 * @author              Michiel Uitdehaag
 * @copyright           2020 - 2023 Michiel Uitdehaag for muis IT
 * @licenses            GPL-3.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:         memberdata-forms
 * Plugin URI:          https://github.com/muisit/memberdata-forms
 * Description:         Creating forms attached to sheets and validate data entry
 * Version:             1.0.6
 * Requires at least:   6.1
 * Requires PHP:        7.2
 * Author:              Michiel Uitdehaag
 * Author URI:          https://www.muisit.nl
 * License:             GNU GPLv3
 * License URI:         https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:         memberdata-forms
 * Domain Path:         /languages
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

define('MEMBERDATAFORMS_VERSION', "1.0.6");
define('MEMBERDATAFORMS_PACKAGENAME', 'memberdata_forms');

function memberdata_forms_autoloader($name)
{
    if (!strncmp($name, 'MemberDataForms\\', 16)) {
        $elements = explode('\\', $name);
        // require at least MemberDataForms\<sub>\<name>, so 3 elements
        if (sizeof($elements) > 2 && $elements[0] == "MemberDataForms") {
            $fname = $elements[sizeof($elements) - 1] . ".php";
            $dir = strtolower(implode("/", array_splice($elements, 1, -1))); // remove the base part and the file itself
            if (file_exists(__DIR__ . "/" . $dir . "/" . $fname)) {
                include(__DIR__ . "/" . $dir . "/" . $fname);
            }
        }
    }
}

spl_autoload_register('memberdata_forms_autoloader');

if (defined('ABSPATH')) {
    \MemberDataForms\Lib\Activator::register(__FILE__);

    add_action('memberdata_loaded', function () {
        \MemberDataForms\Lib\Display::register(__FILE__);
        \MemberDataForms\Lib\API::register(__FILE__);
        \MemberDataForms\Lib\Plugin::register(__FILE__);
    });
}

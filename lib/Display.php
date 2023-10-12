<?php

/**
 * MemberData-Forms page display routines
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

use MemberData\Controllers\Base;
use MemberData\Lib\Services\ManifestService;
use MemberDataForms\Models\Form;

class Display
{
    public const PACKAGENAME = MEMBERDATAFORMS_PACKAGENAME;
    private const ADMINSCRIPT = 'src/admin.ts';
    private const FESCRIPT = 'src/frontend.ts';

    public static function adminPage()
    {
        ManifestService::enqueueAssets(self::ADMINSCRIPT, self::PACKAGENAME, __DIR__ . '/../dist', 'memberdata-forms');
        $nonce = wp_create_nonce(Base::createNonceText());
        $data = [
            "nonce" => $nonce,
            "url" => admin_url('admin-ajax.php?action=' . self::PACKAGENAME),
        ];
        $obj = json_encode($data);
        $id = self::PACKAGENAME . '-admin';
        $dataName = 'data-' . self::PACKAGENAME;
        echo <<<HEREDOC
        <div id="$id" $dataName='$obj'></div>
HEREDOC;
    }

    public static function addFrontEnd(array $attrs, string $content, string $tag)
    {
        if (isset($attrs['name'])) {
            $model = new Form();
            $result = $model->select()->where('name', $attrs['name'])->first();

            if (!empty($result)) {
                ManifestService::enqueueAssets(self::FESCRIPT, self::PACKAGENAME, __DIR__ . '/../dist', 'memberdata-forms');
                $nonce = wp_create_nonce(Base::createNonceText());
                $data = [
                    "nonce" => $nonce,
                    "url" => admin_url('admin-ajax.php?action=' . self::PACKAGENAME),
                    "form" => $result->id
                ];
                $obj = json_encode($data);
                $id = self::PACKAGENAME . '-fe';
                $dataName = 'data-' . self::PACKAGENAME;
                return <<<HEREDOC
                    <div id="$id" $dataName='$obj'></div>
                HEREDOC;
            }
        }
        return '';
    }

    public static function register($plugin)
    {
        load_plugin_textdomain('memberdata-forms', false, basename(dirname(__DIR__)) . '/languages');
        add_action('admin_menu', fn() => self::adminMenu());
        add_shortcode('memberdata-forms', fn($attrs, $content, $tag) => self::addFrontEnd($attrs, $content, $tag));
    }

    private static function adminMenu()
    {
        add_submenu_page(
            'memberdata',
            __('Forms'),
            __('Forms'),
            'manage_memberdata',
            self::PACKAGENAME,
            fn() => Display::adminPage(),
            50
        );
    }
}

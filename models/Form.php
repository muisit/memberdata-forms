<?php

/**
 * MemberData-Forms Result model
 * 
 * @package             memberdata-forms
 * @author              Michiel Uitdehaag
 * @copyright           2023 Michiel Uitdehaag for muis IT
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

namespace MemberDataForms\Models;

use MemberData\Models\Base;
use MemberData\Models\Sheet;
use MemberData\Models\QueryBuilder;

class Form extends Base
{
    public $table = "memberdataforms_form";
    public $pk = "id";
    public $fields = array("id", "sheet_id", "name", "settings" );

    public $rules = array(
        "id" => "skip",
        "sheet_id" => "nullable|int|min=0|model=" . Sheet::class,
        "name" => "required|trim|min=1",
        "settings" => "json",
    );

    public function save()
    {
        $originalValue = $this->settings;
        if (!is_string($this->settings)) {
            $this->settings = json_encode($this->settings);
        }

        $retval = parent::save();
        $this->settings = $originalValue;
        return $retval;
    }

    public function load()
    {
        parent::load();
        $this->getSettings();
    }

    public function getSettings()
    {
        if (is_string($this->settings)) {
            $output = json_decode($this->settings);
            $this->settings = (object)[];
            if ($output !== false) {
                $this->settings = $output;
            }
        }
        return $this->settings;
    }
}

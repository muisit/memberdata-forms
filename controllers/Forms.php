<?php

/**
 * MemberData-Forms Forms Controller
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
use MemberDataForms\Models\Form;
use MemberDataForms\Lib\Services\FormSubmissionService;

class Forms extends Base
{
    public function index()
    {
        $this->authenticate();
        $model = new Form();
        $forms = $model->select()->orderBy('name', 'asc')->orderBy('id', 'desc')->get();
        $data = array_map(function ($formData) {
            $form = new Form($formData);
            return $form->export();
        }, $forms);
        return $data;
    }

    public function get($data)
    {
        // front-end interface
        $this->checkNonce();
        $model = new Form();
        $form = $model->select()->where('id', $data['model']['id'] ?? 0)->first();
        if (empty($form)) {
            die(403);
        }
        return (new Form($form))->export();
    }

    public function save($data)
    {
        $this->authenticate();
        $model = new Form();
        $modelData = $data['model'] ?? [];

        if (isset($modelData['id'])) {
            $model = new Form($modelData['id']);
            $model->load();
        }
        $model->name = $modelData['name'] ?? '';
        $model->sheet_id = empty($modelData['sheet_id']) ? null : intval($modelData['sheet_id']);
        $model->settings = $modelData['settings'] ?? (object)[];

        if ($model->validate()) {
            $model->save();
            return $model->export();
        }
        else {
            return false;
        }
    }

    public function result($data)
    {
        // front-end interface
        $this->checkNonce();
        $model = new Form();
        $form = $model->select()->where('id', $data['model']['id'] ?? 0)->first();
        if (empty($form)) {
            die(403);
        }
        $form = new Form($form);
        if ($form->isNew()) {
            die(403);
        }
        return FormSubmissionService::submit($form, $data['model']['results']);
    }
}

<?php

/**
 * MemberData-Forms Form Submission Service
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

namespace MemberDataForms\Lib\Services;

use MemberDataForms\Models\Form;
use MemberData\Models\Sheet;
use MemberData\Models\Member;
use DateTimeImmutable;

class FormSubmissionService
{
    public static function submit(Form $form, array $dataFields)
    {
        $settings = $form->getSettings();
        $fields = (array) $settings->fields;

        list($messages, $dataFields) = self::validateFields($fields, $dataFields);

        $cnt = 0;
        array_walk($messages, fn($lst) => $cnt += count($lst));
        if ($cnt == 0) {
            return self::saveForm($form, $dataFields);
        }
        return ['success' => false, 'messages' => $messages];
    }

    private static function saveForm(Form $form, array $dataFields)
    {
        $settings = $form->getSettings();
        $emailAfter = $settings->emailAfter ?? '';
        $emailSubject = $settings->emailSubject ?? 'Submitted form';
        $redirectPage = $settings->redirectPage ?? null;

        if (strlen($emailAfter)) {
            self::sendEmail($emailAfter, $emailSubject, $settings->fields, $dataFields);
        }
        self::storeDataInSheet($form->sheet_id, $settings->fields, $dataFields);

        if (!empty($redirectPage)) {
            return ['success' => true, 'url' => home_url($redirectPage)];
        }
        return ['success' => true];
    }

    private static function storeDataInSheet($sheetId, $fields, $dataFields)
    {
        $sheet = new Sheet($sheetId);
        $sheet->load();
        if (!empty($sheet) && !$sheet->isNew()) {
            $configuration = \apply_filters('memberdata_configuration', ['sheet' => $sheet->getKey(), 'configuration' => []]);
            $attributes = $configuration['configuration'];
            $validNames = array_column($attributes, 'name');

            $member = new Member();
            $member->sheet_id = $sheet->getKey();
            $member = \apply_filters('memberdata_save_member', $member);

            $attrValues = [];
            for ($i = 0; $i < count($fields); $i++) {
                $val = count($dataFields) > $i ? $dataFields[$i] : '';
                $attrName = $fields[$i]->attribute;

                if (in_array($attrName, $validNames)) {
                    $attrValues[$attrName] = $val;
                }
            }

            $settings = \apply_filters(
                'memberdata_save_attributes',
                [
                    'member' => $member,
                    'attributes' => $attrValues,
                    'messages' => [],
                    'config' => $attributes
                ]
            );

            if (count($settings['messages'])) {
                error_log("Form submission causes errors in MemberData row entry: " . json_encode($settings));
            }
        }
    }

    private static function sendEmail($emailAddresses, $subject, $fields, $data)
    {
        $fieldTexts = '';
        for ($i = 0; $i < count($fields); $i++) {
            $val = count($data) > $i ? $data[$i] : '';
            $fieldTexts .= $fields[$i]->label . ': "' . $val . '"<br/>';
        }
        $template = <<<HEREDOC
        Hi there,<br/>
        <br/>
        A form was submitted on your site with the following contents:<br/>
        $fieldTexts
        <br/>
        The form data has been stored in the attached sheet.<br/>
        <br/>
        Yours sincerely,<br/>
        <br/>
        The website
        HEREDOC;
        $headers = array('Content-Type: text/html; charset=UTF-8');

        wp_mail($emailAddresses, $subject, $template, $headers);
    }


    private static function validateFields(array $fields, array $data)
    {
        $returnData = [];
        $messages = [];
        for ($i = 0; $i < count($fields); $i++) {
            list($fieldMessages, $fieldValue) = self::validateField($fields[$i], $data[$i] ?? '');
            $messages[] = $fieldMessages;
            $returnData[] = $fieldValue;
        }
        return [$messages, $returnData];
    }

    private static function validateField($field, $value)
    {
        $name = $field->label ?? 'Unknown';
        $options = $field->options ?? '';
        $rules = (array) ($field->rules ?? []);
        $type = $field->type ?? 'text-line';
        $rulesSet = array_keys($rules);
        $messages = [];
        $hasWidget = in_array('widget', $rulesSet);

        switch ($type) {
            case "text-line":
            case "text-area":
                $rules['trim'] = '';
                break;
            case "number":
                if (!in_array('number', $rulesSet)) {
                    $rules['number'] = $options;
                }
                break;
            case "date":
                // the widget always returns a YYYY-MM-DD/Y-m-d back-end value, so
                // no need to check this against the provided format
                if (!in_array('date', $rulesSet) && !$hasWidget) {
                    $rules['date'] = $options;
                }
                break;
            case "time":
                // the widget always returns a HH:mm:ss/H:i:s back-end value, so
                // no need to check this against the provided format
                if (!in_array('time', $rulesSet) && !$hasWidget) {
                    $rules['time'] = $options;
                }
                break;
            case "email":
                if (!in_array('email', $rulesSet)) {
                    $rules['email'] = $options;
                }
                break;
            default:
                break;
        }

        foreach ($rules as $rule => $parameter) {
            list($message, $value) = self::validateRule($rule, $parameter, $value, $name, $type, $options);
            if (!empty($message)) {
                $messages[] = $message;
            }
        }
        return [$messages, $value];
    }

    private static function validateRule($rule, $parameter, $value, $name, $type, $options)
    {
        if (strpos($parameter, ":now:") !== false) {
            $parameter = new DateTimeImmutable();
        }

        $msg = '';
        if (empty($value) && ($value !== 0 && $type == 'number') && ($value !== '0')) {
            if ($rule == 'required') {
                /* translators: %s is replaced with the field label */
                $msg = sprintf(__('%s is a required field', 'memberdata-forms'), $name);
            }
        }
        else {
            switch ($rule) {
                case 'email':
                    $retval = filter_var($value, FILTER_VALIDATE_EMAIL);
                    if ($retval === false) {
                        /* translators: %s is replaced with the field label */
                        $msg = sprintf(__('%s must be a valid e-mail address', 'memberdata-forms'), $name);
                    }
                    break;
                case 'date':
                case 'time':
                    $output = '';
                    try {
                        $dt = DateTimeImmutable::createFromFormat($parameter, $value);
                        if (is_object($dt)) {
                            $output = $dt->format($parameter);
                        }
                    }
                    catch (\Exception $e) {
                        error_log("validation exception on date/time: " . $e->getMessage());
                    }
                    // the value should have been formatted in the front-end. We only check
                    // that the passed value, in this format, actually resolves to that value
                    // when we reformat
                    if ($output != $value) {
                        if ($type == 'date') {
                            /* translators: %1$s is replaced with the field label, %2$s is the target value */
                            $msg = sprintf(__('%1$s must be a valid date in the format %2$s', 'memberdata-forms'), $name, $parameter);
                        }
                        else {
                            /* translators: %1$s is replaced with the field label, %2$s is the target value */
                            $msg = sprintf(__('%1$s must be a valid time in the format %2$s', 'memberdata-forms'), $name, $parameter);
                        }
                    }
                    break;
                case 'lte':
                case 'lt':
                case 'gt':
                case 'gte':
                    $val = 0;
                    $wrt = 0;
                    switch ($type) {
                        case 'number':
                            $val = floatval($val);
                            if (is_nan($val)) {
                                $val = 0;
                            }
                            $wrt = floatval($parameter);
                            break;
                        case 'date':
                            $val = DateTimeImmutable::createFromFormat('Y-m-d', $value);
                            $wrt = is_string($parameter) ? DateTimeImmutable::createFromFormat('Y-m-d', $parameter) : $parameter;
                            break;
                        case 'time':
                            $val = DateTimeImmutable::createFromFormat('H:i:s', $value);
                            $wrt = is_string($parameter) ? DateTimeImmutable::createFromFormat('Y-m-d', $parameter) : $parameter;
                            break;
                        default:
                            $val = strlen($value);
                            $wrt = intval($parameter);
                            break;
                    }
                    $msg = self::compareValue($rule, $val, $wrt, $name, $type, $options);
                    break;
                case 'int':
                    if (!is_numeric($value) || strpos($value, '.') !== false) {
                        /* translators: %s is replaced with the field label */
                        $msg = sprintf(__('%s must be an integral numeric value', 'memberdata-forms'), $rule);
                    }
                    $value = intval($value);
                    break;
                case 'number':
                    $value = floatval($value);
                    if (is_nan($value) || is_infinite($value)) {
                        /* translators: %s is replaced with the field label */
                        $msg = sprintf(__('%s must be a numeric value', 'memberdata-forms'), $rule);
                    }
                    break;
                case 'trim':
                    $value = trim($value);
                    break;
                default:
                        /* translators: %s is replaced with the rule name */
                        $msg = sprintf(__('Rule %s not implemented', 'memberdata-forms'), $rule);
                    break;
            }
        }
        return [$msg, $value];
    }

    private static function compareValue(string $rule, mixed $val, mixed $wrt, string $name, string $type, string $options): string
    {
        $result = true;
        $compareresult = self::compareValues($val, $wrt, $type);
        switch ($rule) {
            case 'lt':
                $result = $compareresult == -1;
                /* translators: %1$s is replaced with the field label, %2$f with the target value */
                $msgnum = sprintf(__('%1$s must be smaller than %2$f', 'memberdata-forms'), $name, $type == 'number' ? $val : 0);
                /* translators: %1$s is replaced with the field label, %2$s with the target value */
                $msgdate = sprintf(__('%1$s must be before %2$s', 'memberdata-forms'), $name, ($type == 'date' || $type == 'time') ? $val->format($options) : '');
                /* translators: %1$s is replaced with the field label, %2$d with the target value */
                $msglength = sprintf(__('%1$s must be smaller than %2$d characters', 'memberdata-forms'), $name, !($type == 'date' || $type == 'time') ? $val : 0);
                break;
            case 'lte':
                $result = $compareresult != 1;
                /* translators: %1$s is replaced with the field label, %2$f with the target value */
                $msgnum = sprintf(__('%1$s must be smaller than or equal to %2$f', 'memberdata-forms'), $name, $type == 'number' ? $val : 0);
                /* translators: %1$s is replaced with the field label, %2$s with the target value */
                $msgdate = sprintf(__('%1$s must be before or at %2$s', 'memberdata-forms'), $name, ($type == 'date' || $type == 'time') ? $val->format($options) : '');
                /* translators: %1$s is replaced with the field label, %2$d with the target value */
                $msglength = sprintf(__('%1$s must be smaller than or equal to %2$d characters', 'memberdata-forms'), $name, !($type == 'date' || $type == 'time') ? $val : 0);
                break;
            case 'gte':
                $result = $compareresult != -1;
                /* translators: %1$s is replaced with the field label, %2$f with the target value */
                $msgnum = sprintf(__('%1$s must be greater than or equal to %2$f', 'memberdata-forms'), $name, $type == 'number' ? $val : 0);
                /* translators: %1$s is replaced with the field label, %2$s with the target value */
                $msgdate = sprintf(__('%1$s must be at or after %2$s', 'memberdata-forms'), $name, ($type == 'date' || $type == 'time') ? $val->format($options) : '');
                /* translators: %1$s is replaced with the field label, %2$d with the target value */
                $msglength = sprintf(__('%1$s must have at least %2$d characters', 'memberdata-forms'), $name, !($type == 'date' || $type == 'time') ? $val : 0);
                break;
            case 'gt':
                $result = $compareresult == 1;
                /* translators: %1$s is replaced with the field label, %2$f with the target value */
                $msgnum = sprintf(__('%1$s must be greater than %2$f', 'memberdata-forms'), $name, $type == 'number' ? $val : 0);
                /* translators: %1$s is replaced with the field label, %2$s with the target value */
                $msgdate = sprintf(__('%1$s must be after %2$s', 'memberdata-forms'), $name, ($type == 'date' || $type == 'time') ? $val->format($options) : '');
                /* translators: %1$s is replaced with the field label, %2$d with the target value */
                $msglength = sprintf(__('%1$s must have more than %2$d characters', 'memberdata-forms'), $name, !($type == 'date' || $type == 'time') ? $val : 0);
                break;
        }

        if ($result) {
            return '';
        }
        else {
            switch ($type) {
                case 'number':
                    return $msgnum;
                case 'date':
                case 'time':
                    return $msgdate;
                default:
                    return $msglength;
            }
        }
    }

    private static function compareValues(mixed $val, mixed $wrt, string $type): int
    {
        switch ($type) {
            default:
            case 'number':
                return $val > $wrt ? 1 : ($val < $wrt ? -1 : 0);
            case 'date':
            case 'time':
                $v1 = intval('1' . $val->format('YmdHis'));
                $v2 = intval('1' . $wrt->format('YmdHis'));
                return $val > $wrt ? 1 : ($val < $wrt ? -1 : 0);
        }
        return 0;
    }
}

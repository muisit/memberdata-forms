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
        error_log("form submit");
        $settings = $form->getSettings();
        $fields = (array) $settings->fields;

        error_log("validating fields");
        list($messages, $dataFields) = self::validateFields($fields, $dataFields);

        $cnt = 0;
        array_walk($messages, fn($lst) => $cnt += count($lst));
        if ($cnt == 0) {
            error_log("no errors, saving form");
            return self::saveForm($form, $dataFields);
        }
        error_log("validation errors found");
        return ['success' => false, 'messages' => $messages];
    }

    private static function saveForm(Form $form, array $dataFields)
    {
        $settings = $form->getSettings();
        $emailAfter = $settings->emailAfter ?? '';
        $emailSubject = $settings->emailSubject ?? 'Submitted form';
        $redirectPage = $settings->redirectPage ?? null;

        if (strlen($emailAfter)) {
            error_log("sending email");
            self::sendEmail($emailAfter, $emailSubject, $settings->fields, $dataFields);
        }
        error_log("storing data in sheet");
        self::storeDataInSheet($form->sheet_id, $settings->fields, $dataFields);

        if (!empty($redirectPage)) {
            error_log("redirecting");
            return ['success' => true, 'url' => home_url($redirectPage)];
        }
        error_log("no redirection found");
        return ['success' => true];
    }

    private static function storeDataInSheet($sheetId, $fields, $dataFields)
    {
        $sheet = new Sheet($sheetId);
        $sheet->load();
        if (!empty($sheet) && !$sheet->isNew()) {
            error_log("sheet exists");
            $configuration = \apply_filters('memberdata_configuration', ['sheet' => $sheet->getKey(), 'configuration' => []]);
            $attributes = $configuration['configuration'];
            $validNames = array_column($attributes, 'name');

            error_log("creating member row");
            $member = new Member();
            $member->sheet_id = $sheet->getKey();
            $member = \apply_filters('memberdata_save_member', $member);

            error_log("collecting attributes");
            $attrValues = [];
            for ($i = 0; $i < count($fields); $i++) {
                $val = count($dataFields) > $i ? $dataFields[$i] : '';
                $attrName = $fields[$i]->attribute;

                if (in_array($attrName, $validNames)) {
                    $attrValues[$attrName] = $val;
                }
            }

            error_log("storing row data");
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
        error_log("sending mail to $emailAddresses, $subject");
        wp_mail($emailAddresses, $subject, $template, $headers);
    }


    private static function validateFields(array $fields, array $data)
    {
        error_log("validating fields " . count($fields) . '/' . count($data));
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
        error_log("validating field $name, $type");
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
        error_log("validating rule $rule, $parameter, $type, $options, " . (is_string($value) ? $value : '<no string>'));
        if (strpos($parameter, ":now:") !== false) {
            $parameter = new DateTimeImmutable();
        }

        $msg = '';
        if (empty($value) && ($value !== 0 && $type == 'number') && ($value !== '0')) {
            if ($rule == 'required') {
                $msg = sprintf(__('%s is a required field', 'memberdata-forms'), $name);
            }
        }
        else {
            switch ($rule) {
                case 'email':
                    $retval = filter_var($value, FILTER_VALIDATE_EMAIL);
                    if ($retval === false) {
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
                            $msg = sprintf(__('%s must be a valid date in the format %s', 'memberdata-forms'), $name, $parameter);
                        }
                        else {
                            $msg = sprintf(__('%s must be a valid time in the format %s', 'memberdata-forms'), $name, $parameter);
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
                            error_log("wrt is " . json_encode($wrt));
                            break;
                        case 'time':
                            $val = DateTimeImmutable::createFromFormat('H:i:s', $value);
                            $wrt = is_string($parameter) ? DateTimeImmutable::createFromFormat('Y-m-d', $parameter) : $parameter;
                            error_log("wrt is " . json_encode($wrt));
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
                        $msg = sprintf(__('%s must be an integral numeric value', 'memberdata-forms'), $rule);
                    }
                    $value = intval($value);
                    break;
                case 'number':
                    $value = floatval($value);
                    if (is_nan($value) || is_infinite($value)) {
                        $msg = sprintf(__('%s must be an numeric value', 'memberdata-forms'), $rule);
                    }
                    break;
                case 'trim':
                    $value = trim($value);
                    break;
                default:
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
                $msgnum = sprintf(__('%s must be smaller than %f', 'memberdata-forms'), $name, $type == 'number' ? $val : 0);
                $msgdate = sprintf(__('%s must be before %s', 'memberdata-forms'), $name, ($type == 'date' || $type == 'time') ? $val->format($options) : '');
                $msglength = sprintf(__('%s must be smaller than %d characters', 'memberdata-forms'), $name, !($type == 'date' || $type == 'time') ? $val : 0);
                break;
            case 'lte':
                $result = $compareresult != 1;
                $msgnum = sprintf(__('%s must be smaller or equal to %f', 'memberdata-forms'), $name, $type == 'number' ? $val : 0);
                $msgdate = sprintf(__('%s must be before or at %s', 'memberdata-forms'), $name, ($type == 'date' || $type == 'time') ? $val->format($options) : '');
                $msglength = sprintf(__('%s must be smaller than or equal to %d characters', 'memberdata-forms'), $name, !($type == 'date' || $type == 'time') ? $val : 0);
                break;
            case 'gte':
                $result = $compareresult != -1;
                $msgnum = sprintf(__('%s must be greater than or equal to %f', 'memberdata-forms'), $name, $type == 'number' ? $val : 0);
                $msgdate = sprintf(__('%s must be after or at %s', 'memberdata-forms'), $name, ($type == 'date' || $type == 'time') ? $val->format($options) : '');
                $msglength = sprintf(__('%s must have at least %d characters', 'memberdata-forms'), $name, !($type == 'date' || $type == 'time') ? $val : 0);
                break;
            case 'gt':
                $result = $compareresult == 1;
                $msgnum = sprintf(__('%s must be greater than %f', 'memberdata-forms'), $name, $type == 'number' ? $val : 0);
                $msgdate = sprintf(__('%s must be after %s', 'memberdata-forms'), $name, ($type == 'date' || $type == 'time') ? $val->format($options) : '');
                $msglength = sprintf(__('%s must have more than %d characters', 'memberdata-forms'), $name, !($type == 'date' || $type == 'time') ? $val : 0);
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


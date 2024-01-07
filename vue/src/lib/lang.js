const { __ } = window.wp.i18n;
import { vsprintf } from 'sprintf-js';

export default {
    ADMIN_PAGE: __('MemberData Forms Manager', 'memberdata-forms'),
    SHORTCODE: __('Shortcode', 'memberdata-forms'),
    TAB_SETTINGS: __('Settings', 'memberdata-forms'),
    SAVE: __('Save', 'memberdata-forms'),
    CLOSE: __('Close', 'memberdata-forms'),
    CANCEL: __('Cancel', 'memberdata-forms'),
    REMOVE: __('Remove', 'memberdata-forms'),
    ADDFORM: __('Add Form', 'memberdata-forms'),
    ADD: __('Add', 'memberdata-forms'),
    ERROR_NETWORK: __("There was a network problem saving the data. Please try again or reload the page", 'memberdata-forms'),
    ERROR_NETWORK_GEN: __('Network error, please try again', 'memberdata-forms'),
    ERROR_VALIDATION: __('Validation', 'memberdata-forms'),
    ERROR_SAVE_FORM: __('Error saving form:', 'memberdata-forms'),
    ERROR_SAVE_RESULTS: __('There was an error saving the form results. Please try again or reload the page. If the problem persists, please contact administration.', 'memberdata-forms'),
    DATEFORMATWITHOUTMINUTES: __('YYYY-MM-DD HH:mm', 'memberdata-forms'),
    NEWFORM: __('NewForm', 'memberdata-forms'),
    NAME: __('Name', 'memberdata-forms'),
    SHEET: __('Sheet', 'memberdata-forms'),
    SHEETSELECT: __('Select a sheet', 'memberdata-forms'),
    OPTION1: __('Option 1', 'memberdata-forms'),
    OPTION2: __('Option 2', 'memberdata-forms'),
    OPTION3: __('Option 3', 'memberdata-forms'),
    NEWFIELD: __('New Field', 'memberdata-forms'),
    FIELDDIALOG_TITLE: __('Edit Field', 'memberdata-forms'),
    LABEL: __('Label', 'memberdata-forms'),
    ATTRIBUTE: __('Attribute', 'memberdata-forms'),
    SELECTATTRIBUTE: __('Select an attribute', 'memberdata-forms'),
    DEFAULTVALUE: __('Default value', 'memberdata-forms'),
    DEFAULTVALUEDESCR: __('Use \':now:\' to get the current date-time', 'memberdata-forms'),
    TYPE: __('Type', 'memberdata-forms'),
    OPTIONS: __('Options', 'memberdata-forms'),
    OPTIONSDESCR: __('Enter a list of values, separated with bars (|)', 'memberdata-forms'),
    STYLING: __('Style', 'memberdata-forms'),
    STYLINGDESCR: __('Enter an optional style sheet', 'memberdata-forms'),
    FORMAT: __('Format', 'memberdata-forms'),
    FORMATDESCR: __('Examples: \'Y-m-d\', \'H:i:s\', \'0.00\'', 'memberdata-forms'),
    HEIGHT: __('Height', 'memberdata-forms'),
    HEIGHTDESCR: __('Indicate a height in number of lines', 'memberdata-forms'),
    RULES: __('Rules', 'memberdata-forms'),
    OPTIONTEXT: __('Text-line', 'memberdata-forms'),
    OPTIONAREA: __('Text-area', 'memberdata-forms'),
    OPTIONNUMBER: __('Number', 'memberdata-forms'),
    OPTIONDATE: __('Date', 'memberdata-forms'),
    OPTIONTIME: __('Time', 'memberdata-forms'),
    OPTIONEMAIL: __('E-mail', 'memberdata-forms'),
    OPTIONSELECT: __('Single selection', 'memberdata-forms'),
    OPTIONMSELECT: __('Limited multiple selection', 'memberdata-forms'),
    OPTIONUSELECT: __('Limited single selection', 'memberdata-forms'),
    OPTIONCHECKBOX: __('Checkbox', 'memberdata-forms'),
    OPTIONHIDDEN: __('Hidden', 'memberdata-forms'),
    OPTIONTEXTCONTENT: __('Text content', 'memberdata-forms'),
    RULEWIDGET: __('Widget', 'memberdata-forms'),
    RULEUSEWIDGET: __('use javascript widget for entry', 'memberdata-forms'),
    RULEREQUIRED: __('is required', 'memberdata-forms'),
    RULELT: __('must be less than or before:', 'memberdata-forms'),
    RULELTE: __('must be less than (before) or equal to:', 'memberdata-forms'),
    RULEGTE: __('must be greater than (after) or equal to:', 'memberdata-forms'),
    RULEGT: __('must be greater than or after:', 'memberdata-forms'),
    RULETRIM: __('trim whitespace characters from start and end', 'memberdata-forms'),
    RULEINT: __('must be an integral number', 'memberdata-forms'),
    SUBMITBUTTON: __('Submit label', 'memberdata-forms'),
    SUBMITBUTTONSUBMIT: __('Submit', 'memberdata-forms'),
    SUBMITBUTTONENTER: __('Enter', 'memberdata-forms'),
    SUBMITBUTTONSEND: __('Send', 'memberdata-forms'),
    SUBMITBUTTONREGISTER: __('Register', 'memberdata-forms'),
    SUBMITBUTTONDONE: __('Done', 'memberdata-forms'),
    SUBMITBUTTONCLOSE: __('Close', 'memberdata-forms'),
    EMAILAFTER: __('Email afterwards', 'memberdata-forms'),
    EMAILAFTERDESCR: __('Enter a comma-separated list of addresses to send the form content to afterwards', 'memberdata-forms'),
    EMAILSUBJECT: __('Email subject', 'memberdata-forms'),
    REDIRECTPAGE: __('Redirect page', 'memberdata-forms'),
    PICKDATE: __('Pick a date', 'memberdata-forms'),
    PICKTIME: __('Pick a time', 'memberdata-forms'),
    ISREQUIRED: __('This value is required!', 'memberdata-forms'),
    MSGRULEREQUIRED: __('%s is a required field', 'memberdata-forms'),
    MSGRULELT: __('%1$s must be smaller than %2$f', 'memberdata-forms'),
    MSGRULELTE: __('%1$s must be smaller than or equal to %2$f', 'memberdata-forms'),
    MSGRULEGTE: __('%1$s must be greater than or equal to %2$f', 'memberdata-forms'),
    MSGRULEGT: __('%1$s must be greater than %2$f', 'memberdata-forms'),
    MSGRULELTDATE: __('%1$s must be before %2$s', 'memberdata-forms'),
    MSGRULELTEDATE: __('%1$s must be before or at %2$s', 'memberdata-forms'),
    MSGRULEGTEDATE: __('%1$s must be at or after %2$s', 'memberdata-forms'),
    MSGRULEGTDATE: __('%1$s must be after %2$s', 'memberdata-forms'),
    MSGRULELTSTRING: __('%1$s must be smaller than %2$d characters', 'memberdata-forms'),
    MSGRULELTESTRING: __('%1$s must be smaller than or equal to %2$d characters', 'memberdata-forms'),
    MSGRULEGTESTRING: __('%1$s must have at least %2$d characters', 'memberdata-forms'),
    MSGRULEGTSTRING: __('%1$s must have more than %2$d characters', 'memberdata-forms'),
    MSGRULEINT: __('%s must be an integral numeric value', 'memberdata-forms'),
    MSGRULENUMBER: __('%s must be a numeric value', 'memberdata-forms'),
    MSGRULEDATE: __('%1$s must be a valid date in the format %2$s', 'memberdata-forms'),
    MSGRULETIME: __('%1$s must be a valid time in the format %2$s', 'memberdata-forms'),
    MSGRULEEMAIL: __('%s must be a valid e-mail address', 'memberdata-forms'),
    CONFIRMDELETEFIELD: __('Are you sure you want to remove this field permanently?', 'memberdata-forms'),
    RESULTS_SAVED: __('The form was saved successfully. You can now close this page', 'memberdata-forms'),
    BUTTONCOLOUR: __('Button colour', 'memberdata-forms'),
    BORDERCOLOUR: __('Border colour', 'memberdata-forms'),
    TEXTCOLOUR: __('Text colour', 'memberdata-forms'),
    LABELCOLOUR: __('Label colour', 'memberdata-forms'),
    ERRORCOLOUR: __('Error colour', 'memberdata-forms'),
    REQUIREDCOLOUR: __('Indicator colour', 'memberdata-forms'),

    replace: function (txt) {
        var args = Array.prototype.slice.call(arguments);
        args.shift();
        return vsprintf(txt, args);
    }
};

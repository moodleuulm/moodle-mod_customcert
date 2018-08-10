<?php
// This file is part of the customcert module for Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'customcertelement_completiontable', language 'en'.
 *
 * @package     customcertelement_completiontable
 * @copyright   2018 Nathan Nguyen  <nathannguyen@@catalyst-au.net>
 * @copyright   2018 Dmitrii Metelkin <dmitriim@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

$string['content'] = 'Content';
$string['content_help'] = 'This hold the configuration of the content of the completion table.<br /><br />
        <h4>Header rows</h4>
        <h5>Syntax:<h5>
        <pre>^:30: Header1 ^:55: Header2 ^:15: Header3 ^</pre>
        <h5>Explanation:</h5>
        <ul>
        <li>Header rows are identified by the circumflex accent sign.</li>
        <li>You can build as many header row columns as necessary.</li>
        <li>The numbers between the colons represent the width of the header row column. The width is mandatory.</li>
        <li>The string Header1 will be output as the heading of the header row column and can be set to an arbitrary string.</li>
        </ul>
        <h4>Group rows</h4>
        <h5>Syntax:<h5>
        <pre># Group1 #</pre>
        <h5>Explanation:</h5>
        <ul>
        <li>Group rows are identified by the hash sign.</li>
        <li>Group rows always span all columns of the completion table.</li>
        <li>The string Group1 will be output as the content of the group row and can be set to an arbitrary string.</li>
        </ul>
        <h4>Section rows</h4>
        <h5>Syntax:<h5>
        <pre>|:left: Label |:left: Description |:left: {completion:4211} |</pre>
        <h5>Explanation:</h5>
        <ul>
        <li>Section rows are identified by the pipe sign.</li>
        <li>You should add as many section row columns as you have header row columns.</li>
        <li>The string between the colons represent the alignment of the section row column. Valid values are left, center, right. The alignment is mandatory.</li>
        <li>The strings Label and Description will be output as the content of the section row column and can be set to arbitrary strings.</li>
        <li>The string {completion:4211} is a placeholder and will output a completion string according to the daterange setting below. 4211 is the activity ID which will be evaluated for getting the completion status.</li>
        <li>Instead of a hardcoded label string, you are allowed to use a placeholder {section-label:12345}. This string will output the label of the section with the given section ID. Below, you will find a list of the section IDs in this course.</li>
        <li>When using the {section-label:12345} placeholder within a section row, the section row will only be added to the certificate if the corresponding section is visible.</li>
        </ul>
        ';
$string['dateranges'] = 'Dateranges';
$string['fallbackstring'] = 'Fallback string';
$string['fallbackstring_help'] = 'This string will be displayed if no daterange applies to a date. If Fallback string is not set, then there will be no output at all.';
$string['help'] = 'Configure a string representation for each daterange. Make sure your ranges do not overlap, otherwise the first matched daterange will be applied. If no daterange matched a date, then Fallback string will be displayed. If Fallback string is not set, then there will be no output.<br/> If you mark a date range as Recurring, then the configured year will not be considered. As the year of a recurring date range is not considered, you are not allowed to configure a recurring date range with more than 12 months as it would become ambiguous otherwise.';
$string['placeholders'] = 'Also following placeholders could be used in the string representation or fallback string. <br/> {{range_first_year}} - first year of the matched range,<br/> {{range_last_year}} - last year of the matched range,<br/> {{recurring_range_first_year}} - first year of the matched recurring period,<br/> {{recurring_range_last_year}} - last year of the matched recurring period,<br/> {{current_year}} - the current year,<br/>  {{date_year}} - a year of the users\'s date.';
$string['maxranges'] = 'Maximum number ranges';
$string['maxranges_desc'] = 'Set a maximum number of date ranges per each element';
$string['pluginname'] = 'Completion Table';
$string['privacy:metadata'] = 'The Completion Table plugin does not store any personal data.';
$string['start'] = 'Start';
$string['end'] = 'End';
$string['datestring'] = 'String';
$string['daterange'] = 'Daterange {$a}';
$string['error:enabled'] = 'You must have at least one datarange enabled';
$string['error:datestring'] = 'You must provide string representation for the enabled datarange';
$string['error:elementwidthlessthanzero'] = 'Width must be greater than 0';
$string['error:elementwidthgreaterthanmaxwidth'] = 'Width must be less than or equal {$a}mm';
$string['error:enddate'] = 'End date must be after Start date';
$string['error:recurring'] = 'Recurring range must not be longer than 12 months';
$string['preview'] = 'Preview {$a}';
$string['recurring'] = 'Recurring?';
$string['sectionplaceholders'] = 'Section placeholders';

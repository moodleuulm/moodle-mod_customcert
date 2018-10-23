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
 * This file contains the customcert completion table element.
 *
 * @package    customcertelement_completiontable
 * @copyright  2018 Nathan Nguyen  <nathannguyen@@catalyst-au.net>
 * @copyright  2018 Dmitrii Metelkin <dmitriim@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace customcertelement_completiontable;

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

/**
 * The customcert completion table element.
 *
 * @package    customcertelement_completiontable
 * @copyright  2018 Nathan Nguyen  <nathannguyen@@catalyst-au.net>
 * @copyright  2018 Dmitrii Metelkin <dmitriim@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class element extends \mod_customcert\element {

    /**
     * Constants for displaying the completion table.
     */
    const COMPLETION_DATE_PREVIEW = '&mdash;';
    const COMPLETION_DATE_NOT_COMPLETED = '&mdash;';

    /**
     * Max recurring period in seconds.
     */
    const MAX_RECURRING_PERIOD = 31556926; // 12 months.

    /**
     * Current year placeholder string.
     */
    const CURRENT_YEAR_PLACEHOLDER = '{{current_year}}';

    /**
     * First year in a date range placeholder string.
     */
    const RANGE_FIRST_YEAR_PLACEHOLDER = '{{range_first_year}}';

    /**
     * Last year in a date range placeholder string.
     */
    const RANGE_LAST_YEAR_PLACEHOLDER = '{{range_last_year}}';

    /**
     * First year in a date range placeholder string.
     */
    const RECUR_RANGE_FIRST_YEAR_PLACEHOLDER = '{{recurring_range_first_year}}';

    /**
     * Last year in a date range placeholder string.
     */
    const RECUR_RANGE_LAST_YEAR_PLACEHOLDER = '{{recurring_range_last_year}}';

    /**
     * A year in the user's date.
     */
    const DATE_YEAR_PLACEHOLDER = '{{date_year}}';

    /**
     * Default max number of dateranges per element.
     */
    const DEFAULT_MAX_RANGES = 10;

    /**
     * This function renders the form elements when adding a customcert element.
     *
     * @param \mod_customcert\edit_element_form $mform the edit_form instance
     */
    public function render_form_elements($mform) {
        global $DB, $COURSE;

        // Content Of The table.
        $mform->addElement('textarea', 'content', get_string('content', 'customcertelement_completiontable'),
                'wrap="virtual" rows="20" cols="100"');
        $mform->setType('content', PARAM_RAW);
        $mform->addHelpButton('content', 'content', 'customcertelement_completiontable');

        // List Of Sections.
        $sections = null;
        try {
            $sections = $DB->get_records_sql('SELECT *
                    FROM {course_sections}
                    WHERE course = :course
                    ORDER BY section',
                    array('course' => $COURSE->id));
        } catch ( \dml_exception $e) {
            $sections = null;
        }
        if ($sections) {
            $sectionlines = '';
            foreach ($sections as $section) {
                $sectionlines .= '<div>'.get_string('section').' '.$section->section;
                if ($section->name != '') {
                    $sectionlines .= ' ('. $section->name.')';
                }
                $sectionlines .= ': {section-label:'.$section->id.'}</div>';
            }
            $mform->addElement('static', 'sectionhelp', get_string('sectionplaceholders', 'customcertelement_completiontable'),
                    $sectionlines);
        }

        parent::render_form_elements($mform);

        $mform->addElement('header', 'dateranges', get_string('dateranges', 'customcertelement_completiontable'));
        $mform->addElement('static', 'help', '', get_string('help', 'customcertelement_completiontable'));
        $mform->addElement('static', 'placeholders', '', get_string('placeholders', 'customcertelement_completiontable'));

        $mform->addElement('text', 'fallbackstring', get_string('fallbackstring', 'customcertelement_completiontable'));
        $mform->addHelpButton('fallbackstring', 'fallbackstring', 'customcertelement_completiontable');
        $mform->setType('fallbackstring', PARAM_NOTAGS);

        if (!$maxranges = get_config('customcertelement_completiontable', 'maxranges')) {
            $maxranges = self::DEFAULT_MAX_RANGES;
        }

        if (!empty($this->get_data())) {
            if ($maxranges < $this->get_decoded_data()->numranges) {
                $maxranges = $this->get_decoded_data()->numranges;
            }
        }

        $mform->addElement('hidden', 'numranges', $maxranges);
        $mform->setType('numranges', PARAM_INT);

        for ($i = 0; $i < $maxranges; $i++) {

            $mform->addElement('static',
                $this->build_element_name('group', $i),
                get_string('daterange', 'customcertelement_completiontable', $i + 1),
                ''
            );

            $mform->addElement(
                'checkbox',
                $this->build_element_name('enabled', $i),
                get_string('enable')
            );
            $mform->setType($this->build_element_name('enabled', $i), PARAM_BOOL);

            $mform->addElement(
                'date_selector',
                $this->build_element_name('startdate', $i),
                get_string('start', 'customcertelement_completiontable')
            );
            $mform->setType($this->build_element_name('startdate', $i), PARAM_INT);

            $mform->addElement(
                'date_selector',
                $this->build_element_name('enddate', $i),
                get_string('end', 'customcertelement_completiontable')
            );
            $mform->setType($this->build_element_name('enddate', $i), PARAM_INT);

            $mform->addElement(
                'checkbox',
                $this->build_element_name('recurring', $i),
                get_string('recurring', 'customcertelement_completiontable')
            );
            $mform->setType($this->build_element_name('recurring', $i), PARAM_BOOL);

            $mform->addElement(
                'text',
                $this->build_element_name('datestring', $i),
                get_string('datestring', 'customcertelement_completiontable'),
                ['class' => 'datestring']
            );
            $mform->setType($this->build_element_name('datestring', $i), PARAM_NOTAGS);

            $mform->disabledIf($this->build_element_name('startdate', $i), $this->build_element_name('enabled', $i), 'notchecked');
            $mform->disabledIf($this->build_element_name('enddate', $i), $this->build_element_name('enabled', $i), 'notchecked');
            $mform->disabledIf($this->build_element_name('recurring', $i), $this->build_element_name('enabled', $i), 'notchecked');
            $mform->disabledIf($this->build_element_name('datestring', $i), $this->build_element_name('enabled', $i), 'notchecked');
        }
    }

    /**
     * A helper function to build consistent form element name.
     *
     * @param string $name
     * @param string $num
     *
     * @return string
     */
    protected function build_element_name($name, $num) {
        return $name . $num;
    }

    /**
     * Get decoded data stored in DB.
     *
     * @return \stdClass
     */
    protected function get_decoded_data() {
        return json_decode($this->get_data());
    }

    /**
     * Sets the data on the form when editing an element.
     *
     * @param \mod_customcert\edit_element_form $mform the edit_form instance
     */
    public function definition_after_data($mform) {
        if (!empty($this->get_data()) && !$mform->isSubmitted()) {
            $element = $mform->getElement('content');
            $element->setValue($this->get_decoded_data()->content);

            $element = $mform->getElement('fallbackstring');
            $element->setValue($this->get_decoded_data()->fallbackstring);

            $element = $mform->getElement('numranges');
            $numranges = $element->getValue();
            if ($numranges < $this->get_decoded_data()->numranges) {
                $element->setValue($this->get_decoded_data()->numranges);
            }

            foreach ($this->get_decoded_data()->dateranges as $key => $range) {
                $mform->setDefault($this->build_element_name('startdate', $key), $range->startdate);
                $mform->setDefault($this->build_element_name('enddate', $key), $range->enddate);
                $mform->setDefault($this->build_element_name('datestring', $key), $range->datestring);
                $mform->setDefault($this->build_element_name('recurring', $key), $range->recurring);
                $mform->setDefault($this->build_element_name('enabled', $key), $range->enabled);
            }
        }

        parent::definition_after_data($mform);
    }

    /**
     * Performs validation on the element values.
     *
     * @param array $data the submitted data
     * @param array $files the submitted files
     * @return array the validation errors
     */
    public function validate_form_elements($data, $files) {
        $errors = parent::validate_form_elements($data, $files);

        // Check if width is less than 0.
        if (isset($data['width']) && ($data['width'] < 0)) {
            $errors['width'] = get_string('error:elementwidthlessthanzero', 'customcertelement_completiontable');
        }
        // Check if width is greater than maximum width.
        $maxwidth = $this->get_max_width();
        if ($maxwidth > 0 && isset($data['width']) && ($data['width'] > $maxwidth)) {
            $errors['width'] = get_string('error:elementwidthgreaterthanmaxwidth', 'customcertelement_completiontable', $maxwidth);
        }

        // Check if at least one range is set.
        $error = get_string('error:enabled', 'customcertelement_completiontable');
        for ($i = 0; $i < $data['numranges']; $i++) {
            if (!empty($data[$this->build_element_name('enabled', $i)])) {
                $error = '';
            }
        }

        if (!empty($error)) {
            $errors['help'] = $error;
        }

        // Check that datestring is set for enabled dataranges.
        for ($i = 0; $i < $data['numranges']; $i++) {
            $enabled = $this->build_element_name('enabled', $i);
            $datestring = $this->build_element_name('datestring', $i);
            if (!empty($data[$enabled]) && empty($data[$datestring])) {
                $name = $this->build_element_name('datestring', $i);
                $errors[$name] = get_string('error:datestring', 'customcertelement_completiontable');
            }
        }

        for ($i = 0; $i < $data['numranges']; $i++) {
            $enabled = $this->build_element_name('enabled', $i);
            $recurring = $this->build_element_name('recurring', $i);
            $startdate = $this->build_element_name('startdate', $i);
            $enddate = $this->build_element_name('enddate', $i);
            $rangeperiod = $data[$enddate] - $data[$startdate];

            // Check that end date is correctly set.
            if (!empty($data[$enabled]) && $data[$startdate] >= $data[$enddate] ) {
                $errors[$this->build_element_name('enddate', $i)] = get_string('error:enddate', 'customcertelement_completiontable');
            }

            // Check that recurring dateranges are not longer than 12 months.
            if (!empty($data[$recurring]) && $rangeperiod >= self::MAX_RECURRING_PERIOD ) {
                $errors[$this->build_element_name('enddate', $i)] = get_string('error:recurring', 'customcertelement_completiontable');
            }
        }

        return $errors;
    }

    /**
     * This will handle how form data will be saved into the data column in the
     * customcert_elements table.
     *
     * @param \stdClass $data the form data
     * @return string the json encoded array
     */
    public function save_unique_data($data) {
        $arrtostore = array(
            'content' => $data->content,
            'fallbackstring' => $data->fallbackstring,
            'numranges' => 0,
            'dateranges' => [],
        );

        // Set Max Width.
        if ($data->width == 0) {
            $maxwidth = $this->get_max_width();
            $data->width = $maxwidth;
        }

        for ($i = 0; $i < $data->numranges; $i++) {
            $startdate = $this->build_element_name('startdate', $i);
            $enddate = $this->build_element_name('enddate', $i);
            $datestring = $this->build_element_name('datestring', $i);
            $recurring = $this->build_element_name('recurring', $i);
            $enabled = $this->build_element_name('enabled', $i);

            if (!empty($data->$datestring)) {
                $arrtostore['dateranges'][] = [
                    'startdate' => $data->$startdate,
                    'enddate' => $data->$enddate,
                    'datestring' => $data->$datestring,
                    'recurring' => !empty($data->$recurring),
                    'enabled' => !empty($data->$enabled),
                ];
                $arrtostore['numranges']++;
            }
        }

        // Encode these variables before saving into the DB.
        return json_encode($arrtostore);
    }

    /**
     * Handles rendering the element on the pdf.
     *
     * @param \pdf $pdf the pdf object
     * @param bool $preview true if it is a preview, false otherwise
     * @param \stdClass $user the user we are rendering this for
     */
    public function render($pdf, $preview, $user) {
        $content = $this->get_decoded_data()->content;
        $htmltext = \mod_customcert\element_helper::render_html_content($this, $this->render_table($content, $user, $preview));

        \mod_customcert\element_helper::render_content($pdf, $this, $htmltext);
    }

    /**
     * Get daterange string.
     *
     * @param int $date Unix stamp date.
     *
     * @return string
     */
    protected function get_daterange_string($date) {
        $matchedrange = null;
        $outputstring = '';
        $formatdata = [];
        $formatdata['date'] = $date;

        foreach ($this->get_decoded_data()->dateranges as $key => $range) {
            if ($this->is_recurring_range($range)) {
                if ($matchedrange = $this->get_matched_recurring_range($date, $range)) {
                    $outputstring = $matchedrange->datestring;
                    $formatdata['range'] = $range;
                    $formatdata['recurringrange'] = $matchedrange;
                    break;
                }
            } else {
                if ($this->is_date_in_range($date, $range)) {
                    $outputstring = $range->datestring;
                    $formatdata['range'] = $range;
                    break;
                }
            }
        }

        if (empty($outputstring) && !empty($this->get_decoded_data()->fallbackstring)) {
            $outputstring = $this->get_decoded_data()->fallbackstring;
        }

        return $this->format_date_string($outputstring, $formatdata);
    }

    /**
     * @param \stdClass $range Range object.
     *
     * @return bool
     */
    protected function is_recurring_range(\stdClass $range) {
        return !empty($range->recurring);
    }

    /**
     * Check if the provided date is in the date range.
     *
     * @param int $date Unix timestamp date to check.
     * @param \stdClass $range Range object.
     *
     * @return bool
     */
    protected function is_date_in_range($date, \stdClass $range) {
        return ($date >= $range->startdate && $date <= $range->enddate);
    }

    /**
     * Check if provided date is in the recurring date range.
     *
     * @param int $date Unix timestamp date to check.
     * @param \stdClass $range Range object.
     *
     * @return bool
     */
    protected function is_date_in_recurring_range($date, \stdClass $range) {
        $intdate = $this->build_number_from_date($date);
        $intstart = $this->build_number_from_date($range->startdate);
        $intend = $this->build_number_from_date($range->enddate);

        if (!$this->has_turn_of_the_year($range)) {
            if ($intdate >= $intstart && $intdate <= $intend) {
                return true;
            }
        } else {
            if ($intdate >= $intstart && $intdate >= $intend) {
                return true;
            }

            if ($intdate <= $intstart && $intdate <= $intend) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if provided recurring range has a turn of the year.
     *
     * @param \stdClass $reccurringrange Range object.
     *
     * @return bool
     */
    protected function has_turn_of_the_year(\stdClass $reccurringrange) {
        return date('Y', $reccurringrange->startdate) != date('Y', $reccurringrange->enddate);
    }

    /**
     * Check if provided date is in the start year of the recurring range with a turn of the year.
     *
     * @param int $date Unix timestamp date to check.
     * @param \stdClass $range Range object.
     *
     * @return bool
     */
    protected function in_start_year($date, \stdClass $range) {
        $intdate = $this->build_number_from_date($date);
        $intstart = $this->build_number_from_date($range->startdate);
        $intend = $this->build_number_from_date($range->enddate);

        return $intdate >= $intstart && $intdate >= $intend;
    }

    /**
     * Check if provided date is in the end year of the recurring range with a turn of the year.
     *
     * @param int $date Unix timestamp date to check.
     * @param \stdClass $range Range object.
     *
     * @return bool
     */
    protected function in_end_year($date, \stdClass $range) {
        $intdate = $this->build_number_from_date($date);
        $intstart = $this->build_number_from_date($range->startdate);
        $intend = $this->build_number_from_date($range->enddate);

        return $intdate <= $intstart && $intdate <= $intend;
    }

    /**
     * Return matched recurring date range.
     *
     * As recurring date ranges do not depend on the year,
     * we will use a date's year to build a new matched recurring date range with
     * start year and end year. This is required to replace placeholders like range_first_year and range_last_year.
     *
     * @param int $date Unix timestamp date to check.
     * @param \stdClass $range Range object.
     *
     * @return \stdClass || null
     */
    protected function get_matched_recurring_range($date, \stdClass $range) {
        if (!$this->is_date_in_recurring_range($date, $range)) {
            return null;
        }

        $matchedrage = clone $range;

        if ($this->has_turn_of_the_year($matchedrage)) {

            if ($this->in_start_year($date, $matchedrage)) {
                $startyear = date('Y', $date);
                $endyear = $startyear + 1;
                $matchedrage->startdate = strtotime(date('d.m.', $matchedrage->startdate) . $startyear);
                $matchedrage->enddate = strtotime(date('d.m.', $matchedrage->enddate) . $endyear);

                return $matchedrage;
            }

            if ($this->in_end_year($date, $matchedrage)) {
                $endyear = date('Y', $date);
                $startyear = $endyear - 1;
                $matchedrage->startdate = strtotime(date('d.m.', $matchedrage->startdate) . $startyear);
                $matchedrage->enddate = strtotime(date('d.m.', $matchedrage->enddate) . $endyear);

                return $matchedrage;
            }
        } else {
            $matchedrage->startdate = strtotime(date('d.m.', $matchedrage->startdate) . date('Y', $date));
            $matchedrage->enddate = strtotime(date('d.m.', $matchedrage->enddate) . date('Y', $date));

            return $matchedrage;
        }

        return null;
    }

    /**
     * Build number representation of the provided date.
     *
     * @param int $date Unix timestamp date to check.
     *
     * @return int
     */
    protected function build_number_from_date($date) {
        return (int)date('md', $date);
    }

    /**
     * Format date string based on different types of placeholders.
     *
     * @param array $formatdata A list of format data.
     *
     * @return string
     */
    protected function format_date_string($datestring, array $formatdata) {
        foreach ($this->get_placeholders() as $search => $replace) {
            $datestring = str_replace($search, $replace, $datestring);
        }

        if (!empty($formatdata['date'])) {
            foreach ($this->get_date_placeholders($formatdata['date']) as $search => $replace) {
                $datestring = str_replace($search, $replace, $datestring);
            }
        }

        if (!empty($formatdata['range'])) {
            foreach ($this->get_range_placeholders($formatdata['range']) as $search => $replace) {
                $datestring = str_replace($search, $replace, $datestring);
            }
        }

        if (!empty($formatdata['recurringrange'])) {
            foreach ($this->get_recurring_range_placeholders($formatdata['recurringrange']) as $search => $replace) {
                $datestring = str_replace($search, $replace, $datestring);
            }
        }

        return $datestring;
    }

    /**
     * Return a list of placeholders to replace in date string as search => $replace pairs.
     *
     * @return array
     */
    protected function get_placeholders() {
        return [
            self::CURRENT_YEAR_PLACEHOLDER => date('Y', time()),
        ];
    }

    /**
     * Return a list of user's date related placeholders to replace in date string as search => $replace pairs.

     * @param int $date Unix timestamp date to check.
     *
     * @return array
     */
    protected function get_date_placeholders($date) {
        return [
            self::DATE_YEAR_PLACEHOLDER => date('Y', $date),
        ];
    }

    /**
     * Return a list of range related placeholders to replace in date string as search => $replace pairs.
     *
     * @param \stdClass $range
     *
     * @return array
     */
    protected function get_range_placeholders(\stdClass $range) {
        return [
            self::RANGE_FIRST_YEAR_PLACEHOLDER => date('Y', $range->startdate),
            self::RANGE_LAST_YEAR_PLACEHOLDER => date('Y', $range->enddate),
        ];
    }

    /**
     * Return a list of recurring range s placeholders to replace in date string as search => $replace pairs.
     *
     * @param \stdClass $range
     *
     * @return array
     */
    protected function get_recurring_range_placeholders(\stdClass $range) {
        return [
            self::RECUR_RANGE_FIRST_YEAR_PLACEHOLDER => date('Y', $range->startdate),
            self::RECUR_RANGE_LAST_YEAR_PLACEHOLDER => date('Y', $range->enddate),
        ];
    }

    /**
     * Render the element in html.
     *
     * This function is used to render the element when we are using the
     * drag and drop interface to position it.
     *
     * @return string the html
     */
    public function render_html() {
        global $USER;

        $courseid = \mod_customcert\element_helper::get_courseid($this->get_id());
        $content = $this->get_decoded_data()->content;
        $text = format_text($content, FORMAT_HTML, ['context' => \context_course::instance($courseid)]);

        return \mod_customcert\element_helper::render_html_content($this, $this->render_table($text, $USER, true));
    }

    /**
     * This function is responsible for handling the restoration process of the element.
     *
     * We will want to update the course module the date element is pointing to as it will
     * have changed in the course restore.
     *
     * @param \restore_customcert_activity_task $restore
     */
    public function after_restore($restore) {
        global $DB;

        $data = $this->get_decoded_data();
        if ($newitem = \restore_dbops::get_backup_ids_record($restore->get_restoreid(), 'course_module', $data->dateitem)) {
            $data->dateitem = $newitem->newitemid;
            $DB->set_field('customcert_elements', 'data', $this->save_unique_data($data), array('id' => $this->get_id()));
        }
    }

    /**
     * Determine max width of the element.
     * @return int
     * @throws \dml_exception
     */
    private function get_max_width() {
        global $DB;
        $pageid = $this->get_pageid();
        $page = $DB->get_record('customcert_pages', array( 'id' => $pageid));
        $maxwidth = $page ? $page->width - $page->leftmargin - $page->rightmargin : 0;
        return $maxwidth;
    }

    /**
     * Completion date based on completion status of an course module
     * @param $cmid course module id
     * @param $user user
     * @param $preview preview mode
     * @return string completion date string
     * @throws \coding_exception
     */
    private function get_completion_date($cmid, $user, $preview) {
        global $DB;
        $cm = null;
        try {
            $cm = $DB->get_record('course_modules', array('id' => $cmid));
        } catch ( \dml_exception $e) {
            $cm = null;
        }

        if (!$cm) {
            return '<div style="color: red"> Invalid ID </div>';
        }

        if ($preview) {
            $completiondate = self::COMPLETION_DATE_PREVIEW;
        } else {
            $modulecompletion = null;
            try {
                $select = 'coursemoduleid = :cmid AND userid = :userid AND (
                        completionstate = :complstate1 OR
                        completionstate = :complstate2
                    )';
                $params = array(
                    'cmid' => $cmid,
                    'userid' => $user->id,
                    'complstate1' => COMPLETION_COMPLETE,
                    'complstate2' => COMPLETION_COMPLETE_PASS
                    /* We could also use negative matching with completionstate<>COMPLETION_INCOMPLETE AND
                    * completionstate<>COMPLETION_COMPLETE_FAIL;
                    * the solution chosen here might make the code easier to understand or easier to grep and is more aligned with
                    * other parts of the Moodle codebase.
                    */
                    );
                $modulecompletion = $DB->get_record_select('course_modules_completion', $select, $params, '*', IGNORE_MISSING);
            } catch ( \dml_exception $e) {
                $modulecompletion = null;
            }

            // Work around PHP_CodeStyle raising an error here due to newline in the ternary operator.
            // The proper fix is to upgrade this module to moodle-plugin-ci, version 2;
            // we are internally tracking this in LMS-3013.
            // @codingStandardsIgnoreStart
            $completiondate = $modulecompletion ?
                    $this->get_daterange_string($modulecompletion->timemodified) : self::COMPLETION_DATE_NOT_COMPLETED;
            // @codingStandardsIgnoreEnd
        }

        return $completiondate;
    }

    /**
     * Render completion table
     * @param $text : content of the table
     * @param $user : current user
     * @param $preview : preview mode
     * @return string
     * @throws \coding_exception
     */
    protected function render_table($text, $user, $preview) {
        global $DB;

        /* Adding '^' and '|' for easier mark up header and section row.
        *  eg, transform ^ header 1 ^ header 2 ^ header 3 ^ into
        *   ^ header 1 ^^ header 2 ^^ header 3 ^
        */
        $patterns = array(
                "/.\^:/m",
                "/.\|:/m",
        );
        $replacements = array(
                "^^:",
                "||:",
        );
        $text = preg_replace($patterns, $replacements, $text);

        // Header row.
        preg_match_all("/^\^(.+?)\^/m", $text, $matches);
        // The function preg_match_all return 2 dimension array.
        if ($matches && count($matches) >= 2) {
            foreach ($matches[0] as $key => $origin) {
                $headerrow = ($matches[1][$key]);
                $text = str_replace($origin, ':header:^'    .$headerrow  .'^', $text);
            }
        }

        // Group row.
        preg_match_all("/^#(.+?)#/m", $text, $matches);
        // The function preg_match_all return 2 dimension array.
        if ($matches && count($matches) >= 2) {
            foreach ($matches[0] as $key => $origin) {
                $grouprow = ($matches[1][$key]);
                $text = str_replace($origin, ':group:#'    .$grouprow  .'#', $text);
            }
        }

        // Section row.
        // Check visibility of all section rows which have a section-label placeholder.
        preg_match_all("/\|:(?:left|center|right):.?\{section-label:(.+?)\}\|/m", $text, $matches);
        // The function preg_match_all return 2 dimension array.
        if ($matches && count($matches) >= 2) {
            foreach ($matches[0] as $key => $origin) {
                $sectionid = ($matches[1][$key]);

                // Get Section.
                $section = null;
                try {
                    $section = $DB->get_record('course_sections', array('id' => $sectionid));
                } catch (\dml_exception $e) {
                    $section = null;
                }

                // Section Not Found.
                if ($section == null) {
                    $sectionlabel = ($matches[0][$key]);
                    $text = str_replace($origin, ':sectioninvalid:'.$origin , $text);
                } else {
                    $visibility = $section->visible;
                    $sectionlabel = $section->name != '' ? strip_tags($section->name) : ' ';
                    $sectionsummary = $section->summary != '' ? strip_tags($section->summary) : ' ';
                    if ($visibility == 0) { // Hidden sections.
                        $text = str_replace($origin, ':sectionhidden:'.$origin, $text);
                    } else { // Visible sections.
                        $text = str_replace($origin, ':sectionvisible:'.$origin, $text);
                    }
                    $text = str_replace("{section-label:$sectionid}", $sectionlabel, $text);
                    $text = str_replace("{section-summary:$sectionid}", $sectionsummary, $text);
                }
            }
        }
        // Fallback: Make all section which haven't a section-label placeholder visible by default.
        $text = preg_replace('/^\|/m', ':sectionvisible:|', $text);

        // Completion ID.
        preg_match_all("/\{completion:(.+?)\}/m", $text, $matches);
        // The function preg_match_all return 2 dimension array.
        if ($matches && count($matches) >= 2) {
            foreach ($matches[0] as $key => $origin) {
                $completionid = ($matches[1][$key]);
                $completiondate = $this->get_completion_date($completionid, $user, $preview);
                // Evaluation Completion status.
                if (!$preview) {
                    preg_match_all("/^\|(.+?\{completion:$completionid)\}.\|/m", $text, $completionmatch);
                    // The function preg_match_all return 2 dimension array.
                    if ($completionmatch && count($completionmatch) >= 2) {
                        foreach ($completionmatch[0] as $completionkey => $completionorigin) {
                            if (trim($completiondate) == self::COMPLETION_DATE_NOT_COMPLETED) {
                                $text = str_replace($completionorigin, $completionorigin . ':sectionnotcompleted:', $text);
                            } else {
                                $text = str_replace($completionorigin, $completionorigin. ':sectioncompleted:', $text);
                            }
                        }
                    }
                }
                $text = str_replace($origin, $completiondate, $text);
            }
        }

        // Transform to table.
        $patterns = array(

                "/:header:(.+?)$/m", // Header.
                "/:group:(.+?)$/m",  // Group.

                "/:sectionhidden:(.+?)$/m",  // Hidden section.
                "/:sectioninvalid:(.+?)$/m", // Invalid Section.

                "/:sectionvisible:(.+?):sectioncompleted:$/m", // Visible Section.
                "/:sectionvisible:(.+?):sectionnotcompleted:$/m", // Visible Section.
                "/:sectionvisible:(.+?)$/m", // Visible Section.

                "/\^:(\d+):[ ]*(.+?)[ ]*\^/m", // Header Content.
                "/#[ ]*(.+?)[ ]*#/m", // Group Content.
                "/\|:(left|center|right):[ ]*(.+?)\|/m", // Section Row Content.
        );

        if ($preview) {
            $replacements = array(

                    '<tr style="color: black; font-style: normal; font-weight: bold;">$1</tr>', // Header.
                    '<tr style="color: black; font-style: normal; font-weight: bold;">$1</tr>', // Group.

                    '<tr style="color: black;font-style: italic;">$1</tr>', // Hidden.
                    '<tr style="color: red; font-style: normal;">$1</tr>', // Invalid.

                    '<tr style="color: black; font-style: normal;">$1</tr>', // Visible, completed.
                    '<tr style="color: black; font-style: normal;">$1</tr>', // Visible, not completed.
                    '<tr style="color: black; font-style: normal;">$1</tr>', // Visible, in general.

                    '<td style="text-align: center; width: $1%; font-weight: bold;">$2</td>', // Header Content.
                    '<td style="word-wrap: break-word; text-align: center; font-weight: bold;" colspan="3" >$1</td>', // Group Content.
                    '<td style="word-wrap: break-word; text-align: $1; vertical-align: top; padding: 2px 4px 2px 4px">$2</td>', // Section Row Content.
            );
        } else {
            $replacements = array(

                    '<tr style="color: black; font-style: normal; font-weight: bold;">$1</tr>', // Header.
                    '<tr style="color: black; font-style: normal; font-weight: bold;">$1</tr>', // Group.

                    '', // Hidden.
                    '', // Invalid.

                    '<tr style="color: black; font-style: normal;">$1</tr>', // Visible, completed.
                    '<tr style="color: grey; font-style: normal;">$1</tr>', // Visible, not completed.
                    '<tr style="color: black; font-style: normal;">$1</tr>', // Visible, in general.

                    '<td style="text-align: center; width: $1%; font-weight: bold;">$2</td>', // Header Content.
                    '<td style="word-wrap: break-word; text-align: center; font-weight: bold;" colspan="3" >$1</td>', // Group Content.
                    '<td style="word-wrap: break-word; text-align: $1; vertical-align: top; padding: 2px 4px 2px 4px">$2</td>', // Section Row Content.
            );
        }

        $output = preg_replace($patterns, $replacements, $text);

        return '<table border="1" style="width: 100%; padding: 2px 4px 2px 4px">'
                .$output.
                '</table>';
    }
}

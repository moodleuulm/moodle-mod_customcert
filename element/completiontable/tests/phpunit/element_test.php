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
 * Test completiontable element.
 *
 * Parts taken from core_completion_progress_testcase in Moodle code by Mark Nelson
 * and from mod_assign_locallib_testcase in Moodle code by Martin Dougiamas.
 *
 * @package    customcertelement_completiontable
 * @copyright  2018 Nicolas Roeser <nicolas.roeser@uni-ulm.de>, kiz Medien, Ulm University
 * @copyright  2017 Mark Nelson <markn@moodle.com>
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/customcert/element/completiontable/tests/phpunit/fixtures/hacked_completiontable_for_testing_element.php');
require_once($CFG->dirroot . '/mod/assign/tests/generator.php');

// Prepare the strings for comparisons.
define('MOD_CUSTOMCERT_TESTS_MATCH_DONE',     '><');
define('MOD_CUSTOMCERT_TESTS_MATCH_NOT_DONE', '>' . '&mdash;' . '<');

class customcertelement_completiontable_element_test extends advanced_testcase {

    // Use helper functions in generator.
    use mod_assign_test_generator;

    /**
     * Test setup.
     */
    public function setUp() {
        global $CFG;

        $CFG->enablecompletion = true;
        $this->resetAfterTest();
    }

    /**
     * Helper function for creating a completiontable element which tracks completion of a specific course module (activity).
     *
     * @param assign $activity The assignment activity which to track.
     *
     * @return \mod_customcert\element Created completiontable element.
     */
    protected function create_completiontable_element_for_cm(assign $activity) {
        $elementdata = new \stdClass();
        $elementdata->element = 'hacked_completiontable_for_testing';

        // Prevent error message, because component cannot be found, by explicitly setting 'name' property beforehand.
        $elementdata->name = get_string('pluginname', 'customcertelement_completiontable');

        $elementdata->data = '{
               "content" : "{completion:' . $activity->get_course_module()->id . '}",
               "fallbackstring" : "",
               "numranges": 0,
               "dateranges": []
            }'; // JSON format.
        $element = \mod_customcert\element_factory::get_element_instance($elementdata);

        return $element;
    }

    /**
     * Update an assignment and set the minimum grading to pass.
     *
     * Function body copied from mod_assign_locallib_testcase::test_attempt_reopen_method_untilpass().
     *
     * @param assign $assign The assignment to modify.
     * @param string $gradepass The grade which is needed to pass. NB: this is a string, for example {@code '10.0'}.
     *
     * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
     */
    protected function set_assignment_gradepass(assign $assign, string $gradepass) {
        $gradeitem = $assign->get_grade_item();
        $gradeitem->gradepass = $gradepass;
        $gradeitem->update();
    }

    /**
     * Test that all possible completion states are handled correctly.
     * There are 4 possibilities for storing a completion state of an activity in the database. The 5th possibility is not storing a
     * completion state at all (which means that the activity has not been completed).
     *
     * Parts have been taken from core_completion_progress_testcase::test_course_progress_percentage_with_just_activities in Moodle
     * code written by Mark Nelson and from functions in mod_assign_locallib_testcase in Moodle code written by Martin Dougiamas,
     * and have been heavily modified.
     */
    public function test_completionstates() {
        // Add a course that supports completion.
        $course = $this->getDataGenerator()->create_course(array('enablecompletion' => 1));

        // Enrol a teacher and a student in the course.
        $teacher = $this->getDataGenerator()->create_and_enrol($course, 'teacher');
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');

        // Add five assignment activities that use completion.
        $assign0 = $this->create_instance($course, [],
            array('completion' => COMPLETION_TRACKING_AUTOMATIC, 'completionusegrade' => 1));
        $assign1 = $this->create_instance($course, [],
            array('completion' => COMPLETION_TRACKING_AUTOMATIC, 'completionview' => COMPLETION_VIEW_REQUIRED));
        $assign2 = $this->create_instance($course, [],
            array('completion' => COMPLETION_TRACKING_AUTOMATIC, 'completionusegrade' => 1));
        $assign3 = $this->create_instance($course, [],
            array('completion' => COMPLETION_TRACKING_AUTOMATIC, 'completionusegrade' => 1));
        $assignx = $this->create_instance($course, [],
            array('completion' => COMPLETION_TRACKING_AUTOMATIC, 'completionusegrade' => 1));
        $this->set_assignment_gradepass($assign0, '50.0');
        // Assignment 1 does not use grading.
        $this->set_assignment_gradepass($assign2, '50.0');
        $this->set_assignment_gradepass($assign3, '50.0');
        $this->set_assignment_gradepass($assignx, '50.0');

        // Create five completiontable elements, each one linked with one assignment activity.
        $element0 = $this->create_completiontable_element_for_cm($assign0);
        $element1 = $this->create_completiontable_element_for_cm($assign1);
        $element2 = $this->create_completiontable_element_for_cm($assign2);
        $element3 = $this->create_completiontable_element_for_cm($assign3);
        $elementx = $this->create_completiontable_element_for_cm($assignx);

        // Check that elements are not marked as completed; element::render_table is not public, so we test with render_html.
        $this->setUser($student);
        $this->assertContains(MOD_CUSTOMCERT_TESTS_MATCH_NOT_DONE, $element0->render_html());
        $this->assertContains(MOD_CUSTOMCERT_TESTS_MATCH_NOT_DONE, $element1->render_html());
        $this->assertContains(MOD_CUSTOMCERT_TESTS_MATCH_NOT_DONE, $element2->render_html());
        $this->assertContains(MOD_CUSTOMCERT_TESTS_MATCH_NOT_DONE, $element3->render_html());
        $this->assertContains(MOD_CUSTOMCERT_TESTS_MATCH_NOT_DONE, $elementx->render_html());

        // Simulate student submission for assignments.
        $this->add_submission($student, $assign0); $this->submit_for_grading($student, $assign0);
        // Assignment 1 only has to be viewed in order to complete it.
        $this->add_submission($student, $assign2); $this->submit_for_grading($student, $assign2);
        $this->add_submission($student, $assign3); $this->submit_for_grading($student, $assign3);
        $this->add_submission($student, $assignx); $this->submit_for_grading($student, $assignx);

        /* Mark three assignments and view one in order to complete it, leave the fifth unchanged.
        This will result in a different completion state for each one. */

        // Simulate marking assignment by teacher and reverting the marking later.
        $this->mark_submission($teacher, $assign0, $student, 80.0);
        $this->assertContains(MOD_CUSTOMCERT_TESTS_MATCH_DONE, $element0->render_html()); // Complete, pass.
        $this->mark_submission($teacher, $assign0, $student, ''); // Remove mark.

        // Simulate marking assignment as complete by student.
        $completion = new completion_info($course);
        $completion->set_module_viewed($assign1->get_course_module(), $student->id);

        // Simulate marking assignments by teacher.
        $this->mark_submission($teacher, $assign2, $student, 100.0);
        $this->mark_submission($teacher, $assign3, $student, 20.0);

        // Do nothing for $assignx.

        /* The preceding code should have created the following result in the database:
        assignment 0: COMPLETION_INCOMPLETE,
        assignment 1: COMPLETION_COMPLETE,
        assignment 2: COMPLETION_COMPLETE_PASS,
        assignment 3: COMPLETION_COMPLETE_FAIL,
        assignment X: (no row in database). */

        // Check that the completion states are correctly taken into account so that e.g. completion dates for the elements appear.
        $this->assertContains(MOD_CUSTOMCERT_TESTS_MATCH_NOT_DONE, $element0->render_html()); // Incomplete (stored explicitly).
        $this->assertContains(MOD_CUSTOMCERT_TESTS_MATCH_DONE, $element1->render_html());     // Complete.
        $this->assertContains(MOD_CUSTOMCERT_TESTS_MATCH_DONE, $element2->render_html());     // Complete, pass.
        $this->assertContains(MOD_CUSTOMCERT_TESTS_MATCH_DONE, $element3->render_html());     // Complete, fail.
        $this->assertContains(MOD_CUSTOMCERT_TESTS_MATCH_NOT_DONE, $elementx->render_html()); // Incomplete (no data).
    }

}

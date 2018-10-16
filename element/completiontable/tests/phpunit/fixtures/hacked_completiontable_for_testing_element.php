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
 * Fake completiontable element for testing.
 *
 * @package    customcertelement_fake_completiontable
 * @copyright  2018 Nicolas Roeser <nicolas.roeser@uni-ulm.de>, kiz Medien, Ulm University
 * @copyright  2018 Dmitrii Metelkin <dmitriim@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// We put the class in this renamed namespace as a hack: it allows us to use mod_customcert\element_factory without changes.
namespace customcertelement_hacked_completiontable_for_testing;

defined('MOODLE_INTERNAL') || die();

class element extends \customcertelement_completiontable\element {

    /**
     * Render completion table, but always in non-preview mode. Method overridden for testing.
     *
     * @param $text : content of the table
     * @param $user : current user
     * @param $preview : preview mode: ignored. Always uses non-preview mode.
     *
     * @return string
     * @throws \coding_exception
     */
    public function render_table($text, $user, $preview) {
        return parent::render_table($text, $user, false);
    }

}

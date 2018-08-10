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
 * Admin settings.
 *
 * @package     customcertelement_completiontable
 * @copyright   2018 Nathan Nguyen  <nathannguyen@@catalyst-au.net>
 * @copyright   2018 Dmitrii Metelkin <dmitriim@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

$options = [];

foreach (range(1, 365) as $number) {
    $options[$number] = $number;
}

$settings->add(new admin_setting_configselect(
    'customcertelement_completiontable/maxranges',
    get_string('maxranges', 'customcertelement_completiontable'),
    get_string('maxranges_desc', 'customcertelement_completiontable'),
    1,
    $options
));


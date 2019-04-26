<?php
// This file is part of Moodle - http://moodle.org/
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
 * Version information for the block_forum_report plugin.
 *
 * @package    block_forum_report
 * @copyright  2017 David Campbell, Eric Hagley & Takahiro Nakahara
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2019042600;        // The current plugin version (Date: YYYYMMDDXX)
$plugin->requires  = 2016052300;        // Requires this Moodle version
$plugin->component = 'block_forum_report'; // Full name of the plugin (used for diagnostics)

$plugin->dependencies = array('mod_forum' => 2016052300);

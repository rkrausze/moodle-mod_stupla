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
 * This file keeps track of upgrades to the stupla module.
 *
 * @package    mod_stupla
 * @subpackage stupla
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(

    // Ability to add an stupla.
    'mod/stupla:addinstance' => array(
        'riskbitmask' => RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/course:manageactivities'
    ),

    // Ability to attempt the stupla.
    'mod/stupla:attempt' => array(
        'captype'      => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes'   => array(
            'student' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'coursecreator' => CAP_ALLOW,
            'manager'  => CAP_ALLOW
        )
    ),

    // Ability to delete anyone's stupla-sessions.
    'mod/stupla:deleteallsessions' => array(
        'riskbitmask'  => RISK_DATALOSS,
        'captype'      => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes'   => array(
            'teacher'  => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager'  => CAP_ALLOW
        )
    ),

    // Ability to delete one's own stupla-sessions.
    'mod/stupla:deletemysessions' => array(
        'riskbitmask'  => RISK_DATALOSS,
        'captype'      => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes'   => array(
            'teacher'  => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager'  => CAP_ALLOW
        )
    ),

    // Ability to preview a atupla as a teacher (and submit results)
    // access restrictions, such as open/close time, will be ignored.
    'mod/stupla:preview' => array(
        'captype'      => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes'   => array(
            'teacher'  => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager'  => CAP_ALLOW
        )
    ),

    // Ability to view anyone's stupla-protocols.
    'mod/stupla:reviewallprotocols' => array(
        'riskbitmask'  => RISK_PERSONAL,
        'captype'      => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes'   => array(
            'teacher'  => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager'  => CAP_ALLOW
        )
    ),

    // Ability to view one's own stupla-protocols.
    'mod/stupla:reviewmyprotocols' => array(
        'captype'      => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes'   => array(
            'user'  => CAP_ALLOW,
            'student'  => CAP_ALLOW,
            'teacher'  => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager'  => CAP_ALLOW
        )
    )

);

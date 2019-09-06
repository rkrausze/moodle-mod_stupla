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
 * The mod_stupla answer submitted event.
 *
 * @package    mod_stupla
 * @copyright  2016 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_stupla\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The mod_stupla answer submitted event class.
 *
 * @property-read array $other {
 *      Extra information about event.
 *
 *      - int stuplaid: id of stupla.
 *      - int optionid: (optional) id of option.
 * }
 *
 * @package    mod_stupla
 * @since      Moodle 2.6
 * @copyright  2016 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class attempt_started extends \core\event\base {

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' made the stupla with id '$this->objectid' in the stupla activity
            with the course module id '$this->contextinstanceid'.";
    }

    /**
     * Return legacy data for add_to_log().
     *
     * @return array
     */
    protected function get_legacy_logdata() {
        $legacylogdata = array($this->courseid,
            'stupla',
            'attempt started',
            'prot/prot.php?id=' . $this->contextinstanceid.'&user='.$this->userid.'&stupla='.$this->objectid.
                        (isset($this->other) && isset($this->other['sessionid']) ? '&sessionid='.$this->other['sessionid'] : ''),
            $this->objectid,
            $this->contextinstanceid);

        return $legacylogdata;
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('attempt_started', 'mod_stupla');
    }

    /**
     * Get URL related to the action
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/mod/stupla/prot/prot.php', array('id' => $this->contextinstanceid, 'user' => $this->userid,
                        'stupla' => $this->objectid));
    }

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'stupla';
    }
}

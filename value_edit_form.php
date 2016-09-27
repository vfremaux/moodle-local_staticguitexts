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

defined('MOODLE_INTERNAL') || die();

require_once $CFG->libdir.'/formslib.php';

class ValueEditForm extends moodleform {

    private $key;

    var $editoroptions;

    function __construct($key) {
        global $CFG, $COURSE;

        $this->key = $key;

        $coursecontext = context_course::instance(SITEID);
        $maxfiles = 99;                // TODO: add some setting
        $maxbytes = $CFG->maxbytes; // TODO: add some setting
        $this->editoroptions = array('trusttext' => true, 'subdirs' => false, 'maxfiles' => $maxfiles, 'maxbytes' => $maxbytes, 'context' => $coursecontext);
        parent::moodleform();
    }

    function definition() {

        $mform = & $this->_form;

        $mform->addElement('hidden', 'key');
        $mform->setType('key', PARAM_TEXT);
        $mform->addElement('hidden', 'from');
        $mform->setType('from', PARAM_URL);

        $keystr = $this->key;
        if (preg_match('/^\[.*\]$/', $keystr)) {
            $keystr = get_string($this->key, 'local_staticguitexts');
        }

        $mform->addElement('editor', 'value', $keystr, array('cols' => 60), $this->editoroptions);

        $mform->addElement('submit','go', get_string('update'));
    }

    function set_data($defaults) {
        global $COURSE;

        $context = context_course::instance($COURSE->id);

        $defaults->valueformat = FORMAT_HTML;

        $drafteditorid = file_get_submitted_draft_itemid('value_editor');
        $currenttext = file_prepare_draft_area($drafteditorid, $context->id, 'local_staticguitexts', $this->key, 0, $this->editoroptions, $defaults->value);
        $defaults = file_prepare_standard_editor($defaults, 'value', $this->editoroptions, $context, 'local_staticguitexts', $this->key, 0);
        $defaults->value = array('text' => $currenttext, 'format' => FORMAT_HTML, 'itemid' => $drafteditorid);

        parent::set_data($defaults);
    }
}

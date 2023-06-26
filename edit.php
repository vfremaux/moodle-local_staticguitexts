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
 * @package    local_staticguitexts
 * @category   local
 * @author     Valery Fremaux <valery@valeisti.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 *
 * This file allows edition of embedded text using a "just in place" editing caller
 * link.
 *
 */
require('../../config.php');
require_once($CFG->dirroot.'/local/staticguitexts/value_edit_form.php');

$context = context_system::instance();
$coursecontext = context_course::instance(SITEID);
$PAGE->set_context($context);

// Security.

require_login();
require_capability('local/staticguitexts:edit', $coursecontext);

$fromurl = urldecode(required_param('from', PARAM_TEXT));
$key = required_param('key', PARAM_TEXT);

$streditguitexts = get_string('adminstrings', 'local_staticguitexts');

$PAGE->set_url('/local/staticguitexts/edit.php');
$PAGE->set_title("$streditguitexts");
$PAGE->set_heading("$streditguitexts");

$mform = new ValueEditForm($key, array('fromurl' => $fromurl));
$formdata = new StdClass;
$formdata->value = @$CFG->$key;
$formdata->valueformat = FORMAT_HTML;
$formdata->from = $fromurl;
$formdata->key = $key;

if ($data = $mform->get_data()) {
    $editor = file_get_submitted_draft_itemid('value');
    $data->value = file_save_draft_area_files($editor, $coursecontext->id, 'local_staticguitexts',
                                              $key, 0, $mform->editoroptions, $data->value['text']);
    $oldvalue = get_config('local_staticguitexts', $key);
    set_config($key, $data->value);
    add_to_config_log($key, $oldvalue, $data->value, 'moodle');
    redirect($fromurl);
} else {
    echo $OUTPUT->header();
    $mform->set_data($formdata);
    $mform->display();
}

echo $OUTPUT->footer();


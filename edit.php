<?php

/**
 * Moodle - Modular Object-Oriented Dynamic Learning Environment
 *          http://moodle.org
 * Copyright (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    moodle
 * @subpackage local
 * @author     Valery Fremaux <valery@valeisti.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 *
 * This file allows edition of embedded text using a "just in place" editing caller
 * link.
 *
 */

	require '../../config.php';
	require_once $CFG->dirroot.'/local/staticguitexts/value_edit_form.php';

	$context = context_system::instance();
	$coursecontext = context_course::instance(SITEID);
		
	$PAGE->set_context($context);
	require_login();
	require_capability('local/staticguitexts:edit', $coursecontext);
	
	$fromurl = required_param('from', PARAM_URL);
	$key = required_param('key', PARAM_TEXT);
	
	$streditguitexts = get_string('adminstrings', 'local_staticguitexts');
	
	$PAGE->set_url('/local/staticguitexts/edit.php');
    $PAGE->set_title("$streditguitexts");
    $PAGE->set_heading("$streditguitexts");
	
	$mform = new ValueEditForm($key);
	$data = new StdClass;
	$data->value = @$CFG->$key;
	$data->valueformat = FORMAT_HTML;
	$data->from = $fromurl;
	$data->key = $key;
	$mform->set_data($data);
	
	if ($data = $mform->get_data()){	
		
		$editor = file_get_submitted_draft_itemid('value');
		$data->value = file_save_draft_area_files($editor, $coursecontext->id, 'local_staticguitexts', $key, 0, $mform->editoroptions, $data->value['text']);

		set_config($key, $data->value);
				
		redirect($fromurl);
	} else {
		echo $OUTPUT->header();
		$mform->display();
	}
	
	echo $OUTPUT->footer();


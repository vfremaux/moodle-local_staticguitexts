<?php

require_once $CFG->libdir.'/formslib.php';

class ValueEditForm extends moodleform{
	
	private $key;
	
	var $editoroptions;
	
	function __construct($key){
		global $CFG, $COURSE;
		
		$this->key = $key;

		$coursecontext = context_course::instance(SITEID);
		$maxfiles = 99;                // TODO: add some setting
		$maxbytes = 0; // TODO: add some setting
		$this->editoroptions = array('trusttext' => true, 'subdirs' => true, 'maxfiles' => $maxfiles, 'maxbytes' => $maxbytes, 'context' => $coursecontext);

		parent::moodleform();
	}
	
	function definition(){
		
		$mform = & $this->_form;

		$mform->addElement('hidden', 'key');
		$mform->setType('key', PARAM_TEXT);
		$mform->addElement('hidden', 'from');
		$mform->setType('from', PARAM_URL);
		
		$keystr = $this->key;
		if (preg_match('/^\[.*\]$/', $keystr)){
			$keystr = get_string($this->key, 'local_staticguitexts');
		}

		$mform->addElement('editor', 'value', $keystr, array('cols' => 60), $this->editoroptions);
		
		$mform->addElement('submit','go', get_string('update'));
	}
	
	function set_data($defaults){
		$coursecontext = context_course::instance(SITEID);

		$drafteditorid = file_get_submitted_draft_itemid('value_editor');
		$currenttext = file_prepare_draft_area($drafteditorid, $coursecontext->id, 'local_staticguitexts', $this->key, 0, $this->editoroptions, $defaults->value);
		$defaults = file_prepare_standard_editor($defaults, 'value', $this->editoroptions, $coursecontext, 'local_staticguitexts', $this->key, 0);
		$defaults->value = array('text' => $currenttext, 'format' => FORMAT_HTML, 'itemid' => $drafteditorid);
		
		parent::set_data($defaults);
	}
	
}

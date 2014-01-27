<?php

/**
* A function that prints a static text edited by administrator
* Where this function is called in a page, it creates a placeholder
* bound to text administration utility
*
* @param string $key the unique key for this admin static text
* @param string $returnurl the url where to return to where the text is exposed
*/
if (!function_exists('local_print_static_text')){

function local_print_static_text($key, $returnurl, $extracapability = false, $return = false){
	global $CFG, $COURSE, $USER, $OUTPUT;
	
	$out = '';
	
	$context = context_course::instance($COURSE->id);

	$txt = file_rewrite_pluginfile_urls(@$CFG->$key, 'pluginfile.php', $context->id, 'local_staticguitexts', $key, 0);
	$txt = str_replace('[[WWWROOT]]', preg_replace('/https?:\/\//', '', $CFG->wwwroot), $txt);
	$txt = str_replace('[[COURSEID]]', $COURSE->id, $txt);	
	$txt = str_replace('[[USERID]]', $USER->id, $txt);	
	$out .= $OUTPUT->box_start('statictext');
	$opt = new StdClass;
	$opt->para = false;
	$opt->trusted = true;
	$opt->noclean = true;
	$out .= format_text($txt, FORMAT_MOODLE, $opt);
	
	$syscontext = context_system::instance();

	$extracap = false;
	if ($extracapability){
		$extracap = has_capability($extracapability, $syscontext);
	}

    if (has_capability('local/staticguitexts:edit', context_course::instance(SITEID)) || has_capability('local/staticguitexts:edit', $syscontext) || $extracap){
    	$url = urlencode($returnurl);
		$out .= "<br/><a href=\"{$CFG->wwwroot}/local/staticguitexts/edit.php?key={$key}&amp;from=$url&amp;extra={$extracapability}\"><img class=\"iconsmall\" src=\"".$OUTPUT->pix_url('t/edit', 'core')."\" /></a>";
    }
	$out .= $OUTPUT->box_end();
	
	if ($return) return $out;
	echo $out;
}

function local_staticguitexts_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload) {
    global $CFG, $DB;
	
    if ($context->contextlevel != CONTEXT_COURSE) {
        return false;
    }
    
    // any file area but within the local_staticguitexts component scope...
	
    $fs = get_file_storage();
    array_shift($args); // remove useless itemid
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/local_staticguitexts/$filearea/0/$relativepath";
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }

    // finally send the file
    send_stored_file($file, 0, 0, false); // download MUST be forced - security!
}

}
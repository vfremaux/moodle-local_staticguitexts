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
 * @package     local_staticguitexts
 * àcategory    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This is a bit more complex security resolution for script that may be called before setup.php.
if (!defined('MOODLE_EARLY_INTERNAL')) {
    defined('MOODLE_INTERNAL') || die();
}

/**
 * This function is not implemented in thos plugin, but is needed to mark
 * the vf documentation custom volume availability.
 */
function local_staticguitexts_supports_feature() {
    assert(1);
}

/**
 * A function that prints a static text edited by administrator
 * Where this function is called in a page, it creates a placeholder
 * bound to text administration utility
 *
 * @param string $key the unique key for this admin static text
 * @param string $returnurl the url where to return to where the text is exposed
 */

function local_print_static_text($key, $returnurl, $extracapability = false, $return = false) {
    global $CFG, $COURSE, $USER, $OUTPUT, $SITE;

    $out = '';

    $context = context_course::instance($COURSE->id);

    require_once($CFG->dirroot.'/lib/filelib.php');
    $txt = file_rewrite_pluginfile_urls(@$CFG->$key, 'pluginfile.php', $context->id, 'local_staticguitexts', $key, 0);
    $txt = str_replace('[[WWWROOT]]', preg_replace('/https?:\/\//', '', $CFG->wwwroot), $txt);
    $txt = str_replace('[[COURSEID]]', $COURSE->id, $txt);
    $txt = str_replace('[[COURSENAME]]', format_string($COURSE->fullname), $txt);
    $txt = str_replace('[[COURSESHORT]]', $COURSE->shortname, $txt);
    $txt = str_replace('[[USERID]]', $USER->id, $txt);
    $txt = str_replace('[[SITENAME]]', format_string($SITE->fullname), $txt);
    $txt = str_replace('[[SITESHORT]]', $SITE->shortname, $txt);
    $txt = str_replace('[[USERNAME]]', $USER->username ?? '', $txt);
    $txt = str_replace('[[FIRSTNAME]]', $USER->firstname ?? '', $txt);
    $txt = str_replace('[[LASTNAME]]', $USER->lastname ?? '', $txt);
    $hasclass = '';
    if (!empty($txt)) {
        $hasclass = 'has-text';
    }
    $out .= $OUTPUT->box_start('statictext '.$hasclass);
    $opt = new StdClass;
    $opt->para = false;
    $opt->trusted = true;
    $opt->noclean = true;
    $out .= format_text($txt, FORMAT_MOODLE, $opt);

    $syscontext = context_system::instance();

    $extracap = false;

    if ($extracapability) {
        $extracap = has_capability($extracapability, $syscontext);
    }

    if (has_capability('local/staticguitexts:edit', context_course::instance(SITEID)) ||
            has_capability('local/staticguitexts:edit', $syscontext) || $extracap) {
        $url = urlencode($returnurl);
        $params = array('key' => $key, 'from' => $url, 'extra' => $extracapability);
        $targeturl = new moodle_url('/local/staticguitexts/edit.php', $params);
        $out .= '<br/><a href="'.$targeturl.'">'.$OUTPUT->pix_icon('edit', get_string('update'), 'local_staticguitexts').'</a>';
    }
    $out .= $OUTPUT->box_end();

    if ($return) {
        return $out;
    }
    echo $out;
}

function local_staticguitexts_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload) {

    if ($context->contextlevel != CONTEXT_COURSE) {
        return false;
    }

    // Any file area but within the local_staticguitexts component scope...

    $fs = get_file_storage();
    array_shift($args); // Remove useless itemid.
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/local_staticguitexts/$filearea/0/$relativepath";
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }

    // Finally send the file.
    send_stored_file($file, 0, 0, false); // Download MUST be forced - security!
}

/**
 * Get icon mapping for font-awesome.
 */
function local_staticguitexts_get_fontawesome_icon_map() {
    return [
        'local_staticguitexts:edit' => 'fa-pen-to-square',
    ];
}

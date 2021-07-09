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

require_once('reportlib.php');
require_once($CFG->libdir.'/navigationlib.php');

defined('MOODLE_INTERNAL') || die();

class block_forum_report extends block_base {
    function init() {
        $this->title = get_string('pluginname', 'block_forum_report');
        
    }

    function applicable_formats() {
        return array('course'=>true);
    }

    function instance_config_save($data, $nolongerused = false) {
        parent::instance_config_save($data);
    }

    function get_content() {
        global $CFG, $DB, $OUTPUT, $USER,$COURSE;
        
        if($this->page->theme->name == "boost"){
            $params['course'] = $COURSE->id;
            $params['startnow'] = 1;
            $url = new moodle_url('/blocks/forum_report/report.php',$params);
            $addflat = navigation_node::create(get_string('pluginname', 'block_forum_report'), $url);
            $flat = new flat_navigation_node($addflat, 0);
            $flat->set_showdivider(true, get_string('pluginname', 'block_forum_report'));
            $flat->key = 'forumreport';
            $flat->type = 0;
            if($this->page->flatnav->find($flat->key)) return;
            if($this->page->flatnav->add($flat)){
                return NULL;
            }
        }
        
        if($this->content !== NULL) {
            return $this->content;
        }
        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        $course = $this->page->course;
        $cm = $this->page->cm;
        $context = $this->page->context;
        if(!has_capability('block/forum_report:view', $context)){
            return;
        }
        
        //has_capabirity viewreport
        
        if(!$cm){
            $archetypes = array();
            $forums = $DB->get_records('forum',array('course'=>$course->id));
            $tableurl = new moodle_url('/blocks/forum_report/report.php', array('sesskey' => sesskey()));
            $this->content->text = '<form action="'.$tableurl.'" method="POST">';
            /*
            $this->content->text .= '<select class="custom-select" name="forum" id="forum" onchange="submit(this.form)">';
            $this->content->text .= '<option disabled selected>'.get_string('selectforum','block_forum_report').'</option>';
            $this->content->text .= '<option value="0">'.get_string('alloncourse','block_forum_report').'</option>';
                foreach($forums as $forum){
                    $this->content->text .= '<option value="'.$forum->id.'">'.$forum->name.'</option>';
                }
                $this->content->text .= '</optiongroup>';
            
            $this->content->text .= '</select>';
            */
            $this->content->text .= '<input type="submit" value="'.get_string('showreport','block_forum_report').'">';
            $this->content->text .= '<input type="hidden" name="course" value="'.$course->id.'">';
            $this->content->text .= '<input type="hidden" name="group" value="0">';
            $this->content->text .= '<input type="hidden" name="country" value="0">';
            $this->content->text .= '<input type="hidden" name="startnow" value="1">';
            $this->content->text .= '</form>';
            return $this->content;
        }else{
            return;
            $modinfo = get_fast_modinfo($course);
            $modfullnames = array();

            $archetypes = array();
            $forums = $DB->get_records('forum',array('course'=>$course->id));
            $tableurl = new moodle_url('/blocks/forum_report/report.php', array('sesskey' => sesskey()));
            $this->content->text = '<form action="'.$tableurl.'" method="POST">';
            $this->content->text .= '<input type="submit" value="'.get_string('showreport','block_forum_report').'">';
            $this->content->text .= '<input type="hidden" name="course" value="'.$course->id.'">';
            $this->content->text .= '<input type="hidden" name="group" value="0">';
            $this->content->text .= '<input type="hidden" name="country" value="0">';
            $this->content->text .= '<input type="hidden" name="startnow" value="1">';
            $this->content->text .= '</form>';
            return $this->content;
        }
    }

    function instance_allow_multiple() {
        return false;
    }
}



<?php
require_once("$CFG->libdir/formslib.php");

class report_form extends moodleform {
    //Add elements to form
    public function definition() {
        global $CFG,$DB,$COURSE;
        $mform = $this->_form;

        $mform->addElement('header','filter', get_string('reportfilter','block_forum_report'));
        $forumdata = $DB->get_records('forum',array('course'=>$COURSE->id));
        foreach($forumdata as $forum){
            $forums[$forum->id] = $forum->name;
        }
        $forums = array('0' => get_string('all')) + $forums;
        $mform->addElement('select', 'forum', get_string('forum','forum'), $forums);
        
        $allgroups = groups_get_all_groups($COURSE->id);
        if(count($allgroups)){
            $groupoptions = array('0'=>get_string('allgroups'));
            foreach($allgroups as $group){
                $groupoptions[$group->id] = $group->name;
            }
            $mform->addElement('select', 'group', get_string('group'), $groupoptions);
        }
        
        $countries = get_string_manager()->get_list_of_countries();

        $countrychoices = get_string_manager()->get_list_of_countries();
        $countrychoices = array('0' => get_string('all')) + $countrychoices;
        $mform->addElement('select', 'country', get_string('country'), $countrychoices);
        
        $mform->addElement('hidden', 'course', $COURSE->id);
        $mform->setType('course',PARAM_INT);
        
        // Open and close dates.
        $mform->addElement('date_time_selector', 'starttime', get_string('reportstart', 'block_forum_report'),array('optional'=>true,'startyear' => 2000, 'stopyear' => date("Y"),'step' => 5));

        $mform->addElement('date_time_selector', 'endtime', get_string('reportend', 'block_forum_report'),array('optional'=>true,'startyear' => 2000, 'stopyear' => date("Y"),'step' => 5));
        
        $mform->addElement('submit', 'changefilter', get_string('showreport','block_forum_report'));
    }
}

function forum_report_sort($sortby){
    return function($a,$b) use ($sortby){
        foreach($sortby as $key=>$order){
            if(strpos($key,"name")!==FALSE){
                if($order == 4){
                    $cmp = strcmp($a->$key,$b->$key);
                }else{
                    $cmp = strcmp($b->$key,$a->$key);
                }
            }else{
                
                if($order == 4){
                    return ($a->$key < $b->$key) ? -1 : 1;
                }else{
                    return ($a->$key > $b->$key) ? -1 : 1;
                }
            }
            break;
        }
        return $cmp;
    };
}

function get_mulutimedia_num($text) {
    global $CFG, $PAGE;

    if (!is_string($text) or empty($text)) {
        // non string data can not be filtered anyway
        return 0;
    }

    if (stripos($text, '</a>') === false && stripos($text, '</video>') === false && stripos($text, '</audio>') === false && (stripos($text, '<img') === false)) {
        // Performance shortcut - if there are no </a>, </video> or </audio> tags, nothing can match.
        return 0;
    }

    // Looking for tags.
    $matches = preg_split('/(<[^>]*>)/i', $text, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
    $count = 0;
    if (!$matches) {
        return 0;
    }else{
              // Regex to find media extensions in an <a> tag.
      $embedmarkers = core_media_manager::instance()->get_embeddable_markers();
      $re = '~<a\s[^>]*href="([^"]*(?:' .  $embedmarkers . ')[^"]*)"[^>]*>([^>]*)</a>~is';

      $tagname = '';
        foreach ($matches as $idx => $tag) {
          if (preg_match('/<(a|img|video|audio)\s[^>]*/', $tag, $tagmatches)){
            $tagname = strtolower($tagmatches[1]);
            if($tagname === "a" && preg_match($re,$tag)){
              $count++;
            }else{
              $count++;
            }
          }
        }
    }
  return $count;
}
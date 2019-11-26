<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/tablelib.php');
require_once('reportlib.php');

$startnow = optional_param('startnow',0, PARAM_INT);
$forumid = optional_param('forum',0, PARAM_INT);
$courseid = required_param('course', PARAM_INT);
$groupid = optional_param('group', 0, PARAM_INT);
$countryid = optional_param('country', '', PARAM_RAW);
$start = optional_param('start', '', PARAM_RAW);
$end = optional_param('end', '', PARAM_RAW);
$tsort = optional_param('tsort', 0, PARAM_RAW);
if(strpos($tsort,'name')!==FALSE){
    $orderbyname = $tsort;
}else{
    $orderbyname = '';
}
$params['course'] = $courseid;
$course = $DB->get_record('course',array('id'=>$courseid));

require_course_login($course);
$coursecontext = context_course::instance($course->id);

require_capability('block/forum_report:view', $coursecontext, NULL, true, 'noviewdiscussionspermission', 'forum');


if($forumid){
    $params['forum'] = $forumid;
    $forum = $DB->get_record('forum',array('id'=>$forumid));
    $cm = get_coursemodule_from_instance('forum', $forum->id, $course->id, false, MUST_EXIST);
    $modcontext = context_module::instance($cm->id);
    $PAGE->set_title("$course->shortname: $forum->name");
    $PAGE->navbar->add($forum->name);
}

$countries = get_string_manager()->get_list_of_countries();

$mform = new report_form();
$fromform = $mform->get_data();
$paramstr = '?course='.$course->id.'&forum='.$forumid;

if($groupid){
    $params['group'] = $groupid;
    $groupfilter = $groupid;;
    $paramstr .= '&group='.$groupfilter;
    $groupname = groups_get_all_groups($course->id)[$groupid]->name;
/*
}elseif(isset($fromform->group)){
    $groupfilter = $fromform->group;
    $paramstr .= '&group='.$groupfilter;
    $params['group'] = $groupfilter;
    echo $groupfilter
    $groupname = groups_get_all_groups($course->id)[$groupfilter]->name;
*/
}else{
    $groupfilter = 0;
    $groupname = "";
}
if($countryid){
    $params['country'] = $countryid;
    $countryfilter = $countryid;
    $paramstr .= '&country='.$countryfilter;
}elseif(isset($fromform->country)){
    $countryfilter = $fromform->country;
    $paramstr .= '&country='.$countryfilter;
    $params['country'] = $countryfilter;
}else{
    $countryfilter = 0;
}
if(isset($fromform->starttime)){
    $starttime = $fromform->starttime;
    $params['start'] = $starttime;
    $paramstr .= '&start='.$starttime;
}elseif($start){
    $starttime = $start;
    $paramstr .= '&start='.$starttime;
    $params['start'] = $starttime;
}else{
    $starttime = 0;
}
if(isset($fromform->endtime)){
    $endtime = $fromform->endtime;
    $params['end'] = $endtime;
    $paramstr .= '&end='.$endtime;
}elseif($end){
    $endtime = $end;
    $paramstr .= '&end='.$endtime;
    $params['end'] = $endtime;
}else{
    $endtime = 0;
}


$PAGE->set_pagelayout('incourse');
/// Output the page
$PAGE->requires->jquery();
$PAGE->requires->js('/blocks/forum_report/scripts.js');
$PAGE->requires->css('/blocks/forum_report/styles.css');
$PAGE->set_url($CFG->wwwroot.'/blocks/forum_report/report.php',$params);
$PAGE->navbar->add('forum_report');
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();
$mform->display();

$strname = get_string('fullname');
$strfirstname = get_string('firstname');
$strlastname = get_string('lastname');
$strcounrty = get_string('country');
$strposts = get_string('posts');
$strviews = get_string('views','block_forum_report');
$strreplies = get_string('replies','block_forum_report');
$strwordcount = get_string('wordcount','block_forum_report');
$strfp = get_string('firstpost','block_forum_report');
$strlp = get_string('lastpost','block_forum_report');
$strsr = get_string('sendreminder','block_forum_report');
$strcl = get_string('completereport');
$strinstituion = get_string('institution');
$strgroup = get_string('group');
if(!$startnow){
    echo '<br>';
    echo '<a href="download.php'.$paramstr.'"><button class="btn btn-primary ">'.get_string('download').'</button></a><br><br>';


    $table = new flexible_table('forum_report_table');
    //$table->head = array($strname,$strcounrty,$strposts,$strreplies,$strwordcount,$strviews,$strfp,$strlp,$strsr,$strcl);
    //$table->define_align = array ("center","center","center","center","center","center","center","center","center","center");
    $table->define_baseurl($PAGE->url);
    $table->define_columns(array('fullname','group', 'country', 'institution', 'posts', 'replies','wordcount', 'views','firstpost','lastpost','action'));
    $table->define_headers(array($strname,$strgroup,$strcounrty,$strinstituion,$strposts,$strreplies,$strwordcount,$strviews,$strfp,$strlp,''));
    $table->sortable(true);
    $table->set_attribute('class', 'admintable generaltable');
    $table->setup();
    $sortby = $table->get_sort_columns();
    if($sortby){
        $orderby = array_keys($sortby)[0];
        $ascdesc = ($sortby[$orderby] == 4) ?'ASC':'DESC';
        if(strpos($orderby,'name') !== FALSE){
            $orderbyname = $orderby.' '.$ascdesc;
        }else{
            $orderbyname = '';
        }
    }else{
        $orderbyname = '';
    }
    
    //get_enrolled_users(context $context, $withcapability = '', $groupid = 0, $userfields = 'u.*', $orderby = '', $limitfrom = 0, $limitnum = 0)に変えること
    //$students = get_enrolled_users($coursecontext);
    //var_dump($students);
    if($forumid){
        $students = get_users_by_capability($modcontext, 'mod/forum:viewdiscussion','',$orderbyname);
        $discussions = $DB->get_records('forum_discussions',array('forum'=>$forum->id));
    }else{
        $students = get_users_by_capability($coursecontext, 'mod/forum:viewdiscussion','',$orderbyname);
        $discussions = $DB->get_records('forum_discussions',array('course'=>$course->id));
    }

    $discussionarray = '(';
    foreach($discussions as $discussion){
        $discussionarray .= $discussion->id.',';
    }
    $discussionarray .= '0)';

    $data = array();

    foreach($students as $student){
        $studentdata = new stdClass();

        if($countryfilter && $countryfilter != $student->country){
            continue;
        }

        //Group
        $studentgroups = groups_get_all_groups($course->id, $student->id);
        $tempgroups = array();
        $studentdata->group ="";
        foreach($studentgroups as $studentgroup){
            $tempgroups[] = $studentgroup->name;
        }
        if($tempgroups) $studentdata->group = implode(',',$tempgroups);
        $ingroups = array_keys($studentgroups);
        if($groupfilter){
            if(!in_array($groupfilter,$ingroups)){
                continue;
            }
        }
        
        $studentdata->id = $student->id;

        //Name
        $studentdata->name = fullname($student);

        //Countryfullname($student);
        $studentdata->country = @$countries[$student->country];
        
        //Instituion
        $studentdata->institution = $student->institution;

        //Posts
        $postsql = 'SELECT * FROM {forum_posts} WHERE userid='.$student->id.' AND discussion IN '.$discussionarray.' AND parent=0';
        if($starttime){
            $postsql = $postsql.' AND created>'.$starttime;
        }
        if($endtime){
            $postsql = $postsql.' AND created<'.$endtime;
        }
        
        $posts = $DB->get_records_sql($postsql);
        $studentdata->posts = count($posts);

        //Replies
        $repsql = 'SELECT * FROM {forum_posts} WHERE userid='.$student->id.' AND discussion IN '.$discussionarray.' AND parent>0';
        if($starttime){
            $repsql = $repsql.' AND created>'.$starttime;
        }
        if($endtime){
            $repsql = $repsql.' AND created<'.$endtime;
        }
        $replies = $DB->get_records_sql($repsql);
        $studentdata->replies = count($replies);

        //View
        $logtable = 'logstore_standard_log';
        $eventname = '\\\\mod_forum\\\\event\\\\discussion_viewed';
        if($forumid){
            $viewsql = "SELECT * FROM {logstore_standard_log} WHERE userid=$student->id AND contextinstanceid=$cm->id AND contextlevel=".CONTEXT_MODULE." AND eventname='$eventname'";
        }else{
            $views = $DB->get_records($logtable,array('userid'=>$student->id,'courseid'=>$courseid,'eventname'=>$eventname));
            $viewsql = "SELECT * FROM {logstore_standard_log} WHERE userid=$student->id AND courseid=$courseid AND eventname='$eventname'";
        }
        if($starttime){
            $viewsql = $viewsql.' AND timecreated>'.$starttime;
        }
        if($endtime){
            $viewsql = $viewsql.' AND timecreated<'.$endtime;
        }
        $views = $DB->get_records_sql($viewsql);
        $studentdata->views = count($views);

        //Word count
        if($posts || $replies){
            $allpostsql = 'SELECT * FROM {forum_posts} WHERE userid='.$student->id.' AND discussion IN '.$discussionarray;
            if($starttime){
                $allpostsql = $allpostsql.' AND created>'.$starttime;
            }
            if($endtime){
                $allpostsql = $allpostsql.' AND created<'.$endtime;
            }
            if($allposts = $DB->get_records_sql($allpostsql)){
                $wordcount = 0;
                foreach($allposts as $post){
                    $wordnum = count_words($post->message);
                    $wordcount += $wordnum;
                }
            }
        }else{
            $wordcount = 0;
        }
        $studentdata->wordcount = $wordcount;

        //First post & Last post
        $firstpostsql = 'SELECT MIN(created) FROM {forum_posts} WHERE userid='.$student->id.' AND discussion IN '.$discussionarray;
        if($posts || $replies){
            
            $firstpostsql = 'SELECT MIN(created) FROM {forum_posts} WHERE userid='.$student->id.' AND discussion IN '.$discussionarray;
            if($starttime){
                $firstpostsql = $firstpostsql.' AND created>'.$starttime;
            }
            if($endtime){
                $firstpostsql = $firstpostsql.' AND created<'.$endtime;
            }
            $firstpost = $DB->get_record_sql($firstpostsql);
            $minstr = 'min(created)'; //
            $firstpostdate = userdate($firstpost->$minstr);
            $studentdata->firstpost = $firstpostdate;
            

            $lastpostsql = 'SELECT MAX(created) FROM {forum_posts} WHERE userid='.$student->id.' AND discussion IN '.$discussionarray;
            if($starttime){
                $lastpostsql = $lastpostsql.' AND created>'.$starttime;
            }
            if($endtime){
                $lastpostsql = $lastpostsql.' AND created<'.$endtime;
            }
            $lastpost = $DB->get_record_sql($lastpostsql);
            $maxstr = 'max(created)'; //
            $lastpostdate = userdate($lastpost->$maxstr);
            $studentdata->lastpost = $lastpostdate;
        }else{
            $studentdata->firstpost = '-';
            $studentdata->lastpost = '-';
        }
        $data[] = $studentdata;
    }
    if($sortby && !$orderbyname){
        usort($data,forum_report_sort($sortby));
    }

    foreach($data as $row){
        //Notification
        //$output = $OUTPUT->pix_icon('t/subscribed', get_string('sendreminder', 'block_forum_report'), 'mod_forum');
        $output = '<span class="forumreporticon-envelop" title="Send reminder"></span>';
        $sendreminder = '<a href="#" onclick="sendreminder('.$row->id.')">'.$output.'</a>';
        //message_sendを別phpで発火させる発火させる
        $compurl = $CFG->wwwroot.'/report/outline/user.php?id='.$row->id.'&course='.$course->id.'&mode=complete';
        $complink = '<a href="'.$compurl.'"><span class="forumreporticon-profile" title="Complete reports"></span></a>';
        //$table->data[] = array($row->name,$row->country,$row->posts,$row->replies,$row->wordcount,$row->views,$row->firstpost,$row->lastpost,$sendreminder,$complink);
        $trdata = array($row->name,$row->group,$row->country,$row->institution,$row->posts,$row->replies,$row->wordcount,$row->views,$row->firstpost,$row->lastpost,$sendreminder.$complink);
        $table->add_data($trdata);
    }
    echo '<input type="hidden" name="course" id="courseid" value="'.$courseid.'">';
    if($forumid){
        echo '<input type="hidden" name="forum" id="forumid" value="'.$forumid.'">';
    }
    $table->finish_output();
    //echo html_writer::table($table);
}
echo $OUTPUT->footer();

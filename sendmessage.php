<?php

require_once(dirname(__FILE__) . '/../../config.php');

$studentid = required_param('student', PARAM_INT);
$courseid = required_param('course', PARAM_INT);
$forumid = optional_param('forum', 0, PARAM_INT);

$student = $DB->get_record('user',array('id'=>$studentid));
$course = $DB->get_record('course',array('id'=>$courseid));

require_login($course);

$message = new \core\message\message();
if($forumid){
    $forum = $DB->get_record('forum',array('id'=>$forumid));
    $message->contexturl = $CFG->wwwroot.'/mod/forum/view.php?f='.$forum->id;
    $message->contexturlname = $forum->name;
    $link = $CFG->wwwroot.'/mod/forum/view.php?f='.$forum->id;
}else{
    $forum = $DB->get_record('forum',array('id'=>$forumid));
    $message->contexturl = $CFG->wwwroot.'/course/view.php?id='.$course->id;
    $message->contexturlname = $course->fullname;
    $link = $CFG->wwwroot.'/course/view.php?id='.$course->id;
}

$message->courseid = $courseid;
$message->component = 'moodle';//'block_forum_report';
$message->name = 'instantmessage'; //'sendreminder';
$message->userfrom = $USER;
$message->userto = $student;
$message->subject = get_string('remindsubject','block_forum_report');
$message->fullmessage = get_string('remindmessage','block_forum_report').$link;
$message->fullmessageformat = FORMAT_HTML;
$message->fullmessagehtml = get_string('remindmessage','block_forum_report').'<br><p>'.$link.'</p>';
$message->smallmessage =  get_string('remindmessage','block_forum_report');
$message->notification = '0';
//$message->replyto = "random@example.com";
//$content = array('*' => array('header' => ' test ', 'footer' => ' test ')); // Extra content for specific processor
//$message->set_additional_content('email', $content);

$messageid = message_send($message);

echo get_string('sentreminder','block_forum_report');
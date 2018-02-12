$(function(){
    sendreminder = function(studentid){
        var forumid = $('#forumid').val();
        var courseid = $('#courseid').val();
        if (confirm("Do you send a reminder?") == true) {
            $.ajax({
                url: "./sendmessage.php",
                type: "POST",
                data: {
                    student:studentid,
                    course:courseid,
                    forum:forumid
                },
                success: function (arr) {
                    alert(arr);
                    //console.log(arr);
                }
            });
        }
    }
    
});
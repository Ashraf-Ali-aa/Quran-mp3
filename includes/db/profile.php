<?php

function profile_user_id ($username , $limit = NULL){
    global $db;

    if (is_null($limit)){
        $buffer = $db->super_query ( "SELECT user_id, avatar FROM vass_users WHERE username = '" . $username . "'" ); // update code
    } else {
        $buffer = $db->super_query ( "SELECT user_id, avatar FROM vass_users WHERE username = '" . $username . "' LIMIT 0,1" );
    }

    return $buffer;
}

function profile_follow ($member_id , $follower_id ){
    global $db;

    $db->query ( "INSERT IGNORE INTO vass_friendship SET user_id = '" . $member_id . "', follower_id = '" . $follower_id . "'" );

    $db->query ( "UPDATE vass_users SET total_following  = total_following+1 WHERE user_id = '" . $member_id . "'" );

    $db->query ( "UPDATE vass_users SET total_followers  = total_followers+1 WHERE user_id = '" . $follower_id . "'" );

}

function profile_unfollow ($member_id , $user_id ){
    global $db;

    $db->query ( "DELETE FROM vass_friendship WHERE user_id = '" . $member_id . "' AND follower_id = '" . $user_id . "'" );

    $db->query ( "UPDATE vass_users SET total_following  = total_following-1 WHERE user_id = '" . $member_id . "'" );

    $db->query ( "UPDATE vass_users SET total_followers  = total_followers-1 WHERE user_id = '" . $user_id . "'" );

} 
<?php

function member_user_id ($username , $limit = NULL){
    global $db;
    
    if (is_null($limit)){
        $buffer = $db->super_query ( "SELECT user_id FROM vass_users WHERE username = '" . $username . "'" );   
    } else {
        $buffer = $db->super_query ( "SELECT user_id FROM vass_users WHERE username = '" . $username . "' LIMIT 0,1" ); 
    }
    
    return $buffer;
} 

function member_sql_result ($row){
    global $db;

    $buffer = $db->query ( "SELECT vass_audios.artist_id, vass_audios.id AS audio_id, vass_audios.title AS audio_title, vass_audios.loved, vass_artists.name AS audio_artist, vass_albums.name AS audio_album, vass_albums.id AS album_id, vass_audio_love.created_on, vass_users.username, vass_users.user_id FROM vass_audio_love LEFT JOIN vass_audios ON vass_audio_love.audio_id = vass_audios.id LEFT JOIN vass_users ON vass_audio_love.user_id = vass_users.user_id LEFT JOIN vass_albums on vass_audios.album_id = vass_albums.id LEFT JOIN vass_artists ON vass_audios.artist_id = vass_artists.id WHERE vass_audio_love.user_id = '" . $row . "' ORDER BY vass_audio_love.id DESC" );

    return $buffer;
} 

function member_tastemakers (){
    global $db;

    $buffer = $db->query ( "SELECT DISTINCT vass_audios.id AS audio_id, vass_audios.title AS audio_title, vass_audios.loved, vass_artists.name AS audio_artist, vass_albums.name AS audio_album, vass_albums.id AS album_id, vass_audio_love.created_on, vass_users.username, vass_users.user_id FROM vass_audio_love LEFT JOIN vass_friendship ON vass_friendship.follower_id = vass_audio_love.user_id LEFT JOIN vass_audios ON vass_audio_love.audio_id = vass_audios.id LEFT JOIN vass_users ON vass_audio_love.user_id = vass_users.user_id LEFT JOIN vass_albums on vass_audios.album_id = vass_albums.id LEFT JOIN vass_artists ON vass_audios.artist_id = vass_artists.id" );

    return $buffer;
} 

function member_tastemakers_user_id ($row){
    global $db;

    $buffer = $db->query ( "SELECT DISTINCT vass_audios.id AS audio_id, vass_audios.title AS audio_title, vass_audios.loved, vass_artists.name AS audio_artist, vass_albums.name AS audio_album, vass_albums.id AS album_id, vass_audio_love.created_on, vass_users.username, vass_users.user_id FROM vass_audio_love LEFT JOIN vass_friendship ON vass_friendship.follower_id = vass_audio_love.user_id LEFT JOIN vass_audios ON vass_audio_love.audio_id = vass_audios.id LEFT JOIN vass_users ON vass_audio_love.user_id = vass_users.user_id LEFT JOIN vass_albums on vass_audios.album_id = vass_albums.id LEFT JOIN vass_artists ON vass_audios.artist_id = vass_artists.id WHERE vass_friendship.user_id = '" . $row . "' ORDER BY vass_audio_love.id DESC" );

    return $buffer;
}


function member_follow ($result){
    global $db;

    $buffer = $db->super_query("SELECT follower_id FROM vass_friendship WHERE follower_id = '" . $result . "'");

    return $buffer;
}

function member_following ($row){
    global $db;

    $buffer = $db->query ( "SELECT vass_friendship.follower_id, vass_users.username, vass_users.name, vass_users.bio, vass_users.website, vass_users.total_loved, vass_users.location, vass_users.total_loved, vass_users.total_following, vass_users.total_followers, vass_users.avatar, vass_background.color, vass_background.image, vass_background.position, vass_background.repeat, vass_background.use_image FROM vass_users LEFT JOIN vass_background ON vass_users.user_id = vass_background.user_id LEFT JOIN vass_friendship ON vass_users.user_id = vass_friendship.follower_id WHERE vass_friendship.user_id = '" . $row . "';" );

    return $buffer;
}

function member_tastemakers_users (){
    global $db;

    $buffer = $db->query ( "SELECT DISTINCT vass_friendship.follower_id, vass_users.username, vass_users.name, vass_users.bio, vass_users.website, vass_users.total_loved, vass_users.location, vass_users.total_loved, vass_users.total_following, vass_users.total_followers, vass_users.avatar FROM vass_users LEFT JOIN vass_friendship ON vass_users.user_id = vass_friendship.follower_id" );

    return $buffer;
}

function member_background_color ($user_id){
    global $db;

    $buffer = $db->super_query ( "SELECT vass_background.color, vass_background.image, vass_background.position, vass_background.repeat, vass_background.use_image FROM vass_background WHERE vass_background.user_id = '" . $user_id . "';" );

    return $buffer;
}

function member_update_background_color ($member_id, $color , $image , $position , $repeat , $use_image){
    global $db;

     $db->query ( "INSERT INTO vass_background SET `user_id` = '" . $member_id . "', `color` = '". $color ."', `image` = '" . $image . "', `position` = '" . $position . "', `repeat` = '" . $repeat . "', `use_image` = '" . $use_image . "' ON DUPLICATE KEY UPDATE `color` = '" . $color . "', `image` = '" . $image . "', `position` = '" . $position . "', `repeat` = '" . $repeat . "', `use_image` = '" . $use_image . "';" );

}

function member_followers ($user_id){
    global $db;

    $buffer = $db->query ( "SELECT vass_users.user_id, vass_users.username, vass_users.name, vass_users.bio, vass_users.website, vass_users.total_loved, vass_users.location, vass_users.total_loved, vass_users.total_following, vass_users.total_followers, vass_users.avatar, vass_background.color, vass_background.image, vass_background.position, vass_background.repeat, vass_background.use_image FROM vass_users LEFT JOIN vass_background ON vass_users.user_id = vass_background.user_id LEFT JOIN vass_friendship ON vass_users.user_id = vass_friendship.user_id WHERE vass_friendship.follower_id = '" . $user_id . "';" );

    return $buffer;
}

function member_playlist ($user_id){
    global $db;

    $buffer = $db->query ( "SELECT vass_playlists.name, vass_playlists.date, vass_playlists.id AS playlist_id, vass_playlists.cover, vass_playlists.descr, vass_users.username FROM vass_playlists LEFT JOIN vass_users ON vass_playlists.user_id = vass_users.user_id WHERE vass_playlists.user_id = '" . $user_id . "';" );

    return $buffer;
}

function member_playlist_total ($user_id){
    global $db;

    $buffer = $db->super_query ( "SELECT COUNT(*) AS count FROM vass_playlists WHERE user_id = '" . $user_id . "';" );

    return $buffer;
}


function member_username ($username){
    global $db;

    $buffer = $db->super_query ( "SELECT vass_friendship.user_id, vass_users.user_id, vass_users.name, vass_users.bio, vass_users.website, vass_users.total_loved, vass_users.location, vass_users.total_loved, vass_users.total_following, vass_users.total_followers, vass_users.avatar, vass_background.color, vass_background.image, vass_background.position, vass_background.repeat, vass_background.use_image FROM vass_users LEFT JOIN vass_background ON vass_users.user_id = vass_background.user_id LEFT JOIN vass_friendship ON vass_users.user_id = vass_friendship.follower_id WHERE vass_users.username = '" . $username . "';" );

    return $buffer;
}

function member_delete_facebook ($member_id){
    global $db;

    $db->query("DELETE FROM vass_facebook WHERE user_id = '" . $member_id . "'");

}

function member_delete_twitter ($member_id){
    global $db;

    $db->query("DELETE FROM vass_twitter WHERE user_id = '" . $member_id . "'");

}

function member_twitter_id ($member_id){
    global $db;

    $buffer = $db->super_query ( "SELECT screen_id AS twitter_screen_id, screen_name AS twitter_screen_name, date AS twitter_date FROM vass_twitter WHERE user_id = '" . $member_id . "'" );
    
    return $buffer;

}

function member_facebook_id ($member_id){
    global $db;

    $buffer = $db->super_query ( "SELECT screen_id AS facebook_screen_id, screen_name AS facebook_screen_name, date AS facebook_date FROM vass_facebook WHERE user_id = '" . $member_id . "'" );
    
    return $buffer;

}

function member_login ($member_id, $password ){
    global $db;

    $buffer = $db->super_query ( "SELECT user_id FROM vass_users WHERE user_id = '" . $member_id . "' AND password = '" . $password . "'" );
    
    return $buffer;

}

function member_login_update_password ($member_id, $new_password ){
    global $db;

    $db->query ( "UPDATE vass_users SET password = '". $new_password . "' WHERE user_id = '" . $member_id . "'" );

}

function member_update_profile ($member_id, $bio, $location, $name, $website ){
    global $db;

    $db->query ( "UPDATE vass_users SET bio = '". $bio ."', location = '". $location. "', name = '". $name ."', website = '". $website. "' WHERE user_id = '" . $member_id . "'" );

}

function member_profile ($username ){
    global $db;

    $buffer = $db->super_query ( "SELECT vass_users.name, vass_users.bio, vass_users.website, vass_users.total_loved, vass_users.location, vass_users.total_loved, vass_users.total_following, vass_users.total_followers, vass_background.color, vass_background.image, vass_background.position, vass_background.repeat, vass_background.use_image FROM vass_users LEFT JOIN vass_background ON vass_users.user_id = vass_background.user_id WHERE vass_users.username = '" . $username . "';" );
    
    return $buffer;

}


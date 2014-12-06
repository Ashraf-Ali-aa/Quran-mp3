<?php
function userlist (){
    global $db;

    $buffer = $db->query("SELECT vass_friendship.follower_id, vass_users.username, vass_users.name, vass_users.bio, vass_users.website, vass_users.total_loved, vass_users.location, vass_users.total_loved, vass_users.total_following, vass_users.total_followers, vass_users.avatar, vass_background.color, vass_background.image, vass_background.position, vass_background.repeat, vass_background.use_image FROM vass_users LEFT JOIN vass_background ON vass_users.user_id = vass_background.user_id LEFT JOIN vass_friendship ON vass_users.user_id = vass_friendship.follower_id LIMIT 0,50");

    return $buffer;

}

function albumlist ($letter = ''){
    global $db;

    if(!empty($letter)) {
        $buffer = $db->query("SELECT vass_albums.artist_id, vass_artists.name AS artist, vass_albums.id, vass_albums.id, vass_albums.view, vass_albums.name FROM vass_albums LEFT JOIN vass_artists ON vass_albums.artist_id = vass_artists.id WHERE vass_albums.name LIKE '" . $letter . "%' LIMIT 0,40");
   } else {
        $buffer = $db->query("SELECT vass_albums.artist_id, vass_artists.name AS artist, vass_albums.id, vass_albums.id, vass_albums.view, vass_albums.name FROM vass_albums LEFT JOIN vass_artists ON vass_albums.artist_id = vass_artists.id LIMIT 0,40");
}

    return $buffer;

}

function artistlist ($letter = ''){
    global $db;

    if(!empty($letter)){
        $buffer = $db->query("SELECT id, name FROM vass_artists WHERE name LIKE binary '". $letter . "%'");
    } else {
        $buffer = $db->query("SELECT id, name FROM vass_artists LIMIT 0,40");
}

    return $buffer;

}

function audio_count ( $row ){
    global $db;

    $buffer = $db->super_query("SELECT COUNT(*) AS count FROM vass_audios WHERE artist_id = '" . $row . "'");

    return $buffer;

}

function related ( $query ){
    global $db;

    $buffer = $db->query("SELECT id, title FROM vass_audios WHERE title LIKE '%" .$query ."%' LIMIT 0,10");

    return $buffer;

}


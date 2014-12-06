<?php
function last_loved_sql_result (){
    global $db;

    $buffer = $db->query ( "SELECT DISTINCT vass_audios.id AS audio_id, vass_audios.title AS audio_title, vass_audios.loved, vass_artists.name AS audio_artist, vass_albums.name AS audio_album, vass_albums.id AS album_id, vass_audio_love.created_on, vass_users.username, vass_users.user_id FROM vass_audio_love LEFT JOIN vass_friendship ON vass_friendship.follower_id = vass_audio_love.user_id LEFT JOIN vass_audios ON vass_audio_love.audio_id = vass_audios.id LEFT JOIN vass_users ON vass_audio_love.user_id = vass_users.user_id LEFT JOIN vass_albums on vass_audios.album_id = vass_albums.id LEFT JOIN vass_artists ON vass_audios.artist_id = vass_artists.id ORDER BY vass_audio_love.id DESC" );

    return $buffer;
}

function love_track ($audioid, $member_id ){
    global $db;

    $db->query ( "INSERT IGNORE INTO vass_audio_love SET audio_id = '" . $audioid . "', user_id= '" . $member_id . "', created_on = '" . date( "Y-m-d H:i:s", time() ) . "'" );

    $db->query ( "UPDATE vass_audios SET loved = loved+1, last_loved = '" . date( "Y-m-d H:i:s", time() ) . "' WHERE id = '" . $audioid . "'" );

    $db->query ( "UPDATE vass_users SET total_loved = total_loved+1 WHERE user_id= '" . $member_id . "'" );

}

function unlove_track ($audioid, $member_id ){
    global $db;

    $db->query ( "DELETE FROM vass_audio_love WHERE audio_id = '" . $audioid . "' AND user_id= '" . $member_id . "'" );

    $db->query ( "UPDATE vass_audios SET loved = loved-1 WHERE id = '" . $audioid . "'" );

    $db->query ( "UPDATE vass_users SET total_loved = total_loved-1 WHERE user_id= '" . $member_id . "'" );

}
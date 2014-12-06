<?php
function search_query ($qtxt){
    global $db;

    $buffer = $db->query ( "SELECT DISTINCT vass_audios.artist_id, vass_audios.id AS audio_id, vass_audios.title AS audio_title, vass_audios.loved, vass_artists.name AS audio_artist, vass_albums.name AS audio_album, vass_albums.id AS album_id FROM vass_audios LEFT JOIN vass_albums ON vass_audios.album_id = vass_albums.id LEFT JOIN vass_artists ON vass_audios.artist_id = vass_artists.id WHERE LOWER(vass_audios.title) LIKE '%". $qtxt . "%' or LOWER(vass_artists.name) LIKE '%" . $qtxt . "%' or LOWER(vass_albums.name) LIKE '%" . $qtxt . "%'" );

    return $buffer;
}

function search_follower ($keyword){
    global $db;

    $buffer = $db->query ( "SELECT DISTINCT vass_friendship.follower_id, vass_users.username, vass_users.name, vass_users.bio, vass_users.website, vass_users.total_loved, vass_users.location, vass_users.total_loved, vass_users.total_following, vass_users.total_followers, vass_users.avatar, vass_background.color, vass_background.image, vass_background.position, vass_background.repeat, vass_background.use_image FROM vass_users LEFT JOIN vass_background ON vass_users.user_id = vass_background.user_id LEFT JOIN vass_friendship ON vass_users.user_id = vass_friendship.follower_id WHERE vass_users.name LIKE '%" . $keyword . "%' or vass_users.email LIKE '%" . $keyword . "%' or vass_users.username LIKE '%" . $keyword . "%'" );

    return $buffer;
}

function search_album ($q){
    global $db;

    $buffer = $db->query("SELECT vass_albums.artist_id, vass_artists.name AS artist, vass_albums.id, vass_albums.id, vass_albums.view, vass_albums.name FROM vass_albums LEFT JOIN vass_artists ON vass_albums.artist_id = vass_artists.id WHERE vass_albums.name LIKE '%" . $q ."%' LIMIT 0,20");

    return $buffer;
}

function search_artist ($q){
    global $db;

    $buffer = $db->query("SELECT id, name FROM vass_artists WHERE name LIKE '%" . $q . "%' LIMIT 0,5");

    return $buffer;
}

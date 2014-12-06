<?php
    
function artist_id ($id ){
    global $db;

    $buffer = $db->super_query ( "SELECT id AS artist_id, name, bio FROM vass_artists WHERE id = '" . $id . "';" );
    
    return $buffer;

}

function artist_results ($id , $start , $results_number ){
    global $db;

    $buffer = $db->query ( "SELECT vass_audios.artist_id, vass_audios.id AS audio_id, vass_audios.loved, vass_audios.title AS audio_title, vass_artists.id AS artist_id, vass_artists.name AS audio_artist, vass_albums.name AS audio_album, vass_albums.id AS album_id FROM vass_audios LEFT JOIN vass_albums ON vass_audios.album_id = vass_albums.id LEFT JOIN vass_artists ON vass_audios.artist_id = vass_artists.id WHERE vass_audios.artist_id REGEXP '[[:<:]]" . $id . "[[:>:]]' LIMIT " . $start . "," . $results_number . "");
    
    return $buffer;

}

function artist_total_results ($id ){
    global $db;

    $buffer = $db->super_query ( "SELECT COUNT(*) AS count FROM vass_audios LEFT JOIN vass_albums ON vass_audios.album_id = vass_albums.id LEFT JOIN vass_artists ON vass_audios.artist_id = vass_artists.id WHERE vass_audios.artist_id REGEXP '[[:<:]]" . $id . "[[:>:]]'" );
    
    return $buffer;

}

function artist_all_albums ($id ){
    global $db;

    $buffer = $db->query("SELECT vass_albums.artist_id, vass_artists.name AS artist, vass_albums.id, vass_albums.id, vass_albums.view, vass_albums.name FROM vass_albums LEFT JOIN vass_artists ON vass_albums.artist_id = vass_artists.id WHERE vass_artists.id = '" . $id . "'");
    
    return $buffer;

}

function artist_total_albums ($id ){
    global $db;

    $buffer = $db->super_query("SELECT COUNT(*) AS count FROM vass_albums WHERE artist_id = '" . $id . "'");
    
    return $buffer;

}


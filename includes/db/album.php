<?php
    
function album_id ($id ){
    global $db;

    $buffer = $db->super_query ( "SELECT vass_albums.descr, vass_albums.id AS album_id, vass_albums.name, vass_artists.id AS artist_id, vass_artists.name AS artist FROM vass_albums LEFT JOIN vass_artists ON vass_albums.artist_id =  vass_artists.id WHERE vass_albums.id = '" . $id . "';" );
    
    return $buffer;

}

function album_results ($id , $start , $results_number ){
    global $db;

    $buffer = $db->query ( "SELECT vass_audios.artist_id, vass_audios.id AS audio_id, vass_audios.loved, vass_audios.title AS audio_title, vass_artists.id AS artist_id, vass_artists.name AS audio_artist, vass_albums.name AS audio_album, vass_albums.id AS album_id FROM vass_audios LEFT JOIN vass_albums ON vass_audios.album_id = vass_albums.id LEFT JOIN vass_artists ON vass_audios.artist_id = vass_artists.id WHERE vass_audios.album_id = '" . $id . "' LIMIT " . $start . "," . $results_number . "");
    
    return $buffer;

}

function album_total_results ($id ){
    global $db;

    $buffer = $db->super_query ( "SELECT COUNT(*) AS count FROM vass_audios WHERE album_id = '" . $id ."'" );
    
    return $buffer;

}
<?php
    function artist_all_videos ($id ){
    global $db;

    $buffer = $db->query("SELECT vass_videos.artist_id, vass_videos.tube_key, vass_artists.name AS artist, vass_videos.id, vass_videos.id, vass_videos.view, vass_videos.name FROM vass_videos LEFT JOIN vass_artists ON vass_videos.artist_id = vass_artists.id WHERE vass_artists.id = '" . $id . "'");
    
    return $buffer;

}

function artist_total_videos ($id ){
    global $db;

    $buffer = $db->super_query("SELECT COUNT(*) AS count FROM vass_videos WHERE artist_id = '" . $id . "'");
    
    return $buffer;

}

function videos ($id ){
    global $db;

    $buffer =  $db->super_query("SELECT vass_videos.artist_id, vass_videos.tube_key, vass_artists.name AS artist, vass_videos.id, vass_videos.id, vass_videos.view, vass_videos.name FROM vass_videos LEFT JOIN vass_artists ON vass_videos.artist_id = vass_artists.id WHERE vass_videos.id = '" . $id ."'");
    
    return $buffer;

}
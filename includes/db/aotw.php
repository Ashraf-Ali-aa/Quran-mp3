<?php
function aotw_album ($config){
    global $db;

    $buffer = $db->super_query ( "SELECT vass_albums.name AS album_title, vass_albums.descr, vass_albums.date, vass_artists.name AS artist_name, vass_albums.id AS album_id FROM vass_albums LEFT JOIN vass_artists ON vass_albums.artist_id = vass_artists.id WHERE vass_albums.id='" . $config . "'" );

    return $buffer;
}

function aotw_sql_result ($config){
    global $db;

    $buffer = $db->query ( "SELECT vass_audios.id AS audio_id, vass_audios.title AS audio_title, vass_audios.loved, vass_artists.name AS audio_artist, vass_albums.name AS audio_album, vass_albums.id AS album_id FROM vass_audios LEFT JOIN vass_albums ON vass_audios.album_id = vass_albums.id LEFT JOIN vass_artists ON vass_audios.artist_id = vass_artists.id WHERE vass_albums.id = '" . $config . "'" );

    return $buffer;
}
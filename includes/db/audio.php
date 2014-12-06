<?php

function audio_track ($audio_id){
    global $db;

    $buffer = $db->super_query ( "SELECT vass_audios.artist_id, vass_audios.created_on, vass_audios.artist_id, vass_artists.tag AS tags, vass_audios.id AS audio_id, vass_audios.title AS audio_title, vass_audios.loved, vass_artists.name AS audio_artist, vass_albums.name AS audio_album, vass_albums.id AS album_id FROM vass_audios LEFT JOIN vass_albums ON vass_audios.album_id = vass_albums.id  LEFT JOIN vass_artists ON vass_audios.artist_id = vass_artists.id WHERE vass_audios.id = '" . $audio_id . "' LIMIT 0,1" );

    return $buffer;
} 

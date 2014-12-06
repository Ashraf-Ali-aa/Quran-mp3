<?php
function trending_day ($start = 0 , $results_number = 20){
    global $db;

    $buffer = $db->query ( "SELECT COUNT(*) AS count, audio_id FROM vass_analz WHERE `time` > '" . date( "Y-m-d", (time()  - 7*24*3600) ) . "' GROUP BY audio_id ORDER by count DESC LIMIT " . $start ."," . $results_number . "" );

    return $buffer;
}

function trending_genre ($trending, $genre_id = NULL){
    global $db;

    if (is_null($limit)){
        $buffer = $db->super_query ( "SELECT vass_audios.id AS audio_id, vass_audios.artist_id, vass_audios.id AS audio_id, vass_audios.loved, vass_audios.title AS audio_title, vass_artists.id AS artist_id, vass_artists.name AS audio_artist, vass_albums.name AS audio_album, vass_albums.id AS album_id FROM vass_audios LEFT JOIN vass_albums ON vass_audios.album_id = vass_albums.id LEFT JOIN vass_artists ON vass_audios.artist_id = vass_artists.id WHERE vass_audios.id = '" . $trending . "'" );
    } else {
        $buffer = $db->super_query ( "SELECT vass_audios.id audio_id, vass_audios.artist_id, vass_audios.id AS audio_id, vass_audios.loved, vass_audios.title AS audio_title, vass_artists.id AS artist_id, vass_artists.name AS audio_artist, vass_albums.name AS audio_album, vass_albums.id AS album_id FROM vass_audios LEFT JOIN vass_albums ON vass_audios.album_id = vass_albums.id LEFT JOIN vass_artists ON vass_audios.artist_id = vass_artists.id WHERE vass_audios.id = '" . $trending . "' AND vass_audios.tags REGEXP '[[:<:]]" . $genre_id . "[[:>:]]'" );
    }

    return $buffer;
}

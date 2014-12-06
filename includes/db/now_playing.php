<?php
function played_count ($audio_id, $_TIME ){
    global $db;

    $db->query ( "UPDATE vass_audios SET played=played+1 WHERE id = '" . $audio_id . "'" );

    $db->query ( "INSERT INTO vass_analz SET `time`= '" . $_TIME . "', audio_id = '" . $audio_id . "'" );

}
/// todo update sql query - new not in more.php

function download_count ($audio_id, $_TIME ){
    global $db;

    $db->query ( "UPDATE vass_audios SET download=download+1 WHERE id = '" . $audio_id . "'" );

    $db->query ( "INSERT INTO vass_analz SET `time`= '" . $_TIME . "', audio_id = '" . $audio_id . "'" );

}
/// too implement
function played_log ($username ){
    global $db;

    $buffer = $db->super_query ( "SELECT vass_playlists.name, vass_playlists.date, vass_playlists.id AS playlist_id, vass_playlists.cover, vass_playlists.descr, vass_users.username FROM vass_playlists LEFT JOIN vass_users ON vass_playlists.user_id = vass_users.user_id WHERE vass_playlists.id = '" . $id . "';" );
    
    return $buffer;
		
}
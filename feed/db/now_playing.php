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
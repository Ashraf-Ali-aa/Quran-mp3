<?php
function playlist_insert ($member_id, $_TIME, $name, $descr ){
    global $db;

    $db->query("INSERT INTO vass_playlists (user_id, `date`, name, descr) VALUES ('" . $member_id . "', '". $_TIME ."', '". $name ."', '". $descr . "');");

}

function playlist_update ($member_id, $id, $name, $descr ){
    global $db;

    $db->query("UPDATE vass_playlists SET name= '". $name ."', descr = '" . $descr ."' WHERE user_id = '" . $member_id . "' AND id = '". $id . "';");

}

function playlist_sort ($playlist_id, $item , $i ){
    global $db;

    $db->query ("UPDATE vass_audio_playlist SET pos = '". $i ."' WHERE audio_id = '" . $item . "' AND playlist_id = '". $playlist_id ."'");

}

function playlist_remove ($member_id, $id ){
    global $db;

    $db->query("DELETE FROM vass_playlists WHERE user_id = '" . $member_id ['user_id'] . "' AND id = '". $id ."';");

}

function playlist_info ($id ){
    global $db;

    $buffer = $db->super_query ( "SELECT vass_playlists.name, vass_playlists.date, vass_playlists.id AS playlist_id, vass_playlists.cover, vass_playlists.descr, vass_users.username FROM vass_playlists LEFT JOIN vass_users ON vass_playlists.user_id = vass_users.user_id WHERE vass_playlists.id = '" . $id . "';" );
    
    return $buffer;
		
}

function playlist_add_audio ($playlist_id , $audio_id){
    global $db;

    $db->query("INSERT IGNORE INTO vass_audio_playlist (audio_id, playlist_id) VALUES ('" . $audio_id ."', '" . $playlist_id ."')");

}

function playlist_remove_audio ($playlist_id , $audio_id){
    global $db;

    $db->query("DELETE FROM vass_audio_playlist WHERE audio_id = '" . $audio_id ."' AND playlist_id = '". $playlist_id ."'");

}

function playlist_results ($playlist_id , $start , $results_number ){
    global $db;

    $buffer = $db->query ( "SELECT vass_audios.artist_id, vass_audios.id AS audio_id, vass_audios.loved, vass_audios.title AS audio_title, vass_artists.id AS artist_id, vass_artists.name AS audio_artist, vass_albums.name AS audio_album, vass_albums.id AS album_id FROM vass_audio_playlist LEFT JOIN vass_audios ON vass_audio_playlist.audio_id = vass_audios.id LEFT JOIN vass_albums ON vass_audios.album_id = vass_albums.id LEFT JOIN vass_artists ON vass_audios.artist_id = vass_artists.id WHERE vass_audio_playlist.playlist_id = '" . $playlist_id . "' ORDER BY vass_audio_playlist.pos ASC LIMIT ". $start. "," . $results_number . "");
    
    return $buffer;

}

function playlist_results_asc ($playlist_id ){
    global $db;

    $buffer = $db->query ( "SELECT vass_audios.artist_id, vass_audios.id AS audio_id, vass_audios.loved, vass_audios.title AS audio_title, vass_artists.id AS artist_id, vass_artists.name AS audio_artist, vass_albums.name AS audio_album, vass_albums.id AS album_id FROM vass_audio_playlist LEFT JOIN vass_audios ON vass_audio_playlist.audio_id = vass_audios.id LEFT JOIN vass_albums ON vass_audios.album_id = vass_albums.id LEFT JOIN vass_artists ON vass_audios.artist_id = vass_artists.id WHERE vass_audio_playlist.playlist_id = '" . $playlist_id . "' ORDER BY vass_audio_playlist.pos ASC" );
    
    return $buffer;

}

function playlist_total_results ($playlist_id ){
    global $db;

    $buffer = $db->super_query ( "SELECT COUNT(*) AS count FROM vass_audio_playlist LEFT JOIN vass_audios ON vass_audio_playlist.audio_id = vass_audios.id LEFT JOIN vass_albums ON vass_audios.album_id = vass_albums.id LEFT JOIN vass_artists ON vass_audios.artist_id = vass_artists.id WHERE vass_audio_playlist.playlist_id = '" . $playlist_id . "'" );
    
    return $buffer;

}

function playlist_creator ($playlist_id ){
    global $db;

    $buffer = $db->super_query("SELECT user_id FROM vass_playlists WHERE id = '" . $playlist_id . "'");
    
    return $buffer;

}

function playlist_last (){
    global $db;

    $buffer = $db->query ( "SELECT vass_playlists.name, vass_playlists.date, vass_playlists.id AS playlist_id, vass_playlists.cover, vass_playlists.descr, vass_users.username FROM vass_playlists LEFT JOIN vass_users ON vass_playlists.user_id = vass_users.user_id ORDER by vass_playlists.id DESC;" );
    
    return $buffer;

}




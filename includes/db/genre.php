<?php
function genre_name ($request_name, $limit = NULL){
    global $db;

    $name = $db->safesql ( $request_name );
    if (is_null($limit)){
        $buffer = $db->super_query("SELECT id FROM vass_genres WHERE name LIKE '%" . $name . "%'");
        } else {
        $buffer = $db->super_query ( "SELECT id FROM vass_genres WHERE name LIKE '%" . $name ."%' LIMIT 0,1" );    
        }

    return $buffer;
}

function genre_sql_result ($row, $start){
    global $db;

    $buffer = $db->query ( "SELECT vass_audios.artist_id, vass_audios.id AS audio_id, vass_audios.loved, vass_audios.title AS audio_title, vass_artists.name AS audio_artist, vass_albums.name AS audio_album, vass_albums.id AS album_id FROM vass_audios LEFT JOIN vass_albums ON vass_audios.album_id = vass_albums.id LEFT JOIN vass_artists ON vass_audios.artist_id = vass_artists.id WHERE vass_artists.tag REGEXP '[[:<:]]" . $row ['id'] . "[[:>:]]' LIMIT ".$start.",200" );

    return $buffer;
} 

function genre_results( $row ){
	global $db;
	
	$buffer = $db->super_query ( "SELECT COUNT(*) AS count FROM vass_audios LEFT JOIN vass_albums ON
	vass_audios.album_id = vass_albums.id LEFT JOIN vass_artists ON vass_audios.artist_id = vass_artists.id
	WHERE vass_artists.tag REGEXP '[[:<:]]" . $row . "[[:>:]]' LIMIT 0,200" );
			
	return $buffer;
}

function genre_sticky( $limit = '20'){
	global $db;
	
	$buffer = $db->query("SELECT name FROM `vass_genres` WHERE stick= 1 ORDER by id ASC LIMIT 0,". $limit."");
			
	return $buffer;
}
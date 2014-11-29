<?php

@session_start();
define( 'ROOT_DIR', dirname( __FILE__ ) );
define( 'INCLUDE_DIR', ROOT_DIR . '/includes' );

include      INCLUDE_DIR . '/config.inc.php';
require_once INCLUDE_DIR . '/class/_class_mysql.php';
require_once INCLUDE_DIR . '/db.php';
require_once INCLUDE_DIR . '/member.php';
require_once ROOT_DIR    . '/modules/functions.php';

header( 'Content-type: text/json' );
header( 'Content-type: application/json' );

$_TIME = date( "Y-m-d H:i:s", time() );

if (isset ( $_REQUEST ['t'] ))
    $type = $_REQUEST ['t'];

if (isset ( $_REQUEST ['start'] ))
    $start = intval( $_REQUEST ['start'] );

if (isset ( $_REQUEST ['results'] ))
    $results_number = intval( $_REQUEST ['results'] );

if ($type == "top") {

    $top_week = $db->query ( "SELECT COUNT(*) AS count, audio_id FROM vass_analz WHERE `time` > '" . date( "Y-m-d", (time() - 30 * 24 * 3600) ) . "' GROUP BY audio_id ORDER by count DESC LIMIT $start, $results_number" );

    $total_results = $db->super_query ( "SELECT COUNT(*) AS count FROM (SELECT COUNT(*) AS bit, audio_id FROM vass_analz WHERE `time` > '" . date( "Y-m-d", (time() - 30 * 24 * 3600) ) . "' GROUP BY audio_id ORDER by bit DESC LIMIT 0,200) AS count" );

    while ( $top = $db->get_row ( $top_week ) ) {

        if ($top ['audio_id']) {
            $row = $db->super_query ( "SELECT vass_audios.artist_id, vass_audios.id AS audio_id, vass_audios.loved, vass_audios.title AS audio_title,
			vass_artists.id AS artist_id, vass_artists.name AS audio_artist, vass_albums.name AS audio_album, vass_albums.id AS album_id
			FROM vass_audios LEFT JOIN vass_albums ON vass_audios.album_id = vass_albums.id LEFT JOIN
			vass_artists ON vass_audios.artist_id = vass_artists.id WHERE vass_audios.id = '" . $top ['audio_id'] . "'" );
            $audios ['album']               = $row ['audio_album'];
            $audios ['artists']             = artists ( $row ['artist_id'] );
            $audios ['similar_artists']     = similar_artists ( $row ['audio_id'] );
            $audios ['buy_link']            = null;
            $audios ['artist']              = $row ['audio_artist'];
            $audios ['url']                 = stream ( $row ['audio_id'] );
            $audios ['image']               = audiolist_images ( $row ['album_id'] );
            $audios ['track_number']        = track_number ( $row ['audio_id'] );
            $audios ['title']               = $row ['audio_title'];
            $audios ['duration']            = track_duration ( $row ['audio_id'] );
            $audios ['metadata_state']      = metadata_state ( $row ['audio_id'] );
            $audios ['sources']             = sources ( $row ['audio_id'] );
            $audios ['play_count']          = play_count ( $row ['audio_id'] );
            $audios ['viewer_love']         = viewer_love ( $row ['audio_id'] );
            $audios ['last_loved']          = last_loved ( $row ['audio_id'] );
            $audios ['recent_loves']        = recent_loves ( $row ['audio_id'] );
            $audios ['aliases']             = aliases ( $row ['audio_id'] );
            $audios ['loved_count']         = $row ['loved'];
            $audios ['id']                  = $row ['audio_id'];
            $audios ['tags']                = tags ( $row ['audio_id'] );
            $audios ['trending_rank_today'] = trending_rank_today ( $row ['audio_id'] );
            $audios ['user_love']           = "";
            $result ['audios'] []           = $audios;
        }
    }

    $result ['status_text'] = "OK";
    $result ['status_code'] = "200";
    $result ['results']     = $total_results['count'];
    $result ['start']       = $start;
    $result ['total']       = $total_results['count'];

    echo json_encode( $result );

} elseif ($type == "aotw") {

    $album = $db->super_query ( "SELECT vass_albums.name AS album_title, vass_albums.descr, vass_albums.date, vass_artists.name AS artist_name, vass_albums.id AS album_id FROM vass_albums LEFT JOIN vass_artists ON vass_albums.artist_id = vass_artists.id WHERE vass_albums.id='" . $config['album_week'] . "'" );

    $sql_result = $db->query ( "SELECT vass_audios.id AS audio_id, vass_audios.title AS audio_title, vass_audios.loved, vass_artists.name AS audio_artist, vass_albums.name AS audio_album, vass_albums.id AS album_id FROM vass_audios LEFT JOIN vass_albums ON vass_audios.album_id = vass_albums.id LEFT JOIN vass_artists ON vass_audios.artist_id = vass_artists.id WHERE vass_albums.id = '" . $config['album_week'] . "'" );

    while ( $row = $db->get_row ( $sql_result ) ) {

        $audio_list ['album']                = $row ['audio_album'];
        $audio_list ['similar_artists']      = similar_artists ( $row ['audio_id'] );
        $audio_list ['buy_link']             = null;
        $audio_list ['artist']               = $row ['audio_artist'];
        $audio_list ['url']                  = stream ( $row ['audio_id'] );
        $audio_list ['image']                = audiolist_images ( $row ['album_id'] );
        $audio_list ['track_number']         = track_number ( $row ['audio_id'] );
        $audio_list ['title']                = $row ['audio_title'];
        $audio_list ['duration']             = track_duration ( $row ['audio_id'] );
        $audio_list ['metadata_state']       = metadata_state ( $row ['audio_id'] );
        $audio_list ['sources']              = sources ( $row ['audio_id'] );
        $audio_list ['play_count']           = play_count ( $row ['audio_id'] );
        $audio_list ['viewer_love']          = viewer_love ( $row ['audio_id'] );
        $audio_list ['last_loved']           = last_loved ( $row ['audio_id'] );
        $audio_list ['recent_loves']         = recent_loves ( $row ['audio_id'] );
        $audio_list ['aliases']              = aliases ( $row ['audio_id'] );
        $audio_list ['loved_count']          = $row ['loved'];
        $audio_list ['id']                   = $row ['audio_id'];
        $audio_list ['tags']                 = tags ( $row ['audio_id'] );
        $audio_list ['trending_rank_today']  = trending_rank_today ( $row ['audio_id'] );
        $audios []                           = $audio_list;

    }

    $buffer = array ("status_text" => "OK", 
                     "status_code" => 200, 
                     "results"     => 1, 
                     "start"       => 0, 
                     "total"       => 1, 
                     "albums"      => array ("description" => $album ['descr'], "artist" => $album ['artist_name'], 
                     "date"        => date( 'D M d Y H:i:s O', strtotime( $album ['date'] ) ), "artwork_url" => $config['siteurl'] . "static/albums/" . $config['album_week'] ."_extralarge.jpg", 
                     "title"       => $album ['album_title'], "day" => 20111005, "audios" => $audios ) );

    header( 'Cache-Control: no-cache, must-revalidate' );

    header( 'Content-type: application/json' );

    print json_encode( $buffer );
} elseif ($type == "last_loved") {
    $sql_result = $db->query ( "SELECT DISTINCT vass_audios.id AS audio_id, vass_audios.title AS audio_title, vass_audios.loved, vass_artists.name AS audio_artist, vass_albums.name AS audio_album, vass_albums.id AS album_id, vass_audio_love.created_on, vass_users.username, vass_users.user_id FROM vass_audio_love LEFT JOIN vass_friendship ON vass_friendship.follower_id = vass_audio_love.user_id LEFT JOIN vass_audios ON vass_audio_love.audio_id = vass_audios.id LEFT JOIN vass_users ON vass_audio_love.user_id = vass_users.user_id LEFT JOIN vass_albums on vass_audios.album_id = vass_albums.id LEFT JOIN vass_artists ON vass_audios.artist_id = vass_artists.id ORDER BY vass_audio_love.id DESC" );

    $start          = $_REQUEST ['start'];
    $results_number = $_REQUEST ['results'];
    $page_start     = $start;
    $page_end       = $start + $results_number;

    $total_results  = $db->num_rows ( $sql_result );

    $i = 0;

    while ( $row = $db->get_row ( $sql_result ) ) {

        if ($i >= $page_start) {

            $object ['title']                          = $row ['audio_title'];
            $object ['object'] ['album']               = $row ['audio_album'];
            $object ['object'] ['similar_artists']     = similar_artists ( $row ['audio_id'] );
            $object ['object'] ['buy_link']            = null;
            $object ['object'] ['artist']              = $row ['audio_artist'];
            $object ['object'] ['url']                 = stream ( $row ['audio_id'] );
            $object ['object'] ['image']               = audiolist_images ( $row ['album_id'] );
            $object ['object'] ['track_number']        = track_number ( $row ['audio_id'] );
            $object ['object'] ['title']               = $row ['audio_title'];
            $object ['object'] ['duration']            = track_duration ( $row ['audio_id'] );
            $object ['object'] ['metadata_state']      = metadata_state ( $row ['audio_id'] );
            $object ['object'] ['sources']             = sources ( $row ['audio_id'] );
            $object ['object'] ['play_count']          = play_count ( $row ['audio_id'] );
            $object ['object'] ['viewer_love']         = viewer_love ( $row ['audio_id'] );
            $object ['object'] ['last_loved']          = last_loved ( $row ['audio_id'] );
            $object ['object'] ['recent_loves']        = recent_loves ( $row ['audio_id'] );
            $object ['object'] ['aliases']             = aliases ( $row ['audio_id'] );
            $object ['object'] ['loved_count']         = $row ['loved'];
            $object ['object'] ['id']                  = $row ['audio_id'];
            $object ['object'] ['tags']                = tags ( $row ['audio_id'] );
            $object ['object'] ['trending_rank_today'] = trending_rank_today ( $row ['audio_id'] );
            $object ['object'] ['user_love']           = array ("username" => $row ['username'],
                "created_on" => date( 'D M d Y H:i:s O', strtotime( $row ['created_on'] ) ) );

            $activities [] = $object;

        }
        $i ++;

        if ($i >= $page_end)
            break;
    }

    $buffer ['status_text'] = "OK";
    $buffer ['status_code'] = "200";
    $buffer ['results']     = $total_results;
    $buffer ['start']       = $start;
    $buffer ['total']       = $total_results;
    $buffer ['activities']  = $activities;

    header( 'Cache-Control: no-cache, must-revalidate' );

    header( 'Content-type: application/json' );

    print json_encode( $buffer );

} elseif ($type == "genre") {

    $name = $db->safesql ( $_REQUEST ['name'] );

    $row = $db->super_query ( "SELECT id FROM vass_genres WHERE name LIKE '%$name%' LIMIT 0,1" );

    $sql_result = $db->query ( "SELECT vass_audios.artist_id, vass_audios.id AS audio_id, vass_audios.loved, vass_audios.title AS audio_title,
	vass_artists.name AS audio_artist, vass_albums.name AS audio_album, vass_albums.id AS album_id FROM vass_audios LEFT JOIN vass_albums ON
	vass_audios.album_id = vass_albums.id LEFT JOIN vass_artists ON vass_audios.artist_id = vass_artists.id
	WHERE vass_artists.tag REGEXP '[[:<:]]" . $row ['id'] . "[[:>:]]' LIMIT $start,200" );

    $total_results = $db->super_query ( "SELECT COUNT(*) AS count FROM vass_audios LEFT JOIN vass_albums ON
	vass_audios.album_id = vass_albums.id LEFT JOIN vass_artists ON vass_audios.artist_id = vass_artists.id
	WHERE vass_artists.tag REGEXP '[[:<:]]" . $row ['id'] . "[[:>:]]' LIMIT 0,200" );;

    while ( $row = $db->get_row ( $sql_result ) ) {

        $audios ['album']               = $row ['audio_album'];
        $audios ['artists']             = artists ( $row ['artist_id'] );
        $audios ['similar_artists']     = similar_artists ( $row ['audio_id'] );
        $audios ['buy_link']            = null;
        $audios ['artist']              = $row ['audio_artist'];
        $audios ['url']                 = stream ( $row ['audio_id'] );
        $audios ['image']               = audiolist_images ( $row ['album_id'] );
        $audios ['track_number']        = track_number ( $row ['audio_id'] );
        $audios ['title']               = $row ['audio_title'];
        $audios ['duration']            = track_duration ( $row ['audio_id'] );
        $audios ['metadata_state']      = metadata_state ( $row ['audio_id'] );
        $audios ['sources']             = sources ( $row ['audio_id'] );
        $audios ['play_count']          = play_count ( $row ['audio_id'] );
        $audios ['viewer_love']         = viewer_love ( $row ['audio_id'] );
        $audios ['last_loved']          = last_loved ( $row ['audio_id'] );
        $audios ['recent_loves']        = recent_loves ( $row ['audio_id'] );
        $audios ['aliases']             = aliases ( $row ['audio_id'] );
        $audios ['loved_count']         = $row ['loved'];
        $audios ['id']                  = $row ['audio_id'];
        $audios ['tags']                = tags ( $row ['audio_id'] );
        $audios ['trending_rank_today'] = trending_rank_today ( $row ['audio_id'] );
        $audios ['user_love']           = null;
        $result ['audios'] []           = $audios;

    }

    $result ['status_text'] = "OK";
    $result ['status_code'] = "200";
    $result ['start']       = $start;
    $result ['total']       = $total_results['count'];
    echo json_encode( $result );

} elseif ($type == "member") {
    $username = $db->safesql ( $_REQUEST ['username'] );

    $username = preg_replace( "/[^a-zA-Z0-9\s]/", "", $username );

    $action = $db->safesql ( $_REQUEST ['action'] );

    if ($action == "loved") {

        $row = $db->super_query ( "SELECT user_id FROM vass_users WHERE username = '" . $username . "'" );

        $sql_result = $db->query ( "SELECT vass_audios.artist_id, vass_audios.id AS audio_id, vass_audios.title AS audio_title, vass_audios.loved, vass_artists.name AS audio_artist, vass_albums.name AS audio_album, vass_albums.id AS album_id, vass_audio_love.created_on, vass_users.username, vass_users.user_id FROM vass_audio_love LEFT JOIN vass_audios ON vass_audio_love.audio_id = vass_audios.id LEFT JOIN vass_users ON vass_audio_love.user_id = vass_users.user_id LEFT JOIN vass_albums on vass_audios.album_id = vass_albums.id LEFT JOIN vass_artists ON vass_audios.artist_id = vass_artists.id WHERE vass_audio_love.user_id = '" . $row ['user_id'] . "' ORDER BY vass_audio_love.id DESC" );

        $start = $_REQUEST ['start'];
        $results_number = $_REQUEST ['results'];

        $page_start = $start;

        $page_end = $start + $results_number;

        $total_results = $db->num_rows ( $sql_result );

        $i = 0;

        while ( $row = $db->get_row ( $sql_result ) ) {

            if ($i >= $page_start) {

                $audios ['album']                 = $row ['audio_album'];
                $audios ['artists']               = artists ( $row ['artist_id'] );
                $audios ['similar_artists']       = similar_artists ( $row ['audio_id'] );
                $audios ['buy_link']              = null;
                $audios ['artist']                = $row ['audio_artist'];
                $audios ['url']                   = stream ( $row ['audio_id'] );
                $audios ['image']                 = audiolist_images ( $row ['album_id'] );
                $audios ['track_number']          = track_number ( $row ['audio_id'] );
                $audios ['title']                 = $row ['audio_title'];
                $audios ['duration']              = track_duration ( $row ['audio_id'] );
                $audios ['metadata_state']        = metadata_state ( $row ['audio_id'] );
                $audios ['sources']               = sources ( $row ['audio_id'] );
                $audios ['play_count']            = play_count ( $row ['audio_id'] );
                $audios ['viewer_love']           = viewer_love ( $row ['audio_id'] );
                $audios ['last_loved']            = last_loved ( $row ['audio_id'] );
                $audios ['recent_loves']          = recent_loves ( $row ['audio_id'] );
                $audios ['aliases']               = aliases ( $row ['audio_id'] );
                $audios ['loved_count']           = $row ['loved'];
                $audios ['id']                    = $row ['audio_id'];
                $audios ['tags']                  = tags ( $row ['audio_id'] );
                $audios ['trending_rank_today']   = trending_rank_today ( $row ['audio_id'] );
                $audios ['user_love']             = array ("username" => $member_id ['username'] );
                $buffer ['audios'] []             = $audios;

            }

            $i ++;

            if ($i >= $page_end)
                break;

        }

        $buffer ['status_text'] = "OK";
        $buffer ['status_code'] = "200";
        $buffer ['results']     = $total_results;
        $buffer ['start']       = $start;
        $buffer ['total']       = $total_results;

    } elseif ($action == "feedlove") {

        $row = $db->super_query ( "SELECT user_id FROM vass_users WHERE username = '" . $username . "'" );

        if ($username == "tastemakers") {

            $sql_result = $db->query ( "SELECT DISTINCT vass_audios.id AS audio_id, vass_audios.title AS audio_title, vass_audios.loved, vass_artists.name AS audio_artist, vass_albums.name AS audio_album, vass_albums.id AS album_id, vass_audio_love.created_on, vass_users.username, vass_users.user_id FROM vass_audio_love LEFT JOIN vass_friendship ON vass_friendship.follower_id = vass_audio_love.user_id LEFT JOIN vass_audios ON vass_audio_love.audio_id = vass_audios.id LEFT JOIN vass_users ON vass_audio_love.user_id = vass_users.user_id LEFT JOIN vass_albums on vass_audios.album_id = vass_albums.id LEFT JOIN vass_artists ON vass_audios.artist_id = vass_artists.id" );

        } else {

            $row = $db->super_query ( "SELECT user_id FROM vass_users WHERE username = '" . $username . "'" );

            $sql_result = $db->query ( "SELECT DISTINCT vass_audios.id AS audio_id, vass_audios.title AS audio_title, vass_audios.loved, vass_artists.name AS audio_artist, vass_albums.name AS audio_album, vass_albums.id AS album_id, vass_audio_love.created_on, vass_users.username, vass_users.user_id FROM vass_audio_love LEFT JOIN vass_friendship ON vass_friendship.follower_id = vass_audio_love.user_id LEFT JOIN vass_audios ON vass_audio_love.audio_id = vass_audios.id LEFT JOIN vass_users ON vass_audio_love.user_id = vass_users.user_id LEFT JOIN vass_albums on vass_audios.album_id = vass_albums.id LEFT JOIN vass_artists ON vass_audios.artist_id = vass_artists.id WHERE vass_friendship.user_id = '" . $row ['user_id'] . "' ORDER BY vass_audio_love.id DESC" );
        }
        $start = $_REQUEST ['start'];
        $results_number = $_REQUEST ['results'];

        $page_start = $start;

        $page_end = $start + $results_number;

        $total_results = $db->num_rows ( $sql_result );

        $i = 0;

        while ( $row = $db->get_row ( $sql_result ) ) {

            if ($i >= $page_start) {
                $object ['title']                          = $row ['audio_title'];
                $object ['object'] ['album']               = $row ['audio_album'];
                $object ['object'] ['similar_artists']     = similar_artists ( $row ['audio_id'] );
                $object ['object'] ['buy_link']            = null;
                $object ['object'] ['artist']              = $row ['audio_artist'];
                $object ['object'] ['url']                 = stream ( $row ['audio_id'] );
                $object ['object'] ['image']               = audiolist_images ( $row ['album_id'] );
                $object ['object'] ['track_number']        = track_number ( $row ['audio_id'] );
                $object ['object'] ['title']               = $row ['audio_title'];
                $object ['object'] ['duration']            = track_duration ( $row ['audio_id'] );
                $object ['object'] ['metadata_state']      = metadata_state ( $row ['audio_id'] );
                $object ['object'] ['sources']             = sources ( $row ['audio_id'] );
                $object ['object'] ['play_count']          = play_count ( $row ['audio_id'] );
                $object ['object'] ['viewer_love']         = viewer_love ( $row ['audio_id'] );
                $object ['object'] ['last_loved']          = last_loved ( $row ['audio_id'] );
                $object ['object'] ['recent_loves']        = recent_loves ( $row ['audio_id'] );
                $object ['object'] ['aliases']             = aliases ( $row ['audio_id'] );
                $object ['object'] ['loved_count']         = $row ['loved'];
                $object ['object'] ['id']                  = $row ['audio_id'];
                $object ['object'] ['tags']                = tags ( $row ['audio_id'] );
                $object ['object'] ['trending_rank_today'] = trending_rank_today ( $row ['audio_id'] );
                $object ['object'] ['user_love']           = array ("username" => $row ['username'],
                    "created_on" => date( 'D M d Y H:i:s O', strtotime( $row ['created_on'] ) ) );

                $activities [] = $object;

            }

            $i ++;

            if ($i >= $page_end)
                break;

        }

        $buffer ['status_text'] = "OK";
        $buffer ['status_code'] = "200";
        $buffer ['results']     = $total_results;
        $buffer ['start']       = $start;
        $buffer ['total']       = $total_results;
        $buffer ['activities']  = $activities;

    } elseif ($action == "following") {

        $row = $db->super_query ( "SELECT user_id FROM vass_users WHERE username = '" . $username . "' LIMIT 0,1" );

        $sql_result = $db->query ( "SELECT vass_friendship.follower_id, vass_users.username, vass_users.name, vass_users.bio, vass_users.website, vass_users.total_loved, vass_users.location, vass_users.total_loved, vass_users.total_following, vass_users.total_followers, vass_users.avatar, vass_background.color, vass_background.image, vass_background.position, vass_background.repeat, vass_background.use_image FROM vass_users LEFT JOIN vass_background ON vass_users.user_id = vass_background.user_id LEFT JOIN vass_friendship ON vass_users.user_id = vass_friendship.follower_id WHERE vass_friendship.user_id = '" . $row ['user_id'] . "';" );

        $start = $_REQUEST ['start'];
        $results_number = $_REQUEST ['results'];

        $page_start = $start;

        $page_end = $start + $results_number;

        $total_results = $db->num_rows ( $sql_result );

        $i = 0;

        while ( $result = $db->get_row ( $sql_result ) ) {

            if ($i >= $page_start) {

                $buffer                      = $result;
                $buffer ['is_beta_tester']   = false;
                $buffer ['viewer_following'] = viewer_following ( $result ['follower_id'] );
                $buffer ['import_feeds']     = import_feeds ( $result ['user_id'] );
                $buffer ['image']            = avatar ( $result ['avatar'], $result ['user_id'] );

                $row = $db->super_query ( "SELECT vass_background.color, vass_background.image, vass_background.position, vass_background.repeat, vass_background.use_image FROM vass_background WHERE vass_background.user_id = '" . $result ['user_id'] . "';" );

                if ($row ['image']) {
                    $use_image = true;
                    $is_default = false;
                } else {
                    $is_default = true;
                    $use_image = false;
                }

                $buffer ['background']                = $row;
                $buffer ['background'] ['is_default'] = $is_default;
                $buffer ['background'] ['use_image']  = $use_image;

                unset ( $buffer ['password'] );

                $following [] = $buffer;

            }
            $i ++;

            if ($i >= $page_end)
                break;
        }

        $buffer = array ("status_code" => 200,
            "status_text"  => "OK",
            "results"      => 20,
            "start"        => $start,
            "following"    => $following,
            "total"        => $total_results );

    } elseif ($action == "followers") {

        $row = $db->super_query ( "SELECT user_id FROM vass_users WHERE username = '" . $username . "' LIMIT 0,1" );

        $sql_result = $db->query ( "SELECT vass_users.user_id, vass_users.username, vass_users.name, vass_users.bio, vass_users.website, vass_users.total_loved, vass_users.location, vass_users.total_loved, vass_users.total_following, vass_users.total_followers, vass_users.avatar, vass_background.color, vass_background.image, vass_background.position, vass_background.repeat, vass_background.use_image FROM vass_users LEFT JOIN vass_background ON vass_users.user_id = vass_background.user_id LEFT JOIN vass_friendship ON vass_users.user_id = vass_friendship.user_id WHERE vass_friendship.follower_id = '" . $row ['user_id'] . "';" );

        $start          = $_REQUEST ['start'];
        $results_number = $_REQUEST ['results'];

        $page_start     = $start;
        $page_end       = $start + $results_number;

        $total_results = $db->num_rows ( $sql_result );

        $i = 0;

        while ( $result = $db->get_row ( $sql_result ) ) {

            if ($i >= $page_start) {

                $buffer                      = $result;
                $buffer ['is_beta_tester']   = false;
                $buffer ['viewer_following'] = viewer_following ( $result ['user_id'] );
                $buffer ['import_feeds']     = import_feeds ( $result ['user_id'] );
                $buffer ['image']            = avatar ( $result ['avatar'], $result ['user_id'] );

                $row = $db->super_query ( "SELECT vass_background.color, vass_background.image, vass_background.position, vass_background.repeat, vass_background.use_image FROM vass_background WHERE vass_background.user_id = '" . $result ['user_id'] . "';" );

                if ($row ['image']) {
                    $use_image = true;
                    $is_default = false;
                } else {
                    $is_default = true;
                    $use_image = false;
                }

                $buffer ['background']                = $row;
                $buffer ['background'] ['is_default'] = $is_default;
                $buffer ['background'] ['use_image']  = $use_image;

                unset ( $buffer ['password'] );

                $followers [] = $buffer;

            }
            $i ++;

            if ($i >= $page_end)
                break;
        }

        $buffer = array ("status_code"  => 200,
            "status_text"  => "OK",
            "results"      => 20,
            "start"        => $start,
            "followers"    => $followers,
            "total"        => $total_results );

    } elseif ($action == "tastemakers") {

        $sql_result = $db->query ( "SELECT DISTINCT vass_friendship.follower_id, vass_users.username, vass_users.name, vass_users.bio, vass_users.website, vass_users.total_loved, vass_users.location, vass_users.total_loved, vass_users.total_following, vass_users.total_followers, vass_users.avatar FROM vass_users LEFT JOIN vass_friendship ON vass_users.user_id = vass_friendship.follower_id" );

        $total_results = $db->num_rows ( $sql_result );

        while ( $result = $db->get_row ( $sql_result ) ) {


            $buffer                      = $result;
            $buffer ['is_beta_tester']   = false;
            $buffer ['viewer_following'] = viewer_following ( $result ['user_id'] );
            $buffer ['import_feeds']     = import_feeds ( $result ['user_id'] );
            $buffer ['image']            = avatar ( $result ['avatar'], $result ['user_id'] );

            $row = $db->super_query ( "SELECT vass_background.color, vass_background.image, vass_background.position, vass_background.repeat, vass_background.use_image FROM vass_background WHERE vass_background.user_id = '" . $result ['user_id'] . "';" );

            if ($row ['image']) {
                $use_image = true;
                $is_default = false;
            } else {
                $is_default = true;
                $use_image = false;
            }

            $row['is_default']     = $is_default;
            $row['use_image']      = $use_image;
            $buffer ['background'] = $row;
            $following []          = $buffer;

        }

        if (! $following)
            $following = "";

        $buffer = array ("status_code" => 200,
            "status_text" => "OK",
            "results"     => 20,
            "start"       => $start,
            "following"   => $following,
            "total"       => $total_results );


    } elseif ($action == "notifications") {

        $buffer = '{
			    "status_text": "OK",
			    "status_code": 200,
			    "results": 0,
			    "sites": [],
			    "start": 0,
			    "total": 0
			}';

    } elseif ($action == "playlist") {

        $row = $db->super_query ( "SELECT user_id FROM vass_users WHERE username = '" . $username . "' LIMIT 0,1" );

        $sql_result = $db->query ( "SELECT vass_playlists.name, vass_playlists.date, vass_playlists.id AS playlist_id, vass_playlists.cover, vass_playlists.descr,
		vass_users.username
		FROM vass_playlists LEFT JOIN vass_users ON vass_playlists.user_id = vass_users.user_id WHERE vass_playlists.user_id = '" . $row ['user_id'] . "';" );

        $start          = $_REQUEST ['start'];
        $results_number = $_REQUEST ['results'];

        $page_start     = $start;
        $page_end       = $start + $results_number;

        $total_results = $db->num_rows ( $sql_result );

        $i = 0;

        while ( $result = $db->get_row ( $sql_result ) ) {

            if ($i >= $page_start) {

                $buffer = $result;

                $playlists [] = $buffer;

            }
            $i ++;

            if ($i >= $page_end)
                break;
        }

        $buffer = array ("status_code" => 200,
            "status_text" => "OK",
            "results"     => 20,
            "start"       => $start,
            "playlists"   => $playlists,
            "total"       => $total_results );

    } elseif ($username) {

        $row = $db->super_query ( "SELECT vass_friendship.user_id, vass_users.user_id, vass_users.name, vass_users.bio, vass_users.website, vass_users.total_loved, vass_users.location, vass_users.total_loved, vass_users.total_following, vass_users.total_followers, vass_users.avatar, vass_background.color, vass_background.image, vass_background.position, vass_background.repeat, vass_background.use_image FROM vass_users LEFT JOIN vass_background ON vass_users.user_id = vass_background.user_id LEFT JOIN vass_friendship ON vass_users.user_id = vass_friendship.follower_id WHERE vass_users.username = '" . $username . "';" );

        if (! $row ['user_id']) {

            header( 'HTTP/1.0 403 Not Found' );
            $buffer ['status_code'] = 400;
            $buffer ['status_text'] = "Unknown user {$username}.";

        } else {

            $buffer ['status_code']               = 200;
            $buffer ['status_text']               = "OK";
            $buffer ['user']                      = $row;
            $buffer ['user'] ['is_beta_tester']   = false;
            $buffer ['user'] ['viewer_following'] = viewer_following ( $row ['user_id'] );
            $buffer ['user'] ['import_feeds']     = import_feeds ( $row ['user_id'] );
            $buffer ['user'] ['image']            = avatar ( $row ['avatar'], $row ['user_id'] );

            $total_playlist = $db->super_query ( "SELECT COUNT(*) AS count FROM vass_playlists WHERE user_id = '" . $member_id['user_id'] . "';" );

            $buffer ['user'] ['total_playlist'] = $total_playlist['count'];

            $row = $db->super_query ( "SELECT vass_background.color, vass_background.image, vass_background.position, vass_background.repeat, vass_background.use_image FROM vass_background WHERE vass_background.user_id = '" . $row ['user_id'] . "';" );

            if ($row ['image']) {
                $use_image = true;
                $is_default = false;
            } else {
                $is_default = true;
                $use_image = false;
            }

            $buffer ['user'] ['background']                = $row;
            $buffer ['user'] ['background'] ['is_default'] = $is_default;
            $buffer ['user'] ['background'] ['use_image']  = $use_image;

            unset ( $buffer ['user'] ['password'] );

        }
    }

    header( 'Cache-Control: no-cache, must-revalidate' );
    header( 'Content-type: application/json' );

    print json_encode( $buffer );

} elseif ($type == "profile") {

    if (! $logged) {

        header( "HTTP/1.0 401 UNAUTHORIZED" );

        $buffer ['status_code'] = 401;
        $buffer ['status_text'] = "Authentication required.";

    } else {

        $username = $db->safesql ( $_REQUEST ['username'] );

        $username = preg_replace( "/[^a-zA-Z0-9\s]/", "", $username );

        $action = $db->safesql ( $_REQUEST ['action'] );

        if ($action == "maybe-friends") {

            $buffer = array ("status_code" => 200,
                "status_text" => "OK",
                "results"     => 20,
                "start"       => $start,
                "users"       => array (),
                "total"       => $total_results );

        } elseif ($action == "notifications") {

            $buffer = array ("status_code" => 200,
                "status_text" => "OK",
                "results"     => 20,
                "start"       => $start,
                "sites"       => array (),
                "total"       => $total_results );

        } elseif ($action == "follow") {

            $row = $db->super_query ( "SELECT user_id, avatar FROM vass_users WHERE username = '" . $username . "' LIMIT 0,1" );

            $db->query ( "INSERT IGNORE INTO vass_friendship SET user_id = '" . $member_id ['user_id'] . "', follower_id = '" . $row ['user_id'] . "'" );

            $db->query ( "UPDATE vass_users SET total_following  = total_following+1 WHERE user_id = '" . $member_id ['user_id'] . "'" );

            $db->query ( "UPDATE vass_users SET total_followers  = total_followers+1 WHERE user_id = '" . $row ['user_id'] . "'" );

            $buffer = array ("status_code" => 200, "status_text" => "OK", "user" => array ("username" => $username, "image" => avatar ( $row ['avatar'], $row ['user_id'] ) ) );

        } elseif ($action == "unfollow") {

            $row = $db->super_query ( "SELECT user_id, avatar FROM vass_users WHERE username = '" . $username . "' LIMIT 0,1" );

            $db->query ( "DELETE FROM vass_friendship WHERE user_id = '" . $member_id ['user_id'] . "' AND follower_id = '" . $row ['user_id'] . "'" );

            $db->query ( "UPDATE vass_users SET total_following  = total_following-1 WHERE user_id = '" . $member_id ['user_id'] . "'" );

            $db->query ( "UPDATE vass_users SET total_followers  = total_followers-1 WHERE user_id = '" . $row ['user_id'] . "'" );

            $buffer = array ("status_code" => 200, "status_text" => "OK", "user" => array ("username" => $username, "image" => avatar ( $row ['avatar'], $row ['user_id'] ) ) );

        } else {

            $buffer ['status_code'] = 200;
            $buffer ['status_text'] = "OK";

            $buffer ['user']                      = $member_id;
            $buffer ['user'] ['is_beta_tester']   = false;
            $buffer ['user'] ['viewer_following'] = viewer_following ( $member_id ['user_id'] );
            $buffer ['user'] ['import_feeds']     = import_feeds ( $member_id ['user_id'] );
            $buffer ['user'] ['image']            = avatar ( $member_id ['avatar'], $member_id ['user_id'] );

            $row = $db->super_query ( "SELECT vass_background.color, vass_background.image, vass_background.position, vass_background.repeat, vass_background.use_image FROM vass_background WHERE vass_background.user_id = '" . $member_id ['user_id'] . "';" );

            if ($row ['image']) {
                $use_image = "true";
                $is_default = "false";
            } else {
                $is_default = "true";
                $use_image = "false";
            }

            $buffer ['user'] ['background']                = $row;
            $buffer ['user'] ['background'] ['is_default'] = $is_default;
            $buffer ['user'] ['background'] ['use_image']  = $use_image;

            unset ( $buffer ['user'] ['password'] );

        }
    }

    header( 'Cache-Control: no-cache, must-revalidate' );

    header( 'Content-type: application/json' );

    print json_encode( $buffer );

} elseif ($type == "me") {

    if (! $member_id) {

        header( "HTTP/1.0 401 UNAUTHORIZED" );

        $buffer ['status_code'] = 401;
        $buffer ['status_text'] = "Authentication required.";

    } else {

        $buffer ['status_code'] = 200;
        $buffer ['status_text'] = "OK";

        $buffer ['user']                      = $member_id;
        $buffer ['user'] ['is_beta_tester']   = false;
        $buffer ['user'] ['viewer_following'] = false;
        $buffer ['user'] ['import_feeds']     = import_feeds ( $member_id ['user_id'] );
        $buffer ['user'] ['image']            = avatar ( $member_id ['avatar'], $member_id ['user_id'] );

        $row = $db->super_query ( "SELECT vass_background.color, vass_background.image, vass_background.position, vass_background.repeat, vass_background.use_image FROM vass_background WHERE vass_background.user_id = '" . $member_id ['user_id'] . "';" );

        if ($row ['image']) {
            $use_image = true;
            $is_default = false;
        } else {
            $is_default = true;
            $use_image = false;
        }

        $buffer ['user'] ['background']                = $row;
        $buffer ['user'] ['background'] ['is_default'] = $is_default;
        $buffer ['user'] ['background'] ['use_image']  = $use_image;

        unset ( $buffer ['user'] ['password'] );

    }

    header( 'Cache-Control: no-cache, must-revalidate' );

    header( 'Content-type: application/json' );

    print json_encode( $buffer );
} elseif ($type == "trending") {

    $_DATE = date( "d", time() );

    $genre = $db->safesql($_REQUEST['genre']);

    $genre_id = $db->super_query("SELECT id FROM vass_genres WHERE name LIKE '%". $genre . "%'");

    $trending_day = $db->query ( "SELECT COUNT(*) AS count, audio_id FROM vass_analz WHERE `time` > '" . date( "Y-m-d", (time()  - 7*24*3600) ) . "' GROUP BY audio_id ORDER by count DESC LIMIT 0,20" );

    $i = 1;

    while ( $trending = $db->get_row ( $trending_day ) ) {

        if(!empty($genre)){
            $row = $db->super_query ( "SELECT vass_audios.id audio_id, vass_audios.artist_id,
				vass_audios.id AS audio_id, vass_audios.loved, vass_audios.title AS audio_title,
				vass_artists.id AS artist_id, vass_artists.name AS audio_artist, vass_albums.name AS audio_album, vass_albums.id AS album_id
				FROM vass_audios LEFT JOIN vass_albums ON vass_audios.album_id = vass_albums.id LEFT JOIN
					vass_artists ON vass_audios.artist_id = vass_artists.id WHERE vass_audios.id = '" . $trending ['audio_id'] . "' AND vass_audios.tags REGEXP '[[:<:]]" . $genre_id['id'] . "[[:>:]]'" );
        }else{
            $row = $db->super_query ( "SELECT vass_audios.id AS audio_id, vass_audios.artist_id,
				vass_audios.id AS audio_id, vass_audios.loved, vass_audios.title AS audio_title,
				vass_artists.id AS artist_id, vass_artists.name AS audio_artist, vass_albums.name AS audio_album, vass_albums.id AS album_id
				FROM vass_audios LEFT JOIN vass_albums ON vass_audios.album_id = vass_albums.id LEFT JOIN
				vass_artists ON vass_audios.artist_id = vass_artists.id WHERE vass_audios.id = '" . $trending ['audio_id'] . "'" );
        }


        if($row ['audio_id']){
            $audios ['album']               = $row ['audio_album'];
            $audios ['artists']             = artists ( $row ['artist_id'] );
            $audios ['similar_artists']     = similar_artists ( $row ['audio_id'] );
            $audios ['buy_link']            = null;
            $audios ['artist']              = $row ['audio_artist'];
            $audios ['url']                 = stream ( $row ['audio_id'] );
            $audios ['image']               = audiolist_images ( $row ['album_id'] );
            $audios ['artist_image']        = artist_images ( $row ['album_id'], $row ['artist_id'] );
            $audios ['track_number']        = track_number ( $row ['audio_id'] );
            $audios ['title']               = $row ['audio_title'];
            $audios ['duration']            = track_duration ( $row ['audio_id'] );
            $audios ['metadata_state']      = metadata_state ( $row ['audio_id'] );
            $audios ['play_count']          = play_count ( $row ['audio_id'] );
            $audios ['sources']             = sources ( $row ['audio_id'] );
            $audios ['viewer_love']         = viewer_love ( $row ['audio_id'] );
            $audios ['last_loved']          = last_loved ( $row ['audio_id'] );
            $audios ['recent_loves']        = recent_loves ( $row ['audio_id'] );
            $audios ['aliases']             = aliases ( $row ['audio_id'] );
            $audios ['loved_count']         = $row ['loved'];
            $audios ['id']                  = $row ['audio_id'];
            $audios ['tags']                = tags ( $row ['audio_id'] );
            $audios ['trending_rank_today'] = $i;
            $result ['audios'] []           = $audios;
        }else $result ['audios'] = array();
        $i ++;
    }

    $result ['trending_date'] = date( "Y-m-d", time() );
    $result ['status_text']   = "OK";
    $result ['status_code']   = "200";
    $result ['start']         = 0;

    if ($result && $i > 19)
        cache ( "trending/" . $_DATE, json_encode( $result ) );

    echo json_encode( $result );

    //} else {

    // echo $trending_json;

    //}

} elseif ($type == "genres") {

    $genres = $db->query("SELECT name FROM `vass_genres` WHERE stick= 1 ORDER by id ASC LIMIT 0,20");
    
    $total_results = $db->num_rows ( $genres );
    
    while($genre = $db->get_row($genres)){
        $result ['genres'][] = $genre['name'];
    }
    
    $result ['status_text']   = "OK";
    $result ['status_code']   = "200";
    $result ['total']         = $total_results;

    echo json_encode( $result );

} elseif ($type == "search") {
    $qtxt = $_REQUEST [q];

    $qtxt = stripUnicode ( strtolower($qtxt) );

    $qtxt = makekeyword ( $qtxt );

    $sql_result = $db->query ( "SELECT DISTINCT vass_audios.artist_id, vass_audios.id AS audio_id, vass_audios.title AS audio_title, vass_audios.loved, vass_artists.name AS audio_artist, vass_albums.name AS audio_album, vass_albums.id AS album_id FROM vass_audios LEFT JOIN vass_albums ON vass_audios.album_id = vass_albums.id LEFT JOIN vass_artists ON vass_audios.artist_id = vass_artists.id WHERE LOWER(vass_audios.title) LIKE '%$qtxt%' or LOWER(vass_artists.name) LIKE '%$qtxt%' or LOWER(vass_albums.name) LIKE '%$qtxt%'" );

    $start = $_REQUEST ['start'];
    $results_number = $_REQUEST ['results'];

    $page_start = $start;

    $page_end = $start + $results_number;

    $total_results = $db->num_rows ( $sql_result );

    $i = 0;

    while ( $row = $db->get_row ( $sql_result ) ) {

        if ($i >= $page_start) {

            $audios ['album']               = $row ['audio_album'];
            $audios ['artists']             = artists ( $row ['artist_id'] );
            $audios ['similar_artists']     = similar_artists ( $row ['audio_id'] );
            $audios ['buy_link']            = null;
            $audios ['artist']              = $row ['audio_artist'];
            $audios ['url']                 = stream ( $row ['audio_id'] );
            $audios ['image']               = audiolist_images ( $row ['album_id'] );
            $audios ['track_number']        = track_number ( $row ['audio_id'] );
            $audios ['title']               = $row ['audio_title'];
            $audios ['duration']            = track_duration ( $row ['audio_id'] );
            $audios ['metadata_state']      = metadata_state ( $row ['audio_id'] );
            $audios ['play_count']          = play_count ( $row ['audio_id'] );
            $audios ['sources']             = sources ( $row ['audio_id'] );
            $audios ['viewer_love']         = viewer_love ( $row ['audio_id'] );
            $audios ['last_loved']          = last_loved ( $row ['audio_id'] );
            $audios ['recent_loves']        = recent_loves ( $row ['audio_id'] );
            $audios ['aliases']             = aliases ( $row ['audio_id'] );
            $audios ['loved_count']         = $row ['loved'];
            $audios ['id']                  = $row ['audio_id'];
            $audios ['tags']                = tags ( $row ['audio_id'] );
            $audios ['trending_rank_today'] = trending_rank_today ( $row ['audio_id'] );
            $result ['audios'] []           = $audios;
        }

        $i ++;

        if ($i >= $page_end)
            break;

    }

    $result ['status_text'] = "OK";
    $result ['status_code'] = "200";
    $result ['results']     = $total_results;
    $result ['start']       = $start;
    $result ['total']       = $total_results;

    $db->close ();

    header( 'Cache-Control: no-cache, must-revalidate' );

    header( 'Content-type: application/json' );

    print json_encode( $result );
} elseif ($type == "love") {

    if (! $logged) {

        header( "HTTP/1.0 401 UNAUTHORIZED" );

        $buffer ['status_code'] = 401;
        $buffer ['status_text'] = "Authentication required.";

    } else {

        $audioid = $db->safesql ( $_REQUEST ['audioid'] );
        $action = $db->safesql ( $_REQUEST ['action'] );

        if ($action == "love") {

            $audio_id = $db->safesql ( $_REQUEST ['audioid'] );

            $db->query ( "INSERT IGNORE INTO vass_audio_love SET audio_id = '" . $audioid . "', user_id= '" . $member_id ['user_id'] . "', created_on = '" . date( "Y-m-d H:i:s", time() ) . "'" );

            $db->query ( "UPDATE vass_audios SET loved = loved+1, last_loved = '" . date( "Y-m-d H:i:s", time() ) . "' WHERE id = '" . $audioid . "'" );

            $db->query ( "UPDATE vass_users SET total_loved = total_loved+1 WHERE user_id= '" . $member_id ['user_id'] . "'" );

            $buffer ['status_code'] = 200;
            $buffer ['status_text'] = "Added audio to lover.";

        } elseif ($action == "unlove") {

            $db->query ( "DELETE FROM vass_audio_love WHERE audio_id = '" . $audioid . "' AND user_id= '" . $member_id ['user_id'] . "'" );

            $db->query ( "UPDATE vass_audios SET loved = loved-1 WHERE id = '" . $audioid . "'" );

            $db->query ( "UPDATE vass_users SET total_loved = total_loved-1 WHERE user_id= '" . $member_id ['user_id'] . "'" );

            $buffer ['status_code'] = 200;
            $buffer ['status_text'] = "removed audio to lover.";
            $buffer ['audio'] ['id'] = $audioid;

        }
    }

    $db->close ();

    header( 'Cache-Control: no-cache, must-revalidate' );

    header( 'Content-type: application/json' );

    print json_encode( $buffer );
} elseif ($type == "now_playing") {
    if (! $_REQUEST ['audioid']) {

        header( 'HTTP/1.0 404 Not Found' );

        $buffer = '{
    				"status_code": 401,
    				"status_text": "Authentication required"
				}';

    } else {

        $audio_id = intval( $_REQUEST ['audioid'] );

        $db->query ( "UPDATE vass_audios SET played=played+1 WHERE id = '" . $audio_id . "'" );

        $db->query ( "INSERT INTO vass_analz SET `time`= '$_TIME', audio_id = '" . $audio_id . "'" );

    }
    $buffer = array ("status_code" => "200" );

    print json_encode( $buffer );

} elseif ($type == "audio") {
    if (! $_REQUEST ['audioid']) {

        header( 'HTTP/1.0 404 Not Found' );

        $buffer ['status_code'] = 404;
        $buffer ['status_text'] = "NOT FOUND.";

    } else {

        $audio_id = $db->safesql ( $_REQUEST ['audioid'] );

        $row = $db->super_query ( "SELECT vass_audios.artist_id, vass_audios.created_on, vass_audios.artist_id, vass_artists.tag AS tags, vass_audios.id AS audio_id, vass_audios.title AS audio_title,
		vass_audios.loved, vass_artists.name AS audio_artist, vass_albums.name AS audio_album, vass_albums.id AS album_id
		FROM vass_audios LEFT JOIN vass_albums ON vass_audios.album_id = vass_albums.id  LEFT JOIN vass_artists ON
		vass_audios.artist_id = vass_artists.id WHERE vass_audios.id = '" . $audio_id . "' LIMIT 0,1" );

        if ($row ['audio_title']) {

            $audios ['album']               = $row ['audio_album'];
            $audios ['artists']             = artists ( $row ['artist_id'] );
            $audios ['created_on']          = date( 'D M d Y H:i:s O', strtotime( $row ['created_on'] ) );
            $audios ['artists']             = artists ( $row ['artist_id'] );
            $audios ['similar_artists']     = similar_artists ( $row ['tags'] );
            $audios ['buy_link']            = null;
            $audios ['artist']              = $row ['audio_artist'];
            $audios ['url']                 = stream ( $row ['audio_id'] );
            $audios ['image']               = audiolist_images ( $row ['album_id'] );
            $audios ['track_number']        = track_number ( $row ['audio_id'] );
            $audios ['title']               = $row ['audio_title'];
            $audios ['duration']            = track_duration ( $row ['audio_id'] );
            $audios ['metadata_state']      = metadata_state ( $row ['audio_id'] );
            $audios ['sources']             = sources ( $row ['audio_id'] );
            $audios ['play_count']          = play_count ( $row ['audio_id'] );
            $audios ['viewer_love']         = viewer_love ( $row ['audio_id'] );
            $audios ['last_loved']          = last_loved ( $row ['audio_id'] );
            $audios ['recent_loves']        = recent_loves ( $row ['audio_id'] );
            $audios ['aliases']             = aliases ( $row ['audio_id'] );
            $audios ['loved_count']         = $row ['loved'];
            $audios ['id']                  = $row ['audio_id'];
            $audios ['tags']                = tags ( $row ['tags'] );
            $audios ['trending_rank_today'] = trending_rank_today ( $row ['audio_id'] );
            $audios ['user_love']           = null;

            $buffer ['status_code'] = 200;
            $buffer ['status_text'] = "OK";
            $buffer ['audio']        = $audios;

        } else {

            header( 'HTTP/1.0 404 Not Found' );

            $buffer ['status_code'] = 404;
            $buffer ['status_text'] = "NOT FOUND.";

        }
    }

    header( 'Cache-Control: no-cache, must-revalidate' );

    header( 'Content-type: application/json' );

    print json_encode( $buffer );
} elseif ($type == "sotd") {
    echo '{
	    "status_text": "OK",
	    "status_code": 200,
	    "results": 1,
	    "sites": [
	    ],
	    "start": 0,
	    "total": 5
	}';

} elseif ($type == "settings") {
    if (! $logged) {

        header( "HTTP/1.0 401 UNAUTHORIZED" );

        $buffer ['status_code'] = 401;
        $buffer ['status_text'] = "Authentication required.";

    } else {

        $username = $db->safesql ( $_REQUEST ['username'] );
        $action   = $db->safesql ( $_REQUEST ['action'] );

        if ($action == "maybe-friends") {

            $buffer ['status_code'] = 200;
            $buffer ['users'] []    = null;
            $buffer ['status_text'] = "OK";
            $buffer ['results']     = 20;
            $buffer ['start']       = 0;
            $buffer ['total']       = 0;

        } elseif( $action == "tastemakers" ){

            $sql_result = $db->query("SELECT vass_users.user_id, vass_users.username, vass_users.name, vass_users.bio, vass_users.website, vass_users.total_loved, vass_users.location, vass_users.total_loved, vass_users.total_following, vass_users.total_followers, vass_users.avatar, vass_background.color, vass_background.image, vass_background.position, vass_background.repeat, vass_background.use_image
FROM vass_users
LEFT JOIN vass_background ON vass_users.user_id = vass_background.user_id
ORDER BY vass_users.user_id LIMIT 0,10");


            $start          = $_REQUEST['start'];
            $results_number = $_REQUEST ['results'];

            $page_start = $start;
            $page_end   = $start + $results_number;

            $total_results = $db->num_rows( $sql_result );

            $i = 0;


            while ($result = $db->get_row($sql_result)){

                if ( $i >= $page_start ){


                    $folow = $db->super_query("SELECT follower_id FROM vass_friendship WHERE follower_id = '" . $result['user_id'] . "'");

                    $buffer                     = $result;
                    $buffer['is_beta_tester']   = false;
                    $buffer['viewer_following'] = viewer_following($folow['follower_id']);
                    $buffer['import_feeds']     = import_feeds($result['user_id']);
                    $buffer['image']            = avatar( $result['avatar'], $result['username'] );


                    $row = $db->super_query("SELECT vass_background.color, vass_background.image, vass_background.position, vass_background.repeat, vass_background.use_image FROM vass_background WHERE vass_background.user_id = '" . $result['user_id'] . "';");

                    if( $row['image'] ) {
                        $use_image = true;
                        $is_default = false;
                    } else {
                        $is_default = true;
                        $use_image = false;
                    }

                    $buffer['background']               = $row;
                    $buffer['background']['is_default'] = $is_default;
                    $buffer['background']['use_image']  = $use_image;

                    unset($buffer['password']);


                    $following[] = $buffer;

                }
                $i++;

                if ($i >= $page_end) break;
            }


            $buffer = array("status_code"  => 200,
                "status_text" => "OK",
                "results"     => 20,
                "start"       => $start,
                "following"   => $following,
                "total"       => $total_results);

        }elseif ($action == "search") {

            $keyword = $db->safesql ( $_REQUEST ['q'] );

            $sql_result = $db->query ( "SELECT DISTINCT vass_friendship.follower_id, vass_users.username, vass_users.name, vass_users.bio, vass_users.website, vass_users.total_loved, vass_users.location, vass_users.total_loved, vass_users.total_following, vass_users.total_followers, vass_users.avatar, vass_background.color, vass_background.image, vass_background.position, vass_background.repeat, vass_background.use_image FROM vass_users LEFT JOIN vass_background ON vass_users.user_id = vass_background.user_id LEFT JOIN vass_friendship ON vass_users.user_id = vass_friendship.follower_id WHERE vass_users.name LIKE '%$keyword%' or vass_users.email LIKE '%$keyword%' or vass_users.username LIKE '%$keyword%'" );

            $start = $_REQUEST ['start'];
            $results_number = $_REQUEST ['results'];

            $page_start = $start;

            $page_end = $start + $results_number;

            $total_results = $db->num_rows ( $sql_result );

            $i = 0;

            while ( $result = $db->get_row ( $sql_result ) ) {

                if ($i >= $page_start) {

                    $buffer                      = $result;
                    $buffer ['is_beta_tester']   = false;
                    $buffer ['viewer_following'] = viewer_following ( $result ['follower_id'] );
                    $buffer ['import_feeds']     = import_feeds ( $result ['user_id'] );
                    $buffer ['image']            = avatar ( $result ['avatar'], $result ['user_id'] );

                    $row = $db->super_query ( "SELECT vass_background.color, vass_background.image, vass_background.position, vass_background.repeat, vass_background.use_image FROM vass_background WHERE vass_background.user_id = '" . $result ['user_id'] . "';" );

                    if ($row ['image']) {
                        $use_image = true;
                        $is_default = false;
                    } else {
                        $is_default = true;
                        $use_image = false;
                    }

                    $buffer ['background']                = $row;
                    $buffer ['background'] ['is_default'] = $is_default;
                    $buffer ['background'] ['use_image']  = $use_image;

                    unset ( $buffer ['password'] );

                    $users [] = $buffer;

                }
                $i ++;

                if ($i >= $page_end)
                    break;
            }

            if (! $users)
                $users = "";

            $buffer = array ("status_code" => 200,
                "status_text" => "OK",
                "results"     => 20,
                "start"       => $start,
                "users"       => $users,
                "total"       => $total_results );

        } elseif ($action == "notifications") {

            $buffer = '{
					    "status_text": "OK",
					    "status_code": 200,
					    "results": 0,
					    "sites": [],
					    "start": 0,
					    "total": 0
					}';

        } elseif ($action == "background") {

            $color     = $db->safesql ( $_POST ['color'] );
            $image     = $db->safesql ( $_POST ['image'] );
            $position  = $db->safesql ( $_POST ['position'] );
            $repeat    = $db->safesql ( $_POST ['repeat'] );
            $use_image = $db->safesql ( $_POST ['use_image'] );

            if ($color)
                $db->query ( "INSERT INTO vass_background SET `user_id` = '" . $member_id ['user_id'] . "', `color` = '$color', `image` = '$image', `position` = '$position', `repeat` = '$repeat', `use_image` = '$use_image' ON DUPLICATE KEY UPDATE `color` = '$color', `image` = '$image', `position` = '$position', `repeat` = '$repeat', `use_image` = '$use_image';" );

            $buffer ['status_code']               = 200;
            $buffer ['status_text']               = "OK";
            $buffer ['user']                      = $member_id;
            $buffer ['user'] ['is_beta_tester']   = false;
            $buffer ['user'] ['viewer_following'] = viewer_following ( $member_id ['user_id'] );
            $buffer ['user'] ['import_feeds']     = import_feeds ( $member_id ['user_id'] );
            $buffer ['user'] ['image']            = avatar ( $member_id ['avatar'], $member_id ['user_id'] );

            $row = $db->super_query ( "SELECT vass_background.color, vass_background.image, vass_background.position, vass_background.repeat, vass_background.use_image FROM vass_background WHERE vass_background.user_id = '" . $member_id ['user_id'] . "';" );

            if ($row ['image']) {
                $use_image = true;
                $is_default = false;
            } else {
                $is_default = true;
                $use_image = false;
            }

            $buffer ['user'] ['background']                = $row;
            $buffer ['user'] ['background'] ['is_default'] = $is_default;
            $buffer ['user'] ['background'] ['use_image']  = $use_image;

            unset ( $buffer ['user'] ['password'] );

        } elseif ($action == "email") {

            $buffer = array ("status_code"  => 200,
                "status_text"  => "OK",
                "user"         => array ("email" => $member_id ['email'] ) );

        } elseif ($action == "services") {

            if (isset ( $_REQUEST ['force'] ) && $_REQUEST ['force'] == "remove") {

                if (isset ( $_REQUEST ['social'] ))
                    $social = $_REQUEST ['social'];

                if ($social == "facebook") {
                    $buffer ['status_code'] = 200;
                    $buffer ['removed']     = "facebook";
                    $buffer ['status_text'] = "OK";
                    $db->query("DELETE FROM vass_facebook WHERE user_id = '" . $member_id['user_id'] . "'");
                } elseif ($social == "twitter") {
                    $db->query("DELETE FROM vass_twitter WHERE user_id = '" . $member_id['user_id'] . "'");
                    $buffer ['status_code'] = 200;
                    $buffer ['removed']     = "twitter";
                    $buffer ['status_text'] = "OK";
                }
            } else {

                $row = $db->super_query ( "SELECT screen_id AS twitter_screen_id, screen_name AS twitter_screen_name, date AS twitter_date FROM vass_twitter WHERE user_id = '" . $member_id ['user_id'] . "'" );

                if ($row ['twitter_screen_id']) {
                    $service ['twitter'] ['name']         = $row ['twitter_screen_name'];
                    $service ['twitter'] ['last_refresh'] = date( 'D M d Y H:i:s O', strtotime( $row ['twitter_date'] ) );
                    $service ['twitter'] ['pic'] = "http://api.twitter.com/1/users/profile_image?screen_name=" . $row ['twitter_screen_name'] . "&size=bigger";
                    $service ['twitter'] ['lookup_id'] = $row ['twitter_screen_name'];
                    $service ['twitter'] ['type']      = "twitter";
                    $service ['twitter'] ['added_on']  = date( 'D M d Y H:i:s O', strtotime( $row ['twitter_date'] ) );
                }

                $row = $db->super_query ( "SELECT screen_id AS facebook_screen_id, screen_name AS facebook_screen_name, date AS facebook_date FROM vass_facebook WHERE user_id = '" . $member_id ['user_id'] . "'" );

                if ($row ['facebook_screen_id']) {

                    $service ['facebook'] ['name']         = $row ['facebook_screen_name'];
                    $service ['facebook'] ['last_refresh'] = date( 'D M d Y H:i:s O', strtotime( $row ['facebook_date'] ) );
                    $service ['facebook'] ['pic'] = "https://graph.facebook.com/" . $row ['facebook_screen_id'] . "/picture?type=large";
                    $service ['facebook'] ['lookup_id'] = $row ['facebook_screen_id'];
                    $service ['facebook'] ['type']      = "facebook";
                    $service ['facebook'] ['added_on']  = date( 'D M d Y H:i:s O', strtotime( $row ['facebook_date'] ) );
                }

                if (! $service)
                    $service = "";

                $buffer = array ("status_code" => 200,
                    "status_text" => "OK",
                    "services"    => $service );
            }

        } elseif ($action == "password") {

            $password             = $db->safesql ( md5( $_POST ['password'] ) );
            $new_password         = $db->safesql ( md5( $_POST ['new_password'] ) );
            $confirm_new_password = $db->safesql ( md5( $_POST ['confirm_new_password'] ) );

            $row = $db->super_query ( "SELECT user_id FROM vass_users WHERE user_id = '" . $member_id ['user_id'] . "' AND password = '" . $password . "'" );

            if (! $row ['user_id']) {
                $buffer = array ("status_code" => "400",
                    "status_text" => "Old password is incorrect" );

            } else {

                $db->query ( "UPDATE vass_users SET password = '$new_password' WHERE user_id = '" . $member_id ['user_id'] . "'" );

                $buffer = array ("status_code" => 200,
                    "status_text" => "OK",
                    "success" => true );

            }

        } elseif ($action == "profile") {

            $bio      = $db->safesql ( $_POST ['bio'] );
            $location = $db->safesql ( $_POST ['location'] );
            $name     = $db->safesql ( $_POST ['name'] );
            $website  = $db->safesql ( $_POST ['website'] );

            $db->query ( "UPDATE vass_users SET bio = '$bio', location = '$location', name = '$name', website = '$website' WHERE user_id = '" . $member_id ['user_id'] . "'" );

            $row = $db->super_query ( "SELECT vass_users.name, vass_users.bio, vass_users.website, vass_users.total_loved, vass_users.location, vass_users.total_loved, vass_users.total_following, vass_users.total_followers, vass_background.color, vass_background.image, vass_background.position, vass_background.repeat, vass_background.use_image FROM vass_users LEFT JOIN vass_background ON vass_users.user_id = vass_background.user_id WHERE vass_users.username = '" . $username . "';" );

            $buffer ['status_code'] = 200;
            $buffer ['status_text'] = "OK";

            $buffer ['user']                      = $row;
            $buffer ['user'] ['is_beta_tester']   = false;
            $buffer ['user'] ['viewer_following'] = viewer_following ( $member_id ['user_id'] );
            $buffer ['user'] ['import_feeds']     = import_feeds ( $member_id ['user_id'] );
            $buffer ['user'] ['image']            = avatar ( $member_id ['avatar'], $member_id ['user_id'] );

            $row = $db->super_query ( "SELECT vass_background.color, vass_background.image, vass_background.position, vass_background.repeat, vass_background.use_image FROM vass_background WHERE vass_background.user_id = '" . $member_id ['user_id'] . "';" );

            if ($row ['image']) {
                $use_image = true;
                $is_default = false;
            } else {
                $is_default = true;
                $use_image = false;
            }

            $buffer ['user'] ['background']                = $row;
            $buffer ['user'] ['background'] ['is_default'] = $is_default;
            $buffer ['user'] ['background'] ['use_image']  = $use_image;

            unset ( $buffer ['user'] ['password'] );

        }
    }

    $db->close ();

    header( 'Cache-Control: no-cache, must-revalidate' );

    header( 'Content-type: application/json' );

    print json_encode( $buffer );
}elseif ($type == "playlist") {

    $action = $db->safesql ( $_REQUEST ['action'] );

    if ($action == "create") {
        if (! $logged) {

            header( "HTTP/1.0 401 UNAUTHORIZED" );

            $buffer ['status_code'] = 401;
            $buffer ['status_text'] = "Authentication required.";

            die();

        }
        $name  = $db->safesql ( $_REQUEST ['name'] );
        $descr = $db->safesql ( $_REQUEST ['descr'] );

        if( ! $name ) die();
        else{
            $db->query("INSERT INTO vass_playlists (user_id, `date`, name, descr) VALUES ('" . $member_id ['user_id'] . "', '$_TIME', '$name', '$descr');");

            $playlist_id = $db->insert_id();

            $buffer ['playlist_id']             = $playlist_id;
            $buffer ['name']                    = $name;
            $buffer ['status_text']             = "OK";
            $buffer ['status_code']             = "200";
            $buffer ['playlist']['name']        = $name;
            $buffer ['playlist']['date']        = $_TIME;
            $buffer ['playlist']['playlist_id'] = $playlist_id;
            $buffer ['playlist']['name']        = $name;
            $buffer ['playlist']['cover']       = 0;
            $buffer ['playlist']['descr']       = $descr;
            $buffer ['playlist']['username']    = $member_id ['username'];
        }

    }elseif ($action == "edit") {
        if (! $logged) {

            header( "HTTP/1.0 401 UNAUTHORIZED" );

            $buffer ['status_code'] = 401;
            $buffer ['status_text'] = "Authentication required.";

            die();

        }
        $name  = $db->safesql ( $_REQUEST ['name'] );
        $descr = $db->safesql ( $_REQUEST ['descr'] );

        $id = intval( $_REQUEST ['id'] );

        if( ! $name ) die();
        else{
            $db->query("UPDATE vass_playlists SET name= '$name', descr = '$descr' WHERE user_id = '" . $member_id ['user_id'] . "' AND id = '$id';");

            $buffer ['name']        = $name;
            $buffer ['descr']       = $descr;
            $buffer ['status_text'] = "OK";
            $buffer ['status_code'] = "200";
        }

    }elseif ($action == "doresort") {
        if (! $logged) {

            header( "HTTP/1.0 401 UNAUTHORIZED" );

            $buffer ['status_code'] = 401;
            $buffer ['status_text'] = "Authentication required.";

            die();

        }

        $playlist_id = intval($_REQUEST ['playlist_id']);

        $items = $_REQUEST ['audios'];

        $items = explode("==", $items);

        $i=1;

        foreach($items as $item){

            if(!empty($item)){
                $db->query ("UPDATE vass_audio_playlist SET pos = '$i' WHERE audio_id = '" . $db->safesql($item) . "' AND playlist_id = '$playlist_id'");
                $i++;
            }
            $buffer ['status_text'] = "OK";
            $buffer ['status_code'] = "200";
        }


    }elseif ($action == "remove") {
        if (! $logged) {

            header( "HTTP/1.0 401 UNAUTHORIZED" );

            $buffer ['status_code'] = 401;
            $buffer ['status_text'] = "Authentication required.";

            die();

        }
        $id = intval( $_REQUEST ['id'] );

        if( ! $id ) die();
        else{
            $db->query("DELETE FROM vass_playlists WHERE user_id = '" . $member_id ['user_id'] . "' AND id = '$id';");
            $buffer ['playlist_id'] = $id;
            $buffer ['status_text'] = "OK";
            $buffer ['status_code'] = "200";
        }

    }elseif($action == "info"){

        $id = intval( $_REQUEST ['id'] );

        $row = $db->super_query ( "SELECT vass_playlists.name, vass_playlists.date, vass_playlists.id AS playlist_id, vass_playlists.cover, vass_playlists.descr,
		vass_users.username
		FROM vass_playlists LEFT JOIN vass_users ON vass_playlists.user_id = vass_users.user_id WHERE vass_playlists.id = '" . $id . "';" );

        $playlist = $row;

        $buffer = array ("status_code" => 200,
            "status_text" => "OK",
            "playlist"    => $playlist );

    }elseif($action == "addaudio"){
        if (! $logged) {

            header( "HTTP/1.0 401 UNAUTHORIZED" );

            $buffer ['status_code'] = 401;
            $buffer ['status_text'] = "Authentication required.";

            die();

        }
        $playlist_id = intval( $_REQUEST ['playlist_id'] );

        $audio_id = $db->safesql ( $_REQUEST ['audio_id'] );

        if($audio_id && $playlist_id) $db->query("INSERT IGNORE INTO vass_audio_playlist (audio_id, playlist_id) VALUES ('$audio_id', '$playlist_id')");

        $buffer = array ("status_code" => 200,
            "status_text" => "OK");

    }elseif($action == "addfromqueue"){
        if (! $logged) {

            header( "HTTP/1.0 401 UNAUTHORIZED" );

            $buffer ['status_code'] = 401;
            $buffer ['status_text'] = "Authentication required.";

            die();

        }
        $playlist_id = intval( $_REQUEST ['playlist_id'] );

        $json = file_get_contents( 'php://input' );
        $obj  = json_decode( $json );

        foreach ($obj as $item) {

            $audio_id = $item->id;

            if($audio_id && $playlist_id) $db->query("INSERT IGNORE INTO vass_audio_playlist (audio_id, playlist_id) VALUES ('$audio_id', '$playlist_id')");

        }

        $buffer = array ("status_code" => 200,
            "status_text" => "OK");

    }elseif($action == "remove_audio"){

        if (! $logged) {

            header( "HTTP/1.0 401 UNAUTHORIZED" );

            $buffer ['status_code'] = 401;
            $buffer ['status_text'] = "Authentication required.";

            die();

        }
        $playlist_id = intval( $_REQUEST ['playlist_id'] );

        $audio_id = $db->safesql ( $_REQUEST ['audio_id'] );

        if($audio_id && $playlist_id) $db->query("DELETE FROM vass_audio_playlist WHERE audio_id = '$audio_id' AND playlist_id = '$playlist_id'");

        $buffer = array ("status_code" => 200,
            "status_text" => "OK");

    }elseif($action == "audios"){

        $playlist_id = intval( $_REQUEST ['id'] );

        $owner = $db->super_query("SELECT user_id FROM vass_playlists WHERE id = '$playlist_id'");

        $sql_query = $db->query ( "SELECT vass_audios.artist_id, vass_audios.id AS audio_id, vass_audios.loved, vass_audios.title AS audio_title,
				vass_artists.id AS artist_id, vass_artists.name AS audio_artist, vass_albums.name AS audio_album, vass_albums.id AS album_id
				FROM vass_audio_playlist LEFT JOIN vass_audios ON vass_audio_playlist.audio_id = vass_audios.id LEFT JOIN vass_albums ON vass_audios.album_id = vass_albums.id LEFT JOIN
				vass_artists ON vass_audios.artist_id = vass_artists.id WHERE vass_audio_playlist.playlist_id = '" . $playlist_id . "' ORDER BY vass_audio_playlist.pos ASC LIMIT $start,$results_number");

        $total_results = $db->super_query ( "SELECT COUNT(*) AS count
				FROM vass_audio_playlist LEFT JOIN vass_audios ON vass_audio_playlist.audio_id = vass_audios.id LEFT JOIN vass_albums ON vass_audios.album_id = vass_albums.id LEFT JOIN
				vass_artists ON vass_audios.artist_id = vass_artists.id WHERE vass_audio_playlist.playlist_id = '" . $playlist_id . "'" );

        while ( $row = $db->get_row ($sql_query) ) {

            if($logged && $member_id['user_id'] == $owner ['user_id']) {
                $audios['playlist_owner'] = true;
                $audios['playlist_id']    = $playlist_id;
            }
            if($row['artist_id']) {

                $audios ['album']  = $row ['audio_album'];
                $audios ['url']    = stream ( $row ['audio_id'] );
                $audios ['image']  = audiolist_images ( $row ['album_id'], $row['artist_id'] );
                $audios ['artist'] = $row ['audio_artist'];

            }else{

                $audios ['album']  = $row ['description'];
                $audios ['url']    = stream ( $row ['audio_id'] );
                $audios ['image']  = audiolist_images ( $row ['artwork_url'], $row['artist_id'] );
                $audios ['artist'] = $row ['tag_list'];

            }
            $audios ['artists']             = artists ( $row ['artist_id'] );
            $audios ['similar_artists']     = similar_artists ( $row ['audio_id'] );
            $audios ['buy_link']            = null;
            $audios ['track_number']        = track_number ( $row ['audio_id'] );
            $audios ['title']               = $row ['audio_title'];
            $audios ['duration']            = track_duration ( $row ['audio_id'] );
            $audios ['metadata_state']      = metadata_state ( $row ['audio_id'] );
            $audios ['sources']             = sources ( $row ['audio_id'] );
            $audios ['play_count']          = play_count ( $row ['audio_id'] );
            $audios ['viewer_love']         = viewer_love ( $row ['audio_id'] );
            $audios ['last_loved']          = last_loved ( $row ['audio_id'] );
            $audios ['recent_loves']        = recent_loves ( $row ['audio_id'] );
            $audios ['aliases']             = aliases ( $row ['audio_id'] );
            $audios ['loved_count']         = $row ['loved'];
            $audios ['id']                  = $row ['audio_id'];
            $audios ['tags']                = tags ( $row ['audio_id'] );
            $audios ['trending_rank_today'] = trending_rank_today ( $row ['audio_id'] );
            $audios ['user_love']           = null;
            $result ['audios'] []           = $audios;
        }

        $result ['status_text'] = "OK";
        $result ['status_code'] = "200";
        $result ['results']     = $total_results['count'];
        $result ['start']       = $start;
        $result ['total']       = $total_results['count'];

        $buffer = $result;
    }elseif($action == "resort"){

        $playlist_id = intval( $_REQUEST ['id'] );

        $owner = $db->super_query("SELECT user_id FROM vass_playlists WHERE id = '$playlist_id'");

        $sql_query = $db->query ( "SELECT vass_audios.artist_id, vass_audios.id AS audio_id, vass_audios.loved, vass_audios.title AS audio_title,
				vass_artists.id AS artist_id, vass_artists.name AS audio_artist, vass_albums.name AS audio_album, vass_albums.id AS album_id
				FROM vass_audio_playlist LEFT JOIN vass_audios ON vass_audio_playlist.audio_id = vass_audios.id LEFT JOIN vass_albums ON vass_audios.album_id = vass_albums.id LEFT JOIN
				vass_artists ON vass_audios.artist_id = vass_artists.id WHERE vass_audio_playlist.playlist_id = '" . $playlist_id . "' ORDER BY vass_audio_playlist.pos ASC" );

        $total_results = $db->super_query ( "SELECT COUNT(*) AS count
				FROM vass_audio_playlist LEFT JOIN vass_audios ON vass_audio_playlist.audio_id = vass_audios.id LEFT JOIN vass_albums ON vass_audios.album_id = vass_albums.id LEFT JOIN
				vass_artists ON vass_audios.artist_id = vass_artists.id WHERE vass_audio_playlist.playlist_id = '" . $playlist_id . "'" );
        $i = 0;
        while ( $row = $db->get_row ($sql_query) ) {

            if($row['artist_id']) {

                $audios ['album']  = $row ['audio_album'];
                $audios ['url']    = stream ( $row ['audio_id'] );
                $audios ['image']  = audiolist_images ( $row ['album_id'], $row['artist_id'] );
                $audios ['artist'] = $row ['audio_artist'];

            }else{

                $audios ['album']  = $row ['description'];
                $audios ['url']    = stream ( $row ['audio_id'] );
                $audios ['image']  = audiolist_images ( $row ['artwork_url'], $row['artist_id'] );
                $audios ['artist'] = $row ['tag_list'];

            }
            $audios ['artists']             = artists ( $row ['artist_id'] );
            $audios ['similar_artists']     = similar_artists ( $row ['audio_id'] );
            $audios ['buy_link']            = null;
            $audios ['track_number']        = track_number ( $row ['audio_id'] );
            $audios ['title']               = $row ['audio_title'];
            $audios ['duration']            = track_duration ( $row ['audio_id'] );
            $audios ['metadata_state']      = metadata_state ( $row ['audio_id'] );
            $audios ['sources']             = sources ( $row ['audio_id'] );
            $audios ['play_count']          = play_count ( $row ['audio_id'] );
            $audios ['viewer_love']         = viewer_love ( $row ['audio_id'] );
            $audios ['last_loved']          = last_loved ( $row ['audio_id'] );
            $audios ['recent_loves']        = recent_loves ( $row ['audio_id'] );
            $audios ['aliases']             = aliases ( $row ['audio_id'] );
            $audios ['loved_count']         = $row ['loved'];
            $audios ['id']                  = $row ['audio_id'];
            $audios ['tags']                = tags ( $row ['audio_id'] );
            $audios ['trending_rank_today'] = trending_rank_today ( $row ['audio_id'] );
            $audios ['user_love']           = null;
            $audios ['position']            = $i;
            $audios['playlist_id']          = $playlist_id;
            $buffer []                     = $audios;
            $i++;
        }
    }

    header( 'Cache-Control: no-cache, must-revalidate' );

    header( 'Content-type: application/json' );

    print json_encode( $buffer );

}elseif ($type == "last_playlists") {

    $sql_result = $db->query ( "SELECT vass_playlists.name, vass_playlists.date, vass_playlists.id AS playlist_id, vass_playlists.cover, vass_playlists.descr,
	vass_users.username
	FROM vass_playlists LEFT JOIN vass_users ON vass_playlists.user_id = vass_users.user_id ORDER by vass_playlists.id DESC;" );

    $start = $_REQUEST ['start'];
    $results_number = $_REQUEST ['results'];

    $page_start = $start;

    $page_end = $start + $results_number;

    $total_results = $db->num_rows ( $sql_result );

    $i = 0;

    while ( $row = $db->get_row ( $sql_result ) ) {

        if ($i >= $page_start) {

            $object ['title']                   = $row ['name'];
            $object ['object'] ['title']        = $row ['name'];
            $object ['object'] ['album']        = $row ['description'];
            $object ['object'] ['track_number'] = track_number ( $row ['audio_id'] );
            $object ['object'] ['url']          = stream ( $row ['audio_id'] );
            $object ['object'] ['artist']       = $row ['username'];
            $object ['object'] ['duration']     = track_duration ( $row ['audio_id'] );
            $object ['object'] ['play_count']   = play_count ( $row ['audio_id'] );
            $object ['object'] ['playlist_id']  = $row ['playlist_id'];
            $object ['object'] ['cover']        = $row ['cover'];
            $object ['object'] ['descr']        = $row ['descr'];
            $object ['object'] ['created_on']   = date( 'D M d Y H:i:s O', strtotime( $row ['date'] ) );
            $object ['object'] ['owner']        = array ("username" => $row ['username'],
                "created_on" => date( 'D M d Y H:i:s O', strtotime( $row ['created_on'] ) ) );

            $activities [] = $object;

        }
        $i ++;
        if ($i >= $page_end)
            break;
    }

    $buffer ['status_text'] = "OK";
    $buffer ['status_code'] = "200";
    $buffer ['results']     = $total_results;
    $buffer ['start']       = $start;
    $buffer ['total']       = $total_results;
    $buffer ['activities']  = $activities;

    header( 'Cache-Control: no-cache, must-revalidate' );
    header( 'Content-type: application/json' );

    print json_encode( $buffer );

}elseif ($type == "artist") {

    $action = $db->safesql ( $_REQUEST ['action'] );

    if($action == "info"){

        $id = intval( $_REQUEST ['id'] );

        $row = $db->super_query ( "SELECT id AS artist_id, name, bio FROM vass_artists WHERE id = '" . $id . "';" );

        $artist = $row;

        $buffer = array ("status_code" => 200,
            "status_text" => "OK",
            "artist"      => $artist );

    }elseif($action == "audios"){

        $id = intval( $_REQUEST ['id'] );

        $sql_query = $db->query ( "SELECT vass_audios.artist_id, vass_audios.id AS audio_id, vass_audios.loved, vass_audios.title AS audio_title,
				vass_artists.id AS artist_id, vass_artists.name AS audio_artist, vass_albums.name AS audio_album, vass_albums.id AS album_id
				FROM vass_audios LEFT JOIN vass_albums ON vass_audios.album_id = vass_albums.id LEFT JOIN
				vass_artists ON vass_audios.artist_id = vass_artists.id WHERE vass_audios.artist_id REGEXP '[[:<:]]" . $id . "[[:>:]]' LIMIT $start,$results_number");

        $total_results = $db->super_query ( "SELECT COUNT(*) AS count
				FROM vass_audios LEFT JOIN vass_albums ON vass_audios.album_id = vass_albums.id LEFT JOIN
				vass_artists ON vass_audios.artist_id = vass_artists.id WHERE vass_audios.artist_id REGEXP '[[:<:]]" . $id . "[[:>:]]'" );

        while ( $row = $db->get_row ($sql_query) ) {

            $audios ['album']               = $row ['audio_album'];
            $audios ['url']                 = stream ( $row ['audio_id'] );
            $audios ['image']               = audiolist_images ( $row ['album_id'], $row['artist_id'] );
            $audios ['artist']              = $row ['audio_artist'];
            $audios ['artists']             = artists ( $row ['artist_id'] );
            $audios ['similar_artists']     = similar_artists ( $row ['audio_id'] );
            $audios ['buy_link']            = null;
            $audios ['track_number']        = track_number ( $row ['audio_id'] );
            $audios ['title']               = $row ['audio_title'];
            $audios ['duration']            = track_duration ( $row ['audio_id'] );
            $audios ['metadata_state']      = metadata_state ( $row ['audio_id'] );
            $audios ['sources']             = sources ( $row ['audio_id'] );
            $audios ['play_count']          = play_count ( $row ['audio_id'] );
            $audios ['viewer_love']         = viewer_love ( $row ['audio_id'] );
            $audios ['last_loved']          = last_loved ( $row ['audio_id'] );
            $audios ['recent_loves']        = recent_loves ( $row ['audio_id'] );
            $audios ['aliases']             = aliases ( $row ['audio_id'] );
            $audios ['loved_count']         = $row ['loved'];
            $audios ['id']                  = $row ['audio_id'];
            $audios ['tags']                = tags ( $row ['audio_id'] );
            $audios ['trending_rank_today'] = trending_rank_today ( $row ['audio_id'] );
            $audios ['user_love']           = null;
            $result ['audios'] []           = $audios;
        }

        $result ['status_text'] = "OK";
        $result ['status_code'] = "200";
        $result ['results']     = $total_results['count'];
        $result ['start']       = $start;
        $result ['total']       = $total_results['count'];

        $buffer = $result;
    }

    header( 'Cache-Control: no-cache, must-revalidate' );
    header( 'Content-type: application/json' );

    print json_encode( $buffer );

}elseif ($type == "album") {

    $action = $db->safesql ( $_REQUEST ['action'] );

    if($action == "info"){

        $id = intval( $_REQUEST ['id'] );

        $row = $db->super_query ( "SELECT vass_albums.descr, vass_albums.id AS album_id, vass_albums.name, vass_artists.id AS artist_id, vass_artists.name AS artist FROM vass_albums LEFT JOIN vass_artists ON vass_albums.artist_id =  vass_artists.id WHERE vass_albums.id = '" . $id . "';" );

        $album = $row;

        $buffer = array ("status_code" => 200,
            "status_text" => "OK",
            "album"       => $album );

    }elseif($action == "audios"){

        $id = intval( $_REQUEST ['id'] );

        $sql_query = $db->query ( "SELECT vass_audios.artist_id, vass_audios.id AS audio_id, vass_audios.loved, vass_audios.title AS audio_title,
				vass_artists.id AS artist_id, vass_artists.name AS audio_artist, vass_albums.name AS audio_album, vass_albums.id AS album_id
				FROM vass_audios LEFT JOIN vass_albums ON vass_audios.album_id = vass_albums.id LEFT JOIN
				vass_artists ON vass_audios.artist_id = vass_artists.id WHERE vass_audios.album_id = '$id' LIMIT $start,$results_number");

        $total_results = $db->super_query ( "SELECT COUNT(*) AS count FROM vass_audios WHERE album_id = '$id'" );

        while ( $row = $db->get_row ($sql_query) ) {

            $audios ['album']               = $row ['audio_album'];
            $audios ['url']                 = stream ( $row ['audio_id'] );
            $audios ['image']               = audiolist_images ( $row ['album_id'], $row['artist_id'] );
            $audios ['artist']              = $row ['audio_artist'];
            $audios ['artists']             = artists ( $row ['artist_id'] );
            $audios ['similar_artists']     = similar_artists ( $row ['audio_id'] );
            $audios ['buy_link']            = null;
            $audios ['track_number']        = track_number ( $row ['audio_id'] );
            $audios ['title']               = $row ['audio_title'];
            $audios ['duration']            = track_duration ( $row ['audio_id'] );
            $audios ['metadata_state']      = metadata_state ( $row ['audio_id'] );
            $audios ['sources']             = sources ( $row ['audio_id'] );
            $audios ['play_count']          = play_count ( $row ['audio_id'] );
            $audios ['viewer_love']         = viewer_love ( $row ['audio_id'] );
            $audios ['last_loved']          = last_loved ( $row ['audio_id'] );
            $audios ['recent_loves']        = recent_loves ( $row ['audio_id'] );
            $audios ['aliases']             = aliases ( $row ['audio_id'] );
            $audios ['loved_count']         = $row ['loved'];
            $audios ['id']                  = $row ['audio_id'];
            $audios ['tags']                = tags ( $row ['audio_id'] );
            $audios ['trending_rank_today'] = trending_rank_today ( $row ['audio_id'] );
            $audios ['user_love']           = null;
            $result ['audios'] []           = $audios;
        }

        $result ['status_text'] = "OK";
        $result ['status_code'] = "200";
        $result ['results']     = $total_results['count'];
        $result ['start']       = $start;
        $result ['total']       = $total_results['count'];

        $buffer = $result;
    }

    header( 'Cache-Control: no-cache, must-revalidate' );
    header( 'Content-type: application/json' );

    print json_encode( $buffer );

} elseif( $type == "userlist" ) {

    $start = $_REQUEST['start'];

    $sql_result = $db->query("SELECT vass_friendship.follower_id, vass_users.username, vass_users.name, vass_users.bio, vass_users.website, vass_users.total_loved, vass_users.location, vass_users.total_loved, vass_users.total_following, vass_users.total_followers, vass_users.avatar, vass_background.color, vass_background.image, vass_background.position, vass_background.repeat, vass_background.use_image FROM vass_users LEFT JOIN vass_background ON vass_users.user_id = vass_background.user_id LEFT JOIN vass_friendship ON vass_users.user_id = vass_friendship.follower_id LIMIT 0,50");

    $total_results = $db->num_rows( $sql_result );

    while ($result = $db->get_row($sql_result)){
        $buffer                     = $result;
        $buffer['is_beta_tester']   = false;
        $buffer['viewer_following'] = viewer_following($result['follower_id']);
        $buffer['import_feeds']     = import_feeds($result['user_id']);
        $buffer['image']            = avatar( $result['avatar'], $result['username'] );

        $row = $db->super_query("SELECT vass_background.color, vass_background.image, vass_background.position, vass_background.repeat, vass_background.use_image FROM vass_background WHERE vass_background.user_id = '" . $result['user_id'] . "';");

        if( $row['image'] ) {
            $use_image = true;
            $is_default = false;
        } else {
            $is_default = true;
            $use_image = false;
        }
        $buffer['background']               = $row;
        $buffer['background']['is_default'] = $is_default;
        $buffer['background']['use_image']  = $use_image;

        unset($buffer['password']);

        $following[] = $buffer;
    }


    $buffer = array("status_code" => 200,
        "status_text" => "OK",
        "results"     => 20,
        "start"       => $start,
        "users"       => $following,
        "total"       => $total_results);

    header( 'Cache-Control: no-cache, must-revalidate' );
    header( 'Content-type: application/json' );

    print json_encode( $buffer );

}elseif( $type == "albumlist" ){

    $letter = $db->safesql ( $_REQUEST ['letter'] );
    if(!empty($letter))
        $db->query("SELECT vass_albums.artist_id, vass_artists.name AS artist, vass_albums.id, vass_albums.id, vass_albums.view, vass_albums.name FROM vass_albums LEFT JOIN vass_artists ON vass_albums.artist_id = vass_artists.id WHERE vass_albums.name LIKE '$letter%' LIMIT 0,20");
    else
        $db->query("SELECT vass_albums.artist_id, vass_artists.name AS artist, vass_albums.id, vass_albums.id, vass_albums.view, vass_albums.name FROM vass_albums LEFT JOIN vass_artists ON vass_albums.artist_id = vass_artists.id LIMIT 0,20");

    while ($row = $db->get_row()){

        $buffer[] = $row;

    }

    if(!$buffer) $buffer = array();

    $buffer = array("status_code" => 200,
        "status_text" => "OK",
        "results"     => 20,
        "start"       => $start,
        "albums"      => $buffer,
        "total"       => $total_results);

    header( 'Cache-Control: no-cache, must-revalidate' );

    header( 'Content-type: application/json' );

    print json_encode( $buffer );
}elseif( $type == "artistlist" ){
    $letter = $db->safesql ( $_REQUEST ['letter'] );
    if(!empty($letter))
        $artists = $db->query("SELECT id, name FROM vass_artists WHERE name LIKE binary '$letter%'");
    else
        $artists = $db->query("SELECT id, name FROM vass_artists LIMIT 0,20");

    while ($row = $db->get_row($artists)){

        $num_audios = $db->super_query("SELECT COUNT(*) AS count FROM vass_audios WHERE artist_id = '" . $row['id'] . "'");

        $row['total_audios'] = $num_audios['count'];

        $buffer[] = $row;

    }

    if(!$buffer) $buffer = array();

    $buffer = array("status_code" => 200, "status_text" => "OK", "results" => 20, "start" => $start, "artists" => $buffer, "total" => $total_results);

    header( 'Cache-Control: no-cache, must-revalidate' );
    header( 'Content-type: application/json' );

    print json_encode( $buffer );
}elseif( $type == "suggess" ){

    $query = $db->safesql( $_REQUEST['query'] );

    $db->query("SELECT id, title FROM vass_audios WHERE title LIKE '%$query%' LIMIT 0,10");

    while ($row = $db->get_row()){

        $buffer[] = $row;

    }

    $buffer = array("status_code" => 200,
        "suggess"     => $buffer);

    header( 'Cache-Control: no-cache, must-revalidate' );
    header( 'Content-type: application/json' );

    print json_encode( $buffer );
}elseif( $type == "searchalbum" ){

    $q = $db->safesql ( $_REQUEST ['q'] );

    $db->query("SELECT vass_albums.artist_id, vass_artists.name AS artist, vass_albums.id, vass_albums.id, vass_albums.view, vass_albums.name FROM vass_albums LEFT JOIN vass_artists ON vass_albums.artist_id = vass_artists.id WHERE vass_albums.name LIKE '%$q%' LIMIT 0,20");

    while ($row = $db->get_row()){

        $buffer[] = $row;

    }

    if(!$buffer) $buffer = array();

    $buffer = array("status_code" => 200,
        "status_text" => "OK",
        "results"     => 20,
        "start"       => $start,
        "albums"      => $buffer,
        "total"       => $total_results);

    header( 'Cache-Control: no-cache, must-revalidate' );
    header( 'Content-type: application/json' );

    print json_encode( $buffer );

}elseif( $type == "searchartist" ){

    $q = $db->safesql ( $_REQUEST ['q'] );

    $db->query("SELECT id, name FROM vass_artists WHERE name LIKE '%$q%' LIMIT 0,5");

    while ($row = $db->get_row()){

        $buffer[] = $row;

    }

    if(!$buffer) $buffer = array();

    $buffer = array("status_code" => 200,
        "status_text" => "OK",
        "results"     => 20,
        "start"       => $start,
        "albums"      => $buffer,
        "total"       => $total_results);

    header( 'Cache-Control: no-cache, must-revalidate' );
    header( 'Content-type: application/json' );

    print json_encode( $buffer );

}elseif( $type == "artistallalbum" ){

    $id = intval( $_REQUEST ['id'] );

    $db->query("SELECT vass_albums.artist_id, vass_artists.name AS artist, vass_albums.id, vass_albums.id, vass_albums.view, vass_albums.name
	FROM vass_albums LEFT JOIN vass_artists ON vass_albums.artist_id = vass_artists.id WHERE vass_artists.id = '$id'");

    while ($row = $db->get_row()){

        $buffer[] = $row;

    }

    if(!$buffer) $buffer = array();

    $total_results = $db->super_query("SELECT COUNT(*) AS count FROM vass_albums WHERE artist_id = '$id'");
    $total_results = $total_results['count'];

    $buffer = array("status_code" => 200,
        "status_text" => "OK",
        "results"     => 20,
        "start"       => $start,
        "albums"      => $buffer,
        "total"       => $total_results);

    header( 'Cache-Control: no-cache, must-revalidate' );
    header( 'Content-type: application/json' );

    print json_encode( $buffer );
}elseif( $type == "artistallvideo" ){

    $id = intval( $_REQUEST ['id'] );

    $db->query("SELECT vass_videos.artist_id, vass_videos.tube_key, vass_artists.name AS artist, vass_videos.id, vass_videos.id, vass_videos.view, vass_videos.name
	FROM vass_videos LEFT JOIN vass_artists ON vass_videos.artist_id = vass_artists.id WHERE vass_artists.id = '$id'");

    while ($row = $db->get_row()){

        $buffer[] = $row;

    }

    if(!$buffer) $buffer = array();

    $total_results = $db->super_query("SELECT COUNT(*) AS count FROM vass_videos WHERE artist_id = '$id'");

    $total_results = intval($total_results['count']);

    $buffer = array("status_code" => 200,
        "status_text" => "OK",
        "results"     => 20,
        "start"       => $start,
        "videos"      => $buffer,
        "total"       => $total_results);

    header( 'Cache-Control: no-cache, must-revalidate' );
    header( 'Content-type: application/json' );

    print json_encode( $buffer );

}elseif( $type == "video" ){

    $id = intval( $_REQUEST ['id'] );

    $row = $db->super_query("SELECT vass_videos.artist_id, vass_videos.tube_key, vass_artists.name AS artist, vass_videos.id, vass_videos.id, vass_videos.view, vass_videos.name
	FROM vass_videos LEFT JOIN vass_artists ON vass_videos.artist_id = vass_artists.id WHERE vass_videos.id = '$id'");

    if(!$buffer) $buffer = array();

    $buffer = array("status_code" => 200,
        "status_text" => "OK",
        "results"     => 20,
        "video"       => $row);

    header( 'Cache-Control: no-cache, must-revalidate' );
    header( 'Content-type: application/json' );

    print json_encode( $buffer );

}
$db->close;
?>
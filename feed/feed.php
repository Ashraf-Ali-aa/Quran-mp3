<?php

@session_start();
define( 'ROOT_DIR', $_SERVER['DOCUMENT_ROOT'] );
define( 'INCLUDE_DIR', ROOT_DIR . '/includes' );

include      INCLUDE_DIR . '/config.inc.php';
require_once INCLUDE_DIR . '/class/_class_mysql.php';
require_once INCLUDE_DIR . '/db.php';
require_once INCLUDE_DIR . '/db_query.php';
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

    $top_week      = top_week($start, $results_number);
    $total_results = top_total_results();

    while ( $top = $db->get_row ( $top_week ) ) {

        if ($top ['audio_id']) {
            $row = top_results($top ['audio_id']);

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

    $album = aotw_album ($config['album_week']);

    $sql_result = aotw_sql_result ($config['album_week']);

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
        "albums"      => array ("description" => $album ['descr'],
            "artist"      => $album ['artist_name'],
            "date"        => date( 'D M d Y H:i:s O', strtotime( $album ['date'] ) ),
            "artwork_url" => $config['siteurl'] . "static/albums/" . $config['album_week'] ."_extralarge.jpg",
            "title"       => $album ['album_title'],
            "day"         => 20111005,
            "audios"       => $audios ) );

    header( 'Cache-Control: no-cache, must-revalidate' );

    header( 'Content-type: application/json' );

    print json_encode( $buffer );

} elseif ($type == "last_loved") {
    $sql_result = last_loved_sql_result ();

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

    $row           = genre_name ( $_REQUEST ['name'] , 1 );
    $sql_result    = genre_sql_result ($row ['id'], $start);
    $total_results = genre_results($row ['id']);

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

        $row = member_user_id ( $username );

        $sql_result = member_sql_result ($row ['user_id']);

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

        $row = member_user_id( $username );

        if ($username == "tastemakers") {

            $sql_result = member_tastemakers ();

        } else {

            $row        = member_user_id( $username );
            $sql_result = member_tastemakers_user_id ( $row ['user_id'] );
        }
        $start          = $_REQUEST ['start'];
        $results_number = $_REQUEST ['results'];
        $page_start     = $start;
        $page_end       = $start + $results_number;

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

        $row = member_user_id ( $username , 1 );

        $sql_result = member_following ($row ['user_id']);

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
                $buffer ['viewer_following'] = viewer_following ( $result ['follower_id'] );
                $buffer ['import_feeds']     = import_feeds ( $result ['user_id'] );
                $buffer ['image']            = avatar ( $result ['avatar'], $result ['user_id'] );

                $row = member_background_color ($result ['user_id']);

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

        $row        = member_user_id ( $username , 1 );
        $sql_result = member_followers($row ['user_id']);

        $start          = $_REQUEST ['start'];
        $results_number = $_REQUEST ['results'];
        $page_start     = $start;
        $page_end       = $start + $results_number;

        $total_results  = $db->num_rows ( $sql_result );

        $i = 0;

        while ( $result = $db->get_row ( $sql_result ) ) {

            if ($i >= $page_start) {

                $buffer                      = $result;
                $buffer ['is_beta_tester']   = false;
                $buffer ['viewer_following'] = viewer_following ( $result ['user_id'] );
                $buffer ['import_feeds']     = import_feeds ( $result ['user_id'] );
                $buffer ['image']            = avatar ( $result ['avatar'], $result ['user_id'] );

                $row = member_background_color ( $result ['user_id'] );

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

        $sql_result = member_tastemakers_users ();

        $total_results = $db->num_rows ( $sql_result );

        while ( $result = $db->get_row ( $sql_result ) ) {


            $buffer                      = $result;
            $buffer ['is_beta_tester']   = false;
            $buffer ['viewer_following'] = viewer_following ( $result ['user_id'] );
            $buffer ['import_feeds']     = import_feeds ( $result ['user_id'] );
            $buffer ['image']            = avatar ( $result ['avatar'], $result ['user_id'] );

            $row = member_background_color ( $result ['user_id'] );

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

        $row        = member_user_id  ($username , 1 );
        $sql_result = member_playlist ($row ['user_id']);

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

        $buffer = array (
            "status_code" => 200,
            "status_text" => "OK",
            "results"     => 20,
            "start"       => $start,
            "playlists"   => $playlists,
            "total"       => $total_results );

    } elseif ($username) {

        $row = member_username ($username);

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

            $total_playlist = member_playlist_total ( $member_id['user_id']);

            $buffer ['user'] ['total_playlist'] = $total_playlist['count'];

            $row = member_background_color ($row ['user_id']);

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

            $row = profile_user_id ( $username , 1 );

            profile_follow ($member_id ['user_id'], $row ['user_id']);

            $buffer = array (
                "status_code" => 200,
                "status_text" => "OK",
                "user"        => array ("username" => $username,
                    "image" => avatar ( $row ['avatar'], $row ['user_id'] ) ) );

        } elseif ($action == "unfollow") {

            $row = profile_user_id ( $username , 1 );

            profile_unfollow ($member_id ['user_id'], $row ['user_id']);

            $buffer = array ("status_code" => 200, "status_text" => "OK", "user" => array ("username" => $username, "image" => avatar ( $row ['avatar'], $row ['user_id'] ) ) );

        } else {

            $buffer ['status_code'] = 200;
            $buffer ['status_text'] = "OK";

            $buffer ['user']                      = $member_id;
            $buffer ['user'] ['is_beta_tester']   = false;
            $buffer ['user'] ['viewer_following'] = viewer_following ( $member_id ['user_id'] );
            $buffer ['user'] ['import_feeds']     = import_feeds ( $member_id ['user_id'] );
            $buffer ['user'] ['image']            = avatar ( $member_id ['avatar'], $member_id ['user_id'] );

            $row = member_background_color ( $member_id ['user_id']);

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

        $row = member_background_color ( $member_id ['user_id']);

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
    $start          = $_REQUEST ['start'];
    $results_number = $_REQUEST ['results'];

    $genre = $db->safesql($_REQUEST['genre']);

    $genre_id = genre_name ($genre);

    $trending_day = trending_day($start , $results_number);

    $i = 1;

    while ( $trending = $db->get_row ( $trending_day ) ) {

        if(!empty($genre)){
            $row = trending_genre ( $trending ['audio_id'] , $genre_id['id'] );
        }else{
            $row = trending_genre ( $trending ['audio_id'] );
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

    $genres = genre_sticky();

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

    $sql_result = search_query( $qtxt );

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
        $action  = $db->safesql ( $_REQUEST ['action'] );

        if ($action == "love") {

            $audio_id = $db->safesql ( $_REQUEST ['audioid'] );

            love_track($audio_id, $member_id ['user_id']);

            $buffer ['status_code'] = 200;
            $buffer ['status_text'] = "Added audio to lover.";

        } elseif ($action == "unlove") {

            unlove_track($audio_id, $member_id ['user_id']);

            $buffer ['status_code'] = 200;
            $buffer ['status_text'] = "removed audio to lover.";
            $buffer ['audio'] ['id'] = $audioid;

        }
    }

    $db->close ();

    header( 'Cache-Control: no-cache, must-revalidate' );
    header( 'Content-type: application/json' );

    print json_encode( $buffer );
} elseif ($type == "play_log") {
    if (! $_REQUEST [$_REQUEST ['username']]) {

        header( 'HTTP/1.0 404 Not Found' );

        $buffer ['status_code'] = 404;
        $buffer ['status_text'] = "Not Found";

    } else {
        $username = $db->safesql ( $_REQUEST ['username'] );
        $row = member_user_id ($name);
        //played_log($row ['user_id']);

    }
    $buffer = array ("status_code" => "200" );

    print json_encode( $buffer );

} elseif ($type == "now_playing") {
    if (! $_REQUEST ['audioid']) {

        header( 'HTTP/1.0 404 Not Found' );

        $buffer ['status_code'] = 404;
        $buffer ['status_text'] = "Not Found";

    } else {

        $audio_id = intval( $_REQUEST ['audioid'] );

        played_count ($audio_id, $_TIME );

    }
    $buffer = array ("status_code" => "200" );

    print json_encode( $buffer );

} elseif ($type == "download") {

    if (! $_REQUEST ['audioid']) {

        header( 'HTTP/1.0 404 Not Found' );

        $buffer ['status_code'] = 404;
        $buffer ['status_text'] = "Not Found";

    } else {

        $audio_id = intval( $_REQUEST ['audioid'] );

        download_count ($audio_id, $_TIME );

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

        $row = audio_track ( $audio_id );

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

            $sql_result = settings_tastemakers();

            $start          = $_REQUEST['start'];
            $results_number = $_REQUEST ['results'];

            $page_start = $start;
            $page_end   = $start + $results_number;

            $total_results = $db->num_rows( $sql_result );

            $i = 0;

            while ($result = $db->get_row($sql_result)){

                if ( $i >= $page_start ){

                    $folow = member_follow( $result['user_id'] );

                    $buffer                     = $result;
                    $buffer['is_beta_tester']   = false;
                    $buffer['viewer_following'] = viewer_following($folow['follower_id']);
                    $buffer['import_feeds']     = import_feeds($result['user_id']);
                    $buffer['image']            = avatar( $result['avatar'], $result['username'] );


                    $row = member_background_color( $result['user_id'] );

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

            $sql_result = search_follower ( $keyword );

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

                    $row = member_background_color ( $result ['user_id'] );

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

                member_update_background_color ($member_id, $color , $image , $position , $repeat , $use_image);

            $buffer ['status_code']               = 200;
            $buffer ['status_text']               = "OK";
            $buffer ['user']                      = $member_id;
            $buffer ['user'] ['is_beta_tester']   = false;
            $buffer ['user'] ['viewer_following'] = viewer_following ( $member_id ['user_id'] );
            $buffer ['user'] ['import_feeds']     = import_feeds ( $member_id ['user_id'] );
            $buffer ['user'] ['image']            = avatar ( $member_id ['avatar'], $member_id ['user_id'] );

            $row = member_background_color ($member_id ['user_id'] );

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
                    member_delete_facebook ( $member_id['user_id']);
                    $buffer ['status_code'] = 200;
                    $buffer ['removed']     = "facebook";
                    $buffer ['status_text'] = "OK";

                } elseif ($social == "twitter") {
                    member_delete_twitter  ( $member_id['user_id']);
                    $buffer ['status_code'] = 200;
                    $buffer ['removed']     = "twitter";
                    $buffer ['status_text'] = "OK";
                }
            } else {

                $row = member_twitter_id ($member_id ['user_id'] );

                if ($row ['twitter_screen_id']) {
                    $service ['twitter'] ['name']         = $row ['twitter_screen_name'];
                    $service ['twitter'] ['last_refresh'] = date( 'D M d Y H:i:s O', strtotime( $row ['twitter_date'] ) );
                    $service ['twitter'] ['pic'] = "http://api.twitter.com/1/users/profile_image?screen_name=" . $row ['twitter_screen_name'] . "&size=bigger";
                    $service ['twitter'] ['lookup_id'] = $row ['twitter_screen_name'];
                    $service ['twitter'] ['type']      = "twitter";
                    $service ['twitter'] ['added_on']  = date( 'D M d Y H:i:s O', strtotime( $row ['twitter_date'] ) );
                }

                $row = member_facebook_id ( $member_id ['user_id'] );

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

            $row = member_login ($member_id ['user_id'] , $password );

            if (! $row ['user_id']) {
                $buffer = array ("status_code" => "400",
                    "status_text" => "Old password is incorrect" );

            } else {
                member_login_update_password($member_id['user_id'], $new_password);

                $buffer = array ("status_code" => 200,
                    "status_text" => "OK",
                    "success" => true );

            }

        } elseif ($action == "profile") {

            $bio      = $db->safesql ( $_POST ['bio'] );
            $location = $db->safesql ( $_POST ['location'] );
            $name     = $db->safesql ( $_POST ['name'] );
            $website  = $db->safesql ( $_POST ['website'] );

            member_update_profile ($member_id, $bio, $location, $name, $website );

            $row = member_profile ( $username );

            $buffer ['status_code'] = 200;
            $buffer ['status_text'] = "OK";

            $buffer ['user']                      = $row;
            $buffer ['user'] ['is_beta_tester']   = false;
            $buffer ['user'] ['viewer_following'] = viewer_following ( $member_id ['user_id'] );
            $buffer ['user'] ['import_feeds']     = import_feeds ( $member_id ['user_id'] );
            $buffer ['user'] ['image']            = avatar ( $member_id ['avatar'], $member_id ['user_id'] );

            $row = member_background_color ( $member_id ['user_id'] );

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
            playlist_insert ($member_id ['user_id'], $_TIME, $name, $descr );


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
            playlist_update ($member_id ['user_id'], $id, $name, $descr);

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
                $item = $db->safesql($item);

                playlist_sort ($playlist_id, $item , $i );

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
            playlist_remove ($member_id ['user_id'], $id );

            $buffer ['playlist_id'] = $id;
            $buffer ['status_text'] = "OK";
            $buffer ['status_code'] = "200";
        }

    }elseif($action == "info"){

        $id = intval( $_REQUEST ['id'] );

        $row = playlist_info ($id);

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

        if($audio_id && $playlist_id){
            playlist_add_audio ($playlist_id , $audio_id);

        }

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

            if($audio_id && $playlist_id){
                playlist_add_audio ($playlist_id , $audio_id);
            }

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

        if($audio_id && $playlist_id){
            playlist_remove_audio ($playlist_id , $audio_id);
        }

        $buffer = array ("status_code" => 200,
            "status_text" => "OK");

    }elseif($action == "audios"){

        $playlist_id = intval( $_REQUEST ['id'] );

        $owner = playlist_creator ($playlist_id );

        $sql_query = playlist_results ($playlist_id , $start , $results_number );


        $total_results = playlist_total_results ($playlist_id );

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

        $owner         = playlist_creator ($playlist_id );
        $sql_query     = playlist_results_asc ($playlist_id );
        $total_results = playlist_total_results ($playlist_id );

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

    $sql_result = playlist_last ();

    $start          = $_REQUEST ['start'];
    $results_number = $_REQUEST ['results'];

    $page_start    = $start;
    $page_end      = $start + $results_number;
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

        $row = artist_id ($id );

        $artist = $row;

        $buffer = array ("status_code" => 200,
            "status_text" => "OK",
            "artist"      => $artist );

    }elseif($action == "audios"){

        $id = intval( $_REQUEST ['id'] );

        $sql_query = artist_results ($id , $start , $results_number );
        $total_results = artist_total_results ($id );

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

        $row = album_id ($id );

        $album = $row;

        $buffer = array ("status_code" => 200,
            "status_text" => "OK",
            "album"       => $album );

    }elseif($action == "audios"){

        $id = intval( $_REQUEST ['id'] );

        $sql_query = album_results ($id , $start , $results_number );


        $total_results = album_total_results ($id );

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

    $sql_result = userlist ();

    $total_results = $db->num_rows( $sql_result );

    while ($result = $db->get_row($sql_result)){
        $buffer                     = $result;
        $buffer['is_beta_tester']   = false;
        $buffer['viewer_following'] = viewer_following($result['follower_id']);
        $buffer['import_feeds']     = import_feeds($result['user_id']);
        $buffer['image']            = avatar( $result['avatar'], $result['username'] );

        $row = member_background_color ( $result['user_id'] );

        if( $row['image'] ) {
            $use_image  = true;
            $is_default = false;
        } else {
            $is_default = true;
            $use_image  = false;
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

    $letter    = $db->safesql ( $_REQUEST ['letter'] );
    $albumlist = albumlist ($letter);

    while ($row = $db->get_row($albumlist)){
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
} elseif( $type == "artistlist" ){
    $letter  = $db->safesql ( $_REQUEST ['letter'] );
    $artists = artistlist ($letter);


    while ($row = $db->get_row($artists)){

        $num_audios = audio_count ( $row['id'] );

        $row['total_audios'] = $num_audios['count'];

        $buffer[] = $row;

    }

    if(!$buffer) $buffer = array();

    $buffer = array("status_code" => 200,
        "status_text" => "OK",
        "results"     => 20,
        "start"       => $start,
        "artists"     => $buffer,
        "total"       => $total_results);

    header( 'Cache-Control: no-cache, must-revalidate' );
    header( 'Content-type: application/json' );

    print json_encode( $buffer );
}elseif( $type == "suggess" ){

    $query = $db->safesql( $_REQUEST['query'] );
    $related = related ( $query );

    while ($row = $db->get_row($related)){

        $buffer[] = $row;

    }

    $buffer = array("status_code" => 200,
        "suggess"     => $buffer);

    header( 'Cache-Control: no-cache, must-revalidate' );
    header( 'Content-type: application/json' );

    print json_encode( $buffer );
}elseif( $type == "searchalbum" ){

    $q = $db->safesql ( $_REQUEST ['q'] );
    
    $search_album = search_album ($q);

    while ($row = $db->get_row($search_album)){

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
    $search_artist = search_artist ($q);

    while ($row = $db->get_row($search_artist)){

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

    $artist_all_album = artist_all_albums ($id );

    while ($row = $db->get_row($artist_all_album)){

        $buffer[] = $row;

    }

    if(!$buffer) $buffer = array();

    $total_results = artist_total_albums ($id );
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
    
    $artist_all_videos = artist_all_videos ($id );

    while ($row = $db->get_row($artist_all_videos)){

        $buffer[] = $row;

    }

    if(!$buffer) $buffer = array();

    $total_results = artist_total_videos ($id );

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

    $row = videos ($id );

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
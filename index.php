<?php

@session_start ();
@ob_start ();
@ob_implicit_flush ( 0 );
@error_reporting ( E_ALL ^ E_NOTICE );
@ini_set ( 'display_errors', true );
@ini_set ( 'html_errors', false );
@ini_set ( 'error_reporting', E_ALL ^ E_NOTICE );

define ( 'ROOT_DIR', dirname ( __FILE__ ) );
define ( 'INCLUDE_DIR', ROOT_DIR . '/includes' );

@include (INCLUDE_DIR . '/config.inc.php');

require_once INCLUDE_DIR . '/class/_class_mysql.php';
require_once INCLUDE_DIR . '/db.php';
require_once ROOT_DIR    . '/modules/functions.php';
require_once INCLUDE_DIR . '/member.php';

if( $_REQUEST['oauth_token'] ){
	
	header("Location: " . $config['siteurl'] . "create-account/twitter/?oauth_token=" . $_REQUEST['oauth_token'] . "&oauth_verifier=" . $_REQUEST['oauth_verifier'] );
	
	die();
	
}
if( $_REQUEST['action'] == 'logout' ){
	
	$member_id = array ();
	
	set_cookie( "user_id", "", 0 );
	set_cookie( "login_pass", "", 0 );
	
	$_SESSION['user_id'] = 0;
	$_SESSION['login_pass'] = "";
	
	@session_destroy();
	@session_unset();
	
	header("Location: " . $config['siteurl'] );
	
	die();
	
}

//Load genres

$genres = $db->query("SELECT name FROM `vass_genres` WHERE stick= 1 ORDER by id ASC LIMIT 0,20");

while($genre = $db->get_row($genres)){

	$genre_list .= '"' . $genre['name'] . '",';
	
}

$genre_list = substr( $genre_list, 0, ( strLen( $genre_list ) - 1 ) );

if($logged){
	$playlists_query = $db->query("SELECT id, name FROM `vass_playlists` WHERE user_id = '" . $member_id ['user_id'] . "' ORDER by id DESC");
	
	while($row = $db->get_row($playlists_query)){
		$playlists .= "<li class=\"playlist_click\" data-playlist-id=\"{$row['id']}\"><span>{$row['name']}</span></li>";
		$playlists_queue .= "<span class=\"queue_to_playlist_dropdown_link\" data-playlist-id=\"{$row['id']}\">{$row['name']}</span>";
	}
}
$ajax = <<<HTML
<script language="javascript" type="text/javascript">
var player_root = '{$config['siteurl']}';
var player_api  = '{$config['siteurl']}/api';
var genre_list  = [{$genre_list}];
var mail_contact = 'ali@deen-ul-islam.org';

</script>
HTML;

$metatags = <<<HTML
<title>{$config['sitetitle']}</title>
<meta name="title" content="{$config['sitetitle']}" />
<meta property="og:title" name="title" content="{$config['sitetitle']}" />
<meta property="og:url" content="{$config['sitetitle']}" />
<meta property="og:image" content="{$config['facebook_icon']}" />
<meta property="og:site_name" content="{$config['sitetitle']}" />
<meta property="og:locale" content="en_US" />
<meta property="fb:app_id" content="{$config['facebook_app_id']}" />
<meta property="og:type" content="musician" />
<meta name="description" property="og:description" content="{$config['webdesc']}" />
<meta name="keywords" content="{$config['keywords']}" />
HTML;


$thistime = time();

$analytics = str_replace( "&#036;", "$", $config['analytics'] );
$analytics = str_replace( "&#123;", "{", $analytics );
$analytics = str_replace( "&#125;", "}", $analytics );

echo <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:og="http://opengraphprotocol.org/schema/" xmlns:fb="http://www.facebook.com/2008/fbml" lang="en" xml:lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
{$metatags}

	<link rel="apple-touch-icon" href="/assets/images/icon/icon.png" />
      <link rel="apple-touch-icon" sizes="72x72" href="/assets/images/icon/icon-72.png" />
      <link rel="apple-touch-icon" sizes="114x114" href="/assets/images/icon/icon@2x.png" />
      <link rel="apple-touch-icon" sizes="144x144" href="/assets/images/icon/icon-72@2x.png" />
      <link rel="icon" sizes="196x196" href="/assets/images/icon/icon-196.png">
      <link rel="icon" sizes="128x128" href="/assets/images/icon/icon-128.png">
      <link rel="apple-touch-icon" sizes="128x128" href="/assets/images/icon/icon-128.png">
      <link rel="apple-touch-icon-precomposed" sizes="128x128" href="/assets/images/icon/icon-128.png">
      
<link rel="apple-touch-startup-image" href="/assets/images/icon/splash/startup-320x460.png" media="screen and (max-device-width : 320px)">
<link rel="apple-touch-startup-image" href="/assets/images/icon/splash/startup-640x920.png" media="(max-device-width : 480px) and (-webkit-min-device-pixel-ratio : 2)">
<link rel="apple-touch-startup-image" href="/assets/images/icon/splash/startup-640x1096.png" media="(max-device-width : 548px) and (-webkit-min-device-pixel-ratio : 2)">
<link rel="apple-touch-startup-image" sizes="1024x748" href="/assets/images/icon/splash/startup-1024x748.png" media="screen and (min-device-width : 481px) and (max-device-width : 1024px) and (orientation : landscape)">
<link rel="apple-touch-startup-image" sizes="768x1004" href="/assets/images/icon/splash/startup-768x1004.png" media="screen and (min-device-width : 481px) and (max-device-width : 1024px) and (orientation : portrait)">
      

<link rel="stylesheet" href="/assets/css/app.css" type="text/css" media="screen" />
<!--[if IE 8]><link rel="stylesheet" type="text/css" media="screen" href="/assets/css/qmp3-app-ie8.css" /><![endif]-->
<link rel="shortcut icon" href="{$config['site_icon']}" />
{$ajax}
{$analytics}
</head>
<body>

<div class="loader">
<div id="page_loader"><span></span><span></span><span></span></div>
</div>
<div id="altContent">
	<h1>Quran Mp3 Player is the best way to listening and sharing the Quran.</h1>
	<h2>Collection million audios.</h2>
	<noscript>
	<ul class="links">
		<li><a href="{$config['siteurl']}trending">Trending</a></li>
		<li><a href="{$config['siteurl']}explore">Explore</a></li>
	</ul>
	</noscript>
	<ul class="links">
		<li><a href="{$config['siteurl']}trending">Trending</a></li>
		<li><a href="{$config['siteurl']}explore">Explore</a></li>
	</ul>
</div>

<div id="middle">
<aside id="sidebarLeft" class="sidebarLeft">
	<div id="left" class="scrollableArea">
		<div id="user_nav">
			<a class="left_row left_row_you" href="/trending" target="_self" id="left_row_trending" data-left-row="trending">
				<div class="left_row_status" id="left_row_status_trending"></div>
				<div class="left_row_icon" id="left_row_trending_icon"> </div>
				<div class="left_row_text">Trending</div>
			</a>
			<a class="left_row left_row_you" href="/explore/top-of-the-day" target="_self" id="left_row_explore" data-left-row="explore">
			<div class="left_row_status" id="left_row_status_explore"></div>
			<div class="left_row_icon" id="left_row_explore_icon"> </div>
			<div class="left_row_text">Explore</div>
			</a>
		<a class="left_row left_row_you" href="/artists" target="_self" id="left_row_artists" data-left-row="artists">
			<div class="left_row_status" id="left_row_status_artists"></div>
			<div class="left_row_icon" id="left_row_sites_icon"> </div>
			<div class="left_row_text">Reciters</div>
			</a>
		<a class="left_row left_row_you" href="/albums" target="_self" id="left_row_albums" data-left-row="Albums">
			<div class="left_row_status" id="left_row_status_explore"></div>
			<div class="left_row_icon" id="left_row_album_icon"> </div>
			<div class="left_row_text">Albums</div>
			</a>
	</div>
	</div>
	</aside>
	<section id="page" class="page">
	<div id="top">
<div id="leftBurger" class="slideRight menu-open">
</div>

 <a id="logo" href="/">Quran Mp3 Player</a>
	<div id="top_right"></div>
	<div id="top_search">
		<form id="top_search_form">
			<input type="text" id="top_search_input" placeholder="Search" />
		</form>
	</div>
	<div id="top_tip" class="top_tip_hidden"></div>
</div>
	<div id="right">
		<div id="home_section" class="display_none"></div>
		<div id="audio_list" class="display_none"></div>
		<div id="sites_list" class="display_none">sites</div>
		<div id="settings" class="display_none"></div>
	</div>
	<div id="current_playlist">
		<div id="current_playlist_header">
			<div id="current_playlist_clear">Clear Queue</div>
			<div id="current_playlist_save">Save Playlist ...</div>
			<div id="current_playlist_close"></div>
			<div id="queue_to_playlist_dropdown" class="display_none">
				<li class="queue_to_new_playlist_dropdown_link" data-playlist-id="NEW"><span>Create playlist</span></li>
				{$playlists_queue}
			</div>
		</div>
		<ul id="current_playlist_rows">
		</ul>
	</div>
	<div id="resort_playlist">
		<div id="resort_playlist_header">
			<div id="resort_playlist_close"></div>
		</div>
		<ul id="resort_playlist_rows">
		</ul>
	</div>
		<div id="right_cover" class="display_none"> 
		<!-- <div id="right_cover_loading">Loading...</div> --> 
	</div>
</div>
</section>
<!-- end middle -->
<div id="bottom">
	<div id="bottom_controls">
		<div id="prev_button" class="controls_button"></div>
		<div id="play_button" class="play_button controls_button"></div>
		<div id="next_button" class="controls_button"></div>
	</div>
	<div id="volume">
		<div id="volume_speaker" class="volume_on"></div>
		<div id="volume_back">
			<div id="volume_thumb"></div>
		</div>
	</div>
	<div id="display">
		<div id="display_coverart" class="hide_when_stopped display_none"></div>
		<div id="display_logo" class="hide_when_playing"></div>
		<div id="display_text" class="hide_when_stopped display_none"> <a id="display_audio"></a> <span id="display_artist"></span>
			<div id="display_album"></div>
			<a id="display_domain" target="_blank" outbound_type="bottom_player_source"></a> </div>
		<div id="display_time" class="hide_when_stopped display_none">
			<div id="display_time_count"></div>
			<div id="display_progress"></div>
			<div id="display_progressed"></div>
			<div id="display_seek_thumb"></div>
			<div id="display_time_total"></div>
		</div>
		<div id="current_audio_love_icon" class="hide_when_stopped display_none tooltip" tooltip="Love this audio"></div>
		<div id="current_audio_share_icon" class="hide_when_stopped display_none tooltip" tooltip="Share this audio"></div>
	</div>
	<div id="playlist_button" tooltip="Open or close Queue" class="tooltip"></div>
	<div id="shuffle_button" tooltip="Shuffe" class="tooltip"></div>
</div>
<div id="tooltip_display"></div>
<div id="top_right_dropdown" class="display_none"> <a href="/settings" class="top_right_dropdown_link">Settings</a> <a href="/settings/social" class="top_right_dropdown_link">Social</a> <a href="/sign-out" class="top_right_dropdown_link" id="sign_out_link">Logout</a> </div>
<div id="full_cover" class="display_none"></div>
<div id="tutorial_container" class="display_none"></div>
<script>loggedInUser = null;userBackground = {};</script>
<script type="text/javascript" src="/assets/js/core.js?{$thistime}"></script>
<script type="text/javascript" src="/assets/js/templates.js?{$thistime}"></script>
<script type="text/javascript" src="/assets/js/app.js?{$thistime}"></script>
<div id="dropdown-1" class="dropdown dropdown-tip">
	<ul class="dropdown-menu" id="all_playlist_menu">
		<li id="create_playlist_click"><span>Create Playlist</span></li>
		<li id="add_to_queue_click"><span>Add to Queue</span></li>
		<li class="dropdown-divider"></li>
		{$playlists}
	</ul>
</div>
</body>

</html>
HTML;
?>
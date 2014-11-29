<?php
function artists($tags){
	global $config, $logged, $db;
	
	if($tags){
		$artists_query = $db->query("SELECT id, name FROM vass_artists WHERE id IN ($tags) LIMIT 0,10;");
		
		while($artists = $db->get_row($artists_query)){
			$buffer[] = $artists;
		}
	}else $buffer = array();
	
	if(!$buffer) $buffer = array();
	
	return $buffer;
}
function get_content($url){
	global $config;
	if($config['content_type']){
		require_once INCLUDE_DIR . '/class/_class_curl.php';
		$curl = new Curl;
		return $curl->get($url);
	}else{
		return file_get_contents($url);
	}
}

function load_cache($prefix) {
	
	$filename = ROOT_DIR . '/cache/' . $prefix . '.tmp';
	
	return @file_get_contents( $filename );
}

function cache($prefix, $cache_text) {
	
	$filename = ROOT_DIR . '/cache/' . $prefix . '.tmp';
	
	$fp = fopen( $filename, 'wb+' );
	fwrite( $fp, $cache_text );
	fclose( $fp );
	
	@chmod( $filename, 0666 );

}
function clear_cache($cache_area = false) {
	
	$fdir = opendir( ROOT_DIR . '/cache' );
	
	while ( $file = readdir( $fdir ) ) {
		if( $file != '.' and $file != '..' and $file != '.htaccess' and $file != 'trending' ) {
			
			if( $cache_area ) {
				
				if( strpos( $file, $cache_area ) !== false ) @unlink( ROOT_DIR . '/cache/' . $file );
			
			} else {
				
				@unlink( ROOT_DIR . '/cache/' . $file );
			
			}
		}
	}
}
function set_cookie($name, $value, $expires) {
	if( $expires ) {	
		$expires = time() + ($expires * 86400);
	} else {	
		$expires = FALSE;
	}
	if( PHP_VERSION < 5.2 ) {	
		setcookie( $name, $value, $expires, "/", DOMAIN . "; HttpOnly" );
	} else {	
		setcookie( $name, $value, $expires, "/", DOMAIN, NULL, TRUE );	
	}
}

function CutContent($content, $from, $end){
	if ((($content and $from) and $end)){
		$r = explode($from, $content);
		if (isset($r[1])){
			$r = explode($end, $r[1]);
			return $r[0];
		}
		return;
	}
}


function viewer_following($user_id){
	global $config, $logged, $db, $member_id;
			
	if(!$logged || $member_id['user_id'] == $user_id){
		$buffer = false;
	}else{
		$viewer_following = $db->super_query("SELECT user_id FROM vass_friendship WHERE follower_id = '" . $user_id . "' AND user_id = '" . $member_id['user_id'] . "'");
		
		if( $viewer_following['user_id'] ) $buffer = true;
		
		else $buffer = false;
	}
	
	return $buffer;
}
function viewer_love($audio_id){
	global $config, $logged, $db, $member_id;
			
	if($logged){
		$row = $db->super_query("SELECT `created_on` FROM vass_audio_love WHERE audio_id = '" . $audio_id . "' AND user_id = '" . $member_id['user_id'] . "' LIMIT 0,1");
    	
    	if( $row['created_on'] ){
			$buffer['username']   = $member_id['username'];
			$buffer['comment']    = null;
			$buffer['context']    = null;
			$buffer['source']     = null;
			$buffer['created_on'] = date( 'D d M Y H:i:s', strtotime( $row['created_on']));
			$buffer['client_id']  = null;
			
		}
		
	}else $buffer = null;
	return $buffer;
}
function aliases($audio_id){
	global $config, $logged, $db;
	
	$buffer = null;
	
	return $buffer;
}
function stream($audio_id){
	global $config, $db;
	
	$row = $db->super_query("SELECT url FROM vass_audios WHERE id = '$audio_id'");
	
	if($row['url']) {
		$urls = json_decode($row['url']);
		$rand = array_rand($urls);
		$buffer = strval($urls[$rand]);
	}
	else $buffer = $config['siteurl'] . "static/audios/". $audio_id . ".mp3";
	
	return $buffer;
}
function metadata_state($audio_id){
	global $config, $logged, $db;
	
	$buffer = 'complete';
	
	return $buffer;
}

function sources($audio_id){
	global $config, $logged, $db;
	
	$buffer[]= "";
	
	return $buffer;
}

function track_number($audio_id){
	global $config, $logged, $db;
	
	$row = $db->super_query("SELECT surah FROM vass_audios WHERE id = '$audio_id'");
	
	if($row['surah']) {
		$buffer = $row['surah'];
	}
			
	return $buffer;
}

function track_duration($audio_id){
	global $config, $logged, $db;
	
	$row = $db->super_query("SELECT duration FROM vass_audios WHERE id = '$audio_id'");
	
	if($row['duration']) {
		$buffer = $row['duration'];
	}
			
	return $buffer;
}

function play_count($audio_id){
	global $config, $logged, $db;
	
	$row = $db->super_query("SELECT played FROM vass_audios WHERE id = '$audio_id'");
	
	if($row['played']) {
		$buffer = $row['played'];
	}
			
	return $buffer;
}

function recent_loves($audio_id){
	global $config, $db,$member_id, $logged;
	$buffer = array();
	if($logged)
		$db->query("SELECT vass_users.username, vass_audio_love.created_on FROM vass_audio_love LEFT JOIN vass_users ON vass_audio_love.user_id = vass_users.user_id WHERE vass_audio_love.audio_id = '" . $audio_id . "' AND vass_users.user_id !='" . $member_id['user_id'] . "' LIMIT 0,10");
	else 
		$db->query("SELECT vass_users.username, vass_audio_love.created_on FROM vass_audio_love LEFT JOIN vass_users ON vass_audio_love.user_id = vass_users.user_id WHERE vass_audio_love.audio_id = '" . $audio_id . "' LIMIT 0,10");
	
	while( $row = $db->get_row() ){
		
		$users['username'] = $row['username'];
		$users['created_on'] = date( 'D d M Y H:i:s', strtotime(  $row['created_on'] ) );
		$buffer[] = $users;
		
	}
		
	return $buffer;
}

function last_loved($audio_id){
    global $config, $db,$member_id, $logged;
	
	$row = $db->query("SELECT played FROM vass_audios WHERE id = '$audio_id'");
	
	if($row['last_loved']) {
		$buffer = date( 'D d M Y H:i:s', strtotime(  $row['last_loved'] ) );
	}
			
	return $buffer;
    
}

function tags($tags){
	global $config, $logged, $db;
	if($tags){
		$tag_query = $db->query ( "SELECT name FROM vass_genres WHERE id IN(" . $tags . ")" );
		while($tag = $db->get_row($tag_query)){
			$buffer[] = $tag['name'];
		}
	}else{
		$buffer = array();
	}
	return $buffer;
}
function trending_rank_today($audio_id){
	global $config, $logged, $db;
	
	$buffer = null;
	
	return $buffer;
}
function import_feeds($user_id){
	global $config, $logged, $db;
	
	$buffer = array();
	
	return $buffer;
}

function similar_artists($tags){
	global $config, $logged, $db;
	$tag = explode(",",$tags);
	if($tags){
		$artists_query = $db->query("SELECT name FROM vass_artists WHERE tag REGEXP '[[:<:]]" . $tag['0'] . "[[:>:]]' LIMIT 0,10;");
		while($artists = $db->get_row($artists_query)){
			$buffer[] = $artists['name'];
		}
	} else $buffer = array();
	
	return $buffer;
}

function avatar( $had, $user_id ) {
	global $config, $logged, $db;
	
	if(file_exists(ROOT_DIR . "/static/users/avatar_small_" . $user_id .".jpg")){
		
		$avatar['small']    = $config['siteurl'] . "static/users/avatar_small_" . $user_id . ".jpg";
		$avatar['medium']   = $config['siteurl'] . "static/users/avatar_medium_" . $user_id . ".jpg";
		$avatar['original'] = $config['siteurl'] . "static/users/avatar_original_" . $user_id . ".jpg";
	} else {
		$avatar['small']    = $config['siteurl'] . "static/users/avatar_small_default.jpg";
		$avatar['medium']   = $config['siteurl'] . "static/users/avatar_medium_default.jpg";
		$avatar['original'] = $config['siteurl'] . "static/users/avatar_original_default.jpg";
		
	}
	return $avatar;
}
function audiolist_images( $album_id ) {
	global $config, $db;
	
	if(file_exists(ROOT_DIR . "/static/albums/" . $album_id ."_small.jpg")){
		$image = array(
			     "small" => $config['siteurl'] . "static/albums/" . $album_id ."_small.jpg",
			     "large" => $config['siteurl'] . "static/albums/" . $album_id ."_large.jpg",
			    "medium" => $config['siteurl'] . "static/albums/" . $album_id ."_medium.jpg",
			"extralarge" => $config['siteurl'] . "static/albums/" . $album_id ."_extralarge.jpg"
		);
	
	} else {
        $image = array(
			     "small" => $config['siteurl'] . "static/albums/small.jpg",
			     "large" => $config['siteurl'] . "static/albums/large.jpg",
			    "medium" => $config['siteurl'] . "static/albums/medium.jpg",
			"extralarge" => $config['siteurl'] . "static/albums/extralarge.jpg"
		);
	}
	return $image;
}
function artist_images( $album_id, $artist_id ){
	global $config, $db;
	
	if(file_exists(ROOT_DIR . "/static/albums/" . $album_id ."_extralarge.jpg")){
		$image = array(
		"extralarge" => array(
			"src"    => $config['siteurl'] . "static/albums/" . $album_id ."_extralarge.jpg",
			"width"  => "500",
			"height" => "500",
			)
		);
	
	} else {
		$image = array(
			"extralarge" => array(
				"src"    => $extralarge = $config['siteurl'] . "static/artists/" . $artist_id ."_extralarge.jpg",
				"width"  => "500",
				"height" => "500",
			)
		);
	}
	
	return $image;
}
function curPageURL() {
	$pageURL = 'http';
	if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	}else{
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}
	return $pageURL;
}

function getRealIpAddress() {
		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		}		
		else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
}

function hyperlink($string){
	
	$string = preg_replace("/([^\w\/])(www\.[a-z0-9\-]+\.[a-z0-9\-]+)/i", "$1http://$2",$string);
	
	$string = preg_replace("/([\w]+:\/\/[\w-?&;#~=\.\/\@]+[\w\/])/i","<a target=\"_blank\" href=\"$1\">$1</a>",$string);
	
	$string = preg_replace("/([\w-?&;#~=\.\/]+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,3}|[0-9]{1,3})(\]?))/i","<a href=\"mailto:$1\">$1</a>",$string);
	
	return $string;
	
}



function hms2sec ($hms) {
     list($m, $s) = explode (":", $hms);
     $seconds = 0;
     $seconds += (intval($m) * 60);
     $seconds += (intval($s));
     return $seconds;
}

function stripUnicode($str){
        if(!$str) return false;
        $unicode = array(
            'a'=>'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd'=>'đ',
            'd'=>'Đ',
            'e'=>'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i'=>'í|ì|ỉ|ĩ|ị',
            'o'=>'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u'=>'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y'=>'ý|ỳ|ỷ|ỹ|ỵ',
        );
        foreach($unicode as $nonUnicode=>$uni) $str = preg_replace("/($uni)/i",$nonUnicode,$str);
		return $str;
}

function makekeysearch($column, $data){	
	$split_stemmed = explode(" ",$data);
	while(list($key,$val)=each($split_stemmed)){
		if($val<>" " and strlen($val) > 0){
		$sql .= $column." LIKE '%".$val."%' AND ";
		}
	}	
		$sql=substr($sql,0,(strLen($sql)-4));//this will eat the last AND
		$sql .= "";
	return $sql;
}
function removetype($data) 
{	
	$data = html_entity_decode($data);
	$data=totranslit( $data , true, false );
	return $data;
}
function artisthash($data) 
{	
	$hash = makekeyword( $data );
	
	$hash = substr(md5( $hash ), 0, 8);
	
	return $hash;
}
function makekeyword($str = null){
	global $config, $db;
	
	$str = urldecode($str);
    
    $str = $db->safesql($str);
    
    return $str;
}

function totranslit($var, $lower = true, $punkt = true) {
	$NpjLettersFrom = "àáâăäåçèêë́íîïđṇ̃óôöû³";
	$NpjLettersTo = "abvgdeziklmnoprstufcyi";
	$NpjBiLetters = array ("é" => "j", "¸" => "yo", "æ" => "zh", "ơ" => "x", "÷" => "ch", "ø" => "sh", "ù" => "shh", "ư" => "ye", "₫" => "yu", "ÿ" => "ya", "ú" => "", "ü" => "", "¿" => "yi", "º" => "ye" );
	
	$NpjCaps = "ÀÁÂĂÄÅ¨ÆÇÈÉÊË̀ÍÎÏĐÑ̉ÓÔƠÖ×ØÙÜÚÛỮß¯ª²";
	$NpjSmall = "àáâăäå¸æçèéêë́íîïđṇ̃óôơö÷øùüúûư₫ÿ¿º³";
	
	$var = str_replace( ".php", "", $var );
	$var = trim( strip_tags( $var ) );
	$var = preg_replace( "/\s+/ms", "-", $var );
	$var = strtr( $var, $NpjCaps, $NpjSmall );
	$var = strtr( $var, $NpjLettersFrom, $NpjLettersTo );
	$var = strtr( $var, $NpjBiLetters );
	
	if ( $punkt ) $var = preg_replace( "/[^a-z0-9\_\-.]+/mi", "", $var );
	else $var = preg_replace( "/[^a-z0-9\_\-]+/mi", "", $var );

	$var = preg_replace( '#[\-]+#i', '-', $var );

	if ( $lower ) $var = strtolower( $var );
	
	if( strlen( $var ) > 50 ) {
		
		$var = substr( $var, 0, 50 );
		
		if( ($temp_max = strrpos( $var, '-' )) ) $var = substr( $var, 0, $temp_max );
	
	}
	
	return $var;
}

function create_keywords($story) {
	global $metatags;
	
	$keyword_count = 20;
	$newarr = array ();
	
	$quotes = array ("\x22", "\x60", "\t", "\n", "\r", ",", ".", "/", "¬", "#", ";", ":", "@", "~", "[", "]", "{", "}", "=", "-", "+", ")", "(", "*", "&", "^", "%", "$", "<", ">", "?", "!", '"' );
	$fastquotes = array ("\x22", "\x60", "\t", "\n", "\r", '"', "\\", '\r', '\n', "/", "{", "}", "[", "]" );
	
	$story = preg_replace( "'\[hide\](.*?)\[/hide\]'si", "", $story );
	$story = preg_replace( "'\[attachment=(.*?)\]'si", "", $story );
	$story = preg_replace( "'\[page=(.*?)\](.*?)\[/page\]'si", "", $story );
	$story = str_replace( "{PAGEBREAK}", "", $story );
	
	$story = str_replace( $fastquotes, '', trim( strip_tags( str_replace( '<br />', ' ', stripslashes( $story ) ) ) ) );
	
	$metatags['description'] = substr( $story, 0, 190 );
	
	$story = str_replace( $quotes, '', $story );
	
	$arr = explode( " ", $story );
	
	foreach ( $arr as $word ) {
		if( strlen( $word ) > 4 ) $newarr[] = $word;
	}
	
	$arr = array_count_values( $newarr );
	arsort( $arr );
	
	$arr = array_keys( $arr );
	
	$total = count( $arr );
	
	$offset = 0;
	
	$arr = array_slice( $arr, $offset, $keyword_count );
	
	$metatags['keywords'] = implode( ", ", $arr );
}

function clean_url($url) {
	
	if( $url == '' ) return;
	
	$url = str_replace( "http://", "", strtolower( $url ) );
	if( substr( $url, 0, 4 ) == 'www.' ) $url = substr( $url, 4 );
	$url = explode( '/', $url );
	$url = reset( $url );
	$url = explode( ':', $url );
	$url = reset( $url );
	
	return $url;
}

function convert_unicode($t, $to = 'windows-1251') {
	$to = strtolower( $to );

	if( $to == 'utf-8' ) {
		
		return urldecode( $t );
	
	} else {
		
		if( function_exists( 'iconv' ) ) $t = iconv( "UTF-8", $to . "//IGNORE", $t );
		else $t = "The library iconv is not supported by your server";
	
	}

	return urldecode( $t );
}

function getSlug($str = null)
  {
    if( null === $str ) {
      $str = $this->getTitle();
    }
    if( strlen($str) > 32 ) {
      $str = substr($str, 0, 32) . '...';
    }
    $str = preg_replace('/([a-z])([A-Z])/', '$1 $2', $str);
    $str = strtolower($str);
    $str = preg_replace('/[^a-z0-9-]+/i', '-', $str);
    $str = preg_replace('/-+/', '-', $str);
    $str = trim($str, '-');
    if( !$str ) {
      $str = '-';
    }
    return $str;
  }
  
$domain_cookie       = explode (".", clean_url( $_SERVER['HTTP_HOST'] ));
$domain_cookie_count = count($domain_cookie);
$domain_allow_count  = -2;

if ( $domain_cookie_count > 2 ) {

	if ( in_array($domain_cookie[$domain_cookie_count-2], array('com', 'net', 'org') )) $domain_allow_count = -3;
	if ( $domain_cookie[$domain_cookie_count-1] == 'ua' ) $domain_allow_count = -3;
	$domain_cookie = array_slice($domain_cookie, $domain_allow_count);
}

$domain_cookie = "." . implode (".", $domain_cookie);

define( 'DOMAIN', $domain_cookie );


?>
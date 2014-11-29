<?php

@session_start ();
@ob_start ();
@ob_implicit_flush ( 0 );

@error_reporting ( E_ALL ^ E_NOTICE );
@ini_set ( 'display_errors', true );
@ini_set ( 'html_errors', false );
@ini_set ( 'error_reporting', E_ALL ^ E_NOTICE );

define ( 'ROOT_DIR', ".." );

define ( 'INCLUDE_DIR', ROOT_DIR . '/includes' );
@include (INCLUDE_DIR . '/config.inc.php');
require_once INCLUDE_DIR . '/class/_class_mysql.php';
require_once INCLUDE_DIR . '/db.php';
include_once(ROOT_DIR . "/admin/functions.php");

require_once INCLUDE_DIR . '/member.php';

if(!$logged) die("error!");

define ( 'TANCODE', true );

$_TIME = date ( "Y-m-d H:i:s", time () );

$allowed_extensions = array ("html", "tpl", "css", "js");

if(isset($_REQUEST['t'])) $type = $_REQUEST['t'];

if( $type == "upload_audio" ){

	$title = $db->safesql( $_REQUEST['title'] );
	$album_id = intval( $_REQUEST['album_id'] );
	$artist_id = intval( $_REQUEST['artist_id'] );
	$url = $_REQUEST['url'];
	$url = json_encode(array_filter($url));
	
	if($title && $artist_id){
		
		$active = ($user_group[$member_id['user_group']]['allow_full_audios']) ? 1 : 0;
		
		$db->query("INSERT INTO  vass_audios (url, title, artist_id, album_id, user_id, active, created_on) VALUES ('$url', '$title', '$artist_id', '$album_id', '" . $member_id['user_id'] . "', '$active', '$_TIME')");
		
		$filename = $db->insert_id();
		
		if ($_FILES['uploadedfile'] && ! $url){
		    if ( in_array( strtolower(strrchr($_FILES['uploadedfile']['name'], '.')), array('.mp3') ) )
		    {
		        if ($_FILES['uploadedfile']['size'] < 20048000) // file size inf 20Mb
		        {
		            $new_file = ROOT_DIR . '/static/audios/'.$filename.'.mp3';
		            if(move_uploaded_file($_FILES['uploadedfile']['tmp_name'],$new_file)){
		                $target_path = $config['siteurl'] . 'uploads/'.$filename.'.mp3'; 
		                echo "File transfer succesfull in ". $new_file;
		             }else {
		                echo 'Error: uploaded file invalid';
		             }
		        } else { echo 'Error: file size is more than 20MB: '.$_FILES['uploadedfile']['size'].' bytes'; }
		    } else { echo 'Error: Wrong file extention: '.strtolower(strrchr($_FILES['uploadedfile']['name'], '.')); }
		} 
	}
	
	if($target_path) { $url = $target_path; }
	
}elseif( $type == "edit_audio" ){

	$audio_id = $db->safesql( $_REQUEST['object_id'] );
	
	$audioer = $db->super_query("SELECT * FROM " . PREFIX . "_audios WHERE id = '".$audio_id."'");
	
	$audio = $audioer['audio_title'];
	$album = $audioer['audio_album'];
	$artist = $audioer['audio_artist'];
	$url = $audioer['url'];
	
	echo '<div class="control-group">
	<label class="control-label">Audio Title</label>
	<div class="controls">
	<input type="text" value="'.$audio.'" name="audio" required="" id="audio" placeholder="Audio name">
	<input type="hidden" value="'.$audio_id.'" name="audio_id" id="audio_id">
	
	</div>
	</div>
	<div class="control-group">
	<label class="control-label">Album</label>
	<div class="controls">
	<input type="text" value="'.$album.'"name="album" required="" id="album" placeholder="Audio album">
	</div>
	</div>
	<div class="control-group">
	<label class="control-label">Artist</label>
	<div class="controls">
	<input type="text" value="'.$artist.'" name="artist" required="" id="artist" placeholder="Audio artist">
	</div>
	</div>
	<div class="control-group">
	<label class="control-label">Url (mp3 file)</label>
	<div class="controls">
	<input type="text" value="'.$url.'" name="url" required="" id="url" placeholder="http://">
	</div>
	</div>';

}elseif( $type == "edit_audio_action" ){

	$audio_id = $db->safesql( $_REQUEST['audio_id'] );
	$audio = $db->safesql( $_REQUEST['audio'] );
	$album = $db->safesql( $_REQUEST['album'] );
	$artist = $db->safesql( $_REQUEST['artist'] );
	$url = $db->safesql( $_REQUEST['url'] );
	$db->super_query("UPDATE " . PREFIX . "_audios  SET `audio_title` = '".$audio."', `audio_album` = '".$album."', `audio_artist` = '".$artist."', `url` = '".$url."' WHERE id = '".$audio_id."'");
	
}elseif( $type == "templates" ){
	
	if(isset($_REQUEST['action'])) $action = $_REQUEST['action'];
	
	if($_POST['action'] == "load") {


	$_POST['file'] = trim(str_replace( "..", "", urldecode($_POST['file']) ));
	
	if(!$_POST['file']) { die ("error"); }
	
	$url = @parse_url ( $_POST['file'] );

	$root = ROOT_DIR . '/templates/';
	$file_path = dirname (clear_url_dir($url['path']));
	$file_name = pathinfo($url['path']);
	$file_name = removetype($file_name['basename'], false, true);

	$type = explode( ".", $file_name );
	$type = removetype( end( $type ) );
	
	if ( !in_array( $type, $allowed_extensions ) ) die ("error");

	if( !file_exists($root.$file_path."/".$file_name) ) die ("error");

	$content = @htmlspecialchars( file_get_contents( $root.$file_path."/".$file_name ), ENT_QUOTES );

	echo $lang['template_edit']." ".$file_path."/".$file_name;

	if(!is_writable($root.$file_path."/".$file_name)) echo " <font color=\"red\">".$lang['template_edit_fail']."</font>";

	$script= "";

	if ($type == "html" || $type == "tpl") {
		$script= <<<HTML
<script language="JavaScript" type="text/javascript">
  var editor = CodeMirror.fromTextArea('file_text', {
    height: "440px",
	textWrapping: false,
    parserfile: ["parsexml.js", "parsecss.js", "tokenizejavascript.js", "parsejavascript.js", "parsehtmlmixed.js"],
    stylesheet: ["js/codemirror/css/xmlcolors.css", "js/codemirror/css/jscolors.css", "js/codemirror/css/csscolors.css"],
    path: "js/codemirror/js/"
  });
</script>
HTML;

	}

	if ($type == "css") {
		$script= <<<HTML
<script language="JavaScript" type="text/javascript">
  var editor = CodeMirror.fromTextArea('file_text', {
    height: "440px",
	textWrapping: false,
    parserfile: "parsecss.js",
	stylesheet: "js/codemirror/css/csscolors.css",
    path: "js/codemirror/js/"
  });
</script>
HTML;

	}

	if ($type == "js") {
		$script= <<<HTML
<script language="JavaScript" type="text/javascript">
  var editor = CodeMirror.fromTextArea('file_text', {
    height: "440px",
	textWrapping: false,
    parserfile: ["tokenizejavascript.js", "parsejavascript.js"],
	stylesheet: "js/codemirror/css/jscolors.css",
    path: "js/codemirror/js/"
  });
</script>
HTML;

	}

	echo <<<HTML
<br /><br /><div style="border: solid 1px #BBB;width:99%;height:440px;"><textarea style="width:100%;height:440px;" name="file_text" id="file_text" wrap="off">{$content}</textarea></div>
<br /><input onClick="savefile('{$file_path}/{$file_name}')" type="button" class="btn btn-success" value="Save" style="width:100px;">
{$script}
HTML;

		}elseif($action == "save"){
			
			$_POST['file'] = trim(str_replace( "..", "", urldecode($_POST['file']) ));
			
			if(!$_POST['file']) { die ("error"); }
			
			$url = @parse_url ( $_POST['file'] );
			
			$root = ROOT_DIR . '/templates/';
			$file_path = dirname (clear_url_dir($url['path']));
			$file_name = pathinfo($url['path']);
			$file_name = removetype($file_name['basename'], false, true);
			
			$type = explode( ".", $file_name );
			$type = removetype( end( $type ) );
			
			if(!in_array( $type, $allowed_extensions ) ) die ("error");
			
			if(!file_exists($root.$file_path."/".$file_name) ) die ("error");
			
			if(!is_writable($root.$file_path."/".$file_name)) { echo " <font color=\"red\">".$lang['template_edit_fail']."</font>"; die (); }
			
			$_POST['content'] = convert_unicode( $_POST['content'], $config['charset']  );
			
			if( function_exists( "get_magic_quotes_gpc" ) && get_magic_quotes_gpc() ) $_POST['content'] = stripslashes( $_POST['content'] );
			
			$handle = fopen( $root.$file_path."/".$file_name, "w" );
			fwrite( $handle, $_POST['content'] );
			fclose( $handle );
			
			//Create new js file
			
			$directory = ROOT_DIR . "/templates/";
 
			$templates = glob($directory . "*.html");
			 
			foreach($templates as $template){
				
				$file_name = pathinfo($template);
				$file_name = $file_name['filename'];
				
				$content = @file_get_contents( $template );
				$content = preg_replace("/[\n\r]/","",$content);  
				$new_templates[$file_name] = $content;
				
			}
			
			$print_templates = "window.Templates = window.Templates || {};\n";
			
			foreach ($new_templates as $key => $value){
			
				$print_templates .= "window.Templates['". $key . "'] = _.template('" . $value . "');\n";
			
			}
			
			$handler = fopen( ROOT_DIR . "/assets/js/templates.js", "w" );
			
			fwrite( $handler, $print_templates );
			
			fclose( $handler );
			
			echo "ok"; die();
			
		} else {
			
			$root = ROOT_DIR . '/templates/';
			
			$_POST['dir'] = clear_url_dir(urldecode($_POST['dir']));
			
			if( file_exists($root . $_POST['dir']) ) {
				$files = scandir($root . $_POST['dir']);
				natcasesort($files);
				if( count($files) > 2 ) {
					echo "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
					// All dirs
					foreach( $files as $file ) {
						if( file_exists($root . $_POST['dir'] . $file) && $file != '.' && $file != '..' && is_dir($root . $_POST['dir'] . $file) ) {
							echo "<li class=\"directory collapsed\"><a href=\"#\" rel=\"" . htmlentities($_POST['dir'] . $file) . "/\">" . htmlentities($file) . "</a></li>";
						}
					}
					// All files
					foreach( $files as $file ) {
						if( file_exists($root . $_POST['dir'] . $file) && $file != '.' && $file != '..' && !is_dir($root . $_POST['dir'] . $file) ) {
							$serverfile_arr = explode( ".", $file );
							$ext = removetype( end( $serverfile_arr ) );
			
							if ( in_array( $ext, $allowed_extensions ) )
								echo "<li class=\"file ext_$ext\"><a href=\"#\" rel=\"" . htmlentities($_POST['dir'] . $file) . "\">" . htmlentities($file) . "</a></li>";
						}
					}
					echo "</ul>";	
				}
			}
		}
	
}elseif( $type == "typeahead" ){
	
	if(isset($_REQUEST['action'])) $action = $_REQUEST['action'];
	
	if(isset($_REQUEST['query'])) $query = $db->safesql($_REQUEST['query']);
	
	if($action == "artist") {
		$db->query("SELECT id, name FROM vass_artists WHERE name LIKE '%$query%' LIMIT 0,10");
		while($row = $db->get_row()){
			$json['label'] = trim($row['name']);
			$json['id'] = trim($row['id']);
			$buffer[] = $json;
		}
	}elseif($action == "album") {
		$db->query("SELECT id, name FROM vass_albums WHERE name LIKE '%$query%' LIMIT 0,10");
		while($row = $db->get_row()){
			$json['label'] = trim($row['name']);
			$json['id'] = trim($row['id']);
			$buffer[] = $json;
		}
	}elseif($action == "genres") {
		$db->query("SELECT id, name FROM vass_genres WHERE name LIKE '%$query%' LIMIT 0,10");
		while($row = $db->get_row()){
			$json['tag'] = trim($row['name']);
			$json['id'] = trim($row['id']);
			$buffer['tags'][] = $json;
		}
	}
	echo json_encode($buffer);
}elseif( $type == "upload" ){
	
	require_once INCLUDE_DIR . '/class/_class_upload.php';
	
	$upload_handler = new UploadHandler();
}elseif( $type == "delete_temp" ){
	
	$filename = $_REQUEST["name"];
	
	$filename = urldecode($filename);
	
	if($filename){
		@unlink(ROOT_DIR . "/static/audios/temp/" . $filename);
	}
	
}

$db->close();
?>
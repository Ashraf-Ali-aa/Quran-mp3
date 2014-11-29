<?

if( ! defined( 'TANCODE' ) ) {

	die( "Hacking attempt!" );

}

if(isset($_GET['action'])) $action = $_GET['action'];

	echo <<<HTML
<div class="container-fluid">
	<div class="row-fluid">
		<div class="span3">
			<div class="sidebar-nav">
				<ul class="nav nav-list bs-docs-sidenav affix-top">
					{$menu_li}
				</ul>
			</div>
		</div>
HTML;

if( $action == "edit" ){
	
	if(isset($_GET['id'])) $id = intval($_GET['id']);
	
	if(isset($_POST['title'])){
		$title = $db->safesql($_POST['title']);
		$artist_id = $db->safesql($_POST['artist_id']);
		$album_id = $db->safesql($_POST['album_id']);
		$url = $_REQUEST['url'];
		$url = json_encode(array_filter($url));
		
		$allowedExts = array("mp3");
		$extension = explode(".", $_FILES["file"]["name"]);
		$extension = end($extension);
		if($_FILES["file"]['name']){
			if (($_FILES["file"]["size"] > 20000) && in_array($extension, $allowedExts)) {
				if ($_FILES["file"]["error"] > 0){
					die("Error: " . $_FILES["file"]["error"]);
				}else{
					@move_uploaded_file($_FILES["file"]["tmp_name"], ROOT_DIR . "/static/audios/" . $id . ".mp3");
					$url = "";
				}
			}else{
				msg_page("error", "<strong>Error!</strong> Invalid file, or size of file is too heavy!", "do=audios&action=edit&id=$id");
			}
		}
		
		if($title){
			
			$db->query("UPDATE vass_audios SET title='$title', artist_id = '$artist_id', album_id = '$album_id', url='$url' WHERE id='$id'");
			
			msg_page("success", "<strong>Well done!</strong> Saved audio!", "do=audios");
			
		}
	}
	
	$row = $db->super_query("SELECT vass_audios.url, vass_audios.played, vass_audios.loved, vass_audios.id AS audio_id, vass_audios.loved, vass_audios.title AS audio_title, 
	vass_artists.id AS artist_id, vass_albums.id AS album_id, vass_artists.name AS audio_artist, vass_albums.name AS audio_album, vass_albums.id AS album_id FROM vass_audios LEFT JOIN vass_albums ON 
	vass_audios.album_id = vass_albums.id LEFT JOIN vass_artists ON vass_audios.artist_id = vass_artists.id WHERE vass_audios.id = '$id'");
	
	if(!$row['audio_id']) die("Audio not exits");
	
	$urls = json_decode($row['url']);
	
	foreach($urls as $url){
		
		$buffer .= '<input class="input-xxlarge" type="text" name="url[]" placeholder="http://" value="' . $url . '"/><br>';
		
	}
	
	
	echo <<<HTML
	<div class="span9">
		<script type="text/javascript" src="js/bootstrap-typeahead-ajax.js"></script>
		<h3>Edit audio: {$row['audio_title']}</h3>
			<form method="post" action="" enctype="multipart/form-data">
				<fieldset>
					<label>Title</label>
					<input class="input-xxlarge" type="text" name="title" value="{$row['audio_title']}" required autocomplete="off"/>
					<label>Aritst</label>
					<input class="input-xxlarge auto_artist" type="text" name="artist" value="{$row['audio_artist']}" required autocomplete="off"/>
					<input type="hidden" name="artist_id" value="{$row['artist_id']}"/>
					<label>Album</label>
					<input class="input-xxlarge auto_album" type="text" name="album" value="{$row['audio_album']}" autocomplete="off"/>
					<input type="hidden" name="album_id" value="{$row['album_id']}"/>
					<label>Mp3 url</label>
					{$buffer}
					<div id="urls_element"></div>
					<button type="button" class="btn" onclick="more();return false;">More URL</button>
					<label>Mp3 file</label>
					<input type="file" name="file" class="input-file">
					<label></label>
					<button type="submit" class="btn">Save</button>
				</fieldset>
			</form>
		</div>
	</div>
</div>
<script>
function more(){
	$("#urls_element").append('<input class="input-xxlarge" type="text" name="url[]" placeholder="http://" value=""/><br>');
}
</script>
HTML;
}elseif($action == "del"){
	
	if(isset($_GET['id'])) $id = intval($_GET['id']);
	
	$db->query("DELETE FROM vass_audios WHERE id = '$id'");
	
	@unlink( ROOT_DIR . '/static/audios/' . $id . '.mp3' );
	
	msg_page("success", "<strong>Success!</strong> Deleted audio!", "do=audios");
	
}else{
	if( isset( $_GET['p'] ) ) $page = intval( $_GET['p'] );
	if( !$page OR $page < 0 ) $page = 1;
	$start = ($page-1) * 20;
	
	$db->query("SELECT vass_audios.played, vass_audios.loved, vass_audios.id AS audio_id, vass_audios.loved, vass_audios.title AS audio_title, 
	vass_artists.name AS audio_artist, vass_albums.name AS audio_album, vass_albums.id AS album_id FROM vass_audios LEFT JOIN vass_albums ON 
	vass_audios.album_id = vass_albums.id LEFT JOIN vass_artists ON vass_audios.artist_id = vass_artists.id LIMIT $start,20");
	while($row = $db->get_row()){
	$audio_list .= <<<HTML
              <tr>
                <td>{$row['audio_title']}</td>
                <td>{$row['audio_artist']}</td>
                <td>{$row['audio_album']}</td>
               <td>{$row['played']}</td>
               <td>{$row['loved']}</td>
				<td><div class="btn-group">
                <button class="btn btn-info dropdown-toggle" data-toggle="dropdown">Action <span class="caret"></span></button>
                <ul class="dropdown-menu">
                  <li><a href="{$PHP_SELF}?do=audios&action=edit&id={$row['audio_id']}">Edit</a></li>
                  <li><a onclick="var r=confirm('Are you sure by deleting this audio?');if (r==true){window.location='{$PHP_SELF}?do=audios&action=del&id={$row['audio_id']}'}; return false;" href="{$PHP_SELF}?do=audios&action=del&id={$row['audio_id']}">Delete</a></li>
                </ul>
              </div>
				</td>
              </tr>
HTML;
}
	$total = $db->super_query("SELECT COUNT(*) AS count FROM vass_audios");
	$pages = navigation("admin/index.php?do=audios&p={page}", $total['count'], 20);
	
	echo <<<HTML
<script type="text/javascript" src="js/bootstrap-typeahead-ajax.js"></script>
<div class="span9">
			<p style="float:right;20px;">
				<button class="btn btn-info" type="button" onclick="window.location='/admin/index.php?do=upload'"><i class="icon-upload icon-white"></i> Batch upload</button> <button id="upload_audio_button" class="btn btn-success" type="button"><i class="icon-plus icon-white"></i>Add new audio</button>
			</p>
	<h3>Audios Manager: Total {$total['count']} audios</h3>
	<table class="table table-bordered table-striped">
		<colgroup>
		<col class="span1">
		<col class="span7">
		</colgroup>
		<thead>
			<tr>
				<th>Title</th>
				<th>Artist</th>
				<th>Album</th>
				<th>Played </th>
				<th>Loved</th>
				<th>Action</th>
			</tr>
		</thead>
		<tbody>
		{$audio_list}
		</tbody>
		
	</table>
	<div class="pagination pagination-right">
		<ul>
			{$pages}
		</ul>
	</div>
</div>
</div>
</div>
<div id="addaudios" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h3 id="myModalLabel">Add new audio</h3>
	</div>
	<form id="add_audio" action="{$PHP_SELF}ajax.php?t=upload_audio" enctype="multipart/form-data" method="POST">
		<div class="modal-body"> 
			<!-- The async form to send and replace the modals content with its response -->
			<div class="control-group">
				<label class="control-label">Audio Title</label>
				<div class="controls">
					<input type="text" name="title" required id="title" placeholder="Audio name" autocomplete="off">
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">Album</label>
				<div class="controls">
					<input type="text" class="auto_album" name="album" id="album" placeholder="Album of the audio" autocomplete="off">
					<input type="hidden" name="album_id" value="{$row['album_id']}"/>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">Artist</label>
				<div class="controls">
					<input type="text" class="auto_artist" name="artist" required id="artist" placeholder="Artist who own the audio" autocomplete="off">
					<input type="hidden" name="artist_id"/>
				</div>
			</div>
			<div class="control-group" id="urls_element">
				<label class="control-label">Urls (mp3 file)</label>
				<div class="controls">
					<input type="text" name="url[]" placeholder="http://">
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">Add more URL?</label>
				<div class="controls">
					<button type="button" class="btn" onclick="more();return false;">More URL</button>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">Or upload MP3 file (<span class="percenter">0%</span >)</label>
				<div class="controls">
					<input name="uploadedfile" id="uploadedfile" type="file" >
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button type="submit" class="btn">Submit</button>
		</div>
	</form>
</div>
<script>
function more(){
	$("#urls_element").append('<div class="controls">'+
					'<input type="text" name="url[]" placeholder="http://">'+
				'</div> ');
}
</script>
HTML;
}
?>
<?php
    
define( 'ROOT_DIR', $_SERVER['DOCUMENT_ROOT'] );
define( 'INCLUDE_DB', ROOT_DIR . '/feed/db' );


require_once INCLUDE_DB . '/trending.php';
require_once INCLUDE_DB . '/list.php';
require_once INCLUDE_DB . '/aotw.php';
require_once INCLUDE_DB . '/settings.php';
require_once INCLUDE_DB . '/album.php';
require_once INCLUDE_DB . '/profile.php';
require_once INCLUDE_DB . '/audio.php';
require_once INCLUDE_DB . '/search.php';
require_once INCLUDE_DB . '/genre.php';
require_once INCLUDE_DB . '/loved.php';
require_once INCLUDE_DB . '/playlist.php';
require_once INCLUDE_DB . '/top.php';
require_once INCLUDE_DB . '/artist.php';
require_once INCLUDE_DB . '/video.php';
require_once INCLUDE_DB . '/now_playing.php';
require_once INCLUDE_DB . '/member.php';
<?php

if ( extension_loaded('mysqli') AND version_compare("5.0.5", phpversion(), "!=") )
{
	include_once( INCLUDE_DIR . '/class/mysqli.php' );
}
else
{
	include_once( INCLUDE_DIR . '/class/mysql.php' );
}

?>
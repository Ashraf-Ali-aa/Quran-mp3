<?php

@session_start ();

define ( 'ROOT_DIR' , '..' );

define ( 'INCLUDE_DIR', ROOT_DIR . '/includes' );

include (INCLUDE_DIR . '/config.inc.php');

require_once INCLUDE_DIR . '/class/_class_mysql.php';

require_once INCLUDE_DIR . '/db.php';

require_once ROOT_DIR . '/modules/functions.php';

require_once INCLUDE_DIR . '/member.php';

include 'epitwitter/EpiCurl.php';

include 'epitwitter/EpiOAuth.php';

include 'epitwitter/EpiTwitter.php';

$consumer_key = $config['t_consumer_key'];

$consumer_secret = $config['t_consumer_secret'];

$token = $config['t_api_token'];

$secret= $config['t_api_token_secret'];

$twitterObj = new EpiTwitter($consumer_key, $consumer_secret, $token, $secret);

$oauth_token = $_REQUEST['oauth_token'];

$twitterObj = new EpiTwitter($consumer_key, $consumer_secret);

$_TIME = date ( "Y-m-d H:i:s", time () );

if( ! $oauth_token ) {
	
	$authorize_url = $twitterObj->getAuthorizationUrl();
	
	header("Location: " . $authorize_url );
	
}else{
	
	@session_register( 'twitter' );
	
	$twitterObj->setToken($_GET['oauth_token']);
	
	$token = $twitterObj->getAccessToken();
	
	$twitterObj->setToken($token->oauth_token, $token->oauth_token_secret);
	
	$twitterInfo = $twitterObj->get_accountVerify_credentials();
	
	if($logged){
		
		$db->query("INSERT IGNORE INTO vass_twitter SET user_id = '" . $member_id['user_id'] . "', screen_id = '" . $twitterInfo->id . "',
		screen_name = '" . $db->safesql($twitterInfo->screen_name) . "', name = '" . $db->safesql($twitterInfo->name) . "',
		profile_image_url = '" . $db->safesql($twitterInfo->profile_image_url) . "', `date` = '$_TIME'
		");
		
		$json['obj']['success'] = true;
		$json['obj']['type'] = "twitter";
		$json['obj']['name'] = $twitterInfo->screen_name;
		$json['obj']['pic'] = $twitterInfo->profile_image_url;
		$json['obj']['lookup_id'] = $twitterInfo->id;
		
		print '
			<html>
			<head>
			    <title>Quran Mp3+ Signup with Facebook or Twitter</title>
			    <script>
					window.opener.SettingsConnections.Add.render(' . json_encode($json) . ');
			       window.close();
			    </script>
			</head>
			<body></body>
			</html>';
	}else{
		
		$row = $db->super_query("SELECT user_id FROM vass_twitter WHERE screen_id  = '" . $twitterInfo->id . "'");
		
		if( $row['user_id'] ) {
			
			$member_id = array ();
			
			$member_id = $db->super_query("SELECT * FROM vass_users WHERE user_id = '" . $row['user_id'] . "'");
			
			if( $member_id['user_id'] ){
				set_cookie( "user_id", $member_id['user_id'], 365 );
				set_cookie( "login_pass", $member_id['password'], 365 );
				$_SESSION['user_id'] = $member_id['user_id'];
				$_SESSION['login_pass'] = $member_id['password'];
				$logged = TRUE;
				
				$buffer ['status_code'] = 200;
				
				$buffer ['status_text'] = "OK";
				
				$buffer = $member_id;
				$buffer ['viewer_following'] = false;
				$buffer ['import_feeds'] = import_feeds ( $member_id ['user_id'] );
				$buffer ['image'] = avatar ( $member_id ['avatar'], $member_id ['username'] );
				
				$row = $db->super_query ( "SELECT vass_background.color, vass_background.image, vass_background.position, vass_background.repeat, vass_background.use_image FROM vass_background WHERE vass_background.user_id = '" . $member_id ['user_id'] . "';" );
				
				if ($row ['image']) {
					$use_image = true;
					$is_default = false;
				} else {
					$is_default = true;
					$use_image = false;
				}
				
				$buffer ['background'] = $row;
				$buffer ['background'] ['is_default'] = $is_default;
				$buffer ['background'] ['use_image'] = $use_image;
				
				unset ( $buffer ['password'] );
				
				$json['response']['message'] = 'You was signed in successfully.';
				$json['response']['user'] = $buffer;
				$json['response']['service']['name'] = $user->name;
				$json['response']['service']['pic'] = 'https://graph.facebook.com/' . $user->id . '/picture?type=large';
				$json['response']['service']['lookup_id'] = $user->id;
				$json['response']['service']['type'] = "facebook";
				$json['response']['service']['added_on'] = $_TIME;
				$json['success'] = true;
				
				print '
				<html>
				    <head>
				        <title>TanCode EX sign in</title>
				        <script>
				            window.opener.CreateAccount.AlreadyLoggedIn(' . json_encode($json) . ');
				            window.close();
				        </script>
				    </head>
				    <body></body>
				</html>';
				
				$db->close();
			}
		}else{
			
			$_SESSION['twitter'] =  array();
			
			$_SESSION['twitter']['description'] = $twitterInfo->description;
			$_SESSION['twitter']['name'] = $twitterInfo->name;
			$_SESSION['twitter']['profile_image_url'] = $twitterInfo->profile_image_url;
			$_SESSION['twitter']['id'] = $twitterInfo->id;
			$_SESSION['twitter']['screen_name'] = $twitterInfo->screen_name;
			
			print '
				<html>
				<head>
				    <title>Quran Mp3+ Signup with Facebook or Twitter</title>
				    <script>
				        window.opener.CreateAccount.Service.listener({"response": {"service": {"info": {"website": null, "bio": "' . $twitterInfo->description . '", "is_default_profile_image": false, "name": "' . $twitterInfo->name . '", "pic": "' . $twitterInfo->profile_image_url . '", "username": "' . $twitterInfo->screen_name . '", "location": "' . $twitterInfo->location . '", "lookup_id": "' . $twitterInfo->id . '", "service_username": "' . $twitterInfo->screen_name . '", "email": ""}, "name": "twitter"}}, "success": true});
				       window.close();
				    </script>
				</head>
				<body></body>
				</html>';
			
		}
	}
}
?>

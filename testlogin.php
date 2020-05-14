<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('max_execution_time', 300); //300 seconds = 5 minutes. In case if your CURL is slow and is loading too much (Can be IPv6 problem)
error_reporting(E_ALL);
define('OAUTH2_CLIENT_ID', '710482345815310377');
define('OAUTH2_CLIENT_SECRET', 'DSTb4zmZr0Tr7bvQGh7xTQH6JnBh_4jm');
$authorizeURL = 'https://discordapp.com/api/oauth2/authorize';
$tokenURL = 'https://discordapp.com/api/oauth2/token';
$apiURLBase = 'https://discordapp.com/api/users/@me';
$revokeURL = 'https://discordapp.com/api/oauth2/token/revoke';
session_start();

if(get('action') == 'login') {
  $params = array(
    'client_id' => OAUTH2_CLIENT_ID,
    'redirect_uri' => 'http://localhost/testlogin.php',
    'response_type' => 'code',
    'scope' => 'identify guilds email'
  );
  // Redirect the user to Discord's authorization page
  header('Location: https://discordapp.com/api/oauth2/authorize' . '?' . http_build_query($params));
  die();
}
if(get('code')) {
    // Exchange the auth code for a token
    $token = apiRequest($tokenURL, array(
      "grant_type" => "authorization_code",
      'client_id' => OAUTH2_CLIENT_ID,
      'client_secret' => OAUTH2_CLIENT_SECRET,
      'redirect_uri' => 'http://localhost/testlogin.php',
      'code' => get('code')
    ));
    $logout_token = $token->access_token;
    $_SESSION['access_token'] = $token->access_token;
    header('Location: ' . $_SERVER['PHP_SELF']);
  }
if(session('access_token')) {
  $user = apiRequest($apiURLBase);
  echo '<h3>Tu est Connecté !</h3>';
  echo '<h4>Bienvenue, ' . $user->username . '</h4>';
  echo '<pre>';
    print_r($user);
  echo '</pre>';
  echo '<p><a href="?action=logout">se déco</a></p>';
} else {
  echo '<h3>tu n\'est pas co</h3>';
  echo '<p><a href="?action=login">se connecter</a></p>';
}
if(get('action') == 'logout') {
    apiRequest($revokeURL, array(
        'token' => session('access_token'),
        'client_id' => OAUTH2_CLIENT_ID,
        'client_secret' => OAUTH2_CLIENT_SECRET,
      ));
    unset($_SESSION['access_token']);
	
    echo "<script> window.location = 'https://akyla-gaming.com/PanelUser/login.php'; </script>";
    die();
  }

function apiRequest($url, $post=FALSE, $headers=array()) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $response = curl_exec($ch);
    if($post)
      curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    $headers[] = 'Accept: application/json';
    if(session('access_token'))
      $headers[] = 'Authorization: Bearer ' . session('access_token');
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    return json_decode($response);
  }
  function get($key, $default=NULL) {
    return array_key_exists($key, $_GET) ? $_GET[$key] : $default;
  }
  function session($key, $default=NULL) {
    return array_key_exists($key, $_SESSION) ? $_SESSION[$key] : $default;
  }
?>
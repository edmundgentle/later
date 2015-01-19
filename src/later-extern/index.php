<?php
if(isset($_POST['email'])) {
	$email=$_POST['email'];
	$api_key='ni390n2xoqKAsoOqnjs2';
	$api_url='http://www.edmundgentle.com/snippets/later/api/';
	$redirect_to='http://www.edmundgentle.com/snippets/later/';
	
	if(isset($_GET['redirect'])) {
		$redirect_to=$_GET['redirect'];
	}
	
	$signature=hash_hmac(
		'sha256',
		$email . '|' . date('d.m.y'),
		$api_key
	);
	
	$post_string='email=' . urlencode($email) .
		'&sig=' . urlencode($signature);
	
	$ch=curl_init();
	curl_setopt($ch, CURLOPT_URL, $api_url.'/login');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
	$res=curl_exec($ch);
	$response = @json_decode(trim($res),true);
	curl_close($ch);
	
	if($response) {
		if(isset($response['success']) and $response['success']) {
			if(isset($response['key'])) {
				$key=$response['key'];
				
				if(strpos($redirect_to,'?')===false) {
					$redirect_to.= '?key=' . $key;
				}else{
					$redirect_to.= '&key=' . $key;
				}
				header("Location: $redirect_to");
				exit();
			}
		}
	}
}
?>
<h1>Login</h1>
<form method="post">
	<label for="form_email">Email address</label>
	<input type="email" id="form_email" name="email" value="<? if(isset($_POST['email'])) {echo $_POST['email'];}?>" />
	<input type="submit" value="Login" />
</form>
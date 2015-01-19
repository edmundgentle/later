<?php
$starttime=microtime(true);
require_once('../includes/db.php');
$settings=array('debug'=>true);
function api_error_handler() {
	header('Access-Control-Allow-Origin: *');
	header("Content-Type: text/javascript; charset=UTF-8\n");
	$response=array('error'=>array('code'=>500,'message'=>'api error'));
	if(function_exists('format_json')) {
		echo format_json(json_encode($response));
	}else{
		echo json_encode($response);
	}
	exit();
}
if(!$settings['debug']) {
	set_error_handler('api_error_handler');
}
function api($params) {
	if(isset($params['uri'][0]) and $params['uri'][0]=='login') {
		if($params['method']=='post') {
			if(isset($params['post']['sig']) and isset($params['post']['email'])) {
				$signature=$params['post']['sig'];
				if(hash_hmac('sha256',$params['post']['email'].'|'.date('d.m.y'),API_KEY)==$signature) {
					$result=mysql_query("SELECT user_id FROM users WHERE email='".mysql_real_escape_string($params['post']['email'])."'");
					if(mysql_num_rows($result)==1) {
						list($user_id)=mysql_fetch_array($result, MYSQL_NUM);
						mysql_query("DELETE FROM user_keys WHERE user_id='".mysql_real_escape_string($user_id)."' OR expires<NOW()");
						do {
							$key=generate_string();
						}
						while(mysql_num_rows(mysql_query("SELECT key_id FROM user_keys WHERE key_id='".mysql_real_escape_string($key)."'")));
						$expires=strtotime("+1 hour");
						$result=mysql_query("INSERT INTO user_keys (key_id, user_id, expires) VALUES ('".mysql_real_escape_string($key)."','".mysql_real_escape_string($user_id)."','".date('Y-m-d H:i:s',$expires)."')");
						return array('success'=>true,'key'=>$key,'expires'=>$expires);
					}
				}else{
					return array('success'=>false,'info'=>'Invalid signature key');
				}
			}
		}
	}
	return array('success'=>false);
}
function format_json($json) {
	$indents=0;
	$indent_size=3;
	$output='';
	$inside=false;
	for ($i = 0, $j = strlen($json); $i < $j; $i++) {
	    $char=$json[$i];
		if($char=='{' || $char=='[') {
			if(!$inside) {
				$indents+=$indent_size;
				$output.=$char."\n".space($indents);
			}else{
				$output.=$char;
			}
		}elseif($char==',') {
			if(!$inside) {
				$output.=$char."\n".space($indents);
			}else{
				$output.=$char;
			}
		}elseif($char==':') {
			if(!$inside) {
				$output.=$char." ";
			}else{
				$output.=$char;
			}
		}elseif($char=='}' || $char==']') {
			if(!$inside) {
				$indents-=$indent_size;
				$output.="\n".space($indents).$char;
			}else{
				$output.=$char;
			}
		}elseif($char=='"') {
			if($inside) {
				$inside=false;
			}else{
				$inside=true;
			}
			$output.=$char;
		}else{
			$output.=$char;
		}
	}
	$output=str_replace('\/','/',$output);
	return $output;
}
function space($x) {
	$output='';
	for($y=1;$y<=$x;$y++) {
		$output.=' ';
	}
	return $output;
}
function tidy_array(&$arr) {
	foreach($arr as $k=>$v) {
		if(is_array($v)) {
			if(count($v)==1 and isset($v['api_params'])) {
				global $params, $settings;
				$p=array_merge($params,(array)$v['api_params']);
				$r=$settings['func_api_call']($p);
				if(isset($r['data'])) {
					$v=$r['data'];
				}else{
					unset($arr[$k]);
				}
			}
			tidy_array($v);
			$arr[$k]=$v;
			if(!count($v)) {
				unset($arr[$k]);
			}
		}else{
			if(!(strlen($v)>0 or $v===false)) {
				unset($arr[$k]);
			}
		}
	}
}
$params=array('uri'=>array(),'get'=>array(),'post'=>array(),'method'=>strtolower($_SERVER['REQUEST_METHOD']));
if(empty($_GET['api_access_url'])) {
	$_GET['api_access_url']='/';
}
$params['uri']=explode('/',trim(strtolower($_GET['api_access_url']),'/'));
unset($_GET['api_access_url']);
$params['get']=$_GET;
if(empty($_POST)) {
	$_POST=array();
}
if(strtoupper($_SERVER['REQUEST_METHOD'])!='GET' && strtoupper($_SERVER['REQUEST_METHOD'])!='POST') {
	parse_str(file_get_contents('php://input'), $_POST);
}
foreach($_POST as $k=>$v) {
	if(is_string($v)) {
		$params['post'][$k]=stripslashes($v);
	}else{
		$params['post'][$k]=$v;
	}
}
$_REQUEST=array_merge($_GET,$_POST);
$response=api($params);
header('Access-Control-Allow-Origin: *');
header('X-Powered-By:');
header("Content-Type: text/javascript; charset=UTF-8\n");
if(isset($params['get']['api_info'])) {
	$response['api_info']['exec_time']=microtime(true)-$starttime;
}else{
	unset($response['api_info']);
}
tidy_array($response);
echo format_json(json_encode($response));
?>
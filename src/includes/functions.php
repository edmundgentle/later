<?php
session_start();
$is_admin=null;
function substr_words($text,$maxchar,$end='...') {
	$text=strip_tags($text);
	if(strlen($text)>$maxchar) {
		$words=explode(" ",$text);
		$output = '';
		$i=0;
		while(1){
			$length = (strlen($output)+strlen($words[$i]));
			if($length>$maxchar){
				break;
			}else{
				$output=$output." ".$words[$i];
				++$i;
			};
		};
	}else{
		return $text;
	}
	return rtrim($output,'.,!? ').$end;
}
function href($path) {
	if(strpos($path,'.')===false && strpos($path,'?')===false) {
		$path=rtrim($path,'/').'/';
	}
	return 'http://www.edmundgentle.com/snippets/later/'.ltrim($path,'/');
}
function format_date($date,$html=true) {
	if(is_string($date)) {
		$date=strtotime($date);
	}
	if($html) {
		return '<abbr class="lat_date" title="'.date('j F Y \a\t H:i',$date).'" data-timestamp="'.$date.'">'.format_date($date,false).'</abbr>';
	}else{
		$old=$date;
		$now=time();
		$diff=$now-$old;
		if($diff<60) {
			return 'A few seconds ago';
		}elseif($diff<3600) {
			if(floor($diff/60)==1) {
				return 'About a minute ago';
			}else{
				return floor($diff/60).' minutes ago';
			}
		}elseif($diff<86400) {
			if(floor($diff/3600)==1) {
				return '1 hour ago';
			}else{
				if(date('d/m/Y',$old)!=date('d/m/Y',$now)) {
					return 'Yesterday at '.date('H:i',$old);
				}else{
					return floor($diff/3600).' hours ago';
				}
			}
		}elseif($diff<604800) {
			if(date('d/m/Y',$old)==date('d/m/Y',strtotime("-1 day",$now))) {
				return 'Yesterday at '.date('H:i',$old);
			}else{
				return date('l \a\t H:i',$old);
			}
		}
		if(date('Y',$old)!=date('Y',$now)) {
			return date('j F Y \a\t H:i',$old);
		}else{
			return date('j F \a\t H:i',$old);
		}
	}
}
function get_user_id() {
	if(isset($_SESSION['user_id'])) {
		return $_SESSION['user_id'];
	}
	return false;
}
function is_logged_in() {
	if(get_user_id()) {
		return true;
	}
	return false;
}
function redirect($url) {
	header("Location: $url");
	exit();
}
function send_email($params=array()) {
	if(isset($params['from'])) {
		$headers = "From: \"".strip_tags($params['from_name'])."\" <".FROM_EMAIL.">\r\n";
		$headers .= "Reply-To: ".strip_tags($params['from'])."\r\n";
	}else{
		$headers = "From: \"".FROM_NAME."\" <".FROM_EMAIL.">\r\n";
	}
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=utf-8\r\n";
	mail('"'.$params['to_name'].'" <'.$params['to'].'>', $params['subject'], $params['body'], $headers);
}
function paginate($total,$current=1) {
	if($total>1) {
		echo '<div align="center"><ul class="pagination">
		<li';
		if($current==1) {
			echo' class="disabled"';
		}
		echo'><a href="?page='.($current-1).'">&laquo;</a></li>';
		$nums=array();
		for($x=1;$x<=min(2,$total);$x++) {
			$nums[]=$x;
		}
		for($x=max($current-1,1);$x<=min($current+1,$total);$x++) {
			$nums[]=$x;
		}
		for($x=max($total-1,1);$x<=$total;$x++) {
			$nums[]=$x;
		}
		$nums=array_unique($nums);
		sort($nums);
		$prev=0;
		foreach($nums as $x) {
			if(($x-1)!=$prev) {
				echo'<li class="disabled"><a>...</a></li>';
			}
			$prev=$x;
			echo'<li';
			if($x==$current) {
				echo' class="active"';
			}
			echo'><a href="?page='.$x.'">'.$x.'</a></li>';
		}
		echo '<li';
		if($current==$total) {
			echo' class="disabled"';
		}
		echo'><a href="?page='.($current+1).'">&raquo;</a></li>
		</ul></div>';
	}
}
function pluralise($num, $single, $plural='+s') {
	if($plural='+s') {
		$plural=$single.'s';
	}
	if($num==1) {
		return $num.' '.$single;
	}
	return $num.' '.$plural;
}
function generate_string($length=32) {
	$chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
	$a=$chars{rand(0, 61)};
	for($i=1;$i<$length;$i=strlen($a)){
		$r=$chars{rand(0, 61)};
		if($r!=$a{$i - 1}) $a.=$r;
	}
	return $a;
}
function login($url='') {
	$go_to=LOGIN_URL;
	if(strlen($url)) {
		if(strpos($go_to,'?')===false) {
			$go_to.='?redirect='.$url;
		}else{
			$go_to.='&redirect='.$url;
		}
	}
	header("Location: $go_to");
	exit();
}
function is_admin() {
	if(is_logged_in()) {
		global $is_admin;
		if(is_null($is_admin)) {
			$result=mysql_query("SELECT admin FROM users WHERE user_id='".mysql_real_escape_string(get_user_id())."'");
			if(mysql_num_rows($result)) {
				list($is_admin)=mysql_fetch_array($result, MYSQL_NUM);
			}
		}
		if($is_admin) {
			return true;
		}
	}
	return false;
}
?>
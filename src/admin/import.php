<?php 
require_once('../includes/db.php');
$subgroups=array();
if(defined('STDIN')) {
	function generate_groups($stage, $course, $school) {
		$groups=array();
		if($stage>-1) {
			//student
			$groups[]='Stage '.$stage.' Students';
			$groups[]='Stage '.$stage.' '.$course.' Students';
			$groups[]='Stage '.$stage.' '.$school.' Students';
			$groups[]=$course.' Students';
			$groups[]=$school.' Students';
			$groups[]='All Students';
			global $subgroups;
			$subgroups['Stage '.$stage.' '.$course.' Students']=$school.' Students';
		}else{
			//staff
			$groups[]=$course.' Staff';
			$groups[]=$school.' Staff';
			$groups[]='All Staff';
		}
		return $groups;
	}
	function generate_management($stage, $course, $school, $admin) {
		$groups=array();
		if($stage==-1) {
			for($x=0;$x<=4;$x++) {
				$groups[]='Stage '.$stage.' '.$course.' Students';
				$groups[]='Stage '.$stage.' '.$school.' Students';
			}
			$groups[]=$course.' Students';
			$groups[]=$school.' Students';
			$groups[]=$course.' Staff';
			$groups[]=$school.' Staff';
		}
		return $groups;
	}
	echo "Importing a user list from a CSV file\n";
	$files=array();
	if($handle = opendir('.')) {
		while(false !== ($entry = readdir($handle))) {
			if($entry != "." && $entry != "..") {
				if(strtolower(substr($entry,-3))=='csv') {
					$files[]=$entry;
				}
			}
		}
		closedir($handle);
	}
	if(count($files)) {
		echo "Select the file you would like to import by entering it's corresponding number from the list below:\n";
		foreach($files as $k=>$v) {
			echo ($k+1)." => $v\n";
		}
		echo "File number: ";
		$handle = fopen ("php://stdin","r");
		$line = strtolower(trim(fgets($handle)));
		if(is_numeric($line)) {
			$num=$line-1;
			if(isset($files[$num])) {
				echo "Importing {$files[$num]}...\n";
				//get current users from the system
				echo " - Loading all users\n";
				$old=get_users();
				$oldemails=array();
				foreach($old as $u) {
					$oldemails[$u['email']]=$u['user_id'];
				}
				echo " - Removing old auto-created groups\n";
				$remove_groups=array();
				$groups=get_groups(array('admin_id'=>0));
				foreach($groups as $g) {
					$remove_groups[]=$g['group_id'];
				}
				if(count($remove_groups)) {
					mysql_query("DELETE FROM group_users WHERE group_id IN ('".implode("','",$remove_groups)."')");
					mysql_query("DELETE FROM group_managers WHERE group_id IN ('".implode("','",$remove_groups)."')");
					mysql_query("DELETE FROM groups WHERE group_id IN ('".implode("','",$remove_groups)."')");
				}
				unset($remove_groups);
				unset($groups);
				//Email address	First name	Last name	Admin	Stage (-1 for staff)	Course / Job Description	School / Department
				$created_groups=array();
				$push_to_group=array();
				$push_to_manager=array();
				if(($handle = fopen($files[$num], "r")) !== FALSE) {
					echo " - Reading new users\n";
					while(($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
						$groups=generate_groups($data[4],$data[5],$data[6]);
						$management=generate_management($data[4],$data[5],$data[6], $data[3]);
						if($data[4]==-1) {
							$data[4]='NULL';
						}else{
							$data[4]="'".mysql_real_escape_string($data[4])."'";
						}
						if(isset($oldemails[$data[0]])) {
							//existing - update
							mysql_query("UPDATE users SET first_name='".mysql_real_escape_string($data[1])."', last_name='".mysql_real_escape_string($data[2])."', admin='".mysql_real_escape_string($data[3])."', stage={$data[4]}, course='".mysql_real_escape_string($data[5])."', department='".mysql_real_escape_string($data[6])."' WHERE user_id='".mysql_real_escape_string($oldemails[$data[0]])."'");
							$user_id=$oldemails[$data[0]];
							unset($oldemails[$data[0]]);
						}else{
							//new - insert
							mysql_query("INSERT INTO users (first_name, last_name, email, admin, stage, course, department) VALUES ('".mysql_real_escape_string($data[1])."', '".mysql_real_escape_string($data[2])."', '".mysql_real_escape_string($data[0])."', '".mysql_real_escape_string($data[3])."', {$data[4]}, '".mysql_real_escape_string($data[5])."', '".mysql_real_escape_string($data[6])."')");
							$user_id=mysql_insert_id();
						}
						foreach($groups as $group) {
							if(!isset($created_groups[$group])) {
								mysql_query("INSERT INTO groups (name, admin_id) VALUES ('".mysql_real_escape_string($group)."', 0)");
								$created_groups[$group]=mysql_insert_id();
							}
							$push_to_group[]="('".mysql_real_escape_string($created_groups[$group])."', '".mysql_real_escape_string($user_id)."')";
						}
						foreach($management as $group) {
							if(!isset($created_groups[$group])) {
								mysql_query("INSERT INTO groups (name, admin_id) VALUES ('".mysql_real_escape_string($group)."', 0)");
								$created_groups[$group]=mysql_insert_id();
							}
							$push_to_manager[]="('".mysql_real_escape_string($created_groups[$group])."', '".mysql_real_escape_string($user_id)."')";
						}
					}
					fclose($handle);
				}
				echo " - Removing old users\n";
				if(count($oldemails)) {
					mysql_query("DELETE FROM group_users WHERE user_id IN ('".implode("','",$oldemails)."')");
					mysql_query("DELETE FROM group_managers WHERE user_id IN ('".implode("','",$oldemails)."')");
					mysql_query("DELETE FROM user_preferences WHERE user_id IN ('".implode("','",$oldemails)."')");
					mysql_query("DELETE FROM messages WHERE sender_id IN ('".implode("','",$oldemails)."')");
					mysql_query("DELETE FROM users WHERE user_id IN ('".implode("','",$oldemails)."')");
				}
				unset($oldemails);
				echo " - Populating new auto-groups\n";
				if(count($push_to_group)) {
					mysql_query("INSERT INTO group_users (group_id, user_id) VALUES ".implode(' , ',$push_to_group));
				}
				unset($push_to_group);
				if(count($push_to_manager)) {
					mysql_query("INSERT INTO group_managers (group_id, user_id) VALUES ".implode(' , ',$push_to_manager));
				}
				$push_to_manager=array();
				foreach($subgroups as $sub=>$parent) {
					if(isset($created_groups[$parent]) and isset($created_groups[$sub])) {
						$result=mysql_query("SELECT user_id FROM group_managers WHERE group_id='".mysql_real_escape_string($created_groups[$parent])."'");
						while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
							$push_to_manager[]="('".mysql_real_escape_string($created_groups[$sub])."', '".mysql_real_escape_string($row['user_id'])."')";
						}
					}
				}
				if(count($push_to_manager)) {
					mysql_query("INSERT INTO group_managers (group_id, user_id) VALUES ".implode(' , ',$push_to_manager));
				}
				unset($push_to_manager);
				echo " - Removing new empty groups\n";
				$remove_groups=array();
				$result=mysql_query("SELECT group_id FROM groups WHERE (SELECT COUNT(user_id) FROM group_users WHERE group_users.group_id=groups.group_id)<=1 AND admin_id=0");
				while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
					$remove_groups[]=$row['group_id'];
				}
				if(count($remove_groups)) {
					mysql_query("DELETE FROM group_users WHERE group_id IN ('".implode("','",$remove_groups)."')");
					mysql_query("DELETE FROM group_managers WHERE group_id IN ('".implode("','",$remove_groups)."')");
					mysql_query("DELETE FROM groups WHERE group_id IN ('".implode("','",$remove_groups)."')");
				}
				echo " - Optimising tables\n";
				mysql_query("OPTIMIZE TABLE groups, group_users, group_managers, users, messages, user_preferences");
				echo "Import complete.\n";
				exit();
			}else{
				echo "That file number couldn't be found.\n";
				exit();
			}
		}else{
			echo "You must enter a number.\n";
			exit();
		}
	}else{
		echo "There are no CSV files in this directory. Please add a CSV file then try again.\n";
		exit();
	}
}else{
	echo"Hint: Run this from a command line!";
}
?>
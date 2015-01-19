<?php 
require_once('includes/db.php');
$subgroups=array();
if(defined('STDIN')) {
	echo "Installing Later\n";
	mysql_query("CREATE TABLE  `messages` (`message_id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,`slug` VARCHAR( 50 ) NOT NULL ,`subject` VARCHAR( 120 ) NOT NULL ,`body` TEXT NOT NULL ,`category_id` VARCHAR( 4 ) NOT NULL ,`sender_id` VARCHAR( 10 ) NOT NULL ,`date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,`private` BOOL NOT NULL DEFAULT  '0',INDEX (  `category_id` ,  `sender_id` ) ,UNIQUE (`slug`)) ENGINE = MYISAM");
	mysql_query("CREATE TABLE  `users` (`user_id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,`first_name` VARCHAR( 20 ) NULL ,`last_name` VARCHAR( 40 ) NOT NULL ,`email` VARCHAR( 80 ) NOT NULL ,`admin` BOOL NOT NULL DEFAULT  '0',`stage` INT( 1 ) UNSIGNED NULL ,`course` VARCHAR( 100 ) NOT NULL ,`department` VARCHAR( 100 ) NOT NULL ,INDEX (  `first_name` ,  `last_name` ) ,UNIQUE (`email`)) ENGINE = MYISAM");
	mysql_query("CREATE TABLE  `groups` (`group_id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,`name` VARCHAR( 50 ) NOT NULL ,`admin_id` VARCHAR( 10 ) NOT NULL ,INDEX (  `admin_id` ) ,UNIQUE (`name`)) ENGINE = MYISAM");
	mysql_query("CREATE TABLE  `group_managers` (`id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,`group_id` VARCHAR( 10 ) NOT NULL ,`user_id` VARCHAR( 10 ) NOT NULL ,INDEX (  `group_id` ,  `user_id` )) ENGINE = MYISAM");
	mysql_query("CREATE TABLE  `group_users` (`id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,`group_id` VARCHAR( 10 ) NOT NULL ,`user_id` VARCHAR( 10 ) NOT NULL ,INDEX (  `group_id` ,  `user_id` )) ENGINE = MYISAM");
	mysql_query("CREATE TABLE  `message_recipients` (`rec_id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,`message_id` VARCHAR( 10 ) NOT NULL ,`group_id` VARCHAR( 10 ) NOT NULL ,INDEX (  `message_id` ,  `group_id` )) ENGINE = MYISAM");
	mysql_query("CREATE TABLE  `user_preferences` (`id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,`user_id` VARCHAR( 10 ) NOT NULL ,`category_id` VARCHAR( 4 ) NOT NULL ,`preference` VARCHAR( 10 ) NOT NULL COMMENT  'no_emails, summary, all_emails',INDEX (  `user_id` )) ENGINE = MYISAM");
	mysql_query("CREATE TABLE  `categories` (`category_id` INT( 4 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,`slug` VARCHAR( 30 ) NOT NULL ,`name` VARCHAR( 30 ) NOT NULL ,UNIQUE (`slug` ,`name`)) ENGINE = MYISAM");
	mysql_query("CREATE TABLE  `user_keys` (`key_id` CHAR( 32 ) NOT NULL ,`user_id` VARCHAR( 10 ) NOT NULL ,`expires` DATETIME NOT NULL ,PRIMARY KEY (  `key_id` ) ,UNIQUE (`user_id`)) ENGINE = MYISAM");
	echo "Installation complete\n";
	exit();
}else{
	echo"Hint: Run this from a command line!";
}
?>
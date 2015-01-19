About Later
===========

I developed Later in 2013 as an application to sort out issues with mailing lists, with a focus on a university environment. This system could optionally be used in any organisation with a relatively large number of people. It has been developed using PHP and MySQL, using Bootstrap for the user interface, so can easily be skinned if need be.

After developing the software, I realised that I didn't have any time to commercialise it effectively, hence why I'm releasing it on GitHub. If anyone else can find a use for it, then that'd be great!

The project required me to document the software I'd written, which I've included in this document. This provides information on how to install and set up Later.

Technical Overview
==================

Later is a web application which centralises messages sent within an organisation. It is best described as a mix between a message board and mailing list. Users can post messages to the board and select the relevant recipients.

Later then emails recipients who may be interested in this message - either immediately or in a summary message sent weekly. The message will also be available for everyone to see from within the web application, significantly increasing the potential reach of the message.


Later is built using freely available and standard technologies: a LAMP stack (Linux, Apache, MySQL, PHP) and Bootstrap. In order to install Later, a server capable of running a LAMP stack and SSH must be available. This server will need to be accessible through HTTP protocols, so it would make sense for it to have a domain or subdomain, depending on the organisation's needs.

Installation
------------

To install Later,copy the files to the root web directory on the server. The file `includes/config.php` (config file) must be modified to include the relevant information required to run the system, such as MySQL login details and timezone.

The settings in the config file are:

 - MYSQL_SERVER - if unknown, try
 - MYSQL_USERNAME
 - MYSQL_PASSWORD
 - MYSQL_DB
 - TIMEZONE - The timezone to use (according to [PHP's Supported Timezones](http://www.php.net/manual/en/timezones. php))
 - RESULTS_PER_PAGE - The number of messages to show per page
 - API_KEY - a randomly generated string used in the login flow (see Login API)
 - FROM_NAME - The name to show when an email is received
 - FROM_EMAIL - The email address to send messages from
 - LOGIN_URL - The URL to the beginning of the login flow (see Login API)

Once this file has been setup, run `admin/install.php` in a command line tool (logging onto the server using SSH). This can be run by navigating to the folder admin, then typing the line:

```
 php -q install.php
```

This will create the tables in the database.

Login API
---------

The login flow for Later has been designed to be incorporated within an existing login system. Once your system has authenticated the user, their email address is passed using a cURL (or similar) web connection to the Later API:

```
 POST [Later URL]/api/login
 DATA: email=[email]&sig=[signature]
```

The signature is generated concatenating the user's email address with a vertical line symbol ( `|` ) followed by the current date in the format `dd.mm.yy`.

For example: `bob@example.com|14.09.13`

This string is then hashed using a [HMAC SHA256 hash algorithm](http://en.wikipedia.org/wiki/Hash-based_ message_authentication_code) using the API key (which you defined in the `includes/config.php` file).

In PHP, this may look like the code block below.

Once the POST request is made, if successful, the JSON response string will contain an element called key. This can simply be appended to the end of any URL as a GET parameter named key.

The LOGIN_URL set up in the config file may have a GET parameter appended to it, called redirect. If the user expects to be redirected back to a particular place within Later, then it will be this parameter. Otherwise, the user can be redirected back to the Later homepage.

This guide may seem quite overwhelming, so for a sample PHP integration, visit `later-extern/index.php` which has some basic code.

```php
$email=''; //the user's email
$api_key=''; //the API key
$api_url='[Later URL]/api/';

if(isset($_GET['redirect'])) {
	
	$signature=hash_hmac( 'sha256' , $email . '|' . date('d.m.y') , $api_key);
	$post_string='email=' . urlencode($email) . '&sig=' . urlencode($signature);
	
	$ch=curl_init();
	curl_setopt($ch , CURLOPT_URL , $api_url.'/login');
	curl_setopt($ch , CURLOPT_RETURNTRANSFER , true);
	curl_setopt($ch , CURLOPT_POST , true);
	curl_setopt($ch , CURLOPT_POSTFIELDS , $post_string);

	$res = curl_exec($ch);
	$response = @json_decode( trim($res) , true);
	curl_close($ch);
	
	if($response) {
		//All's good
	}
}
```

Data Import
-----------

| Column Name | Data type | Notes |
| ----------- | --------- | ----- |
| Email address | `string` | |
| First name | `string` | |
| Last name | `string` | |
| Admin | `boolean` (`integer`) | Whether or not the user should be a system administrator (`1` for yes, `0` for no) |
| Stage | `integer` | The student's current stage of study (numeric value), or `-1` for staff |
| Course title / job description | `string` | |
| School / department | `string` | |

To import users into Later, you simply need a [CSV file](http://en.wikipedia.org/wiki/Comma- separated_values) with the columns above.

This CSV file should be placed within the folder admin.

Then, using SSH, the file admin/import. php should be run in a command line tool, by navigating to the folder admin then running the following line:

```
 php -q import.php
```

This will guide you through the import process.

First of all, it will select all the CSV files within the folder and allow you to choose which one (by typing in the relevant number for the file). Once selected, it will automatically import all the records from this file. It will let you know all the stages as it goes through them, then it will show you that it has completed.

The import process updates the user table and auto generates groups.

As a result of this, please note that when your users change (which will often happen annually, but can be updated at any point) all the users must be reimported into the system.

This will remove any users who are not reimported and update all the details for the existing users (as well as adding new users). But, importantly, this saves the existing preferences for existing users, rather than resetting them to the default settings. This helps to maintain a consistent user experience.

Cron
----

Finally, a cron job will need to be set up to automatically send out emails.

This should run weekly. A recommended time could be Friday morning, for instance. The command should be:

```
 php -q [Later folder]/admin/cron.php
```

To find out more about cron and cron jobs, the [Wikipedia article on cron](http://en.wikipedia.org/wiki/Cron) is very comprehensive.

An example of the timing may be as follows:

```
30 9 * * 5
```

As stated above, this will run the cron job at half past 9 every Friday morning. More information on cron timings can, again, be found in the [Wikipedia article](http://en.wikipedia.org/wiki/Cron).

If you've got this far, then Later should be installed successfully! You can go ahead and add some categories and send out messages.

Additional Points
-----------------
You may want to remove the CSV file once you've imported it. This will prevent anyone trying to find the file.

You may also want to update the permissions of `admin/import.php`, `admin/cron.php` and remove `install.php`, in order to stop these being run by unauthorised people.

Note that the admin directory will need to be accessible by users of the web application, as your system administrators would need to use this.

User Guide
==========

Later is a tool which allows you to specifically target your bulk email messages to ensure that you are not overwhelming your recipients with unnecessary messages.

Groups
------

Groups are collections of people who receive a message. A message can be received by any number of groups. They help to manage a large number of people quickly and easily.

If you are a member of staff, you may already have automatically generated groups available. If not, then you are able to create your own groups by going to the "My Groups" page and clicking "Create a Group".

Sending a message
-----------------

Once you have a group, you can send a message. Click "Send Message" at the top of the page.

This will take you to a page which looks similar to when you send a standard email message, with a few additional features.

Enter the subject and message as you normally would. Then you should find a predefined list of categories which you can add your message to. Select the relevant category and then enter the recipients for the message.

There's a final option: only show to selected recipients. By default, all messages are available for everyone to see from the homepage. But if you would like a bit more privacy for your message, then this message will only be visible for the recipients you have just selected.

Once you're done, click "Send" and the message will be sent out.

Anyone is free to send messages out within the system, however only certain people have access to the auto-generated groups. The managers of these groups, and all groups, can be managed in the "My Groups" tab.

Updating your email preferences
-------------------------------

Later allows you to choose which emails you want to receive. To do this, click "Settings" at the top of the page.

Here you will see your settings for each of the categories. You can opt to receive all the emails for a particular category (this is the default option), a weekly summary of all the messages or no emails at all.

The weekly summary will include all the messages you have received in a single email, with links so you can find more information about each message.

Even if you opt to not receive any messages, then you can still view all the messages that have been sent by going to Later and clicking "Home" at the top.

You also can filter the messages by category using the menu on the left hand side.

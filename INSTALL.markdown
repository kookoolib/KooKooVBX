# KooKooVBX Install Guide
http://www.KooKooVBX.org

Installing KooKooVBX is quick and easy, just follow this five step guide to get up and running in no time.

# Requirements
* Web Server
* MySQL 5+
* PHP 5.2 recommended _using less than PHP 5.2 requires [PEAR Services_JSON](http://pear.php.net/package/Services_JSON)_
* [Twilio Account](https://www.twilio.com/try-twilio) OR [Tropo Account](https://www.tropo.com/) 

# Step 1: Get the Code
[Download](http://www.KooKooVBX.org/download) the latest release and unpack the source code into your webroot.

# Step 2: Create a Database
KooKooVBX needs a database from either your hosting provider or your own web server.  Please see your hosting provider's documentation on creating databases for more info.

# Step 3: Run the Installer
Open your web browser and navigate to the URL of your KooKooVBX installation. The installer will check that your system meets the minimum requirements and will configure your new phone system. You may have to adjust the permissions on the KooKooVBX upload and configuration directories.

# Step 4: Connect to KooKoo
During the install process, you will be prompted for your KooKoo API credentials. You can obtain your you API KEY from you KooKoo dashboard. You must be logged in to your http://kookoo.in account to access your API KEY.
# Step 5: Login
Navigate to the URL of your KooKooVBX installation and login using the account you created during the installation. Once you're logged in you'll be able to add users and groups to your new phone system. You can also add devices, provision phone numbers, configure voicemail, and design call flows.

# Step 6: Profit!
__That's it, you're all set.__
KooKooVBX is open source and extensible so feel free to skin it, hack it, and sell it!

# Installing on Godaddy

Add this to the bottom of KooKooVBX/config/config.php
$config['uri_protocol'] = 'REQUEST_URI';
$config['index_page'] = '';


# More Resources
Now that you've got a working installation you can:
* Extend KooKooVBX by installing a plugin or writing your own - http://www.KooKooVBX.org/plugins
* Scratch your own itch and help improve KooKooVBX - http://www.KooKooVBX.org/get-involved/
* Read the documentation - documents http://www.KooKooVBX.org/docs
* Get support - http://getsatisfaction.com/support


----

KooKooVBX Step by Step Explanation

# About
This page provides detailed information for each step of the KooKooVBX install process.

# KooKooVBX Server Check
KooKooVBX requires the software listed below. It is all available for free and is open source. KooKooVBX is supported and should run on all major linux distributions. KooKooVBX may run on other platforms (namely Windows) but is currently unsupported.

1. PHP version
 * We recommend PHP 5.2 or higher, although you can get away with earlier versions as long as you have [PEAR Services_JSON](http://pear.php.net/package/Services_JSON) installed
1. CURL support
 * KooKooVBX requires CURL. If you don't meet this requirement, install the CURL module.
1. Apache version
 * We recommend Apache version 2.2+. Earlier versions and other web servers may work, but are currently unsupported.
1. MySQL support
 * We require MySQL version 5+.
1. APC support (optional)
 * APC is recommended, but not required.
1. Config directory writable
 * The configuration directory must be writable by the user your webserver is running as for the KooKooVBX installation to complete. The path to the configuration directory is '<webroot>/KooKooVBX/config'. On unix systems you can adjust the permissions with the `chown` and `chmod` commands.
1. SimpleXML support
 * KooKooVBX requires SimpleXML. If you don't meet this requirement, install the SimpleXML module.
1. JSON support
 * KooKooVBX requires JSON. If you don't meet this requirement, install the JSON module. For versions of PHP prior to 5.2 you will need the [PEAR Services_JSON](http://pear.php.net/package/Services_JSON) PECL module installed.
1. Upload directory writable
 * The upload directory must be writable by the user your webserver is running as for the KooKooVBX installation to complete. The path to the configuration directory is '<webroot>/KooKooVBX/audio-uploads'. On unix systems you can adjust the permissions with the `chown` and `chmod` commands.

# Configure Database
KooKooVBX requires a MySQL database. You should create a database, and a user with permissions to access the database for KooKooVBX. If you are running KooKooVBX on a shared hosting environment, you may have to use the tools provided by your hosting provider to create a new database and database user.

1. Hostname
 * In most cases this should be 'localhost'. If your database server is on a machine other than your webserver, specify it's address here.
1. Username
 * The username to use when connecting to MySQL
1. Password
 * The password to use when connecting to MySQL
1. Database Name
 * The name of your KooKooVBX database.

# Connect to your KooKoo account
KooKooVBX requires a KooKoo account to enable provisioning phone numbers, sending and receiving voice calls, and sending and receiving SMS from KooKoo. 

1. KooKoo API KEY
 * This is your account identifier, it is unique to you and private and should not be shared

# Optional Settings
KooKooVBX has the ability to send email notifications to users. This includes password reset emails, voicemail and SMS notifications, as well as notifications defined by plugins.

1. From Email
 * This email address will be used in the from field of emails sent from your KooKooVBX installation.

# Setup Your Account
In order to administer your KooKooVBX installation you'll need to create a user account. This is the account that will be used to manage your KooKooVBX installation. This information can be updated later from within KooKooVBX.

1. Email
 * The email address to be associated with your KooKooVBX admin account.
1. Password
 * The password for your KooKooVBX admin account.
1. First Name
 * The first name of your KooKooVBX administrator.
1. Last Name
 * The last name of your KooKooVBX administrator.


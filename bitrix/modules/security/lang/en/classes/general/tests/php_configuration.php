<?
$MESS["SECURITY_SITE_CHECKER_PhpConfigurationTest_NAME"] = "PHP settings check";
$MESS["SECURITY_SITE_CHECKER_PHP_ENTROPY"] = "No additional entropy source for session ID is defined";
$MESS["SECURITY_SITE_CHECKER_PHP_ENTROPY_RECOMMENDATION"] = "Add the following line to the PHP settings:<br>session.entropy_file = /dev/urandom<br>session.entropy_length = 128";
$MESS["SECURITY_SITE_CHECKER_PHP_INCLUDE"] = "URL wrappers are enabled";
$MESS["SECURITY_SITE_CHECKER_PHP_INCLUDE_DETAIL"] = "This option is absolutely not recommended.";
$MESS["SECURITY_SITE_CHECKER_PHP_INCLUDE_RECOMMENDATION"] = "Add or edit the following line in the PHP settings:<br>allow_url_include = Off";
$MESS["SECURITY_SITE_CHECKER_PHP_FOPEN"] = "Read access for URL wrappers is enabled";
$MESS["SECURITY_SITE_CHECKER_PHP_FOPEN_DETAIL"] = "This option is not required, but may possibly be used by an attacker.";
$MESS["SECURITY_SITE_CHECKER_PHP_FOPEN_RECOMMENDATION"] = "Add or edit the following line in the PHP settings:<br>allow_url_fopen = Off";
$MESS["SECURITY_SITE_CHECKER_PHP_ASP"] = "ASP style tags are enabled";
$MESS["SECURITY_SITE_CHECKER_PHP_ASP_DETAIL"] = "Only a few developers know that this option exists. This option is redundant.";
$MESS["SECURITY_SITE_CHECKER_PHP_ASP_RECOMMENDATION"] = "Add or edit the following line in the PHP settings:<br>asp_tags = Off";
$MESS["SECURITY_SITE_CHECKER_LOW_PHP_VERSION_ENTROPY"] = "Version of php is outdated";
$MESS["SECURITY_SITE_CHECKER_LOW_PHP_VERSION_ENTROPY_DETAIL"] = "The current version of php does not support the installation of an additional source of entropy when creating a session ID";
$MESS["SECURITY_SITE_CHECKER_LOW_PHP_VERSION_ENTROPY_RECOMMENDATION"] = "Update php to version 5.3.3 or higher, ideally to the most recent stable version";
$MESS["SECURITY_SITE_CHECKER_PHP_ENTROPY_DETAIL"] = "The lack of additional entropy may be used to predict random numbers.";
$MESS["SECURITY_SITE_CHECKER_PHP_HTTPONLY"] = "Cookies are accessible from JavaScript";
$MESS["SECURITY_SITE_CHECKER_PHP_HTTPONLY_DETAIL"] = "Making cookies accessible from JavaScript will increase severity of successful XSS attacks.";
$MESS["SECURITY_SITE_CHECKER_PHP_HTTPONLY_RECOMMENDATION"] = "Add or edit the following line in the PHP settings:<br>session.cookie_httponly = On";
$MESS["SECURITY_SITE_CHECKER_PHP_COOKIEONLY"] = "Session ID's are saved in other storages besides cookies";
$MESS["SECURITY_SITE_CHECKER_PHP_COOKIEONLY_DETAIL"] = "Saving a session ID in places other than cookies may lead to session hijacking.";
$MESS["SECURITY_SITE_CHECKER_PHP_COOKIEONLY_RECOMMENDATION"] = "Add or edit the following line in the PHP settings:<br>session.use_only_cookies = On";
$MESS["SECURITY_SITE_CHECKER_PHP_MBSTRING_SUBSTITUTE"] = "Mbstring deletes invalid characters";
$MESS["SECURITY_SITE_CHECKER_PHP_MBSTRING_SUBSTITUTE_DETAIL"] = "The ability to delete invalid characters may be exploited for the so-called invalid byte sequence attacks.";
$MESS["SECURITY_SITE_CHECKER_PHP_MBSTRING_SUBSTITUTE_RECOMMENDATION"] = "In PHP settings, change the value of mbstring.substitute_character to anything but \"none\".";
$MESS["SECURITY_SITE_CHECKER_ZEND_MULTIBYTE_ENABLED"] = "Parsing of PHP source files in multibyte encodings is enabled.";
$MESS["SECURITY_SITE_CHECKER_ZEND_MULTIBYTE_ENABLED_DETAIL"] = "Enabling this option is highly undesirable because dynamically generated PHP scripts like cache files may be parsed in an unpredictable way.";
$MESS["SECURITY_SITE_CHECKER_ZEND_MULTIBYTE_ENABLED_RECOMMENDATION"] = "For PHP 5.4.0 and later, specify zend.multibyte = off in the php.ini file.";
$MESS["SECURITY_SITE_CHECKER_DISPLAY_ERRORS"] = "Errors are set to be printed to output.";
$MESS["SECURITY_SITE_CHECKER_DISPLAY_ERRORS_DETAIL"] = "Displaying errors is useful for development and debugging but must be turned off in release versions.";
$MESS["SECURITY_SITE_CHECKER_DISPLAY_ERRORS_RECOMMENDATION"] = "Add or edit the following line in the PHP settings:<br>display_errors = Off";
$MESS["SECURITY_SITE_CHECKER_PHP_REQUEST_ORDER"] = "The _REQUEST array is created in a wrong sequence";
$MESS["SECURITY_SITE_CHECKER_PHP_REQUEST_ORDER_DETAIL"] = "The _REQUEST array generally does not need to contain all variables except for _GET and _POST. Otherwise, this may compromise user and/or site data, or bring about other unforeseen consequences.";
$MESS["SECURITY_SITE_CHECKER_PHP_REQUEST_ORDER_RECOMMENDATION"] = "Add or edit the following line in the PHP settings:<br>request_order = \"GP\"";
$MESS["SECURITY_SITE_CHECKER_PHP_REQUEST_ORDER_ADDITIONAL"] = "Current value: \"#CURRENT#\"<br>Recommended: \"#RECOMMENDED#\"";
$MESS["SECURITY_SITE_CHECKER_MAIL_ADD_HEADER"] = "Mail messages contain the UID of the PHP process";
$MESS["SECURITY_SITE_CHECKER_MAIL_ADD_HEADER_DETAIL"] = "Each of the e-mail message includes the \"X-PHP-Originating-Script\" header containing the UID and name of a script that sends a message. This allows an attacker to learn a user that is running PHP.";
$MESS["SECURITY_SITE_CHECKER_MAIL_ADD_HEADER_RECOMMENDATION"] = "Add or edit the following line in the PHP settings:<br>mail.add_x_header = Off";
?>
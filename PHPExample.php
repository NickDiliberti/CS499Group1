<?php

/*
 *sendNote.php
 *
 *Takes $_REQUEST['q'] and looks it up on the indicated table
 *
 *If it's there, sends an email using programatically-
 *assembled headers and content.
*/

//------------------------------------SETUP------------------------------------

require_once "Mail.php";                      //Include PEAR's Mail.php.  Change to 'pear/Mail.php' to use on the site.
date_default_timezone_set('America/Chicago'); //Set timezone to Eastern

$DB_LOCATION = '*********************'; //Database URL
$DB_USERNAME = '***********';           //Login username
$DB_PW       = '********';              //Login password
$DB_NAME     = '***********';           //Database name
$TABLE_NAME  = '********';              //Table name

$link = mysqli_connect($DB_LOCATION, $DB_USERNAME, $DB_PW); //Open link to database

//Check to ensure link was successful
if (!$link) { 
    echo 'Failed to connect to server';
    return;
}
//Check to ensure communication character set was established
if (!mysqli_set_charset($link, 'utf8')) {
    echo 'Failed to set charset';
    return;
}
//Check to ensure the database was able to be selected
if (!mysqli_select_db($link, $DB_NAME)) { 
    echo 'Failed to select database';
    return;
}

//--------------------------------QUERY DATABASE-------------------------------

$input    = mysqli_real_escape_string($link, $_REQUEST["q"]);           //Recieve and sanitize input string from request URL
$query    = "SELECT * FROM $TABLE_NAME WHERE CompanyName = '$input'";   //Construct SQL query
$response = mysqli_query($link, $query);                                //Query database and store result

//Check if input is an empty string or if $input is an empty variable
if (!$input || "" == $input) { 
    echo "Function has been dry-fired; no contact indicated.";
    return;
}

//Check if $response is an empty variable
if (!$response) { 
    echo "No record of $input exists";
    return;
}

$info      = mysqli_fetch_assoc($response); //Extract contact information from the query response
$comp_name = $info["CompanyName"];          //Extract company name from the contact information
$rep_name  = $info["RepresentativeName"];   //Extract respresentative name from the contact information
$email     = $info["Email"];                //Extract email address from the contact information

//--------------------------------PREPARE EMAIL--------------------------------

$EMAIL_TO = '<*******************************>'; //Address to which the email will be sent

$EMAIL_HEADERS = array(
    'From' => '*****************************',  //Name and <address> the email will claim to come from
    'To' => $EMAIL_TO,                          //Address the email will claim to be addressed to
    'Subject' => '****************************' //Subject line of the email
);

$EMAIL_BODY = "At " . date('h:i \o\n l F jS') .                             
", $rep_name at $comp_name ($email) referred a customer" . 
" to the ************************* associate site."; //Body of the email, including date & time of referral

$SMTP_OPTIONS = array(
    'host' => 'ssl://*****************************',//Host of the mailserver to be used
    'port' => '465',                                //Port on the host to be used
    'auth' => true,                                 //Indicating intent to be authorized
    'username' => '******************************', //Username to authenticate with the mail server
    'password' => '**********'                      //Password to authenticate with the mail server
); 

//---------------------------------SEND EMAIL----------------------------------

$smtp =& Mail::factory('smtp', $SMTP_OPTIONS); //Use PEAR's SMTP class to remotely connect to the mail server

$mail = $smtp->send($EMAIL_TO, $EMAIL_HEADERS, $EMAIL_BODY); //Send the message after filling in the set-up fields

//Investigate success/failure of the sending
if (PEAR::isError($mail)) { 
    echo ('<p>' . $mail->getMessage() . '</p>');    //Report error message
} else {
    echo ('Message has been sent!');                //Report success
}

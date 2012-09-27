
<?php
include 'mcavatar.class.php';

$notice = 'This is an example of the MCAvatar-class.<br />
  Edit the "members.xml" file in your favorite texteditor to modify the shown users<br />
  Use the "Readme.txt" te help getting you started.';

//derive a new object from MCAvatar and pass the respected variables to the constructor
$mca = new MCAvatar('members.xml', 'tmp', 'avatars', $notice);

//setup a basic html page
$html = '<html>
          <head>
            <title>MCAvatar Example</title>
            <link rel="stylesheet" type="text/css" href="/mcavatar.css" />
          </head>
          <body>';

$html .= '<a href="?update"><input style="width:100%;margin-bottom:20px;" type="button" value="Update Avatars" /></a>';

//build the avatar page within the body tags
$html .= $mca->mca_build_page();

//show the message in (unformatted) underneath the avatars
$html .= '<br />Script Messages:<br /><pre>'.print_r($mca->mca_messages(), true).'</pre>';

//close the html page
$html .= '</body>
          </hmtl>';

echo $html;


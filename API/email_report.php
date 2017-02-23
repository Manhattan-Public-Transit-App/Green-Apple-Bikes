<?php
$report = $_POST['report'];
$email_from = 'yourname@yourwebsite.com';
$email_subject = "New Form submission";
$email_body = "You have received a new message from the user $name.\n".
"Here is the message:\n $message".
$to = "cbald24@ksu.edu";
$headers = "From: cbald24@ksu.edu \r\n";
$headers .= "Reply-To: ace24stang@yahoo.com \r\n";
mail($to,$email_subject,$email_body,$headers);
?>
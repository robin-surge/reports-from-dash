<?php
/*Send email with attachment, script by Olaf Lederer. See https://www.tutdepot.com/php-e-mail-attachment-script/ */
function mail_attachment($file, $mailto, $from_mail, $from_name, $replyto, $subject, $body)
{
    $file_size = filesize($file);
    $filename = basename($file);
    $handle = fopen($file, "r");
    $content = fread($handle, $file_size);
    fclose($handle);
    $content = chunk_split(base64_encode($content));
    $uid = md5(uniqid(time()));
    $header = "From: " . $from_name . " <" . $from_mail . ">\r\n";
    $header .= "Reply-To: " . $replyto . "\r\n";
    $header .= "MIME-Version: 1.0\r\n";
    $header .= "Content-Type: multipart/mixed; boundary=\"" . $uid . "\"\r\n";
    $message = "--" . $uid . "\r\n";
    $message .= "Content-type:text/plain; charset=iso-8859-1\r\n";
    $message .= "Content-Transfer-Encoding: 7bit\r\n";
    $message .= $body . "\r\n";
    $message .= "--" . $uid . "\r\n";
    $message .= "Content-Type: application/octet-stream; name=\"" . $filename . "\"\r\n"; // use different content types here
    $message .= "Content-Transfer-Encoding: base64\r\n";
    $message .= "Content-Disposition: attachment; filename=\"" . $filename . "\"\r\n";
    $message .= $content . "\r\n";
    $message .= "--" . $uid . "--";
    if (mail($mailto, $subject, $message, $header)) {
        return true;
    } else {
        return false;
    }
}
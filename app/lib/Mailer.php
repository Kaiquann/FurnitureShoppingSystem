<?php

/**
 * @author: Chong Jun Xiang
 */
const SENDER_EMAIL   = 'no-reply@lovedolove.me';
const RESEND_API_KEY = 're_25i4459y_79PWxsUgRutcnWbjymKbUyB7';

// Simple Title Message Email Design
const SIMPLE_TITLE_MESSAGE_MAIL_BODY = "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width,initial-scale=1'><title>{{ title }}</title><style>body{font-family:Arial,sans-serif}.container{width:100%;max-width:600px;margin:0 auto}.card{border:1px solid #ddd;border-radius:5px;margin-top:50px;padding:20px;text-align:center}.card-title{font-size:24px;margin-bottom:20px}.card-text{font-size:18px;margin-bottom:20px}.btn{display:inline-block;color:#fff;background-color:#0d6efd;border-color:#0d6efd;padding:.375rem .75rem;font-size:1rem;line-height:1.5;border-radius:.25rem;text-decoration:none}.btn:hover{color:#fff;background-color:#0b5ed7;border-color:#0a58ca}</style></head><body><div class='container'><div class='card'><h5 class='card-title'>{{ title }}</h5><p class='card-text'>{{ message }}</p></div></div></body></html>";

$resend = Resend::client(RESEND_API_KEY);

/**
 * @author: RESEND Company
 * Modified by: Chong Jun Xiang
 * This function is used to send an email.
 */
function sendEmail($to, $subject, $body, $attachments = null)
{
    global $resend;
    $payload = [
        'from'    => SENDER_EMAIL,
        'to'      => $to,
        'subject' => $subject,
        'html'    => $body
    ];
    if ($attachments) {
        $payload['attachments'] = $attachments;
    }
    try {
        $resend->emails->send($payload);
        return true;
    }
    catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: " . $resend->ErrorInfo);
        return false;
    }
}

function generateBodyWithTitleMessage($title, $message)
{
    return str_replace(
        ['{{ title }}', '{{ message }}'],
        [$title, $message],
        SIMPLE_TITLE_MESSAGE_MAIL_BODY
    );
}

?>

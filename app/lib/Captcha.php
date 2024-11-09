<?php

/**
 * @author: Chong Jun Xiang
 * This function is used to verify the captcha.
 */

const CF_CAPTCHA_URL        = "https://challenges.cloudflare.com/turnstile/v0/siteverify";
const CF_CAPTCHA_SITE_KEY   = "0x4AAAAAAAiYqpFtwfi-RgEG";
const CF_CAPTCHA_SECRET_KEY = "0x4AAAAAAAiYqvJ6_bTVeqHDHOiLFFEqKi0";
const CF_CAPTCHA_HTML       = "<div class='cf-turnstile' data-sitekey='CF_CAPTCHA_SITE_KEY'></div>";

function html_captcha()
{
    return str_replace("CF_CAPTCHA_SITE_KEY", CF_CAPTCHA_SITE_KEY, CF_CAPTCHA_HTML);
}

function verify_captcha($turnstile_token)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, CF_CAPTCHA_URL);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "secret=" . CF_CAPTCHA_SECRET_KEY . "&response=" . $turnstile_token);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    $response = json_decode($response, true);
    return $response["success"];
}

?>

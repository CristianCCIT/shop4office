<?php
/*
  $Id: validations.php 1739 2007-12-20 00:52:16Z hpdl $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  ////////////////////////////////////////////////////////////////////////////////////////////////
  //
  // Function    : tep_validate_email
  //
  // Arguments   : email   email address to be checked
  //
  // Return      : true  - valid email address
  //               false - invalid email address
  //
  // Description : function for validating email address that conforms to RFC 822 specs
  //
  //               This function is converted from a JavaScript written by
  //               Sandeep V. Tamhankar (stamhankar@hotmail.com). The original JavaScript
  //               is available at http://javascript.internet.com
  //
  // Sample Valid Addresses:
  //
  //    first.last@host.com
  //    firstlast@host.to
  //    "first last"@host.com
  //    "first@last"@host.com
  //    first-last@host.com
  //    first.last@[123.123.123.123]
  //
  // Invalid Addresses:
  //
  //    first last@host.com
  //
  //
  ////////////////////////////////////////////////////////////////////////////////////////////////
  function tep_validate_email($email) {
    $valid_address = true;

    $mail_pat = '/^(.+)@(.+)$/i';
    $valid_chars = "[^] \(\)<>@,;:\.\\\"\[]";
    $atom = "$valid_chars+";
    $quoted_user='(\"[^\"]*\")';
    $word = "($atom|$quoted_user)";
    $user_pat = "/^$word(\.$word)*$/i";
    $ip_domain_pat='/^\[([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\]$/i';
    $domain_pat = "/^$atom(\.$atom)*$/i";

    if (preg_match($mail_pat, $email, $components)) {
      $user = $components[1];
      $domain = $components[2];
      // validate user
      if (preg_match($user_pat, $user)) {
        // validate domain
        if (preg_match($ip_domain_pat, $domain, $ip_components)) {
          // this is an IP address
      	  for ($i=1;$i<=4;$i++) {
      	    if ($ip_components[$i] > 255) {
      	      $valid_address = false;
      	      break;
      	    }
          }
        }
        else {
          // Domain is a name, not an IP
          if (preg_match($domain_pat, $domain)) {
            /* domain name seems valid, but now make sure that it ends in a valid TLD or ccTLD
               and that there's a hostname preceding the domain or country. */
            $domain_components = explode(".", $domain);
            // Make sure there's a host name preceding the domain.
            if (sizeof($domain_components) < 2) {
              $valid_address = false;
            } else {
              $top_level_domain = strtolower($domain_components[sizeof($domain_components)-1]);
              // Allow all 2-letter TLDs (ccTLDs)
              if (preg_match('/^[a-z][a-z]$/i', $top_level_domain) != 1) {
                $tld_pattern = '';
                // Get authorized TLDs from text file
                $tlds = file(DIR_WS_INCLUDES . 'tld.txt');
                while (list(,$line) = each($tlds)) {
                  // Get rid of comments
                  $words = explode('#', $line);
                  $tld = trim($words[0]);
                  // TLDs should be 3 letters or more
                  if (preg_match('/^[a-z]{3,}$/i', $tld) == 1) {
                    $tld_pattern .= '^' . $tld . '$|';
                  }
                }
                // Remove last '|'
                $tld_pattern = substr($tld_pattern, 0, -1);
                if (preg_match("/$tld_pattern/i", $top_level_domain) == 0) {
                    $valid_address = false;
                }
              }
            }
          }
          else {
      	    $valid_address = false;
      	  }
      	}
      }
      else {
        $valid_address = false;
      }
    }
    else {
      $valid_address = false;
    }
    if ($valid_address && ENTRY_EMAIL_ADDRESS_CHECK == 'true') {
      if (!checkdnsrr($domain, "MX") && !checkdnsrr($domain, "A")) {
        $valid_address = false;
      }
    }
    return $valid_address;
  }
  
/*CAPTCHA*/
function tep_captcha_code($characters) {
	// list all possible characters, similar looking characters and vowels have been removed
	// when using other than basic mono font make sure letters are recognizable
	$possible = '23456789bcdghkmnpqrsvwxz';
	$code = '';
	$i = 0;
	while ($i < $characters) {
		$code .= substr($possible, mt_rand(0, strlen($possible)-1), 1);
		$i++;
	}
	return $code;
}
function tep_captcha_image($width='150',$height='50',$characters='5') {
	$code = tep_captcha_code($characters);
	/* font size will be 65% of the image height */
	$font_size = $height * 0.65;
	$image = @imagecreate($width, $height) or die('Cannot initialize new GD image stream');
	/* set the colours */
	$background_color = imagecolorallocate($image, 255, 255, 255);
	$text_color = imagecolorallocate($image, 68, 68, 68);
	$noise_color = imagecolorallocate($image, 165, 165, 165);
	/* generate random dots in background */
	for( $i=0; $i<($width*$height)/3; $i++ ) {
		imagefilledellipse($image, mt_rand(0,$width), mt_rand(0,$height), 1, 1, $noise_color);
	}
	/* generate random lines in background */
	for( $i=0; $i<($width*$height)/150; $i++ ) {
		imageline($image, mt_rand(0,$width), mt_rand(0,$height), mt_rand(0,$width), mt_rand(0,$height), $noise_color);
	}
	/* create textbox and add text */
	$textbox = imagettfbbox($font_size, 0, DIR_FS_CATALOG.CAPTCHA_FONT , $code) or die('Error in imagettfbbox function');
	$x = ($width - $textbox[4])*0.5;
	$y = ($height - $textbox[5])*0.45; // just a little higher than middle for decenders
	imagettftext($image, $font_size, 0, $x, $y, $text_color, DIR_FS_CATALOG.CAPTCHA_FONT , $code) or die('Error in imagettftext function');
			/* output captcha image to file */
			imagejpeg($image, DIR_FS_CATALOG.CAPTCHA_IMAGE);
			imagedestroy($image);
			/* return the code that matches the image */
			return $code;
}
/*CAPTCHA*/
function validate_email($email) {
	return filter_var($email, FILTER_VALIDATE_EMAIL);
}
function validate_phone($phone) {
	$replace_chars = array('/', '.', ' ','(', ')');
	$phone = str_replace($replace_chars, '', $phone);
	return preg_match("/^[0-9]+$/", $phone);
}
?>
<?php
  require_once('includes/application_top.php');
  $process = false;
  if (isset($_POST['action']) && ($_POST['action'] == 'process')) {
    $process = true;

    if (ACCOUNT_GENDER == 'true') {
      if (isset($_POST['gender'])) {
        $gender = tep_db_prepare_input($_POST['gender']);
      } else {
        $gender = false;
      }
    }
    $firstname = tep_db_prepare_input($_POST['firstname']);
    $lastname = tep_db_prepare_input($_POST['lastname']);
    if (ACCOUNT_DOB == 'true') {
        $dob = tep_db_prepare_input($_POST['dob']);
    }
    $email_address = tep_db_prepare_input($_POST['email_address']);
    if (ACCOUNT_COMPANY == 'true') {
        $company = tep_db_prepare_input($_POST['company']);
        $btwnr = tep_db_prepare_input($_POST['btwnr']);
    }
    /*FORUM*/
    $forum_username = tep_db_prepare_input($_POST['forum_username']);
    /*FORUM*/
    $street_address = tep_db_prepare_input($_POST['street_address']);
    if (ACCOUNT_SUBURB == 'true') {
        $suburb = tep_db_prepare_input($_POST['suburb']);
    }
    $postcode = tep_db_prepare_input($_POST['postcode']);
    $city = tep_db_prepare_input($_POST['city']);
    if (ACCOUNT_STATE == 'true') {
      $state = tep_db_prepare_input($_POST['state']);
      if (isset($_POST['zone_id'])) {
        $zone_id = tep_db_prepare_input($_POST['zone_id']);
      } else {
        $zone_id = false;
      }
    }
    $country = tep_db_prepare_input($_POST['country']);
    $telephone = tep_db_prepare_input($_POST['telephone']);
    $fax = tep_db_prepare_input($_POST['fax']);

    if ((CREATE_ACCOUNT_MODE=='Direct access') || (CREATE_ACCOUNT_MODE=='Moderated access')) {
        $password = tep_db_prepare_input($_POST['password']);
        $confirmation = tep_db_prepare_input($_POST['confirmation']);
    }
    if (CONDITIONS_CREATE_ACCOUNT != 'Uitgeschakeld' && CONDITIONS_MUST_ACCEPT == 'true') {
        $terms = tep_db_prepare_input($_POST['TermsAgree']);
    }

    $error = false;
    if (strlen($firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
      $error = true;
      $messageStack->add('firstname', sprintf(Translate('Uw voornaam moet minstens %s karakters bevatten'), ENTRY_FIRST_NAME_MIN_LENGTH));
    }
    if (strlen($lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
      $error = true;
      $messageStack->add('lastname', sprintf(Translate('Uw achternaam moet minstens %s karakters bevatten'), ENTRY_LAST_NAME_MIN_LENGTH));
    }
    if (tep_validate_email($email_address) == false) {
      $error = true;
      $messageStack->add('email_address', Translate('Gelieve een geldig e-mailadres in te geven'));
    } else {
      $check_email_query = tep_db_query("select count(*) as total from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($email_address) . "'");
      $check_email = tep_db_fetch_array($check_email_query);
      if ($check_email['total'] > 0) {
        $error = true;
        $messageStack->add('email_address_exists', Translate('Het ingegeven e-mailadres bestaat al in ons systeem. Gelieve in te loggen of een account te registreren met een ander e-mailadres'));
      }
    }
    /*FORUM*/
    if ((FORUM_ACTIVE=='true') && (FORUM_SYNC_USERS=='true')) {
        if (strlen($forum_username) < ENTRY_FORUM_USERNAME_MIN_LENGTH) {
          $error = true;
          $messageStack->add('forum_username', sprintf(Translate('Uw gebruikersnaam moet minstens %s karakters bevatten'), ENTRY_FORUM_USERNAME_MIN_LENGTH));
        }
        /*check username*/
        $check_username_query = tep_db_query("SELECT user_id FROM ".FORUM_DB_DATABASE.".users WHERE username_clean = '".strtolower($forum_username)."'");
        $check_username = tep_db_fetch_array($check_username_query);
        if (tep_db_num_rows($check_username_query)>0) {
            $error = true;
            $messageStack->add('forum_username_exists', Translate('Deze gebruikernaam voor het forum is reeds in gebruik.'));
        }
        /*check username*/
        $check_email_query = tep_db_query("SELECT user_id FROM ".FORUM_DB_DATABASE.".users WHERE user_email = '".strtolower($email_address)."'");
        $check_email = tep_db_fetch_array($check_email_query);
        if (tep_db_num_rows($check_email_query)>0) {
            $error = true;
            $messageStack->add('email_address_exists', Translate('Het ingegeven e-mailadres bestaat al in ons systeem. Gelieve in te loggen of een account te registreren met een ander e-mailadres'));
        }
    }
    /*FORUM*/
    if (!preg_match("/[a-zA-Z]\s\d/", $street_address)) {
        $error = true;
        $messageStack->add('street_address', Translate('Gelieve uw straat EN huisnummer in te geven.'));
    }
    if (strlen($postcode) < ENTRY_POSTCODE_MIN_LENGTH) {
      $error = true;
      $messageStack->add('postcode', sprintf(Translate('Uw postcode moet minstens %s karakters bevatten'), ENTRY_POSTCODE_MIN_LENGTH));
    }
    if (strlen($city) < ENTRY_CITY_MIN_LENGTH) {
      $error = true;
      $messageStack->add('city', sprintf(Translate('Uw woonplaats moet minstens %s karakters bevatten'),ENTRY_CITY_MIN_LENGTH));
    }
    if (is_numeric($country) == false || $country == '0') {
        $error = true;
        $messageStack->add('country', Translate('Gelieve een land uit de lijst te selecteren'));
    }
    /*
    if ($country == '21') {
        if (!preg_match("/\d{3}\/\d{2}\.\d{2}\.\d{2}/", $telephone)) {
            $error = true;
            $messageStack->add('telephone', Translate('Gelieve op een correcte manier uw telefoonnummer in te geven.'));
        }
        if ($fax != '') {
            if (!preg_match("/\d{3}\/\d{2}\.\d{2}\.\d{2}/", $fax)) {
                $error = true;
                $messageStack->add('fax', Translate('Gelieve op de correcte manier uw faxnummer in te geven.'));
            }
        }
    } else if ($country == '150') {
        if (!preg_match("/\d{3}\-\d{7}/", $telephone)) {
            $error = true;
            $messageStack->add('telephone', Translate('Gelieve op een correcte manier uw telefoonnummer in te geven.'));
        }
        if ($fax != '') {
            if (!preg_match("/\d{3}\-\d{7}/", $fax)) {
                $error = true;
                $messageStack->add('fax', Translate('Gelieve op de correcte manier uw faxnummer in te geven.'));
            }
        }
    } else { */
        if (strlen($telephone) < 5) {
            $error = true;
            $messageStack->add('telephone', Translate('Gelieve op een correcte manier uw telefoonnummer in te geven.'));
        }
        if ($fax != '') {
            if (strlen($fax) < 5) {
                $error = true;
                $messageStack->add('fax', Translate('Gelieve op de correcte manier uw faxnummer in te geven.'));
            }
        }
    /*}*/
    if (CREATE_ACCOUNT_MODE=='Register') {
        if (strlen($password) < ENTRY_PASSWORD_MIN_LENGTH) {
          $error = true;
          $messageStack->add('password', sprintf(Translate('Uw paswoord moet minstens %s karakters bevatten'),ENTRY_PASSWORD_MIN_LENGTH));
        } elseif ($password != $confirmation) {
          $error = true;
          $messageStack->add('confirmation', Translate('Wachtwoord en Wachtwoord Bevestiging moeten gelijk zijn aan elkaar'));
        }
    }
    if (CONDITIONS_CREATE_ACCOUNT != 'Uitgeschakeld' && CONDITIONS_MUST_ACCEPT == 'true') {
        if (!$terms) {
            $error = true;
            $messageStack->add('terms', Translate('U moet akkoord gaan met de algemene voorwaarden voor u een account kan aanmaken!'));
        }
    }

    if ($error == false) {
        if ((CREATE_ACCOUNT_MODE=='Direct access') || (CREATE_ACCOUNT_MODE=='Moderated access')) {
                if (CREATE_ACCOUNT_MODE=='Moderated access') {
                    $status = '0';
                } else {
                    $status = '1';
                }
                $lists = PHPLIST_LISTNUMBERS;
                $lists = explode(';', $lists);
                $newsletter = false;
                foreach ($lists as $key=>$list) {
                    if (isset($_POST['newsletters_'.$list])) {
                        put_user_in_list($list, 'subscribe', $email_address, $lastname.' '.$firstname);
                        $newsletter = true;
                    }
                }
              $sql_data_array = array('customers_firstname' => $lastname.' '.$firstname,
                                      'customers_lastname' => '',
                                      'customers_email_address' => $email_address,
                                      'customers_telephone' => $telephone,
                                      'customers_fax' => $fax,
                                      'customers_newsletter' => $newsletter,
                                      'customers_password' => tep_encrypt_password($password),
                                      'status' => $status);

              if (ACCOUNT_GENDER == 'true') $sql_data_array['customers_gender'] = $gender;
              if (ACCOUNT_DOB == 'true') $sql_data_array['customers_dob'] = tep_date_raw($dob);

              tep_db_perform(TABLE_CUSTOMERS, $sql_data_array);

              $customer_id = tep_db_insert_id();

              $sql_data_array = array('customers_id' => $customer_id,
                                      'entry_firstname' => $lastname.' '.$firstname,
                                      'entry_lastname' => '',
                                      'entry_street_address' => $street_address,
                                      'entry_postcode' => $postcode,
                                      'entry_city' => $city,
                                      'entry_country_id' => $country);

              if (ACCOUNT_GENDER == 'true') $sql_data_array['entry_gender'] = $gender;
              if (ACCOUNT_COMPANY == 'true') $sql_data_array['entry_company'] = $company;
              if (ACCOUNT_COMPANY == 'true') $sql_data_array['billing_tva_intracom'] = $btwnr;
              if (ACCOUNT_SUBURB == 'true') $sql_data_array['entry_suburb'] = $suburb;
              if (ACCOUNT_STATE == 'true') {
                if ($zone_id > 0) {
                  $sql_data_array['entry_zone_id'] = $zone_id;
                  $sql_data_array['entry_state'] = '';
                } else {
                  $sql_data_array['entry_zone_id'] = '0';
                  $sql_data_array['entry_state'] = $state;
                }
              }

              tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);

              $address_id = tep_db_insert_id();

              tep_db_query("update " . TABLE_CUSTOMERS . " set customers_default_address_id = '" . (int)$address_id . "' where customers_id = '" . (int)$customer_id . "'");

              tep_db_query("insert into " . TABLE_CUSTOMERS_INFO . " (customers_info_id, customers_info_number_of_logons, customers_info_date_account_created) values ('" . (int)$customer_id . "', '0', now())");

              if (SESSION_RECREATE == 'True') {
                tep_session_recreate();
              }

              $customer_first_name = $firstname;
              $customer_default_address_id = $address_id;
              $customer_country_id = $country;
              $customer_zone_id = $zone_id;
            if (CREATE_ACCOUNT_MODE=='Direct access') {

                /*FORUM*/
                if ((FORUM_ACTIVE=='true') && (FORUM_SYNC_USERS=='true')) {
                    /*add user*/
                    $sql_data_array = array('user_type' => '0',
                                          'group_id' => '10',
                                          'user_permissions' => '',
                                          'user_ip' => $_SERVER['REMOTE_ADDR'],
                                          'user_regdate' => time(),
                                          'username' => $forum_username,
                                          'username_clean' => strtolower($forum_username),
                                          'user_password' => phpbb_hash($password),
                                          'user_passchg' => time(),
                                          'user_email' => strtolower($email_address),
                                          'user_email_hash' => phpbb_email_hash(strtolower($email_address)),
                                          'user_lastvisit' => time(),
                                          'user_lastmark' => time(),
                                          'user_lastpage' => FILENAME_CREATE_ACCOUNT,
                                          'user_lang' => 'nl',
                                          'user_timezone' => '1.00',
                                          'user_dst' => '1',
                                          'user_dateformat' => 'd M Y, H:i',
                                          'user_style' => '3',
                                          'user_form_salt' => unique_id(),
                                          'user_new' => '1'
                                          );
                    tep_db_perform(FORUM_DB_DATABASE.'.users', $sql_data_array, 'insert', false);
                    /*get user id*/
                    $get_forum_user_query = tep_db_query("SELECT user_id FROM ".FORUM_DB_DATABASE.".users WHERE user_email = '".$email_address."'");
                    $get_forum_user = tep_db_fetch_array($get_forum_user_query);
                    $get_usergroup_query = tep_db_query("SELECT group_id FROM ".FORUM_DB_DATABASE.".groups WHERE group_name = 'REGISTERED'");
                    $get_usergroup = tep_db_fetch_array($get_usergroup_query);
                    /*add user to groups*/
                    tep_db_query("INSERT INTO ".FORUM_DB_DATABASE.".user_group (group_id, user_id, group_leader, user_pending) VALUES ('".$get_usergroup['group_id']."','".$get_forum_user['user_id']."','0','0')");
                    /*user is created, let's add session for autologin*/
                    if (FORUM_CROSS_LOGIN=='true') {
                        $user->session_begin();
                        $auth->acl($user->data);

                        $auth->login(strtolower($forum_username), $password, false, 1, 0);
                    }
                }
                /*FORUM*/
              tep_session_register('customer_id');
              tep_session_register('customer_first_name');
              tep_session_register('customer_default_address_id');
              tep_session_register('customer_country_id');
              tep_session_register('customer_zone_id');

                // restore cart contents
                $cart->restore_contents();


            // BEGIN SEND HTML MAIL//
            $email_table = '<table cellspacing="0" cellpadding="0" border="0" width="587" bgcolor="#ffffff">';
            $email_table .= '<tr><td style="width:5px;"></td><td>';
            $email_table .= Translate('Beste ').'&nbsp;'.$lastname.' '.$firstname."\n\n";
            $email_table .= "\n" . sprintf(Translate('Wij heten u welkom bij <b>%s</b>'), STORE_NAME) . "\n\n";
            $email_table .= "\n" . Translate('U kunt nu gebruik maken van <b>verschillende services</b> die wij aanbieden. Enkele van deze services zijn:' . "\n\n" . '<li><b>Permanente Winkelwagen</b> - Elk product die u hierin plaatst zal daar blijven totdat u ze zelf verwijderd, of gaat afrekenen.' . "\n" . '<li><b>Bestel Geschiedenis</b> - Bekijk de bestellingen die u eerder heeft geplaatst.' . "\n\n");
// Start - CREDIT CLASS Gift Voucher Contribution
            if (NEW_SIGNUP_GIFT_VOUCHER_AMOUNT > 0) {
                $coupon_code = create_coupon_code();
                $insert_query = tep_db_query("insert into coupons (coupon_code, coupon_type, coupon_amount, date_created) values ('".$coupon_code."', 'G', '".NEW_SIGNUP_GIFT_VOUCHER_AMOUNT."', now())");
                $insert_id = tep_db_insert_id();
                $insert_query = tep_db_query("insert into coupon_email_track (coupon_id, customer_id_sent, sent_firstname, emailed_to, date_sent) values ('".$insert_id."', '0', 'Admin', '".$email_address."', now() )");

                $email_table .= sprintf(Translate('Als deel van de verwelkoming van nieuwe klanten hebben wij u een cadeaubon verstuurd ter waarde van %s'), $currencies->format(NEW_SIGNUP_GIFT_VOUCHER_AMOUNT)) . "\n\n";
                $email_table .= Translate('U kan de cadeaubon valideren door op deze link te klikken').' <a href="'.tep_href_link(FILENAME_GV_REDEEM, 'gift=' . $coupon_code,'NONSSL', false).'">'.tep_href_link(FILENAME_GV_REDEEM, 'gift=' . $coupon_code,'NONSSL', false).'</a>'."\n\n";
            }
            if (NEW_SIGNUP_DISCOUNT_COUPON != '') {
                $coupon_code = NEW_SIGNUP_DISCOUNT_COUPON;
                $coupon_query = tep_db_query("select * from coupons where coupon_code = '".$coupon_code."'");
                $coupon = tep_db_fetch_array($coupon_query);
                $coupon_id = $coupon['coupon_id'];
                $coupon_desc_query = tep_db_query("select * from coupons_description where coupon_id = '".$coupon_id."' and language_id = '".(int)$languages_id."'");
                $coupon_desc = tep_db_fetch_array($coupon_desc_query);
                $insert_query = tep_db_query("insert into coupon_email_track (coupon_id, customer_id_sent, sent_firstname, emailed_to, date_sent) values ('".$coupon_id."', '0', 'Admin', '".$email_address."', now() )");


//////////////////////////////////////////////////////////////// - #1179 - 30-05-2013
//attach coupon code details in customer email only if coupn module is enabled
                                if(($status = get_coupon_status()) == 'on'){
									$email_table .= Translate('Proficiat, om uw eerste bezoek aan onze shop aangenamer te maken zenden wij u een kortings coupon.')."\n";
									$email_table .= sprintf(Translate('Om de coupon te gebruiken vult u de coupon code, %s, in tijdens de checkout.'), $coupon['coupon_code'])."\n\n";
                                }
//////////////////////////////////////////////////////////////// - #1179 - 30-05-2013


            }
// End - CREDIT CLASS Gift Voucher Contribution
            $email_table .= "\n" . Translate('Voor hulp met een van deze services kunt u een email sturen naar ' . STORE_NAME . ': ' . STORE_OWNER_EMAIL_ADDRESS . '.' . "\n\n");
            $email_table .= '</td><td style="width: 5px;"></td></tr></table>';
            $name = $lastname . " " . $firstname;
            $Varlogo = '<a href="' . HTTP_SERVER . DIR_WS_CATALOG . '"><img src="'. HTTP_SERVER . DIR_WS_CATALOG . DIR_WS_IMAGES.'mail/logo.jpg" border="0" /></a> ';
            $Vartable1 = '<table width="100%"  border="0" cellpadding="0" cellspacing="0" bgcolor="#ffffff">';
            $Vartable2 = '<table width="100%" border="0" cellpadding="3" cellspacing="3" bgcolor="#EFEFEF">';
            $Vartext1 = '<h1>'.Translate('Account aanmaken').'</h1>';
            $Vartext2 = $email_table;//content
            $Varcopyright = 'Copyright &copy; '.date('Y');
            $Varmailfooter = Translate('Dit email adres is ingegeven op onze website door u of door een van onze bezoekers. Als u zich niet ingeschreven hebt op onze website contacteer ons dan via').' <a href="mailto:' . STORE_OWNER_EMAIL_ADDRESS . '">' . STORE_OWNER_EMAIL_ADDRESS . '</a>';
            require(DIR_WS_MODULES . 'email/html_create_account.php');
            $email_text = $html_email_text;
            if (EMAIL_USE_HTML == 'true') {
                $email_text;
            } else {
                $email_text = sprintf(Translate('Beste %s'), $lastname.' '.$firstname);
                $email_text .= "\n" . sprintf(Translate('Wij heten u welkom bij <b>%s</b>)' . "\n\n"),STORE_NAME);
                $email_text .= "\n" . Translate('U kunt nu gebruik maken van <b>verschillende services</b> die wij aanbieden. Enkele van deze services zijn:' . "\n\n" . '<li><b>Permanente Winkelwagen</b> - Elk product die u hierin plaatst zal daar blijven totdat u ze zelf verwijderd, of gaat afrekenen.' . "\n" . '<li><b>Bestel Geschiedenis</b> - Bekijk de bestellingen die u eerder heeft geplaatst.' . "\n\n");
                $email_text .= "\n" . sprintf(Translate('Voor help met een van deze services kunt u een email sturen naar %s: %s' . "\n\n"), STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);
            }
            //END SEND HTML EMAIL//
            tep_mail($name, $email_address, sprintf(Translate('Welkom bij %s'),STORE_NAME), $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
            tep_redirect(tep_href_link(FILENAME_CREATE_ACCOUNT_SUCCESS, '', 'SSL'));
            } else {
                //moderated access

                //MAIL TO OWNER
                $email_table = '<table cellspacing="0" cellpadding="0" border="0" width="587" bgcolor="#ffffff">';
                $email_table .= '<tr><td style="width:5px;"></td><td>';
                $email_table .= Translate('Beste ').' '.Translate('beheerder')."\n\n";
                $email_table .= "\n" . sprintf(Translate('Een bezoeker heeft zich geregistreerd via %s'), STORE_NAME) . "\n\n";
                $email_table .= "\n\n" . Translate('Deze klant zal pas kunnen inloggen op het beveiligd gedeelte van de website, nadat u de account activeert door middel van onderstaande link.')."\n\n";
                $email_table .= "\n\n".'<a href="'.HTTP_SERVER.DIR_WS_HTTP_CATALOG.'scripts/user_activate.php?user='.$email_address.'">' . Translate('account activeren')."</a>"."\n\n";
                $email_table .= '<table cellspacing="0" cellpadding="3" border="0" width="100%">';
                $email_table .= '<tr><td width="150">' . Translate('Voornaam').': </td><td>'.$firstname.'</td></tr>';
                $email_table .= "<tr><td>" . Translate('Achternaam').': </td><td>'.$lastname.'</td></tr>';
                $email_table .= "<tr><td>" . Translate('E-mailadres').': </td><td>'.$email_address.'</td></tr>';
                if (ACCOUNT_COMPANY=='true') {
                $email_table .= "<tr><td>" . Translate('Bedrijfsnaam').': </td><td>'.$company.'</td></tr>';
                $email_table .= "<tr><td>" . Translate('BTW Nummer').': </td><td>'.$btwnr.'</td></tr>';
                }
                $email_table .= "<tr><td>" . Translate('Straat en huisnummer').': </td><td>'.$street_address.'</td></tr>';
                $email_table .= "<tr><td>" . Translate('Postcode').': </td><td>'.$postcode.'</td></tr>';
                $email_table .= "<tr><td>" . Translate('Woonplaats').': </td><td>'.$city.'</td></tr>';
                $email_table .= "<tr><td>" . Translate('Telefoonnummer').': </td><td>'.$telephone.'</td></tr>';
                $email_table .= "<tr><td>" . Translate('Faxnummer').': </td><td>'.$fax.'</td></tr>';
                $email_table .= "<tr><td>" . Translate('Land').': </td><td>'.tep_get_country_name($country).'</td></tr>';
                $email_table .= '</table>';
                $email_table .= '</td><td style="width: 5px;"></td></tr></table>';
                $name = $lastname . " " . $firstname;
                $Varlogo = '<a href="' . HTTP_SERVER . DIR_WS_CATALOG . '"><img src="'. HTTP_SERVER . DIR_WS_CATALOG . DIR_WS_IMAGES.'mail/logo.jpg" border="0" /></a> ';
                $Vartable1 = '<table width="100%"  border="0" cellpadding="0" cellspacing="0" bgcolor="#ffffff">';
                $Vartable2 = '<table width="100%" border="0" cellpadding="3" cellspacing="3" bgcolor="#EFEFEF">';
                $Vartext1 = '<h1>'.Translate('Account aanmaken').'</h1>';
                $Vartext2 = $email_table;//content
                $Varcopyright = Translate('Copyright &copy; 2010');
                $Varmailfooter = Translate('Dit email adres is ingegeven op onze website door u of door een van onze bezoekers. Als u zich niet ingeschreven hebt op onze website contacteer ons dan via').' <a href="mailto:' . STORE_OWNER_EMAIL_ADDRESS . '">' . STORE_OWNER_EMAIL_ADDRESS . '</a>';
                require(DIR_WS_MODULES . 'email/html_create_account.php');
                $email_text = $html_email_text;

                tep_mail(STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, Translate('Nieuwe registratie'), $email_text, $lastname.' '.$firstname, $email_address);

                //MAIL TO CLIENT
                $email_table = '<table cellspacing="0" cellpadding="0" border="0" width="587" bgcolor="#ffffff">';
                $email_table .= '<tr><td style="width:5px;"></td><td>';
                $email_table .= Translate('Beste ').' '.$lastname.' '.$firstname."\n\n";
                $email_table .= "\n\n" . Translate('Uw account voor onze website werd succesvol aangevraagd. Hieronder vind u nog eens de ingevulde gegevens. Uw gegevens zijn aan ons doorgegeven voor moderatie. Van zodra uw account geactiveerd is, ontvangt u hierover een e-mail.')."\n\n";
                $email_table .= '<table cellspacing="0" cellpadding="3" border="0" width="100%">';
                $email_table .= '<tr><td width="150">' . Translate('Voornaam').': </td><td>'.$firstname.'</td></tr>';
                $email_table .= "<tr><td>" . Translate('Achternaam').': </td><td>'.$lastname.'</td></tr>';
                $email_table .= "<tr><td>" . Translate('E-mailadres').': </td><td>'.$email_address.'</td></tr>';
                if (ACCOUNT_COMPANY=='true') {
                $email_table .= "<tr><td>" . Translate('Bedrijfsnaam').': </td><td>'.$company.'</td></tr>';
                $email_table .= "<tr><td>" . Translate('BTW Nummer').': </td><td>'.$btwnr.'</td></tr>';
                }
                $email_table .= "<tr><td>" . Translate('Straat en huisnummer').': </td><td>'.$street_address.'</td></tr>';
                $email_table .= "<tr><td>" . Translate('Postcode').': </td><td>'.$postcode.'</td></tr>';
                $email_table .= "<tr><td>" . Translate('Woonplaats').': </td><td>'.$city.'</td></tr>';
                $email_table .= "<tr><td>" . Translate('Telefoonnummer').': </td><td>'.$telephone.'</td></tr>';
                $email_table .= "<tr><td>" . Translate('Faxnummer').': </td><td>'.$fax.'</td></tr>';
                $email_table .= "<tr><td>" . Translate('Land').': </td><td>'.tep_get_country_name($country).'</td></tr>';
                $email_table .= '</table>';
                $email_table .= '</td><td style="width: 5px;"></td></tr></table>';
                $name = $lastname . " " . $firstname;
                $Varlogo = '<a href="' . HTTP_SERVER . DIR_WS_CATALOG . '"><img src="'. HTTP_SERVER . DIR_WS_CATALOG . DIR_WS_IMAGES.'mail/logo.jpg" border="0" /></a> ';
                $Vartable1 = '<table width="100%"  border="0" cellpadding="0" cellspacing="0" bgcolor="#ffffff">';
                $Vartable2 = '<table width="100%" border="0" cellpadding="3" cellspacing="3" bgcolor="#EFEFEF">';
                $Vartext1 = '<h1>'.Translate('Account aanmaken').'</h1>';
                $Vartext2 = $email_table;//content
                $Varcopyright = Translate('Copyright &copy; 2010');
                $Varmailfooter = Translate('Dit email adres is ingegeven op onze website door u of door een van onze bezoekers. Als u zich niet ingeschreven hebt op onze website contacteer ons dan via').' <a href="mailto:' . STORE_OWNER_EMAIL_ADDRESS . '">' . STORE_OWNER_EMAIL_ADDRESS . '</a>';
                require(DIR_WS_MODULES . 'email/html_create_account.php');
                $email_text = $html_email_text;

                tep_mail($lastname.' '.$firstname, $email_address, Translate('Nieuwe registratie'), $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);

                tep_redirect(tep_href_link(FILENAME_CREATE_ACCOUNT_SUCCESS, '', 'SSL'));
            }
        } else {
            //Request account
            $email_table = '<table cellspacing="0" cellpadding="0" border="0" width="587" bgcolor="#ffffff">';
            $email_table .= '<tr><td style="width:5px;"></td><td>';
            $email_table .= Translate('Beste ').' '.Translate('beheerder')."\n\n";
            $email_table .= "\n" . sprintf(Translate('Een bezoeker heeft zich geregistreerd via %s'), STORE_NAME) . "\n\n";
            $email_table .= '<table cellspacing="0" cellpadding="3" border="0" width="100%">';
            $email_table .= '<tr><td width="150">' . Translate('Voornaam').': </td><td>'.$firstname.'</td></tr>';
            $email_table .= "<tr><td>" . Translate('Achternaam').': </td><td>'.$lastname.'</td></tr>';
            $email_table .= "<tr><td>" . Translate('E-mailadres').': </td><td>'.$email_address.'</td></tr>';
            if (ACCOUNT_COMPANY=='true') {
            $email_table .= "<tr><td>" . Translate('Bedrijfsnaam').': </td><td>'.$company.'</td></tr>';
            $email_table .= "<tr><td>" . Translate('BTW Nummer').': </td><td>'.$btwnr.'</td></tr>';
            }
            $email_table .= "<tr><td>" . Translate('Straat en huisnummer').': </td><td>'.$street_address.'</td></tr>';
            $email_table .= "<tr><td>" . Translate('Postcode').': </td><td>'.$postcode.'</td></tr>';
            $email_table .= "<tr><td>" . Translate('Woonplaats').': </td><td>'.$city.'</td></tr>';
            $email_table .= "<tr><td>" . Translate('Telefoonnummer').': </td><td>'.$telephone.'</td></tr>';
            $email_table .= "<tr><td>" . Translate('Faxnummer').': </td><td>'.$fax.'</td></tr>';
            $email_table .= "<tr><td>" . Translate('Land').': </td><td>'.tep_get_country_name($country).'</td></tr>';
            $email_table .= '</table>';
            $email_table .= "\n\n" . Translate('Zonder manuele toevoeging in het softwarepakket, zal deze klant niet toegelaten worden in het beveiligde gedeelte van de website. ')."\n\n";
            $email_table .= '</td><td style="width: 5px;"></td></tr></table>';
            $name = $lastname . " " . $firstname;
            $Varlogo = '<a href="' . HTTP_SERVER . DIR_WS_CATALOG . '"><img src="'. HTTP_SERVER . DIR_WS_CATALOG . DIR_WS_IMAGES.'mail/logo.jpg" border="0" /></a> ';
            $Vartable1 = '<table width="100%"  border="0" cellpadding="0" cellspacing="0" bgcolor="#ffffff">';
            $Vartable2 = '<table width="100%" border="0" cellpadding="3" cellspacing="3" bgcolor="#EFEFEF">';
            $Vartext1 = '<h1>'.Translate('Account aanmaken').'</h1>';
            $Vartext2 = $email_table;//content
            $Varcopyright = Translate('Copyright &copy; 2010');
            $Varmailfooter = Translate('Dit email adres is ingegeven op onze website door u of door een van onze bezoekers. Als u zich niet ingeschreven hebt op onze website contacteer ons dan via').' <a href="mailto:' . STORE_OWNER_EMAIL_ADDRESS . '">' . STORE_OWNER_EMAIL_ADDRESS . '</a>';
            require(DIR_WS_MODULES . 'email/html_create_account.php');
            $email_text = $html_email_text;
            //END SEND HTML EMAIL//
            tep_mail(STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, Translate('Nieuwe registratie'), $email_text, $name, $email_address);
            tep_redirect(tep_href_link(FILENAME_CREATE_ACCOUNT_SUCCESS, '', 'SSL'));
        }
    }
  }

?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">
<link rel="stylesheet" type="text/css" href="stylesheet.css">
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="3" cellpadding="3">
  <tr>
    <td width="<?php echo BOX_WIDTH; ?>" valign="top">
    <table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="2">
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
    </table>
    </td>
<!-- body_text //-->
    <td width="100%" valign="top">
    <?php echo tep_draw_form('create_account', tep_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL'), 'post', 'id="validate_form"').tep_draw_hidden_field('action', 'process'); ?>
    <script type="text/javascript" src="includes/js/form_validation.js"></script>
        <div class="subcategories_box">
            <div class="subcategories_box_content">
        <table border="0" width="100%" cellspacing="0" cellpadding="0" class="create-account">
            <tr>
                <td colspan="2">
                    <?php if (CREATE_ACCOUNT_MODE=='Request') { ?>
                        <h1><?php echo Translate('Account aanvragen'); ?></h1>
                    <?php } else { ?>
                        <h1><?php echo Translate('Account aanmaken'); ?></h1>
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <td class="smallText">
                    <table width="100%">
                        <tr>
                            <td>
                                <?php
                                if ($error) {
                                    echo '<div class="message error" style="text-align:center;"><img width="10" border="0" height="10" alt="" src="images/icons/error.gif">';
                                    echo Translate('Gelieve alle velden correct in te vullen!');
                                    if ($messageStack->size('email_address_exists') > 0) {
                                        echo '<br />'.Translate('Het ingegeven e-mailadres bestaat al in ons systeem. Gelieve in te loggen of een account te registreren met een ander e-mailadres');
                                    }
                                    /*FORUM*/
                                    if ($messageStack->size('forum_username_exists') > 0) {
                                        echo '<br />'.Translate('Deze gebruikernaam voor het forum is reeds in gebruik');
                                    }
                                    /*FORUM*/
                                    echo '</div>';
                                } else {
                                    echo sprintf(Translate("LET OP: Als u reeds een account heeft aangemaakt, log dan in bij de login pagina"), tep_href_link(FILENAME_LOGIN, tep_get_all_get_params(), 'SSL'));
                                }
                                ?>
                            </td>
                            <td class="inputRequirement" align="right" width="100"><?php echo Translate('* Verplicht veld'); ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <?php if ((CREATE_ACCOUNT_MODE=='Request account') || (CREATE_ACCOUNT_MODE=='Moderated access')) { ?>
            <tr>
                <td height="10"></td>
            </tr>
            <tr>
                <td>
                <?php echo Translate('Indien u geen geregistreerd lid bent van deze website, maar u toch producten wenst te bestellen, vul hieronder uw gegevens correct in. Deze gegevens worden door ons ontvangen, gecontroleerd en indien mogelijk wordt u toegevoegd aan onze ledenlijst.'); ?>
                </td>
            </tr>
            <?php } ?>
            <tr>
                <td valign="top">
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="50%" valign="top">
                                <table width="100%">
                                    <tr>
                                        <td valign="top" colspan="3">
                                        <h2 style="margin: 20px 0 0px 0;" class="subtitle"><?php echo Translate('Persoonlijke gegevens'); ?></h2></td>
                                    </tr>
                                    <tr>
                                        <td valign="top">
                                            <table cellpadding="5" cellspacing="5" width="100%" class="loginpage_boxes">
                                                <tr>
                                                    <td><label for="firstname" class="formLabel"><?php echo Translate('Voornaam'); ?>:</label></td>
                                                    <td><label for="lastname" class="formLabel"><?php echo Translate('Achternaam'); ?>:</label></td>
                                                    <td><label for="email_address" class="formLabel"><?php echo Translate('E-mailadres'); ?>:</label></td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input class="inputbox<?php if ($messageStack->size('firstname') > 0) { echo ' error';}?>" type="text" name="firstname" size="30" style="width:170px;" title="<?php echo sprintf(Translate('Uw voornaam moet minstens %s karakters bevatten'), ENTRY_FIRST_NAME_MIN_LENGTH);?>" condition="<?php echo ENTRY_FIRST_NAME_MIN_LENGTH;?>" value="<?php echo $_POST['firstname'];?>" id="firstname" />
                                                        <span class="inputRequirement">&nbsp;*</span>
                                                    </td>
                                                    <td>
                                                        <input class="inputbox<?php if ($messageStack->size('lastname') > 0) { echo ' error';}?>" type="text" name="lastname" size="30" style="width:170px;" title="<?php echo sprintf(Translate('Uw achternaam moet minstens %s karakters bevatten'), ENTRY_LAST_NAME_MIN_LENGTH);?>" condition="<?php echo ENTRY_LAST_NAME_MIN_LENGTH;?>" value="<?php echo $_POST['lastname'];?>" id="lastname" />
                                                        <span class="inputRequirement">&nbsp;*</span>
                                                    </td>
                                                    <td>
                                                        <input class="inputbox<?php if (($messageStack->size('email_address') > 0) || ($messageStack->size('email_address_exists') > 0)) { echo ' error';}?>" type="text" name="email_address" size="30" style="width:170px;" title="<?php echo Translate('Gelieve een geldig e-mailadres in te geven');?>" condition="email_required" value="<?php echo $_POST['email_address'];?>" id="email_address" />
                                                        <span class="inputRequirement">&nbsp;*</span>
                                                    </td>
                                                </tr>
                                                <?php /*FORUM*/ if ((FORUM_ACTIVE=='true') && (FORUM_SYNC_USERS=='true')) { ?>
                                                <tr>
                                                    <td><label for="forum_username" class="formLabel"><?php echo Translate('Forum gebruikersnaam'); ?>:</label></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input class="inputbox<?php if ($messageStack->size('forum_username') > 0) { echo ' error';}?>" type="text" name="forum_username" size="30" style="width:170px;" title="<?php echo sprintf(Translate('Geen mogelijke gebruikersnaam'), ENTRY_FORUM_USERNAME_MIN_LENGTH);?>" condition="<?php echo ENTRY_FORUM_USERNAME_MIN_LENGTH; ?>" value="<?php echo $_POST['forum_username'];?>" id="forum_username" />
                                                        <span class="inputRequirement">&nbsp;*</span>
                                                    </td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                                <?php } /*FORUM*/ ?>
                                            </table>
                                        </td>
                                    </tr>
                                    <?php if (ACCOUNT_COMPANY=='true') { ?>
                                    <tr>
                                        <td valign="top" colspan="3">
                                        <h2 style="margin: 20px 0 0px 0;" class="subtitle"><?php echo Translate('Bedrijfsgegevens'); ?></h2></td>
                                    </tr>
                                    <tr>
                                        <td valign="top">
                                            <table cellpadding="5" cellspacing="5" width="100%" class="loginpage_boxes">
                                                <tr>
                                                    <td><label for="company" class="formLabel"><?php echo Translate('Bedrijfsnaam'); ?>:</label></td>
                                                    <td><label for="btwnr" class="formLabel"><?php echo Translate('BTW Nummer'); ?>:</label></td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td valign="top"><input class="inputbox<?php if ($messageStack->size('company') > 0) echo ' error';?>" type="text" name="company" size="30" style="width:170px;" value="<?php echo $_POST['company'];?>" id="company" /></td>
                                                    <td valign="top">
                                                        <input class="inputbox<?php if ($messageStack->size('btwnr') > 0) echo ' error';?>" type="text" name="btwnr" size="30" style="width:170px;" mask="bv. BE 0000 000 000" title="<?php echo Translate('Vul een geldig btw nummer in!');?>" condition="btw" value="<?php echo $_POST['btwnr'];?>" id="btwnr" />
                                                        <div class="masked">bv. BE 0000 000 000</div>
                                                    </td>
                                                    <td width="183"></td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                    <tr>
                                        <td valign="top" colspan="3">
                                        <h2 style="margin: 20px 0 0px 0;" class="subtitle"><?php echo Translate('Adresgegevens'); ?></h2></td>
                                    </tr>
                                    <tr>
                                        <td valign="top">
                                            <table cellpadding="5" cellspacing="5" width="100%" class="loginpage_boxes">
                                                <tr>
                                                    <td><label for="street_address" class="formLabel"><?php echo Translate('Straat en huisnummer'); ?>:</label></td>
                                                    <td><label for="postcode" class="formLabel"><?php echo Translate('Postcode'); ?>:</label></td>
                                                    <td><label for="city" class="formLabel"><?php echo Translate('Woonplaats'); ?>:</label></td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input class="inputbox<?php if ($messageStack->size('street_address') > 0) echo ' error';?>" type="text" name="street_address" size="30" style="width:170px;" title="<?php echo Translate('De straatnaam moet minstens 5 karakters lang zijn.');?>" condition="reg[a-zA-Z]\s\d_required" value="<?php echo $_POST['street_address'];?>" id="street_address" />
                                                        <span class="inputRequirement">&nbsp;*</span>
                                                    </td>
                                                    <td>
                                                        <input class="inputbox<?php if ($messageStack->size('postcode') > 0) echo ' error';?>" type="text" name="postcode" size="30" style="width:170px;" title="<?php echo sprintf(Translate('Uw postcode moet minstens %s karakters bevatten'), ENTRY_POSTCODE_MIN_LENGTH);?>" condition="<?php echo ENTRY_POSTCODE_MIN_LENGTH;?>" value="<?php echo $_POST['postcode'];?>" id="postcode" />
                                                        <span class="inputRequirement">&nbsp;*</span>
                                                    </td>
                                                    <td>
                                                        <input class="inputbox<?php if ($messageStack->size('city') > 0) echo ' error';?>" type="text" name="city" size="30" style="width:170px;" title="<?php echo sprintf(Translate('Uw woonplaats moet minstens %s karakters bevatten'),ENTRY_CITY_MIN_LENGTH);?>" condition="<?php echo ENTRY_CITY_MIN_LENGTH;?>" value="<?php echo $_POST['city'];?>" id="city" />
                                                        <span class="inputRequirement">&nbsp;*</span>
                                                    </td>
                                                    </tr>
                                                    <tr>
                                                    <td><label for="country" class="formLabel"><?php echo Translate('Land'); ?>:</label></td>
                                                    <td><label for="telephone" class="formLabel"><?php echo Translate('Telefoonnummer'); ?>:</label></td>
                                                    <td><label for="fax" class="formLabel"><?php echo Translate('Faxnummer'); ?>:</label></td>
                                                </tr>
                                                <tr>
                                                    <td valign="top">
                                                        <?php
                                                        $country_title = Translate('Gelieve een land uit de lijst te selecteren');
                                                        if ($messageStack->size('country') > 0) {
                                                            $country_class =' error';
                                                        } else {
                                                            $country_class ='';
                                                        }
                                                        echo tep_get_country_list('country', '', ' class="inputbox'.$country_class.'" style="width:170px;" condition="select_i0" id="country" title="'.$country_title.'"');
                                                        ?>
                                                        <span class="inputRequirement">&nbsp;*</span>
                                                    </td>
                                                    <td valign="top">
																											<input class="inputbox<?php if ($messageStack->size('telephone') > 0) echo ' error';?>" type="text" name="telephone" size="30" style="width:170px;" title="" condition="reg[a-zA-Z0-9]{5}_required" value="<?php echo $_POST['telephone'];?>" id="telephone" />
																											<?php
																											/*
																											/////////////////////////////////////////// #1177 - 27-05-2013
																											<input class="inputbox<?php// if ($messageStack->size('telephone') > 0) echo ' error';?>" type="text" name="telephone" size="30" style="width:170px;" title="" mask="bv. 000/00.00.00" condition="reg\d{3}\/\d{2}\.\d{2}\.\d{2}_required" value="<?php// echo $_POST['telephone'];?>" id="telephone" />
																											/////////////////////////////////////////// #1177 - 27-05-2013
																											* */
																											?>
																											<span class="inputRequirement">&nbsp;*</span>
																											<!--<div class="masked">bv. 000/00.00.00</div>-->
                                                                                                        </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <?php if (ACCOUNT_NEWSLETTER=='true') { ?>
                                    <tr>
                                        <td valign="top" colspan="3">
                                        <h2 style="margin: 20px 0 0px 0;" class="subtitle"><?php echo Translate('Nieuwsbrief'); ?></h2></td>
                                    </tr>
                                    <tr>
                                        <td valign="top">
                                            <table cellpadding="5" cellspacing="5" width="100%" class="loginpage_boxes">
                                                <?php
                                                $lists = PHPLIST_LISTNUMBERS;
                                                $lists = explode(';', $lists);
                                                foreach ($lists as $key=>$list) {
                                                    $newsletter = getListName($list);
                                                    echo '<tr><td width="20">'.tep_draw_checkbox_field('newsletters_'.$list, '1', false, 'id="newsletters_'.$list.'"').'</td>';
                                                    echo '<td><label for="newsletters_'.$list.'" class="formLabel">'.$newsletter.'</label></td></tr>';
                                                }
                                                tep_db_list_close();
                                                tep_db_connect();
                                                ?>
                                            </table>
                                        </td>
                                    </tr>
                                    <?php
                                    }
                                   if ((CREATE_ACCOUNT_MODE=='Direct access') || (CREATE_ACCOUNT_MODE=='Moderated access')) {
                                    ?>
                                    <tr>
                                        <td valign="top">
                                            <h2 style="margin: 20px 0 0px 0;" class="subtitle"><?php echo Translate('Wachtwoord'); ?></h2>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3">
                                            <table cellpadding="5" cellspacing="5" width="100%" class="loginpage_boxes">
                                                <tr>
                                                    <td><label for="password" class="formLabel"><?php echo Translate('Wachtwoord'); ?>:</label></td>
                                                    <td><label for="confirmation" class="formLabel"><?php echo Translate('Wachtwoord bevestigen'); ?>:</label></td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input class="inputbox<?php if ($messageStack->size('password') > 0) echo ' error';?>" type="password" name="password" size="45" style="width:250px;" condition="<?php echo ENTRY_PASSWORD_MIN_LENGTH;?>" title="<?php echo sprintf(Translate('Uw paswoord moet minstens %s karakters bevatten'),ENTRY_PASSWORD_MIN_LENGTH);?>" id="password"  value="<?php echo $_POST['password'];?>" />
                                                        <span class="inputRequirement">&nbsp;*</span>
                                                    </td>
                                                    <td>
                                                        <input class="inputbox<?php if ($messageStack->size('confirmation') > 0) echo ' error';?>" type="password" name="confirmation" size="45" style="width:250px;" title="" condition="confirmation_password_required" id="confirmation" value="<?php echo $_POST['confirmation'];?>" />
                                                        <span class="inputRequirement">&nbsp;*</span>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td height="10"></td>
                                    </tr>
                                    <?php
                                    }
                                    if (CONDITIONS_CREATE_ACCOUNT != 'Uitgeschakeld' && CONDITIONS_MUST_ACCEPT == 'true') {
                                        if (CONDITIONS_CREATE_ACCOUNT == 'Textbox') {
                                    ?>
                                    <tr>
                                        <td id="conditions_textbox">
                                                <div class="conditions_textbox">
                                                    <?php
                                                        $page_query = tep_db_query("SELECT i.infopages_id, it.infopages_title, it.infopages_description, i.type FROM infopages i JOIN infopages_text it USING (infopages_id)
                                                        WHERE i.infopages_status = 1 AND i.type ='condition' AND it.language_id = ".(int)$languages_id);
                                                        $page = tep_db_fetch_array($page_query);
                                                        echo '<h1>'.$page['infopages_title'].'</h1>';
                                                        echo $page['infopages_description'];
                                                    ?>
                                                </div>
                                        </td>
                                    </tr>
                                    <?php
                                        }
                                    if ($messageStack->size('terms') > 0) {
                                        $terms_class = ' error';
                                    } else {
                                        $terms_class = '';
                                    }
                                    ?>
                                    <tr>
                                        <td align="center" id="CAparagraph" class="loginpage_boxes<?php echo $terms_class;?>">
                                            <?php
                                                echo '<label for="TermsAgree">';
                                                $termsAgree = sprintf(Translate("Ik heb de <a href='%s' target='_blank'>algemene voorwaarden</a> gelezen en ga hiermee akkoord:"), tep_href_link('conditions_modal.php'));
                                                if (CONDITIONS_CREATE_ACCOUNT == 'Link') {
                                                    echo $termsAgree;
                                                }else{
                                                    echo strip_tags($termsAgree);
                                                }
                                                echo '</label>';
                                                echo tep_draw_checkbox_field('TermsAgree','true', false, 'id="TermsAgree" onChange="updatebutton()"');
                                            ?>
                                            <script type="text/javascript">
                                                function updatebutton(){
                                                    if($("#TermsAgree").attr("checked")){
                                                        updateSubmitButton();
                                                    } else {
                                                        $('input:submit').css('opacity', '0.5');
                                                    }
                                                }
                                                $(document).ready(function() {
                                                    $('#CAparagraph a').click(function() {
                                                        window.open($(this).attr('href'), '<?php echo Translate('Algemene voorwaarden');?>', 'width=500,height=400,scrollbars=yes');
                                                        return false;
                                                    });
                                                    updatebutton();
                                                    $('input:submit').hover(
                                                        function() {
                                                            if(!$("#TermsAgree").attr("checked")){
                                                                $('#CAparagraph').addClass('error', 200);
                                                                updatebutton();
                                                            }
                                                        },
                                                        function() {
                                                            if(!$("#TermsAgree").attr("checked")){
                                                                $('#CAparagraph').removeClass('error', 200);
                                                                updatebutton();
                                                            }
                                                        }
                                                    );
                                                    $('input:submit').click(function() {
                                                        updatebutton();
                                                        if ((($('input.error').length > 0) && ($('select.error').length > 0) && ($('textarea.error').length > 0)) || ($('.hiddenError').length > 0)) {
                                                            return false;
                                                        }
                                                    });
                                                });
                                            </script>
                                        </td>
                                    </tr>
                                    <?php
                                    }
                                    ?>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr><td height="20"></td></tr>
            <tr>
                <td align="right"><input type="submit" value="<?php echo Translate('Ga verder'); ?>" class="formbutton button-a" id="create_account_submit" /></td>
            </tr>
        </table>
            </div>
            <div class="subcategories_box_bottom"><span><span></span></span></div>
        </div>
        </form>
    </td>
<!-- body_text_eof //-->
<td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="2">
<!-- right_navigation //-->
<?php include(DIR_WS_INCLUDES . 'column_right.php'); ?>
<!-- right_navigation_eof //-->
</table></td>
</tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php include(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
<?php
/**
 * Written by Boris Wintein
 * For aboservice
 * Time: 11:29
 */
class Customer
{
    public $customers_firstname, $customers_email_address, $customers_telephone, $parent_id, $parent_firstname, $children, $options;

    public $error_messages = array();

    // Internal vars.
    private $customers_id, $customers_group_id, $address, $customers_default_address_id, $status, $permissions, $invites;

    // Full row return for the customer.
    private $fullresult;

    // Database Tables. Allows for change of tables if needed.
    private $table_settings, $table_products = TABLE_PRODUCTS, $table_basket = TABLE_CUSTOMERS_BASKET;

    public function __construct ($customers_id) {
        // GET THE PARAMS BOY
        $query = "SELECT * FROM extensions WHERE name = 'Klanten'";
        $resource = tep_db_query($query);
        $temp = tep_db_fetch_array($resource);
        $this->table_settings = $temp['table'];
        $query = "SELECT name, value FROM " . $this->table_settings;
        $resource = tep_db_query($query);

        while ($option = tep_db_fetch_array($resource)) {
            $this->options[$option['name']] = $option['value'];
        }

        if ($customers_id !== null) {

            if (is_int($customers_id)) {
                $this->customers_id = $customers_id;

                $customers_id = (int)mysql_real_escape_string($customers_id);
                $query = "SELECT * FROM customers c WHERE c.customers_id = $customers_id";
                $resource = tep_db_query($query);

                if (tep_db_num_rows($resource) > 0) {
                    $result = tep_db_fetch_array($resource);
                    $this->fullresult = $result;
                    foreach ($result as $key => $value){
                        if (property_exists("Customer", $key)) {
                            $this->$key = $value;
                        }
                    }
                } else {
                    die ("[CUSTOMERS] - The customer object failed to construct. The customers_id has no associated customer.");
                }
            } else {
                die ("[CUSTOMERS] - Ho there sailor, that isn't a userid! I need a integer in order to work!");
            }

            $this->customer_get_perms();
            $this->customer_get_group();

        } else {
            $this->status = 0;
        }
    }

    public function customer_status() {
        if (!empty($this->status)) {
            if ($this->status == 1) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function customer_get_address() {
        if ($this->options['address_book'] == 'on') {
            if (isset($this->address) && !empty($this->address['main'])) {
                return $this->address;
            } else {
                if (empty($this->customers_id)) {
                    $query = "SELECT * FROM address_book WHERE customers_id = $this->customers_id ORDER BY address_book_id";
                    $resource = tep_db_query($query);

                    if (tep_db_num_rows($resource) > 0) {

                        while ($address = tep_db_fetch_array($resource)) {
                            if ($address['address_book_id'] == $this->customers_default_address_id) {
                                $this->address['main'] = $address;
                            } else {
                                $this->address['additional'][] = $address;
                            }
                        }

                        return $this->address;
                    } else {
                        $this->address['main'] = null;
                        $this->address['additional'] = null;
                        return false;
                    }
                } else {
                    die("[CUSTOMERS] - Hey, I can't load the address-data if you don't define a customer!");
                }
            }
        } else {
            return false;
        }
    }

    public function customer_get_group($group_name = false) {
        if ($this->options['customers_group'] == 'on') {
            if (isset($this->customers_group_id) && !empty($this->customers_group_id)) {
                if ($group_name) {
                    $query = "SELECT group_name FROM customers_groups WHERE group_id = $this->customers_group_id";
                    $resource = tep_db_query($query);
                    $result = tep_db_fetch_array($resource);
                    return $result['group_name'];
                } else {
                    return $this->customers_group_id;
                }
            } else {
                $query = "SELECT customers_group_id FROM customers WHERE customers_id = $this->customers_id";
                $resource = tep_db_query($query);
                $result = tep_db_fetch_array($resource);

                $group_id = $this->customers_group_id = $result['group_id'];

                if ($group_name && !empty($group_id)) {
                    $query = "SELECT group_name FROM customers_groups WHERE group_id = $group_id";
                    $resource = tep_db_query($query);
                    $result = tep_db_fetch_array($resource);
                    return $result['group_name'];
                } else {
                    return $this->customers_group_id;
                }


            }
        } else {
            return false;
        }
    }

    public function customer_get_parent() {
        if (!empty ($this->parent_id)) {
            return $this->parent_id;
        } else {
            $query = "SELECT parent_id FROM customers WHERE customers_id = $this->customers_id";
            $resource = tep_db_query($query);
            $result = tep_db_fetch_array($resource);

            $this->parent_id = $result['parent_id'];
            return $this->parent_id;
        }
    }

    public function customer_get_parent_name() {
        if (!empty($this->parent_firstname)) {
            return $this->parent_firstname;
        } else {
            $parent_id = $this->customer_get_parent();
            $query = "SELECT customers_firstname FROM customers WHERE customers_id = $parent_id";
            $resource = tep_db_query($query);
            $customer = tep_db_fetch_array($resource);

            $this->parent_firstname = $customer['customers_firstname'];
            return $customer['customers_firstname'];
        }
    }

    public function customer_set_parent($parent_id, $cust_id = '$this->customers_id') {
        if (is_int($parent_id)) {
            $parent_id = mysql_real_escape_string($parent_id);
            $query = "SELECT customers_firstname FROM customers WHERE customers_id = $parent_id";
            $resource = tep_db_query($query);

            if (tep_db_num_rows($resource) > 0){
                if ($cust_id == '$this->customers_id') {
                    $customers_id = $this->customers_id;
                } else {
                    if (is_int($cust_id)) {
                        $customers_id = $cust_id;
                    } else {
                        return false;
                    }
                }
                $customers_id = mysql_real_escape_string($customers_id);
                $query = "SELECT customers_group_id FROM customers WHERE customers_id = $customers_id";
                $resource = tep_db_query($query);

                if (tep_db_num_rows($resource) > 0) {
                    $query = "UPDATE customers SET parent_id = $parent_id WHERE customers_id = $customers_id";
                    $resource = tep_db_query($query);

                    if ($customers_id == $this->customers_id) {
                        $this->parent_id = $parent_id;
                    }

                    return true;
                } else {
                    return false;
                }

            } else {
                return false;
            }
        } else {
            die("[CUSTOMERS] - Changing customers parent is impossible if you don't supply a valid INT");
        }
    }

    private function customer_is_child($child_id) {
        if (is_int($child_id)) {
            $parent_id = (int)$this->customers_id;
            $query = "SELECT parent_id FROM customers WHERE customers_id = $child_id AND parent_id = $parent_id";
            $resource = tep_db_query($query);

            if (tep_db_num_rows($resource) > 0) {
                return true;
            } else {
                return false;
            }

        } else {
            $vartype = gettype($child_id);
            die ("[CUSTOMER] - Looks like something went wrong. Children must be indicated by integers. An $vartype was given.");
        }
    }

    public function customer_has_children() {
        if (count($this->customer_get_children()) > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function customer_get_children() {
        if (!empty($this->children)) {
            return $this->children;
        }
        $query = "SELECT customers_id, customers_firstname FROM customers WHERE parent_id = $this->customers_id";
        $resource = tep_db_query($query);

        if (tep_db_num_rows($resource) > 0) {
            while ($child = tep_db_fetch_array($resource)) {
                $this->children[] = $child;
            }
            return $this->children;
        } else {
            $this->children = array();
            return $this->children;
        }
    }

    public function customer_children_have_orders() {
        $hasorders = false;
        if ($this->customer_has_children()) {
            foreach ($this->children as $child) {
                if ($this->customer_has_orders($child['customers_id'])) {
                    $hasorders = true;
                }
            }
        }
        return $hasorders;
    }

    public function customer_get_children_orders ($child_ids = null) {
        $orders = array();
        if (is_null($child_ids)) {
            $child_ids = $this->customer_get_children();
        }

        if (is_array($child_ids)) {
            foreach($child_ids as $child) {
                if ($this->customer_is_child((int)$child['customers_id'])) {
                    $key = "id_" . $child['customers_id'];
                    if (($order = $this->customer_get_orders((int)$child['customers_id'])) !== false) {
                        $orders[$key] = $order;
                    }
                }
            }
        } else {
            $vartype = gettype($child_ids);
            die ("[CUSTOMER] - To get child-orders I need a 2-dimensional array. Like this: Array( Array('customers_id' => 10, 'customers_firstname' => 'Foo'), Array('customers_id' => 5, 'customers_firstname' => 'bar')). $vartype was given.");
        }

        return $orders;
    }

    public function customer_delete_child($child_id) {
        $child_id = (int)$child_id;
        if ($this->customer_is_child($child_id)) {

            $query = "DELETE FROM customers WHERE customers_id = $child_id";
            tep_db_query($query);

            $query = "DELETE FROM address_book WHERE customers_id = $child_id";
            tep_db_query($query);

            $query = "DELETE FROM customers_basket WHERE customers_id = $child_id";
            tep_db_query($query);

            $query = "DELETE FROM customers_basket_attributes WHERE customers_id = $child_id";
            tep_db_query($query);

            $query = "DELETE FROM customers_basket_wishlist WHERE customers_id = $child_id";
            tep_db_query($query);

            $query = "DELETE FROM customers_invites WHERE parent_id = $child_id";
            tep_db_query($query);

            return true;
        } else {
            $this->error_messages[] = Translate("De gebruiker die u probeerde te verwijderen is geen subgebruiker. Gebruiker werd niet verwijderd.");
            return false;
        }
    }

    public function customer_has_invites() {
        $invites = $this->customer_get_invites();
        if (!empty($invites)) {
            return true;
        } else {
            return false;
        }
    }

    public function customer_get_invites() {
        if (!empty($this->invites)) {
            return $this->invites;
        } else {
            $query = "SELECT * FROM customers_invites WHERE parent_id = " . $this->getCustomersId();
            $resource = tep_db_query($query);

            $invites = array();
            while ($invite = tep_db_fetch_array($resource)) {
                $invites[] = $invite;
            }

            $this->invites = $invites;
            return $invites;
        }
    }

    public function customer_send_invite($data) {

        $data = $this->clean_data($data);

        $first_name = $data['firstname'];
        $last_name = $data['lastname'];
        $email_address = $data['email_address'];
        $parent_id = (int)$data['parent_id'];

        if (!empty($email_address) && !empty($first_name) && !empty($parent_id)) {

            // Check email if it is valid.
            if (!tep_validate_email($email_address)) {
                $this->error_messages[] = Translate("Het email adres dat u ingaf is niet correct.");
                $error = true;
            }

            // Check lenght of names.
            if (strlen($first_name) < 2 && strlen($last_name) < 2) {
                $this->error_messages[] = Translate("De door u ingevulde naam of achternaam is niet lang genoeg.");
                $error = true;
            }

            if ($error) {
                return false;
            }

            // Check if the parent exists.
            $query = "SELECT customers_firstname FROM customers WHERE customers_id = $parent_id";
            $resource = tep_db_query($query);
            if (tep_db_num_rows($resource) < 1) {
                $this->error_messages[] = Translate("De superuser die u ingaf voor deze uitnodiging bestaat niet.");
                $error = true;
            }
            $result = tep_db_fetch_array($resource);
            $parent_name = $result['customers_firstname'];

            // Check if the user exists.
            $query = "SELECT customers_id FROM customers WHERE customers_email_address = '$email_address'";
            $resource = tep_db_query($query);
            if (tep_db_num_rows($resource) > 0) {
                $this->error_messages[] = Translate("De gebruiker die u probeerde uit te nodigen bestaat reeds.");
                return false;
            }

            $query = "SELECT parent_id FROM customers_invites WHERE email_address = '$email_address'";
            $resource = tep_db_query($query);
            if (tep_db_num_rows($resource) > 0) {
                $this->error_messages[] = Translate("Er is reeds een uitnodiging verstuurd naar dit email-addres.");
                return false;
            }

            // Create a invite hash.
            $hash = tep_db_input($this->customer_create_hash($email_address));
            $query = "SELECT invite_id FROM customers_invites WHERE invite_hash = '$hash'";
            $resource = tep_db_query($query);

            // Be sure that it is unique.
            while (tep_db_num_rows($resource) > 0) {
                $hash = tep_db_input($this->customer_create_hash($email_address));
                $query = "SELECT invite_id FROM customers_invites WHERE invite_hash = '$hash'";
                $resource = tep_db_query($query);
            }

            // Input the invite into the database.
            $cleanhash = mysql_real_escape_string($hash);

            $query = "INSERT INTO customers_invites (invite_hash, first_name, last_name, parent_id, email_address ) VALUES ('$cleanhash', '$first_name', '$last_name', $parent_id, '$email_address')";
            tep_db_query($query);

            $invite_link = tep_href_link(FILENAME_CREATE_ACCOUNT, "invite=$hash");



            // Send a mail to the invitee with the invite link.
            if (file_exists("includes/sts_templates/marsival/invite_mail.html")) {
                ob_start();
                include("includes/sts_templates/marsival/invite_mail.html");
                $invite_mail = ob_get_contents();
                ob_end_clean();
            } else {
                die("failed");
            }

            // Replace all the vars.
            $invite_mail = str_replace("{name}", $first_name . "&nbsp;" . $last_name, $invite_mail);
            $invite_mail = str_replace("{invitelink}", $invite_link, $invite_mail);
            $invite_mail = str_replace("{parent_name}", $parent_name, $invite_mail);

            if ($_SERVER['REMOTE_ADDR'] == "91.183.44.122" && true) {
                return tep_mail($first_name, "boris@aboservice.be", sprintf(Translate("Maak een account aan op %s"), STORE_NAME), $invite_mail, $parent_name . " - " . STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);
            } else {
                return tep_mail($first_name, $email_address, sprintf(Translate("Maak een account aan op %s"), STORE_NAME), $invite_mail, $parent_name . " - " . STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);
            }
        } else {
            $this->error_messages[] = Translate("Gelieve alle velden in te vullen.");
            return false;
        }
    }

    private function customer_create_hash($salt) {
        $code = md5(uniqid($salt) . STORE_NAME);
        return $code;
    }

    public function customer_get_perms() {
        if ($this->options['customers_group'] == 'on') {
            if (!empty($this->permissions) && sizeof($this->permissions) >= 1) {
                return $this->permissions;
            } else {
                if (!empty($this->customers_group_id)) {
                    $group_id = $this->customers_group_id;
                } else {
                    $group_id = $this->customer_get_group();
                }

                if ($group_id) {
                    $query = "SELECT gpk.permission_key, gp.permission_value FROM customers_groups_permissions gp, customers_groups_permissions_keys gpk WHERE gpk.permission_key_id = gp.permission_key_id AND customers_group_id = $group_id";
                    $resource = tep_db_query($query);

                    while ($permission = tep_db_fetch_array($resource)) {
                        $key = (string)$permission['permission_key'];
                        $value = $permission['permission_value'];
                        $this->permissions[$key] = $value;
                    }

                    return $this->permissions;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    public function customer_has_perm($permission) {
        if (empty($this->permissions)) {
            $permissions = $this->customer_get_perms();
        } else {
            $permissions = $this->permissions;
        }

        if (!is_array($permissions)) {

            return false;
        } else {
            if (key_exists($permission, $permissions)) {
                if ($permissions[$permission] == "true") {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
    }

    public function customer_set_group($group_id, $cust_id = '$this->customers_id') {
        if (is_int($group_id) && isset($group_id)) {
            $group_id = mysql_real_escape_string($group_id);
            $query = "SELECT group_name FROM customers_groups WHERE group_id = $group_id";
            $resource = tep_db_query($query);

            if (tep_db_num_rows($resource) > 0) {
                if ($cust_id == '$this->customers_id') {
                    $customers_id = $this->customers_id;
                } else {
                    if (is_int($cust_id)) {
                        $customers_id = $cust_id;
                    } else {
                        return false;
                    }
                }
                $customers_id = mysql_real_escape_string($customers_id);
                $query = "SELECT customers_group_id FROM customers WHERE customers_id = $customers_id";
                $resource = tep_db_query($query);

                if (tep_db_num_rows($resource) > 0) {
                    $query = "UPDATE customers SET customers_group_id = $group_id WHERE customers_id = $customers_id";
                    $resource = tep_db_query($query);

                    return true;
                } else {
                    return false;
                }

            } else {
                return false;
            }

        } else {
            die ("[CUSTOMER] - Changing the customers group is impossible if you don't supply a valid INT");
        }
    }

    /**
     * @param $customers_id
     * @return array|null
     *
     * @description This is an adaptation of the shopping_cart class "restore_content" function.
     * Mostly because the database structure for this bit is mind-boggling, and I don't want to spend hours decoding
     * fucked up code.
     *
     * OsCommerce devs, get your shit together and write some comments. Just saying //attributes isn't a fucking comment
     * and you might as well just write nothing.
     */

    public function customer_get_orders($customers_id) {
        global $languages_id;

        if (is_null($customers_id)) {
            $customer_id = $this->customers_id;
        } else if (is_int($customers_id)) {
            $customer_id = (int)$customers_id;
        } else {
            die("[CUSTOMER] - To get the cart-contents I need a customer_id. This has to be an integer. Not a " . gettype($customers_id));
        }

        $contents = array();

        $table_bask = $this->table_basket;
        $table_bask_attr = TABLE_CUSTOMERS_BASKET_ATTRIBUTES;
        $query = "SELECT products_id, customers_basket_quantity, customers_basket_id FROM $table_bask WHERE customers_id = $customers_id ORDER BY customers_basket_id ASC";
        $resource = tep_db_query($query);

        if (tep_db_num_rows($resource) > 0) {
            while ($products = tep_db_fetch_array($resource)) {
                $customer_id = mysql_real_escape_string($customer_id);

                // Marsival Database is fucked up. Uses strange brackets and awkwardness as products_id.
                $temp = explode("{", $products['products_id']);
                $product_id = mysql_real_escape_string($temp[0]);

                $product_info_query = "SELECT p.products_id, products_model, products_price, products_tax_class_id, products_name, products_opt1 FROM products p, products_description pd WHERE p.products_id = pd.products_id AND p.products_id = $product_id";
                $product_info_resource = tep_db_query($product_info_query);
                $product_info = tep_db_fetch_array($product_info_resource);

                $products_name = str_replace(" ", "_",strtolower(trim($product_info['products_name'])));
                $contents[$products_name] = $product_info;
                $contents[$products_name]["qty"] = $products['customers_basket_quantity'];
                $contents[$products_name]["customers_basket_id"] = $products["customers_basket_id"];

                $attributes_query = "SELECT * FROM $table_bask_attr WHERE customers_id = $customer_id AND products_id = $product_id";
                $attributes_resource = tep_db_query($attributes_query);

                if (tep_db_num_rows($resource) > 0) {
                    // Get the attributes.
                    while ($attributes = tep_db_fetch_array($attributes_resource)) {
                        $options_key_id = mysql_real_escape_string($attributes['products_options_id']);
                        $options_value_id = mysql_real_escape_string($attributes['products_options_value_id']);

                        $table_product_opt = TABLE_PRODUCTS_OPTIONS;
                        $table_product_opt_val = TABLE_PRODUCTS_OPTIONS_VALUES;
                        $table_product_attr = TABLE_PRODUCTS_ATTRIBUTES;

                        $attributes_values_query = "SELECT *
                                                FROM $table_product_opt popt, $table_product_opt_val poval, $table_product_attr pa
                                                WHERE pa.products_id = '$product_id'
                                                AND pa.options_id = '$options_key_id'
                                                AND pa.options_id = popt.products_options_id
                                                AND pa.options_values_id = '$options_value_id'
                                                AND pa.options_values_id = poval.products_options_values_id
                                                AND popt.language_id = '$languages_id'
                                                AND poval.language_id = popt.language_id";
                        $attributes_values_resource = tep_db_query($attributes_values_query);
                        $attributes_values = tep_db_fetch_array($attributes_values_resource);

                        $contents[$products_name]['attributes'][$options_key_id]['products_options_name'] = $attributes_values['products_options_name'];
                        $contents[$products_name]['attributes'][$options_key_id]['options_values_id'] = $options_value_id;

                        if ($options_key_id == '1') {
                            $contents[$products_name]['attributes'][$options_key_id]['products_options_name'] = Translate('Klas');
                        } else {
                            $contents[$products_name]['attributes'][$options_key_id]['products_options_name'] = $attributes_values['products_options_name'];
                        }

                        $contents[$products_name]['attributes'][$options_key_id]['options_values_price'] = $attributes_values['options_values_price'];
                        $contents[$products_name]['attributes'][$options_key_id]['price_prefix'] = $attributes_values['price_prefix'];
                    }
                } else {
                    $contents[$products_name]['attributes'] = false;
                }


            }

            return $contents;
        } else {

            return false;
        }
    }

    public function customer_has_orders($customers_id = '$this->customers_id') {
        $table_bask = $this->table_basket;
        if ($customers_id == '$this->customers_id') {
            $customers_id = $this->customers_id;
        } else {
            $customers_id = mysql_real_escape_string($customers_id);
        }

        $query = "SELECT products_id, customers_basket_quantity, customers_basket_id FROM $table_bask WHERE customers_id = $customers_id ORDER BY customers_basket_id ASC";
        $resource = tep_db_query($query);

        if (tep_db_num_rows($resource) > 0) {
            return true;
        } else {
            return false;
        }
    }

    private function clean_data($data) {
        if (is_array($data)) {
            $cleandata = array();
            foreach ($data as $key => $value) {
                $cleandata[mysql_real_escape_string($key)] = mysql_real_escape_string($value);
            }

            return $cleandata;
        } else {
            return mysql_real_escape_string($data);
        }
    }

    public function setCustomersId($customers_id)
    {
        // Suddenly this is a read-only property.
        return false;
    }

    public function getCustomersId()
    {
        return $this->customers_id;
    }
	
	public function create_customer($data) {
		global $user, $auth, $cart, $customer_id, $currencies;
		$errors = array();
		$process = true;
		$error = false;
		//Gender
		if ($this->options['customers_gender'] == 'on') {
			if (isset($data['gender'])) {
				$gender = mysql_real_escape_string($data['gender']);
			} else {
				$gender = false;
			}
		}
		//Name
		if (isset($data['firstname']) || isset($data['lastname'])) {
			$name = '';
			if ($this->options['customers_firstname'] == 'on') {
				$name .= $data['firstname'];
			}
			if ($this->options['customers_firstname'] == 'on' && $this->options['customers_lastname'] == 'on') {
				$name .= ' ';
			}
			if ($this->options['customers_lastname'] == 'on') {
				$name .= $data['lastname'];
			}
		} else if ($data['name']) {
			$name = $data['name'];
		} else if ($data['fullname']) {
			$name = $data['fullname'];
		}
		if (strlen($name) < ENTRY_FIRST_NAME_MIN_LENGTH) {
			$error = true;
			$errors['name'] = sprintf(Translate('Uw voornaam moet minstens %s karakters bevatten'), ENTRY_FIRST_NAME_MIN_LENGTH);
		}
		//Day of birth
		if ($this->options['customers_dob'] == 'on') {
			$dob = mysql_real_escape_string($data['dob']);
		}
		//Email adress
		if ($this->options['customers_email_address'] == 'on') {
			$email_address = mysql_real_escape_string($data['email_address']);
			if (tep_validate_email($email_address) == false) {
				$error = true;
				$errors['email_address'] = Translate('Gelieve een geldig e-mailadres in te geven');
			} else {
				$check_email_query = tep_db_query("select count(*) as total from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($email_address) . "'");
				$check_email = tep_db_fetch_array($check_email_query);
				if ($check_email['total'] > 0) {
					$error = true;
					$errors['email_address_exists'] = Translate('Het ingegeven e-mailadres bestaat al in ons systeem. Gelieve in te loggen of een account te registreren met een ander e-mailadres');
				}
			}
		}
		//Company
		if ($this->options['entry_company'] == 'on') {
			$company = mysql_real_escape_string($data['company']);
		}
		//BTW nummer
		if ($this->options['billing_tva_intracom'] == 'on') {
			$btwnr = mysql_real_escape_string($data['btwnr']);
		}
		//Forum
		if ((FORUM_ACTIVE=='true') && (FORUM_SYNC_USERS=='true')) {
			if (!isset($data['forum_username'])) {
				$data['forum_username'] = $name;
			}
			$forum_username = mysql_real_escape_string($data['forum_username']);			
			if (strlen($forum_username) < ENTRY_FORUM_USERNAME_MIN_LENGTH) {
				$error = true;
				$errors['forum_username'] = sprintf(Translate('Uw gebruikersnaam moet minstens %s karakters bevatten'), ENTRY_FORUM_USERNAME_MIN_LENGTH);
			}
			/*check username*/
			$check_username_query = tep_db_query("SELECT user_id FROM ".FORUM_DB_DATABASE.".users WHERE username_clean = '".strtolower($forum_username)."'");
			$check_username = tep_db_fetch_array($check_username_query);
			if (tep_db_num_rows($check_username_query)>0) {
				$error = true;
				$errors['forum_username_exists'] = Translate('Deze gebruikernaam voor het forum is reeds in gebruik.');
			}
			/*check username*/
			$check_email_query = tep_db_query("SELECT user_id FROM ".FORUM_DB_DATABASE.".users WHERE user_email = '".strtolower($email_address)."'");
			$check_email = tep_db_fetch_array($check_email_query);
			if (tep_db_num_rows($check_email_query)>0) {
				$error = true;
				$errors['email_address_exists'] = Translate('Het ingegeven e-mailadres bestaat al in ons systeem. Gelieve in te loggen of een account te registreren met een ander e-mailadres');
			}
		}
		//Street address
		if ($this->options['entry_street_address'] == 'on') {
			$street_address = mysql_real_escape_string($data['street_address']);
			if (!preg_match("/[a-zA-Z]\s\d/", $street_address)) {
				$error = true;
				$errors['street_address'] = Translate('Gelieve uw straat EN huisnummer in te geven.');
			}
		}
		//Suburb
		if ($this->options['entry_suburb'] == 'on') {
			$suburb = mysql_real_escape_string($data['suburb']);
		}
		//Postcode
		if ($this->options['entry_postcode'] == 'on') {
			$postcode = mysql_real_escape_string($data['postcode']);
			if (strlen($postcode) < ENTRY_POSTCODE_MIN_LENGTH) {
				$error = true;
				$errors['postcode'] = sprintf(Translate('Uw postcode moet minstens %s karakters bevatten'), ENTRY_POSTCODE_MIN_LENGTH);
			}
		}
		//City
		if ($this->options['entry_city'] == 'on') {
			$city = mysql_real_escape_string($data['city']);
			if (strlen($city) < ENTRY_CITY_MIN_LENGTH) {
				$error = true;
				$errors['city'] = sprintf(Translate('Uw woonplaats moet minstens %s karakters bevatten'),ENTRY_CITY_MIN_LENGTH);
			}
		}
		//State
		if ($this->options['entry_state'] == 'on') {
			$state = mysql_real_escape_string($data['state']);
		}
		//Zone
		if ($this->options['entry_zone'] == 'on' && isset($data['zone_id'])) {
			$zone_id = mysql_real_escape_string($data['zone_id']);
		} else {
			$zone_id = false;
		}
		//Country
		if ($this->options['entry_country'] == 'on') {
			$country = mysql_real_escape_string($data['country']);
			if (is_numeric($country) == false || $country == '0') {
				$error = true;
				$errors['country'] = Translate('Gelieve een land uit de lijst te selecteren');
			}
		}
		//Telephone
		if ($this->options['customers_telephone'] == 'on') {
			$telephone = mysql_real_escape_string($data['telephone']);
			if (strlen($telephone) < 5) {
				$error = true;
				$errors['telephone'] = Translate('Gelieve op een correcte manier uw telefoonnummer in te geven.');
			}
		}
		//Fax
		if ($this->options['customers_fax'] == 'on') {
			$fax = mysql_real_escape_string($data['fax']);
			if ($fax != '') {
				if (strlen($fax) < 5) {
					$error = true;
					$errors['fax'] = Translate('Gelieve op de correcte manier uw faxnummer in te geven.');
				}
			}
		}
		//Create account type
		if ($this->options['create_account_mode'] == 'Direct access' || $this->options['create_account_mode'] == 'Moderated access') {
			$password = mysql_real_escape_string($data['password']);
			$confirmation = mysql_real_escape_string($data['confirmation']);
			if (strlen($password) < ENTRY_PASSWORD_MIN_LENGTH) {
				$error = true;
				$errors['password'] = sprintf(Translate('Uw paswoord moet minstens %s karakters bevatten'),ENTRY_PASSWORD_MIN_LENGTH);
			} elseif ($password != $confirmation) {
				$error = true;
				$errors['confirmation'] = Translate('De ingevoerde wachtwoorden moeten hetzelfde zijn. Voer ze opnieuw in.');
			}
		}
		if ($this->options['conditions_create_account'] != 'Uitgeschakeld' && CONDITIONS_MUST_ACCEPT == 'true') {
			$terms = mysql_real_escape_string($data['TermsAgree']);
			if (!$terms) {
				$error = true;
				$errors['terms'] = Translate('U moet akkoord gaan met de algemene voorwaarden voor u een account kan aanmaken!');
			}
		}
		
		//Check if error
		if ($error) {
			return array('errors' => $errors);
		} else {
			if ($this->options['create_account_mode'] == 'Direct access' || $this->options['create_account_mode'] == 'Moderated access') {
				/********************************/
				/*	Direct Or Moderated access	*/
				/********************************/
				if ($this->options['create_account_mode'] == 'Moderated access') {
					$status = '0';
				} else {
					$status = '1';
				}
				//Newsletter
				$lists = PHPLIST_LISTNUMBERS;
				$lists = explode(';', $lists);
				$newsletter = false;
				foreach ($lists as $key=>$list) {
					if (isset($data['newsletters_'.$list])) {
						put_user_in_list($list, 'subscribe', $email_address, $lastname.' '.$firstname);
						$newsletter = true;
					}
				}
				//Customers table
				$sql_data_array = array('customers_firstname' => $name,
										'customers_lastname' => '',
										'customers_email_address' => $email_address,
										'customers_telephone' => $telephone,
										'customers_fax' => $fax,
										'customers_newsletter' => $newsletter,
										'customers_password' => tep_encrypt_password($password),
										'status' => $status);
				if (ACCOUNT_GENDER == 'true') $sql_data_array['customers_gender'] = $gender;
				if (ACCOUNT_DOB == 'true') $sql_data_array['customers_dob'] = tep_date_raw($dob);
				tep_db_perform('customers', $sql_data_array);
				$customer_id = tep_db_insert_id();
				//Address book table
				$sql_data_array = array('customers_id' => $customer_id,
										'entry_firstname' => $name,
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
				tep_db_perform('address_book', $sql_data_array);
				$address_id = tep_db_insert_id();
				tep_db_query("update customers set customers_default_address_id = '".(int)$address_id."' where customers_id = '".(int)$customer_id."'");
				//Customers info table
				tep_db_query("insert into customers_info (customers_info_id, customers_info_number_of_logons, customers_info_date_account_created) values ('".(int)$customer_id."', '0', now())");
				
				//Session
				if (SESSION_RECREATE == 'True') {
					tep_session_recreate();
				}
				$customer_first_name = $name;
				$customer_default_address_id = $address_id;
				$customer_country_id = $country;
				$customer_zone_id = $zone_id;

				if ($this->options['create_account_mode'] == 'Direct access') {
					/********************/
					/*	Direct access	*/
					/********************/
					//Forum
					if ((FORUM_ACTIVE=='true') && (FORUM_SYNC_USERS=='true') && !empty($forum_username)) {
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
					
					//Session
					$_SESSION['customer_id'] = $customer_id;
					$_SESSION['customer_first_name'] = $customer_first_name;
					$_SESSION['customer_default_address_id'] = $customer_default_address_id;
					$_SESSION['customer_country_id'] = $customer_country_id;
					$_SESSION['customer_zone_id'] = $customer_zone_id;
					// restore cart contents
					$cart->restore_contents();
					
					//HTML mail
					$email_table = '<table cellspacing="0" cellpadding="0" border="0" width="587" bgcolor="#ffffff">';
					$email_table .= '<tr><td style="width:5px;"></td><td>';
					$email_table .= Translate('Beste ').'&nbsp;'.$name."\n\n";
					$email_table .= "\n" . sprintf(Translate('Wij heten u welkom bij <b>%s</b>'), STORE_NAME) . "\n\n";
					$email_table .= "\n" . Translate('U kunt nu gebruik maken van <b>verschillende services</b> die wij aanbieden. Enkele van deze services zijn:' . "\n\n" . '<li><b>Permanente Winkelwagen</b> - Elk product die u hierin plaatst zal daar blijven totdat u ze zelf verwijderd, of gaat afrekenen.' . "\n" . '<li><b>Bestel Geschiedenis</b> - Bekijk de bestellingen die u eerder heeft geplaatst.' . "\n\n");
					//Cadeaubon voor nieuwe klanten
					if (NEW_SIGNUP_GIFT_VOUCHER_AMOUNT > 0) {
						$coupon_code = create_coupon_code();
						$insert_query = tep_db_query("insert into coupons (coupon_code, coupon_type, coupon_amount, date_created) values ('".$coupon_code."', 'G', '".NEW_SIGNUP_GIFT_VOUCHER_AMOUNT."', now())");
						$insert_id = tep_db_insert_id();
						$insert_query = tep_db_query("insert into coupon_email_track (coupon_id, customer_id_sent, sent_firstname, emailed_to, date_sent) values ('".$insert_id."', '0', 'Admin', '".$email_address."', now() )");
						$email_table .= sprintf(Translate('Als deel van de verwelkoming van nieuwe klanten hebben wij u een cadeaubon verstuurd ter waarde van %s'), $currencies->format(NEW_SIGNUP_GIFT_VOUCHER_AMOUNT)) . "\n\n";
						$email_table .= Translate('U kan de cadeaubon valideren door op deze link te klikken').' <a href="'.tep_href_link(FILENAME_GV_REDEEM, 'gift=' . $coupon_code,'NONSSL', false).'">'.tep_href_link(FILENAME_GV_REDEEM, 'gift=' . $coupon_code,'NONSSL', false).'</a>'."\n\n";
					}
					//Coupon code voor nieuwe klanten
					if (NEW_SIGNUP_DISCOUNT_COUPON != '') {
						$coupon_code = NEW_SIGNUP_DISCOUNT_COUPON;
						$coupon_query = tep_db_query("select * from coupons where coupon_code = '".$coupon_code."'");
						$coupon = tep_db_fetch_array($coupon_query);
						$coupon_id = $coupon['coupon_id'];		
						$coupon_desc_query = tep_db_query("select * from coupons_description where coupon_id = '".$coupon_id."' and language_id = '".(int)$languages_id."'");
						$coupon_desc = tep_db_fetch_array($coupon_desc_query);
						$insert_query = tep_db_query("insert into coupon_email_track (coupon_id, customer_id_sent, sent_firstname, emailed_to, date_sent) values ('".$coupon_id."', '0', 'Admin', '".$email_address."', now() )");
						$email_table .= Translate('Proficiat, om uw eerste bezoek aan onze shop aangenamer te maken zenden wij u een kortings coupon.')."\n";
						$email_table .= sprintf(Translate('Om de coupon te gebruiken vult u de coupon code, %s, in tijdens de checkout.'), $coupon['coupon_code'])."\n\n";
					}
					$email_table .= "\n" . Translate('Voor hulp met een van deze services kunt u een email sturen naar '.STORE_NAME.': '.STORE_OWNER_EMAIL_ADDRESS.'.'."\n\n");
					$email_table .= '</td><td style="width: 5px;"></td></tr></table>';
					$Varlogo = '<a href="'.HTTP_SERVER.DIR_WS_CATALOG.'"><img src="'.HTTP_SERVER.DIR_WS_CATALOG.DIR_WS_IMAGES.'mail/logo.jpg" border="0" /></a> ';
					$Vartable1 = '<table width="100%"  border="0" cellpadding="0" cellspacing="0" bgcolor="#ffffff">';
					$Vartable2 = '<table width="100%" border="0" cellpadding="3" cellspacing="3" bgcolor="#EFEFEF">';
					$Vartext1 = '<h1>'.Translate('Account aanmaken').'</h1>';
					$Vartext2 = $email_table;//content
					$Varcopyright = 'Copyright &copy; '.date('Y');
					$Varmailfooter = Translate('Dit email adres is ingegeven op onze website door u of door een van onze bezoekers. Als u zich niet ingeschreven hebt op onze website contacteer ons dan via').' <a href="mailto:' . STORE_OWNER_EMAIL_ADDRESS . '">' . STORE_OWNER_EMAIL_ADDRESS . '</a>';
					require(DIR_WS_MODULES . 'email/html_create_account.php');
					$email_text = $html_email_text;
					//Send mail
					tep_mail($name, $email_address, sprintf(Translate('Welkom bij %s'),STORE_NAME), $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
				} else {
					/************************/
					/*	Moderated access	*/
					/************************/
					//Mail to store owner
					$email_table = '<table cellspacing="0" cellpadding="0" border="0" width="587" bgcolor="#ffffff">';
					$email_table .= '<tr><td style="width:5px;"></td><td>';
					$email_table .= Translate('Beste ').' '.Translate('beheerder')."\n\n";
					$email_table .= "\n" . sprintf(Translate('Een bezoeker heeft zich geregistreerd via %s'), STORE_NAME) . "\n\n";
					$email_table .= "\n\n" . Translate('Deze klant zal pas kunnen inloggen op het beveiligd gedeelte van de website, nadat u de account activeert door middel van onderstaande link.')."\n\n";
					$email_table .= "\n\n".'<a href="'.HTTP_SERVER.DIR_WS_HTTP_CATALOG.'scripts/user_activate.php?user='.$email_address.'">' . Translate('account activeren')."</a>"."\n\n";
					$email_table .= '<table cellspacing="0" cellpadding="3" border="0" width="100%">';
					$email_table .= '<tr><td width="150">' . Translate('Naam').': </td><td>'.$name.'</td></tr>';
					//Email
					if ($this->options['customers_email_address'] == 'on') {
						$email_table .= "<tr><td>" . Translate('E-mailadres').': </td><td>'.$email_address.'</td></tr>';
					}
					//Company
					if ($this->options['entry_company'] == 'on') {
						$email_table .= "<tr><td>" . Translate('Bedrijfsnaam').': </td><td>'.$company.'</td></tr>';
					}
					//BTW nummer
					if ($this->options['billing_tva_intracom'] == 'on') {
						$email_table .= "<tr><td>" . Translate('BTW Nummer').': </td><td>'.$btwnr.'</td></tr>';
					}
					//Street address
					if ($this->options['entry_street_address'] == 'on') {
						$email_table .= "<tr><td>" . Translate('Straat en huisnummer').': </td><td>'.$street_address.'</td></tr>';
					}
					//Postcode
					if ($this->options['entry_postcode'] == 'on') {
						$email_table .= "<tr><td>" . Translate('Postcode').': </td><td>'.$postcode.'</td></tr>';
					}
					//City
					if ($this->options['entry_city'] == 'on') {
						$email_table .= "<tr><td>" . Translate('Woonplaats').': </td><td>'.$city.'</td></tr>';
					}
					//Telephone
					if ($this->options['customers_telephone'] == 'on') {
						$email_table .= "<tr><td>" . Translate('Telefoonnummer').': </td><td>'.$telephone.'</td></tr>';
					}
					//Fax
					if ($this->options['customers_fax'] == 'on') {
						$email_table .= "<tr><td>" . Translate('Faxnummer').': </td><td>'.$fax.'</td></tr>';
					}
					//Country
					if ($this->options['entry_country'] == 'on') {
						$email_table .= "<tr><td>" . Translate('Land').': </td><td>'.tep_get_country_name($country).'</td></tr>';
					}
					$email_table .= '</table>';
					$email_table .= '</td><td style="width: 5px;"></td></tr></table>';
					$Varlogo = '<a href="' . HTTP_SERVER . DIR_WS_CATALOG . '"><img src="'. HTTP_SERVER . DIR_WS_CATALOG . DIR_WS_IMAGES.'mail/logo.jpg" border="0" /></a> ';
					$Vartable1 = '<table width="100%"  border="0" cellpadding="0" cellspacing="0" bgcolor="#ffffff">';
					$Vartable2 = '<table width="100%" border="0" cellpadding="3" cellspacing="3" bgcolor="#EFEFEF">';
					$Vartext1 = '<h1>'.Translate('Account aanmaken').'</h1>';
					$Vartext2 = $email_table;//content
					$Varcopyright = Translate('Copyright &copy; 2010');
					$Varmailfooter = Translate('Dit email adres is ingegeven op onze website door u of door een van onze bezoekers. Als u zich niet ingeschreven hebt op onze website contacteer ons dan via').' <a href="mailto:' . STORE_OWNER_EMAIL_ADDRESS . '">' . STORE_OWNER_EMAIL_ADDRESS . '</a>';
					require(DIR_WS_MODULES . 'email/html_create_account.php');
					$email_text = $html_email_text;
					tep_mail(STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, Translate('Nieuwe registratie'), $email_text, $name, $email_address);
					
					//Mail to customer
					$email_table = '<table cellspacing="0" cellpadding="0" border="0" width="587" bgcolor="#ffffff">';
					$email_table .= '<tr><td style="width:5px;"></td><td>';
					$email_table .= Translate('Beste ').' '.$name."\n\n";
					$email_table .= "\n\n" . Translate('Uw account voor onze website werd succesvol aangevraagd. Hieronder vind u nog eens de ingevulde gegevens. Uw gegevens zijn aan ons doorgegeven voor moderatie. Van zodra uw account geactiveerd is, ontvangt u hierover een e-mail.')."\n\n";
					$email_table .= '<table cellspacing="0" cellpadding="3" border="0" width="100%">';
					$email_table .= '<tr><td width="150">' . Translate('Naam').': </td><td>'.$name.'</td></tr>';
					//Email
					if ($this->options['customers_email_address'] == 'on') {
						$email_table .= "<tr><td>" . Translate('E-mailadres').': </td><td>'.$email_address.'</td></tr>';
					}
					//Company
					if ($this->options['entry_company'] == 'on') {
						$email_table .= "<tr><td>" . Translate('Bedrijfsnaam').': </td><td>'.$company.'</td></tr>';
					}
					//BTW nummer
					if ($this->options['billing_tva_intracom'] == 'on') {
						$email_table .= "<tr><td>" . Translate('BTW Nummer').': </td><td>'.$btwnr.'</td></tr>';
					}
					//Street address
					if ($this->options['entry_street_address'] == 'on') {
						$email_table .= "<tr><td>" . Translate('Straat en huisnummer').': </td><td>'.$street_address.'</td></tr>';
					}
					//Postcode
					if ($this->options['entry_postcode'] == 'on') {
						$email_table .= "<tr><td>" . Translate('Postcode').': </td><td>'.$postcode.'</td></tr>';
					}
					//City
					if ($this->options['entry_city'] == 'on') {
						$email_table .= "<tr><td>" . Translate('Woonplaats').': </td><td>'.$city.'</td></tr>';
					}
					//Telephone
					if ($this->options['customers_telephone'] == 'on') {
						$email_table .= "<tr><td>" . Translate('Telefoonnummer').': </td><td>'.$telephone.'</td></tr>';
					}
					//Fax
					if ($this->options['customers_fax'] == 'on') {
						$email_table .= "<tr><td>" . Translate('Faxnummer').': </td><td>'.$fax.'</td></tr>';
					}
					//Country
					if ($this->options['entry_country'] == 'on') {
						$email_table .= "<tr><td>" . Translate('Land').': </td><td>'.tep_get_country_name($country).'</td></tr>';
					}
					$email_table .= '</table>';
					$email_table .= '</td><td style="width: 5px;"></td></tr></table>';
					$Varlogo = '<a href="' . HTTP_SERVER . DIR_WS_CATALOG . '"><img src="'. HTTP_SERVER . DIR_WS_CATALOG . DIR_WS_IMAGES.'mail/logo.jpg" border="0" /></a> ';
					$Vartable1 = '<table width="100%"  border="0" cellpadding="0" cellspacing="0" bgcolor="#ffffff">';
					$Vartable2 = '<table width="100%" border="0" cellpadding="3" cellspacing="3" bgcolor="#EFEFEF">';
					$Vartext1 = '<h1>'.Translate('Account aanmaken').'</h1>';
					$Vartext2 = $email_table;//content
					$Varcopyright = Translate('Copyright &copy; 2010');
					$Varmailfooter = Translate('Dit email adres is ingegeven op onze website door u of door een van onze bezoekers. Als u zich niet ingeschreven hebt op onze website contacteer ons dan via').' <a href="mailto:' . STORE_OWNER_EMAIL_ADDRESS . '">' . STORE_OWNER_EMAIL_ADDRESS . '</a>';
					require(DIR_WS_MODULES . 'email/html_create_account.php');
					$email_text = $html_email_text;
					tep_mail($name, $email_address, Translate('Nieuwe registratie'), $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
				}
			} else {
				/********************/
				/*	Request account	*/
				/********************/
				$email_table = '<table cellspacing="0" cellpadding="0" border="0" width="587" bgcolor="#ffffff">';
				$email_table .= '<tr><td style="width:5px;"></td><td>';
				$email_table .= Translate('Beste ').' '.Translate('beheerder')."\n\n";
				$email_table .= "\n" . sprintf(Translate('Een bezoeker heeft zich geregistreerd via %s'), STORE_NAME) . "\n\n";
				$email_table .= '<table cellspacing="0" cellpadding="3" border="0" width="100%">';
				$email_table .= '<tr><td width="150">' . Translate('Naam').': </td><td>'.$name.'</td></tr>';
				//Email
				if ($this->options['customers_email_address'] == 'on') {
					$email_table .= "<tr><td>" . Translate('E-mailadres').': </td><td>'.$email_address.'</td></tr>';
				}
				//Company
				if ($this->options['entry_company'] == 'on') {
					$email_table .= "<tr><td>" . Translate('Bedrijfsnaam').': </td><td>'.$company.'</td></tr>';
				}
				//BTW nummer
				if ($this->options['billing_tva_intracom'] == 'on') {
					$email_table .= "<tr><td>" . Translate('BTW Nummer').': </td><td>'.$btwnr.'</td></tr>';
				}
				//Street address
				if ($this->options['entry_street_address'] == 'on') {
					$email_table .= "<tr><td>" . Translate('Straat en huisnummer').': </td><td>'.$street_address.'</td></tr>';
				}
				//Postcode
				if ($this->options['entry_postcode'] == 'on') {
					$email_table .= "<tr><td>" . Translate('Postcode').': </td><td>'.$postcode.'</td></tr>';
				}
				//City
				if ($this->options['entry_city'] == 'on') {
					$email_table .= "<tr><td>" . Translate('Woonplaats').': </td><td>'.$city.'</td></tr>';
				}
				//Telephone
				if ($this->options['customers_telephone'] == 'on') {
					$email_table .= "<tr><td>" . Translate('Telefoonnummer').': </td><td>'.$telephone.'</td></tr>';
				}
				//Fax
				if ($this->options['customers_fax'] == 'on') {
					$email_table .= "<tr><td>" . Translate('Faxnummer').': </td><td>'.$fax.'</td></tr>';
				}
				//Country
				if ($this->options['entry_country'] == 'on') {
					$email_table .= "<tr><td>" . Translate('Land').': </td><td>'.tep_get_country_name($country).'</td></tr>';
				}
				$email_table .= '</table>';
				$email_table .= "\n\n" . Translate('Zonder manuele toevoeging in het softwarepakket, zal deze klant niet toegelaten worden in het beveiligde gedeelte van de website. ')."\n\n";
				$email_table .= '</td><td style="width: 5px;"></td></tr></table>';
				$Varlogo = '<a href="' . HTTP_SERVER . DIR_WS_CATALOG . '"><img src="'. HTTP_SERVER . DIR_WS_CATALOG . DIR_WS_IMAGES.'mail/logo.jpg" border="0" /></a> ';
				$Vartable1 = '<table width="100%"  border="0" cellpadding="0" cellspacing="0" bgcolor="#ffffff">';
				$Vartable2 = '<table width="100%" border="0" cellpadding="3" cellspacing="3" bgcolor="#EFEFEF">';
				$Vartext1 = '<h1>'.Translate('Account aanmaken').'</h1>';
				$Vartext2 = $email_table;//content
				$Varcopyright = Translate('Copyright &copy; 2010');
				$Varmailfooter = Translate('Dit email adres is ingegeven op onze website door u of door een van onze bezoekers. Als u zich niet ingeschreven hebt op onze website contacteer ons dan via').' <a href="mailto:' . STORE_OWNER_EMAIL_ADDRESS . '">' . STORE_OWNER_EMAIL_ADDRESS . '</a>';
				require(DIR_WS_MODULES . 'email/html_create_account.php');
				$email_text = $html_email_text;
				tep_mail(STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, Translate('Nieuwe registratie'), $email_text, $name, $email_address);
			}
			return array('address_book_id' => $address_id, 'customer_id' => $customer_id);
		}
	}
	
	public function create_address_book_item($customer_id, $data) {
		$process = true;
		$error = false;
		$sql_data_array = array();
		//Gender
		if ($this->options['customers_gender'] == 'on') {
			 if (isset($data['gender'])) {
				$gender = mysql_real_escape_string($data['gender']);
			} else {
				$gender = false;
			}
			$sql_data_array['entry_gender'] = $gender;
		}
		//Company
		if ($this->options['entry_company'] == 'on') {
			$company = mysql_real_escape_string($data['company']);
			$sql_data_array['entry_company'] = $company;
		}
		//BTW nummer
		if ($this->options['billing_tva_intracom'] == 'on') {
			$btwnr= mysql_real_escape_string($data['btwnr']);
			$sql_data_array['billing_tva_intracom'] = $btwnr;
		}
		//Name
		if (isset($data['firstname']) || isset($data['lastname'])) {
			$name = '';
			if ($this->options['customers_firstname'] == 'on') {
				$name .= $data['firstname'];
			}
			if ($this->options['customers_firstname'] == 'on' && $this->options['customers_lastname'] == 'on') {
				$name .= ' ';
			}
			if ($this->options['customers_lastname'] == 'on') {
				$name .= $data['lastname'];
			}
		} else if ($data['name']) {
			$name = $data['name'];
		} else if ($data['fullname']) {
			$name = $data['fullname'];
		}
		if (strlen($name) < ENTRY_FIRST_NAME_MIN_LENGTH) {
			$error = true;
			$errors['name'] = sprintf(Translate('Uw voornaam moet minstens %s karakters bevatten'), ENTRY_FIRST_NAME_MIN_LENGTH);
		} else {
			$sql_data_array['entry_firstname'] = $name;
		}
		//Street address
		if ($this->options['entry_street_address'] == 'on') {
			$street_address = mysql_real_escape_string($data['street_address']);
			if (!preg_match("/[a-zA-Z]\s\d/", $street_address)) {
				$error = true;
				$errors['street_address'] = Translate('Gelieve uw straat EN huisnummer in te geven.');
			}
			$sql_data_array['entry_street_address'] = $street_address;
		}
		//Suburb
		if ($this->options['entry_suburb'] == 'on') {
			$suburb = mysql_real_escape_string($data['suburb']);
			$sql_data_array['entry_suburb'] = $suburb;
		}
		//Postcode
		if ($this->options['entry_postcode'] == 'on') {
			$postcode = mysql_real_escape_string($data['postcode']);
			if (strlen($postcode) < ENTRY_POSTCODE_MIN_LENGTH) {
				$error = true;
				$errors['postcode'] = sprintf(Translate('Uw postcode moet minstens %s karakters bevatten'), ENTRY_POSTCODE_MIN_LENGTH);
			}
			$sql_data_array['entry_postcode'] = $postcode;
		}
		//City
		if ($this->options['entry_city'] == 'on') {
			$city = mysql_real_escape_string($data['city']);
			if (strlen($city) < ENTRY_CITY_MIN_LENGTH) {
				$error = true;
				$errors['city'] = sprintf(Translate('Uw woonplaats moet minstens %s karakters bevatten'),ENTRY_CITY_MIN_LENGTH);
			}
			$sql_data_array['entry_city'] = $city;
		}
		//State
		if ($this->options['entry_state'] == 'on') {
			$state = mysql_real_escape_string($data['state']);
			$sql_data_array['entry_state'] = $state;
		}
		//Zone
		if ($this->options['entry_zone'] == 'on' && isset($data['zone_id'])) {
			$zone_id = mysql_real_escape_string($data['zone_id']);
		} else {
			$zone_id = false;
		}
		$sql_data_array['entry_zone_id'] = (int)$zone_id;
		//Country
		if ($this->options['entry_country'] == 'on') {
			$country = mysql_real_escape_string($data['country']);
			if (is_numeric($country) == false || $country == '0') {
				$error = true;
				$errors['country'] = Translate('Gelieve een land uit de lijst te selecteren');
			}
			$sql_data_array['entry_country_id'] = $country;
		}
		if ($error) {
			return array('errors' => $errors);
		} else {
			$sql_data_array['customers_id'] = (int)$customer_id;
			tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);
			$new_address_book_id = tep_db_insert_id();
			return array('address_book_id' => $new_address_book_id);
		}
	}
}
?>
<?php
class Checkout extends Modules {
    private static $instance;
    public static $checkout_steps = array(
                            1 => array('title' => 'Adres gegevens', 'modules' => array(1 => 'customers_info')),
                            2 => array('title' => 'Verzendmethode', 'modules' => array(1 => 'shipping')),
                            3 => array('title' => 'Kortingscode', 'modules' => array()),
                            4 => array('title' => 'Betaalmethode', 'modules' => array(1 => 'coupon', 2 => 'payment', 3 => 'cadeaubon', 4 => 'extra')),
                            '99' => array('title' => 'Overzicht' , 'modules' => array(1 => 'order_subtotal', 2 => 'order_total', 10 => 'billing_address', 11 => 'delivery_address', 12 => 'payment_method', 13 => 'shipping_method', 30 => 'comment')),
                            'summary' => array('title' => 'Overzicht' , 'modules' => array(1 => 'order_total'))
                            )
                , $errors = array();
    public $modules = array()
         , $temp_data = array()
         , $total_weight = 0;

    public function __construct($show_step = true) {
        global $Modules, $temp_orders_id, $cart;
        global $customer_id;
        if (isset($_GET['install'])) {
            //check if all translations are available for checkout
            parent::checkTranslations(dirname(__FILE__), $this->getTranslations());
            parent::addCron($min = '0', $hour = '4', $dayOfMonth = '1', $month = '*', $dayOfWeek = '*', $cmd = '/usr/bin/php -q '.dirname(__FILE__).'/assets/cron/cron.php');
        }
        $this->modules = $Modules->modules;
        $goto_next_step = true;
        $errors = array();
        if ($temp_orders_id == 0 && $show_step === true) {
            //create order in db
            $temp_orders_id = $this->create_order();
        } else {
            if (basename($_SERVER['HTTP_REFERER']) == 'shopping_cart.php') {
                $this->add_products_to_db(false);
            }
        }

        if (isset($_POST['checkout_modules'])) {
            //process all modules from filled in step
            foreach($_POST['checkout_modules'] as $module) {
                global $$module;
                $answer = $$module->process_data();
                if (count($$module->errors) > 0) {
                    self::$errors = array_merge(self::$errors, $$module->errors);
                }
                if ($answer !== true) {
                    $goto_next_step = false;
                }
            }
        }
        //Check if we comeback from a payment site
        foreach($this->modules as $type=>$typedata) {
            foreach($typedata as $module) {
                global $$module;
                if (method_exists($$module, 'after_extern_process')) {
                    $$module->after_extern_process();
                }
            }
        }
        //get step if given
        if (isset($_POST['checkout_step'])) {
            $getstep = $_POST['checkout_step'];
			
			 if($_POST['checkout_step'] == $this->last_active_step())
			 {
			if (!isset($_POST['terms'])) {
			$current_step=$_POST['checkout_step'];
			$step=$_POST['checkout_step'];
		self::$errors=Translate('Voorwaarden accepteren');		
		    }
			 }
			
            if($_POST['checkout_step'] == $this->last_active_step() && count(self::$errors) == 0) {
                //last step is processed and there were no errors
                tep_db_query('INSERT INTO temp_orders_steps (orders_id, step, status, errors) VALUES("'.$temp_orders_id.'", "'.$getstep.'", "1", "'.addslashes(serialize(self::$errors)).'")');
                $query = tep_db_query('SELECT payment_method FROM temp_orders WHERE orders_id = "'.$temp_orders_id.'"');
                $orders_array = tep_db_fetch_array($query);
                $instance_id = end(explode('_', $orders_array['payment_method']));
                $payment_method = substr($orders_array['payment_method'], 0, -((strlen($instance_id)+1)));
                //Check if modules need processing before confirm
                foreach($this->modules as $type=>$typedata) {
                    foreach($typedata as $module) {
                        if ($module == $payment_method) {
                            global $$module;
                            if (method_exists($$module, 'before_confirm')) {
                                $$module->before_confirm();
                            }
                            if (method_exists($$module, 'after_confirm')) {
                                $module_error = $$module->after_confirm();
                                if (count($module_error) > 0) {
                                    self::$errors = array_merge(self::$errors, $module_error);
                                }
                            }
                        }
                    }
                }
                if (count(self::$errors) == 0) {
                    //put data from temp to right db tables
                    $this->get_all_data_from_temp_db($temp_orders_id);
                    $orders_id = $this->put_all_data_in_db($temp_orders_id);
                    //process with orders id
                    foreach($this->modules as $type=>$typedata) {
                        foreach($typedata as $module) {
                            global $$module;
                            if (method_exists($$module, 'after_process')) {
                                $$module->after_process($orders_id);
                            }
                        }
                    }
#	    	if (STOCK_LIMITED == 'true') {
#                  $products = $cart->get_products();
#		  for ($i=0;$i<sizeof($products);$i++) {
#	           tep_db_query("update " . TABLE_PRODUCTS . " set products_quantity = products_quantity-" . (int)$products[$i]['quantity'] . " where products_id = '" . (int)$products[$i]['id'] . "'");
#	           echo "update " . TABLE_PRODUCTS . " set products_quantity = products_quantity-" . (int)$products[$i]['quantity'] . " where products_id = '" . (int)$products[$i]['id'] . "'";
#		  }
#		}

                    //send mail to customer and shop owner
                    $this->send_order_mail($orders_id);
                    $cart->reset(true);
                    setcookie('temp_orders_id', '', time() - 3600, '/');
                    tep_redirect(tep_href_link('checkout_success.php'));
                }
            }
        }
        if ($temp_orders_id > 0) {
            if (basename($_SERVER['HTTP_REFERER']) == 'shopping_cart.php') {
                $last_step = $this->next_active_step('');
            } else {
                $last_step_data = $this->last_filled_in_step($temp_orders_id);
                $last_step = $last_step_data['step'];
            }
        }
        //if step is asked through get param, force this.
        //first check if step is available => don't skip steps
        if (isset($_GET['force_checkout_step'])) {
            $step = $_GET['force_checkout_step'];
        } else if (isset($_GET['checkout_step']) && $temp_orders_id > 0 && $last_step >= $_GET['checkout_step']) {
            $step = $_GET['checkout_step'];
        //check if the right step is requested
        } else if ($temp_orders_id > 0 && (count($_POST) > 0)) {
            if ($getstep == '') {
                //if page was reloaded this brings the customer back to right step
                $last_step_data = $this->last_filled_in_step($temp_orders_id);
                $last_step = $last_step_data['step'];
                foreach(unserialize($last_step_data['errors']) as $module=>$module_data) {
                    global $$module;
                    $$module->errors = $module_erros;
                }
                $getstep = $last_step;
                $goto_next_step = false;
            }
            // @TODO test this
            //check if prev step is completed
            if ($prev_step = $this->prev_active_step($getstep)) {
                $query = tep_db_query('SELECT errors FROM temp_orders_steps WHERE orders_id = "'.$temp_orders_id.'" AND step = "'.$prev_step.'" AND status = "1"');
                //no completed step found
                if (tep_db_num_rows($query)< 0) {
                    $goto_next_step = false;
                    $error_query = tep_db_query('SELECT errors FROM temp_orders_steps WHERE orders_id = "'.$temp_orders_id.'" AND step = "'.$prev_step.'" AND status = "0" ORDER BY date desc LIMIT 1');
                    $errors_array = tep_db_fetch_array($error_query);
                    foreach(unserialize($errors_array) as $module=>$module_erros) {
                        global $$module;
                        $$module->errors = $module_erros;
                    }
                    //prev active step will be loaded again
                    $step = $prev_step;
                }
            }
            // @TODO eof test
            if ($goto_next_step) {
                if ($getstep > 0) {
                    tep_db_query('INSERT INTO temp_orders_steps (orders_id, step, status, errors) VALUES("'.$temp_orders_id.'", "'.$getstep.'", "1", "'.addslashes(serialize(self::$errors)).'")');
                }
                //get next step with active modules
                if ($this->last_active_step() == $getstep) {
                    //save all data from temp to orders table
                    // @TODO save data after last step with the use of $temp_orders_id
                } else {
                    $step = $this->next_active_step($getstep);
                }
            } else {
                tep_db_query('INSERT INTO temp_orders_steps (orders_id, step, status, errors) VALUES("'.$temp_orders_id.'", "'.$getstep.'", "0", "'.addslashes(serialize(self::$errors)).'")');
                $step = $getstep;
            }
        } else if ($temp_orders_id > 0) {
            if (basename($_SERVER['HTTP_REFERER']) == 'shopping_cart.php') {
                $last_step = $this->next_active_step('');
            } else {
                //if page was reloaded this brings the customer back to right step
                $last_step_data = $this->last_filled_in_step($temp_orders_id);
                $last_step = $last_step_data['step'];
                $last_step_errors = unserialize($last_step_data['errors']);
                $count_errors = 0;
                if (is_array($last_step_errors)) {
                    foreach($last_step_errors as $module=>$module_data) {
                        global $$module;
                        $$module->errors = $module_data;
                        $count_errors += count($module_data);
                    }
                } else {
                    $$module->errors = array();
                }
            }
            $step = $last_step;
        } else {
            if (!empty($getstep)) {
                $step = $getstep;
            } else {
                $step = $this->next_active_step('');
            }
        }
        if (empty($step)) {
            $step = $this->next_active_step('');
        }
        if ($show_step) {
            echo $this->build_steps($step);
        }
    }

    static function instance($show_step = true) {
        if (!self::$instance) {
            self::$instance = new Checkout($show_step);
        }
        return self::$instance;
    }

    public function get_step_for_type($type) {
        foreach(self::$checkout_steps as $key=>$value) {
            if (in_array($type, $value['modules'])) {
                return $key;
            }
        }
    }

    //get the next active step
    public function next_active_step($step) {
        reset(self::$checkout_steps);
        if ($step == '') {
            //no step defined, start with first
            $step = 1;
        } else {
            //set pointer of array to selected step
            while (key(self::$checkout_steps) != $step) next(self::$checkout_steps);
            next(self::$checkout_steps);
            $step = key(self::$checkout_steps);
        }
        //check if step has active modules
        while ($this->check_step($step) === false) {
            next(self::$checkout_steps);
            $step = key(self::$checkout_steps);
        }
        return $step;
    }

    public function prev_active_step($step) {
        $prev_step = $step;
        reset(self::$checkout_steps);
        //set pointer of array to selected step
        while (key(self::$checkout_steps) != $prev_step) next(self::$checkout_steps);
        next(self::$checkout_steps);
        $prev_step = key(self::$checkout_steps);
        //check if step has active modules
        while ($this->check_step($prev_step) === false) {
            prev(self::$checkout_steps);
            $prev_step = key(self::$checkout_steps);
        }
        if ($prev_step < $step) {
            return $prev_step;
        } else {
            return false;
        }
    }

    //check if the choosen step has active modules
    //returns the step OR false
    public function check_step($step = '0') {
        global $Modules;
        $all_checkout_steps = self::$checkout_steps;
        if ($step == '0' || $step == '') {
            //get first active step
            foreach($all_checkout_steps as $key=>$value) {
                foreach($value['modules'] as $steps=>$type) {
                    if (isset($Modules->modules[$type])) {
                        foreach($Modules->modules[$type] as $sort_order=>$module) {
                            global $$module;
                            //check if module is active
                            if ($$module->is_active()) {
                                $step = $key;
                                //one active module is enough, we don't have to check all off them
                                break 3;
                            }
                        }
                    }
                }
            }
        } else {
            //check if step is available
            if (count($all_checkout_steps[$step]) > 0 && is_numeric($step)) {
                $continue = false;
                //there are modules for this step, now check if at least one is active
                foreach($all_checkout_steps[$step]['modules'] as $steps=>$type) {
                    if (isset($Modules->modules[$type])) {
                        foreach($Modules->modules[$type] as $sort_order=>$module) {
                            global $$module;
                            //check if module is active
                            if ($$module->is_active()) {
                                $continue = true;
                                break 2;
                            }
                        }
                    }
                }
                if (!$continue) {
                    return false;
                }
            } else {
                //this step is not active
                return false;
            }
        }
        //we have a step with active modules
        return $step;
    }

    public function last_active_step() {
        $last_active_step = 0;
        //set pointer to last step
        $steps = self::$checkout_steps;
        end($steps);
        //go through all steps starting from the last one and going backwards
        while (self::check_step(key($steps)) === false) prev($steps);
        return key($steps);
    }

    public function build_steps($current_step = 1) {
        $count = 0;
        $html = '';
        foreach(self::$checkout_steps as $step=>$stepdata) {
            if ($this->check_step($step)) {
                $count++;
                $html .= '<section>';
                if ($current_step == $step) {
                    //current active step
                    $html .= '<div class="step step'.$step.' active">';
                    $html .= '<div class="step_title">'.$count.'. '.Translate($stepdata['title']).'</div>';
                    $html .= '<div class="step_content">';
                    $html .= '<form name="process_step" method="POST" action="'.tep_href_link(basename($_SERVER['PHP_SELF'])).'" class="form-inline">';
                    $html .= '<input type="hidden" name="checkout_step" value="'.$step.'" />';
                    //sort all types by sort_order for this step
                    ksort($stepdata['modules']);
                    //go through all modules and get html
                    foreach($stepdata['modules'] as $steps=>$type) {
                        //check if modules are active for this type
                        if (isset($this->modules[$type])) {
                            //sort all modules by sort order for this type
                            ksort($this->modules[$type]);
                            //go through all modules for this type
                            foreach($this->modules[$type] as $sort_order=>$module) {
                                global $$module;
                                $html .= '<input type="hidden" name="checkout_modules[]" value="'.$module.'" />';
                                $html .= $$module->output($step);
                            }
                        }
                    }
                    if (count(array_intersect_key(parent::$disable_next_button, $_GET)) == 0) {
                        if ($this->last_active_step() == $current_step) {
							if(self::$errors)
						{
						$html .= '<div class="alert alert-error"><strong>'.Translate('Opgelet!').'</strong> '.Translate('Voorwaarden accepteren').'</div>';	}
							 $html .= '<div class="control-group"><div style="float:left;"><input type="checkbox" name="terms" id="terms" /></div><div style="float:left;padding:2px 0 0 5px;"> <a href="'.tep_href_link('conditions_modal.php').'"  onclick="openexpwindow(this.href);return false" class="alertcont ">'.Translate('Voorwaarden accepteren').'</a></div><br /></div>';
                            $html .= '<button type="submit" class="btn btn-large btn-cta checkoutfinal">'.Translate('Bestelling bevestigen').'</button>';
                        } else {
                            $html .= '<button type="submit" class="btn btn-large btn-cta">'.Translate('Volgende stap').'</button>';
                        }
                    }
                    $html .= '</form>';
                    $html .= '</div>';
                    $html .= '</div>';
                } else {
                    if ($current_step > $step) {
                        $html .= '<div class="step step'.$step.' active">';
                        $html .= '<div class="step_title"><a href="'.tep_href_link(basename($_SERVER['PHP_SELF']), 'checkout_step='.$step).'" title="'.Translate($stepdata['title']).'">'.$count.'. '.Translate($stepdata['title']).'</a></div>';
                        $html .= '</div>';
                    } else{
                        $html .= '<div class="step step'.$step.'">';
                        $html .= '<div class="step_title">'.$count.'. '.Translate($stepdata['title']).'</div>';
                        $html .= '</div>';
                    }
                }
                $html .= '</section>';
            }
        }
        return $html;
    }

    public function last_filled_in_step($orders_id) {
        $query = tep_db_query('SELECT step, errors FROM temp_orders_steps WHERE orders_id = "'.$orders_id.'" ORDER BY date desc LIMIT 1');
        $array = tep_db_fetch_array($query);
        return $array;
    }

    public function create_order() {
        global $temp_orders_id, $cart, $languages_id;
        //create order and get order_id.
        tep_db_query('INSERT INTO temp_orders (last_modified) VALUES(NOW())');
        $temp_orders_id = tep_db_insert_id();
        setcookie('temp_orders_id', $temp_orders_id, time()+60*60*24*1, '/'); //available for 30 days
        //put products in to db
        self::add_products_to_db();
        return $temp_orders_id;
    }

    public function add_products_to_db($new = true, $country_id = -1, $zone_id = -1) {
        global $temp_orders_id, $cart, $languages_id;
        $products = $cart->get_products();
        if(!$new) {
            tep_db_query('DELETE FROM temp_orders_products WHERE orders_id = "'.$temp_orders_id.'"');
            tep_db_query('DELETE FROM temp_orders_products_attributes WHERE orders_id = "'.$temp_orders_id.'"');
        }
        for ($i=0;$i<sizeof($products);$i++) {
            $products_tax = tep_get_tax_rate($products[$i]['tax_class_id'], $country_id, $zone_id);//in %
            tep_db_query('INSERT INTO temp_orders_products (orders_id, products_id, products_model, products_name, products_price, final_price, products_tax, products_quantity, products_weight) VALUES("'.$temp_orders_id.'", "'.(int)$products[$i]['id'].'", "'.$products[$i]['model'].'", "'.$products[$i]['name'].'", "'.$products[$i]['price'].'", "'.$products[$i]['final_price'].'", "'.$products_tax.'", "'.$products[$i]['quantity'].'", "'.$products[$i]['weight'].'")');
            if(extension_loaded('apc') && ini_get('apc.enabled')) {
                apc_delete('temp_orders_products_'.$temp_orders_id);
            }
            $orders_products_id = tep_db_insert_id();
            // Push all attributes information into db
            if (isset($products[$i]['attributes']) && is_array($products[$i]['attributes'])) {
                while (list($option, $value) = each($products[$i]['attributes'])) {
                    $attributes = tep_db_query("select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix
                                               from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                               where pa.products_id = '" . (int)$products[$i]['id'] . "'
                                               and pa.options_id = '" . $option . "'
                                               and pa.options_id = popt.products_options_id
                                               and pa.options_values_id = '" . $value . "'
                                               and pa.options_values_id = poval.products_options_values_id
                                               and popt.language_id = '" . $languages_id . "'
                                               and poval.language_id = '" . $languages_id . "'");
                    $attributes_values = tep_db_fetch_array($attributes);
                    tep_db_query('INSERT INTO temp_orders_products_attributes (orders_id, orders_products_id, products_options, products_options_values, options_values_price, price_prefix) VALUES("'.$temp_orders_id.'", "'.$orders_products_id.'", "'.$attributes_values['products_options_name'].'", "'.$attributes_values['products_options_values_name'].'", "'.$attributes_values['options_values_price'].'", "'.$attributes_values['price_prefix'].'")');
                }
                if(extension_loaded('apc') && ini_get('apc.enabled')) {
                    apc_delete('temp_orders_products_attributes_'.$temp_orders_id);
                }
            }
        }
        // check if subtotal and total module are active
        if (isset($this->modules['order_subtotal']) && count($this->modules['order_subtotal']) > 0) {
            foreach($this->modules['order_subtotal'] as $module) {
                global $$module;
                $$module->process_data();
            }
        }
        if (isset($this->modules['order_total']) && count($this->modules['order_total']) > 0) {
            foreach($this->modules['order_total'] as $module) {
                global $$module;
                $$module->process_data();
            }
        }
    }

    public function get_all_data_from_temp_db($orders_id) {
        return self::get_all_data_from_db($orders_id, 'temp_');
    }

    private function get_all_data_from_db($orders_id, $db_prefix = '') {
        global $pdo;
        $this->temp_data[$orders_id] = array();
        if (!empty($db_prefix)) {
            if (substr($db_prefix, -1) != '_') {
                $db_prefix .= '_';
            }
        }
        //temp_orders data
        $to_query = $pdo->prepare('SELECT * FROM '.$db_prefix.'orders WHERE orders_id = :orders_id');
        $to_query->execute(array('orders_id' => $orders_id));
        if(extension_loaded('apc') && ini_get('apc.enabled')) {
            apc_add($db_prefix.'orders_'.$orders_id, $to_query->fetch(PDO::FETCH_ASSOC));
            $to = apc_fetch($db_prefix.'orders_'.$orders_id);
        } else {
            $to = $to_query->fetch(PDO::FETCH_ASSOC);
        }
        $this->temp_data[$orders_id]['orders'] = $to;
        //temp_orders_products data
        $top_query = $pdo->prepare('SELECT * FROM '.$db_prefix.'orders_products WHERE orders_id = :orders_id');
        $top_query->execute(array('orders_id' => $orders_id));
        if(extension_loaded('apc') && ini_get('apc.enabled')) {
            apc_add($db_prefix.'orders_products_'.$orders_id, $top_query->fetchAll(PDO::FETCH_ASSOC));
            $top = apc_fetch($db_prefix.'orders_products_'.$orders_id);
        } else {
            $top = $top_query->fetchAll(PDO::FETCH_ASSOC);
        }
        foreach($top as $top_data) {
            $this->temp_data[$orders_id]['orders_products'][$top_data['orders_products_id']] = $top_data;
        }
        //temp_orders_products_attributes data
        $topa_query = $pdo->prepare('SELECT * FROM '.$db_prefix.'orders_products_attributes WHERE orders_id = :orders_id');
        $topa_query->execute(array('orders_id' => $orders_id));
        if(extension_loaded('apc') && ini_get('apc.enabled')) {
            apc_add($db_prefix.'orders_products_attributes_'.$orders_id, $topa_query->fetchAll(PDO::FETCH_ASSOC));
            $topa = apc_fetch($db_prefix.'orders_products_attributes_'.$orders_id);
        } else {
            $topa = $topa_query->fetchAll(PDO::FETCH_ASSOC);
        }
        foreach($topa as $topa_data) {
            $this->temp_data[$orders_id]['orders_products_attributes'][$topa_data['orders_products_id']][] = $topa_data;
        }
        //temp_orders_status_history data
        $tosh_query = $pdo->prepare('SELECT * FROM '.$db_prefix.'orders_status_history WHERE orders_id = :orders_id');
        $tosh_query->execute(array('orders_id' => $orders_id));
        if(extension_loaded('apc') && ini_get('apc.enabled')) {
            apc_add($db_prefix.'orders_status_history_'.$orders_id, $tosh_query->fetchAll(PDO::FETCH_ASSOC));
            $tosh = apc_fetch($db_prefix.'orders_status_history_'.$orders_id);
        } else {
            $tosh = $tosh_query->fetchAll(PDO::FETCH_ASSOC);
        }
        foreach($tosh as $tosh_data) {
            $this->temp_data[$orders_id]['orders_status_history'][$tosh_data['orders_status_history_id']] = $tosh_data;
        }
        //temp_orders_total data
        $tot_query = $pdo->prepare('SELECT * FROM '.$db_prefix.'orders_total WHERE orders_id = :orders_id ORDER BY sort_order asc');
        $tot_query->execute(array('orders_id' => $orders_id));
        if(extension_loaded('apc') && ini_get('apc.enabled')) {
            apc_add($db_prefix.'orders_total_'.$orders_id, $tot_query->fetchAll(PDO::FETCH_ASSOC));
            $tot = apc_fetch($db_prefix.'orders_total_'.$orders_id);
        } else {
            $tot = $tot_query->fetchAll(PDO::FETCH_ASSOC);
        }
        foreach($tot as $tot_data) {
            $this->temp_data[$orders_id]['orders_total'][$tot_data['orders_total_id']] = $tot_data;
        }
        return $this->temp_data;
    }

    public function calculate_weight($orders_id, $db_prefix = '') {
        global $pdo;
        $this->total_weight = 0;
        $top_query = $pdo->prepare('SELECT * FROM '.$db_prefix.'orders_products WHERE orders_id = :orders_id');
        $top_query->execute(array('orders_id' => $orders_id));
        if(extension_loaded('apc') && ini_get('apc.enabled')) {
            apc_add($db_prefix.'orders_products_'.$orders_id, $top_query->fetchAll(PDO::FETCH_ASSOC));
            $top = apc_fetch($db_prefix.'orders_products_'.$orders_id);
        } else {
            $top = $top_query->fetchAll(PDO::FETCH_ASSOC);
        }
        foreach($top as $top_data) {
            $this->total_weight += ($top_data['products_quantity'] * $top_data['products_weight']);
        }
        return $this->total_weight;
    }

    private function put_all_data_in_db($orders_id) {
        global $currency, $currencies;
        //fill orders table
        if(extension_loaded('apc') && ini_get('apc.enabled')) {
            apc_delete('temp_orders_'.$orders_id);
            apc_delete('temp_orders_products_'.$orders_id);
            apc_delete('temp_orders_status_history_'.$orders_id);
            apc_delete('temp_orders_total_'.$orders_id);
        }
        $this->get_all_data_from_temp_db($orders_id);
        $orders = $this->temp_data[$orders_id]['orders'];
        unset($orders['orders_id']);
        unset($orders['delivery_address_id']);
        unset($orders['billing_address_id']);
        unset($orders['coupon_id']);
        unset($orders['payment_method_extra']);
        unset($orders['shipping_method_extra']);
        unset($orders['processed_order_id']);
        $orders['abo_status'] = '0';
        $orders['date_purchased'] = $orders['last_modified'] = date("Y-m-d H:m:s");
        $orders['currency'] = $currency;
        $orders['currency_value'] = $currencies->currencies[$currency]['value'];
        $orders['customers_address_format_id'] = tep_get_address_format_id($orders['customers_country']);
        $orders['customers_country'] = tep_get_country_name($orders['customers_country']);
        $orders['billing_address_format_id'] = tep_get_address_format_id($orders['billing_country']);
        $orders['billing_country'] = tep_get_country_name($orders['billing_country']);
        $orders['delivery_address_format_id'] = tep_get_address_format_id($orders['delivery_country']);
        $orders['delivery_country'] = tep_get_country_name($orders['delivery_country']);
        tep_db_perform('orders', $orders, 'insert');
        $new_orders_id = tep_db_insert_id();
        //add order id to temp orders table
        tep_db_query('UPDATE temp_orders SET processed_order_id = "'.$new_orders_id.'" WHERE orders_id = "'.$orders_id.'"');

        //fill orders_products table
        $orders_products = $this->temp_data[$orders_id]['orders_products'];
        //fill orders_products_attributes
        $orders_products_attributes = $this->temp_data[$orders_id]['orders_products_attributes'];
        if (is_array($orders_products)) {
            foreach($orders_products as $products_order_id=>$data) {

//////////////////////////////////////////////////////////////////////////// 07-06-2013
if(STOCK_LIMITED == 'true'){
    //function exists below. To reduce quantity from table on checkout
    //params: product_id and ordered qty
    $this->setQuantityInProductsTable($orders_products[$products_order_id]['products_id'],$orders_products[$products_order_id]['products_quantity']);
}
//////////////////////////////////////////////////////////////////////////// 07-06-2013

                unset($orders_products[$products_order_id]['orders_products_id']);
                unset($orders_products[$products_order_id]['products_weight']);
                $orders_products[$products_order_id]['orders_id'] = $new_orders_id;
                tep_db_perform('orders_products', $orders_products[$products_order_id], 'insert');
                $orders_products_id = tep_db_insert_id();
                if (is_array($orders_products_attributes)) {
                    foreach($orders_products_attributes[$products_order_id] as $key=>$value) {
                        unset($orders_products_attributes[$products_order_id][$key]['orders_products_attributes_id']);
                        $orders_products_attributes[$products_order_id][$key]['orders_id'] = $new_orders_id;
                        $orders_products_attributes[$products_order_id][$key]['orders_products_id'] = $orders_products_id;
                        tep_db_perform('orders_products_attributes', $orders_products_attributes[$products_order_id][$key], 'insert');
                    }
                }
            }
        }

        //fill orders_products_status_history table
        $orders_status_history = $this->temp_data[$orders_id]['orders_status_history'];
        if (is_array($orders_status_history)) {
            foreach($orders_status_history as $key=>$value) {
                unset($orders_status_history[$key]['orders_status_history_id']);
                $orders_status_history[$key]['orders_id'] = $new_orders_id;
                $orders_status_history[$key]['orders_status_id'] = $orders['orders_status'];
                tep_db_perform('orders_status_history', $orders_status_history[$key], 'insert');
            }
        }

        //fill orders_total table
        $orders_total = $this->temp_data[$orders_id]['orders_total'];
        if (is_array($orders_total)) {
            foreach($orders_total as $key=>$data) {
                unset($orders_total[$key]['orders_total_id']);
                $orders_total[$key]['orders_id'] = $new_orders_id;
                tep_db_perform('orders_total', $orders_total[$key], 'insert');
            }
        }
        return $new_orders_id;
    }

    public function send_order_mail($order_id) {
        global $currencies;
        $html_products = '';
        $count = 0;
        $this->get_all_data_from_db($order_id);
        //Get all products
        foreach($this->temp_data[$order_id]['orders_products'] as $orders_products_id=>$products_data) {
            $count++;
            $products_ordered_attributes = '';
            //get attributes for product
            if (isset($this->temp_data[$order_id]['orders_products_attributes'][$orders_products_id])) {
                foreach($this->temp_data[$order_id]['orders_products_attributes'][$orders_products_id] as $key=>$value) {
                    $products_ordered_attributes .= "\n\t".$this->temp_data[$order_id]['orders_products_attributes'][$orders_products_id][$key]['products_option'].' '.$this->temp_data[$order_id]['orders_products_attributes'][$orders_products_id][$key]['products_options_values'];
                }
            }
            //oef get attributes
            $products_qty = $products_data['products_quantity'];
            $products_name = $products_data['products_name'].'<span style="font-size:10px;display:block;">'.$products_ordered_attributes.'</span>';
            if (empty($products_data['products_model'])) {
                $products_model = Translate('geen referentie');
            } else {
                $products_model = $products_data['products_model'];
            }
            $products_price = $currencies->format($products_data['final_price'] * $products_data['products_quantity']);
            if ($count%2) {
                $html_products .= '<tr class="odd">';
            } else {
                $html_products .= '<tr class="even">';
            }
            $html_products .= '<td class="boxmailgris">'.$products_name.'</td>';
            $html_products .= '<td class="boxmailgris">'. $products_model.'</td>';
            if (USE_PRICES_TO_QTY == 'true') {
                $html_products .= '<td class="boxmailgris">'. $order->products[$i]['maat'].'</td>';
            }
            $html_products .= '<td class="boxmailgris">'. $products_data['products_quantity'].'</td>';
            $html_products .= '<td class="boxmailgris" style="text-align:right;">'.$products_price.'</td></tr>';
        }
        //eof Get all products

        //Get order totals
        $order_total = 0;
        $Vartaxe = '';
        foreach($this->temp_data[$order_id]['orders_total'] as $orders_total_id=>$ot_data) {
            if ($ot_data['class'] == 'order_total') {
                $order_total = $ot_data['value'];
            }
            $Vartaxe .= $ot_data['title'].': '.$ot_data['text'] . "<br />";
        }
        //eof Get order totals

        //Get payment method
        $Varmodpay = '';
        if (!empty($this->temp_data[$order_id]['orders']['payment_method']) ) {
            $instance_id = end(explode('_', $this->temp_data[$order_id]['orders']['payment_method']));
            $strlen = strlen($this->temp_data[$order_id]['orders']['payment_method']) - (strlen($instance_id)+1);
            $payment_method = substr($this->temp_data[$order_id]['orders']['payment_method'], 0, $strlen);
            global $$payment_method;
            $Varmodpay .= $$payment_method->instances[$this->temp_data[$order_id]['orders']['payment_method']]['title'];
            if (isset($$payment_method->instances[$this->temp_data[$order_id]['orders']['payment_method']]['description'])) {
                $constants = get_defined_constants();
                $description = '';
                $description_lines = explode("\n", stripslashes($$payment_method->instances[$this->temp_data[$order_id]['orders']['payment_method']]['description']));
                foreach($description_lines as $line) {
                    if (preg_match_all('/(\w*\([^)]*\))/',$line,$matches)) {
                        foreach($matches[0] as $match) {
                            print eval('$Nmatch = '.$match.';');
                            $line = str_replace($match, $Nmatch, $line);
                        }
                    }
                    $line = str_replace(array_keys($constants), $constants, $line);
                    $description .= $line.'<br />';
                }
                $Varmodpay .= '<br />'.$description;
            }
        }
        //eof Get payment method

        //Get shipping method
        $Varmodeship = '';
        if (!empty($this->temp_data[$order_id]['orders']['shipping_method']) ) {
            if (strstr($this->temp_data[$order_id]['orders']['shipping_method'], '_')) {
                $instance_id = end(explode('_', $this->temp_data[$order_id]['orders']['shipping_method']));
                $strlen = strlen($this->temp_data[$order_id]['orders']['shipping_method']) - (strlen($instance_id)+1);
                $shipping_method = substr($this->temp_data[$order_id]['orders']['shipping_method'], 0, $strlen);
            } else {
                $shipping_method = $this->temp_data[$order_id]['orders']['shipping_method'];
            }
            global $$shipping_method;
            $Varmodeship .= $$shipping_method->instances[$this->temp_data[$order_id]['orders']['shipping_method']]['title'];
            if (isset($$shipping_method->instances)) {
                if (isset($$shipping_method->instances[$this->temp_data[$order_id]['orders']['shipping_method']]['description'])) {
                    $constants = get_defined_constants();
                    $description = '';
                    $description_lines = explode("\n", stripslashes($$shipping_method->instances[$this->temp_data[$order_id]['orders']['shipping_method']]['description']));
                    foreach($description_lines as $line) {
                        if (preg_match_all('/(\w*\([^)]*\))/',$line,$matches)) {
                            foreach($matches[0] as $match) {
                                print eval('$Nmatch = '.$match.';');
                                $line = str_replace($match, $Nmatch, $line);
                            }
                        }
                        $line = str_replace(array_keys($constants), $constants, $line);
                        $description .= $line;
                    }
                    $Varmodeship .= '<br />'.$description;
                }
            } else {
                //method title
                if (method_exists($$shipping_method, 'getTitle')) {
                    $Varmodeship .= '<br />'.Translate($$shipping_method->getTitle());
                } else {
                    $Varmodeship .= '<br />'.$$shipping_method->config['description'];
                }
            }
            if (GIFT_WRAP=="true") {
             if ($this->temp_data[$order_id]['orders']['gift_wrap']=='1')
              $Varmodeship.="<br>".Translate("Gift wrap selected");
            }
            if (isset($$shipping_method->instances[$this->temp_data[$order_id]['orders']['payment_method']]['description'])) {
                $constants = get_defined_constants();
                $description = '';
                $description_lines = explode("\n", stripslashes($$payment_method->instances[$this->temp_data[$order_id]['orders']['payment_method']]['description']));
                foreach($description_lines as $line) {
                    if (preg_match_all('/(\w*\([^)]*\))/',$line,$matches)) {
                        foreach($matches[0] as $match) {
                            print eval('$Nmatch = '.$match.';');
                            $line = str_replace($match, $Nmatch, $line);
                        }
                    }
                    $line = str_replace(array_keys($constants), $constants, $line);
                    $description .= $line;
                }
                $Varmodpay .= '<br />'.$description;
            }
        }
        //eof Get shipping method

        //email vars
        $Varlogo = '<a href="' . HTTP_SERVER . DIR_WS_CATALOG . '"><img src="'. HTTP_SERVER . DIR_WS_CATALOG . DIR_WS_IMAGES.'mail/logo.jpg" border="0" /></a> ';
        $Vartable1 = '<table width="100%"  border="0" cellpadding="0" cellspacing="0" bgcolor="#ffffff">';
        $Vartable2 = '<table width="100%" border="0" cellpadding="3" cellspacing="3" bgcolor="#EFEFEF">';
        $varHiddenTest = Translate('Bestelnummer').': <STRONG> '.$order_id.'</STRONG>'."\n".Translate('Besteldatum').': <strong>'.utf8_encode(strftime(DATE_FORMAT_LONG)).'</strong>'."\n".Translate('Totaal').': '.str_replace('&euro;', '€', $order_total)."\n";
        $Vartext1 = '<b>'.Translate('Beste').' '.$this->temp_data[$order_id]['orders']['customers_name'].' </b><br>'.Translate('Hartelijk dank voor uw bestelling bij ').' '.STORE_NAME;
        $Vartext2 = '    '.Translate('Bestelnummer').': <STRONG> '.$order_id.'</STRONG><br>'.Translate('Besteldatum').': <strong>'.utf8_encode(strftime(DATE_FORMAT_LONG)).'</strong><br><a href="'.HTTP_SERVER.DIR_WS_CATALOG.'account_history_info.php?order_id='.$order_id.'">'.Translate('Gedetailleerde pakbon').': '.$order_id.'</a>' ;
        $Varmailfooter = Translate('Dit email adres is ingegeven op onze website door u of door een van onze bezoekers. Als u zich niet ingeschreven hebt op onze website contacteer ons dan via').' '.STORE_OWNER_EMAIL_ADDRESS;
        $Varretour = '<b>'.Translate('De consument heeft het recht aan de onderneming mee te delen dat hij afziet van de aankoop, zonder betaling van een boete en zonder opgave van een motief binnen 14 kalenderdagen vanaf de dag die volgt op de levering van het goed.').'</b>';
        $VarArticles= Translate('Item');
        $VarModele= Translate('Product nummer');
        $VarMaat= Translate('Maat');
        $VarQte= Translate('Aantal');
        $VarTotal= Translate('Totaal');
        $VarAddresship = Translate('Verzendadres');
        $VarAddressbill = Translate('Factuuradres');
        $Varmetodpaye = Translate('Betaalmethode');
        $Varmetodship = Translate('Verzendmethode');
        $Vardetail = '';
        $Varhttp = '<base href="' . HTTP_SERVER . DIR_WS_CATALOG . '">';
        $Varstyle = '<link rel="stylesheet" type="text/css" href="stylesheetmail.css">';
        $Varcomment = '';
        foreach($this->temp_data[$order_id]['orders_status_history'] as $key=>$data) {
            if ($data['comments']) {
                $Varcomment = '<tr><td colspan="2" class="boxmail">'.Translate('Opmerking').'</td></tr>';
                $Varcomment .= '<tr><td colspan="2">'.$data['comments'].'</td></tr>';
            }
        }
        //eof email vars

        //Get shipping address
        $Varshipaddress = '';
        $shipping_address = array();
        $shipping_address['firstname'] = $this->temp_data[$order_id]['orders']['delivery_name'];
        $shipping_address['email_address'] = $this->temp_data[$order_id]['orders']['customers_email_address'];
        $shipping_address['telephone'] = $this->temp_data[$order_id]['orders']['customers_telephone'];
        $shipping_address['street_address'] = $this->temp_data[$order_id]['orders']['delivery_street_address'];
        $shipping_address['suburb'] = $this->temp_data[$order_id]['orders']['delivery_suburb'];
        $shipping_address['city'] = $this->temp_data[$order_id]['orders']['delivery_city'];
        $shipping_address['state'] = $this->temp_data[$order_id]['orders']['delivery_state'];
        $shipping_address['country']['title'] = $this->temp_data[$order_id]['orders']['delivery_country'];
        $shipping_address['postcode'] = $this->temp_data[$order_id]['orders']['delivery_postcode'];

	if (!empty($this->temp_data[$order_id]['orders']['delivery_company'])) $shipping_address['company'] = $this->temp_data[$order_id]['orders']['delivery_company'];
	else $shipping_address['company'] = $this->temp_data[$order_id]['orders']['billing_company'];

	if (!empty($shipping_address['tva_intracom']))  $shipping_address['tva_intracom'] = $this->temp_data[$order_id]['orders']['delivery_tva_intracom'];
	else $shipping_address['tva_intracom'] = $this->temp_data[$order_id]['orders']['billing_tva_intracom'];

        $Varshipaddress = tep_address_format($this->temp_data[$order_id]['orders']['delivery_address_format_id'], $shipping_address, $html = false, $boln = '', $eoln = "\n");
        //eof Get shipping address

        //Get payment address
        $Varadpay = '';
        $payment_address = array();
        $payment_address['firstname'] = $this->temp_data[$order_id]['orders']['billing_name'];
        $payment_address['email_address'] = $this->temp_data[$order_id]['orders']['customers_email_address'];
        $payment_address['telephone'] = $this->temp_data[$order_id]['orders']['customers_telephone'];
        $payment_address['street_address'] = $this->temp_data[$order_id]['orders']['billing_street_address'];
        $payment_address['suburb'] = $this->temp_data[$order_id]['orders']['billing_suburb'];
        $payment_address['city'] = $this->temp_data[$order_id]['orders']['billing_city'];
        $payment_address['state'] = $this->temp_data[$order_id]['orders']['billing_state'];
        $payment_address['country']['title'] = $this->temp_data[$order_id]['orders']['billing_country'];
        $payment_address['postcode'] = $this->temp_data[$order_id]['orders']['billing_postcode'];
	$payment_address['company'] = $this->temp_data[$order_id]['orders']['billing_company'];
	$payment_address['tva_intracom'] = $this->temp_data[$order_id]['orders']['billing_tva_intracom'];
        $Varadpay = tep_address_format($this->temp_data[$order_id]['orders']['billing_address_format_id'], $payment_address, $html = false, $boln = '', $eoln = "\n");
        //eof Get payment address

        //load email template
        $cwd = getcwd();
        chdir($_SERVER['DOCUMENT_ROOT'].DIR_WS_HTTP_CATALOG);
        require(DIR_WS_MODULES . 'email/html_checkout_process.php');
        $email_order = $html_email_order ;
        chdir($cwd);

        //send email
        if (tep_mail($this->temp_data[$order_id]['orders']['customers_name'], $this->temp_data[$order_id]['orders']['customers_email_address'], Translate('Verwerking bestelling'), $email_order, STORE_NAME, STORE_OWNER_EMAIL_ADDRESS)) {
            tep_db_query('UPDATE orders_status_history SET customer_notified = 1 WHERE orders_id = "'.$order_id.'"');
        }

        //send extra emails
        if (SEND_EXTRA_ORDER_EMAILS_TO != '') {
            tep_mail('', SEND_EXTRA_ORDER_EMAILS_TO, Translate('Verwerking bestelling'), $email_order, STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);
        }
    }



////////////////////////////////////////////////////////// 21-05-2013 //customer email on order status
    ///ORIGINAL //public function send_order_error_mail($subject, $content) {
    public function send_order_error_mail($subject, $content, $customerEmail = '') {
        $html_products = '';

        /*EMAIL VARIABLES*/
        $Vartable1 = '<table width="100%"  border="0" cellpadding="0" cellspacing="0" bgcolor="#ffffff">';
        $Varlogo = '<a href="' . HTTP_SERVER . DIR_WS_CATALOG . '"><img src="'. HTTP_SERVER . DIR_WS_CATALOG . DIR_WS_IMAGES.'mail/logo.jpg" border="0" /></a> ';
        $Vartable2 = '<table width="100%" border="0" cellpadding="3" cellspacing="3" bgcolor="#EFEFEF">';
        $Vargendertext = Translate('Beste');
        $Vartextmail = $content;
        /*END EMAIL VARIABLES*/

        /*EMAIL TEMPLATE*/
        $cwd = getcwd();
        chdir($_SERVER['DOCUMENT_ROOT'].DIR_WS_HTTP_CATALOG);
        require(DIR_WS_MODULES . 'email/html_standard.php');
        $email_order = $html_email_text ;
        chdir($cwd);

        /*SEND EMAIL*/
        //email to admin
        $tempSubject = Translate('Update bestelling').' '.$subject.' '.Translate('op').' '.STORE_NAME;
        tep_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, $tempSubject, $email_order,STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);

        //mail to customer
        if($customerEmail){
            $tempSubject = Translate('Update voor uw bestelling').' '.$subject .' '.Translate('op').' '.STORE_NAME;
            tep_mail(STORE_NAME, $customerEmail, $tempSubject, $email_order, STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);
        }
    }
////////////////////////////////////////////////////////// 21-05-2013 //customer email on order status

    private function getTranslations() {
        return array('Adres gegevens' => array(
                            '1' => 'Adres gegevens',
                            '2' => 'Des données d\'adresses',
                            '3' => 'Address data',
                            '4' => 'Adressdaten')
                    ,'Volgende stap' => array(
                            '1' => 'Volgende stap',
                            '2' => 'Prochaine étape',
                            '3' => 'Next Step',
                            '4' => 'Next Step')
                    ,'Keer terug naar de shop en vervolledig uw bestelling.' => array(
                            '1' => 'Keer terug naar de shop en vervolledig uw bestelling.',
                            '2' => 'Retour à la boutique et compléter votre commande.',
                            '3' => 'Return to the shop and complete your order.',
                            '4' => 'Zurück in den Laden und füllen Sie Ihre Bestellung.')
                    ,'Ik ben iets vergeten!' => array(
                            '1' => 'Ik ben iets vergeten!',
                            '2' => 'J\'ai oublié quelque chose!',
                            '3' => 'I forgot something!',
                            '4' => 'Ich habe etwas vergessen!')
                    ,'Bestelling bevestigen' => array(
                            '1' => 'Bestelling bevestigen',
                            '2' => 'Valider la commande',
                            '3' => 'confirm order',
                            '4' => 'Bestellung bestätigen')
                    ,'Bestelnummer' => array(
                            '1' => 'Bestelnummer',
                            '2' => 'Numéro de commande',
                            '3' => 'Order number',
                            '4' => 'Bestell-Nummer')
                    ,'Besteldatum' => array(
                            '1' => 'Besteldatum',
                            '2' => 'Date de commande',
                            '3' => 'Order Date',
                            '4' => 'Sortierung: Datum')
                    ,'Totaal' => array(
                            '1' => 'Totaal',
                            '2' => 'Total',
                            '3' => 'Total',
                            '4' => 'Gesamt')
                    ,'Beste' => array(
                            '1' => 'Beste',
                            '2' => 'Bonjour',
                            '3' => 'Hello',
                            '4' => 'Hallo')
                    ,'Hartelijk dank voor uw bestelling bij' => array(
                            '1' => 'Hartelijk dank voor uw bestelling bij',
                            '2' => 'Nous vous remercions de votre commande',
                            '3' => 'Thank you for your order',
                            '4' => 'Vielen Dank für Ihre Bestellung')
                    ,'Gedetailleerde pakbon' => array(
                            '1' => 'Gedetailleerde pakbon',
                            '2' => 'Détaillée la liste de colisage',
                            '3' => 'Detailed packing list',
                            '4' => 'Detaillierte Packliste')
                    ,'Dit email adres is ingegeven op onze website door u of door een van onze bezoekers. Als u zich niet ingeschreven hebt op onze website contacteer ons dan via' => array(
                            '1' => 'Dit email adres is ingegeven op onze website door u of door een van onze bezoekers. Als u zich niet ingeschreven hebt op onze website contacteer ons dan via',
                            '2' => 'Cette adresse email est entré sur notre site par vous ou l\'un de nos visiteurs. Si vous n\'êtes pas inscrit sur ​​notre site s\'il vous plaît contactez-nous au',
                            '3' => 'This email address is entered on our website by you or any of our visitors. If you have not registered on our website please contact us at',
                            '4' => 'Diese E-Mail-Adresse ist auf unserer Website durch Sie oder eine von unseren Besuchern eingetragen. Wenn Sie nicht auf unserer Website registriert haben, kontaktieren Sie uns unter')
                    ,'De consument heeft het recht aan de onderneming mee te delen dat hij afziet van de aankoop, zonder betaling van een boete en zonder opgave van een motief binnen 14 kalenderdagen vanaf de dag die volgt op de levering van het goed.' => array(
                            '1' => 'De consument heeft het recht aan de onderneming mee te delen dat hij afziet van de aankoop, zonder betaling van een boete en zonder opgave van een motief binnen 14 kalenderdagen vanaf de dag die volgt op de levering van het goed.',
                            '2' => 'Le consommateur a le droit de la société de révéler qu\'il abandonne l\'achat sans pénalité et sans donner de raison dans les 14 jours calendaires à compter du jour suivant la livraison des marchandises.',
                            '3' => 'The consumer has the right to the company to disclose that he abandons the purchase without penalty and without giving a reason within 14 calendar days from the day following the delivery of the goods.',
                            '4' => 'Der Verbraucher hat das Recht, die Gesellschaft offen zu legen, dass er den Kauf ohne Strafe und ohne Angabe von Gründen innerhalb von 14 Kalendertagen ab dem Tag nach der Ablieferung der Ware verzichtet.')
                    ,'Item' => array(
                            '1' => 'Item',
                            '2' => 'Article',
                            '3' => 'Article',
                            '4' => 'Artikel')
                    ,'Product nummer' => array(
                            '1' => 'Product nummer',
                            '2' => 'Numéro de l\'article',
                            '3' => 'Product number',
                            '4' => 'Produktnummer')
                    ,'Maat' => array(
                            '1' => 'Maat',
                            '2' => 'Taille',
                            '3' => 'Size',
                            '4' => 'Größe')
                    ,'Aantal' => array(
                            '1' => 'Aantal',
                            '2' => 'Nombre',
                            '3' => 'Amount',
                            '4' => 'Anzahl')
                    ,'Verzendadres' => array(
                            '1' => 'Verzendadres',
                            '2' => 'Adresse d\'expédition',
                            '3' => 'Shipping Address',
                            '4' => 'Versandadresse')
                    ,'Factuuradres' => array(
                            '1' => 'Factuuradres',
                            '2' => 'Adresse de facturation',
                            '3' => 'Billing Address',
                            '4' => 'Rechnungsadresse')
                    ,'Betaalmethode' => array(
                            '1' => 'Betaalmethode',
                            '2' => 'Paiement',
                            '3' => 'Payment',
                            '4' => 'Zahlung')
                    ,'Verzendmethode' => array(
                            '1' => 'Verzendmethode',
                            '2' => 'Méthode d\'expédition',
                            '3' => 'Shipping Method',
                            '4' => 'Liefer-Methode')
                    ,'Opmerking' => array(
                            '1' => 'Opmerking',
                            '2' => 'Remarque',
                            '3' => 'Remark',
                            '4' => 'Bemerkung')
                    ,'Verwerking bestelling' => array(
                            '1' => 'Verwerking bestelling',
                            '2' => 'L\'ordre de traitement',
                            '3' => 'Processing order',
                            '4' => 'Bearbeitungsreihenfolge')
                    ,'Bestellingen' => array(
                            '1' => 'Bestellingen',
                            '2' => 'Ordres',
                            '3' => 'Orders',
                            '4' => 'Bestellungen')
                    ,'Maak uw keuze' => array(
                            '1' => 'Maak uw keuze',
                            '2' => 'Faites votre choix',
                            '3' => 'Make your choice',
                            '4' => 'Treffen Sie Ihre Wahl')
                    ,'geen referentie' => array(
                            '1' => 'geen referentie',
                            '2' => 'aucune référence',
                            '3' => 'no reference',
                            '4' => 'kein Hinweis')
                    );
    }

//////////////////////////////////////////////////////////////////////////// 07-06-2013
    ////////////////////////////////////////////////////////////////// function to update product quantity in product table after order success; - 23-05-2013
    private function setQuantityInProductsTable($tempProductId,$tempOrderedQty){
        if($tempProductId){
            tep_db_query('UPDATE products prd, (SELECT products_quantity FROM products where products_id = "'.$tempProductId.'") prd2 SET prd.products_quantity = (prd2.products_quantity-'.(int)$tempOrderedQty.') where prd.products_id="'.$tempProductId.'"');
        }
    }
//////////////////////////////////////////////////////////////////////////// 07-06-2013

}

////////////////////////Custom function to get customer email id based on order id - 21-05-2013
////////////////////////////////////////////////////////// 21-05-2013 //customer email on order status
function getCustomerOrderEmailId($tempOrderId){
    $qryString = tep_db_query('SELECT customers_email_address FROM orders WHERE orders_id = '.$tempOrderId);
    $arrResult = tep_db_fetch_array($qryString);
    return $arrResult['customers_email_address'];
}
?>

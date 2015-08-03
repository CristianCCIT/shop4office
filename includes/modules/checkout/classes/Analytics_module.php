<?php
class Analytics
{
    private $tracker_id;

    function __construct(){
        global $tracker_id;
        if (!defined(ANALYTICS_STORE_DB))
          tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_group_id, set_function, access_level) values ('Store events in DB', 'ANALYTICS_STORE_DB', 'true', '33', \"tep_cfg_select_option(array('true', 'false'),\", '2')");

        if (!$this->abo_analytics_has_tracker()) {
            $this->tracker_id = $this->abo_analytics_create_tracker();
            $tracker_id = $this->tracker_id;
        } else {
            $this->tracker_id = $this->abo_analytics_read_tracker();
            $tracker_id = $this->tracker_id;
        }
    }

    public function module_load() {
        global $application_core;

        // Listen to a shitload of events.

        $application_core->hook("Analytics");
    }

    public function abo_analytics_add_action($type, $description, $event, $data = '') {
		global $customer_id;
		if (defined(ANALYTICS_STORE_DB) && ANALYTICS_STORE_DB=='false') return false;
		if (is_array($data) || is_object($data)) {
			$data = serialize($data);
		}
		tep_db_query('INSERT INTO analytics_user (user_hash, type, description, event, data, customers_id) VALUES("'.$_COOKIE['abo_userid'].'", "'.$type.'", "'.$description.'", "'.$event.'", "'.addslashes($data).'", "'.$customer_id.'")');
    }

    public function abo_analytics_get_trackerid() {
        return $this->tracker_id;
    }

    private function abo_analytics_read_tracker() {
        return $_COOKIE['abo_userid'];
    }

    private function abo_analytics_create_tracker() {
        $unique = false;

        while (!$unique) {
            $rand = rand(0, 5000);
            $hash = md5($rand);

            $query = "SELECT * FROM analytics_user WHERE user_hash = '" . $hash . "'";
            $unique = true;

            $resource = tep_db_query($query);

            if (tep_db_num_rows($resource) > 0) {
                $unique = false;
            } else {
                $unique = true;
            }
        }

        $twomonths = 60 * 60 * 24 * 60 + time();
        setcookie("abo_userid", $hash, $twomonths);

        return $hash;
    }

    private function abo_analytics_has_tracker() {

        if (isset($_COOKIE['abo_userid']) && $_COOKIE['abo_userid'] != "") {
            return true;
        } else {
            return false;
        }
    }
}
?>
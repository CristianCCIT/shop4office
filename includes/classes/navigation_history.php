<?php
/*
  $Id: navigation_history.php 1739 2007-12-20 00:52:16Z hpdl $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  class navigationHistory {
    var $path, $snapshot;

    function navigationHistory() {
      $this->reset();
    }

    function reset() {
      $this->path = array();
      $this->snapshot = array();
    }

	function add_current_page() {
		global $_SERVER, $_GET, $_POST, $request_type, $cPath;
		if (count($this->path) > 10) {
			unset($this->path[0]);
			$this->path = array_values($this->path);
		}
		$set = 'true';
		$count = count($this->path);
		if (basename($_SERVER['PHP_SELF']) != 'login.php') {
			for ($i=0; $i<$count; $i++) {
				if ( ($this->path[$i]['page'] == basename($_SERVER['PHP_SELF'])) ) {
					if (isset($cPath)) {
						if (!isset($this->path[$i]['get']['cPath'])) {
							continue;
						} else {
							if ($this->path[$i]['get']['cPath'] == $cPath) {
								unset($this->path[$i]);
							}
						}
					} else {
						array_splice($this->path, ($i));
						$set = 'true';
						break;
					}
				}
			}
			if ($set == 'true') {
				$this->path[] = array('page' => basename($_SERVER['PHP_SELF']),
										'mode' => $request_type,
										'get' => $this->filter_parameters($_GET),
										'post' => $this->filter_parameters($_POST));
			}
		}
		$this->path = array_values($this->path);
	}

    function remove_current_page() {
      global $PHP_SELF;

      $last_entry_position = sizeof($this->path) - 1;
      if ($this->path[$last_entry_position]['page'] == basename($PHP_SELF)) {
        unset($this->path[$last_entry_position]);
      }
    }

    function set_snapshot($page = '') {
      global $_SERVER, $_GET, $_POST, $request_type;
      if (is_array($page)) {
        $this->snapshot = array('page' => $page['page'],
                                'mode' => $page['mode'],
                                'get' => $this->filter_parameters($page['get']),
                                'post' => $this->filter_parameters($page['post']));
      } else {
		$url_data = tep_get_url_data();
		if (!empty($url_data['page'])) {
			$this->snapshot = array('page' => $url_data['page'],
									'mode' => $request_type,
									'get' => $this->filter_parameters($url_data['get']),
									'post' => $this->filter_parameters($_POST));
		} else {
			if (basename($_SERVER['PHP_SELF']) != 'login.php') {
				$this->snapshot = array('page' => basename($_SERVER['PHP_SELF']),
										'mode' => $request_type,
										'get' => $this->filter_parameters($_GET),
										'post' => $this->filter_parameters($_POST));
			}
		}
      }
    }

    function clear_snapshot() {
      $this->snapshot = array();
    }

    function set_path_as_snapshot($history = 0) {
      $pos = (sizeof($this->path)-1-$history);
      $this->snapshot = array('page' => $this->path[$pos]['page'],
                              'mode' => $this->path[$pos]['mode'],
                              'get' => $this->path[$pos]['get'],
                              'post' => $this->path[$pos]['post']);
    }

    function debug() {
      for ($i=0, $n=sizeof($this->path); $i<$n; $i++) {
        echo $this->path[$i]['page'] . '?';
        while (list($key, $value) = each($this->path[$i]['get'])) {
          echo $key . '=' . $value . '&';
        }
        if (sizeof($this->path[$i]['post']) > 0) {
          echo '<br>';
          while (list($key, $value) = each($this->path[$i]['post'])) {
            echo '&nbsp;&nbsp;<b>' . $key . '=' . $value . '</b><br>';
          }
        }
        echo '<br>';
      }

      if (sizeof($this->snapshot) > 0) {
        echo '<br><br>';

        echo $this->snapshot['mode'] . ' ' . $this->snapshot['page'] . '?' . tep_array_to_string($this->snapshot['get'], array(tep_session_name())) . '<br>';
      }
    }

    function filter_parameters($parameters) {
      $clean = array();

      if (is_array($parameters)) {
        reset($parameters);
        while (list($key, $value) = each($parameters)) {
          if (strpos($key, '_nh-dns') < 1) {
            $clean[$key] = $value;
          }
        }
      }

      return $clean;
    }

    function unserialize($broken) {
      for(reset($broken);$kv=each($broken);) {
        $key=$kv['key'];
        if (gettype($this->$key)!="user function")
        $this->$key=$kv['value'];
      }
    }
  }
?>

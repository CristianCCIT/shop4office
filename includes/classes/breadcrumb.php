<?php
/*
  $Id: breadcrumb.php 1739 2007-12-20 00:52:16Z hpdl $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  class breadcrumb {
    var $_trail;

    function breadcrumb() {
      $this->reset();
    }

    function reset() {
      $this->_trail = array();
    }

    function add($title, $link = '') {
      $this->_trail[] = array('title' => $title, 'link' => $link);
    }

    function trail($separator = ' - ') {
      $trail_string = '';

      for ($i=0, $n=sizeof($this->_trail); $i<$n; $i++) {
        if (isset($this->_trail[$i]['link']) && tep_not_null($this->_trail[$i]['link'])) {
          $trail_string .= '<li><a href="' . $this->_trail[$i]['link'] . '" class="headerNavigation">' . convert_to_entities($this->_trail[$i]['title']) . '</a></li>';
        } else {
          $trail_string .= '<li>'.convert_to_entities($this->_trail[$i]['title']).'</li>';
        }

        //if (($i+1) < $n) $trail_string .= $separator;
      }

      return '<ul>'.$trail_string.'</ul>';
    }
  }
?>

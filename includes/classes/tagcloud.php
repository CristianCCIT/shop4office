<?php
	class tagcloud {
		var $a_tag_styles = array('xx-small', 'x-small', 'small', 'medium', 'large', 'x-large', 'xx-large');
		var $max_shown_tags;
		var $a_tc_data;
		function tagcloud($max_shown_tags = 60) {
			$this->max_shown_tags = $max_shown_tags;
		}
		function set_tagcloud_data($a_tc_data) {
			$this->a_tc_data = $a_tc_data;
			arsort($this->a_tc_data);
			$a_tags = array();
			reset($this->a_tc_data);
			$tag_count = count($this->a_tc_data);
			$i = 1;
			while ($i <= $tag_count && $i <= $this->max_shown_tags) {
				$a_tags[key($this->a_tc_data)] = current($this->a_tc_data);
				next($this->a_tc_data);
				$i++;
			}
			$this->a_tc_data = $a_tags;
			return true;
		}
		function get_tagcloud() {
			if (count($this->a_tc_data) <= 0) {
				return '';
			}
			reset($this->a_tc_data);
			$count_high = current($this->a_tc_data);
			$count_low = end($this->a_tc_data);
			$range = ($count_high - $count_low) / (count($this->a_tag_styles) - 1);
			ksort($this->a_tc_data);
			if ($range > 0) {
				$b_first = true;
				$prev_search = '';
				foreach ($this->a_tc_data as $tag => $tagcount) {
					if ($b_first) {
						$html_cloud = '<a href="advanced_search_result.php?keywords='.$tag.'&search_in_description=1" class="' . $this->a_tag_styles[round(($tagcount - $count_low) / $range, 0)] . '">' . $tag . '</a> ';
						$b_first = false;
					} else {
						$html_cloud .= '<a href="advanced_search_result.php?keywords='.$tag.'&search_in_description=1" class="' . $this->a_tag_styles[round(($tagcount - $count_low) / $range, 0)] . '">' . $tag . '</a> ';
					}
				}
			} else {
				$b_first = true;
				foreach ($this->a_tc_data as $tag => $tagcount) {
					if ($b_first) {
						$html_cloud = '<span class="tag' . $this->a_tag_styles[round((count($this->a_tag_styles)-1) / 2, 0)] . '"><a href="advanced_search.php?keywords='.$tag.'&search_in_description=1">' . $tag . '</a></span>';
						$b_first = false;
					} else {
						$html_cloud .= ' <span class="tag' . $this->a_tag_styles[round((count($this->a_tag_styles)-1) / 2, 0)] . '"><a href="advanced_search.php?keywords='.$tag.'&search_in_description=1">' . $tag . '</a></span>';
					}
				}
			}
			return $html_cloud;
		}
	}
?>
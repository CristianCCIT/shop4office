<?php
/**
 * Created by PhpStorm.
 * User: Cristian
 * Date: 5/7/2015
 * Time: 8:30 AM
 */

class page_layout {
	private $html = array();

	/**
	 * Set html attributes of the page
	 *
	 * @param $key
	 * @param null $value
	 * @return $this
	 */
	public function setHtml($key, $value=null)
	{
		if(is_array($key)){
			$this->html = array_merge($this->html, $key);
		} elseif($value){
			$this->html[$key] = $value;
		}

		return $this;
	}

	/**
	 * Get html attributes of the page
	 *
	 * @param null $key
	 * @return array|bool
	 */
	public function getHtml($key = null)
	{
		if($key){
			if(isset($this->html[$key])){
				return $this->html[$key];
			}

			return false;
		}

		return $this->html;
	}
}
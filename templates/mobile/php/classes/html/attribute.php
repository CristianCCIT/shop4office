<?php
/**
 * Created by PhpStorm.
 * User: Cristian
 * Date: 5/7/2015
 * Time: 9:51 AM
 */

class html_attribute {
	protected $attr;
	protected $value;

	public function __construct($attr, $values)
	{
		$this->attr = $attr;
		$this->value = $values;
	}

	public function make()
	{
		return ' '.$this->attr.'='.implode(' ',$this->value).' ';
	}
}
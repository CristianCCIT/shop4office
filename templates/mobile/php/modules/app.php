<?php
/**
 * Created by PhpStorm.
 * User: Cristian
 * Date: 5/7/2015
 * Time: 12:51 PM
 */

class app {

	private $IoC = array();

	public function __construct()
	{
		$this->register(array(
			'html' => new html_html()
		));
	}

	public function run()
	{
//		echo 'Running ...';
	}

	public function register($alias, $class = null)
	{
		if(is_array($alias)){
			$this->IoC = array_merge($this->IoC, $alias);
		} else {
			$this->IoC[$alias] = $class;
		}
	}
}
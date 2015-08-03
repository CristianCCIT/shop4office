<?php
/**
 * Created by PhpStorm.
 * User: Cristian
 * Date: 5/7/2015
 * Time: 8:35 AM
 */

class html_html {
	private $doctype;
	private $head;
	private $body;

	public function __construct()
	{

	}

	public function drawHtml()
	{
		echo $this->doctype->render(), $this->render(array($this->head->render(), $this->body->render()));
	}
}
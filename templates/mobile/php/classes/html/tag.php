<?php
/**
 * Created by PhpStorm.
 * User: Cristian
 * Date: 5/7/2015
 * Time: 8:43 AM
 */

class html_tag {
	protected $oTag;
	protected $cTag;
	protected $tagAttributes;
	protected $tagContent;

	public function __construct($oTag, $cTag=false, $attr=false, $content=false)
	{
		$this->oTag = $oTag;
		$this->cTag = $cTag;
		$this->tagAttributes = $attr;
		$this->tagContent = $content;
	}

	public function render()
	{
		return sprintf('<%s %s>');'<'.$this->oTag.'>';
	}

	protected function renderAttributes()
	{
		$attributes = '';
		if($this->tagAttributes){
			foreach($this->tagAttributes as $attr){
				$attributes.= $attr->make();
			}
		}

		return $attributes;
	}

	protected function addAttribute($attributeObject){
		$this->tagAttributes = array_merge($this->tagAttributes, array());
	}
}
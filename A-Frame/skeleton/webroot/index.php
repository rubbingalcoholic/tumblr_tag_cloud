<?
	/**
	 * This file is a traffic cop. It sends all traffic to ../index.php which passes the request off to Aframe. This is 
	 * just here as a palceholder so webroot can be the document root and still have framework access.
	 * 
	 * 
	 * Copyright (c) 2009, Lyon Bros Enterprises, LLC. (http://www.lyonbros.com)
	 * 
	 * Licensed under The MIT License. 
	 * Redistributions of files must retain the above copyright notice.
	 * 
	 * @copyright	Copyright (c) 2009, Lyon Bros Enterprises, LLC. (http://www.lyonbros.com)
	 * @package		aframe
	 * @subpackage	aframe.skeleton
	 * @license		http://www.opensource.org/licenses/mit-license.php
	 */
	
	$lbase	=	dirname(dirname(__FILE__));
	include_once $lbase . '/index.php';
?>
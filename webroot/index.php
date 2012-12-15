<?
	/**
	 *   TTTTTTTTTT  UU     UU  MM          MM  BBBBBB    LL       RRRRRR
	 *       TT      UU     UU  MMMM      MMMM  BB    BB  LL       RR    RR
	 *       TT      UU     UU  MM  MM  MM  MM  BBBBBB    LL       RRRRRR
	 *       TT      UU     UU  MM    MM    MM  BB    BB  LL       RR    RR
	 *       TT        UUUUU    MM          MM  BBBBBB    LLLLLLL  RR    RR
	 *
	 *
	 *   TTTTTTTTTT    AAAA      GGGGGG
	 *       TT      AA    AA  GG    
	 *       TT      AAAAAAAA  GG    GGGG
	 *       TT      AA    AA  GG      GG
	 *       TT      AA    AA    GGGGGG
	 *
	 *
	 *      CCCCCCC  LL         OOOOO    UU     UU  DDDDDD
	 *    CC         LL       OO     OO  UU     UU  DD    DD
	 *    CC         LL       OO     OO  UU     UU  DD     DD
	 *    CC         LL       OO     OO  UU     UU  DD    DD
	 *      CCCCCCC  LLLLLLL    OOOOO      UUUUU    DDDDDD
	 *
	 *
	 * -------------------- WEBROOT INDEX FILE LOLOLOL ------------------------
	 */
	/**
	 *	This file checks that the local configuration file (includes/local.php)
	 *  exists with the proper permissions. If not, a helpful error screen will
	 *  be shown. Otherwise, we simply include ../index.php to load the A-Frame
	 *  MVC framework.
	 *
	 *	If you're trying to pick apart the code, a good starting place would be
	 *	includes/routes.php ... notice that our default route is admin/index
	 *
	 *	So then, open up the controllers/admin.php and notice a function called
	 *	index. You're welcome.
	 *
	 * ------------------------------------------------------------------------
	 */
	$lbase	=	dirname(dirname(__FILE__));
	if (!file_exists($lbase .'/includes/local.php'))
	{
		$main_content = file_get_contents($lbase . '/views/errors/missing_local_php.php');
		include $lbase . '/views/layouts/message.php';
		exit;
	}
	if (!is_readable($lbase .'/includes/local.php') || !is_executable($lbase .'/includes/local.php'))
	{
		$main_content = file_get_contents($lbase . '/views/errors/bad_local_php_permissions.php');
		include $lbase . '/views/layouts/message.php';
		exit;
	}
	/**
	 * This file is a traffic cop. It sends all traffic to ../index.php which passes the request off to A-Frame. This is 
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
	include_once $lbase . '/index.php';
?>
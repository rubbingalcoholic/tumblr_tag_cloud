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
	 * ---------------------- MAIN INDEX FILE LOLOLOL -------------------------
	 */
	/**
	 *	This file simply includes the A-Frame framework, which handles loading
	 *  the app based on the controllers, models and libraries specified in the
	 *  respectively-named directories.
	 *
	 *	If you're trying to pick apart the code, a good starting place would be
	 *	includes/routes.php ... notice that our default route is admin/index
	 *
	 *	So then, open up the controllers/admin.php and notice a function called
	 *	index. You're welcome.
	 *
	 * ------------------------------------------------------------------------
	 */
	/**
	 *	Actually, we're gonna do one error check to make sure you aren't using
	 *	this file in your webroot.
	 */
	if (!isset($lbase))
	{
		$main_content = file_get_contents($lbase . '/views/errors/bad_webroot_layout.php');
		include $lbase . '/views/layouts/message.php';
		exit;
	}
	/**
	 * This includes and runs Aframe, which processes the incoming request.
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
	$app_base	=	dirname(__FILE__);
	$core_base	=	$app_base . '/A-Frame';
	include_once $core_base . '/index.php';
?>
<?
	/**
	 * The message object. Usually lives in session for cross-request access and passing front-end messages
	 * to views on another pageload.
	 * 
	 * 
	 * Copyright (c) 2009, Lyon Bros Enterprises, LLC. (http://www.lyonbros.com)
	 * 
	 * Licensed under The MIT License. 
	 * Redistributions of files must retain the above copyright notice.
	 * 
	 * @copyright	Copyright (c) 2009, Lyon Bros Enterprises, LLC. (http://www.lyonbros.com)
	 * @package		aframe
	 * @subpackage	aframe.core
	 * @license		http://www.opensource.org/licenses/mit-license.php
	 */
	
	/**
	 * Some stupid defines (success or error or warning)
	 */
	define('MSG_SUCCESS', 	0);
	define('MSG_ERROR', 	1);
	define('MSG_WARNING', 	2);
	
	/**
	 * Messaging class.
	 * 
	 * Very simple object for storing messages between layers of the App. If chair_model loads a chair from the database
	 * but the chair has a broken leg, chair_model would probably want the saleperson to know. It can send a message to 
	 * the view by adding it to the msg object. When the front-end view loads, all messages from msg are dumped out. It
	 * is generally stored in session so that it exists across redirects.
	 * 
	 * @package		aframe
	 * @subpackage	aframe.core
	 * @author		Andrew Lyon
	 */
	class msg
	{
		/**
		 * Holds all of our messages
		 * @var array
		 */
		var $messages		=	array();
		
		/**
		 * Number of messages we're holding
		 * @var int
		 */
		var $num_messages	=	0;
		
		/**
		 * If we add a message of $type MSG_ERROR, this flag is set until messages are dumped again.
		 * @var bool
		 */
		var $has_errors		=	false;
		
		/**
		 * Constructor. Does nothing.
		 */
		function msg()
		{
			$this->clear();
		}
		
		/**
		 * Add a message to the queue for later dumping. Sets the error flag if $type == MSG_ERROR
		 * 
		 * @param string $msg	The message we're passing
		 * @param int $type		The type of message we're passing. Either success or error.
		 * @param int $code		The error code (if applicable)
		 */
		function add($msg, $type = MSG_ERROR, $code = 0)
		{
			$this->messages[$this->num_messages]['msg']		=	$msg;
			$this->messages[$this->num_messages]['type']	=	$type;
			$this->messages[$this->num_messages]['code']	=	$code;
			$this->num_messages++;
			if($type == MSG_ERROR)
			{
				$this->has_errors	=	true;
			}
		}
		
		/**
		 * Dump out all messages to a string and return it.
		 *
		 * TODO: don't build fucking JSON by hand when doing JS messages...?
		 * 
		 * @param boolean $dump_javascript	(optional) Dump the messages as javascript array rather than a UL
		 * @param string $js_var			(optional) The name of the javascript var messages are put into
		 * @return string					List of messages, encoded in ul list, into a string.
		 */
		function dump($dump_javascript=false, $js_var = 'msg')
		{
			if (!$dump_javascript) {
				$str	=	'<ul>';
				for($i = 0, $n = $this->num_messages; $i < $n; $i++)
				{
					$type	=	$this->messages[$i]['type'];
					if($type == MSG_ERROR)
					{
						$color	=	'class="error"';
					}
					elseif($type == MSG_SUCCESS)
					{
						$color	=	'class="success"';
					}
					elseif($type == MSG_WARNING)
					{
						$color	=	'class="warning"';
					}
					else
					{
						$color	=	'';
					}
					$str	.=	'<li '.$color.'>'.$this->messages[$i]['msg'].'</li>';
				}
				$str	.=	'</ul>';
			} else {
				if(!empty($js_var))
				{
					$str	=	"<script type=\"text/javascript\" language=\"javascript\">\nvar ". $js_var ." = [\n";
				}
				else
				{
					$str	=	'[';
				}
				for($i = 0, $n = $this->num_messages; $i < $n; $i++)
				{
					if ($i > 0) {
						$str	.=	",\n";
					}
					$type	=	$this->messages[$i]['type'];
					if ($type == MSG_ERROR)
					{
						$type = 'error';
					}
					elseif ($type == MSG_SUCCESS)
					{
						$type = 'success';
					}
					elseif ($type == MSG_WARNING)
					{
						$type = 'warning';
					}
					else
					{
						$type = '';
					}
					$message = explode('|', $this->messages[$i]['msg']);
					if (count($message) > 1)
					{
						$str .= "['".addslashes($message[1])."', '".$type."', '".addslashes($message[0])."']";
					}
					else
					{
						$str .= "['".addslashes($message[0])."', '".$type."']";
					}
				}
				if(!empty($js_var))
				{
					$str	.=	"\n];\n</script>";
				}
				else
				{
					$str	.=	"\n];";
				}
			}
			$this->clear();
			return $str;
		}
		
		/**
		 * Hit the reset switch
		 */
		function clear()
		{
			$this->messages		=	array();
			$this->num_messages	=	0;
			$this->has_errors	=	false;
		}
	}
?>

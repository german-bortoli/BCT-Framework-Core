<?php
	/**
	 * @file
	 * Exception definitions.
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2011
	 * @link http://platform.barcamptransparency.org/
	 */

	/**
	 * Generic platform exception.
	 * 
	 * To take advantage of some useful aspects of the framework (for example the export views),
	 * any exceptions that you throw should use this class as a parent.
	 */
	class BCTPlatformException extends Exception 
	{
		/**
		 * Render the exception using the views system.
		 */
		public function __toString() 
		{
			$class = strtolower(get_class($this));
			
			$content = view("exceptions/$class", array('exception' => $this));
			if ($content)
				return $content;
				
			$content = view('exceptions/__default', array('exception' => $this));
			if ($content)
				return $content;
				
			return false;
		}	
	}
	

	class ClassException extends BCTPlatformException {}
	class ClassNotFoundException extends ClassException {}
	
	
	class BCTObjectException extends BCTPlatformException {}
	class RelationshipException extends BCTObjectException {}
	class AnnotationException extends BCTObjectException {}
	
	
	class DatabaseException extends BCTPlatformException {}
	
	
	class PageException extends BCTPlatformException {}
	class PageNotFoundException extends PageException {}
	
	
	class PluginException extends BCTPlatformException {}
	
	
	class CallException extends BCTPlatformException {}
	class FunctionNotFoundException extends CallException {}
	
	
	class ActionException extends BCTPlatformException {}
	class ActionBlockedException extends ActionException {}
	class ActionNotFoundException extends ActionException {}


	class ExportException extends BCTPlatformException {}
	
	
	class SecurityException extends BCTPlatformException {}
	
	class ConfigurationException extends BCTPlatformException {}

	class FactoryException extends BCTPlatformException {}

	class MessengerException extends BCTPlatformException {}
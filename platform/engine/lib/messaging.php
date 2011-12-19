<?php

    /**
     * @file
     * Messaging.
     *
     * This file contains functions and classes for creating messaging objects
     * for sending messages between users and objects local and remote to the system by
     * various methods including email.
     *
     * @note This is separate from messages.php which displays messages system messages to the
     *	     user, although this might be replaced with a messaging wrapper later on.
     *
     * @package core
     * @license The MIT License (see LICENCE.txt), other licenses available.
     * @author Marcus Povey <marcus@marcus-povey.co.uk>
     * @copyright Marcus Povey 2009-2011
     * @link http://platform.barcamptransparency.org/
     */


     /**
      * Messenger.
      *
      * This class provides the prototype methods for sending messages to users, objects and
      * other addresses.
      */
     abstract class Messenger
     {
	 /**
	  * Send a message to a specific object.
	  * Send $message to an object using addressing details found in $to.
	  * @param Messageable $to Messagable object to send messages to
	  * @param array $message The message fields, which vary depending on delivery method. If a field is required but
	  *			  is missing and can't be derived then a MessengerException is thrown.
	  * @return bool true if message sent, false if not.
	  */
	 abstract public function messageObject(BCTObject $to, array $message);

	 /**
	  * Send a message to an arbitrary address.
	  * @param string|array $address An address in Messenger compatible format
	  * @param array $message The message array to send (@see messageObject)
	  */
	 abstract public function message($address, array $message);

	 /**
	  * Attempt to retrieve a property for a Messageable object.
	  * This method calls a hook, passing the class name of the message handler as namespace, with messenger:getproperty as the hook + the
	  * object we're operating on.
	  * containing the object and the messenger.
	  * @param Messageable $object The object to message.
	  * @param String $property The property.
	  */
	 protected function getProperty(BCTObject $object, $property = 'address')
	 {
	     return trigger_hook(strtolower(get_class($this)), 'messenger:getproperty',
		 $object, false);
	 }
     }

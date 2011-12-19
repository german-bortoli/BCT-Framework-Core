<?php

	/**
	 * @file
	 *
	 * Basic user tools.
	 * 
	 * This file defines a basic user class and defines hooks to interface with them. Extend
	 * this for other kinds of users (eg openid), extending the authentication mechanisms as necessary.
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2011
	 * @link http://www.marcus-povey.co.uk
	 */

	/**
	 * Basic user class.
	 * 
	 * This class defines a basic user class, and provides basic user handling
	 * methods.
	 *
	 * Other users should be an extension of this user.
	 */
	class User extends Object
	{
		/**
		 * Construct the user.
		 */
		public function __construct() 
		{ 
			parent::__construct(); 
			
			$this->setType('user'); 
		}
		
		/**
		 * Set the username.
		 * @param string $username The username.
		 * @return bool
		 */
		public function setUsername($username)
		{
			$username = trim($username);
			
			if (!$username) return false;
			
			$this->username = $username;
		}
		
		/**
		 * Return the username.
		 * @return string
		 */
		public function getUsername() { return $this->username; }
		
		/**
		 * Return a full name.
		 * @return string
		 */
		public function getName() { return $this->name; }
		
		/**
		 * Get the email address of the user.
		 * @return email
		 */
		public function getEmail() { return $this->email; }

		/**
		 * Returns boolean whether a user has a specific role or not.
		 * @param string $role The role in question
		 * @return bool
		 */
		public function hasRole($role)
		{
			if (is_array($this->role))
			    return in_array($role, $this->role);

			if ($this->role == $role) return true;

			return false;
		}

		/**
		 * Add a role to a user.
		 * @param string $role The role
		 * @return bool
		 */
		public function addRole($role)
		{
			if (!$this->role)
			    $this->role = array($role);
			else
			{
			    if (!is_array($this->role))
				    $this->role = array($this->role);

			    $this->role[] = $role;
			    $this->role = array_unique($this->role);
			}

			return true;
		}

		/**
		 * Remove a given role from the user.
		 * @param string $role The role
		 * @return bool
		 */
		public function removeRole($role)
		{
			if (!$this->role) return;

			if (!is_array($this->role))
			{
			    if ($this->role == $role)
			        unset($this->role);
			}
			else
			{
			    $role_array = array();
			    foreach ($this->role as $r)
			    {
				if ($r!=$role)
				    $role_array[] = $r;
			    }

			    $this->role = $role_array;
			}

			return true;
		}
		
		/**
		 * Set the password for this user.
		 * @param string $password The password.
		 * @return bool
		 */
		public function setPassword($password)
		{
			$password = trim($password);
			if (!$password)
				return false;
			
			$salt = substr(md5(rand().microtime()), 0, 8);
			$pw = md5($password.$salt);
			
			$this->password = $pw;
			$this->salt = $salt;
			
			return true;
		}
		
		/**
		 * Does the given password match the one stored.
		 * 
		 * @param string $password The password.
		 */
		public function isPasswordCorrect($password) 
		{ 
			return md5($password.$this->salt) == $this->password; 
		}
		
		/**
		 * Log this user in, establishing them as the current user.
		 * 
		 * @return bool
		 */
		public function login()
		{
			if ($_SESSION['user'] instanceof User)
				$_SESSION['user']->logout();
			
			$_SESSION['user'] = $this;
			
			session_regenerate_id();
			
			return true;
		}
		
		/**
		 * Log a user out.
		 */
		public function logout()
		{
			unset($_SESSION['user']);
		}

                /**
                 * Override the default canEdit() to add the ability for Users to be able
                 * to edit themselves.
                 */
                public function canEdit(User $user = null)
                {
                    if (!$user) $user = user_get_current();
                    if ($user)
                    {
                        // Can always edit myself
                        if ($user->getGUID() == $this->getGUID()) return true;
                    }

		    if (!$user) $user = null;
		    
                    return parent::canEdit($user);
                }

		/**
		 * Override the export of the User object heirachy and ensure that
		 * security sensitive information is not exported.
		 */
		public function safeExport()
		{
		    $export = parent::safeExport();

		    unset($export->salt);
		    unset($export->password);

		    return $export;
		}
	}

	/**
	 * Provides a disk file store for a user.
	 *
	 * This extends DiskFilestore to provide a segregated user data store which
	 * stores all user created files in the same physical location under the
	 * same data root.
	 */
	 class UserDiskFilestore extends DiskFilestore
	 {
	     /// The user this filestore is bound to
	     private $user;

	     public function __construct(User $user)
	     {
		 $this->user = $user;

		 parent::__construct();
	     }

	     /**
	      * Configure a data root for the user.
	      *
	      * This differs from parent::setDataRoot() in that the actual root set
	      * will be specific to the user, but still use $root as its base.
	      *
	      * So, for example for a user with GUID 634 will for a $root of '/var/bct/data/'
	      * will result in a physical location on disk of '/var/bct/data/6/634/' where the
	      * first 6 comes from the first number of the GUID.
	      *
	      * @param string $root Initial base path
	      * @return bool
	      */
	     public function setDataRoot($root = '') {
		 parent::setDataRoot($root); // First set defaults

		 $userid = $this->user->getGUID();

		 // Now set again, but with user dir as well
		 return parent::setDataRoot(parent::getDataRoot() . "{$userid[0]}/{$userid}/");
	     }
	 }

	/**
	 * Get the currently logged in user.
	 * 
	 * Trigger the hook user/current which returns the currently logged in user if any.
	 *
	 * @return User|false
	 */
	function user_get_current()
	{
		return trigger_hook('user', 'current', NULL, false);
	}
	
	/**
	 * Return whether a user is logged in or not.
	 *
	 * @return bool
	 */
	function user_isloggedin()
	{
		if (user_get_current())
			return true;
			
		return false;
	}
	
	/**
	 * Wrapper.
	 * @see user_isloggedin()
	 */
	function user_isloggedon() { return user_isloggedin(); }
	
	/**
	 * Attempt to authenticate a user against some provided credentials, logging that user in if successful.
	 *
	 * @param array $credentials The credentials, for example the username and password.
	 * @return user|false
	 */
	function user_authenticate(array $credentials = null)
	{
		return trigger_hook('user', 'authenticate', $credentials, false);
	}
	
	/**
	 * Attempt to authenticate a user based on some credentials.
	 */
	function user_authenticate_hook($class, $hook, $parameters, $return_value)
	{
		if (
			(is_array($parameters)) &&
			(isset($parameters['username'])) &&
			(isset($parameters['password'])) &&
			(!$return_value)
		)
		{
			$users = getObjects('obj:user%', 
				array('username' => $parameters['username']),
				array('limit' => 1) 
			);
			
			if ($users)
			{
				$user = $users[0];
				
				if ($user->isPasswordCorrect($parameters['password']))
				{
					$user->login();
					return $user;
				}
			}
		}
	}
	
	/**
	 * Return the current user.
	 */
	function user_current_hook($class, $hook, $parameters, $return_value)
	{
		if (isset($_SESSION['user']))
			return $_SESSION['user'];
			
	}
	
	/**
	 * Get a user by username.
	 */
	function user_get_by_username($username)
	{
		$result = getObjects('obj:user%', 
			array('username' => $username),
			array('limit' => 1) 
		);
		
		if ($result)
			return $result[0];
			
		return false;
	}

	/**
	 * Create file store for storing user specific data.
	 */
	function user_filestore_factory($class, $hook, $parameters, $return_value)
	{
	     // If we already have a filestore for this, don't create a new one.
	     if ($return_value)
		return $return_value;

	     // Otherwise we see if we can create a filestore
	     switch ($hook)
	     {
		case 'filestore:user' : // Default user data store
		case 'filestore:disk:user' :
		case 'filestore:user:icon' : // Also the default store for icons.
		    
		    $user = $parameters['user'];
		    if (!$user) $user = user_get_current();
		    if (!$user) throw new FactoryException(_echo('user:datastore:nouser'));

		    return new UserDiskFilestore($user);
	     }
	}
	
	function user_init()
	{
		global $CONFIG;
		
		// Authentication and retrieval of current logged in user
		register_hook('user', 'authenticate', 'user_authenticate_hook');
		register_hook('user', 'current', 'user_current_hook');
	}

	function user_boot()
	{
	    register_factory('filestore:user', 'user_filestore_factory');
	    register_factory('filestore:disk:user', 'user_filestore_factory');
	    register_factory('filestore:user:icon', 'user_filestore_factory');
	}

	register_event('system', 'boot', 'user_boot');
	register_event('system', 'init', 'user_init');

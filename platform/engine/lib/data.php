<?php
	/**
	 * @file
	 * Data handling objects.
	 * 
	 * Contains classes for handling data objects, relationships and data items as well
	 * as some very powerful query functions.
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2012
	 * @link http://platform.barcamptransparency.org/
	 */


	/**
	 * Barcamp Transparency Object.
	 * 
	 * This class represents the root class for all objects in the framework and provides
	 * a helpful wrapper for data manipulation.
	 *
	 * Most every object that you use will be a subclass of this object (or more accurately
	 * subclass of one of its subclasses) (Object, Relationship, Annotation etc.
	 *
	 * Each class has a hierachy, set by a call to setType() in its constructor. This, combined with a
	 * call to the parent constructor defines the type of the object and a unique identifier for this
	 * class for the database queries.
	 *
	 * @see Object
	 */
	abstract class BCTObject implements
		Iterator,	// Override foreach behaviour
		ArrayAccess // Override for array access
	{
		/**
		 * Attribute store for data.
		 */
		private $__attributes = array();
		
		/**
		 * Object type hierarchy.
		 * @see mysql.sql
		 */
		private $__type_hierarchy = array();
		
		/**
		 * Object type.
		 *
		 * @var string
		 */
		private $__type = null;

		/**
		 * Has metadata been modified.
		 */
		private $__attributes_modified = false;
		
		/**
		 * Public GUID for this data item.
		 *
		 * Having this here keeps the item separate from the data item's other parameters.
		 */
		private $guid = null;
		
		/**
		 * Created timestamp.
		 */
		private $created_ts = null;
		
		public function __construct() {}
		
		/**
		 * Return a GUID.
		 *
		 * @return int
		 */
		public function getGUID() { return $this->guid; }
		
		/**
		 * Return a unix timestamp for when the object was created, blank if not saved.
		 *
		 * @return int
		 */
		public function getCreatedTs() { return $this->created_ts; }
		
		/**
		 * Return the full type hierarchy of this object.
		 *
		 * @return string
		 */
		public function getTypeHierarchy() { return implode(':', $this->__type_hierarchy); }
		
		/**
		 * Add a type to the type hierarchy.
		 * 
		 * This is used to identify the object in the database. Call this method from your constructor, but
		 * remember to do so AFTER calling the parent constructor.
		 *
		 * @param string $type
		 */
		protected function setType($type) 
		{	
			$this->__type = $type; 
			$this->__type_hierarchy[] = $type; 
		}
		
		/**
		 * Class member get overloading
		 *
		 * @param string $name
		 * @return mixed
		 */
		public function &__get($name) {
			
			// Give read only access to some vars, TODO: do this generically.
			if ($name=='guid') 
				return $this->getGUID(); 
			if ($name=='created_ts')
				return $this->getCreatedTs();
				 
			if (isset($this->__attributes[$name]))
				return $this->__attributes[$name];

			return false;
		}
		
		/**
		 * Class member set overloading
		 *
		 * @param string $name
		 * @param mixed $value
		 * @return mixed
		 */
		public function __set($name, $value) {
			// Safety TODO: Handle more generically
			if ($name == 'guid')
				return; // won't set guid
				
			if ($name == 'created_ts') { // can set create time
				$this->created_ts = $value;
				
				return;
			}

			// Otherwise this is metadata
			$this->__attributes_modified = true;
			return $this->__attributes[$name] = $value; 
		}
		
		/**
		 * Supporting isset.
		 *
		 * @param string $name The name of the attribute or metadata.
		 * @return bool
		 */
		public function __isset($name) { return isset($this->__attributes[$name]); }
		
		/**
		 * Supporting unsetting of magic attributes.
		 *
		 * @param string $name The name of the attribute or metadata.
		 */
		public function __unset($name) { unset($this->__attributes[$name]); }
		
		/**
		 * Render this object.
		 * @param array $vars Optional variables to pass to the view.
		 * @return string|false The rendered view, or false.
		 */
		public function draw(array $vars = null)
		{
			if (!$vars) $vars = array();
			$vars['item'] = $this;
			
			$class = strtolower(get_class($this));
			
			$content = view("data/items/$class", $vars);
			if ($content) 
				return $content;
				
			$content = view('data/items/__default', $vars);
			if ($content) 
				return $content;
				
			return false;
		}
				
		/**
		 * Render an object using the views system (using default values).
		 * 
		 * This method uses the views system to render the data of an entity in a viewable form, 
		 * depending on context and view type.
		 */
		public function __toString() { return $this->draw(); }
		
		/**
		 * Return a copy of this object which is safe to export, that is that it has 
		 * security sensitive information stripped.
		 */
		public function safeExport()
		{
		    $export = new stdClass();

		    foreach ($this as $field => $value)
			$export->$field = $value;


		    $export->url = $this->getURL(); // Everything has a URL, so add it

		    return $export;
		}

		/**
		 * Get an object URL.
		 * 
		 * This provides a URL for that can display an entity in the system. It uses hooks to retrieve a URL
		 * and returns the default entity URL.
		 * 
		 * Override this Class's property:url hook to provide your own url handler.
		 * 
		 * Will only work for entities with a valid GUID (saved entities).
		 *
		 * @return url
		 */
		public function getUrl()
		{
			global $CONFIG;
			
			if (!$this->guid) 
				return false;
				
			return $this->__send_object_hook('property:url', "{$CONFIG->wwwroot}object/{$this->guid}");
		}
		
		/**
		 * Return an icon for the object.
		 * 
		 * Objects should override the 'property:icon' hook for the class, alternatively override
		 * getIcon directly.
		 *
		 * @param string $size 
		 */
		public function getIcon($size = 'small')
		{
			global $CONFIG;
			
			return $this->__send_object_hook('property:icon', '', array('size' => $size)); // TODO: Return default icon..? 
		}
		
		/**
		 * Can this object be edited.
		 * 
		 * Return true or false whether this object can be edited by a given user.
		 *
		 * @param User $user The user, if blank the currently logged in user is used. 
		 * @return bool By default this returns a permissive 'true' passed through a hook
		 */
		public function canEdit(User $user = null) { return $this->__send_object_hook('canedit', false, array('user' => $user ? $user : user_get_current())); }
		
		/**
		 * Can this object be viewed?
		 *
		 * Hook to determine whether this object can be viewed by the current user, this can be 
		 * used to determine whether to display a full or preview value.
		 * 
		 * Note, this is separate from a database level ACL (which may come later) which would
		 * prevent this value from being retrieved at all.
		 * 
		 * @param User $user The user, if blank the currently logged in user is used. 
		 * @return bool By default this returns a permissive 'true' passed through a hook
		 */
		public function canView(User $user = null) { return $this->__send_object_hook('canview', true, array('user' => $user ? $user : user_get_current())); }
		
		/**
		 * Save entity to database.
		 * @return GUID|false Returns the object's GUID on success, false on an error.
		 */
		public function save()
		{
			global $CONFIG;
			
			$guid = (int) $this->guid;
			$update = false;
			if ($guid)
				$update = true;
				
			// Test for type
			if (!$this->__type)
				throw new ClassException(sprintf(_echo('class:exception:missingtype'), get_class($this)));
				
			// Trigger pre event, block creation/update if pre returns false 
			// (doing this means we don't have to do any messy delete/reset 
			// stuff if an event action blocks)
			if (!$this->__send_object_event($update ? 'updating' : 'saving'))
				return false; 
				
			// Update or save?
			if ($guid > 0) 
			{
				
				// No need to update main entity atm since there are no fields to update
				
				
				
				// Delete metadata if we need to update
				if ($this->__attributes_modified) {
				    $result = db_delete_data(
					    array (
						    'table_col' => "{$CONFIG->dbprefix}bctobjects_metadata",
						    'where' => 'guid='.(int)$guid
					    )
				    );
				}
			}
			else
			{
				// If time not set then use the current time, this lets you 
				// set times which are in the past or future on creation.
				$time = $this->created_ts;
				if (!$time)
					$time = time();
				
				// Create new entity
				$guid = db_insert_data(
					array ( 
						'table_col' => "{$CONFIG->dbprefix}bctobjects",
						'set_values' => array (
							'type="'.$this->getTypeHierarchy().'"',
							'handling_class="'.get_class($this).'"',
							'created_ts="'.$time.'"'
						)
					)
				);
				
				// Set guid 
				$this->guid = $guid;
				
				// Timestamp
				$this->created_ts = $time;
			}
			
			// Save metadata if guid there
			if (($guid) && ($this->__attributes_modified))
			{
				// Set metadata
				foreach ($this->__attributes as $key => $value)
				{
					// Sanitise string
					$key = sanitise_string($key);
					
					// Convert non-array to array 
					if (!is_array($value))
						$value = array($value);
					
					// Save metadata
					foreach ($value as $meta)
					{
						db_insert_data(
							array ( 
								'table_col' => "{$CONFIG->dbprefix}bctobjects_metadata",
								'set_values' => array (
									'guid='.(int)$guid,
									'name="'.$key.'"',
									'value="'.sanitise_string($meta).'"'
								)
							)
						);
					}
				}

				$this->__attributes_modified = false;
			}
			
			// Saved, so trigger save event
			if ($guid)
				$this->__send_object_event($update ? 'updated' : 'saved');
			
			return $guid;
		}
		
		
		/**
		 * Construct a BCT Object from a row.
		 * 
		 * Construct an object from a row and load it's metadata. This method will function for all
		 * objects which use the base tables, if your class uses secondary tables to hold extra information
		 * you will need to extend this method.
		 *
		 * @param stdClass $row The database row as loaded from the database functions
		 */
		public function loadFromRow(stdClass $row)
		{
			global $CONFIG;
	
			// Sanity check, make sure we're not being called incorrectly
			if (
				(!($row instanceof stdClass)) ||
				(!$row->guid) ||
				(!$row->handling_class)
			)	
				throw new ClassException(_echo('class:exception:invalidrow'));
				
			// Populate from row
			$this->created_ts = $row->created_ts;
			$this->guid = $row->guid;
			
			// Populate from metadata
			$metadata = db_getdata(
				array (
					'select_expr' => '*',
					'table_col' => "{$CONFIG->dbprefix}bctobjects_metadata",
					'where' => 'guid=' . (int)$this->guid
				)
			);

			if ($metadata)
			{
				foreach ($metadata as $md)
				{
					$name = $md->name;
					$value = $md->value;
				
					// If already set, turn existing value into array
					if ($this->$name) 
					{
						if (!is_array($this->$name))
							$tmp = array($this->$name);
						else
							$tmp = $this->$name;
						$tmp[] = $value;
						$this->$name = $tmp;
					}	
					else
						$this->$name = $value;
					
				}
			}
			
			return false;
		}
		
		/**
		 * Delete a previously saved object and all of it's metadata. 
		 * 
		 * @return bool
		 */
		public function delete()
		{
			global $CONFIG;
			
			// Not saved, so can't delete
			if (!$this->guid)
				return false;
				
			// Trigger pre-delete
			if (!$this->__send_object_event('deleting'))
				return false; 
				
			// Delete data
			if (db_delete_data(
				array (
					'table_col' => "{$CONFIG->dbprefix}bctobjects",
					'where' => 'guid=' . (int)$this->guid
				)
			))
			{
				$meta = db_delete_data(
					array (
						'table_col' => "{$CONFIG->dbprefix}bctobjects_metadata",
						'where' => 'guid=' . (int)$this->guid
					)
				);	
					
				if ($meta) 
				{
					$this->__send_object_event('deleted');
					
					return true;
				}
			}
			
			return false;
		}
		
		/**
		 * Helper method for sending object related events.
		 * 
		 * @param string $event The event.
		 */
		protected function __send_object_event($event)
		{
			$event = sanitise_string($event);
			
			return trigger_event($this->getTypeHierarchy(), $event, array('object' => $this));
		}
		
		/**
		 * Helper method for triggering object related hooks.
		 *
		 * @param string $hook The hook.
		 * @param string $return_value Return value
		 * @param array $parameters Optional parameter.
		 */
		protected function __send_object_hook($hook, $return_value, array $parameters = NULL)
		{
			$hook = sanitise_string($hook);
			if (!is_array($parameters))
				$parameters = array();
			
			$parameters['object'] = $this;
			
			return trigger_hook($this->getTypeHierarchy(), $hook, $parameters, $return_value);
		}
		
		
		// ITERATOR INTERFACE //////////////////////////////////////////////////////////////
		/*
		 * This lets an entity's attributes be displayed using foreach as a normal array.
		 * Example: http://www.sitepoint.com/print/php5-standard-library
		 */
		
		private $__iterator_valid = FALSE; 
		
   		public function rewind() { $this->__iterator_valid = (FALSE !== reset($this->__attributes));  	}
   		public function current() 	{ return current($this->__attributes); 	}	
   		public function key() 	{ return key($this->__attributes); 	}	
   		public function next() { $this->__iterator_valid = (FALSE !== next($this->__attributes));  }
   		public function valid() { 	return $this->__iterator_valid;  }
   		
   		// ARRAY ACCESS INTERFACE //////////////////////////////////////////////////////////
		/*
		 * This lets an entity's attributes be accessed like an associative array.
		 * Example: http://www.sitepoint.com/print/php5-standard-library
		 */

		public function offsetSet($key, $value) { if ( array_key_exists($key, $this->__attributes) ) $this->__attributes[$key] = $value; } 
 		public function offsetGet($key) { if ( array_key_exists($key, $this->__attributes) ) return $this->__attributes[$key]; } 
 		public function offsetUnset($key) { if ( array_key_exists($key, $this->__attributes) ) $this->__attributes[$key] = ""; } 
 		public function offsetExists($offset) { return array_key_exists($offset, $this->__attributes);	} 
	}

	/**
	 * Datalist.
	 * 
	 * This class is a default container for lists of data items and can render
	 * itself.
	 *
	 * Subclass this to do more advanced stuff, although the default container should
	 * be good enough for most things as it contains support for pagination etc.
	 *
	 * This class also contains some PHP syntactic candy which lets you address it as
	 * an array!
	 */
	class BCTDatalist implements
		Iterator,	// Override foreach behaviour
		ArrayAccess // Override for array access
	{
		/// List of data items
		private $list = array();
		
		/// The page
		private $page = 0;
		
		/// Items per page
		private $perpage = 10;
		
		/// Total number of items
		private $total = 0;
		
		/**
		 * Create a datalist.
		 * @param array $items Datalist items
		 */
		public function __construct(array $items = NULL) 
		{
			$this->setItems($items);
		}
		
		/**
		 * Copy constructor.
		 * Copy datalist items from one list to another.
		 * @param BCTDatalist $list
		 */
		public function copyDatalist(BCTDatalist $list)
		{
			$this->setItems($list->getItems());
		}
		
		/**
		 * Render list with optional parameters.
		 * @param array $vars Array of parameters
		 * @return string|false
		 */
		public function draw(array $vars = null)
		{	
			if (!$vars)	$vars = array();
			$vars['list'] = $this;
			
			$class = strtolower(get_class($this));
			
			$content = view("data/lists/$class", $vars);
			if ($content) 
				return $content;
				
			$content = view('data/lists/__default', $vars);
			if ($content) 
				return $content;
				
			return false;
		}
		
		/**
		 * Render datalists using default values.
		 */
		public function __toString() { return $this->draw(); }
		
		/**
		 * Set data items.
		 * @param array $items The items.
		 */
		public function setItems(array $items) { $this->list = $items; }
		
		/**
		 * Get the items.
		 * @return array
		 */
		public function getItems() { return $this->list; }
		
		public function setPage($page) { $this->page = $page; }
		public function getPage() { return $this->page; }
		public function setItemsPerPage($perpage) { $this->perpage = $perpage; } 
		public function getItemsPerPage() { return $this->perpage; }
		public function setTotalItems($total) { $this->total = $total; }
		public function getTotalItems() { return $this->total; }
		public function getTotalPages() { return $this->perpage!=0 ? ceil($this->total/$this->perpage) : 1; /* If per page is zero, that means offsets and limits weren't set, so we only have one page */ }
		
		// ITERATOR INTERFACE //////////////////////////////////////////////////////////////
		/*
		 * This lets an entity's attributes be displayed using foreach as a normal array.
		 * Example: http://www.sitepoint.com/print/php5-standard-library
		 */
		
		private $__iterator_valid = FALSE; 
		
   		public function rewind() { $this->__iterator_valid = (FALSE !== reset($this->list));  	}
   		public function current() 	{ return current($this->list); 	}	
   		public function key() 	{ return key($this->list); 	}	
   		public function next() { $this->__iterator_valid = (FALSE !== next($this->list));  }
   		public function valid() { 	return $this->__iterator_valid;  }
   		
   		// ARRAY ACCESS INTERFACE //////////////////////////////////////////////////////////
		/*
		 * This lets an entity's attributes be accessed like an associative array.
		 * Example: http://www.sitepoint.com/print/php5-standard-library
		 */

		public function offsetSet($key, $value) { if ( array_key_exists($key, $this->list) ) $this->list[$key] = $value; } 
 		public function offsetGet($key) { if ( array_key_exists($key, $this->list) ) return $this->list[$key]; } 
 		public function offsetUnset($key) { if ( array_key_exists($key, $this->list) ) $this->list[$key] = ""; } 
 		public function offsetExists($offset) { return array_key_exists($offset, $this->list);	}
	}
	
	/**
	 * Relationship class.
	 * 
	 * A subclass of the BCTObject which can define relationships between other objects. It is of course
	 * itself an object which means that you are able to attach annotations and metadata to it.
	 * 
	 * You can of course attach a relationship on a relationship, and this is most likely a dumb thing to do,
	 * however the framework won't enforce any restrictions.
	 * 
	 * To define a relationship type you should subclass this and define the type of the relationship with
	 * the setType() method. This will let you use the standard search methods.
	 * 
	 * TODO: Although this is a BCT Object, should we consider overriding save and load methods to save the core
	 * data in a separate table.
	 */
	abstract class Relationship extends BCTObject 
	{
		/**
		 * Create a new Relationship.
		 */
		public function __construct()
		{
			parent::__construct();
			
			$this->setType('relationship');
		}	
		
		/**
		 * Construct a relationship between two objects.
		 * 
		 * @param BCTObject $object_one
		 * @param BCTObject $object_two
		 */
		public function link(BCTObject $object_one, BCTObject $object_two)
		{
			$guid_one = $object_one->getGUID();
			$guid_two = $object_two->getGUID();
			
			if ((!$guid_one) || (!$guid_two))
				throw new RelationshipException(_echo('relationship:create:missingguid'));
			
			$this->guid_one = $guid_one;
			$this->guid_two = $guid_two;
		}	
	}
	
	/**
	 * An annotation.
	 * 
	 * An annotation is a bct object which is only ever going to be providing information
	 * about another BCT object - for example a Comment.
	 * 
	 * While you could do this with a relationship, it makes sense to have its own parent class
	 * as it means you only need to create one object as opposed to two, and you are able to use
	 * the standard search functions in order to address them. 
	 * 
	 * If you want to annotate something with another main object (for example annotating a presentation
	 * with a blog post) then its probably best to create an Annotation stub for it (that way you can
	 * fully take advantage of the object rendering functions).
	 */
	abstract class Annotation extends BCTObject
	{
		/**
		 * Construct a new annotation.
		 *
		 * @param BCTObject $annotating The object you are annotating
		 */
		public function __construct()
		{
			parent::__construct();
			
			$this->setType('annotation');
			
		}
		
		/**
		 * Attach this annotation to an object
		 *
		 * @param BCTObject $annotating The object you are annotating
		 */
		public function annotate(BCTObject $annotating)
		{
			$guid = $annotating->getGUID();
			
			if (!$guid)
				throw new AnnotationException(_echo('annotation:create:missingguid'));
			
			$this->annotating_guid = $guid;
		}
	}
	
	/**
	 * A object in the system.
	 * 
	 * This class represents an object in the system (vs a relationship or an annotation. This class
	 * is primarily a stub but it provides the 'obj' subtype in the hierachy, letting you easily distinguish
	 * between relationships and annotations on a search.
	 * 
	 * Almost everything you will want to create will subclass this class.
	 *
	 * @section Example
	 * Here is an example of how you might create a Blog object.
	 * \code
	 *
	 * // Create our class
	 * class Blog extends BCTObject {
	 *
	 *	// Construct the object, note the importance of 1) Setting the type, 2) Calling the parent
	 *	// before doing so!
	 *	public function __construct()
	 *	{
	 *	    parent::__construct();
	 *
	 *	    $this->setType('blog');
	 *	}
	 * }
	 *
	 * ...
	 *
	 * // Now somewhere in your create code, probably in an action handler, we
	 * // create and save our new object.
	 *
	 * $post = new Blog();
	 * $post->title = $title;
	 * $post->description = $description;
	 * if ($guid = $post->save())
	 *	echo "Post saved and it's unique ID is $guid";
	 * echo
	 *	echo "Post could not be saved";
	 *
	 * \endcode
	 * @see register_action()
	 */
	abstract class Object extends BCTObject
	{
		/**
		 * Object constructor.
		 * 
		 * It is important that you call this method first thing in your object's constructor.
		 */
		public function __construct() 
		{ 
			parent::__construct(); 
			
			$this->setType('obj'); 
		}
	}
	
	/**
	 * Object factory callback.
	 * 
	 * Construct a BCT object out of a database row, pass this to the database functions as a callback
	 * on all queries which deal with BCT abstract objects.
	 *
	 * Note, this should not be confused with factories for things like datastores and caches as
	 * created by the functions in factory.php!
	 *
	 * @param stdClass $row The database row.
	 */
	function __object_row_factory($row)
	{
		// Sanity check
		if (
			(!($row instanceof stdClass)) ||
			(!$row->guid) ||
			(!$row->handling_class)
		)	
			throw new ClassException(_echo('class:exception:invalidrow'));  
			
		$class = $row->handling_class;
		
		if (class_exists($class))
		{
			$object = new $class();
			
			if (!($object instanceof BCTObject))
				throw new ClassException(_echo('class:exception:invalidbctobject'));
				
			// Populate the new object with data from the already loaded row
			$object->loadFromRow($row);
			
			return $object;
		}
		else
			log_echo(sprintf(_echo('class:error:classnotfound'), $class), 'ERROR');  
			
		return false;
	}
	
	/**
	 * Construct a new object from GUID.
	 *
	 * @param int $guid The unique identifier of an object as returned by its save() method.
	 * @return BCTObject|false
	 */
	function getObject($guid)
	{
		global $CONFIG;
		
		$obj = db_getdata_row(
			array(
				'select_expr' => '*',
				'table_col' => "{$CONFIG->dbprefix}bctobjects",
				'where' => 'guid=' . (int)$guid
			), '__object_row_factory'
		);
		
		if ($obj)
			return $obj;
			
		return false;
	}
	
	/**
	 * Get an object by a URL.
	 * 
	 * Attempt to retrieve an object by a URL, you can provide a way to match custom
	 * objects to URL by using the hook object/getbyurl, otherwise the function assumes
	 * the default export url.
	 *
	 * @section Rationale
	 * All objects in the BCT system have a getURL() method to return an address by which the object
	 * can be accessed (and by specifying a view=xxxx parameter on this url can be accessed in a variety
	 * of different formats). This function allows this process to go full circle and take a previously exported
	 * address of an object and return the object it references.
	 * 
	 * @param string $url
	 * @return BCTObject|false
	 */
	function getObjectByUrl($url)
	{
		global $CONFIG;
		
		// Try to get an object by a url
		$result = trigger_hook('object', 'getbyurl', array('url' => $url) );
		if ($result instanceof BCTObject) return $result;
		
		// No luck, so try matching against default url
		$match = array();
		preg_match("/".str_replace('/','\/', $CONFIG->wwwroot)."object\/([0-9]*)/", $url, $match);
		$result = getObject((int)$match[1]);
		if ($result) return $result;
		
		return false;
	}
	
	/**
	 * Construct an array of objects based on a number of parameters.
	 *
	 * @param string|array $type The type, based on the hierachy, use '%' as a wildcard and ":" as delimiter. 
	 * 	Eg. 
	 * 		Use 'news' to return only news objects.
	 * 		'news:%' to return news items and any subclasses of news items
	 * 		'news:foo' to return only objects of the foo subclass of news. 
	 * This parameter may also be an array of such values, if an array is provided the values are ORed together
	 * in order to retrieve the desired set.
	 * @param array $nameValuePairs Name / value list of metadata serving as search parameters.
	 *								 Additionally, you may define an entry as follows:
	 * 									'field' => array(
	 * 													operation (<,>,<=,>=,=,like,in,not) => comparison value
	 * 													...
	 * 												)
	 * 	
	 * 									eg. 'number' => array('>' => 5, '<' => 10)
	 * @param array $queryParameters Parameters to pass to the query, e.g. limit and offset, or ordering.
	 * 					Available parameters:
	 * 						'limit' => int
	 * 						'offset' => int
	 * 						'orderby' => string - field to order results by
	 * 						'order	=> string ASC or DESC
	 * 						'datalist' => Optionally specify a data list class to return results with.
	 * 						'no_count' => By default, the function will attempt to return how many possible results could
	 * 									  have been returned by this call by making a secondary count call. This is so that
	 * 									  the datalist can render navigation controls. Set this value to prevent this from happening.
	 * @return BCTDatalist|false
	 */
	function getObjects($type = null, array $nameValuePairs = null, array $queryParameters = null)
	{
		global $CONFIG;
		
		$where = array();
		$joins = array();
		$sort_field = false;
		
		// If we have specified a sort field AND table, then use this - otherwise we attempt autodetection
		// against metadata
		if ((isset($queryParameters['order'])) && (strpos($queryParameters['order'],'.')!==false))
			$sort_field = $queryParameters['order'];
		
		
		// Find
		if ($type) 
		{
			$type = sanitise_string($type);
			
			if (!is_array($type))
				$type = array($type);
				
			$type_array = array();
			foreach ($type as $set)
				$type_array[] = "o.type like '$set'";
			
			$where[] = implode(' OR ', $type_array);
		}
		
		// Parameters
		if ($nameValuePairs)
		{
			$n = 0;
			
			foreach ($nameValuePairs as $name => $value)
			{
				if (!is_array($value))
						$value = array('=' => $value);
					
				foreach ($value as $operator => $value) 
				{
					$name = trim(sanitise_string($name));
					$value = trim(sanitise_string($value));
					if ($operator != 'in') 
						$value = "'$value'";
					else
					{
						// in
						if (is_array($value))
							$value = implode("','", $value);
						$value = "('$value')";
					}
					
					$joins[] = "{$CONFIG->dbprefix}bctobjects_metadata m$n ON m$n.guid=o.guid";
					$where[] = "(m$n.name='$name' and m$n.value $operator $value)";
						
					$n++;
				}
			}
		}
		
		// Ordering
		$orderby = sanitise_string($queryParameters['orderby']);
		if ($orderby)
		{
			// Is it a main field
			if (in_array($orderby, array('guid','type','handling_class','created_ts')))
				$sort_field = "o.$orderby ";
			else
			{
				$joins[] = "{$CONFIG->dbprefix}bctobjects_metadata sort ON sort.guid=o.guid";
				$where[] = "sort.name='$orderby'";
				$sort_field = "sort.value";
			}
		}
		
		// Get data
		$query = array(
				'select_expr' => 'o.*',
				'table_col' => "{$CONFIG->dbprefix}bctobjects o",
				'where' => $where,
				'joins' => $joins,
			);
			
		if (isset($queryParameters['limit'])) $query['limit'] = (int)$queryParameters['limit'];
		if (isset($queryParameters['offset'])) $query['offset'] = (int)$queryParameters['offset'];
		
		// Sorting 
		if (!$sort_field)
		{
			// If not sort field then use default
			$sort_field = 'o.created_ts';
		}
		
		if ($sort_field) $query['orderby'] = sanitise_string($sort_field);
		
		// Sort order
		if (isset($queryParameters['order'])) $query['order'] = sanitise_string($queryParameters['order']);
		
		
		$return = db_getdata($query, '__object_row_factory');
		
		if ($return) {
			$list = false;
			
			// Construct datalist
			if (isset($queryParameters['datalist']))
			{
				$datalist = $queryParameters['datalist'];
				if (class_exists($datalist))
					$list = new $datalist($return);
			}
			
			if (!$list) $list = new BCTDatalist($return);
			if ($list) {
				
				// Autolist
				global $__PAGE_CONTAINS_LIST;
				$__PAGE_CONTAINS_LIST = true;
				
				// Now, lets set some variables needed for pagination
				$num_items = count($return);
				$page = $query['offset'] > 0 ? ceil($query['offset'] / $query['limit'])+1 : 1;
				$numperpage = isset($query['limit']) ? $query['limit'] : 0;
				$total = (($page == 0) && ($num_items<$numperpage)) ? $num_items : ($queryParameters['no_count'] ? $num_items : getObjectsCount($type, $nameValuePairs, $queryParameters));
			
				$list->setPage($page);
				$list->setItemsPerPage($numperpage);
				$list->setTotalItems($total);
				
				// Return list
				return $list;
			}
		}

		return false;
	}
	
	/**
	 * Count the number of objects returned by a query.
	 *
	 * @see getObjects()
	 * @param string|array $type The type.
	 * @param array $nameValuePairs Name / value list of metadata serving as search parameters.
	 * @param array $queryParameters Parameters to pass to the query.
	 * @return int|false
	 */
	function getObjectsCount($type = "", array $nameValuePairs = null, array $queryParameters = null)
	{
		global $CONFIG;
		
		$where = array();
		$joins = array();
		
		// Find
		if ($type) 
		{
			$type = sanitise_string($type);
			
			if (!is_array($type))
				$type = array($type);
				
			$type_array = array();
			foreach ($type as $set)
				$type_array[] = "o.type like '$set'";
			
			$where[] = implode(' OR ', $type_array);
		}
		
		// Parameters
		if ($nameValuePairs)
		{
			$n = 0;
			
			foreach ($nameValuePairs as $name => $value)
			{
				if (!is_array($value))
						$value = array('=' => $value);
					
				foreach ($value as $operator => $value) 
				{
					$name = trim(sanitise_string($name));
					$value = trim(sanitise_string($value));
					
					$joins[] = "{$CONFIG->dbprefix}bctobjects_metadata m$n ON m$n.guid=o.guid";
					$where[] = "(m$n.name='$name' and m$n.value $operator '$value')";
						
					$n++;
				}
			}
		}
		
		$query = array(
			'select_expr' => '*',
			'table_col' => "{$CONFIG->dbprefix}bctobjects o",
			'where' => $where,
			'joins' => $joins,
			'select_func' => 'COUNT',
		);
	
		$return = db_getdata_row($query);
		
		if ($return) 
			return $return->select_func_result;

		return false;
		
	}
	
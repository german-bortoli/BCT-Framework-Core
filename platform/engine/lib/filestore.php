<?php
    /**
     * @file
     * Filestore.
     *
     * Tools for manipulating files in a consistent way, and providing an interface
     * to provide different types of file storage device in the future (for example Amazon S3) etc.
     *
     * @package core
     * @license The MIT License (see LICENCE.txt), other licenses available.
     * @author Marcus Povey <marcus@marcus-povey.co.uk>
     * @copyright Marcus Povey 2009-2012
     * @link http://platform.barcamptransparency.org/
     */

     /**
      * Define the interface to a filestore, assumes that the underlaying
      * storage device is posix compliant at least to some extent.
      */
     abstract class Filestore
     {
	/**
	 * Open a file.
	 * @param string $filename The filename
	 * @param string $mode Write mode
	 * @return File handle|false
	 */
	public abstract function open($filename, $mode = 'wb');

	/**
	 * Close a given file pointer.
	 * @param pointer $handle File pointer
	 */
	public abstract function close($handle);

	/**
	 * Read bytes from the file pointer.
	 * @param handle $handle
	 * @param int $length
	 * @return string
	 */
	public abstract function read($handle, $length = 8192);

	/**
	 * Write bytes to the handle.
	 * @param handle $handle
	 * @param string $string Data to write
	 * @param int $length How much of $string to write (optional).
	 */
	public abstract function write($handle, $string, $length = null);

	/**
	 * Remove a file.
	 * @param string $filename Path and filename relative to the given root.
	 */
	public abstract function delete($filename);

	/**
	 * Does a file exist?
	 * @param string $filename Path and filename relative to the given root.
	 */
	public abstract function exists($filename);

	/**
	 * Get size of a given file in bytes.
	 * @param string $filename Path and filename relative to the given root.
	 */
	public abstract function size($filename);

	/**
	 * Read the entire contents of the given file.
	 * @param string $filename filename
	 */
	public abstract function readAll($filename);

	/**
	 * Write all of a buffer to a given file.
	 * @param string $filename
	 * @param string $data Data to write
	 */
	public abstract function writeAll($filename, $data);

	/**
	 * Create a directory relative to the current data root.
	 */
	public abstract function makePath($path);

	/**
	 * Import an already existing file.
	 *
	 * Imports a file already present on the filesystem into the datastore (such as an uploaded file - which is
	 * likely to be much more efficient than reading and writing the file within the script.)
	 * @param string $filename Filename to import
	 * @param string $import_as Import as the following directory and filename under the datastore root.
	 */
	public abstract function importFile($filename, $import_as);
     }

     /**
      * Create a physical filestore on a disk.
      *
      * By default this creates a filestore relative to $CONFIG->dataroot.
      */
     class DiskFilestore extends Filestore
     {
	/// Base of file system, defaults to $CONFIG->dataroot
	private $dataroot;

	public function __construct() { $this->setDataRoot(); }

	/**
	 * Set the data root for this filestore.
	 *
	 * @param string $root The dataroot, if none provided the default $CONFIG->dataroot is used.
	 * @return bool
	 */
	public function setDataRoot($root = '')
	{
	    global $CONFIG;

	    if (!$root) $root = $CONFIG->dataroot;

	    $this->dataroot = '/' . trim($root, ' /\\.') . '/';


	    // TODO : Sanitise escape and slash

	    return @mkdir($this->dataroot, 0777, true);
	}

	/**
	 * Get the current data root.
	 * @return string
	 */
	public function getDataRoot() { return $this->dataroot; }

	public function makePath($path) { return @mkdir($this->getDataRoot() . trim($path, ' /\\'), 0777, true); }

	public function open($filename, $mode = 'rb')
	{
	    $bits = pathinfo($filename);
	    $path = $this->sanitisePath($bits['dirname']) . '/';

	    $this->makePath($path);

	    $filename = $this->sanitisePath($filename);

	    return fopen($this->getDataRoot() . $path . $filename, $mode);
	}

	public function close($handle) { return fclose($handle); }

	public function read($handle, $length = 8192) { return fread($handle, $length); }

	public function write($handle, $string, $length = null) { return fwrite($handle, $string, $length); }

	public function delete($filename)
	{
	    $filename = $this->sanitisePath($filename);

	    return unlink($this->getDataRoot() . $filename);
	}

	public function exists($filename)
	{
	    $filename = $this->sanitisePath($filename);

	    return file_exists($this->getDataRoot() . $filename);
	}

	public function size($filename)
	{
	    $filename = $this->sanitisePath($filename);

	    return filesize($this->getDataRoot() . $filename);
	}

	public function readAll($filename)
	{
	    $filename = $this->sanitisePath($filename);

	    return file_get_contents($this->getDataRoot() . $filename);
	}

	public function writeAll($filename, $data)
	{
	    $bits = pathinfo($filename);
	    $path = $this->sanitisePath($bits['dirname']) . '/';

	    $this->makePath($path);
	    
	    $filename = $this->sanitisePath($filename);

	    return file_put_contents($this->getDataRoot() . $filename, $data);
	}

	public function importFile($filename, $import_as)
	{
	    $bits = pathinfo($import_as);
	    $path = $this->sanitisePath($bits['dirname']) . '/';

	    $this->makePath($path);

	    $import_as = $this->sanitisePath($import_as);
	    return copy($filename, $this->getDataRoot() . $import_as);
	}

	/**
	 * Sanitise a path.
	 *
	 * TODO: Make more comprehensive.
	 */
	protected function sanitisePath($path) 
	{
	    $path = str_replace('../', '', $path); // Remove '../' paths
	    $path = explode(';', $path); // Remove concatinated command strings
	    return trim($path[0], ' /\\.');
	}
    }

     /**
      * Filestore factory.
      *
      * Registers some factories, currently 'filestore' and 'filestore:disk' which both by default
      * create a new DiskFilestore object.
      */
     function filestore_factory($class, $hook, $parameters, $return_value)
     {	
	 // If we already have a filestore for this, don't create a new one.
	 if ($return_value) 
	    return $return_value;
		
	 // Otherwise we see if we can create a filestore
	 switch ($hook)
	 {
	    case 'filestore' : // If we ask for a 'filestore' we just get this
	    case 'filestore:disk' : 
		return new DiskFilestore();
	 }
     }

     /**
      * Initialise the filestore subsystem.
      *
      */
     function filestore_boot()
     {
	 register_factory('filestore', 'filestore_factory');
	 register_factory('filestore:disk', 'filestore_factory');
     }

     register_event('system', 'boot', 'filestore_boot');
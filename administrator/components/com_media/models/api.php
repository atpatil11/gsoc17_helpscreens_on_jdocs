<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseModel;

/**
 * Api Model
 *
 * @since  __DEPLOY_VERSION__
 */
class MediaModelApi extends BaseModel
{
	/**
	 * The local file adapter to work with.
	 *
	 * @var MediaFileAdapterInterface
	 */
	protected $adapter = null;
	/**
	 * Constructor
	 *
	 * @param   array  $config  An array of configuration options (name, state, dbo, table_path, ignore_request).
	 *
	 * @since   3.0
	 * @throws  \Exception
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		if (!isset($config['fileadapter']))
		{
			// Import Local file system plugin
			JPluginHelper::importPlugin('filesystem');

			$app = JFactory::getApplication();

			$results = $app->triggerEvent('onFileSystemGetAdapters');

			if ($results != null)
			{
				$config['fileadapter'] = $results[0];
			}
		}

		if (isset($config['fileadapter']))
		{
			$this->adapter = $config['fileadapter'];
		}
	}

	/**
	 * Returns the requested file or folder information. More information
	 * can be found in MediaFileAdapterInterface::getFile().
	 *
	 * @param   string  $path  The path to the file or folder
	 *
	 * @return  stdClass[]
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception
	 * @see     MediaFileAdapterInterface::getFile()
	 */
	public function getFile($path = '/')
	{
		return $this->adapter->getFile($path);
	}

	/**
	 * Returns the folders and files for the given path. More information
	 * can be found in MediaFileAdapterInterface::getFiles().
	 *
	 * @param   string  $path    The folder
	 * @param   string  $filter  The filter
	 *
	 * @return  stdClass[]
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception
	 * @see     MediaFileAdapterInterface::getFile()
	 */
	public function getFiles($path = '/', $filter = '')
	{
		if (!$this->adapter)
		{
			return array();
		}

		return $this->adapter->getFiles($path, $filter);
	}

	/**
	 * Creates a folder with the given name in the given path. More information
	 * can be found in MediaFileAdapterInterface::createFolder().
	 *
	 * @param   string  $name  The name
	 * @param   string  $path  The folder
	 *
	 * @return  string  The new file name
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception
	 * @see     MediaFileAdapterInterface::createFolder()
	 */
	public function createFolder($name, $path)
	{
		$name = $this->getSafeName($name);

		$this->adapter->createFolder($name, $path);

		return $name;
	}

	/**
	 * Creates a file with the given name in the given path with the data. More information
	 * can be found in MediaFileAdapterInterface::createFile().
	 *
	 * @param   string  $name  The name
	 * @param   string  $path  The folder
	 * @param   binary  $data  The data
	 *
	 * @return  string  The new file name
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception
	 * @see     MediaFileAdapterInterface::createFile()
	 */
	public function createFile($name, $path, $data)
	{
		$name = $this->getSafeName($name);

		$this->adapter->createFile($name, $path, $data);

		return $name;
	}

	/**
	 * Updates the file with the given name in the given path with the data. More information
	 * can be found in MediaFileAdapterInterface::updateFile().
	 *
	 * @param   string  $name  The name
	 * @param   string  $path  The folder
	 * @param   binary  $data  The data
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception
	 * @see     MediaFileAdapterInterface::updateFile()
	 */
	public function updateFile($name, $path, $data)
	{
		$this->adapter->updateFile($name, $path, $data);
	}

	/**
	 * Deletes the folder or file of the given path. More information
	 * can be found in MediaFileAdapterInterface::delete().
	 *
	 * @param   string  $path  The path to the file or folder
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception
	 * @see     MediaFileAdapterInterface::delete()
	 */
	public function delete($path)
	{
		$this->adapter->delete($path);
	}

	/**
	 * Creates a safe file name for the given name.
	 *
	 * @param   string  $name  The filename
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception
	 */
	private function getSafeName($name)
	{
		// Make the filename safe
		$name = JFile::makeSafe($name);

		// Transform filename to punycode
		$name = JStringPunycode::toPunycode($name);

		$extension = JFile::getExt($name);

		if ($extension)
		{
			$extension = '.' . strtolower($extension);
		}

		// Transform filename to punycode, then neglect other than non-alphanumeric characters & underscores.
		// Also transform extension to lowercase.
		$nameWithoutExtension = substr($name, 0, strlen($name) - strlen($extension));
		$name = preg_replace(array("/[\\s]/", '/[^a-zA-Z0-9_]/'), array('_', ''), $nameWithoutExtension) . $extension;

		return $name;
	}

	/**
	 * Copies file or folder from source path to destination path
	 * If forced, existing files/folders would be overwritten
	 *
	 * @param   string  $sourcePath       Source path of the file or folder (relative)
	 * @param   string  $destinationPath  Destination path(relative)
	 * @param   bool    $force            Force to overwrite
	 *
	 * @return void
	 *
	 * @since __DEPLOY_VERSION__
	 * @throws  Exception
	 */
	public function copy($sourcePath, $destinationPath, $force = false)
	{
		$this->adapter->copy($sourcePath, $destinationPath, $force);
	}

	/**
	 * Moves file or folder from source path to destination path
	 * If forced, existing files/folders would be overwritten
	 *
	 * @param   string  $sourcePath       Source path of the file or folder (relative)
	 * @param   string  $destinationPath  Destination path(relative)
	 * @param   bool    $force            Force to overwrite
	 *
	 * @return void
	 *
	 * @since __DEPLOY_VERSION__
	 * @throws  Exception
	 */
	public function move($sourcePath, $destinationPath, $force = false)
	{
		 $this->adapter->move($sourcePath, $destinationPath, $force);
	}
}

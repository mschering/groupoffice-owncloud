<?php

namespace OC\Files\Storage;

class Groupoffice extends \OC\Files\Storage\Local {

	protected $groupofficepath = '';

	public function __construct($arguments) {
		$this->datadir = $arguments['datadir'];
		if (substr($this->datadir, -1) !== '/') {
			$this->datadir .= '/';
		}

		$this->groupofficepath = $arguments['groupofficepath'];
	}

	public function mkdir($path) {
		$tmp_path = dirname($path);
		if ($tmp_path == '.')
			$tmp_path = '';

		if ($this->isCreatable($tmp_path)) {
			$fullpath = dirname($this->groupofficepath.'/'.$path);

			$folder = \GO_Files_Model_Folder::model()->findByPath($fullpath);
			$folder->addFolder(basename($path));
			return true;

		}

		return false;
	}
	public function rmdir($path) {
		if ($this->isDeletable($path)) {
			$fullpath = $this->groupofficepath.'/'.$path;

			$folder = \GO_Files_Model_Folder::model()->findByPath($fullpath);
			$folder->delete();

			return true;
		} else
			return false;
	}
	public function is_dir($path) {
		if ($this->datadir == '/Groupoffice' || $this->datadir == '/Groupoffice/') {
			return true;
		} else {
			if (substr($path, -1) == '/') {
				$path = substr($path, 0, -1);
			}
			return is_dir($this->datadir . $path);
		}
	}

	public function getMimeType($path) {
		if ($this->isReadable($path)) {
			if ($path == '' || $path == '/') {
				return 'httpd/unix-directory';
			} else {
				return \OC_Helper::getMimeType($this->datadir . $path);
			}
		} else {
			return false;
		}
	}

	public function is_file($path) {
		if ($this->datadir == '/Groupoffice' || $this->datadir == '/Groupoffice/') {
			return false;
		} else {
			return is_file($this->datadir . $path);
		}
	}

	public function opendir($path) {
		if ($this->datadir == '/Groupoffice' || $this->datadir == '/Groupoffice/') {
			$files = array();
			\OC\Files\Stream\Dir::register('groupoffice', $files);
			return opendir('fakedir://groupoffice');
		} else {
			return opendir($this->datadir . $path);
		}
	}

	public function stat($path) {
		if ($this->datadir == '/Groupoffice' || $this->datadir == '/Groupoffice/') {
			$stat['size'] = 0;
			$stat['mtime'] = 0;
			return $stat;
		} else {
			$fullPath = $this->datadir . $path;
			$statResult = stat($fullPath);

			if ($statResult['size'] < 0) {
				$size = self::getFileSizeFromOS($fullPath);
				$statResult['size'] = $size;
				$statResult[7] = $size;
			}
			return $statResult;
		}
	}

	public function copy($path1, $path2) {
		if ($this->isCreatable(dirname($path2))) {
			if ($this->is_dir($path2)) {
				if (!$this->file_exists($path2)) {
					$this->mkdir($path2);
				}
				$source = substr($path1, strrpos($path1, '/') + 1);
				$path2 .= $source;
			}
			return copy($this->datadir . $path1, $this->datadir . $path2);
		}
	}

	public function filemtime($path) {
		if ($this->datadir == '/Groupoffice' || $this->datadir == '/Groupoffice/') {
			return 0;
		} else {
			return filemtime($this->datadir . $path);
		}
	}

	public function filetype($path) {
		if ($this->datadir == '/Groupoffice' || $this->datadir == '/Groupoffice/') {
			return 'dir';
		} else {
			$filetype = filetype($this->datadir . $path);
			if ($filetype == 'link') {
				$filetype = filetype(realpath($this->datadir . $path));
			}
			return $filetype;
		}
	}

	public function file_exists($path) {
		if ($this->datadir == '/Groupoffice' || $this->datadir == '/Groupoffice/') {
			return true;
		} else {
			return file_exists($this->datadir . $path);
		}
	}
	public function unlink($path) {
		if ($this->isDeletable($path))
		{
			return $this->delTree($path);
		}

		return false;
	}
	private function delTree($dir) {
		$dirRelative = $dir;
		$dir = $this->datadir . $dir;
		if (!file_exists($dir)) return true;
		if (!is_dir($dir) || is_link($dir)) return unlink($dir);
		foreach (scandir($dir) as $item) {
			if ($item == '.' || $item == '..') continue;
			if (is_file($dir . '/' . $item)) {
				if (unlink($dir . '/' . $item)) {
				}
			} elseif (is_dir($dir . '/' . $item)) {
				if (!$this->delTree($dirRelative . "/" . $item)) {
					return false;
				};
			}
		}
		if ($return = rmdir($dir)) {
		}
		return $return;
	}
	public function file_get_contents($path) {
		if ($this->datadir == '/Groupoffice' || $this->datadir == '/Groupoffice/') {
			return false;
		} else {
			return file_get_contents($this->datadir . $path);
		}
	}

	public function file_put_contents($path, $data) {
		if ($this->datadir == '/Groupoffice' || $this->datadir == '/Groupoffice/') {
			return false;
		} else {
			if (($this->file_exists($path) && !$this->isUpdatable($path))
				|| ($this->is_dir($path) && !$this->isCreatable($path)))
				return false;
			else
				return file_put_contents($this->datadir . $path, $data);
		}
	}

	public function fopen($path, $mode) {
		if ($this->datadir == '/Groupoffice' || $this->datadir == '/Groupoffice/') {
			return false;
		} else {
			if ($return = fopen($this->datadir . $path, $mode)) {
				switch ($mode) {
					case 'r':
						break;
					case 'r+':
					case 'w+':
					case 'x+':
					case 'a+':
						break;
					case 'w':
					case 'x':
					case 'a':
						break;
				}
			}
			return $return;
		}
	}

	public static function setup($options) {
		if (\OCP\User::isLoggedIn()) {
			$user_dir = $options['user_dir'];
			\OC\Files\Filesystem::mount('\OC\Files\Storage\Groupoffice',
				array('datadir' => '/Groupoffice', 'groupofficepath' => ''),
				$user_dir . '/Groupoffice/');

			\OC\Files\Storage\Groupoffice::getShares($options);
		}
	}

	private static function getShares($options) {
		$shares = \GO_Files_Model_Folder::model()->getTopLevelShares(\GO_Base_Db_FindParams::newInstance()->limit(100));

		foreach($shares as $folder){
			\OC\Files\Filesystem::mount('\OC\Files\Storage\Groupoffice',
				array('datadir' => \GO::config()->file_storage_path.$folder->path, 'groupofficepath' => $folder->path),
				$options['user_dir'] . '/Groupoffice/'.$folder->name);
		}
	}

	public function free_space($path) {
		if ($this->datadir == '/Groupoffice' || $this->datadir == '/Groupoffice/') {
			return -2;
		} else {
			$space = @disk_free_space($this->datadir . $path);
			if ($space === false) {
				return -2;
			}
			return $space;
		}
	}

	/*
	*
	*
	*
	*/
	public function isReadable($path) {
		if ($this->datadir.$path == '/Groupoffice' || $this->datadir.$path == '/Groupoffice/') {
			return true;
		} else {
			$fullpath = $this->groupofficepath.'/'.$path;
	
			if ($this->is_file($path))
				$fullpath = dirname($fullpath);
	
			$folder = \GO_Files_Model_Folder::model()->findByPath($fullpath);
	
			if ($folder != '') {
				if ($folder->checkPermissionLevel(\GO_Base_Model_Acl::READ_PERMISSION))
				{
					return true;
				} else {
					return false;
				}
			}
	
			return false;
		}
	}
	public function isUpdatable($path) {
		$fullpath = $this->groupofficepath.'/'.$path;

		if ($this->is_file($path))
			$fullpath = dirname($fullpath);

		$folder = \GO_Files_Model_Folder::model()->findByPath($fullpath);

		if ($folder != '') {
			if ($folder->checkPermissionLevel(\GO_Base_Model_Acl::WRITE_PERMISSION))
			{
				return true;
			} else {
				return false;
			}
		}

		return false;
	}
	public function isCreatable($path) {
		$fullpath = $this->groupofficepath.'/'.$path;

		if ($this->is_file($path))
			$fullpath = dirname($fullpath);

		$folder = \GO_Files_Model_Folder::model()->findByPath($fullpath);

		if ($folder != '') {
			if ($folder->checkPermissionLevel(\GO_Base_Model_Acl::CREATE_PERMISSION))
			{
				return true;
			} else {
				return false;
			}
		}

		return false;
	}
	public function isDeletable($path) {
		$fullpath = $this->groupofficepath.'/'.$path;

		if ($this->is_file($path))
			$fullpath = dirname($fullpath);

		$folder = \GO_Files_Model_Folder::model()->findByPath($fullpath);

		if ($folder != '') {
			if ($folder->checkPermissionLevel(\GO_Base_Model_Acl::DELETE_PERMISSION))
			{
				return true;
			} else {
				return false;
			}
		}

		return false;
	}
	public function isSharable($path) {
		return false;
	}
}

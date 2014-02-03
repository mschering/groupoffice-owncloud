<?php

namespace OC\Files\Storage;

class Groupoffice extends \OC\Files\Storage\Common
{

    private $groupoffice_data;
    private $groupoffice_shares = array();
    private $id;

    public function __construct($arguments)
    {
        if (isset($arguments['user'])) {
            $this->id = 'groupoffice::' . $arguments['user'] . '/';
            $this->groupoffice_data = \GO::config()->file_storage_path;

            $this->groupoffice_shares['ownFolder'] = 'users/' . $arguments['user'];
            $shares = \GO_Files_Model_Folder::model()->getTopLevelShares(\GO_Base_Db_FindParams::newInstance()->limit(100));

            foreach ($shares as $folder) {
                $this->groupoffice_shares[$folder->name] = $folder->path;
            }
        } else {
            throw new \Exception('Creating \OC\Files\Storage\Groupoffice storage failed');
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function isSharable($path)
    {
        return false;
    }

    public function isReadable($path)
    {
        \OCP\Util::writeLog('groupoffice', 'isreadable: ' . $path, \OCP\Util::DEBUG);
        if ($path == '' || $path == '/') {
            return true;
        } else {
            $fullpath = $this->get_real_path($path);

            if ($this->is_file($path))
                $fullpath = dirname($fullpath);

            $folder = \GO_Files_Model_Folder::model()->findByPath($fullpath);

            if ($folder != '') {
                if ($folder->checkPermissionLevel(\GO_Base_Model_Acl::READ_PERMISSION)) {
                    return true;
                } else {
                    return false;
                }
            }

            return false;
        }
    }

    public function opendir($path)
    {
        \OCP\Util::writeLog('groupoffice', 'opendir: ' . $path, \OCP\Util::DEBUG);
        if ($path == '' || $path == '/') {
            $files = array();
            $files[] = 'ownFolder';

            $shares = \GO_Files_Model_Folder::model()->getTopLevelShares(\GO_Base_Db_FindParams::newInstance()->limit(100));

            foreach ($shares as $folder) {
                $files[] = $folder->name;
            }

            \OC\Files\Stream\Dir::register('groupoffice' . $path, $files);
            return opendir('fakedir://groupoffice' . $path);
        } else {
            return opendir($this->groupoffice_data . $this->get_real_path($path));
        }
    }

    private function get_real_path($path)
    {
        $tmp = explode("/", $path);
        $basefolder = $tmp[0];
        unset($tmp[0]);
        $realfolder = implode("/", $tmp);

        if (array_key_exists($basefolder, $this->groupoffice_shares)) {
            $realpath = $this->groupoffice_shares[$basefolder] . '/' . $realfolder;
            return $realpath;
        } else
            return $path;
    }

    public function is_dir($path)
    {
        \OCP\Util::writeLog('groupoffice', 'isdir: ' . $path, \OCP\Util::DEBUG);
        if ($path == '' || $path == '/') {
            return true;
        } else {
            if (substr($path, -1) == '/') {
                $path = substr($path, 0, -1);
            }
            return is_dir($this->groupoffice_data . $this->get_real_path($path));
        }
    }

    public function is_file($path)
    {
        \OCP\Util::writeLog('groupoffice', 'isfile: ' . $path, \OCP\Util::DEBUG);
        if ($path == '' || $path == '/') {
            return false;
        } else {
            return is_file($this->groupoffice_data . $this->get_real_path($path));
        }
    }

    public function getMimeType($path)
    {
        if ($this->isReadable($path)) {
            if ($path == '' || $path == '/') {
                return 'httpd/unix-directory';
            } else {
                return \OC_Helper::getMimeType($this->groupoffice_data . $this->get_real_path($path));
            }
        } else {
            return false;
        }
    }

    public function filetype($path)
    {
        if ($path == '' || $path == '/') {
            return 'dir';
        } else {
            $realpath = $this->groupoffice_data . $this->get_real_path($path);
            $filetype = filetype($realpath);
            if ($filetype == 'link') {
                $filetype = filetype(realpath($realpath));
            }
            return $filetype;
        }
    }

    public function stat($path)
    {
        if ($path == '' || $path == '/') {
            $stat['size'] = 0;
            $stat['ctime'] = 0;
            $stat['atime'] = 0;
            $stat['mtime'] = 0;
            return $stat;
        } else {
            $fullPath = $this->groupoffice_data . $this->get_real_path($path);
            $statResult = stat($fullPath);

            if ($statResult['size'] < 0) {
                $size = self::getFileSizeFromOS($fullPath);
                $statResult['size'] = $size;
                $statResult[7] = $size;
            }
            return $statResult;
        }
    }

    public function hasUpdated($path, $time)
    {
        return $this->filemtime($path) > $time;
    }

    public function filemtime($path)
    {
        if ($path == '' || $path == '/') {
            $mtime = 0;
            foreach ($this->groupoffice_shares as $map => $folder) {
                $tmpmtime = $this->filemtime($map);
                if ($tmpmtime > $mtime) {
                    $mtime = $tmpmtime;
                }
            }
            return $mtime;
        } else {
            return filemtime($this->groupoffice_data . $this->get_real_path($path));
        }
    }

    public function isUpdatable($path)
    {
        \OCP\Util::writeLog('groupoffice', 'isupdatable: ' . $path, \OCP\Util::DEBUG);
        if ($path == '' || $path == '/') {
            return false;
        } else {
            $fullpath = $this->get_real_path($path);

            if ($this->is_file($path))
                $fullpath = dirname($fullpath);

            $folder = \GO_Files_Model_Folder::model()->findByPath($fullpath);

            if ($folder != '') {
                if ($folder->checkPermissionLevel(\GO_Base_Model_Acl::WRITE_PERMISSION)) {
                    return true;
                } else {
                    return false;
                }
            }

            return false;
        }
    }

    public function isCreatable($path)
    {
        \OCP\Util::writeLog('groupoffice', 'iscreatable: ' . $path, \OCP\Util::DEBUG);
        if ($path == '' || $path == '/') {
            return false;
        } else {
            $fullpath = $this->get_real_path($path);

            if ($this->is_file($path))
                $fullpath = dirname($fullpath);

            $folder = \GO_Files_Model_Folder::model()->findByPath($fullpath);

            if ($folder != '') {
                if ($folder->checkPermissionLevel(\GO_Base_Model_Acl::CREATE_PERMISSION)) {
                    return true;
                } else {
                    return false;
                }
            }

            return false;
        }
    }

    public function isDeletable($path)
    {
        \OCP\Util::writeLog('groupoffice', 'isdeletable: ' . $path, \OCP\Util::DEBUG);
        if ($path == '' || $path == '/') {
            return false;
        } else {
            $fullpath = $this->get_real_path($path);

            if ($this->is_file($path))
                $fullpath = dirname($fullpath);

            $folder = \GO_Files_Model_Folder::model()->findByPath($fullpath);

            if ($folder != '') {
                if ($folder->checkPermissionLevel(\GO_Base_Model_Acl::DELETE_PERMISSION)) {
                    return true;
                } else {
                    return false;
                }
            }

            return false;
        }
    }

    public function file_exists($path)
    {
        if ($path == '' || $path == '/') {
            return true;
        } else {
            return file_exists($this->groupoffice_data . $this->get_real_path($path));
        }
    }

    public function free_space($path)
    {
        if ($path == '' || $path == '/') {
            return \OC\Files\FREE_SPACE_UNKNOWN;
        } else {
            $space = @disk_free_space($this->groupoffice_data . $this->get_real_path($path));
            if ($space === false) {
                return \OC\Files\FREE_SPACE_UNKNOWN;
            }
            return $space;
        }
    }

    public function mkdir($path)
    {
        if ($path == '' || $path == '/') {
            return false;
        } else {
            $tmp_path = dirname($path);
            if ($tmp_path == '.')
                $tmp_path = '';

            \OCP\Util::writeLog('groupoffice', 'mkdir: ' . $tmp_path, \OCP\Util::DEBUG);
            if ($this->isCreatable($tmp_path)) {
                $fullpath = $this->get_real_path($path);
                $fullpath = dirname($fullpath);

                $folder = \GO_Files_Model_Folder::model()->findByPath($fullpath);
                $folder->addFolder(basename($path));
                return true;

            }

            return false;
        }
    }

    public function rmdir($path)
    {
        \OCP\Util::writeLog('groupoffice', 'rmdir: ' . $path, \OCP\Util::DEBUG);
        if ($this->isDeletable($path)) {
            $fullpath = $this->get_real_path($path);

            $folder = \GO_Files_Model_Folder::model()->findByPath($fullpath);

            if ($folder != '') {
                $folder->delete();
                \OCP\Util::writeLog('groupoffice', 'rmdir: true', \OCP\Util::DEBUG);
                return true;
            }
        } else
            return false;
    }

    public function fopen($path, $mode)
    {
        \OCP\Util::writeLog('groupoffice', 'fopen: ' . $path, \OCP\Util::DEBUG);
        if ($path == '' || $path == '/') {
            return false;
        } else {
            if ($return = fopen($this->groupoffice_data . $this->get_real_path($path), $mode)) {
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

    public function copy($path1, $path2)
    {
        if ($this->isCreatable(dirname($path2))) {
            if ($this->is_dir($path2)) {
                if (!$this->file_exists($path2)) {
                    $this->mkdir($path2);
                }
                $source = substr($path1, strrpos($path1, '/') + 1);
                $path2 .= $source;
            }
            return copy($this->groupoffice_data . $this->get_real_path($path1), $this->groupoffice_data . $this->get_real_path($path2));
        }
    }

    public function unlink($path)
    {
        if ($path == '' || $path == '/') {
            return false;
        } else {
            if ($this->isDeletable($path)) {
                return $this->delTree($path);
            }

            return false;
        }
    }

    private function delTree($dir)
    {
        $dirRelative = $dir;
        $dir = $this->groupoffice_data . $this->get_real_path($dir);
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

    public function file_get_contents($path)
    {
        if ($path == '' || $path == '/') {
            return false;
        } else {
            return file_get_contents($this->groupoffice_data . $this->get_real_path($path));
        }
    }

    public function file_put_contents($path, $data)
    {
        if ($path == '' || $path == '/') {
            return false;
        } else {
            if (($this->file_exists($path) && !$this->isUpdatable($path))
                || ($this->is_dir($path) && !$this->isCreatable($path))
            )
                return false;
            else
                return file_put_contents($this->groupoffice_data . $this->get_real_path($path), $data);
        }
    }

    public function touch($path, $mtime = null)
    {
        if ($this->file_exists($path) and !$this->isUpdatable($path)) {
            return false;
        }
        if (!is_null($mtime)) {
            $result = touch($this->groupoffice_data . $this->get_real_path($path), $mtime);
        } else {
            $result = touch($this->groupoffice_data . $this->get_real_path($path));
        }
        if ($result) {
            clearstatcache(true, $this->groupoffice_data . $this->get_real_path($path));
        }

        return $result;
    }
}

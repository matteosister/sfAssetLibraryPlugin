<?php
class sfAsset extends BasesfAsset
{
  /**
   * Get folder relative path
   *
   * @return string
   */
  public function getFolderPath()
  {
    $folder = $this->getsfAssetFolder();
    if (!$folder)
    {
      throw new Exception(sprintf('You must set define the folder for an asset prior to getting its path. Asset %d doesn\'t have a folder yet.', $this->getFilename()));
    }
    return $folder->getRelativePath();
  }

  /**
   * Gives the file relative path
   *
   * @return string
   */
  public function getRelativePath()
  {
    return $this->getFolderPath() . '/' . $this->getFilename();
  }

  /**
   * Gives full filesystem path
   *
   * @param string $thumbnail_type
   * @return string
   */
  public function getFullPath($thumbnail_type = 'full')
  {
    return sfAssetsLibraryTools::getThumbnailPath($this->getFolderPath(), $this->getFilename(), $thumbnail_type);
  }

  public function setFilename($filename)
  {
    $filename = sfAssetsLibraryTools::sanitizeName($filename);

    return parent::setFilename($filename);
  }

  /**
   * Gives the URL for the given thumbnail
   *
   * @param string $thumbnail_type
   * @return string
   */
  public function getUrl($thumbnail_type = 'full', $relative_path = null)
  {
    if(is_null($relative_path))
    {
      if (!$folder = $this->getsfAssetFolder())
      {
        throw new Exception(sprintf('You must set define the folder for an asset prior to getting its path. Asset %d doesn\'t have a folder yet.', $this->getFilename()));
      }
      $relative_path = $folder->getRelativePath();
    }
    $url = sfAssetsLibraryTools::getMediaDir();
    if ($thumbnail_type == 'full')
    {
      $url .= $relative_path . DIRECTORY_SEPARATOR . $this->getFilename();
    }
    else
    {
      $url .= sfAssetsLibraryTools::getThumbnailDir($relative_path) . $thumbnail_type . '_' . $this->getFilename();
    }

    return $url;
  }

  public function autoSetType()
  {
    $this->setType(sfAssetsLibraryTools::getType($this->getFullPath()));
  }

  public function isImage()
  {
    return $this->getType() === 'image';
  }

  public function supportsThumbnails()
  {
    return $this->isImage() && class_exists('sfThumbnail');
  }

  /**
   * Physically creates asset
   *
   * @param string $asset_path path to the asset original file
   * @param bool $move do move or just copy ?
   */
  public function create($asset_path, $move = true, $checkDuplicate = true)
  {
    if (!is_file($asset_path))
    {
      throw new sfAssetException('Asset "%asset%" not found', array('%asset%' => $asset_path));
    }

    // calculate asset properties
    if (!$this->getFilename())
    {
      list (,$filename) = sfAssetsLibraryTools::splitPath($asset_path);
      $this->setFilename($filename);
    }

    // check folder
    if (!$this->getsfAssetFolder()->exists())
    {
      $this->getsfAssetFolder()->create();
    }
    else
    {
      // check if a file with this name already exists
      if($checkDuplicate && sfAssetPeer::exists($this->getsfAssetFolder()->getId(), $this->getFilename()))
      {
        $this->setFilename(time().$this->getFilename());
      }
    }

    $this->setFilesize((int) filesize($asset_path) / 1024);
    $this->autoSetType();
    if (sfConfig::get('app_sfAssetsLibrary_check_type', false) && !in_array($this->getType(), sfConfig::get('app_sfAssetsLibrary_types', array('image', 'txt', 'archive', 'pdf', 'xls', 'doc', 'ppt'))))
    {
      throw new sfAssetException('Filetype "%type%" not allowed', array('%type%' => $this->getType()));
    }

    if ($move)
    {
      rename($asset_path, $this->getFullPath());
    }
    else
    {
      copy($asset_path, $this->getFullPath());
    }

    if ($this->supportsThumbnails())
    {
      sfAssetsLibraryTools::createThumbnails($this->getFolderPath(), $this->getFilename());
    }
  }

  public function getFilepaths()
  {
    $filepaths = array('full' => $this->getFullPath());
    if ($this->isImage() && $this->supportsThumbnails())
    {
      // Add path to the thumbnails
      foreach (sfConfig::get('app_sfAssetsLibrary_thumbnails', array(
        'small' => array('width' => 84, 'height' => 84, 'shave' => true),
        'large' => array('width' => 194, 'height' => 152)
        )) as $key => $params)
      {
        $filepaths[$key] = $this->getFullPath($key);
      }
    }

    return $filepaths;
  }

  /**
   * Change asset directory and/or name
   *
   * @param sfAssetFolder $new_folder
   * @param string $new_filename
   */
  public function move(sfAssetFolder $new_folder, $new_filename = null)
  {
    if(sfAssetPeer::exists($new_folder->getId(), $new_filename ? $new_filename : $this->getFilename()))
    {
      throw new sfAssetException('The target folder "%folder%" already contains an asset named "%name%". The asset has not been moved.', array('%folder%' => $new_folder->getName(), '%name%' => $new_filename ? $new_filename : $this->getFilename()));
    }
    $old_filepaths = $this->getFilepaths();
    if ($new_filename)
    {
      if(sfAssetsLibraryTools::sanitizeName($new_filename) != $new_filename)
      {
        throw new sfAssetException('The filename "%name%" contains incorrect characters. The asset has not be altered.', array('%name%' => $new_filename));
      }
      $this->setFilename($new_filename);
    }
    $this->setFolderId($new_folder->getId());
    $success = true;
    foreach ($old_filepaths as $type => $filepath)
    {
      $success = rename($filepath, $this->getFullPath($type)) && $success;
    }
    if(!$success)
    {
      throw new sfAssetException('Some or all of the file operations failed. It is possible that the moved asset or its thumbnails are missing.');
    }
  }

  /**
   * Physically remove assets
   */
  public function destroy()
  {
    $success = true;
    foreach ($this->getFilepaths() as $filepath)
    {
      $success = unlink($filepath) && $success;
    }

    return $success;
  }

  public function delete(PropelPDO $con = null)
  {
    $success = $this->destroy();
    parent::delete($con);

    return $success;
  }

  /**
   * magic method
   * @return string
   */
  public function __toString()
  {
    return $this->getFilename();
  }

  /**
   * get thumbnail url, or generic thumbnail url
   * @return string
   */
  public function getThumbUrl()
  {
    return is_file($this->getFullPath('small')) ?
      $this->getUrl('small') : '/sfAssetsLibraryPlugin/images/unknown.png';
  }

  /**
   * get thumbnail url, or generic thumbnail url, plus filename
   * @return string
   */
  public function getNameThumbUrl()
  {
    $ret = $this->getFilename() . '|';
    $ret .= is_file($this->getFullPath('small')) ?
      $this->getUrl('small') : '/sfAssetsLibraryPlugin/images/unknown.png';

    return $ret;
  }

  /**
   * get thumbnail url, plus other info
   * @return string
   */
  public function getInfoThumbUrl()
  {
    $ret = $this->getFilename() . '|';
    $ret .= is_file($this->getFullPath('small')) ?
      $this->getUrl('small') : '/sfAssetsLibraryPlugin/images/unknown.png';
    $ret .=  '|' . $this->getFolderId() . '|' . $this->getType();

    return $ret;
  }

}

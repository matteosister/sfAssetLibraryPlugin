<?php

/**
 * Subclass for performing query and update operations on the 'sf_asset' table.
 *
 * 
 *
 * @package plugins.sfAssetsLibraryPlugin.lib.model
 */ 
class sfAssetPeer extends BasesfAssetPeer
{
  public static function exists($folderId, $filename)
  {
    $c = new Criteria();
    $c->add(self::FOLDER_ID, $folderId);
    $c->add(self::FILENAME, $filename);
    
    return self::doCount($c) > 0 ? true : false;
  }
  
  /**
   * Retrieves a sfAsset object from a relative URL like
   *    /medias/foo/bar.jpg
   * i.e. the kind of URL returned by $sf_asset->getUrl()
   */
  public static function retrieveFromUrl($url)
  {
    $url = sfAssetFolderPeer::cleanPath($url);
    list($relPath, $filename) = sfAssetsLibraryTools::splitPath($url);
    
    $c = new Criteria();
    $c->add(sfAssetPeer::FILENAME, $filename);
    $c->addJoin(sfAssetPeer::FOLDER_ID, sfAssetFolderPeer::ID);
    $c->add(sfAssetFolderPeer::RELATIVE_PATH, $relPath ?  $relPath : null);
    
    return sfAssetPeer::doSelectOne($c);
  }
  
}

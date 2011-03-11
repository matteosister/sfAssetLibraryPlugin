<?php

/**
 *
 * @package    symfony
 * @subpackage plugin
 * @author     Francois Zaninotto <FRANCOIS.ZANINOTTO@symfony-project.com>
 */
class sfAssetRouting
{
  /**
   * Listens to the routing.load_configuration event.
   *
   * @param sfEvent An sfEvent instance
   */
  static public function listenToRoutingLoadConfigurationEvent(sfEvent $event)
  {
    $r = $event->getSubject();

    // preprend our routes
    $r->prependRoute('sf_guard_signin', new sfRoute('/login', array('module' => 'sfGuardAuth', 'action' => 'signin')));

    $r->prependRoute('sf_asset_library_dir', new sfRoute('/sfAsset/dir/:dir', array(
        'module'    => 'sfAsset',
        'action'    => 'list',
        'dir'       => sfConfig::get('app_sfAssetsLibrary_upload_dir', 'media')
      ),
      array('dir' => '.*?'))
    );

    $actions = array(
      'index'         => 'index',
      'list'          => 'list',
      'search'        => 'search',
      'edit'          => 'edit',
      'update'        => 'update',
      'move_asset'    => 'moveAsset',
      'rename_asset'  => 'renameAsset',
      'replace_asset' => 'replaceAsset',
      'delete_asset'  => 'deleteAsset',
      'create_folder' => 'createFolder',
      'move_folder'   => 'moveFolder',
      'rename_folder' => 'renameFolder',
      'delete_folder' => 'deleteFolder',
      'mass_upload'   => 'massUpload',
      'add_quick'     => 'addQuick',
      'tiny_config'   => 'tinyConfigMedia',
    );

    foreach ($actions as $route => $action)
    {
      $r->prependRoute('sf_asset_library_' . $route, new sfRoute('/sfAsset/' . $action, array(
        'module' => 'sfAsset', 'action' => $action,
      )));
    }
  }
}

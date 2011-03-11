<?php

require_once(sfConfig::get('sf_plugins_dir'). '/sfAssetsLibraryPlugin/modules/sfAsset/lib/BasesfAssetActions.class.php');

class sfAssetActions extends BasesfAssetActions
{
  /**
   * get a list of assets
   */
  public function executeAjaxlist(sfWebRequest $request)
  {
    $dir = $request->getParameter('dir');
    $folder = $dir > 0 ? sfAssetFolderPeer::retrieveByPK($dir) : sfAssetFolderPeer::retrieveRoot();
    $this->forward404Unless($folder, 'folder not found');
    $c = new Criteria();
    $c->add(sfAssetPeer::FOLDER_ID, $folder->getId());
    if ($request->getParameter('type') && $request->getParameter('type') != 'all')
    {
      $c->add(sfAssetPeer::TYPE, $request->getParameter('type'));
    }
    $this->files = sfAssetPeer::doSelect($c);
  }

  /**
   * get a list of asset folders
   */
  public function executeAjaxfolders(sfWebRequest $request)
  {
    $form = new sfAssetFoldersForm(null, array('folder' => $request->getParameter('folder'), 'name' => $request->getParameter('name')));

    return $this->renderText($form['folder']->render() . ' ' . $form['type']->render());
  }

}
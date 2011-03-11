<?php

class BasesfAssetActions extends sfActions
{
  public function executeIndex()
  {
    $this->getUser()->getAttributeHolder()->remove('popup', null, 'sf_admin/sf_asset/navigation');
    $this->redirect('@sf_asset_library_list');
  }

  public function executeList(sfWebRequest $request)
  {
    $folder = sfAssetFolderPeer::retrieveByPath($request->getParameter('dir'));
    if (!$folder)
    {
      if ($this->getUser()->getFlash('sfAsset_folder_not_found'))
      {
        throw new sfException('You must create a root folder. Use the `php symfony asset:create-root` command for that.');
      }
      else
      {
        if ($popup = $request->getParameter('popup'))
        {
          $this->getUser()->setAttribute('popup', $popup, 'sf_admin/sf_asset/navigation');
          if(strpos($popup, 'tiny') !== false) {
            $this->getUser()->setAttribute('popup_tinymce', true, 'sf_admin/sf_asset/tinymce');
          } else {
            $this->getUser()->setAttribute('popup_tinymce', false, 'sf_admin/sf_asset/tinymce');
          }
          $this->getUser()->setAttribute('input_id', $request->getParameter('input_id', null), 'sf_admin/sf_asset/input_id');
          $this->getUser()->setAttribute('preview_type', $request->getParameter('preview_type', 'small'), 'sf_admin/sf_asset/preview_type');
        }
        $this->getUser()->setFlash('sfAsset_folder_not_found', true);
        $this->redirect('@sf_asset_library_list');
      }
    }

    if ($popup = $request->getParameter('popup'))
    {
      $this->getUser()->setAttribute('popup', $popup, 'sf_admin/sf_asset/navigation');
      if(strpos($popup, 'tiny') !== false) {
        $this->getUser()->setAttribute('popup_tinymce', true, 'sf_admin/sf_asset/tinymce');
      } else {
        $this->getUser()->setAttribute('popup_tinymce', false, 'sf_admin/sf_asset/tinymce');
      }
      $this->getUser()->setAttribute('input_id', $request->getParameter('input_id', null), 'sf_admin/sf_asset/input_id');
      $this->getUser()->setAttribute('preview_type', $request->getParameter('preview_type', 'small'), 'sf_admin/sf_asset/preview_type');
    }

    
    $this->filterform = new sfAssetFormFilter();
    $this->folderform = new sfAssetFolderForm(null, array('parent_id' => $folder->getId()));
    $this->fileform = new sfAssetForm(null, array('parent_id' => $folder->getId()));
    $this->renameform = new sfAssetFolderRenameForm($folder);
    $this->moveform = new sfAssetFolderMoveForm($folder);
    $dirs = $folder->getChildren();
    $c = new Criteria();
    $c->add(sfAssetPeer::FOLDER_ID, $folder->getId());
    $this->processSort($request);
    $sortOrder = $this->getUser()->getAttribute('sort', 'name', 'sf_admin/sf_asset/sort');
    switch ($sortOrder)
    {
      case 'date':
        $dirs = sfAssetFolderPeer::sortByDate($dirs);
        $c->addDescendingOrderByColumn(sfAssetPeer::CREATED_AT);
        break;
      default:
        $dirs = sfAssetFolderPeer::sortByName($dirs);
        $c->addAscendingOrderByColumn(sfAssetPeer::FILENAME);
        break;
    }
    $this->files = sfAssetPeer::doSelect($c);
    $this->nb_files = count($this->files);
    if($this->nb_files)
    {
      $total_size = 0;
      foreach ($this->files as $file)
      {
        $total_size += $file->getFilesize();
      }
      $this->total_size = $total_size;
    }
    $this->dirs = $dirs;
    $this->nb_dirs = count($dirs);
    $this->folder = $folder;

    $this->removeLayoutIfPopup($request);

    return sfView::SUCCESS;
  }

  protected function processSort(sfWebRequest $request)
  {
    if ($request->getParameter('sort'))
    {
      $this->getUser()->setAttribute('sort', $request->getParameter('sort'), 'sf_admin/sf_asset/sort');
    }
  }

  public function executeSearch(sfWebRequest $request)
  {
    $this->form = new sfAssetFormFilter;
    $this->form->bind($request->getParameter($this->form->getName()));
    $this->filterform = new sfAssetFormFilter();

    // We keep the search params in the session for easier pagination
    if ($request->hasParameter('search_params'))
    {
      $search_params = $request->getParameter('search_params');
      if (isset($search_params['created_at']['from']) && $search_params['created_at']['from'] !== '')
      {
        $search_params['created_at']['from'] = sfI18N::getTimestampForCulture($search_params['created_at']['from'], $this->getUser()->getCulture());
      }
      if (isset($search_params['created_at']['to']) && $search_params['created_at']['to'] !== '')
      {
        $search_params['created_at']['to'] = sfI18N::getTimestampForCulture($search_params['created_at']['to'], $this->getUser()->getCulture());
      }

      $this->getUser()->getAttributeHolder()->removeNamespace('sf_admin/sf_asset/search_params');
      $this->getUser()->getAttributeHolder()->add($search_params, 'sf_admin/sf_asset/search_params');
    }

    $this->search_params = $this->getUser()->getAttributeHolder()->getAll('sf_admin/sf_asset/search_params');

    if ($this->form->isValid())
    {
      $c = $this->processSearch($this->form->getValues(), $request);
    }
    else
    {
      $c = new Criteria();
    }

    $pager = new sfPropelPager('sfAsset', sfConfig::get('app_sfAssetsLibrary_search_pager_size', 20));
    $pager->setCriteria($c);
    $pager->setPage($request->getParameter('page', 1));
    $pager->setPeerMethod('doSelectJoinsfAssetFolder');
    $pager->init();

    $this->pager = $pager;

    $this->removeLayoutIfPopup($request);
  }

  protected function processSearch($search_params, $request)
  {
    $c = new Criteria();

    if (isset($search_params['folder_id']) && $search_params['folder_id'] !== '')
    {
      if (null!= $folder = sfAssetFolderPeer::retrieveByPK($search_params['folder_id']))
      {
        $c->addJoin(sfAssetPeer::FOLDER_ID, sfAssetFolderPeer::ID);
        $c->add(sfAssetFolderPeer::TREE_LEFT, $folder->getTreeLeft(), Criteria::GREATER_EQUAL);
        $c->add(sfAssetFolderPeer::TREE_RIGHT, $folder->getTreeRIGHT(), Criteria::LESS_EQUAL);
      }
    }
    if (isset($search_params['filename']['is_empty']))
    {
      $criterion = $c->getNewCriterion(sfAssetPeer::FILENAME, '');
      $criterion->addOr($c->getNewCriterion(sfAssetPeer::FILENAME, null, Criteria::ISNULL));
      $c->add($criterion);
    }
    else if (isset($search_params['filename']['text']) && $search_params['filename']['text'] !== '')
    {
      $c->add(sfAssetPeer::FILENAME, '%'.trim($search_params['filename']['text'], '*%').'%', Criteria::LIKE);
    }
    if (isset($search_params['author']['is_empty']))
    {
      $criterion = $c->getNewCriterion(sfAssetPeer::AUTHOR, '');
      $criterion->addOr($c->getNewCriterion(sfAssetPeer::AUTHOR, null, Criteria::ISNULL));
      $c->add($criterion);
    }
    else if (isset($search_params['author']['text']) && $search_params['author']['text'] !== '')
    {
      $c->add(sfAssetPeer::AUTHOR, '%'.trim($search_params['author']['text'], '*%').'%', Criteria::LIKE);
    }
    if (isset($search_params['copyright']['is_empty']))
    {
      $criterion = $c->getNewCriterion(sfAssetPeer::COPYRIGHT, '');
      $criterion->addOr($c->getNewCriterion(sfAssetPeer::COPYRIGHT, null, Criteria::ISNULL));
      $c->add($criterion);
    }
    else if (isset($search_params['copyright']['text']) && $search_params['copyright']['text'] !== '')
    {
      $c->add(sfAssetPeer::COPYRIGHT, '%'.trim($search_params['copyright']['text'], '*%').'%', Criteria::LIKE);
    }
    if (isset($search_params['created_at']))
    {
      if (isset($search_params['created_at']['from']) && $search_params['created_at']['from'] !== array())  // TODO check this
      {
        $criterion = $c->getNewCriterion(sfAssetPeer::CREATED_AT, $search_params['created_at']['from'], Criteria::GREATER_EQUAL);
      }
      if (isset($search_params['created_at']['to']) && $search_params['created_at']['to'] !== array())  // TODO check this
      {
        if (isset($criterion))
        {
          $criterion->addAnd($c->getNewCriterion(sfAssetPeer::CREATED_AT, $search_params['created_at']['to'], Criteria::LESS_EQUAL));
        }
        else
        {
          $criterion = $c->getNewCriterion(sfAssetPeer::CREATED_AT, $search_params['created_at']['to'], Criteria::LESS_EQUAL);
        }
      }
      if (isset($criterion))
      {
        $c->add($criterion);
      }
    }
    if (isset($search_params['description']['is_empty']))
    {
      $criterion = $c->getNewCriterion(sfAssetPeer::DESCRIPTION, '');
      $criterion->addOr($c->getNewCriterion(sfAssetPeer::DESCRIPTION, null, Criteria::ISNULL));
      $c->add($criterion);
    }
    else if (isset($search_params['description']) && $search_params['description'] !== '')
    {
      $c->add(sfAssetPeer::DESCRIPTION, '%'.trim($search_params['description'], '*%').'%', Criteria::LIKE);
    }

    $this->processSort($request);
    $sortOrder = $this->getUser()->getAttribute('sort', 'name', 'sf_admin/sf_asset/sort');
    switch($sortOrder)
    {
      case 'date':
        $c->addDescendingOrderByColumn(sfAssetPeer::CREATED_AT);
        break;
      default:
        $c->addAscendingOrderByColumn(sfAssetPeer::FILENAME);
        break;
    }

    return $c;
  }

  public function executeCreateFolder(sfWebRequest $request)
  {
    $this->form = new sfAssetFolderForm();
    $this->form->bind($request->getParameter($this->form->getName()));
    if ($this->form->isValid())
    {
      $parentFolder = sfAssetFolderPeer::retrieveByPK($this->form->getValue('parent_folder'));
      $this->forward404Unless($parentFolder, 'parent folder not found');
      $folder = new sfAssetFolder();
      $folder->setName($this->form->getValue('name'));
      $folder->insertAsLastChildOf($parentFolder);
      $folder->save();
      $this->redirectToPath('@sf_asset_library_list?dir='.$folder->getRelativePath());
    }
  }

  public function executeMoveFolder(sfWebRequest $request)
  {
    $this->forward404Unless($request->getMethod() == sfRequest::POST, 'method not allowed');
    $sf_asset_folder = $request->getParameter('sf_asset_folder');
    $folder = sfAssetFolderPeer::retrieveByPk($sf_asset_folder['id']);
    $this->forward404Unless($folder, 'folder not found');
    $this->form = new sfAssetFolderMoveForm($folder);
    $this->form->bind($request->getParameter($this->form->getName()));
    if ($this->form->isValid())
    {
      try
      {
        $targetFolder = sfAssetFolderPeer::retrieveByPK($this->form->getValue('parent_folder'));
        $folder->move($targetFolder);
        $this->getUser()->setFlash('notice', 'The folder has been moved');
      }
      catch (sfAssetException $e)
      {
        $this->getUser()->setFlash('warning_message', $e->getMessage());
        $this->getUser()->setFlash('warning_params', $e->getMessageParams());
      }

      return $this->redirectToPath('@sf_asset_library_list?dir=' . $folder->getRelativePath());
    }
  }

  public function executeRenameFolder(sfWebRequest $request)
  {
    $this->forward404Unless($request->getMethod() == sfRequest::POST, 'method not allowed');
    $sf_asset_folder = $request->getParameter('sf_asset_folder');
    $folder = sfAssetFolderPeer::retrieveByPk($sf_asset_folder['id']);
    $this->forward404Unless($folder, 'folder not found');
    $this->form = new sfAssetFolderRenameForm($folder);
    $this->form->bind($request->getParameter($this->form->getName()));
    if ($this->form->isValid())
    {
      try
      {
        $folder->rename($this->form->getValue('name'));
        $this->getUser()->setFlash('notice', 'The folder has been renamed');
      }
      catch (sfAssetException $e)
      {
        $this->getUser()->setFlash('warning_message', $e->getMessage());
        $this->getUser()->setFlash('warning_params', $e->getMessageParams());
      }

      return $this->redirectToPath('@sf_asset_library_list?dir=' . $folder->getRelativePath());
    }
  }

  public function executeDeleteFolder(sfWebRequest $request)
  {
    $this->forward404Unless($request->isMethod(sfRequest::POST));
    $folder = sfAssetFolderPeer::retrieveByPk($request->getParameter('id'));
    $this->forward404Unless($folder);
    try
    {
      $folder->delete();
      $this->getUser()->setFlash('notice', 'The folder has been deleted');
    }
    catch (sfAssetException $e)
    {
      $this->getUser()->setFlash('warning_message', $e->getMessage());
      $this->getUser()->setFlash('warning_params', $e->getMessageParams());
    }

    return $this->redirectToPath('@sf_asset_library_list?dir=' . $folder->getParentPath());
  }

  public function executeAddQuick(sfWebRequest $request)
  {
    $this->form = new sfAssetForm();
    $this->form->bind($request->getParameter($this->form->getName()),
                      $request->getFiles($this->form->getName()));
    if (!$this->form->isValid())
    {
      $this->sf_asset = new sfAsset;
      return sfView::SUCCESS;
    }

    $folder = sfAssetFolderPeer::retrieveByPK($this->form->getValue('folder_id'));
    $this->forward404Unless($folder, 'folder not found');
    $file = $this->form->getValue('file');
    try
    {
      $asset = new sfAsset();
      $asset->setsfAssetFolder($folder);
      $asset->setDescription($file->getOriginalName());
      try
      {
        $asset->setAuthor($this->getUser()->getUsername());
      }
      catch(sfException $e)
      {
        // no getUsername() method in sfUser, all right: do nothing
      }
      $asset->setFilename($file->getOriginalName());
      $asset->create($file->getTempName());
      $asset->save();
    }
    catch(sfAssetException $e)
    {
      $this->getUser()->setFlash('warning_message', $e->getMessage());
      $this->getUser()->setFlash('warning_params', $e->getMessageParams());
      $this->redirectToPath('@sf_asset_library_list?dir='.$folder->getRelativePath());
    }

    if($this->getUser()->hasAttribute('popup', 'sf_admin/sf_asset/navigation'))
    {
      if($this->getUser()->getAttribute('popup', null, 'sf_admin/sf_asset/navigation') == 1)
      {
        $this->redirect('@sf_asset_library_tiny_config?id='.$asset->getId());
      }
      else
      {
        $this->redirectToPath('@sf_asset_library_list?dir='.$folder->getRelativePath());
      }
    }
    $this->redirect('@sf_asset_library_edit?id='.$asset->getId());
  }

  public function executeMassUpload(sfWebRequest $request)
  {
    $this->form = new sfAssetsForm(null, array('size' => sfConfig::get('app_sfAssetsLibrary_mass_upload_size', 5)));
    if ($request->getMethod() == sfRequest::POST)
    {
      $this->form->bind($request->getParameter($this->form->getName()),
                        $request->getFiles($this->form->getName()));
      if ($this->form->isValid())
      {
        $folder = sfAssetFolderPeer::retrieveByPK($this->form->getValue('folder_id'));
        $this->forward404Unless($folder, 'folder not found');
        try
        {
          $nbFiles = 0;
          for ($i = 1; $i <= sfConfig::get('app_sfAssetsLibrary_mass_upload_size', 5) ; $i ++)
          {
            if ($file = $this->form->getValue('file_' . $i))
            {
              $asset = new sfAsset();
              $asset->setsfAssetFolder($folder);
              $asset->setDescription($file->getOriginalName());
              try
              {
                $asset->setAuthor($this->getUser()->getUsername());
              }
              catch(sfException $e)
              {
                // no getUsername() method in sfUser, all right: do nothing
              }
              $asset->setFilename($file->getOriginalName());
              $asset->create($file->getTempName());
              $asset->save();
              $nbFiles ++;
            }
          }
        }
        catch(sfAssetException $e)
        {
          $this->getUser()->setFlash('warning_message', $e->getMessage());
          $this->getUser()->setFlash('warning_params', $e->getMessageParams());
          $this->redirectToPath('@sf_asset_library_list?dir='.$folder->getRelativePath());
        }
        $this->getUser()->setFlash('notice', 'Files successfully uploaded');
        $this->redirectToPath('@sf_asset_library_list?dir='.$folder->getRelativePath());
      }
    }
  }

  public function executeDeleteAsset(sfWebRequest $request)
  {
    $sf_asset = sfAssetPeer::retrieveByPk($request->getParameter('id'));
    $this->forward404Unless($sf_asset, 'asset not found');
    $folderPath = $sf_asset->getFolderPath();
    try
    {
      $sf_asset->delete();
    }
    catch (PropelException $e)
    {
      $request->setError('delete', 'Impossible to delete asset, probably due to related records');
      return $this->forward('sfAsset', 'edit');
    }

    return $this->redirectToPath('@sf_asset_library_list?dir='.$folderPath);
  }

  public function executeCreate()
  {
    return $this->forward('sfAsset', 'edit');
  }

  public function executeEdit(sfWebRequest $request)
  {
    $this->sf_asset = sfAssetPeer::retrieveByPK($request->getParameter('id'));
    $this->forward404Unless($this->sf_asset, 'asset not found');
    $this->form = new sfAssetForm($this->sf_asset);
    $this->renameform = new sfAssetRenameForm($this->sf_asset);
    $this->moveform = new sfAssetMoveForm($this->sf_asset);
    $this->replaceform = new sfAssetReplaceForm($this->sf_asset);
  }

  public function executeUpdate(sfWebRequest $request)
  {
    $sf_asset = $request->getParameter('sf_asset');
    $this->sf_asset = sfAssetPeer::retrieveByPK($sf_asset['id']);
    $this->forward404Unless($this->sf_asset, 'asset not found');
    $this->form = new sfAssetForm($this->sf_asset);
    $this->renameform = new sfAssetRenameForm($this->sf_asset);
    $this->moveform = new sfAssetMoveForm($this->sf_asset);
    $this->replaceform = new sfAssetReplaceForm($this->sf_asset);
    if ($this->form->bindAndSave($request->getParameter($this->form->getName())))
    {
      $this->getUser()->setFlash('notice', 'Your modifications have been saved');
      return $this->redirect('@sf_asset_library_edit?id='.$this->sf_asset->getId());
    }
    else
    {
      $this->getUser()->setFlash('notice', 'Error: ' . $this->form->getErrorSchema());
    }
    $this->setTemplate('edit');
  }

  public function executeMoveAsset(sfWebRequest $request)
  {
    $this->forward404Unless($request->getMethod() == sfRequest::POST, 'method not allowed');
    $sfAsset = $request->getParameter('sf_asset');
    $sf_asset = sfAssetPeer::retrieveByPK($sfAsset['id']);
    $this->forward404Unless($sf_asset, 'asset not found');
    $new_folder = sfAssetFolderPeer::retrieveByPk($sfAsset['parent_folder']);
    $this->forward404Unless($new_folder, 'destination folder not found');
    $form = new sfAssetMoveForm($sf_asset);
    $form->bind($request->getParameter($form->getName()));
    if ($form->isValid() && $new_folder->getId() != $sf_asset->getFolderId())
    {
      try
      {
        $sf_asset->move($new_folder);
        $sf_asset->save();
        $this->getUser()->setFlash('notice', 'The file has been moved');
      }
      catch(sfAssetException $e)
      {
        $this->getUser()->setFlash('warning_message', $e->getMessage());
        $this->getUser()->setFlash('warning_params', $e->getMessageParams());
      }
    }
    else
    {
      $this->getUser()->setFlash('warning', 'The asset has not been moved.');
    }

    return $this->redirect('@sf_asset_library_edit?id='.$sf_asset->getId());
  }

  public function executeRenameAsset(sfWebRequest $request)
  {
    $this->forward404Unless($request->getMethod() == sfRequest::POST, 'method not allowed');
    $sfAsset = $request->getParameter('sf_asset');
    $sf_asset = sfAssetPeer::retrieveByPK($sfAsset['id']);
    $this->forward404Unless($sf_asset, 'asset not found');
    $form = new sfAssetRenameForm($sf_asset);
    $form->bind($request->getParameter($form->getName()));
    if ($form->isValid() && $sf_asset->getFilename() != $form->getValue('filename'))
    {
      try
      {
        $sf_asset->move($sf_asset->getsfAssetFolder(), $form->getValue('filename'));
        $sf_asset->save();
        $this->getUser()->setFlash('notice', 'The file has been renamed');
      }
      catch(sfAssetException $e)
      {
        $this->getUser()->setFlash('warning_message', $e->getMessage());
        $this->getUser()->setFlash('warning_params', $e->getMessageParams());
      }
    }
    else
    {
      $this->getUser()->setFlash('notice', 'The asset has not been renamed.');

    }

    return $this->redirect('@sf_asset_library_edit?id='.$sf_asset->getId());
  }

  public function executeReplaceAsset(sfWebRequest $request)
  {
    $this->forward404Unless($request->isMethod(sfRequest::POST), 'method not allowed');
    $sfAsset = $request->getParameter('sf_asset');
    $sf_asset = sfAssetPeer::retrieveByPK($sfAsset['id']);
    $this->forward404Unless($sf_asset, 'asset not found');
    $form = new sfAssetReplaceForm($sf_asset);
    $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
    if ($form->isValid())
    {
      // physically replace asset
      $file = $form->getValue('file');
      $sf_asset->destroy();
      $sf_asset->create($file->getTempName(), true, false);
    }

    $this->getUser()->setFlash('notice', 'The file has been replaced');

    return $this->redirect('@sf_asset_library_edit?id='.$sf_asset->getId());
  }

  protected function removeLayoutIfPopup(sfWebRequest $request)
  {
    if ($popup = $request->getParameter('popup'))
    {
      $this->getUser()->setAttribute('popup', $popup, 'sf_admin/sf_asset/navigation');
    }
    if  ($this->getUser()->hasAttribute('popup', 'sf_admin/sf_asset/navigation'))
    {
      $this->setLayout($this->getContext()->getConfiguration()->getTemplateDir('sfAsset', 'popupLayout.php') . DIRECTORY_SEPARATOR . 'popupLayout');
      $this->popup = true;
      // tinyMCE?
      if (strpos($this->getUser()->getAttribute('popup', null, 'sf_admin/sf_asset/navigation'), 'tiny') !== false)
      {
        $this->getResponse()->addJavascript('tiny_mce/tiny_mce_popup');
      }
    }
    else
    {
      $this->popup = false;
    }
  }

  protected function getsfAssetOrCreate(sfWebRequest $request, $id = 'id')
  {
    if (!$request->getParameter($id))
    {
      $sf_asset = new sfAsset();
    }
    else
    {
      $sf_asset = sfAssetPeer::retrieveByPk($request->getParameter($id));

      $this->forward404Unless($sf_asset, 'asset not found');
    }

    return $sf_asset;
  }

  protected function redirectToPath($path, $statusCode = 302)
  {
    $url = $this->getController()->genUrl($path, true);
    $url = str_replace('%2F', '/', $url);

    if (sfConfig::get('sf_logging_enabled'))
    {
      $this->getContext()->getLogger()->info('{sfAction} redirect to "'.$url.'"');
    }

    $this->getController()->redirect($url, 0, $statusCode);

    throw new sfStopException();
  }

  public function executeTinyConfigMedia(sfWebRequest $request)
  {
    $this->forward404Unless($this->hasRequestParameter('id'), 'missing id');
    $this->sf_asset = sfAssetPeer::retrieveByPk($request->getParameter('id'));
    $this->forward404Unless($this->sf_asset, 'asset not found');
    $this->form = new sfAssetTinyConfigMediaForm($this->sf_asset);
    $this->setLayout($this->getContext()->getConfiguration()->getTemplateDir('sfAsset', 'popupLayout.php') . DIRECTORY_SEPARATOR . 'popupLayout');

    return sfView::SUCCESS;
  }
}

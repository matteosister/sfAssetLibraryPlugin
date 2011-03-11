<?php

/**
 * sfAssetFolders form.
 *
 * @package    symfony
 * @subpackage form
 * @author     Massimiliano Arione <garakkio@gmail.com>
 */
class sfAssetFoldersForm extends BasesfAssetForm
{
  public function configure()
  {
    // hide unuseful fields
    $this->useFields(array('type'));
    // order folders
    $this->widgetSchema['folder'] = new sfWidgetFormPropelChoice(array(
      'model'    => 'sfAssetFolder',
      'criteria' => sfAssetFolderQuery::create()->addAscendingOrderByColumn(sfAssetFolderPeer::TREE_LEFT),
    ));
    // nice type
    $types = sfConfig::get('app_sfAssetsLibrary_types', array('image' => 'image'));
    $this->widgetSchema['type'] = new sfWidgetFormChoice(array('choices' => $types));
    $this->validatorSchema['type'] = new sfValidatorChoice(array('choices' => array_keys($types)));

    // possible selected folder
    if ($this->getOption('folder'))
    {
      $this->setDefault('folder', $this->getOption('folder'));
    }

    // if name passed as option, set it (to avoid conflicts in DOM)
    if ($this->getOption('name'))
    {
      $this->widgetSchema->setNameFormat($this->getOption('name') . '_' . $this->widgetSchema->getNameFormat());
    }
  }
}

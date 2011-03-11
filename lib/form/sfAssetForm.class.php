<?php

/**
 * sfAsset form.
 *
 * @package    symfony
 * @subpackage form
 * @author     Massimiliano Arione <garakkio@gmail.com>
 */
class sfAssetForm extends BasesfAssetForm
{
  public function configure()
  {
    // hide some fields
    unset($this['created_at'], $this['updated_at'], $this['filesize'], $this['page_asset_list']);

    // filename not required (since it's extracted from file)
    $this->validatorSchema['filename']->setOption('required', false);

    // new asset (create)
    if ($this->getObject()->isNew())
    {
      // add hidden parent folder
      $this->widgetSchema['folder_id'] = new sfWidgetFormInputHidden();
      if (!empty($this->options['parent_id']))
      {
        $this->setDefault('folder_id', $this->options['parent_id']);
      }
      $this->validatorSchema['folder_id'] = new sfValidatorPropelChoice(array('model' => 'sfAssetFolder',
                                                                              'column' => 'id',
                                                                              'required' => true));

      // add file input
      $this->widgetSchema['file'] = new sfWidgetFormInputFile();
      $this->validatorSchema['file'] = new sfValidatorFile();
    }
    // old asset (edit)
    else
    {
      // hide other fields
      unset($this['folder_id'], $this['filename']);

      // type
      $types = sfConfig::get('app_sfAssetsLibrary_types', array('image' => 'image', 'txt' => 'txt', 'archive' => 'archive', 'pdf' => 'pdf', 'xls' => 'xls', 'doc' => 'doc', 'ppt' => 'ppt'));
      $this->widgetSchema['type'] = new sfWidgetFormChoice(array('choices' => $types));
      $this->validatorSchema['type'] = new sfValidatorChoice(array('choices' => array_keys($types)));

      // formatter (see sfWidgetFormSchemaFormatterAsset.class.php)
      $this->widgetSchema->setFormFormatterName('assets');
    }
  }
}

<?php

/**
 * sfAsset replace form.
 *
 * @package    symfony
 * @subpackage form
 * @author     Massimiliano Arione <garakkio@gmail.com>
 */
class sfAssetReplaceForm extends BasesfAssetForm
{
  public function configure()
  {
    // new file
    $this->widgetSchema['file'] = new sfWidgetFormInputFile();
    $this->validatorSchema['file'] = new sfValidatorFile();

    // remove unneeded fields
    unset($this['folder_id'], $this['filename'], $this['description'], $this['author'],
          $this['copyright'], $this['type'], $this['filesize'], $this['created_at']);

    // avoid id conflict for id
    $this->widgetSchema['id']->setIdFormat('replace_%s');
  }
}

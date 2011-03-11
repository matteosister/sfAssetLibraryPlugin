<?php

/**
 * sfAsset multiple form.
 *
 * @package    symfony
 * @subpackage form
 * @author     Massimiliano Arione <garakkio@gmail.com>
 */
class sfAssetsForm extends BasesfAssetForm
{
  public function configure()
  {
    // add file inputs
    for ($i = 1; $i <= $this->options['size']; $i ++)
    {
      $this->widgetSchema['file_' . $i] = new sfWidgetFormInputFile();
      $this->validatorSchema['file_' . $i] = new sfValidatorFile(array('required' => false));
    }

    // remove unneeded fields
    unset($this['filename'], $this['description'], $this['author'], $this['copyright'],
          $this['type'], $this['filesize'], $this['created_at'], $this['updated_at']);

    // formatter (see sfWidgetFormSchemaFormatterAsset.class.php)
    $this->widgetSchema->setFormFormatterName('assets');
  }
}

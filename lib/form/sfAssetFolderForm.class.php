<?php

/**
 * sfAssetFolder form.
 *
 * @package    symfony
 * @subpackage form
 * @author     Massimiliano Arione <garakkio@gmail.com>
 */
class sfAssetFolderForm extends BasesfAssetFolderForm
{
  public function configure()
  {
    // hide some fields
    unset($this['tree_left'], $this['tree_right'], $this['relative_path'],
          $this['created_at'], $this['updated_at']);

    // add hidden parent folder
    $this->widgetSchema['parent_folder'] = new sfWidgetFormInputHidden();
    if (!empty($this->options['parent_id']))
    {
      $this->setDefault('parent_folder', $this->options['parent_id']);
    }
    $this->validatorSchema['parent_folder'] = new sfValidatorPropelChoice(array('model' => 'sfAssetFolder',
                                                                                'column' => 'id',
                                                                                'required' => true));

    // avoid id conflict for name and parent_folder
    $this->widgetSchema['name']->setIdFormat('create_%s');
    $this->widgetSchema['parent_folder']->setIdFormat('create_%s');

    // check for correct name
    $this->validatorSchema['name'] = new sfValidatorRegex(array('pattern' => '/^[a-zA-Z0-9\-\_\.]+$/'));
  }
}

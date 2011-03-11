<?php

/**
 * sfWidgetFormAssetInput
 *
 * @package    symfony
 * @subpackage widget
 * @author     Massimiliano Arione <garakkio@gmail.com>
 */
class sfWidgetFormAssetInput extends sfWidgetFormInput
{
  /**
   * Constructor.
   *
   * Available options:
   *
   *  * asset_type: The asset type ('all' for all types)
   *  * form_name: The form name (javascript based by default)
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   * @see sfWidgetFormInput
   */
  protected function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);
    $this->addOption('preview_type', 'small');
    $this->addOption('asset_type', 'image');
    $this->addOption('form_name', 'this.previousSibling.previousSibling.form.name');
  }

  /**
   * @param  string $name        The element name
   * @param  string $value       The value displayed in this widget
   * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
   * @param  array  $errors      An array of errors for the field
   *
   * @return string An HTML tag string
   *
   * @see sfWidgetFormInput
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $folder = 'media';
    if ($value != null) {
      $asset = sfAssetPeer::retrieveByPK($value);
      $folder = $asset->getFolderPath();
    }

    // IMMAGINE DI PREVIEW
    $className = 'asset-widget-img-preview '.$this->generateId($name);
    if ($value == null) {
      $preview_image = image_tag('/images/backend/no-foto.jpg', array('class' => $className));
    } else {
      $preview_image = asset_image_tag(sfAssetPeer::retrieveByPK($value), $this->getOption('preview_type'), array('class' => $className));
    }
    
    return parent::render($name, $value, $attributes, $errors) . '&nbsp;' .
      image_tag('/images/backend/image_edit.png', array(
        'alt' => __('Insert Image'),
        'style' => 'cursor: pointer; vertical-align: middle',
        'onclick' => "
          initialDir = '".$folder."';
          if(!initialDir) initialDir = '".sfConfig::get('app_sfAssetsLibrary_upload_dir', 'media')."';
          sfAssetsLibrary.openWindow({
            form_name: ".$this->getOption('form_name').",
            field_name: '".$name."',
            type: '".$this->getOption('asset_type')."',
            url: '".url_for('@sf_asset_library_list?dir=PLACEHOLDER')."&input_id=" . $this->generateId($name) . "&preview_type=".$this->getOption('preview_type')."&popup=2'.replace('PLACEHOLDER', initialDir),
            scrollbars: 'yes'
          });"
      )) . '&nbsp;' . $preview_image;

      // image_tag($value == null ? '/images/backend/no-foto.jpg' : sfAssetPeer::retrieveByPK($value)->getUrl('small'), array('class' => 'asset-widget-img-preview '.$this->generateId($name)))
  }

  /**
   * Gets the JavaScript paths associated with the widget.
   *
   * @return array An array of JavaScript paths
   */
  public function getJavascripts()
  {
    return array('/sfAssetsLibraryPlugin/js/main', '/sfAssetsLibraryPlugin/js/asset_library_enhanced');
  }

}

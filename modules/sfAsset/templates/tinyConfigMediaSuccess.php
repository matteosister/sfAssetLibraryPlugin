<?php use_helper('JavascriptBase', 'I18N', 'sfAsset') ?>
<p><?php echo button_to_function(__('Back to the list', null, 'sfAsset'), 'history.back()') ?></p>

<script src="/js/tiny_mce/tiny_mce_popup.js"></script>
<script src="/js/tiny_mce/plugins/sfAssetsLibrary/jscripts/sfAssetsLibrary.js"></script>
<form action="" method="post" id="tinyMCE_insert_form">
  <fieldset>
    <?php echo asset_image_tag($sf_asset, 'large', array('class' => 'thumb')) ?>

    <div class="form-row">
      <label><?php echo __('Filename', null, 'sfAsset'); ?></label>
      <div class=""><?php echo $sf_asset->getUrl() ?></div>
    </div>

    <?php echo $form /* TODO add javascript events */ ?>

    </fieldset>

    <ul class="sf_admin_actions">
      <li>
        <?php echo button_to_function(__('Insert', null, 'sfAsset'),
         "insertAction(
           '".$sf_asset->getUrl()."',
           $('alt".$sf_asset->getId()."').value,
           $('border".$sf_asset->getId()."').checked,
           $('legend".$sf_asset->getId()."').checked,
           $('description".$sf_asset->getId()."').value,
           $('align".$sf_asset->getId()."').value,
           $('thumbnails".$sf_asset->getId()."').selectedIndex,
           $('width".$sf_asset->getId()."').value
          )",'class=sf_admin_action_save') ?>
      </li>
    </ul>
  </fieldset>
</form>

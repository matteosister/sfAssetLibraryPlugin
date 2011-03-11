<?php use_helper('JavascriptBase') ?>
<div class="form-row">
  <?php echo image_tag('/sfAssetsLibraryPlugin/images/magnifier.png', 'alt=search align=top') ?>
  <?php echo link_to_function(
    __('Search', null, 'sfAsset'),
    'document.getElementById("sf_asset_search").style.display="block"'
  ) ?>
</div>

<form action="<?php echo url_for('@sf_asset_library_search') ?>" method="get" id="sf_asset_search" style="display:none">

  <?php echo $form ?>

  <ul class="sf_admin_actions">
    <li>
      <input type="submit" value="<?php echo __('Search', null, 'sfAsset') ?>" name="search" class="sf_admin_action_filter" />
    </li>
  </ul>

</form>
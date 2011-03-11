<?php use_helper('sfAsset') ?>
<div class="form-row">
  <?php $sort = $sf_user->getAttribute('sort', 'name', 'sf_admin/sf_asset/sort') ?>
  <label>
    <?php echo image_tag('/sfAssetsLibraryPlugin/images/text_linespacing.png', 'alt=sort align=top') ?>
    <?php if ($sort == 'name'): ?>
      <?php echo __('Sorted by name', null, 'sfAsset') ?>
      <?php /* TODO check action calmelCase */ echo link_to_asset(__('Sort by date', null, 'sfAsset'), '@sf_asset_library_'.$sf_params->get('action').'?dir='.$sf_params->get('dir'), array('query_string' => 'sort=date')) ?>
    <?php else: ?>
      <?php echo __('Sorted by date', null, 'sfAsset') ?>
      <?php echo link_to_asset(__('Sort by name', null, 'sfAsset'), '@sf_asset_library_'.$sf_params->get('action').'?dir='.$sf_params->get('dir'), array('query_string' => 'sort=name')) ?>
    <?php endif ?>
  </label>
</div>
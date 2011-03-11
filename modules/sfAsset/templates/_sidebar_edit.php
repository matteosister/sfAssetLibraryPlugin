<?php use_helper('JavascriptBase', 'sfAsset') ?>

<?php if (!$sf_asset->isNew()): ?>
  <div id="thumbnail">
    <a href="<?php echo $sf_asset->getUrl('full') ?>"><?php echo asset_image_tag($sf_asset, 'large', array('title' => __('See full-size version', null, 'sfAsset')), null) ?></a>
  </div>
  <p><?php echo auto_wrap_text($sf_asset->getFilename()) ?></p>
  <p><?php echo __('%weight% Kb', array('%weight%' => $sf_asset->getFilesize()), 'sfAsset') ?></p>
  <p><?php echo __('Created on %date%', array('%date%' => format_date($sf_asset->getCreatedAt('U'))), 'sfAsset') ?></p>

  <form action="<?php echo url_for('@sf_asset_library_rename_asset') ?>" method="post">
  <?php echo $renameform['id'] ?>
  <div class="form-row">
    <label for="new_name">
      <?php echo image_tag('/sfAssetsLibraryPlugin/images/page_edit.png', 'align=top alt="edit') ?>
      <?php echo link_to_function(__('Rename', null, 'sfAsset'), 'document.getElementById("input_new_name").style.display="block";document.getElementById("sf_asset_filename").focus()') ?>
    </label>
    <div class="content" id="input_new_name" style="display:none">
      <?php echo $renameform['filename'] ?>
      <?php echo $renameform->renderHiddenFields() ?>
      <input type="submit" value="<?php echo __('Ok', null, 'sfAsset') ?>" />
    </div>
  </div>
  </form>

  <form action="<?php echo url_for('@sf_asset_library_move_asset') ?>" method="post">
  <?php echo $moveform['id'] ?>
  <div class="form-row">
    <label for="move_folder">
      <?php echo image_tag('/sfAssetsLibraryPlugin/images/page_go.png', 'alt=go align=top') ?>
      <?php echo link_to_function(__('Move', null, 'sfAsset'), 'document.getElementById("input_move_folder").style.display="block"') ?>
    </label>
    <div class="content" id="input_move_folder" style="display:none">
      <?php echo $moveform['parent_folder'] ?>
      <?php echo $moveform->renderHiddenFields() ?>
      <input type="submit" value="<?php echo __('Ok', null, 'sfAsset') ?>" />
    </div>
  </div>
  </form>

  <form action="<?php echo url_for('@sf_asset_library_replace_asset') ?>" method="post" enctype="multipart/form-data">
  <?php echo $replaceform['id'] ?>
  <div class="form-row">
    <label for="new_file">
      <?php echo image_tag('/sfAssetsLibraryPlugin/images/page_refresh.png', 'alt=refresh align=top') ?>
      <?php echo link_to_function(__('Replace', null, 'sfAsset'), 'document.getElementById("input_new_file").style.display="block"') ?>
    </label>
    <div class="content" id="input_new_file" style="display:none">
      <?php echo $replaceform['file']->render(array('size' => 10)) ?>
      <?php echo $replaceform->renderHiddenFields() ?>
      <input type="submit" value="<?php echo __('Ok', null, 'sfAsset') ?>" />
    </div>
  </div>
  </form>

  <div class="form-row">
    <?php echo image_tag('/sfAssetsLibraryPlugin/images/page_delete.png', 'alt=delete align=top') ?>
    <?php echo link_to(__('Delete', null, 'sfAsset'), '@sf_asset_library_delete_asset?id='.$sf_asset->getId(), array(
      'post' => true,
      'confirm' => __('Are you sure?', null, 'sfAsset'),
    )) ?>
  </div>

<?php endif ?>

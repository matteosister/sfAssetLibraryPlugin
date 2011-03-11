<?php use_helper('JavascriptBase', 'sfAsset') ?>

<?php if ($folder->isRoot()): ?>
<div class="form-row">
  <?php echo image_tag('/sfAssetsLibraryPlugin/images/images.png', 'alt=images align=top') ?>
  <?php echo link_to(__('Mass upload', null, 'sfAsset'), '@sf_asset_library_mass_upload') ?>
</div>
<?php endif ?>

<form action="<?php echo url_for('@sf_asset_library_add_quick') ?>" method="post" enctype="multipart/form-data">
<div class="form-row">
  <label for="new_file">
    <?php echo image_tag('/sfAssetsLibraryPlugin/images/image_add.png', 'alt=add align=top') ?>
    <?php echo link_to_function(__('Upload a file here', null, 'sfAsset'), 'document.getElementById("input_new_file").style.display="block"') ?>
  </label>
  <div class="content" id="input_new_file" style="display:none">
    <?php echo $fileform['file'] ?>
    <input type="submit" value="<?php echo __('Add', null, 'sfAsset') ?>" />
  </div>
</div>
<?php echo $fileform->renderHiddenFields() ?>
</form>

<form action="<?php echo url_for('@sf_asset_library_create_folder') ?>" method="post">
<div class="form-row">
  <label for="new_directory">
    <?php echo image_tag('/sfAssetsLibraryPlugin/images/folder_add.png', 'alt=add align=top') ?>
    <?php echo link_to_function(__('Add a subfolder', null, 'sfAsset'), 'document.getElementById("input_new_directory").style.display="block"') ?>
  </label>
  <div class="content" id="input_new_directory" style="display:none">
    <?php echo $folderform['name'] ?>
    <input type="submit" value="<?php echo __('Create', null, 'sfAsset') ?>" />
  </div>
</div>
<?php echo $folderform->renderHiddenFields() ?>
</form>

<?php if (!$folder->isRoot()): ?>
<form action="<?php echo url_for('@sf_asset_library_rename_folder') ?>" method="post">
<?php echo $renameform['id'] ?>
<div class="form-row">
  <label for="new_folder">
    <?php echo image_tag('/sfAssetsLibraryPlugin/images/folder_edit.png', 'alt=edit align=top') ?>
    <?php echo link_to_function(__('Rename folder', null, 'sfAsset'), 'document.getElementById("input_new_name").style.display="block";document.getElementById("sf_asset_folder_name").focus()') ?>
  </label>
  <div class="content" id="input_new_name" style="display:none">
    <?php echo $renameform['name'] ?>
    <input type="submit" value="<?php echo __('Ok', null, 'sfAsset') ?>" />
  </div>
</div>
<?php echo $renameform->renderHiddenFields() ?>
</form>

<form action="<?php echo url_for('@sf_asset_library_move_folder') ?>" method="post">
<?php echo $moveform['id'] ?>
<div class="form-row">
  <label for="new_folder">
    <?php echo image_tag('/sfAssetsLibraryPlugin/images/folder_go.png', 'alt=go align=top') ?>
    <?php echo link_to_function(__('Move folder', null, 'sfAsset'), 'document.getElementById("input_move_folder").style.display="block"') ?>
  </label>
  <div class="content" id="input_move_folder" style="display:none">
    <?php echo $moveform['parent_folder'] ?>
    <input type="submit" value="<?php echo __('Ok', null, 'sfAsset') ?>" />
  </div>
</div>
<?php echo $moveform->renderHiddenFields() ?>
</form>

<div class="form-row">
  <?php echo image_tag('/sfAssetsLibraryPlugin/images/folder_delete.png', 'alt=delete align=top') ?>
  <?php echo link_to(__('Delete folder', null, 'sfAsset'), '@sf_asset_library_delete_folder?id='.$folder->getId(), array(
    'post' => true,
    'confirm' => __('Are you sure?', null, 'sfAsset'),
  )) ?>
</div>
<?php endif ?>

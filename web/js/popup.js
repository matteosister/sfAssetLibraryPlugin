function setImageField(url, id)
{
  var win = null;
  if (typeof tinyMCEPopup != 'undefined')
  {
    var win = tinyMCEPopup.getWindowArg('window');
  }

  if (win)
  {
    if (tinyMCEPopup.getWindowArg("type") == 'image' && win.ImageDialog.showPreviewImage)
    {
      win.ImageDialog.showPreviewImage(url);
    }

    win.document.getElementById(tinyMCEPopup.getWindowArg('input')).value = url;
    tinyMCEPopup.close();
  }
  else
  {
    if (!opener)
    {
      opener = window.opener;
    }
    opener.sfAssetsLibrary.fileBrowserReturn(url, id);
    window.close();
  }
}

function setImageFieldPopup(url, id, input_id, preview_type)
{
  var win = null;
  if (typeof tinyMCEPopup != 'undefined')
  {
    var win = tinyMCEPopup.getWindowArg('window');
  }

  if (win)
  {
    win.document.getElementById(tinyMCEPopup.getWindowArg('input')).value = url;
    if (typeof(win.ImageDialog) != "undefined") {
      // we are, so update image dimensions...
      if (win.ImageDialog.getImageData)
          win.ImageDialog.getImageData();

      // ... and preview if necessary
      if (win.ImageDialog.showPreviewImage)
          win.ImageDialog.showPreviewImage(url);
    }

    tinyMCEPopup.close();
  }
  else
  {
    if (!opener)
    {
      opener = window.opener;
    }
    opener.sfAssetsLibrary.fileBrowserReturn(url, id);
    opener.changePreviewFoto(url, input_id);
    window.close();
  }
}

function addImageField(url, id)
{
  if (!opener)
  {
    opener = window.opener;
  }
  opener.sfAssetsLibrary.fileBrowserAdd(url, id);
}

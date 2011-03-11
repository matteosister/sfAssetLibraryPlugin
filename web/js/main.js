var sfAssetsLibrary_Engine = function(){};

sfAssetsLibrary_Engine.prototype = {
  init : function(url)
  {
    this.url = url;
  },

  fileBrowserReturn : function (url, id)
  {
    if(this.isTinyMCE)
    {
      tinyMCE.setWindowArg('editor_id', this.fileBrowserWindowArg);
      if (this.fileBrowserType == 'image')
      {
        this.callerWin.showPreviewImage(url);
      }
    }
    this.callerWin.document.forms[this.callerFormName].elements[this.callerFieldName].value = id;
    var img = this.callerWin.document.getElementById(this.callerFieldName + '_img');
    if (img)
    {
      img.src = url;
    }
  },

  // tentativo di aggiunta multipla, non funziona perche' non si possono
  // inserire elementi del dom in una finestra diversa da quella corrente :-|
  fileBrowserAdd : function (url, id)
  {
    var ul = this.callerWin.document.getElementById('multiassets');
    if (!ul)
    {
      return;
    }
    var li = this.callerWin.document.createElement('li');
    var img = this.callerWin.document.createElement('img');
    img.setAttribute('src', url);
    li.appendChild(img);
    ul.appendChild(ul);
  },

  fileBrowserCallBack : function (field_name, url, type, win)
  {
    tinyMCE.activeEditor.windowManager.open({
      file :      type == 'image' ? this.url + '/images_only/1/tiny' : this.url + '/tiny',
      title:      'Assets',
      width :     550,
      height :    600,
      inline:     'yes',
      resizable : 'yes',
      scrollbars: 'yes'
    },
    {
      input:      field_name,
      type:       type,
      window:     win
    });

    return false;
  },

  openWindow : function(options)
  {
    var width, height, x, y, resizable, scrollbars, url;

    if (!options) return;
    if (!options['field_name']) return;
    if (options['url'])
    {
      this.url = options['url'];
    }
    else if (!this.url)
    {
      return;
    }
    this.callerWin = self;
    this.callerFormName = (options['form_name'] == '') ? 0 : options['form_name'];
    this.callerFieldName = options['field_name'];
    this.fileBrowserType = options['type'];
    url = this.url;

    if (options['type'] == 'image') url += '/images_only/1';
    if (!(width = parseInt(options['width']))) width = 1000;
    if (!(height = parseInt(options['height']))) height = 600;

    // Add to height in M$ due to SP2 WHY DON'T YOU GUYS IMPLEMENT innerWidth of windows!!
    if (sfAssetsLibrary.isMSIE)
      height += 40;
    else
      height += 20;

    x = parseInt(screen.width / 2.0) - (width / 2.0);
    y = parseInt(screen.height / 2.0) - (height / 2.0);

    resizable = (options && options['resizable']) ? options['resizable'] : "no";
    scrollbars = (options && options['scrollbars']) ? options['scrollbars'] : "no";

    var modal = (resizable == "yes") ? "no" : "yes";

    if (sfAssetsLibrary.isGecko && sfAssetsLibrary.isMac) modal = "no";

    if (options['close_previous'] != "no") try {sfAssetsLibrary.lastWindow.close();} catch (ex) {}

    var win = window.open(url, "sfPopup" + new Date().getTime(), "top=" + y + ",left=" + x + ",scrollbars=" + scrollbars + ",dialog=" + modal + ",minimizable=" + resizable + ",modal=" + modal + ", width=1000, height=600,resizable=" + resizable);
    this.fileBrowserWin = win;
    if (options['close_previous'] != "no") sfAssetsLibrary.lastWindow = win;

    win.focus();
  }
}

var sfAssetsLibrary = new sfAssetsLibrary_Engine();

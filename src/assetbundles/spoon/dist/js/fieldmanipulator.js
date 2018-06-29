/**
 * @author    Supercool Ltd <josh@supercooldesign.co.uk>
 * @copyright Copyright (c) 2015, Supercool Ltd
 * @see       http://supercooldesign.co.uk
 */

(function($){


if (typeof PimpMyMatrix == 'undefined')
{
  PimpMyMatrix = {};
}


/**
 * Overrides the default Matrix ‘add block’ buttons with our grouped ones
 * and keeps them up to date based on the current context.
 *
 * Also adds any field layouts that may exist for each block type
 * in the current context.
 */
PimpMyMatrix.FieldManipulator = Garnish.Base.extend(
{

  $matrixContainer: null,

  init: function(settings)
  {

    // Set up
    this.setSettings(settings, PimpMyMatrix.FieldManipulator.defaults);
    this.refreshMatrixContainers();

    // Work out if we’re in the 'entrytype' context so we can keep things up to date
    if (this.settings.context.split(':')[0] === 'entrytype')
    {
      // Thanks mmikkel: http://craftcms.stackexchange.com/a/9466/144
      this.addListener(Garnish.$doc, 'ajaxComplete', function(ev, status, requestData)
      {
        if ( requestData.url.indexOf( 'switchEntryType' ) > -1 )
        {
          this.settings.context = 'entrytype:' + $('#entryType').val();
          this.processMatrixFields();
        }
      });
    }

    // Wait until load to loop the Matrix fields
    this.addListener(Garnish.$win, 'load', 'processMatrixFields');

    // Debounced resize event
    this.addListener(Garnish.$win, 'resize', $.proxy(function()
    {
      if (this.resizeTimeout)
      {
        clearTimeout(this.resizeTimeout);
      }

      this.resizeTimeout = setTimeout($.proxy(this, 'processMatrixFields'), 25);
    }, this));

  },

  // Update our copy of all the Matrix containers
  refreshMatrixContainers: function()
  {
    this.$matrixContainer = Garnish.$doc.find('.matrix-field');
  },

  processMatrixFields: function()
  {

    this.refreshMatrixContainers();

    var _this = this;

    // loop each matrix field
    this.$matrixContainer.each(function()
    {

      var $matrixField = $(this);

      // sort out the button groups
      _this.initBlockTypeGroups($matrixField);

      // initialize the blocks
      $matrixField.find('.blocks > .matrixblock').each(function()
      {
        _this.initBlocks($(this), $matrixField);
      });

    });

  },

  initBlockTypeGroups: function($matrixField)
  {

    // check if we’ve already pimped this field
    if ( !$matrixField.data('pimped') )
    {

      // get matrix field handle out of the dom
      var matrixFieldHandle = this._getMatrixFieldName($matrixField);

      // Filter by the current matrix field
      var pimpedBlockTypes = [];

      // Check current context first
      if (typeof this.settings.blockTypes[this.settings.context] !== "undefined")
      {
        pimpedBlockTypes = $.grep(this.settings.blockTypes[this.settings.context], function(e){ return e.fieldHandle === matrixFieldHandle; });
      }

      // Check global context
      if (pimpedBlockTypes.length < 1 && typeof this.settings.blockTypes['global'] !== "undefined")
      {
        pimpedBlockTypes = $.grep(this.settings.blockTypes['global'], function(e){ return e.fieldHandle === matrixFieldHandle; });
      }

      // Check we have some config
      if ( pimpedBlockTypes.length >= 1 )
      {

        // add some data to tell us we’re pimped
        $matrixField.data('pimped', true);

        // store the data for when we loop the blocks themselves so we don’t have to run all this again
        $matrixField.data('pimpmymatrix-block-types', pimpedBlockTypes);

        // find the original buttons
        var $origButtons = $matrixField.find('> .buttons').first();

        // hide the original ones and start the button pimping process
        $origButtons.addClass('hidden');

        // make our own container, not using .buttons as it gets event bindings
        // from MatrixInput.js that we really don't want
        var $pimpedButtonsContainer = $('<div class="buttons-pimped" />').insertAfter($origButtons);

        // the main button group
        var $mainButtons = $('<div class="btngroup" />').appendTo($pimpedButtonsContainer);

        // the secondary one, used when the container gets too small
        var $secondaryButtons = $('<div class="btn add icon menubtn hidden">'+Craft.t('Add a block')+'</div>').appendTo($pimpedButtonsContainer),
            $secondaryMenu = $('<div class="menu pimpmymatrix-secondary-menu" />').appendTo($pimpedButtonsContainer);

        // loop each block type config
        for (var i = 0; i < pimpedBlockTypes.length; i++)
        {

          // check if group exists, add if not
          if ( $mainButtons.find('[data-pimped-group="'+pimpedBlockTypes[i].groupName+'"]').length === 0 )
          {
            // main buttons
            var $mainMenuBtn = $('<div class="btn  menubtn">'+pimpedBlockTypes[i]['groupName']+'</div>').appendTo($mainButtons),
                $mainMenu = $('<div class="menu" data-pimped-group="'+pimpedBlockTypes[i]['groupName']+'" />').appendTo($mainButtons),
                $mainUl = $('<ul />').appendTo($mainMenu);

            // single group buttons
            if (i!==0)
            {
              $('<hr>').appendTo($secondaryMenu);
            }
            $('<h6>'+pimpedBlockTypes[i]['groupName']+'</h6>').appendTo($secondaryMenu);
            var $secondaryUl = $('<ul/>').appendTo($secondaryMenu);
          }

          // make a link
          $li = $('<li><a data-type="'+pimpedBlockTypes[i].matrixBlockType.handle+'">'+pimpedBlockTypes[i].matrixBlockType.name+'</a></li>');

          // add it to the main list
          $li.appendTo($mainUl);

          // add a copy to the secondary one as well
          $li.clone().appendTo($secondaryUl);

        }

        // make the MenuBtns work
        $mainButtons.find('.menubtn').each(function()
        {

          new Garnish.MenuBtn($(this),
          {
            onOptionSelect: function(option)
            {
              // find our type and click the correct original btn!
              var type = $(option).data('type');
              $origButtons.find('[data-type="'+type+'"]').trigger('click');
            }
          });

        });

        new Garnish.MenuBtn($secondaryButtons,
        {
          onOptionSelect: function(option)
          {
            // find our type and click the correct original btn!
            var type = $(option).data('type');
            $origButtons.find('[data-type="'+type+'"]').trigger('click');
          }
        });

        // Bind a resize to the $matrixField so we can work out which groups UI to show
        this.addListener($matrixField, 'resize', $.proxy(function()
        {
          // Do we know what the button group width is yet?
          if (!$matrixField.data('pimpmymatrix-main-buttons-width'))
          {
            $matrixField.data('pimpmymatrix-main-buttons-width', $mainButtons.width());

            if (!$matrixField.data('pimpmymatrix-main-buttons-width'))
            {
              return;
            }
          }

          // Check the widths and do the hide/show
          var fieldWidth = $matrixField.width(),
              mainButtonsWidth = $matrixField.data('pimpmymatrix-main-buttons-width');
          if (fieldWidth < mainButtonsWidth)
          {
            $secondaryButtons.removeClass('hidden');
            $mainButtons.addClass('hidden');
          }
          else
          {
            $secondaryButtons.addClass('hidden');
            $mainButtons.removeClass('hidden');
          }

        }, this));

      }

    }

  },

  initBlocks: function($matrixBlock, $matrixField)
  {

    if ( !$matrixBlock.data('pimped') )
    {

      // Set this so we don’t re-run this
      $matrixBlock.data('pimped', true);

      // Get the cached pimped block types data for this whole field
      var pimpedBlockTypes = $matrixField.data('pimpmymatrix-block-types');

      // Check we have some config
      if ( typeof pimpedBlockTypes !== "undefined" && pimpedBlockTypes.length >= 1 )
      {

        // First, sort out the settings menu
        var $settingsBtn = $matrixBlock.find('.actions .settings.menubtn');
        this.initSettingsMenu($settingsBtn, pimpedBlockTypes);

        // Second, get the current block’s type out of the dom so we can do the field layout
        var matrixBlockTypeHandle = this._getMatrixBlockTypeHandle($matrixBlock);

        // Further filter our pimpedBlockTypes array by the current block’s type
        var pimpedBlockType = $.grep(pimpedBlockTypes, function(e){ return e.matrixBlockType.handle === matrixBlockTypeHandle; });

        // Initialize the field layout on the block
        if ( pimpedBlockType.length === 1 && pimpedBlockType[0].fieldLayoutId !== null )
        {
          $matrixBlock.data('pimped-block-type', pimpedBlockType[0]);
          this.initBlockFieldLayout($matrixBlock, $matrixField);
        }
        // If that failed, do another check against the global context
        else
        {
          var matrixFieldHandle = this._getMatrixFieldName($matrixField);
          pimpedBlockTypes = $.grep(this.settings.blockTypes['global'], function(e){ return e.fieldHandle === matrixFieldHandle; });

          if ( pimpedBlockTypes.length >= 1 )
          {
            pimpedBlockType = $.grep(pimpedBlockTypes, function(e){ return e.matrixBlockType.handle === matrixBlockTypeHandle; });

            if ( pimpedBlockType.length === 1 && pimpedBlockType[0].fieldLayoutId !== null )
            {
              $matrixBlock.data('pimped-block-type', pimpedBlockType[0]);
              this.initBlockFieldLayout($matrixBlock, $matrixField);
            }
            else
            {
              $matrixBlock.addClass('matrixblock-not-pimped');
            }
          }
          else
          {
            $matrixBlock.addClass('matrixblock-not-pimped');
          }
        }

      }
      else
      {
        $matrixBlock.addClass('matrixblock-not-pimped');
      }

    }
    else
    {
      // Fixes Redactor
      Garnish.$doc.trigger('scroll');
    }

  },

  initBlockFieldLayout: function($matrixBlock, $matrixField)
  {

    var pimpedBlockType = $matrixBlock.data('pimped-block-type'),
        tabs = pimpedBlockType.fieldLayout.tabs,
        fields = pimpedBlockType.fieldLayout.fields;

    // Check we have some tabs
    // TODO: would be nice to not show the tab nav if there is only one tab
    if ( tabs.length >= 1 )
    {
      // Add a class so we can style
      $matrixBlock.addClass('matrixblock-pimped');

      // Get a namespaced id
      var namespace = $matrixField.prop('id') + '-' + $matrixBlock.data('id'),
          pimpedNamespace = 'pimpmymatrix-' + namespace;

      // Add the tabs container
      var $tabs = $('<ul class="pimpmymatrix-tabs"/>').appendTo($matrixBlock);

      // Make our own fields container and hide the native one, but keep its height
      var $pimpedFields = $('<div class="pimpmymatrix-fields"/>').css({ 'opacity' : 0 }).appendTo($matrixBlock),
          $fields = $matrixBlock.find('.fields');
      $fields.css({ 'opacity' : 0 });

      // Wait a bit for the add block animation to finish
      setTimeout($.proxy(function()
      {

        // Loop the tabs
        for (var i = 0; i < tabs.length; i++)
        {

          // Set up the first one to be active
          var navClasses = '',
              paneClasses = '';

          if (i==0)
          {
            navClasses = ' sel';
          }
          else
          {
            paneClasses = ' hidden';
          }

          // Add the tab nav, if there is more than one
          if (tabs.length > 1)
          {
            var $tabLi = $('<li/>').appendTo($tabs);
            $('<a id="'+pimpedNamespace+'-'+i+'" class="tab'+navClasses+'">'+tabs[i].name+'</a>')
              .appendTo($tabLi)
              .data('pimped-tab-target', '#'+pimpedNamespace+'-pane-'+i);
          }

          // Make a tab pane
          var $pane = $('<div id="'+pimpedNamespace+'-pane-'+i+'" class="'+paneClasses+'"/>').appendTo($pimpedFields);

          // Filter the fields array by their associated tabId and loop over them
          var tabFields = $.grep(fields, function(e){ return e.tabId === tabs[i].id; });
          for (var n = 0; n < tabFields.length; n++)
          {
            // Move the required field to our new container
            $fields.find('#' + namespace + '-fields-' + tabFields[n].field.handle + '-field').appendTo($pane);
          }

        }

        // Bind events to tab nav clicks
        if (tabs.length > 1)
        {
          this.addListener($tabs.find('a'), 'click', 'onTabClick');
        }

        // Force the fields to be removed from the layout
        $fields.hide();

        $pimpedFields.velocity({opacity: 1}, 'fast', $.proxy(function()
        {
          // Re-initialize the Craft UI
          Craft.initUiElements($pimpedFields);
        }, this));


      }, this), 110);

    }

  },

  onTabClick: function(ev)
  {

    ev.preventDefault();
    ev.stopPropagation();

    var $tab = $(ev.target),
        $tabNav = $tab.parent().parent('.pimpmymatrix-tabs'),
        targetSelector = $tab.data('pimped-tab-target'),
        $target = $(targetSelector);

    // Toggle tab nav state
    $tabNav.find('a.sel').removeClass('sel');
    $tab.addClass('sel');

    // Toggle the pane state
    $target.siblings('div').addClass('hidden');
    $target.removeClass('hidden');

  },

  initSettingsMenu: function($settingsBtn, pimpedBlockTypes)
  {
    setTimeout($.proxy(function()
    {
      // Get the Garnish.MenuBtn object
      var menuBtn = $settingsBtn.data('menubtn') || false;

      // If there wasn’t one then fail and try again
      if (!menuBtn)
      {
        this.initSettingsMenu($settingsBtn, pimpedBlockTypes);
        return;
      }

      // Get the actual menu out of it once we get this far
      var $menu = menuBtn.menu.$container;
      $menu.addClass('pimpmymatrix-settings-menu');

      // Hide all the li’s with add block links in them
      $menu.find('a[data-action="add"]').parents('li').addClass('hidden');

      // Remove all the padded classes on hr’s
      $menu.find('hr').removeClass('padded');

      // Get the correct ul to play with in the menu container
      var $origUl = $menu.find('a[data-action="add"]').parents('li').parent('ul');

      // Loop the given block type data and adjust the menu to match the groups
      for (var i = 0; i < pimpedBlockTypes.length; i++)
      {
        var handle = pimpedBlockTypes[i].matrixBlockType.handle;

        // Make a new group ul if needed
        if ( $menu.find('[data-pimped-group="'+pimpedBlockTypes[i].groupName+'"]').length === 0 )
        {
          var $newUl = $('<ul class="padded" data-pimped-group="'+pimpedBlockTypes[i].groupName+'" />');
          if (i!==0)
          {
            $('<hr/>').insertBefore($origUl);
          }
          $('<h6>'+pimpedBlockTypes[i].groupName+'</h6>').insertBefore($origUl);
          $newUl.insertBefore($origUl);
        }

        // Add the li
        var $li = $menu.find('a[data-type="'+handle+'"]').parents('li');
        $newUl.append($li);
        $li.removeClass('hidden');
      }

    }, this), 0);
  },

  /**
   * This simply returns a fieldHandle if it can get one or false if not
   */
  _getMatrixFieldName: function($matrixField)
  {
    var matrixFieldId = $matrixField.parents('.field').prop('id'),
        parts = matrixFieldId.split("-"),
        matrixFieldHandle = parts[parts.length-2];

    if ( matrixFieldHandle != '' )
    {
      return matrixFieldHandle;
    }
    else
    {
      return false;
    }
  },

  /**
   * Returns the block type handle for a given $matrixBlock
   */
  _getMatrixBlockTypeHandle: function($matrixBlock)
  {
    var blockTypeHandle = $matrixBlock.find('input[type="hidden"][name*="type"]').val();

    if ( typeof blockTypeHandle == 'string' )
    {
      return blockTypeHandle;
    }
    else
    {
      return false;
    }
  }

},
{
  defaults: {
    blockTypes: null,
    context: false
  }
});


})(jQuery);

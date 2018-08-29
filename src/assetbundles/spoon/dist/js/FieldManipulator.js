/**
 * Spoon plugin for Craft CMS
 *
 * @author    Angell & Co
 * @copyright Copyright (c) 2018 Angell & Co
 * @link      https://angell.io
 * @package   Spoon
 * @since     3.0.0
 */
(function($){


    if (typeof Spoon == 'undefined')
    {
        Spoon = {};
    }

    /**
     * Overrides the default Matrix ‘add block’ buttons with our grouped ones
     * and keeps them up to date based on the current context.
     *
     * Also adds any field layouts that may exist for each block type
     * in the current context.
     */
    Spoon.FieldManipulator = Garnish.Base.extend(
        {

            $matrixContainer: null,

            init: function(settings)
            {

                // Set up
                this.setSettings(settings, Spoon.FieldManipulator.defaults);
                this.refreshMatrixContainers();

                // Work out if we’re in the 'entrytype' context so we can keep things up to date
                if (this.settings.context.split(':')[0] === 'entrytype')
                {
                    // Listen to entry type switch
                    Garnish.on(Craft.EntryTypeSwitcher, 'typeChange', $.proxy(function(ev)
                    {
                        this.settings.context = 'entrytype:' + $('#entryType').val();
                        this.processMatrixFields();
                    }, this));
                }

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

                // check if we’ve already spooned this field
                if ( !$matrixField.data('spooned') )
                {

                    // get matrix field handle out of the dom
                    var matrixFieldHandle = this._getMatrixFieldName($matrixField);

                    // Filter by the current matrix field
                    var spoonedBlockTypes = [];

                    // Check current context first
                    if (typeof this.settings.blockTypes[this.settings.context] !== "undefined")
                    {
                        spoonedBlockTypes = $.grep(this.settings.blockTypes[this.settings.context], function(e){ return e.fieldHandle === matrixFieldHandle; });
                    }

                    // Check global context
                    if (spoonedBlockTypes.length < 1 && typeof this.settings.blockTypes['global'] !== "undefined")
                    {
                        spoonedBlockTypes = $.grep(this.settings.blockTypes['global'], function(e){ return e.fieldHandle === matrixFieldHandle; });
                    }

                    // Check we have some config
                    if ( spoonedBlockTypes.length >= 1 )
                    {

                        // add some data to tell us we’re spooned
                        $matrixField.data('spooned', true);

                        // store the data for when we loop the blocks themselves so we don’t have to run all this again
                        $matrixField.data('spoon-block-types', spoonedBlockTypes);

                        // find the original buttons
                        var $origButtons = $matrixField.find('> .buttons').first();

                        // hide the original ones and start the button spooning process
                        $origButtons.addClass('hidden');

                        // make our own container, not using .buttons as it gets event bindings
                        // from MatrixInput.js that we really don't want
                        var $spoonedButtonsContainer = $('<div class="buttons-spooned" />').insertAfter($origButtons);

                        // the main button group
                        var $mainButtons = $('<div class="btngroup" />').appendTo($spoonedButtonsContainer);

                        // the secondary one, used when the container gets too small
                        var $secondaryButtons = $('<div class="btn add icon menubtn hidden">'+Craft.t('app', 'Add a block')+'</div>').appendTo($spoonedButtonsContainer),
                            $secondaryMenu = $('<div class="menu spoon-secondary-menu" />').appendTo($spoonedButtonsContainer);

                        // loop each block type config
                        for (var i = 0; i < spoonedBlockTypes.length; i++)
                        {

                            // check if group exists, add if not
                            if ( $mainButtons.find('[data-spooned-group="'+spoonedBlockTypes[i].groupName+'"]').length === 0 )
                            {
                                // main buttons
                                var $mainMenuBtn = $('<div class="btn  menubtn">'+spoonedBlockTypes[i]['groupName']+'</div>').appendTo($mainButtons),
                                    $mainMenu = $('<div class="menu" data-spooned-group="'+spoonedBlockTypes[i]['groupName']+'" />').appendTo($mainButtons),
                                    $mainUl = $('<ul />').appendTo($mainMenu);

                                // single group buttons
                                if (i!==0)
                                {
                                    $('<hr>').appendTo($secondaryMenu);
                                }
                                $('<h6>'+spoonedBlockTypes[i]['groupName']+'</h6>').appendTo($secondaryMenu);
                                var $secondaryUl = $('<ul/>').appendTo($secondaryMenu);
                            }

                            // make a link
                            $li = $('<li><a data-type="'+spoonedBlockTypes[i].matrixBlockType.handle+'">'+spoonedBlockTypes[i].matrixBlockType.name+'</a></li>');

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
                            if (!$matrixField.data('spoon-main-buttons-width'))
                            {
                                $matrixField.data('spoon-main-buttons-width', $mainButtons.width());

                                if (!$matrixField.data('spoon-main-buttons-width'))
                                {
                                    return;
                                }
                            }

                            // Check the widths and do the hide/show
                            var fieldWidth = $matrixField.width(),
                                mainButtonsWidth = $matrixField.data('spoon-main-buttons-width');
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

                if ( !$matrixBlock.data('spooned') )
                {

                    // Set this so we don’t re-run this
                    $matrixBlock.data('spooned', true);

                    // Get the cached spooned block types data for this whole field
                    var spoonedBlockTypes = $matrixField.data('spoon-block-types');

                    // Check we have some config
                    if ( typeof spoonedBlockTypes !== "undefined" && spoonedBlockTypes.length >= 1 )
                    {

                        // First, sort out the settings menu
                        var $settingsBtn = $matrixBlock.find('.actions .settings.menubtn');
                        this.initSettingsMenu($settingsBtn, spoonedBlockTypes, $matrixField);

                        // Second, get the current block’s type out of the dom so we can do the field layout
                        var matrixBlockTypeHandle = this._getMatrixBlockTypeHandle($matrixBlock);

                        // Further filter our spoonedBlockTypes array by the current block’s type
                        var spoonedBlockType = $.grep(spoonedBlockTypes, function(e){ return e.matrixBlockType.handle === matrixBlockTypeHandle; });

                        // Initialize the field layout on the block
                        if ( spoonedBlockType.length === 1 && spoonedBlockType[0].fieldLayoutId !== null )
                        {
                            $matrixBlock.data('spooned-block-type', spoonedBlockType[0]);
                            this.initBlockFieldLayout($matrixBlock, $matrixField);
                        }
                        // If that failed, do another check against the global context
                        else if (this.settings.blockTypes.hasOwnProperty('global'))
                        {
                            var matrixFieldHandle = this._getMatrixFieldName($matrixField);
                            spoonedBlockTypes = $.grep(this.settings.blockTypes['global'], function(e){ return e.fieldHandle === matrixFieldHandle; });

                            if ( spoonedBlockTypes.length >= 1 )
                            {
                                spoonedBlockType = $.grep(spoonedBlockTypes, function(e){ return e.matrixBlockType.handle === matrixBlockTypeHandle; });

                                if ( spoonedBlockType.length === 1 && spoonedBlockType[0].fieldLayoutId !== null )
                                {
                                    $matrixBlock.data('spooned-block-type', spoonedBlockType[0]);
                                    this.initBlockFieldLayout($matrixBlock, $matrixField);
                                }
                                else
                                {
                                    $matrixBlock.addClass('matrixblock-not-spooned');
                                }
                            }
                            else
                            {
                                $matrixBlock.addClass('matrixblock-not-spooned');
                            }
                        }
                        else
                        {
                            $matrixBlock.addClass('matrixblock-not-spooned');
                        }

                    }
                    else
                    {
                        $matrixBlock.addClass('matrixblock-not-spooned');
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

                // console.log($matrixBlock.data('spooned-block-type'));
                // return;
                var spoonedBlockType = $matrixBlock.data('spooned-block-type'),
                    tabs = spoonedBlockType.fieldLayoutModel.tabs,
                    fields = spoonedBlockType.fieldLayoutModel.fields;

                // Check we have some tabs
                // TODO: would be nice to not show the tab nav if there is only one tab
                if ( tabs.length >= 1 )
                {
                    // Add a class so we can style
                    $matrixBlock.addClass('matrixblock-spooned');

                    // Get a namespaced id
                    var namespace = $matrixField.prop('id') + '-' + $matrixBlock.data('id'),
                        spoonedNamespace = 'spoon-' + namespace;

                    // Add the tabs container
                    var $tabs = $('<ul class="spoon-tabs"/>').appendTo($matrixBlock);

                    // Make our own fields container and hide the native one, but keep its height
                    var $spoonedFields = $('<div class="spoon-fields"/>').css({ 'opacity' : 0 }).appendTo($matrixBlock),
                        $fields = $matrixBlock.find('> .fields');
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
                                $('<a id="'+spoonedNamespace+'-'+i+'" class="tab'+navClasses+'">'+tabs[i].name+'</a>')
                                    .appendTo($tabLi)
                                    .data('spooned-tab-target', '#'+spoonedNamespace+'-pane-'+i);
                            }

                            // Make a tab pane
                            var $pane = $('<div id="'+spoonedNamespace+'-pane-'+i+'" class="'+paneClasses+'"/>').appendTo($spoonedFields);

                            // Filter the fields array by their associated tabId and loop over them
                            var tabFields = $.grep(fields, function(e){ return e.tabId === tabs[i].id; });
                            for (var n = 0; n < tabFields.length; n++)
                            {
                                // Move the required field to our new container
                                $fields.find('#' + namespace + '-fields-' + tabFields[n].handle + '-field').appendTo($pane);
                            }

                        }

                        // Bind events to tab nav clicks
                        if (tabs.length > 1)
                        {
                            this.addListener($tabs.find('a'), 'click', 'onTabClick');
                        }

                        // Force the fields to be removed from the layout
                        $fields.hide();

                        $spoonedFields.velocity({opacity: 1}, 'fast', $.proxy(function()
                        {
                            // Re-initialize the Craft UI
                            Craft.initUiElements($spoonedFields);
                        }, this));


                    }, this), 110);

                }

            },

            onTabClick: function(ev)
            {

                ev.preventDefault();
                ev.stopPropagation();

                var $tab = $(ev.target),
                    $tabNav = $tab.parent().parent('.spoon-tabs'),
                    targetSelector = $tab.data('spooned-tab-target'),
                    $target = $(targetSelector);

                // Toggle tab nav state
                $tabNav.find('a.sel').removeClass('sel');
                $tab.addClass('sel');

                // Toggle the pane state
                $target.siblings('div').addClass('hidden');
                $target.removeClass('hidden');

            },

            initSettingsMenu: function($settingsBtn, spoonedBlockTypes, $matrixField)
            {
                setTimeout($.proxy(function()
                {
                    // Get the Garnish.MenuBtn object
                    var menuBtn = $settingsBtn.data('menubtn') || false;

                    // If there wasn’t one then fail and try again
                    if (!menuBtn)
                    {
                        this.initSettingsMenu($settingsBtn, spoonedBlockTypes, $matrixField);
                        return;
                    }

                    // Get the field handle
                    var matrixFieldHandle = this._getMatrixFieldName($matrixField);

                    // Get the actual menu out of it once we get this far
                    var $menu = menuBtn.menu.$container;
                    $menu.addClass('spoon-settings-menu');

                    // Hide all the li’s with add block links in them
                    $menu.find('a[data-action="add"]').parents('li').addClass('hidden');

                    // Remove all the padded classes on hr’s
                    $menu.find('hr').removeClass('padded');

                    // Get the correct ul to play with in the menu container
                    var $origUl = $menu.find('a[data-action="add"]').parents('li').parent('ul');

                    // Loop the given block type data and adjust the menu to match the groups
                    for (var i = 0; i < spoonedBlockTypes.length; i++)
                    {
                        var handle = spoonedBlockTypes[i].matrixBlockType.handle;

                        // Make a new group ul if needed
                        if ( $menu.find('[data-spooned-group="'+spoonedBlockTypes[i].groupName+'"]').length === 0 )
                        {
                            var nestedSettingsHandles = $.grep(this.settings.nestedSettingsHandles, function(a){ return a === matrixFieldHandle; });
                            if (nestedSettingsHandles.length) {
                                var $newUl = $('<ul class="padded hidden" data-spooned-group="'+spoonedBlockTypes[i].groupName+'" />');
                                if (i!==0)
                                {
                                    $('<hr/>').insertBefore($origUl);
                                }

                                var $groupHeading = $('<a class="fieldtoggle">' + spoonedBlockTypes[i].groupName + '</a>');
                                $groupHeading.insertBefore($origUl);

                                $newUl.insertBefore($origUl);

                                this.addListener($groupHeading, 'click', function(event) {
                                    var $trigger = $(event.currentTarget),
                                        $target = $trigger.next('ul');

                                    if ($target.hasClass('hidden')) {
                                        $target.removeClass('hidden');
                                        $trigger.addClass('expanded');
                                    } else {
                                        $target.addClass('hidden');
                                        $trigger.removeClass('expanded');
                                    }
                                });
                            } else {
                                var $newUl = $('<ul class="padded" data-spooned-group="'+spoonedBlockTypes[i].groupName+'" />');
                                if (i!==0)
                                {
                                    $('<hr/>').insertBefore($origUl);
                                }
                                $('<h6>' + spoonedBlockTypes[i].groupName + '</h6>').insertBefore($origUl);
                                $newUl.insertBefore($origUl);
                            }

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
                context: false,
                nestedSettingsHandles: []
            }
        });


    // Load event has to be here otherwise Safari doesn’t see it
    Garnish.$win.on('load', function() {

        // Check if the Spoon.fieldmanipulator has been created or not yet
        if (typeof Spoon.fieldmanipulator == 'undefined') {
            setTimeout(function() {
                Spoon.fieldmanipulator.processMatrixFields();
            }, 250);
        } else {
            Spoon.fieldmanipulator.processMatrixFields();
        }

    });

})(jQuery);

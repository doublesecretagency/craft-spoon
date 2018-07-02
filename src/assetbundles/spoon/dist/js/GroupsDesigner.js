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


    Spoon.GroupsDesigner = Craft.FieldLayoutDesigner.extend(
        {

            $form: null,
            $spinner: null,

            modal: null,

            initField: function($blockType)
            {
                var $editBtn = $blockType.find('.settings'),
                    $menu = $('<div class="menu" data-align="center"/>').insertAfter($editBtn),
                    $ul = $('<ul/>').appendTo($menu);

                $('<li><a data-action="edit-field-layout">'+Craft.t('app', 'Edit field layout')+'</a></li>').appendTo($ul);

                $('<li><a data-action="remove">'+Craft.t('app', 'Remove')+'</a></li>').appendTo($ul);

                new Garnish.MenuBtn($editBtn, {
                    onOptionSelect: $.proxy(this, 'onFieldOptionSelect')
                });
            },

            onFieldOptionSelect: function(option)
            {
                var $option = $(option),
                    $blockType = $option.data('menu').$anchor.parent(),
                    action = $option.data('action');

                switch (action)
                {
                    case 'edit-field-layout':
                    {
                        this.onEditFieldLayout($blockType);
                        break;
                    }
                    case 'remove':
                    {
                        this.removeField($blockType);
                        break;
                    }
                }
            },

            // cloned for language adjustments
            renameTab: function($tab)
            {
                if (!this.settings.customizableTabs)
                {
                    return;
                }

                var $labelSpan = $tab.find('.tabs .tab span'),
                    oldName = $labelSpan.text(),
                    newName = prompt(Craft.t('app', 'Give your group a name.'), oldName);

                if (newName && newName != oldName)
                {
                    $labelSpan.text(newName);
                    $tab.find('.id-input').attr('name', this.getFieldInputName(newName));
                }
            },

            // cloned for language adjustments
            addTab: function()
            {
                if (!this.settings.customizableTabs)
                {
                    return;
                }

                var $tab = $('<div class="fld-tab">' +
                    '<div class="tabs">' +
                    '<div class="tab sel draggable">' +
                    '<span>Group '+(this.tabGrid.$items.length+1)+'</span>' +
                    '<a class="settings icon" title="'+Craft.t('app', 'Rename')+'"></a>' +
                    '</div>' +
                    '</div>' +
                    '<div class="fld-tabcontent"></div>' +
                    '</div>').appendTo(this.$tabContainer);

                this.tabGrid.addItems($tab);
                this.tabDrag.addItems($tab);

                this.initTab($tab);
            },

            /**
             * When trying to edit a field layout make sure the
             * block groups have saved first
             */
            onEditFieldLayout: function($blockType)
            {
                var $modalForm = $blockType.parents('.modal');
                $modalForm.one('blockTypesSaved', $.proxy(function()
                {
                    this.editFieldLayout($blockType);
                }, this));

                $modalForm.trigger('submit');
            },

            /**
             * This pops open another modal with another fld in it to enable
             * the editing of fields and tabs to happen inside the block type
             */
            editFieldLayout: function($blockType)
            {

                // Build the markup
                this.$form = $('<form class="modal elementselectormodal spoon-fields-configurator"/>');

                var $body = $('<div class="body"/>').appendTo(this.$form),
                    $body = $('<div class="content"/>').appendTo($body),
                    $bigSpinner = $('<div class="spinner big"/>').appendTo($body),
                    $body = $('<div class="main"/>').appendTo($body),
                    $footer = $('<div class="footer"/>').appendTo(this.$form),
                    $buttons = $('<div class="buttons right"/>').appendTo($footer);

                this.$spinner = $('<div class="spinner hidden"/>').appendTo($buttons);

                var $cancelBtn = $('<div class="btn">'+Craft.t('app', 'Cancel')+'</div>').appendTo($buttons),
                    $submitBtn = $('<input type="submit" class="btn submit" value="'+Craft.t('app', 'Save')+'"/>').appendTo($buttons);

                this.modal = new Garnish.Modal(this.$form,
                    {
                        resizable: true,
                        closeOtherModals: false,
                        onFadeIn: $.proxy(function()
                        {

                            var data = {
                                context : this.settings.context,
                                blockTypeId : $blockType.data('id')
                            };

                            Craft.postActionRequest('spoon/configurator/get-fields-html', data, $.proxy(function(response, textStatus)
                            {
                                if (textStatus == 'success')
                                {
                                    $(response.html).appendTo($body);
                                    $bigSpinner.addClass('hidden');
                                    var fld = new Spoon.BlockTypeFieldLayoutDesigner('#spoon-fields-configurator', {
                                        fieldInputName: 'blockTypeFieldLayouts[__TAB_NAME__][]'
                                    });
                                }
                            }, this));

                        }, this),
                        onHide: $.proxy(function()
                        {
                            this.modal.$container.remove();
                            this.modal.$shade.remove();
                            delete this;
                        }, this)
                    });

                this.addListener(this.$form, 'submit', '_handleSubmit');
                this.addListener($cancelBtn, 'click', $.proxy(function()
                {
                    this.modal.hide();
                }, this));
            },

            _handleSubmit: function(ev)
            {
                ev.preventDefault();

                // Show spinner
                this.$spinner.removeClass('hidden');

                // Get the form data
                var data = this.$form.serialize();

                // Post it
                Craft.postActionRequest('spoon/block-types/save-field-layout', data, $.proxy(function(response, textStatus)
                {
                    this.$spinner.addClass('hidden');
                    if (textStatus == 'success' && response.success)
                    {
                        Craft.cp.displayNotice(Craft.t('app', 'Field layout saved.'));
                        this.modal.hide();
                    }
                    else
                    {
                        if (textStatus == 'success')
                        {
                            Craft.cp.displayError(Craft.t('app', 'An unknown error occurred.'));
                        }
                    }
                }, this));
            }

        });


})(jQuery);

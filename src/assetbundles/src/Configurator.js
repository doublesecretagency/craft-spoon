/**
 * Spoon plugin for Craft CMS
 *
 * @copyright Copyright (c) 2018, 2022 Double Secret Agency
 * @link      https://plugins.doublesecretagency.com/
 * @package   Spoon
 * @since     3.0.0
 */
(function($){


    if (typeof Spoon == 'undefined')
    {
        Spoon = {};
    }


    /**
     * Adds itself to the settings menu of and Matrix field in a fld
     * and provides a fld interface for the configuration of the block
     * type groups and a further fld for each block type’s fields.
     */
    Spoon.Configurator = Garnish.Base.extend(
        {

            $container: null,
            fields: [],

            $form: null,
            $body: null,
            $bigSpinner: null,
            $spinner: null,

            modal: null,

            _handleCreateSettingsHudProxy: null,

            init: function(container, settings)
            {

                this.$container = $(container);
                this.setSettings(settings, Spoon.Configurator.defaults);

                if (this.settings.context === 'global')
                {
                    this.addListener(this.$container.find('.edit'), 'click', 'onFieldConfiguratorClick');
                    this.addListener(this.$container.find('.delete'), 'click', 'onRemoveConfiguration');
                }
                else
                {
                    this._handleCreateSettingsHudProxy = $.proxy(this, 'handleCreateSettingsHud');
                    Garnish.on(Craft.FieldLayoutDesigner.Element, 'createSettingsHud', this._handleCreateSettingsHudProxy);
                }

            },

            handleCreateSettingsHud: function(ev)
            {
                var field = ev.target.$container[0];
                var id = $(field).data('id');

                if (id && $.inArray(id.toString(), this.settings.matrixFieldIds) !== -1) {


                    var $btn = $('<button class="btn" type="button"><svg style="opacity: 0.5;margin-right: 6px;" width="9px" height="15px" viewBox="0 0 158 261" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><path d="M1.05077297,26.430506 C-0.198015952,31.6824241 -2.68717109,45.54842 10.3073398,57.2013671 C19.3864487,65.3432711 30.8183973,72.5365737 45.137792,75.931797 C55.7330869,78.4440631 67.39342,79.2790682 78.3456638,75.8328957 C85.7654005,73.4982265 89.9973805,68.5904809 98.8745378,62.3614415 C113.705104,54.0780014 123.361508,63.2141386 130.36737,75.0680786 C144.903722,99.6639393 143.150926,146.894175 143.081124,171.793704 C143.370788,196.34558 138.40516,238.152875 138.995137,245.34144 C139.585212,252.529909 141.773666,261.029714 148.033875,261.029714 C154.294065,261.029714 156.482587,252.529909 157.072623,245.34144 C157.662668,238.152875 155.306118,196.237785 152.986627,171.793704 C149.618411,112.685456 143.316282,86.074635 136.267242,72.4801139 C126.458358,53.5628061 117.153384,56.4224351 104.607988,38.2480952 C99.4824257,28.698951 97.9113468,22.4152887 92.3345395,16.9990185 C84.1026398,9.0041613 73.3110085,4.51570678 62.7157136,2.00353734 C48.3963189,-1.39168601 34.9464003,-0.0979453333 23.1711534,3.09918545 C6.31797278,7.6750366 2.2995619,21.1784912 1.05077297,26.430506 Z" fill="#0B69A3" fill-rule="nonzero"></path></g></svg> Spoon</button>');


                    $btn.on('click', $.proxy(function(e) {
                        e.preventDefault();
                        this.onFieldConfiguratorClick(e, id);
                    }, this));

                    ev.target.hud.$footer.find('.buttons').prepend($btn);
                }

            },

            onRemoveConfiguration: function(ev)
            {
                var $btn = $(ev.target),
                    fieldId = $btn.data('spoon-field-id'),
                    data = {
                        context : 'global',
                        fieldId : fieldId
                    };

                Craft.postActionRequest('spoon/block-types/delete', data, $.proxy(function(response, textStatus)
                {
                    if (textStatus == 'success' && response.success)
                    {
                        Craft.cp.displayNotice(Craft.t('spoon', 'Block type groups deleted.'));
                        $btn.addClass('hidden');
                    }
                    else
                    {
                        if (textStatus == 'success')
                        {
                            Craft.cp.displayError(Craft.t('app', 'There was an unknown error.'));
                        }
                    }
                }, this));
            },

            onFieldConfiguratorClick: function(ev, fieldId)
            {

                ev.preventDefault();
                ev.stopPropagation();

                var $elem = $(ev.target);

                if (typeof fieldId == "undefined") {
                    fieldId = $elem.data('spoon-field-id');
                }

                // Start the markup
                this.$form = $('<form class="modal elementselectormodal spoon-configurator"/>');

                // Get field id and store it on the modal form element
                this.$form.data('spoon-field-id', fieldId);

                // Make the rest of the modal markup
                this.$body = $('<div class="body"/>').appendTo(this.$form);
                this.$body = $('<div class="content"/>').appendTo(this.$body);
                this.$bigSpinner = $('<div class="spinner big"/>').appendTo(this.$body);
                this.$body = $('<div class="main"/>').appendTo(this.$body);
                var $footer = $('<div class="footer"/>').appendTo(this.$form),
                    $buttons = $('<div class="buttons right"/>').appendTo($footer);

                var $cancelBtn = $('<div class="btn">'+Craft.t('app', 'Cancel')+'</div>').appendTo($buttons),
                    $submitBtn = $('<input type="submit" class="btn submit" value="'+Craft.t('app', 'Save')+'"/>').appendTo($buttons);
                this.$spinner = $('<div class="spinner hidden"/>').appendTo($buttons);

                // Make the Garnish Modal object
                this.modal = new Garnish.Modal(this.$form,
                    {
                        resizable: true,
                        closeOtherModals: true,
                        onFadeIn: $.proxy(function()
                        {
                            this._populateModal();
                        }, this),
                        onHide: $.proxy(function()
                        {
                            this.modal.$container.remove();
                            this.modal.$shade.remove();
                            delete this;
                        }, this)
                    });

                // Submit and cancel handlers
                this.addListener(this.$form, 'submit', '_handleSubmit');

                this.addListener($submitBtn, 'click', $.proxy(function(ev)
                {
                    ev.preventDefault();
                    this.$form.one('blockTypesSaved', $.proxy(function()
                    {
                        this.modal.hide();

                        if (this.settings.context === 'global')
                        {
                            $elem.parent('td').parent('tr').find('.delete').removeClass('hidden');
                        }

                    }, this));
                    this.$form.trigger('submit');
                }, this));

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

                // Add the context
                data += '&context=' + this.settings.context

                // Post it
                Craft.postActionRequest('spoon/block-types/save', data, $.proxy(function(response, textStatus)
                {
                    this.$spinner.addClass('hidden');
                    if (textStatus == 'success' && response.success)
                    {
                        Craft.cp.displayNotice(Craft.t('spoon', 'Block type groups saved.'));
                        this._populateModal();
                        this.$form.trigger('blockTypesSaved');
                    }
                    else
                    {
                        if (textStatus == 'success')
                        {
                            Craft.cp.displayError(Craft.t('spoon', 'There was an unknown error saving some block type groups.'));
                        }
                    }
                }, this));
            },

            _populateModal: function()
            {
                // Load a fld with all the blocks in the un-used section, this will
                // allow grouping them in 'tabs' to give us the block groups like normal
                var data = {
                    fieldId : this.$form.data('spoon-field-id'),
                    context : this.settings.context
                };
                Craft.postActionRequest('spoon/configurator/get-html', data, $.proxy(function(response, textStatus)
                {
                    if (textStatus == 'success')
                    {
                        this.$body.html(response.html);
                        this.$bigSpinner.addClass('hidden');
                        var fld = new Spoon.GroupsDesigner('#spoon-configurator', {
                            customizableTabs: true,
                            customizableUi: false,
                            context : this.settings.context
                        });
                    }
                }, this));
            }

        },
        {
            defaults: {
                matrixFieldIds: null,
                context: false,
                fieldSelector: '.fld-tabcontent > .fld-field'
            }
        });


})(jQuery);

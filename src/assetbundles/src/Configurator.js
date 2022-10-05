/**
 * Spoon plugin for Craft CMS
 *
 * @copyright Copyright (c) 2018, 2022 Double Secret Agency
 * @link      https://plugins.doublesecretagency.com/
 * @package   Spoon
 * @since     3.0.0
 */

// Import Vue components
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import FieldLayoutDesigner from './vue/fld';

(function($){

    if (typeof Spoon == 'undefined')
    {
        const Spoon = {};
    }

    // noinspection JSVoidFunctionReturnValueUsed
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

            _handleCreateSettingsProxy: null,

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
                    this._handleCreateSettingsProxy = $.proxy(this, 'handleCreateSettings');
                    Garnish.on(Craft.FieldLayoutDesigner.Element, 'createSettings', this._handleCreateSettingsProxy);
                }

            },

            // Add "Spoon" button to slideout
            handleCreateSettings: function(ev)
            {
                var field = ev.target.$container[0];
                var id = $(field).data('id');

                // If no ID exists, bail
                if (!id) {
                    return;
                }

                // If not a matrix field, bail
                if (!this.settings.matrixFieldIds.map(Number).includes(Number(id))) {
                    return;
                }


                // Get the current slideout container
                let $slideoutContainer = $('.slideout-container:not(.hidden)');
                let $slideoutFooter = $slideoutContainer.find('.fld-element-settings-footer');

                // Create a "Spoon" button
                var $btn = $('<button type="button" class="btn">Spoon</button>');

                // Get the SVG image
                var $svg = $(`<img style="height:22px; opacity:0.55; margin-right:12px;" src="${this.settings.iconMask}" alt="icon" />`);

                // Add the SVG to the button
                $btn.prepend($svg);

                // Set button click behavior
                $btn.on('click', $.proxy(function(e) {
                    e.preventDefault();
                    this.onFieldConfiguratorClick(e, id);
                }, this));

                // Inject the "Spoon" button into the slideout footer
                $slideoutFooter.prepend($btn);

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
                    if (textStatus === 'success' && response.success)
                    {
                        Craft.cp.displayNotice(Craft.t('spoon', 'Block type groups deleted.'));
                        $btn.addClass('hidden');
                    }
                    else
                    {
                        if (textStatus === 'success')
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
                Craft.postActionRequest('spoon/group-settings/save', data, $.proxy(function(response, textStatus)
                {
                    this.$spinner.addClass('hidden');
                    if (textStatus === 'success' && response.success)
                    {
                        Craft.cp.displayNotice(Craft.t('spoon', 'Block type groups saved.'));
                        this._populateModal();
                        this.$form.trigger('blockTypesSaved');
                    }
                    else
                    {
                        if (textStatus === 'success')
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
                Craft.postActionRequest('spoon/group-settings/modal', data, $.proxy(function(response, textStatus)
                {
                    if (textStatus === 'success')
                    {
                        this.$body.html(response.html);
                        this.$bigSpinner.addClass('hidden');
                        // var fld = new Spoon.GroupsDesigner('#spoon-configurator', {
                        //     customizableTabs: true,
                        //     customizableUi: false,
                        //     context: this.settings.context
                        // });
                        this._loadComponents();
                    }
                }, this));
            },

            _loadComponents: function()
            {
                // Initialize Vue instance
                const app = createApp(FieldLayoutDesigner, {
                    'label': 'Block Types',
                    'config': window.blockGroupsData.config,
                    'elements': window.blockGroupsData.elements,
                });
                // Initialize Pinia
                app.use(createPinia());
                // Mount to DOM
                app.mount('#spoon-configurator');
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

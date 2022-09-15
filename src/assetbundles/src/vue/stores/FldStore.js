import { defineStore } from 'pinia';

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import FieldLayoutDesigner from './../fld';

export const useFldStore = defineStore('fld', {
    // Similar to `data`
    state: () => ({
        label: '',
        config: [],
        elements: [],
        activeOptions: null,
        searchFilter: '',
    }),
    // Similar to `computed`
    getters: {
        /**
         * Get IDs of all unselected block types.
         */
        unselected: function ()
        {
            // Initialize arrays of IDs
            let selected = [];
            let unselected = [];

            // Loop through all groups
            this.config.forEach(group => {
                // Append selected block type IDs
                selected = selected.concat(group.ids);
            });

            // Loop through all possible block types
            this.elements.forEach(type => {

                // If block type is selected, bail
                if (selected.includes(type.id)) {
                    return;
                }

                let filter = this.searchFilter.toLowerCase();

                // Check if search matches name or handle
                let matchName   = type.name.toLowerCase().includes(filter);
                let matchHandle = type.handle.toLowerCase().includes(filter);

                // Whether either values were matched
                let match = (matchName || matchHandle);

                // If search doesn't match name or handle, bail
                if (!match) {
                    return;
                }

                // Append unselected block type ID
                unselected.push(type.id);

            });

            // Return the unselected IDs
            return unselected;
        },
        /**
         * Calculate the maximum index.
         */
        maxIndex: function ()
        {
            // Return maximum index
            return this.config.length - 1;
        },
    },
    // Similar to `methods`
    actions: {

        setSearchFilter($event)
        {
            // Update search filter
            this.searchFilter = $event.target.value;
        },

        // ========================================================================= //

        elementDetails(id)
        {
            // Get details for specified element
            let matches = this.elements.filter(obj => {
                return obj.id === id
            });

            // If no matching element exists
            if (!matches.length) {
                // Return an empty object
                return {};
            }

            // Return matching element
            return matches[0];
        },

        // ========================================================================= //

        addTab()
        {
            // Get total number of existing groups
            let totalGroups = this.config.length;

            // Calculate the next group number
            let nextGroup = totalGroups + 1;

            // Append new group
            this.config.push({
                name: `Group ${nextGroup}`,
                ids: []
            });
        },

        openOptions(index)
        {
            // If these options are already open
            if (this.activeOptions === index) {
                // Close them
                this.activeOptions = null;
                // Bail
                return;
            }

            // Set currently active tab options
            this.activeOptions = index;
        },

        // ========================================================================= //

        tabRename(i)
        {
            // Close tab options
            this.activeOptions = null;

            // State the question
            let question = Craft.t('app', 'Give your group a name.');

            // Get old tab name
            let oldName = this.config[i].name;

            // Get new tab name
            let newName = prompt(question, oldName);

            // Change the group name
            this.config[i].name = (newName ?? oldName);
        },

        tabRemove(i)
        {
            // Remove tab
            this.config.splice(i, 1);
        },

        tabMoveLeft(i)
        {
            // If already at the beginning
            if (i === 0) {
                // Bail
                return;
            }

            // Move tab to the left
            this._moveTab(i, i - 1);
        },

        tabMoveRight(i)
        {
            // If already at the end
            if (i === this.maxIndex) {
                // Bail
                return;
            }

            // Move tab to the right
            this._moveTab(i, i + 1);
        },

        // ========================================================================= //

        _moveTab(oldPosition, newPosition)
        {
            // Close tab options
            this.activeOptions = null;

            // Get the existing tab data
            const tab = this.config[oldPosition];

            // Remove tab from the old position
            this.config.splice(oldPosition, 1);

            // Add tab to the new position
            this.config.splice(newPosition, 0, tab);
        },

        // GENERIC IMPLEMENTATION (GOOD)
        // ========================================================================= //

        // ------------------------------------------------------------------------- //

        // ========================================================================= //
        // ONE-OFF IMPLEMENTATION (BAD)

        openBlockFieldLayout(element)
        {
            // Submit configurator form
            window.configurator.$form.trigger('submit');

            // Open new modal for block field layout
            this._openModal(element);
        },

        _openModal(element)
        {
            // Get the block type ID
            var matrixBlockTypeId = element.id;

            // Get the context
            var context = window.configurator.settings.context;

            // Build the markup
            let $form = $('<form class="modal elementselectormodal spoon-fields-configurator"/>');

            var $body = $('<div class="body"/>').appendTo($form),
                $body = $('<div class="content"/>').appendTo($body),
                $bigSpinner = $('<div class="spinner big"/>').appendTo($body),
                $body = $('<div class="main"/>').appendTo($body),
                $footer = $('<div class="footer"/>').appendTo($form),
                $buttons = $('<div class="buttons right"/>').appendTo($footer);

            var $cancelBtn = $('<div class="btn">'+Craft.t('app', 'Cancel')+'</div>').appendTo($buttons),
                $submitBtn = $('<input type="submit" class="btn submit" value="'+Craft.t('app', 'Save')+'"/>').appendTo($buttons);

            var $spinner = $('<div id="small-spinner" class="spinner hidden"/>').appendTo($buttons);

            const modal = new Garnish.Modal($form, {
                resizable: true,
                closeOtherModals: false,
                onFadeIn: $.proxy(function()
                {

                    // Populate the modal
                    this._populateModal({
                        element,
                        matrixBlockTypeId,
                        context,
                        dom: {
                            $body, $bigSpinner
                        }
                    });

                }, this),
                onHide: $.proxy(function()
                {
                    modal.$container.remove();
                    modal.$shade.remove();
                    delete this;
                }, this)
            });

            // When cancel button is clicked, close modal
            $cancelBtn[0].addEventListener('click', function () {
                modal.hide();
            });

            // Alias
            const that = this;

            // When form is submitted, handle submission
            $form[0].addEventListener('submit', function ($event) {
                that._handleSubmit($event, modal);
            });

        },

        _populateModal(opts)
        {
            // Set data to render Twig template
            var data = {
                element : opts.element,
                fieldId : opts.fieldId,
                matrixBlockTypeId : opts.matrixBlockTypeId,
                context : opts.context
            };

            // Render Twig template
            Craft.postActionRequest('spoon/configurator/get-fields-html', data, $.proxy(function(response, textStatus)
            {
                if (textStatus === 'success')
                {
                    opts.dom.$body.html(response.html);
                    opts.dom.$bigSpinner.addClass('hidden');

                    // Initialize Vue instance
                    const app = createApp(FieldLayoutDesigner, {
                        'label': 'Fields',
                        'config': window.blockTabsData.config,
                        'elements': window.blockTabsData.elements,
                    });

                    // Initialize Pinia
                    app.use(createPinia());

                    // Mount to DOM
                    app.mount('#spoon-fields-configurator');

                }
            }, this));
        },

        _handleSubmit: function($event, modal)
        {
            // Prevent default form submission behavior
            $event.preventDefault();

            // Get small spinner
            const $spinner = $('#small-spinner');

            // Show spinner
            $spinner.removeClass('hidden');

            // Get the form
            const $form = $($event.target);

            // Get the form data
            const data = $form.serialize();

            // Submit form data to controller endpoint
            Craft.postActionRequest('spoon/block-types/save-field-layout', data, $.proxy(function(response, textStatus)
            {
                // Hide small spinner
                $spinner.addClass('hidden');

                if (textStatus === 'success' && response.success)
                {
                    Craft.cp.displayNotice(Craft.t('app', 'Field layout saved.'));
                    modal.hide();
                }
                else
                {
                    if (textStatus === 'success')
                    {
                        Craft.cp.displayError(Craft.t('app', 'An unknown error occurred.'));
                    }
                }
            }, this));
        }

    },
})

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


    Spoon.BlockTypeFieldLayoutDesigner = Craft.FieldLayoutDesigner.extend(
        {

            initField: function($blockType)
            {
                var $editBtn = $blockType.find('.settings'),
                    $menu = $('<div class="menu" data-align="center"/>').insertAfter($editBtn),
                    $ul = $('<ul/>').appendTo($menu);

                $('<li><a data-action="remove">'+Craft.t('app', 'Remove')+'</a></li>').appendTo($ul);

                new Garnish.MenuBtn($editBtn, {
                    onOptionSelect: $.proxy(this, 'onFieldOptionSelect')
                });
            }

        });


})(jQuery);

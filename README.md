<img align="left" width="120" src="https://raw.githubusercontent.com/doublesecretagency/craft-spoon/45a4a86792ba76b2f35144d13cd61cd06d520247/src/icon.svg" alt="Plugin icon">

# Spoon plugin for Craft CMS

**Bend the Matrix field with block groups, tabs, and more.**

---

Use Spoon to group Matrix blocks, hide blocks and / or fields, and organise your block layouts with tabs.

Spoon never touches your content, it is simply a layer on top of the core Matrix field type, so if you ever don’t want it you can just take the blue pill and uninstall it.

Or is it the red pill. Which is the real illusion?!

**Now works with [Super Table](https://verbb.io/craft-plugins/super-table)!**

Get inception heavy with either a Matrix inside a Super Table or go wild and nest that Super Table in _another_ Matrix for more fun.

---

## How to Install the Plugin

### Installation via Plugin Store

To install the Spoon plugin via the plugin store, follow these steps:

1. In your site's control panel, visit the Plugin Store page. If you do not see a link to the Plugin Store, be sure you are working in an environment which allows admin changes.

2. Search for "Spoon".

3. Install the plugin titled **Spoon**.

### Installation via Console Commands

To install the Spoon plugin via the console, follow these steps:

1. Open your terminal and go to your Craft project:

```sh
cd /path/to/project
```

2. Then tell Composer to load the plugin:

```sh
composer require doublesecretagency/craft-spoon
```

3. Then tell Craft to install the plugin:

```sh
./craft plugin/install spoon
```

>Alternatively, you can visit the **Settings > Plugins** page to finish the installation.

---

## Overview

Organise long lists of blocks into smaller groups for clearer selection.

![block type groups example](resources/img/docs/groups-ui.png)

Arrange fields into tabbed groups on each block type.

![block type field layouts example](resources/img/docs/flds-ui.png)

Use just one Matrix field and hide and show blocks or fields based on the Entry Type, Category Group and more.

![group block types button](resources/img/docs/group-block-types.jpg)


## Usage

The way Spoon works is by allowing you to create your block type groups and field layouts in multiple contexts.

Say you have a large Matrix field that drives a lot of the content on your site, you want it to work the same way across most of the control panel but there are often a couple of places you just want to tweak it. You might want an extra block type for a specific section, or to not show certain fields somewhere as they aren’t applicable in that context.

We enable this to happen by making use of contexts. Each time the code runs that manipulates the output of your Matrix fields we check the context of the page to see if there is any specific configuration for that context and if not fall back to any defaults you may have set.

The following contexts are currently supported:

* Entry Types
* Category Groups
* Global Sets
* Users

You can override your defaults for a specific context by going to the field layout designer for each one, clicking the gear icon of any active Matrix field and selecting “Group block types”:

![group block types button](resources/img/docs/group-block-types.jpg)


### Setting up defaults

To create default block type groups and field layouts for all your Matrix fields go to Spoon in the main navigation. Here you will find a list of your current Matrix fields.

Click a field name to launch the block type groups editor. It should look something like this:

![block type groups editor](resources/img/docs/block-type-groups-editor.jpg)

Now you can group your block types in the same way that you create a field layout for a section:

![block type groups editor filled in](resources/img/docs/block-type-groups-editor-2.jpg)

If you leave any block types off then they won’t be shown.

Once you have some groups you can go one step further and customize the field layout for a particular block - just click the gear icon and select ‘Edit field layout’.

![block type field layout](resources/img/docs/block-type-field-layout-editor.jpg)

Thats it! You should now be able to browse to somewhere that uses that field and see your new groups and field layouts in action.


### Config settings

Spoon supports the standard config.php multi-environment friendly config file for the plugin settings. Just copy the config.php to your Craft config/ directory as spoon.php and you can configure the settings in a multi-environment friendly way.

* `nestedSettings` - set this to an array of Matrix field handles that should use the nested settings menu display mode:

![nested settings menu](resources/img/docs/nested-setting-menu-example.png)


---

## Further Support

If you have any remaining questions, feel free to [reach out to us](https://www.doublesecretagency.com/contact) (via Discord is preferred).

**On behalf of Double Secret Agency, thanks for checking out our plugin!** 🍺

<p align="center">
    <img width="130" src="https://www.doublesecretagency.com/resources/images/dsa-transparent.png" alt="Logo for Double Secret Agency">
</p>

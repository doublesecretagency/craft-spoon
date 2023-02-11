---
description: While you typically won't need a separate PHP config file for the Spoon plugin, here's how to add one if you find that you need it. 
---

# PHP Config File

It's generally not necessary to create a PHP config file for Spoon. If you find that you need one, it can be easily added to your `config` directory.

Here's how to create a new `config/spoon.php` file:

```shell
# Copy this file...
/vendor/doublesecretagency/craft-spoon/src/config.php

# To here... (and rename it)
/config/spoon.php
```

:::warning Multi-Environment Configs
Much like the `db.php` and `general.php` files, `spoon.php` is [environmentally aware](https://craftcms.com/docs/4.x/config/#multi-environment-configs).
:::

## Options

```php
return [

    // An array of Matrix field handles which should
    // use the nested settings menu display mode
    'nestedSettings' => []
    
];
```

### `nestedSettings`

_array_ - Defaults to an empty array.

An array of Matrix field handles which should use the nested settings menu display mode.

```php
// Use a nested settings menu for "My Matrix Field"
'nestedSettings' => ['myMatrixField']
```

## Nested Settings Menu Display Mode

Enabling this mode for a given Matrix field will change how its blocks are listed under the block settings gear.

Instead of listing each block individually, only the **group title** will be displayed, with an **arrow to expand/collapse** the block types within each group.

<img class="dropshadow" :src="$withBase('/images/getting-started/nested-settings.png')" alt="Screenshot of collapsible block type groups" width="775" style="margin-top:12px">

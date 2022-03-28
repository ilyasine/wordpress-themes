# Freemium plugin development

How do I create a freemium plugin? We have tried to abstract this programmatically as far as possible but you also need to do manual steps. In this step-by-step guide it is explained by the example Real Media Library and you have to adjust the names and constant prefixes.

## Install

Navigate to your plugin and install this package:

```bash
composer require "devowl-wp/freemium @dev"
lerna add @devowl-wp/freemium --scope @devowl-wp/real-media-library
```

## Prepare freemium mechanism

### 1. Modify `package.json`

Navigate to your plugin's `package.json` and add the following lines:

```json
{
    "slugLite": "real-media-library-lite",
    "scripts": {
        "lite:activate": "yarn --silent root:run wp-cli \"wp config set RML_LITE true --raw\"",
        "lite:deactivate": "yarn --silent root:run wp-cli \"wp config set RML_LITE false --raw\""
    }
}
```

### 2. Modify `index.php` and create `fallback-already.php`

Navigate to your plugin's main file `src/index.php` and change the following lines (it adds the `require_once`). It prevents loading of two instances of the same plugin and shows a notice if this happens:

```php
if (defined("RML_PATH")) {
    require_once path_join(dirname(__FILE__), "inc/base/others/fallback-already.php");
    return;
}
```

Also, add the following constants:

```php
define("RML_SLUG_LITE", "real-media-library-lite");
define("RML_SLUG_PRO", "real-media-library-pro");
define("RML_PRO_VERSION", "https://devowl.io/go/real-media-library?source=rml-lite");
```

-   `RML_SLUG_LITE`: Define the public slug on wordpress.org of your plugin
-   `RML_PRO_VERSION`: Link to pro version so we can show a **Get PRO!** link in plugins list

Create a new file `inc/base/others/fallback-already.php`:

```php
<?php
defined("ABSPATH") or die("No script kiddies please!"); // Avoid direct file request

if (!function_exists("rml_skip_already_admin_notice")) {
    /**
     * Show an admin notice to administrators when the plugin is already active.
     */
    function rml_skip_already_admin_notice()
    {
        if (current_user_can("activate_plugins")) {
            $data = get_plugin_data(RML_FILE, true, false);
            echo '<div class=\'notice notice-error\'>
				<p>Currently multiple versions of the plugin <strong>Real Media Library</strong> are active. Please deactivate all versions except the one you want to use.</p>' .
                "</div>";
        }
    }
}
add_action("admin_notices", "rml_skip_already_admin_notice");

if (!function_exists("rml_skip_already_deactivate_lite")) {
    /**
     * Automatically deactivate lite version when we try to activate the PRO version.
     */
    function rml_skip_already_deactivate_lite()
    {
        // Avoid doing this in local stack as we do not have pro and lite difference in slug
        if (defined("DEVOWL_WP_DEV") && constant("DEVOWL_WP_DEV") && $_SERVER["SERVER_PORT"] === strval(10000)) {
            return;
        }

        deactivate_plugins(RML_SLUG_LITE . "/index.php");
    }
}
register_activation_hook(
    constant("WP_PLUGIN_DIR") . DIRECTORY_SEPARATOR . RML_SLUG_PRO . DIRECTORY_SEPARATOR . "index.php",
    "rml_skip_already_deactivate_lite"
);
```

### 3. Modify `start.php` and `composer.json`

Navigate to `src/inc/base/others/start.php` and add the following lines (`new Autoloader`):

```php
<?php
use DevOwl\Freemium\Autoloader;

new Autoloader("RML");
require_once RML_INC . "Core.php"; // <-- before this
```

Now, the autoloader for PHP freemium coding is registered. To avoid overlapping you also need to tell composer to ignore the `overrides` folder in your `composer.json` file:

```json
{
    "autoload": {
        "exclude-from-classmap": ["/inc/overrides/lite/", "/inc/overrides/pro/"]
    }
}
```

### 4. Extend `UtilsProvider.php`

Navigate to `src/inc/base/UtilsProvider.php` and use the trait `FreemiumProvider` so we can access freemium functionality (e. g. `$this->isPro()`) in all our classes:

```php
<?php
use DevOwl\Freemium\Autoloader;

trait UtilsProvider {
    use FreemiumProvider;
```

### 5. Extend `Core.php` and create first override

Navigate to `src/inc/Core.php` and let it look like this:

```php
use MatthiasWeb\RealMediaLibrary\overrides\interfce\IOverrideCore;
use MatthiasWeb\RealMediaLibrary\lite\Core as LiteCore;

class Core extends BaseCore implements IOverrideCore {
    use LiteCore;

    public function __construct() {
        $this->overrideConstructFreemium(); // <- call this method in your constructor
    }
```

**Important:** Make sure to always use the namespace of `lite`, because the Autoloader from above will handle the rest.

Afterwards, copy the complete `overrides` folder from this package to your `src/inc` folder. It contains the above Core override (after copying, make sure to use your namespaces). _What is an override?_ An override contains an interface and two traits: one for the lite version and the other for pro version. Depending on the current plugin state the correct trait is loaded.

### 6. Extend `Assets.php` and `option.tsx`

Navigate to `src/inc/Assets.php` and use the trait `FreemiumAssets` so we can access freemium functionality (e. g. `$this->localizeFreemiumScript()`) in our assets class:

```php
<?php
use DevOwl\Freemium\Assets as FreemiumAssets;

class Assets {
    use FreemiumAssets;

    // [...]

    public function enqueue_scripts_and_styles($context) {
        // [...]

        wp_localize_script($handle, 'realMediaLibrary', $this->localizeScript($type)); // Make sure to use your pro slug camelcased
    }

    public function overrideLocalizeScript($context) {
        return array_merge([
            // [...]
        ], $this->localizeFreemiumScript()); // <- make sure to merge with this method result
    }
```

So, our backend now serves four keys to the frontend where our TypeScript can consume that variables. Navigate to `src/public/ts/store/option.tsx` and add the following typing `FreemiumOptions` to your `others` object:

```ts
import { FreemiumOptions } from "@devowl-wp/freemium";

class OptionStore extends BaseOptions {
    @observable
    public others: { /* [...] */ } & FreemiumOptions = {};
```

### 7. Extend `Gruntfile.ts`

Navigate to `scripts/Gruntfile.ts` and add the following lines:

```ts
// [...]

// Make sure to add compress:liteInstallablePlugin
BUILD_POST_TASKS: ["compress:wordpressPlugin", "compress:liteInstallablePlugin"];

// [...]

applyFreemiumRunnerConfiguration(grunt);
applyPluginRunnerConfiguration(grunt); // <- make sure to put the above line before this one
```

Now, our `yarn build` builds two plugin folders, `real-media-library` and `real-media-library-lite` (and the zip's).

### 8. Extend `webpack.config.ts`

Navigate to `scripts/webpack.config.ts` and let it look like this:

```ts
/* eslint-disable import/no-default-export */
import { createDefaultSettings } from "@devowl-wp/wp-webpack";
import {
    Contexts,
    applyFreemiumConfigOverride,
    applyFreemiumDefinePlugin,
    applyFreemiumWebpackBarOptions
} from "@devowl-wp/freemium/scripts/webpack.freemium";

function createContextSettings(pluginContext: Contexts) {
    return createDefaultSettings(__filename, "plugin", {
        override: ([config], factoryValues) => {
            applyFreemiumConfigOverride(pluginContext, config);
        },
        definePlugin: (processEnv) => {
            applyFreemiumDefinePlugin(pluginContext, processEnv);
            return processEnv;
        },
        webpackBarOptions: (options) => {
            applyFreemiumWebpackBarOptions(pluginContext, options);
            return options;
        }
    });
}

export default [createContextSettings("lite"), createContextSettings("pro")].flat();
```

### I want to use `-pro` suffix?

You can use the predefined `pro:naming-reverse-compress` task which builds and compresses the plugin from to a `-pro` naming:

```diff
grunt.initConfig({
-    BUILD_POST_TASKS: ["compress:wordpressPlugin", "compress:liteInstallablePlugin"],
+    BUILD_POST_TASKS: ["pro:naming-reverse-compress"],
```

## Write freemium code

### Enqueue assets correctly

You will notice that now `yarn dev` or `yarn docker:start` builds two files of each entrypoint. For example, you will now get a `src/public/dev/rml.lite.js` and `src/public/dev/rml.pro.js`. You need to enqueue now the assets correctly in your `Assets.php`:

```php
$this->enqueueScript("rml", [[$this->isPro(), "rml.pro.js"], "rml.lite.js"], $scriptDeps);
```

### TypeScript coding

To differ now between lite and pro version you can use the following approach:

```ts
if (process.env.PLUGIN_CTX === "pro") {
    /* onlypro:start */
    console.log("This console.log will be pruned in lite version").
    /* onlypro:end */
}
```

### PHP coding

To prune PHP coding from your lite version is more difficult than TypeScript. You need to define your implementations in so-called `overrides`. For each override you need to define an interface and two traits. Then implement that interface in your class and use the lite trait. To learn more about it, have a look at [5. Extend `Core.php` and create first override](#5-extend-core-php-and-create-first-override).

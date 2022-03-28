# `@devowl-wp/real-utils`

## Installation

Navigate to your real plugin and execute the following commands to add `real-utils` as dependency.

```
composer require "devowl-wp/real-utils @dev"
yarn lerna add @devowl-wp/real-utils --scope @devowl-wp/real-plugin
```

## Usage

### Initialize, enqueue and configure.

Navigate to your plugin's `inc` folder and create `AdInitiator.php`. It can look like this:

```php
<?php
namespace DevOwl\RealThumbnailGenerator;

use DevOwl\RealUtils\AbstractInitiator;
use DevOwl\RealUtils\WelcomePage;
use DevOwl\RealThumbnailGenerator\base\UtilsProvider;

// @codeCoverageIgnoreStart
defined('ABSPATH') or die('No script kiddies please!'); // Avoid direct file request
// @codeCoverageIgnoreEnd

/**
 * Initiate real-utils functionality.
 */
class AdInitiator extends AbstractInitiator
{
    use UtilsProvider;

    /**
     * Documented in AbstractInitiator.
     *
     * @codeCoverageIgnore
     */
    public function getPluginBase()
    {
        return $this;
    }

    /**
     * Documented in AbstractInitiator.
     *
     * @codeCoverageIgnore
     */
    public function getPluginAssets()
    {
        return $this->getCore()->getAssets();
    }

    /**
     * Documented in AbstractInitiator.
     *
     * @codeCoverageIgnore
     */
    public function getRateLink()
    {
        return $this->isPro()
            ? 'https://codecanyon.net/downloads#item-18937507'
            : 'https://wordpress.org/support/plugin/' . RTG_SLUG_LITE . '/reviews/#new-post';
    }

    /**
     * Documented in AbstractInitiator.
     *
     * @codeCoverageIgnore
     */
    public function getKeyFeatures()
    {
        $isPro = $this->isPro();

        return [
            [
                'image' => $this->getAssetsUrl('feature-bulk.jpg'),
                'title' => __('Regenerate all your media in bulk', RTG_TD),
                'description' => __(
                    'Navigate to your media library and press the "Regenerate" button. A new dialog will open where you can regenerate your all thumbnails in media library with one click. Fast and efficient!',
                    RTG_TD
                ),
                'available_in' => $isPro
                    ? null
                    : [['Lite', WelcomePage::COLOR_BADGE_LITE], ['Pro', WelcomePage::COLOR_BADGE_PRO]],
                'highlight_badge' => $isPro
                    ? null
                    : [
                        'Pro',
                        WelcomePage::COLOR_BADGE_PRO,
                        __(
                            'In the PRO version you are able to regenerate more than 300 media files, skip existing files and check only specific thumbnail sizes.',
                            RTG_TD
                        ),
                    ],
            ],
            [
                'image' => $this->getAssetsUrl('feature-physical.gif'),
                'title' => __('Custom thumbnail upload structure', RTG_TD),
                'description' => __(
                    'Have you ever looked at the URL paths of your media uploads? Not really expressive. But this is exactly what is important to ensure that your images and the pages on which they are used get a good ranking in search engines. Improve your ranking with physically reordered uploads!',
                    RTG_TD
                ),
                'available_in' => $isPro ? null : [['Pro', WelcomePage::COLOR_BADGE_PRO]],
            ],
            [
                'image' => $this->getAssetsUrl('feature-delete-unused.gif'),
                'title' => __('Rich meta data and detect unused files', RTG_TD),
                'description' => __(
                    'When you open a single media file, you can view a list of all included thumbnail sizes.',
                    RTG_TD
                ),
                'available_in' => $isPro
                    ? null
                    : [['Lite', WelcomePage::COLOR_BADGE_LITE], ['Pro', WelcomePage::COLOR_BADGE_PRO]],
                'highlight_badge' => $isPro
                    ? null
                    : [
                        'Pro',
                        WelcomePage::COLOR_BADGE_PRO,
                        __(
                            'In the PRO version you can also delete unused thumbnail sizes - this can also be done for all files with one click.',
                            RTG_TD
                        ),
                    ],
            ],
        ];
    }

    /**
     * Documented in AbstractInitiator.
     *
     * @codeCoverageIgnore
     */
    public function getHeroButton()
    {
        return $this->isPro() ? null : [__('Get your PRO license now!', RTG_TD), RTG_PRO_VERSION];
    }

    /**
     * Documented in AbstractInitiator.
     *
     * @param boolean $isFirstTime
     * @codeCoverageIgnore
     */
    public function getNextRatingPopup($isFirstTime)
    {
        return $isFirstTime ? 0 : parent::getNextRatingPopup($isFirstTime);
    }
}
```

You can override more methods defined in `AbstractInitiator`, have a look at the `get` methods. All methods are well documented so you can override them without any problems.

Afterwards, you need to initialize this class and enqueue scripts as well. In your `Core.php#__construct` add the initialization like this: `(new AdInitiator())->start();`. In your `Assets.php` add `$RealUtils = RTG_ROOT_SLUG . '-real-utils-helper';` as script and style dependency to your enqueued scripts and styles.

### Implement rating popup

You can implement the following property getter in your `OptionStore`:

```ts
import { isRatable } from "@devowl-wp/real-utils";

class OptionStore extends BaseOptions {
    get isRatable() {
        return isRatable(this.slug);
    }
}
```

To use `isRatable` you need to modify your UI, have a look at this example:

```ts
optionStore.isRatable && new RatingPointer(optionStore.slug, $(".some-selector"));
```

### Add cross-selling product

This is currently very easy to implement, just have a look at `cross/CrossRealMediaLibrary.php`. One thing to note is that you need to create an instance of the created class in `Core.php#__construct`.

In `cross.tsx` you need to check for your action and afterwards create an instance of `CrossSellingPointer`.

<?php
/**
 * Backward-compatible class aliases.
 */

use MatthiasWeb\RealMediaLibrary\Activator;
use MatthiasWeb\RealMediaLibrary\Assets;
use MatthiasWeb\RealMediaLibrary\exception\FolderAlreadyExistsException;
use MatthiasWeb\RealMediaLibrary\folder\QueryCount;
use MatthiasWeb\RealMediaLibrary\Localization;
use MatthiasWeb\RealMediaLibrary\Util;
use MatthiasWeb\RealMediaLibrary\view\FolderShortcode;
use MatthiasWeb\RealMediaLibrary\view\Gutenberg;
use MatthiasWeb\RealMediaLibrary\view\Lang;
use MatthiasWeb\RealMediaLibrary\view\Options;
use MatthiasWeb\RealMediaLibrary\view\View;

// v4.5.4 -> v4.6.0, see #3jm006
class_alias(FolderAlreadyExistsException::class, RML_NS . '\\general\\FolderAlreadyExistsException');
class_alias(QueryCount::class, RML_NS . '\\general\\QueryCount');
class_alias(Activator::class, RML_NS . '\\general\\Activator');
class_alias(Assets::class, RML_NS . '\\general\\Assets');
class_alias(Localization::class, RML_NS . '\\general\\Localization');
class_alias(Util::class, RML_NS . '\\general\\Util');
class_alias(Options::class, RML_NS . '\\general\\Options');
class_alias(FolderShortcode::class, RML_NS . '\\general\\FolderShortcode');
class_alias(Gutenberg::class, RML_NS . '\\general\\Gutenberg');
class_alias(View::class, RML_NS . '\\general\\View');
class_alias(Lang::class, RML_NS . '\\general\\Lang');

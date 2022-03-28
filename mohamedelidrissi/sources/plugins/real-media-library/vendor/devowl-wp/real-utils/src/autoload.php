<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils;

// Simply check for defined constants, we do not need to `die` here
if (\defined('ABSPATH')) {
    \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\UtilsProvider::setupConstants();
    \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\Localization::instanceThis()->hooks();
}

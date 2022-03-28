<?php

namespace MatthiasWeb\RealMediaLibrary\usersettings;

use MatthiasWeb\RealMediaLibrary\api\IUserSettings;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
class Demo implements \MatthiasWeb\RealMediaLibrary\api\IUserSettings {
    // Documented in CommonUserSettingsTrait
    public function content($content, $user) {
        return '<label>Demo for user #' .
            $user .
            '</label>
            <textarea name="demo" type="text" class="regular-text" style="width: 100%;box-sizing: border-box;">Your Text</textarea>
            <p class="description">Data is not saved</p>';
    }
    // Documented in CommonUserSettingsTrait
    public function save($response, $user, $request) {
        $response['errors'][] =
            'An error occured with demo text: ' . $request->get_param('demo') . '. This is only a demo.';
        return $response;
    }
    // Documented in CommonUserSettingsTrait
    public function scripts($assets) {
        // Silence is golden.
    }
}

<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\Freemium;

// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request.
// @codeCoverageIgnoreEnd
/**
 * Plugin autoloader for lite version.
 */
class Autoloader {
    private $constantPrefix;
    /**
     * C'tor.
     *
     * @param string $constantPrefix
     * @codeCoverageIgnore
     */
    public function __construct($constantPrefix) {
        $this->constantPrefix = $constantPrefix;
        $this->prepare();
    }
    /**
     * Register the autoloader and define needed constants.
     */
    protected function prepare() {
        $prefix = $this->getConstantPrefix();
        $inc = \constant($prefix . '_INC');
        $isPro = \is_dir($inc . 'overrides/pro') && !(\defined($prefix . '_LITE') && \constant($prefix . '_LITE'));
        \define($prefix . '_IS_PRO', $isPro);
        \define($prefix . '_OVERRIDES_INC', $inc . 'overrides/' . ($isPro ? 'pro' : 'lite') . '/');
        \spl_autoload_register([$this, 'autoload']);
    }
    /**
     * Autoloader for lite classes.
     *
     * @param string $className
     */
    public function autoload($className) {
        $namespace = \constant($this->getConstantPrefix() . '_NS') . '\\';
        if (0 === \strpos($className, $namespace)) {
            $name = \substr($className, \strlen($namespace));
            $basepath = \str_replace('\\', '/', $name);
            if (\substr($basepath, 0, 5) === 'lite/') {
                $basepath = \preg_replace('/^lite\\//', '', $basepath, 1);
                $filename = \constant($this->getConstantPrefix() . '_OVERRIDES_INC') . $basepath . '.php';
                if (\file_exists($filename)) {
                    // @codeCoverageIgnoreStart
                    if (!\defined('PHPUNIT_FILE')) {
                        require_once $filename;
                    }
                    // @codeCoverageIgnoreEnd
                    return;
                }
            }
        }
    }
    /**
     * Get constant prefix.
     *
     * @codeCoverageIgnore
     */
    public function getConstantPrefix() {
        return $this->constantPrefix;
    }
}

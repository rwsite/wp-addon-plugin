<?php

namespace WpAddon\Services;

/**
 * Service for managing plugin options
 */
class OptionService
{
    /**
     * Option key
     */
    private string $optionKey;

    /**
     * Constructor
     *
     * @param string $optionKey
     */
    public function __construct(string $optionKey = 'wp-addon')
    {
        $this->optionKey = $optionKey;
    }

    /**
     * Get plugin settings from DB
     *
     * @return array
     */
    public function getSettings(): array
    {
        return get_option($this->optionKey, []) ?: [];
    }

    /**
     * Update plugin settings
     *
     * @param array $settings
     * @return bool
     */
    public function updateSettings(array $settings): bool
    {
        return update_option($this->optionKey, $settings);
    }

    /**
     * Get specific setting value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getSetting(string $key, $default = null)
    {
        $settings = $this->getSettings();
        return $settings[$key] ?? $default;
    }
}

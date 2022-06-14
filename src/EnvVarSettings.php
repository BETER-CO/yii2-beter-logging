<?php

namespace Beter\Yii2\Logging;

class EnvVarSettings
{
    /**
     * Checks NO_COLOR setting from $_SERVER (for SAPI like php-fcgi or apache) or ENV variable
     *
     * @return bool|null if setting isn't set returns null, true/false otherwise
     */
    public static function colorSettingEnabled(): ?bool
    {
        // Follow https://no-color.org/
        $settingVal = static::getEnvSettingValue('NO_COLOR');
        return false !== $settingVal ? !static::parseBooleanEnvSetting($settingVal) : null;
    }

    /**
     * Checks setting from $_SERVER (for SAPI like php-fcgi or apache) or ENV variable.
     *
     * Uses LOGGER_MACHINE_READABLE by default.
     *
     * @param string $envSettingName
     * @return bool|null if setting isn't set returns null, true/false otherwise
     */
    public static function machineReadableSettingEnabled(string $envSettingName = 'LOGGER_MACHINE_READABLE'): ?bool
    {
        $settingVal = static::getEnvSettingValue($envSettingName);
        return false !== $settingVal ? static::parseBooleanEnvSetting($settingVal) : null;
    }

    /**
     * Checks setting from $_SERVER (for SAPI like php-fcgi or apache) or ENV variable.
     *
     * Uses LOGGER_ENABLE_LOGSTASH by default.
     *
     * @param string $envSettingName
     * @return bool|null if setting isn't set returns null, true/false otherwise
     */
    public static function logstashSettingEnabled(string $envSettingName = 'LOGGER_ENABLE_LOGSTASH'): ?bool
    {
        $settingVal = static::getEnvSettingValue($envSettingName);
        return false !== $settingVal ? static::parseBooleanEnvSetting($settingVal) : null;
    }

    /**
     * Gets env setting from the $_SERVER or env.
     *
     * @param string $name returns false if setting isn't set
     * @return string|bool
     */
    public static function getEnvSettingValue(string $name)
    {
        // SAPI like apache, apache2handler, fpm-fcgi and etc passes ENV settings through $_SERVER superglobal
        if (isset($_SERVER[$name])) {
            return $_SERVER[$name];
        }

        return getenv($name);
    }

    /**
     * Parses value.
     *
     * Returns (bool) true for
     *   - strings "true", "TRUE", "True", "1";
     *   - true bool value passed to this method.
     *
     * Returns (bool) false for
     *   - strings "false", "FALSE", "False", "0";
     *   - false bool value passed to this method.
     *
     * Returns null otherwise.
     *
     * @param mixed $value value of any type
     * @return bool|null
     */
    public static function parseBooleanEnvSetting($value): ?bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            $value = strtoupper($value);
            if ($value === '1' || $value === 'TRUE') {
                return true;
            } elseif ($value === '0' || $value === 'FALSE') {
                return false;
            }

            return null;
        }

        return null;
    }
}
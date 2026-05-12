// Settings field dropdown options configuration
// Labels use translation keys that should be resolved at runtime with i18n

export interface SettingsOption {
    value: string | number;
    labelKey: string;
}

export const timezoneOptions: SettingsOption[] = [
    { value: 'UTC', labelKey: 'modules.core.settings.options.timezone.utc' },
    { value: 'Asia/Jakarta', labelKey: 'modules.core.settings.options.timezone.jakarta' },
    { value: 'Asia/Makassar', labelKey: 'modules.core.settings.options.timezone.makassar' },
    { value: 'Asia/Jayapura', labelKey: 'modules.core.settings.options.timezone.jayapura' },
    { value: 'America/New_York', labelKey: 'modules.core.settings.options.timezone.newyork' },
    { value: 'America/Chicago', labelKey: 'modules.core.settings.options.timezone.chicago' },
    { value: 'America/Los_Angeles', labelKey: 'modules.core.settings.options.timezone.losangeles' },
    { value: 'Europe/London', labelKey: 'modules.core.settings.options.timezone.london' },
    { value: 'Europe/Paris', labelKey: 'modules.core.settings.options.timezone.paris' },
    { value: 'Australia/Sydney', labelKey: 'modules.core.settings.options.timezone.sydney' },
    { value: 'Asia/Tokyo', labelKey: 'modules.core.settings.options.timezone.tokyo' },
    { value: 'Asia/Singapore', labelKey: 'modules.core.settings.options.timezone.singapore' },
]

export const dateFormatOptions: SettingsOption[] = [
    { value: 'Y-m-d', labelKey: 'modules.core.settings.options.dateFormat.ymd' },
    { value: 'd/m/Y', labelKey: 'modules.core.settings.options.dateFormat.dmy' },
    { value: 'm/d/Y', labelKey: 'modules.core.settings.options.dateFormat.mdy' },
    { value: 'd-m-Y', labelKey: 'modules.core.settings.options.dateFormat.dmyDash' },
    { value: 'F j, Y', labelKey: 'modules.core.settings.options.dateFormat.full' },
    { value: 'j F Y', labelKey: 'modules.core.settings.options.dateFormat.fullReverse' },
]

export const timeFormatOptions: SettingsOption[] = [
    { value: 'H:i:s', labelKey: 'modules.core.settings.options.timeFormat.24hSeconds' },
    { value: 'H:i', labelKey: 'modules.core.settings.options.timeFormat.24h' },
    { value: 'h:i A', labelKey: 'modules.core.settings.options.timeFormat.12h' },
    { value: 'h:i:s A', labelKey: 'modules.core.settings.options.timeFormat.12hSeconds' },
]

export const itemsPerPageOptions: SettingsOption[] = [
    { value: 10, labelKey: 'modules.core.settings.options.itemsPerPage.10' },
    { value: 15, labelKey: 'modules.core.settings.options.itemsPerPage.15' },
    { value: 20, labelKey: 'modules.core.settings.options.itemsPerPage.20' },
    { value: 25, labelKey: 'modules.core.settings.options.itemsPerPage.25' },
    { value: 50, labelKey: 'modules.core.settings.options.itemsPerPage.50' },
    { value: 100, labelKey: 'modules.core.settings.options.itemsPerPage.100' },
]

export const mailDriverOptions: SettingsOption[] = [
    { value: 'smtp', labelKey: 'modules.core.settings.options.mailDriver.smtp' },
    { value: 'sendmail', labelKey: 'modules.core.settings.options.mailDriver.sendmail' },
    { value: 'mailgun', labelKey: 'modules.core.settings.options.mailDriver.mailgun' },
    { value: 'ses', labelKey: 'modules.core.settings.options.mailDriver.ses' },
    { value: 'postmark', labelKey: 'modules.core.settings.options.mailDriver.postmark' },
    { value: 'log', labelKey: 'modules.core.settings.options.mailDriver.log' },
]

export const mailEncryptionOptions: SettingsOption[] = [
    { value: 'tls', labelKey: 'modules.core.settings.options.mailEncryption.tls' },
    { value: 'ssl', labelKey: 'modules.core.settings.options.mailEncryption.ssl' },
    { value: 'null', labelKey: 'modules.core.settings.options.mailEncryption.none' },
]

export const mailPortOptions: Record<string, SettingsOption[]> = {
    tls: [
        { value: 587, labelKey: 'modules.core.settings.options.mailPort.587tls' },
        { value: 2525, labelKey: 'modules.core.settings.options.mailPort.2525' },
        { value: 25, labelKey: 'modules.core.settings.options.mailPort.25tls' },
        { value: 465, labelKey: 'modules.core.settings.options.mailPort.465tls' },
    ],
    ssl: [
        { value: 465, labelKey: 'modules.core.settings.options.mailPort.465ssl' },
        { value: 587, labelKey: 'modules.core.settings.options.mailPort.587ssl' },
        { value: 25, labelKey: 'modules.core.settings.options.mailPort.25ssl' },
    ],
    null: [
        { value: 25, labelKey: 'modules.core.settings.options.mailPort.25' },
        { value: 587, labelKey: 'modules.core.settings.options.mailPort.587' },
        { value: 465, labelKey: 'modules.core.settings.options.mailPort.465' },
        { value: 2525, labelKey: 'modules.core.settings.options.mailPort.2525plain' },
    ],
}

export const storageDriverOptions: SettingsOption[] = [
    { value: 'local', labelKey: 'modules.core.settings.options.storageDriver.local' },
    { value: 's3', labelKey: 'modules.core.settings.options.storageDriver.s3' },
    { value: 'google', labelKey: 'modules.core.settings.options.storageDriver.google' },
    { value: 'ftp', labelKey: 'modules.core.settings.options.storageDriver.ftp' },
    { value: 'dropbox', labelKey: 'modules.core.settings.options.storageDriver.dropbox' },
]

export const thumbnailSizeOptions: SettingsOption[] = [
    { value: 150, labelKey: 'modules.core.settings.options.thumbnailSize.150' },
    { value: 200, labelKey: 'modules.core.settings.options.thumbnailSize.200' },
    { value: 250, labelKey: 'modules.core.settings.options.thumbnailSize.250' },
    { value: 300, labelKey: 'modules.core.settings.options.thumbnailSize.300' },
    { value: 400, labelKey: 'modules.core.settings.options.thumbnailSize.400' },
    { value: 500, labelKey: 'modules.core.settings.options.thumbnailSize.500' },
]

// Security field options
export const passwordMinLengthOptions: SettingsOption[] = [
    { value: 6, labelKey: 'modules.core.settings.options.passwordMinLength.6' },
    { value: 8, labelKey: 'modules.core.settings.options.passwordMinLength.8' },
    { value: 10, labelKey: 'modules.core.settings.options.passwordMinLength.10' },
    { value: 12, labelKey: 'modules.core.settings.options.passwordMinLength.12' },
    { value: 16, labelKey: 'modules.core.settings.options.passwordMinLength.16' },
]

export const loginAttemptsOptions: SettingsOption[] = [
    { value: 3, labelKey: 'modules.core.settings.options.loginAttempts.3' },
    { value: 5, labelKey: 'modules.core.settings.options.loginAttempts.5' },
    { value: 10, labelKey: 'modules.core.settings.options.loginAttempts.10' },
    { value: 0, labelKey: 'modules.core.settings.options.loginAttempts.unlimited' },
]

export const blockDurationOptions: SettingsOption[] = [
    { value: 5, labelKey: 'modules.core.settings.options.blockDuration.5' },
    { value: 15, labelKey: 'modules.core.settings.options.blockDuration.15' },
    { value: 30, labelKey: 'modules.core.settings.options.blockDuration.30' },
    { value: 60, labelKey: 'modules.core.settings.options.blockDuration.60' },
    { value: 1440, labelKey: 'modules.core.settings.options.blockDuration.1440' },
]

export const sessionLifetimeOptions: SettingsOption[] = [
    { value: 30, labelKey: 'modules.core.settings.options.sessionLifetime.30' },
    { value: 60, labelKey: 'modules.core.settings.options.sessionLifetime.60' },
    { value: 120, labelKey: 'modules.core.settings.options.sessionLifetime.120' },
    { value: 480, labelKey: 'modules.core.settings.options.sessionLifetime.480' },
    { value: 1440, labelKey: 'modules.core.settings.options.sessionLifetime.1440' },
    { value: 10080, labelKey: 'modules.core.settings.options.sessionLifetime.10080' },
]

export const maxConcurrentSessionsOptions: SettingsOption[] = [
    { value: 0, labelKey: 'modules.core.settings.options.maxSessions.unlimited' },
    { value: 1, labelKey: 'modules.core.settings.options.maxSessions.1' },
    { value: 2, labelKey: 'modules.core.settings.options.maxSessions.2' },
    { value: 3, labelKey: 'modules.core.settings.options.maxSessions.3' },
    { value: 5, labelKey: 'modules.core.settings.options.maxSessions.5' },
]

export const logRetentionOptions: SettingsOption[] = [
    { value: 0, labelKey: 'modules.core.settings.options.logRetention.forever' },
    { value: 7, labelKey: 'modules.core.settings.options.logRetention.7' },
    { value: 14, labelKey: 'modules.core.settings.options.logRetention.14' },
    { value: 30, labelKey: 'modules.core.settings.options.logRetention.30' },
    { value: 60, labelKey: 'modules.core.settings.options.logRetention.60' },
    { value: 90, labelKey: 'modules.core.settings.options.logRetention.90' },
    { value: 365, labelKey: 'modules.core.settings.options.logRetention.365' },
]

export const cacheTtlOptions: SettingsOption[] = [
    { value: 300, labelKey: 'modules.core.settings.options.cacheTtl.300' },
    { value: 600, labelKey: 'modules.core.settings.options.cacheTtl.600' },
    { value: 1800, labelKey: 'modules.core.settings.options.cacheTtl.1800' },
    { value: 3600, labelKey: 'modules.core.settings.options.cacheTtl.3600' },
    { value: 7200, labelKey: 'modules.core.settings.options.cacheTtl.7200' },
    { value: 86400, labelKey: 'modules.core.settings.options.cacheTtl.86400' },
]

export const maxUploadSizeOptions: SettingsOption[] = [
    { value: 1024, labelKey: 'modules.core.settings.options.maxUploadSize.1024' },
    { value: 2048, labelKey: 'modules.core.settings.options.maxUploadSize.2048' },
    { value: 5120, labelKey: 'modules.core.settings.options.maxUploadSize.5120' },
    { value: 10240, labelKey: 'modules.core.settings.options.maxUploadSize.10240' },
    { value: 20480, labelKey: 'modules.core.settings.options.maxUploadSize.20480' },
    { value: 51200, labelKey: 'modules.core.settings.options.maxUploadSize.51200' },
    { value: 102400, labelKey: 'modules.core.settings.options.maxUploadSize.102400' },
]

export const cacheDriverOptions: SettingsOption[] = [
    { value: 'file', labelKey: 'modules.core.settings.cache.drivers.file' },
    { value: 'redis', labelKey: 'modules.core.settings.cache.drivers.redis' },
    { value: 'database', labelKey: 'modules.core.settings.cache.drivers.database' },
    { value: 'array', labelKey: 'modules.core.settings.cache.drivers.array' },
]

export const twoFactorMethodOptions: SettingsOption[] = [
    { value: 'app', labelKey: 'modules.core.settings.options.twoFactorMethod.app' },
    { value: 'email', labelKey: 'modules.core.settings.options.twoFactorMethod.email' },
]

export const twoFactorEnforcedOptions: SettingsOption[] = [
    { value: 'no', labelKey: 'modules.core.settings.options.twoFactorEnforced.no' },
    { value: 'admin', labelKey: 'modules.core.settings.options.twoFactorEnforced.admin' },
    { value: 'all', labelKey: 'modules.core.settings.options.twoFactorEnforced.all' },
]

export const captchaMethodOptions: SettingsOption[] = [
    { value: 'slider', labelKey: 'modules.core.settings.options.captchaMethod.slider' },
    { value: 'math', labelKey: 'modules.core.settings.options.captchaMethod.math' },
    { value: 'image', labelKey: 'modules.core.settings.options.captchaMethod.image' },
]

export const shieldProtectionModeOptions: SettingsOption[] = [
    { value: 'off', labelKey: 'modules.core.settings.options.shieldProtectionMode.off' },
    { value: 'suspicious', labelKey: 'modules.core.settings.options.shieldProtectionMode.suspicious' },
    { value: 'always', labelKey: 'modules.core.settings.options.shieldProtectionMode.always' },
]

export const shieldProtectionDifficultyOptions: SettingsOption[] = [
    { value: 3, labelKey: 'modules.core.settings.options.shieldProtectionDifficulty.3' },
    { value: 4, labelKey: 'modules.core.settings.options.shieldProtectionDifficulty.4' },
    { value: 5, labelKey: 'modules.core.settings.options.shieldProtectionDifficulty.5' },
    { value: 6, labelKey: 'modules.core.settings.options.shieldProtectionDifficulty.6' },
    { value: 7, labelKey: 'modules.core.settings.options.shieldProtectionDifficulty.7' },
]

// Maintenance presets options
export const maintenanceTitlePresets: SettingsOption[] = [
    { value: 'Coming Soon', labelKey: 'modules.core.settings.options.maintenanceTitlePresets.coming_soon' },
    { value: 'Under Maintenance', labelKey: 'modules.core.settings.options.maintenanceTitlePresets.under_maintenance' },
    { value: 'Launch Day!', labelKey: 'modules.core.settings.options.maintenanceTitlePresets.launch_day' },
    { value: 'System Update', labelKey: 'modules.core.settings.options.maintenanceTitlePresets.system_update' },
]

export const maintenanceMessagePresets: SettingsOption[] = [
    { value: 'We are currently working on something awesome. Please check back later.', labelKey: 'modules.core.settings.options.maintenanceMessagePresets.awesome' },
    { value: "Sorry for the inconvenience. We're performing some maintenance and will be back shortly.", labelKey: 'modules.core.settings.options.maintenanceMessagePresets.inconvenience' },
    { value: 'Something exciting is coming! Stay tuned for our launch.', labelKey: 'modules.core.settings.options.maintenanceMessagePresets.exciting' },
    { value: "We are updating our system to provide you a better experience. We'll be back online soon.", labelKey: 'modules.core.settings.options.maintenanceMessagePresets.better_exp' },
]

// Helper function to get options for a specific field
export function getFieldOptions(fieldKey: string): SettingsOption[] | null {
    const optionsMap: Record<string, SettingsOption[]> = {
        timezone: timezoneOptions,
        date_format: dateFormatOptions,
        time_format: timeFormatOptions,
        items_per_page: itemsPerPageOptions,
        mail_driver: mailDriverOptions,
        mail_encryption: mailEncryptionOptions,
        storage_driver: storageDriverOptions,
        thumbnail_width: thumbnailSizeOptions,
        thumbnail_height: thumbnailSizeOptions,
        // Security options
        password_min_length: passwordMinLengthOptions,
        two_factor_method: twoFactorMethodOptions,
        two_factor_enforced_roles: twoFactorEnforcedOptions,
        captcha_method: captchaMethodOptions,
        login_attempts_limit: loginAttemptsOptions,
        block_duration_minutes: blockDurationOptions,
        session_lifetime: sessionLifetimeOptions,
        max_concurrent_sessions: maxConcurrentSessionsOptions,
        log_retention_days: logRetentionOptions,
        // Performance options
        cache_driver: cacheDriverOptions,
        cache_ttl: cacheTtlOptions,
        // Media options
        max_upload_size: maxUploadSizeOptions,
        // Bot Shield options
        shield_protection_mode: shieldProtectionModeOptions,
        shield_protection_difficulty: shieldProtectionDifficultyOptions,
    }

    return (optionsMap as Record<string, SettingsOption[]>)[fieldKey] || null
}

// Helper to get presets for a specific field
export function getFieldPresets(fieldKey: string): SettingsOption[] | null {
    const presetsMap: Record<string, SettingsOption[]> = {
        maintenance_title: maintenanceTitlePresets,
        maintenance_message: maintenanceMessagePresets,
    }

    return presetsMap[fieldKey] || null
}

// Helper to check if a field should use dropdown
export function shouldUseDropdown(fieldKey: string): boolean {
    return getFieldOptions(fieldKey) !== null
}

// Get mail port options based on encryption
export function getMailPortOptions(encryption: string): SettingsOption[] {
    return (mailPortOptions as Record<string, any>)[encryption] || mailPortOptions.null
}

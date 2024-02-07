<?php

namespace burnthebook\craftflarumsso\models;

use Craft;
use craft\base\Model;

/**
 * Flarum SSO for Craft 4 settings
 */
class Settings extends Model
{
    /**
     * Flarum API URL (Required)
     * @var string
     */
    public string $flarumApiUrl = '';

    /**
     * Flarum API Key (Required)
     * @var string
     */
    public string $flarumApiKey = '';

    /**
     * Flarum Cookie - Domain (Required)
     * @var string
     */
    public string $flarumCookieDomain = '';

    /**
     * Flarum Cookie - HTTP Only?
     * @var bool
     */
    public bool $flarumCookieHttpOnly = true;

    /**
     * Flarum Cookie - Secure Only?
     * @var bool
     */
    public bool $flarumCookieSecureOnly = false;

    /**
     * Flarum Cookie - Prefix
     * @var string
     */
    public string $flarumCookiePrefix = 'flarum_';

    /**
     * Flarum Cookie - Path
     * @var string
     */
    public string $flarumCookiePath = '/';

    /**
     * Flarum Cookie - Same Site
     * @var string
     */
    public string $flarumCookieSameSite = 'lax';

    /**
     * @return array
     */
    public function defineRules(): array
    {
        return [
            [
                [
                    'flarumApiUrl',
                    'flarumApiKey',
                    'flarumCookieDomain',
                ],
                'required',
            ],
            [
                [
                    'flarumCookieHttpOnly',
                    'flarumCookieSecureOnly',
                ],
                'boolean',
            ],
            [
                [
                    'flarumCookiePrefix',
                    'flarumCookiePath',
                    'flarumCookieSameSite',
                ],
                'string',
            ],
        ];
    }
}

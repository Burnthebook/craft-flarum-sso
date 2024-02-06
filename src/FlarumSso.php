<?php

namespace burnthebook\craftflarumsso;

use Craft;
use yii\base\Event;
use craft\base\Model;
use craft\base\Plugin;
use craft\web\Request;
use Maicol07\SSO\Flarum;
use craft\helpers\UrlHelper;
use craft\events\FindLoginUserEvent;
use craft\controllers\UsersController;
use burnthebook\craftflarumsso\models\Settings;
use burnthebook\craftflarumsso\services\FlarumApiClient;

/**
 * Flarum SSO for Craft 4 plugin
 *
 * @method static FlarumSso getInstance()
 * @method Settings getSettings()
 * @author Burnthebook <support@burnthebook.co.uk>
 * @copyright Burnthebook
 * @license MIT
 */
class FlarumSso extends Plugin
{
    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = true;

    public static function config(): array
    {
        return [
            'components' => [
                // Define component configs here...
            ],
        ];
    }

    public function init(): void
    {
        parent::init();

        // Defer most setup tasks until Craft is fully initialized
        Craft::$app->onInit(function() {
            $this->attachEventHandlers();
            // ...
        });
    }

    protected function createSettingsModel(): ?Model
    {
        return Craft::createObject(Settings::class);
    }

    protected function settingsHtml(): ?string
    {
        return Craft::$app->view->renderTemplate('flarum-sso/_settings.twig', [
            'plugin' => $this,
            'settings' => $this->getSettings(),
        ]);
    }

    private function attachEventHandlers(): void
    {
        // Register event handlers here ...
        // (see https://craftcms.com/docs/4.x/extend/events.html to get started)

        // API Client Options
        $options = [
            'endpoint' => 'http://flarum.musicinmind-craft.test',
            'api_key' => '4jSxH2e1Ecz*WF@z2JuJHB61F0UQb=TOgQrGs^%b',
            'cookie_options' => [
                'domain' => 'musicinmind-craft.test',
                'prefix' => 'flarum_', // optional
                'http_only' => true, // optional
                'path' => '/', // optional
                'same_site' => 'lax', // optional
                'secure_only' => false, // optional
            ]
        ];

        // Init client
        $client = new FlarumApiClient(
            endpoint: $options['endpoint'], 
            apiKey: $options['api_key'], 
            cookieOptions: $options['cookie_options']
        );

        // Get redirect URL
        $redirect = Craft::$app->request->getParam('redirect');

        /**
         * On login, log user into flarum too
         */
        Event::on(
            UsersController::class,
            UsersController::EVENT_AFTER_FIND_LOGIN_USER,
            function (FindLoginUserEvent $event) use($options, $client, $redirect) {
                // Check we actually authenticated with Craft
                if ($event->user) {
                    $craftUser = [
                        'username' => $event->user->username,
                        'email' => $event->user->email,
                        'password' => $event->sender->request->getBodyParam('password'),
                    ];

                    // Check if user exists on Flarum
                    if ($client->checkUserExists(username: $craftUser['username'])) {
                        $this->login(client: $client, user: $craftUser);
                        Craft::$app->getResponse()->redirect(UrlHelper::url($redirect))->send();
                    } else {
                        $this->signup(client: $client, user: $craftUser);
                        Craft::$app->getResponse()->redirect(UrlHelper::url($redirect))->send();
                    }
                }
            }
        );
    }

    /**
     * Log the user into Flarum with their Craft Credentials
     * 
     * @param   \burnthebook\craftflarumsso\services\FlarumApiClient $client An instance of the Flarum API Client
     * @param   array $user The CraftCMS User Data
     * 
     * @return void
     */
    protected function login(FlarumApiClient $client, array $user) : void
    {
        // Get token
        $token = $client->getToken(
            username: $user['username'], 
            password: $user['password']
        );

        // getToken returns an array if error.
        if ($token['error']) {
            throw new \Exception('Authentication failed: '. $token['data']);
        }

        // Set session cookie
        $client->setCookie(
            name: 'token', 
            payload: $token['data']->token
        );

        // Set remember cookie
        $client->setCookie(
            name: 'remember', 
            payload: $token['data']->token, 
            longLived: true
        );
    }

    /**
     * Sign the user up to Flarum with their Craft Credentials
     * 
     * @param   \burnthebook\craftflarumsso\services\FlarumApiClient $client An instance of the Flarum API Client
     * @param   array $user The CraftCMS User Data
     * 
     * @return void
     */
    protected function signup(FlarumApiClient $client, array $user) : void
    {
        // Create account
        $client->createAccount(userDetails: $user);

        // Log account in
        $this->login($client, $user);
    }
}

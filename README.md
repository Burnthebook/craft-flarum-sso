# Flarum SSO for Craft 4

Adds Single Sign On (SSO) for Craft 4 as a parent with Flarum as child

## Requirements

This plugin requires Craft CMS 4.5.0 or later, and PHP 8.0.2 or later.

This plugin also requires the [Flarum SSO Extension](https://github.com/maicol07/flarum-ext-sso) installing and configuring on your Flarum installation.

## Installation

You can install this plugin from the Plugin Store or with Composer.

#### From the Plugin Store

Go to the Plugin Store in your project’s Control Panel and search for “Flarum SSO for Craft 4”. Then press “Install”.

#### With Composer

Open your terminal and run the following commands:

```bash
# go to the project directory
cd /path/to/my-project.test

# tell Composer to load the plugin
composer require burnthebook/craft-flarum-sso

# tell Craft to install the plugin
./craft plugin/install flarum-sso
```

## Configuration

To configure the plugin, head on over to Settings > Plugins (Section at bottom of page) > Flarum SSO for Craft 4 and click the icon.

There are three required settings:

**Flarum URL**
_The URL to your Flarum installation. This is recommended to be a subdomain of your Craft CMS installation, e.g. https://discuss.craftcms.com_

**Flarum API Key**
_The API key you manually inserted into your Flarum installations api_keys database table_


**Forum SSO Cookie - Domain**
_The root domain for the SSO Cookie. If your Craft CMS installation is on www.craftcms.com, and your Flarum installation is on discuss.craftcms.com, you would set this to 'craftcms.com'_

The rest of the settings on the Settings page are optional and pre-configured to work correctly with the `maicol07/flarum-ext-sso` package that you will be installing on your Flarum installation, and so can be left as-is unless you need to configure the cookie settings for security purposes.

## Usage

This plugin automatically hooks into CraftCMS's login, register and logout events in order to replicate these events on the Flarum side of things. E.g. you log in to CraftCMS, you're logged into Flarum. You create an account on CraftCMS, your account is created on Flarum.

Additionally, if you have pre-existing users on Craft CMS that don't exist in Flarum, upon logging into your Craft CMS site after this plugin has been installed, a user will be created for them on Flarum automatically - they will not need to sign up again.

## Troubleshooting / FAQs

**Q: I am not logged into Flarum after I am logged into Craft**

A: Ensure your root domain matches, due to cookies it is best to have the Flarum forum on a subdomain and the Craft CMS either on another subdomain or a root domain. 

Additionally, ensure your cookie domain is set up within the Plugin Settings as detailed within the Configuration section. 

_**For Example:** If your Craft CMS installation is on craftcms.com, you'd want to have your Flarum installation on discuss.craftcms.com and your cookie domain set up as "craftcms.com" in Plugin Settings_

**Q: I am recieving "Authentication failed: Unauthorized" upon logging in or creating accounts in Craft CMS even though I know the username and password are correct**

A: Ensure your API Key is correctly set up within the Plugin Settings as detailed within the Configuration section. Ensure there are no trailing or leading spaces or extraneous characters.
_Optionally, ensure your API Key set in the Flarum `api_keys` table (has to be inserted manually) is for an Administrator user._

**Q: I want to see more detailed logs of what the API is returning rather than the friendly error messages output by the plugin**

A: Sure, we log all raw error responses to the storage/logs/flarum-sso-<yyyy>-<mm>-<dd>.log file

**Q: How do I log in as an admin to Flarum?**

A: Because of how `maicol07/flarum-ext-sso` and Flarum works, you will need to have the same username and password for both your Flarum installation and your Craft CMS installation for your administrator account. Then, when you log into Craft CMS as an administrator, you will automatically be logged into your administrator account on Flarum. This is important to note when setting up your Flarum installation for the first time.

**Q: Can all of my Craft CMS users go into a usergroup within Flarum to indicate that they came from CraftCMS?**

A: This functionality (or any groups functionality) is not currently supported, but if there is enough interest we can consider it.

**Q: Can my users still log into Flarum without a Craft CMS account?**

A: No, your users will require a Craft CMS account to log into Flarum once this plugin is installed to Craft CMS and maicol07/flarum-ext-sso is installed to Flarum. Craft CMS becomes the parent application and Flarum becomes the child application, so all user management should be performed within CraftCMS.

**Q: What if I have existing users within Flarum?**

A: This plugin is intended for use with brand new Flarum installations as of plugin version 1.0.0. If there is enough interest, in future we may add logic that checks if the user already exists within Flarum and create the user within CraftCMS, but as of now we only check if the user exists within Craft CMS and if they do and have authenticated with the correct credentials, create them within Flarum, and authenticate them.
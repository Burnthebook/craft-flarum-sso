# Flarum SSO for Craft 4

Adds Single Sign On (SSO) for Craft 4 as master with Flarum as slave

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



## Troubleshooting

**Q: I am not logged into Flarum after I am logged into Craft**

A: Ensure your root domain matches, due to cookies it is best to have the Flarum forum on a subdomain and the CraftCMS either on another subdomain or a root domain. 

Additionally, ensure your cookie domain is set up within the Plugin Settings as detailed within the Configuration section. 

_**For Example:** If your CraftCMS installation is on craftcms.com, you'd want to have your Flarum installation on discuss.craftcms.com and your cookie domain set up as "craftcms.com" in Plugin Settings_

**Q: I am recieving "Authentication failed: Unauthorized" upon logging in or creating accounts in CraftCMS even though I know the username and password are correct**

A: Ensure your API Key is correctly set up within the Plugin Settings as detailed within the Configuration section. Ensure there are no trailing or leading spaces or extraneous characters.
_Optionally, ensure your API Key set in the Flarum `api_keys` table (has to be inserted manually) is for an Administrator user._

**Q: I want to see more detailed logs of what the API is returning rather than the friendly error messages output by the plugin**


A: Sure, we log all raw error responses to the storage/logs/flarum-sso-<yyyy>-<mm>-<dd>.log file
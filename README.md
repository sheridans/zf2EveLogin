zf2EveLogin
===========

# Introduction
For EVE Online, the SSO means that you can sign into a web site that has
integrated the EVE SSO and confirm you are a specific character. While
signing into a site you will be asked which character you wish to
authenticate with and the web site that let you sign in with the EVE SSO
will get confirmation from CCP that you own that character. The original
web site will only ever get your character, they never see your account
name or password. The original web site will not know what account that
character is on or have any way, from us at least, of linking that
character to any other character on the same account.

## Installation

### Using composer

1. Add `sheridan/zf2-eve-login` (version `dev-master`) to requirements
2. Run `update` command on composer
3. Add `zf2EveLogin` to your `application.config.php` file

### Manually

Clone this project into your `./vendor/` directory and enable it in your
`application.config.php` file.

### Requires

PHP >= 5.3.3

Create a config/zf2evelogin.global.php file with the following contents
	return array(
		'zf2EveLogin' => array(
			'client_id'     => '<YOUR CLIENT ID PROVIDED BY CCP>',
			'secret'        => '<YOUR SECRET PROVIDED BY CCP',
			'callback_url'  => 'http://www.yourwebsite.com/eve-sso/authorise',
		),
	);
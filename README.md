# Environment Info

Show some environmental info in admin bar. Never again delete production content because you thought you were on stage.

## Setup

Activate plugin and add `ENVIRONMENT_INFO_SETTINGS` constant to wp-config.php. 

```php
define( 'ENVIRONMENT_INFO_SETTINGS', [
	[
		"pathPart" => "butler",
		"title" => "🤖 Local Butler DEV",
	],
	[
		"pathPart" => "s1234",
		"title" => "🤖 Freistil Site s1234",
		"background" => "#238422",
		"color" => "white",
	]
]);
```

Alternatively you can use the `environment_info_identify_site` filter to provide a custom identification.
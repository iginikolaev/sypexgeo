SypexGeo
==========

Sypex Geo - product for location by IP address.
Obtaining the IP address, Sypex Geo outputs information about the location of the visitor - country, region, city,
geographical coordinates and other in Russian and in English.
Sypex Geo use local compact binary database file and works very quickly.
For more information visit: http://sypexgeo.net/

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Add

```
"igi/sypexgeo": "*"
```

to the require section of your `composer.json` file.


Update database on "composer install" command
------------
Put "post-install-cmd" event to composer.json
```json
"scripts": {
    "post-install-cmd": [
        "IgI\\SypexGeo\\Composer::installDatabases"
    ]
}
```
Put "extra" settings to composer.json
```json
"extra": {
    "sypexgeo_remote": "https://sypexgeo.net/files/SxGeoCity_utf8.zip",
    "sypexgeo_local": "/path/to/project/SxGeoCity.dat"
}
```
# Trending Posts plugin for Craft CMS 3.x

A plugin to track page views

![Screenshot](resources/img/plugin-logo.png)

## Requirements

This plugin requires Craft CMS 3.0.0-beta.23 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require madebyraygun/trending-posts

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Trending Posts.

## Trending Posts Overview

A plugin to track page views

## Configuring Trending Posts

Set section and days to track by making changes in general.php:

//section to track
'trackSection' => ['1','2'],

//days to track records
'trackDays' => '7',

## Using Trending Posts

Use the following to increment view counts

`{% do craft.trendingposts.increment(entry.id) %}`

To order entries by most viewed use

`order('popular')`

Example:
For one section
`craft.entries.section('news').limit('3').order('popular').find()`

For multiple section
`craft.entries.section(['news','home']).limit('3').order('popular').find()`

## Credits

Brought to you by [Raygun](https://madebyraygun.com)
With support and initial development by [Bhashkar Yadav](http://sidd3.com)

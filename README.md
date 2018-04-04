# Trending Posts plugin for Craft CMS 3.x

A plugin to track page views on specific sections over time and order posts on the front-end by popularity.

![Screenshot](resources/img/plugin-logo.png)

## Requirements

This plugin requires Craft CMS 3.0.0 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require madebyraygun/trending-posts

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Trending Posts.

## Overview

A plugin to track page views on specific sections over time. Time period, sections to track, and visitor timeout are configurable. Adds an Element Query orderBy filter to return posts ordered by page views.

## Configuration

By default, the plugin will track page views in all sections. You can change which sections to track, how many days of activity to record, and visitor settings by creating a settings file in config/trending-posts.php. 

```<?php

return [
    'trackDays' => 7, //default is 7 days
    'trackSection' => [1,2], //limit tracking to specific sections
    'visitorTimeout' => 15 //default is 15 minutes
];
```

The plugin uses an array of numerical section IDs to determine which entries to track. You can find a section's numerical ID by viewing the section in the admin control pannel (/admin/settings/sections) and clicking on the section name. For example: http://yoursite.dev/admin/settings/sections/1

## Using in a template

Use the following twig tag to increment view counts. You can put this in a global footer or only on entries you want to track.

`{% do craft.trendingPosts.increment(entry.id) %}`

To order entries by most viewed use

`orderBy('popular')`

Example:
For a single section
`craft.entries.section('news').limit('3').orderBy('popular').all()`

For multiple section
`craft.entries.section(['news','home']).limit('3').orderBy('popular').all()`

## ToDo

Add a widget to display popular posts per section
Move summary table updates and stale record deletion to a background task (preferably with cron)
Create a settings section with selectable sections

## Credits

Brought to you by [Raygun](https://madebyraygun.com)
With initial development by [Bhashkar Yadav](http://sidd3.com)

# Trending Posts plugin for Craft CMS 3.x

Track pageviews over time and order your posts by popularity on the front-end. Options include the number of days to retain and a timed delay to prevent duplicate pageviews per IP address.

## Requirements

This plugin requires Craft CMS 3.0.0 or later.

## Installation

This plugin is not on packagist, so you'll need to create a local Composer repository to install. From [Andrew Welch's guide to Craft 3 plugins](https://nystudio107.com/blog/so-you-wanna-make-a-craft-3-plugin):

1. Download the plugin locally to a directory relative to your Craft installation. 

2. Add the following to the *require* section of your site's composer.json:

`"madebyraygun/trending-posts": "^1.0.0"`

3. Add your local directory to the list of Composer repositories like so:

```"repositories": [
  {
    "type": "composer",
    "url": "https://asset-packagist.org"
  },
  {
    "type": "path",
    "url": "../dev/trending-posts/"
  }
]
```

4. Run composer update.

5. In the Control Panel, go to Settings → Plugins and click the “Install” button for Trending Posts.

## Configuration

You can change which sections to record, how many days of activity to keep, and visitor settings by creating a settings file in config/trending-posts.php. 

```<?php

return [
    'trackDays' => 7, //default is 7 days
    'trackSection' => [1,2], //limit tracking to specific sections
    'visitorTimeout' => 15 //default is 15 minutes
];
```

The plugin uses an array of numerical section IDs to determine which entries to track. You can find a section's numerical ID by viewing the section in the admin control pannel (/admin/settings/sections) and clicking on the section name. For example: http://yoursite.dev/admin/settings/sections/1

## Using in a template

Use the following twig tag to increment view counts. You can put this in a global footer or only in specific sections that you want to track.

`{% do craft.trendingPosts.increment(entry.id) %}`

To order entries by most viewed use

`orderBy('popular')`

Example:
For a single section
`craft.entries.section('news').limit('3').orderBy('popular').all()`

For multiple section
`craft.entries.section(['news','home']).limit('3').orderBy('popular').all()`

## ToDo
Ideas?

## Credits

Brought to you by [Raygun](https://madebyraygun.com)

With initial development by [Bhashkar Yadav](http://sidd3.com)

<?php
/**
 * Trending Posts plugin for Craft CMS 3.x
 *
 * Sort posts by popularity over time
 *
 * @link      https://madebyraygun.com
 * @copyright Copyright (c) 2018 Raygun Design, LLC
 */

namespace madebyraygun\trendingposts\variables;

use madebyraygun\trendingposts\TrendingPosts;

use Craft;

/**
 * @author    Raygun Design, LLC
 * @package   TrendingPosts
 * @since     1.0.0
 */
class TrendingPostsVariable
{
    // Increment Views Count
    // Use {% do craft.trendingPosts.increment(entry.id) %}
    public function increment($entryId)
    {
        TrendingPosts::$plugin->trendingPostsService->increment($entryId);
    }
}

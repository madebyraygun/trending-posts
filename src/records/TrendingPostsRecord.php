<?php
/**
 * Trending Posts plugin for Craft CMS 3.x
 *
 * Sort posts by popularity over time
 *
 * @link      https://madebyraygun.com
 * @copyright Copyright (c) 2018 Raygun Design, LLC
 */

namespace madebyraygun\trendingposts\records;

use madebyraygun\trendingposts\TrendingPosts;

use Craft;
use craft\db\ActiveRecord;

/**
 * @author    Raygun Design, LLC
 * @package   TrendingPosts
 * @since     1.0.0
 */
class TrendingPostsRecord extends ActiveRecord
{
    // Table Names
    // =========================================================================
    public static function tableName()
    {
        return '{{%trendingpostsrecord}}';
    }
}

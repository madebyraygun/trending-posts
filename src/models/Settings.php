<?php

namespace madebyraygun\trendingposts\models;

use craft\base\Model;

class Settings extends Model
{
    public $trackDays = 7;
    public $trackSection = null;
    public $visitorTimeout = 15;
}

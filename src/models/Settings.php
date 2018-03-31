<?php

namespace madebyraygun\trendingposts\models;

use craft\base\Model;

class Settings extends Model
{
    public $trackDays = 7;
    public $trackSection = null; //Empty by default means track all sections
    public $visitorTimeout = 15;
}

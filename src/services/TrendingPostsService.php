<?php
/**
 * Trending Posts plugin for Craft CMS 3.x
 *
 * Sort posts by popularity over time
 *
 * @link      https://madebyraygun.com
 * @copyright Copyright (c) 2018 Raygun Design, LLC
 */

namespace madebyraygun\trendingposts\services;

use madebyraygun\trendingposts\TrendingPosts;

use Craft;
use craft\base\Component;
use craft\helpers\Db;
use craft\db\Query;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\elements\db\ElementQuery;
use craft\elements\db\ElementQueryInterface;
use craft\config\GeneralConfig;
use yii\db\Expression;

/**
 * @author    Raygun Design, LLC
 * @package   TrendingPosts
 * @since     1.0.0
 */
class TrendingPostsService extends Component
{
    public $trendingposts = '{{%trendingposts}}';

    public $trendingpostssummary = '{{%trendingpostssummary}}';

    public function _pageViewQuery(): Query
    {
        return (new Query())
            ->select([
                'id',
                'dateCreated',
                'dateDebounce',
                'entryId',
                'userIp',
                'views',
            ])
            ->from(['{{%trendingposts}}']);
    }

    public function _getpageViewQuery(): Query
    {
        return (new Query())
            ->select([
                'totalviews'
            ])
            ->from(['{{%trendingpostssummary}}']);
    }

    public function increment($entryId)
    {   
        $trackSection = \madebyraygun\trendingposts\TrendingPosts::getInstance()->getSettings()->trackSection;
        if (!$trackSection) { // Null by default means views on all entries are recorded
            $inSection = true; 
        } elseif (is_array($trackSection)) {
            $inSection = in_array($getSection, $trackSection);
        } else {
            return;
        }

        $trackDays = \madebyraygun\trendingposts\TrendingPosts::getInstance()->getSettings()->trackDays;
        $trackSeconds = $trackDays*24*60*60;
        $now = Db::prepareDateForDb(new \DateTime());
        $currentDate = strtotime($now);
        $yesterday = date("Y-m-d H:i:s", $currentDate-(24*60*60));

        //debounce time limit to prevent duplicate entries
        $visitorTimeout = \madebyraygun\trendingposts\TrendingPosts::getInstance()->getSettings()->visitorTimeout;
        $duplicateEntryCutoff = date("Y-m-d H:i:s", $currentDate+(60*$visitorTimeout));

        //calculate cutoff date to delete old entries
        $minusTrackDate = date("Y-m-d H:i:s", $currentDate-$trackSeconds);

        // This clears out older pageview records.
        // @todo  Move this to cron task    
        Craft::$app->getDb()->createCommand()
        ->delete($this->trendingposts,
                '[[dateCreated]] <= "'.$minusTrackDate.'"'
                )
        ->execute();

        //Tells us whether or not we need to update an existing record
        $getResults = $this->_pageViewQuery()
            ->where([
                'and',
                ['entryId' => $entryId],
                ['userIp' => $_SERVER['REMOTE_ADDR']],
                '[[dateCreated]] >= "'.$yesterday.'"',
            ])
            ->all();

        // If we found a matching record that needs to be udpated, update it
        if ($getResults) {
            foreach ($getResults as $result) {
                if ( $result['dateDebounce'] <= $now ) { //Only udpate if we've crossed the debounce threshhold
                    Craft::$app->getDb()->createCommand()
                    ->update(
                        $this->trendingposts,
                        [   
                            'dateDebounce' => $duplicateEntryCutoff,
                            'views' => new Expression('views + 1')
                        ],
                        [
                            'and',
                            ['id' => $getResults[0]['id']]
                        ])
                    ->execute();
                }
            }
        } else { 
            // Otherwise create a new record
            Craft::$app->getDb()->createCommand()
            ->insert(
                $this->trendingposts,
                [   
                    'dateDebounce' => $duplicateEntryCutoff,
                    'siteId'      => 1,
                    'entryId' => $entryId,
                    'userIp' => $_SERVER['REMOTE_ADDR'],
                    'userAgent' => $_SERVER['HTTP_USER_AGENT'],
                    'views' => 1
                ])
            ->execute();
        }

        // Rebuild the summary table
        // @todo  Move this to cron task   

        $query = (new Query())
            ->select(['entryId', 'sum(views) as totalviews'])
            ->from(['{{%trendingposts}}'])
            ->orderBy(['totalviews' => SORT_DESC])
            ->groupBy('entryId')
            ->all();

        Craft::$app->getDb()->createCommand()
        ->delete($this->trendingpostssummary)
        ->execute();
            
        foreach ($query as $i => $result) {
            Craft::$app->getDb()->createCommand()
            ->insert(
                $this->trendingpostssummary,
                [   
                    'dateCreated' => $now,
                    'dateUpdated' => $now,
                    'siteId'      => 1,
                    'entryId'     => $result['entryId'],
                    'totalviews'  => $result['totalviews']
                ])
            ->execute();
        }
        
    }

    /**
     * Modifies the query to order by Popular 
     */
    public function modifyElementsQuery (ElementQueryInterface $sender)
    {   
        $tableName = $this->trendingpostssummary;
        $tableAlias = 'trendingpostssummary' . bin2hex(openssl_random_pseudo_bytes(5));

        $on = '[[entries.id]] = [['.$tableAlias.'.entryId]]';

        $sender->query->join(
            'JOIN',
            "{$tableName} {$tableAlias}",
            $on
        );

        $sender->subQuery->join(
            'JOIN',
            "{$tableName} {$tableAlias}",
            $on
        );

        $sender->query->addOrderBy([$tableAlias.".totalviews" => SORT_DESC]);
        $sender->subQuery->addOrderBy([$tableAlias.".totalviews" => SORT_DESC]);
        return;

    }

    public function removeElementsQuery (ElementQueryInterface $sender)
    {      
        $sender->query->addOrderBy(['elements.dateCreated' => SORT_DESC]);
        $sender->subQuery->addOrderBy(['elements.dateCreated' => SORT_DESC]);
        return;
    }

    public function getViews ($e)
    {      
        $sender = $e->sender;
        $getSection = $sender->sectionId;
        $trackSection = \madebyraygun\trendingposts\TrendingPosts::getInstance()->getSettings()->trackSection;
        
        if (!$trackSection) {
            $inSection = true; 
        } elseif (is_array($trackSection)) {
            $inSection = in_array($getSection, $trackSection);
        } else {
            $inSection = false;
        }

        if($inSection == true){
            $entryId = $sender->id;
            $getResults = $this->_getpageViewQuery()
                ->where(['entryId' => $entryId])
                ->one();
            $getResult = $getResults["totalviews"];
            if($getResult){
                $e->html = $getResult;
            }else{
                $e->html = 0;
            }
        }else{
            $e->html = '-';
        }
        return;
    }
}

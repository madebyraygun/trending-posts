<?php
/**
 * Trending Posts plugin for Craft CMS 3.x
 *
 * Sort posts by popularity over time
 *
 * @link      https://madebyraygun.com
 * @copyright Copyright (c) 2018 Raygun Design, LLC
 */

namespace madebyraygun\trendingposts;

use madebyraygun\trendingposts\services\TrendingPostsService as TrendingPostsServiceService;
use madebyraygun\trendingposts\variables\TrendingPostsVariable;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\twig\variables\CraftVariable;

use craft\base\Element;
use craft\elements\Entry;
use craft\models\EntryDraft;
use craft\config\GeneralConfig;
use craft\elements\db\ElementQuery;
use craft\elements\db\ElementQueryInterface;

use yii\base\Event;

/**
 * Class trendingposts
 *
 * @author    Raygun Design, LLC
 * @package   TrendingPosts
 * @since     1.0.0
 *
 * @property  trendingpostsServiceService $trendingpostsService
 */
class TrendingPosts extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var trendingposts
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */

    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('trendingPosts', TrendingPostsVariable::class);
            }
        );

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                }
            }
        );

        // ModifyElementsQuery 
        Event::on(
            ElementQuery::class,
            ElementQuery::EVENT_BEFORE_PREPARE,
            function (Event $event) {
                $sender = $event->sender;
                if($sender->elementType == 'craft\elements\Entry'){
                    if ($sender && !empty($sender->orderBy) && array_key_exists('popular', $sender->orderBy)) {
                        $getSection = $sender->sectionId;
                        $trackSection = \madebyraygun\trendingposts\TrendingPosts::getInstance()->getSettings()->trackSection;
                        if (!$trackSection) { // Null by default means all posts are tracked
                            $inSection = true; 
                        } elseif (is_array($trackSection)) {
                            $inSection = !empty(array_intersect($trackSection, $getSection));
                        } else {
                            $inSection = false;
                        }

                        if($inSection == true){
                            TrendingPosts::$plugin->trendingPostsService->modifyElementsQuery($sender);
                        }else{
                            TrendingPosts::$plugin->trendingPostsService->removeElementsQuery($sender);
                        }
                    }
                }else{
                    if ($sender && !empty($sender->orderBy) && array_key_exists('popular', $sender->orderBy)) {
                        TrendingPosts::$plugin->trendingPostsService->removeElementsQuery($sender);
                    }
                }
            }
        );

        Event::on(
            Element::class,
            Element::EVENT_REGISTER_TABLE_ATTRIBUTES,
            function(craft\events\RegisterElementTableAttributesEvent $e) {
                $e->tableAttributes['views'] = [
                    'label' => "Page Views"
                ];
        });

        Event::on(
            Entry::class,
            Element::EVENT_SET_TABLE_ATTRIBUTE_HTML,
            function(craft\events\SetElementTableAttributeHtmlEvent $e) {
                if($e->attribute === 'views'){
                    TrendingPosts::$plugin->trendingPostsService->getViews($e);
                }
            });

        Craft::info(
            Craft::t(
                'trending-posts',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

    protected function createSettingsModel()
    {
        return new \madebyraygun\trendingposts\models\Settings();
    }

}

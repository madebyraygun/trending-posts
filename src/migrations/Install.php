<?php
/**
 * Trending Posts plugin for Craft CMS 3.x
 *
 * Sort posts by popularity over time
 *
 * @link      https://madebyraygun.com
 * @copyright Copyright (c) 2018 Raygun Design, LLC
 */

namespace madebyraygun\trendingposts\migrations;

use madebyraygun\trendingposts\TrendingPosts;

use Craft;
use craft\config\DbConfig;
use craft\db\Migration;

/**
 * @author    Raygun Design, LLC
 * @package   TrendingPosts
 * @since     1.0.0
 */
class Install extends Migration
{
    // Public Properties
    // =========================================================================

    /**
     * @var string The database driver to use
     */
    public $driver;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        if ($this->createTables()) {
            $this->createIndexes();
            $this->addForeignKeys();
            // Refresh the db schema caches
            Craft::$app->db->schema->refresh();
            $this->insertDefaultData();
        }

        return true;
    }

   /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeTables();

        return true;
    }

    // Protected Methods
    // =========================================================================

    /**
     * @return bool
     */
    protected function createTables()
    {
        $tablesCreated = false;

        // trendingpostsrecord table
        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%trendingpostsrecord}}');
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                '{{%trendingpostsrecord}}',
                [
                    'id' => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'dateDebounce' => $this->dateTime()->notNull(),
                    'siteId' => $this->integer()->notNull(),
                    'uid' => $this->uid(),
                    'entryId' => $this->integer()->notNull(),
                    'userIp' => $this->string(255)->notNull()->defaultValue(''),
                    'userAgent' => $this->string(255)->notNull()->defaultValue(''),
                    'views' => $this->integer()->notNull(),
                ]
            );
            // trendingpostscounterrecord table
            $this->createTable(
                '{{%trendingpostscounterrecord}}',
                [
                    'id' => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'siteId' => $this->integer()->notNull(),
                    'uid' => $this->uid(),
                    'entryId' => $this->integer()->notNull(),
                    'totalviews' => $this->integer()->notNull(),
                ]
            );
            // // Garbage collection table
            // $this->createTable(
            //     '{{%pageviewsgarbagecollectionrecord}}',
            //     [
            //         'id' => $this->primaryKey(),
            //         'dateCreated' => $this->dateTime()->notNull(),
            //         'dateUpdated' => $this->dateTime()->notNull(),
            //         'uid' => $this->uid(),
            //         'nextRun' => $this->dateTime()->notNull(),
            //     ]
            // );
        }

        return $tablesCreated;
    }

    /**
     * @return void
     */
    protected function createIndexes()
    {
    // trendingpostsrecord table
        $this->createIndex(
            null,
            '{{%trendingpostsrecord}}',
            'id',
            true
        );
        $this->createIndex(
            null,
            '{{%trendingpostscounterrecord}}',
            'id',
            true
        );
        // $this->createIndex(
        //     null,
        //     '{{%pageviewsgarbagecollectionrecord}}',
        //     'id',
        //     true
        // );
        // Additional commands depending on the db driver
        switch ($this->driver) {
            case DbConfig::DRIVER_MYSQL:
                break;
            case DbConfig::DRIVER_PGSQL:
                break;
        }
    }

    /**
     * @return void
     */
    protected function addForeignKeys()
    {
    // trendingpostsrecord table
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%trendingpostsrecord}}', 'siteId'),
            '{{%trendingpostsrecord}}',
            'siteId',
            '{{%sites}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%trendingpostscounterrecord}}', 'siteId'),
            '{{%trendingpostscounterrecord}}',
            'siteId',
            '{{%sites}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }
    /**
     * @return void
     */
    protected function insertDefaultData()
    {
    }

    /**
     * @return void
     */
    protected function removeTables()
    {
        $this->dropTableIfExists('{{%trendingpostsrecord}}');
        $this->dropTableIfExists('{{%trendingpostscounterrecord}}');
        // $this->dropTableIfExists('{{%pageviewsgarbagecollectionrecord}}');
    }
}

<?php

namespace craft\commerce\migrations;

use Craft;
use craft\db\Migration;

/**
 * m1901004_184910_orderPaidDate migration.
 */
class m1901004_184910_orderPaidDate extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // This query looks for any order where there is an amount owing and sets the date paid to null.
        // This goes along with this fix: https://github.com/craftcms/commerce/commit/d7c9e32dfe9a5158a044e560470b627411739041
        $sql = "
            UPDATE {{%commerce_orders}} AS o
            SET [[o.datePaid]] = null
            WHERE (SELECT SUM(CASE WHEN [[t.type]] = 'refund' THEN amount
                                   WHEN [[t.type]] IN ('purchase', 'capture') THEN -amount
                              END)
            FROM {{%commerce_transactions}} AS t
            WHERE [[t.orderId]] = [[o.id]] AND [[t.status]] = 'success' AND [[o.totalPrice]] != 0 
            ) > 0;
        ";

        Craft::$app->getDb()->createCommand($sql)->execute();
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m1901004_184910_orderPaidDate cannot be reverted.\n";
        return false;
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: VitProg
 * Date: 18.04.2017
 * Time: 19:48
 */

namespace app\modules\api\queries;


use app\modules\api\models\Car;
use yii\redis\ActiveQuery;

/**
 * Class CarQuery
 * @package app\modules\api\queries
 *
 * @method Car[] all($db = null)
 * @method Car one($db = null)
 */
class CarQuery extends ActiveQuery
{
    /**
     * @param $year
     * @return $this
     */
    public function year($year)
    {
        return $this->andWhere(['year' => $year]);
    }

}
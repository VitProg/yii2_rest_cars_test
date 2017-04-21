<?php
/**
 * Created by PhpStorm.
 * User: VitProg
 * Date: 18.04.2017
 * Time: 19:45
 */

namespace app\modules\api\models;


use app\modules\api\queries\CarQuery;
use Yii;
use yii\redis\ActiveRecord;
use yii\redis\Connection;

/**
 * Class Car
 * @package app\modules\api\models
 *
 * @property integer $id
 * @property string $brand
 * @property string $model
 * @property integer $year
 * @property string $color
 * @property integer $mileage
 * @property integer $cost
 */
class Car extends ActiveRecord
{
    const REDIS_KEY_CAR_YEARS_COUNTER = 'car:years:counts';
    const REDIS_KEY_CAR_YEARS = 'car:years';

    /**
     * @return array the list of attributes for this record
     */
    public function attributes()
    {
        return ['id', 'brand', 'model', 'year', 'color', 'mileage', 'cost'];
    }

    public function rules()
    {
        /** @noinspection PhpUnusedParameterInspection */
        return [
            [['brand', 'model', 'year', 'color', 'mileage', 'cost'], 'safe'],
            [['brand', 'model', 'color'], 'string'],

            [['brand', 'model', 'year'], 'required'],

            ['year', 'integer'],
            ['year', function ($attribute, $params) {
                if (is_numeric($this->$attribute) == false) {
                    $this->addError($attribute, 'year is not number');
                }
                if ($this->$attribute < 1800 || $this->$attribute > intval(date('Y')) + 30) {
                    $this->addError($attribute, 'year is not valid `' . $this->$attribute . '``');
                }
            }],

            [['cost', 'mileage'], 'integer'],
            [['cost', 'mileage'], 'default', 'value' => 0],
        ];
    }

    /**
     * @inheritdoc
     * @return CarQuery
     */
    public static function find()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Yii::createObject(CarQuery::className(), [get_called_class()]);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        /** @var Connection $redis */
        $redis = Yii::$app->get('redis');

        // при вставки/изменении авто нужно привести впорядок счетчики по годам
        if ($insert) {
            $redis->zadd(static::REDIS_KEY_CAR_YEARS, $this->year, $this->year);
            $redis->zincrby(static::REDIS_KEY_CAR_YEARS_COUNTER, 1, $this->year);
        } else {
            if (isset($changedAttributes['year']) && $changedAttributes['year'] != $this->year) {
                $this->incrementYearCounter($changedAttributes['year'], -1);
                $this->incrementYearCounter($this->year, 1);
            }
        }
    }

    public function afterDelete()
    {
        parent::afterDelete();

        $this->incrementYearCounter($this->year, -1);
    }

    /**
     * Инкриментирует счетчик кол-ва авто в году
     * @param integer $year - год
     * @param integer $increment - инкримент
     */
    protected function incrementYearCounter($year, $increment = 1) {
        /** @var Connection $redis */
        $redis = Yii::$app->get('redis');

        $redis->zincrby(static::REDIS_KEY_CAR_YEARS_COUNTER, $increment, $this->year);

        $this->normalizeYearCounter($year);
    }

    /**
     * Нормализирует счетчик кол-во авто по году
     * @param integer $year - год
     */
    protected function normalizeYearCounter($year) {
        /** @var Connection $redis */
        $redis = Yii::$app->get('redis');

        $countByYear = Car::find()->year($year)->count();
        if (!$countByYear) {
            $redis->zrem(static::REDIS_KEY_CAR_YEARS, $year);
        }

        $count = $redis->zscore(static::REDIS_KEY_CAR_YEARS_COUNTER, $year);
        if ($count <= 0) {
            $redis->zrem(static::REDIS_KEY_CAR_YEARS_COUNTER, $year);
        }
    }

    /**
     * @inheritdoc
     */
    public static function deleteAll($condition = null)
    {
        /** @var Connection $redis */
        $redis = Yii::$app->get('redis');

        if ($condition === null) {
            $redis->del(static::REDIS_KEY_CAR_YEARS_COUNTER);
            $redis->del(static::REDIS_KEY_CAR_YEARS);
        } else {
            // todo - пересчитывать счетчики
        }

        return parent::deleteAll($condition);
    }

    public static function getYearCount($year) {
        /** @var Connection $redis */
        $redis = Yii::$app->get('redis');

        $count = $redis->zscore(Car::REDIS_KEY_CAR_YEARS_COUNTER, $year);

        return $count === null ? null : intval($count);
    }

    /**
     * @return integer[]
     */
    public static function getYearsList() {
        /** @var Connection $redis */
        $redis = Yii::$app->get('redis');
        $years = $redis->zrange(Car::REDIS_KEY_CAR_YEARS, 0, 3000);
        return $years;
    }

}
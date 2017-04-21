<?php
/**
 * Created by PhpStorm.
 * User: VitProg
 * Date: 18.04.2017
 * Time: 19:45
 */

namespace app\modules\api\controllers;


use app\modules\api\models\Car;
use app\modules\api\queries\CarQuery;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\Cors;
use yii\redis\Connection;
use yii\rest\ActiveController;
use yii\web\Response;

class CarController extends ActiveController
{
    public $modelClass = Car::class;

    public function behaviors()
    {
        return [
            'corsFilter' => [
                'class' => Cors::className(),
                'cors' => [
                    'Origin' => ['*'],
                    'Access-Control-Expose-Headers' => [
                        'X-Pagination-Per-Page',
                        'X-Pagination-Total-Count',
                        'X-Pagination-Current-Page',
                        'X-Pagination-Page-Count',
                    ],
                ],
            ],
            [
                'class' => 'yii\filters\ContentNegotiator',
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
        ];
    }

    public function actions()
    {
        $actions = parent::actions();

        $actions['index']['prepareDataProvider'] = function ($action) {
            $modelClass = $action->modelClass;

            /** @var CarQuery $query */
            $query = $modelClass::find();

            $getYear = intval(\Yii::$app->request->get('year'));
            if ($getYear) {
                $query->year($getYear);
            }

            $provider = new ActiveDataProvider(
                [
                    'query' => $query,
                ]
            );

            if ($getYear) {
                /** @var Connection $redis */
                $redis = Yii::$app->get('redis');
                $count = Car::getYearCount($getYear);
                $provider->setTotalCount($count);
            }

            return $provider;
        };

        return $actions;
    }

    /**
     * @return integer[]
     */
    public function actionYears() {
        return Car::getYearsList();
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: VitProg
 * Date: 21.04.2017
 * Time: 13:34
 */

namespace tests\models;


use app\modules\api\models\Car;
use Codeception\Test\Unit;
use Yii;
use yii\redis\Connection;


class CarTest extends Unit
{

    public function testFlushDB() {
        /** @var Connection $redis */
        $redis = Yii::$app->get('redis');
        $redis->flushdb();
    }

    public function testAddCar() {
        $car = new Car([
            'brand' => 'Audi',
            'model' => 'A8',
            'year' => 2017,
            'color' => 'red',
            'mileage' => 10000,
            'cost' => 1000000,
        ]);
        expect($car->validate())->true();
        expect($car->save())->true();
        expect($car->refresh())->true();
        expect($car->brand)->equals('Audi');
        expect($car->model)->equals('A8');
        expect($car->year)->equals(2017);
        expect($car->color)->equals('red');
        expect($car->mileage)->equals(10000);
        expect($car->cost)->equals(1000000);
    }

    public function testAdd99Cars() {
        $brands = ['audi', 'volvo', 'bmw', 'reno', 'lada', 'mercedes'];
        $models = ['a','b','c','d','e','f','g','h','i','j'];
        $colors = ['red', 'green', 'blue', 'white', 'black'];

        for ($i = 0; $i < 99; $i++) {
            $car = new Car([
                'brand' => $brands[array_rand($brands)],
                'model' => $models[array_rand($models)],
                'year' => rand(2000, 2017),
                'color' => $colors[array_rand($colors)],
                'mileage' => rand(0, 100000),
                'cost' => rand(500000, 3000000),
            ]);
            expect($car->save())->true();
        }

        expect(Car::find()->count())->equals(100);
    }

    public function testUpdate() {
        expect_that($car = Car::find()->one());
        $car->model = 'TEST';
        expect($car->save())->true();
        expect($car->refresh())->true();
        expect($car->model)->equals('TEST');
    }

    public function testDeleteOne() {
        expect_that($car = Car::find()->one());

        $yearCount = Car::getYearCount($car->year);

        expect($yearCount)->greaterOrEquals(1);

        expect($car->delete())->equals(1);

        expect(Car::find()->count())->equals(99);

        $yearCountAfterDelete = Car::getYearCount($car->year);

        expect($yearCountAfterDelete < $yearCount)->true();
    }

    public function testDeleteAll() {
        expect(Car::deleteAll())->equals(99);
        expect(Car::find()->count())->equals(0);

        /** @var Connection $redis */
        $redis = Yii::$app->get('redis');

        expect($redis->exists(Car::REDIS_KEY_CAR_YEARS))->equals(0);
        expect($redis->exists(Car::REDIS_KEY_CAR_YEARS_COUNTER))->equals(0);
    }
}

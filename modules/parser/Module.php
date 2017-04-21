<?php
/**
 * Created by PhpStorm.
 * User: VitProg
 * Date: 18.04.2017
 * Time: 19:45
 */

namespace app\modules\parser;

use yii\base\Application;
use yii\base\BootstrapInterface;
use \yii\base\Module as BaseModule;

class Module extends BaseModule implements BootstrapInterface
{
    public $controllerNamespace = 'app\modules\api\controllers';

    public function bootstrap($app)
    {
        if ($app instanceof \yii\console\Application) {
            $this->controllerNamespace = 'app\modules\parser\commands';
            $app->controllerMap['parse'] = [
                'class' => 'app\modules\parser\commands\ParseController',
                'module' => $this,
            ];
        }
    }


}
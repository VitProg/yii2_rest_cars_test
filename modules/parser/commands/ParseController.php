<?php
/**
 * Created by PhpStorm.
 * User: VitProg
 * Date: 18.04.2017
 * Time: 22:15
 */

namespace app\modules\parser\commands;


use app\modules\parser\parsers\BaseParser;
use GuzzleHttp\Client;
use phpQuery;
use phpQueryObject;
use yii\base\UnknownClassException;
use yii\console\Controller;
use yii\helpers\Console;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\helpers\Url;

class ParseController extends Controller
{
    protected function log(...$args)
    {
        echo date('Y-m-d H:i:s') . ': ' . join(
                ' -- ',
                array_map(
                    function ($i) {
                        return is_object($i) || is_array($i) ? Json::encode($i) : $i;
                    },
                    $args
                )
            ) . PHP_EOL;
    }

//    public function behaviors()
//    {
//        return [
//            'rateLimiter' => [
//                'class' => RateLimiter::className(),
//            ],
//        ];
//    }

    /**
     * @param string $parser
     * @param integer|null $max_pages
     * @return integer
     * @throws UnknownClassException
     */
    public function actionIndex($parser, $max_pages = null)
    {
        $fileLock = fopen("lock", "w+");
        // пытаемся залочить файл, если неудача, то скрипт уже запущен парралельно
        if (flock($fileLock, LOCK_EX | LOCK_NB) === false) {
            echo 'The script is already running. You can not run two or more scripts in parallel';
            return 1;
        }

        $parserClass = 'app\modules\parser\parsers\\' . Inflector::camelize($parser) . 'Parser';

        \Yii::info('$parserClass = ' . $parserClass);
        \Yii::warning('$parserClass = ' . $parserClass);
        \Yii::error('$parserClass = ' . $parserClass);

        if (class_exists($parserClass) === false) {
            throw new UnknownClassException('Parser `' . $parser . '` class `' . $parserClass . '` not found!');
        }

        /** @var BaseParser $parser */
        $parser = new $parserClass();

        $restApiUrl = Url::to(['/api/cars']);

        $parser->setApiSaveUrl($restApiUrl);

        $parser->runParse($max_pages ? intval($max_pages) : null);

        \Yii::info('FIN');

        // снимаем блокировку в конце обработки запроса
        flock($fileLock, LOCK_UN);
        fclose($fileLock);

        return 0;
    }

}
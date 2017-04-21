<?php
/**
 * Created by PhpStorm.
 * User: VitProg
 * Date: 19.04.2017
 * Time: 14:08
 */

namespace app\modules\parser\parsers;

use Yii;
use yii\helpers\Json;
use yii\httpclient\Client;

abstract class BaseParser
{
    const MAX_PAGES = 500;

    /** @var  Client */
    protected $httpClient;
    /** @var  Client */
    protected $restClient;

    protected $apiSaveUrl = null;
    protected $maxPages = null;
    protected $carsCount = 0;
    protected $pageNumber;
    protected $maxPagesOnRun;

    public function __construct()
    {
        $this->httpClient = new Client();
        $this->restClient = new Client();
    }

    public function setApiSaveUrl($url)
    {
        $this->apiSaveUrl = $url;
    }

    public function getMaxPages()
    {
        return $this->maxPagesOnRun ? min($this->maxPagesOnRun, $this->maxPages) : $this->maxPages;
    }

    abstract public function getBaseUrl();

    public function getUrl($page = 1)
    {
        return $this->getBaseUrl() . $page;
    }

    public function runParse($maxPages = null)
    {

        Yii::info('Parser `' . static::class . '` start...', 'parser');

        $this->maxPages = min(static::MAX_PAGES, $this->maxPages);
        $this->maxPagesOnRun = $maxPages;
        if ($maxPages) {
            $this->maxPages = min($maxPages, $this->maxPages);
        }

        $this->carsCount = 0;

        $this->pageNumber = 1;
        $maxPageNumber = null;

        while ($this->pageNumber !== false) {
            $url = $this->getBaseUrl() . $this->pageNumber;

            Yii::info('Page number: ' . $this->pageNumber, 'parser');
            Yii::info('Load url: ' . $url, 'parser');

            $request = $this->httpClient->createRequest()
                ->setMethod('GET')
                ->setUrl($url);

            $response = $request->send();

            if ($response->isOk === false) {
                // fixme error
                continue;
            }

            $items = $this->parsePage($response->getContent());

            Yii::info('Parsing page completed, save by rest api...', 'parser');

            if ($items === false) {
                Yii::info('Parsing end...', 'parser');
                unset($response);
                unset($items);
                unset($res);
                break;
            }

            $this->saveItems($items);

            $this->pageNumber++;
            unset($items);
            unset($res);

            if ($this->pageNumber >= $this->getMaxPages()) {
                Yii::info('Final page, finish parsing...', 'parser');
                unset($response);
                break;
            }

            usleep(rand(100, 500));
        }

        Yii::info('Parsing `' . static::class . '` completed!', 'parser');
        Yii::info('Cars count: ' . $this->carsCount, 'parser');

        return true;
    }

    /**
     * @param $content
     * @return mixed
     */
    abstract public function parsePage($content);

    public function saveItems($items)
    {
        $errorsCounter = 0;

        foreach ($items as $item) {
            $request = $this->restClient->createRequest()
                ->setMethod('POST')
                ->setUrl($this->apiSaveUrl)
                ->setData($item);
            $response = $request->send();

            if ($response->isOk === false) {
                $errorsCounter++;
                Yii::info('Save error ' . Json::encode($item), 'parser');
                Yii::info('response: ' . $response->getContent(), 'parser');

                // вторая попытка с небольшой задержкой
                usleep(rand(10, 100));
                Yii::info('try again...', 'parser');
                $response = $request->send();
                if ($response->isOk === false) {
                    Yii::info('...error: ' . Json::encode($item), 'parser');
                } else {
                    Yii::info('...ok', 'parser');
                }
            }
        }

        Yii::info('Save finished...', 'parser');
        if ($errorsCounter > 0) {
            Yii::info('Errors: ' . $errorsCounter, 'parser');
        }
    }

}
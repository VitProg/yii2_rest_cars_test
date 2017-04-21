<?php
/**
 * Created by PhpStorm.
 * User: VitProg
 * Date: 19.04.2017
 * Time: 14:09
 */

namespace app\modules\parser\parsers;


use phpQuery;
use phpQueryObject;
use Yii;

class AmRuParser extends BaseParser
{
    protected $brands = null;

    public function getBaseUrl()
    {
        return 'https://spb.am.ru/used/search/?p1118=null%2C0&p=';
//        return 'https://spb.am.ru/used/search/?p1118=1%2C0&p=';
    }

    /**
     * @param $content
     * @return mixed
     */
    public function parsePage($content)
    {
        // подключаем phpQuery для обработки страницы
        $document = phpQuery::newDocumentHTML($content);

        if ($this->brands === null) {
            // считываем все бренды
            Yii::info('Parse brands', 'parser');
            $this->brands = [];
            $brandsElem = $document->find('.js-brand-model__brands option');
            foreach ($brandsElem as $brandElem) {
                $brandElem = pq($brandElem);
                if (intval($brandElem->attr('value')) > 0) {
                    $this->brands[] = $brandElem->text();
                }
            }
            sort($this->brands);
            $this->brands = array_unique($this->brands);
            Yii::info('Brands count: ' . count($this->brands), 'parser');
            // ----
        }

        if ($this->maxPages === null) {
            // считываем кол-во страниц
            Yii::info('Parse pages count', 'parser');
            $this->maxPages = static::MAX_PAGES;
            $paginatorAmountText = $document->find('.paginator-amount')->text(); // 9 880 объявлений, 824 страницы
            preg_match('/([\d ]+)страницы/', $paginatorAmountText, $matches);
            if (count($matches) === 2) {
                $this->maxPages = intval(str_replace(' ', '', $matches[1]));
                $this->maxPages = min(static::MAX_PAGES, $this->maxPages);
            }
            Yii::info('Pages count: ' . $this->maxPages, 'parser');
        }


        $items = $document->find('.b-snippet.b-snippet-big');

        Yii::info('Parse items', 'parser');
        Yii::info('Items count: ' . count($items), 'parser');

        if (count($items) <= 0) {
            Yii::info('Items empty, finish parsing...', 'parser');
            return false;
        }

        if ($this->pageNumber > $this->getMaxPages()) {
            Yii::info('Final page, finish parsing...', 'parser');
            return false;
        }

        $cars = [];

        /** @var phpQueryObject $item */
        foreach ($items as $item) {
            $elem = pq($item);

            $titleElem = $elem->find('.b-snippet__title-big');
            $linkElem = $titleElem->find('a:first');

            //            $link =  $linkElem->attr('href'); //https://spb.am.ru/used/toyota/corolla/avs-toyota-tsentr-parnas--bc78075e/#ofrs
            $title = $linkElem->text();
            $color = $titleElem->find('.color')->attr('title');
            $year = $titleElem->find('span:first')->text();
            $cost = $elem->find('.price-block > a')->text();

            $mileage = $elem->find('.b-car__info_list > li:first')->text();
            $mileage = preg_replace('/[^\d]/', '', $mileage);
            $mileage = intval($mileage);

            $titleArr = preg_split('/\s/', $title);

            $brand = $titleArr[0] ?? '';
            $model = join(' ', array_slice($titleArr, 1));
            for ($i = 0; $i < count($titleArr); $i++) {
                $brandTest = join(' ', array_slice($titleArr, 0, $i + 1));
                if (in_array($brandTest, $this->brands)) {
                    $brand = $brandTest;
                    $model = join(' ', array_slice($titleArr, $i + 1));
                    break;
                }
            }

            $year = substr($year, 1, 4);
            $cost = intval(str_replace(' ', '', $cost));

            $car = [
                'brand' => $brand,
                'model' => $model,
                'year' => $year,
                'cost' => $cost,
                'color' => $color,
                'mileage' => $mileage,
            ];
            $cars[] = $car;
            $this->carsCount++;
        }
//        var_dump($cars);die();

        return $cars;
    }
}
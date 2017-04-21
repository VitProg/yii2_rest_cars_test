<?php
/**
 * Created by PhpStorm.
 * User: VitProg
 * Date: 21.04.2017
 * Time: 14:25
 */

namespace tests\parsers;


use app\modules\parser\parsers\AmRuParser;
use Yii;


class AmRuParserTest extends \PHPUnit_Framework_TestCase
{
    protected $pageContent = null;
    protected $carsItems = null;

    public function testLoadPage() {
        $this->pageContent = file_get_contents(Yii::getAlias('@test/_data/amru_test.html'));
        expect($this->pageContent)->notNull();
        expect(empty($this->pageContent) === false)->true();
    }

    public function testParsePage() {
        if (!$this->pageContent) {
            $this->pageContent = file_get_contents(Yii::getAlias('@test/_data/amru_test.html'));
        }
        expect($this->pageContent)->notNull();

        $parser = new AmRuParser();
        $this->carsItems = $parser->parsePage($this->pageContent);
        expect(count($this->carsItems))->equals(15);
        expect($this->carsItems[0]['brand'])->equals('SsangYong');
        expect($this->carsItems[11]['model'])->equals('Creta');
        expect($this->carsItems[7]['year'])->equals('2017');
        expect($this->carsItems[4]['cost'])->equals(809900);
        expect($this->carsItems[13]['color'])->equals('черный');
        expect($this->carsItems[1]['mileage'])->equals(155100);
        expect($this->carsItems[14]['mileage'])->equals(0);
    }
}

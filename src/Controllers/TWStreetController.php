<?php

namespace Hsquare\TWStreet\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use Excel;
use Symfony\Component\DomCrawler\Crawler;
use Colors\Color;

class TWStreetController
{
    private $postalIndex = 'http://www.post.gov.tw/post/internet/Postal/index.jsp?ID=207';

    private $postalUri = 'http://www.post.gov.tw/post/internet/Postal/streetNameData.jsp';

    private $city = [];

    private $cityArea = [];

    private $cityAreaCount = [];

    /**
     * Colors\Color instance
     *
     * @var Colors\Color $color
     */
    protected $color;   

    /**
     * Create a new TWStreetController instance.
     *
     * @return void
     */
    public function __construct(Color $color)
    {
        $this->color = $color;
    }

    /**
     * Console-Color White string.
     *
     * @param  string  $str
     * @return void
     */
    public function info($str)
    {
		// $c = $this->color;
		// echo $c($str)->white();
		echo $str.PHP_EOL;;
    }

    /**
     * Console-Color Green string.
     *
     * @param  string  $str
     * @return void
     */
    public function line($str)
    {
		$c = $this->color;
		echo $c($str)->green().PHP_EOL;
    }

    /**
     * Get data of counties, districts, and streets and export excel file.
     *
     * @return mixed
     */
    public function getData()
    {
        $this->getPostalCity();
        $rows = $this->getStreets();
        $this->createExcel($rows);
    }

    /**
     * Get streets.
     *
     * @return array   $rows
     */
    public function getStreets()
    {
        $cityLength = count($this->city);
        $rows = [];
        $rows[0] = ['縣市', '鄉鎮市區', '道路或街名或村里名稱'];
        for ($i = 0; $i < $cityLength; $i++) {
            $count = $this->cityAreaCount[$i + 1] - $this->cityAreaCount[$i];
            $start = $this->cityAreaCount[$i] + 1;
            $end = $this->cityAreaCount[$i + 1];
            for ($j = $start; $j <= $end; $j++) {
                $this->info($this->city[$i]." ".$this->cityArea[$j]);
                $response = $this->requestPostalUri($this->city[$i], $this->cityArea[$j]);
                // $body = $response->getBody();
                $contents = $response->getBody()->getContents();
                if ($response) {
                    $this->parseXML($contents, $this->city[$i], $this->cityArea[$j], $rows);
                } else {
                    $this->error(__FUNCTION__.": No Response from ".$this->postalUri);
                    exit(2);
                }
            }
        }
        return $rows;
    }

    /**
     * Parse XML.
     *
     * @param  string  $contents
     * @param  string  $city
     * @param  string  $cityArea
     * @param  array   &$rows
     * @return void
     */
    public function parseXML($contents, $city, $cityArea, &$rows)
    {
        $xml = simplexml_load_string($contents);
        $this->line($city." ".$cityArea." 總數：".count($xml->array->array0));
        $streets = $xml->array->array0;
        foreach ($streets as $street) {
            $cols = [
                        'county'   => $city,
                        'district' => $cityArea,
                        'street'   => $street
                    ];
            $rows[] = $cols;
        }
    }

    /**
     * Send Request to PostalUri.
     *
     * @param  string  $city
     * @param  string  $cityArea
     * @return mixed
     */
    public function requestPostalUri($city, $cityArea)
    {
        $response = null;
        sleep(mt_rand(1, 2));
        // sleep(1);
        $client = new Client();
        try {
            $response = $client->request('POST', $this->postalUri, [
                'form_params' => [
                    'city'     => $city,
                    'cityarea' => $cityArea
                ],
                'timeout' => 5
            ]);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $this->error(Psr7\str($e->getResponse()));
            } else {
                $this->error(__FUNCTION__.": No Response from ".$this->postalUri);
            }
            exit(1);
        }
        return $response;
    }

    /**
     * Create Excel.
     *
     * @param  array  $rows
     * @return void
     */
    public function createExcel($rows)
    {
        date_default_timezone_set('Asia/Taipei');
        $now = date("YmdHis");
        Excel::create('street'.$now, function ($excel) use ($rows) {
            $excel->sheet('street', function ($sheet) use ($rows) {
                // Set width for multiple cells
                $sheet->setWidth(array(
                    'A'     =>  10,
                    'B'     =>  12,
                    'C'     =>  25
                ));
                $sheet->fromArray($rows, null, 'A1', false, false);
            });
        })->store('xlsx', storage_path('excel/exports'));
    }

    /**
     * Get Postal City.
     *
     * @return void
     */
    public function getPostalCity()
    {
        $client = new Client();
        try {
            $response = $client->request('GET', $this->postalIndex, ['timeout' => 5]);
        } catch (RequestException $e) {
            // $this->error(Psr7\str($e->getRequest()));
            if ($e->hasResponse()) {
                $this->error(Psr7\str($e->getResponse()));
            } else {
                $this->error(__FUNCTION__.": No Response from ".$this->postalIndex);
            }
            exit(1);
        }

        $html = $response->getBody()->getContents();
        $this->parseHTML($html);
        // $crawler = new Crawler($html);

        // $this->city = $crawler->filter('select[name="city"]')->filter('option')->each(function (Crawler $node, $i) {
            // do not return index 0 - "請選擇縣市"
            // if ($i) {
                // return $node->text();
            // }
        // });

        // index 0 is empty value, Shift an element off the beginning of array
        // array_shift($this->city);
        // print_r($this->city);
        // $pattern = '/^cityarea_account\[\d+\] = (.*);$/m';
        // preg_match_all($pattern, $html, $matches);
        // $this->cityAreaCount = $matches[1];
        // print_r($this->cityAreaCount);

        // $pattern = '/^cityarea\[\d+\] = \'(.*)\';$/m';
        // preg_match_all($pattern, $html, $matches);
        // array_unshift($matches[1], '');
        // $this->cityArea = $matches[1];
        // print_r($this->cityArea);
    }
    
    /**
     * Parse HTML.
     *
     * @return void
     */
    public function parseHTML($html)
    {
        $crawler = new Crawler($html);
        // echo $crawler->filter('select[name="city"]')->text();
        $this->city = $crawler->filter('select[name="city"]')->filter('option')->each(function (Crawler $node, $i) {
            // echo $i." ".$node->text().",";
            // echo gettype($i).' $i '.$node->text().",";
            // do not return index 0 - "請選擇縣市"
            if ($i) {
                return $node->text();
            }
        });

        // index 0 is empty value, Shift an element off the beginning of array
        array_shift($this->city);
        print_r($this->city);
        $pattern = '/^cityarea_account\[\d+\] = (.*);$/m';
        preg_match_all($pattern, $html, $matches);
        $this->cityAreaCount = $matches[1];
        print_r($this->cityAreaCount);

        $pattern = '/^cityarea\[\d+\] = \'(.*)\';$/m';
        preg_match_all($pattern, $html, $matches);
        array_unshift($matches[1], '');
        $this->cityArea = $matches[1];
        print_r($this->cityArea);
    }
}

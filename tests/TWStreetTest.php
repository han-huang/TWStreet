<?php

// use PHPUnit\Framework\TestCase;
use Hsquare\TWStreet\Controllers\TWStreetController;
use Colors\Color;
// use Maatwebsite\Excel\Facades\Excel;
use Hsquare\TWStreet\TWStreetServiceProvider;

// use Illuminate\Contracts\Foundation\Application;
// use Mockery;
// use Illuminate\Foundation\AliasLoader;
// use Maatwebsite\Excel\Facades\Excel as ExcelFacade;

// class TWStreetTest extends TestCase
class TWStreetTest extends Illuminate\Foundation\Testing\TestCase
{
    /**
     * @var Mockery\Mock
     */
    // protected $application_mock;

    // private $TWStreet;
    private static $TWStreet;

    // private $serviceProvider;

    private $html = __DIR__."/post.html";

    private $xml = __DIR__."/streetNameData.xml";

    private $city = '基隆市';

    private $cityArea = '仁愛區';

    private $rows = [];

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../../../../bootstrap/app.php';

        $app->register(TWStreetServiceProvider::class);

        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    public static function setUpBeforeClass()
    {
        self::$TWStreet = new TWStreetController(new Color());
    }
    
    public function setUp()
    {
        parent::setUp();
        // $this->TWStreet = new TWStreetController(new Color());

        // $this->setUpMocks();
        // $this->serviceProvider = new TWStreetServiceProvider($this->application_mock);
        // $this->serviceProvider->register();
        // $loader = AliasLoader::getInstance();
        // $loader->alias('Excel', ExcelFacade::class);
    }

    // protected function setUpMocks()
    // {
        // $this->application_mock = Mockery::mock(Application::class);
    // }

    public function testGetPostalCity()
    {
        // $this->TWStreet->getPostalCity();
        self::$TWStreet->getPostalCity();
        // $this->assertTrue($this->TWStreet instanceof TWStreetController);
        $this->assertTrue(self::$TWStreet instanceof TWStreetController);
        $this->runAssert();
    }

    /**
     * Reference http://eliysha.info/entry/phpunit-how-to-test-private-properties
     *
     * Return value of a private property using ReflectionClass
     *
     * @param TWStreetController $instance
     * @param string $property
     *
     * @return mixed
     */
    private function _getInnerPropertyValueByReflection(TWStreetController $instance, $property)
    {
        $reflector = new \ReflectionClass($instance);
        $reflector_property = $reflector->getProperty($property);
        $reflector_property->setAccessible(true);

        return $reflector_property->getValue($instance);
    }

    public function testParseHTML()
    {
        $html = $this->freadFile($this->html);
        // var_dump($html);
        // $this->TWStreet->parseHTML($html);
        self::$TWStreet->parseHTML($html);
        $this->runAssert();
    }

    public function freadFile($target)
    {
        $myfile = fopen($target, "r") or die("Unable to open file!");
        $html = fread($myfile, filesize($target));
        fclose($myfile);
        return $html;
    }

    public function runAssert()
    {
        // $reflected_data = $this->_getInnerPropertyValueByReflection($this->TWStreet, 'city');
        $reflected_data = $this->_getInnerPropertyValueByReflection(self::$TWStreet, 'city');
        $this->assertTrue(in_array('基隆市', $reflected_data));
        $this->assertTrue(in_array('連江縣', $reflected_data));
        // print_r($reflected_data);
        // $reflected_data = $this->_getInnerPropertyValueByReflection($this->TWStreet, 'cityArea');
        $reflected_data = $this->_getInnerPropertyValueByReflection(self::$TWStreet, 'cityArea');
        $this->assertTrue(in_array('仁愛區', $reflected_data));
        $this->assertTrue(in_array('東引鄉', $reflected_data));
        // print_r($reflected_data);
        // $reflected_data = $this->_getInnerPropertyValueByReflection($this->TWStreet, 'cityAreaCount');
        $reflected_data = $this->_getInnerPropertyValueByReflection(self::$TWStreet, 'cityAreaCount');
        $this->assertTrue(in_array(7, $reflected_data));
        $this->assertTrue(in_array(367, $reflected_data));
        // print_r($reflected_data);
    }

    public function testRequestPostalUri()
    {
        // $response = $this->TWStreet->requestPostalUri($this->city, $this->cityArea);
        $response = self::$TWStreet->requestPostalUri($this->city, $this->cityArea);
        return $response;
    }

    /**
     * @depends testRequestPostalUri
     *
     * @param $response
     */
    public function testParseXML($response)
    {
        $contents = $response->getBody()->getContents();
        // $this->TWStreet->parseXML($contents, $this->city, $this->cityArea, $this->rows);
        self::$TWStreet->parseXML($contents, $this->city, $this->cityArea, $this->rows);
        $this->assertEquals(43, count($this->rows));
        // var_dump($this->rows);

        // Once the test method has finished running, whether it succeeded or failed,
        // another template method called tearDown() is invoked.
        // tearDown() is where you clean up the objects against which you tested.
        return $this->rows;
    }

    /**
     * @depends testParseXML
     */
    public function testCreateExcel($rows)
    {
        $this->assertEquals(43, count($rows));
        // var_dump($rows);
        // $output = $this->TWStreet->createExcel($rows);
        $output = self::$TWStreet->createExcel($rows);
        // var_dump($output);
        $filePath = storage_path('excel/exports/'.$output);
        $this->assertTrue(file_exists($filePath));
        $ret = $this->loadExcel($filePath);
        // var_dump($ret);
        $this->assertEquals(43, count($ret));
    }

    /**
     * Load Excel.
     *
     * @param  string $file
     * @return array  $rows
     */
    public function loadExcel($filePath)
    {
        $rows = [];
        Excel::load($filePath, function ($reader) use (&$rows) {
            $reader->noHeading();
            $results = $reader->all();
            $rows = $results->toArray();
        });
        return $rows;
    }
}

<?php
/**
 * Part of JsonMapper
 *
 * PHP version 5
 *
 * @category Netresearch
 * @package  JsonMapper
 * @author   Christian Weiske <christian.weiske@netresearch.de>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     http://www.netresearch.de/
 */

require_once 'JsonMapperTest/Array.php';
require_once 'JsonMapperTest/Broken.php';
require_once 'JsonMapperTest/DependencyInjector.php';
require_once 'JsonMapperTest/Simple.php';
require_once 'JsonMapperTest/Logger.php';
require_once 'JsonMapperTest/PrivateWithSetter.php';
require_once 'JsonMapperTest/ValueObject.php';
require_once 'JsonMapperTest/SimpleBase.php';
require_once 'JsonMapperTest/SimpleBaseWithMissingDiscrimType.php';
require_once 'JsonMapperTest/DerivedClass.php';
require_once 'JsonMapperTest/DerivedClass2.php';
require_once 'JsonMapperTest/FactoryMethod.php';
require_once 'JsonMapperTest/FactoryMethodWithError.php';
require_once 'JsonMapperTest/MapsWithSetters.php';
require_once 'JsonMapperTest/ClassWithCtor.php';
require_once 'JsonMapperTest/ComplexClassWithCtor.php';
require_once 'JsonMapperTest/JsonMapperCommentsDiscardedException.php';

if (PHP_VERSION_ID >= 70000) {
    require_once 'JsonMapperTest/Php7TypedClass.php';
}

if (PHP_VERSION_ID >= 70100) {
    require_once 'JsonMapperTest/Php7_1TypedClass.php';
}

use apimatic\jsonmapper\JsonMapper;
use apimatic\jsonmapper\JsonMapperException;

/**
 * Unit tests for JsonMapper
 *
 * @category Netresearch
 * @package  JsonMapper
 * @author   Christian Weiske <christian.weiske@netresearch.de>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     http://www.netresearch.de/
 * @covers \apimatic\jsonmapper\JsonMapper
 * @covers \apimatic\jsonmapper\TypeCombination
 * @covers \apimatic\jsonmapper\JsonMapperException
 */
class JsonMapperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test for "@var string"
     */
    public function testMapSimpleString()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"str":"stringvalue"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertTrue(is_string($sn->str));
        $this->assertEquals('stringvalue', $sn->str);
    }
    
    /**
     * Test for "@var string"
     */
    public function testMapSimpleStringWithMapClass()
    {
        $jm = new JsonMapper();
        $sn = $jm->mapClass(
            json_decode('{"str":"stringvalue"}'),
            'JsonMapperTest_Simple'
        );
        $this->assertTrue(is_string($sn->str));
        $this->assertEquals('stringvalue', $sn->str);
    }

    /**
     * Test for "@var float"
     */
    public function testMapSimpleFloat()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"fl":"1.2"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertTrue(is_float($sn->fl));
        $this->assertEquals(1.2, $sn->fl);
    }

    /**
     * Test for "@var double"
     */
    public function testMapSimpleDouble()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"db":"1.2"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertTrue(is_float($sn->db));
        $this->assertEquals(1.2, $sn->db);
    }

    /**
     * Test for "@var bool"
     */
    public function testMapSimpleBool()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"pbool":"1"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertTrue(is_bool($sn->pbool));
        $this->assertEquals(true, $sn->pbool);
    }

    /**
     * Test for "@var boolean"
     */
    public function testMapSimpleBoolean()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"pboolean":"0"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertTrue(is_bool($sn->pboolean));
        $this->assertEquals(false, $sn->pboolean);
    }

    /**
     * Test for "@var int"
     */
    public function testMapSimpleInt()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"pint":"123"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertTrue(is_int($sn->pint));
        $this->assertEquals(123, $sn->pint);
    }

    /**
     * Test for "@var integer"
     */
    public function testMapSimpleInteger()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"pinteger":"12345"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertTrue(is_int($sn->pinteger));
        $this->assertEquals(12345, $sn->pinteger);
    }

    /**
     * Test for "@var mixed"
     */
    public function testMapSimpleMixed()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"mixed":12345}'),
            new JsonMapperTest_Simple()
        );
        $this->assertTrue(is_int($sn->mixed));
        $this->assertEquals('12345', $sn->mixed);

        $sn = $jm->map(
            json_decode('{"mixed":"12345"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertTrue(is_string($sn->mixed));
        $this->assertEquals(12345, $sn->mixed);
    }

    /**
     * Test for "@var int|null" with int value
     */
    public function testMapSimpleNullableInt()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"pnullable":0}'),
            new JsonMapperTest_Simple()
        );
        $this->assertTrue(is_int($sn->pnullable));
        $this->assertEquals(0, $sn->pnullable);
    }

    /**
     * Test for "@var int|null" with null value
     */
    public function testMapSimpleNullableNull()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"pnullable":null}'),
            new JsonMapperTest_Simple()
        );
        $this->assertNull($sn->pnullable);
        $this->assertEquals(null, $sn->pnullable);
    }

    /**
     * Test for "@var int|null" with string value
     */
    public function testMapSimpleNullableWrong()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"pnullable":"12345"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertTrue(is_int($sn->pnullable));
        $this->assertEquals(12345, $sn->pnullable);
    }

    /**
     * Test for variable with no @var annotation
     */
    public function testMapSimpleNoType()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"notype":{"k":"v"}}'),
            new JsonMapperTest_Simple()
        );
        $this->assertTrue(is_object($sn->notype));
        $this->assertEquals((object) array('k' => 'v'), $sn->notype);
    }

    /**
     * Variable with an underscore
     */
    public function testMapSimpleUnderscore()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"under_score":"f"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertTrue(is_string($sn->under_score));
        $this->assertEquals('f', $sn->under_score);
    }

    /**
     * Variable with an underscore and a setter method
     */
    public function testMapSimpleUnderscoreSetter()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"under_score_setter":"blubb"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertTrue(is_string($sn->internalData['under_score_setter']));
        $this->assertEquals(
            'blubb', $sn->internalData['under_score_setter']
        );
    }

    /**
     * Test for a class name "@var Classname"
     */
    public function testMapObject()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"simple":{"str":"stringvalue"}}'),
            new JsonMapperTest_Simple()
        );
        $this->assertTrue(is_object($sn->simple));
        $this->assertInstanceOf('JsonMapperTest_Simple', $sn->simple);
        $this->assertEquals('stringvalue', $sn->simple->str);
    }

    /**
     * Test for an array of classes "@var Classname[]"
     */
    public function testMapTypedArray()
    {
        $jm = new JsonMapper();
        $jm->arChildClasses['JsonMapperTest_Simple'] = array();
        $sn = $jm->map(
            json_decode('{"typedArray":[{"str":"stringvalue"},{"fl":"1.2"}]}'),
            new JsonMapperTest_Array()
        );
        $this->assertTrue(is_array($sn->typedArray));
        $this->assertEquals(2, count($sn->typedArray));
        $this->assertInstanceOf('JsonMapperTest_Simple', $sn->typedArray[0]);
        $this->assertInstanceOf('JsonMapperTest_Simple', $sn->typedArray[1]);
        $this->assertEquals('stringvalue', $sn->typedArray[0]->str);
        $this->assertEquals(1.2, $sn->typedArray[1]->fl);
    }

    /**
     * Test for a map of classes "@var array<string,Classname>"
     */
    public function testMapTypedMap()
    {
        $jm = new JsonMapper();
        $jm->arChildClasses['JsonMapperTest_Simple'] = array();
        $sn = $jm->map(
            json_decode('{"typedMap":{"key0":{"str":"stringvalue"},"key1":{"fl":"1.2"}}}'),
            new JsonMapperTest_Array()
        );
        $this->assertTrue(is_array($sn->typedMap));
        $this->assertEquals(2, count($sn->typedMap));
        $this->assertInstanceOf('JsonMapperTest_Simple', $sn->typedMap['key0']);
        $this->assertInstanceOf('JsonMapperTest_Simple', $sn->typedMap['key1']);
        $this->assertEquals('stringvalue', $sn->typedMap['key0']->str);
        $this->assertEquals(1.2, $sn->typedMap['key1']->fl);
    }

    /**
     * Test for an array of map of classes "@var array<string,Classname>[]"
     */
    public function testMapTypedArrayOfMap()
    {
        $jm = new JsonMapper();
        $jm->arChildClasses['JsonMapperTest_Simple'] = array();
        $sn = $jm->map(
            json_decode('{"typedArrayOfMap":[{"class1":{"str":"stringvalue"},"class2":{"fl":"1.2"}},{"class3":{"pbool":true}}]}'),
            new JsonMapperTest_Array()
        );
        $this->assertTrue(is_array($sn->typedArrayOfMap));
        $this->assertEquals(2, count($sn->typedArrayOfMap));
        $this->assertTrue(is_array($sn->typedArrayOfMap[0]));
        $this->assertTrue(is_array($sn->typedArrayOfMap[1]));
        $this->assertEquals(2, count($sn->typedArrayOfMap[0]));
        $this->assertInstanceOf('JsonMapperTest_Simple', $sn->typedArrayOfMap[0]['class1']);
        $this->assertInstanceOf('JsonMapperTest_Simple', $sn->typedArrayOfMap[0]['class2']);
        $this->assertEquals(1, count($sn->typedArrayOfMap[1]));
        $this->assertInstanceOf('JsonMapperTest_Simple', $sn->typedArrayOfMap[1]['class3']);
        $this->assertEquals('stringvalue', $sn->typedArrayOfMap[0]['class1']->str);
        $this->assertEquals(1.2, $sn->typedArrayOfMap[0]['class2']->fl);
        $this->assertEquals(true, $sn->typedArrayOfMap[1]['class3']->pbool);
    }

    public function testMapTypedWithNullValue(Type $var = null)
    {
        $jm = new JsonMapper();
        $sn = $jm->mapClassArray(null, new JsonMapperTest_Array());
        $this->assertEquals(null, $sn);
    }

    /**
     * Test for an array of classes "@var ClassName[]" with
     * flat/simple json values (string, float)
     */
    public function testMapTypedSimpleArray()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"typedSimpleArray":["2014-01-02",null,"2014-05-07"]}'),
            new JsonMapperTest_Array()
        );
        $this->assertTrue(is_array($sn->typedSimpleArray));
        $this->assertEquals(3, count($sn->typedSimpleArray));
        $this->assertInstanceOf('DateTime', $sn->typedSimpleArray[0]);
        $this->assertNull($sn->typedSimpleArray[1]);
        $this->assertInstanceOf('DateTime', $sn->typedSimpleArray[2]);
        $this->assertEquals(
            '2014-01-02', $sn->typedSimpleArray[0]->format('Y-m-d')
        );
        $this->assertEquals(
            '2014-05-07', $sn->typedSimpleArray[2]->format('Y-m-d')
        );
    }
    
    public function testMapClassSimple()
    {
        $jm = new JsonMapper();
        $sn = $jm->mapClass(
            json_decode('{"str":"stringvalue"}'),
            'JsonMapperTest_Simple'
        );
        $this->assertTrue(is_string($sn->str));
        $this->assertEquals('stringvalue', $sn->str);
    }

    public function testMapClassNullJson()
    {
        $jm = new JsonMapper();
        $sn = $jm->mapClass(null, 'JsonMapperTest_Simple');
        $this->assertEquals(null, $sn);
    }

    public function testMapClassWithNonObject()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('JsonMapper::mapClass() requires first argument to be an object, integer given.');
        $jm = new JsonMapper();
        $sn = $jm->mapClass(123, 'JsonMapperTest_Simple');
    }

    public function testOpCacheSaveCommentsDiscarded()
    {
        echo ("Starting testOpCacheSaveCommentsDiscarded!" . "\n");

        $this->expectException(JsonMapperException::class);
        $this->expectExceptionMessage("Comments cannot be discarded in the configuration file i.e. the php.ini file; doc comments are a requirement for JsonMapper. Following configuration keys must have a value set to \"1\": zend_optimizerplus.save_comments, opcache.save_comments.");

        $config = parse_ini_file(php_ini_loaded_file());

        $config["opcache.save_comments"] = "0";

        try
        {
            echo ("Inside Try!" . "\n");
            new JsonMapperCommentsDiscardedException($config);
            echo ("After JsonMapper Instantiation!" . "\n");
        }

        catch(JsonMapperException $ex)
        {
            echo ("Inside specific JsonMapperException catch!" . "\n");
            print_r($ex instanceof JsonMapperException . "\n");
            fwrite(STDERR, print_r($ex instanceof JsonMapperException . "\n", TRUE));
            echo ("Ending specific JsonMapperException catch!" . "\n");
        }

        catch (Exception $ex)
        {
            echo ("Inside generic JsonMapperException catch!" . "\n");
            print_r(gettype($ex) . "\n");
            print_r($ex  . "\n");
            fwrite(STDERR, print_r(gettype($ex) . "\n", TRUE));
            fwrite(STDERR, print_r($ex  . "\n", TRUE));
            fwrite(STDERR, print_r("In generic Exception block!"  . "\n", TRUE));
            echo ("Ending generic JsonMapperException catch!" . "\n");
        }
    }

    public function testZendOptimizerPlusCommentsDiscarded()
    {
        $this->expectException(JsonMapperException::class);
        $this->expectExceptionMessage("Comments cannot be discarded in the configuration file i.e. the php.ini file; doc comments are a requirement for JsonMapper. Following configuration keys must have a value set to \"1\": zend_optimizerplus.save_comments, opcache.save_comments.");

        $config = parse_ini_file(php_ini_loaded_file());

        $config["zend_optimizerplus.save_comments"] = "0";

        new JsonMapperCommentsDiscardedException($config);
    }

    public function testMapNullJson()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('JsonMapper::map() requires first argument to be an object, NULL given.');
        $jm = new JsonMapper();
        $sn = $jm->map(null, new JsonMapperTest_Simple());
    }

    public function testMapNullObject()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('JsonMapper::map() requires second argument to be an object, NULL given.');
        $jm = new JsonMapper();
        $sn = $jm->map(new stdClass(), null);
    }

    public function testMapArrayJsonNoTypeEnforcement()
    {
        $jm = new JsonMapper();
        $jm->bEnforceMapType = false;
        $sn = $jm->map(array(), new JsonMapperTest_Simple());
        $this->assertInstanceOf('JsonMapperTest_Simple', $sn);
    }

    /**
     * Test for an array of float "@var float[]"
     */
    public function testFlArray()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"flArray":[1.23,3.14,2.048]}'),
            new JsonMapperTest_Array()
        );
        $this->assertTrue(is_array($sn->flArray));
        $this->assertEquals(3, count($sn->flArray));
        $this->assertTrue(is_float($sn->flArray[0]));
        $this->assertTrue(is_float($sn->flArray[1]));
        $this->assertTrue(is_float($sn->flArray[2]));
    }

    /**
     * Test for an array of strings - "@var string[]"
     */
    public function testStrArray()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"strArray":["str",false,2.048]}'),
            new JsonMapperTest_Array()
        );
        $this->assertTrue(is_array($sn->strArray));
        $this->assertEquals(3, count($sn->strArray));
        $this->assertTrue(is_string($sn->strArray[0]));
        $this->assertTrue(is_string($sn->strArray[1]));
        $this->assertTrue(is_string($sn->strArray[2]));
    }

    /**
     * Test for a map of strings - "@var array<string,string>"
     */
    public function testStrMap()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"strMap":{"key0":"str","key1":false,"key2":2.048}}'),
            new JsonMapperTest_Array()
        );
        $this->assertTrue(is_array($sn->strMap));
        $this->assertEquals(3, count($sn->strMap));
        $this->assertTrue(is_string($sn->strMap['key0']));
        $this->assertTrue(is_string($sn->strMap['key1']));
        $this->assertTrue(is_string($sn->strMap['key2']));
    }

    /**
     * Test for a map of array of strings - "@var array<string,string[]>"
     */
    public function testStrMapOfArray()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"strMapOfArray":{"key0":["str","other"],"key1":[false,3],"key2":[2.048,"asad"]}}'),
            new JsonMapperTest_Array()
        );
        $this->assertTrue(is_array($sn->strMapOfArray));
        $this->assertEquals(3, count($sn->strMapOfArray));
        $this->assertTrue(is_array($sn->strMapOfArray['key0']));
        $this->assertEquals(2, count($sn->strMapOfArray['key0']));
        $this->assertTrue(is_string($sn->strMapOfArray['key0'][0]));
        $this->assertTrue(is_string($sn->strMapOfArray['key0'][1]));
        $this->assertTrue(is_array($sn->strMapOfArray['key1']));
        $this->assertEquals(2, count($sn->strMapOfArray['key1']));
        $this->assertTrue(is_string($sn->strMapOfArray['key1'][0]));
        $this->assertTrue(is_string($sn->strMapOfArray['key1'][1]));
        $this->assertTrue(is_array($sn->strMapOfArray['key2']));
        $this->assertEquals(2, count($sn->strMapOfArray['key2']));
        $this->assertTrue(is_string($sn->strMapOfArray['key2'][0]));
        $this->assertTrue(is_string($sn->strMapOfArray['key2'][1]));
    }

    /**
     * Test for an array of map of strings - "@var array<string,string>[]"
     */
    public function testStrArrayOfMap()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"strArrayOfMap":[{"key0":"str","key1":"other"},{"key0":false,"key1":3},{"key0":2.048,"key1":"asad"}]}'),
            new JsonMapperTest_Array()
        );
        $this->assertTrue(is_array($sn->strArrayOfMap));
        $this->assertEquals(3, count($sn->strArrayOfMap));
        $this->assertTrue(is_array($sn->strArrayOfMap[0]));
        $this->assertEquals(2, count($sn->strArrayOfMap[0]));
        $this->assertTrue(is_string($sn->strArrayOfMap[0]['key0']));
        $this->assertTrue(is_string($sn->strArrayOfMap[0]['key1']));
        $this->assertTrue(is_array($sn->strArrayOfMap[1]));
        $this->assertEquals(2, count($sn->strArrayOfMap[1]));
        $this->assertTrue(is_string($sn->strArrayOfMap[1]['key0']));
        $this->assertTrue(is_string($sn->strArrayOfMap[1]['key1']));
        $this->assertTrue(is_array($sn->strArrayOfMap[2]));
        $this->assertEquals(2, count($sn->strArrayOfMap[2]));
        $this->assertTrue(is_string($sn->strArrayOfMap[2]['key0']));
        $this->assertTrue(is_string($sn->strArrayOfMap[2]['key1']));
    }

    /**
     * Test for "@var ArrayObject"
     */
    public function testMapArrayObject()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"pArrayObject":[{"str":"stringvalue"},{"fl":"1.2"}]}'),
            new JsonMapperTest_Array()
        );
        $this->assertInstanceOf('ArrayObject', $sn->pArrayObject);
        $this->assertEquals(2, count($sn->pArrayObject));
        $this->assertInstanceOf('\stdClass', $sn->pArrayObject[0]);
        $this->assertInstanceOf('\stdClass', $sn->pArrayObject[1]);
        $this->assertEquals('stringvalue', $sn->pArrayObject[0]->str);
        $this->assertEquals('1.2', $sn->pArrayObject[1]->fl);
    }

    /**
     * Test for "@var ArrayObject[Classname]"
     */
    public function testMapTypedArrayObject()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode(
                '{"pTypedArrayObject":[{"str":"stringvalue"},{"fl":"1.2"}]}'
            ),
            new JsonMapperTest_Array()
        );
        $this->assertInstanceOf('ArrayObject', $sn->pTypedArrayObject);
        $this->assertEquals(2, count($sn->pTypedArrayObject));
        $this->assertInstanceOf('JsonMapperTest_Simple', $sn->pTypedArrayObject[0]);
        $this->assertInstanceOf('JsonMapperTest_Simple', $sn->pTypedArrayObject[1]);
        $this->assertEquals('stringvalue', $sn->pTypedArrayObject[0]->str);
        $this->assertEquals('1.2', $sn->pTypedArrayObject[1]->fl);
    }

    /**
     * Test for "@var ArrayObject[int]"
     */
    public function testMapSimpleArrayObject()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode(
                '{"pSimpleArrayObject":{"eins":"1","zwei":"1.2"}}'
            ),
            new JsonMapperTest_Array()
        );
        $this->assertInstanceOf('ArrayObject', $sn->pSimpleArrayObject);
        $this->assertEquals(2, count($sn->pSimpleArrayObject));
        $this->assertTrue(is_int($sn->pSimpleArrayObject['eins']));
        $this->assertTrue(is_int($sn->pSimpleArrayObject['zwei']));
        $this->assertEquals(1, $sn->pSimpleArrayObject['eins']);
        $this->assertEquals(1, $sn->pSimpleArrayObject['zwei']);
    }

    /**
     * Test for "@var "
     */
    public function testMapEmpty()
    {
        $this->expectException(JsonMapperException::class);
        $this->expectExceptionMessage('Empty type at property "JsonMapperTest_Simple::$empty"');
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode(
                '{"empty":{"a":"b"}}'
            ),
            new JsonMapperTest_Simple()
        );
    }

    /**
     * The TYPO3 autoloader breaks if we autoload a class with a [ or ]
     * in its name.
     *
     * @runInSeparateProcess
     */
    public function testMapTypedArrayObjectDoesNotExist()
    {
        $this->assertTrue(
            spl_autoload_register(
                array($this, 'mapTypedArrayObjectDoesNotExistAutoloader')
            )
        );
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode(
                '{"pTypedArrayObjectNoClass":[{"str":"stringvalue"}]}'
            ),
            new JsonMapperTest_Broken()
        );
        $this->assertInstanceOf('ArrayObject', $sn->pTypedArrayObjectNoClass);
        $this->assertEquals(1, count($sn->pTypedArrayObjectNoClass));
        $this->assertInstanceOf(
            'ThisClassDoesNotExist', $sn->pTypedArrayObjectNoClass[0]
        );
    }

    public function mapTypedArrayObjectDoesNotExistAutoloader($class)
    {
        $this->assertFalse(
            strpos($class, '['),
            'class name contains a "[": ' . $class
        );
        $code = '';
        if (strpos($class, '\\') !== false) {
            $lpos = strrpos($class, '\\');
            $namespace = substr($class, 0, $lpos);
            $class = substr($class, $lpos + 1);
            $code .= 'namespace ' . $namespace . ";\n";
        }
        $code .= 'class ' . $class . '{}';
        eval($code);
    }

    /**
     * There is no property, but a setter method.
     * The parameter has a type hint.
     */
    public function testMapOnlySetterTypeHint()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"simpleSetterOnlyTypeHint":{"str":"stringvalue"}}'),
            new JsonMapperTest_Simple()
        );

        $this->assertTrue(is_object($sn->internalData['typehint']));
        $this->assertInstanceOf(
            'JsonMapperTest_Simple', $sn->internalData['typehint']
        );
        $this->assertEquals(
            'stringvalue', $sn->internalData['typehint']->str
        );
    }

    /**
     * There is no property, but a setter method.
     * It indicates the type in the docblock's @param annotation
     */
    public function testMapOnlySetterDocblock()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"simpleSetterOnlyDocblock":{"str":"stringvalue"}}'),
            new JsonMapperTest_Simple()
        );
        $this->assertTrue(is_object($sn->internalData['docblock']));
        $this->assertInstanceOf(
            'JsonMapperTest_Simple', $sn->internalData['docblock']
        );
        $this->assertEquals(
            'stringvalue', $sn->internalData['docblock']->str
        );
    }

    /**
     * There is no property, but a setter method, but it indicates no type
     */
    public function testMapOnlySetterNoType()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"simpleSetterOnlyNoType":{"str":"stringvalue"}}'),
            new JsonMapperTest_Simple()
        );
        $this->assertTrue(is_object($sn->internalData['notype']));
        $this->assertInstanceOf(
            'stdClass', $sn->internalData['notype']
        );
        $this->assertEquals(
            'stringvalue', $sn->internalData['notype']->str
        );
    }

    /**
     * Test for protected properties that have no setter method
     */
    public function testMapProtectedWithoutSetterMethod()
    {
        $jm = new JsonMapper();
        $logger = new JsonMapperTest_Logger();
        $jm->setLogger($logger);
        $sn = $jm->map(
            json_decode('{"protectedStrNoSetter":"stringvalue"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertNull($sn->getProtectedStrNoSetter());
        $this->assertEquals(
            array(
                array(
                    'info',
                    'Property {property} has no public setter method in {class}',
                    array(
                        'class' => 'JsonMapperTest_Simple',
                        'property' => 'protectedStrNoSetter'
                    )
                )
            ),
            $logger->log
        );
    }

    public function testMapDateTime()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"datetime":"2014-04-01T00:00:00+02:00"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertInstanceOf('DateTime', $sn->datetime);
        $this->assertEquals(
            '2014-04-01T00:00:00+02:00',
            $sn->datetime->format('c')
        );
    }

    public function testMapDateTimeNull()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"datetime":null}'),
            new JsonMapperTest_Simple()
        );
        $this->assertNull($sn->datetime);
    }

    public function testMissingDataException()
    {
        $this->expectException(JsonMapperException::class);
        $this->expectExceptionMessage('Required property "pMissingData" of class JsonMapperTest_Broken is missing in JSON data');
        $jm = new JsonMapper();
        $jm->bExceptionOnMissingData = true;
        $sn = $jm->map(
            json_decode('{}'),
            new JsonMapperTest_Broken()
        );
    }

    /**
     * We check that checkMissingData exits cleanly; needed for 100% coverage.
     */
    public function testMissingDataNoException()
    {
        $jm = new JsonMapper();
        $jm->bExceptionOnMissingData = true;
        $sn = $jm->map(
            json_decode('{"pMissingData":1}'),
            new JsonMapperTest_Broken()
        );
        $this->assertTrue(true);
    }

    public function testUndefinedPropertyException()
    {
        $this->expectException(JsonMapperException::class);
        $this->expectExceptionMessage('JSON property "undefinedProperty" does not exist in object of type JsonMapperTest_Broken');
        $jm = new JsonMapper();
        $jm->bExceptionOnUndefinedProperty = true;
        $sn = $jm->map(
            json_decode('{"undefinedProperty":123}'),
            new JsonMapperTest_Broken()
        );
    }

    public function testPrivatePropertyWithPublicSetter()
    {
        $jm = new JsonMapper();
        $jm->bExceptionOnUndefinedProperty = true;
        $logger = new JsonMapperTest_Logger();
        $jm->setLogger($logger);

        $json   = '{"privateProperty" : 1}';
        $result = $jm->map(json_decode($json), new PrivateWithSetter());

        $this->assertEquals(1, $result->getPrivateProperty());
        $this->assertTrue(empty($logger->log));
    }

    public function testPrivatePropertyWithNoSetter()
    {
        $this->expectException(JsonMapperException::class);
        $this->expectExceptionMessage('JSON property "privateNoSetter" has no public setter method in object of type PrivateWithSetter');
        $jm = new JsonMapper();
        $jm->bExceptionOnUndefinedProperty = true;
        $logger = new JsonMapperTest_Logger();
        $jm->setLogger($logger);

        $json   = '{"privateNoSetter" : 1}';
        $result = $jm->map(json_decode($json), new PrivateWithSetter());

        $this->assertEquals(1, $result->getPrivateProperty());
        $this->assertTrue(empty($logger->log));
    }

    public function testPrivatePropertyWithPrivateSetter()
    {
        $this->expectException(JsonMapperException::class);
        $this->expectExceptionMessage('JSON property "privatePropertyPrivateSetter" has no public setter method in object of type PrivateWithSetter');
        $jm = new JsonMapper();
        $jm->bExceptionOnUndefinedProperty = true;
        $logger = new JsonMapperTest_Logger();
        $jm->setLogger($logger);

        $json   = '{"privatePropertyPrivateSetter" : 1}';
        $result = $jm->map(json_decode($json), new PrivateWithSetter());
    }

    public function testSetterIsPreferredOverProperty()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"setterPreferredOverProperty":"foo"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertTrue(is_string($sn->setterPreferredOverProperty));
        $this->assertEquals(
            'set via setter: foo', $sn->setterPreferredOverProperty
        );
    }

    public function testSettingValueObjects()
    {
        $valueObject = new JsonMapperTest_ValueObject('test');
        $jm = new JsonMapper();
        $sn = $jm->map(
            (object) array('value_object' => $valueObject),
            new JsonMapperTest_Simple()
        );

        $this->assertSame($valueObject, $sn->getValueObject());
    }

    public function testCaseInsensitivePropertyMatching()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            (object) array('PINT' => 2),
            new JsonMapperTest_Simple()
        );

        $this->assertSame(2, $sn->pint);
    }

    public function testDependencyInjection()
    {
        $jm = new JsonMapperTest_DependencyInjector();

        $sn = $jm->map(
            (object) array(
                'str' => 'first level',
                'simple' => (object) array(
                    'str' => 'second level'
                )
            ),
            $jm->createInstance('JsonMapperTest_Simple')
        );

        $this->assertEquals('first level', $sn->str);
        $this->assertEquals('database', $sn->db);

        $this->assertEquals('second level', $sn->simple->str);
        $this->assertEquals('database', $sn->simple->db);
    }

    public function testDependencyInjectionWithMissingCtorArgs()
    {
        $this->expectException(JsonMapperException::class);
        $this->expectExceptionMessage('ClassWithCtor class requires 2 arguments in constructor but none provided');
        $jm = new JsonMapperTest_DependencyInjector();
        $jm->createInstance('ClassWithCtor');
    }

    public function testDiscriminatorWithBaseTypeWithMissingDiscriminatorType()
    {
        $jm = new JsonMapper();
        $jm->arChildClasses['JsonMapperTest_SimpleBase'] = array('JsonMapperTest_DerivedClass');
        $jm->arChildClasses['JsonMapperTest_DerivedClass'] = array('JsonMapperTest_DerivedClass2');
        $jm->arChildClasses['JsonMapperTest_DerivedClass2'] = array();

        $sn = $jm->mapClass(
            (object) array('afield' => 'abc', 'bfield' => 12, 'type' => 'base'),
            'JsonMapperTest_SimpleBaseWithMissingDiscrimType'
        );

        $this->assertInstanceOf('JsonMapperTest_SimpleBaseWithMissingDiscrimType', $sn);
        $this->assertEquals('abc', $sn->afield);
        $this->assertEquals(12, $sn->bfield);
        $this->assertEquals('base', $sn->type);
    }

    public function testDiscriminatorWithBaseType()
    {
        $jm = new JsonMapper();
        $jm->arChildClasses['JsonMapperTest_SimpleBase'] = array('JsonMapperTest_DerivedClass');
        $jm->arChildClasses['JsonMapperTest_DerivedClass'] = array('JsonMapperTest_DerivedClass2');
        $jm->arChildClasses['JsonMapperTest_DerivedClass2'] = array();

        $sn = $jm->mapClass(
            (object) array('afield' => 'abc', 'bfield' => 12, 'type' => 'base'),
            'JsonMapperTest_SimpleBase'
        );

        $this->assertInstanceOf('JsonMapperTest_SimpleBase', $sn);
        $this->assertEquals('abc', $sn->afield);
        $this->assertEquals(12, $sn->bfield);
        $this->assertEquals('base', $sn->type);

    }

    public function testDiscriminatorWithIncorrectDiscriminatorValue()
    {
        $jm = new JsonMapper();
        $jm->arChildClasses['JsonMapperTest_SimpleBase'] = array('JsonMapperTest_DerivedClass');
        $jm->arChildClasses['JsonMapperTest_DerivedClass'] = array('JsonMapperTest_DerivedClass2');
        $jm->arChildClasses['JsonMapperTest_DerivedClass2'] = array();

        $sn = $jm->mapClass(
            (object) array('afield' => 'abc', 'bfield' => 12, 'type' => 'incorrect!'),
            'JsonMapperTest_SimpleBase'
        );

        $this->assertInstanceOf('JsonMapperTest_SimpleBase', $sn);
        $this->assertEquals('abc', $sn->afield);
        $this->assertEquals(12, $sn->bfield);
        $this->assertEquals('incorrect!', $sn->type);

    }

    public function testDiscriminatorWithUnregisteredClass()
    {
        $jm = new JsonMapper();

        $sn = $jm->mapClass(
            (object) array('afield' => 'abc', 'bfield' => 12, 'type' => 'incorrect!'),
            'JsonMapperTest_SimpleBase'
        );

        $this->assertInstanceOf('JsonMapperTest_SimpleBase', $sn);
        $this->assertEquals('abc', $sn->afield);
        $this->assertEquals(12, $sn->bfield);
        $this->assertEquals('incorrect!', $sn->type);
    }

    public function testDiscriminatorWithInvalidClassName()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('JsonMapper::mapClass() requires second argument to be a class name, InvalidClassThatDoesNotExist given');
        $jm = new JsonMapper();

        $sn = $jm->mapClass(
            (object) array('afield' => 'abc', 'bfield' => 12, 'type' => 'incorrect!'),
            'InvalidClassThatDoesNotExist'
        );
    }

    public function testDiscriminatorWithDerivedType()
    {
        $jm = new JsonMapper();
        $jm->arChildClasses['JsonMapperTest_SimpleBase'] = array('JsonMapperTest_DerivedClass');
        $jm->arChildClasses['JsonMapperTest_DerivedClass'] = array('JsonMapperTest_DerivedClass2');
        $jm->arChildClasses['JsonMapperTest_DerivedClass2'] = array();

        $sn = $jm->mapClass(
            (object) array('afield' => 'abc', 'bfield' => 12, 'type' => 'derived1', 'derived1Field' => 'derived1 field'),
            'JsonMapperTest_SimpleBase'
        );

        $this->assertInstanceOf('JsonMapperTest_DerivedClass', $sn);
        $this->assertEquals('derived1', $sn->type);
        $this->assertEquals('derived1 field', $sn->derived1Field);
    }

    public function testDiscriminatorWithTwoLevelDerivedType()
    {
        $jm = new JsonMapper();
        $jm->arChildClasses['JsonMapperTest_SimpleBase'] = array('JsonMapperTest_DerivedClass');
        $jm->arChildClasses['JsonMapperTest_DerivedClass'] = array('JsonMapperTest_DerivedClass2');
        $jm->arChildClasses['JsonMapperTest_DerivedClass2'] = array();

        $sn = $jm->mapClass(
            (object) array('afield' => 'abc', 'bfield' => 12, 'type' => 'derived2', 'derived1Field' => 'derived1 field', 'derived2Field' => 'derived2 Field'),
            'JsonMapperTest_SimpleBase'
        );

        $this->assertInstanceOf('JsonMapperTest_DerivedClass2', $sn);
        $this->assertEquals('derived2', $sn->type);
        $this->assertEquals('derived2 Field', $sn->derived2Field);
    }

    public function testDiscriminatorWithArrayOfObjects()
    {
        $jm = new JsonMapper();
        $jm->arChildClasses['JsonMapperTest_SimpleBase'] = array('JsonMapperTest_DerivedClass');
        $jm->arChildClasses['JsonMapperTest_DerivedClass'] = array('JsonMapperTest_DerivedClass2');
        $jm->arChildClasses['JsonMapperTest_DerivedClass2'] = array();

        $sn = $jm->mapClassArray(
            array(
                (object) array('afield' => 'abc', 'bfield' => 12, 'type' => 'base'),
                (object) array('afield' => 'abc', 'bfield' => 12, 'type' => 'derived1', 'derived1Field' => 'derived1 field'),
                (object) array('afield' => 'abc', 'bfield' => 12, 'type' => 'derived2', 'derived1Field' => 'derived1 field', 'derived2Field' => 'derived2 Field')
            ),
            'JsonMapperTest_SimpleBase'
        );

        $this->assertTrue(is_array($sn));
        $this->assertInstanceOf('JsonMapperTest_SimpleBase', $sn[0]);
        $this->assertInstanceOf('JsonMapperTest_DerivedClass', $sn[1]);
        $this->assertInstanceOf('JsonMapperTest_DerivedClass2', $sn[2]);
    }

    public function testDiscriminatorWithEmbeddedObject()
    {
        $jm = new JsonMapper();
        $jm->arChildClasses['JsonMapperTest_SimpleBase'] = array('JsonMapperTest_DerivedClass');
        $jm->arChildClasses['JsonMapperTest_DerivedClass'] = array('JsonMapperTest_DerivedClass2');
        $jm->arChildClasses['JsonMapperTest_DerivedClass2'] = array();

        $sn = $jm->mapClass(
            (object) array('afield' => 'abc', 'bfield' => 12, 'type' => 'derived2', 'embedded' => (object) array('afield' => 'abc', 'bfield' => 12, 'type' => 'derived2', 'derived1Field' => 'derived1 field', 'derived2Field' => 'derived2 Field')),
            'JsonMapperTest_SimpleBase'
        );

        $this->assertInstanceOf('JsonMapperTest_SimpleBase', $sn);
        $this->assertInstanceOf('JsonMapperTest_DerivedClass2', $sn->embedded);
        
        $this->assertEquals('derived2', $sn->embedded->type);
        $this->assertEquals('derived2 Field', $sn->embedded->derived2Field);
    }

    public function testDiscriminatorWithEmbeddedObjectArray()
    {
        $jm = new JsonMapper();
        $jm->arChildClasses['JsonMapperTest_SimpleBase'] = array('JsonMapperTest_DerivedClass');
        $jm->arChildClasses['JsonMapperTest_DerivedClass'] = array('JsonMapperTest_DerivedClass2');
        $jm->arChildClasses['JsonMapperTest_DerivedClass2'] = array();

        $sn = $jm->mapClass(
            (object) array(
                'afield' => 'abc',
                'bfield' => 12,
                'type' => 'derived2',
                'embeddedArray' => array(
                    (object) array('afield' => 'abc', 'bfield' => 12, 'type' => 'derived1', 'derived1Field' => 'derived1 field'),
                    (object) array('afield' => 'abc', 'bfield' => 12, 'type' => 'derived2', 'derived1Field' => 'derived1 field', 'derived2Field' => 'derived2 Field')
                )
            ),
            'JsonMapperTest_SimpleBase'
        );

        $this->assertInstanceOf('JsonMapperTest_SimpleBase', $sn);
        $this->assertInstanceOf('JsonMapperTest_DerivedClass', $sn->embeddedArray[0]);
        $this->assertInstanceOf('JsonMapperTest_DerivedClass2', $sn->embeddedArray[1]);
    }

    public function testFactoryMethods()
    {
        $jm = new JsonMapper();
        $fm = $jm->map(
            json_decode('{"simple":"hello world", "value":123, "bool": 0, "datetime": 1511247096, "object": "some value", "objObj":{"a":"b"}, "array": [1,2,3], "valueArr":[1,2,3], "privateValue": 4242}'),
            new FactoryMethod()
        );
        $this->assertEquals("hello world", $fm->simple);
        $this->assertEquals("value is 123", $fm->value);
        $this->assertEquals(false, $fm->bool);
        $this->assertTrue(is_bool($fm->bool));
        $this->assertInstanceOf('DateTime', $fm->datetime);
        $this->assertInstanceOf('JsonMapperTest_ValueObject', $fm->object);
        $this->assertInstanceOf('JsonMapperTest_ValueObject', $fm->objObj);
        $this->assertEquals(array(1, 4, 9), $fm->array);
        $this->assertEquals(6, $fm->valueArr);
        $this->assertEquals("value is 4242", $fm->getPrivateValue());
    }

    public function testFactoryMethodException()
    {
        $this->expectException(JsonMapperException::class);
        $this->expectExceptionMessage('Factory method "NonExistentMethod" referenced by "FactoryMethodWithError" is not callable');
        $jm = new JsonMapper();
        $fm = $jm->map(
            json_decode('{"simple":"hello world"}'),
            new FactoryMethodWithError()
        );
    }

    public function testMapsAnnotationOnSetter()
    {
        $jm = new JsonMapper();
        $fm = $jm->map(
            json_decode('{"name":"hello","my_age":123123, "factoryValue": "factval", "public": "yes"}'),
            new MapsWithSetters()
        );
        $this->assertEquals("hello", $fm->getName());
        $this->assertEquals(123123, $fm->getAge());
        $this->assertEquals("value is factval", $fm->getMappedAndFactory());
        $this->assertEquals("yes", $fm->publicProp);
    }

    public function testAdditionalProperties()
    {
        $jm = new JsonMapper();
        $jm->sAdditionalPropertiesCollectionMethod = 'addAdditionalProperty';
        $fm = $jm->map(
            json_decode('{"random11":"hello","random22":123123}'),
            new JsonMapperTest_Simple()
        );
        $this->assertEquals("hello", $fm->additional['random11']);
        $this->assertEquals(123123, $fm->additional['random22']);
        $this->assertEquals(2, count($fm->additional));
    }

    public function testAdditionalPropertiesWithPrivateMethod()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('privateAddAdditionalProperty method is not public on the given class.');
        $jm = new JsonMapper();
        $jm->sAdditionalPropertiesCollectionMethod = 'privateAddAdditionalProperty';
        $fm = $jm->map(new stdClass, new JsonMapperTest_Simple());
    }

    public function testAdditionalPropertiesWithBrokenMethod()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('brokenAddAdditionalProperty method does not receive two args, $key and $value.');
        $jm = new JsonMapper();
        $jm->sAdditionalPropertiesCollectionMethod = 'brokenAddAdditionalProperty';
        $fm = $jm->map(new stdClass, new JsonMapperTest_Simple());
    }

    public function testAdditionalPropertiesWithMissingMethod()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('missingMethod method is not available on the given class.');
        $jm = new JsonMapper();
        $jm->sAdditionalPropertiesCollectionMethod = 'missingMethod';
        $fm = $jm->map(new stdClass, new JsonMapperTest_Simple());
    }
    
    public function testMapTypeWithCtor()
    {
        $jm = new JsonMapper();
        $fm = $jm->mapClass(
            json_decode('{"attr1":"hello","attr2":123123}'),
            'ClassWithCtor'
        );
        
        $this->assertEquals("hello", $fm->getAttr1());

        $this->assertInstanceOf('JsonMapperTest_ValueObject', $fm->getAttr2());
        $this->assertEquals(123123, $fm->getAttr2()->getValue());
    }

    public function testMapTypeWithCtorMissingArgument()
    {
        $this->expectException(JsonMapperException::class);
        $this->expectExceptionMessage('Could not find required constructor arguments for ClassWithCtor: attr2');
        $jm = new JsonMapper();
        $fm = $jm->mapClass(
            json_decode('{"attr1":"hello"}'),
            'ClassWithCtor'
        );
    }
        
    public function testMapTypeWithCtorComplex()
    {
        $jm = new JsonMapper();
        $fm = $jm->mapClass(
            json_decode('{"anotherSetter":"something","attr1":"hello","attr2":123123,"attr3":1,"attr4":true,"attr5":["abc"],"anotherProp":"foo"}'),
            'ComplexClassWithCtor'
        );
        $this->assertEquals("hello", $fm->getAttr1());
        $this->assertEquals(123123, $fm->getAttr2());
        $this->assertEquals(2, $fm->attr3);
        $this->assertEquals(true, $fm->foo);
        $this->assertEquals("abc", $fm->getAttr5()[0]);
        $this->assertEquals("last", $fm->getAttr5()[1]);
        $this->assertEquals("foo", $fm->anotherProp);
        $this->assertEquals("something new", $fm->getAnotherSetter());
    }

    public function testPhp7BasicTypeHints()
    {
        if (PHP_VERSION_ID >= 70000) {
            $jm = new JsonMapper();
            $fm = $jm->mapClass(
                json_decode('{"name":"abcdef","age":30,"value":"givenvalue"}'),
                'Php7TypedClass'
            );
            $this->assertEquals("abcdef", $fm->getName());
            $this->assertEquals(30, $fm->getAge());
            $this->assertEquals("givenvalue", $fm->getValue()->getValue());
        } else {
            $this->assertTrue(true);
        }
    }

    public function testPhp7_1BasicTypeHints()
    {
        if (PHP_VERSION_ID >= 70100) {
            $jm = new JsonMapper();
            $fm = $jm->mapClass(
                json_decode('{"nullableArray":["value1","value2"]}'),
                'Php7_1TypedClass'
            );
            $this->assertEquals("value1", $fm->getNullableArray()[0]->getValue());
            $this->assertEquals("value2", $fm->getNullableArray()[1]->getValue());
        } else {
            $this->assertTrue(true);
        }
    }
}
?>

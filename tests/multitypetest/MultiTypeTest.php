<?php
namespace multitypetest;
require_once __DIR__ . '/model/SimpleCaseA.php'; // have field value with anyOf("int[]","float[]","bool")
require_once __DIR__ . '/model/SimpleCaseB.php'; // have field value with oneOf("bool","int[]","array")
require_once __DIR__ . '/model/ComplexCaseA.php';
    // have field value with oneOf("DateTime[]",anyOf("DateTime","string"),"ComplexCaseA")
    // have field optional with oneOf("ComplexCaseA","ComplexCaseB","SimpleCaseA")
require_once __DIR__ . '/model/ComplexCaseB.php';
    // have field value with anyOf("Evening[]","Morning[]","Employee","Person[]",oneOf("Vehicle","Car"))
    // have field optional with anyOf("ComplexCaseA","SimpleCaseB[]","array")
require_once __DIR__ . '/model/Person.php';
require_once __DIR__ . '/model/Employee.php';
require_once __DIR__ . '/model/Postman.php';
require_once __DIR__ . '/model/Morning.php';
require_once __DIR__ . '/model/Evening.php';
require_once __DIR__ . '/model/Vehicle.php';
require_once __DIR__ . '/model/Car.php';
require_once __DIR__ . '/model/Atom.php';
require_once __DIR__ . '/model/Orbit.php';
require_once __DIR__ . '/model/OuterArrayCase.php';

use apimatic\jsonmapper\JsonMapper;
use apimatic\jsonmapper\JsonMapperException;
use PHPUnit\Framework\TestCase;

class MultiTypeTest extends TestCase
{
    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testSimpleCaseAWithFieldFloatArray()
    {
        $mapper = new JsonMapper();
        $json = '{"value":[1.2,3.4]}';
        $res = $mapper->mapClass(json_decode($json), '\multitypetest\model\SimpleCaseA');
        $this->assertInstanceOf('\multitypetest\model\SimpleCaseA', $res);
    }
    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testSimpleCaseAWithFieldIntArray()
    {
        $mapper = new JsonMapper();
        $json = '{"value":[1,2]}';
        $res = $mapper->mapClass(json_decode($json), '\multitypetest\model\SimpleCaseA');
        $this->assertInstanceOf('\multitypetest\model\SimpleCaseA', $res);
    }
    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testSimpleCaseAWithFieldBoolean()
    {
        $mapper = new JsonMapper();
        $json = '{"value":true}';
        $res = $mapper->mapClass(json_decode($json), '\multitypetest\model\SimpleCaseA');
        $this->assertInstanceOf('\multitypetest\model\SimpleCaseA', $res);
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testSimpleCaseAFailWithConstructorArgumentMissing()
    {
        $this->expectException(JsonMapperException::class);
        $this->expectExceptionMessage('Could not find required constructor arguments for multitypetest\model\SimpleCaseA: value');
        $mapper = new JsonMapper();
        $json = '{"key":true}';
        $mapper->mapClass(json_decode($json), '\multitypetest\model\SimpleCaseA');
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testSimpleCaseAFailWithFieldBoolArray()
    {
        $this->expectException(JsonMapperException::class);
        $this->expectExceptionMessage('Unable to map AnyOf (int[],float[],bool) on: [false,true]');
        $mapper = new JsonMapper();
        $json = '{"value":[false,true]}';
        $mapper->mapClass(json_decode($json), '\multitypetest\model\SimpleCaseA');
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testSimpleCaseAFailWithFieldString()
    {
        $this->expectException(JsonMapperException::class);
        $this->expectExceptionMessage('Unable to map AnyOf (int[],float[],bool) on: "some string"');
        $mapper = new JsonMapper();
        $json = '{"value":"some string"}';
        $mapper->mapClass(json_decode($json), '\multitypetest\model\SimpleCaseA');
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testSimpleCaseBWithFieldBoolean()
    {
        $mapper = new JsonMapper();
        $json = '{"value":true}';
        $res = $mapper->mapClass(json_decode($json), '\multitypetest\model\SimpleCaseB');
        $this->assertInstanceOf('\multitypetest\model\SimpleCaseB', $res);
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testSimpleCaseBWithFieldArray()
    {
        $mapper = new JsonMapper();
        $json = '{"value":["some","value"]}';
        $res = $mapper->mapClass(json_decode($json), '\multitypetest\model\SimpleCaseB');
        $this->assertInstanceOf('\multitypetest\model\SimpleCaseB', $res);
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testSimpleCaseBFailWithFieldIntArray()
    {
        $this->expectException(JsonMapperException::class);
        $this->expectExceptionMessage('Cannot map more than OneOf { array and int[] } on: [2,3]');
        $mapper = new JsonMapper();
        $json = '{"value":[2,3]}';
        $mapper->mapClass(json_decode($json), '\multitypetest\model\SimpleCaseB');
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testStringOrStringList()
    {
        $mapper = new JsonMapper();
        $json = '"some value"';
        $res = $mapper->mapFor(json_decode($json), 'anyOf(string[],string)');
        $this->assertEquals('some value', $res);

        $json = '["some","value"]';
        $res = $mapper->mapFor(json_decode($json), 'anyOf(string[],string)');
        $this->assertEquals('value', $res[1]);
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testNeitherStringNorStringList()
    {
        $this->expectException(JsonMapperException::class);
        $this->expectExceptionMessage('Unable to map AnyOf (string[],string) on: [false,"value"]');
        $mapper = new JsonMapper();
        $json = '[false,"value"]';
        $mapper->mapFor(json_decode($json), 'anyOf(string[],string)');
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testNullableObjectOrBool()
    {
        $mapper = new JsonMapper();
        $json = '["some","value"]';
        $res = $mapper->mapFor(json_decode($json), 'oneOf(anyOf(array,null),bool)');
        $this->assertEquals('value', $res[1]);

        $json = '{"key":false}';
        $res = $mapper->mapFor(json_decode($json), 'oneOf(anyOf(array,null),bool)');
        $this->assertEquals('key', array_keys($res)[0]);
        $this->assertEquals(false, array_values($res)[0]);

        $res = $mapper->mapFor(false, 'oneOf(anyOf(array,null),bool)');
        $this->assertEquals(false, $res);

        $res = $mapper->mapFor(null, 'oneOf(anyOf(array,null),bool)');
        $this->assertEquals(null, $res);
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testNullableOnNonNullable()
    {
        $mapper = new JsonMapper();
        $this->expectException(JsonMapperException::class);
        $this->expectExceptionMessage('Unable to map AnyOf (array,bool) on: null');
        $mapper->mapFor(null, 'oneOf(array,bool)');
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testMixedOrInt()
    {
        $mapper = new JsonMapper();
        $json = '{"passed":false}';
        $res = $mapper->mapFor(json_decode($json), 'oneOf(mixed,int)');
        $this->assertEquals(false, $res->passed);

        $json = 'passed string';
        $res = $mapper->mapFor($json, 'oneOf(mixed,int)');
        $this->assertEquals('passed string', $res);
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testMixedAndIntFail()
    {
        $mapper = new JsonMapper();
        $this->expectException(JsonMapperException::class);
        $this->expectExceptionMessage('Cannot map more than OneOf { int and mixed } on: 502');
        $mapper->mapFor(502, 'oneOf(mixed,int)');
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testStringOrSimpleCaseA()
    {
        $mapper = new JsonMapper();
        $json = '{"value":[1.2]}';
        $res = $mapper->mapFor(
            json_decode($json),
            'oneOf(string,SimpleCaseA)',
            'multitypetest\model'
        );
        $this->assertInstanceOf('\multitypetest\model\SimpleCaseA', $res);

        $json = '{"value":[1.2]}';
        $res = $mapper->mapFor(
            $json,
            'oneOf(string,SimpleCaseA)',
            'multitypetest\model'
        );
        $this->assertEquals('{"value":[1.2]}', $res);
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testOneOfSimpleCases()
    {
        $mapper = new JsonMapper();
        $json = '{"value":["aplha","beta","gamma"]}';
        $res = $mapper->mapFor(
            json_decode($json),
            'oneOf(SimpleCaseA,SimpleCaseB)',
            'multitypetest\model'
        );
        $this->assertInstanceOf('\multitypetest\model\SimpleCaseB', $res);
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testOneOfSimpleCasesWithFieldArrayAndFloatArrayFail()
    {
        $this->expectException(JsonMapperException::class);
        $this->expectExceptionMessage('Cannot map more than OneOf { SimpleCaseB and SimpleCaseA } on: {"value":[2.2,3.3]}');
        $mapper = new JsonMapper();
        $json = '{"value":[2.2,3.3]}';
        $mapper->mapFor(
            json_decode($json),
            'oneOf(SimpleCaseA,SimpleCaseB)',
            'multitypetest\model'
        );
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testAnyOfSimpleCases()
    {
        $mapper = new JsonMapper();
        $json = '{"value":[2.2,3.3]}';
        $res = $mapper->mapFor(
            json_decode($json),
            'anyOf(SimpleCaseA,SimpleCaseB)',
            'multitypetest\model'
        );
        $this->assertInstanceOf('\multitypetest\model\SimpleCaseA', $res);

        $json = '{"value":[2.2,3.3]}';
        $res = $mapper->mapFor(
            json_decode($json),
            'anyOf(SimpleCaseB,SimpleCaseA)',
            'multitypetest\model'
        );
        $this->assertInstanceOf('\multitypetest\model\SimpleCaseB', $res);

        $json = '{"value":["string1","string2"]}';
        $res = $mapper->mapFor(
            json_decode($json),
            'anyOf(SimpleCaseA,SimpleCaseB)',
            'multitypetest\model'
        );
        $this->assertInstanceOf('\multitypetest\model\SimpleCaseB', $res);
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testAnyOfSimpleCasesFailWithFieldString()
    {
        $this->expectException(JsonMapperException::class);
        $this->expectExceptionMessage('Unable to map AnyOf (SimpleCaseA,SimpleCaseB) on: {"value":"some value"}');
        $mapper = new JsonMapper();
        $json = '{"value":"some value"}';
        $mapper->mapFor(
            json_decode($json),
            'anyOf(SimpleCaseA,SimpleCaseB)',
            'multitypetest\model'
        );
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testMapAndObject()
    {
        $mapper = new JsonMapper();
        $this->expectException(JsonMapperException::class);
        $this->expectExceptionMessage('Cannot map more than OneOf { int[] and Atom } on: {"numberOfElectrons":4}');
        $json = '{"numberOfElectrons":4}';
        // oneof map of int & Atom (having all int fields)
        $mapper->mapFor(
            json_decode($json),
            'oneOf(Atom,int[])',
            'multitypetest\model'
        );
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testArrayOfMapAndArrayOfObject()
    {
        $mapper = new JsonMapper();
        $this->expectException(JsonMapperException::class);
        $this->expectExceptionMessage('Cannot map more than OneOf { int[][] and Atom[] } on: [{"numberOfElectrons":4,"numberOfProtons":2}]');
        $json = '[{"numberOfElectrons":4,"numberOfProtons":2}]';
        // oneof arrayOfmap of int & array of Atom (having all int fields)
        $mapper->mapFor(
            json_decode($json),
            'oneOf(Atom[],int[][])',
            'multitypetest\model'
        );
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testArrayOfMapOrArrayOfObject()
    {
        $mapper = new JsonMapper();
        $json = '[{"numberOfElectrons":4,"numberOfProtons":2}]';
        $res = $mapper->mapFor(
            json_decode($json),
            'anyOf(Atom[],int[][])',
            'multitypetest\model'
        );
        $this->assertTrue(is_array($res));
        $this->assertInstanceOf('\multitypetest\model\Atom', $res[0]);
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testOneOfObjectsFailWithSameRequiredFields()
    {
        $this->expectException(JsonMapperException::class);
        $this->expectExceptionMessage('Cannot map more than OneOf { Orbit and Atom } on: {"numberOfProtons":4,"numberOfElectrons":4}');
        $mapper = new JsonMapper();
        $json = '{"numberOfProtons":4,"numberOfElectrons":4}';
        // oneof Orbit (did not have # of protons) & Atom (have # of protons optional)
        $mapper->mapFor(
            json_decode($json),
            'oneOf(Atom,Orbit)',
            'multitypetest\model'
        );
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testComplexCases()
    {
        $mapper = new JsonMapper();
        $mapper->arChildClasses['multitypetest\model\Vehicle'] = [
            'multitypetest\model\Car',
        ];

        $json = '{"value": "199402-19", "optional": {"value": [23,24]}}';
        $res = $mapper->mapClass(json_decode($json),'\multitypetest\model\ComplexCaseA');
        $this->assertInstanceOf('\multitypetest\model\ComplexCaseA', $res);
        $this->assertTrue(is_string($res->getValue()));
        $this->assertInstanceOf('\multitypetest\model\SimpleCaseA', $res->getOptional());
        $this->assertTrue(is_int($res->getOptional()->getValue()[0]));

        $json = '{"value": "1994-02-12", "optional": {"value": ["1994-02-13","1994-02-14"],
            "optional": {"value": {"numberOfTyres":"4"}, "optional":[234,567]}}}';
        $res = $mapper->mapClass(json_decode($json),'\multitypetest\model\ComplexCaseA');
        $this->assertInstanceOf('\multitypetest\model\ComplexCaseA', $res);
        $this->assertInstanceOf('\DateTime', $res->getValue());
        $this->assertInstanceOf('\multitypetest\model\ComplexCaseA', $res->getOptional());
        $this->assertInstanceOf('\DateTime', $res->getOptional()->getValue()[0]);
        $this->assertInstanceOf('\multitypetest\model\ComplexCaseB', $res->getOptional()->getOptional());
        $this->assertInstanceOf('\multitypetest\model\Vehicle', $res->getOptional()->getOptional()->getValue());
        $this->assertTrue(is_int($res->getOptional()->getOptional()->getOptional()[0]));
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testComplexCasesWithDiscriminators()
    {
        $mapper = new JsonMapper();
        $mapper->arChildClasses['multitypetest\model\Person'] = [
            'multitypetest\model\Postman',
            'multitypetest\model\Employee',
        ];
        $json = '{"value":[{"name":"Shahid Khaliq","age":5147483645,"address":"H # 531, S # 20","uid":"123321",' .
            '"birthday":"1994-02-13","personType":"Per"},{"name":"Shahid Khaliq","age":5147483645,' .
            '"address":"H # 531, S # 20","uid":"123321","birthday":"1994-02-13","personType":"Per"},' .
            '{"name":"Shahid Khaliq","age":5147483645,"address":"H # 531, S # 20","uid":"123321",' .
            '"birthday":"1994-02-13","personType":"Per"}]}';
        $res = $mapper->mapFor(
            json_decode($json),
            'anyOf(ComplexCaseA,ComplexCaseB)',
            'multitypetest\model'
        );
        $this->assertInstanceOf('\multitypetest\model\ComplexCaseB', $res);
        $this->assertInstanceOf('\multitypetest\model\Person', $res->getValue()[0]);
        $this->assertInstanceOf('\multitypetest\model\Person', $res->getValue()[1]);
        $this->assertInstanceOf('\multitypetest\model\Person', $res->getValue()[2]);

        $json = '{"name":"Shahid Khaliq","age":5147483645,"address":"H # 531, S # 20","uid":"123321",' .
            '"birthday":"1994-02-13","personType":"Empl"}';
        $res = $mapper->mapFor(
            json_decode($json),
            'oneOf(Employee,Person)',
            'multitypetest\model'
        );
        $this->assertInstanceOf('\multitypetest\model\Employee', $res);

        $json = '{"name":"Shahid Khaliq","age":5147483645,"address":"H # 531, S # 20","uid":"123321",' .
            '"birthday":"1994-02-13","personType":"Empl"}';
        $res = $mapper->mapFor(
            json_decode($json),
            'anyOf(Person,Employee)',
            'multitypetest\model'
        );
        $this->assertInstanceOf('\multitypetest\model\Employee', $res);

        $json = '{"startsAt":"15:00","endsAt":"21:00","sessionType":"Evening"}';
        $res = $mapper->mapFor(
            json_decode($json),
            'oneOf(Evening,Morning)',
            'multitypetest\model'
        );
        $this->assertInstanceOf('\multitypetest\model\Evening', $res);

        $json = '{"value": [{"startsAt":"15:00","endsAt":"21:00","sessionType":"Evening"},' .
            '{"startsAt":"15:00","endsAt":"21:00","sessionType":"Evening"}]}';
        $res = $mapper->mapFor(
            json_decode($json),
            'anyOf(ComplexCaseA,ComplexCaseB)',
            'multitypetest\model'
        );
        $this->assertInstanceOf('\multitypetest\model\ComplexCaseB', $res);
        $this->assertInstanceOf('\multitypetest\model\Evening', $res->getValue()[0]);
        $this->assertInstanceOf('\multitypetest\model\Evening', $res->getValue()[1]);

        $json = '{"value": [{"startsAt":"15:00","endsAt":"21:00","sessionType":"Morning"},' .
            '{"startsAt":"15:00","endsAt":"21:00","sessionType":"Morning"}]}';
        $res = $mapper->mapFor(
            json_decode($json),
            'anyOf(ComplexCaseA,ComplexCaseB)',
            'multitypetest\model'
        );
        $this->assertInstanceOf('\multitypetest\model\ComplexCaseB', $res);
        $this->assertInstanceOf('\multitypetest\model\Morning', $res->getValue()[0]);
        $this->assertInstanceOf('\multitypetest\model\Morning', $res->getValue()[1]);
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testDiscriminatorsFailWithDiscriminatorMatchesParent()
    {
        $mapper = new JsonMapper();
        $mapper->arChildClasses['multitypetest\model\Person'] = [
            'multitypetest\model\Postman',
            'multitypetest\model\Employee',
        ];
        $this->expectException(JsonMapperException::class);
        $this->expectExceptionMessage('Unable to map AnyOf (Postman,Employee) on: {"name":"Shahid Khaliq","age":5147483645,"address":"H # 531, S # 20","uid":"123321","birthday":"1994-02-13","personType":"Per"}');
        $json = '{"name":"Shahid Khaliq","age":5147483645,"address":"H # 531, S # 20","uid":"123321",' .
            '"birthday":"1994-02-13","personType":"Per"}';
        $mapper->mapFor(
            json_decode($json),
            'anyOf(Postman,Employee)',
            'multitypetest\model'
        );
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testDiscriminatorsMatchedButFailedWithOneOf()
    {
        $mapper = new JsonMapper();
        $this->expectException(JsonMapperException::class);
        $this->expectExceptionMessage('Cannot map more than OneOf { array and Morning } on: {"startsAt":"15:00","endsAt":"21:00","sessionType":"Morning"}');
        $json = '{"startsAt":"15:00","endsAt":"21:00","sessionType":"Morning"}';
        $mapper->mapFor(
            json_decode($json),
            'oneOf(Morning,Evening,array)',
            'multitypetest\model'
        );
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testArrays()
    {
        $mapper = new JsonMapper();
        $json = '[true,false]';
        $res = $mapper->mapFor(json_decode($json),'oneOf(bool[],int[])');
        $this->assertTrue(is_array($res));
        $this->assertTrue(is_bool($res[0]));
        $this->assertTrue(is_bool($res[1]));
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testMaps()
    {
        $mapper = new JsonMapper();
        $json = '{"value1":31,"value2":32}';
        $res = $mapper->mapFor(json_decode($json),'oneOf(bool[],int[])');
        $this->assertTrue(is_array($res));
        $this->assertTrue(is_int($res['value1']));
        $this->assertTrue(is_int($res['value2']));
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testMapOfArrays()
    {
        $mapper = new JsonMapper();
        $json = '{"value":[true,false]}';
        $res = $mapper->mapFor(json_decode($json),'oneOf(bool[][],int[][])');
        $this->assertTrue(is_array($res));
        $this->assertTrue(is_array($res['value']));
        $this->assertTrue(is_bool($res['value'][0]));

        $json = '{"value":[{"numberOfElectrons":4},{"numberOfElectrons":9}]}';
        $res = $mapper->mapFor(
            json_decode($json),
            'oneOf(Atom[][],Car[][])',
            'multitypetest\model'
        );
        $this->assertTrue(is_array($res));
        $this->assertTrue(is_array($res['value']));
        $this->assertInstanceOf('\multitypetest\model\Atom', $res['value'][0]);
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testArrayOfMaps()
    {
        $mapper = new JsonMapper();
        $json = '[{"value":true,"value2":false},{"someBool":false}]';
        $res = $mapper->mapFor(
            json_decode($json),
            'oneOf(bool[][],int[][])',
            'multitypetest\model'
        );
        $this->assertTrue(is_array($res));
        $this->assertTrue(is_array($res[0]));
        $this->assertTrue(is_array($res[1]));
        $this->assertTrue(is_bool($res[0]['value']));
        $this->assertTrue(is_bool($res[0]['value2']));
        $this->assertTrue(is_bool($res[1]['someBool']));

        $json = '[{"atom1":{"numberOfElectrons":4},"atom2":{"numberOfElectrons":9}}]';
        $res = $mapper->mapFor(
            json_decode($json),
            'oneOf(Atom[][],Car[][])',
            'multitypetest\model'
        );
        $this->assertTrue(is_array($res));
        $this->assertTrue(is_array($res[0]));
        $this->assertInstanceOf('\multitypetest\model\Atom', $res[0]['atom1']);
        $this->assertInstanceOf('\multitypetest\model\Atom', $res[0]['atom2']);
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testMultiDimensionalArrays()
    {
        $mapper = new JsonMapper();
        $json = '[[true,false],[false,false]]';
        $res = $mapper->mapFor(
            json_decode($json),
            'oneOf(bool[][],int[][])',
            'multitypetest\model'
        );
        $this->assertTrue(is_array($res));
        $this->assertTrue(is_array($res[0]));
        $this->assertTrue(is_array($res[1]));
        $this->assertTrue(is_bool($res[0][0]));
        $this->assertTrue(is_bool($res[0][1]));

        $json = '[[{"numberOfElectrons":4},{"numberOfElectrons":9}],[{"numberOfElectrons":2}]]';
        $res = $mapper->mapFor(
            json_decode($json),
            'oneOf(Atom[][],Car[][])',
            'multitypetest\model'
        );
        $this->assertTrue(is_array($res));
        $this->assertTrue(is_array($res[0]));
        $this->assertTrue(is_array($res[1]));
        $this->assertInstanceOf('\multitypetest\model\Atom', $res[0][0]);
        $this->assertInstanceOf('\multitypetest\model\Atom', $res[1][0]);
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testOuterArrayInModelField()
    {
        $mapper = new JsonMapper();
        $json = '{"value":[true,[1,2],"abc"]}';
        $res = $mapper->mapClass(
            json_decode($json),
            '\multitypetest\model\OuterArrayCase'
        );
        $this->assertInstanceOf('\multitypetest\model\OuterArrayCase', $res);
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testOuterArray()
    {
        $mapper = new JsonMapper();
        $json = '[true,{"numberOfElectrons":4,"numberOfProtons":2},false]';
        $res = $mapper->mapFor(
            json_decode($json),
            'oneOf(bool,Atom)[]',
            'multitypetest\model'
        );
        $this->assertTrue(is_array($res));
        $this->assertTrue($res[0] === true);
        $this->assertInstanceOf('\multitypetest\model\Atom', $res[1]);
        $this->assertTrue($res[2] === false);
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testOuterMap()
    {
        $mapper = new JsonMapper();
        $json = '{"key1":true,"key2":{"numberOfElectrons":4,"numberOfProtons":2},"key3":false}';
        $res = $mapper->mapFor(
            json_decode($json),
            'oneOf(bool,Atom)[]',
            'multitypetest\model'
        );
        $this->assertTrue(is_array($res));
        $this->assertTrue($res['key1'] === true);
        $this->assertInstanceOf('\multitypetest\model\Atom', $res['key2']);
        $this->assertTrue($res['key3'] === false);
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testOuterMapOfArrays()
    {
        $mapper = new JsonMapper();
        $json = '{"value":[{"numberOfElectrons":4},{"haveTrunk":false,"numberOfTyres":6}]}';
        $res = $mapper->mapFor(
            json_decode($json),
            'oneOf(Atom,Car)[][]',
            'multitypetest\model'
        );
        $this->assertTrue(is_array($res));
        $this->assertTrue(is_array($res['value']));
        $this->assertInstanceOf('\multitypetest\model\Atom', $res['value'][0]);
        $this->assertInstanceOf('\multitypetest\model\Car', $res['value'][1]);

        $json = '{"value":[[[{"numberOfElectrons":4}]],[[true,true],[false,true]]]}';
        $res = $mapper->mapFor(
            json_decode($json),
            'oneOf(Atom[][],bool[][])[][]',
            'multitypetest\model'
        );
        $this->assertTrue(is_array($res));
        $this->assertTrue(is_array($res['value']));
        $this->assertTrue(is_array($res['value'][0]));
        $this->assertTrue(is_array($res['value'][0][0]));
        $this->assertInstanceOf('\multitypetest\model\Atom', $res['value'][0][0][0]);
        $this->assertTrue(is_array($res['value'][1]));
        $this->assertTrue(is_array($res['value'][1][0]));
        $this->assertTrue(is_bool($res['value'][1][0][0]));
        $this->assertTrue(is_array($res['value'][1][1]));
        $this->assertTrue(is_bool($res['value'][1][1][0]));

        $json = '{"key0":["alpha",true],"key1":["beta",[12,{"numberOfElectrons":4}],[1,3]],"key2":[false,true]}';
        $res = $mapper->mapFor(
            json_decode($json),
            'anyOf(bool,oneOf(int,Atom)[],string)[][]',
            'multitypetest\model'
        );
        $this->assertTrue(is_array($res));
        $this->assertTrue(is_array($res['key0']));
        $this->assertTrue($res['key0'][0] === 'alpha');
        $this->assertTrue(is_array($res['key1']));
        $this->assertTrue($res['key1'][0] === 'beta');
        $this->assertTrue(is_array($res['key1'][1]));
        $this->assertTrue($res['key1'][1][0] === 12);
        $this->assertInstanceOf('\multitypetest\model\Atom', $res['key1'][1][1]);
        $this->assertTrue(is_array($res['key2']));
        $this->assertTrue($res['key2'][0] === false);
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testOuterArrayOfMaps()
    {
        $mapper = new JsonMapper();
        $json = '[{"key0":{"numberOfElectrons":4},"key1":{"haveTrunk":false,"numberOfTyres":6}}]';
        $res = $mapper->mapFor(
            json_decode($json),
            'oneOf(Atom,Car)[][]',
            'multitypetest\model'
        );
        $this->assertTrue(is_array($res));
        $this->assertTrue(is_array($res[0]));
        $this->assertInstanceOf('\multitypetest\model\Atom', $res[0]['key0']);
        $this->assertInstanceOf('\multitypetest\model\Car', $res[0]['key1']);

        $json = '[{"key0":[[{"numberOfElectrons":4}]],"key1":[[true,true],[false,true]]}]';
        $res = $mapper->mapFor(
            json_decode($json),
            'oneOf(Atom[][],bool[][])[][]',
            'multitypetest\model'
        );
        $this->assertTrue(is_array($res));
        $this->assertTrue(is_array($res[0]));
        $this->assertTrue(is_array($res[0]['key0']));
        $this->assertTrue(is_array($res[0]['key0'][0]));
        $this->assertInstanceOf('\multitypetest\model\Atom', $res[0]['key0'][0][0]);
        $this->assertTrue(is_array($res[0]['key1']));
        $this->assertTrue(is_array($res[0]['key1'][0]));
        $this->assertTrue(is_bool($res[0]['key1'][0][0]));
        $this->assertTrue(is_array($res[0]['key1'][1]));
        $this->assertTrue(is_bool($res[0]['key1'][1][0]));

        $json = '[{"key0":"alpha","key1":true},{"key0":"beta","key1":[12,{"numberOfElectrons":4}],"key2":[1,3]},{"key0":false,"key1":true}]';
        $res = $mapper->mapFor(
            json_decode($json),
            'anyOf(bool,oneOf(int,Atom)[],string)[][]',
            'multitypetest\model'
        );
        $this->assertTrue(is_array($res));
        $this->assertTrue(is_array($res[0]));
        $this->assertTrue($res[0]['key0'] === 'alpha');
        $this->assertTrue(is_bool($res[0]['key1']));
        $this->assertTrue(is_array($res[1]));
        $this->assertTrue($res[1]['key0'] === 'beta');
        $this->assertTrue(is_array($res[1]['key1']));
        $this->assertTrue($res[1]['key1'][0] === 12);
        $this->assertInstanceOf('\multitypetest\model\Atom', $res[1]['key1'][1]);
        $this->assertTrue(is_array($res[1]['key2']));
        $this->assertTrue($res[1]['key2'][0] === 1);
        $this->assertTrue(is_array($res[2]));
        $this->assertTrue($res[2]['key0'] === false);
        $this->assertTrue($res[2]['key1'] === true);
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testOuterMultiDimensionalArray()
    {
        $mapper = new JsonMapper();
        $json = '[[{"numberOfElectrons":4},{"haveTrunk":false,"numberOfTyres":6}]]';
        $res = $mapper->mapFor(
            json_decode($json),
            'oneOf(Atom,Car)[][]',
            'multitypetest\model'
        );
        $this->assertTrue(is_array($res));
        $this->assertTrue(is_array($res[0]));
        $this->assertInstanceOf('\multitypetest\model\Atom', $res[0][0]);
        $this->assertInstanceOf('\multitypetest\model\Car', $res[0][1]);

        $json = '[[[[{"numberOfElectrons":4}]],[[true,true],[false,true]]]]';
        $res = $mapper->mapFor(
            json_decode($json),
            'oneOf(Atom[][],bool[][])[][]',
            'multitypetest\model'
        );
        $this->assertTrue(is_array($res));
        $this->assertTrue(is_array($res[0]));
        $this->assertTrue(is_array($res[0][0]));
        $this->assertTrue(is_array($res[0][0][0]));
        $this->assertInstanceOf('\multitypetest\model\Atom', $res[0][0][0][0]);
        $this->assertTrue(is_array($res[0][1]));
        $this->assertTrue(is_array($res[0][1][0]));
        $this->assertTrue(is_bool($res[0][1][0][0]));
        $this->assertTrue(is_array($res[0][1][1]));
        $this->assertTrue(is_bool($res[0][1][1][0]));

        $json = '[["alpha",true],["beta",[12,{"numberOfElectrons":4}],[1,3]],[false,true]]';
        $res = $mapper->mapFor(
            json_decode($json),
            'anyOf(bool,oneOf(int,Atom)[],string)[][]',
            'multitypetest\model'
        );
        $this->assertTrue(is_array($res));
        $this->assertTrue(is_array($res[0]));
        $this->assertTrue($res[0][0] === 'alpha');
        $this->assertTrue(is_array($res[1]));
        $this->assertTrue($res[1][0] === 'beta');
        $this->assertTrue(is_array($res[1][1]));
        $this->assertTrue($res[1][1][0] === 12);
        $this->assertInstanceOf('\multitypetest\model\Atom', $res[1][1][1]);
        $this->assertTrue(is_array($res[2]));
        $this->assertTrue($res[2][0] === false);
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testOuterArrayCaseFailWithAnyOf()
    {
        $mapper = new JsonMapper();
        $this->expectException(JsonMapperException::class);
        $this->expectExceptionMessage('Unable to map AnyOf (float[],(bool,(int,Atom)[],string)) on: {"key0":["alpha",true],"key2":[false,true],"key3":[1.1,3.3],"key1":["beta",[12,{"numberOfElectrons":4}],[1,3]]}');
        $json = '{"key0":["alpha",true],"key2":[false,true],"key3":[1.1,3.3]' .
            ',"key1":["beta",[12,{"numberOfElectrons":4}],[1,3]]}';
        $mapper->mapFor(
            json_decode($json),
            'anyOf(float[],anyOf(bool,oneOf(int,Atom)[],string)[][])',
            'multitypetest\model'
        );
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testOuterArrayCaseFailWith2DMapOfAtomAnd2DMapOfIntArray()
    {
        $mapper = new JsonMapper();
        $this->expectException(JsonMapperException::class);
        $this->expectExceptionMessage('Cannot map more than OneOf { (bool,(int,Atom)[],string)[][] and int[][][] } on: {"key":{"element":{"atom":1,"orbits":9},"compound":[4,8]}}');
        $json = '{"key":{"element":{"atom":1,"orbits":9},"compound":[4,8]}}';
        $mapper->mapFor(
            json_decode($json),
            'oneOf(int[][][],anyOf(bool,oneOf(int,Atom)[],string)[][])',
            'multitypetest\model'
        );
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testOuterArrayFailWithMapOfStringInsteadOfMapOfArrayOfString()
    {
        $mapper = new JsonMapper();
        $this->expectException(JsonMapperException::class);
        $this->expectExceptionMessage('Unable to map Array: (bool,(int,Atom)[],string)[] on: "alpha"');
        $json = '{"key0":["beta",[12,{"numberOfElectrons":4}],[1,3]],"key1":"alpha"' .
            ',"key2":[false,true]}';
        $mapper->mapFor(
            json_decode($json),
            'anyOf(bool,oneOf(int,Atom)[],string)[][]',
            'multitypetest\model'
        );
    }
}

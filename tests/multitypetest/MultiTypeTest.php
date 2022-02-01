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
        $mapper = new JsonMapper();
        $json = '{"key":true}';
        try {
            $mapper->mapClass(json_decode($json), '\multitypetest\model\SimpleCaseA');
        } catch (\Exception $e) {
            $res = $e->getMessage();
        }
        $this->assertEquals('Could not find required constructor arguments for multitypetest\model\SimpleCaseA: value', $res);
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testSimpleCaseAFailWithFieldBoolArray()
    {
        $mapper = new JsonMapper();
        $json = '{"value":[false,true]}';
        try {
            $mapper->mapClass(json_decode($json), '\multitypetest\model\SimpleCaseA');
        } catch (\Exception $e) {
            $res = $e->getMessage();
        }
        $this->assertEquals('Unable to map AnyOf (int[],float[],bool) on: [false,true]', $res);
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testSimpleCaseAFailWithFieldString()
    {
        $mapper = new JsonMapper();
        $json = '{"value":"some string"}';
        try {
            $mapper->mapClass(json_decode($json), '\multitypetest\model\SimpleCaseA');
        } catch (\Exception $e) {
            $res = $e->getMessage();
        }
        $this->assertEquals('Unable to map AnyOf (int[],float[],bool) on: "some string"', $res);
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
        $mapper = new JsonMapper();
        $json = '{"value":[2,3]}';
        try {
            $mapper->mapClass(json_decode($json), '\multitypetest\model\SimpleCaseB');
        } catch (\Exception $e) {
            $res = $e->getMessage();
        }
        $this->assertEquals('Cannot map more than OneOf { array and int[] } on: [2,3]', $res);
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
        $mapper = new JsonMapper();
        $json = '[false,"value"]';
        try {
            $mapper->mapFor(json_decode($json), 'anyOf(string[],string)');
        } catch (\Exception $e) {
            $res = $e->getMessage();
        }
        $this->assertEquals('Unable to map AnyOf (string[],string) on: [false,"value"]', $res);
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testObjectOrBool()
    {
        $mapper = new JsonMapper();
        $json = '["some","value"]';
        $res = $mapper->mapFor(json_decode($json), 'oneOf(null,array,bool)');
        $this->assertEquals('value', $res[1]);

        $json = '{"key":false}';
        $res = $mapper->mapFor(json_decode($json), 'oneOf(null,array,bool)');
        $this->assertEquals('key', array_keys($res)[0]);
        $this->assertEquals(false, array_values($res)[0]);

        $json = 'false';
        $res = $mapper->mapFor(json_decode($json), 'oneOf(null,array,bool)');
        $this->assertEquals(false, $res);

        $json = null;
        try {
            $mapper->mapFor(json_decode($json), 'oneOf(array,bool)');
        } catch (\Exception $e) {
            $res = $e->getMessage();
        }
        $this->assertEquals('Unable to map AnyOf (array,bool) on: null', $res);

        $res = $mapper->mapFor(json_decode($json), 'oneOf(null,array,bool)');
        $this->assertEquals(null, $res);
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testMixedAndInt()
    {
        $mapper = new JsonMapper();
        $json = '{"passed":false}';
        $res = $mapper->mapFor(json_decode($json), 'oneOf(mixed,int)');
        $this->assertEquals(false, $res->passed);

        $json = '"passed string"';
        $res = $mapper->mapFor(json_decode($json), 'oneOf(mixed,int)');
        $this->assertEquals('passed string', $res);

        $json = '502';
        try {
            $mapper->mapFor(json_decode($json), 'oneOf(mixed,int)');
        } catch (\Exception $e) {
            $res = $e->getMessage();
        }
        $this->assertEquals('Cannot map more than OneOf { int and mixed } on: 502', $res);
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

        $json = '"{\"value\":[1.2]}"';
        $res = $mapper->mapFor(
            json_decode($json),
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
    public function testOneOfSimpleCasesFail()
    {
        $mapper = new JsonMapper();
        $json = '{"value":[2.2,3.3]}';
        try {
            $mapper->mapFor(
                json_decode($json),
                'oneOf(SimpleCaseA,SimpleCaseB)',
                'multitypetest\model'
            );
        } catch (\Exception $e) {
            $res = $e->getMessage();
        }
        $this->assertEquals('Cannot map more than OneOf { SimpleCaseB and SimpleCaseA } on: {"value":[2.2,3.3]}', $res);
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
    public function testAnyOfSimpleCasesFail()
    {
        $mapper = new JsonMapper();
        $json = '{"value":"some value"}';
        try {
            $mapper->mapFor(
                json_decode($json),
                'anyOf(SimpleCaseA,SimpleCaseB)',
                'multitypetest\model'
            );
        } catch (\Exception $e) {
            $res = $e->getMessage();
        }
        $this->assertEquals('Unable to map AnyOf (SimpleCaseA,SimpleCaseB) on: {"value":"some value"}', $res);
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testMapOrObject()
    {
        $mapper = new JsonMapper();

        $json = '{"numberOfElectrons":4}';
        try {
            // oneof map of int & Atom (having all int fields)
            $mapper->mapFor(
                json_decode($json),
                'oneOf(Atom,int[])',
                'multitypetest\model'
            );
        } catch (\Exception $e) {
            $res = $e->getMessage();
        }
        $this->assertEquals('Cannot map more than OneOf { int[] and Atom } on: {"numberOfElectrons":4}', $res);

        $json = '[{"numberOfElectrons":4,"numberOfProtons":2}]';
        $res = '';
        try {
            // oneof arrayOfmap of int & array of Atom (having all int fields)
            $mapper->mapFor(
                json_decode($json),
                'oneOf(Atom[],int[][])',
                'multitypetest\model'
            );
        } catch (\Exception $e) {
            $res = $e->getMessage();
        }
        $this->assertEquals('Cannot map more than OneOf { int[][] and Atom[] } on: [{"numberOfElectrons":4,"numberOfProtons":2}]', $res);

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
    public function testOrbitOrAtom()
    {
        $mapper = new JsonMapper();
        $json = '{"numberOfProtons":4,"numberOfElectrons":4}';
        try {
            // oneof Orbit (did not have # of protons) & Atom (have # of protons optional)
            $res = $mapper->mapFor(
                json_decode($json),
                'oneOf(Atom,Orbit)',
                'multitypetest\model'
            );
        } catch (\Exception $e) {
            $res = $e->getMessage();
        }
        $this->assertEquals('Cannot map more than OneOf { Orbit and Atom } on: {"numberOfProtons":4,"numberOfElectrons":4}', $res);
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
    public function testComplexCasesWithDescriminators()
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
    public function testDescriminatorsFail()
    {
        $mapper = new JsonMapper();
        $mapper->arChildClasses['multitypetest\model\Person'] = [
            'multitypetest\model\Postman',
            'multitypetest\model\Employee',
        ];

        $json = '{"name":"Shahid Khaliq","age":5147483645,"address":"H # 531, S # 20","uid":"123321",' .
            '"birthday":"1994-02-13","personType":"Per"}';
        try {
            $mapper->mapFor(
                json_decode($json),
                'anyOf(Postman,Employee)',
                'multitypetest\model'
            );
        } catch (\Exception $e) {
            $res = $e->getMessage();
        }
        $this->assertEquals('Unable to map AnyOf (Postman,Employee) on: {"name":"Shahid Khaliq","age":5147483645,"address":"H # 531, S # 20","uid":"123321","birthday":"1994-02-13","personType":"Per"}', $res);

        $json = '{"startsAt":"15:00","endsAt":"21:00","sessionType":"Morning"}';
        try {
            $res = $mapper->mapFor(
                json_decode($json),
                'oneOf(Morning,Evening,array)',
                'multitypetest\model'
            );
        } catch (\Exception $e) {
            $res = $e->getMessage();
        }
        $this->assertEquals('Cannot map more than OneOf { array and Morning } on: {"startsAt":"15:00","endsAt":"21:00","sessionType":"Morning"}', $res);
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testMultiDimensionalArray()
    {
        $mapper = new JsonMapper();

        $json = '{"value":[true,false]}';
        $res = $mapper->mapFor(
            json_decode($json),
            'oneOf(bool[][],int[][])',
            'multitypetest\model'
        );
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
    }

    /**
     * @covers \apimatic\jsonmapper\JsonMapper
     * @covers \apimatic\jsonmapper\TypeCombination
     * @covers \apimatic\jsonmapper\JsonMapperException
     */
    public function testOuterArrayCases()
    {
        $mapper = new JsonMapper();

        $json = '{"value":[true,[1,2],"abc"]}';
        $res = $mapper->mapClass(
            json_decode($json),
            '\multitypetest\model\OuterArrayCase'
        );
        $this->assertInstanceOf('\multitypetest\model\OuterArrayCase', $res);

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

        $json = '{"key0":["alpha",true],"key2":[false,true]' .
            ',"key1":["beta",[12,{"numberOfElectrons":4}],[1,3]]}';
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
    public function testOuterArrayCaseFail()
    {
        $mapper = new JsonMapper();
        $json = '{"key0":["alpha",true],"key2":[false,true],"key3":[1.1,3.3]' .
            ',"key1":["beta",[12,{"numberOfElectrons":4}],[1,3]]}';
        try {
            $res = $mapper->mapFor(
                json_decode($json),
                'anyOf(float[],anyOf(bool,oneOf(int,Atom)[],string)[][])',
                'multitypetest\model'
            );
        } catch (\Exception $e) {
            $res = $e->getMessage();
        }
        $this->assertEquals('Unable to map AnyOf (float[],(bool,(int,Atom)[],string)) on: {"key0":["alpha",true],"key2":[false,true],"key3":[1.1,3.3],"key1":["beta",[12,{"numberOfElectrons":4}],[1,3]]}', $res);

        $json = '{"key":{"element":{"atom":1,"orbits":9},"compound":[4,8]}}';
        try {
            $res = $mapper->mapFor(
                json_decode($json),
                'oneOf(int[][][],anyOf(bool,oneOf(int,Atom)[],string)[][])',
                'multitypetest\model'
            );
        } catch (\Exception $e) {
            $res = $e->getMessage();
        }
        $this->assertEquals('Cannot map more than OneOf { (bool,(int,Atom)[],string)[][] and int[][][] } on: {"key":{"element":{"atom":1,"orbits":9},"compound":[4,8]}}', $res);

        $json = '{"key0":["beta",[12,{"numberOfElectrons":4}],[1,3]],"key1":"alpha"' .
            ',"key2":[false,true]}';
        try {
            $res = $mapper->mapFor(
                json_decode($json),
                'anyOf(bool,oneOf(int,Atom)[],string)[][]',
                'multitypetest\model'
            );
        } catch (\Exception $e) {
            $res = $e->getMessage();
        }
        $this->assertEquals('Unable to map Array: (bool,(int,Atom)[],string)[] on: "alpha"', $res);
    }
}

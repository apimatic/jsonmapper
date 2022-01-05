<?php
namespace multitypetest;
require_once __DIR__ . '/model/SimpleCaseA.php'; // have field value with AnyOf("int[]","float[]","bool")
require_once __DIR__ . '/model/SimpleCaseB.php'; // have field value with OneOf("bool","int[]","array")
require_once __DIR__ . '/model/ComplexCaseA.php';
    // have field value with OneOf("DateTime[]",AnyOf("DateTime","string"),"ComplexCaseA")
    // have field optional with OneOf("ComplexCaseA","ComplexCaseB","SimpleCaseA")
require_once __DIR__ . '/model/ComplexCaseB.php';
    // have field value with AnyOf("Evening[]","Morning[]","Employee","Person[]",OneOf("Vehicle","Car"))
    // have field optional with AnyOf("ComplexCaseA","SimpleCaseB[]","array")
require_once __DIR__ . '/model/Person.php';
require_once __DIR__ . '/model/Employee.php';
require_once __DIR__ . '/model/Postman.php';
require_once __DIR__ . '/model/Morning.php';
require_once __DIR__ . '/model/Evening.php';
require_once __DIR__ . '/model/Vehicle.php';
require_once __DIR__ . '/model/Car.php';
require_once __DIR__ . '/model/Atom.php';
require_once __DIR__ . '/model/Orbit.php';

use apimatic\jsonmapper\JsonMapper;
use PHPUnit\Framework\TestCase;

class MultiTypeTest extends TestCase
{
    public function testSimpleCaseA()
    {
        $mapper = new JsonMapper();
        $json = '{"value":[1.2,3.4]}';
        $res = $mapper->mapClass(json_decode($json), '\multitypetest\model\SimpleCaseA');
        $this->assertInstanceOf('\multitypetest\model\SimpleCaseA', $res);

        $json = '{"value":[1,2]}';
        $res = $mapper->mapClass(json_decode($json), '\multitypetest\model\SimpleCaseA');
        $this->assertInstanceOf('\multitypetest\model\SimpleCaseA', $res);

        $json = '{"value":true}';
        $res = $mapper->mapClass(json_decode($json), '\multitypetest\model\SimpleCaseA');
        $this->assertInstanceOf('\multitypetest\model\SimpleCaseA', $res);
    }

    public function testSimpleCaseAFail()
    {
        $mapper = new JsonMapper();
        $json = '{"key":true}';
        try {
            $mapper->mapClass(json_decode($json), '\multitypetest\model\SimpleCaseA');
        } catch (\Exception $e) {
            $res = $e->getMessage();
        }
        $this->assertTrue(strpos($res, 'Could not find required constructor arguments for') === 0);

        $json = '{"value":[false,true]}';
        try {
            $mapper->mapClass(json_decode($json), '\multitypetest\model\SimpleCaseA');
        } catch (\Exception $e) {
            $res = $e->getMessage();
        }
       $this->assertTrue(strpos($res, 'Unable to map AnyOf') === 0);

        $json = '{"value":"some string"}';
        try {
            $mapper->mapClass(json_decode($json), '\multitypetest\model\SimpleCaseA');
        } catch (\Exception $e) {
            $res = $e->getMessage();
        }
       $this->assertTrue(strpos($res, 'Unable to map AnyOf') === 0);
    }

    public function testSimpleCaseB()
    {
        $mapper = new JsonMapper();
        $json = '{"value":true}';
        $res = $mapper->mapClass(json_decode($json), '\multitypetest\model\SimpleCaseB');
        $this->assertInstanceOf('\multitypetest\model\SimpleCaseB', $res);

        $json = '{"value":["some","value"]}';
        $res = $mapper->mapClass(json_decode($json), '\multitypetest\model\SimpleCaseB');
        $this->assertInstanceOf('\multitypetest\model\SimpleCaseB', $res);
    }

    public function testSimpleCaseBFail()
    {
        $mapper = new JsonMapper();
        $json = '{"value":[2,3]}';
        try {
            $mapper->mapClass(json_decode($json), '\multitypetest\model\SimpleCaseB');
        } catch (\Exception $e) {
            $res = $e->getMessage();
        }
       $this->assertTrue(strpos($res, 'Cannot map more then OneOf') === 0);
    }

    public function testStringOrStringList()
    {
        $mapper = new JsonMapper();
        $json = '"some value"';
        $res = $mapper->mapFor(json_decode($json), 'AnyOf("string[]","string")');
        $this->assertEquals('some value', $res);

        $json = '["some","value"]';
        $res = $mapper->mapFor(json_decode($json), 'AnyOf("string[]","string")');
        $this->assertEquals('value', $res[1]);

        $json = '[false,"value"]';
        try {
            $mapper->mapFor(json_decode($json), 'AnyOf("string[]","string")');
        } catch (\Exception $e) {
            $res = $e->getMessage();
        }
       $this->assertTrue(strpos($res, 'Unable to map AnyOf') === 0);
    }

    public function testObjectOrBool()
    {
        $mapper = new JsonMapper();
        $json = '["some","value"]';
        $res = $mapper->mapFor(json_decode($json), 'OneOf("null","array","bool")');
        $this->assertEquals('value', $res[1]);

        $json = '{"key":false}';
        $res = $mapper->mapFor(json_decode($json), 'OneOf("null","array","bool")');
        $this->assertEquals('key', array_keys($res)[0]);
        $this->assertEquals(false, array_values($res)[0]);

        $json = 'false';
        $res = $mapper->mapFor(json_decode($json), 'OneOf("null","array","bool")');
        $this->assertEquals(false, $res);

        $json = null;
        try {
            $mapper->mapFor(json_decode($json), 'OneOf("array","bool")');
        } catch (\Exception $e) {
            $res = $e->getMessage();
        }
       $this->assertTrue(strpos($res, 'Unable to map AnyOf') === 0);

        $res = $mapper->mapFor(json_decode($json), 'OneOf("null","array","bool")');
        $this->assertEquals(null, $res);
    }

    public function testMixedAndInt()
    {
        $mapper = new JsonMapper();
        $json = '{"passed":false}';
        $res = $mapper->mapFor(json_decode($json), 'OneOf("mixed","int")');
        $this->assertEquals(false, $res->passed);

        $json = '"passed string"';
        $res = $mapper->mapFor(json_decode($json), 'OneOf("mixed","int")');
        $this->assertEquals('passed string', $res);

        $json = '502';
        try {
            $mapper->mapFor(json_decode($json), 'OneOf("mixed","int")');
        } catch (\Exception $e) {
            $res = $e->getMessage();
        }
       $this->assertTrue(strpos($res, 'Cannot map more then OneOf') === 0);
    }

    public function testStringOrSimpleCaseA()
    {
        $mapper = new JsonMapper();
        $json = '{"value":[1.2]}';
        $res = $mapper->mapFor(
            json_decode($json),
            'OneOf("string","SimpleCaseA")',
            'multitypetest\model'
        );
        $this->assertInstanceOf('\multitypetest\model\SimpleCaseA', $res);

        $json = '"{\"value\":[1.2]}"';
        $res = $mapper->mapFor(
            json_decode($json),
            'OneOf("string","SimpleCaseA")',
            'multitypetest\model'
        );
        $this->assertEquals('{"value":[1.2]}', $res);
    }

    public function testOneOfSimpleCases()
    {
        $mapper = new JsonMapper();
        $json = '{"value":["aplha","beta","gamma"]}';
        $res = $mapper->mapFor(
            json_decode($json),
            'OneOf("SimpleCaseA","SimpleCaseB")',
            'multitypetest\model'
        );
        $this->assertInstanceOf('\multitypetest\model\SimpleCaseB', $res);
    }

    public function testOneOfSimpleCasesFail()
    {
        $mapper = new JsonMapper();
        $json = '{"value":[2.2,3.3]}';
        try {
            $mapper->mapFor(
                json_decode($json),
                'OneOf("SimpleCaseA","SimpleCaseB")',
                'multitypetest\model'
            );
        } catch (\Exception $e) {
            $res = $e->getMessage();
        }
       $this->assertTrue(strpos($res, 'Cannot map more then OneOf') === 0);
    }

    public function testAnyOfSimpleCases()
    {
        $mapper = new JsonMapper();
        $json = '{"value":[2.2,3.3]}';
        $res = $mapper->mapFor(
            json_decode($json),
            'AnyOf("SimpleCaseA","SimpleCaseB")',
            'multitypetest\model'
        );
        $this->assertInstanceOf('\multitypetest\model\SimpleCaseA', $res);

        $json = '{"value":[2.2,3.3]}';
        $res = $mapper->mapFor(
            json_decode($json),
            'AnyOf("SimpleCaseB","SimpleCaseA")',
            'multitypetest\model'
        );
        $this->assertInstanceOf('\multitypetest\model\SimpleCaseB', $res);

        $json = '{"value":["string1","string2"]}';
        $res = $mapper->mapFor(
            json_decode($json),
            'AnyOf("SimpleCaseA","SimpleCaseB")',
            'multitypetest\model'
        );
        $this->assertInstanceOf('\multitypetest\model\SimpleCaseB', $res);
    }

    public function testAnyOfSimpleCasesFail()
    {
        $mapper = new JsonMapper();
        $json = '{"value":"some value"}';
        try {
            $mapper->mapFor(
                json_decode($json),
                'AnyOf("SimpleCaseA","SimpleCaseB")',
                'multitypetest\model'
            );
        } catch (\Exception $e) {
            $res = $e->getMessage();
        }
       $this->assertTrue(strpos($res, 'Unable to map AnyOf') === 0);
    }

    public function testMapOrObject()
    {
        $mapper = new JsonMapper();

        $json = '{"numberOfElectrons":4}';
        try {
            // oneof map of int & Atom (having all int fields)
            $mapper->mapFor(
                json_decode($json),
                'OneOf("Atom","int[]")',
                'multitypetest\model'
            );
        } catch (\Exception $e) {
            $res = $e->getMessage();
        }
        $this->assertTrue(strpos($res, 'Cannot map more then OneOf') === 0);

        $json = '[{"numberOfElectrons":4,"numberOfProtons":2}]';
        $res = '';
        try {
            // oneof arrayOfmap of int & array of Atom (having all int fields)
            $mapper->mapFor(
                json_decode($json),
                'OneOf("Atom[]","int[][]")',
                'multitypetest\model'
            );
        } catch (\Exception $e) {
            $res = $e->getMessage();
        }
        $this->assertTrue(strpos($res, 'Cannot map more then OneOf') === 0);

        $res = $mapper->mapFor(
            json_decode($json),
            'AnyOf("Atom[]","int[][]")',
            'multitypetest\model'
        );
        $this->assertTrue(is_array($res));
        $this->assertInstanceOf('\multitypetest\model\Atom', $res[0]);
    }

    public function testOrbitOrAtom()
    {
        $mapper = new JsonMapper();
        $json = '{"numberOfProtons":4,"numberOfElectrons":4}';
        try {
            // oneof Orbit (did not have # of protons) & Atom (have # of protons optional)
            $res = $mapper->mapFor(
                json_decode($json),
                'OneOf("Atom","Orbit")',
                'multitypetest\model'
            );
        } catch (\Exception $e) {
            $res = $e->getMessage();
        }
        $this->assertTrue(strpos($res, 'Cannot map more then OneOf') === 0);

    }

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

    public function testComplexCasesWithDescriminators()
    {
        $mapper = new JsonMapper();
        $mapper->arChildClasses['multitypetest\model\Person'] = [
            'multitypetest\model\Postman',
            'multitypetest\model\Employee',
        ];
        $json = '{"value":[{"name":"Shahid Khaliq","age":5147483645,"address":"H # 531, S # 20","uid":"123321",' .
            '"birthday":"1994-02-13","personType":"Post"},{"name":"Shahid Khaliq","age":5147483645,' .
            '"address":"H # 531, S # 20","uid":"123321","birthday":"1994-02-13","personType":"Empl"},' .
            '{"name":"Shahid Khaliq","age":5147483645,"address":"H # 531, S # 20","uid":"123321",' .
            '"birthday":"1994-02-13","personType":"Per"}]}';
        $res = $mapper->mapFor(
            json_decode($json),
            'AnyOf("ComplexCaseA","ComplexCaseB")',
            'multitypetest\model'
        );
        $this->assertInstanceOf('\multitypetest\model\ComplexCaseB', $res);
        $this->assertInstanceOf('\multitypetest\model\Postman', $res->getValue()[0]);
        $this->assertInstanceOf('\multitypetest\model\Employee', $res->getValue()[1]);
        $this->assertInstanceOf('\multitypetest\model\Person', $res->getValue()[2]);

        $json = '{"name":"Shahid Khaliq","age":5147483645,"address":"H # 531, S # 20","uid":"123321",' .
            '"birthday":"1994-02-13","personType":"Empl"}';
        $res = $mapper->mapFor(
            json_decode($json),
            'AnyOf("Person","Employee")',
            'multitypetest\model'
        );
        $this->assertInstanceOf('\multitypetest\model\Employee', $res);

        $json = '{"startsAt":"15:00","endsAt":"21:00","sessionType":"Evening"}';
        $res = $mapper->mapFor(
            json_decode($json),
            'OneOf("Evening","Morning")',
            'multitypetest\model'
        );
        $this->assertInstanceOf('\multitypetest\model\Evening', $res);

        $json = '{"value": [{"startsAt":"15:00","endsAt":"21:00","sessionType":"Evening"},' .
            '{"startsAt":"15:00","endsAt":"21:00","sessionType":"Evening"}]}';
        $res = $mapper->mapFor(
            json_decode($json),
            'AnyOf("ComplexCaseA","ComplexCaseB")',
            'multitypetest\model'
        );
        $this->assertInstanceOf('\multitypetest\model\ComplexCaseB', $res);
        $this->assertInstanceOf('\multitypetest\model\Evening', $res->getValue()[0]);
        $this->assertInstanceOf('\multitypetest\model\Evening', $res->getValue()[1]);

        $json = '{"value": [{"startsAt":"15:00","endsAt":"21:00","sessionType":"Morning"},' .
            '{"startsAt":"15:00","endsAt":"21:00","sessionType":"Morning"}]}';
        $res = $mapper->mapFor(
            json_decode($json),
            'AnyOf("ComplexCaseA","ComplexCaseB")',
            'multitypetest\model'
        );
        $this->assertInstanceOf('\multitypetest\model\ComplexCaseB', $res);
        $this->assertInstanceOf('\multitypetest\model\Morning', $res->getValue()[0]);
        $this->assertInstanceOf('\multitypetest\model\Morning', $res->getValue()[1]);
    }

    public function testDescriminatorsFail()
    {
        $mapper = new JsonMapper();
        $mapper->arChildClasses['multitypetest\model\Person'] = [
            'multitypetest\model\Postman',
            'multitypetest\model\Employee',
        ];
        $json = '{"name":"Shahid Khaliq","age":5147483645,"address":"H # 531, S # 20","uid":"123321",' .
            '"birthday":"1994-02-13","personType":"Empl"}';
        try {
            $mapper->mapFor(
                json_decode($json),
                'OneOf("Employee","Person")',
                'multitypetest\model'
            );
        } catch (\Exception $e) {
            $res = $e->getMessage();
        }
       $this->assertTrue(strpos($res, 'Cannot map more then OneOf') === 0);

        $json = '{"name":"Shahid Khaliq","age":5147483645,"address":"H # 531, S # 20","uid":"123321",' .
            '"birthday":"1994-02-13","personType":"Per"}';
        try {
            $mapper->mapFor(
                json_decode($json),
                'AnyOf("Postman","Employee")',
                'multitypetest\model'
            );
        } catch (\Exception $e) {
            $res = $e->getMessage();
        }
       $this->assertTrue(strpos($res, 'Unable to map AnyOf') === 0);

        $json = '{"startsAt":"15:00","endsAt":"21:00","sessionType":"Morning"}';
        try {
            $res = $mapper->mapFor(
                json_decode($json),
                'OneOf("Morning","Evening","array")',
                'multitypetest\model'
            );
        } catch (\Exception $e) {
            $res = $e->getMessage();
        }
       $this->assertTrue(strpos($res, 'Cannot map more then OneOf') === 0);
    }
}

<?php

namespace OpenStack\Test\Common\Api;

use OpenStack\Common\Api\Parameter;
use OpenStack\Test\Fixtures\ComputeV2Api;

class ParameterTest extends \PHPUnit_Framework_TestCase
{
    const PARAMETER_CLASS = 'OpenStack\Common\Api\Parameter';

    private $param;
    private $data;

    public function setUp()
    {
        $this->data = ComputeV2Api::postServer()['params']['name'] + ['name' => 'name'];
        $this->param = new Parameter($this->data);
    }

    public function test_it_should_provide_access_to_a_name()
    {
        $this->assertEquals($this->data['name'], $this->param->getName());
    }

    public function test_it_should_use_sentAs_alias_for_name_if_one_is_set()
    {
        $data = $this->data + ['sentAs' => 'foo'];
        $param = new Parameter($data);

        $this->assertEquals($data['sentAs'], $param->getName());
    }

    public function test_it_indicates_whether_it_is_required_or_not()
    {
        $this->assertTrue($this->param->isRequired());
    }

    public function test_it_indicates_its_item_schema()
    {
        $data = ComputeV2Api::postServer()['params']['networks'] + ['name' => 'networks'];
        $param = new Parameter($data);

        $this->assertInstanceOf(self::PARAMETER_CLASS, $param->getItemSchema());
    }

    public function test_it_allows_property_retrieval()
    {
        $definition = ComputeV2Api::postServer()['params']['networks']['items'] + ['name' => 'network'];
        $param = new Parameter($definition);

        $this->assertInstanceOf(self::PARAMETER_CLASS, $param->getProperty('uuid'));
    }

    public function test_it_indicates_its_path()
    {
        $path = 'foo.bar.baz';
        $param = new Parameter($this->data + ['path' => $path]);

        $this->assertEquals($path, $param->getPath());
    }

    public function test_it_verifies_a_given_location_with_a_boolean()
    {
        $this->assertFalse($this->param->hasLocation('foo'));
        $this->assertTrue($this->param->hasLocation('json'));
    }

    public function test_it_should_return_true_when_required_attributes_are_provided_and_match_their_definitions()
    {
        $this->assertTrue($this->param->validate('TestName'));
    }

    /**
     * @expectedException \Exception
     */
    public function test_it_throws_exception_when_values_do_not_match_their_definition_types()
    {
        $data = ComputeV2Api::postServer()['params']['networks'] + ['name' => 'networks'];
        $param = new Parameter($data);

        $param->validate('a_network!'); // should be an array
    }

    /**
     * @expectedException \Exception
     */
    public function test_it_throws_exception_when_deeply_nested_values_have_wrong_types()
    {
        $data = ComputeV2Api::postServer()['params']['networks'] + ['name' => 'networks'];

        $param = new Parameter($data);
        $param->validate(['name' => false]); // value should be a string, not bool
    }

    public function test_metadata_properties_are_handled_differently()
    {
        $params = [
            'name' => 'metadata',
            'type' => 'object',
            'properties' => [
                'type' => 'string'
            ]
        ];

        $userValues = ['some' => 'value'];

        $param = new Parameter($params);
        $this->assertTrue($param->validate($userValues));
    }

    public function test_it_passes_validation_when_array_values_pass()
    {
        $params = [
            'name' => 'foo',
            'type' => 'array',
            'items' => ['type' => 'string'],
        ];

        $userVals = ['1', '2', '3'];

        $param = new Parameter($params);
        $this->assertTrue($param->validate($userVals));
    }

    /**
     * @expectedException \Exception
     */
    public function test_an_exception_is_thrown_when_an_undefined_property_is_provided()
    {
        $params = ['type' => 'object', 'properties' => ['foo' => ['type' => 'string']]];
        $userVals = ['bar' => 'baz'];

        $param = new Parameter($params);
        $param->validate($userVals);
    }

    public function test_it_passes_validation_when_all_subproperties_pass()
    {
        $params = ['type' => 'object', 'properties' => ['foo' => ['type' => 'string']]];
        $userVals = ['foo' => 'baz'];

        $param = new Parameter($params);
        $this->assertTrue($param->validate($userVals));
    }
}
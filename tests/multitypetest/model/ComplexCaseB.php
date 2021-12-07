<?php

namespace multitypetest\model;

use stdClass;

/**
 * This class contains inner array case of oneOf.
 */
class ComplexCaseB implements \JsonSerializable
{
    /**
     * @var Evening[]|Morning[]|Postman|Person[]|Vehicle|Car
     */
    private $value;

    /**
     * @var ComplexCaseA|SimpleCaseB[]|array
     */
    private $optional;

    /**
     * @param Evening[]|Morning[]|Postman|Person[]|Vehicle|Car $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Returns Value.
     * @return Evening[]|Morning[]|Postman|Person[]|Vehicle|Car
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Sets Value.
     *
     * @param Evening[]|Morning[]|Postman|Person[]|Vehicle|Car|string $value
     * @required
     * @maps value AnyOf("Evening[]","Morning[]","Employee","Person[]",OneOf("Vehicle","Car"),"string")
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Returns Optional.
     *
     * @return ComplexCaseA|SimpleCaseB[]|array
     */
    public function getOptional()
    {
        return $this->optional;
    }

    /**
     * Sets Value.
     *
     * @param ComplexCaseA|SimpleCaseB[]|array $optional
     * @required
     * @maps optional AnyOf("ComplexCaseA","SimpleCaseB[]","array")
     */
    public function setOptional($optional)
    {
        $this->optional = $optional;
    }

    /**
     * Encode this object to JSON
     *
     * @param bool $asArrayWhenEmpty Whether to serialize this model as an array whenever no fields
     *        are set. (default: false)
     *
     * @return mixed
     */
    public function jsonSerialize($asArrayWhenEmpty = false)
    {
        $json = [];
        $json['value'] = $this->value;

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}

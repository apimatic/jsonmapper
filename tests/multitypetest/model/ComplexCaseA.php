<?php

declare(strict_types=1);

namespace multitypetest\model;
require_once __DIR__ . '/DateTimeHelper.php';

use stdClass;

/**
 * This class contains simple case of oneOf.
 */
class ComplexCaseA implements \JsonSerializable
{
    /**
     * @var \DateTime[]|\DateTime|string|ComplexCaseA
     */
    private $value;

    /**
     * @var ComplexCaseA|ComplexCaseB|SimpleCaseA
     */
    private $optional;

    /**
     * @param \DateTime[]|\DateTime|string|ComplexCaseA $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Returns Value.
     *
     * @return \DateTime[]|\DateTime|string|ComplexCaseA
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Sets Value.
     *
     * @param \DateTime[]|\DateTime|string|ComplexCaseA $value
     * @required
     * @maps value OneOf("DateTime[]",AnyOf("DateTime","string"),"ComplexCaseA")
     * @factory multitypetest\model\DateTimeHelper::fromSimpleDate DateTime
     * @factory multitypetest\model\DateTimeHelper::fromSimpleDateArray DateTime[]
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }

    /**
     * Returns Optional.
     *
     * @return ComplexCaseA|ComplexCaseB|SimpleCaseA
     */
    public function getOptional()
    {
        return $this->optional;
    }

    /**
     * Sets Value.
     *
     * @param ComplexCaseA|ComplexCaseB|SimpleCaseA $optional
     * @required
     * @maps optional OneOf("ComplexCaseA","ComplexCaseB","SimpleCaseA")
     */
    public function setOptional($optional): void
    {
        $this->optional = $optional;
    }

    /**
     * Encode this object to JSON
     *
     * @param bool $asArrayWhenEmpty Whether to serialize this model as an array whenever no fields
     *        are set. (default: false)
     *
     * @return array|stdClass
     */
    public function jsonSerialize(bool $asArrayWhenEmpty = false)
    {
        $json = [];
        $json['value'] = $this->value;

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}

<?php

declare(strict_types=1);

namespace multitypetest\model;

use stdClass;

/**
 * This class contains simple case of oneOf.
 */
class Car extends Vehicle implements \JsonSerializable
{
    /**
     * @var bool
     */
    private $haveTrunk;

    /**
     * @param int $numberOfTyres
     * @param bool $haveTrunk
     */
    public function __construct(int $numberOfTyres, bool $haveTrunk)
    {
        parent::__construct($numberOfTyres);
        $this->haveTrunk = $haveTrunk;
    }

    /**
     * Returns HaveTrunk.
     */
    public function getHaveTrunk(): bool
    {
        return $this->haveTrunk;
    }

    /**
     * Sets HaveTrunk.
     *
     * @maps haveTrunk
     */
    public function setHaveTrunk(bool $haveTrunk): void
    {
        $this->haveTrunk = $haveTrunk;
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
        $json['haveTrunk'] = $this->haveTrunk;
        $json = array_merge($json, parent::jsonSerialize(true));

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}

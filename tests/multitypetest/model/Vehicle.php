<?php

declare(strict_types=1);

namespace multitypetest\model;

use stdClass;

/**
 * This class contains simple case of oneOf.
 */
class Vehicle implements \JsonSerializable
{
    /**
     * @var int
     */
    private $numberOfTyres;

    /**
     * @param int $numberOfTyres
     */
    public function __construct(int $numberOfTyres)
    {
        $this->numberOfTyres = $numberOfTyres;
    }

    /**
     * Returns NumberOfTyres.
     */
    public function getNumberOfTyres(): int
    {
        return $this->numberOfTyres;
    }

    /**
     * Sets Value.
     *
     * @required
     * @maps numberOfTyres
     */
    public function setNumberOfTyres(int $numberOfTyres): void
    {
        $this->numberOfTyres = $numberOfTyres;
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
        $json['numberOfTyres'] = $this->numberOfTyres;

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}

<?php

declare(strict_types=1);

namespace multitypetest\model;

use stdClass;

/**
 * Course evening session
 *
 * @discriminator sessionType
 * @discriminatorType Evening
 */
class Evening implements \JsonSerializable
{
    /**
     * @var string
     */
    private $startsAt;

    /**
     * @var string
     */
    private $endsAt;

    /**
     * @var string|null
     */
    private $sessionType;

    /**
     * @param string $startsAt
     * @param string $endsAt
     */
    public function __construct(string $startsAt, string $endsAt)
    {
        $this->startsAt = $startsAt;
        $this->endsAt = $endsAt;
    }

    /**
     * Returns Starts At.
     *
     * Session start time
     */
    public function getStartsAt(): string
    {
        return $this->startsAt;
    }

    /**
     * Sets Starts At.
     *
     * Session start time
     *
     * @required
     * @maps startsAt
     */
    public function setStartsAt(string $startsAt): void
    {
        $this->startsAt = $startsAt;
    }

    /**
     * Returns Ends At.
     *
     * Session end time
     */
    public function getEndsAt(): string
    {
        return $this->endsAt;
    }

    /**
     * Sets Ends At.
     *
     * Session end time
     *
     * @required
     * @maps endsAt
     */
    public function setEndsAt(string $endsAt): void
    {
        $this->endsAt = $endsAt;
    }

    /**
     * Returns Session Type.
     */
    public function getSessionType(): ?string
    {
        return $this->sessionType;
    }

    /**
     * Sets Session Type.
     *
     * @maps sessionType
     */
    public function setSessionType(?string $sessionType): void
    {
        $this->sessionType = $sessionType;
    }

    /**
     * Encode this object to JSON
     *
     * @param bool $asArrayWhenEmpty Whether to serialize this model as an array whenever no fields
     *        are set. (default: false)
     *
     * @return mixed
     */
    public function jsonSerialize(bool $asArrayWhenEmpty = false)
    {
        $json = [];
        $json['startsAt']    = $this->startsAt;
        $json['endsAt']      = $this->endsAt;
        $json['sessionType'] = $this->sessionType;

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}

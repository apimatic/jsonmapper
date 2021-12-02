<?php

declare(strict_types=1);

namespace multitypetest\model;

use stdClass;

/**
 * @discriminator personType
 * @discriminatorType Empl
 */
class Employee extends Person implements \JsonSerializable
{
    /**
     * @var string
     */
    private $department;

    /**
     * @var Person[]
     */
    private $dependents;

    /**
     * @var \DateTime
     */
    private $hiredAt;

    /**
     * @var string
     */
    private $joiningDay;

    /**
     * @var int
     */
    private $salary;

    /**
     * @var string[]
     */
    private $workingDays;

    /**
     * @var Person|null
     */
    private $boss;

    /**
     * @param string $address
     * @param int $age
     * @param string $name
     * @param string $uid
     */
    public function __construct(
        string $address,
        int $age,
        string $name,
        string $uid
    ) {
        parent::__construct($address, $age, $name, $uid);
    }

    /**
     * Returns Department.
     */
    public function getDepartment(): string
    {
        return $this->department;
    }

    /**
     * Sets Department.
     *
     * @required
     * @maps department
     */
    public function setDepartment(string $department): void
    {
        $this->department = $department;
    }

    /**
     * Returns Dependents.
     *
     * @return Person[]
     */
    public function getDependents(): array
    {
        return $this->dependents;
    }

    /**
     * Sets Dependents.
     *
     * @required
     * @maps dependents
     *
     * @param Person[] $dependents
     */
    public function setDependents(array $dependents): void
    {
        $this->dependents = $dependents;
    }

    /**
     * Returns Hired At.
     */
    public function getHiredAt(): \DateTime
    {
        return $this->hiredAt;
    }

    /**
     * Sets Hired At.
     *
     * @required
     * @maps hiredAt
     * @factory \DateTimeHelper::fromRfc1123DateTime
     */
    public function setHiredAt(\DateTime $hiredAt): void
    {
        $this->hiredAt = $hiredAt;
    }

    /**
     * Returns Joining Day.
     */
    public function getJoiningDay(): string
    {
        return $this->joiningDay;
    }

    /**
     * Sets Joining Day.
     *
     * @required
     * @maps joiningDay
     */
    public function setJoiningDay(string $joiningDay): void
    {
        $this->joiningDay = $joiningDay;
    }

    /**
     * Returns Salary.
     */
    public function getSalary(): int
    {
        return $this->salary;
    }

    /**
     * Sets Salary.
     *
     * @required
     * @maps salary
     */
    public function setSalary(int $salary): void
    {
        $this->salary = $salary;
    }

    /**
     * Returns Working Days.
     *
     * @return string[]
     */
    public function getWorkingDays(): array
    {
        return $this->workingDays;
    }

    /**
     * Sets Working Days.
     *
     * @required
     * @maps workingDays
     *
     * @param string[] $workingDays
     */
    public function setWorkingDays(array $workingDays): void
    {
        $this->workingDays = $workingDays;
    }

    /**
     * Returns Boss.
     */
    public function getBoss(): ?Person
    {
        return $this->boss;
    }

    /**
     * Sets Boss.
     *
     * @maps boss
     */
    public function setBoss(?Person $boss): void
    {
        $this->boss = $boss;
    }

    private $additionalProperties = [];

    /**
     * Add an additional property to this model.
     *
     * @param string $name Name of property
     * @param mixed $value Value of property
     */
    public function addAdditionalProperty(string $name, $value)
    {
        $this->additionalProperties[$name] = $value;
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
        $json['department']  = $this->department;
        $json['dependents']  = $this->dependents;
        $json['hiredAt']     = DateTimeHelper::toRfc1123DateTime($this->hiredAt);
        $json['joiningDay']  = $this->joiningDay;
        $json['salary']      = $this->salary;
        $json['workingDays'] = $this->workingDays;
        $json['boss']        = $this->boss;
        $json = array_merge($json, parent::jsonSerialize(true), $this->additionalProperties);

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}

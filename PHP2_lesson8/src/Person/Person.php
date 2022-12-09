<?php
namespace GeekBrains\LevelTwo\Person;

use \DateTimeImmutable;

class Person
{
    private Name $name;
    private DateTimeImmutable $registeredOn;

    public function __construct(Name $name, DateTimeImmutable $registeredOn) {
        $this->registeredOn = $registeredOn;
        $this->name = $name;
    }

    public function __toString()
    {
        return $this->name . ' (на сайте с ' . $this->registeredOn->format('Y-m-d') . ')';
    }

    /**
     * @return Name
     */
    public function getName(): Name
    {
        return $this->name;
    }

    /**
     * @param Name $name
     */
    public function setName(Name $name): void
    {
        $this->name = $name;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getRegisteredOn(): DateTimeImmutable
    {
        return $this->registeredOn;
    }

    /**
     * @param DateTimeImmutable $registeredOn
     */
    public function setRegisteredOn(DateTimeImmutable $registeredOn): void
    {
        $this->registeredOn = $registeredOn;
    }


}
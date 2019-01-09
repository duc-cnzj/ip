<?php

namespace DucCnzj\Ip\Imp;

interface DataMapImp
{
    public function hasInfo(): bool;

    public function getIp(): string;

    public function getCity(): string;

    public function getCountry(): string;

    public function getRegion(): string;

    public function getAddress(): string;
}

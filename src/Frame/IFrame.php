<?php

namespace App\Frame;

interface IFrame
{
    public function serialize() : string;
    public function streamId() : int;
    public function complete() : bool;
    public function next() : bool;
    public function payload() : string;
}
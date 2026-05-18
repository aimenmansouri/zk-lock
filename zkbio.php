<?php

class ZKBio
{
    public $zkbio_address;

    public function __construct($zkbio_address)
    {
        $this->zkbio_address = $zkbio_address;
    }

    public function getzkbio_address()
    {
        return $this->zkbio_address;
    }
}
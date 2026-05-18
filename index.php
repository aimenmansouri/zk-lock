<?php

require_once 'config/env.php';
require_once 'zkbio.php';
require_once 'card-managment.php';

$zkbio_address = $_ENV['ZKBIO'];

$zkbio = new ZKBio($zkbio_address);


echo "zkbio address" . $zkbio->getzkbio_address();

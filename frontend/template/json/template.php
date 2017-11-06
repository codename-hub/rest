<?php
namespace codename\rest;
header('Content-Type: application/json');
print_r(json_encode(app::getResponse()->getData()));

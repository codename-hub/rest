<?php

namespace codename\rest;

app::getResponse()->setHeader('Content-Type: application/json');
print_r(json_encode(app::getResponse()->getData()));

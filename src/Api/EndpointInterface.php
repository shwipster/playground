<?php

namespace A24\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface EndpointInterface
{
    public function authenticate(Request $request): void;

    public function proccess(Request $request): Response;
}
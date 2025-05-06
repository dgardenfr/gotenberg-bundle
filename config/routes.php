<?php

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes) {
    $routes->add('dgarden_gotenberg_async_pdf_generation', '/_dg/pdf/generate')
        ->methods(['POST'])
        ->controller('dgarden.gotenberg.action.async_pdf_generation');
};
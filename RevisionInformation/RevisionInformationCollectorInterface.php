<?php

namespace Lsw\VersionInformationBundle\RevisionInformation;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface RevisionInformationCollectorInterface
{
    /**
     * @param string     $rootDir
     * @param Request    $request
     * @param Response   $response
     * @param \Exception $exception
     *
     * @return RevisionInformationFetcherInterface
     */
    public function collect($rootDir, Request $request, Response $response, \Exception $exception = null);
}

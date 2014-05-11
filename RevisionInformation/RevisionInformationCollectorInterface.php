<?php

namespace Lsw\VersionInformationBundle\RevisionInformation;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface RevisionInformationCollectorInterface
{
    /**
     * This collects the data for the repository. It can return null to indicate an invalid repository,
     * in this case it will continue trying to find a collector.
     *
     * @param string     $rootDir
     * @param Request    $request
     * @param Response   $response
     * @param \Exception $exception
     *
     * @return RevisionInformationFetcherInterface|null
     */
    public function collect($rootDir, Request $request, Response $response, \Exception $exception = null);

    /**
     * This should check whether the directory is a valid repository or not.
     *
     * @param string $dir
     *
     * @return boolean
     */
    public function isValidRepository($dir);
}

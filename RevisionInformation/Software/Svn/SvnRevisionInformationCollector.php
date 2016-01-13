<?php

namespace Lsw\VersionInformationBundle\RevisionInformation\Software\Svn;

use Lsw\VersionInformationBundle\RevisionInformation\RevisionInformationCollectorInterface;
use Lsw\VersionInformationBundle\RevisionInformation\RevisionInformationFetcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;

class SvnRevisionInformationCollector implements RevisionInformationCollectorInterface
{
    /**
     * @var string
     */
    private $rootDir;

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
    public function collect($rootDir, Request $request, Response $response, \Exception $exception = null)
    {
        $this->rootDir = $rootDir;
        $data = array();
        $data['information'] = $this->getInformation();
        $data['informationText'] = $this->getInformationText();
        $data['status'] = $this->getStatus();
        $data['statusText'] = $this->getStatusText();

        $fetcher = new SvnRevisionInformationFetcher();
        $fetcher->setData($data);

        return $fetcher;
    }

    /**
     * This should check whether the directory is a valid repository or not.
     *
     * @param string $dir
     *
     * @return boolean
     */
    public function isValidRepository($dir)
    {
        return file_exists($dir . '/.svn/');
    }

    private function getInformation()
    {
        $output = $this->execute('svn info --xml');

        return json_decode(json_encode(simplexml_load_string($output)), true);
    }

    private function getStatus()
    {
        $output = $this->execute('svn status --xml');

        return json_decode(json_encode(simplexml_load_string($output)), true);
    }

    private function getInformationText()
    {
        return $this->execute('svn info');
    }

    private function getStatusText()
    {
        return $this->execute('svn status');
    }

    private function execute($command, $addRootDir = true)
    {
        if ($addRootDir) {
            $command .= ' ' . $this->rootDir;
        }

        $process = new Process($command);
        $process->run();
        $output = $process->getOutput();
        if (!$process->isSuccessful()) {
            throw new \Exception($process->getErrorOutput());
        }

        return trim($output);
    }
}

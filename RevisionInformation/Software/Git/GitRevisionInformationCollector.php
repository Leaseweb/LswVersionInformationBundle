<?php

namespace Lsw\VersionInformationBundle\RevisionInformation\Software\Git;

use Lsw\VersionInformationBundle\RevisionInformation\RevisionInformationCollectorInterface;
use Lsw\VersionInformationBundle\RevisionInformation\RevisionInformationFetcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;

class GitRevisionInformationCollector implements RevisionInformationCollectorInterface
{
    private $data = array();

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

        $refs = $this->getRefs();
        $currentBranch = $this->getCurrentBranch();
        $head = $this->getHead($refs, $currentBranch);
        $remote = $this->getRemote($refs);
        $statusInfo = $this->getStatusInfo($rootDir);

        $data = array();
        $data['currentBranch'] = $currentBranch;
        $data['information'] = $this->getLogInformation($rootDir);
        $data['informationText'] = $this->getInformationText($rootDir);
        $data['status'] = $statusInfo ? explode("\n", $statusInfo) : array();
        $data['statusText'] = $statusInfo;
        $data['ahead'] = $this->getAheadInfo($head, $remote);
        $data['aheadText'] = $this->getAheadText($head, $remote);
        $data['behind'] = $this->getBehindInfo($head, $remote);
        $data['behindText'] = $this->getBehindText($head, $remote);

        $fetcher = new GitRevisionInformationFetcher();
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
        return file_exists($dir . '/.git');
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

    private function getRefs()
    {
        $output = $this->execute('cd ' . $this->rootDir . '; git --no-pager show-ref --dereference', false);

        return explode("\n", $output);
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function getCurrentBranch()
    {
        return $this->execute('git rev-parse --abbrev-ref HEAD', false);
    }

    /**
     * @param array $refs
     *
     * @return string
     */
    private function getRemote($refs)
    {
        $remote = '';
        foreach ($refs as $ref) {
            $remote = substr($ref, 41);
            if (stripos($remote, 'origin') !== false && stripos($remote, 'master') !== false) {
                break;
            }
        }

        return $remote;
    }

    /**
     * @param $refs
     * @param $currentBranch
     *
     * @return string
     */
    private function getHead($refs, $currentBranch)
    {
        $head = substr($refs[0], 41);
        foreach ($refs as $ref) {
            if (strstr($ref, $currentBranch)) {
                $head = substr($ref, 41);
                break;
            }
        }

        return $head;
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function getLogInformation()
    {
        $output = $this->execute(
            'git --no-pager log -1 --pretty=\'{"hash":"%h","date":"%ai","name":"%an","branch":"%d"}\''
        );

        return json_decode($output, true);
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function getInformationText()
    {
        return $this->execute('git --no-pager log -1 --decorate');
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function getStatusInfo()
    {
        return $this->execute('git --no-pager status --porcelain');
    }

    /**
     * @param string $head
     * @param string $remote
     *
     * @throws \Exception
     * @return array
     */
    private function getAheadInfo($head, $remote)
    {
        return $this->getExecuteBehindInfo($head . '..' . $remote);
    }

    private function getAheadText($head, $remote)
    {
        return $this->getExecuteBehindText($head . '..' . $remote);
    }

    private function getBehindInfo($head, $remote)
    {
        return $this->getExecuteBehindInfo($remote . '..' . $head);
    }

    private function getBehindText($head, $remote)
    {
        return $this->getExecuteBehindText($remote . '..' . $head);
    }

    private function getExecuteBehindInfo($argument)
    {
        $output = $this->execute('git --no-pager log --pretty=format: ' . $argument . ' --name-status');

        return $output ? array_filter(explode("\n", $output)) : array();
    }

    private function getExecuteBehindText($argument)
    {
        $output = $this->execute('git --no-pager log ' . $argument . ' --name-status');

        return $output;
    }
}

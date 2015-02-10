<?php
namespace Lsw\VersionInformationBundle\DataCollector;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;

/**
 * VersionInformationDataCollector
 *
 * @author Maurits van der Schee <m.vanderschee@leaseweb.com>
 */
class VersionInformationDataCollector extends DataCollector
{

    private $kernel;

    const SVN = 'svn';
    const GIT = 'git';

    /**
     * Class constructor
     *
     * @param KernelInterface $kernel Kernel object
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        if (isset($this->data) && is_array($this->data) && count($this->data) > 0) {
            return;
        }

        $this->data = (object) array();
        $dumper = new \Symfony\Component\Yaml\Dumper();
        $container = $this->kernel->getContainer();
        $rootDir = realpath($container->getParameter('root_dir') ?: $this->kernel->getRootDir() . '/../');

        if (file_exists($rootDir . '/.svn/')) {
            $this->data->mode = self::SVN;
            $this->collectSvn($rootDir, $request, $response, $exception);
        } elseif (file_exists($rootDir . '/.git/')) {
            $this->data->mode = self::GIT;
            $this->collectGit($rootDir, $request, $response, $exception);
        } else {
            throw new \Exception('Could not find Subversion or Git.');
        }

    }

    private function collectGit($rootDir, Request $request, Response $response, \Exception $exception = null)
    {
        $process = new Process('cd '.$rootDir.'; git --no-pager show-ref --dereference');
        $process->run();
        $output = $process->getOutput();
        if (!$process->isSuccessful()) {
            throw new \Exception($process->getErrorOutput());
        }

        $process = new Process('git rev-parse --abbrev-ref HEAD');
        $process->run();
        $currentBranch = trim($process->getOutput());
        if (!$process->isSuccessful()) {
            throw new \Exception($process->getErrorOutput());
        }

        $refs = explode("\n",trim($output));
        $head = substr($refs[0],41);
        foreach ($refs as $ref) {
            if (strstr($ref, $currentBranch)) {
                $head = substr($ref,41);
                break;
            }
        }
        foreach ($refs as $ref) {
            $remote = substr($ref,41);
            if (stripos($remote,'origin')!==false && stripos($remote,'master')!==false) {
                break;
            }
        }
        $ahead = "$head..$remote";
        $behind = "$remote..$head";

        $process = new Process('git --no-pager log -1 --pretty=\'{"hash":"%h","date":"%ai","name":"%an","branch":"%d"}\' ' . $rootDir);
        $process->run();
        $output = $process->getOutput();
        if (!$process->isSuccessful()) {
            throw new \Exception($process->getErrorOutput());
        }
        $this->data->information = json_decode($output);

        $process = new Process('git --no-pager log -1 --decorate ' . $rootDir);
        $process->run();
        $output = $process->getOutput();
        if (!$process->isSuccessful()) {
            throw new \Exception($process->getErrorOutput());
        }
        $this->data->informationText = $output;

        $process = new Process('git --no-pager status --porcelain ' . $rootDir);
        $process->run();
        $output = $process->getOutput();
        if (!$process->isSuccessful()) {
            throw new \Exception($process->getErrorOutput());
        }
        $this->data->status = $output ? explode("\n", trim($output)) : array();
        $this->data->statusText = $output;

        $process = new Process('git --no-pager log --pretty=format: '.$ahead.' --name-status ' . $rootDir);
        $process->run();
        $output = trim($process->getOutput());
        if (!$process->isSuccessful()) {
            throw new \Exception($process->getErrorOutput());
        }
        $this->data->ahead = $output ? explode("\n", trim($output)) : array();
        $this->data->ahead = array_filter($this->data->ahead);

        $process = new Process('git --no-pager log '.$ahead.' --name-status ' . $rootDir);
        $process->run();
        $output = trim($process->getOutput());
        if (!$process->isSuccessful()) {
            throw new \Exception($process->getErrorOutput());
        }
        $this->data->aheadText = $output;

        $process = new Process('git --no-pager log --pretty=format: '.$behind.' --name-status ' . $rootDir);
        $process->run();
        $output = $process->getOutput();
        if (!$process->isSuccessful()) {
            throw new \Exception($process->getErrorOutput());
        }
        $this->data->behind = $output ? explode("\n", trim($output)) : array();
        $this->data->behind = array_filter($this->data->behind);

        $process = new Process('git --no-pager log '.$behind.' --name-status ' . $rootDir);
        $process->run();
        $output = $process->getOutput();
        if (!$process->isSuccessful()) {
            throw new \Exception($process->getErrorOutput());
        }
        $this->data->behindText = $output;

    }

    private function collectSvn($rootDir, Request $request, Response $response,
            \Exception $exception = null)
    {
        $process = new Process('svn info --xml ' . $rootDir);
        $process->run();
        $output = $process->getOutput();
        if (!$process->isSuccessful()) {
            throw new \Exception($process->getErrorOutput());
        }
        $this->data->information = json_decode(json_encode(simplexml_load_string($output)));

        $process = new Process('svn info ' . $rootDir);
        $process->run();
        $output = $process->getOutput();
        if (!$process->isSuccessful()) {
            throw new \Exception($process->getErrorOutput());
        }
        $this->data->informationText = $output;

        $process = new Process('svn status --xml ' . $rootDir);
        $process->run();
        $output = $process->getOutput();
        if (!$process->isSuccessful()) {
            throw new \Exception($process->getErrorOutput());
        }
        $this->data->status = json_decode(json_encode(simplexml_load_string($output)));

        $process = new Process('svn status ' . $rootDir);
        $process->run();
        $output = $process->getOutput();
        if (!$process->isSuccessful()) {
            throw new \Exception($process->getErrorOutput());
        }
        $this->data->statusText = $output;
    }

    /**
     * Get the string 'svn' or 'git', depending on the mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->data->mode;
    }

    /**
     * Get the last revision number from svn info
     *
     * @return number
     */
    public function getRevision()
    {
        if ($this->data->mode == self::SVN) {
            return $this->data->information->entry->commit->{'@attributes'}
                    ->revision;
        } elseif ($this->data->mode == self::GIT) {
            return $this->data->information->hash;
        }
    }

    /**
     * Get the last author from svn info
     *
     * @return string
     */
    public function getAuthor()
    {
        if ($this->data->mode == self::SVN) {
            return $this->data->information->entry->commit->author;
        } elseif ($this->data->mode == self::GIT) {
            return $this->data->information->name;
        }
    }

    /**
     * Get the branche from svn info
     *
     * @return string
     */
    public function getBranch()
    {
        if ($this->data->mode == self::SVN) {
            return str_replace(
                    $this->data->information->entry->repository->root, '',
                    $this->data->information->entry->url);
        } elseif ($this->data->mode == self::GIT) {
            return $this->data->information->branch;
        }
    }

    /**
     * Get the last modified date from svn info
     *
     * @return date
     */
    public function getDate()
    {
        if ($this->data->mode == self::SVN) {
            return strtotime($this->data->information->entry->commit->date);
        } elseif ($this->data->mode == self::GIT) {
            return $this->data->information->date;
        }
    }

    /**
     * Get the number of dirty files from svn status
     *
     * @return number
     */
    public function getDirtyCount()
    {
        if ($this->data->mode == self::SVN) {
            if (!isset($this->data->status->target->entry)) {
                return 0;
            }

            return count($this->data->status->target->entry);
        } elseif ($this->data->mode == self::GIT) {
            return count($this->data->status);
        }

    }

    /**
     * Get the number of commits ahead from git log
     *
     * @return number
     */
    public function getAheadCount()
    {
        if ($this->data->mode == self::GIT) {
            return count($this->data->ahead);
        }

        return 0;
    }

    /**
     * Get the number of commits behind from git log
     *
     * @return number
     */
    public function getBehindCount()
    {
        if ($this->data->mode == self::GIT) {
            return count($this->data->behind);
        }

        return 0;
    }

    /**
     * Get the svn info output
     *
     * @return string
     */
    public function getInformationText()
    {
        return $this->data->informationText;
    }

    /**
     * Get the svn status output
     *
     * @return string
     */
    public function getStatusText()
    {
        return $this->data->statusText;
    }

    /**
     * Get the git log ahead output
     *
     * @return string
     */
    public function getAheadText()
    {
        return $this->data->aheadText;
    }

    /**
     * Get the git log behind output
     *
     * @return string
     */
    public function getBehindText()
    {
        return $this->data->behindText;
    }
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'version_information';
    }
}

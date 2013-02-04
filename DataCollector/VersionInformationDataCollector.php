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
        if (isset($this->data)) {
            return;
        }

        $this->data = (object) array();
        $dumper = new \Symfony\Component\Yaml\Dumper();
        $rootDir = realpath($this->kernel->getRootDir().'/../');

        if (file_exists($rootDir.'/.svn/')) {
            $this->data->mode = self::SVN;
            $this->collectSvn($rootDir, $request, $response, $exception);
        } elseif (file_exists($rootDir.'/.git/')) {
            $this->data->mode = self::GIT;
            $this->collectGit($rootDir, $request, $response, $exception);
        } else {
            throw new \Exception('Could not find Subversion or Git.');
        }

    }

    private function collectGit($rootDir, Request $request, Response $response, \Exception $exception = null)
    {
        $process = new Process('git --no-pager log -1 --pretty=\'{"hash":"%h","date":"%ai","name":"%an"}\' '.$rootDir);
        $process->run();
        $output = $process->getOutput();
        if (!$process->isSuccessful()) {
            throw new \Exception($process->getErrorOutput());
        }
        $this->data->information = json_decode($output);

        $process = new Process('git log -1 '.$rootDir);
        $process->run();
        $output = $process->getOutput();
        if (!$process->isSuccessful()) {
            throw new \Exception($process->getErrorOutput());
        }
        $this->data->informationText = $output;

        $process = new Process('git status --porcelain '.$rootDir);
        $process->run();
        $output = trim($process->getOutput());
        if (!$process->isSuccessful()) {
            throw new \Exception($process->getErrorOutput());
        }
        if ($output) {
             $this->data->status = explode("\n", $output);
        } else {
            $this->data->status = array();
        }


        $process = new Process('git status '.$rootDir);
        $process->run();
        $output = $process->getOutput();
        if (!$process->isSuccessful()) {
            throw new \Exception($process->getErrorOutput());
        }
        $this->data->statusText = $output;
    }

    private function collectSvn($rootDir, Request $request, Response $response, \Exception $exception = null)
    {
        $process = new Process('svn info --xml '.$rootDir);
        $process->run();
        $output = $process->getOutput();
        if (!$process->isSuccessful()) {
            throw new \Exception($process->getErrorOutput());
        }
        $this->data->information = json_decode(json_encode(simplexml_load_string($output)));

        $process = new Process('svn info '.$rootDir);
        $process->run();
        $output = $process->getOutput();
        if (!$process->isSuccessful()) {
            throw new \Exception($process->getErrorOutput());
        }
        $this->data->informationText = $output;

        $process = new Process('svn status --xml '.$rootDir);
        $process->run();
        $output = $process->getOutput();
        if (!$process->isSuccessful()) {
            throw new \Exception($process->getErrorOutput());
        }
        $this->data->status = json_decode(json_encode(simplexml_load_string($output)));

        $process = new Process('svn status '.$rootDir);
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
            return $this->data->information->entry->commit->{'@attributes'}->revision;
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
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'version_information';
    }
}

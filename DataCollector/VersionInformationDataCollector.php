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
     * Get the last revision number from svn info
     *
     * @return number
     */
    public function getRevision()
    {
        return $this->data->information->entry->commit->{'@attributes'}->revision;
    }

    /**
     * Get the last author from svn info
     *
     * @return string
     */
    public function getAuthor()
    {
        return $this->data->information->entry->commit->author;
    }

    /**
     * Get the last modified date from svn info
     *
     * @return date
     */
    public function getDate()
    {
        return strtotime($this->data->information->entry->commit->date);
    }

    /**
     * Get the number of dirty files from svn status
     *
     * @return number
     */
    public function getDirtyCount()
    {
        if (!isset($this->data->status->target->entry)) {
            return 0;
        }

        return count($this->data->status->target->entry);
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

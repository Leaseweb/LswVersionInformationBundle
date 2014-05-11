<?php

namespace Lsw\VersionInformationBundle\RevisionInformation;

/**
 * This class contains base methods that only one revision control has so the others don't have to implement it
 *
 * Class AbstractRevisionControlInformation
 * @package Lsw\VersionInformationBundle\RevisionControlInformation
 */
abstract class AbstractRevisionInformationFetcher implements RevisionInformationFetcherInterface
{
    /**
     * @var array
     */
    protected $data;

    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getViewName()
    {
        return ucfirst($this->getMode());
    }

    public function getAheadCount()
    {
        return false;
    }

    public function getBehindCount()
    {
        return false;
    }

    public function getInformationText()
    {
        return false;
    }

    public function getStatusText()
    {
        return false;
    }

    public function getAheadText()
    {
        return false;
    }

    public function getBehindText()
    {
        return false;
    }

    /**
     * Gets the color of dirty files
     *
     * @return string
     */
    public function getDirtyFilesColor()
    {
        return $this->getColorByCount($this->getDirtyCount());
    }

    /**
     * Gets the color of commits ahead
     *
     * @return string
     */
    public function getAheadColor()
    {
        return $this->getColorByCount($this->getAheadCount());
    }

    /**
     * Gets the color of commits behind
     *
     * @return string
     */
    public function getBehindColor()
    {
        return $this->getColorByCount($this->getBehindCount());
    }

    protected function getColorByCount($count)
    {
        if ($count > 5) {
            return 'red';
        }
        if ($count > 0) {
            return 'yellow';
        }

        return 'green';
    }
}

<?php

namespace Lsw\VersionInformationBundle\RevisionInformation\Software\Svn;

use Lsw\VersionInformationBundle\DataCollector\VersionInformationDataCollector;
use Lsw\VersionInformationBundle\RevisionInformation\AbstractRevisionInformationFetcher;

class SvnRevisionInformationFetcher extends AbstractRevisionInformationFetcher
{
    /**
     * @return string
     */
    public function getMode()
    {
        return VersionInformationDataCollector::MODE_GIT;
    }

    public function getViewName()
    {
        return $this->getMode();
    }

    /**
     * Get the last revision number from svn info
     *
     * @return integer
     */
    public function getRevision()
    {
        return $this->data['information']['entry']['commit']['@attributes']['revision'];
    }

    /**
     * Get the last author from svn info
     *
     * @return string
     */
    public function getAuthor()
    {
        return $this->data['information']['entry']['commit']['author'];
    }

    /**
     * Get the branch from svn info
     *
     * @return string
     */
    public function getBranchInfo()
    {
        return str_replace(
            $this->data['information']['entry']['repository']['root'],
            '',
            $this->data['information']['entry']['url']
        );
    }

    /**
     * Get the branch from svn info
     *
     * @return string
     */
    public function getCurrentBranch()
    {
        // unlike git, there's no such thing as a remote
        return $this->getBranchInfo();
    }

    /**
     * Get the last modified date from svn info
     *
     * @return string
     */
    public function getDate()
    {
        return strtotime($this->data['information']['entry']['commit']['date']);
    }

    /**
     * Get the number of dirty files from svn status
     *
     * @return number
     */
    public function getDirtyCount()
    {
        if (!isset($this->data['status']['target']['entry'])) {
            return 0;
        }

        return count($this->data['status']['target']['entry']);
    }

    public function getInformationText()
    {
        return $this->data['informationText'];
    }

    public function getStatusText()
    {
        return $this->data['statusText'];
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAcCAYAAABh2p9gAAAAAXNSR0IArs4c6QAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB90BBxUdJ4JIDLQAAAAZdEVYdENvbW1lbnQAQ3JlYXRlZCB3aXRoIEdJTVBXgQ4XAAABAUlEQVRIx+2ToY6DQBRFz24a0g/A8i1tDaKgUJOmIfixKDxNKvoFSMSoSUbUNhmNBz5gVFPTpA7DqsV0JWZ3OeqKd0+eubDw9/nYbDbjnMLPuT9chDMKj8cjxhistVhrCYIArTWe5wHgeR5aa4IgwFpLmqZcr1e01ux2u3fh4XCgqirCMGS73eKco+97oigCII5juq7DOQfA6/UiSRLO5zNSynfh5XJhv9+jtSbLMgCUUgghWK/XCCFQSk1FYwzDMNA0Db7vvwtvtxtSSvI8RwgBQNu2PB4PTqcT9/udruum4jj+vIfVd7DWAvB8PqnrejpQSlGWJUVRLNP7rcKF/8AXC1ZjwNsw7R0AAAAASUVORK5CYII=';
    }
}

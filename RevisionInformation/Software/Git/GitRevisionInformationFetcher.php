<?php

namespace Lsw\VersionInformationBundle\RevisionInformation\Software\Git;

use Lsw\VersionInformationBundle\DataCollector\VersionInformationDataCollector;
use Lsw\VersionInformationBundle\RevisionInformation\AbstractRevisionInformationFetcher;

class GitRevisionInformationFetcher extends AbstractRevisionInformationFetcher
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'git';
    }

    /**
     * Get the last revision number from svn info
     *
     * @return integer
     */
    public function getRevision()
    {
        return $this->data['information']['hash'];
    }

    /**
     * Get the last author from svn info
     *
     * @return string
     */
    public function getAuthor()
    {
        return $this->data['information']['name'];
    }

    /**
     * Get the branch from svn info
     *
     * @return string
     */
    public function getBranchInfo()
    {
        return $this->data['information']['branch'];
    }

    /**
     * Get the branch from svn info
     *
     * @return string
     */
    public function getCurrentBranch()
    {
        return $this->data['currentBranch'];
    }

    /**
     * Get the last modified date from svn info
     *
     * @return string
     */
    public function getDate()
    {
        return $this->data['information']['date'];
    }

    /**
     * Get the number of dirty files from svn status
     *
     * @return number
     */
    public function getDirtyCount()
    {
        return count($this->data['status']);
    }

    public function getAheadCount()
    {
        return count($this->data['ahead']);
    }

    public function getBehindCount()
    {
        return count($this->data['behind']);
    }

    public function getAheadText()
    {
        return $this->data['aheadText'];
    }

    public function getBehindText()
    {
        return $this->data['behindText'];
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAcCAYAAABh2p9gAAAAAXNSR0IArs4c6QAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB90BBxUfMDOt6/EAAAAZdEVYdENvbW1lbnQAQ3JlYXRlZCB3aXRoIEdJTVBXgQ4XAAAA3ElEQVRIx+2TLQ6EMBSEB7IYJKIhIQhOQlV9D8AhsNiGg7wEiUBgQHEQBCFBcATUroJA+NllgyKMe2nmy0xfCzy6vzTf999XAvWrE94MWFXVOaCmaZBSgohQFAWICEKI6Zxz/hX+mg9SSgghEMcxmqYBYwxBEKAsy/+eDRFBKYW6rncrc85X6ebJFwlt20bbtqtKc8M4j/DDO+z7Ho7jTKYtw6ml5HmOMAzheR4Mw4DrurvGYRhgWdbxUtI0ha7rUEqBMYau6xBF0SYwyzIkSQLTNBdNnr/86NEv+gC9ykorte4x7QAAAABJRU5ErkJggg==';
    }
}

<?php

namespace Lsw\VersionInformationBundle\RevisionInformation;

interface RevisionInformationFetcherInterface
{
    /**
     * @return string
     */
    public function getMode();

    /**
     * @return string
     */
    public function getViewName();

    /**
     * Set the information data
     *
     * @param array $data
     */
    public function setData($data);

    /**
     * Get the last revision number from svn info
     *
     * @return integer
     */
    public function getRevision();

    /**
     * Get the last author from svn info
     *
     * @return string
     */
    public function getAuthor();

    /**
     * Get the branch from svn info
     *
     * @return string
     */
    public function getBranchInfo();

    /**
     * Get the branch from svn info
     *
     * @return string
     */
    public function getCurrentBranch();

    /**
     * Get the last modified date from svn info
     *
     * @return string
     */
    public function getDate();

    /**
     * Get the number of dirty files from svn status
     *
     * @return number
     */
    public function getDirtyCount();

    /**
     * Get the number of commits ahead from git log
     *
     * @return integer
     */
    public function getAheadCount();

    /**
     * Get the number of commits behind from git log
     *
     * @return integer
     */
    public function getBehindCount();

    /**
     * Gets the color of dirty files
     *
     * @return string
     */
    public function getDirtyFilesColor();

    /**
     * Gets the color of commits ahead
     *
     * @return string
     */
    public function getAheadColor();

    /**
     * Gets the color of commits behind
     *
     * @return string
     */
    public function getBehindColor();

    /**
     * Get the svn info output
     *
     * @return string
     */
    public function getInformationText();

    /**
     * Get the svn status output
     *
     * @return string
     */
    public function getStatusText();

    /**
     * Get the git log ahead output
     *
     * @return string
     */
    public function getAheadText();

    /**
     * Get the git log behind output
     *
     * @return string
     */
    public function getBehindText();

    /**
     * @return string
     */
    public function getIcon();
}

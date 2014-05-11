<?php
namespace Lsw\VersionInformationBundle\DataCollector;

use Lsw\VersionInformationBundle\RevisionInformation\RevisionInformationFetcherInterface;
use Lsw\VersionInformationBundle\RevisionInformation\Software\Git\GitRevisionInformationCollector;
use Lsw\VersionInformationBundle\RevisionInformation\Software\Svn\SvnRevisionInformationCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * VersionInformationDataCollector
 *
 * @author Maurits van der Schee <m.vanderschee@leaseweb.com>
 */
class VersionInformationDataCollector extends DataCollector
{
    const MODE_SVN = 'svn';
    const MODE_GIT = 'git';
    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var string
     */
    private $appRootDir;

    /**
     * @var array
     */
    private $settings;

    /**
     * @param string $rootDir
     * @param string $appRootDir
     * @param array  $settings
     */
    public function __construct($rootDir, $appRootDir, array $settings)
    {
        $this->rootDir = $rootDir;
        $this->appRootDir = $appRootDir;
        $this->settings = $settings;
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
        $rootDir = realpath($this->rootDir ? : $this->appRootDir . '/../');

        $this->data->settings = $this->settings;
        if (file_exists($rootDir . '/.svn/')) {
            $collector = new SvnRevisionInformationCollector();
        } elseif (file_exists($rootDir . '/.git/')) {
            $collector = new GitRevisionInformationCollector();
        } else {
            throw new \Exception('Could not find Subversion or Git.');
        }

        $this->data->fetcher = $collector->collect($rootDir, $request, $response, $exception);
    }

    /**
     * @return RevisionInformationFetcherInterface
     */
    public function getFetcher()
    {
        return $this->data->fetcher;
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        return $this->data->settings;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'version_information';
    }
}

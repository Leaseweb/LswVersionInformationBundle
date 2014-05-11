<?php
namespace Lsw\VersionInformationBundle\DataCollector;

use Lsw\VersionInformationBundle\RevisionInformation\RevisionInformationCollectorInterface;
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
     * @var RevisionInformationCollectorInterface[]
     */
    private $collectors;

    /**
     * @param string                                  $rootDir
     * @param string                                  $appRootDir
     * @param array                                   $settings
     * @param RevisionInformationCollectorInterface[] $collectors
     */
    public function __construct($rootDir, $appRootDir, array $settings, array $collectors)
    {
        $this->rootDir = $rootDir;
        $this->appRootDir = $appRootDir;
        $this->settings = $settings;
        $this->collectors = $collectors;
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

        if (!isset($this->collectors['svn'])) {
            $this->collectors['svn'] = new SvnRevisionInformationCollector();
        }
        if (!isset($this->collectors['git'])) {
            $this->collectors['git'] = new GitRevisionInformationCollector();
        }

        foreach ($this->collectors as $name => $collector) {
            if (is_array($collector) && isset($collector['class'])) {
                $class = $collector['class'];
                $collector = new $class();
            }

            if (!($collector instanceof RevisionInformationCollectorInterface)) {
                throw new \Exception(
                    sprintf(
                        'Class %s must implement the interface
                        Lsw\VersionInformationBundle\RevisionInformation\RevisionInformationCollectorInterface',
                        get_class($collector)
                    )
                );
            }

            if ($collector->isValidRepository($rootDir)) {
                $fetcher = $collector->collect($rootDir, $request, $response, $exception);
                if ($fetcher !== null) {
                    if (!($fetcher instanceof RevisionInformationFetcherInterface)) {
                        throw new \RuntimeException(
                            sprintf(
                                'Class %s must return an implementation of
                                Lsw\VersionInformationBundle\RevisionInformation\RevisionInformationFetcherInterface',
                                get_class($collector)
                            )
                        );
                    }

                    $this->data->fetcher = $fetcher;

                    return;
                }
            }
        }

        throw new \RuntimeException('Could not find a valid repository collector.');
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

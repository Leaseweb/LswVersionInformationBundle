LswVersionInformationBundle
===========================

How do the testers know what revision of our application they are testing and what branch or 
tag they are testing? This is especially a problem with acceptance testing where interactions
between various systems are tested. Because the testers do not have command line access on
the Linux machines that run the acceptance environment they cannot simply issue the "svn info"
and "svn status" (or "git log -1" and "git status") commands like developers can.

To solve this problem we wrote a Symfony bundle called LswVersionInformationBundle. It shows
the output of the "svn info" and "svn status" (or "git log -1" and "git status") commands in a
tab in the Symfony2 debug toolbar. This bundle is actually a rewrite of the Symfony1 
"lwTestingInformationPlugin" we wrote a year ago that can be found on the link below.

http://www.leaseweblabs.com/2011/12/subversion-revision-information-in-the-symfony-debug-toolbar/

### Installation

To install LswVersionInformationBundle with Composer just add the following to your 'composer.json' file:

    {
        require: {
            "leaseweb/version-information-bundle": "dev-master"
            ...
        }
    }

The next thing you should do is install the bundle by executing the following command:

    php composer.phar update leaseweb/version-information-bundle

Finally, add the bundle to the registerBundles function of the AppKernel class in the 'app/AppKernel.php' file:

    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Lsw\VersionInformationBundle\LswVersionInformationBundle(),
            // ...
        );


Now the Subversion information should show up with a little 'svn' (or 'Git') icon in your debug toolbar.

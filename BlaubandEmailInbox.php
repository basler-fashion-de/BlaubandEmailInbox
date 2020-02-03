<?php

namespace BlaubandEmailInbox;

use BlaubandEmailInbox\Installers\Models;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BlaubandEmailInbox extends Plugin
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        $container->setParameter('blauband_email_inbox.plugin_dir', $this->getPath());
        parent::build($container);
    }

    public function install(InstallContext $context)
    {
        $this->setup(null, $context->getCurrentVersion());
        parent::install($context);
    }

    public function update(UpdateContext $context)
    {
        $this->setup($context->getCurrentVersion(), $context->getUpdateVersion());
        parent::update($context);
    }

    public function uninstall(UninstallContext $context)
    {
        if(!$context->keepUserData()){
            (new Models($this->container->get('models')))->uninstall();
        }
    }

    public function setup($oldVersion = null, $newVersion = null)
    {
        $versions = [
            '1.0.0' => function () {
                (new Models($this->container->get('models')))->update();
                return true;
            },
        ];

        foreach ($versions as $version => $callback) {
            if ($oldVersion === null || (version_compare($oldVersion, $version, '<') && version_compare($version, $newVersion, '<='))) {
                if (!$callback($this, $oldVersion, $version, $newVersion)) {
                    return false;
                }
            }
        }

        return true;
    }
}

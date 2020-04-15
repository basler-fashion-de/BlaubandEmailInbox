<?php

namespace BlaubandEmailInbox\Subscribers;

use BlaubandEmail\Models\LoggedMail;
use Enlight\Event\SubscriberInterface;
use Shopware\Components\Model\ModelManager;

class BlaubandEmail implements SubscriberInterface
{
    /**
     * @var ModelManager
     */
    private $modelManager;
    private $pluginDirectory;

    public function __construct(ModelManager $modelManager, $pluginDirectory)
    {
        $this->modelManager = $modelManager;
        $this->pluginDirectory = $pluginDirectory;
    }

    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Backend_BlaubandEmail' => 'onPostDispatch',
            'Enlight_Controller_Action_PreDispatch_Backend_BlaubandEmail' => 'onPreDispatch',
        ];
    }

    public function onPreDispatch(\Enlight_Event_EventArgs $args)
    {
        /** @var \Shopware_Controllers_Backend_BlaubandEmail $subject */
        $subject = $args->getSubject();
        $subject->View()->addTemplateDir($this->pluginDirectory . '/Resources/views');
    }

    public function onPostDispatch(\Enlight_Event_EventArgs $args)
    {
        /** @var \Shopware_Controllers_Backend_BlaubandEmail $subject */
        $subject = $args->getSubject();
        $view = $subject->View();
        $request = $subject->Request();
        $mailId = $request->getParam('mailId');

        if (empty($mailId)) {
            return;
        }

        $mail = $this->modelManager->find(LoggedMail::class, $mailId);

        if (empty($mail)) {
            return;
        }

        $mailContent = $mail->getBody();
        $mailContent = preg_replace('#<head(.*?)>(.*?)</head>#is', '', $mailContent);
        $mailContent = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $mailContent);
        $mailContent = preg_replace('#<style(.*?)>(.*?)</style>#is', '', $mailContent);
        $mailContent = strip_tags($mailContent, '<br>');
        $mailContent = str_replace(['<br />', '<br/>', '<br>'], "\n", $mailContent);

        $separator = "\n&nbsp;\n&nbsp;\n------------------------\n&nbsp;\n&nbsp;\n";

        $bodyContent = $view->getAssign('bodyContent');

        $view->assign('bodyContent', $bodyContent . $separator . $mailContent);
        $view->assign('mailId', $mailId);
    }
}
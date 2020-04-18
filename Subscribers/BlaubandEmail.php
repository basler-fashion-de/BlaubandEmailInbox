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
    /**
     * @var \Enlight_Components_Snippet_Manager
     */
    private $snippets;
    private $pluginDirectory;

    public function __construct(
        ModelManager $modelManager,
        \Enlight_Components_Snippet_Manager $snippets,
        $pluginDirectory
    )
    {
        $this->modelManager = $modelManager;
        $this->snippets = $snippets;
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

        $startSeparatorText = $this->snippets->getNamespace('blauband/mail')->get('startSeparator');
        $endSeparatorText = $this->snippets->getNamespace('blauband/mail')->get('endSeparator');
        $createDateText = $this->snippets->getNamespace('blauband/mail')->get('mailDate');
        $subjectText = $this->snippets->getNamespace('blauband/mail')->get('mailSubject');
        $fromText = $this->snippets->getNamespace('blauband/mail')->get('mailFrom');
        $toText = $this->snippets->getNamespace('blauband/mail')->get('mailTo');

        $startSeparator = "\n&nbsp;\n&nbsp;\n------------$startSeparatorText------------\n&nbsp;\n";
        $endSeparator = "\n&nbsp;\n&nbsp;\n------------$endSeparatorText------------\n&nbsp;\n";

        $mailHeader = $createDateText . ': ' . date_format( $mail->getCreateDate(),'d M yy H:i:s') . "\n";
        $mailHeader .= $subjectText . ': ' . $mail->getSubject() . "\n";
        $mailHeader .= $fromText . ': ' . $mail->getFrom() . "\n";
        $mailHeader .= $toText . ': ' . $mail->getTo() . "\n";
        $mailHeader .= "\n&nbsp;\n&nbsp;\n";

        $bodyContent = $view->getAssign('bodyContent');

        $view->assign('bodyContent', $bodyContent . $startSeparator . $mailHeader . $mailContent . $endSeparator);
        $view->assign('mailId', $mailId);
    }
}
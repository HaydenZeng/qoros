<?php
namespace Common\HttpApi;

use Zend\Mail\Message;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mime\Mime;
use Zend\Mime\Part as MimePart;
use Zend\Mime\Message as MimeMessage;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Model\ViewModel;
use Zend\View\View;

class EmailApi implements ServiceLocatorAwareInterface{

    /**
     * @var Smtp
     */
    protected $smtp;
    protected $config;
    protected $services;

    public function __construct($config) {
        $this->smtp = new Smtp();
        $this->smtp->setOptions(new SmtpOptions($config['transport']['options']));
        $this->config = $config;
    }

    public function send($email, $subject, $content, $url = null) {
        $view = new ViewModel();
        $view->setTemplate('email/common');
        $view->setVariables(array('title' => $subject, 'content' => $content, 'url' => $url));
        $viewRender = $this->getServiceLocator()->get('ViewRenderer');
        $htmlBody = $viewRender->render($view);

        $htmlPart = new MimePart($htmlBody);
        $htmlPart->type = Mime::TYPE_HTML;
        $body = new MimeMessage();
        $body->setParts(array($htmlPart));

        $message = new Message();
        $message->setEncoding("UTF-8");
        $fromEmail = $this->config['transport']['options']['connection_config']['username'];
        $message->addFrom($fromEmail, "e财会")
            ->addTo($email)
            ->setSubject($subject);
        $message->setBody($body);
        // Set UTF-8 charset
        $headers = $message->getHeaders();
        $headers->removeHeader('Content-Type');
        $headers->addHeaderLine('Content-Type', 'text/html; charset=UTF-8');

        $this->smtp->send($message);
    }

    /**
     * Set service locator
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->services = $serviceLocator;
    }

    /**
     * Get service locator
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->services;
    }
}
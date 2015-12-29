<?php
namespace Common\Model;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;
use Doctrine\ORM\EntityManager;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;
use Traversable;

class Model implements ServiceLocatorAwareInterface{
    protected $services;
    private $logger;
    /**
     * @var EntityManager
     */
    private static $em;
    /**
     * @var EventManagerInterface
     */
    protected $events;

    /* (non-PHPdoc)
     * @see \Zend\ServiceManager\ServiceLocatorAwareInterface::setServiceLocator()
    */
    public function setServiceLocator(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator) {
        $this->services = $serviceLocator;
    }

    /* (non-PHPdoc)
     * @see \Zend\ServiceManager\ServiceLocatorAwareInterface::getServiceLocator()
    */
    public function getServiceLocator() {
        return $this->services;
    }

    /**
     * @return Logger
     */
    public function getLogger() {
        if ($this->logger == null) {
            $this->logger = new Logger();
            $writer = new Stream(ROOT.'/data/log/'.__CLASS__.'.log');
            $this->logger->addWriter($writer);
        }
    }
    
    public function getEntityManager()
    {
        if (null === self::$em) {
            self::$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        }
        return self::$em;
    }

    /**
     * @param EntityManager $em
     */
    public static function setEntityManager($em)
    {
        self::$em = $em;
    }

    /**
     * Set the event manager instance used by this context
     *
     * @param  EventManagerInterface $events
     * @return mixed
     */
    protected function setEventManager(EventManagerInterface $events)
    {
        $identifiers = array(__CLASS__, get_called_class(), 'eck');
        if (isset($this->eventIdentifier)) {
            if ((is_string($this->eventIdentifier))
                || (is_array($this->eventIdentifier))
                || ($this->eventIdentifier instanceof Traversable)
            ) {
                $identifiers = array_unique(array_merge($identifiers, (array) $this->eventIdentifier));
            } elseif (is_object($this->eventIdentifier)) {
                $identifiers[] = $this->eventIdentifier;
            }
            // silently ignore invalid eventIdentifier types
        }
        $events->setIdentifiers($identifiers);
        $this->events = $events;
        return $this;
    }

    /**
     * Retrieve the event manager
     *
     * Lazy-loads an EventManager instance if none registered.
     *
     * @return EventManagerInterface
     */
    protected function getEventManager()
    {
        if (!$this->events instanceof EventManagerInterface) {
            $this->setEventManager(new EventManager());
        }
        return $this->events;
    }

    protected function getErrorMessage($inputFilter)
    {
        foreach ($inputFilter->getInvalidInput() as $error) {
            return $error->getErrorMessage();
        }
    }





}

?>
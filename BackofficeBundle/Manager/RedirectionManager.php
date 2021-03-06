<?php

namespace OpenOrchestra\BackofficeBundle\Manager;

use Doctrine\ODM\MongoDB\DocumentManager;
use OpenOrchestra\BaseBundle\Context\CurrentSiteIdInterface;
use OpenOrchestra\ModelInterface\Event\RedirectionEvent;
use OpenOrchestra\ModelInterface\Model\RedirectionInterface;
use OpenOrchestra\ModelInterface\Model\SiteAliasInterface;
use OpenOrchestra\ModelInterface\RedirectionEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use OpenOrchestra\ModelInterface\Repository\SiteRepositoryInterface;

/**
 * Class RedirectionManager
 */
class RedirectionManager
{
    protected $redirectionClass;
    protected $documentManager;
    protected $eventDispatcher;
    protected $contextManager;
    protected $siteRepository;

    /**
     * @param string                   $redirectionClass
     * @param CurrentSiteIdInterface   $contextManager
     * @param DocumentManager          $documentManager
     * @param EventDispatcherInterface $eventDispatcher
     * @param SiteRepositoryInterface  $siteRepository
     */
    public function __construct(
        $redirectionClass,
        CurrentSiteIdInterface $contextManager,
        DocumentManager $documentManager,
        EventDispatcherInterface $eventDispatcher,
        SiteRepositoryInterface $siteRepository
    ){
        $this->redirectionClass = $redirectionClass;
        $this->contextManager = $contextManager;
        $this->documentManager = $documentManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->siteRepository = $siteRepository;
    }

    /**
     * @param string $pattern
     * @param string $nodeId
     * @param string $language
     */
    public function createRedirection($pattern, $nodeId, $language)
    {
        $redirectionClass = $this->redirectionClass;
        $site = $this->siteRepository->findOneBySiteId($this->contextManager->getCurrentSiteId());
        /** @var SiteAliasInterface $alias */
        foreach ($site->getAliases() as $key => $alias) {
            if ($language == $alias->getLanguage()) {
                /** @var RedirectionInterface $redirection */
                $redirection = new $redirectionClass();
                $redirection->setNodeId($nodeId);
                $redirection->setLocale($language);
                $redirection->setRoutePattern(str_replace('//', '/', '/' . $alias->getPrefix() . '/' . $pattern));
                $redirection->setSiteId($site->getSiteId());
                $redirection->setSiteName($site->getName());
                $this->documentManager->persist($redirection);
                $this->documentManager->flush($redirection);
                $this->eventDispatcher->dispatch(RedirectionEvents::REDIRECTION_CREATE, new RedirectionEvent($redirection));
            }
        }
    }
}

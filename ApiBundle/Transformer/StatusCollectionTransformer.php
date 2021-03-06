<?php

namespace OpenOrchestra\ApiBundle\Transformer;

use Doctrine\Common\Collections\ArrayCollection;
use OpenOrchestra\BaseApi\Facade\FacadeInterface;
use OpenOrchestra\BaseApi\Transformer\AbstractTransformer;
use OpenOrchestra\ApiBundle\Facade\StatusCollectionFacade;

/**
 * Class StatusCollectionTransformer
 */
class StatusCollectionTransformer extends AbstractTransformer
{
    /**
     * @param ArrayCollection $mixed
     * @param StatusInterface $currentStatus
     *
     * @return FacadeInterface|StatusCollectionFacade
     */
    public function transform($mixed, $currentStatus = null)
    {
        $facade = new StatusCollectionFacade();

        foreach ($mixed as $status) {
            $facade->addStatus($this->getTransformer('status')->transform($status, $currentStatus));
        }

        $facade->addLink('_self_add', $this->generateRoute(
            'open_orchestra_backoffice_status_new',
            array()
        ));

        return $facade;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'status_collection';
    }

}

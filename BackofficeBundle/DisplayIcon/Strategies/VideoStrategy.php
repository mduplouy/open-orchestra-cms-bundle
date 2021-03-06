<?php

namespace OpenOrchestra\BackofficeBundle\DisplayIcon\Strategies;

use OpenOrchestra\DisplayBundle\DisplayBlock\Strategies\VideoStrategy as BasevideoStrategy;
use OpenOrchestra\BackofficeBundle\DisplayIcon\Strategies\AbstractStrategy;

/**
 * Class VideoStrategy
 */
class VideoStrategy extends AbstractStrategy
{
    /**
     * Check if the strategy support this block
     *
     * @param string $block
     *
     * @return boolean
     */
    public function support($block)
    {
        return BasevideoStrategy::VIDEO === $block;
    }

    /**
     * Display an icon for a block
     *
     * @return string
     */
    public function show()
    {
        return $this->render('OpenOrchestraBackofficeBundle:Block/Video:showIcon.html.twig');
    }

    /**
     * Get the name of the strategy
     *
     * @return string
     */
    public function getName()
    {
        return 'video';
    }
}

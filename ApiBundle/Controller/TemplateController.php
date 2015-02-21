<?php

namespace OpenOrchestra\ApiBundle\Controller;

use OpenOrchestra\ApiBundle\Facade\FacadeInterface;
use OpenOrchestra\ModelInterface\Event\TemplateEvent;
use OpenOrchestra\ModelInterface\Model\TemplateInterface;
use OpenOrchestra\ModelInterface\TemplateEvents;
use OpenOrchestra\ApiBundle\Controller\Annotation as Api;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TemplateController
 *
 * @Config\Route("template")
 */
class TemplateController extends BaseController
{
    /**
     * @param string $templateId
     *
     * @Config\Route("/{templateId}", name="open_orchestra_api_template_show")
     * @Config\Method({"GET"})
     * @Api\Serialize()
     *
     * @return FacadeInterface
     */
    public function showAction($templateId)
    {
        $template = $this->get('open_orchestra_model.repository.template')->findOneByTemplateId($templateId);

        return $this->get('open_orchestra_api.transformer_manager')->get('template')->transform($template);
    }

    /**
     * @param string $templateId
     *
     * @Config\Route("/{templateId}/delete", name="open_orchestra_api_template_delete")
     * @Config\Method({"DELETE"})
     *
     * @return Response
     */
    public function deleteAction($templateId)
    {
        /** @var TemplateInterface $template */
        $template = $this->get('open_orchestra_model.repository.template')->findOneByTemplateId($templateId);
        $template->setDeleted(true);
        $this->dispatchEvent(TemplateEvents::TEMPLATE_DELETE, new TemplateEvent($template));
        $this->get('doctrine.odm.mongodb.document_manager')->flush();

        return new Response('', 200);
    }
}

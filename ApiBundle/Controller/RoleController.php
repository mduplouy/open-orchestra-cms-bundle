<?php

namespace OpenOrchestra\ApiBundle\Controller;

use OpenOrchestra\ApiBundle\Controller\ControllerTrait\HandleRequestDataTable;
use OpenOrchestra\BaseApi\Facade\FacadeInterface;
use OpenOrchestra\ModelInterface\Event\RoleEvent;
use OpenOrchestra\ModelInterface\RoleEvents;
use OpenOrchestra\BaseApiBundle\Controller\Annotation as Api;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use OpenOrchestra\BaseApiBundle\Controller\BaseController;

/**
 * Class RoleController
 *
 * @Config\Route("role")
 */
class RoleController extends BaseController
{
    use HandleRequestDataTable;

    /**
     * @param int $roleId
     *
     * @Config\Route("/{roleId}", name="open_orchestra_api_role_show")
     * @Config\Method({"GET"})
     *
     * @Config\Security("has_role('ROLE_ACCESS_ROLE')")
     *
     * @Api\Serialize()
     *
     * @return FacadeInterface
     */
    public function showAction($roleId)
    {
        $role = $this->get('open_orchestra_model.repository.role')->find($roleId);

        return $this->get('open_orchestra_api.transformer_manager')->get('role')->transform($role);
    }

    /**
     * @param Request $request
     *
     * @Config\Route("", name="open_orchestra_api_role_list")
     * @Config\Method({"GET"})
     *
     * @Config\Security("has_role('ROLE_ACCESS_ROLE')")
     *
     * @Api\Serialize()
     *
     * @return FacadeInterface
     */
    public function listAction(Request $request)
    {
        $columnsNameToEntityAttribute = array(
            'description' => array('key' => 'name'),
            'from_status' => array('key' => 'fromStatus.name'),
            'to_status'   => array('key' => 'toStatus.name'),
        );
        $repository = $this->get('open_orchestra_model.repository.role');
        $collectionTransformer = $this->get('open_orchestra_api.transformer_manager')->get('role_collection');
        $elementTransformer = $this->get('open_orchestra_api.transformer_manager')->get('role');

        return $this->handleRequestDataTable($request, $repository, $columnsNameToEntityAttribute, $collectionTransformer, $elementTransformer);
    }

    /**
     * @param int $roleId
     *
     * @Config\Route("/{roleId}/delete", name="open_orchestra_api_role_delete")
     * @Config\Method({"DELETE"})
     *
     * @Config\Security("has_role('ROLE_ACCESS_ROLE')")
     *
     * @return Response
     */
    public function deleteAction($roleId)
    {
        $role = $this->get('open_orchestra_model.repository.role')->find($roleId);
        $dm = $this->get('doctrine.odm.mongodb.document_manager');
        $this->dispatchEvent(RoleEvents::ROLE_DELETE, new RoleEvent($role));
        $dm->remove($role);
        $dm->flush();

        return new Response('', 200);
    }
}

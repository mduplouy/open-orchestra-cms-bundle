<?php

namespace OpenOrchestra\UserAdminBundle\Controller\Api;

use OpenOrchestra\BaseApiBundle\Controller\BaseController;
use OpenOrchestra\BaseApi\Facade\FacadeInterface;
use OpenOrchestra\UserBundle\Event\UserEvent;
use OpenOrchestra\UserBundle\UserEvents;
use OpenOrchestra\BaseApiBundle\Controller\Annotation as Api;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UserController
 *
 * @Config\Route("user")
 */
class UserController extends BaseController
{
    /**
     * @param string $userId
     *
     * @Config\Route("/{userId}", name="open_orchestra_api_user_show")
     * @Config\Method({"GET"})
     *
     * @Config\Security("has_role('ROLE_ACCESS_USER')")
     *
     * @Api\Serialize()
     *
     * @return FacadeInterface
     */
    public function showAction($userId)
    {
        $user = $this->get('open_orchestra_user.repository.user')->find($userId);

        return $this->get('open_orchestra_api.transformer_manager')->get('user')->transform($user);
    }

    /**
     * @param Request $request
     *
     * @Config\Route("", name="open_orchestra_api_user_list")
     * @Config\Method({"GET"})
     *
     * @Config\Security("has_role('ROLE_ACCESS_USER')")
     *
     * @Api\Serialize()
     *
     * @return FacadeInterface
     */
    public function listAction(Request $request)
    {
        $columns = $request->get('columns');
        $search = $request->get('search');
        $search = (null !== $search && isset($search['value'])) ? $search['value'] : null;
        $order = $request->get('order');
        $skip = $request->get('start');
        $skip = (null !== $skip) ? (int)$skip : null;
        $limit = $request->get('length');
        $limit = (null !== $limit) ? (int)$limit : null;

        $columnsNameToEntityAttribute = array(
            'username' => array('key' => 'username')
        );

        $repository =  $this->get('open_orchestra_user.repository.user');

        $userCollection = $repository->findForPaginateAndSearch($columnsNameToEntityAttribute, $columns, $search, $order, $skip, $limit);
        $recordsTotal = $repository->count();
        $recordsFiltered = $repository->countWithSearchFilter($columnsNameToEntityAttribute, $columns, $search);

        $facade = $this->get('open_orchestra_api.transformer_manager')->get('user_collection')->transform($userCollection);
        $facade->recordsTotal = $recordsTotal;
        $facade->recordsFiltered =$recordsFiltered;

        return $facade;
    }

    /**
     * @param int $userId
     *
     * @Config\Route("/{userId}/delete", name="open_orchestra_api_user_delete")
     * @Config\Method({"DELETE"})
     *
     * @Config\Security("has_role('ROLE_ACCESS_USER')")
     *
     * @return Response
     */
    public function deleteAction($userId)
    {
        $user = $this->get('open_orchestra_user.repository.user')->find($userId);
        $dm = $this->get('doctrine.odm.mongodb.document_manager');
        $this->dispatchEvent(UserEvents::USER_DELETE, new UserEvent($user));
        $dm->remove($user);
        $dm->flush();

        return new Response('', 200);
    }
}

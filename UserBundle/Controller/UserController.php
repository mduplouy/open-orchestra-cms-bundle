<?php

namespace PHPOrchestra\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UserController
 */
class UserController extends Controller
{
    /**
     * @param Request $request
     * @param string  $userId
     *
     * @Config\Route("/form/{userId}", name="php_orchestra_user_user_form")
     * @Config\Method({"GET", "POST"})
     *
     * @return Response
     */
    public function formAction(Request $request, $userId)
    {
        $user = $this->get('php_orchestra_user.repository.user')->find($userId);

        $form = $this->createForm(
            'user',
            $user,
            array(
                'action' => $this->generateUrl('php_orchestra_user_user_form', array(
                    'userId' => $userId,
                ))
            )
        );

        $form->handleRequest($request);
        if ($form->isValid()) {
            $documentManager = $this->get('doctrine.odm.mongodb.document_manager');
            $documentManager->persist($user);
            $documentManager->flush();

            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('php_orchestra_user.form.user.success')
            );
        }

        return $this->render('PHPOrchestraBackofficeBundle:Editorial:template.html.twig', array(
            'form' => $form->createView()
        ));
    }
}
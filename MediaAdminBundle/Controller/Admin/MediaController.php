<?php

namespace OpenOrchestra\MediaAdminBundle\Controller\Admin;

use OpenOrchestra\BackofficeBundle\Controller\AbstractAdminController;
use OpenOrchestra\Media\Event\MediaEvent;
use OpenOrchestra\Media\MediaEvents;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MediaController
 */
class MediaController extends AbstractAdminController
{
    /**
     * @param Request $request
     * @param string  $folderId
     *
     * @Config\Route("/media/new/{folderId}", name="open_orchestra_media_admin_media_new")
     * @Config\Method({"GET", "POST"})
     *
     * @return Response
     */
    public function newAction(Request $request, $folderId)
    {
        $folderRepository = $this->get('open_orchestra_media.repository.media_folder');
        $folder = $folderRepository->find($folderId);

        $mediaClass = $this->container->getParameter('open_orchestra_media.document.media.class');
        $media = new $mediaClass();
        $media->setMediaFolder($folder);

        $form = $this->createForm('media', $media, array(
            'action' => $this->generateUrl('open_orchestra_media_admin_media_new', array(
                'folderId' => $folderId,
            ))
        ));

        $form->handleRequest($request);

        $this->handleForm(
            $form,
            $this->get('translator')->trans('open_orchestra_media_admin.form.media.success'),
            $media
        );

        return $this->renderAdminForm($form);
    }

    /**
     * @Config\Route("/media/list/folders", name="open_orchestra_media_admin_media_list_form")
     * @Config\Method({"GET"})
     *
     * @return Response
     */
    public function showFoldersAction()
    {
        $siteId = $this->get('open_orchestra_backoffice.context_manager')->getCurrentSiteId();
        $rootFolders = $this->get('open_orchestra_media.repository.media_folder')->findAllRootFolderBySiteId($siteId);

        return $this->render( 'OpenOrchestraMediaAdminBundle:Tree:showModalFolderTree.html.twig', array(
                'folders' => $rootFolders,
        ));
    }

    /**
     * @param Request $request
     * @param string  $mediaId
     *
     * @Config\Route("/media/{mediaId}/crop", name="open_orchestra_media_admin_media_crop")
     * @Config\Method({"GET", "POST"})
     *
     * @return Response
     * @throws \Doctrine\ODM\MongoDB\LockException
     */
    public function cropAction(Request $request, $mediaId)
    {
        $form = $this->createForm('media_crop', null, array(
            'action' => $this->generateUrl('open_orchestra_media_admin_media_crop', array(
                'mediaId' => $mediaId,
            ))
        ));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $mediaRepository = $this->get('open_orchestra_media.repository.media');
            $media = $mediaRepository->find($mediaId);

            $gaufretteManager = $this->get('open_orchestra_media.manager.gaufrette');
            $filename = $media->getFilesystemName();
            $this->get('filesystem')->dumpFile(
                $this->container->getParameter('open_orchestra_media.tmp_dir') . '/' . $filename,
                $gaufretteManager->getFileContent($filename)
            );

            $this->get('open_orchestra_media.manager.image_resizer')->crop(
                $media,
                $data['x'],
                $data['y'],
                $data['h'],
                $data['w'],
                $data['format']
            );

            $this->dispatchEvent(MediaEvents::MEDIA_CROP, new MediaEvent($media));
        }


        return $this->renderAdminForm($form);
    }

    /**
     * @param Request $request
     * @param string  $format
     * @param string  $mediaId
     *
     * @Config\Route("/media/override/{mediaId}/{format}", name="open_orchestra_media_admin_media_override")
     * @Config\Method({"GET", "POST"})
     *
     * @return Response
     *
     * @throws \Doctrine\ODM\MongoDB\LockException
     */
    public function overrideAction(Request $request, $format, $mediaId)
    {
        $mediaRepository = $this->get('open_orchestra_media.repository.media');
        $media = $mediaRepository->find($mediaId);

        $form = $this->createForm('media', null, array(
            'action' => $this->generateUrl('open_orchestra_media_admin_media_override', array(
                'mediaId' => $mediaId,
                'format' => $format
            ))
        ));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $file = $form->getData()->getFile();
            $tmpDir = $this->container->getParameter('open_orchestra_media.tmp_dir');
            $file->move($tmpDir, $format . '-' . $media->getFilesystemName());
            $this->get('open_orchestra_media.manager.image_resizer')->override($media, $format);

            $this->dispatchEvent(MediaEvents::MEDIA_CROP, new MediaEvent($media));
        }

        return $this->renderAdminForm($form);
    }

    /**
     * @param Request $request
     * @param string  $mediaId
     *
     * @Config\Route("/media/{mediaId}/meta", name="open_orchestra_media_admin_media_meta")
     * @Config\Method({"GET", "POST"})
     *
     * @return Response
     * @throws \Doctrine\ODM\MongoDB\LockException
     */
    public function metaAction(Request $request, $mediaId)
    {
        $mediaRepository = $this->get('open_orchestra_media.repository.media');
        $media = $mediaRepository->find($mediaId);

        $form = $this->createForm('media_meta', $media, array(
            'action' => $this->generateUrl('open_orchestra_media_admin_media_meta', array(
                'mediaId' => $mediaId,
            ))
        ));

        $form->handleRequest($request);

        $this->handleForm($form, $this->get('translator')->trans('open_orchestra_media_admin.form.media.success'));

        return $this->renderAdminForm($form);
    }
}

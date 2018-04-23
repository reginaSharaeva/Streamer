<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Camera;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;

/**
 * Camera controller.
 *
 * @Route("camera")
 */
class CameraController extends Controller
{
    /**
     * Lists all camera entities.
     *
     * @Route("/", name="camera_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $cameras = $em->getRepository('AppBundle:Camera')->findAll();

        return $this->render('camera/index.html.twig', array(
            'cameras' => $cameras,
        ));
    }

    /**
     * Creates a new camera entity.
     *
     * @Route("/new", name="camera_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $camera = new Camera();
        $form = $this->createForm('AppBundle\Form\CameraType', $camera);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($camera);
            $em->flush();

            return $this->redirectToRoute('camera_show', array('id' => $camera->getId()));
        }

        return $this->render('camera/new.html.twig', array(
            'camera' => $camera,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a camera entity.
     *
     * @Route("/{id}", name="camera_show")
     * @Method("GET")
     */
    public function showAction(Camera $camera)
    {
        $deleteForm = $this->createDeleteForm($camera);

        return $this->render('camera/show.html.twig', array(
            'camera' => $camera,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing camera entity.
     *
     * @Route("/{id}/edit", name="camera_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Camera $camera)
    {
        $deleteForm = $this->createDeleteForm($camera);
        $editForm = $this->createForm('AppBundle\Form\CameraType', $camera);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('camera_edit', array('id' => $camera->getId()));
        }

        return $this->render('camera/edit.html.twig', array(
            'camera' => $camera,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a camera entity.
     *
     * @Route("/{id}", name="camera_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Camera $camera)
    {
        $form = $this->createDeleteForm($camera);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($camera);
            $em->flush();
        }

        return $this->redirectToRoute('camera_index');
    }

    /**
     * Creates a form to delete a camera entity.
     *
     * @param Camera $camera The camera entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Camera $camera)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('camera_delete', array('id' => $camera->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}

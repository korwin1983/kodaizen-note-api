<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\Get;
use AppBundle\Entity\Note;
use FOS\RestBundle\View\View; // utilisation de la vue de FOSRestBundle
use FOS\RestBundle\Controller\Annotations as Rest; //annotations pour FOSRestBundle
use AppBundle\Form\Type\NoteType;


class NoteController extends Controller
{




    /**
     * @Rest\View(statusCode=Response::HTTP_CREATED)
     * @Rest\Post("/notes")
     */
    public function postNotesAction(Request $request)
    {
    $note = new Note();

        $form = $this->createForm(NoteType::class, $note);

        $form->submit($request->request->all()); // Validation des données

        if ($form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $em->persist($note);
            $em->flush();
            return $note;
        } else {
            return $form;
        }

//        $validator = $this->get('validator');
//
//        $note->setName($request->get('name'));
//        $note->setContent($request->get('content'));
//
//        $listErrors = $validator->validate($note);
//
//        if (count($listErrors)>0){
//            return $listErrors;
//        }
//        else {
//            $em = $this->get('doctrine.orm.entity_manager');
//            $em->persist($note);
//            $em->flush();
//            return $note;
//        }


    }

    /**
     * @Rest\View()
     * @Rest\Get("/notes")
     */
    public function getNotesAction(Request $request)
    {
        $notes = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:Note')
            ->findAll();
        /* @var $notes Note[] */

        return $notes;
    }

    /**
     * @Rest\View()
     * @Get("/notes/{id}")
     */
    public function getNoteAction(Request $request)
    {
        $note = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:Note')
            ->find($request->get('id'));
        /* @var $note Note */

        if (empty($note)) {
            return new JsonResponse(['message' => 'Note not found'], Response::HTTP_NOT_FOUND);
        }

        return $note;

    }
}


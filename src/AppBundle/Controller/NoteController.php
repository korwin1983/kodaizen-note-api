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
	 * @Rest\View()
	 * @Rest\Put("/notes/{id}")
	 */
	public function updateNoteAction(Request $request)
	{
		return $this->updateNote($request, false);
	}

	/**
	 * @Rest\View()
	 * @Rest\Patch("/notes/{id}")
	 */
	public function patchNoteAction(Request $request)
	{
		return $this->updateNote($request, true);
	}


	private function updateNote(Request $request, $clearMissing)
	{
		$note = $this->get('doctrine.orm.entity_manager')
			->getRepository('AppBundle:Note')
			->find($request->get('id')); // L'identifiant en tant que paramètre n'est plus nécessaire
		/* @var $note Note */

		if (empty($note)) {
			return new JsonResponse(['message' => 'Note not found'], Response::HTTP_NOT_FOUND);
		}

		$form = $this->createForm(NoteType::class, $note);

		$form->submit($request->request->all(), $clearMissing);

		if ($form->isValid()) {
			$em = $this->get('doctrine.orm.entity_manager');
			// l'entité vient de la base, donc le merge n'est pas nécessaire.
			// il est utilisé juste par soucis de clarté
			$em->merge($note);
			$em->flush();
			return $note;
		} else {
			return $form;
		}
	}


	//delete note
    /**
     * @Rest\View(statusCode=Response::HTTP_NO_CONTENT)
     * @Rest\Delete("/notes/{id}")
     */
    public function removeProjectAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $note = $em->getRepository('AppBundle:Note')
            ->find($request->get('id'));
        /* @var $note Notes */

        if ($note){
            $em->remove($note);
            $em->flush();
        }
    }


	//add note
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


    }
	//get all notes
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

    //get note by id
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


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
use AppBundle\Entity\Project;


class NoteController extends Controller
{


	/**
	 * @Rest\View(serializerGroups={"note"})
	 * @Rest\Put("/projects/{id}/notes/{note_id}")
	 */
	public function updateNoteAction(Request $request)
	{
		return $this->updateNote($request, false);
	}

	/**
	 * @Rest\View(serializerGroups={"note"})
	 * @Rest\Patch("/projects/{id}/notes/{note_id}")
	 */
	public function patchNoteAction(Request $request)
	{
		return $this->updateNote($request, true);
	}


	private function updateNote(Request $request, $clearMissing)
	{
        $project = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:Project')
            ->find($request->get('id'));
        /* @var $project Project */

        if (empty($project)) {
            return $this->projectNotFound();
        }

		$note = $this->get('doctrine.orm.entity_manager')
			->getRepository('AppBundle:Note')
			->find($request->get('note_id')); // L'identifiant en tant que paramètre n'est plus nécessaire
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
     * @Rest\Delete("/projects/{id}/notes/{note_id}")
     */
    public function removeNoteAction(Request $request)
    {
        $project = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:Project')
            ->find($request->get('id'));
        /* @var $project Project */

        if (empty($project)) {
            return $this->projectNotFound();
        }
        $em = $this->get('doctrine.orm.entity_manager');
        $note = $em->getRepository('AppBundle:Note')
            ->find($request->get('note_id'));
        /* @var $note Notes */

        if ($note){
            $em->remove($note);
            $em->flush();
        }
    }


	//add note
    /**
     * @Rest\View(statusCode=Response::HTTP_CREATED, serializerGroups={"note"})
     * @Rest\Post("projects/{id}/notes")
     */
    public function postNotesAction(Request $request)
    {
		$project = $this->get('doctrine.orm.entity_manager')
			->getRepository('AppBundle:Project')
			->find($request->get('id'));
		/* @var $project Project */

		if (empty($project)) {
			return $this->projectNotFound();
		}

		$note = new Note();
		$note->setProject($project); // Ici, le projet est associé a la note
		$form = $this->createForm(NoteType::class, $note);

		// Le paramétre false dit à Symfony de garder les valeurs dans notre
		// entité si l'utilisateur n'en fournit pas une dans sa requête
		$form->submit($request->request->all());

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
     * @Rest\View(serializerGroups={"note"})
     * @Rest\Get("/projects/{id}/notes")
     */
    public function getNotesAction(Request $request)
    {
		$project = $this->get('doctrine.orm.entity_manager')
			->getRepository('AppBundle:Project')
			->find($request->get('id')); // L'identifiant en tant que paramétre n'est plus nécessaire
		/* @var $project Project */

		if (empty($project)) {
			return $this->projectNotFound();
		}

        $notes = $project->getNotes();
		$notes = $notes->toArray();
        return $notes;

    }

    //get note by id
    /**
     * @Rest\View(serializerGroups={"note"})
     * @Get("/projects/{id}/notes/{note_id}")
     */
    public function getNoteAction(Request $request)
    {

        $project = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:Project')
            ->find($request->get('id')); // L'identifiant en tant que paramétre n'est plus nécessaire
        /* @var $project Project */

        if (empty($project)) {
            return $this->projectNotFound();
        }

        $note = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:Note')
            ->find($request->get('id'));
        /* @var $note Note */

        if (empty($note)) {
            return new JsonResponse(['message' => 'Note not found'], Response::HTTP_NOT_FOUND);
        }

        return $note;

    }


	private function projectNotFound()
	{
		return \FOS\RestBundle\View\View::create(['message' => 'Project not found'], Response::HTTP_NOT_FOUND);
	}

}


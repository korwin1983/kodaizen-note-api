<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Project; //importation du modèle Project
use FOS\RestBundle\View\View; // utilisation de la vue de FOSRestBundle
use FOS\RestBundle\Controller\Annotations as Rest; //annotations pour FOSRestBundle
use AppBundle\Form\Type\ProjectType;

class ProjectController extends Controller
{


	//update project
	/**
	 * @Rest\View()
	 * @Rest\Put("/projects/{id}")
	 */
	public function putPlaceAction(Request $request)
	{
		$project = $this->get('doctrine.orm.entity_manager')
			->getRepository('AppBundle:Project')
			->find($request->get('id')); // L'identifiant en tant que paramètre n'est plus nécessaire
		/* @var $project Project */

		if (empty($project)) {
			return new JsonResponse(['message' => 'Project not found'], Response::HTTP_NOT_FOUND);
		}

		$form = $this->createForm(ProjectType::class, $project);

		$form->submit($request->request->all());

		if ($form->isValid()) {
			$em = $this->get('doctrine.orm.entity_manager');
			// l'entité vient de la base, donc le merge n'est pas nécessaire.
			// il est utilisé juste par soucis de clarté
			$em->merge($project);
			$em->flush();
			return $project;
		} else {
			return $form;
		}
	}


	//delete project
	/**
	 * @Rest\View(statusCode=Response::HTTP_NO_CONTENT)
	 * @Rest\Delete("/projects/{id}")
	 */
	public function removeProjectAction(Request $request)
	{
		$em = $this->get('doctrine.orm.entity_manager');
		$project = $em->getRepository('AppBundle:Project')
			->find($request->get('id'));
		/* @var $project Project */

		if ($project) {
			$em->remove($project);
			$em->flush();
		}
	}


	//add project
	/**
	 * @Rest\View(statusCode=Response::HTTP_CREATED)
	 * @Rest\Post("/projects")
	 */
	public function postPlacesAction(Request $request)
	{
		$project = new Project();

		$form = $this->createForm(ProjectType::class, $project);

		$form->submit($request->request->all()); // Validation des données

		if ($form->isValid()) {
			$em = $this->get('doctrine.orm.entity_manager');
			$em->persist($project);
			$em->flush();
			return $project;
		} else {
			return $form;
		}
	}

	//get all projects
	/**
	 * @Rest\View()
	 * @Rest\Get("/projects")
	 */
	public function getProjectsAction(Request $request)
	{

		$projects = $this->get('doctrine.orm.entity_manager')
			->getRepository('AppBundle:Project')
			->findAll();
		/* @var $projects Project[] */


		return $projects;

	}

	//get project by id
	/**
	 * @Rest\View()
	 * @Rest\Get("/projects/{id}")
	 */
	public function getProjectAction($id, Request $request)
	{
		$project = $this->get('doctrine.orm.entity_manager')
			->getRepository('AppBundle:Project')
			->find($id);
		/* @var $project Project */

		if (empty($project)) {
			return new JsonResponse(['message' => 'Project not found'], Response::HTTP_NOT_FOUND);
		}

		return $project;

	}

}
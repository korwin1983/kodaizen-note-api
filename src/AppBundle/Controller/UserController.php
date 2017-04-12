<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\User; //importation du modèle Project
use FOS\RestBundle\View\View; // utilisation de la vue de FOSRestBundle
use FOS\RestBundle\Controller\Annotations as Rest; //annotations pour FOSRestBundle
use AppBundle\Form\Type\UserType;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserController extends Controller {
	//add user
	/**
	 * @Rest\View(statusCode=Response::HTTP_CREATED)
	 * @Rest\Post("/users")
	 */
	public function postUsersAction(Request $request)
	{
		$user = new User();

		$form = $this->createForm(UserType::class, $user);

		$form->submit($request->request->all()); // Validation des données

		if ($form->isValid()) {
			$em = $this->get('doctrine.orm.entity_manager');
			$em->persist($user);
			$em->flush();
			return $user;
		} else {
			return $form;
		}
	}

	//get all users
	/**
	 * @Rest\View()
	 * @Rest\Get("/users")
	 */
	public function getUsersAction(Request $request)
	{

		$users = $this->get('doctrine.orm.entity_manager')
			->getRepository('AppBundle:User')
			->findAll();
		/* @var $users User[] */

		return $users;
	}


	//get user by id
	/**
	 * @Rest\View()
	 * @Rest\Get("/users/{id}")
	 */
	public function getUserAction($id, Request $request)
	{
		$project = $this->get('doctrine.orm.entity_manager')
			->getRepository('AppBundle:User')
			->find($id);
		/* @var $user User */

		if (empty($user)) {
			return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
		}

		return $user;

	}


	/**
	 * @Rest\View()
	 * @Rest\Put("/users/{id}")
	 */
	public function updateUserAction(Request $request)
	{
		return $this->updateUser($request, true);
	}

	/**
	 * @Rest\View()
	 * @Rest\Patch("/users/{id}")
	 */
	public function patchUserAction(Request $request)
	{
		return $this->updateUser($request, false);
	}




	private function updateUser(Request $request, $clearMissing)
	{
		$user = $this->get('doctrine.orm.entity_manager')
			->getRepository('AppBundle:User')
			->find($request->get('id')); // L'identifiant en tant que paramètre n'est plus nécessaire
		/* @var $user User */

		if (empty($user)) {
			return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
		}

		$form = $this->createForm(UserType::class, $user);

		$form->submit($request->request->all(), $clearMissing);

		if ($form->isValid()) {
			$em = $this->get('doctrine.orm.entity_manager');
			// l'entité vient de la base, donc le merge n'est pas nécessaire.
			// il est utilisé juste par soucis de clarté
			$em->merge($user);
			$em->flush();
			return $user;
		} else {
			return $form;
		}
	}


	//delete user
	/**
	 * @Rest\View(statusCode=Response::HTTP_NO_CONTENT)
	 * @Rest\Delete("/users/{id}")
	 */
	public function removeUserAction(Request $request)
	{
		$em = $this->get('doctrine.orm.entity_manager');
		$user = $em->getRepository('AppBundle:User')
			->find($request->get('id'));
		/* @var $user User */

		if ($user) {
			$em->remove($user);
			$em->flush();
		}
	}



}
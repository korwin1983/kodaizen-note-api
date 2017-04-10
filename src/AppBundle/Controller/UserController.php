<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\User; //importation du modèle Project
use FOS\RestBundle\View\View; // utilisation de la vue de FOSRestBundle
use FOS\RestBundle\Controller\Annotations as Rest; //annotations pour FOSRestBundle
use AppBundle\Form\Type\UserType;

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
}
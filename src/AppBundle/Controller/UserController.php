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

class UserController extends Controller
{
    //add user
    /**
     * @Rest\View(statusCode=Response::HTTP_CREATED, serializerGroups={"user"})
     * @Rest\Post("/users")
     */
    public function postUsersAction(Request $request)
    {

        $user = new User();

        $form = $this->createForm(UserType::class, $user);

        $form->submit($request->request->all()); // Validation des données

        if ($form->isValid()) {
            $role = $user->getRole();
            if($role === "ROLE_ADMIN" && !$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
                return \FOS\RestBundle\View\View::create(['message' => 'Invalid access rights'], Response::HTTP_FORBIDDEN);
            }
            $user->setRoles(array($role));
            $encoder = $this->get('security.password_encoder');
            // le mot de passe en claire est encodé avant la sauvegarde
            $encoded = $encoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($encoded);
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
     * @Rest\View(serializerGroups={"user"})
     * @Rest\Get("/users")
     */
    public function getUsersAction(Request $request)
    {

        // On vérifie que l'utilisateur dispose bien du rôle ROLE_ADMIN
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            // Sinon on déclenche une exception « Accès interdit »
            return \FOS\RestBundle\View\View::create(['message' => 'Invalid access rights'], Response::HTTP_FORBIDDEN);
        }

        $users = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:User')
            ->findAll();
        /* @var $users User[] */

        return $users;
    }


    //get user by id
    /**
     * @Rest\View(serializerGroups={"user"})
     * @Rest\Get("/users/{id}")
     */
    public function getUserAction($id, Request $request)
    {

        // On vérifie que l'utilisateur dispose bien du rôle ROLE_ADMIN
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            // Sinon on déclenche une exception « Accès interdit »
            return \FOS\RestBundle\View\View::create(['message' => 'Invalid access rights'], Response::HTTP_FORBIDDEN);
        }

        $user = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:User')
            ->find($id);
        /* @var $user User */

        if (empty($user)) {
            return $this->userNotFound();
        }

        return $user;

    }


    /**
     * @Rest\View(serializerGroups={"user"})
     * @Rest\Put("/users/{id}")
     */
    public function updateUserAction(Request $request)
    {

        // On vérifie que l'utilisateur dispose bien du rôle ROLE_ADMIN
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            // Sinon on déclenche une exception « Accès interdit »
            return \FOS\RestBundle\View\View::create(['message' => 'Invalid access rights'], Response::HTTP_FORBIDDEN);
        }

        return $this->updateUser($request, true);
    }

    /**
     * @Rest\View(serializerGroups={"user"})
     * @Rest\Patch("/users/{id}")
     */
    public function patchUserAction(Request $request)
    {

        // On vérifie que l'utilisateur dispose bien du rôle ROLE_ADMIN
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            // Sinon on déclenche une exception « Accès interdit »
            return \FOS\RestBundle\View\View::create(['message' => 'Invalid access rights'], Response::HTTP_FORBIDDEN);
        }

        return $this->updateUser($request, false);
    }


    private function updateUser(Request $request, $clearMissing)
    {
        $user = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:User')
            ->find($request->get('id')); // L'identifiant en tant que paramètre n'est plus nécessaire
        /* @var $user User */

        if (empty($user)) {
            return $this->userNotFound();
        }

        if ($clearMissing) { // Si une mise à jour complète, le mot de passe doit être validé
            $options = ['validation_groups' => ['Default', 'FullUpdate']];
        } else {
            $options = []; // Le groupe de validation par défaut de Symfony est Default
        }

        $form = $this->createForm(UserType::class, $user, $options);

        $form->submit($request->request->all(), $clearMissing);

        if ($form->isValid()) {

            // Si l'utilisateur veut changer son mot de passe
            if (!empty($user->getPlainPassword())) {
                $encoder = $this->get('security.password_encoder');
                $encoded = $encoder->encodePassword($user, $user->getPlainPassword());
                $user->setPassword($encoded);
            }

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

        // On vérifie que l'utilisateur dispose bien du rôle ROLE_ADMIN
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            // Sinon on déclenche une exception « Accès interdit »
            return \FOS\RestBundle\View\View::create(['message' => 'Invalid access rights'], Response::HTTP_FORBIDDEN);
        }

        $em = $this->get('doctrine.orm.entity_manager');
        $user = $em->getRepository('AppBundle:User')
            ->find($request->get('id'));
        /* @var $user User */

        if ($user) {
            $em->remove($user);
            $em->flush();
        }
    }


    private function userNotFound()
    {
        //return \FOS\RestBundle\View\View::create(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('User not found');
    }


}
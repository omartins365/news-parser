<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\LoginType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class SecurityController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    private function getUserRepository()
    {
        return $this->entityManager->getRepository(User::class);
    }
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastEmail = $authenticationUtils->getLastUsername();

        $form = $this->createForm(LoginType::class); 
        // dd($request->toArray());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // retrieve the form data
            $data = $form->getData();
            // try to authenticate the user
            $user = $this->getUserRepository()->findOneBy(['email' => $data->getEmail()]);

            if (!$user) {
                throw new CustomUserMessageAuthenticationException('Invalid email or password');
            }

            $isPasswordValid = $this->passwordEncoder->isPasswordValid($user, $data->getPassword());

            if (!$isPasswordValid) {
                throw new CustomUserMessageAuthenticationException('Invalid email or password');
            }

            // authenticate the user
            $token = new UsernamePasswordToken($user, $user->getPassword(), 'main', $user->getRoles());
            $this->get('security.token_storage')->setToken($token);

            // redirect to the dashboard page
            return $this->redirectToRoute('dashboard');
        }

        // return $this->render('security/login.html.twig', [
        //     'last_username' => $lastEmail,
        //     'error' => $error,
        //     'loginForm' => $form->createView()
        // ]);
        return new Response($this->renderForm('security/login.html.twig', [
            // 'last_username' => $lastEmail,
            // 'error' => $error,
            // 'loginForm' => $form->createView()
            'form' => $form
        ]));
    }
}

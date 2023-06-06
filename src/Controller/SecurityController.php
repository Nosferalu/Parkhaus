<?php

namespace App\Controller;

use App\Security\UserProvider;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class SecurityController extends AbstractController
{
    private $dbConnection;
    private $requestStack;
    private $tokenStorage;

    public function __construct(Connection $dbConnection, RequestStack $requestStack, TokenStorageInterface $tokenStorage)
    {
        $this->dbConnection = $dbConnection;
        $this->requestStack = $requestStack;
        $this->tokenStorage = $tokenStorage;
    }

    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // Last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        // Render the login template with the necessary data
        return $this->render('pages/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    public function authenticate(Request $request, UserProvider $userProvider): \Symfony\Component\HttpFoundation\RedirectResponse
    {

        $email = $request->request->get('_username'); // Retrieve the email value
        $password = $request->request->get('_password');


        // Retrieve the user from the user provider
        $user = $userProvider->loadUserByIdentifier($email);

        // Check if the password is valid
        if ($password !== $user->getPassword()) {
            throw new AuthenticationException('Invalid credentials');
        }

        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->tokenStorage->setToken($token);

        return $this->redirectToRoute('home');
    }


    /**
     * @Route("/register", name="app_register", methods={"GET", "POST"})
     */
    public function register(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        // Check if the request method is POST
        if ($request->isMethod('POST')) {
            // Get the necessary registration data from the request
            $firstName = $request->request->get('vorname');
            $lastName = $request->request->get('nachname');
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            $kennzeichen = $request->request->get('kennzeichen');
            $dauerparker = $request->request->get('dauerparker') ? 1 : 0;

            // Generate a UUID for the Parker ID
            $parkerId = Uuid::uuid4()->toString();

            // Create a new Car (Fahrzeuge) entry
            $this->dbConnection->executeQuery('
            INSERT INTO Fahrzeuge (kennzeichen)
            VALUES (?)
        ', [$kennzeichen]);

            // Save the user data to the database
            $this->dbConnection->executeQuery('
            INSERT INTO Parker (parker_id, vorname, nachname, email, password, fahrzeug_id, dauerparker)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ', [$parkerId, $firstName, $lastName, $email, $password, $kennzeichen, $dauerparker]);

            // Redirect to the login page or any other desired page
            return $this->redirectToRoute('security_login');
        }

        // Render the registration form template
        return $this->render('pages/register.html.twig');
    }


}

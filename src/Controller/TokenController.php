<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Firebase\JWT\JWT;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class TokenController extends AbstractController
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var JWTEncoderInterface
     */
    private $jwtEncoder;

    /**
     * TokenController constructor.
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param JWTEncoderInterface $jwtEncoder
     */
    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        JWTEncoderInterface $jwtEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->jwtEncoder = $jwtEncoder;
    }

    /**
     * @Route("/api/tokens", name="api_token", methods={"POST"})
     */
    public function newTokenAction(Request $request, UserRepository $userRepository)
    {
        $user = $userRepository->findOneBy(['email' => $request->get('email') ]);

        if (!$user) {
            throw $this->createNotFoundException();
        }

        // check password is valid
        $isValid = $this->passwordEncoder->isPasswordValid($user, $request->get('password'));

        if (!$isValid) {
            throw new BadCredentialsException();
        }
        //generate token
        $token = JWT::encode([
            'email' => $user->getEmail(),
            'exp' => time() + 3600 // 1 hour expiration
        ], $this->getParameter('jwt_secret'), 'HS256');

        return new JsonResponse(['token' => $token]);
    }
}

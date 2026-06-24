<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Dto\Response\Authorize\UsersResponseDto;
use App\Security\UserProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * 用户控制器
 * @uses UserController
 */
#[Route('/api/user', defaults: ['anonymous' => false], methods: ['POST'])]
class UserController extends AbstractController
{
    /**
     * 获取当前用户信息
     * @param UserProvider $userProvider
     * @param UsersResponseDto $response
     * @return Response
     * @uses info
     */
    #[Route('/info')]
    public function info(
        UserProvider       $userProvider,
        UsersResponseDto   $response,
    ): Response
    {
        $cache = $userProvider->getUserCache();

        return $response->trans([
            'token' => $userProvider->getCredentials(),
            'cache' => $cache,
        ])->response();
    }
}

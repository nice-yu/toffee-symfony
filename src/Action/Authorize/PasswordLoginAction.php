<?php
declare(strict_types=1);

namespace App\Action\Authorize;

use App\Dto\Request\Authorize\LoginAuthRequest;
use App\Entity\Users;
use App\Exceptions\BusinessException;
use App\Repository\UserRepository;
use App\Task\Authorize\UserLoginTask;

/**
 * 手机号密码登录 Action
 * @uses PasswordLoginAction
 */
class PasswordLoginAction
{
    public function __construct(
        private UserRepository  $userRepository,
        private UserLoginTask   $loginTask,
    ) {}

    /**
     * @param LoginAuthRequest $dto
     * @return array{token: string, cache: object}
     */
    public function run(LoginAuthRequest $dto): array
    {
        /**
         * 获取用户信息
         * @var Users $userInfo
         */
        $userInfo = $this->userRepository->findOneBy(['phone' => $dto->phone]);

        /** 验证用户状态 */
        if (is_null($userInfo) || $userInfo->getStatus() !== 1) {
            throw new BusinessException(message: '手机号或密码错误');
        }

        /** 验证用户密码 */
        if (!password_verify($dto->password . $userInfo->getSalt(), $userInfo->getPassword() ?? '')) {
            throw new BusinessException(message: '手机号或密码错误');
        }

        return $this->loginTask->run($userInfo, $dto->loginType);
    }
}

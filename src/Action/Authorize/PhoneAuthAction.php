<?php
declare(strict_types=1);

namespace App\Action\Authorize;

use App\Dto\Request\Authorize\LoginAuthRequest;
use App\Exceptions\BusinessException;
use App\Repository\UserRepository;
use App\Task\Authorize\UserLoginTask;
use App\Task\Authorize\UserRegisterTask;
use App\Task\Common\VerifyCodeTask;
use Exception;
use Psr\Log\LoggerInterface;

/**
 * 手机号验证码登录/注册 Action
 * @uses PhoneAuthAction
 */
class PhoneAuthAction
{
    public function __construct(
        private VerifyCodeTask    $verifyCodeTask,
        private UserRepository    $userRepository,
        private UserRegisterTask  $registerTask,
        private UserLoginTask     $loginTask,
        private LoggerInterface   $logger,
    ) {}

    /**
     * @param LoginAuthRequest $dto
     * @return array{token: string, cache: object}
     */
    public function run(LoginAuthRequest $dto): array
    {
        /** 校验短信验证码，失败透传 ValidatorParamsException */
        $this->verifyCodeTask->run($dto->phone, $dto->code);

        /** 查用户：已注册则登录，未注册则注册后登录 */
        $userInfo = $this->userRepository->findOneBy(['phone' => $dto->phone]);

        if (is_null($userInfo)) {
            try {
                $userInfo = $this->registerTask
                    ->setPhone($dto->phone)
                    ->setInviteCode($dto->inviteCode)
                    ->run($dto->loginType);
            } catch (Exception $e) {
                $this->logger->error('用户注册失败', [
                    'phone' => substr($dto->phone, 0, 3) . '****' . substr($dto->phone, -4),
                    'error' => $e->getMessage(),
                ]);
                throw new BusinessException(message: '注册失败，请稍后重试');
            }
        } elseif ($userInfo->getStatus() !== 1) {
            throw new BusinessException(message: '账号已被禁用，请联系客服');
        }

        return $this->loginTask->run($userInfo, $dto->loginType);
    }
}

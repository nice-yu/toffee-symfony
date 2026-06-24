<?php
declare(strict_types=1);

namespace App\Action\Authorize;

use App\Dto\Request\Authorize\WechatLoginRequest;
use App\Entity\UserWechat;
use App\Exceptions\ServiceException;
use App\Repository\UserRepository;
use App\Service\WeChat\MiniProgram\JsCodeService;
use App\Task\Authorize\UserLoginTask;
use App\Task\Authorize\UserRegisterTask;
use Doctrine\ORM\EntityManagerInterface;

/**
 * 微信小程序登录 Action
 *
 * 流程：code 换 openid → 查 user_wechat → 已绑定则登录，未绑定则注册+绑定
 * @uses WechatLoginAction
 */
class WechatLoginAction
{
    public function __construct(
        private JsCodeService          $jsCodeService,
        private UserRepository         $userRepository,
        private EntityManagerInterface $entityManager,
        private UserRegisterTask       $registerTask,
        private UserLoginTask          $loginTask,
    ) {}

    /**
     * @param WechatLoginRequest $dto
     * @return array{token: string, cache: object}
     */
    public function run(WechatLoginRequest $dto): array
    {
        /** code 换 openid */
        $result  = $this->jsCodeService->run($dto->code);
        $openid  = $result['openid'] ?? '';
        $unionid = $result['unionid'] ?? '';
        if (empty($openid)) {
            throw new ServiceException(message: '微信登录失败，请稍后重试');
        }

        /** 查微信绑定 */
        $wechat = $this->entityManager->getRepository(UserWechat::class)->findOneBy(['openid' => $openid]);

        if ($wechat) {
            /** 已绑定：加载关联 → 状态检查 */
            $user = $this->userRepository->findUserWithRelations($wechat->getUserId(), ['up', 'uw', 'ud']);
            if ($user === null || $user->getStatus() !== 1) {
                throw new ServiceException(message: '账号已被禁用，请联系客服');
            }
            if ($user->getProfile()) {
                if (!empty($wechat->getNickname())) {
                    $user->getProfile()->setNickname($wechat->getNickname());
                }
                if (!empty($wechat->getAvatarUrl())) {
                    $user->getProfile()->setAvatarUrl($wechat->getAvatarUrl());
                }
            }
        } else {
            /** 未绑定：注册 + 绑定微信 + 登录 */
            $user = $this->registerTask
                ->setPhone('')
                ->setInviteCode($dto->inviteCode)
                ->setOpenid($openid)
                ->setUnionid($unionid)
                ->run($dto->loginType);
        }

        return $this->loginTask->run($user, $dto->loginType);
    }
}

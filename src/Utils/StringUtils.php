<?php
declare(strict_types=1);

namespace App\Utils;

use Random\RandomException;

class StringUtils
{
    /**
     * 隐藏11位手机号的中间四位数
     *
     * @param string $phone 11位手机号（如 "13812345678"）
     * @return string 脱敏后的手机号（如 "138****5678"）
     */
    public static function maskPhone(string $phone): string {
        /** 去除可能存在的空格、横线等非数字字符 */
        $digits = preg_replace('/\D/', '', $phone);
        if (strlen($digits) !== 11) {
            /** 非标准11位时的回退：原样返回，或只隐藏后半部分 */
            return $phone;
        }
        return substr($digits, 0, 3) . '****' . substr($digits, -4);
    }

    /**
     * 获取随机字符串
     * @param int $length
     * @return string
     */
    public static function random(int $length = 16): string
    {
        try {
            return bin2hex(random_bytes(intdiv($length, 2)));
        } catch (RandomException) {
            return self::inviteCode($length);
        }
    }

    /**
     * 获取随机邀请码
     * @param int $length
     * @return string
     */
    public static function inviteCode(int $length = 6): string
    {
        // 去除: 0O 1I
        return substr(str_shuffle('23456789ABCDEFGHJKLMNPQRSTUVWXYZ'), 0, $length);
    }
}
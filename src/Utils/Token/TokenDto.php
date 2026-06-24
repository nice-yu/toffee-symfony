<?php
declare(strict_types=1);

namespace App\Utils\Token;

/**
 * Token 载荷
 */
class TokenDto
{
    public int $id;
    public int $expire;
    public int $device;
}

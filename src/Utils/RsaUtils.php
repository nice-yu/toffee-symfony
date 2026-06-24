<?php
declare(strict_types=1);

namespace App\Utils;

use App\Exceptions\ServiceException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * RSA 加密工具类
 */
class RsaUtils
{
    public function __construct(
        private array $reaConfig,
    ) {}

    /**
     * 加密
     * @param string $plaintext
     * @return string|null
     */
    public function encrypt(string $plaintext): ?string
    {
        $key = $this->getCipher();
        if (!openssl_public_encrypt($plaintext, $cipherText, $key)) {
            return null;
        }
        return base64_encode($cipherText);
    }

    /**
     * 解密
     * @param string $cipherText
     * @return string|null
     */
    public function decrypt(string $cipherText): ?string
    {
        $key = $this->getCipher(false);
        $cipherText = base64_decode($cipherText);
        if (!openssl_private_decrypt($cipherText, $plainText, $key)) {
            return null;
        }
        return $plainText;
    }

    /**
     * 获取密钥
     */
    private function getCipher(bool $mode = true): string
    {
        $public  = $this->reaConfig['public'];
        $private = $this->reaConfig['private'];

        if (!is_file($public) || !is_file($private)) {
            return $this->generateKeys();
        }

        return file_get_contents($mode ? $public : $private);
    }

    /**
     * 生成密钥文件
     */
    private function generateKeys(): string
    {
        $public  = $this->reaConfig['public'];
        $private = $this->reaConfig['private'];
        $bits    = (int) ($this->reaConfig['bits'] ?? 2048);
        $config  = $this->reaConfig['config'] ?? '';

        $res = openssl_pkey_new([
            'private_key_bits' => $bits,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
            'config'           => $config,
            'digest_alg'       => 'sha256',
        ]);

        if ($res === false) {
            throw new ServiceException(message: '生成密钥失败: ' . openssl_error_string());
        }

        $configArgs = $config ? ['config' => $config] : [];
        openssl_pkey_export($res, $privateKey, null, $configArgs);

        $publicKey = openssl_pkey_get_details($res)['key'];

        $this->ensureDirectoryExists($public);
        $this->ensureDirectoryExists($private);

        file_put_contents($public, $publicKey);
        file_put_contents($private, $privateKey);

        return $publicKey;
    }

    /**
     * 确保目录存在
     */
    private function ensureDirectoryExists(string $filePath): void
    {
        $directoryPath = dirname($filePath);
        if (!is_dir($directoryPath)) {
            (new Filesystem())->mkdir($directoryPath, 0775);
        }
    }
}

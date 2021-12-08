<?php

namespace Cryption;

use RuntimeException;
use Cryption\Exception\DecryptException;
use Cryption\Exception\EncryptException;

class Encrypter extends BaseEncrypter
{
    /**
     * The algorithm used for encryption.
     *
     * @var string
     */
    protected $key, $cipher, $iv;

    /**
     * Create a new encrypter instance.
     *
     * @param  string  $key
     * @param  string  $cipher
     * @return void
     *
     * @throws \RuntimeException
     */
    public function __construct($key, $cipher = 'AES-128-CBC', $iv)
    {
        $iv = $iv;

        if (str_starts_with($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        } else {
            $key = $key;
        }

        // dd([$key, $cipher, $iv]);

        if (static::supported($key, $cipher)) {
            $this->key = $key;
            $this->cipher = $cipher;
            $this->iv = $iv;
        } else {
            throw new RuntimeException('The only supported ciphers are AES-128-CBC and AES-256-CBC with the correct key lengths.');
        }

        parent::__construct($this->iv);
    }

    /**
     * Determine if the given key and cipher combination is valid.
     *
     * @param  string  $key
     * @param  string  $cipher
     * @return bool
     */
    public static function supported($key, $cipher)
    {
        $length = mb_strlen($key, '8bit');

        return ($cipher === 'AES-128-CBC' && $length === 16) || ($cipher === 'AES-256-CBC' && $length === 32);
    }

    /**
     * Encrypt the given value.
     *
     * @param  string  $value
     * @return string
     *
     * @throws \Illuminate\Contracts\Encryption\EncryptException
     */
    public function encrypt($value)
    {
        if (is_null($value)) {
            return null;
        } else if (!$value) {
            return '';
        }

        //$iv = \random_bytes($this->getIvSize());

        $value = \openssl_encrypt(serialize($value), $this->cipher, $this->key, 0, $this->iv);

        if ($value === false) {
            throw new EncryptException('Could not encrypt the data.');
        }

        // Once we have the encrypted value we will go ahead base64_encode the input
        // vector and create the MAC for the encrypted value so we can verify its
        // authenticity. Then, we'll JSON encode the data in a "payload" array.
        // $mac = $this->hash($iv = base64_encode($this->iv), $value);
        $mac = $this->hash($this->iv, $value);

        $json = json_encode(compact('value', 'mac'));

        if (!is_string($json)) {
            throw new EncryptException('Could not encrypt the data.');
        }

        return base64_encode($json);
    }

    /**
     * Decrypt the given value.
     *
     * @param  string  $payload
     * @return string
     *
     * @throws \Illuminate\Contracts\Encryption\DecryptException
     */
    public function decrypt($payload)
    {
        if (!$payload) {
            return null;
        }

        $payload = $this->getJsonPayload($payload);

        if (!$payload) {
            return null;
        }

        $decrypted = \openssl_decrypt($payload['value'], $this->cipher, $this->key, 0, $this->iv);

        if ($decrypted === false) {
            throw new DecryptException('Could not decrypt the data.');
        }

        return unserialize($decrypted);
    }

    /**
     * Get the IV size for the cipher.
     *
     * @return int
     */
    protected function getIvSize()
    {
        return 16;
    }
}

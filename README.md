# Yii2 Laravel Cryption
this is same as laravel(>=5.1) encrypt and decrypt function.

## Changelog
### 1.1.4 November 16, 2021
* Enh : Add behavior for an Active record

## Installation
```
composer require mazfreelance/yii2-laravel-crypt
```

## Config
add your params local
```
'encrypter' => [
    'key' = '',
    'cipher' = ''
]
```

## Usage:
#### 1) single use OR
```
use Cryption\Encrypter;

$cryption = Encrypter("yourRandomString","AES-256-CBC");
$cryption->encrypt("yourData");`
```
#### 2) this Behavior is used to encrypt data before storing it on the database and to decrypt it upon retrieval.
- add the following code on Model class
```
public function behaviors()
{
    return [
        'encryption' => [
            'class' => '\Cryption\EncryptionBehavior',
            'attributes' => [
                'attribute1',
                'attribute2',
            ],
        ],
    ];
}
```
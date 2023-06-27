<?php

namespace App\Models;

use InvalidArgumentException;

enum ImportSource: string
{
    case LOCAL = 'local';

    case WEBDAV = 'webdav';


    public function configurationTemplate(): array
    {
        switch ($this) {
            case self::WEBDAV:
                return [
                    'url' => __('The URL of the server'),
                    'username' => __('The username to use for authentication'),
                    'password' => __('The password to use for authentication'),
                ];
                break;
            case self::LOCAL:
                return [
                    'root' => __('The root folder'),
                ];
                break;
        }

        throw new InvalidArgumentException('No configuration template defined for ' . $this->name);
    }
}

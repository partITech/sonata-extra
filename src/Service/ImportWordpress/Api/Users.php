<?php

namespace Partitech\SonataExtra\Service\ImportWordpress\Api;

class Users extends Api
{
    protected string $endPoint = '/wp-json/wp/v2/users';
    protected bool $auth = true;
}

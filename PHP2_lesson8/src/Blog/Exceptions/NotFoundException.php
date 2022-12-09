<?php

namespace GeekBrains\LevelTwo\Blog\Exceptions;

use Psr\Container\NotFoundExceptionInterface;
use Exception;

class NotFoundException extends AppException implements NotFoundExceptionInterface
{

}
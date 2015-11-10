<?php

namespace Imager;


interface Exception
{}

class InvalidArgumentException extends \InvalidArgumentException implements Exception
{}

class NotExistsException extends \InvalidArgumentException implements Exception
{}

class BadPermissionException extends \InvalidArgumentException implements Exception
{}

class RuntimeException extends \RuntimeException implements Exception
{}

class InvalidStateException extends \RuntimeException implements Exception
{}

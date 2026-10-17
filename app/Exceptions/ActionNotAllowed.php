<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A business rule said no (e.g. "you can't delete yourself").
 * Controllers turn it into a red flash message instead of an error page.
 */
class ActionNotAllowed extends RuntimeException {}

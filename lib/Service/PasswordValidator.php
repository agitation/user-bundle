<?php
declare(strict_types=1);

/*
 * @package    agitation/user-bundle
 * @link       http://github.com/agitation/user-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\UserBundle\Service;

use Agit\IntlBundle\Tool\Translate;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PasswordValidator
{
    const ALLOWED_CHARS = '!§$%&/()[]{}=?@+*~#-_.:,;';

    public function validate($password)
    {
        if (strlen($pass1) < 8)
        {
            throw new BadRequestHttpException(sprintf(Translate::t('The password must have at least %d characters.'), 8));
        }

        if (! preg_match('|\d|', $pass1) || ! preg_match('|[a-z]|i', $pass1))
        {
            throw new BadRequestHttpException(Translate::t('The password must contain at least one letter and one number.'));
        }

        if (preg_match('|[^a-z0-9' . preg_quote(static::ALLOWED_CHARS, '|') . ']|i', $pass1))
        {
            throw new BadRequestHttpException(sprintf(Translate::t('The password must only consist of letters, numbers and the characters %s.'), static::ALLOWED_CHARS));
        }
    }

    public function createValidPassword()
    {
        return base64_encode(random_bytes(18)) . '@b3';
    }
}

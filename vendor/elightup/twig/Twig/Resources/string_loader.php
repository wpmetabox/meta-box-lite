<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use eLightUp\Twig\Environment;
use eLightUp\Twig\Extension\StringLoaderExtension;
use eLightUp\Twig\TemplateWrapper;

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function elightup_twig_template_from_string(Environment $env, $template, ?string $name = null): TemplateWrapper
{
    trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return StringLoaderExtension::templateFromString($env, $template, $name);
}

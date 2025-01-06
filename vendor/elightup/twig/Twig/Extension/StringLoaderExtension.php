<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace eLightUp\Twig\Extension {
use eLightUp\Twig\TwigFunction;

final class StringLoaderExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('template_from_string', 'elightup_twig_template_from_string', ['needs_environment' => true]),
        ];
    }
}
}

namespace {
use eLightUp\Twig\Environment;
use eLightUp\Twig\TemplateWrapper;

    if (!function_exists('elightup_twig_template_from_string')) {
        /**
         * Loads a template from a string.
         *
         *     {{ include(template_from_string("Hello {{ name }}")) }}
         *
         * @param string $template A template as a string or object implementing __toString()
         * @param string $name     An optional name of the template to be used in error messages
         */
        function elightup_twig_template_from_string(Environment $env, $template, string $name = null): TemplateWrapper
        {
            return $env->createTemplate((string) $template, $name);
        }
    }
}

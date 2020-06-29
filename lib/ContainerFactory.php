<?php

/**
 * RQuadling/DependencyInjection
 *
 * LICENSE
 *
 * This is free and unencumbered software released into the public domain.
 *
 * Anyone is free to copy, modify, publish, use, compile, sell, or distribute this software, either in source code form or
 * as a compiled binary, for any purpose, commercial or non-commercial, and by any means.
 *
 * In jurisdictions that recognize copyright laws, the author or authors of this software dedicate any and all copyright
 * interest in the software to the public domain. We make this dedication for the benefit of the public at large and to the
 * detriment of our heirs and successors. We intend this dedication to be an overt act of relinquishment in perpetuity of
 * all present and future rights to this software under copyright law.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT
 * OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * For more information, please refer to <https://unlicense.org>
 *
 */

namespace RQuadling\DependencyInjection;

use DI\Container;
use DI\ContainerBuilder;
use Exception;
use RQuadling\Environment\Environment;

class ContainerFactory
{
    /**
     * @param bool $forceRebuild This invalidates any pre-built dependencies.
     *                           PLEASE NOTE: It does not invalidate the definitions from which the dependencies are resolved!
     * @param array<string, mixed> $mocks Allow mocks to be injected when rebuilding the container
     *
     * @throws Exception
     */
    public static function build(bool $forceRebuild = false, array $mocks = []): Container
    {
        /** @var Container */
        static $container;

        /** @var string */
        static $config;

        if (empty($config)) {
            $config = \sprintf('%s/di.php', Environment::getRoot());
        }

        if (!$container instanceof Container || $forceRebuild) {
            $containerBuilder = new ContainerBuilder();
            $containerBuilder->useAutowiring(true);
            $containerBuilder->useAnnotations(true);

            // Add the default DI configuration first, and then add mocks (if there are any).
            // The resolver works in the reverse order, so the mocks will be checked first.
            if (\file_exists($config)) {
                $containerBuilder->addDefinitions($config);
            }
            if ($mocks) {
                $containerBuilder->addDefinitions($mocks);
            }

            $container = $containerBuilder->build();
        }

        return $container;
    }
}

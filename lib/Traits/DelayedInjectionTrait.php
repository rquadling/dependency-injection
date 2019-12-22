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

namespace RQuadling\DependencyInjection\Traits;

use DI\Container;
use DI\NotFoundException;
use PhpDocReader\PhpDocReader;
use ReflectionException;
use RQuadling\DependencyInjection\ContainerFactory;
use RQuadling\Reflection\ReflectionClass;
use RQuadling\Reflection\ReflectionProperty;
use Throwable;

trait DelayedInjectionTrait
{
    /**
     * @var Container
     */
    protected $container;

    protected function handleDelayedInjection()
    {
        $this->loadManualDependencies($this->loadAutomaticDependencies());
    }

    /**
     * Load dependencies after the container has been made aware of the required environment.
     *
     * @return ReflectionProperty[] a set of properties that could not be loaded automatically
     *
     * @throws ReflectionException
     */
    private function loadAutomaticDependencies(): array
    {
        /** @var ReflectionProperty[] $loadableProperties */
        $loadableProperties = \array_filter(
            (new ReflectionClass(\get_called_class()))->getProperties(),
            function (ReflectionProperty $property) {
                return $property->isDelayedInjected();
            }
        );

        $dependenciesThatMustBeLoadedManually = [];
        if ($loadableProperties) {
            $this->container = ContainerFactory::build();
            $docblockParser = new PhpDocReader();
            foreach ($loadableProperties as $property) {
                try {
                    if (!$property->isPublic()) {
                        $property->setAccessible(true);
                    }
                    $class = $docblockParser->getPropertyClass($property);
                    if ($class) {
                        $property->setValue($this, $this->container->get($class));
                    } else {
                        $dependenciesThatMustBeLoadedManually[] = $property;
                    }
                } catch (Throwable $exception) {
                    $dependenciesThatMustBeLoadedManually[] = $property;
                }
            }
        }

        return $dependenciesThatMustBeLoadedManually;
    }

    /**
     * Manually load any additional dependencies once the container has been made aware of the required environment.
     *
     * @param ReflectionProperty[] $manualDependencies a set of properties that could not be loaded automatically
     *
     * @throws NotFoundException
     */
    protected function loadManualDependencies(array $manualDependencies = [])
    {
        if ($manualDependencies) {
            $calledClass = \get_called_class();
            $renderedManualDependencies = \implode(
                PHP_EOL,
                \array_map(
                    function (ReflectionProperty $property) {
                        return \sprintf(
                            '        $this->%s = $this->container->get(?????::class); // Unable to determine dependency from "%s".',
                            $property->getName(),
                            $property->getTypeFromDocBlock()
                        );
                    },
                    $manualDependencies
                )
            );

            throw new NotFoundException("

    /**
     * Load dependencies after the container has been made aware of the required environment.
     *
     * @param \RQuadling\Reflection\ReflectionProperty[] \$manualDependencies A set of properties that could not be loaded automatically.
     *
     * The code included below has been automatically generated as a template for you to include and amend in the
     * \\{$calledClass} class.
     *
     * It is based upon @Inject'd and @DelayedInject'd properties that failed to have the type correctly parsed.
     */
    protected function loadManualDependencies(array \$manualDependencies = [])
    {
{$renderedManualDependencies}
    }

");
        }
    }
}

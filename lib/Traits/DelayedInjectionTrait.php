<?php

namespace RQuadling\DependencyInjection\Traits;

use DI\Container;
use DI\NotFoundException;
use PhpDocReader\PhpDocReader;
use ReflectionException;
use Throwable;
use RQuadling\DependencyInjection\ContainerFactory;
use RQuadling\Reflection\ReflectionClass;
use RQuadling\Reflection\ReflectionProperty;

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
     * @return ReflectionProperty[] A set of properties that could not be loaded automatically.
     *
     * @throws ReflectionException
     */
    private function loadAutomaticDependencies(): array
    {
        /** @var ReflectionProperty[] $loadableProperties */
        $loadableProperties = array_filter(
            (new ReflectionClass(get_called_class()))->getProperties(),
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
     * @param ReflectionProperty[] $manualDependencies A set of properties that could not be loaded automatically.
     *
     * @throws NotFoundException
     */
    protected function loadManualDependencies(array $manualDependencies = [])
    {
        if ($manualDependencies) {
            $calledClass = get_called_class();
            $renderedManualDependencies = implode(
                PHP_EOL,
                array_map(
                    function (ReflectionProperty $property) {
                        return sprintf(
                            '        $this->%s = $this->container->get(?????::class); // Unable to determine dependency from "%s".',
                            $property->getName(),
                            $property->getTypeFromDocBlock()
                        );
                    },
                    $manualDependencies
                )
            );

            throw new NotFoundException(
                <<< END_PHP

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


END_PHP
            );
        }
    }
}

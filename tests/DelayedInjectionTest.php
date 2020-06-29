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

namespace RQuadlingTests\DependencyInjection;

use DI\NotFoundException;
use PHPUnit\Framework\TestCase;
use RQuadlingTests\DependencyInjection\Fixtures\Fixture;
use RQuadlingTests\DependencyInjection\Fixtures\UsesDelayedInject;
use RQuadlingTests\DependencyInjection\Fixtures\UsesDelayedInjectWithManualDependencies;

class DelayedInjectionTest extends TestCase
{
    public function testDelayedInjection(): void
    {
        $instance = new UsesDelayedInject();

        $instance->handleDelayedInjectionCaller();
        $this->assertInstanceOf(Fixture::class, $instance->getFixture());
    }

    public function testDelayedInjectionRequiresManualDependencies(): void
    {
        $instance = new UsesDelayedInjectWithManualDependencies();

        $this->assertNull($instance->getMissingFixture());

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('/**
     * Load dependencies after the container has been made aware of the required environment.
     *
     * @param \RQuadling\Reflection\ReflectionProperty[] $manualDependencies A set of properties that could not be loaded automatically.
     *
     * The code included below has been automatically generated as a template for you to include and amend in the
     * \RQuadlingTests\DependencyInjection\Fixtures\UsesDelayedInjectWithManualDependencies class.
     *
     * It is based upon @Inject\'d and @DelayedInject\'d properties that failed to have the type correctly parsed.
     */
    protected function loadManualDependencies(array $manualDependencies = [])
    {
        $this->missingFixture = $this->container->get(?????::class); // Unable to determine dependency from "MissingFixture".
        $this->untyped = $this->container->get(?????::class); // Unable to determine dependency from "".
    }');
        $instance->handleDelayedInjectionCaller();
    }
}

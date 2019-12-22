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

use DI\Container;
use DI\NotFoundException;
use PHPUnit\Framework\TestCase;
use RQuadling\DependencyInjection\ContainerFactory;
use RQuadlingTests\DependencyInjection\Fixtures\Fixture;

class ContainerFactoryTest extends TestCase
{
    public function testBuild()
    {
        $this->assertInstanceOf(Container::class, ContainerFactory::build());
    }

    public function testRebuild()
    {
        $containers = [
            ContainerFactory::build(false),
            ContainerFactory::build(true),
            ContainerFactory::build(false),
            ContainerFactory::build(true),
        ];

        $this->assertNotSame($containers[0], $containers[1]);
        $this->assertNotSame($containers[0], $containers[2]);
        $this->assertNotSame($containers[0], $containers[3]);
        $this->assertSame($containers[1], $containers[2]);
        $this->assertNotSame($containers[1], $containers[3]);
        $this->assertNotSame($containers[2], $containers[3]);
    }

    public function testRebuildWithMocks()
    {
        $mocks = [
            'Mocked' => new Fixture(),
        ];

        try {
            ContainerFactory::build(false, $mocks)->get('Mocked');
        } catch (NotFoundException $exception) {
            $this->assertEquals($exception->getMessage(), 'No entry or class found for \'Mocked\'');
        }

        $mock1 = ContainerFactory::build(true, $mocks)->get('Mocked');
        $this->assertInstanceOf(Fixture::class, $mock1);

        $mock2 = ContainerFactory::build(false, $mocks)->get('Mocked');
        $this->assertInstanceOf(Fixture::class, $mock2);

        $this->assertSame($mock1, $mock2);
    }
}

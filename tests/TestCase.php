<?php

namespace Coconuts\Mail\Tests;

use Coconuts\Mail\PostmarkServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use ReflectionClass;

class TestCase extends Orchestra
{
    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('mail.driver', 'postmark');
        $app['config']->set('postmark.secret', 'POSTMARK_API_TEST');
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [PostmarkServiceProvider::class];
    }

    /**
     * Invoke a non-public method.
     *
     * @param  mixed  $object
     * @param  string  $name
     * @param  array  $params
     * @return mixed
     *
     * @throws \ReflectionException
     */
    protected function invokeMethod(&$object, $name, array $params = [])
    {
        $reflection = new ReflectionClass(get_class($object));
        $method = $reflection->getMethod($name);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $params);
    }

    /**
     * Read a non-public property.
     *
     * @param  mixed  $object
     * @param  string  $name
     * @return mixed
     *
     * @throws \ReflectionException
     */
    protected function readProperty(&$object, $name)
    {
        $reflection = new ReflectionClass(get_class($object));
        $property = $reflection->getProperty($name);
        $property->setAccessible(true);

        return $property->getValue($object);
    }
}

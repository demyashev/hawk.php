<?php

declare(strict_types=1);

namespace Hawk;

use Hawk\Addons\AddonInterface;
use Hawk\Addons\Headers;
use Hawk\Addons\Os;
use Hawk\Addons\Runtime;
use Hawk\Util\Stacktrace;

/**
 * Class EventPayloadFactory is a factory object
 *
 * @package Hawk
 */
class EventPayloadFactory
{
    /**
     * List of addon resolvers
     *
     * @var array
     */
    private $addonsResolvers = [];

    /**
     * EventPayloadFactory constructor.
     */
    public function __construct()
    {
        $this->addonsResolvers['runtime'] = new Runtime();
        $this->addonsResolvers['server'] = new Os();
        $this->addonsResolvers['header'] = new Headers();
    }

    /**
     * Returns EventPayload object
     *
     * @param array $data - event payload
     *
     * @return EventPayload
     */
    public function create(array $data): EventPayload
    {
        $eventPayload = new EventPayload();

        if (isset($data['context'])) {
            $eventPayload->setContext($data['context']);
        }

        if (isset($data['user'])) {
            $eventPayload->setUser($data['user']);
        }

        if (isset($data['exception']) && $data['exception'] instanceof \Throwable) {
            $exception = $data['exception'];
            $backtrace = Stacktrace::buildStack($exception);

            $eventPayload->setTitle($exception->getMessage());
        } else {
            $backtrace = debug_backtrace();
        }

        $eventPayload->setBacktrace($backtrace);

        // Resolve addons
        $eventPayload->setAddons($this->resolveAddons());

        return $eventPayload;
    }

    /**
     * Resolves addons list and returns array
     *
     * @return array
     */
    private function resolveAddons(): array
    {
        $result = [];

        /**
         * @var string         $key
         * @var AddonInterface $resolver
         */
        foreach ($this->addonsResolvers as $key => $resolver) {
            $result[$key] = $resolver->resolve();
        }

        return $result;
    }
}

<?php

namespace Garlic\HealthCheck\Service\Processor;

use Enqueue\Client\TopicSubscriberInterface;
use Garlic\Bus\Service\Abstracts\ProcessorConfigAbstract;
use Garlic\Bus\Service\CommunicatorService;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Interop\Queue\Processor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ServiceDiscoveryProcessor extends ProcessorConfigAbstract implements Processor, TopicSubscriberInterface
{
    public static function getSubscribedTopics()
    {
        return ['serviceDiscovery']; //, 'anotherTopic' and any other
    }

    /**
     * {@inheritdoc}
     */
    public function process(Message $message, Context $context)
    {
        $payload = \json_decode($message->getBody());
        $emitTime = $payload->path->date;

        $container = $this->kernel->getContainer();

        $response = $container->get('http_kernel')
            ->handle(Request::create('/graphql', 'POST', ['query' => $this->getIntrospectionQuery()]), HttpKernelInterface::MASTER_REQUEST);

        $container->get(CommunicatorService::class)
            ->command('gateway')
            ->post()
            ->serviceRebuildSchema([], [
                'data' => $response->getContent(),
                'name' => getenv('SERVICE_NAME'),
                'timing' => $emitTime ? microtime(true) - $emitTime : 0
            ]);

        return self::ACK;
    }

    protected function getIntrospectionQuery()
    {
        return file_get_contents(__DIR__ . '/../../Resources/query/introspection.graphql');
    }
}

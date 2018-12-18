<?php

namespace Garlic\HealthCheck\Service\Processor;

use Enqueue\Client\TopicSubscriberInterface;
use Garlic\Bus\Service\Abstracts\ProcessorConfigAbstract;
use Garlic\Bus\Service\CommunicatorService;
use Interop\Queue\PsrProcessor;
use Interop\Queue\PsrContext;
use Interop\Queue\PsrMessage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ServiceDiscoveryProcessor extends ProcessorConfigAbstract implements PsrProcessor, TopicSubscriberInterface
{
    public static function getSubscribedTopics()
    {
        return ['serviceDiscovery']; //, 'anotherTopic' and any other
    }

    /**
     * {@inheritdoc}
     * @todo:: implement
     */
    public function process(PsrMessage $message, PsrContext $context)
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
